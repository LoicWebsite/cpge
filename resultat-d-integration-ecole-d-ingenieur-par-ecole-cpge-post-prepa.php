<!doctype html>

<?php

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";

	// URL canonique: conserve uniquement les paramètres qui changent réellement le contenu.
	$canonicalBase = 'https://loic.website/CPGE/resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php';
	$canonicalParams = [];
	if (($ecole !== "") && ($ecole !== "toutes")) {
		$canonicalParams['ecole'] = $ecole;
	} elseif ($recherche !== "") {
		$canonicalParams['recherche'] = $recherche;
	}
	$canonicalUrl = $canonicalBase;
	if (!empty($canonicalParams)) {
		$canonicalUrl .= '?' . http_build_query($canonicalParams, '', '&', PHP_QUERY_RFC3986);
	}
?>


<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Statistique SCEI d'admissions par école aux écoles d'ingénieurs post prépa CPGE">
		<link rel="canonical" href="<?php echo escapeHtml($canonicalUrl); ?>" />

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
				<li class="breadcrumb-item">
					<a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
						<i class="bi bi-house-door-fill"></i>
					</a>
				</li>
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
			echo "<h1 class='h3'><i class='bi bi-mortarboard-fill'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions<br/>pour l'école ".escapeHtml($ecole)."</h1>";
		} else {
			echo "<h1 class='h3'><i class='bi bi-mortarboard-fill'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions</h1>";
		}
		echo "<br/>";
		echo "</header>";

		// section principale de la page
		echo "<main class='container-fluid'>";

		// conexion à la base concours cpge
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// construction de la clause WHERE (requête préparée)
		$paramsEcole = [];
		$sql = "SELECT Filiere, Concours, Ecole FROM Ecole";
		if (($ecole <> "") and ($ecole <> "toutes")) {
			$sql .= " WHERE Ecole LIKE :ecole_like";
			$paramsEcole[':ecole_like'] = '%' . $ecole . '%';
		} elseif ($recherche <> "") {
			$sql .= " WHERE Ecole LIKE :recherche_like";
			$paramsEcole[':recherche_like'] = '%' . $recherche . '%';
		}
		$sql .= " ORDER BY Ecole ASC, Filiere ASC";
		if ($debug) {
			echo "SQL Ecole = " . escapeHtml($sql) . "<br/>";
			echo "PARAMS Ecole = " . escapeHtml(json_encode($paramsEcole, JSON_UNESCAPED_UNICODE)) . "<br/>";
		}
		try {
			$stmtEcole = $db->prepare($sql);
			$stmtEcole->execute($paramsEcole);
			$result = $stmtEcole;

			$sqlNote = 'SELECT Inscrit, Integre, RangMedian, Dernier FROM Note WHERE Ecole = :ecole AND Filiere = :filiere AND Concours = :concours AND An = :an';
			$stmtNote = $db->prepare($sqlNote);
			$annees = ['2025', '2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016'];

			// affichage de l'en tête du tableau
			echo "<table class='table-hover' style='width:100%;' id='tableau-par-ecole'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une ligne pour voir le détail de cette école.<br/>
				<span style='color:darkslategray;'>En <strong>noir</strong> le rang médian</span>, <span style='color:#0000FF'>en <strong>bleu</strong> le rang du dernier</span>.";
			$idTableau = '"#tableau-par-ecole","école;filière;concours;2023;2022;2021;2020;2019;2018;2017;2016"';
			echo "<br>Cliquer sur le bouton pour télécharger le tableau au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></small></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			echo "<th  style='position:static;' colspan=3></th>";
			echo "<th  style='position:static;' colspan=8>Intégrés : Rang médian / Inscrits&nbsp;&nbsp;<span style='font-weight:normal'>(en noir)</span><br/>Intégrés : Rang dernier / Inscrits&nbsp;&nbsp;<span style='font-weight:normal'>(en bleu)</span><br/>
								<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Lorsque le rang du dernier admis est connu c&apos;est celui-ci qui est affiché (en bleu).<br/>
								<br/>&bull; Sinon c&apos;est le rang médian qui est affiché (en noir).<br/>
								<br/>&bull; Le rang du dernier appelé a été supprimé des statistiques SCEI à partir de 2018. Il a été remplacé par le rang médian et le rang moyen.<br/>
								<br/>&bull; Seuls certains concours continuent à publier le rang du dernier admis par école (voir la note d&apos;information &#x24D8; en page d&apos;accueil pour plus de détails).'></i></th>";
			echo "<th  style='position:static;' colspan=2>Intégrés : Rang dernier / Inscrits<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='En 2017 et 2016 le rang du dernier appelé est sytématiquement renseigné dans SCEI. C&apos;est lui qui est affiché.'></i></th>";
			echo "</tr>";
			echo "<tr>";
			echo "<th>Ecole<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='&bull;Lorsqu&apos;une école a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>
								<br/>&bull; Lorsque plusieurs écoles ont fusionné, les différentes écoles apparaissent séparément avant la fusion.<br/>
								<br/>&bull; Lorsqu&apos;une école change de concours, elle apparaît soit dans le nouveau concours soit dans l&apos;ancien suivant la date.<br/>
								<br/>&bull; A noter que le nom affiché est celui qui apparaît dans SCEI.'></i></th>";
			echo "<th>&nbsp;Filière&nbsp;</th>";
			echo "<th>Concours<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Lorsqu&apos;un concours a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>Exemple CCP devenu CCINP en 2019.'></i></th>";
			echo "<th>2025</th>";
			echo "<th>2024</th>";
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
				$jsEcole = encodeJs($Ecole);
				$jsFiliere = encodeJs($Filiere);
				$jsConcours = encodeJs($Concours);
				echo "<tr ondblclick='zoom(" . $jsEcole . "," . $jsFiliere . "," . $jsConcours . ")'>";
				echo "<td".$class.">" . escapeHtml($Ecole) . "</td>";
				echo "<td".$class." style='text-align:center'><strong>". escapeHtml(strtoupper($Filiere)) ."</strong></td>";
				echo "<td".$class.">". escapeHtml($Concours) ."</td>";

				foreach ($annees as $annee) {
					if ($debug) {
						echo "SQL Note = " . escapeHtml($sqlNote) . " | PARAMS = " . escapeHtml(json_encode([
							'ecole' => $Ecole,
							'filiere' => $Filiere,
							'concours' => $Concours,
							'an' => $annee
						], JSON_UNESCAPED_UNICODE)) . "<br/>";
					}
					try {
						$stmtNote->execute([
							':ecole' => $Ecole,
							':filiere' => $Filiere,
							':concours' => $Concours,
							':an' => $annee,
						]);
						$rowNote = $stmtNote->fetch(PDO::FETCH_ASSOC);
						if ($rowNote === false) {
							echo "<td".$class." style='text-align:center'></td>";
						} else {
							$Inscrit = $rowNote['Inscrit'];
							$Integre = $rowNote['Integre'];
							$RangMedian = $rowNote['RangMedian'];
							$Dernier = $rowNote['Dernier'];

							if (($Dernier <> 0) and ($Dernier <> "")) {
								echo "<td".$class." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".escapeHtml($Integre)." : <strong>".escapeHtml($Dernier)."</strong></span> / ".escapeHtml($Inscrit)."</td>";
							} elseif (($RangMedian <> 0) and ($RangMedian <> "")) {
								echo "<td".$class." style='text-align:center'><span style='font-size:120%'>".escapeHtml($Integre)." : <strong>".escapeHtml($RangMedian)."</strong></span> / ".escapeHtml($Inscrit)."</td>";
							} else {
								echo "<td".$class." style='text-align:center'></td>";
							}
						}
					}
					catch(PDOException $erreur)	{
						echo "Erreur SELECT Note " . escapeHtml($annee) . " : " . escapeHtml($erreur->getMessage());
					}
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
				$baseZoom = 'detail-resultat-admission-par-ecole.php?' . http_build_query([
					'reference' => $reference,
					'an' => 'toutes',
				], '', '&', PHP_QUERY_RFC3986);
				echo 'window.location.href = ' . encodeJs($baseZoom) . " + '&filiere=' + encodeURIComponent(filiere) + '&concours=' + encodeURIComponent(concours) + '&ecole=' + encodeURIComponent(ecole);";
			?>
		}

		// pour retourner en arrière dans l'historique du navigateur
		function questionnaire() {
			<?php
				$queryQuestionnaireParams = [];
				if (($ecole !== "") && ($ecole !== "toutes")) {
					$queryQuestionnaireParams['ecole'] = $ecole;
				}
				if ($recherche !== "") {
					$queryQuestionnaireParams['recherche'] = $recherche;
				}
				$queryQuestionnaire = http_build_query($queryQuestionnaireParams, '', '&', PHP_QUERY_RFC3986);
				$questionnaireUrl = 'statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php';
				if ($queryQuestionnaire !== '') {
					$questionnaireUrl .= '?' . $queryQuestionnaire;
				}
				echo 'window.location.href = ' . encodeJs($questionnaireUrl) . ';';
			?>
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