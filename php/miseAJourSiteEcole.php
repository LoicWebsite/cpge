<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Mise à jour des URL des écoles dans la base du site CPGE">

    <title>Mise à jour du site des écoles</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "../php/style.php";
	?>
	
  </head>
  <body id="hautdepage" class="m-2">
		
	<?php

		include "fonctionConcours.php";

		// conexion à la base concours (user = concours)
		try {
			$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "cpge", "cpge");
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// passage au mode exception pour les erreurs
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// exécution de la requête SQL
		// on exécute pas petits paquest car le temps de réponse est très long : les écoles commençant par A, puis B, CEN, CES, autres C, D, ENS, ES etc.
		$sql = "SELECT DISTINCT(Ecole), UrlEtudiant FROM Ecole WHERE UrlEtudiant IS NOT NULL AND Ecole LIKE '3%';";
		try {
			$result = $db->query($sql);
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
								echo "Ecole = " . $Ecole . " - Url = " . $url . "<br/>";

								// mise à jour de la table Ecole
								$update = "UPDATE Ecole SET UrlEcole='$url' WHERE Ecole='" . supprimerApostrophe($Ecole) . "';";
								echo "update = " . $update . "<br/>";
								try {
									$retour = $db->exec($update);
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
					echo "Ecole = " . $Ecole . " : pas de site<br/>";
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