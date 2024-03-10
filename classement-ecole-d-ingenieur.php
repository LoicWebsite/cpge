<!doctype html>

<?php

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";
?>

<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Classement des écoles d'ingénieurs post prépa CPGE">

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Classement des écoles d'ingénieurs post prépa</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>

	<style>
		th, td {font-size:100%}
	</style>
	
  </head>
  <body id="hautdepage">

	<?php
		include "php/menu.php";
	?>
	<nav id="chemin" class="container">
		<div class="row" style='margin-top:80px;'>
			<div class="col-sm" aria-label="breadcrumb">
			  <ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php"><i class="fas fa-home"></i></a></li>
				<li class="breadcrumb-item active" aria-current="page">Classement</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- en tête de la page -->
	<header class='container'>
		<h1 class='h3'><i class='fa-solid fa-ranking-star'></i>&nbsp;&nbsp;&nbsp;Classement des écoles d'ingénieurs post prépa
		</h1><br>

		<p>
		  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail" onclick="changerTexte(this)">
			+  Voir l'explication
		  </button>
		</p>
		
		<div class="collapse" id="detail" style="margin-left:20px;">
			<p>3 classements sont présentés :
				<ul>
					<li>Le classement du magazine <a href='https://www.letudiant.fr/classements/classement-des-ecoles-d-ingenieurs.html' target=_blank tittle='vers le site de L&apos;Etudiant'>L'Etudiant</a> a évalué 170 écoles en 2024 (169 en 2023, 172 en 2022) sur environ 200 écoles certifiées par la CTI.
						Certaines écoles sont absentes de ce classement, par exemple les ENS, l'Ecole Navale ou l'Ecole de l'Air.
						Pour mémoire, le classement de L'Etudiant comprend aussi bien des écoles post prépa que post bac.
						<br>En 2023, ce classement a changé de méthodologie. Il s'appuie en 2023 sur 11 critère notés de 0 à 10 (de 0 à 5 en 2022). Les Groupes sont désormais depuis 2023 de simples quartiles (A+, A, B et C).
						<br>En 2024, les critères ont encore évolué. Les écoles retenues par L’Etudiant sont notées sur 14 critères avec un total de 123 points maximum. Le développement durable et l’alternance sont notamment renforcés. Pour nous, le résultat de ce classement 2024 est non pertinent et il n’est pas publié sur ce site.
						<br>C'est pourquoi pour le classement de l'Etudiant on privilégie celui de 2022</strong> dans lequel le classement et les groupes d'écoles nous paraissent plus judicieux.
					</li>
					<br>
					<li>Le classement du site <a href='https://www.daur-rankings.com/rankings/' target=_blank tittle='vers le site DAUR'>DAUR Rankings</a> a évalué 185 écoles sous statut étudiant en 2023 et 176 en 2022.
						Ce classement a analysé les écoles à travers 5 critères (sélectivité, employabilité, recherche, international, alumni). Les formules de calul utilisées sont publiées sur le site.
						Certaines écoles sont absentes de ce classement, par exemple les ENS, l'Ecole Navale ou l'Ecole de l'Air.
						Le classement DAUR inclut des écoles post bac et post  prépas.
						<br>Le classement DAUR est considéré comme le plus pertinent du point de vu statistiques basées sur les données de SCEI, de Parcoursup, de la campagne de certification annuelle de la CTI et de l'enquête sur l'insertion professionnelle de la CDEFI.
					</li>
					<br>
					<li>Le classement du magazine <a href='https://etudiant.lefigaro.fr/etudes/ecoles-ingenieurs/classement/' target=_blank tittle='vers le site Le Figaro étudiant'>Le Figaro étudiant</a> a évalué 87 écoles post prépa en 2024.
						Ce classement a analysé les écoles à travers 14 critères répartis en 3 groupes de critères (excellence académique, international, employabilité) donnant une note globale sur 20. Ce classement exclut les écoles purement post bac.
						A noter que dans l'excellence académique, la sélectivité aux concours est un critère pris en compte. Il repose sur le rang moyen des intégrés par rapport au nombre de candidats (données SCEI).
						<br>Seules 87 écoles font partie de ce classement. En complément, le magazine publie d'autres classements : les écoles d'informatique, les écoles en agronomie et les écoles post bac. Ces classements ne sont pas pris en compte dans ce site.
					</li>
				</ul>
			</p>
			<p>Dans tous ces classements présentés sur une seule page, sont présentées uniquement les <strong>écoles accessibles après une prépa</strong> via les voeux sur la plateforme SCEI.</p>
			<br>
		</div>
	</header>

	<main class='container'>

		<!-- gestion des onglets -->
		<nav>
			<div class="nav nav-tabs" id="nav-tab" role="tablist">
				<button class="nav-link active" id="tab-etudiant-2022" data-bs-toggle="tab" data-bs-target="#etudiant-2022" type="button" role="tab" aria-controls="etudiant 2022" aria-selected="true">L'Etudiant<br>2022</button>
				<button class="nav-link" id="tab-etudiant-2023" data-bs-toggle="tab" data-bs-target="#etudiant-2023" type="button" role="tab" aria-controls="etudiant 2023" aria-selected="false">L'Etudiant<br>2023</button>
				<button class="nav-link" id="tab-daur-2022" data-bs-toggle="tab" data-bs-target="#daur-2022" type="button" role="tab" aria-controls="daur 2022" aria-selected="false">DAUR<br>2022</button>
				<button class="nav-link" id="tab-daur-2023" data-bs-toggle="tab" data-bs-target="#daur-2023" type="button" role="tab" aria-controls="daur 2023" aria-selected="false">DAUR<br>2023</button>
				<button class="nav-link" id="tab-figaro" data-bs-toggle="tab" data-bs-target="#figaro" type="button" role="tab" aria-controls="figaro" aria-selected="false">Le Figaro<br>2024</button>
			</div>
		</nav>
		
		<!-- ouverture du 1er onglet -->		
		<div class="tab-content" id="nav-tabContent">
			<div class="tab-pane fade show active" id="etudiant-2022" role="tabpanel" aria-labelledby="tab-etudiant-2022">

