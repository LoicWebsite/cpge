<?php
/**
 * Estimation du rang du dernier admis quand le concours ne le publie pas : accès à la table
 * NoteEstimation et fonctions d'affichage communes (fiche école, listes par filière et par école,
 * page de méthode). La table est générée par python/estimation_dernier/calibrer.py ; voir
 * procédure/Guide estimation rang dernier.md.
 *
 * Dépend de formater() et escapeHtml() (php/fonctionConcours.php), à charger avant ce fichier.
 */

// La table NoteEstimation (estimation du rang du dernier admis quand le concours ne
// le publie pas) est générée par python/estimation_dernier/calibrer.py : voir
// procédure/Guide estimation rang dernier.md. Tant qu'elle n'est pas déployée dans la
// base, les pages fonctionnent sans estimation.
function estimationDisponible(PDO $db): bool {
	static $disponible = null;
	if ($disponible === null) {
		try {
			$stmt = $db->query("SHOW TABLES LIKE 'NoteEstimation'");
			$disponible = ($stmt !== false && $stmt->fetchColumn() !== false);
			if ($stmt !== false) {
				$stmt->closeCursor();
			}
		}
		catch (PDOException $erreur) {
			$disponible = false;
		}
	}
	return $disponible;
}

// Page publique qui explique la méthode (minimum, milieu « 3 chances sur 4 », maximum).
const URL_METHODE_ESTIMATION = 'estimation-rang-dernier-integre-ecole-d-ingenieur.php';

// Estimation du rang du dernier admis d'une ligne de Note, uniquement quand le dernier
// n'est pas publié et que la table NoteEstimation en fournit une. Statuts : exact (un seul
// intégré, le dernier est la médiane), plancher (minimum seul), estime (milieu et/ou
// maximum). Retourne null sinon.
function estimationDeLigne(array $ligne): ?array {
	if ((int) ($ligne['Dernier'] ?? 0) > 0) {
		return null;
	}
	if (empty($ligne['EstimationStatut']) || (int) ($ligne['EstimationPlancher'] ?? 0) <= 0) {
		return null;
	}
	return [
		'statut'  => $ligne['EstimationStatut'],
		'plancher' => (int) $ligne['EstimationPlancher'],
		'milieu'  => isset($ligne['EstimationMilieu']) ? (int) $ligne['EstimationMilieu'] : null,
		'plafond' => isset($ligne['EstimationPlafond']) ? (int) $ligne['EstimationPlafond'] : null,
	];
}

// Valeur courte d'une estimation pour une tuile ou une cellule : « ≥ 849 » (minimum garanti),
// ou « 849 » quand le rang est exact.
function valeurEstimation(array $estimation): string {
	$valeur = formater($estimation['plancher'], 0);
	return $estimation['statut'] === 'exact' ? $valeur : "≥ " . $valeur;
}

// Valeur d'une estimation sans séparateur de milliers (comme les rangs de la matrice « par école »),
// avec espace insécable après le signe : « ≥ 849 », ou « 849 » quand le rang est exact.
function valeurEstimationBrute(array $estimation): string {
	return $estimation['statut'] === 'exact' ? (string) $estimation['plancher'] : "≥\u{00A0}" . $estimation['plancher'];
}

// Aide contextuelle (HTML déjà échappé pour un attribut title entre apostrophes).
function aideEstimation(array $estimation): string {
	if ($estimation['statut'] === 'exact') {
		return "Un seul candidat a intégré l&apos;école : le rang du dernier admis est égal au rang médian.";
	}
	$aide = "Rang du dernier admis non publié par ce concours : estimation.<br/>"
		. "&bull; Admission assurée jusqu&apos;au rang " . formater($estimation['plancher'], 0) . " (minimum garanti).";
	if ($estimation['milieu'] !== null) {
		$aide .= "<br/>&bull; Environ 3 chances sur 4 d&apos;être admis jusqu&apos;au rang " . formater($estimation['milieu'], 0) . ".";
	}
	if ($estimation['plafond'] !== null) {
		$aide .= "<br/>&bull; Impossible au-delà du rang " . formater($estimation['plafond'], 0) . " (nombre de classés).";
	}
	return $aide;
}

// Pourcentage de largeur CSS (point décimal quel que soit la locale).
function pourcentageLargeur(float $part, float $total): string {
	return number_format(max(0, min(100, $part / $total * 100)), 2, '.', '');
}

