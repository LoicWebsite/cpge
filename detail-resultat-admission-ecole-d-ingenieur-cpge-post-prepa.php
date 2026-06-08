<!doctype html>

<?php

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";

	// URL canonique: cette page détail dépend de plusieurs paramètres de navigation.
	$canonicalBase = 'https://loic.website/CPGE/detail-resultat-admission-ecole-d-ingenieur-cpge-post-prepa.php';
	$canonicalParams = [];
	if (($reference !== '') && ($reference !== 'toutes')) {
		$canonicalParams['reference'] = $reference;
	}
	if (($an !== '') && ($an !== 'toutes')) {
		$canonicalParams['an'] = $an;
	}
	if (($filiere !== '') && ($filiere !== 'toutes')) {
		$canonicalParams['filiere'] = $filiere;
	}
	if (($concours !== '') && ($concours !== 'tous')) {
		$canonicalParams['concours'] = $concours;
	}
	if (($ecole !== '') && ($ecole !== 'toutes')) {
		$canonicalParams['ecole'] = $ecole;
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
    <meta name="description" content="Statistique SCEI sur les admissions d'une école d'ingénieur post prépa CPGE">
		<link rel="canonical" href="<?php echo escapeHtml($canonicalUrl); ?>" />

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Détail admissions en école d'ingénieurs</title>

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
			<div class="col-sm">
			  <ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php"><i class="bi bi-house-door-fill"></i></a></li>
				<li class="breadcrumb-item"><a href="#" onclick="questionnaire()">Filière</a></li>
				<li class="breadcrumb-item"><a href="javascript:window.history.back();">Statistiques</a></li>
				<li class="breadcrumb-item active" aria-current="page">Détail</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- détail de l'école -->
	<?php
		include "php/detail.php";	
	?>

	<!-- retour en arrière vers le formulaire -->
	<footer class="container" style='margin-top:40px; margin-bottom:80px;'>
		<br/>
		<p class=text-center>
			<button class="btn btn-primary" onclick="questionnaire()">|&larr; Retour aux critères</button>
&nbsp;&nbsp;&nbsp;
			<button class="btn btn-primary" onclick="javascript:window.history.back();">&larr; Retour à la liste</button>
		</p>
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
		// pour retourner à la sélection de critères
		function questionnaire() {
			<?php
				$queryQuestionnaireParams = [];
				if ($reference !== '' && $reference !== 'toutes') {
					$queryQuestionnaireParams['reference'] = $reference;
				}
				if ($filiere !== '') {
					$queryQuestionnaireParams['filiere'] = $filiere;
				}
				if ($concours !== '') {
					$queryQuestionnaireParams['concours'] = $concours;
				}
				if ($ecole !== '' && $ecole !== 'toutes') {
					$queryQuestionnaireParams['ecole'] = $ecole;
				}
				$queryQuestionnaire = http_build_query($queryQuestionnaireParams, '', '&', PHP_QUERY_RFC3986);
				$questionnaireUrl = 'statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php';
				if ($queryQuestionnaire !== '') {
					$questionnaireUrl .= '?' . $queryQuestionnaire;
				}
				echo 'window.location.href = ' . encodeJs($questionnaireUrl) . ';';
			?>
		}
	</script>		
  </body>
</html>