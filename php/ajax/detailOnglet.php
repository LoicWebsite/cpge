<?php
/**
 * Endpoint AJAX pour le chargement à la demande des onglets de la page détail
 * école (php/detail.php). L'onglet Spécialités est rendu directement par
 * php/detail.php et ne doit donc jamais être demandé ici. Les classements,
 * le salaire et chaque filière sont des fragments chargés ici, un onglet à la
 * fois, uniquement quand l'utilisateur l'ouvre.
 *
 * Paramètres GET :
 *   ecole, origine, debug — mêmes conventions que detail.php (voir controleParametre.php)
 *   onglet                — 'entete' (bandeau classements/attractivité), 'classements',
 *                           'salaire', ou un code de filière (mp, pc, psi, ...)
 *
 * Retourne un fragment HTML brut (pas de JSON), injecté via innerHTML côté client.
 */
include __DIR__ . '/../controleParametre.php';
include __DIR__ . '/../fonctionConcours.php';
require_once __DIR__ . '/../detailEntete.php';
require_once __DIR__ . '/../detailClassement.php';
require_once __DIR__ . '/../detailFiliere.php';

header('Content-Type: text/html; charset=utf-8');

$ecoleFiltre = str_replace("\\'", "'", remettreEsperluete($ecole));
$onglet = isset($_GET['onglet']) ? trim((string) $_GET['onglet']) : '';
$ongletsAutorises = array_merge(['entete', 'classements', 'salaire'], $allowed_filieres);

if ($ecoleFiltre === '' || $ecoleFiltre === 'toutes' || !in_array($onglet, $ongletsAutorises, true)) {
	http_response_code(400);
	exit;
}

// Chaque onglet est recalculé à la demande : requêtes indexées (voir
// data/add_detail_ecole_perf_indexes.sql), pas besoin de cache fichier.
try {
	$db = openDatabase();
}
catch (PDOException $erreur) {
	echo "<div class='alert alert-danger'>Erreur connexion base : " . escapeHtml($erreur->getMessage()) . "</div>";
	exit;
}

ob_start();

if ($onglet === 'entete') {
	$classements = chargerClassements($db, $ecoleFiltre, $debug);
	$attractivite = chargerAttractivite($db, $ecoleFiltre);
	afficherBadgesEntete($classements, $attractivite);
}
elseif ($onglet === 'classements') {
	$classements = chargerClassements($db, $ecoleFiltre, $debug);
	$attractivite = chargerAttractivite($db, $ecoleFiltre);
	afficherClassements($classements, $ecoleFiltre, $attractivite);
}
elseif ($onglet === 'salaire') {
	// Le fragment reste léger : le navigateur chargera ensuite les données JSON
	// et le module Chart.js seulement après l'ouverture de l'onglet.
	echo "<section class='salaire-ecole-detail'>";
	echo "<h2 class='h5 text-center mb-3'><i class='bi bi-cash-stack'></i>&nbsp; Salaires des diplômés</h2>";
	echo "<p class='text-muted text-center'><small>Salaire mensuel net en équivalent temps plein, mesuré 12, 18, 24 et 30 mois après le diplôme.</small></p>";
	echo "<details class='mb-3'><summary>À propos des données</summary>";
	echo "<p class='mt-2 mb-0'><small>Source : dispositif InserSup du Ministère de l'Enseignement supérieur. Q1 : 25 % des diplômés gagnent moins ; médiane : 50 % gagnent moins ; Q3 : 75 % gagnent moins. <a href='https://data.enseignementsup-recherche.gouv.fr/explore/assets/fr-esr-insersup/' target='_blank' rel='noopener'>Source officielle</a>.</small></p></details>";
	echo "<div style='position:relative;max-width:900px;margin:20px auto;'><canvas id='canvas-salaire-detail'></canvas></div>";
	echo "<div id='zone-tableau-salaire-detail' class='mt-4' style='overflow-x:auto;'></div>";
	echo "<div id='erreur-salaire-detail' class='alert alert-info d-none mt-3'></div>";
	echo "</section>";
}
else {
	$ecolesConcoursRecherchees = obtenirEcolesConcoursRecherchees($db, $ecoleFiltre, $origine);
	$parConcours = chargerNotesFiliere($db, $ecolesConcoursRecherchees, $onglet, $debug);
	if (empty($parConcours)) {
		echo "<div class='alert alert-info'>Aucun résultat disponible pour cette filière.</div>";
	}
	else {
		afficherFiliere($onglet, $parConcours, $ecoleFiltre);
	}
}

$db = null;
$contenu = ob_get_clean();
echo $contenu;
