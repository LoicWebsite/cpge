<?php
/**
 * PIPELINE GLOBAL "salaire InserSup" (ordonnancement des scripts) :
 *   1. data/createSalaire.sql             -> crée la table
 *   2. data/add_salaire_perf_indexes.sql  -> ajoute les index perf (1x, si absents)
 *   3. python/load_salaire.py (local)     -> recharge les données, 1x/an
 *      OU php/loadSalaire.php (production, via navigateur + token)
 *   4. php/ajax/load_salaire.php           -> lit Salaire, sert le JSON -- CE FICHIER
 *   5. salaire-ingenieur-cpge-post-prepa.php -> page affichée aux visiteurs
 *
 * Endpoint AJAX pour les données de salaires InserSup.
 *
 * Source : table Salaire (sous-ensemble filtré de l'API InserSup, noms de
 * colonnes lisibles, renommée depuis InsersupRaw le 2026-09-03 après
 * suppression de l'ancienne table T-Salaire qui occupait ce nom).
 *
 * Paramètre GET obligatoire :
 *   etablissement  — valeur de l'établissement OU mot-clé spécial :
 *                    '__liste__'       → liste déroulante des établissements
 *                    '__promotions__'  → liste des promotions disponibles
 *                    '__classement__'  → classement toutes écoles (tab 2)
 *
 * Paramètres GET optionnels (pour la requête de données principale) :
 *   col            — colonne de filtre : 'uo_lib' (défaut) ou 'source'
 *                    'source' est utilisé pour les sous-écoles des groupes
 *                    multi-formations (ex. 'TELECOM PARIS' au lieu de
 *                    'Institut Mines-Télécom')
 *
 * Retourne du JSON ; Content-Type application/json.
 */
include __DIR__ . '/../controleParametre.php';
include __DIR__ . '/../fonctionConcours.php';

header('Content-Type: application/json; charset=utf-8');

// Lecture et nettoyage du paramètre principal
$etablissement = isset($_GET['etablissement']) ? trim($_GET['etablissement']) : '';

if ($etablissement === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètre établissement manquant']);
    exit;
}

// ──────────────────────────────────────────────────────────────────────────────
// Filtres de base communs à toutes les requêtes Salaire.
// Objectif : exactement 1 ligne par école et par année de promotion.
//
//   - type_diplome = 'Formation ingénieur'
//   - libelle_diplome = 'Tout diplôme d\'ingénieur' : ligne agrégée tous diplômes
//   - obtention_diplome = 'diplômé' : exclut la ligne 'ensemble' (diplômés + non-diplômés)
//   - nationalite = 'ensemble'
//   - genre = 'ensemble'
//   - regime_inscription = 'ensemble'
//   - promo_annee IS NOT NULL : exclut les lignes cumul 2 ans (ex. ["2021","2022"])
//   - promo_annee NOT IN ('2023','2024') : données incomplètes (salaires non encore publiés)
//   - uo_lib != 'National' : exclut la ligne agrégat national
//
// Exception : les groupes multi-formations (Institut Mines-Télécom, etc.) ont
// naturellement plusieurs lignes — une par sous-école — c'est géré séparément.
//
// ══════════════════════════════════════════════════════════════════════════════
// PROCÉDURE DE MISE À JOUR ANNUELLE
// ══════════════════════════════════════════════════════════════════════════════
// InserSup publie les enquêtes avec ~2 ans de décalage (ex. promo 2022 → données
// disponibles fin 2024). Quand de nouvelles données sont publiées :
//
//  1. Recharger la table Salaire depuis l'API (en local) :
//       cd /Applications/MAMP/htdocs/loic.website/CPGE
//       python3 python/load_salaire.py
//     Puis exporter le dump SQL et l'importer en production via phpMyAdmin
//     (table assez petite désormais, ~4 400 lignes). php/loadSalaire.php
//     reste une alternative de secours si l'import phpMyAdmin n'est pas
//     possible.
//
//  2. Vérifier quelles années ont des salaires renseignés :
//       SELECT promo_annee,
//         SUM(salaire_q2_12 IS NOT NULL AND salaire_q2_12 > 0) ok_12,
//         SUM(salaire_q2_18 IS NOT NULL AND salaire_q2_18 > 0) ok_18
//       FROM Salaire
//       WHERE type_diplome = 'Formation ingénieur'
//         AND libelle_diplome = 'Tout diplôme d\'ingénieur'
//         AND obtention_diplome = 'diplômé'
//         AND nationalite = 'ensemble' AND genre = 'ensemble'
//         AND regime_inscription = 'ensemble' AND uo_lib != 'National'
//       GROUP BY promo_annee ORDER BY promo_annee;
//
//  3. Pour chaque nouvelle année avec ok_12 > 0, la retirer du NOT IN ci-dessous.
//     Exemple : si 2023 est disponible → AND promo_annee NOT IN ('2024')
//
//  Aucune autre modification n'est nécessaire : les onglets et graphiques
//  s'adaptent automatiquement aux données retournées.
// ──────────────────────────────────────────────────────────────────────────────
$ANNEE_PROMO = "promo_annee";
$BASE_FILTER = "type_diplome = 'Formation ingénieur'
                AND libelle_diplome = 'Tout diplôme d\\'ingénieur'
                AND obtention_diplome = 'diplômé'
                AND nationalite = 'ensemble'
                AND genre = 'ensemble'
                AND regime_inscription = 'ensemble'
                AND promo_annee IS NOT NULL
                AND promo_annee NOT IN ('2023','2024')
                AND Salaire.uo_lib != 'National'";

