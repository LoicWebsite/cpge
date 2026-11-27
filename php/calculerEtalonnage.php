<?php

/**
 * Recalcule la table ConcoursEtalonnage à partir de Note, EcoleConcours, Ecole et Attractivite.
 * À relancer après chaque mise à jour des statistiques ou du niveau moyen DAUR :
 *
 *   php php/calculerEtalonnage.php
 *
 * Voir php/etalonnage.php pour la méthode.
 */

if (PHP_SAPI !== 'cli') {
	http_response_code(404);
	exit;
}

include __DIR__ . "/fonctionConcours.php";
include __DIR__ . "/etalonnage.php";

$db = openDatabase();
$resultat = enregistrerEtalonnage($db);

printf("Pente : %.4f ; dispersion résiduelle : %.3f (échelle logit)\n", $resultat['pente'], $resultat['sigma']);
$etalonnes = 0;
foreach ($resultat['groupes'] as $groupe) {
	if ($groupe['Decalage'] !== null) $etalonnes++;
}
printf("%d concours/filières, dont %d étalonnés.\n", count($resultat['groupes']), $etalonnes);
