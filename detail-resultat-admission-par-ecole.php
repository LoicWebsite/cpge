<?php

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";
	require_once "php/detailEntete.php";

	$ecoleAffichee = str_replace("\\'", "'", remettreEsperluete(isset($ecole) ? $ecole : ''));
	if ($ecoleAffichee !== '' && $ecoleAffichee !== 'toutes') {
		try {
			$db = openDatabase();
			$ecoleCanoniqueFiche = obtenirNomEcoleFiche($db, $ecoleAffichee);
		} catch (RuntimeException $erreur) {
			error_log('Fiche école : ' . $erreur->getMessage());
			http_response_code(503);
			die("<p>Impossible de charger l'identité de l'école. Veuillez réessayer.</p>");
		}
		$ecoleAffichee = $ecoleCanoniqueFiche;
	}
	$titrePage = ($ecoleAffichee !== '' && $ecoleAffichee !== 'toutes')
		? $ecoleAffichee . " : admissions, classements et spécialités"
		: "Détail admissions en école d'ingénieurs";

	// canonical centré sur l'école, sans les autres paramètres de navigation
	$canonical = "https://loic.website/CPGE/detail-resultat-admission-par-ecole.php";
	if ($ecoleAffichee !== '' && $ecoleAffichee !== 'toutes') {
		$canonical .= '?' . http_build_query(['ecole' => $ecole], '', '&', PHP_QUERY_RFC3986);
	}

	if ($origine === '' && isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php') !== false) {
		$origine = 'ecole';
	}

	$navigationRetour = destinationRetourNavigation(array_replace($_GET, [
		'origine' => $origine, 'ecole' => $ecole, 'filiere' => $filiere,
		'concours' => $concours, 'reference' => $reference, 'specialite' => $specialite,
		'strict' => $strict ? '1' : '',
	]), $_SERVER);
	$urlCriteres = urlPageNavigation('statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php',
		$ecole !== '' && $ecole !== 'toutes' ? ['ecole' => $ecole] : []);

?>
<!doctype html>

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
				<li class="breadcrumb-item"><a data-retour-navigation href="<?php echo escapeHtml($urlCriteres); ?>">Ecole</a></li>
				<li class="breadcrumb-item" id="filStatistiques"><a data-retour-navigation href="<?php echo escapeHtml($navigationRetour['url']); ?>"><?php echo escapeHtml($navigationRetour['rubrique']); ?></a></li>
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
			<a class="btn btn-primary" data-retour-navigation href="<?php echo escapeHtml($navigationRetour['url']); ?>">&larr; <?php echo escapeHtml($navigationRetour['libelle']); ?></a>
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

  </body>
</html>