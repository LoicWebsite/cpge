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
				<li class="breadcrumb-item"><a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php"><i class="bi bi-house-door-fill"></i></a></li>
				<li class="breadcrumb-item active" aria-current="page">Classement</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- en tête de la page -->
	<header class='container'>
		<h1 class='h3'><i class="bi bi-clipboard-data"></i></i>&nbsp;&nbsp;&nbsp;Classement des écoles d'ingénieurs post prépa
		</h1><br>

		<p>
		  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail" onclick="changerTexte(this)">
			+  Voir l'explication
		  </button>
		</p>
		
		<div class="collapse" id="detail" style="margin-left:20px;">
			<p>3 classements sont présentés :
				<ul>
					<li><strong>Le classement du site <a href='https://www.daur-rankings.com/rankings/' target=_blank tittle='vers le site DAUR'>DAUR</a></strong> a évalué 190 écoles sous statut étudiant en 2025, 185 en 2024 et 176 en 2023.
						En 2025, ce classement a analysé les écoles à travers 6 critères (niveau moyen, sélectivité, attractivité, employabilité, recherche, entrepreneuriat) et a publié les 32 premières. Les formules de calul utilisées sont publiées sur le site.
						Certaines écoles sont absentes de ce classement, par exemple les ENS, l'Ecole Navale ou l'Ecole de l'Air.
						Le classement DAUR inclut certaines écoles post bac et post  prépas.
						<br>Le classement DAUR est considéré comme le plus pertinent du point de vu statistiques basées sur les données de SCEI, de Parcoursup, de la campagne de certification annuelle de la CTI et de l'enquête sur l'insertion professionnelle de la CDEFI.
						<br><strong>Autres classements</strong> : le site DAUR publient d'autres classements très riches mais non repris sur ce site.
							<ul>
								<li><a href='https://www.daur-rankings.com/rankings/degrees/2025/1_1_agriculture/initial' target=_blank tittle='vers le site de DAUR'>Le classement des diplômes d'ingénieur</a> et non pas seulement le classement des écoles qui peuvent délivrer plusieurs diplômes par exemple un diplôme généraliste et des diplômes de spécialités. Ce classement des diplômes d'ingénieurs est réalisé par secteur (53 secteurs différents recensés).</li>
								<li><a href='https://www.daur-rankings.com/blog/attractivity-engineering-2025' target=_blank tittle='vers le site de DAUR'>L'attractivité des écoles</a> en fonction des voeux et des désistements des préparationnaires.</li>
								<li><a href='https://www.daur-rankings.com/rankings/prepa_degrees/2025/mp/france' target=_blank tittle='vers le site de DAUR'>Le classement des CPGE scientifiques</a> par filière. Il est basé sur les résultats publiés dans SCEI.</li>
							</ul>
					</li>
					<br>
					<li><strong>Le classement du magazine <a href='https://etudiant.lefigaro.fr/etudes/ecoles-ingenieurs/classement/' target=_blank tittle='vers le site Le Figaro étudiant'>Le Figaro étudiant</a></strong> a évalué 87 écoles post prépa en 2025 et 2024.
						Les écoles d’ingénieurs sont classées en fonction d’une note générale sur 20. Cette note générale est la moyenne pondérée de trois notes évaluant leur excellence académique (coefficient 2), leur ouverture à l’international (coefficient 1) et l’emploi, ou réussite professionnelle des diplômés (coefficient 3). La plupart des indicateurs sont construits sur la base des données certifiées de la CTI. Ce classement exclut les écoles purement post bac.
						Il repose sur le rang moyen des intégrés par rapport au nombre de candidats (données SCEI).
						<br>Seules 87 écoles font partie de ce classement. En complément, le magazine publie d'autres classements : les écoles d'informatique, les écoles en agronomie et les écoles post bac. Ces classements ne sont pas pris en compte dans ce site.
					</li>
					<br>
					<li><strong>Le classement du magazine <a href='https://www.letudiant.fr/classements/classement-des-ecoles-d-ingenieurs.html' target=_blank tittle='vers le site de L&apos;Etudiant'>L'Etudiant</a></strong> a évalué 169 écoles en 2023 (172 en 2022) sur environ 200 écoles certifiées par la CTI.
						Certaines écoles sont absentes de ce classement, par exemple les ENS, l'Ecole Navale ou l'Ecole de l'Air.
						Pour mémoire, le classement de L'Etudiant comprend aussi bien des écoles post prépa que post bac.
						<br>En 2023, ce classement a changé de méthodologie. Il s'appuie en 2023 sur 11 critère notés de 0 à 10 (de 0 à 5 en 2022). Les Groupes sont désormais depuis 2023 de simples quartiles (A+, A, B et C).
						<br>En 2024, les critères ont encore évolué. Les écoles retenues par L’Etudiant sont notées sur 14 critères avec un total de 123 points maximum. Le développement durable et l’alternance sont notamment renforcés. Pour nous, les classements depuis 2024 sont non pertinents et ils ne sont plus publiés sur ce site.
						<br>C'est pourquoi pour le classement de l'Etudiant on privilégie celui de 2022</strong> dans lequel le classement et les groupes d'écoles nous paraissent plus judicieux. Les classements après 2023 ne sont plus publiés pour les mêmes raison (pertinence faible).
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
				<button class="nav-link active" id="tab-daur-2025" data-bs-toggle="tab" data-bs-target="#daur-2025" type="button" role="tab" aria-controls="daur 2025" aria-selected="true">DAUR<br>2025</button>
				<button class="nav-link" id="tab-daur-2024" data-bs-toggle="tab" data-bs-target="#daur-2024" type="button" role="tab" aria-controls="daur 2024" aria-selected="false">DAUR<br>2024</button>
				<button class="nav-link" id="tab-daur-2023" data-bs-toggle="tab" data-bs-target="#daur-2023" type="button" role="tab" aria-controls="daur 2023" aria-selected="false">DAUR<br>2023</button>
				<button class="nav-link" id="tab-figaro-2025" data-bs-toggle="tab" data-bs-target="#figaro-2025" type="button" role="tab" aria-controls="figaro-2025" aria-selected="false">Le Figaro<br>2025</button>
				<button class="nav-link" id="tab-figaro-2024" data-bs-toggle="tab" data-bs-target="#figaro-2024" type="button" role="tab" aria-controls="figaro-2024" aria-selected="false">Le Figaro<br>2024</button>
				<button class="nav-link" id="tab-etudiant-2023" data-bs-toggle="tab" data-bs-target="#etudiant-2023" type="button" role="tab" aria-controls="etudiant 2023" aria-selected="false">L'Etudiant<br>2023</button>
				<button class="nav-link" id="tab-etudiant-2022" data-bs-toggle="tab" data-bs-target="#etudiant-2022" type="button" role="tab" aria-controls="etudiant 2022" aria-selected="false">L'Etudiant<br>2022</button>
			</div>
		</nav>

		<!-- container de tous les onglets -->
		<div class="tab-content" id="nav-tabContent">
			<?php
				// conexion à la base concours cpge
				try {
					$db = openDatabase();
				}
				catch(PDOException $erreur)	{
					die('Erreur connexion base : ' . $erreur->getMessage());
				}
			?>

			<!-- ouverture de l'onglet DAUR 2025 -->
			<div class="tab-pane fade show active" id="daur-2025" role="tabpanel" aria-labelledby="tab-daur-2025">
			<?php
				// exécution de la requête SQL pour le classement DAUR 2025 (sur data 2024)
				$sql = "SELECT DISTINCT DAUR.Ecole, DAUR.Rang, DAUR.Groupe, DAUR.Point, DAUR.UrlEcole
						FROM DAUR
						INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',DAUR.Ecole,'%')
						WHERE DAUR.Ecole IS NOT NULL AND DAUR.An = '2024'
						ORDER BY DAUR.Rang;";
				
				if ($debug) echo "SQL = " . $sql ."<br>";
				try {
					$result = $db->query($sql);

					// affichage de l'en tête du tableau
					echo "<table id='tableau-daur-2025'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-daur-2025","notation;rang;note finale;école;site web"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Notation&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2025 est défini par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 60<br>AA : 59 à 47'></i></th>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 28). Lorsque plusieurs écoles ont la même note finale, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Note finale&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='La note finale est attribuée à partie de 6 critères détaillés sur le site de DAUR Rankings.'></i></th>";
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
			<!-- fermeture de l'onglet DAUR 2025 -->
			</div>
	
			<!-- ouverture de l'onglet DAUR 2024 (sur data de 2023) -->
			<div class="tab-pane fade" id="daur-2024" role="tabpanel" aria-labelledby="tab-daur-2024">
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
					echo "<table id='tableau-daur-2024'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-daur-2024","notation;rang;note finale;école;site web"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Notation&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2024 est défini par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 54<br>A : 53 à 47 points<br>BBB : 46 à 41<br>BB : 40 à 37<br>B : 36 à 34<br>CCC : 33 à 31<br>CC : 30 à 28<br>C : 27 à 0'></i></th>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 166). Lorsque plusieurs écoles ont la même note finale, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Note finale&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='La note finale est attribuée à partie de 5 critères détaillés sur le site de DAUR Rankings.'></i></th>";
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
			<!-- fermeture de l'onglet DAUR 2024 -->
			</div>

			<!-- ouverture de l'onglet DAUR 2023 (sur data de 2022) -->
			<div class="tab-pane fade" id="daur-2023" role="tabpanel" aria-labelledby="tab-daur-2023">
			<?php
				// exécution de la requête SQL pour le classement DAUR (DAUR 2023 est classé en 2022, date des données)
				$sql = "SELECT DISTINCT DAUR.Ecole, DAUR.Rang, DAUR.Groupe, DAUR.Point, DAUR.UrlEcole
						FROM DAUR
						INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',DAUR.Ecole,'%')
						WHERE DAUR.Ecole IS NOT NULL AND DAUR.An = '2022'
						ORDER BY DAUR.Rang;";
				
				if ($debug) echo "SQL = " . $sql ."<br>";
				try {
					$result = $db->query($sql);

					// affichage de l'en tête du tableau
					echo "<table id='tableau-daur-2023'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-daur-2023","notation,rang,note finale,école,site web"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Notation&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2023 est défini par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 56<br>A : 55 à 49 points<br>BBB : 48 à 44<br>BB : 43 à 39<br>B : 39 à 35<br>CCC : 34 à 32<br>CC : 31 à 29<br>C : 29 à 0'></i></th>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 159). Lorsque plusieurs écoles ont la même note finale, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Note finale&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='La note finale est attribuée à partie de 5 critères détaillés sur le site de DAUR Rankings.'></i></th>";
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
			<!-- fermeture de l'onglet DAUR 2023 -->
			</div>

			<!-- ouverture de l'onglet FIGARO 2025 -->
			<div class="tab-pane fade" id="figaro-2025" role="tabpanel" aria-labelledby="tab-figaro-2025">
			<?php
				// exécution de la requête SQL pour le classement Figaro 2025
				$sql = "SELECT DISTINCT Figaro.Ecole, Figaro.Rang, Figaro.Point, Figaro.UrlFigaro
						FROM Figaro
						INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',Figaro.Ecole,'%')
						WHERE Figaro.Ecole IS NOT NULL AND Figaro.An = '2025'
						ORDER BY Figaro.Rang, Figaro.Ecole;";
				
				if ($debug) echo "SQL = " . $sql ."<br>";
				try {
					$result = $db->query($sql);

					// affichage de l'en tête du tableau
					echo "<table id='tableau-figaro-2025'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-figaro-2025","rang,note,école,descriptif Le Figaro"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 87). Lorsque plusieurs écoles ont la même note, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Note&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Les écoles sont notées en 2025 de 0 à 20. Cette note résulte de l&apos;évaluation de 14 critères.'></i></th>";
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
			?>
			<!-- fermeture de l'onglet FIGARO 2025 -->
			</div>
			
			<!-- ouverture de l'onglet FIGARO 2024 -->
			<div class="tab-pane fade" id="figaro-2024" role="tabpanel" aria-labelledby="tab-figaro-2024">
			<?php
				// exécution de la requête SQL pour le classement Figaro 2024
				$sql = "SELECT DISTINCT Figaro.Ecole, Figaro.Rang, Figaro.Point, Figaro.UrlFigaro
						FROM Figaro
						INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%',Figaro.Ecole,'%')
						WHERE Figaro.Ecole IS NOT NULL AND Figaro.An = '2023'
						ORDER BY Figaro.Rang, Figaro.Ecole;";
				
				if ($debug) echo "SQL = " . $sql ."<br>";
				try {
					$result = $db->query($sql);

					// affichage de l'en tête du tableau
					echo "<table id='tableau-figaro-2024'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-figaro-2024","rang,note,école,descriptif Le Figaro"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 87). Lorsque plusieurs écoles ont la même note, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Note&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Les écoles sont notées en 2024 de 0 à 20. Cette note résulte de l&apos;évaluation de 14 critères.'></i></th>";
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
			?>
			<!-- fermeture de l'onglet FIGARO 2024 -->
			</div>

			<!-- ouverture de l'onglet ETUDIANT 2023 -->
			<div class="tab-pane fade" id="etudiant-2023" role="tabpanel" aria-labelledby="tab-etudiant-2023">
			<?php
				// exécution de la requête SQL pour le classement de l'Etudiant
				$sql = "SELECT DISTINCT Classement.Ecole, Classement.Rang, Classement.Point, Classement.Groupe, Classement.UrlEtudiant, Classement.UrlEcole
						FROM Classement
						INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%', Classement.Ecole,'%')
						WHERE Classement.Ecole IS NOT NULL AND Classement.An = '2023'
						ORDER BY Classement.Rang;";
				if ($debug) echo "SQL = " . $sql ."<br>";
				try {
					$result = $db->query($sql);

					// affichage de l'en tête du tableau
					echo "<table id='tableau-etudiant-2023'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-etudiant-2023","groupe,rang,point,école,site web"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Groupe&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Groupe d&apos;appartenance de l&apos;école en 2023 est désormais défini par le magasine L&apos;Etudiant comme étant un simple quartile.<br>A+ : 97 à 63 points<br>A : 62 à 51 points<br>B : 50 à 44 points<br>C : 0 à 43 points'></i></th>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par points (de 1 à 153). Lorsque plusieurs écoles ont les mêmes points, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Point&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le nombre de Points attribués est au maximum de 111 en 2023. Il résulte de l&apos;évaluation de 11 critères.'></i></th>";
					echo "<th>&nbsp;Ecole&nbsp;</th>";
					echo "<th>&nbsp;Site web&nbsp;</th>";
					echo "<th>&nbsp;Fiche info de L'Etudiant&nbsp;</th>";
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
						echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlEtudiant . "' target=_blank>Fiche " . $Ecole . "</a></td>";
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
			<!-- fermeture de l'onglet ETUDIANT 2023 -->
			</div>

			<!-- ouverture de l'onglet ETUDIANT 2022 -->		
			<div class="tab-pane fade" id="etudiant-2022" role="tabpanel" aria-labelledby="tab-etudiant-2022">
			<?php
				// exécution de la requête SQL pour le classement de l'Etudiant
				$sql = "SELECT DISTINCT Classement.Ecole, Classement.Rang, Classement.Point, Classement.Groupe, Classement.UrlEtudiant, Classement.UrlEcole
						FROM Classement
						INNER JOIN Ecole ON Ecole.Ecole LIKE CONCAT('%', Classement.Ecole,'%')
						WHERE Classement.Ecole IS NOT NULL AND Classement.An = '2022'
						ORDER BY Classement.Rang;";
				if ($debug) echo "SQL = " . $sql ."<br>";
				try {
					$result = $db->query($sql);

					// affichage de l'en tête du tableau
					echo "<table id='tableau-etudiant-2022'>";
					echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
					$idTableau = '"#tableau-etudiant-2022","groupe,rang,point,école,site web"';
					echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></caption>";
					echo "<thead class='text-center'>";
					echo "<tr>";
					echo "<th>&nbsp;Groupe&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Groupe d&apos;appartenance de l&apos;école est défini par le magasine L&apos;Etudiant selon le nombre de points obtenus par l&apos;école dans leur classement.<br>En 2022<br>A+ : 42 à 58 points<br>A : 34 à 41 points<br>B : 24 à 33 points<br>C : 0 à 23 points<br>En 2023<br>A+ : 97 à 63 points<br>A : 62 à 51 points<br>B : 50 à 44 points<br>C : 0 à 43 points'></i></th>";
					echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par points (de 1 à 156). Lorsque plusieurs écoles ont les mêmes points, elles partagent alors le même rang.'></i></th>";
					echo "<th>&nbsp;Point&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le nombre de Points attribués est au maximum de 58 en 2022. Il résulte de l&apos;évaluation d&apos;une cinquantaine de critères.'></i></th>";
					echo "<th>&nbsp;Ecole&nbsp;</th>";
					echo "<th>&nbsp;Site web&nbsp;</th>";
					echo "<th>&nbsp;Fiche info de L'Etudiant&nbsp;</th>";
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
						echo "<td ".$class." style='padding-left:10px'><a href='" . $UrlEtudiant . "' target=_blank>Fiche " . $Ecole . "</a></td>";
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
			<!-- fermeture de l'onglet ETUDIANT 2022 et division englobante -->
			</div>

			<?php
				// fermeture de la base
				if (isset($result)) {$result->closeCursor();}
				$db = null;
			?>
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
	
	<script>
	<?php
		// fonction d'export des tableaus HTML en CSV
		include "js/tableToCSV.js";
	?>
	</script>

  </body>
</html>