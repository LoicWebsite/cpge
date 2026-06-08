<?php
/******
* Script appelé en AJAX pour récupérer la liste des écoles
* qui proposent une spécialité donnée.
* La liste des écoles est renvoyée dans un fichier JSON au format Options d'un Select HTML.
******/

include "controleParametre.php";   // récupère $specialite
include "fonctionConcours.php";     // fonctions utilitaires

header('Content-Type: application/json; charset=utf-8');

try {
    // connexion PDO
    $db = openDatabase();

    // requête pour récupérer les écoles associées à la spécialité
    $sql = "SELECT DISTINCT(Ecole) FROM Specialite WHERE Specialite = :specialite ORDER BY Ecole ASC";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':specialite', $specialite, PDO::PARAM_STR);
    $stmt->execute();

    $ecolesArray = [];
    // option par défaut vide
    $ecolesArray[] = ['value' => '', 'text' => ''];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $libelleEcole = supprimerRetour(supprimerApostrophe($row['Ecole']));
        $ecolesArray[] = ['value' => $libelleEcole, 'text' => $libelleEcole];
    }

    $json = ['options' => $ecolesArray];
    echo json_encode($json, JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Erreur SELECT EcoleParSpecialite : " . $e->getMessage());
    echo json_encode(['options' => [['value' => '', 'text' => '']]], JSON_UNESCAPED_UNICODE);
}

// fermeture base
$stmt = null;
$db = null;
?>