<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Statistiques SCEI d'admissions aux écoles d'ingénieurs post prépa CPGE">

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>
 
 	<style>
		ul {list-style-type: none;}
 	</style>
    
    <title>Statistiques admissions en écoles d'ingénieurs</title>
    
	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>
	
  </head>
  <body id="hautdepage">

	<?php
		include "php/menu.php";
	?>

	<?php
		// récupération-contrôle des paramètres
		include "php/controleParametre.php";
		
		// fonctions communes du site
		include "php/fonctionConcours.php";
	?>

	<header class="container" style='margin-top:90px;'>
		<h1>Statistiques intégrations post CPGE</h1>
		<br/>
	</header>

	<main class="container">

		<div id="actualite" class="container ancre">
			<br/>
			<p class="text-center">
				<mark style="color:#808080;"><strong>Actualité</strong> : ajout des classement du Figaro 2024 et de DAUR 2023.<br>
				Seules les écoles post prépa sont listées dans la page des classements.
				</mark>
			</p>
			<br/>
		</div>

		<section>

			<div>
				<p class="text-center"><i class="fa-solid fa-building-columns"></i>&nbsp;&nbsp;<a href="statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php">Statistiques d'admissions <strong>par filière</strong> et concours</a></p>
				<p class="text-center"><i class="fa-solid fa-graduation-cap"></i>&nbsp;&nbsp;<a href="statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php">Statistiques d'admissions <strong>pour une école</strong></a></p>
				<p class="text-center"><i class="fa-solid fa-ranking-star"></i>&nbsp;&nbsp;<a href="classement-ecole-d-ingenieur.php"><strong>Classement</strong> des écoles</a></p>
			</div>
			<br/><hr>
			<h2 class="h4">A quoi sert ce site ?</h2>
			<br/>
			<div>
				<p>Ce site est <strong>un <em>visualisateur</em> des statistiques SCEI</strong>. Il permet d'explorer les résultats d'admissions aux écoles d'ingénieurs post CPGE par filière, par concours ou par école. Les données sont issues du SCEI et enrichies du rang des derniers admis provenant des rapports des concours notamment Polytechnique, CentraleSupelec, Mines-Telecom et Mines-Ponts. 
						<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Pour le concours CentraleSupelec, le rang du dernier admis par école est connu uniquement à partir de 2019 pour les filières MP, PC, PSI et TSI. Et depuis 2023 pour MPI.<br/>
						<br/>&bull;  Pour le concours Mines-Ponts, le rang du dernier admis par école est connu pour les filières MP, PC et PSI depuis 2022, et MPI depuis 2023.<br/>
						<br/>&bull;  Pour les concours Polytechnique et Mines-Telecom, le rang du dernier admis par école est connu pour toutes les filières.<br/>
						<br/>&bull;  Pour les concours de la filière BCPST G2E, A ENV, A BIO et A PC BIO, le rang du dernier admis est connu pour toutes les années.<br/>
						<br/>&bull;  Pour tous les autres concours, le rang du dernier admis est connu uniquement pour les années 2016 et 2017.'></i></p>
				<p>Les données accessibles sont celles de 2016 à 2023. Les différentes années sont visibles en même temps sur un seul et même tableau.</p>
			</div>
			<br/>
		</section>

		<section class="container">
			<div class="row gy-4 justify-content-md-center">
			    <div class="col-md">
					<div class="p-3 border bg-light">
						<h2 class="h4 pb-2">Par filière et concours</h2>
						<p>Vous pouvez visualiser les statistiques d'admissions en choisissant la filière (MP, PC, PT...), puis le concours et éventuellement l'école.
						  Il est également possible de sélectionner une année particulière.</p>
						<p><i class="fa-solid fa-building-columns"></i>&nbsp;&nbsp;<a href="statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php">Voir les statistiques d'admissions <strong>par filière</strong> et concours<br/><br/>
							<img src="image/statistiques-filiere-CPGE.png" class="img-fluid img-thumbnail" alt="tableau statistiques des résultats d'admissions d'une école d'ingénieur post prépa pour une filière">
						</a></p>
					</div>
				</div>
				<div class="col-md">
					<div class="p-3 border bg-light">
						<h2 class="h4 pb-2">Par école</h2>
						<p>Vous pouvez aussi visualiser les statistiques pour une école donnée, quelque soit le concours et la filière (MP, PC, PT...).
						  Toutes les années de 2016 à 2023 seront alors affichées pour cette école.</p>
						<p><i class="fa-solid fa-graduation-cap"></i>&nbsp;&nbsp;<a href="statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php">Voir les statistiques d'admissions <strong>pour une école</strong><br/><br/>
							<img src="image/statistiques-ecole-CPGE.png" class="img-fluid img-thumbnail" alt="tableau statistiques des résultats d'admissions d'une école d'ingénieur pour toutes ses filières d'admissions'">
						</a></p>
					</div>
				</div>
			</div>
			<br/><br/>
			<div class="row justify-content-md-center">
			    <div class="col-md-6">
					<div class="p-3 border bg-light">
						<h2 class="h4 pb-2">Classement des écoles</h2>
						<p>Vous pouvez également regarder les classements des écoles (issus du magazine L'Etudiant, du site DAUR Rankings et du magazine Le Figaro étudiant). Cette page liste pour chacun des 3 classements, toutes les écoles post prépa sans avoir à paginer. En cliquant sur une école pour pouvez voir ses statistiques d'admissions. Et le site web des écoles est indiqué directement sur cette page sans avoir à zoomer.</p>
						<p><i class="fa-solid fa-ranking-star"></i>&nbsp;&nbsp;<a href="classement-ecole-d-ingenieur.php">Voir le <strong>classement des écoles</strong><br/><br/>
							<img src="image/classement-ecole-ingenieur.png" class="img-fluid img-thumbnail" alt="classement des écoles d'ingénieurs selon L'Etudiant">
						</a></p>
					</div>
				</div>
			</div>
		</section>

		<section id="propos">
			<br/><br/>
			<h2 class='h4'>A propos</h2>
			<p>Je suis le père d'un étudiant en prépa. A force de discuter avec lui du classement de ses voeux dans SCEI, j'ai senti le besoin d'afficher les statistiques d'admissions de façon plus simple et plus accessible, en particulier depuis un smartphone.</p>
			<p>C'est l'objet de ce petit site que je partage maintenant de façon bénévole. En souhaitant qu'il aide (un peu) les préparationnaires à se renseigner sur les écoles possibles, et surtout à <strong>ne pas se censurer dans l'établissement des voeux SCEI</strong>. N'oubliez pas de faire : 
			<ul class="list-group">
				<li><span class="badge bg-primary rounded-pill">1</span>&nbsp;&nbsp;des voeux de <strong>rêves</strong> (il faut rêver et avoir de l'ambition),</li>
				<li><span class="badge bg-primary rounded-pill">2</span>&nbsp;&nbsp;des voeux <strong>réalistes</strong> (vos professeurs vous conseilleront bien),</li>
				<li><span class="badge bg-primary rounded-pill">3</span>&nbsp;&nbsp;et aussi des voeux de <strong>secours</strong> (on ne sait jamais, ce sont des concours).</li>
			</ul>
			<br/>
		</section>	

		<section id="contact">
			<br/>
			<h2 class='h4'>Contact</h2>
			<p>N'hésitez pas à me contacter par mail pour me faire part de vos remarques, suggestions, demandes d'évolution ou bug rencontré. Je m'efforcerai de traiter votre demande dans la mesure de mon temps disponible. Je vous remercie par avance.</p>
			<p><a href="mailto:contact@loic.website?subject=Demande&nbsp;d&apos;information&nbsp;concours&nbsp;CPGE"><i class="fa fa-envelope">&nbsp;&nbsp;</i>Contactez-moi par email</a></p>
			<br/>
		</section>	
	
	</main>

	<footer style='margin-top:40px; margin-bottom:80px;'>
		&nbsp;
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