<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Liste des écoles par spécialité proposée post prépa CPGE">
		<!-- Canonical fixe: URL de référence de la page spécialités. -->
		<link rel="canonical" href="https://loic.website/CPGE/statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php" />

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>
    
    <title>Écoles proposant une spécialité</title>
    
	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>
	<style>
		#resultats {
			scroll-margin-top: 80px;
		}
		#detailTermesRecherche {
			overflow-wrap: anywhere;
		}
		#boutonTermesRecherche {
			cursor: pointer;
		}
		#detailTermesRecherche[hidden] {
			display: none !important;
		}
		.detail-recherche-grille {
			display: grid;
			grid-template-columns: 8rem minmax(0, 1fr);
			gap: 0.375rem 1rem;
			margin: 0 0 0 1.25rem;
		}
		.detail-recherche-grille dt {
			font-weight: 600;
		}
		.detail-recherche-grille dd {
			min-width: 0;
			margin: 0;
		}
		@media (max-width: 375.98px) {
			.detail-recherche-grille {
				grid-template-columns: minmax(0, 1fr);
				row-gap: 0.25rem;
			}
			.detail-recherche-grille dt:not(:first-child) {
				margin-top: 0.5rem;
			}
		}
		.table-specialites thead th {
			position: static;
			top: auto;
			z-index: auto;
			background-color: DarkSlateBlue;
			color: #fff;
			font-size: 0.875rem;
			font-weight: normal;
			line-height: 1.25;
		}
		.table-specialites thead th em {
			color: #fff;
		}
		.table-specialites {
			table-layout: fixed;
			max-width: 100%;
		}
		.table-specialites th,
		.table-specialites td {
			overflow-wrap: anywhere;
		}
		.table-specialites th:last-child,
		.table-specialites td:last-child {
			border-left: 0;
		}
		.table-specialites th:nth-last-child(2),
		.table-specialites td:nth-last-child(2) {
			border-right: 0;
		}
		.table-specialites th:first-child {
			white-space: nowrap;
		}
		.table-specialites th:last-child,
		.table-specialites td:last-child {
			min-width: 3.25rem;
			padding-left: 0.25rem;
			padding-right: 0.25rem;
			text-align: center;
			vertical-align: middle;
		}
		.table-specialites tbody tr:last-child td {
			border-bottom-style: solid;
			border-bottom-color: #666666;
			border-bottom-width: 1px;
		}
		.raison-selection {
			font-size: 80%;
		}
		.badge-pertinence {
			border: 1px solid transparent;
			border-radius: 0.25rem;
			font-weight: 500;
		}
		.badge-pertinence-tres-forte {
			background-color: #edf5ff;
			border-color: #b8d7f4;
			color: #255b88;
		}
		.badge-pertinence-forte {
			background-color: #eef8f8;
			border-color: #b9dddd;
			color: #386d70;
		}
		.badge-pertinence-moyenne {
			background-color: #f7f7f7;
			border-color: #d7d7d7;
			color: #666666;
		}
		.badge-pertinence-large {
			background-color: #ffffff;
			border-color: #dddddd;
			color: #777777;
		}
		.carte-specialite {
			font-size: 0.95rem;
		}
		.carte-specialite .badge {
			white-space: normal;
			text-align: left;
		}
		@media (max-width: 575.98px) {
			.table-specialites th:first-child {
				width: 24% !important;
			}
			.table-specialites th:nth-child(2) {
				width: 38% !important;
			}
			.table-specialites th:nth-child(3) {
				width: 30% !important;
			}
			.table-specialites th:last-child {
				width: 8% !important;
			}
		}
	</style>
  </head>
  <body id="hautdepage">

	<?php
		include "php/menu.php";

		// récupération-contrôle des paramètres
		include "php/controleParametre.php";

		// fonctions communes du site
		include "php/fonctionConcours.php";

		// liste des mots-clés proposés (voir php/specialiteRecherche.php)
		require_once "php/specialiteRecherche.php";

		$suggestionsAffichees = [];
		try {
			$db = openDatabase();
			$suggestionsAffichees = motsClesSpecialite($db, isset($_GET['nocache']) && $_GET['nocache'] === '1');
			$db = null;
		} catch (PDOException $erreur) {
			$suggestionsAffichees = [];
		}
	?>

	<nav class="container" style='margin-top:80px;' aria-label="breadcrumb">
	  <ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
				<i class="bi bi-house-door-fill"></i>
			</a>
		</li>
		<li class="breadcrumb-item">Spécialité</li>
		<li class="breadcrumb-item active" aria-current="page">Recherche</li>
	  </ol>
	</nav>

	<header class="container">
		<h1 class="h3">
			<i class="bi bi-search" aria-hidden="true"></i>
			&nbsp;&nbsp;&nbsp;Écoles par spécialité
		</h1>
		<br/>
	</header>

	<main id="questionnaire" class="container">

		<!-- formulaire de recherche -->
		<div class="border bg-light p-2">
			<h2 class="h4" style="text-align:left;">Rechercher une spécialité ou un domaine</h2>
			<br/>
			<form id="formSpecialite" onsubmit="rechercherSpecialites(); return false;">
			<div class="row align-items-center">
				<div class="col-lg-1"></div>
				<div class="col-md-3 col-lg-2">
					<label for="specialite" class="form-label">Mots-clés<sup>*</sup> :</label>
				</div>
				<div class="col-md-9 col-lg-8">
					<div class="d-none">
						<input list="domaines-specialite" class="form-control" id="specialite" name="specialite" type="search" placeholder="ex. robotique, data, énergie, matériaux" autocomplete="off" minlength="2">
					</div>
					<div>
						<select class="form-select" id="specialiteMobile" aria-label="Choisir un mot-clé" onchange="synchroniserSpecialiteMobile()">
							<option value="">Choisir dans la liste</option>
							<?php
							foreach ($suggestionsAffichees as $suggestion) {
								echo "<option value='" . escapeHtml($suggestion) . "'>" . escapeHtml($suggestion) . "</option>";
							}
							?>
						</select>
						<input class="form-control mt-2" id="specialiteLibreMobile" type="search" placeholder="ou saisir un autre mot-clé" autocomplete="off" minlength="2" oninput="synchroniserSpecialiteLibreMobile()">
					</div>
					<datalist id="domaines-specialite">
						<?php
						foreach ($suggestionsAffichees as $suggestion) {
							echo "<option value='" . escapeHtml($suggestion) . "'>";
						}
						?>
					</datalist>
				</div>
				<div class="col-lg-1"></div>
			</div>
			<div class="row align-items-center mt-2">
				<div class="col-lg-1"></div>
				<div class="col-md-3 col-lg-2">
					<label for="rechercheStricte" class="form-label mb-0">Mode strict<sup>*</sup> :</label>
				</div>
				<div class="col-md-9 col-lg-8">
					<div class="form-check form-switch">
						<input class="form-check-input" type="checkbox" role="switch" id="rechercheStricte" aria-label="Activer la recherche stricte" onchange="actualiserRechercheStricte()">
						<label class="form-check-label" for="rechercheStricte" id="etatRechercheStricte">OFF</label>
					</div>
				</div>
				<div class="col-lg-1"></div>
			</div>
			<div class="row align-items-center mt-2">
				<div class="col-lg-1"></div>
				<div class="col-md-3 col-lg-2">
					<label for="filiereSpecialite" class="form-label mb-0">Filière CPGE :</label>
				</div>
				<div class="col-md-9 col-lg-8">
					<select class="form-select" id="filiereSpecialite" name="filiere">
						<option value="">Toutes</option>
						<option value="bcpst">BCPST</option>
						<option value="mp">MP</option>
						<option value="mpi">MPI</option>
						<option value="pc">PC</option>
						<option value="psi">PSI</option>
						<option value="pt">PT</option>
						<option value="tb">TB</option>
						<option value="tsi">TSI</option>
						<option value="tpc">TPC</option>
					</select>
				</div>
				<div class="col-lg-1"></div>
			</div>
			<div class="row">
				<div class="col-12 text-center mt-3">
					<button type="submit" class="btn btn-primary text-nowrap"><i class="bi bi-search"></i>&nbsp; Rechercher</button>
				</div>
			</div>
			<br/>
			<span style='font-style:italic;'><sup>*</sup><small>En mode étendu, la recherche utilise le nom des spécialités, celui des groupes de spécialités et des synonymes. En mode strict, une recherche libre porte uniquement sur le nom de la spécialité.</small></span>
			<!-- Les synonymes sont utilisés lorsque le mot-clé correspond à un domaine de la liste ; sinon, aucun synonyme n'est ajouté. -->
			</form>
		</div>

		<!-- zone de résultat -->
		<br/>
		<div class="border bg-white p-2" id="resultats">
			<h2 class="h4" style="text-align:left;">Écoles correspondant à la recherche</h2>
			<p id="resumeRecherche" class="text-muted small mb-2">Saisissez un domaine ou une spécialité pour lancer la recherche.</p>
			<div id="termesRechercheMobile" class="small mb-2 d-none">
				<button id="boutonTermesRecherche" class="btn btn-link btn-sm p-0 text-decoration-none" type="button" aria-expanded="false" aria-controls="detailTermesRecherche"><i id="iconeDetailRecherche" class="bi bi-plus" aria-hidden="true"></i> Détails de la recherche</button>
				<div class="mt-1 text-muted" id="detailTermesRecherche" role="region" aria-labelledby="boutonTermesRecherche" hidden>
					<dl class="detail-recherche-grille">
						<dt>Mot-clé :</dt><dd id="motCleDetailRecherche"></dd>
						<dt>Mode :</dt><dd id="modeDetailRecherche"></dd>
						<dt>Termes recherchés :</dt><dd id="listeTermesRechercheMobile"></dd>
					</dl>
					<br>
				</div>
			</div>
			<div id="choixAffichageSpecialite" class="justify-content-between align-items-center flex-wrap gap-2 mb-3 d-none" role="group" aria-label="Choix de l'affichage et export">
				<div class="d-flex gap-2" role="group" aria-label="Choix de l'affichage">
					<button type="button" class="btn btn-sm btn-secondary bouton-vue-liste" onclick="choisirVue('liste')" data-bs-toggle="tooltip" title="Afficher les résultats sous forme de cartes empilées, quelle que soit la taille de l'écran."><i class="bi bi-list-ul"></i> liste</button>
					<button type="button" class="btn btn-sm btn-secondary bouton-vue-tableau" onclick="choisirVue('tableau')" data-bs-toggle="tooltip" title="Afficher les résultats dans un tableau, quelle que soit la taille de l'écran."><i class="bi bi-table"></i> tableau</button>
					<button type="button" class="btn btn-sm btn-secondary bouton-vue-auto" onclick="choisirVue('auto')" data-bs-toggle="tooltip" title="Choisir automatiquement selon la taille de l'écran : tableau sur ordinateur, liste sur mobile."><i class="bi bi-arrow-repeat"></i> auto</button>
				</div>
				<small class="text-muted">Cliquer sur le bouton pour télécharger tous les résultats au format CSV : <button type="button" class="btn btn-secondary btn-sm" onclick="tableToCSV('#tableau-specialites', 'pertinence;école - diplôme;spécialité;')"><i class="bi bi-download"></i> csv</button></small>
			</div>
			<div id="listeEcoles"></div>
			<div class='border-top mt-4 pt-3'>
				<ul class='text-muted small mb-0'>
					<li>Les spécialités recensées à ce jour sont celles des écoles des concours X-ENS, CentraleSupélec, Mines-Ponts, Mines-Télécom, CCINP, Groupe INSA, Polytech, Ecoles Militaires et Puissance Alpha.</li>
					<li>Les autres concours seront couverts progressivement.</li>
					<li>Les spécialités correspondent aux spécialisations choisies par les étudiants. Elles se distinguent des enseignements fondamentaux et des orientations professionnelles (recherche, conseil, entrepreneuriat, etc.) qui peuvent compléter le parcours.</li>
				</ul>
			</div>
			<div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
				<a href="#hautdepage" class="btn btn-primary btn-sm"><i class="bi bi-arrow-up"></i> Haut de page</a>
				<?php if (filter_var($_GET['domaine'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])): ?>
					<a href="paysage-specialites-ecoles-ingenieur.php" class="btn btn-primary btn-sm"><i class="bi bi-arrow-left" aria-hidden="true"></i> Retour vers le panorama</a>
				<?php endif; ?>
			</div>
		</div>

	</main>

	<footer style='margin-top:40px; margin-bottom:80px;'>
		&nbsp;
	</footer>

	<?php
		include "php/librairie.php"; // jquery + popper + bootstrap
	?>

	<script>
	<?php include "js/basculeAffichage.js"; ?>
	</script>

	<script>
	<?php include "js/tableToCSV.js"; ?>
	</script>

	<script>
		var resultatsSpecialite = [];
		var etatTriSpecialite = { colonne: 'pertinence', direction: 'desc' };
		var domainePanoramaSpecialite = <?php echo encodeJs(filter_var($_GET['domaine'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: null); ?>;
		var motClePanoramaSpecialite = <?php echo encodeJs($specialite ?? ''); ?>;
		var positionnerResultatsAuChargement = domainePanoramaSpecialite !== null || window.location.hash === '#resultats';

		// Lance la recherche des que le DOM est pret, sans attendre images et scripts externes asynchrones.
		window.addEventListener('DOMContentLoaded', function() {
			initialiserDetailTermesRecherche();
			<?php
			if ($filiere !== '') {
				echo 'document.getElementById("filiereSpecialite").value = ' . encodeJs(strtolower($filiere)) . ';' . "\n";
			}
			if (isset($specialite) && ($specialite != "")) {
				echo 'document.getElementById("specialite").value = ' . encodeJs($specialite) . ';' . "\n";
				echo 'initialiserSpecialiteMobile(' . encodeJs($specialite) . ');' . "\n";
				if ($strict) { echo 'setRechercheStricte(true);' . "\n"; }
				if ($rechercher) {
					echo 'rechercherSpecialites();' . "\n";
				}
			}
			?>
		});

		// La recherche utilise partout le même modèle : un select natif pour les
		// mots-clés connus et un champ libre pour les termes absents de la liste.
		// Le champ #specialite reste caché comme champ de référence technique pour
		// préserver les URLs préremplies et le comportement historique.
		// Cette fonction choisit la valeur réellement saisie avant l'appel AJAX.
		function valeurSpecialiteRecherche() {
			var champDesktop = document.getElementById('specialite');
			var saisieMobile = document.getElementById('specialiteLibreMobile');
			var selectMobile = document.getElementById('specialiteMobile');
			if (saisieMobile && saisieMobile.value.trim() !== '') {
				return saisieMobile.value.trim();
			}
			if (selectMobile && selectMobile.value.trim() !== '') {
				return selectMobile.value.trim();
			}
			return champDesktop ? champDesktop.value.trim() : '';
		}

		// Quand l'utilisateur mobile choisit une valeur du select, on vide la saisie
		// libre et on reporte la valeur dans le champ desktop caché, qui reste le
		// champ de référence pour les URLs préremplies et le comportement historique.
		function synchroniserSpecialiteMobile() {
			var champDesktop = document.getElementById('specialite');
			var saisieMobile = document.getElementById('specialiteLibreMobile');
			var selectMobile = document.getElementById('specialiteMobile');
			if (selectMobile && selectMobile.value !== '') {
				if (saisieMobile) { saisieMobile.value = ''; }
				if (champDesktop) { champDesktop.value = selectMobile.value; }
			}
		}

		// La saisie libre mobile permet de chercher un terme absent du select.
		// Elle prend alors le dessus sur la liste fermée et la synchronise avec le
		// champ de référence.
		function synchroniserSpecialiteLibreMobile() {
			var champDesktop = document.getElementById('specialite');
			var saisieMobile = document.getElementById('specialiteLibreMobile');
			var selectMobile = document.getElementById('specialiteMobile');
			if (saisieMobile && saisieMobile.value.trim() !== '') {
				if (selectMobile) { selectMobile.value = ''; }
				if (champDesktop) { champDesktop.value = saisieMobile.value; }
			}
		}

		// Préremplit les deux interfaces lorsqu'une recherche arrive par URL : si la
		// valeur existe dans le select mobile, elle est sélectionnée ; sinon elle est
		// placée dans le champ libre mobile.
		function initialiserSpecialiteMobile(valeur) {
			var champDesktop = document.getElementById('specialite');
			var saisieMobile = document.getElementById('specialiteLibreMobile');
			var selectMobile = document.getElementById('specialiteMobile');
			if (champDesktop) { champDesktop.value = valeur; }
			if (!selectMobile) { return; }
			selectMobile.value = valeur;
			if (selectMobile.value !== valeur && saisieMobile) {
				saisieMobile.value = valeur;
			}
		}

		// Envoie la requête AJAX avec le mot-clé, le mode strict et la filière choisie.
		function rechercherSpecialites() {
			var specialite = valeurSpecialiteRecherche();
			var rechercheStricte = document.getElementById('rechercheStricte').checked;
			var filiere = document.getElementById('filiereSpecialite').value;
			var resume = document.getElementById('resumeRecherche');
			var output = document.getElementById('listeEcoles');
			if (specialite.length < 2) {
				resume.textContent = 'Saisissez au moins 2 caractères ou choisissez un mot-clé dans la liste.';
				output.innerHTML = '';
				masquerChoixAffichageSpecialite();
				masquerTermesRechercheMobile();
				return;
			}
			resume.textContent = 'Recherche en cours...';
			output.innerHTML = '';
			masquerChoixAffichageSpecialite();
			masquerTermesRechercheMobile();

			var httpRequest = new XMLHttpRequest();
			httpRequest.onreadystatechange = function() {
				if (httpRequest.readyState === 4 && httpRequest.status === 200) {
					var data = JSON.parse(httpRequest.responseText);
					afficherResultatsSpecialite(data);
				} else if (httpRequest.readyState === 4) {
					resume.textContent = 'Erreur pendant la recherche.';
				}
			};
			// Une modification du mot-cle ou du mode revient au moteur de recherche habituel.
			var domaine = domainePanoramaSpecialite && rechercheStricte && specialite === motClePanoramaSpecialite ? domainePanoramaSpecialite : null;
			httpRequest.open('GET', 'php/lireEcoleParSpecialite.php?q=' + encodeURIComponent(specialite) + (rechercheStricte ? '&strict=1' : '') + (filiere ? '&filiere=' + encodeURIComponent(filiere) : '') + (domaine ? '&domaine=' + encodeURIComponent(domaine) : ''), true);
			httpRequest.send();
		}

		// Synchronise l'état visuel et accessible du switch Bootstrap.
		function setRechercheStricte(actif) {
			var interrupteur = document.getElementById('rechercheStricte');
			interrupteur.checked = actif;
			interrupteur.setAttribute('aria-label', actif ? 'Désactiver la recherche stricte' : 'Activer la recherche stricte');
			document.getElementById('etatRechercheStricte').textContent = actif ? 'ON' : 'OFF';
		}

		// Inverse l'état du switch sans lancer automatiquement de recherche.
		function basculerRechercheStricte() {
			setRechercheStricte(!document.getElementById('rechercheStricte').checked);
		}

		// Met à jour le libellé après une modification native de la checkbox.
		function actualiserRechercheStricte() {
			setRechercheStricte(document.getElementById('rechercheStricte').checked);
		}

		// Affiche seulement les totaux dans le resume ; le mode et les termes restent
		// disponibles dans un bloc replie par defaut, sur ordinateur comme sur mobile.
		function afficherResultatsSpecialite(data) {
			var resume = document.getElementById('resumeRecherche');
			var output = document.getElementById('listeEcoles');
			output.innerHTML = '';
			if (!data.results || data.results.length === 0) {
				resultatsSpecialite = [];
				resume.textContent = 'Aucune école trouvée pour cette recherche.';
				masquerChoixAffichageSpecialite();
				masquerTermesRechercheMobile();
				positionnerResultatsSpecialites();
				return;
			}

			resultatsSpecialite = data.results;
			etatTriSpecialite = { colonne: 'pertinence', direction: 'desc' };
			var termesRecherche = (data.terms || []).join(', ');
			var filtreFiliere = data.filiere ? '. Filière CPGE : ' + String(data.filiere).toUpperCase() : '';
			var totalEcoles = data.totalEcoles !== undefined ? data.totalEcoles : new Set(data.results.map(function (resultat) { return resultat.ecole; })).size;
			var totalSpecialites = data.totalSpecialites !== undefined ? data.totalSpecialites : data.results.length;
			var bilan = totalSpecialites.toLocaleString('fr-FR') + (totalSpecialites === 1 ? ' spécialité proposée par ' : ' spécialités proposées par ') + totalEcoles.toLocaleString('fr-FR') + (totalEcoles === 1 ? ' école' : ' écoles');
			var mode = data.domaine ? 'strict sur libellé, avec synonymes du domaine' : (data.strict ? 'strict' : 'étendu');
			resume.textContent = bilan;
			afficherTermesRechercheMobile(termesRecherche, mode + filtreFiliere, data.query || valeurSpecialiteRecherche());
			afficherChoixAffichageSpecialite();
			dessinerResultatsSpecialites();
			positionnerResultatsSpecialites();
		}

		// Recale l'arrivee apres l'AJAX puis la mise en page finale, sans deplacer les recherches suivantes.
		function positionnerResultatsSpecialites() {
			if (!positionnerResultatsAuChargement) { return; }
			positionnerResultatsAuChargement = false;
			var chargement = new Promise(function (resolve) {
				if (document.readyState === 'complete') { resolve(); }
				else { window.addEventListener('load', resolve, { once: true }); }
			});
			chargement.then(function () {
				return document.fonts ? document.fonts.ready : undefined;
			}).then(function () {
				window.requestAnimationFrame(function () {
					window.requestAnimationFrame(recalerResultatsSpecialites);
				});
			});
		}

		// Respecte la marge CSS du menu fixe pour la cible de l'ancre.
		function recalerResultatsSpecialites() {
			document.getElementById('resultats').scrollIntoView({ block: 'start', behavior: 'instant' });
		}

		// Affiche les details sur tous les ecrans uniquement avec des resultats,
		// sans interpreter les mots-cles comme du HTML. Chaque recherche repart repliee.
		function afficherTermesRechercheMobile(termesRecherche, mode, motCle) {
			var bloc = document.getElementById('termesRechercheMobile');
			var detail = document.getElementById('detailTermesRecherche');
			var bouton = document.getElementById('boutonTermesRecherche');
			document.getElementById('listeTermesRechercheMobile').textContent = termesRecherche;
			document.getElementById('modeDetailRecherche').textContent = mode;
			document.getElementById('motCleDetailRecherche').textContent = motCle;
			bloc.classList.remove('d-none');
			detail.hidden = true;
			document.getElementById('iconeDetailRecherche').className = 'bi bi-plus';
			bouton.setAttribute('aria-expanded', 'false');
		}

		// Masque et referme les details pendant une nouvelle recherche, une
		// erreur de saisie ou une réponse sans résultat.
		function masquerTermesRechercheMobile() {
			var bloc = document.getElementById('termesRechercheMobile');
			var detail = document.getElementById('detailTermesRecherche');
			var bouton = document.getElementById('boutonTermesRecherche');
			bloc.classList.add('d-none');
			detail.hidden = true;
			document.getElementById('iconeDetailRecherche').className = 'bi bi-plus';
			bouton.setAttribute('aria-expanded', 'false');
		}

		// Bascule le détail sans dépendre du plugin Collapse Bootstrap ou de son CDN.
		function initialiserDetailTermesRecherche() {
			var detail = document.getElementById('detailTermesRecherche');
			var bouton = document.getElementById('boutonTermesRecherche');
			var icone = document.getElementById('iconeDetailRecherche');
			bouton.addEventListener('click', function () {
				detail.hidden = !detail.hidden;
				var ouvert = !detail.hidden;
				bouton.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
				icone.className = ouvert ? 'bi bi-dash' : 'bi bi-plus';
			});
		}

		// Les tooltips restent utiles au survol sur desktop, mais sont neutralisés sur
		// smartphone où il n'y a pas de hover fiable. Les titres sont mémorisés dans
		// data-tooltip-title pour être restaurés si la largeur repasse en desktop.
		function actualiserTooltipsAffichage() {
			var desktop = window.matchMedia('(min-width: 992px)').matches;
			document.querySelectorAll('#choixAffichageSpecialite [data-bs-toggle="tooltip"], #choixAffichageSpecialite [data-tooltip-title]').forEach(function (bouton) {
				if (!bouton.dataset.tooltipTitle && bouton.getAttribute('title')) {
					bouton.dataset.tooltipTitle = bouton.getAttribute('title');
				}
				var instanceTooltip = bootstrap.Tooltip.getInstance(bouton);
				if (!desktop) {
					if (instanceTooltip) { instanceTooltip.dispose(); }
					bouton.removeAttribute('data-bs-toggle');
					bouton.removeAttribute('title');
				} else if (bouton.dataset.tooltipTitle) {
					bouton.setAttribute('data-bs-toggle', 'tooltip');
					bouton.setAttribute('title', bouton.dataset.tooltipTitle);
				}
			});
		}

		window.addEventListener('resize', actualiserTooltipsAffichage);
		document.addEventListener('DOMContentLoaded', actualiserTooltipsAffichage);

		// Les boutons liste/tableau/auto n'apparaissent qu'après une recherche utile.
		// Ils réutilisent js/basculeAffichage.js : auto = tableau desktop, cartes
		// mobile ; liste/tableau forcent le rendu quelle que soit la largeur.
		function afficherChoixAffichageSpecialite() {
			var choixAffichage = document.getElementById('choixAffichageSpecialite');
			choixAffichage.classList.remove('d-none');
			choixAffichage.classList.add('d-flex');
		}

		// En l'absence de résultats, on retire aussi d-flex : Bootstrap ferait sinon
		// gagner d-flex contre d-none selon l'ordre des classes.
		function masquerChoixAffichageSpecialite() {
			var choixAffichage = document.getElementById('choixAffichageSpecialite');
			choixAffichage.classList.remove('d-flex');
			choixAffichage.classList.add('d-none');
		}

		// Les deux rendus sont reconstruits à partir de la même réponse AJAX. Les
		// classes vue-tableau/vue-cartes laissent ensuite la bascule commune choisir
		// celui qui doit être visible.
		function dessinerResultatsSpecialites() {
			var output = document.getElementById('listeEcoles');
			output.innerHTML = '';
			output.appendChild(construireTableauSpecialites());
			output.appendChild(construireListeSpecialites());
			majIndicateursTriSpecialite();
		}

		// Construit le tableau complet, partage par l'affichage et l'export CSV.
		function construireTableauSpecialites() {
			var table = document.createElement('table');
				table.className = 'table table-specialites align-middle';
				table.id = 'tableau-specialites';
			var thead = document.createElement('thead');
			thead.innerHTML = '<tr>'
				+ '<th style="width:14%;"><span class="d-block">Pertinence</span><button id="triPertinence" type="button" class="btn btn-secondary btn-sm" title="Trier par pertinence" onclick="trierResultatsSpecialite(\'pertinence\')">&darr;</button></th>'
				+ '<th style="width:50%;">&nbsp;École - Diplôme&nbsp;&nbsp;<button id="triDiplome" type="button" class="btn btn-secondary btn-sm" title="Trier par école et diplôme" onclick="trierResultatsSpecialite(\'diplome\')">&darr;</button></th>'
				+ '<th style="width:32%;">Spécialité<br>Groupe de spécialités<br><em class="raison-selection">Raison de la sélection</em></th>'
				+ '<th class="text-center align-middle p-1" style="width:4%;"><button id="triSpecialite" type="button" class="btn btn-secondary btn-sm" title="Trier par spécialité" onclick="trierResultatsSpecialite(\'specialite\')">&darr;</button></th>'
				+ '</tr>';
			table.appendChild(thead);
			var tbody = document.createElement('tbody');
			var lignes = resultatsSpecialite;
			for (var i = 0; i < lignes.length; ++i) {
				var r = lignes[i];
				var tr = document.createElement('tr');
				var ecoleDiplome = libelleEcoleDiplome(r.ecole, r.diplome);
				var groupe = r.groupe ? '<br><span class="text-muted small">' + echapperHtml(r.groupe) + '</span>' : '';
				var correspondances = libelleCorrespondances(r.matches || []);
				tr.innerHTML = '<td style="text-align:center"><span class="badge badge-pertinence ' + classePertinence(r.score) + '">' + libellePertinence(r.score) + '</span></td>'
					+ '<td style="padding-left:10px;font-size:95%"><a href="' + r.detail_url + '"><strong>' + echapperHtml(ecoleDiplome) + '</strong></a></td>'
					+ '<td style="padding-left:10px;font-size:95%" data-csv="' + echapperHtml(r.specialite) + '">' + echapperHtml(r.specialite) + groupe + correspondances + '</td>'
					+ '<td class="text-center align-middle p-1"></td>';
				tbody.appendChild(tr);
			}
			table.appendChild(tbody);
			var tableauResponsive = document.createElement('div');
			tableauResponsive.className = 'table-responsive d-none d-lg-block vue-tableau';
			tableauResponsive.appendChild(table);
			return tableauResponsive;
		}

		// Vue smartphone : une carte par spécialité trouvée, pour éviter les colonnes
		// étroites et rendre les libellés longs lisibles en portrait.
		function construireListeSpecialites() {
			var liste = document.createElement('div');
			liste.className = 'd-lg-none vue-cartes';
			var lignes = resultatsSpecialite;
			for (var i = 0; i < lignes.length; ++i) {
				var r = lignes[i];
				var carte = document.createElement('div');
				var ecoleDiplome = libelleEcoleDiplome(r.ecole, r.diplome);
				var groupe = r.groupe ? '<div class="text-muted small mt-1">Groupe : ' + echapperHtml(r.groupe) + '</div>' : '';
				var correspondances = libelleCorrespondances(r.matches || []);
				carte.className = 'carte-specialite p-2 mb-2 border rounded bg-white';
				carte.innerHTML = '<div class="d-flex justify-content-between align-items-start gap-2">'
					+ '<a href="' + r.detail_url + '" class="fw-bold">' + echapperHtml(ecoleDiplome) + '</a>'
					+ '<span class="badge badge-pertinence ' + classePertinence(r.score) + '">' + libellePertinence(r.score) + '</span>'
					+ '</div>'
					+ '<div class="mt-2"><strong>' + echapperHtml(r.specialite) + '</strong></div>'
					+ groupe
					+ correspondances;
				liste.appendChild(carte);
			}
			return liste;
		}

		// Trie localement les résultats selon la colonne sélectionnée.
		function trierResultatsSpecialite(colonne) {
			var direction = 'asc';
			if (etatTriSpecialite.colonne === colonne) {
				direction = etatTriSpecialite.direction === 'asc' ? 'desc' : 'asc';
			} else if (colonne === 'pertinence') {
				direction = 'desc';
			}
			etatTriSpecialite = { colonne: colonne, direction: direction };

			resultatsSpecialite.sort(function (a, b) {
				var comparaison;
				if (colonne === 'pertinence') {
					comparaison = Number(a.score || 0) - Number(b.score || 0);
				} else {
					var valeurA = colonne === 'diplome' ? libelleEcoleDiplome(a.ecole, a.diplome) : a[colonne];
					var valeurB = colonne === 'diplome' ? libelleEcoleDiplome(b.ecole, b.diplome) : b[colonne];
					comparaison = String(valeurA || '').localeCompare(String(valeurB || ''), 'fr', { sensitivity: 'base' });
				}
				return direction === 'asc' ? comparaison : -comparaison;
			});
			dessinerResultatsSpecialites();
		}

		// Met à jour les flèches indiquant la colonne et le sens du tri.
		function majIndicateursTriSpecialite() {
			var ids = { pertinence: 'triPertinence', diplome: 'triDiplome', specialite: 'triSpecialite' };
			for (var colonne in ids) {
				var bouton = document.getElementById(ids[colonne]);
				if (bouton) {
					var colonneActive = etatTriSpecialite.colonne === colonne;
					bouton.innerHTML = colonneActive && etatTriSpecialite.direction === 'asc' ? '&uarr;' : '&darr;';
					bouton.classList.toggle('btn-primary', colonneActive);
					bouton.classList.toggle('btn-secondary', !colonneActive);
					bouton.setAttribute('aria-label', colonneActive ? 'Colonne triée, inverser le tri' : 'Trier par cette colonne');
				}
			}
		}

		// Convertit un score numérique en niveau de pertinence lisible.
		function libellePertinence(score) {
			if (score >= 100) { return 'Très forte'; }
			if (score >= 70) { return 'Forte'; }
			if (score >= 40) { return 'Moyenne'; }
			return 'Large';
		}

		// Associe une classe Bootstrap au niveau de pertinence.
		function classePertinence(score) {
			if (score >= 100) { return 'badge-pertinence-tres-forte'; }
			if (score >= 70) { return 'badge-pertinence-forte'; }
			if (score >= 40) { return 'badge-pertinence-moyenne'; }
			return 'badge-pertinence-large';
		}

		// Construit un libellé unique sans répéter l'école lorsque le diplôme
		// reprend déjà son nom ou lorsqu'il est identique à celui-ci.
		function libelleEcoleDiplome(ecole, diplome) {
			var nomEcole = String(ecole || '').trim();
			var nomDiplome = String(diplome || '').trim();
			if (!nomDiplome || nomDiplome.toLocaleLowerCase('fr-FR') === nomEcole.toLocaleLowerCase('fr-FR')) {
				return nomEcole;
			}
			if (nomDiplome.toLocaleLowerCase('fr-FR').indexOf(nomEcole.toLocaleLowerCase('fr-FR')) === 0) {
				return nomDiplome;
			}
			return nomEcole + ' - ' + nomDiplome;
		}

		// Formate les champs ayant déclenché la correspondance affichée.
		function libelleCorrespondances(matches) {
			if (!matches.length) {
				return '';
			}
			var champs = { Specialite: 'spécialité', Groupe: 'groupe', Diplome: 'diplôme' };
			var textes = matches.slice(0, 4).map(function (match) {
				return (champs[match.field] || match.field) + ' : ' + match.term;
			});
			return '<br><em class="text-muted raison-selection">' + echapperHtml(textes.join(', ')) + '</em>';
		}

		// Protège les valeurs issues de la base avant leur insertion dans le HTML.
		function echapperHtml(texte) {
			return String(texte).replace(/[&<>'"]/g, function (caractere) {
				return {'&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;'}[caractere];
			});
		}

	</script>

  </body>
</html>