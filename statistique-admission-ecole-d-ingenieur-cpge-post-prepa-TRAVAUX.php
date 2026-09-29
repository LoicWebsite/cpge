<!doctype html>
<?php
	/**
	 * ============================================================================
	 * PAGE TEMPORAIRE DE MAINTENANCE
	 * ============================================================================
	 * Cette page remplace provisoirement la page d'accueil habituelle pendant une
	 * mise à jour de la base de données (le fichier .htaccess pointe sur ce nom
	 * de fichier via DirectoryIndex, on ne le modifie donc pas).
	 *
	 * La vraie page d'accueil est sauvegardée ici, à restaurer une fois la mise
	 * à jour terminée :
	 * save/statistique-admission-ecole-d-ingenieur-cpge-post-prepa-20260828-avant-maintenance.php
	 *
	 * Pour repasser en ligne : écraser ce fichier par la sauvegarde ci-dessus.
	 * ============================================================================
	 */
?>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="description" content="Le site Stat concours est en cours de mise à jour, de retour très vite.">

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";

		// styles nécessaires à l'application (bootstrap + bootstrap-icons + concours.css)
		include "php/style.php";
	?>

    <title>Site en maintenance - Stat concours</title>

	<style>
		/* Icône centrale : légère oscillation en boucle, clin d'œil ludique
		   sans bibliothèque externe (juste une transformation CSS animée). */
		@keyframes balancement {
			0%, 100% { transform: rotate(-8deg); }
			50%      { transform: rotate(8deg); }
		}
		.icone-maintenance {
			display: inline-block;
			animation: balancement 1.6s ease-in-out infinite;
			transform-origin: 70% 70%;
		}
		.fond-degrade {
			min-height: calc(100vh - 56px);
			background: linear-gradient(180deg, DarkSlateBlue 0%, #ffffff 55%);
		}
	</style>

  </head>
  <body id="hautdepage">

	<!-- en-tête simplifié : même identité visuelle que le reste du site,
	     mais sans liens de navigation puisque les autres pages ne sont pas
	     garanties disponibles/à jour pendant la maintenance. -->
	<nav id="navigation" class="navbar fixed-top navbar-dark" style="background-color:DarkSlateBlue">
		<div class="container-fluid">
			&nbsp;&nbsp;
			<span class="navbar-brand">
				<img src="image/logo.png" width="32" height="23" alt="logo écran avec roue dentelée pour symboliser la technologie" loading="lazy">
				&nbsp;Stat concours
			</span>
		</div>
	</nav>

	<main class="fond-degrade d-flex align-items-center justify-content-center" style="padding-top:56px;">
		<div class="container text-center" style="max-width:640px;">

			<div class="icone-maintenance" style="font-size:5rem; color:DarkSlateBlue;">
				<i class="bi bi-cone-striped" aria-hidden="true"></i>
			</div>

			<h1 class="h3 mt-4" style="color:DarkSlateBlue;">Petite pause technique&nbsp;!</h1>

			<p class="mt-3" style="font-size:1.1rem;">
				Le site fait sa mise à jour de rentrée 😉 : on révise la base de données
				notamment pour ajouter les spécialités des écoles en 2A ou en 3A.
			</p>

			<p class="text-muted">
				Pas de panique, ce n'est qu'une <strong>courte pause</strong> — le temps d'enrichir les
				statistiques et d'ajouter de nouvelles fonctionnalités, et tout reviendra en ligne très
				bientôt dans quelques heures.
			</p>

			<div class="mt-4">
				<span class="badge rounded-pill" style="background-color:DarkSlateBlue; padding:10px 18px; font-size:0.9rem;">
					<i class="bi bi-arrow-repeat"></i>&nbsp; De retour très vite
				</span>
			</div>

			<p class="text-muted small mt-4">
				Une question en attendant ? <a href="mailto:contact@loic.website">Contactez-nous</a>.
			</p>

		</div>
	</main>

  </body>
</html>