<?php
	
		// conexion à la base cpge
		try {
			$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSE");
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// passage au mode exception pour les erreurs
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// exécution de la requête SQL pour le classement de l'Etudiant
		$sql = "SELECT DISTINCT Classement.Ecole, Classement.Rang, Classement.Point, Classement.Groupe, Classement.UrlEcole
				FROM Classement
				INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%', Classement.Ecole,'%')
				WHERE Classement.Ecole IS NOT NULL AND Classement.An = '2022'
 				ORDER BY Classement.Rang;";
		if ($debug) echo "SQL = " . $sql ."<br>";
		try {
			$result = $db->query($sql);

			// affichage de l'en tête du tableau
			echo "<table id='tableau-etudiant-2022'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			$idTableau = '"#tableau-etudiant-2022"';
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='fa-solid fa-download'></i> csv</button></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th>&nbsp;Groupe&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Groupe d&apos;appartenance de l&apos;école est défini par le magasine L&apos;Etudiant selon le nombre de points obtenus par l&apos;école dans leur classement.<br>En 2022<br>A+ : 42 à 58 points<br>A : 34 à 41 points<br>B : 24 à 33 points<br>C : 0 à 23 points<br>En 2023<br>A+ : 97 à 63 points<br>A : 62 à 51 points<br>B : 50 à 44 points<br>C : 0 à 43 points'></i></th>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par points (de 1 à 172).'></i></th>";
			echo "<th>&nbsp;Point&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le nombre de Points attribués est au maximum de 58 en 2022. Il résulte de l&apos;évaluation d&apos;une cinquantaine de critères.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;Site web&nbsp;</th>";
			echo "</tr></thead>";

			$groupeCourant = "";
			$class = "";
			$firstRecord = true;

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// affichage d'un trait plein au changement de Groupe
				if (!$firstRecord) {
					if ($Groupe == $groupeCourant) {
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}

				// affichage de la ligne
				echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;)">';
				echo "<td ".$class." style='text-align:center'>" . $Groupe . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Rang . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Point . "</td>";
				echo "<td ".$class." style='padding-left:10px'><strong>" . $Ecole . "</strong></td>";
				echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlEcole . "' target=_blank>" . $UrlEcole . "</a></td>";
 				echo "</tr>";	

				$groupeCourant = $Groupe;
				$firstRecord = false;
				$class = "";

			}
			echo "</table>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}

	?>
			<!-- fermeture du 1er onglet -->
			</div>

			<!-- ouverture du 2ème onglet -->
			<div class="tab-pane fade" id="etudiant-2023" role="tabpanel" aria-labelledby="tab-etudiant-2023">

	<?php
	
		// conexion à la base cpge
		try {
			$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSE");
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// passage au mode exception pour les erreurs
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// exécution de la requête SQL pour le classement de l'Etudiant
		$sql = "SELECT DISTINCT Classement.Ecole, Classement.Rang, Classement.Point, Classement.Groupe, Classement.UrlEcole
				FROM Classement
				INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%', Classement.Ecole,'%')
				WHERE Classement.Ecole IS NOT NULL AND Classement.An = '2023'
 				ORDER BY Classement.Rang;";
		if ($debug) echo "SQL = " . $sql ."<br>";
		try {
			$result = $db->query($sql);

			// affichage de l'en tête du tableau
			echo "<table id='tableau-etudiant-2023'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			$idTableau = '"#tableau-etudiant-2023"';
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='fa-solid fa-download'></i> csv</button></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th>&nbsp;Groupe&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Groupe d&apos;appartenance de l&apos;école en 2023 est désormais défini par le magasine L&apos;Etudiant comme étant un simple quartile.<br>A+ : 97 à 63 points<br>A : 62 à 51 points<br>B : 50 à 44 points<br>C : 0 à 43 points'></i></th>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par points (de 1 à 169).'></i></th>";
			echo "<th>&nbsp;Point&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le nombre de Points attribués est au maximum de 111 en 2023. Il résulte de l&apos;évaluation de 11 critères.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;Site web&nbsp;</th>";
			echo "</tr></thead>";

			$groupeCourant = "";
			$class = "";
			$firstRecord = true;

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// affichage d'un trait plein au changement de Groupe
				if (!$firstRecord) {
					if ($Groupe == $groupeCourant) {
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}

				// affichage de la ligne
				echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;)">';
				echo "<td ".$class." style='text-align:center'>" . $Groupe . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Rang . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Point . "</td>";
				echo "<td ".$class." style='padding-left:10px'><strong>" . $Ecole . "</strong></td>";
				echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlEcole . "' target=_blank>" . $UrlEcole . "</a></td>";
 				echo "</tr>";	

				$groupeCourant = $Groupe;
				$firstRecord = false;
				$class = "";

			}
			echo "</table>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}

	?>

			<!-- fermeture du deuxième onglet -->
			</div>
			
			<!-- ouverture 3ème onglet -->
			<div class="tab-pane fade" id="daur-2022" role="tabpanel" aria-labelledby="tab-daur-2022">

	<?php

		// exécution de la requête SQL pour le classement DAUR
		$sql = "SELECT DISTINCT DAUR.Ecole, DAUR.Rang, DAUR.Groupe, DAUR.Point, DAUR.UrlEcole
				FROM DAUR
				INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',DAUR.Ecole,'%')
				WHERE DAUR.Ecole IS NOT NULL AND DAUR.An = '2022'
				ORDER BY DAUR.Rang;";
				
		if ($debug) echo "SQL = " . $sql ."<br>";
		try {
			$result = $db->query($sql);

			// affichage de l'en tête du tableau
			echo "<table id='tableau-DAUR-2022'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			$idTableau = '"#tableau-DAUR-2022"';
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='fa-solid fa-download'></i> csv</button></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th>&nbsp;Notation&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2022 est défini par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 56<br>A : 55 à 49 points<br>BBB : 48 à 44<br>BB : 43 à 39<br>B : 39 à 35<br>CCC : 34 à 32<br>CC : 31 à 29<br>C : 29 à 0'></i></th>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 176).'></i></th>";
			echo "<th>&nbsp;Note finale&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La note finale est attribuée à partie de 5 critères détaillés sur le site de DAUR Rankings.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;Site web&nbsp;</th>";
			echo "</tr></thead>";

			$groupeCourant = "";
			$class = "";
			$firstRecord = true;

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// affichage d'un trait plein au changement de Groupe
				if (!$firstRecord) {
					if ($Groupe == $groupeCourant) {
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}

				// affichage de la ligne
				echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;)">';
				echo "<td ".$class." style='text-align:center'>" . $Groupe . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Rang . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Point . "</td>";
				echo "<td ".$class." style='padding-left:10px'><strong>" . $Ecole . "</strong></td>";
 				echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlEcole . "' target=_blank>" . $UrlEcole . "</a></td>";
 				echo "</tr>";	

				$groupeCourant = $Groupe;
				$firstRecord = false;
				$class = "";

			}
			echo "</table>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}
	?>

			<!-- fermeture du 3ème onglet -->
			</div>
			
			<!-- ouverture du 4ème onglet -->
			<div class="tab-pane fade" id="daur-2023" role="tabpanel" aria-labelledby="tab-daur-2023">

	<?php

		// exécution de la requête SQL pour le classement DAUR 2023
		$sql = "SELECT DISTINCT DAUR.Ecole, DAUR.Rang, DAUR.Groupe, DAUR.Point, DAUR.UrlEcole
				FROM DAUR
				INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',DAUR.Ecole,'%')
				WHERE DAUR.Ecole IS NOT NULL AND DAUR.An = '2023'
				ORDER BY DAUR.Rang;";
				
		if ($debug) echo "SQL = " . $sql ."<br>";
		try {
			$result = $db->query($sql);

			// affichage de l'en tête du tableau
			echo "<table id='tableau-DAUR-2023'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			$idTableau = '"#tableau-DAUR-2023"';
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='fa-solid fa-download'></i> csv</button></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th>&nbsp;Notation&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2023 est défini par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 54<br>A : 53 à 47 points<br>BBB : 46 à 41<br>BB : 40 à 37<br>B : 36 à 34<br>CCC : 33 à 31<br>CC : 30 à 28<br>C : 27 à 0'></i></th>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 185).'></i></th>";
			echo "<th>&nbsp;Note finale&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La note finale est attribuée à partie de 5 critères détaillés sur le site de DAUR Rankings.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;Site web&nbsp;</th>";
			echo "</tr></thead>";

			$groupeCourant = "";
			$class = "";
			$firstRecord = true;

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// affichage d'un trait plein au changement de Groupe
				if (!$firstRecord) {
					if ($Groupe == $groupeCourant) {
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}

				// affichage de la ligne
				echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;)">';
				echo "<td ".$class." style='text-align:center'>" . $Groupe . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Rang . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Point . "</td>";
				echo "<td ".$class." style='padding-left:10px'><strong>" . $Ecole . "</strong></td>";
 				echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlEcole . "' target=_blank>" . $UrlEcole . "</a></td>";
 				echo "</tr>";	

				$groupeCourant = $Groupe;
				$firstRecord = false;
				$class = "";

			}
			echo "</table>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}
	?>
			<!-- fermeture du 4ème onglet et division englobante -->
			</div>
			
			<!-- ouverture du 5ème onglet -->
			<div class="tab-pane fade" id="figaro" role="tabpanel" aria-labelledby="tab-figaro">

	<?php

		// exécution de la requête SQL pour le classement Figaro 2024
		$sql = "SELECT DISTINCT Figaro.Ecole, Figaro.Rang, Figaro.Point, Figaro.UrlFigaro
				FROM Figaro
				INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',Figaro.Ecole,'%')
				WHERE Figaro.Ecole IS NOT NULL
				ORDER BY Figaro.Rang, Figaro.Ecole;";
				
		if ($debug) echo "SQL = " . $sql ."<br>";
		try {
			$result = $db->query($sql);

			// affichage de l'en tête du tableau
			echo "<table id='tableau-figaro-2024'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			$idTableau = '"#tableau-figaro-2024"';
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='fa-solid fa-download'></i> csv</button></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 87).'></i></th>";
			echo "<th>&nbsp;Note&nbsp;<br><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Les écoles sont notées en 2024 de 0 à 20. Cette note résulte de l&apos;évaluation de 14 critères.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;Descriptif de l'école par Le Figaro</th>";
			echo "</tr></thead>";

			$class = "";

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				$class = "";
// 				$class = " class='nouvelEcole'";

				// affichage de la ligne
				echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;)">';
				echo "<td ".$class." style='text-align:center'>" . $Rang . "</td>";
				echo "<td ".$class." style='text-align:center'>" . $Point . "</td>";
				echo "<td ".$class." style='padding-left:10px'><strong>" . $Ecole . "</strong></td>";
 				echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlFigaro . "' target=_blank>" . $Ecole . "</a></td>";
 				echo "</tr>";	
			}
			echo "</table>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}

		// fermeture de la base
		if (isset($result)) {$result->closeCursor();}
		$db = null;
	?>

			<!-- fermeture du 5ème onglet et division englobante -->
			</div>
		</div>
	</main>

	<!-- bas de page -->
	<footer class="container" style='margin-top:40px; margin-bottom:80px;'>
		<br>
		<div class='d-flex justify-content-center'>
			<a class="btn btn-primary" href="#">&uarr; Haut de liste</a>
		</div>
	</footer>

	<?php
		// librairies javascript nécessaires à l'application (popper + bootstrap)
		include "php/librairie.php";
	?>

	<!-- activation tooltip Bootstrap 5 -->
	<script>
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl)
			})		
	</script>

	<!-- navigation -->
	<script>
		// pour zoomer sur une école
		function zoom(ecole) {
			<?php
 				echo "window.location.href='resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php?origine=classement&recherche=' + ecole";
			?>
		}
		
		// pour changer le texte et le symbole du bouton détail
		function changerTexte(bouton) {
			if (bouton.innerText.indexOf("Voir") == -1) {
				bouton.innerText = "+  Voir l'explication";
			} else {
				bouton.innerText = "-  Masquer l'explication";
			}
		}
	</script>
	
	<?php
		// fonction d'export des tableaus HTML en CSV
		include "js/tableToCSV.js";
	?>

  </body>
</html>