<?php
/**
 * Service commun du Panorama et de la recherche de specialites, en lecture seule.
 *
 * Sources : Specialite -> Diplome -> Ecole et domaines actifs avec leurs synonymes.
 * Reutilise le noyau lexical de specialiteRecherche.php pour les dictionnaires,
 * les correspondances, la normalisation, les variantes et les scores.
 * Source commune du Panorama et des recherches lancees depuis ses domaines.
 * N'elargit pas un domaine vers les synonymes d'autres domaines et ne limite pas
 * le corpus. Les recherches libres partagent ce noyau avec des termes et champs
 * adaptes au mode strict ou etendu. L'expansion etendue donne la priorite aux
 * domaines composes explicites et preserve les recherches generales ou ambigues.
 * Produit deux vues : libelle seul et libelle + groupe + diplome.
 * Les effectifs sont distincts par identifiant dans chaque domaine, non additionnables
 * entre domaines. L'identifiant 0 est reserve aux specialites sans correspondance.
 * Ce fichier ne produit aucune sortie HTML et ne modifie jamais la base.
 */
require_once __DIR__ . '/specialiteRecherche.php';

/**
 * Classe un corpus de lignes uniques par IdSpecialite selon les synonymes propres
 * a chaque domaine. Une specialite peut appartenir a plusieurs domaines.
 *
 * @param array $specialites Lignes avec IdSpecialite, IdDiplome, IdEcole et les libelles.
 * @param array $referentiel Lignes IdDomaine, Domaine, TermeRecherche, eventuellement null.
 * @param bool $avecContexte Inclut le groupe et le diplome dans le texte compare.
 * @return array Totaux distincts, couverture et domaines avec listes d'identifiants.
 */
function classerPaysageSpecialites(array $specialites, array $referentiel, bool $avecContexte = false): array {
    $domaines = [];
    $reglesDomaines = preparerDomainesSpecialites($referentiel);
    foreach ($reglesDomaines as $id => $domaine) {
        $domaines[$id] = ['id' => $id, 'nom' => $domaine['nom'], 'specialites' => [], 'ecoles' => []];
    }
    $domaines[0] = ['id' => 0, 'nom' => 'Non classées', 'specialites' => [], 'ecoles' => []];
    $ecoles = [];
    $diplomes = [];
    $classees = 0;
    foreach ($specialites as $specialite) {
        $idSpecialite = (int) $specialite['IdSpecialite'];
        $idEcole = (int) $specialite['IdEcole'];
        $ecoles[$idEcole] = true;
        $diplomes[(int) $specialite['IdDiplome']] = true;
        $texte = (string) ($specialite['Specialite'] ?? '');
        if ($avecContexte) {
            $texte .= ' ' . ($specialite['Groupe'] ?? '') . ' ' . ($specialite['Diplome'] ?? '');
        }
        $texte = normaliserRecherche($texte);
        $ids = [];
        foreach ($reglesDomaines as $id => $domaine) {
            if (analyserTexteSpecialite($texte, $domaine['regles'])['trouve']) {
                $ids[] = $id;
            }
        }
        if ($ids !== []) {
            $classees++;
        } else {
            $ids = [0];
        }
        // Les identifiants comme cles empechent de compter plusieurs fois une ecole.
        foreach ($ids as $id) {
            $domaines[$id]['specialites'][$idSpecialite] = $idSpecialite;
            $domaines[$id]['ecoles'][$idEcole] = $idEcole;
        }
    }
    foreach ($domaines as &$domaine) {
        $domaine['specialites'] = array_values($domaine['specialites']);
        $domaine['ecoles'] = array_values($domaine['ecoles']);
    }
    unset($domaine);
    return [
        'totalSpecialites' => count($specialites),
        'totalDiplomes' => count($diplomes),
        'totalEcoles' => count($ecoles),
        'classees' => $classees,
        'domaines' => array_values($domaines),
    ];
}

/**
 * Charge le corpus complet et le classement sur libelle. Le classement avec
 * contexte est optionnel ; les pages du Panorama ne paient pas son calcul inutile.
 * Un domaine sans synonyme reste present, avec un effectif nul.
 *
 * @param PDO $db Connexion fournie par openDatabase(), sans modification de configuration.
 * @param bool $calculerContexte Inclut la vue groupe + diplome, utile aux tests et rapports.
 * @return array Corpus, agregats libelle et, si demande, contexte.
 * @throws PDOException Si le corpus ou le referentiel ne peuvent pas etre lus.
 */
