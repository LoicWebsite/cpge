<?php 

	// --- DEBUG ---
	$debug = (isset($_GET['debug']) && $_GET['debug'] === "true");

	// --- LISTES BLANCHES ---
	$allowed_years = ["2025","2024","2023","2022","2021","2020","2019","2018","2017","2016","toutes"];
	$allowed_filieres = ["mp","pc","psi","tpc","pt","bcpst","tsi","tb","mpi"];

	// --- PARAMÈTRES WHITELIST / SECURISES ---

	// Filière (16 chars max)
	$filiere = isset($_GET['filiere']) ? trim($_GET['filiere']) : "";
	$filiere = preg_replace("/[^a-zA-Z0-9\-]/", "", $filiere);
	$filiere = substr($filiere, 0, 16);
	if (!in_array(strtolower($filiere), $allowed_filieres)) {
		$filiere = "";
	}

	// Année du concours / référence ('toutes' par défaut, 4 chars max ou 'toutes')
	$reference = isset($_GET['reference']) ? trim($_GET['reference']) : "toutes";
	$reference = preg_replace("/[^0-9a-zA-Z]/", "", $reference);
	$reference = substr($reference, 0, 6);
	if (!in_array($reference, $allowed_years)) {
		$reference = "toutes";
	}

	$an = isset($_GET['an']) ? trim($_GET['an']) : "2025";
	$an = preg_replace("/[^0-9a-zA-Z]/", "", $an);
	$an = substr($an, 0, 6);
	if (!in_array($an, $allowed_years)) {
		$an = "2025";
	}

	// Paramètres “libres” : on autorise tout, 
	// car on va utiliser PDO avec des requêtes préparées pour éviter les injections SQL.
	$recherche  = isset($_GET['recherche']) ? trim($_GET['recherche']) : "";
	$recherche  = substr($recherche, 0, 256);

	$specialite = isset($_GET['specialite']) ? trim($_GET['specialite']) : "";
	$specialite = substr($specialite, 0, 256);

	$ecole = isset($_GET['ecole']) ? (string)$_GET['ecole'] : "";
	$ecole = substr($ecole, 0, 256); // On limite juste la taille par sécurité

	$concours = isset($_GET['concours']) ? (string)$_GET['concours'] : "";
	$concours = substr($concours, 0, 256);	

?>