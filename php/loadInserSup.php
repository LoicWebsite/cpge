<?php
/**
 * load_insersup.php
 * Recharge la table InsersupRaw depuis l'API InserSup (formation ingénieur uniquement).
 * À appeler depuis le navigateur : https://monsite.com/php/load_insersup.php?token=SECRET
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * SÉCURITÉ : Définissez un token secret ci-dessous et ne partagez pas cette URL.
 *            Supprimez ou renommez ce fichier après utilisation si vous préférez.
 * ─────────────────────────────────────────────────────────────────────────────
 *
 * FONCTIONNEMENT :
 *   - Filtre l'API sur type_diplome='formation_ingenieur' (réduit ~10x le volume)
 *   - Télécharge le CSV depuis l'API InserSup (format streaming, peu de mémoire)
 *   - Insère par lots de 500 enregistrements
 *   - Affiche la progression en temps réel dans le navigateur
 *
 * MISE À JOUR ANNUELLE :
 *   Appeler simplement cette URL une fois par an quand InserSup publie de nouvelles
 *   données. Puis retirer l'année concernée du NOT IN dans load_salaire.php.
 *
 * COMPATIBILITÉ : MySQL 5.5+ (pas de fonctions JSON requises)
 */

// ── Configuration ─────────────────────────────────────────────────────────────
define('SECRET_TOKEN', 'MonTokenSecret223557!');  // ← À personnaliser avant upload

define('DB_HOST',   'localhost');
define('DB_NAME',   'cpge');
define('DB_USER',   'cpge');
define('DB_PASS',   'cpge');

define('API_BASE',  'https://data.enseignementsup-recherche.gouv.fr'
                  . '/api/explore/v2.1/catalog/datasets/fr-esr-insersup/exports/csv/');

define('BATCH_SIZE', 500);   // lignes par INSERT
define('WHERE_FILTER', "type_diplome='formation_ingenieur'");

// Colonnes dans l'ordre du CSV de l'API (identiques à la table InsersupRaw)
const FIELDS = [
    'date_jeu', 'reg_nom', 'reg_id', 'aca_nom', 'aca_id',
    'uo_lib', 'uo_lib_actuel', 'id_paysage', 'id_paysage_actuel',
    'etablissement', 'denomination_principale',
    'type_diplome_long', 'type_diplome',
    'dom_lib', 'dom', 'discipli_lib', 'discipli', 'sectdis_lib', 'sectdis',
    'libelle_diplome', 'diplome',
    'source', 'obtention_diplome', 'genre', 'nationalite', 'regime_inscription',
    'nb_sortants', 'nb_poursuivants', 'promo',
    'flag_6', 'flag_12', 'flag_18', 'flag_24', 'flag_30',
    'exception_6', 'exception_12', 'exception_18', 'exception_24', 'exception_30',
    'tx_sortants_en_emploi_6', 'tx_sortants_en_emploi_12', 'tx_sortants_en_emploi_18',
    'tx_sortants_en_emploi_etranger_6', 'tx_sortants_en_emploi_etranger_12',
    'tx_sortants_en_emploi_etranger_18',
    'nb_sortants_en_emploi_sal_fr_6',  'nb_sortants_en_emploi_sal_fr_12',
    'nb_sortants_en_emploi_sal_fr_18', 'nb_sortants_en_emploi_sal_fr_24',
    'nb_sortants_en_emploi_sal_fr_30',
    'tx_sortants_en_emploi_sal_fr_6',  'tx_sortants_en_emploi_sal_fr_12',
    'tx_sortants_en_emploi_sal_fr_18', 'tx_sortants_en_emploi_sal_fr_24',
    'tx_sortants_en_emploi_sal_fr_30',
    'nb_sortants_en_emploi_non_sal_6',  'nb_sortants_en_emploi_non_sal_12',
    'nb_sortants_en_emploi_non_sal_18', 'nb_sortants_en_emploi_non_sal_24',
    'nb_sortants_en_emploi_non_sal_30',
    'tx_sortants_en_emploi_non_sal_6',  'tx_sortants_en_emploi_non_sal_12',
    'tx_sortants_en_emploi_non_sal_18', 'tx_sortants_en_emploi_non_sal_24',
    'tx_sortants_en_emploi_non_sal_30',
    'nb_sortants_en_emploi_stable_6',  'nb_sortants_en_emploi_stable_12',
    'nb_sortants_en_emploi_stable_18', 'nb_sortants_en_emploi_stable_24',
    'nb_sortants_en_emploi_stable_30',
    'tx_sortants_en_emploi_stable_6',  'tx_sortants_en_emploi_stable_12',
    'tx_sortants_en_emploi_stable_18', 'tx_sortants_en_emploi_stable_24',
    'tx_sortants_en_emploi_stable_30',
    'salaire_q1_6',  'salaire_q1_12',  'salaire_q1_18',  'salaire_q1_24',  'salaire_q1_30',
    'salaire_q2_6',  'salaire_q2_12',  'salaire_q2_18',  'salaire_q2_24',  'salaire_q2_30',
    'salaire_q3_6',  'salaire_q3_12',  'salaire_q3_18',  'salaire_q3_24',  'salaire_q3_30',
];

