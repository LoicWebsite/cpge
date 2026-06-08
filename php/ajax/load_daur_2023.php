<?php
// AJAX endpoint to return DAUR 2023 table HTML fragment
include __DIR__ . '/../controleParametre.php';
include __DIR__ . '/../fonctionConcours.php';

try {
    $db = openDatabase();

    $sql = "SELECT DISTINCT DAUR.Ecole, DAUR.Rang, DAUR.Groupe, DAUR.Point, DAUR.UrlEcole
            FROM DAUR
            INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',DAUR.Ecole,'%')
            WHERE DAUR.Ecole IS NOT NULL AND DAUR.An = '2023'
            ORDER BY DAUR.Rang;";
    $result = $db->query($sql);

    ob_start();
    echo "<table id='tableau-daur-2023'>";
    echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
    $idTableau = '"#tableau-daur-2023","notation;rang;note finale;école;site web"';
    echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
    echo "<thead class='text-center'>";
    echo "<tr>";
    echo "<th>&nbsp;Notation&nbsp;</th>";
    echo "<th>&nbsp;Rang&nbsp;</th>";
    echo "<th>&nbsp;Note finale&nbsp;</th>";
    echo "<th>&nbsp;Ecole&nbsp;</th>";
    echo "<th>&nbsp;Site web&nbsp;</th>";
    echo "</tr></thead>";

    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $jsEcole = encodeJs($Ecole);
        echo "<tr ondblclick='zoom(" . $jsEcole . ")'>";
        echo "<td style='text-align:center'>" . $Groupe . "</td>";
        echo "<td style='text-align:center'>" . $Rang . "</td>";
        echo "<td style='text-align:center'>" . $Point . "</td>";
        echo "<td style='padding-left:10px'><strong>" . $Ecole . "</strong></td>";
        echo "<td style='padding-left:10px'><a href='" . $UrlEcole . "' target=_blank rel='noopener'>" . $UrlEcole . "</a></td>";
        echo "</tr>";
    }

    echo "</table>";
    $html = ob_get_clean();
    echo $html;

} catch (PDOException $e) {
    http_response_code(500);
    echo "Erreur: " . $e->getMessage();
}
