<?php
/******
* Script appelé en AJAX pour récupérer la liste des écoles
* selon la filière, le concours et l’année sélectionnés.
* La liste des écoles est renvoyé dans un fichier JSON au format Option d'un Select HTML.
******/

include "controleParametre.php";   // récupère $filiere, $concours, $an
include "fonctionConcours.php";     // fonctions utilitaires

header('Content-Type: application/json; charset=utf-8');

try {
    // connexion PDO
    $db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSE");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // construction dynamique de la clause WHERE
    $conditions = [];
    if (!empty($filiere) && $filiere != "toutes") {
        $conditions[] = "Filiere = :filiere";
    }
    if (!empty($concours) && $concours != "tous") {
        $conditions[] = "Concours = :concours";
    }
    if (!empty($an) && $an != "toutes") {
        $conditions[] = "An = :an";
    }

    $where = '';
    if (count($conditions) > 0) {
        $where = " WHERE " . implode(" AND ", $conditions);
    }

    $sql = "SELECT DISTINCT(Ecole) FROM Note" . $where . " ORDER BY Ecole ASC";
    $stmt = $db->prepare($sql);

    // liaison sécurisée des paramètres
    if (!empty($filiere) && $filiere != "toutes") {
        $stmt->bindParam(':filiere', $filiere, PDO::PARAM_STR);
    }
    if (!empty($concours) && $concours != "tous") {
        $stmt->bindParam(':concours', $concours, PDO::PARAM_STR);
    }
    if (!empty($an) && $an != "toutes") {
        $stmt->bindParam(':an', $an, PDO::PARAM_INT);
    }

    $stmt->execute();

    // création du tableau de sortie
    $ecolesArray = [];
    // option par défaut
    $ecolesArray[] = ['value' => 'toutes', 'text' => 'toutes'];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $libelleEcole = supprimerRetour(supprimerApostrophe($row['Ecole']));
        $ecolesArray[] = ['value' => $libelleEcole, 'text' => $libelleEcole];
    }

    $json = ['options' => $ecolesArray];

    // envoi du JSON
    echo json_encode($json, JSON_UNESCAPED_UNICODE);

    // log pour debug (affiche le JSON envoyé)
    //error_log("JSON envoyé = " . json_encode($json, JSON_UNESCAPED_UNICODE));

} catch (PDOException $e) {
    error_log("Erreur SELECT Ecole : " . $e->getMessage());

    // renvoie un JSON minimal pour éviter un plantage JS
    echo json_encode(['options' => [['value' => 'toutes', 'text' => 'toutes']]], JSON_UNESCAPED_UNICODE);
}

// fermeture base
$stmt = null;
$db = null;
?>
