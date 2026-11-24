<?php
/**
 * Noyau lexical commun du Panorama, de la recherche et des mots-cles proposes.
 *
 * Centralise normalisation, variantes, dictionnaires de synonymes, bornes de mots,
 * correspondances et scores. Les modes strict et etendu utilisent le meme moteur :
 * seuls les termes prepares et les champs examines changent.
 * Les chargements SQL partages sont en lecture seule ; ce fichier ne produit ni
 * HTML ni JSON. Les pages conservent la presentation et le controleur gere HTTP.
 *
 * Hierarchie des domaines (table SpecialiteDomaine)
 * -------------------------------------------------
 * - Code : numero hierarchique en texte ('11', '11.1', '18.2'), unique. Il sert a l'ordre
 *   d'affichage (tri naturel : 11.2 avant 11.10, 2 avant 10), jamais de cle de jointure.
 * - IdParent : NULL pour un domaine racine, sinon IdDomaine du parent. Deux niveaux au maximum.
 *   Exemple : Informatique (18) est le parent de Data / IA (18.1) et Cybersecurite (18.2).
 * - Un parent est DERIVE : il vaut ses affectations directes plus celles de ses enfants
 *   (fusionnerEnfantsDansParents, termesDomaineDepuisReferentiel). Choisir « Informatique »
 *   signifie donc « toute l'informatique ». Aucune affectation n'est recopiee dans le parent.
 * - Une decision « exclure » ne concerne que l'affectation directe d'un domaine : exclue du
 *   parent mais incluse dans un enfant, une specialite apparait quand meme dans le parent.
 * - Le libelle Domaine reste la cle fonctionnelle (liste de choix, liens, import) et
 *   IdDomaine la cle etrangere : ni l'un ni l'autre ne change avec la hierarchie.
 * Les lignes du referentiel (chargerReferentielSpecialites) portent IdDomaine, Code,
 * IdParent, Domaine, TermeRecherche, Poids ; Code et IdParent sont facultatifs (fixtures de test).
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
 *
 * Structure retournee, indexee par IdDomaine :
 *   [11 => ['id' => 11, 'nom' => 'Mobilités', 'code' => '11', 'idParent' => null,
 *           'termes' => ['transport' => 90, 'transports' => 90, ...],   // terme normalise => poids
 *           'regles' => <dictionnaire pour analyserTexteSpecialite>]]
 * Les synonymes propres ne comprennent pas ceux des enfants : voir termesDomaineDepuisReferentiel.
 */
function preparerDomainesSpecialites(array $referentiel): array {
    $domaines = [];
    foreach ($referentiel as $ligne) {
        $id = (int) $ligne['IdDomaine'];
        if (!isset($domaines[$id])) {
            $domaines[$id] = [
                'id' => $id,
                'nom' => $ligne['Domaine'],
                'code' => (string) ($ligne['Code'] ?? ''),
                'idParent' => !empty($ligne['IdParent']) ? (int) $ligne['IdParent'] : null,
                'termes' => [],
            ];
        }
        ajouterTerme($domaines[$id]['termes'], normaliserRecherche((string) ($ligne['TermeRecherche'] ?? '')), max(1, (int) ($ligne['Poids'] ?? 60)));
    }
    foreach ($domaines as &$domaine) {
        $domaine['regles'] = preparerCorrespondanceSpecialite($domaine['termes']);
    }
    unset($domaine);
    return $domaines;
}

/**
 * Retourne les synonymes (terme normalise => poids) d'un domaine, sans elargir aux domaines voisins.
 * Un parent reprend en plus ceux de ses enfants (IdParent), un enfant jamais ceux de son parent.
 * Exemple : pour Informatique (synonyme « informatique »), avec Data / IA (« data ») et
 * Cybersecurite (« cyber ») comme enfants, le resultat contient informatique, data et cyber ;
 * pour Data / IA seul, il ne contient que data. Un terme present deux fois garde le poids maximal.
 * Ordre stable : le parent d'abord, puis ses enfants par IdDomaine.
 */
function termesDomaineDepuisReferentiel(array $referentiel, int $idDomaine): array {
    $ids = [$idDomaine];
    foreach ($referentiel as $ligne) {
        if ((int) ($ligne['IdParent'] ?? 0) === $idDomaine) {
            $ids[] = (int) $ligne['IdDomaine'];
        }
    }
    $ids = array_values(array_unique($ids));
    sort($ids);
    $lignesDomaine = array_filter($referentiel, static function (array $ligne) use ($ids): bool {
        return in_array((int) $ligne['IdDomaine'], $ids, true);
    });
    $domaines = preparerDomainesSpecialites($lignesDomaine);
    $termes = [];
    // Ordre stable : le parent d'abord, puis ses enfants par identifiant.
    foreach (array_merge([$idDomaine], array_diff($ids, [$idDomaine])) as $id) {
        foreach ($domaines[$id]['termes'] ?? [] as $terme => $poids) {
            $termes[$terme] = max($termes[$terme] ?? 0, $poids);
        }
    }
    return $termes;
}