function chargerPaysageSpecialites(PDO $db, bool $calculerContexte = true): array {
    $specialites = chargerCorpusSpecialites($db);
    $referentiel = chargerReferentielSpecialites($db);
    $donnees = [
        'specialites' => $specialites,
        'libelle' => classerPaysageSpecialites($specialites, $referentiel),
    ];
    if ($calculerContexte) {
        $donnees['contexte'] = classerPaysageSpecialites($specialites, $referentiel, true);
    }
    return $donnees;
}

/**
 * Charge les agrégats du Panorama depuis les décisions persistées.
 * Seules les inclusions liées à un domaine actif sont comptées ; les exclusions
 * et les spécialités sans lien actif restent dans « Non classées ».
 * La forme retournée est identique à la vue « libelle » de chargerPaysageSpecialites.
 *
 * @param PDO $db Connexion ouverte à la base CPGE.
 * @return array Corpus et agrégats par domaine à partir de SpecialiteDomaineAffectation.
 */
function chargerPaysageSpecialitesAffectees(PDO $db): array {
    $specialites = chargerCorpusSpecialites($db);
    $referentiel = chargerReferentielSpecialites($db);
    $domaines = [];
    foreach ($referentiel as $ligne) {
        $id = (int) $ligne['IdDomaine'];
        if (!isset($domaines[$id])) {
            $domaines[$id] = ['id' => $id, 'nom' => $ligne['Domaine'], 'specialites' => [], 'ecoles' => []];
        }
    }
    $domaines[0] = ['id' => 0, 'nom' => 'Non classées', 'specialites' => [], 'ecoles' => []];

    $corpusParId = [];
    $ecolesParSpecialite = [];
    $ecoles = [];
    $diplomes = [];
    foreach ($specialites as $specialite) {
        $idSpecialite = (int) $specialite['IdSpecialite'];
        $idEcole = (int) $specialite['IdEcole'];
        $corpusParId[$idSpecialite] = true;
        $ecolesParSpecialite[$idSpecialite] = $idEcole;
        $ecoles[$idEcole] = true;
        $diplomes[(int) $specialite['IdDiplome']] = true;
    }

    $classes = [];
    $stmt = $db->query("SELECT a.IdSpecialite, a.IdDomaine, e.IdEcole
        FROM SpecialiteDomaineAffectation a
        JOIN Specialite s ON s.IdSpecialite = a.IdSpecialite
        JOIN Diplome d ON d.IdDiplome = s.IdDiplome
        JOIN Ecole e ON e.IdEcole = d.IdEcole
        JOIN SpecialiteDomaine domaine ON domaine.IdDomaine = a.IdDomaine
        WHERE a.Decision = 'inclure' AND domaine.Actif = 1
        ORDER BY a.IdSpecialite, a.IdDomaine");
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $affectation) {
        $idSpecialite = (int) $affectation['IdSpecialite'];
        $idDomaine = (int) $affectation['IdDomaine'];
        $idEcole = (int) $affectation['IdEcole'];
        if (!isset($corpusParId[$idSpecialite], $domaines[$idDomaine])) continue;
        $domaines[$idDomaine]['specialites'][$idSpecialite] = $idSpecialite;
        $domaines[$idDomaine]['ecoles'][$idEcole] = $idEcole;
        $classes[$idSpecialite] = true;
    }
    $stmt->closeCursor();

    foreach ($corpusParId as $idSpecialite => $_) {
        if (!isset($classes[$idSpecialite])) {
            $idEcole = $ecolesParSpecialite[$idSpecialite];
            $domaines[0]['specialites'][$idSpecialite] = $idSpecialite;
            $domaines[0]['ecoles'][$idEcole] = $idEcole;
        }
    }

    foreach ($domaines as &$domaine) {
        $domaine['specialites'] = array_values($domaine['specialites']);
        $domaine['ecoles'] = array_values($domaine['ecoles']);
    }
    unset($domaine);

    return [
        'specialites' => $specialites,
        'libelle' => [
            'totalSpecialites' => count($specialites),
            'totalDiplomes' => count($diplomes),
            'totalEcoles' => count($ecoles),
            'classees' => count($classes),
            'domaines' => array_values($domaines),
        ],
    ];
}

