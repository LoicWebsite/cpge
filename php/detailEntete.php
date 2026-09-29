<?php

require_once __DIR__ . '/detailClassement.php';

// Libellés longs des filières CPGE.
function libellesFilieres(): array {
	return [
		'mp'    => 'Mathématiques - Physique',
		'mpi'   => 'Mathématiques, Physique, Informatique',
		'pc'    => 'Physique - Chimie',
		'psi'   => 'Physique et Sciences de l\'Ingénieur',
		'pt'    => 'Physique - Technologie',
		'tsi'   => 'Technologie et Sciences Industrielles',
		'tpc'   => 'Technologie, Physique et Chimie',
		'bcpst' => 'Biologie, Chimie, Physique et Sciences de la Terre',
		'tb'    => 'Technologie et Biologie',
	];
}

// Ordre d'affichage habituel des filières sur le site.
function ordreFilieres(): array {
	return ['mp', 'mpi', 'pc', 'psi', 'pt', 'tsi', 'tpc', 'bcpst', 'tb'];
}

// Identifiant d'ancre / d'onglet sûr pour le HTML.
function identifiantOnglet(string $cle): string {
	return 'onglet-' . preg_replace('/[^a-z0-9\-]/', '', strtolower($cle));
}

// Titre d'un onglet, qui rappelle le nom de l'école restée en haut de page.
function afficherTitreSection(string $ecole, string $section): void {
	echo "<h2 class='h5 text-center mb-1'>";
	if ($ecole !== '') {
		echo "<span class='text-secondary'>" . escapeHtml($ecole) . "</span><span class='text-secondary'> &middot; </span>";
	}
	echo escapeHtml($section);
	echo "</h2>";
}

// Cherche le meilleur rang d'attractivité parmi les formations de l'école.
function chargerAttractivite(PDO $db, string $ecole): array {
	$noms = [$ecole];
	$stmt = $db->prepare("SELECT Ecole FROM EcoleConcours WHERE EcoleConcours = :ecole AND Ecole IS NOT NULL LIMIT 1");
	$stmt->execute([':ecole' => $ecole]);
	$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	if ($ligne && $ligne['Ecole'] !== '') {
		$noms[] = $ligne['Ecole'];
	}

	$conditions = [];
	$params = [];
	foreach (array_values(array_unique($noms)) as $index => $nom) {
		$param = ':nom' . $index;
		$conditions[] = "(a.Formation = $param OR a.Formation LIKE CONCAT($param, ' (%)'))";
		$params[$param] = $nom;
	}
	$sql = "SELECT MIN(a.Rang) AS Rang, (SELECT COUNT(*) FROM Attractivite WHERE Rang IS NOT NULL) AS Total
			FROM Attractivite a
			WHERE a.Rang IS NOT NULL AND (" . implode(' OR ', $conditions) . ')';
	$stmt = $db->prepare($sql);
	$stmt->execute($params);
	$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();

	return ($ligne && $ligne['Rang'] !== null)
		? ['rang' => (int) $ligne['Rang'], 'total' => (int) $ligne['Total']]
		: ['rang' => '', 'total' => 0];
}

// Affiche l'en-tête de la page : nom de l'école, site web et repères de classement.
// Bandeau statique : ne dépend que de l'URL officielle (1 requête légère) et de la
// liste des filières déjà connue. Les classements/attractivité (plus coûteux, ~9
// requêtes) sont chargés en arrière-plan par AJAX et injectés dans #badges-entete.
function afficherEnteteEcole(string $ecole, string $urlEcole, array $filieres): void {
	$libelles = libellesFilieres();

	echo "<header class='container'>";
	echo "<div class='p-3 p-md-4 border bg-light rounded'>";

	echo "<h1 class='h3 mb-3 text-center'><i class='bi bi-bank2'></i>&nbsp;&nbsp;" . escapeHtml($ecole) . "</h1>";

	echo "<p class='text-center mb-3'><span class='text-secondary'>Admissions, classements et spécialités de l'école d'ingénieurs</span></p>";

	if ($urlEcole !== '') {
		echo "<p class='text-center mb-3'>";
		echo "<a class='btn btn-outline-primary btn-sm' href='" . escapeHtml($urlEcole) . "' target='_blank' rel='noopener'>";
		echo "<i class='bi bi-box-arrow-up-right'></i>&nbsp; Site officiel de l'école</a>";
		echo "</p>";
	}

	// rempli après coup par ajax/detailOnglet.php?onglet=entete (voir script en bas de detail.php)
	echo "<p class='text-center mb-3' id='badges-entete'><span class='spinner-border spinner-border-sm text-secondary' role='status' aria-label='Chargement des classements'></span></p>";

	if (!empty($filieres)) {
		echo "<p class='text-center mb-0 small text-secondary'>Accessible depuis ";
		echo count($filieres) > 1 ? "les filières " : "la filière ";
		$noms = [];
		foreach ($filieres as $cle) {
			$noms[] = "<span data-bs-toggle='tooltip' title='" . escapeHtml(isset($libelles[$cle]) ? $libelles[$cle] : $cle) . "'>" . escapeHtml(strtoupper($cle)) . "</span>";
		}
		echo implode(', ', $noms);
		echo "</p>";
	}

	echo "</div>";
	echo "</header>";
}

// Fragment des badges de classement/attractivité, calculé une fois les données
// disponibles (chargement direct ou via AJAX) et injecté dans #badges-entete.
function afficherBadgesEntete(array $classements, array $attractivite = []): void {
	// repères rapides : le classement DAUR et le classement Le Figaro les plus récents disponibles
	$badges = [];
	$famillesRetenues = ['DAUR' => false, 'Figaro' => false];
	foreach (configurationClassements() as $source) {
		if (!isset($famillesRetenues[$source['table']]) || $famillesRetenues[$source['table']]) {
			continue;
		}
		$ligne = isset($classements['lignes'][$source['cle']]) ? $classements['lignes'][$source['cle']] : null;
		if ($ligne === null || ($ligne['rang'] === '' && $ligne['groupe'] === '')) {
			continue;
		}
		$valeur = ($ligne['groupe'] !== '') ? $ligne['groupe'] . ' ' : '';
		$valeur .= ($ligne['rang'] !== '') ? $ligne['rang'] . '/' . $source['total'] : '';
		$badges[] = ['libelle' => $source['libelle'], 'valeur' => trim($valeur)];
		$famillesRetenues[$source['table']] = true;
	}
	if (!empty($badges)) {
		foreach ($badges as $badge) {
			echo "<span class='badge rounded-pill bg-white border text-secondary fw-normal me-1 mb-1'>"
			   . escapeHtml($badge['libelle']) . " <span class='fw-semibold'>" . escapeHtml($badge['valeur']) . "</span></span>";
		}
		if (!empty($attractivite) && $attractivite['rang'] !== '') {
			echo "<span class='badge rounded-pill bg-white border text-secondary fw-normal me-1 mb-1'>Attractivité <span class='fw-semibold'>"
			   . escapeHtml($attractivite['rang'] . '/' . $attractivite['total']) . "</span></span>";
		}
	}
	elseif (!empty($attractivite) && $attractivite['rang'] !== '') {
		echo "<span class='badge rounded-pill bg-white border text-secondary fw-normal me-1 mb-1'>Attractivité <span class='fw-semibold'>"
		   . escapeHtml($attractivite['rang'] . '/' . $attractivite['total']) . "</span></span>";
	}
}
