<?php
/******
* Recherche AJAX des écoles par domaine ou spécialité.
* Le mode étendu combine les mots saisis avec les synonymes métier stockés en
* base ; le mode strict interroge uniquement les libellés de spécialité.
* Lorsqu'un domaine composé de la liste est choisi en mode strict, ses éléments
* séparés par « / » sont recherchés comme des alternatives.
******/

include "controleParametre.php";
include "fonctionConcours.php";
require_once __DIR__ . '/specialiteRecherche.php';

header('Content-Type: application/json; charset=utf-8');

// Détermine si deux termes peuvent désigner la même recherche après variantes.
function termesCompatibles(string $a, string $b): bool {
    foreach (variantesTerme($a) as $varianteA) {
        foreach (variantesTerme($b) as $varianteB) {
            if (contientTerme($varianteA, $varianteB) || contientTerme($varianteB, $varianteA)) {
                return true;
            }
        }
    }
    return false;
}

// Ajoute un terme et ses variantes au dictionnaire de recherche, en conservant
// le poids maximal si le même terme est rencontré plusieurs fois.
function ajouterTerme(array &$termes, string $termeNormalise, int $poids): void {
    foreach (variantesTerme($termeNormalise) as $variante) {
        if ($variante !== '') {
            $termes[$variante] = max($termes[$variante] ?? 0, $poids);
        }
    }
}