// Thermomètre du rang du dernier admis : zones assurée (jusqu'au minimum), probable (jusqu'au
// milieu, environ 3 chances sur 4) et incertaine (jusqu'au nombre de classés). La légende
// porte le sens, la couleur seule ne suffit jamais. Sans maximum fiable (concours interfilière,
// classés inconnus), seule la légende est affichée : pas de barre pour ne pas suggérer une
// précision que l'on n'a pas.
function afficherThermometreDernier(array $estimation, bool $avecLien = true): void {
	if ($estimation['statut'] === 'exact') {
		return;
	}
	$plancher = $estimation['plancher'];
	$milieu = $estimation['milieu'];
	$plafond = $estimation['plafond'];

	echo "<div class='thermo-dernier mt-3 mb-2'>";
	echo "<div class='thermo-titre'><i class='bi bi-thermometer-half'></i>&nbsp; Rang du dernier admis : estimation"
	   . "&nbsp;<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='" . aideEstimation($estimation) . "'></i></div>";

	if ($plafond !== null && $plafond > 0) {
		$fin = $milieu !== null ? $milieu : $plancher;
		$texteAlternatif = "Admission assurée jusqu'au rang " . formater($plancher, 0)
			. ($milieu !== null ? ", environ 3 chances sur 4 jusqu'au rang " . formater($milieu, 0) : "")
			. " et impossible au-delà du rang " . formater($plafond, 0) . ".";
		echo "<div class='thermo-barre' role='img' aria-label='" . escapeHtml($texteAlternatif) . "'>";
		echo "<span class='thermo-zone thermo-assure' style='width:" . pourcentageLargeur($plancher, $plafond) . "%'></span>";
		if ($milieu !== null) {
			echo "<span class='thermo-zone thermo-probable' style='width:" . pourcentageLargeur($milieu - $plancher, $plafond) . "%'></span>";
		}
		echo "<span class='thermo-zone thermo-incertain' style='width:" . pourcentageLargeur($plafond - $fin, $plafond) . "%'></span>";
		echo "</div>";
		// Échelle : trait + nombre au début, au minimum garanti (frontière de la zone assurée) et au maximum.
		// Les valeurs sont aussi dans la légende et l'alternative textuelle.
		echo "<div class='thermo-echelle' aria-hidden='true' style='--pos:" . pourcentageLargeur($plancher, $plafond) . "%'>"
		   . "<i class='thermo-repere' style='left:0'></i><span class='thermo-debut'>1</span>"
		   . "<i class='thermo-repere' style='left:var(--pos);margin-left:-1px'></i><span class='thermo-minimum'>" . escapeHtml(formater($plancher, 0)) . "</span>"
		   . "<i class='thermo-repere' style='right:0'></i><span class='thermo-fin'>" . escapeHtml(formater($plafond, 0)) . "</span>"
		   . "</div>";
	}

	echo "<ul class='thermo-legende list-unstyled mb-2'>";
	echo "<li><span class='thermo-pastille thermo-assure'></span><span>Admission <strong>assurée</strong> jusqu'au rang <strong>" . escapeHtml(formater($plancher, 0)) . "</strong></span></li>";
	if ($milieu !== null) {
		echo "<li><span class='thermo-pastille thermo-probable'></span><span>Environ <strong>3 chances sur 4</strong> d'être admis jusqu'au rang <strong>" . escapeHtml(formater($milieu, 0)) . "</strong></span></li>";
	}
	if ($plafond !== null && $plafond > 0) {
		echo "<li><span class='thermo-pastille thermo-incertain'></span><span>Ensuite de moins en moins probable. <strong>impossible</strong> au-delà du rang <strong>" . escapeHtml(formater($plafond, 0)) . "</strong> (candidats classés)</span></li>";
	} else {
		echo "<li><span class='thermo-pastille thermo-inconnu'></span><span>Au-delà : non estimé pour ce concours</span></li>";
	}
	echo "</ul>";

	if ($avecLien) {
		// Explication complète sur une page dédiée : ici seule la légende, pour une lecture rapide.
		echo "<p class='thermo-methode mb-0'><i class='bi bi-question-circle'></i>&nbsp; "
		   . "<a href='" . URL_METHODE_ESTIMATION . "' data-methode-estimation>Comment cette estimation est-elle calculée ?</a></p>";
	}
	echo "</div>";
}
