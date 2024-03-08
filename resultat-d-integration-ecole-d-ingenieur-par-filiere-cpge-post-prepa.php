<!doctype html>

<?php

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// récupération de l'ordre de tri
	$tri="ecole";
	if (isset($_GET['tri']))  {
		if (($_GET['tri'] == "selectivite") or ($_GET['tri'] == "ecole")) {
			$tri = trim($_GET['tri']);
		}
	}

	// fonctions communes du site
	include "php/fonctionConcours.php";
?>

<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Statistique SCEI d'admissions par filière aux écoles d'ingénieurs post prépa CPGE">

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Admissions en écoles d'ingénieurs par filière</title>

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
				<li class="breadcrumb-item"><a href="#" onclick="questionnaire()">Filière</a></li>
				<li class="breadcrumb-item active" aria-current="page">Statistiques</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- en tête du tableau -->

	<?php
	
		// titre de la page
		echo "<header class='container'>";
		echo "<h1 class='h3'><i class='fa-solid fa-building-columns'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions " . strtoupper($filiere);

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
		$where = " WHERE An<>'' AND An<>0 ";
		if (($filiere <> "") and ($filiere <> "toutes")) {
			$where = $where . " AND Filiere='" . $filiere . "'";
		}
		if (($concours <> "") and ($concours <> "tous")) {
			$where = $where . " AND Concours='" . $concours . "'";
		}
		if (($reference <> "") and ($reference <> "0") and ($reference <> "toutes")) {
			$where = $where . " AND An='" . $reference . "'" ;
		}
		if (($ecole <> "") and ($ecole <> "toutes")) {
			$where = $where . " AND Ecole='" . supprimerApostrophe($ecole) . "'";
		}

		// construction de la clause ORDER
		$order = " ORDER BY Filiere ASC, Concours ASC, Ecole ASC, An DESC;";
		if ($tri == "selectivite") {
			$order = " ORDER BY Selectivite ASC, SelectiviteMediane ASC, Concours ASC, Ecole ASC;";
		}
		
		// exécution de la requête SQL
		$sql = "SELECT  Filiere,
						Concours,
						Ecole,
						An,
						Place,
						Inscrit,
						Classe,
						Integre,
						RangMedian,						
						Dernier,
						Dernier / Inscrit AS Selectivite,
						RangMedian / Inscrit AS SelectiviteMediane
				FROM Note" . $where . $order;
		if ($debug) echo "SQL = " . $sql ."<br/>";
		try {
			$result = $db->query($sql);

			// affichage du titre
			if (($concours <> "tous") and ($concours <> "")) {
				if (($ecole <> "") and ($ecole <> "toutes")) {
					echo " pour l'école " . $ecole;
				} else {
					echo " pour le concours " . $concours;
				}
				echo "<br/>";
				if (($reference <> "toutes") and ($reference <> 0) and ($reference <> '')) {
					echo " en " . $reference;
				} else {
					echo " de 2016 à 2023";
				}
			} else {
				echo "<br/>";
				if (($reference <> "toutes") and ($reference <> 0) and ($reference <> '')) {
					echo " en " . $reference;
				} else {
					echo " de 2016 à 2023";
				}
			}
			echo "</h1><br/>";
			echo "</header>";

			// section principale de la page
			echo "<main class='container-fluid'>";

			// boutons de tri
			echo "<div class='d-flex justify-content-between'>";
			if ($tri == 'ecole') {
				echo "<button id='ecole' type='button' class='btn btn-secondary btn-sm' disabled onclick='triEcole()'>Trier par école &darr;</button>";
				echo "<button id='selectivite' type='button' class='btn btn-secondary btn-sm' onclick='triSelectivite()'>Trier par sélectivité &darr;</button>";
			} else {
				echo "<button id='ecole' type='button' class='btn btn-secondary btn-sm' onclick='triEcole()'>Trier par école &darr;</button>";
				echo "<button id='selectivite' type='button' class='btn btn-secondary btn-sm' disabled onclick='triSelectivite()'>Trier par sélectivité &darr;</button>";
			}
			echo "</div><br/>";

			// affichage de l'en tête du tableau
			echo "<table id='tableau'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='fa fa-mouse-pointer' aria-hidden='true'></i>&nbsp; sur une ligne pour voir le détail de cette école.</small></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			if (($filiere == "toute") or ($filiere == "")) {
				echo "<th>&nbsp;Filiere&nbsp;</th>";
			}
			if (($concours == "tous") or ($concours == "")) {
				echo "<th>&nbsp;Concours&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Lorsqu&apos;un concours a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>Exemple CCP devenu CCINP en 2019.'></i></th>";
			}
			echo "<th>&nbsp;Ecole&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Lorsqu&apos;une école a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>
											<br/>&bull; Lorsque plusieurs écoles ont fusionné, les différentes écoles apparaissent séparément avant la fusion.<br/>
											<br/>&bull; Lorsqu&apos;une école change de concours, elle apparaît soit dans le nouveau concours soit dans l&apos;ancien suivant la date.<br/>
											<br/>&bull; A noter que le nom affiché est celui qui apparaît dans SCEI.'></i></th>";
			echo "<th>&nbsp;Année&nbsp;</th>";
			echo "<th>&nbsp;Places&nbsp;</th>";
			echo "<th>&nbsp;Inscrits&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-html='true' title='Le nombre d&apos;inscrits est soit celui de l&apos;école soit par défaut celui du concours.'></i></th>";
			echo "<th>&nbsp;Integrés&nbsp;</th>";
			echo "<th>&nbsp;Rang median&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Depuis 2018 les statistiques SCEI affichent le rang médian et le rang moyen. Pour simplifier la lecture ici, seul le rang médian est affiché.'></i></th>";
			echo "<th>&nbsp;Sélectivité médiane&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Sélectivité médiane = Rang médian des admis / Nombre d&apos;inscrits'></i></th>";
			echo "<th>&nbsp;Rang dernier&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Le rang du dernier appelé a été supprimé des statistiques SCEI à partir de 2018.<br/>Il a été remplacé par le rang médian et le rang moyen.<br/>
					<br/>&bull; Seuls certains concours continuent à publier le rang du dernier admis par école (voir la note d&apos;information &#x24D8; en page d&apos;accueil pour plus de détails).'></i></th>";
			echo "<th>&nbsp;Sélectivité&nbsp;<br/><i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Sélectivité = Rang du dernier admis / Nombre d&apos;inscrits'></i></th>";
			echo "</tr></thead>";

			$ecoleCourante = "";
			$class = "";
			$firstRecord = true;

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// coloration de la ligne si le rang visé est inférieur au rang du dernier ou au rang médian
 				$style = "";
//  				if ($Dernier <> "") {
//  					if ($rang <= $Dernier) {
//  						$style = " style=background-color:lemonchiffon;";
//  					}
//  				} elseif ($RangMedian <> "") {
// 					if ($rang <= $RangMedian) { 
// 						$style = " style=background-color:lemonchiffon;";
// 					}
// 				}

				// affichage d'un trait plein au changement d'école
				if (!$firstRecord) {
//					if ($Ecole == $ecoleCourante) {
					if (strcasecmp($Ecole, $ecoleCourante) == 0) {		// ne tient pas compte des minuscules ou majuscules
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}
				
				// affichage de la ligne
				echo '<tr '.$style.' ondblclick="zoom(&apos;'.supprimerApostrophe($Ecole).'&apos;,&apos;'.$An.'&apos;)">';
				if (($filiere == "toute") or ($filiere == "")) {
					echo "<td>". strtoupper($Filiere) ."</td>";
				}
				if (($concours == "tous") or ($concours == "")) {
					echo "<td".$class.">". $Concours ."</td>";
				}
				echo "<td".$class."><strong>" . $Ecole . "</strong></td>";
				echo "<td".$class." style='text-align:center;'>" . $An . "</td>";
				echo "<td".$class." style='text-align:right;'>" . $Place . "&nbsp;</td>";
				echo "<td".$class." style='text-align:right;'>" . formater($Inscrit, 0) . "&nbsp;</td>";
// 				echo "<td".$class." style='text-align:right;'>" . formater($Classe, 0) . "&nbsp;</td>";
				echo "<td".$class." style='text-align:right;'>" . formater($Integre, 0) . "&nbsp;</td>";
				echo "<td".$class." style='text-align:right;'>" . formater($RangMedian, 0) . "&nbsp;</td>";
				if ($Inscrit <> 0) {
					$selectivite = ($Dernier / $Inscrit) * 100; 
					$selectiviteMediane = ($RangMedian / $Inscrit) * 100;
				} else {
					$selectivite = 0;
					$selectiviteMediane = 0;
				}
				if ($selectiviteMediane <> 0) {
					echo "<td".$class." style='text-align:right;'>" . formater($selectiviteMediane, 0) . "%&nbsp;</td>";
				} else {
					echo "<td".$class." style='text-align:right;'>&nbsp;</td>";
				}
				echo "<td".$class." style='text-align:right;'>" . formater($Dernier, 0) . "&nbsp;</td>";
				if ($selectivite <> 0) {
					echo "<td".$class." style='text-align:right;'>" . formater($selectivite, 0) . "%&nbsp;</td>";
				} else {
					echo "<td".$class." style='text-align:right;'>&nbsp;</td>";
				}
 				echo "</tr>";	
				
				$ecoleCourante = $Ecole;
				$firstRecord = false;
				$class = "";
			}
			echo "</table>";
			echo "</main>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}

		// fermeture de la base
		if (isset($result)) {$result->closeCursor();}
		$db = null;
	
	?>

	<!-- retour en arrière vers le formulaire -->
	<footer class="container" style='margin-top:40px; margin-bottom:80px;'>
		<br/>
		<div class='d-flex justify-content-center'>
			<button class="btn btn-primary" onclick="questionnaire()">&larr; Retour aux critères</button>
			&nbsp;&nbsp;&nbsp;&nbsp;
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
		function zoom(ecole,an) {
			<?php
// 				echo "window.location.href='detail-resultat-admission-ecole-d-ingenieur-cpge-post-prepa.php?reference=" . $reference . "&an=' + an + '&filiere=" . $filiere . "&concours=" . $concours . "&ecole=' + ecole";
 				echo "window.location.href='detail-resultat-admission-ecole-d-ingenieur-cpge-post-prepa.php?reference=" . $reference . "&an=toutes&filiere=" . $filiere . "&concours=" . $concours . "&ecole=' + ecole";
			?>
		}

		// fonctions pour appeler la même page mais avec critère de tri
		function triSelectivite() {
			<?php
				echo "window.location.href='resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php?reference=" . $reference . "&filiere=" . $filiere . "&concours=" . $concours . "&ecole=" . supprimerApostrophe($ecole) . "&tri=selectivite'";
			?>
		}
		function triEcole() {
			<?php
				echo "window.location.href='resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php?reference=" . $reference . "&filiere=" . $filiere . "&concours=" . $concours . "&ecole=" . supprimerApostrophe($ecole) . "&tri=ecole'";
			?>
		}

		// pour retourner en arrière dans l'historique du navigateur
		function questionnaire() {
			<?php
				echo "window.location.href='statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php?reference=" . $reference . "&filiere=" . $filiere . "&concours=" . $concours . "&ecole=" . supprimerApostrophe($ecole) . "'";
			?>
		}
	</script>		
  </body>
</html>