<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Critères pour les statistiques SCEI d'admissions par école aux écoles d'ingénieurs post prépa CPGE">
		<!-- Canonical fixe: cette page est un formulaire de critères. -->
		<link rel="canonical" href="https://loic.website/CPGE/statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php" />

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>
    
    <title>Critères admissions en écoles d'ingénieurs</title>
    
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

		// Année de référence conservée au retour depuis la page résultat (voir
		// questionnaire() dans resultat-d-integration-ecole-d-ingenieur-par-ecole...).
		$anneeEcole = isset($_GET['anneeEcole']) ? trim($_GET['anneeEcole']) : 'toutes';
		$anneeEcole = preg_replace('/[^0-9a-zA-Z]/', '', $anneeEcole);
		$anneeEcole = substr($anneeEcole, 0, 6);
		if (!in_array($anneeEcole, $allowed_years, true)) {
			$anneeEcole = 'toutes';
		}
	?>

	<nav class="container" style='margin-top:80px;' aria-label="breadcrumb">
	  <ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
				<i class="bi bi-house-door-fill"></i>
			</a>
		</li>
		<li class="breadcrumb-item active" aria-current="page">Ecole</li>
	  </ol>
	</nav>

	<header class="container">
		<h1 class="h3">
			<i class="bi bi-mortarboard-fill"></i>
			&nbsp;&nbsp;&nbsp;Statistiques par école</h1>
		<br/>
	</header>

	<main id="questionnaire" class="container">

		<!-- formulaire -->
		<form name="critere" action="resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php" method="GET" onsubmit="return controleSaisie();">

			<!-- critères -->
			<div class="border bg-light p-2">
				<h2 class="h4" style="text-align:left;">1 - Saisir l'école recherchée</h2>
				<br/>

				<!-- Année de référence : ne sert qu'à raccourcir la liste ci-dessous aux écoles
					 ayant publié un résultat cette année-là ; les statistiques affichées ensuite
					 restent toutes années confondues. -->
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-4">
						Année de référence :
						<i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" data-bs-html="true" title="Limite la liste des écoles ci-dessous à celles ayant publié un résultat pour cette année.<br/><strong>'toutes'</strong> affiche toutes les écoles connues, quelle que soit l'année. C'est la valeur par défaut."></i>
					</div>
					<div class="col-md-6">
						<div class="form-check form-check-inline">
							<input type="radio" id="anEcoleToutes" name="anneeEcole" class="form-check-input" value="toutes" <?= ($anneeEcole === 'toutes') ? 'checked' : '' ?> onchange="rafraichirListeEcoles(this.value)">
							<label class="form-check-label" for="anEcoleToutes">toutes</label>
						</div>
						<?php foreach (range(2025, 2016) as $anneeRadio): ?>
						<div class="form-check form-check-inline">
							<input type="radio" id="anEcole<?= $anneeRadio ?>" name="anneeEcole" class="form-check-input" value="<?= $anneeRadio ?>" <?= ($anneeEcole === (string) $anneeRadio) ? 'checked' : '' ?> onchange="rafraichirListeEcoles(this.value)">
							<label class="form-check-label" for="anEcole<?= $anneeRadio ?>"><?= $anneeRadio ?></label>
						</div>
						<?php endforeach; ?>
					</div>
					<div class="col-md-1">
					</div>
				</div>

				<!-- Select avec la liste des écoles -->
				<br/>
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-4">
						<label for="ecole" class="form-label">Sélectionner une école dans la liste<sup>*</sup> :</label>
					</div>
					<div class="col-md-6">
						<select name="ecole" id="ecole" class="form-select ecole">
							<option value="">Choisir une école dans la liste</option>
							<?php
								// conexion à la base concours cpge
								try {
									$db = openDatabase();
								}
								catch(PDOException $erreur)	{
									die('Erreur connexion base : ' . $erreur->getMessage());
								}
																$sql = "SELECT DISTINCT EcoleConcours AS Ecole FROM EcoleConcours ORDER BY EcoleConcours ASC";
								if ($debug) echo "SQL = " . $sql ."<br/>";
								try {
									$result = $db->prepare($sql);
									$result->execute();
									while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
										extract($row);
										$libelleEcole = escapeHtml($Ecole);
										echo "<option value='".$libelleEcole."'>".$libelleEcole."</option>";
									}
								}
								catch(PDOException $erreur)	{
									echo "Erreur SELECT Ecole : " . $erreur->getMessage();
								}
								// fermeture de la base
								if (isset($result)) {$result->closeCursor();}
								$db = null;
							?>
						</select>
					</div>
					<div class="col-md-1">
					</div>
				</div>
				<br/>

				<!-- Input pour saisir l'école recherchée -->
				<br/>
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-4">
						<label for="recherche" class="form-label">Ou saisir le nom de l'école<sup>*</sup> :</label>
					</div>
					<div class="col-md-6">
						<input id="recherche" name="recherche" type="search" class="form-control" placeholder="saisir au moins 3 caractères ou le nom complet" autocomplete="off" minlength="3">
					</div>
					<div class="col-md-1">
					</div>
				</div>
				<br/>
				
				<span style='font-style:italic;'><sup>*</sup><small>la saisie d'un des 2 champs est obligatoire.</small></span>
			</div>

			<!-- validation du formulaire -->
			<br/><br/>
			<nav class="border bg-light p-2">
				<h2 class="h4" style="text-align:left;">2 - Visualiser les statistiques d'admission de l'école</h2>

				<div class="text-center">
					<div class="d-flex flex-wrap justify-content-center gap-3 mt-1 mb-3">
						<div>
							<button type="submit" class="btn btn-primary">
								<i class="bi bi-table"></i>&nbsp; Voir les statistiques
							</button>
							<div><small class="text-muted">Tableau comparatif année par année</small></div>
						</div>
						<div>
							<button type="button" id="ficheEcole" class="btn btn-primary" disabled>
								<i class="bi bi-bank2"></i>&nbsp; Voir la fiche de l'école
							</button>
							<div><small class="text-muted">Classements, filières et spécialités<sup>*</sup></small></div>
						</div>
					</div>
				</div>
				<div class="text-center">
					<em>Effacer l'école sélectionnée pour recommencer une nouvelle recherche</em>
					<br/><button name="reset" type="reset" class="btn btn-info mt-1 mb-5">Effacer les critères</button>
				</div>
				<span style='font-style:italic;'><sup>*</sup><small>la sélection d'une école dans la liste est obligatoire pour accéder à sa fiche.</small></span>
			</nav>
		</form>

	</main>

	<footer style='margin-top:40px; margin-bottom:80px;'>
		&nbsp;
	</footer>

	<?php
		// librairies javascript nécessaires à l'application (popper + bootstrap)
		include "php/librairie.php";
	?>

	<script>
 		window.onload = function () {
 		
 		// positionnement du select Ecole à partir de paramètre passé à l'URL
 		<?php
 			if (($ecole <> "") and ($ecole <> "toutes")) {
				echo 'document.getElementById("ecole").value = ' . encodeJs($ecole) . ';';
 			}
 			if ($recherche <> "") {
				echo 'document.getElementById("recherche").value = ' . encodeJs($recherche) . ';';
 			}
			// la liste des écoles doit refléter l'année conservée au retour, le radio
			// étant déjà coché côté serveur ci-dessus (rien à faire si 'toutes').
 			if ($anneeEcole !== "toutes") {
				echo 'rafraichirListeEcoles(' . encodeJs($anneeEcole) . ');';
 			}
 		?>
 		}
	</script>
	
	<script>
		// un seul champ de saisie doit être renseigné, donc on efface l'autre dès qu'il y a une saisie
		recherche.oninput = function() {
			document.getElementById('ecole').value = '';

            // effacement de l'éventuel message d'erreur précédent
            critere.ecole.setCustomValidity('');
			critere.ecole.reportValidity();

		};
		// 'change' est utilisé en plus de 'input' pour fiabiliser la détection
		// de la sélection sur un <select> (notamment sur mobile).
		function surChoixEcole() {
			document.getElementById('recherche').value = '';

            // effacement de l'éventuel message d'erreur précédent
            critere.ecole.setCustomValidity('');
			critere.ecole.reportValidity();
		}
		ecole.oninput = surChoixEcole;
		ecole.onchange = surChoixEcole;
	</script>
	
	<!-- contrôle de saisie avant envoi du formulaire : au moins un des 2 champ renseigné -->
    <script>
        function controleSaisie() {
			// le champ ecole est un <select> : toute valeur non vide provient forcément de la liste
			var optionFound = critere.ecole.value != '';

            if (critere.ecole.value == '' && critere.recherche.value == '') {
                critere.ecole.setCustomValidity('Veuillez sélectionner une école dans ce champ liste.\nOu bien saisir le nom d\'une école dans le champ d\'après.');
				critere.ecole.reportValidity();
                return false;	// on bloque l'envoi du formulaire
            }
            else if (critere.ecole.value != '' && !optionFound) {
                critere.ecole.setCustomValidity('Veuillez sélectionner une école valide dans la liste.');
				critere.ecole.reportValidity();    
				return false; 	// on bloque l'envoi du formulaire
            }
            else {
                critere.ecole.setCustomValidity('');
				critere.ecole.reportValidity();

				// N'envoie pas de paramètres vides dans l'URL pour limiter les doublons SEO.
				if (critere.ecole.value == '') {
					critere.ecole.disabled = true;
				}
				if (critere.recherche.value == '') {
					critere.recherche.disabled = true;
				}

                return true;  // on autorise l'envoi du formulaire
            }
        }
    </script>	

	<!-- accès direct à la fiche d'une école, seulement si une école exacte est sélectionnée -->
	<script>
		(function () {
			var champEcole = document.getElementById('ecole');
			var bouton = document.getElementById('ficheEcole');

			function ecoleValide() {
				// le champ ecole est un <select> : toute valeur non vide provient forcément de la liste
				return champEcole.value !== '';
			}

			function actualiser() {
				bouton.disabled = !ecoleValide();
			}

			champEcole.addEventListener('input', actualiser);
			champEcole.addEventListener('change', actualiser);
			document.getElementById('recherche').addEventListener('input', actualiser);
			document.querySelector('button[name="reset"]').addEventListener('click', function () {
				setTimeout(actualiser, 0);
			});

			bouton.addEventListener('click', function () {
				if (!ecoleValide()) { return; }
				window.location.href = 'detail-resultat-admission-par-ecole.php?ecole=' + encodeURIComponent(champEcole.value);
			});

			actualiser();
			window.addEventListener('load', actualiser);
		})();
	</script>

	<!-- rafraîchissement de la liste des écoles selon l'année de référence choisie -->
	<!-- réutilise php/lireEcole.php (déjà utilisé par la page filière), filiere/concours
		 laissés à leur valeur par défaut pour ne récupérer que le filtre par année. -->
	<script>
		function rafraichirListeEcoles(anChoisi) {
			const httpRequest = new XMLHttpRequest();
			httpRequest.onreadystatechange = function () {
				if (httpRequest.readyState !== 4) return;
				if (httpRequest.status !== 200) {
					console.error('Erreur AJAX', httpRequest.status, httpRequest.statusText);
					return;
				}
				try {
					const data = JSON.parse(httpRequest.responseText);
					const liste = document.getElementById('ecole');
					const valeurCourante = liste.value;
					liste.innerHTML = '';
					const optionVide = document.createElement('option');
					optionVide.value = '';
					optionVide.textContent = 'Choisir une école dans la liste';
					liste.appendChild(optionVide);
					(data.options || []).forEach(function (option) {
						if (option.value === 'toutes') return;
						const opt = document.createElement('option');
						opt.value = option.text;
						opt.textContent = option.text;
						liste.appendChild(opt);
					});
					// on conserve la sélection précédente si elle existe toujours dans la nouvelle liste
					liste.value = valeurCourante;
					if (liste.value !== valeurCourante) { liste.value = ''; }
				} catch (erreur) {
					console.error('Erreur parsing JSON', erreur, httpRequest.responseText);
				}
			};
			httpRequest.open('GET', 'php/lireEcole.php?filiere=toutes&concours=tous&an=' + encodeURIComponent(anChoisi), true);
			httpRequest.send();
		}
	</script>

	<!-- activation tooltip Bootstrap 5 -->
	<script>
		document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
			new bootstrap.Tooltip(element);
		});
	</script>

  </body>
</html>