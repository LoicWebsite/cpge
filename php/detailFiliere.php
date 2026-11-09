<?php

require_once __DIR__ . '/detailEntete.php';
require_once __DIR__ . '/estimation.php';

// Libellés EcoleConcours à rechercher dans Note pour une école : par défaut, ou
// depuis une ligne École/Filière, seul le libellé exact compte (ne pas agréger
// les autres formations de l'école canonique). Les pages qui veulent une fiche
// canonique élargie (spécialité, classement, attractivité, salaire) le demandent
// avec leur origine explicite. L'origine 'ecole' (page statistiques par école)
// agrège aussi : son select envoie le nom canonique (EcoleConcours.Ecole), qui
// n'existe pas tel quel dans Note (ex. "AgroParisTech" vs "AgroParisTech - Palaiseau").
// Un simple "OR ... IN (sous-requête)" empêche MySQL d'utiliser l'index sur
// Note.EcoleConcours (confirmé par EXPLAIN le 2026-09-01 : scan complet de la
// table). Résoudre la liste ici, puis filtrer Note avec un simple IN (...),
// permet un vrai lookup indexé.
function obtenirEcolesConcoursRecherchees(PDO $db, string $ecole, string $origine): array {
	if (!in_array($origine, ['specialite', 'classement', 'attractivite', 'salaire', 'ecole'], true)) {
		return [$ecole];
	}
	$stmt = $db->prepare("SELECT EcoleConcours FROM EcoleConcours WHERE Ecole = :ecole");
	$stmt->execute([':ecole' => $ecole]);
	$valeurs = $stmt->fetchAll(PDO::FETCH_COLUMN);
	$stmt->closeCursor();
	$valeurs[] = $ecole; // filet de sécurité si cet EcoleConcours n'a pas d'Ecole canonique renseignée
	return array_values(array_unique($valeurs));
}

// Condition WHERE + paramètres liés pour filtrer Note par une liste d'EcoleConcours.
// $alias qualifie la colonne quand la requête joint une autre table qui porte aussi EcoleConcours.
function filtreEcolesConcoursNote(array $ecolesConcours, string $alias = ''): string {
	$placeholders = [];
	foreach (array_keys($ecolesConcours) as $index) {
		$placeholders[] = ':ecoleConcours' . $index;
	}
	return "AND " . $alias . "EcoleConcours IN (" . implode(', ', $placeholders) . ")";
}

function parametresFiltreEcolesConcoursNote(array $ecolesConcours): array {
	$params = [];
	foreach (array_values($ecolesConcours) as $index => $valeur) {
		$params[':ecoleConcours' . $index] = $valeur;
	}
	return $params;
}

// Liste (légère, 1 requête) des filières où l'école a des résultats, pour construire
// la barre d'onglets sans charger le détail (Place/Inscrit/Rangs...) de chacune.
function obtenirFilieresEcole(PDO $db, array $ecolesConcours, bool $debug = false): array {
	$filieresEcole = [];
	try {
		$sql = "SELECT DISTINCT Filiere FROM Note
				WHERE An <> '' AND An <> 0
				" . filtreEcolesConcoursNote($ecolesConcours);
		if ($debug) {
			echo "SQL filieres = " . escapeHtml($sql) . "<br/>";
		}
		$stmt = $db->prepare($sql);
		$stmt->execute(parametresFiltreEcolesConcoursNote($ecolesConcours));
		$trouvees = $stmt->fetchAll(PDO::FETCH_COLUMN);
		$stmt->closeCursor();

		foreach (ordreFilieres() as $cle) {
			if (in_array($cle, $trouvees, true)) {
				$filieresEcole[] = $cle;
			}
		}
		foreach ($trouvees as $cle) {
			if (!in_array($cle, $filieresEcole, true)) {
				$filieresEcole[] = $cle;
			}
		}
	}
	catch (PDOException $erreur) {
		echo "<div class='alert alert-danger'>Erreur SELECT Note (filières) : " . escapeHtml($erreur->getMessage()) . "</div>";
	}
	return $filieresEcole;
}

