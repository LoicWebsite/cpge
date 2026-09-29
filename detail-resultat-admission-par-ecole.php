<!doctype html>

<?php

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";

	$ecoleAffichee = str_replace("\\'", "'", remettreEsperluete(isset($ecole) ? $ecole : ''));
	$titrePage = ($ecoleAffichee !== '' && $ecoleAffichee !== 'toutes')
		? $ecoleAffichee . " : admissions, classements et spécialités"
		: "Détail admissions en école d'ingénieurs";

	// canonical centré sur l'école, sans les autres paramètres de navigation
	$canonical = "https://loic.website/CPGE/detail-resultat-admission-par-ecole.php";
	if ($ecoleAffichee !== '' && $ecoleAffichee !== 'toutes') {
		$canonical .= '?' . http_build_query(['ecole' => $ecole], '', '&', PHP_QUERY_RFC3986);
	}

	$vientRechercheSpecialite = ($origine === 'specialite');
	$retourSpecialite = '';
	if ($vientRechercheSpecialite && isset($specialite) && $specialite !== '') {
		$paramsRetourSpecialite = ['specialite' => $specialite, 'rechercher' => '1'];
		if ($strict) {
			$paramsRetourSpecialite['strict'] = '1';
		}
		if ($filiere !== '') {
			$paramsRetourSpecialite['filiere'] = strtolower($filiere);
		}
		$retourSpecialite = 'statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php?' . http_build_query($paramsRetourSpecialite, '', '&', PHP_QUERY_RFC3986);
	}
	$vientAttractivite = ($origine === 'attractivite');
	// L'origine salaire permet d'afficher un retour vers la page Salaires.
	$vientSalaire = ($origine === 'salaire');
	$vientClassement = ($origine === 'classement');
	if ($origine === '' && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php') !== false) {
		$origine = 'ecole';
	}

	$vientFiliere = ($origine === 'filiere');
	$vientEcole = ($origine === 'ecole');

?>

<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo escapeHtml($titrePage); ?> - statistiques SCEI d'admission en école d'ingénieurs post prépa CPGE">
	<?php if ($ecoleAffichee === '' || $ecoleAffichee === 'toutes') { ?>
	<meta name="robots" content="noindex, follow">
	<?php } ?>
	<link rel="canonical" href="<?php echo escapeHtml($canonical); ?>" />
	
	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title><?php echo escapeHtml($titrePage); ?></title>

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
				<li class="breadcrumb-item"><a href="#" onclick="questionnaire()">Ecole</a></li>
				<li class="breadcrumb-item" id="filStatistiques" hidden><a href="javascript:window.history.back();">Statistiques</a></li>
				<li class="breadcrumb-item active" aria-current="page"><?php echo escapeHtml(($ecoleAffichee !== '' && $ecoleAffichee !== 'toutes') ? $ecoleAffichee : 'Détail'); ?></li>
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
		<div class="d-flex flex-wrap justify-content-center gap-2">
			<a class="btn btn-primary" href="#hautdepage">&uarr; Haut de page</a>
			<?php if ($retourSpecialite !== '') { ?>
				<a class="btn btn-primary" href="<?php echo escapeHtml($retourSpecialite); ?>" onclick="if (window.history.length > 1) { window.history.back(); return false; }">&larr; Retour à la recherche par spécialité</a>
			<?php } elseif ($vientAttractivite) { ?>
				<a class="btn btn-primary" href="statistique-attractivite-ecoles.php" onclick="if (window.history.length > 1) { window.history.back(); return false; }">&larr; Retour à l'attractivité</a>
			<?php } elseif ($vientSalaire) { ?>
				<a class="btn btn-primary" href="salaire-ingenieur-cpge-post-prepa.php" onclick="if (window.history.length > 1) { window.history.back(); return false; }">&larr; Retour aux salaires</a>
			<?php } elseif ($vientClassement) { ?>
				<a class="btn btn-primary" href="classement-ecole-d-ingenieur.php" onclick="if (window.history.length > 1) { window.history.back(); return false; }">&larr; Retour au classement</a>
			<?php } elseif ($vientFiliere) { ?>
				<a class="btn btn-primary" href="statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php" onclick="if (window.history.length > 1) { window.history.back(); return false; }">&larr; Retour à la liste d'écoles</a>
			<?php } elseif ($vientEcole) { ?>
				<a class="btn btn-primary" href="resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php" onclick="if (window.history.length > 1) { window.history.back(); return false; }">&larr; Retour à la liste d'écoles</a>
			<?php } else { ?>
				<button class="btn btn-primary" onclick="questionnaire()">&larr; Retour au choix de l'école</button>
			<?php } ?>
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
		// pour retourner à la sélection de critères
		function questionnaire() {
			<?php
				// On ne garde que les paramètres réellement utiles pour éviter les URLs dupliquées.
				$queryQuestionnaireParams = [];
				if (($ecole !== "") && ($ecole !== "toutes")) {
					$queryQuestionnaireParams['ecole'] = $ecole;
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
  </body>
</html>