<?php
/**
 * Noyau lexical commun du Panorama, de la recherche et des mots-cles proposes.
 *
 * Centralise normalisation, variantes, dictionnaires de synonymes, bornes de mots,
 * correspondances et scores. Les modes strict et etendu utilisent le meme moteur :
 * seuls les termes prepares et les champs examines changent.
 * Les chargements SQL partages sont en lecture seule ; ce fichier ne produit ni
 * HTML ni JSON. Les pages conservent la presentation et le controleur gere HTTP.
 */

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
    return preg_match(motifTermesSpecialite([$termeNormalise]), $texteNormalise) === 1;
}

/**
 * Source unique des bornes de mots et expressions : aucun terme ne correspond
 * au milieu d'un autre mot. Les termes sont deja normalises et sont echappes.
 * Un dictionnaire vide produit un motif qui ne peut jamais correspondre.
 */
function motifTermesSpecialite(array $termes): string {
    if ($termes === []) return '/(?!)/';
    $alternatives = array_map(static function (string $terme): string {
        return preg_quote($terme, '/');
    }, $termes);
    return '/(^| )(?:' . implode('|', $alternatives) . ')( |$)/';
}

/** Compile une seule fois un dictionnaire terme => poids pour les deux pages. */
function preparerCorrespondanceSpecialite(array $termes): array {
    $motifsTermes = [];
    foreach ($termes as $terme => $poids) {
        $motifsTermes[$terme] = motifTermesSpecialite([(string) $terme]);
    }
    return ['termes' => $termes, 'motif' => motifTermesSpecialite(array_keys($termes)), 'motifsTermes' => $motifsTermes];
}

/**
 * Noyau commun de rapprochement. Le Panorama demande seulement l'appartenance ;
 * la recherche demande aussi les termes trouves pour les scores et les raisons.
 * Le motif groupe preserve l'optimisation du Panorama : aucun parcours des termes
 * n'est fait lorsqu'un libelle ne correspond pas, ni pour un simple comptage.
 */
function analyserTexteSpecialite(string $texteNormalise, array $regles, bool $avecDetails = false): array {
    $trouve = $texteNormalise !== '' && preg_match($regles['motif'], $texteNormalise) === 1;
    $correspondances = [];
    if ($trouve && $avecDetails) {
        foreach ($regles['motifsTermes'] as $terme => $motif) {
            if (preg_match($motif, $texteNormalise) === 1) {
                $correspondances[$terme] = $regles['termes'][$terme];
            }
        }
    }
    return ['trouve' => $trouve, 'termes' => $correspondances];
}

/** Ajoute les variantes d'un terme en conservant le poids maximal, sans doublon. */
function ajouterTerme(array &$termes, string $termeNormalise, int $poids): void {
    foreach (variantesTerme($termeNormalise) as $variante) {
        if ($variante !== '') {
            $termes[$variante] = max($termes[$variante] ?? 0, $poids);
        }
    }
}

/**
 * Prepare les synonymes propres a chaque domaine. Les poids servent au classement
 * de la recherche, jamais a exclure un synonyme de l'appartenance au domaine.
 * Le meme dictionnaire alimente les bulles, la recherche stricte et les suggestions.
 */
function preparerDomainesSpecialites(array $referentiel): array {
    $domaines = [];
    foreach ($referentiel as $ligne) {
        $id = (int) $ligne['IdDomaine'];
        if (!isset($domaines[$id])) {
            $domaines[$id] = ['id' => $id, 'nom' => $ligne['Domaine'], 'termes' => []];
        }
        ajouterTerme($domaines[$id]['termes'], normaliserRecherche((string) ($ligne['TermeRecherche'] ?? '')), max(1, (int) ($ligne['Poids'] ?? 60)));
    }
    foreach ($domaines as &$domaine) {
        $domaine['regles'] = preparerCorrespondanceSpecialite($domaine['termes']);
    }
    unset($domaine);
    return $domaines;
}