/**
 * Selectionne et classe les resultats pour le controleur AJAX, sans produire de
 * HTML ni de liens. Un domaine strict réutilise les identifiants du Panorama.
 * En étendu, le nom exact d'un domaine utilise ses seuls synonymes dans les trois
 * champs ; un mot-clé libre conserve l'expansion multi-domaines existante.
 * Le filtre CPGE précède les totaux.
 *
 * @return array Domaine reconnu, termes, lignes enrichies des scores/raisons et totaux.
 * @throws PDOException Si les donnees necessaires a la recherche sont illisibles.
 */
function rechercherSpecialitesReferentiel(PDO $db, string $requete, bool $strict = false, string $filiere = ''): array {
    $domaineReconnu = null;
    $corpus = null;
    $termes = [];
    $stmt = $db->prepare('SELECT IdDomaine FROM SpecialiteDomaine WHERE Actif = 1 AND Domaine = :domaine LIMIT 1');
    $stmt->execute([':domaine' => $requete]);
    $idDomaineExact = $stmt->fetchColumn();
    $stmt->closeCursor();

    if ($strict) {
        if ($idDomaineExact !== false) {
            $corpus = chargerPaysageSpecialitesAffectees($db);
            foreach ($corpus['libelle']['domaines'] as $domaine) {
                if ($domaine['id'] === (int) $idDomaineExact) {
                    $domaineReconnu = $domaine;
                    break;
                }
            }
            $termes = termesDomaineDepuisReferentiel(chargerReferentielSpecialites($db), (int) $idDomaineExact);
        } else {
            ajouterTerme($termes, normaliserRecherche($requete), 120);
        }
    } else {
        if ($idDomaineExact !== false) {
            $termes = termesDomaineDepuisReferentiel(chargerReferentielSpecialites($db), (int) $idDomaineExact);
        } else {
            $termes = termesElargis($db, $requete);
        }
    }
    if ($domaineReconnu !== null) {
        $ids = array_fill_keys($domaineReconnu['specialites'], true);
        $lignes = array_filter($corpus['specialites'], static function (array $ligne) use ($ids): bool {
            return isset($ids[(int) $ligne['IdSpecialite']]);
        });
    } else {
        // Preserve la vue et la deduplication des recherches libres existantes.
        $lignes = $db->query('SELECT Ecole, Diplome, Groupe, Specialite, TypeEtude, AnneeChoix
            FROM VueSpecialite WHERE Specialite IS NOT NULL
            ORDER BY Ecole, Diplome, Groupe, Specialite')->fetchAll(PDO::FETCH_ASSOC);
    }
    $ecolesAccessibles = null;
    if ($filiere !== '') {
        $stmt = $db->prepare("SELECT DISTINCT ec.Ecole FROM Note n
            JOIN EcoleConcours ec ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours
                AND ec.EcoleConcours = n.EcoleConcours
            WHERE n.Filiere = :filiere AND ec.Ecole IS NOT NULL AND ec.Ecole <> ''");
        $stmt->execute([':filiere' => $filiere]);
        $ecolesAccessibles = array_fill_keys($stmt->fetchAll(PDO::FETCH_COLUMN), true);
        $stmt->closeCursor();
    }
    $regles = preparerCorrespondanceSpecialite($termes);
    $resultats = [];
    foreach ($lignes as $ligne) {
        if ($ecolesAccessibles !== null && !isset($ecolesAccessibles[$ligne['Ecole']])) continue;
        [$score, $correspondances] = scorerSpecialite($ligne, $regles, $strict);
        if ($score <= 0 && $domaineReconnu === null) continue;
        $cle = $domaineReconnu !== null ? (int) $ligne['IdSpecialite']
            : $ligne['Ecole'] . '|' . $ligne['Diplome'] . '|' . $ligne['Groupe'] . '|' . $ligne['Specialite'];
        $ligne['score'] = $score;
        $ligne['matches'] = $correspondances;
        $resultats[$cle] = $ligne;
    }
    usort($resultats, static function (array $premier, array $second): int {
        if ($premier['score'] === $second['score']) {
            return [$premier['Ecole'], $premier['Diplome'], $premier['Specialite']]
                <=> [$second['Ecole'], $second['Diplome'], $second['Specialite']];
        }
        return $second['score'] <=> $premier['score'];
    });
    return [
        'domaine' => $domaineReconnu !== null ? $domaineReconnu['id'] : null,
        'terms' => array_keys($termes),
        'results' => $resultats,
        'totalSpecialites' => count($resultats),
        'totalEcoles' => count(array_unique(array_column($resultats, 'Ecole'))),
    ];
}