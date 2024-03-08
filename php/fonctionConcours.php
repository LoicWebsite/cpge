<?php 

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

?>