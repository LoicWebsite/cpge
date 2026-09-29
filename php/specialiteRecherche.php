<?php
/******
* Normalisation partagée par le moteur de recherche par spécialité
* (php/lireEcoleParSpecialite.php) et par la construction de la liste de
* mots-clés proposée dans le formulaire. Les deux doivent appliquer exactement
* les mêmes règles, sinon la liste pourrait proposer un mot-clé que le moteur
* ne sait pas retrouver.
******/

// Transforme un libellé en forme comparable : accents, casse et ponctuation
// sont neutralisés sans modifier la valeur affichée à l'utilisateur.
function normaliserRecherche(string $texte): string {
    $texte = trim($texte);
    $texte = strtr($texte, [
        'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
        'ç' => 'c',
        'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
        'ñ' => 'n',
        'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
        'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
        'ý' => 'y', 'ÿ' => 'y',
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A', 'Å' => 'A',
        'Ç' => 'C',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ý' => 'Y', 'Ÿ' => 'Y',
        'œ' => 'oe', 'Œ' => 'OE', 'æ' => 'ae', 'Æ' => 'AE',
    ]);
    $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texte);
    if ($ascii !== false) {
        $texte = $ascii;
    }
    $texte = strtolower($texte);
    $texte = preg_replace('/[^a-z0-9]+/', ' ', $texte);
    return trim(preg_replace('/\s+/', ' ', $texte));
}

// Vérifie qu'un terme apparaît comme mot ou expression entière, afin d'éviter
// qu'une requête courte comme "ia" corresponde au milieu d'un autre mot.
function contientTerme(string $texteNormalise, string $termeNormalise): bool {
    if ($texteNormalise === '' || $termeNormalise === '') {
        return false;
    }
    return preg_match('/(^| )' . preg_quote($termeNormalise, '/') . '( |$)/', $texteNormalise) === 1;
}

// Accords en genre réguliers du français, appliqués dans les deux sens : sans
// eux, "naval" ne trouve pas "Industries Aéronautiques et Navales", et une
// trentaine de cas comparables échappent à la recherche stricte.
function accordsGenre(): array {
    return [
        'al'  => ['ale', 'ales'],
        'el'  => ['elle', 'elles'],
        'if'  => ['ive', 'ives'],
        'ier' => ['iere', 'ieres'],
        'ien' => ['ienne', 'iennes'],
        'eux' => ['euse', 'euses'],
        'eau' => ['eaux'],
    ];
}

// Produit les variantes singulier/pluriel et masculin/féminin prises en charge
// par le moteur, sans appliquer de stemming approximatif susceptible de créer
// des faux positifs.
function variantesTerme(string $termeNormalise): array {
    $variantes = [$termeNormalise];
    $mots = explode(' ', $termeNormalise);
    if (count($mots) === 1) {
        $dernier = $mots[0];
        if (strlen($dernier) > 3 && substr($dernier, -1) === 's') {
            $variantes[] = substr($dernier, 0, -1);
        } elseif (strlen($dernier) > 3) {
            $variantes[] = $dernier . 's';
        }

        foreach (accordsGenre() as $masculin => $feminins) {
            $longueurMasculin = strlen($masculin);
            if (strlen($dernier) - $longueurMasculin >= 3 && substr($dernier, -$longueurMasculin) === $masculin) {
                $racine = substr($dernier, 0, -$longueurMasculin);
                foreach ($feminins as $feminin) {
                    $variantes[] = $racine . $feminin;
                }
            }
            foreach ($feminins as $feminin) {
                $longueurFeminin = strlen($feminin);
                if (strlen($dernier) - $longueurFeminin >= 3 && substr($dernier, -$longueurFeminin) === $feminin) {
                    $variantes[] = substr($dernier, 0, -$longueurFeminin) . $masculin;
                }
            }
        }
    }
    if (strpos($termeNormalise, 'materiau') !== false) {
        $variantes[] = str_replace('materiau', 'materiaux', $termeNormalise);
        $variantes[] = str_replace('materiaux', 'materiau', $termeNormalise);
    }
    return array_values(array_unique(array_filter($variantes)));
}

// Liste de mots-clés proposée dans le formulaire de recherche.
//
// Source unique : les libellés de SpecialiteDomaine. Chaque domaine est affiché
// tel qu'il est éditorialement défini, même lorsque son libellé est composé.
//
// Chaque candidat n'est retenu que si l'un de ses synonymes ramène au moins une
// spécialité : la liste ne peut donc pas proposer un mot-clé qui n'aboutirait à
// aucun résultat. Le corpus des spécialités est petit (< 1 000 libellés) mais la
// vérification est mise en cache pour ne pas la refaire à chaque affichage.
function motsClesSpecialite(PDO $db, bool $sansCache = false): array {
    $cacheDir = sys_get_temp_dir() . '/cpge_specialite_cache';
    $cacheFile = $cacheDir . '/motscles_v2.json';
    $cacheTtl = 43200; // 12h

    if (!$sansCache && is_file($cacheFile) && (time() - @filemtime($cacheFile)) <= $cacheTtl) {
        $contenu = @file_get_contents($cacheFile);
        if ($contenu !== false) {
            $liste = json_decode($contenu, true);
            if (is_array($liste) && !empty($liste)) {
                return $liste;
            }
        }
    }

    // Un domaine par entrée, avec ses synonymes : un libellé composé tel que
    // « Data / IA » ne peut pas être validé par une recherche stricte sur son
    // seul texte, mais ses termes de recherche permettent bien de le couvrir.
    $stmt = $db->query("SELECT d.Domaine, s.TermeRecherche
                        FROM SpecialiteDomaine d
                        JOIN SpecialiteSynonyme s ON s.IdDomaine = d.IdDomaine
                        WHERE d.Actif = 1
                        ORDER BY d.IdDomaine, s.Poids DESC, s.TermeRecherche");
    $candidats = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $ligne) {
        $domaine = trim((string) $ligne['Domaine']);
        $normalise = normaliserRecherche($domaine);
        $terme = normaliserRecherche((string) $ligne['TermeRecherche']);
        if ($domaine !== '' && $normalise !== '' && $terme !== '') {
            if (!isset($candidats[$normalise])) {
                $candidats[$normalise] = ['libelle' => $domaine, 'termes' => []];
            }
            foreach (variantesTerme($terme) as $variante) {
                $candidats[$normalise]['termes'][$variante] = true;
            }
        }
    }
    $stmt->closeCursor();

    $stmt = $db->query("SELECT DISTINCT Specialite FROM VueSpecialite WHERE Specialite IS NOT NULL");
    $specialites = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $specialite) {
        $normalise = normaliserRecherche((string) $specialite);
        if ($normalise !== '') {
            $specialites[] = $normalise;
        }
    }
    $stmt->closeCursor();

    $liste = [];
    foreach ($candidats as $candidat) {
        foreach (array_keys($candidat['termes']) as $variante) {
            foreach ($specialites as $specialite) {
                if (contientTerme($specialite, $variante)) {
                    $liste[] = $candidat['libelle'];
                    continue 3;
                }
            }
        }
    }

    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0775, true);
    }
    if (!empty($liste)) {
        @file_put_contents($cacheFile, json_encode($liste, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    return $liste;
}
?>