// ── Vérification du token ─────────────────────────────────────────────────────
if (!isset($_GET['token']) || $_GET['token'] !== SECRET_TOKEN) {
    http_response_code(403);
    die('Accès interdit. Ajoutez ?token=VOTRE_TOKEN à l\'URL.');
}

// ── Démarrage de la sortie HTML en streaming ───────────────────────────────────
@ini_set('max_execution_time', 0);
@ini_set('memory_limit', '256M');

header('Content-Type: text/html; charset=utf-8');
header('X-Accel-Buffering: no');  // désactive le buffering nginx si présent

// Vider tous les buffers de sortie pour affichage temps réel
while (ob_get_level()) ob_end_flush();
ob_implicit_flush(true);

echo '<!DOCTYPE html><html><head><meta charset="utf-8">
<title>Chargement InsersupRaw</title>
<style>
  body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
  .ok  { color: #4ec9b0; }
  .err { color: #f44747; }
  .inf { color: #9cdcfe; }
  h2   { color: #dcdcaa; }
</style></head><body>';
echo '<h2>Chargement InsersupRaw — formation ingénieur</h2>';
echo '<pre>';

function out($msg, $class = '') {
    $prefix = date('[H:i:s] ');
    if ($class) {
        echo "<span class=\"$class\">$prefix$msg</span>\n";
    } else {
        echo "$prefix$msg\n";
    }
    flush();
}

// ── Connexion PDO ─────────────────────────────────────────────────────────────
try {
    $db = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    out('Erreur connexion BDD : ' . $e->getMessage(), 'err');
    echo '</pre></body></html>';
    exit(1);
}

out('Connexion BDD OK.', 'ok');

// ── TRUNCATE ──────────────────────────────────────────────────────────────────
out('Vidage de la table InsersupRaw (TRUNCATE)…');
$db->exec('TRUNCATE TABLE InsersupRaw');
out('Table vidée.', 'ok');

// ── Construction de l\'URL CSV ─────────────────────────────────────────────────
$url = API_BASE . '?' . http_build_query([
    'where'    => WHERE_FILTER,
    'lang'     => 'fr',
    'timezone' => 'Europe/Paris',
    'delimiter' => ';',
]);
out('Téléchargement CSV depuis l\'API InserSup…', 'inf');
out('URL : ' . $url, 'inf');

// ── Ouverture du flux CSV en streaming ────────────────────────────────────────
$context = stream_context_create(['http' => [
    'timeout'     => 600,
    'user_agent'  => 'load_insersup_php/1.0',
    'ignore_errors' => true,
]]);

$handle = @fopen($url, 'r', false, $context);
if (!$handle) {
    out('Impossible d\'ouvrir le flux CSV depuis l\'API.', 'err');
    echo '</pre></body></html>';
    exit(1);
}

// ── Lecture de l\'en-tête CSV pour mapper les colonnes ─────────────────────────
$csvHeader = fgetcsv($handle, 0, ';');
if (!$csvHeader) {
    out('Impossible de lire l\'en-tête CSV.', 'err');
    fclose($handle);
    echo '</pre></body></html>';
    exit(1);
}

// Nettoyer les BOM et espaces
$csvHeader = array_map(function($h) {
    return trim(str_replace("\xEF\xBB\xBF", '', $h));
}, $csvHeader);

out('Colonnes API reçues : ' . count($csvHeader), 'inf');

// Construire le mapping : index CSV → nom de colonne attendu
$colMap = [];   // colMap[i] = nom de colonne FIELDS si présent dans l'API
foreach ($csvHeader as $i => $name) {
    if (in_array($name, FIELDS, true)) {
        $colMap[$i] = $name;
    }
}
out('Colonnes mappées : ' . count($colMap) . '/' . count(FIELDS), 'inf');

// ── Préparation de l\'INSERT ───────────────────────────────────────────────────
$colNames   = implode(', ', array_map(fn($c) => "`$c`", FIELDS));
$insertSql  = "INSERT INTO `InsersupRaw` ($colNames) VALUES ";

// ── Lecture et insertion en lots ──────────────────────────────────────────────
$batch      = [];
$totalRows  = 0;
$lastReport = 0;

function buildValues(array $row, array $csvHeader, array $colMap): string {
    // Initialiser toutes les colonnes à NULL
    $vals = array_fill_keys(FIELDS, null);
    foreach ($colMap as $i => $colName) {
        $val = isset($row[$i]) ? $row[$i] : null;
        $vals[$colName] = ($val === '' || $val === null) ? null : $val;
    }

    // promo : la colonne locale est GENERATED AS JSON_LENGTH(promo)
    // → toute valeur stockée doit être du JSON valide, sinon MySQL 8 crashe.
    // • déjà un tableau JSON "[...]" → on garde tel quel
    // • année à 4 chiffres "2022"   → on encapsule en '["2022"]'
    // • tout autre format ("2022-2023", etc.) → NULL (promo_annee sera NULL de toute façon)
    if ($vals['promo'] !== null) {
        $p = trim($vals['promo']);
        if (substr($p, 0, 1) === '[') {
            // déjà JSON array, on garde
        } elseif (preg_match('/^\d{4}$/', $p)) {
            $vals['promo'] = '["' . $p . '"]';
        } else {
            $vals['promo'] = null;
        }
    }

    $parts = [];
    foreach (FIELDS as $col) {
        $v = $vals[$col];
        if ($v === null) {
            $parts[] = 'NULL';
        } else {
            $parts[] = "'" . addslashes($v) . "'";
        }
    }

    return '(' . implode(', ', $parts) . ')';
}

function flushBatch(PDO $db, string $insertSql, array &$batch, int &$totalRows): void {
    if (empty($batch)) return;
    $sql = $insertSql . implode(",\n", $batch) . ';';
    $db->exec($sql);
    $totalRows += count($batch);
    $batch = [];
}

while (($row = fgetcsv($handle, 0, ';')) !== false) {
    if (empty(array_filter($row))) continue;  // ligne vide

    $batch[] = buildValues($row, $csvHeader, $colMap);

    if (count($batch) >= BATCH_SIZE) {
        flushBatch($db, $insertSql, $batch, $totalRows);
    }

    // Rapport toutes les 10 000 lignes
    if ($totalRows - $lastReport >= 10000) {
        out("$totalRows lignes insérées…");
        $lastReport = $totalRows;
    }
}
fclose($handle);

// Dernier lot
flushBatch($db, $insertSql, $batch, $totalRows);

// ── UPDATE promo_annee (colonne normale partout depuis la simplification du schéma)
out('Mise à jour de promo_annee…');
$db->exec("UPDATE InsersupRaw SET promo_annee = SUBSTRING(promo, 3, 4) WHERE CHAR_LENGTH(promo) = 8 AND promo_annee IS NULL");
out('promo_annee mise à jour.', 'ok');

// ── Résultat final ────────────────────────────────────────────────────────────
out('');
out("Terminé ! $totalRows enregistrements insérés.", 'ok');

// Vérification rapide
$count = $db->query('SELECT COUNT(*) FROM InsersupRaw')->fetchColumn();
out("Vérification COUNT(*) : $count", 'ok');

$check = $db->query("SELECT promo_annee, COUNT(*) n FROM InsersupRaw WHERE promo_annee IS NOT NULL GROUP BY promo_annee ORDER BY promo_annee")->fetchAll(PDO::FETCH_ASSOC);
out('Répartition par année :', 'inf');
foreach ($check as $r) {
    out("  promo {$r['promo_annee']} → {$r['n']} lignes");
}

echo '</pre><p style="color:#4ec9b0;font-weight:bold">✓ Import terminé avec succès.</p>';
echo '</body></html>';