/**
 * Associe chaque domaine enfant a son parent d'apres la colonne IdParent du referentiel.
 * Exemple : [12 => 11, 13 => 11, 14 => 11, 22 => 21, 23 => 21] (Mobilites = 11, Informatique = 21).
 * Retourne un tableau vide tant que la hierarchie n'est pas renseignee.
 */
function parentsDomainesSpecialites(array $referentiel): array {
    $parents = [];
    foreach ($referentiel as $ligne) {
        if (!empty($ligne['IdParent'])) {
            $parents[(int) $ligne['IdDomaine']] = (int) $ligne['IdParent'];
        }
    }
    return $parents;
}

/**
 * Retire des affectations automatiques calculees les liens vers un parent deja couverts par un enfant.
 * Le parent est derive : il reprend ses enfants sans lien propre, un lien direct serait redondant.
 * Un lien d'enfant est effectif s'il est calcule et non bloque par une decision humaine, ou s'il vient
 * d'une inclusion manuelle/Copilot. Les decisions humaines elles-memes ne sont jamais modifiees ici.
 * Exemple : « Data et cybersecurite » calculee pour Informatique, Data / IA et Cybersecurite ne garde que
 * les deux enfants ; « Informatique generale » (aucun lien d'enfant) garde son lien vers Informatique.
 *
 * @param array $affectations Liens calcules, indexes par "idSpecialite|idDomaine" : ['IdSpecialite' => 1, 'IdDomaine' => 21].
 * @param array $parentDe Table idEnfant => idParent (parentsDomainesSpecialites).
 * @param array $decisionsProtegees Paires "idSpecialite|idDomaine" avec decision manuelle/Copilot, inclusion ou exclusion.
 * @param array $inclusionsProtegees Sous-ensemble des paires protegees dont la decision est « inclure ».
 * @return array [liens conserves, nombre de liens retires]
 */
function retirerLiensParentsRedondants(array $affectations, array $parentDe, array $decisionsProtegees, array $inclusionsProtegees): array {
    $parentsCouverts = []; // "idSpecialite|idParent" => true
    foreach ($affectations as $cle => $affectation) {
        if (isset($parentDe[$affectation['IdDomaine']]) && !isset($decisionsProtegees[$cle])) {
            $parentsCouverts[$affectation['IdSpecialite'] . '|' . $parentDe[$affectation['IdDomaine']]] = true;
        }
    }
    foreach ($inclusionsProtegees as $cle => $_) {
        [$idSpecialite, $idDomaine] = array_map('intval', explode('|', $cle));
        if (isset($parentDe[$idDomaine])) {
            $parentsCouverts[$idSpecialite . '|' . $parentDe[$idDomaine]] = true;
        }
    }
    $retires = 0;
    foreach ($affectations as $cle => $affectation) {
        if (isset($parentsCouverts[$cle])) {
            unset($affectations[$cle]);
            $retires++;
        }
    }
    return [$affectations, $retires];
}

/**
 * Un parent est derive : il reprend les specialites et les ecoles de ses enfants.
 * Les listes doivent encore etre indexees par identifiant, avant array_values.
 * Une specialite n'est comptee qu'une fois dans le parent, meme si elle est dans plusieurs enfants.
 * Exemple : Informatique contient {1, 2}, Data / IA {2, 3}, Cybersecurite {4} ;
 * apres fusion, Informatique contient {1, 2, 3, 4} et ses enfants sont inchanges.
 *
 * @param array $domaines Domaines par IdDomaine, chacun avec 'specialites' et 'ecoles' indexees par identifiant.
 * @param array $parents Table idEnfant => idParent (parentsDomainesSpecialites).
 * @return array Memes domaines, parents completes.
 */
