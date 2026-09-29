<?php
/**
 * Orchestrateur de la fiche école.
 * Appelé par detail-resultat-admission-par-ecole.php, il prépare les onglets,
 * rend les spécialités disponibles immédiatement et délègue les contenus
 * lourds aux endpoints AJAX au premier clic. Les tests Salaire/Spécialités
 * servent à ne pas afficher d'onglet sans donnée exploitable.
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
		$db = openDatabase();
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
		$stmtCanonique = $db->prepare("SELECT Ecole FROM EcoleConcours WHERE EcoleConcours = :ecole AND Ecole IS NOT NULL LIMIT 1");
		$stmtCanonique->execute([':ecole' => $ecoleFiltre]);
		$ecoleCanonique = $stmtCanonique->fetchColumn() ?: $ecoleFiltre;

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

		// Spécialités est rendu immédiatement côté serveur : son contenu ne doit
		// jamais porter data-onglet, sinon le gestionnaire AJAX le remplacerait.
		// Salaire, Classements et les filières sont des fragments AJAX différés.
		afficherEnteteEcole($ecoleFiltre, $urlEcole, $filieresEcole);

		echo "<main class='container' style='margin-top:24px;'>";

		// barre d'onglets (défilement horizontal sur petit écran)
		echo "<nav class='onglets-ecole'>";
		echo "<div class='nav nav-tabs' id='nav-ecole' role='tablist'>";

		$idClassements = identifiantOnglet('classements');
		echo "<button class='nav-link" . ($ongletActif === 'classements' ? ' active' : '') . "' id='tab-" . $idClassements . "'"
		   . " data-bs-toggle='tab' data-bs-target='#" . $idClassements . "' data-onglet='classements' type='button' role='tab'"
		   . " aria-controls='" . $idClassements . "' aria-selected='" . ($ongletActif === 'classements' ? 'true' : 'false') . "'>"
		   . "<i class='bi bi-trophy'></i>&nbsp; Classements</button>";

		foreach ($filieresEcole as $cle) {
			$id = identifiantOnglet($cle);
			echo "<button class='nav-link' id='tab-" . $id . "'"
			   . " data-bs-toggle='tab' data-bs-target='#" . $id . "' data-onglet='" . escapeHtml($cle) . "' type='button' role='tab'"
			   . " aria-controls='" . $id . "' aria-selected='false'>"
			   . escapeHtml(strtoupper($cle)) . "</button>";
		}

		$idSpecialites = identifiantOnglet('specialites');
		$idSalaire = identifiantOnglet('salaire');
		if ($hasSalaires) {
			echo "<button class='nav-link" . ($ongletActif === 'salaire' ? ' active' : '') . "' id='tab-" . $idSalaire . "'"
			   . " data-bs-toggle='tab' data-bs-target='#" . $idSalaire . "' data-onglet='salaire' type='button' role='tab'"
			   . " aria-controls='" . $idSalaire . "' aria-selected='" . ($ongletActif === 'salaire' ? 'true' : 'false') . "'>"
			   . "<i class='bi bi-cash-stack'></i>&nbsp; Salaire</button>";
		}
		if ($hasSpecialites) {
			echo "<button class='nav-link" . ($ongletActif === 'specialites' ? ' active' : '') . "' id='tab-" . $idSpecialites . "'"
		   . " data-bs-toggle='tab' data-bs-target='#" . $idSpecialites . "' type='button' role='tab'"
		   . " aria-controls='" . $idSpecialites . "' aria-selected='" . ($ongletActif === 'specialites' ? 'true' : 'false') . "'>"
		   . "<i class='bi bi-diagram-3'></i>&nbsp; Spécialités</button>";
		}

		echo "</div>";
		echo "</nav>";

		// Contenu : Spécialités est déjà rempli côté serveur ; Salaire, Classements
		// et chaque filière démarrent avec un spinner et sont remplis par
		// ajax/detailOnglet.php au premier affichage.
		echo "<div class='tab-content p-3 p-md-4 border border-top-0 bg-light' id='nav-ecoleContent'>";

		echo "<div class='tab-pane fade" . ($ongletActif === 'classements' ? ' show active' : '') . "' id='" . $idClassements . "' role='tabpanel' aria-labelledby='tab-" . $idClassements . "' data-onglet='classements'>";
		echo "<div class='text-center text-secondary py-4'><span class='spinner-border spinner-border-sm'></span>&nbsp; Chargement...</div>";
		echo "</div>";

		foreach ($filieresEcole as $cle) {
			$id = identifiantOnglet($cle);
			echo "<div class='tab-pane fade' id='" . $id . "' role='tabpanel' aria-labelledby='tab-" . $id . "' data-onglet='" . escapeHtml($cle) . "'>";
			echo "<div class='text-center text-secondary py-4'><span class='spinner-border spinner-border-sm'></span>&nbsp; Chargement...</div>";
			echo "</div>";
		}

		if ($hasSalaires) {
			echo "<div class='tab-pane fade" . ($ongletActif === 'salaire' ? ' show active' : '') . "' id='" . $idSalaire . "' role='tabpanel' aria-labelledby='tab-" . $idSalaire . "' data-onglet='salaire'>";
			echo "<div class='text-center text-secondary py-4'><span class='spinner-border spinner-border-sm'></span>&nbsp; Chargement...</div>";
			echo "</div>";
		}

		if ($hasSpecialites) {
			// Spécialités est déjà rendu côté serveur : il ne doit pas passer par l'AJAX.
			echo "<div class='tab-pane fade" . ($ongletActif === 'specialites' ? ' show active' : '') . "' id='" . $idSpecialites . "' role='tabpanel' aria-labelledby='tab-" . $idSpecialites . "'>";
			afficherSpecialites($db, $ecoleFiltre, $debug);
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
		var ongletInitial = <?php echo json_encode($filiere, JSON_UNESCAPED_UNICODE); ?>;
		var ongletActif = <?php echo json_encode($ongletActif, JSON_UNESCAPED_UNICODE); ?>;

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
			return fetch('php/ajax/detailOnglet.php?' + params.toString()).then(function (reponse) { return reponse.text(); });
		}

		// bandeau classements/attractivité : chargé indépendamment des onglets
		var badges = document.getElementById('badges-entete');
		if (badges) {
			chargerFragment('entete')
				.then(function (html) {
					badges.innerHTML = html;
					initialiserTooltips(badges);
				})
				.catch(function () { badges.innerHTML = ''; });
		}

		var barre = document.getElementById('nav-ecole');
		if (!barre || typeof bootstrap === 'undefined') { return; }
		initialiserTooltips(document);

		// Seuls les panneaux portant data-onglet sont des fragments AJAX. Le panneau
		// Spécialités n'en porte pas : il a déjà été rendu par PHP.
		/** Charge une seule fois le contenu du panneau demandé par l'utilisateur. */
		function chargerOnglet(panneau) {
			if (!panneau || !panneau.dataset.onglet || panneau.dataset.charge === '1') { return; }
			panneau.dataset.charge = '1';
			chargerFragment(panneau.dataset.onglet)
				.then(function (html) {
					panneau.innerHTML = html;
					initialiserTooltips(panneau);
					if (panneau.dataset.onglet === 'salaire') { chargerDonneesSalaire(panneau); }
				})
				.catch(function () { panneau.innerHTML = "<div class='alert alert-danger'>Erreur de chargement.</div>"; });
		}

		/** Charge les dépendances puis les données et rend le graphique/tableau Salaire. */
		function chargerDonneesSalaire(panneau) {
			var canvas = panneau.querySelector('#canvas-salaire-detail');
			var tableau = panneau.querySelector('#zone-tableau-salaire-detail');
			var erreur = panneau.querySelector('#erreur-salaire-detail');
			var params = new URLSearchParams({ ecole: parametresOnglets.ecole });
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
				.then(function () { return chargerScript('js/salaireEcole.js', 'script-salaire-ecole'); })
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
					SalaireEcole.afficherGraphique(donnees, canvas, {});
					SalaireEcole.construireTableau(donnees, tableau, { id: 'tableau-salaire-detail', titre: parametresOnglets.ecole });
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
				history.replaceState(null, '', evenement.target.getAttribute('data-bs-target'));
			});
		});
		if (ongletActif === 'salaire' || ongletActif === 'classements') {
			chargerOnglet(document.getElementById('onglet-' + ongletActif));
		}

		// ouverture directe d'un onglet via l'ancre de l'URL, ou via le paramètre
		// filiere (lien provenant d'une autre page) : l'affichage initial reste sur
		// Spécialités, seul le contenu de l'onglet ciblé est chargé après coup.
		var ancre = window.location.hash;
		if (/^#onglet-[a-z0-9\-]+$/.test(ancre)) {
			var bouton = barre.querySelector('[data-bs-target="' + ancre + '"]');
			if (bouton) { bootstrap.Tab.getOrCreateInstance(bouton).show(); }
		}
		else if (ongletInitial) {
			var boutonFiliere = document.getElementById('tab-onglet-' + ongletInitial);
			if (boutonFiliere) { bootstrap.Tab.getOrCreateInstance(boutonFiliere).show(); }
		}
	});

	// le retour à la liste n'a de sens que si l'on arrive d'une page de résultats
	document.addEventListener('DOMContentLoaded', function () {
		var venantDUneListe = /\/resultat-d-integration-ecole-d-ingenieur-par-(ecole|filiere)-cpge-post-prepa\.php/.test(document.referrer);
		if (!venantDUneListe) { return; }
		['retourListe', 'filStatistiques'].forEach(function (id) {
			var element = document.getElementById(id);
			if (element) { element.hidden = false; }
		});
	});
</script>