// Résultats d'admission d'une seule filière : une seule requête isolée charge
// l'onglet demandé, puis le PHP regroupe en mémoire par libellé exact
// EcoleConcours, puis par concours. Ce regroupement évite que les fiches
// élargies affichent plusieurs voies d'une même école comme de fausses lignes
// dupliquées, sans ajouter de requête SQL par variante.
// Quand la table NoteEstimation existe, la même requête la joint sur la clé de Note
// (colonnes Estimation*), sans requête supplémentaire.
function chargerNotesFiliere(PDO $db, array $ecolesConcours, string $filiere, bool $debug = false): array {
	$parFormation = [];
	try {
		$colonnesEstimation = '';
		$jointureEstimation = '';
		if (estimationDisponible($db)) {
			$colonnesEstimation = ", e.Statut AS EstimationStatut, e.Plancher AS EstimationPlancher,
					e.Milieu AS EstimationMilieu, e.Plafond AS EstimationPlafond";
			$jointureEstimation = "LEFT JOIN NoteEstimation e
					ON e.Filiere = n.Filiere AND e.An = n.An AND e.Concours = n.Concours AND e.EcoleConcours = n.EcoleConcours";
		}
		$sql = "SELECT n.EcoleConcours, n.Filiere, n.Concours, n.An, n.Place, n.Inscrit, n.Classe, n.Integre,
					n.RangMedian, n.RangMoyen, n.Dernier" . $colonnesEstimation . "
				FROM Note n
				" . $jointureEstimation . "
				WHERE n.An <> '' AND n.An <> 0 AND n.Filiere = :filiere
				" . filtreEcolesConcoursNote($ecolesConcours, 'n.') . "
				ORDER BY n.EcoleConcours ASC, n.Concours ASC, n.An DESC";
		if ($debug) {
			echo "SQL = " . escapeHtml($sql) . "<br/>";
		}
		$stmt = $db->prepare($sql);
		$params = parametresFiltreEcolesConcoursNote($ecolesConcours);
		$params[':filiere'] = $filiere;
		$stmt->execute($params);
		while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$parFormation[$row['EcoleConcours']][$row['Concours']][] = $row;
		}
		$stmt->closeCursor();
	}
	catch (PDOException $erreur) {
		echo "<div class='alert alert-danger'>Erreur SELECT Note : " . escapeHtml($erreur->getMessage()) . "</div>";
	}
	return $parFormation;
}

// Sélectivités calculées à partir d'une ligne de la table Note.
function calculerSelectivites(array $ligne): array {
	$selectivite = '';
	$selectiviteMediane = '';
	$inscrit = $ligne['Inscrit'];
	if ($inscrit <> 0) {
		if (($ligne['Dernier'] <> 0) && ($ligne['Dernier'] <> '')) {
			$selectivite = ($ligne['Dernier'] / $inscrit) * 100;
		}
		if (($ligne['RangMedian'] <> 0) && ($ligne['RangMedian'] <> '')) {
			$selectiviteMediane = ($ligne['RangMedian'] / $inscrit) * 100;
		}
	}
	return ['selectivite' => $selectivite, 'mediane' => $selectiviteMediane];
}

// Ligne de référence d'une voie : année la plus récente disponible, puis plus
// grand nombre de places si plusieurs concours coexistent la même année. Ce
// repère sert uniquement à ordonner les blocs déjà chargés en mémoire et à
// expliquer leur priorité visuelle ; il n'ajoute aucune requête SQL.
function ligneReferenceFormation(array $parConcours): array {
	$reference = null;
	foreach ($parConcours as $lignes) {
		foreach ($lignes as $ligne) {
			if ($reference === null
				|| (int) $ligne['An'] > (int) $reference['An']
				|| ((int) $ligne['An'] === (int) $reference['An'] && (int) $ligne['Place'] > (int) $reference['Place'])) {
				$reference = $ligne;
			}
		}
	}
	return $reference === null ? [] : $reference;
}

// Les fiches élargies placent d'abord les voies les plus représentatives pour
// l'utilisateur : données les plus récentes, puis volume de places décroissant.
// Le tri est fait après l'unique SELECT de l'onglet, donc sans impact notable
// sur la charge base.
function trierFormationsParImportanceRecente(array &$parFormations): void {
	uasort($parFormations, function ($a, $b) {
		$referenceA = ligneReferenceFormation($a);
		$referenceB = ligneReferenceFormation($b);
		$anneeA = (int) ($referenceA['An'] ?? 0);
		$anneeB = (int) ($referenceB['An'] ?? 0);
		if ($anneeA !== $anneeB) {
			return $anneeB <=> $anneeA;
		}
		return (int) ($referenceB['Place'] ?? 0) <=> (int) ($referenceA['Place'] ?? 0);
	});
}

