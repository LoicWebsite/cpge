<?php 

		// initialisation des variables
		$debug = false;

		// récupération du paramètre pour passer en mode debug (par défaut pas de debug)
		if (isset($_GET['debug']))  {
			if ($_GET['debug'] == "true") {
				$debug=true;
			}
		}

		// récupération du nom de l'école recherchée (en enlevant les caractères spéciaux)
		$recherche="";
		if (isset($_GET['recherche']))  {
			$recherche = trim($_GET['recherche']);
		}

		// récupération du rang visé au concours ('aucun' par défaut)
// 		$rang="aucun";
// 		if (isset($_GET['rang']))  {
// 			if (is_numeric($_GET['rang'])) {
// 				if (($_GET['rang'] > 0) and ($_GET['rang'] < 5001)) {
// 					$rang = floor($_GET['rang']);
// 				}
// 			}
// 		}

		// récupération de l'année du concours ('toutes' par défaut)
		$reference="toutes";
		if (isset($_GET['reference']))  {
			if (($_GET['reference'] == "2024 ") or($_GET['reference'] == "2023 ") or ($_GET['reference'] == "2022 ") or ($_GET['reference'] == "2021 ") or ($_GET['reference'] == "2020") or ($_GET['reference'] == "2019") or ($_GET['reference'] == "2018") or ($_GET['reference'] == "2017")
				or ($_GET['reference'] == "2016") or ($_GET['reference'] == "toutes")) {
				$reference = trim($_GET['reference']);
			}
		}

		// récupération de l'année des statistiques pour alimenter la base et pour le zoom sur une année ('2021' par défaut)
		$an="2024";
		if (isset($_GET['an']))  {
			if (($_GET['reference'] == "2024 ") or ($_GET['reference'] == "2023 ") or ($_GET['an'] == "2022") or ($_GET['an'] == "2021") or ($_GET['an'] == "2020") or ($_GET['an'] == "2019") or ($_GET['an'] == "2018") or ($_GET['an'] == "2017") or ($_GET['an'] == "2016") or ($_GET['an'] == "toutes")) {
				$an = trim($_GET['an']);
			}
		}

		// récupération de la filière pour alimenter la base ('pt' par défaut). Attention: il faut des minuscules pour trouver la bonne URL.
		$filiere="";
		if (isset($_GET['filiere']))  {
			if (($_GET['filiere'] == "mp") or ($_GET['filiere'] == "pc") or ($_GET['filiere'] == "psi") or ($_GET['filiere'] == "tpc")
				or ($_GET['filiere'] == "pt") or ($_GET['filiere'] == "bcpst") or ($_GET['filiere'] == "tsi") or ($_GET['filiere'] == "tb")
				or ($_GET['filiere'] == "mpi")) {
				$filiere = trim($_GET['filiere']);
			}
		}

		// récupération du concours ('tous' par défaut)
		$concours="";
		if (isset($_GET['concours']))  {
			$concours = trim($_GET['concours']);
		}

		// récupération de l'école
		$ecole="";
		if (isset($_GET['ecole']))  {
			$ecole = trim($_GET['ecole']);
		}

?>