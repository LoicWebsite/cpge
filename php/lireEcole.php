<?php

	/******
	*	ce script est appelé (en ajax) depuis une page web pour charger
	*	les écoles d'un concours passé en paramètre.
	*	La liste des écoles est renvoyé dans un fichier JSON au format Option d'un Select HTML.
	******/

	$nbEcole = 0;

	// récupération-contrôle des paramètres, dont le concours
	include "controleParametre.php";

	// fonctions communes du site
	include "fonctionConcours.php";
	
	// conexion à la base concours cpge
	try {
		$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSE");
	}
	catch(PDOException $erreur)	{
		die('Erreur connexion base : ' . $erreur->getMessage());
	}

	// passage au mode exception pour les erreurs
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// construction de la clause WHERE
	$where = "";
	if (($filiere <> "") and ($filiere <> "toutes")) {
		$where = " WHERE Filiere='" . $filiere . "'";
		if (($concours <> "") and ($concours <> "tous")) {
			$where = $where . " AND Concours='" . $concours . "'";
		}
	}

	// exécution de la requête SQL
	$sql = "SELECT Ecole FROM Ecole" . $where . " ORDER BY Ecole ASC;";
	if ($debug) echo "SQL = " . $sql ."<br/>";
	try {
		$result = $db->query($sql);

		// création de l'entête du fichier JSON
		$json = '{';
		$json = $json . '"options": [';
		$json = $json . '{"value": "toutes", "text": "toutes"},';
				
		// affichage de chaque école dans la liste déroulante nommée ecole
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			extract($row);
			$nbEcole += 1;
			if ($nbEcole < $result->rowCount()) {
//				$json = $json . '{"value": "' . $Ecole . '", "text": "' . $Ecole . '"},';
				$json = $json . '{"value": "' . supprimerRetour($Ecole) . '", "text": "' . supprimerRetour($Ecole) . '"},';
			} else {
				$json = $json . '{"value": "' . supprimerRetour($Ecole) . '", "text": "' . supprimerRetour($Ecole) . '"}';
//				$json = $json . '{"value": "' . $Ecole . '", "text": "' . $Ecole . '"}';
			}
		}
		$json = $json . ']';
		$json = $json . '}';
	}
	catch(PDOException $erreur)	{
		echo "Erreur SELECT Ecole : " . $erreur->getMessage();
	}
	
	echo $json;
	
	// fermeture de la base
	if (isset($result)) {$result->closeCursor();}
	$db = null;
?>