// Titre de l'onglet filière : le libellé long (« Mathématiques - Physique »)
// suivi du sigle entre parenthèses. Le sigle seul (« MP ») est déjà porté par
// le bouton actif du sélecteur, et « Admissions » par l'onglet : les répéter
// dans le titre serait redondant. En fiche élargie, un tooltip discret explique
// le regroupement des voies.
function afficherTitreFiliere(string $ecole, string $filiere, string $aide = ''): void {
	$libelles = libellesFilieres();
	$libelle = isset($libelles[$filiere]) ? $libelles[$filiere] : '';
	$section = $libelle !== ''
		? $libelle . " (" . strtoupper($filiere) . ")"
		: strtoupper($filiere);
	if ($aide === '') {
		afficherTitreSection($ecole, $section);
		// Espace vertical avant le premier concours (le titre partagé a mb-1).
		echo "<div class='mb-3'></div>";
		return;
	}
	echo "<h2 class='h5 text-center mb-1'>";
	echo escapeHtml($section);
	echo "&nbsp;<i class='bi bi-info-circle-fill text-secondary' data-bs-toggle='tooltip' data-bs-html='true' title='" . escapeHtml($aide) . "'></i>";
	echo "</h2>";
	echo "<div class='mb-3'></div>";
}

// Une tuile "chiffre clé".
function afficherTuile(string $libelle, string $valeur, string $icone, string $aide = '', string $classeValeur = 'text-primary'): void {
	echo "<div class='col-6 col-md-4 col-xl'>";
	echo "<div class='p-3 border bg-white rounded h-100 text-center'>";
	echo "<div class='text-secondary small'><i class='bi " . $icone . "'></i>&nbsp; " . $libelle;
	if ($aide !== '') {
		echo "&nbsp;<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='" . $aide . "'></i>";
	}
	echo "</div>";
	echo "<div class='h4 mb-0 mt-1 " . $classeValeur . "'>" . ($valeur === '' ? "&mdash;" : $valeur) . "</div>";
	echo "</div>";
	echo "</div>";
}

// Tableau d'historique : $colonnes = [libellé => [clé, décimales, suffixe, aide]]
function afficherTableauHistorique(string $titre, array $lignes, array $colonnes): void {
	echo "<h4 class='h6 text-secondary mt-4 mb-2'>" . $titre . "</h4>";
	echo "<div class='table-responsive'>";
	echo "<table class='table-filiere'>";
	echo "<thead><tr><th scope='col'>&nbsp;Année&nbsp;</th>";
	foreach ($colonnes as $entete => $definition) {
		echo "<th scope='col'>&nbsp;" . $entete . "&nbsp;";
		if (isset($definition[3]) && $definition[3] !== '') {
			echo "<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='" . $definition[3] . "'></i>";
		}
		echo "</th>";
	}
	echo "</tr></thead><tbody>";

	foreach ($lignes as $ligne) {
		$selectivites = calculerSelectivites($ligne);
		echo "<tr>";
		echo "<td style='text-align:center'><strong>" . escapeHtml($ligne['An']) . "</strong></td>";
		foreach ($colonnes as $definition) {
			$cle = $definition[0];
			if ($cle === 'Selectivite') {
				$valeur = $selectivites['selectivite'];
			} elseif ($cle === 'SelectiviteMediane') {
				$valeur = $selectivites['mediane'];
			} else {
				$valeur = isset($ligne[$cle]) ? $ligne[$cle] : '';
			}
			// un rang à zéro signifie une donnée non publiée par le concours
			if (in_array($cle, ['RangMedian', 'RangMoyen', 'Dernier'], true) && $valeur == 0) {
				$valeur = '';
			}
			$texte = formater($valeur, $definition[1]);
			if ($texte !== '') {
				$texte .= $definition[2];
			}
			// dernier non publié : l'estimation (minimum garanti), en gris italique, remplace le tiret
			$estimation = ($cle === 'Dernier' && $texte === '') ? estimationDeLigne($ligne) : null;
			if ($estimation !== null) {
				echo "<td style='text-align:center'><span class='estimation-valeur' data-bs-toggle='tooltip' data-bs-html='true' title='"
				   . aideEstimation($estimation) . "'>" . escapeHtml(valeurEstimation($estimation)) . "</span></td>";
				continue;
			}
			echo "<td style='text-align:center'>" . ($texte === '' ? "&mdash;" : escapeHtml($texte)) . "</td>";
		}
		echo "</tr>";
	}

	echo "</tbody></table>";
	echo "</div>";
}