function fusionnerEnfantsDansParents(array $domaines, array $parents): array {
    foreach ($parents as $idEnfant => $idParent) {
        if (!isset($domaines[$idEnfant], $domaines[$idParent])) continue;
        foreach (['specialites', 'ecoles'] as $liste) {
            foreach ($domaines[$idEnfant][$liste] as $identifiant) {
                $domaines[$idParent][$liste][$identifiant] = $identifiant;
            }
        }
    }
    return $domaines;
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
 * Chaque ligne : IdDomaine, Code, IdParent, Domaine, TermeRecherche (NULL sans synonyme), Poids.
 */
function chargerReferentielSpecialites(PDO $db, bool $ordonner = true): array {
    $sql = 'SELECT d.IdDomaine, d.Code, d.IdParent, d.Domaine, s.TermeRecherche, s.Poids
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
 * historique, sauf s'il designe un domaine parent (colonne IdParent) : il retient
 * alors le parent et ses enfants, soit « tout le domaine ».
 * La comparaison accepte les variantes mais pas une simple
 * inclusion de mots. Si plusieurs domaines portent ce composant, aucun choix
 * arbitraire n'est fait : l'expansion historique reste alors applicable.
 * Les synonymes seuls ne servent pas a privilegier un domaine parent trop large.
 *
 * Retourne les noms normalises des domaines retenus (cles du tableau), vide pour l'expansion historique.
 * Exemples : « informatique » -> ['informatique', 'data ia', 'cybersecurite'] (parent + enfants) ;
 * « Data / IA » -> ['data ia'] (nom exact) ; « chimie » (sans enfants) -> [] (historique).
 */
function domainesExplicitesRechercheEtendue(array $lignes, string $requeteNormalisee): array {
    $variantes = variantesTerme($requeteNormalisee);
    $noms = array_unique(array_column($lignes, 'Domaine'));
    $nomsParId = [];
    foreach ($lignes as $ligne) {
        if (isset($ligne['IdDomaine'])) $nomsParId[(int) $ligne['IdDomaine']] = normaliserRecherche($ligne['Domaine']);
    }
    $familles = [];
    foreach (parentsDomainesSpecialites($lignes) as $idEnfant => $idParent) {
        if (!isset($nomsParId[$idEnfant], $nomsParId[$idParent])) continue;
        $familles[$nomsParId[$idParent]][$nomsParId[$idParent]] = true;
        $familles[$nomsParId[$idParent]][$nomsParId[$idEnfant]] = true;
    }
    $exacts = [];
    $composants = [];
    foreach ($noms as $nom) {
        $normalise = normaliserRecherche($nom);
        if ($normalise === '') continue;
        if (strpos($nom, '/') === false) {
            if (array_intersect($variantes, variantesTerme($normalise)) !== []) return $familles[$normalise] ?? [];
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

/**
 * Trie des domaines (tableaux portant 'code' et 'id') dans l'ordre de la hierarchie :
 * tri « naturel » sur Code, de sorte que 2 < 10, 11 < 11.1 < 11.2 < 11.10 < 12.
 * Un tri textuel donnerait 1, 10, 11, 11.1, 11.10, 11.2, 2. Sans Code (fixtures), l'ordre
 * des identifiants est conserve. Le tri est stable : a code egal, l'ordre d'entree reste.
 */
function trierDomainesParCode(array $domaines): array {
    $domaines = array_values($domaines);
    usort($domaines, static function (array $premier, array $second): int {
        $ordre = strnatcmp((string) ($premier['code'] ?? ''), (string) ($second['code'] ?? ''));
        return $ordre !== 0 ? $ordre : (int) $premier['id'] <=> (int) $second['id'];
    });
    return $domaines;
}

/**
 * Liste de mots-cles proposee dans le formulaire de recherche, dans l'ordre de la hierarchie.
 *
 * Source unique : les libelles de SpecialiteDomaine. Chaque domaine est affiche
 * tel qu'il est editorialement defini, meme lorsque son libelle est compose.
 *
 * Regle de retention : un domaine n'est propose que si l'un de ses synonymes ramene au moins
 * une specialite, la liste ne pouvant pas proposer un mot-cle sans resultat. Les synonymes
 * d'un parent comprennent ceux de ses enfants (termesDomaineDepuisReferentiel) : « Informatique »
 * est donc propose des qu'une specialite de Data / IA ou de Cybersecurite existe.
 * Le corpus est petit (< 1 000 libelles) mais le resultat est mis en cache 12 h.
 *
 * Structure retournee (une entree par domaine propose, triee par Code) :
 *   [['nom' => 'Informatique',  'niveau' => 0, 'code' => '18'],
 *    ['nom' => 'Data / IA',     'niveau' => 1, 'code' => '18.1'],
 *    ['nom' => 'Cybersécurité', 'niveau' => 1, 'code' => '18.2'],
 *    ['nom' => 'Mathématiques / Finance de marché', 'niveau' => 0, 'code' => '19'], ...]
 * 'nom' est la valeur envoyee a la recherche, 'niveau' vaut 0 (racine) ou 1 (enfant) et sert
 * uniquement a l'indentation : le numero n'est pas affiche aux etudiants.
 *
 * Cache : fichier motscles_v4.json (v3 ne portait que des libelles, d'ou le changement de nom ;
 * apres une evolution de la hierarchie, ajouter ?nocache=1 a la page pour le renouveler).
 */
function motsClesSpecialite(PDO $db, bool $sansCache = false): array {
    $cacheDir = sys_get_temp_dir() . '/cpge_specialite_cache';
    $cacheFile = $cacheDir . '/motscles_v4.json';
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
    $referentiel = chargerReferentielSpecialites($db);
    $candidats = trierDomainesParCode(preparerDomainesSpecialites($referentiel));
    $specialites = array_map(static function (array $ligne): string {
        return normaliserRecherche((string) ($ligne['Specialite'] ?? ''));
    }, chargerCorpusSpecialites($db));
    $liste = [];
    foreach ($candidats as $candidat) {
        $regles = preparerCorrespondanceSpecialite(termesDomaineDepuisReferentiel($referentiel, $candidat['id']));
        foreach ($specialites as $specialite) {
            if (analyserTexteSpecialite($specialite, $regles)['trouve']) {
                $liste[] = [
                    'nom' => trim((string) $candidat['nom']),
                    'niveau' => $candidat['idParent'] === null ? 0 : 1,
                    'code' => $candidat['code'],
                ];
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