// Identifie les domaines correspondant à la requête puis ajoute leurs synonymes.
// En recherche étendue, le résultat est un dictionnaire terme normalisé => poids.
function termesElargis(PDO $db, string $requete): array {
    $normalisee = normaliserRecherche($requete);
    $termes = [];
    ajouterTerme($termes, $normalisee, 120);

    $stmt = $db->query("SELECT d.Domaine, s.TermeRecherche, s.Poids
                        FROM SpecialiteDomaine d
                        JOIN SpecialiteSynonyme s ON s.IdDomaine = d.IdDomaine
                        WHERE d.Actif = 1");
    $domainesTouches = [];
    $lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    foreach ($lignes as $ligne) {
        $domaine = normaliserRecherche($ligne['Domaine']);
        $terme = normaliserRecherche($ligne['TermeRecherche']);
        if ($terme === '' || $domaine === '') {
            continue;
        }
        if (termesCompatibles($normalisee, $terme) || termesCompatibles($normalisee, $domaine)) {
            $domainesTouches[$domaine] = true;
        }
    }

    foreach ($lignes as $ligne) {
        $domaine = normaliserRecherche($ligne['Domaine']);
        if (isset($domainesTouches[$domaine])) {
            $terme = normaliserRecherche($ligne['TermeRecherche']);
            if ($terme !== '') {
                $poids = (int) $ligne['Poids'];
                ajouterTerme($termes, $terme, $poids);
            }
        }
    }

    // Écarte les termes vides ou sans poids exploitable.
    return array_filter($termes, function ($poids, $terme) {
        return $terme !== '' && $poids > 0;
    }, ARRAY_FILTER_USE_BOTH);
}

// En mode strict, un domaine choisi dans la liste peut contenir plusieurs
// concepts séparés par « / ». On les cherche alors séparément, uniquement dans
// le libellé de spécialité ; une saisie libre conserve sa recherche littérale.
function termesStricts(PDO $db, string $requete): array {
    $stmt = $db->prepare("SELECT Domaine
                          FROM SpecialiteDomaine
                          WHERE Actif = 1 AND Domaine = :domaine
                          LIMIT 1");
    $stmt->execute([':domaine' => $requete]);
    $domaine = $stmt->fetchColumn();
    $stmt->closeCursor();

    $termes = [];
    $parties = ($domaine !== false)
        ? preg_split('#\s*/\s*#u', (string) $domaine)
        : [$requete];
    foreach ($parties as $partie) {
        $terme = normaliserRecherche($partie);
        if ($terme !== '') {
            ajouterTerme($termes, $terme, 120);
        }
    }
    return $termes;
}

// Calcule le score d'une ligne en donnant la priorité à la spécialité, puis au
// groupe et au diplôme ; le mode strict ne conserve que le premier champ.
function scorerLigne(array $ligne, array $termes, bool $rechercheStricte = false): array {
    $score = 0;
    $correspondances = [];
    $champs = $rechercheStricte
        ? ['Specialite' => 1.0]
        : [
            'Specialite' => 1.0,
            'Groupe'     => 0.7,
            'Diplome'    => 0.4,
        ];

    foreach ($champs as $champ => $coefficientChamp) {
        $texte = normaliserRecherche((string) ($ligne[$champ] ?? ''));
        foreach ($termes as $terme => $poidsTerme) {
            if (contientTerme($texte, $terme)) {
                $score += (int) round($poidsTerme * $coefficientChamp);
                $correspondances[$champ . '|' . $terme] = [
                    'term' => $terme,
                    'field' => $champ,
                ];
            }
        }
    }

    return [$score, array_values($correspondances)];
}

// Récupère et borne les paramètres de recherche avant tout accès à la base.
$requete = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($requete === '' && isset($_GET['specialite'])) {
    $requete = trim((string) $_GET['specialite']);
}
$requete = substr($requete, 0, 120);
$rechercheStricte = $strict ?? false;
$filiereRecherche = strtolower($filiere ?? '');

if (strlen(normaliserRecherche($requete)) < 2) {
    echo json_encode(['query' => $requete, 'filiere' => $filiereRecherche, 'terms' => [], 'results' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Charge les spécialités, calcule chaque score côté PHP, puis renvoie les
    // 120 meilleurs résultats au navigateur sous forme de JSON.
    $db = openDatabase();
    $termes = [];
    if ($rechercheStricte) {
        $termes = termesStricts($db, $requete);
    } else {
        $termes = termesElargis($db, $requete);
    }
    $ecolesAccessibles = null;
    if ($filiereRecherche !== '') {
        $stmtFilieres = $db->prepare("SELECT DISTINCT ec.Ecole
                                     FROM Note n
                                     JOIN EcoleConcours ec ON ec.Filiere = n.Filiere
                                        AND ec.Concours = n.Concours
                                        AND ec.EcoleConcours = n.EcoleConcours
                                     WHERE n.Filiere = :filiere
                                       AND ec.Ecole IS NOT NULL AND ec.Ecole <> ''");
        $stmtFilieres->execute([':filiere' => $filiereRecherche]);
        $ecolesAccessibles = array_fill_keys($stmtFilieres->fetchAll(PDO::FETCH_COLUMN), true);
        $stmtFilieres->closeCursor();
    }

    $stmt = $db->query("SELECT Ecole, Diplome, Groupe, Specialite, TypeEtude, AnneeChoix
                        FROM VueSpecialite
                        WHERE Specialite IS NOT NULL
                        ORDER BY Ecole, Diplome, Groupe, Specialite");

    // la recherche est réinjectée dans le lien pour que la fiche école puisse
    // proposer un retour qui relance la même recherche
    $paramsDetail = ['origine' => 'specialite', 'specialite' => $requete];
    if ($rechercheStricte) {
        $paramsDetail['strict'] = '1';
    }
    if ($filiereRecherche !== '') {
        $paramsDetail['filiere'] = $filiereRecherche;
    }

    $resultats = [];
    while ($ligne = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($ecolesAccessibles !== null && !isset($ecolesAccessibles[$ligne['Ecole']])) {
            continue;
        }
        [$score, $correspondances] = scorerLigne($ligne, $termes, $rechercheStricte);
        if ($score <= 0) {
            continue;
        }
        $cle = $ligne['Ecole'] . '|' . $ligne['Diplome'] . '|' . $ligne['Groupe'] . '|' . $ligne['Specialite'];
        $resultats[$cle] = [
            'ecole'       => $ligne['Ecole'],
            'diplome'     => $ligne['Diplome'],
            'groupe'      => $ligne['Groupe'],
            'specialite'  => $ligne['Specialite'],
            'type_etude'  => $ligne['TypeEtude'],
            'annee_choix' => $ligne['AnneeChoix'],
            'score'       => $score,
            'matches'     => $correspondances,
            'detail_url'  => 'detail-resultat-admission-par-ecole.php?'
                . http_build_query($paramsDetail + ['ecole' => $ligne['Ecole']], '', '&amp;', PHP_QUERY_RFC3986),
        ];
    }
    $stmt->closeCursor();

    // Classe les résultats par score décroissant, puis par libellé.
    usort($resultats, function ($a, $b) {
        if ($a['score'] === $b['score']) {
            return [$a['ecole'], $a['diplome'], $a['specialite']] <=> [$b['ecole'], $b['diplome'], $b['specialite']];
        }
        return $b['score'] <=> $a['score'];
    });

    echo json_encode([
        'query' => $requete,
        'strict' => $rechercheStricte,
        'filiere' => $filiereRecherche,
        'terms' => array_keys($termes),
        'results' => array_slice($resultats, 0, 120),
    ], JSON_UNESCAPED_UNICODE);
}
catch (Throwable $e) {
    error_log("Erreur lireEcoleParSpecialite : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['query' => $requete, 'filiere' => $filiereRecherche, 'terms' => [], 'results' => [], 'error' => 'Erreur de recherche'], JSON_UNESCAPED_UNICODE);
}
?>
