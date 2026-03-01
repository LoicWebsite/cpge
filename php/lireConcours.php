<?php
/******
* Script appelé en AJAX pour récupérer la liste des concours
* d'une filière et d'une année donnée.
* La liste des concours est renvoyé dans un fichier JSON au format Option d'un Select HTML.
******/

include "controleParametre.php";   // récupère $filiere, $an
include "fonctionConcours.php";     // fonctions utilitaires

header('Content-Type: application/json; charset=utf-8');

try {
    // connexion PDO
    $db = openDatabase();

    // construction clause WHERE
    $conditions = [];
    if (!empty($filiere) && $filiere != "toutes") {
        $conditions[] = "Filiere = :filiere";
    }
    if (!empty($an) && $an != "toutes") {
        $conditions[] = "An = :an";
    }

    $where = '';
    if (count($conditions) > 0) {
        $where = " WHERE " . implode(" AND ", $conditions);
    }

    $sql = "SELECT DISTINCT(Concours) FROM Note" . $where . " ORDER BY Concours ASC";
    $stmt = $db->prepare($sql);

    if (!empty($filiere) && $filiere != "toutes") {
        $stmt->bindParam(':filiere', $filiere, PDO::PARAM_STR);
    }
    if (!empty($an) && $an != "toutes") {
        $stmt->bindParam(':an', $an, PDO::PARAM_INT);
    }

    $stmt->execute();

    $concoursArray = [];
    // option par défaut
    $concoursArray[] = ['value' => 'tous', 'text' => 'tous'];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $libelleConcours = supprimerRetour(supprimerApostrophe($row['Concours']));
        $concoursArray[] = ['value' => $libelleConcours, 'text' => $libelleConcours];
    }

    $json = ['options' => $concoursArray];
    echo json_encode($json, JSON_UNESCAPED_UNICODE);

    // log pour debug
    //error_log("JSON envoyé = " . json_encode($json, JSON_UNESCAPED_UNICODE));

} catch(PDOException $e) {
    error_log("Erreur SELECT Concours : " . $e->getMessage());
    echo json_encode(['options' => [['value' => 'tous', 'text' => 'tous']]], JSON_UNESCAPED_UNICODE);
}

// fermeture base
$stmt = null;
$db = null;
?>