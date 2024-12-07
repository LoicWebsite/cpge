<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Critères pour les statistiques SCEI d'admissions par filière aux écoles d'ingénieurs post prépa CPGE">

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
		<li class="breadcrumb-item"><a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php"><i class="fas fa-home"></i></a></li>
		<li class="breadcrumb-item active" aria-current="page">Filière</li>
	  </ol>
	</nav>

	<header class="container">
		<h1 class="h3"><i class="fa-solid fa-building-columns"></i>&nbsp;&nbsp;&nbsp;Statistiques par filière et concours</h1>
		<br/>
	</header>

	<main id="questionnaire" class="container">

		<!-- formulaire -->
		<form name="critere" action="resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php" method="GET" onsubmit="return controleSaisie();" onreset="return viderSelect();">

			<!-- critères -->
			<div class="border  bg-light p-2">
				<h2 class="h4" style="text-align:left;">1 - Saisissez vos critères de choix</h2>
				<br/>
		
				<!-- rang visé -->
<!-- 
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-3">
						<label for="rang" class="form-label">
							Rang visé :
							<i class="fas fa-info-circle" data-bs-toggle="tooltip" data-bs-html="true" title="Le <strong>Rang visé</strong> sert à mettre en évidence les écoles dont le rang d'admission est supérieur à celui-ci.<br/>Pour les années 2018 à 2021 la comparaison est effectuée avec le <strong>Rang médian</strong>.<br/>Pour les années 2017 et 2016 la comparaison est effectuée avec le <strong>Rang du dernier appelé</strong>.<br/>Cette saisie est facultative."></i>
						</label>
					</div>
					<div class="col-md-7">
						<input id="rang" name="rang" type="number" min="0" max="5000" class="form-control" placeholder="indifférent">
					</div>
					<div class="col-md-1">
					</div>
				</div>
 -->

				<!-- année des statistiques -->
