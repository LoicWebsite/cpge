	<?php
		//$debug="true";	
		
		// titre de la page
		echo "<header class='container'>";
		echo "<h1 class='h3'><i class='fa-solid fa-building-columns'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions " . strtoupper($filiere) . "<br/>";

		// conexion à la base cpge
		try {
			$db = new PDO("mysql:host=localhost;dbname=cpge;charset=utf8", "USER", "PASSE");
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// passage au mode exception pour les erreurs
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

		// construction de la clause WHERE
		$where = " WHERE An<>'' AND An<>0 ";
		if (($filiere <> "") and ($filiere <> "toutes")) {
			$where = $where . " AND Note.Filiere='" . $filiere . "'";
		}
		if (($concours <> "") and ($concours <> "tous")) {
			$where = $where . " AND Note.Concours='" . $concours . "'";
		}
		if (($an <> "") and ($an <> "0") and ($an <> "toutes")) {
			$where = $where . " AND Note.An='" . $an . "'" ;
		}
		if (($ecole <> "") and ($ecole <> "toutes")) {
			$where = $where . " AND Note.Ecole='" . remettreEsperluete(supprimerApostrophe($ecole)) . "'";
		}
		
		// exécution de la requête SQL
		$sql = "SELECT  Filiere,
						Concours,
						Ecole,
						An,
						Place,
						Inscrit,
						Classe,
						Integre,
						RangMedian,
						RangMoyen,						
						Dernier
				FROM Note" . $where . " ORDER BY Filiere ASC, Concours ASC, Ecole ASC, An DESC;";

		if ($debug) echo "SQL = " . $sql ."<br/>";
		try {
			$result = $db->query($sql);

			// affichage du titre
			if (($concours <> "tous") and ($concours <> "")) {
				if (($ecole <> "") and ($ecole <> "toutes")) {
					echo " pour l'école " . $ecole;
				} else {
					echo " pour le concours " . $concours;
				}
				echo "<br/>";
				if (($an <> "toutes") and ($an <> 0) and ($an <> '')) {
					echo " en " . $an;
				} else {
					echo " de 2016 à 2023";
				}
			} else {
				echo "<br/>";
				if (($an <> "toutes") and ($an <> 0) and ($an <> '')) {
					echo " en " . $reference;
				} else {
					echo " de 2016 à 2023";
				}
			}
			echo "</h1><br/>";
			echo "</header>";

			// recherche du groupe et du rang de l'école dans le classement Etudiant 2022
			$sqlEcole = "SELECT Rang AS RangEtudiant2022, Groupe AS GroupeEtudiant2022, UrlEcole, UrlEtudiant FROM Classement WHERE Ecole=(SELECT DISTINCT(EcoleClassement) FROM Ecole WHERE Ecole.Ecole='" . remettreEsperluete(supprimerApostrophe($ecole)) . "') AND An='2022';";
			if ($debug) echo "SQLEcole = " . $sqlEcole ."<br/>";
			try {
				$resultEcole = $db->query($sqlEcole);
				while ($rowEcole = $resultEcole->fetch(PDO::FETCH_ASSOC)) {
					extract($rowEcole);
				}
			}
			catch(PDOException $erreur)	{
				echo "Erreur SELECT Etudiant 2022 : " . $erreur->getMessage();
			}

			// recherche du groupe et du rang de l'école dans le classement Etudiant 2023
			$sqlEcole = "SELECT Rang AS RangEtudiant2023, Groupe AS GroupeEtudiant2023 FROM Classement WHERE Ecole=(SELECT DISTINCT(EcoleClassement) FROM Ecole WHERE Ecole.Ecole='" . remettreEsperluete(supprimerApostrophe($ecole)) . "') AND An='2023';";
			if ($debug) echo "SQLEcole = " . $sqlEcole ."<br/>";
			try {
				$resultEcole = $db->query($sqlEcole);
				while ($rowEcole = $resultEcole->fetch(PDO::FETCH_ASSOC)) {
					extract($rowEcole);
				}
			}
			catch(PDOException $erreur)	{
				echo "Erreur SELECT Etudiant 2023 : " . $erreur->getMessage();
			}

			// recherche du groupe et du rang de l'école dans le classement DAUR 2022
			$sqlEcole = "SELECT Rang AS RangDAUR2022, Groupe AS GroupeDAUR2022 FROM DAUR WHERE Ecole=(SELECT DISTINCT(EcoleClassement) FROM Ecole WHERE Ecole.Ecole='" . remettreEsperluete(supprimerApostrophe($ecole)) . "') AND An='2022';";
			if ($debug) echo "SQLEcole = " . $sqlEcole ."<br/>";
			try {
				$resultEcole = $db->query($sqlEcole);
				while ($rowEcole = $resultEcole->fetch(PDO::FETCH_ASSOC)) {
					extract($rowEcole);
				}
			}
			catch(PDOException $erreur)	{
				echo "Erreur SELECT DAUR 2022 : " . $erreur->getMessage();
			}

			// recherche du groupe et du rang de l'école dans le classement DAUR 2023
			$sqlEcole = "SELECT Rang AS RangDAUR2023, Groupe AS GroupeDAUR2023 FROM DAUR WHERE Ecole=(SELECT DISTINCT(EcoleClassement) FROM Ecole WHERE Ecole.Ecole='" . remettreEsperluete(supprimerApostrophe($ecole)) . "') AND An='2023';";
			if ($debug) echo "SQLEcole = " . $sqlEcole ."<br/>";
			try {
				$resultEcole = $db->query($sqlEcole);
				while ($rowEcole = $resultEcole->fetch(PDO::FETCH_ASSOC)) {
					extract($rowEcole);
				}
			}
			catch(PDOException $erreur)	{
				echo "Erreur SELECT DAUR 2023 : " . $erreur->getMessage();
			}

			// recherche du groupe et du rang de l'école dans le classement Le Figaro 2024
			$sqlEcole = "SELECT Rang AS RangFigaro2024 FROM Figaro WHERE Ecole=(SELECT DISTINCT(EcoleClassement) FROM Ecole WHERE Ecole.Ecole='" . remettreEsperluete(supprimerApostrophe($ecole)) . "') AND An='2024';";
			if ($debug) echo "SQLEcole = " . $sqlEcole ."<br/>";
			try {
				$resultEcole = $db->query($sqlEcole);
				while ($rowEcole = $resultEcole->fetch(PDO::FETCH_ASSOC)) {
					extract($rowEcole);
				}
			}
			catch(PDOException $erreur)	{
				echo "Erreur SELECT Figaro 2024 : " . $erreur->getMessage();
			}

			// section principale de la page
			echo "<main class='container'>";

			echo "<div class='p-3 border bg-light'>";
			
			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "Filière :";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			echo strtoupper($filiere);
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "Concours :";				
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			echo $concours;	
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "Ecole :";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			echo $ecole;
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "Site Web de l'école :";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			if (isset($UrlEcole)) {
				echo "<a href=" . $UrlEcole . " target=_blank>" . $UrlEcole . "</a>";
			}
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "Classements :";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			echo " ";
			echo "</div>";
			echo "</div>";
				
			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "&bull; l'Etudiant 2022 &nbsp;&nbsp;<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le classement du magasine L&apos;Etudiant en 2022 est réalisé en comptabilisant le nombre de points sur une cinquantaine de critères. 172 écoles y sont notées.<br>Le Groupe d&apos;appartenance de l&apos;école en 2022 est défini par le magasine L&apos;Etudiant selon la note obtenue par l&apos;école dans leur classement.<br/>A+ : 42 à 58 points<br/>A : 34 à 41 points<br/>B : 24 à 33 points<br/>C : 0 à 23 points'></i>";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			if (isset($GroupeEtudiant2022)) {
				echo $GroupeEtudiant2022;
			}
			echo " &nbsp; ";
			if (isset($RangEtudiant2022)) {
				if ($RangEtudiant2022 != "") {
					echo $RangEtudiant2022 . " / 172";
				}
			}
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "&bull; l'Etudiant 2023 &nbsp;&nbsp;<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le nombre de Points attribués est au maximum de 111 en 2023. Il résulte de l&apos;évaluation de 11 critères.<br>Le Rang est le résultat du classement par points (de 1 à 169).<br>Le Groupe d&apos;appartenance de l&apos;école en 2023 est désormais défini par le magasine L&apos;Etudiant comme étant un simple quartile.<br>A+ : 97 à 63 points<br>A : 62 à 51 points<br>B : 50 à 44 points<br>C : 0 à 43 points'></i>";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			if (isset($GroupeEtudiant2023)) {
				echo $GroupeEtudiant2023;
			}
			echo " &nbsp; ";
			if (isset($RangEtudiant2023)) {
				if ($RangEtudiant2023 != "") {
					echo $RangEtudiant2023 . " / 169";
				}
			}
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "&bull; DAUR 2022 &nbsp;&nbsp;<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2022 est définie par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 56<br>A : 55 à 49 points<br>BBB : 48 à 44<br>BB : 43 à 39<br>B : 39 à 35<br>CCC : 34 à 32<br>CC : 31 à 29<br>C : 29 à 0<br>Le Rang est le résultat du classement par note finale décroissante (de 1 à 176)<br>La note finale est attribuée à partie de 5 critères détaillés sur le site de DAUR Rankings.'></i>";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			if (isset($GroupeDAUR2022)) {
				echo $GroupeDAUR2022;
			}
			echo " &nbsp; ";
			if (isset($RangDAUR2022)) {
				if ($RangDAUR2022 != "") {
					echo $RangDAUR2022 . " / 176";
				}
			}
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "&bull; DAUR 2023 &nbsp;&nbsp;<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La notation de l&apos;école en 2023 est définie par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 54<br>A : 53 à 47 points<br>BBB : 46 à 41<br>BB : 40 à 37<br>B : 36 à 34<br>CCC : 33 à 31<br>CC : 30 à 28<br>C : 27 à 0<br>Le Rang est le résultat du classement par note finale décroissante (de 1 à 185)<br>La note finale est attribuée à partie de 5 critères détaillés sur le site de DAUR Rankings.'></i>";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			if (isset($GroupeDAUR2023)) {
				echo $GroupeDAUR2023;
			}
			echo " &nbsp; ";
			if (isset($RangDAUR2023)) {
				if ($RangDAUR2023 != "") {
					echo $RangDAUR2023 . " / 185";
				}
			}
			echo "</div>";
			echo "</div>";

			echo "<div class='row'>";
			echo "<div class='col-5 text-secondary'>";
			echo "&bull; Le Figaro 2024 &nbsp;&nbsp;<i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='Le Rang est le résultat du classement par note finale décroissante (de 1 à 87).<br>Les écoles sont notées en 2024 de 0 à 20. Cette note résulte de l&apos;évaluation de 14 critères.'></i>";
			echo "</div>";
			echo "<div class='col-7 text-primary'>";
			if (isset($RangFigaro2024)) {
				if ($RangFigaro2024 != "") {
					echo $RangFigaro2024 . " / 87";
				}
			}
			echo "</div>";
			echo "</div>";

// 				echo "<div class='row'>";
// 				echo "<div class='col-5 text-secondary'>";
// 				echo "Résumé de l'école par l'Etudiant :";
// 				echo "</div>";
// 				echo "<div class='col-7 text-primary'>";
// 				echo "<a href=" . $UrlEtudiant . " target=_blank>" . $UrlEtudiant . "</a>";
// 				echo "</div>";
// 				echo "</div>";
				
			echo "</div>";
			echo "<br/><hr><br/>";
				
			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				echo "<div class='row gy-4 justify-content-md-center'>";
			    echo "<div class='col-md'>";

				echo "<div class='p-3 border bg-light'>";

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "<strong>Année :</strong>";
				echo "</div>";
				echo "<div class='col-7 text-primary'><strong>";
				echo $An;
				echo "</strong></div>";
				echo "</div>";
				
				echo "<br/>";

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Nombre de places :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($Place,0);
				echo "</div>";
				echo "</div>";

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Nombre d'inscrits :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($Inscrit,0);
				echo "</div>";
				echo "</div>";
				
				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Nombre de classés :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($Classe,0);
				echo "</div>";
				echo "</div>";

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Nombre d'intégrés :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($Integre,0);
				echo "</div>";
				echo "</div>";

				echo "</div></div>";
								
			    echo "<div class='col-md'>";
				echo "<div class='p-3 border bg-light'>";
	
				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Rang médian des admis :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($RangMedian,0);
				echo "</div>";
				echo "</div>";


				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Rang moyen des admis :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($RangMoyen,0);
				echo "</div>";
				echo "</div>";

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Rang du dernier admis :";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				echo formater($Dernier,0);
				echo "</div>";
				echo "</div>";

				echo "<br/>";
	
				$selectivite = "";
				$selectiviteMediane = "";
				if ($Inscrit <> 0) {
					if (($Dernier <>0) and ($Dernier <> "") ) {
						$selectivite = ($Dernier / $Inscrit) * 100; 
					}
					if (($RangMedian <> 0) and ($RangMedian <> "")) {
						$selectiviteMediane = ($RangMedian / $Inscrit) * 100;
					}
				}

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Sélectivité médiane : <i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La sélectivité médiane est le rapport rang médian divisé par le nombre d&apos;inscrits.'></i>";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				if ($selectiviteMediane <> "") {
					echo formater($selectiviteMediane,1)."%";
				}
				echo "</div>";
				echo "</div>";

				echo "<div class='row'>";
				echo "<div class='col-5 text-secondary'>";
				echo "Sélectivité : <i class='fas fa-info-circle' data-bs-toggle='tooltip' data-bs-html='true' title='La sélectivité est le rapport rang du dernier admis divisé par le nombre d&apos;inscrits.'></i>";
				echo "</div>";
				echo "<div class='col-7 text-primary'>";
				if ($selectivite <> "") {
					echo formater($selectivite,1)."%";
				}
				echo "</div>";
				echo "</div>";

				echo "</div></div>";
				echo "</div>";
				echo "<br/>";
				echo "<hr>";
				echo "<br/>";
			}
			echo "</main>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}

		// fermeture de la base
		if (isset($result)) {$result->closeCursor();}
		$db = null;	
	?>
