<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Mise à jour des URL des écoles dans la base du site CPGE">

    <title>Mise à jour du site des écoles - classement DAUR</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "../php/style.php";
	?>
	
  </head>
  <body id="hautdepage" class="m-2">
		
	<?php

		include "fonctionConcours.php";

		// conexion à la base concours cpge
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// lecture de la table DAUR - pour chaque école on va chercher son Url dans la table Classement
		$sql = "SELECT DAUR.Ecole FROM DAUR";
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt;

			$sqlDAUR = "SELECT Classement.Ecole, Classement.UrlEcole FROM Classement WHERE Classement.Ecole = :ecole";
			$stmtDAUR = $db->prepare($sqlDAUR);

			$update = "UPDATE DAUR SET DAUR.UrlEcole = :url WHERE DAUR.Ecole = :ecole";
			$stmtUpdate = $db->prepare($update);

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);
		
				// initialisation des variables
				$record = 0;
				$url = "";

				// récupération de l'Url dans la table Classement
				try {
					$stmtDAUR->execute([':ecole' => $Ecole]);
					$resultDAUR = $stmtDAUR;
					while ($rowDAUR = $resultDAUR->fetch(PDO::FETCH_ASSOC)) {
						extract($rowDAUR);

						// mise à jour de la table Ecole
						echo "update DAUR pour " . escapeHtml($Ecole) . " =&gt; " . escapeHtml($UrlEcole) . "<br/>";
						try {
							$stmtUpdate->execute([
								':url' => $UrlEcole,
								':ecole' => $Ecole,
							]);
						}
						catch(PDOException $erreur)	{
							echo "Erreur : " . $erreur->getMessage();
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Classement : " . $erreur->getMessage();
				}
			}
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT DAUR : " . $erreur->getMessage();
		}

	?>
	</div>

	<footer style='margin-top:80px;'>
	</footer>

	<?php
		// librairies javascript nécessaires à l'application (jquery + popper + bootstrap)
		include "../php/librairie.php";
	?>
	
  </body>
</html>