<!-- 				<br/> -->
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-3">
						Année de référence : 
						<i class="fas fa-info-circle" data-bs-toggle="tooltip" data-bs-html="true" title="L'année de référence sert à afficher les résultats d'une année particulière.<br/><strong>'toutes'</strong> permet d'afficher toutes les années. C'est la valeur par défaut."></i>
					</div>
					<div class="col-md-7">	
						<div class="form-check form-check-inline">
							<input type="radio" id="all" name="reference" class="form-check-input" value="toutes" checked>
							<label class="form-check-label" for="all">toutes</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2024" name="reference" class="form-check-input" value="2024">
							<label class="form-check-label" for="an2024">2024</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2023" name="reference" class="form-check-input" value="2023">
							<label class="form-check-label" for="an2023">2023</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2022" name="reference" class="form-check-input" value="2022">
							<label class="form-check-label" for="an2022">2022</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2021" name="reference" class="form-check-input" value="2021">
							<label class="form-check-label" for="an2021">2021</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2020" name="reference" class="form-check-input" value="2020">
							<label class="form-check-label" for="an2020">2020</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2019" name="reference" class="form-check-input" value="2019">
							<label class="form-check-label" for="an2019">2019</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2018" name="reference" class="form-check-input" value="2018">
							<label class="form-check-label" for="an2018">2018</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2017" name="reference" class="form-check-input" value="2017">
							<label class="form-check-label" for="an2017">2017</label>
						</div>
						<div class="form-check form-check-inline">
							<input type="radio" id="an2016" name="reference" class="form-check-input" value="2016">
							<label class="form-check-label" for="an2016">2016</label>
						</div>
					</div>
					<div class="col-md-1">
					</div>
				</div>

				<!-- filière -->
				<br/>
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-3">
						<label for="filiere" class="form-label">Filière<sup>*</sup> :</label>
					</div>
					<div class="col-md-7">
						<select class="form-select" id="filiere" name="filiere" onchange="listerConcours(this.value,'');" required="required">
							<option id="toutes" value="" disabled selected hidden>sélectionner une filière</option>
							<?php
								// conexion à la base concours cpge
								try {
									$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PSWD");
								}
								catch(PDOException $erreur)	{
									die('Erreur connexion base : ' . $erreur->getMessage());
								}
								// passage au mode exception pour les erreurs
								$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
								$sql = "SELECT Filiere FROM Filiere ORDER BY Filiere ASC;";
								if ($debug) echo "SQL = " . $sql ."<br/>";
								try {
									$result = $db->query($sql);
									while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
										extract($row);
										echo "<option id='".$Filiere."' value='".$Filiere."'>".strtoupper($Filiere)."</option>";
									}
								}
								catch(PDOException $erreur)	{
									echo "Erreur SELECT Filiere : " . $erreur->getMessage();
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

				<!-- concours -->
				<br/>
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-3">
						<label for="concours" class="form-label">Concours :</label>
					</div>
					<div class="col-md-7">
						<select class="form-select" id="concours" name="concours" onchange="listerEcole(this.value,'');">
						</select>
					</div>
					<div class="col-md-1">
					</div>
				</div>

				<!-- écoles -->
				<br/>
				<div class="row">
					<div class="col-md-1">
					</div>
					<div class="col-md-3">
						<label for="ecole" class="form-label">Ecole :</label>
					</div>
					<div class="col-md-7">
						<select class="form-select" id="ecole" name="ecole">
						</select>
					</div>
					<div class="col-md-1">
					</div>
				</div>
				<br/>

				<span style='font-style:italic;'><sup>*</sup><small>la sélection de la filière est obligatoire.</small></span>
			</div>

			<!-- validation du formulaire -->
			<br/><br/>
			<nav class="border bg-light p-2">
				<h2 class="h4" style="text-align:left;">2 - Visualiser les statistiques d'admissions</h2>

				<div class="text-center">
					<div>
						<button type="submit" class="btn btn-primary mt-1 mb-5">Voir les statistiques</button>
					</div>
				</div>
				<div class="text-center">
					<em>Effacer tous les critères pour recommencer une nouvelle recherche</em>
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
 		
 		// positionnement des select et radio à partir des paramètres passés à l'URL
 		<?php
//  			if (($rang <> "") and ($rang <> "0") and ($rang <> "aucun")) {echo "document.getElementById('rang').value = '" . $rang . "';\n";}
 			if (($reference <> "") and ($reference <> "toutes")) {echo "document.getElementById('an".$reference."').checked = true;\n";}

			// wait 1 seconde nécessaire, sinon le call back lireEcole n'a pas le temps de répondre
 			if (($concours <> "") and ($concours <> "tous")) {
 				echo "setTimeout(function(){listerEcole('".$concours."','".supprimerApostrophe($ecole)."')},500);\n";
 			}

 			if (($filiere <> "") and ($filiere <> "toutes")) {
 				echo "document.getElementById('" . $filiere . "').selected = true;\n";
 				echo "listerConcours('".$filiere."','".$concours."');\n";
 			}
 

 		?>
 		}
	</script>
	
	<!-- requêtes AJAX pour charger les concours et les écoles -->	
	<script>

		// chargement des concours pour la filière sélectionnée en appelant un script sur le serveur via AJAX
		function listerConcours(filiereChoisi, concoursChoisi) {

            // remise à blanc de l'éventuel message d'erreur sur la filière
            critere.filiere.setCustomValidity('');
			critere.filiere.reportValidity();

//console.log("filière ="+filiereChoisi+"- concours="+concoursChoisi+".");
			// création de la requête AJAX
	  		httpRequest = new XMLHttpRequest();
			if (!httpRequest) {
			  alert('Abandon :( Impossible de créer une instance de XMLHTTP pour les concours');
			  return false;
			}

			// fonction qui traite le retour de la requête
	  		httpRequest.onreadystatechange = function() {

//console.log("fonction appelée");
				// si la requête est terminée et qu'elle s'est bien passée
				if (httpRequest.readyState === 4 && httpRequest.status === 200) {

					// effacement des anciens concours présents dans le SELECT Concours
					var listeConcours = document.getElementById('concours');
					listeConcours.options.length=0;
				
					// effacement des anciennes écoles présentes dans le SELECT Ecole
					var listeEcole = document.getElementById('ecole');
					listeEcole.options.length=0;
				
					// chargement des nouveaux concours correspondants à la filière sélectionnée
					try {
						var lesConcours = JSON.parse(httpRequest.responseText);
					} catch(erreur) {
						alert('erreur parse : ' + erreur);
					}
					var indexConcours = 0;
					for (index = 0; index < lesConcours.options.length; ++index) {
						option = lesConcours.options[index];
						if (concoursChoisi != '') {
							if (option.text == concoursChoisi) {
								indexConcours = index;
							}
						}
						listeConcours.add(new Option(option.text, option.value),null);
					}
					listeConcours.options[indexConcours].selected=true;
				}
//console.log("readyState="+httpRequest.readyState+" - status="+httpRequest.status+".");
	  		};

			// appel de la requête
	  		httpRequest.open("GET", "php/lireConcours.php?filiere="+filiereChoisi, true);
	  		httpRequest.send();
		}

		// chargement des écoles pour le concours sélectionné en appelant un script sur le serveur via AJAX
		function listerEcole(concoursChoisi,ecoleChoisi) {
	  		
			// récupération de la filière pour la passer en paramètre du script PHP
			var filiereChoisi = document.getElementById('filiere').options[document.getElementById('filiere').selectedIndex].value;			
			
			// création de la requête AJAX
	  		httpRequest = new XMLHttpRequest();
			if (!httpRequest) {
			  alert('Abandon :( Impossible de créer une instance de XMLHTTP pour les écoles');
			  return false;
			}

			// fonction qui traite le retour de la requête
	  		httpRequest.onreadystatechange = function () {

				// si la requête est terminée et qu'elle s'est bien passée
				if (httpRequest.readyState === 4 && httpRequest.status === 200) {

					// effacement des anciennes écoles présentes dans le SELECT
					var listeEcole = document.getElementById('ecole');
					listeEcole.options.length=0;

					// chargement des nouvelles écoles correspondantes au concours sélectionné
					try {
						var ecoles = JSON.parse(httpRequest.responseText);
					} catch(erreur) {
						alert('erreur parse : ' + erreur);
					}
					var indexEcole = 0;
					for (index = 0; index < ecoles.options.length; ++index) {
						option = ecoles.options[index];
						if (ecoleChoisi != '') {
							if (option.text == ecoleChoisi) {
								indexEcole = index;
							}
						}
						listeEcole.add(new Option(option.text, option.value),null);
					}
					listeEcole.options[indexEcole].selected=true;
				}	  		
	  		};

			// appel de la requête
	  		httpRequest.open("GET", "php/lireEcole.php?filiere="+filiereChoisi+"&concours="+concoursChoisi, true);
	  		httpRequest.send();
		}

	</script>
	
	<!-- activation tooltip Bootstrap 5 -->
	<script>
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl)
			})		
	</script>

    <script>

		//contrôle de saisie avant envoi du formulaire : au moins un des 2 champ renseigné
        function controleSaisie() {
            if (critere.filiere.value == 'toutes') {
                critere.filiere.setCustomValidity('Veuillez sélectionner une filière.');
				critere.filiere.reportValidity();
                return false;
            }
            else {
                critere.filiere.setCustomValidity('');
				critere.filiere.reportValidity();
                critere.submit();
            }
        }

		// vider les select au reset du formulaire
        function viderSelect() {
			critere.concours.length = 0;
			critere.ecole.length = 0;
			return true;
		}
    </script>	

  </body>
</html>