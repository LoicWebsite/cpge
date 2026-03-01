<?php 

// ouvre la base de données
function openDatabase() {
    static $db = null; // Pour éviter de recréer la connexion à chaque appel
    if ($db === null) {
        $db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSWORD");

		// passege au mode exception pour les erreurs
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    return $db;
}

// encode le nom de l'école dans le cas où il y a un apostrophe '
// sinon la requête SQL plante
// on change ' par \'
// suppression également du & (exemple Polytechnique P&SI) car l'école est passaée en paramèrte de l'URL et le & ne passe pas
function supprimerApostrophe ($nom) {
	
	$libelle = str_replace("'","\'",$nom);
	$libelle = str_replace("&","%26",$libelle);

	return $libelle;
}

// on remet le & à la place du %26 (une fois passer par l'URL pour pouvoir effectuer une requête)
function remettreEsperluete ($nom) {
	
	$libelle = str_replace("%26","&",$nom);

	return $libelle;
}

// nombre formaté que si différent de vide (sinon pas de différence entre zéro et vide)
function formater ($nombre, $decimal) {
	
	if ($nombre == "") {
		$libelle = "";
	} else {
		$libelle = number_format($nombre, $decimal, ',', ' ');
	}

	return $libelle;
}

// enlève le retour chariot d'un string et tous les autres caractères spéciaux en début et fin de chaîne
// enlève également les liens "<a href=.../a>"
// et enlève le caractère blanc exprimé comme &nbsp;
function supprimerRetour ($chaine) {

	// suppression du caractère html &nbsp;
	$libelle = str_replace("&nbsp;"," ",$chaine);

	// remplacezment &amp; par &
	$libelle = str_replace("&amp;","&",$libelle);

	// suppression des caractères pséciaux
	$libelle = trim($libelle);

	// suppression d'un éventuel lien "<a href= ... </a>"
	if ((strpos($libelle,"a  href")) or (strpos($libelle,"a href"))) {
		$posDebut = strpos($libelle,"<a");
		$libelle = substr($libelle,0, $posDebut);
	}

	return $libelle;
}

// extrait le nom de l'école à utiliser pour la recherche/routing
// règles :
// - on considère comme séparateur uniquement " - " (espace tiret espace)
// - si la chaîne ne contient pas ce séparateur, on renvoie la chaîne complète
// - sinon on renvoie la première partie sauf si la première partie contient "INP"
//   dans ce cas on renvoie la concaténation des deux premières parties
function extraireNomEcolePourRecherche($formation) {
	$f = trim($formation);
	// split uniquement sur ' - ' (espace tiret espace)
	$parts = preg_split('/\s-\s/', $f);
	if (count($parts) === 1) {
		$out = $f;
	} else {
		$first = $parts[0];
		if (stripos($first, 'INP') !== false) {
			if (isset($parts[1])) {
				$out = $parts[0] . ' - ' . $parts[1];
			} else {
				$out = $parts[0];
			}
		} else {
			$out = $parts[0];
		}
	}

	// retirer tout contenu entre parenthèses, garder les autres caractères
	// et normaliser les espaces
	$out = preg_replace('/\\s*\\(.*?\\)\\s*/u', ' ', $out);
	$out = trim(preg_replace('/\\s+/', ' ', $out));

	return $out;
}

?>