/** Retourne les synonymes d'un seul domaine, sans élargir aux domaines voisins. */
function termesDomaineDepuisReferentiel(array $referentiel, int $idDomaine): array {
    $lignesDomaine = array_filter($referentiel, static function (array $ligne) use ($idDomaine): bool {
        return (int) $ligne['IdDomaine'] === $idDomaine;
    });
    $domaines = preparerDomainesSpecialites($lignesDomaine);
    return $domaines[$idDomaine]['termes'] ?? [];
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
        if (strlen($dernier) > 4 && substr($dernier, -4) === 'eaux') {
            $variantes[] = substr($dernier, 0, -1);
        } elseif (strlen($dernier) > 4 && substr($dernier, -3) === 'aux') {
            $singuliersIrreguliers = ['travaux' => 'travail'];
            $variantes[] = $singuliersIrreguliers[$dernier] ?? (substr($dernier, 0, -3) . 'al');
        } elseif (strlen($dernier) > 3 && substr($dernier, -1) === 's') {
            $variantes[] = substr($dernier, 0, -1);
        } elseif (strlen($dernier) > 3 && substr($dernier, -1) === 'x') {
            // Les pluriels en -x ne prennent pas de s supplémentaire.
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

/** Charge une seule version du corpus specialise relie aux diplomes et ecoles. */
function chargerCorpusSpecialites(PDO $db): array {
    return $db->query('SELECT s.IdSpecialite, s.Specialite, s.Groupe, s.TypeEtude, s.AnneeChoix,
        d.IdDiplome, d.Diplome, e.IdEcole, e.Ecole
        FROM Specialite s JOIN Diplome d ON d.IdDiplome = s.IdDiplome
        JOIN Ecole e ON e.IdEcole = d.IdEcole
        ORDER BY e.Ecole, d.Diplome, s.Specialite')->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Charge les domaines actifs, y compris ceux sans synonyme. L'ordre alphabetique
 * sert au Panorama ; l'ordre naturel preserve celui des termes de la recherche etendue.
 */
function chargerReferentielSpecialites(PDO $db, bool $ordonner = true): array {
    $sql = 'SELECT d.IdDomaine, d.Domaine, s.TermeRecherche, s.Poids
        FROM SpecialiteDomaine d LEFT JOIN SpecialiteSynonyme s ON s.IdDomaine = d.IdDomaine
        WHERE d.Actif = 1';
    if ($ordonner) $sql .= ' ORDER BY d.Domaine, s.TermeRecherche';
    return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
}

/** Compare deux termes apres variantes pour l'expansion du mode etendu. */
function termesCompatibles(string $premier, string $second): bool {
    foreach (variantesTerme($premier) as $variantePremiere) {
        foreach (variantesTerme($second) as $varianteSeconde) {
            if (contientTerme($variantePremiere, $varianteSeconde) || contientTerme($varianteSeconde, $variantePremiere)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * Reconnait un domaine compose explicitement nomme, en entier ou par un composant
 * separe par une barre oblique. Un nom simple complet conserve son elargissement
 * historique : Informatique, par exemple, doit aussi garder la cybersecurite.
 * La comparaison accepte les variantes mais pas une simple
 * inclusion de mots. Si plusieurs domaines portent ce composant, aucun choix
 * arbitraire n'est fait : l'expansion historique reste alors applicable.
 * Les synonymes seuls ne servent pas a privilegier un domaine parent trop large.
 */
function domainesExplicitesRechercheEtendue(array $lignes, string $requeteNormalisee): array {
    $variantes = variantesTerme($requeteNormalisee);
    $noms = array_unique(array_column($lignes, 'Domaine'));
    $exacts = [];
    $composants = [];
    foreach ($noms as $nom) {
        $normalise = normaliserRecherche($nom);
        if ($normalise === '') continue;
        if (strpos($nom, '/') === false) {
            if (array_intersect($variantes, variantesTerme($normalise)) !== []) return [];
            continue;
        }
        if (array_intersect($variantes, variantesTerme($normalise)) !== []) {
            $exacts[$normalise] = true;
        }
        foreach (preg_split('#\s*/\s*#u', $nom) as $partie) {
            if (array_intersect($variantes, variantesTerme(normaliserRecherche($partie))) !== []) {
                $composants[$normalise] = true;
            }
        }
    }
    if ($exacts !== []) return count($exacts) === 1 ? $exacts : [];
    return count($composants) === 1 ? $composants : [];
}

/**
 * Prepare les termes etendus a partir du referentiel, sans acces SQL, pour rendre
 * la priorite testable. Un domaine compose explicite et unique fournit ses seuls synonymes ;
 * un mot-cle generique ou ambigu conserve l'expansion historique multi-domaines.
 * Le mot saisi et les poids maximaux des variantes sont toujours conserves.
 */
function termesElargisDepuisReferentiel(array $lignes, string $requete): array {
    $normalisee = normaliserRecherche($requete);
    $termes = [];
    ajouterTerme($termes, $normalisee, 120);
    $domainesTouches = domainesExplicitesRechercheEtendue($lignes, $normalisee);
    if ($domainesTouches === []) {
        foreach ($lignes as $ligne) {
            $domaine = normaliserRecherche($ligne['Domaine']);
            $terme = normaliserRecherche((string) ($ligne['TermeRecherche'] ?? ''));
            if ($terme === '' || $domaine === '') continue;
            if (termesCompatibles($normalisee, $terme) || termesCompatibles($normalisee, $domaine)) {
                $domainesTouches[$domaine] = true;
            }
        }
    }
    foreach ($lignes as $ligne) {
        if (isset($domainesTouches[normaliserRecherche($ligne['Domaine'])])) {
            $terme = normaliserRecherche((string) ($ligne['TermeRecherche'] ?? ''));
            if ($terme !== '') ajouterTerme($termes, $terme, (int) $ligne['Poids']);
        }
    }
    return array_filter($termes, static function ($poids, $terme): bool {
        return $terme !== '' && $poids > 0;
    }, ARRAY_FILTER_USE_BOTH);
}

/** Charge le referentiel commun et prepare l'elargissement du mode etendu. */
function termesElargis(PDO $db, string $requete): array {
    return termesElargisDepuisReferentiel(chargerReferentielSpecialites($db, false), $requete);
}

/**
 * Calcule les scores et les raisons avec le noyau commun de rapprochement.
 * En strict, seul Specialite est examine ; en etendu, Groupe et Diplome sont
 * ponderes a 0,7 et 0,4. Les regles sont preparees une fois pour tout le corpus.
 */
function scorerSpecialite(array $ligne, array $regles, bool $rechercheStricte = false): array {
    $score = 0;
    $correspondances = [];
    $champs = $rechercheStricte ? ['Specialite' => 1.0] : ['Specialite' => 1.0, 'Groupe' => 0.7, 'Diplome' => 0.4];
    foreach ($champs as $champ => $coefficient) {
        $analyse = analyserTexteSpecialite(normaliserRecherche((string) ($ligne[$champ] ?? '')), $regles, true);
        foreach ($analyse['termes'] as $terme => $poids) {
            $score += (int) round($poids * $coefficient);
            $correspondances[$champ . '|' . $terme] = ['term' => $terme, 'field' => $champ];
        }
    }
    return [$score, array_values($correspondances)];
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
    $cacheFile = $cacheDir . '/motscles_v3.json';
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

    // La liste utilise les memes dictionnaires et le meme test que le Panorama.
    $candidats = preparerDomainesSpecialites(chargerReferentielSpecialites($db));
    ksort($candidats);
    $specialites = array_map(static function (array $ligne): string {
        return normaliserRecherche((string) ($ligne['Specialite'] ?? ''));
    }, chargerCorpusSpecialites($db));
    $liste = [];
    foreach ($candidats as $candidat) {
        foreach ($specialites as $specialite) {
            if (analyserTexteSpecialite($specialite, $candidat['regles'])['trouve']) {
                $liste[] = trim((string) $candidat['nom']);
                break;
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
