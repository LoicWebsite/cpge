<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Mise à jour des URL des écoles dans la base du site CPGE">

    <title>Mise à jour du site des écoles - classement L'Etudiant</title>

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

		// exécution de la requête SQL
		// on exécute pas petits paquest car le temps de réponse est très long : par paquet de 10 (avec le offset à changer à chaque fois).
		$sql = "SELECT Ecole, UrlEtudiant FROM Classement WHERE UrlEtudiant IS NOT NULL LIMIT 10 OFFSET 150";
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$result = $stmt;

			$update = "UPDATE Classement SET UrlEcole = :url WHERE Ecole = :ecole";
			$stmtUpdate = $db->prepare($update);

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);
		
				// initialisation des variables
				$record = 0;
				$debut = 0;
				$fin = 0;
				$prochaineLigne = false;
				$url = "";

				// récupération de la page de l'Etudiant détaillant l'école
				if ($UrlEtudiant <> "") {
					$page = fopen($UrlEtudiant,"r"); //lecture du fichier

					if ($page != false) {
						while (!feof($page)) { //on parcourt toutes les lignes
							$ligne = fgets($page, 4096); // lecture du contenu de la ligne
							$record += 1;

							// recherche du site web de l'école
							// exemple de lignes :
							//		<li class="tw-flex tw-mb-3">
							//		<a href="https://artsetmetiers.fr/fr/"

							if (!$prochaineLigne) {
								if (strpos($ligne,"tw-flex tw-mb-3")) {
									$prochaineLigne = true;
								}			
							} elseif (strpos($ligne,"href=")) {
								$debut = strpos($ligne, "=") + 2;
								$fin = strpos($ligne, '"') - 1;
								$longueur = $fin - $debut;
								$url = substr($ligne, $debut, $longueur);
								echo "Ecole = " . escapeHtml($Ecole) . " - Url = " . escapeHtml($url) . "<br/>";

								// mise à jour de la table Ecole
//								$update = "UPDATE Ecole SET UrlEcole = :url WHERE Ecole = :ecole";
								echo "update Classement pour " . escapeHtml($Ecole) . "<br/>";
								try {
									$stmtUpdate->execute([
										':url' => $url,
										':ecole' => $Ecole,
									]);
								}
								catch(PDOException $erreur)	{
									echo "Erreur : " . $erreur->getMessage();
								}

								// on sort : plus besoin de lire le reste de la page
								break;
							}
						}
					}
				} else {
					echo "Ecole = " . escapeHtml($Ecole) . " : pas de site<br/>";
				}
			}
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
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