// Affiche le contenu d'un onglet filière : dans une fiche exacte, le rendu reste
// centré sur les concours. Dans une fiche élargie, chaque libellé EcoleConcours
// a son propre bloc pour séparer les voies/diplômes rattachés à la même école.
function afficherFiliere(string $filiere, array $parFormations, string $ecole = ''): void {
	$libelles = libellesFilieres();
	$libelle = isset($libelles[$filiere]) ? $libelles[$filiere] : '';
	$nombreFormations = count($parFormations);
	$aideFicheElargie = '';
	if ($nombreFormations > 1) {
		// En fiche élargie, le paragraphe d'explication prenait trop de place avant
		// les premiers résultats. On le garde donc en aide contextuelle sur le titre.
		$aideFicheElargie = "Cette fiche regroupe plusieurs voies rattachées à " . $ecole . ". "
			. "Les statistiques sont présentées séparément pour éviter de mélanger les admissions. "
			. "Les voies sont classées par année récente puis par nombre de places décroissant.";
	}

	afficherTitreFiliere($ecole, $filiere, $aideFicheElargie);

	if ($nombreFormations > 1) {
		trierFormationsParImportanceRecente($parFormations);
		// Accordéon non exclusif : le premier bloc est ouvert par défaut pour montrer
		// directement la voie la plus représentative, mais l'utilisateur peut ouvrir
		// plusieurs autres voies en parallèle pour comparer.
		echo "<div class='accordion' id='accordeon-formations-" . escapeHtml($filiere) . "'>";
	}

	$premiereFormation = true;
	$indexFormation = 0;
	foreach ($parFormations as $ecoleConcours => $parConcours) {
		if (!$premiereFormation && $nombreFormations === 1) {
			echo "<hr class='my-4'>";
		}
		$indexFormation++;
		$formationOuverte = ($indexFormation === 1);
		$idFormation = "formation-" . escapeHtml($filiere) . "-" . $indexFormation;

		if ($nombreFormations > 1) {
			// Le résumé reste visible même quand le bloc est replié : il permet de
			// comprendre l'ordre des voies et de choisir quoi ouvrir sans tâtonner.
			$referenceFormation = ligneReferenceFormation($parConcours);
			$resumeFormation = '';
			if (!empty($referenceFormation)) {
				$libellePlaces = ((int) $referenceFormation['Place'] > 1) ? " places" : " place";
				$resumeFormation = escapeHtml($referenceFormation['An']) . " · " . escapeHtml(formater($referenceFormation['Place'], 0)) . $libellePlaces;
				if (!empty($referenceFormation['Concours'])) {
					$resumeFormation .= " · " . escapeHtml($referenceFormation['Concours']);
				}
			}
			echo "<section class='accordion-item'>";
			echo "<h3 class='accordion-header'>";
			echo "<button class='accordion-button" . ($formationOuverte ? "" : " collapsed") . "' type='button' data-bs-toggle='collapse' data-bs-target='#" . $idFormation . "' aria-expanded='" . ($formationOuverte ? "true" : "false") . "' aria-controls='" . $idFormation . "'>";
			echo "<span><span class='fw-bold'>" . escapeHtml($ecoleConcours) . "</span>";
			if ($resumeFormation !== '') {
				echo "<br><span class='text-secondary small fw-normal'>" . $resumeFormation . "</span>";
			}
			echo "</span>";
			echo "</button></h3>";
			echo "<div id='" . $idFormation . "' class='accordion-collapse collapse" . ($formationOuverte ? " show" : "") . "'>";
			echo "<div class='accordion-body'>";
		}
		$premiereFormation = false;

		// concours les plus récents en premier pour chaque voie.
		uasort($parConcours, function ($a, $b) {
			return strcmp($b[0]['An'], $a[0]['An']);
		});

		// Plusieurs concours dans une même voie traduisent souvent un changement de
		// banque d'épreuves au fil des ans ; on garde alors un sous-bloc par concours.
		if (count($parConcours) > 1) {
			// Note secondaire : même gris clair que la voie d'admission (fiche-contexte).
			echo "<p class='text-center small mb-4'><i class='bi bi-info-circle'></i>&nbsp; "
			   . "<span class='fiche-contexte'>Cette école a recruté en "
			   . escapeHtml(strtoupper($filiere)) . " via " . count($parConcours) . " concours successifs. "
			   . "Chaque bloc ci-dessous correspond à l'un d'eux, du plus récent au plus ancien.</span></p>";
		}

		$premierConcours = true;
		foreach ($parConcours as $concours => $lignes) {
			if (!$premierConcours) {
				echo "<hr class='my-4'>";
			}
			$premierConcours = false;

			$annees = array_column($lignes, 'An');
			$anneeMax = max($annees);
			$anneeMin = min($annees);
			$derniere = $lignes[0];
			$selectivites = calculerSelectivites($derniere);
			// Ligne de contexte : concours + année de référence des chiffres clés.
			// La période complète n'est pas rappelée : le tableau « Effectifs par
			// année » ci-dessous liste déjà chaque année couverte.
			echo "<div class='d-flex flex-wrap align-items-baseline justify-content-center gap-2 mb-3'>";
			echo "<h4 class='h6 fw-bold mb-0 text-secondary'><i class='bi bi-mortarboard-fill'></i>&nbsp; " . escapeHtml($concours) . "</h4>";
			echo "<span class='text-secondary small'>données " . escapeHtml($anneeMax) . "</span>";
			echo "</div>";

			echo "<div class='row g-2 g-md-3 mb-2'>";
			afficherTuile("Places", formater($derniere['Place'], 0), "bi-door-open");
			afficherTuile("Inscrits", formater($derniere['Inscrit'], 0), "bi-people-fill");
			afficherTuile("Intégrés", formater($derniere['Integre'], 0), "bi-check-circle-fill");
			// Rang médian toujours affiché (publié par le SCEI). Quand le dernier admis est publié :
			// rang médian, dernier admis, sélectivité (dernier / inscrits). Sinon : rang médian,
			// sélectivité médiane, puis le dernier admis estimé, du plus sûr au plus incertain.
			$estimationDerniere = estimationDeLigne($derniere);
			$dernierPublie = ($derniere['Dernier'] <> 0 && $derniere['Dernier'] <> '');
			afficherTuile(
				"Rang médian",
				($derniere['RangMedian'] <> 0 && $derniere['RangMedian'] <> '') ? formater($derniere['RangMedian'], 0) : '',
				"bi-sort-numeric-down",
				"Rang médian des candidats admis."
			);
			if ($dernierPublie) {
				afficherTuile(
					"Dernier admis",
					formater($derniere['Dernier'], 0),
					"bi-flag-fill",
					"Rang du dernier candidat admis dans l&apos;école."
				);
				afficherTuile(
					"Sélectivité",
					$selectivites['selectivite'] === '' ? '' : formater($selectivites['selectivite'], 1) . "%",
					"bi-funnel-fill",
					"La sélectivité est le rapport rang du dernier admis divisé par le nombre d&apos;inscrits."
				);
			} else {
				afficherTuile(
					"Sélectivité méd.",
					$selectivites['mediane'] === '' ? '' : formater($selectivites['mediane'], 1) . "%",
					"bi-funnel-fill",
					"La sélectivité médiane est le rapport rang médian divisé par le nombre d&apos;inscrits."
				);
				if ($estimationDerniere !== null) {
					afficherTuile(
						"Dernier admis (estimé)",
						escapeHtml(valeurEstimation($estimationDerniere)),
						"bi-flag-fill",
						aideEstimation($estimationDerniere),
						"text-secondary fst-italic"
					);
				} else {
					afficherTuile(
						"Dernier admis",
						'',
						"bi-flag-fill",
						"Rang du dernier candidat admis : non publié par ce concours."
					);
				}
			}
			echo "</div>";

			if ($estimationDerniere !== null) {
				afficherThermometreDernier($estimationDerniere);
			}

			afficherTableauHistorique(
				"<i class='bi bi-people'></i>&nbsp; Effectifs par année",
				$lignes,
				[
					"Places"   => ['Place', 0, '', ''],
					"Inscrits" => ['Inscrit', 0, '', "Nombre de candidats inscrits au concours pour cette école."],
					"Classés"  => ['Classe', 0, '', "Nombre de candidats classés par l&apos;école à l&apos;issue des épreuves."],
					"Intégrés" => ['Integre', 0, '', "Nombre de candidats ayant effectivement intégré l&apos;école."],
				]
			);

			afficherTableauHistorique(
				"<i class='bi bi-bar-chart-line'></i>&nbsp; Rangs et sélectivité par année",
				$lignes,
				[
					"Rang médian"      => ['RangMedian', 0, '', "Rang médian des candidats admis."],
					"Rang moyen"       => ['RangMoyen', 0, '', "Rang moyen des candidats admis."],
					"Dernier admis"    => ['Dernier', 0, '', "Rang du dernier candidat admis.<br/>Quand le concours ne le publie pas, il est estimé : la valeur est alors affichée en <i>italique gris</i> (minimum garanti, précédé de &ge;)."],
					"Sélectivité méd." => ['SelectiviteMediane', 1, '%', "La sélectivité médiane est le rapport rang médian divisé par le nombre d&apos;inscrits."],
					"Sélectivité"      => ['Selectivite', 1, '%', "La sélectivité est le rapport rang du dernier admis divisé par le nombre d&apos;inscrits.<br/>Quand le rang du dernier admis est estimé (car non publié), la sélectivité n&apos;est pas calculée."],
				]
			);
		}

		if ($nombreFormations > 1) {
			echo "</div></div></section>";
		}
	}
	if ($nombreFormations > 1) {
		echo "</div>";
	}
}
