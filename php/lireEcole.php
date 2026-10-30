<?php
/******
* Script appelé en AJAX pour récupérer la liste des écoles
* selon la filière, le concours et l’année sélectionnés.
* La liste des écoles est renvoyé dans un fichier JSON au format Option d'un Select HTML.
******/

include "controleParametre.php";   // récupère $filiere, $concours, $an
include "fonctionConcours.php";     // fonctions utilitaires

header('Content-Type: application/json; charset=utf-8');

// mode=canonique : renvoie les noms canoniques (EcoleConcours.Ecole, une entrée par
// école) au lieu des libellés concours (Note.EcoleConcours). Utilisé par la page
// "par école" ; la page "par filière" garde le mode par défaut (libellés concours).
$canonique = (isset($_GET['mode']) && $_GET['mode'] === 'canonique');

try {
    // connexion PDO
    $db = openDatabase();

    // construction dynamique de la clause WHERE (alias n = table Note)
    $conditions = [];
    if (!empty($filiere) && $filiere != "toutes") {
        $conditions[] = "n.Filiere = :filiere";
    }
    if (!empty($concours) && $concours != "tous") {
        $conditions[] = "n.Concours = :concours";
    }
    if (!empty($an) && $an != "toutes") {
        $conditions[] = "n.An = :an";
    }

    $where = '';
    if (count($conditions) > 0) {
        $where = " WHERE " . implode(" AND ", $conditions);
    }

    if ($canonique) {
        // Jointure sur la clé complète de EcoleConcours pour retrouver le nom
        // canonique de chaque école ayant publié un résultat (Note).
        $sql = "SELECT DISTINCT ec.Ecole AS Ecole
                FROM Note n
                JOIN EcoleConcours ec
                  ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours"
                . $where
                . ($where === '' ? " WHERE" : " AND") . " ec.Ecole IS NOT NULL AND ec.Ecole <> ''"
                . " ORDER BY ec.Ecole ASC";
    } else {
        $sql = "SELECT DISTINCT(n.EcoleConcours) AS Ecole FROM Note n" . $where . " ORDER BY n.EcoleConcours ASC";
    }
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

    // while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    //     $libelleEcole = supprimerRetour(supprimerApostrophe($row['Ecole']));
    //     $ecolesArray[] = ['value' => $libelleEcole, 'text' => $libelleEcole];
    // }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $ecoleBrute = $row['Ecole'];
    // On garde le texte PROPRE pour l'affichage
    // On envoie la valeur brute, c'est le rôle de encodeURIComponent en JS et de PDO en PHP de gérer l'apostrophe
    $ecolesArray[] = [
        'value' => $ecoleBrute, 
        'text'  => $ecoleBrute
        ];
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
