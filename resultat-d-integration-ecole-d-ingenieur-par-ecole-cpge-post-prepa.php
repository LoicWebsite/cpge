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
    <meta name="description" content="Statistique SCEI d'admissions par école aux écoles d'ingénieurs post prépa CPGE">

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Admissions en écoles d'ingénieurs par école</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>
	
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
				<li class="breadcrumb-item"><a href="#" onclick="questionnaire()">Ecole</a></li>
				<li class="breadcrumb-item active" aria-current="page">Statistiques</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- en tête du tableau -->



	<?php

		// titre de la page
		echo "<header class='container'>";
		if (($ecole <> "") and ($ecole <> "toutes")) {
			echo "<h1 class='h3'><i class='fa-solid fa-graduation-cap'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions<br/>pour l'école ".$ecole."</h1>";
		} else {
			echo "<h1 class='h3'><i class='fa-solid fa-graduation-cap'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions</h1>";
		}
		echo "<br/>";
		echo "</header>";

		// section principale de la page
		echo "<main class='container-fluid'>";

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
		$where = "";
		if (($ecole <> "") and ($ecole <> "toutes")) {
			$where = ' WHERE Ecole="' . $ecole . '"';
		} elseif ($recherche <> "") {
			$where = ' WHERE Ecole LIKE "%'.$recherche.'%"';				
		}
		$where = $where . " ORDER BY Ecole ASC, Filiere ASC;";
		
		// exécution de la requête SQL
		$sql = "SELECT Filiere, Concours, Ecole FROM Ecole" . $where;
		if ($debug) echo "SQL Ecole = " . $sql ."<br/>";
		try {
			$result = $db->query($sql);

			// affichage de l'en tête du tableau
			echo "<table class='table-hover' style='width:100%;'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une ligne pour voir le détail de cette école.<br/>
				<span style='color:darkslategray;'>En <strong>noir</strong> le rang médian</span>, <span style='color:#0000FF'>en <strong>bleu</strong> le rang du dernier</span>.</small></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th  style='position:static;' colspan=3></th>";
			echo "<th  style='position:static;' colspan=6>Intégrés : Rang médian / Inscrits&nbsp;&nbsp;<span style='font-weight:normal'>(en noir)</span><br/>Intégrés : Rang dernier / Inscrits&nbsp;&nbsp;<span style='font-weight:normal'>(en bleu)</span><br/>
								<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Lorsque le rang du dernier admis est connu c&apos;est celui-ci qui est affiché (en bleu).<br/>
								<br/>&bull; Sinon c&apos;est le rang médian qui est affiché (en noir).<br/>
								<br/>&bull; Le rang du dernier appelé a été supprimé des statistiques SCEI à partir de 2018. Il a été remplacé par le rang médian et le rang moyen.<br/>
								<br/>&bull; Seuls certains concours continuent à publier le rang du dernier admis par école (voir la note d&apos;information &#x24D8; en page d&apos;accueil pour plus de détails).'></i></th>";
			echo "<th  style='position:static;' colspan=2>Intégrés : Rang dernier / Inscrits<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='En 2017 et 2016 le rang du dernier appelé est sytématiquement renseigné dans SCEI. C&apos;est lui qui est affiché.'></i></th>";
			echo "</tr>";
			echo "<tr>";
			echo "<th>Ecole<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='&bull;Lorsqu&apos;une école a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>
								<br/>&bull; Lorsque plusieurs écoles ont fusionné, les différentes écoles apparaissent séparément avant la fusion.<br/>
								<br/>&bull; Lorsqu&apos;une école change de concours, elle apparaît soit dans le nouveau concours soit dans l&apos;ancien suivant la date.<br/>
								<br/>&bull; A noter que le nom affiché est celui qui apparaît dans SCEI.'></i></th>";
			echo "<th>&nbsp;Filière&nbsp;</th>";
			echo "<th>Concours<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Lorsqu&apos;un concours a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>Exemple CCP devenu CCINP en 2019.'></i></th>";
			echo "<th>2023</th>";
			echo "<th>2022</th>";
			echo "<th>2021</th>";
			echo "<th>2020</th>";
			echo "<th>2019</th>";
			echo "<th>2018</th>";
			echo "<th>2017</th>";
			echo "<th>2016</th>";
			echo "</tr>";
			echo "</thead>";

			$ecoleCourante = "";
			$class = "";
			$firstRecord = true;

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// affichage d'un trait plein au changement d'école
				if (!$firstRecord) {
//					if ($Ecole == $ecoleCourante) {
					if (strcasecmp($Ecole, $ecoleCourante) == 0) {		// ne tient pas compte des minuscules ou majuscules
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}
				
				// affichage du début de la ligne du tableau
				echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;,&apos;'.$Filiere.'&apos;,&apos;'.$Concours.'&apos;)">';
				echo "<td".$class.">" . $Ecole . "</td>";
				echo "<td".$class." style='text-align:center'><strong>". strtoupper($Filiere) ."</strong></td>";
				echo "<td".$class.">".$Concours ."</td>";

				// affichage des stats 2023
				$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2023";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
							} else {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".$Integre." : <strong>".$RangMedian."</strong></span> / ".$Inscrit."</td>";
							}
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2023 : " . $erreur->getMessage();
				}

				// affichage des stats 2022
				$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2022";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
							} else {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".$Integre." : <strong>".$RangMedian."</strong></span> / ".$Inscrit."</td>";
							}
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2022 : " . $erreur->getMessage();
				}	

				// affichage des stats 2021
				$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2021";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
							} else {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".$Integre." : <strong>".$RangMedian."</strong></span> / ".$Inscrit."</td>";
							}
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2021 : " . $erreur->getMessage();
				}						

				// affichage des stats 2020
				$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2020";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
							} else {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".$Integre." : <strong>".$RangMedian."</strong></span> / ".$Inscrit."</td>";
							}
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2020 : " . $erreur->getMessage();
				}					
				
				// affichage des stats 2019
				$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2019";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
							} else {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".$Integre." : <strong>".$RangMedian."</strong></span> / ".$Inscrit."</td>";
							}
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2019 : " . $erreur->getMessage();
				}						

				// affichage des stats 2018
				$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2018";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
							} else {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".$Integre." : <strong>".$RangMedian."</strong></span> / ".$Inscrit."</td>";
							}
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2018 : " . $erreur->getMessage();
				}					

				// affichage des stats 2017
				$sqlNote = 'SELECT Inscrit, Integre, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2017";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2017 : " . $erreur->getMessage();
				}					

				// affichage des stats 2016
				$sqlNote = 'SELECT Inscrit, Integre, Dernier FROM Note WHERE Ecole="'.$Ecole.'" AND Filiere="'.$Filiere.'" AND Concours="'.$Concours.'" AND An="2016";';
				if ($debug) {echo "SQL Note = " . $sqlNote;}
				try {
					$resultNote = $db->query($sqlNote);
					if ($resultNote->rowCount() == 0) {
						echo "<td".$class." style='text-align:center'></td>";
					} else {
						while ($rowNote = $resultNote->fetch(PDO::FETCH_ASSOC)) {
							extract($rowNote);
							echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".$Integre." : <strong>".$Dernier."</strong></span> / ".$Inscrit."</td>";
						}
					}
				}
				catch(PDOException $erreur)	{
					echo "Erreur SELECT Note 2016 : " . $erreur->getMessage();
				}	
				
				echo "</tr>\n";
				$ecoleCourante = $Ecole;
				$firstRecord = false;
				$class = "";
			}
			echo "</table>";
			echo "</main>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Ecole : " . $erreur->getMessage();
		}

		// fermeture de la base
		if (isset($result)) {$result->closeCursor();}
		$db = null;
	
	?>

	<!-- naivigation en bas de page -->
	<footer style='margin-top:40px; margin-bottom:80px;'>
		<br/>

		<!-- retour vers le formulaire -->
		<div class='d-flex justify-content-center'>
			<button class="btn btn-primary" onclick="questionnaire()">&larr; Retour au choix de l'école</button>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<a class="btn btn-primary" href="#">&uarr; Haut de liste</a>

	<?php
		
		// retour vers le classement
		$origine="";
		if (isset($_GET['origine']))  {
			$origine = trim($_GET['origine']);
		}
		if ($origine == "classement") {
			echo "&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<button class='btn btn-primary' onclick='javascript:window.history.back();'>&rarr; Retour au classement</button>";
		}
	?>
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
		function zoom(ecole,filiere,concours) {
			<?php
 				echo "window.location.href='detail-resultat-admission-par-ecole.php?reference=" . $reference . "&an=toutes&filiere=' + filiere + '&concours=' + concours + '&ecole=' + ecole";
			?>
		}

		// pour retourner en arrière dans l'historique du navigateur
		function questionnaire() {
			<?php
				echo 'window.location.href="statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php?ecole=' . $ecole . '&recherche=' . $recherche . '";';
			?>
		}
	</script>	
  </body>
</html>