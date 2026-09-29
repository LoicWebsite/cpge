<?php
/**
 * Endpoint AJAX de l'onglet Salaire de la fiche école.
 *
 * Appelé par php/detail.php, via le JavaScript chargé à l'ouverture de
 * l'onglet Salaire. Il reçoit le nom canonique Ecole, retrouve les couples
 * EcoleSalaire correspondants, puis agrège Salaire par promotion. Il renvoie
 * du JSON avec les médianes, Q1, Q3 et les quatre horizons disponibles.
 * Les lignes de plusieurs rattachements sont volontairement regroupées par
 * promotion, comme sur la page principale des salaires.
 */
include __DIR__ . '/../controleParametre.php';
include __DIR__ . '/../fonctionConcours.php';

header('Content-Type: application/json; charset=utf-8');

$ecole = isset($_GET['ecole']) ? trim((string)$_GET['ecole']) : '';
if ($ecole === '' || $ecole === 'toutes') {
    http_response_code(400);
    echo json_encode(['error' => 'École manquante']);
    exit;
}

// Ces filtres sélectionnent la ligne InserSup agrégée des diplômés ingénieurs.
$BASE_FILTER = "Salaire.type_diplome = 'Formation ingénieur'
                AND Salaire.libelle_diplome = 'Tout diplôme d\\'ingénieur'
                AND Salaire.obtention_diplome = 'diplômé'
                AND Salaire.nationalite = 'ensemble'
                AND Salaire.genre = 'ensemble'
                AND Salaire.regime_inscription = 'ensemble'
                AND Salaire.promo_annee IS NOT NULL
                AND Salaire.promo_annee NOT IN ('2023','2024')
                AND Salaire.uo_lib != 'National'";

// La requête utilise EcoleSalaire comme pont stable entre le référentiel Ecole
// et les libellés variables de Salaire ; le couple vide désigne une école simple.
try {
    $db = openDatabase();
    $sql = "SELECT
                Salaire.promo_annee AS promotion,
                AVG(NULLIF(NULLIF(Salaire.salaire_q2_12, 'nd'), '')) AS med_12,
                AVG(NULLIF(NULLIF(Salaire.salaire_q2_18, 'nd'), '')) AS med_18,
                AVG(NULLIF(NULLIF(Salaire.salaire_q2_24, 'nd'), '')) AS med_24,
                AVG(NULLIF(NULLIF(Salaire.salaire_q2_30, 'nd'), '')) AS med_30,
                AVG(NULLIF(NULLIF(Salaire.salaire_q1_12, 'nd'), '')) AS q1_12,
                AVG(NULLIF(NULLIF(Salaire.salaire_q1_18, 'nd'), '')) AS q1_18,
                AVG(NULLIF(NULLIF(Salaire.salaire_q1_24, 'nd'), '')) AS q1_24,
                AVG(NULLIF(NULLIF(Salaire.salaire_q1_30, 'nd'), '')) AS q1_30,
                AVG(NULLIF(NULLIF(Salaire.salaire_q3_12, 'nd'), '')) AS q3_12,
                AVG(NULLIF(NULLIF(Salaire.salaire_q3_18, 'nd'), '')) AS q3_18,
                AVG(NULLIF(NULLIF(Salaire.salaire_q3_24, 'nd'), '')) AS q3_24,
                AVG(NULLIF(NULLIF(Salaire.salaire_q3_30, 'nd'), '')) AS q3_30,
                COUNT(DISTINCT CONCAT(Salaire.uo_lib, '|', Salaire.denomination_principale)) AS nb_sources
            FROM Salaire
            INNER JOIN EcoleSalaire
                ON EcoleSalaire.uo_lib = Salaire.uo_lib
               AND (EcoleSalaire.denomination_principale = ''
                    OR EcoleSalaire.denomination_principale = Salaire.denomination_principale)
               AND EcoleSalaire.Ecole = :ecole
            WHERE $BASE_FILTER
            GROUP BY Salaire.promo_annee
            HAVING med_12 IS NOT NULL OR med_18 IS NOT NULL OR med_24 IS NOT NULL OR med_30 IS NOT NULL
            ORDER BY Salaire.promo_annee ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':ecole' => $ecole]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