// ──────────────────────────────────────────────────────────────────────────────
// Groupes multi-formations : dans Salaire, uo_lib est le nom du groupe
// (ex. 'Institut Mines-Télécom') et denomination_principale est le nom de la
// sous-école en MAJUSCULES (ex. 'TELECOM PARIS').
// Ces groupes sont éclatés en entrées individuelles dans __liste__ et __classement__.
// ──────────────────────────────────────────────────────────────────────────────
$MULTI_FORMATION = [
    'AgroParisTech',
    'CESI',
    'CY Cergy Paris Université',
    'Centrale Lille Institut',
    'Centrale Lyon',
    'EPF - École d\'ingénieurs',
    'Groupe ENSAE-ENSAI',
    'Groupe Institut catholique d\'arts et métiers',
    "Institut Mines-Télécom",
    "Institut national d'enseignement supérieur pour l'agriculture, l'alimentation et l'environnement",
    'Institut polytechnique UniLaSalle',
    'Junia',
    'Université Clermont Auvergne',
];

try {
    $db = openDatabase();

    // ──────────────────────────────────────────────────────────────────────
    // __liste__ : retourne le tableau des établissements pour la liste déroulante
    //
    // Chaque élément retourné est un objet JS { label, col, val } :
    //   - label : texte affiché dans le <select>
    //   - col   : colonne SQL sur laquelle filtrer ('uo_lib' ou 'source')
    //   - val   : valeur à passer en paramètre à la requête de données
    //
    // Exemple pour une école simple :
    //   { label: 'CentraleSupélec', col: 'uo_lib', val: 'CentraleSupélec' }
    //
    // Exemple pour une sous-école d'un groupe :
    //   { label: 'Telecom Paris (Institut Mines-Télécom)', col: 'source', val: 'TELECOM PARIS' }
    // ──────────────────────────────────────────────────────────────────────
    if ($etablissement === '__liste__') {
        $placeholders = implode(',', array_fill(0, count($MULTI_FORMATION), '?'));

        // ── 1re requête : écoles à formation unique ──
        $sqlSingle = "SELECT DISTINCT uo_lib AS val
                      FROM Salaire
                      WHERE $BASE_FILTER
                        AND uo_lib NOT IN ($placeholders)
                        AND uo_lib IS NOT NULL AND uo_lib <> ''
                      ORDER BY uo_lib";
        $stmtS = $db->prepare($sqlSingle);
        $stmtS->execute($MULTI_FORMATION);

        $liste = [];
        while ($row = $stmtS->fetch(PDO::FETCH_ASSOC)) {
            $liste[] = ['label' => $row['val'], 'col' => 'uo_lib', 'val' => $row['val']];
        }

        // ── 2e requête : sous-écoles des groupes multi-formations ──
        $sqlMulti = "SELECT DISTINCT uo_lib AS groupe, denomination_principale AS source
                     FROM Salaire
                     WHERE $BASE_FILTER
                       AND uo_lib IN ($placeholders)
                       AND denomination_principale IS NOT NULL
                       AND denomination_principale <> ''";
        $stmtM = $db->prepare($sqlMulti);
        $stmtM->execute($MULTI_FORMATION);

        while ($row = $stmtM->fetch(PDO::FETCH_ASSOC)) {
            // denomination_principale est en MAJUSCULES (ex. 'TELECOM PARIS')
            // → ucwords(mb_strtolower()) pour obtenir 'Telecom Paris'
            $sourceLabel = ucwords(mb_strtolower($row['source'], 'UTF-8'));
            $liste[] = [
                'label' => $sourceLabel . ' (' . $row['groupe'] . ')',
                'col'   => 'source',
                'val'   => $row['source'],
            ];
        }

        $collator = new Collator('fr_FR');
        usort($liste, function($a, $b) use ($collator) {
            return $collator->compare($a['label'], $b['label']);
        });

        $json = json_encode($liste);
        echo $json;
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────
    // __promotions__ : liste des années de promotion disponibles
    // ──────────────────────────────────────────────────────────────────────
    if ($etablissement === '__promotions__') {
        $sql = "SELECT DISTINCT $ANNEE_PROMO AS promo
                FROM Salaire
                WHERE $BASE_FILTER
                ORDER BY promo DESC";
        $result = $db->query($sql);
        $liste = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $liste[] = $row['promo'];
        }
        echo json_encode($liste);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────
    // __classement__ : toutes les écoles classées par salaire médian
    //
    // Paramètres GET supplémentaires :
    //   horizon   — délai en mois : '12' (défaut), '18', '24' ou '30'
    //   promotion — année de promotion (optionnel ; si absent → toutes promos)
    //
    // Note : dans Salaire, salaire_q2_X est la médiane (Q2 = 50e percentile).
    // AVG + NULLIF('nd', 'nd') : 'nd' (non disponible) est la valeur de l'API
    // pour les données manquantes ; NULLIF le convertit en NULL avant AVG.
    // ──────────────────────────────────────────────────────────────────────
    if ($etablissement === '__classement__') {
        $promotion = isset($_GET['promotion']) ? trim($_GET['promotion']) : '';
        $horizon   = isset($_GET['horizon'])   ? trim($_GET['horizon'])   : '12';

        // Whitelist : évite l'injection SQL dans le nom de colonne
        $horizonsValides = ['12', '18', '24', '30'];
        if (!in_array($horizon, $horizonsValides, true)) {
            http_response_code(400);
            echo json_encode(['error' => 'Horizon invalide']);
            exit;
        }

        $colMed = "salaire_q2_$horizon";
        $colQ1  = "salaire_q1_$horizon";
        $colQ3  = "salaire_q3_$horizon";

        $placeholders = implode(',', array_fill(0, count($MULTI_FORMATION), '?'));
        $promoWhere = $promotion !== '' ? " AND $ANNEE_PROMO = ?" : '';

        // ── 1re requête : écoles simples ──
        $sqlSingle = "SELECT
                          Salaire.uo_lib AS etablissement,
                          NULL   AS source,
                                                    EcoleSalaire.Ecole AS ecole,
                          AVG(NULLIF(NULLIF(`$colMed`, 'nd'), '')) AS med,
                          AVG(NULLIF(NULLIF(`$colQ1`,  'nd'), '')) AS q1,
                          AVG(NULLIF(NULLIF(`$colQ3`,  'nd'), '')) AS q3
                      FROM Salaire
                                            LEFT JOIN EcoleSalaire
                                                ON EcoleSalaire.uo_lib = Salaire.uo_lib
                                             AND EcoleSalaire.denomination_principale = ''
                      WHERE $BASE_FILTER
                        AND Salaire.uo_lib NOT IN ($placeholders)
                        $promoWhere
                      GROUP BY Salaire.uo_lib
                      HAVING med IS NOT NULL AND med > 0";

        $paramsSingle = $MULTI_FORMATION;
        if ($promotion !== '') $paramsSingle[] = $promotion;

        $stmtSingle = $db->prepare($sqlSingle);
        $stmtSingle->execute($paramsSingle);
        $rows = $stmtSingle->fetchAll(PDO::FETCH_ASSOC);

        // ── 2e requête : sous-écoles des groupes multi-formations ──
        $sqlMulti = "SELECT
                         Salaire.uo_lib                AS groupe,
                         Salaire.denomination_principale AS source,
                                 EcoleSalaire.Ecole AS ecole,
                         AVG(NULLIF(NULLIF(`$colMed`, 'nd'), '')) AS med,
                         AVG(NULLIF(NULLIF(`$colQ1`,  'nd'), '')) AS q1,
                         AVG(NULLIF(NULLIF(`$colQ3`,  'nd'), '')) AS q3
                     FROM Salaire
                                         LEFT JOIN EcoleSalaire
                                             ON EcoleSalaire.uo_lib = Salaire.uo_lib
                                            AND EcoleSalaire.denomination_principale = Salaire.denomination_principale
                     WHERE $BASE_FILTER
                       AND Salaire.uo_lib IN ($placeholders)
                       AND Salaire.denomination_principale IS NOT NULL
                       AND Salaire.denomination_principale <> ''
                       $promoWhere
                    GROUP BY Salaire.uo_lib, Salaire.denomination_principale
                     HAVING med IS NOT NULL AND med > 0";

        $paramsMulti = $MULTI_FORMATION;
        if ($promotion !== '') $paramsMulti[] = $promotion;

        $stmtMulti = $db->prepare($sqlMulti);
        $stmtMulti->execute($paramsMulti);

        foreach ($stmtMulti->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $rows[] = [
                'etablissement' => ucwords(mb_strtolower($r['source'], 'UTF-8')) . ' (' . $r['groupe'] . ')',
                'source'        => $r['source'],
                'ecole'         => $r['ecole'],
                'med'           => $r['med'],
                'q1'            => $r['q1'],
                'q3'            => $r['q3'],
            ];
        }

        usort($rows, function ($a, $b) {
            return (float)$b['med'] <=> (float)$a['med'];
        });

        echo json_encode(array_values($rows));
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────
    // Requête principale : données Q1 / médiane / Q3 pour un établissement
    //
    // Résultats groupés par promo (une ligne = une année de promotion).
    // Les 4 horizons (12/18/24/30 mois) sont calculés en colonnes.
    //
    // Paramètre GET 'col' :
    //   'uo_lib' (défaut) → filtre sur `uo_lib` (écoles simples)
    //   'source'          → filtre sur `denomination_principale` (sous-écoles)
    //
    // Sécurité : $colName n'accepte que deux valeurs connues.
    // ──────────────────────────────────────────────────────────────────────
    $col = isset($_GET['col']) ? trim($_GET['col']) : 'uo_lib';
    $colName = ($col === 'source') ? 'denomination_principale' : 'uo_lib';
    $filterColumn = "Salaire.`$colName`";
    $cleEcoleSalaire = ($colName === 'denomination_principale')
        ? 'Salaire.denomination_principale'
        : "''";

    $sql = "SELECT
                $ANNEE_PROMO                                               AS promotion,
                MAX(EcoleSalaire.Ecole)                                      AS ecole,
                AVG(NULLIF(NULLIF(salaire_q2_12, 'nd'), ''))               AS med_12,
                AVG(NULLIF(NULLIF(salaire_q2_18, 'nd'), ''))               AS med_18,
                AVG(NULLIF(NULLIF(salaire_q2_24, 'nd'), ''))               AS med_24,
                AVG(NULLIF(NULLIF(salaire_q2_30, 'nd'), ''))               AS med_30,
                AVG(NULLIF(NULLIF(salaire_q1_12, 'nd'), ''))               AS q1_12,
                AVG(NULLIF(NULLIF(salaire_q1_18, 'nd'), ''))               AS q1_18,
                AVG(NULLIF(NULLIF(salaire_q1_24, 'nd'), ''))               AS q1_24,
                AVG(NULLIF(NULLIF(salaire_q1_30, 'nd'), ''))               AS q1_30,
                AVG(NULLIF(NULLIF(salaire_q3_12, 'nd'), ''))               AS q3_12,
                AVG(NULLIF(NULLIF(salaire_q3_18, 'nd'), ''))               AS q3_18,
                AVG(NULLIF(NULLIF(salaire_q3_24, 'nd'), ''))               AS q3_24,
                AVG(NULLIF(NULLIF(salaire_q3_30, 'nd'), ''))               AS q3_30
            FROM Salaire
                        LEFT JOIN EcoleSalaire
                            ON EcoleSalaire.uo_lib = Salaire.uo_lib
                         AND EcoleSalaire.denomination_principale = $cleEcoleSalaire
            WHERE $filterColumn = :valeur
              AND $BASE_FILTER
            GROUP BY $ANNEE_PROMO
            HAVING med_12 IS NOT NULL OR med_18 IS NOT NULL OR med_24 IS NOT NULL OR med_30 IS NOT NULL
            ORDER BY $ANNEE_PROMO ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':valeur' => $etablissement]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($rows);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
