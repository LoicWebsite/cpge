<?php

	/******
	*	ce script est appelé (en ajax) depuis une page web pour charger
	*	les  concours d'une filière passée en paramètre.
	*	La liste des concours est renvoyée dans un fichier JSON au format Option d'un Select HTML.
	******/

	$nbConcours = 0;

	// récupération-contrôle des paramètres, dont le concours
	include "controleParametre.php";
//error_log("script php - filiere =".$filiere."- concours =".$concours.".",0);		
	// fonctions communes du site
	include "fonctionConcours.php";
	
	// conexion à la base cpge
	try {
		$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSE");
	}
	catch(PDOException $erreur)	{
		die('Erreur connexion base : ' . $erreur->getMessage());
	}

	// passage au mode exception pour les erreurs
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// construction de la clause WHERE
	if (($filiere <> "") and ($filiere <> "toutes")) {
		$where = " where Filiere='" . $filiere . "'";
	} else {
		$where = "";
	}
	
	// exécution de la requête SQL
	$sql = "SELECT Concours FROM Concours" . $where . " ORDER BY Concours ASC;";
	if ($debug) echo "SQL = " . $sql ."<br/>";
//error_log("requête =".$sql.".");
	try {
		$result = $db->query($sql);

		// création de l'entête du fichier JSON
		$json = '{';
		$json = $json . '"options": [';
		$json = $json . '{"value": "tous", "text": "tous"},';
				
		// affichage de chaque école dans la liste déroulante nommée ecole
		while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			extract($row);
			$nbConcours += 1;
			$libelleConcours = supprimerRetour(supprimerApostrophe($Concours));
			if ($nbConcours < $result->rowCount()) {
				$json = $json . '{"value": "' . $libelleConcours . '", "text": "' . $libelleConcours . '"},';
			} else {
				$json = $json . '{"value": "' . $libelleConcours . '", "text": "' . $libelleConcours . '"}';
			}
		}
		$json = $json . ']';
		$json = $json . '}';
	}
	catch(PDOException $erreur)	{
		echo "Erreur SELECT Concours : " . $erreur->getMessage();
	}

	// fermeture de la base
	if (isset($result)) {$result->closeCursor();}
	$db = null;

//error_log("json =".$json.".");	
	echo $json;
?>