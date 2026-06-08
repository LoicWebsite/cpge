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
	
  </head>
  <body id="hautdepage">

	<?php
		include "php/menu.php";

		// fonctions communes du site
		include "php/fonctionConcours.php";
	?>

	<nav class="container" style='margin-top:80px;' aria-label="breadcrumb">
	  <ol class="breadcrumb">
		<li class="breadcrumb-item">
			<a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
				<i class="bi bi-house-door-fill"></i>
			</a>
		</li>
		<li class="breadcrumb-item active" aria-current="page">Spécialité</li>
	  </ol>
	</nav>

	<header class="container">
		<h1 class="h3">
			<i class="bi bi-bank2"></i>
			&nbsp;&nbsp;&nbsp;Écoles par spécialité
		</h1>
		<br/>
	</header>

	<main id="questionnaire" class="container">

		<!-- formulaire de sélection -->
		<div class="border  bg-light p-2">
			<h2 class="h4" style="text-align:left;">Choisissez une spécialité</h2>
			<br/>
			<div class="row">
				<div class="col-md-1"></div>
				<div class="col-md-3">
					<label for="specialite" class="form-label">Spécialité<sup>*</sup> :</label>
				</div>
				<div class="col-md-7">
					<select class="form-select" id="specialite" name="specialite" onchange="listerEcolesSpecialite(this.value);" required>
						<option id="toutes" value="" disabled selected hidden>sélectionner une spécialité</option>
						<?php
						// récupération des spécialités disponibles
						try {
							$db = openDatabase();
							$sql = "SELECT DISTINCT Specialite FROM Specialite ORDER BY Specialite ASC";
							$result = $db->query($sql);
							while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
								extract($row);
								$sel = '';
								if (isset(
									$specialite) && ($specialite == $Specialite)) {
									$sel = ' selected';
								}
								echo "<option value='" . escapeHtml($Specialite) . "'" . $sel . ">" . escapeHtml($Specialite) . "</option>";
							}
							if (isset($result)) {$result->closeCursor();}
							$db = null;
						} catch (PDOException $erreur) {
							echo "<option value=''>Erreur";
						}
						?>
					</select>
				</div>
				<div class="col-md-1"></div>
			</div>
		</div>

		<!-- zone de résultat -->
		<br/>
		<div class="border bg-white p-2" id="resultats">
			<h2 class="h4" style="text-align:left;">Écoles proposant cette spécialité</h2>
			<ul id="listeEcoles"></ul>
		</div>

	</main>

	<footer style='margin-top:40px; margin-bottom:80px;'>
		&nbsp;
	</footer>

	<?php
		include "php/librairie.php"; // jquery + popper + bootstrap
	?>

	<script>
		window.onload = function() {
			<?php
			if (isset($specialite) && ($specialite != "")) {
				echo 'document.getElementById("specialite").value = ' . encodeJs($specialite) . ';' . "\n";
				echo 'listerEcolesSpecialite(' . encodeJs($specialite) . ');' . "\n";
			}
			?>
		}

		function listerEcolesSpecialite(specialite) {
			if (specialite == "") {
				return;
			}

			var httpRequest = new XMLHttpRequest();
			httpRequest.onreadystatechange = function() {
				if (httpRequest.readyState === 4 && httpRequest.status === 200) {
					var data = JSON.parse(httpRequest.responseText);
					var output = document.getElementById('listeEcoles');
					output.innerHTML = '';
					for (var i = 0; i < data.options.length; ++i) {
						var opt = data.options[i];
						if (opt.text === '') continue;
						var li = document.createElement('li');
						li.textContent = opt.text;
						output.appendChild(li);
					}
				}
			};
			httpRequest.open('GET', 'php/lireEcoleParSpecialite.php?specialite=' + encodeURIComponent(specialite), true);
			httpRequest.send();
		}
	</script>

  </body>
</html>