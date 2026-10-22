<?php
/**
 * Orchestrateur de la fiche école.
 * Appelé par detail-resultat-admission-par-ecole.php, il prépare les onglets,
 * rend les spécialités disponibles immédiatement et délègue les contenus
 * lourds aux endpoints AJAX au premier clic. Les tests Salaire/Spécialités
 * servent à ne pas afficher d'onglet sans donnée exploitable.
 * Essai visuel sans évolution de la base : identité canonique, contexte conservé,
 * quatre rubriques et sous-onglets de filière à l'intérieur d'Admissions. Seul un identifiant
 * explicite et vérifié peut sélectionner un diplôme, jamais son nom approchant.
 */

	$debug = isset($debug) ? $debug : false;
	$filiere = isset($filiere) ? $filiere : '';
	$ecole = isset($ecole) ? $ecole : '';
	$origine = isset($origine) ? $origine : '';

	require_once __DIR__ . '/detailEntete.php';
	require_once __DIR__ . '/detailClassement.php';
	require_once __DIR__ . '/detailFiliere.php';
	require_once __DIR__ . '/detailSpecialite.php';

	// nom de l'école tel qu'il figure dans la table Note (libellé propre au concours)
	$ecoleFiltre = str_replace("\\'", "'", remettreEsperluete($ecole));

	if ($ecoleFiltre === '' || $ecoleFiltre === 'toutes') {
		echo "<main class='container'><div class='alert alert-warning' style='margin-top:20px;'>";
		echo "<i class='bi bi-exclamation-triangle'></i>&nbsp; Aucune école sélectionnée.";
		echo "</div></main>";
		return;
	}

	// Chaque visite recalcule la page : la table Salaire/Note est indexée et
	// les onglets (Classements, une filière) sont chargés à la demande par
	// ajax/detailOnglet.php, donc le premier affichage reste léger (~4 requêtes).
	try {
		if (!isset($db) || !($db instanceof PDO)) {
			$db = openDatabase();
		}
	}
	catch (PDOException $erreur) {
		die('Erreur connexion base : ' . $erreur->getMessage());
	}

		// données légères nécessaires au premier affichage : liste des filières
		// (barre d'onglets) et URL officielle (bandeau). Les classements/attractivité
		// et le détail de chaque filière ne sont chargés qu'à l'ouverture de l'onglet.
		$ecolesConcoursRecherchees = obtenirEcolesConcoursRecherchees($db, $ecoleFiltre, $origine);
		$filieresEcole = obtenirFilieresEcole($db, $ecolesConcoursRecherchees, $debug);
		$urlEcole = obtenirUrlEcole($db, $ecoleFiltre);
		// Résout le nom canonique utilisé par Diplome, Specialite et EcoleSalaire.
		$ecoleCanonique = $ecoleCanoniqueFiche ?? obtenirNomEcoleFiche($db, $ecoleFiltre);
		$idDiplomeConsulte = null;
		$contexteFiche = $ecoleFiltre !== $ecoleCanonique
			? "Voie d'admission consultée : " . libelleFormationFiche($ecoleCanonique, $ecoleFiltre) : '';
		$erreurDiplome = '';
		if (isset($_GET['diplome']) && $_GET['diplome'] !== '') {
			$idDemande = filter_var($_GET['diplome'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
			$diplome = false;
			if ($idDemande !== false) {
				$stmtDiplome = $db->prepare("SELECT d.IdDiplome, d.Diplome FROM Diplome d
					JOIN Ecole e ON e.IdEcole = d.IdEcole WHERE d.IdDiplome = :id AND e.Ecole = :ecole");
				$stmtDiplome->execute([':id' => $idDemande, ':ecole' => $ecoleCanonique]);
				$diplome = $stmtDiplome->fetch(PDO::FETCH_ASSOC);
				$stmtDiplome->closeCursor();
			}
			if ($diplome) {
				$idDiplomeConsulte = (int) $diplome['IdDiplome'];
				$contexteFiche = "Diplôme consulté : " . libelleFormationFiche($ecoleCanonique, $diplome['Diplome']);
			} else {
				$erreurDiplome = "Le diplôme demandé n'appartient pas à cette école ou n'est plus disponible. Aucun autre diplôme n'a été sélectionné automatiquement.";
			}
		}

		// Un diplôme seul ne suffit pas : l'onglet exige une spécialité collectée.
		$stmtSpecialites = $db->prepare("SELECT 1 FROM Ecole e
			JOIN Diplome d ON d.IdEcole = e.IdEcole
			JOIN Specialite s ON s.IdDiplome = d.IdDiplome
			WHERE (e.Ecole = :canonique OR e.Ecole = :directe)
			  AND s.Specialite IS NOT NULL AND s.Specialite <> '' LIMIT 1");
		$stmtSpecialites->execute([':canonique' => $ecoleCanonique, ':directe' => $ecoleFiltre]);
		$hasSpecialites = (bool) $stmtSpecialites->fetchColumn();

		// Le salaire est considéré disponible si au moins un horizon contient une valeur.
		$stmtSalaires = $db->prepare("SELECT 1 FROM Salaire
			JOIN EcoleSalaire es ON es.uo_lib = Salaire.uo_lib
			 AND (es.denomination_principale = '' OR es.denomination_principale = Salaire.denomination_principale)
			WHERE es.Ecole = :ecole
			  AND Salaire.type_diplome = 'Formation ingénieur'
			  AND Salaire.libelle_diplome = 'Tout diplôme d\\'ingénieur'
			  AND Salaire.obtention_diplome = 'diplômé'
			  AND Salaire.nationalite = 'ensemble' AND Salaire.genre = 'ensemble'
			  AND Salaire.regime_inscription = 'ensemble'
			  AND Salaire.promo_annee IS NOT NULL AND Salaire.promo_annee NOT IN ('2023','2024')
			  AND Salaire.uo_lib <> 'National'
			  AND (Salaire.salaire_q2_12 > 0 OR Salaire.salaire_q2_18 > 0 OR Salaire.salaire_q2_24 > 0 OR Salaire.salaire_q2_30 > 0)
			LIMIT 1");
		$stmtSalaires->execute([':ecole' => $ecoleCanonique]);
		$hasSalaires = (bool) $stmtSalaires->fetchColumn();
		$ongletActif = $hasSpecialites ? 'specialites' : ($hasSalaires ? 'salaire' : 'classements');
		$filiereDemandee = strtolower($filiere);
		$filiereSelectionnee = $filiereDemandee !== ''
			? (in_array($filiereDemandee, $filieresEcole, true) ? $filiereDemandee : '')
			: ($filieresEcole[0] ?? '');
		if (in_array($origine, ['classement', 'attractivite'], true)) {
			$ongletActif = 'classements';
		} elseif ($origine === 'salaire' && $hasSalaires) {
			$ongletActif = 'salaire';
		} elseif ($filiereDemandee !== '' && $origine !== 'specialite' && !empty($filieresEcole)) {
			$ongletActif = 'admissions';
		}

		// Spécialités est rendu immédiatement côté serveur : son contenu ne doit
		// jamais porter data-onglet, sinon le gestionnaire AJAX le remplacerait.
		// Salaire, Classements et les filières sont des fragments AJAX différés.
		afficherEnteteEcole($ecoleCanonique, $urlEcole, $filieresEcole, $contexteFiche);

		echo "<main class='container' style='margin-top:24px;'>";
		if ($erreurDiplome !== '') echo "<p class='alert alert-warning'>" . escapeHtml($erreurDiplome) . "</p>";

		// Quatre rubriques au maximum ; les filières ne concurrencent plus les rubriques.
		echo "<nav class='onglets-ecole'>";
		echo "<div class='nav nav-tabs' id='nav-ecole' role='tablist'>";

		$rubriques = [];
		if (!empty($filieresEcole)) $rubriques['admissions'] = 'Admissions';
		if ($hasSpecialites) $rubriques['specialites'] = 'Spécialités';
		$rubriques['classements'] = 'Classements';
		if ($hasSalaires) $rubriques['salaire'] = 'Salaires';
		$iconesRubriques = ['admissions' => 'person-check', 'specialites' => 'diagram-3', 'classements' => 'trophy', 'salaire' => 'cash-stack'];
		foreach ($rubriques as $cle => $libelle) {
			$id = identifiantOnglet($cle);
			echo "<button class='nav-link" . ($ongletActif === $cle ? ' active' : '') . "' id='tab-" . $id . "'"
			   . " data-bs-toggle='tab' data-bs-target='#" . $id . "' type='button' role='tab'"
			   . " aria-controls='" . $id . "' aria-selected='" . ($ongletActif === $cle ? 'true' : 'false') . "'>"
			   . "<i class='bi bi-" . $iconesRubriques[$cle] . "' aria-hidden='true'></i><span>" . escapeHtml($libelle) . "</span></button>";
		}
		echo "</div></nav>";
		echo "<div class='tab-content p-3 p-md-4 border border-top-0 bg-light' id='nav-ecoleContent'>";
		if (!empty($filieresEcole)) {
			echo "<div class='tab-pane fade" . ($ongletActif === 'admissions' ? ' show active' : '') . "' id='onglet-admissions' role='tabpanel' aria-labelledby='tab-onglet-admissions'>";
			echo "<p class='text-center small text-secondary mb-2' id='titre-filieres-admissions'>Filière CPGE</p>";
			echo "<div class='nav nav-pills filieres-admissions mb-3' id='filieres-admissions' role='tablist' aria-labelledby='titre-filieres-admissions'>";
			foreach ($filieresEcole as $cle) {
				$id = 'admissions-' . $cle;
				$selectionnee = $cle === $filiereSelectionnee;
				echo "<button class='nav-link" . ($selectionnee ? ' active' : '') . "' id='tab-" . escapeHtml($id) . "'"
				   . " data-bs-toggle='pill' data-bs-target='#" . escapeHtml($id) . "' data-filiere='" . escapeHtml($cle) . "' type='button' role='tab'"
				   . " aria-controls='" . escapeHtml($id) . "' aria-selected='" . ($selectionnee ? 'true' : 'false') . "'"
				   . " title='" . escapeHtml(libellesFilieres()[$cle] ?? $cle) . "'>"
				   . escapeHtml(strtoupper($cle)) . "</button>";
			}
			echo "</div>";
			if ($filiereDemandee !== '' && $filiereSelectionnee === '') {
				echo "<p class='alert alert-info'>Aucun résultat disponible pour la filière demandée. Sélectionnez une autre filière.</p>";
			}
			echo "<div class='tab-content' id='contenu-admissions'>";
			// Sans fondu : Bootstrap 5.0 peut réactiver un ancien panneau après un changement rapide.
			foreach ($filieresEcole as $cle) {
				echo "<div class='tab-pane" . ($cle === $filiereSelectionnee ? ' show active' : '') . "' id='admissions-" . escapeHtml($cle) . "'"
				   . " role='tabpanel' aria-labelledby='tab-admissions-" . escapeHtml($cle) . "' data-onglet='" . escapeHtml($cle) . "'>"
				   . "<p class='text-secondary' role='status'>Chargement des admissions…</p></div>";
			}
			echo "</div></div>";
		}
		echo "<div class='tab-pane fade" . ($ongletActif === 'classements' ? ' show active' : '') . "' id='onglet-classements' role='tabpanel' aria-labelledby='tab-onglet-classements' data-onglet='classements'>";
		echo "<div class='text-center text-secondary py-4'><span class='spinner-border spinner-border-sm'></span>&nbsp; Chargement...</div></div>";
		if ($hasSalaires) {
			echo "<div class='tab-pane fade" . ($ongletActif === 'salaire' ? ' show active' : '') . "' id='onglet-salaire' role='tabpanel' aria-labelledby='tab-onglet-salaire' data-onglet='salaire'>";
			echo "<div class='text-center text-secondary py-4'><span class='spinner-border spinner-border-sm'></span>&nbsp; Chargement...</div></div>";
		}
		if ($hasSpecialites) {
			echo "<div class='tab-pane fade" . ($ongletActif === 'specialites' ? ' show active' : '') . "' id='onglet-specialites' role='tabpanel' aria-labelledby='tab-onglet-specialites'>";
			afficherSpecialites($db, $ecoleFiltre, $debug, $idDiplomeConsulte);
			echo "</div>";
		}

		echo "</div>";

		if (empty($filieresEcole)) {
			echo "<div class='alert alert-warning mt-3'><i class='bi bi-exclamation-triangle'></i>&nbsp; "
			   . "Aucun résultat d'admission n'est disponible pour cette école.</div>";
		}

		echo "</main>";

		$db = null;
?>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		var parametresOnglets = {
			ecole: <?php echo json_encode($ecole, JSON_UNESCAPED_UNICODE); ?>,
			origine: <?php echo json_encode($origine, JSON_UNESCAPED_UNICODE); ?>
		};
		var ecoleCanonique = <?php echo json_encode($ecoleCanonique, JSON_UNESCAPED_UNICODE); ?>;
		var barreFilieres = document.getElementById('filieres-admissions');

		// Les contenus d'onglets sont injectés en AJAX après le chargement initial de
		// la page. Bootstrap ne détecte pas automatiquement les nouveaux attributs
		// data-bs-toggle="tooltip" : on initialise donc les tooltips dans le fragment
		// qui vient d'être ajouté au DOM.
		/** Initialise les tooltips Bootstrap présents dans un fragment injecté. */
		function initialiserTooltips(conteneur) {
			if (!conteneur || typeof bootstrap === 'undefined') { return; }
			conteneur.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
				bootstrap.Tooltip.getOrCreateInstance(element);
			});
		}

		/** Demande le fragment HTML correspondant à un onglet de la fiche. */
		function chargerFragment(onglet) {
			var params = new URLSearchParams(parametresOnglets);
			params.set('onglet', onglet);
			return fetch('php/ajax/detailOnglet.php?' + params.toString()).then(function (reponse) {
				if (!reponse.ok) { throw new Error('Erreur HTTP ' + reponse.status); }
				return reponse.text();
			});
		}

		// bandeau classements/attractivité : chargé indépendamment des onglets
		var badges = document.getElementById('badges-entete');
		if (badges) {
			chargerFragment('entete')
				.then(function (html) {
					badges.innerHTML = html;
					initialiserTooltips(badges);
				})
				.catch(function (erreur) {
					console.error('Classements :', erreur);
					badges.textContent = 'Repères de classement temporairement indisponibles.';
				});
		}

		var barre = document.getElementById('nav-ecole');
		if (!barre || typeof bootstrap === 'undefined') { return; }
		initialiserTooltips(document);

		// Seuls les panneaux portant data-onglet sont des fragments AJAX. Le panneau
		// Spécialités n'en porte pas : il a déjà été rendu par PHP.
		/** Charge une seule fois le contenu du panneau demandé par l'utilisateur. */
		function chargerOnglet(panneau) {
			if (panneau && panneau.id === 'onglet-admissions') {
				chargerOnglet(document.querySelector('#contenu-admissions > .tab-pane.active'));
				return;
			}
			if (!panneau || !panneau.dataset.onglet || panneau.dataset.charge === '1') { return; }
			panneau.dataset.charge = '1';
			chargerFragment(panneau.dataset.onglet)
				.then(function (html) {
					panneau.innerHTML = html;
					initialiserTooltips(panneau);
					if (panneau.dataset.onglet === 'salaire') { chargerDonneesSalaire(panneau); }
				})
				.catch(function (erreur) {
					console.error('Onglet :', erreur);
					delete panneau.dataset.charge;
					panneau.innerHTML = "<div class='alert alert-danger'>Erreur de chargement. Rouvrez l'onglet pour réessayer.</div>";
				});
		}

		if (barreFilieres) {
			barreFilieres.querySelectorAll('button[data-filiere]').forEach(function (bouton) {
				bouton.addEventListener('show.bs.tab', function () {
					chargerOnglet(document.querySelector(bouton.getAttribute('data-bs-target')));
				});
				bouton.addEventListener('shown.bs.tab', function () {
					history.replaceState(null, '', '#onglet-' + bouton.dataset.filiere);
				});
			});
		}
		/** Charge les dépendances puis les données et rend le graphique/tableau Salaire. */
		function chargerDonneesSalaire(panneau) {
			var canvas = panneau.querySelector('#canvas-salaire-detail');
			var tableau = panneau.querySelector('#zone-tableau-salaire-detail');
			var carteReference = panneau.querySelector('#carte-salaire-reference-detail');
			var erreur = panneau.querySelector('#erreur-salaire-detail');
			var params = new URLSearchParams({ ecole: ecoleCanonique });
			/** Ajoute une dépendance JavaScript au premier besoin, sans doublon. */
			function chargerScript(src, id) {
				if (document.getElementById(id)) { return Promise.resolve(); }
				return new Promise(function (resolve, reject) {
					var script = document.createElement('script');
					script.id = id;
					script.src = src;
					script.onload = resolve;
					script.onerror = reject;
					document.head.appendChild(script);
				});
			}
			chargerScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js', 'script-chartjs-salaire')
				.then(function () { return chargerScript('js/salaireEcole.js?v=<?php echo filemtime(__DIR__ . '/../js/salaireEcole.js'); ?>', 'script-salaire-ecole'); })
				.then(function () { return fetch('php/ajax/salaire_ecole.php?' + params.toString()); })
				.then(function (reponse) {
					return reponse.json().then(function (donnees) {
						if (!reponse.ok) { throw new Error(donnees.error || 'Erreur HTTP ' + reponse.status); }
						return donnees;
					});
				})
				.then(function (donnees) {
					if (!Array.isArray(donnees) || donnees.length === 0) {
						erreur.textContent = 'Aucune donnée salariale disponible pour cette école.';
						erreur.classList.remove('d-none');
						return;
					}
					SalaireEcole.afficherCarteSalaireReference(donnees, carteReference);
					SalaireEcole.afficherGraphique(donnees, canvas, {});
					SalaireEcole.construireTableau(donnees, tableau, { id: 'tableau-salaire-detail', titre: ecoleCanonique });
				})
				.catch(function (e) {
					erreur.textContent = 'Erreur lors du chargement des salaires : ' + e.message;
					erreur.classList.remove('d-none');
				});
		}

		barre.querySelectorAll('button[data-bs-toggle="tab"]').forEach(function (bouton) {
			bouton.addEventListener('show.bs.tab', function (evenement) {
				chargerOnglet(document.querySelector(evenement.target.getAttribute('data-bs-target')));
			});
			bouton.addEventListener('shown.bs.tab', function (evenement) {
				var cible = evenement.target.getAttribute('data-bs-target');
				var filiereActive = barreFilieres && barreFilieres.querySelector('button.active');
				if (cible === '#onglet-admissions' && filiereActive) {
					cible = '#onglet-' + filiereActive.dataset.filiere;
				}
				history.replaceState(null, '', cible);
			});
		});
		// Les anciennes ancres #onglet-mp, etc. restent des liens directs valides.
		var ancre = window.location.hash;
		var boutonFiliere = barreFilieres && Array.from(barreFilieres.querySelectorAll('button[data-filiere]')).find(function (bouton) {
			return ancre === '#onglet-' + bouton.dataset.filiere;
		});
		if (boutonFiliere) {
			bootstrap.Tab.getOrCreateInstance(boutonFiliere).show();
			ancre = '#onglet-admissions';
		}
		if (/^#onglet-[a-z0-9\-]+$/.test(ancre)) {
			var bouton = barre.querySelector('[data-bs-target="' + ancre + '"]');
			if (bouton) { bootstrap.Tab.getOrCreateInstance(bouton).show(); }
		}
		chargerOnglet(document.querySelector('#nav-ecoleContent > .tab-pane.active'));
	});

</script>
