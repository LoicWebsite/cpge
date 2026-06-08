<?php
/**
 * Endpoint AJAX pour les données de salaires InserSup.
 *
 * Source : table InsersupRaw (miroir complet de l'API InserSup, noms de colonnes
 * lisibles) — remplace l'ancienne table Salaire (noms de colonnes hachés, données
 * partielles).
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
// Filtres de base communs à toutes les requêtes InsersupRaw.
// Objectif : exactement 1 ligne par école et par année de promotion.
//
//   - type_diplome = 'formation_ingenieur'
//   - libelle_diplome = 'Tout diplôme d\'ingénieurs' : ligne agrégée tous diplômes
//   - obtention_diplome = 'diplômé' : exclut la ligne 'ensemble' (diplômés + non-diplômés)
//   - nationalite = 'ensemble'
//   - genre = 'ensemble'
//   - regime_inscription = 'ensemble'
//   - promo_annee IS NOT NULL : exclut les lignes cumul 2 ans (ex. ["2021","2022"])
//   - promo_annee NOT IN ('2023','2024') : données incomplètes (salaires non encore publiés)
//   - etablissement != 'all' : exclut la ligne agrégat national (uo_lib='National')
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
//  1. Recharger la table InsersupRaw depuis l'API :
//       cd /Applications/MAMP/htdocs/loic.website/CPGE
//       python3 python/load_insersup.py
//
//  2. Vérifier quelles années ont des salaires renseignés :
//       SELECT promo_annee,
//         SUM(salaire_q2_12 IS NOT NULL AND salaire_q2_12 > 0) ok_12,
//         SUM(salaire_q2_18 IS NOT NULL AND salaire_q2_18 > 0) ok_18
//       FROM InsersupRaw
//       WHERE type_diplome = 'formation_ingenieur'
//         AND libelle_diplome = 'Tout diplôme d\'ingénieurs'
//         AND obtention_diplome = 'diplômé'
//         AND nationalite = 'ensemble' AND genre = 'ensemble'
//         AND regime_inscription = 'ensemble' AND etablissement != 'all'
//       GROUP BY promo_annee ORDER BY promo_annee;
//
//  3. Pour chaque nouvelle année avec ok_12 > 0, la retirer du NOT IN ci-dessous.
//     Exemple : si 2023 est disponible → AND promo_annee NOT IN ('2024')
//
//  Aucune autre modification n'est nécessaire : les onglets et graphiques
//  s'adaptent automatiquement aux données retournées.
// ──────────────────────────────────────────────────────────────────────────────
$ANNEE_PROMO = "promo_annee";
$BASE_FILTER = "type_diplome = 'formation_ingenieur'
                AND libelle_diplome = 'Tout diplôme d\\'ingénieurs'
                AND obtention_diplome = 'diplômé'
                AND nationalite = 'ensemble'
                AND genre = 'ensemble'
                AND regime_inscription = 'ensemble'
                AND promo_annee IS NOT NULL
                AND promo_annee NOT IN ('2023','2024')
                AND etablissement != 'all'";

// ──────────────────────────────────────────────────────────────────────────────
// Groupes multi-formations : dans InsersupRaw, uo_lib est le nom du groupe
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

    // Cache fichier simple pour eviter de recalculer les memes agregats a
    // chaque requete (utile sur hebergement mutualise).
    $cacheDir = sys_get_temp_dir() . '/cpge_salaire_cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }
    $cacheDisabled = isset($_GET['nocache']) && $_GET['nocache'] === '1';

    $readCache = function ($key, $ttlSeconds) use ($cacheDir, $cacheDisabled) {
        if ($cacheDisabled) return false;
        $file = $cacheDir . '/' . sha1($key) . '.json';
        if (!is_file($file)) return false;
        if ((time() - @filemtime($file)) > $ttlSeconds) return false;
        $content = @file_get_contents($file);
        return ($content === false) ? false : $content;
    };

    $writeCache = function ($key, $payload) use ($cacheDir, $cacheDisabled) {
        if ($cacheDisabled) return;
        $file = $cacheDir . '/' . sha1($key) . '.json';
        @file_put_contents($file, $payload, LOCK_EX);
    };

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
        $cacheKey = 'salaire|liste|v1';
        $cached = $readCache($cacheKey, 86400);
        if ($cached !== false) {
            echo $cached;
            exit;
        }

        $placeholders = implode(',', array_fill(0, count($MULTI_FORMATION), '?'));

        // ── 1re requête : écoles à formation unique ──
        $sqlSingle = "SELECT DISTINCT uo_lib AS val
                      FROM InsersupRaw
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
                     FROM InsersupRaw
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
        $writeCache($cacheKey, $json);
        echo $json;
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────
    // __promotions__ : liste des années de promotion disponibles
    // ──────────────────────────────────────────────────────────────────────
    if ($etablissement === '__promotions__') {
        $cacheKey = 'salaire|promotions|v1';
        $cached = $readCache($cacheKey, 86400);
        if ($cached !== false) {
            echo $cached;
            exit;
        }

        $sql = "SELECT DISTINCT $ANNEE_PROMO AS promo
                FROM InsersupRaw
                WHERE $BASE_FILTER
                ORDER BY promo DESC";
        $result = $db->query($sql);
        $liste = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $liste[] = $row['promo'];
        }
        $json = json_encode($liste);
        $writeCache($cacheKey, $json);
        echo $json;
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────
    // __classement__ : toutes les écoles classées par salaire médian
    //
    // Paramètres GET supplémentaires :
    //   horizon   — délai en mois : '12' (défaut), '18', '24' ou '30'
    //   promotion — année de promotion (optionnel ; si absent → toutes promos)
    //
    // Note : dans InsersupRaw, salaire_q2_X est la médiane (Q2 = 50e percentile).
    // AVG + NULLIF('nd', 'nd') : 'nd' (non disponible) est la valeur de l'API
    // pour les données manquantes ; NULLIF le convertit en NULL avant AVG.
    // ──────────────────────────────────────────────────────────────────────
    if ($etablissement === '__classement__') {
        $promotion = isset($_GET['promotion']) ? trim($_GET['promotion']) : '';
        $horizon   = isset($_GET['horizon'])   ? trim($_GET['horizon'])   : '12';

        $cacheKey = 'salaire|classement|h=' . $horizon . '|p=' . $promotion . '|v1';
        $cached = $readCache($cacheKey, 21600);
        if ($cached !== false) {
            echo $cached;
            exit;
        }

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
                          uo_lib AS etablissement,
                          NULL   AS source,
                          AVG(NULLIF(NULLIF(`$colMed`, 'nd'), '')) AS med,
                          AVG(NULLIF(NULLIF(`$colQ1`,  'nd'), '')) AS q1,
                          AVG(NULLIF(NULLIF(`$colQ3`,  'nd'), '')) AS q3
                      FROM InsersupRaw
                      WHERE $BASE_FILTER
                        AND uo_lib NOT IN ($placeholders)
                        $promoWhere
                      GROUP BY uo_lib
                      HAVING med IS NOT NULL AND med > 0";

        $paramsSingle = $MULTI_FORMATION;
        if ($promotion !== '') $paramsSingle[] = $promotion;

        $stmtSingle = $db->prepare($sqlSingle);
        $stmtSingle->execute($paramsSingle);
        $rows = $stmtSingle->fetchAll(PDO::FETCH_ASSOC);

        // ── 2e requête : sous-écoles des groupes multi-formations ──
        $sqlMulti = "SELECT
                         uo_lib                AS groupe,
                         denomination_principale AS source,
                         AVG(NULLIF(NULLIF(`$colMed`, 'nd'), '')) AS med,
                         AVG(NULLIF(NULLIF(`$colQ1`,  'nd'), '')) AS q1,
                         AVG(NULLIF(NULLIF(`$colQ3`,  'nd'), '')) AS q3
                     FROM InsersupRaw
                     WHERE $BASE_FILTER
                       AND uo_lib IN ($placeholders)
                       AND denomination_principale IS NOT NULL
                       AND denomination_principale <> ''
                       $promoWhere
                     GROUP BY uo_lib, denomination_principale
                     HAVING med IS NOT NULL AND med > 0";

        $paramsMulti = $MULTI_FORMATION;
        if ($promotion !== '') $paramsMulti[] = $promotion;

        $stmtMulti = $db->prepare($sqlMulti);
        $stmtMulti->execute($paramsMulti);

        foreach ($stmtMulti->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $rows[] = [
                'etablissement' => ucwords(mb_strtolower($r['source'], 'UTF-8')) . ' (' . $r['groupe'] . ')',
                'source'        => $r['source'],
                'med'           => $r['med'],
                'q1'            => $r['q1'],
                'q3'            => $r['q3'],
            ];
        }

        usort($rows, function ($a, $b) {
            return (float)$b['med'] <=> (float)$a['med'];
        });

        $json = json_encode(array_values($rows));
        $writeCache($cacheKey, $json);
        echo $json;
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

    $cacheKey = 'salaire|detail|col=' . $colName . '|val=' . $etablissement . '|v1';
    $cached = $readCache($cacheKey, 21600);
    if ($cached !== false) {
        echo $cached;
        exit;
    }

    $sql = "SELECT
                $ANNEE_PROMO                                               AS promotion,
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
            FROM InsersupRaw
            WHERE `$colName` = :valeur
              AND $BASE_FILTER
            GROUP BY $ANNEE_PROMO
            HAVING med_12 IS NOT NULL OR med_18 IS NOT NULL OR med_24 IS NOT NULL OR med_30 IS NOT NULL
            ORDER BY $ANNEE_PROMO ASC";

    $stmt = $db->prepare($sql);
    $stmt->execute([':valeur' => $etablissement]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $json = json_encode($rows);
    $writeCache($cacheKey, $json);
    echo $json;

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
