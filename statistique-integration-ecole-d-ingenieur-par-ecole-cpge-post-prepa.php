<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Critères pour les statistiques SCEI d'admissions par école aux écoles d'ingénieurs post prépa CPGE">

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

				<!-- Select avec la liste des écoles -->
				<br/>
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-4">
						<label for="ecole" class="form-label">Sélectionner une école dans la liste<sup>*</sup> :</label>
					</div>
					<div class="col-md-6">
						<input list="ecoles" name="ecole" id="ecole" type="search" class="form-control ecole" placeholder="sélectionner une école" autocomplete="off"/>
						<datalist id="ecoles">
							<?php
								// conexion à la base concours cpge
								try {
									$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSWORD");
								}
								catch(PDOException $erreur)	{
									die('Erreur connexion base : ' . $erreur->getMessage());
								}
								// passage au mode exception pour les erreurs
								$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
								$sql = "SELECT DISTINCT Ecole FROM Ecole ORDER BY Ecole ASC;";
								if ($debug) echo "SQL = " . $sql ."<br/>";
								try {
									$libelleEcole = "";
									$result = $db->query($sql);
									while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
										extract($row);
										$libelleEcole = str_replace("'","&apos;",$Ecole);
										echo "<option data-name='".$libelleEcole."' value='".$libelleEcole."'>".$libelleEcole."</option>";
									}
								}
								catch(PDOException $erreur)	{
									echo "Erreur SELECT Ecole : " . $erreur->getMessage();
								}
								// fermeture de la base
								if (isset($result)) {$result->closeCursor();}
								$db = null;
							?>
						</datalist>
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
					<div>
						<button type="submit" class="btn btn-primary mt-1 mb-5">Voir les statistiques</button>
					</div>
				</div>
				<div class="text-center">
					<em>Effacer l'école sélectionnée pour recommencer une nouvelle recherche</em>
					<br/><button name="reset" type="reset" class="btn btn-info mt-1 mb-5">Effacer les critères</button>
				</div>
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
 				echo 'document.getElementById("ecole").value = "'.$ecole.'";';
 			}
 			if ($recherche <> "") {
 				echo 'document.getElementById("recherche").value = "' . $recherche . '";';
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
		ecole.oninput = function() {
			document.getElementById('recherche').value = '';

            // effacement de l'éventuel message d'erreur précédent
            critere.ecole.setCustomValidity('');
			critere.ecole.reportValidity();
		};
	</script>
	
	<!-- contrôle de saisie avant envoi du formulaire : au moins un des 2 champ renseigné -->
    <script>
        function controleSaisie() {
			var valeurSaisie = critere.ecole.value;
        	var optionFound = false;
    		for (var j = 0; j < ecoles.options.length; j++) {
      			if (valeurSaisie == ecoles.options[j].value) {
        			optionFound = true;
        			break;
      			}
    		}

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
                return true;  // on autorise l'envoi du formulaire
            }
        }
    </script>	

  </body>
</html>