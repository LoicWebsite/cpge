<?php
/**
 * Controleur HTTP de la recherche de specialites.
 * Valide les parametres, appelle le service commun de paysageSpecialites.php,
 * puis traduit ses lignes en JSON et construit les liens vers les fiches.
 * Aucune regle de rapprochement, de score, de tri ou de comptage n'est definie ici.
 * Le lien cible un diplome deja identifie par le service ou par un couple exact
 * ecole / diplome unique ; un libelle ambigu ne produit aucun identifiant devine.
 */

include "controleParametre.php";
include "fonctionConcours.php";
require_once __DIR__ . '/specialiteRecherche.php';
require_once __DIR__ . '/paysageSpecialites.php';

header('Content-Type: application/json; charset=utf-8');

// Récupère et borne les paramètres de recherche avant tout accès à la base.
$requete = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
if ($requete === '' && isset($_GET['specialite'])) {
    $requete = trim((string) $_GET['specialite']);
}
$requete = substr($requete, 0, 120);
$rechercheStricte = $strict ?? false;
$filiereRecherche = strtolower($filiere ?? '');
$idDomaineDemande = filter_var($_GET['domaine'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null;

if (strlen(normaliserRecherche($requete)) < 2) {
    echo json_encode(['query' => $requete, 'filiere' => $filiereRecherche, 'terms' => [], 'results' => []], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $db = openDatabase();
    $recherche = rechercherSpecialitesReferentiel($db, $requete, $rechercheStricte, $filiereRecherche);
    $idsDiplomes = [];
    if ($recherche['results']) {
        foreach ($db->query('SELECT d.IdDiplome, d.Diplome, e.Ecole FROM Diplome d JOIN Ecole e ON e.IdEcole = d.IdEcole') as $diplome) {
            $cle = $diplome['Ecole'] . "\0" . $diplome['Diplome'];
            $idsDiplomes[$cle][] = (int) $diplome['IdDiplome'];
        }
    }

    // la recherche est réinjectée dans le lien pour que la fiche école puisse
    // proposer un retour qui relance la même recherche
    $paramsDetail = ['origine' => 'specialite', 'specialite' => $requete];
    if ($rechercheStricte) {
        $paramsDetail['strict'] = '1';
    }
    if ($recherche['domaine'] !== null && $idDomaineDemande !== null) {
        $paramsDetail['domaine'] = $recherche['domaine'];
    }
    if ($filiereRecherche !== '') {
        $paramsDetail['filiere'] = $filiereRecherche;
    }

    $resultats = [];
    foreach ($recherche['results'] as $ligne) {
        $ids = $idsDiplomes[$ligne['Ecole'] . "\0" . $ligne['Diplome']] ?? [];
        $idDiplome = isset($ligne['IdDiplome']) ? (int) $ligne['IdDiplome'] : (count($ids) === 1 ? $ids[0] : null);
        $paramsFormation = $paramsDetail + ['ecole' => $ligne['Ecole']];
        if ($idDiplome !== null) $paramsFormation['diplome'] = $idDiplome;
        $resultats[] = [
            'idDiplome'   => $idDiplome,
            'idSpecialite' => $recherche['domaine'] !== null ? (int) $ligne['IdSpecialite'] : null,
            'ecole'       => $ligne['Ecole'],
            'diplome'     => $ligne['Diplome'],
            'groupe'      => $ligne['Groupe'],
            'specialite'  => $ligne['Specialite'],
            'type_etude'  => $ligne['TypeEtude'],
            'annee_choix' => $ligne['AnneeChoix'],
            'score'       => $ligne['score'],
            'matches'     => $ligne['matches'],
            'detail_url'  => 'detail-resultat-admission-par-ecole.php?'
                . http_build_query($paramsFormation, '', '&amp;', PHP_QUERY_RFC3986)
                . '#onglet-specialites',
        ];
    }

    echo json_encode([
        'query' => $requete,
        'strict' => $rechercheStricte,
        'filiere' => $filiereRecherche,
        'terms' => $recherche['terms'],
        'domaine' => $recherche['domaine'],
        'totalSpecialites' => $recherche['totalSpecialites'],
        'totalEcoles' => $recherche['totalEcoles'],
        'results' => $resultats,
    ], JSON_UNESCAPED_UNICODE);
}
catch (Throwable $e) {
    error_log("Erreur lireEcoleParSpecialite : " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['query' => $requete, 'filiere' => $filiereRecherche, 'terms' => [], 'results' => [], 'error' => 'Erreur de recherche'], JSON_UNESCAPED_UNICODE);
}
?>
