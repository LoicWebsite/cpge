<!doctype html>

<?php
	/**
	 * ============================================================================
	 * Page : Résultats d'intégration par filière (une ligne = une école pour
	 *        une année et un concours donnés, colonnes = métriques fixes)
	 * ============================================================================
	 *
	 * Contrairement à la page "par école", ici les années sont des LIGNES et non
	 * des colonnes : le tableau ne s'élargit jamais avec le temps, il s'allonge
	 * juste verticalement (scroll normal, même sur mobile). Le problème ergonomique
	 * est différent : jusqu'à 11 colonnes par ligne, trop large pour un écran étroit.
	 *
	 * Une première tentative de colonne "Ecole" fixe (position: sticky) a été
	 * abandonnée (bugs de rendu navigateur sur border-collapse + sticky, complexité
	 * disproportionnée). Solution retenue à la place :
	 *   - desktop (>= lg) : le tableau original est conservé à l'identique ;
	 *   - mobile/tablette (< lg) : le tableau est remplacé par une liste de cartes
	 *     empilées, une carte par ligne de données (école + année + concours), avec
	 *     les métriques affichées verticalement. Pas d'accordéon ici : chaque ligne
	 *     est déjà une donnée atomique, il n'y a rien à regrouper/replier.
	 *   Les deux rendus sont produits à partir de la MÊME lecture SQL (une seule
	 *   boucle qui alimente à la fois le tableau et le tableau PHP $lignesCartes).
	 *
	 * Sauvegarde d'avant cette modification :
	 * save/resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa-20260828-avant-cartes.php
	 * ============================================================================
	 */

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";

	// URL canonique: on garde uniquement les paramètres qui modifient le contenu de la page.
	$canonicalBase = 'https://loic.website/CPGE/resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php';
	$canonicalParams = [];
	if (($reference !== '') && ($reference !== 'toutes')) {
		$canonicalParams['reference'] = $reference;
	}
	if (($filiere !== '') && ($filiere !== 'toutes')) {
		$canonicalParams['filiere'] = $filiere;
	}
	if (($concours !== '') && ($concours !== 'tous')) {
		$canonicalParams['concours'] = $concours;
	}
	if (($ecole !== '') && ($ecole !== 'toutes')) {
		$canonicalParams['ecole'] = $ecole;
	}
	$canonicalUrl = $canonicalBase;
	if (!empty($canonicalParams)) {
		$canonicalUrl .= '?' . http_build_query($canonicalParams, '', '&', PHP_QUERY_RFC3986);
	}
?>

<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Statistique SCEI d'admissions par filière aux écoles d'ingénieurs post prépa CPGE">
		<link rel="canonical" href="<?php echo escapeHtml($canonicalUrl); ?>" />

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Admissions en écoles d'ingénieurs par filière</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>

	<!--
		Colonne Ecole fixée à gauche pendant le défilement horizontal du tableau.
		Contrairement à la 1ère tentative (abandonnée), on ne redéfinit pas
		border-collapse ni les couleurs à l'échelle du site : on bascule
		UNIQUEMENT ce tableau en "border-collapse: separate", comme le fait avec
		succès (sans bug de bordure) un tableau similaire du site ECN
		(ECN/tableau-specialite.php). C'est ce mode "separate" qui permet à
		position:sticky de fonctionner proprement avec des bordures visibles.
	-->
	<style>
		#tableau-par-filiere {
			border-collapse: separate;
			border-spacing: 0;
			/* css/concours.css impose une bordure sur <table> lui-même (en plus de
			   celle des cellules) : en border-collapse:separate, cette bordure
			   extérieure s'additionne à celle des cellules de bord (plus épaisse
			   en haut/gauche/droite, trait plein + pointillé superposés en bas).
			   On la retire : seules les bordures des cellules dessinent le cadre. */
			border-style: none;
		}
		/* En border-collapse:separate, chaque cellule dessine sa PROPRE bordure :
		   sans cette règle, une rupture d'école cumulerait la bordure basse
		   pointillée de la ligne précédente ET la bordure haute pleine de
		   .nouvelEcole (trait doublé, à la fois plein et pointillé superposés).
		   On ne garde donc qu'une bordure haute par ligne (jamais de bordure
		   basse), sauf sur la toute dernière ligne pour fermer le tableau. */
		#tableau-par-filiere td, #tableau-par-filiere th {
			border-bottom-style: none;
		}
		/* Trait plein pour fermer le tableau en bas (comme le cadre haut/gauche/
		   droite), au lieu du pointillé utilisé entre les lignes de données. */
		#tableau-par-filiere tbody tr:last-child td {
			border-bottom-style: solid;
		}
		/* Même principe pour les bordures verticales : chaque cellule a par
		   défaut border-left ET border-right (voir concours.css), ce qui double
		   l'épaisseur à chaque frontière de colonne. On ne garde qu'une seule
		   bordure par frontière (la gauche), sauf sur la toute dernière colonne
		   pour fermer le tableau à droite. */
		#tableau-par-filiere td, #tableau-par-filiere th {
			border-right-style: none;
		}
		#tableau-par-filiere td:last-child, #tableau-par-filiere th:last-child {
			border-right-style: solid;
		}
		#tableau-par-filiere .colonne-ecole-fixe {
			position: sticky;
			left: 0;
			background-color: #fff;
			/* sans z-index supérieur, les th/td plus à droite (même z-index:1 hérité
			   des th) passent AU-DESSUS de cette colonne quand ils la recouvrent en
			   défilant horizontalement -> l'en-tête Ecole semblait "disparaitre". */
			z-index: 2;
		}
		#tableau-par-filiere th.colonne-ecole-fixe {
			background-color: DarkSlateBlue;
			/* les th héritent aussi de "position:sticky; top:50px" (règle globale
			   th { ... }) : coller un même en-tête à la fois en haut ET à gauche
			   est peu fiable selon les navigateurs. On désactive donc le collage
			   vertical ici pour ne garder que le collage horizontal, comme sur
			   les cellules de données du corps du tableau. */
			top: auto;
		}
	</style>

  </head>
  <body id="hautdepage">

	<?php
		include "php/menu.php";
	?>
	<nav id="chemin" class="container">
		<div class="row" style='margin-top:80px;'>
			<div class="col-sm" aria-label="breadcrumb">
			  <ol class="breadcrumb">
				<li class="breadcrumb-item">
					<a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
						<i class="bi bi-house-door-fill"></i>
					</a>
				</li>
				<li class="breadcrumb-item"><a href="#" onclick="questionnaire()">Filière</a></li>
				<li class="breadcrumb-item active" aria-current="page">Statistiques</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- en tête du tableau -->

	<?php
//echo "ecole = " . $ecole . "<br>";

		// titre de la page
		echo "<header class='container'>";
		echo "<h1 class='h3'><i class='bi bi-bank2'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions " . strtoupper($filiere);


		// conexion à la base concours cpge
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// 1. Construction de la clause WHERE avec des marqueurs
		$where = " WHERE An<>'' AND An<>0 ";
		$params = []; // Tableau pour stocker les valeurs

		if (($filiere <> "") and ($filiere <> "toutes")) {
			$where .= " AND Filiere = :filiere";
			$params[':filiere'] = $filiere;
		}
		if (($concours <> "") and ($concours <> "tous")) {
			$where .= " AND Concours = :concours";
			$params[':concours'] = $concours;
		}
		if (($reference <> "") and ($reference <> "0") and ($reference <> "toutes")) {
			$where .= " AND An = :an";
			$params[':an'] = $reference;
		}
		if (($ecole <> "") and ($ecole <> "toutes")) {
			$where .= " AND EcoleConcours = :ecole";
			$params[':ecole'] = $ecole; // ON ENVOIE LA VALEUR BRUTE ICI
		}

	// exécution de la requête SQL (sans ORDER BY, le tri se fera en JavaScript)
	$sql = "SELECT  Filiere,
					Concours,
					EcoleConcours AS Ecole,
					An,
					Place,
					Inscrit,
					Classe,
					Integre,
					RangMedian,
					Dernier,
					ROUND(Dernier / Inscrit, 1) AS SelectiviteDernier,
					ROUND(RangMedian / Inscrit, 1) AS SelectiviteMediane
			FROM Note" . $where;
		if ($debug) echo "SQL = " . $sql ."<br/>";
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute($params);
			$result = $stmt; // On remplace l'ancien $result pour que la boucle while continue de fonctionner avec le prepared statement
			
			// affichage du titre
			if (($concours <> "tous") and ($concours <> "")) {
				if (($ecole <> "") and ($ecole <> "toutes")) {
					echo " pour l'école " . $ecole;
				} else {
					echo " pour le concours " . $concours;
				}
				echo "<br/>";
				if (($reference <> "toutes") and ($reference <> 0) and ($reference <> '')) {
					echo " en " . $reference;
				} else {
					echo " de 2016 à 2025";
				}
			} else {
				echo "<br/>";
				if (($reference <> "toutes") and ($reference <> 0) and ($reference <> '')) {
					echo " en " . $reference;
				} else {
					echo " de 2016 à 2025";
				}
			}
			echo "</h1><br/>";
			echo "</header>";

			// section principale de la page
			echo "<main class='container-fluid'>";

			// boutons de tri
			echo "<div class='d-flex justify-content-between'>";
			echo "</div><br/>";

			// Bascule manuelle "en liste" / "en tableau" / "auto", indépendante de la
			// largeur d'écran (voir js/basculeAffichage.js). "Auto" restaure le
			// comportement responsive habituel (tableau desktop / cartes mobile).
			// Le bouton correspondant au mode actif est désactivé (rien à faire en
			// le recliquant) ; gap-2 espace les boutons au lieu de les coller
			// (contrairement à un .btn-group classique).
			echo "<div class='d-flex gap-2 mb-3' role='group' aria-label=\"Choix de l'affichage\">";
			echo "<button type='button' class='btn btn-sm btn-secondary bouton-vue-liste' onclick=\"choisirVue('liste')\" data-bs-toggle='tooltip' title='Afficher les résultats sous forme de cartes empilées, quelle que soit la taille de l&apos;écran.'><i class='bi bi-list-ul'></i> liste</button>";
			echo "<button type='button' class='btn btn-sm btn-secondary bouton-vue-tableau' onclick=\"choisirVue('tableau')\" data-bs-toggle='tooltip' title='Afficher les résultats dans un tableau, quelle que soit la taille de l&apos;écran.'><i class='bi bi-table'></i> tableau</button>";
			echo "<button type='button' class='btn btn-sm btn-secondary bouton-vue-auto' onclick=\"choisirVue('auto')\" data-bs-toggle='tooltip' title='Choisir automatiquement selon la taille de l&apos;écran : tableau sur ordinateur, liste sur mobile.'><i class='bi bi-arrow-repeat'></i> auto</button>";
			echo "</div>";

			// affichage de l'en tête du tableau (desktop par défaut, ou forcé quel
			// que soit l'écran via .vue-tableau si l'utilisateur a cliqué "en tableau")
			// table-responsive : nécessaire pour le défilement horizontal, la colonne
			// Ecole restant fixe à gauche pendant ce défilement (voir <style> ci-dessus).
			echo "<div class='d-none d-lg-block vue-tableau'>";
			echo "<div class='table-responsive'>";
			echo "<table id='tableau-par-filiere'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une ligne pour voir le détail de cette école.";
			$idTableau = '"#tableau-par-filiere","concours;école;année;places;inscrits;intégrés;rang médian;sélectivité médiane;rang dernier;sélectivité"';
			echo "<br>Cliquer sur le bouton pour télécharger le tableau au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button></small></caption>";
			echo "<thead class='text-center'>";
			echo "<tr>";
			if (($filiere == "toute") or ($filiere == "")) {
				echo "<th>&nbsp;Filiere&nbsp;</th>";
			}
			if (($concours == "tous") or ($concours == "")) {
				echo "<th>&nbsp;<button id='concours' type='button' class='btn btn-secondary btn-sm' title='Trier par concours' onclick='triConcours()'>&darr;</button>&nbsp;&nbsp;Concours&nbsp;<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Lorsqu&apos;un concours a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>Exemple CCP devenu CCINP en 2019.'></i></th>";
			}
			echo "<th class='colonne-ecole-fixe'>&nbsp;<button id='ecole' type='button' class='btn btn-secondary btn-sm' title='Trier par école' onclick='triEcole()'>&darr;</button>&nbsp;&nbsp;Ecole&nbsp;<br>
											<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Lorsqu&apos;une école a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>
											<br/>&bull; Lorsque plusieurs écoles ont fusionné, les différentes écoles apparaissent séparément avant la fusion.<br/>
											<br/>&bull; Lorsqu&apos;une école change de concours, elle apparaît soit dans le nouveau concours soit dans l&apos;ancien suivant la date.<br/>
											<br/>&bull; A noter que le nom affiché est celui qui apparaît dans SCEI.'></i></th>";
			echo "<th>&nbsp;Année&nbsp;</th>";
			echo "<th>&nbsp;Places&nbsp;</th>";
			echo "<th>&nbsp;Inscrits&nbsp;<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-html='true' title='Le nombre d&apos;inscrits est soit celui de l&apos;école soit par défaut celui du concours.'></i></th>";
			echo "<th>&nbsp;Integrés&nbsp;</th>";
			echo "<th>&nbsp;Rang median&nbsp;<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Depuis 2018 les statistiques SCEI affichent le rang médian et le rang moyen. Pour simplifier la lecture ici, seul le rang médian est affiché.'></i></th>";
			echo "<th>&nbsp;<button id='selectivite' type='button' class='btn btn-secondary btn-sm' title='Trier par sélectivité médiane croissante' onclick='triSelectiviteMediane()'>&darr;</button>&nbsp;&nbsp;Sélectivité médiane&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Sélectivité médiane = Rang médian des admis / Nombre d&apos;inscrits'></i></th>";
			echo "<th>&nbsp;Rang dernier&nbsp;<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Le rang du dernier appelé a été supprimé des statistiques SCEI à partir de 2018.<br/>Il a été remplacé par le rang médian et le rang moyen.<br/>
					<br/>&bull; Seuls certains concours continuent à publier le rang du dernier admis par école (voir la note d&apos;information &#x24D8; en page d&apos;accueil pour plus de détails).'></i></th>";
			echo "<th>&nbsp;<button id='dernier' type='button' class='btn btn-secondary btn-sm' title='Trier par sélectivité croissante' onclick='triSelectiviteDernier()'>&darr;</button>&nbsp;&nbsp;Sélectivité&nbsp;<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Sélectivité = Rang du dernier admis / Nombre d&apos;inscrits'></i></th>";
			echo "</tr></thead>";

			$ecoleCourante = "";
			$class = "";
			$firstRecord = true;

			// alimente en parallèle la liste de cartes mobiles (voir plus bas),
			// construite à partir de la même lecture SQL que le tableau desktop.
			$lignesCartes = [];

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				// coloration de la ligne si le rang visé est inférieur au rang du dernier ou au rang médian
 				$style = "";
//  				if ($Dernier <> "") {
//  					if ($rang <= $Dernier) {
//  						$style = " style=background-color:lemonchiffon;";
//  					}
//  				} elseif ($RangMedian <> "") {
// 					if ($rang <= $RangMedian) { 
// 						$style = " style=background-color:lemonchiffon;";
// 					}
// 				}

				// affichage d'un trait plein au changement d'école
				if (!$firstRecord) {
//					if ($Ecole == $ecoleCourante) {
					if (strcasecmp($Ecole, $ecoleCourante) == 0) {		// ne tient pas compte des minuscules ou majuscules
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}
				
				// affichage de la ligne
				$jsEcole = encodeJs($Ecole);
				$jsAn = encodeJs($An);
				echo "<tr " . $style . " ondblclick='zoom(" . $jsEcole . "," . $jsAn . ")'>";
				if (($filiere == "toute") or ($filiere == "")) {
					echo "<td>". strtoupper($Filiere) ."</td>";
				}
				if (($concours == "tous") or ($concours == "")) {
					echo "<td".$class.">". $Concours ."</td>";
				}
				echo "<td class='colonne-ecole-fixe" . ($class !== "" ? " nouvelEcole" : "") . "'><a href='detail-resultat-admission-par-ecole.php?origine=filiere&amp;ecole=" . rawurlencode($Ecole) . "'><strong>" . escapeHtml($Ecole) . "</strong></a></td>";
				echo "<td".$class." style='text-align:center;'>" . $An . "</td>";
				echo "<td".$class." style='text-align:right;'>" . $Place . "&nbsp;</td>";
				echo "<td".$class." style='text-align:right;'>" . formater($Inscrit, 0) . "&nbsp;</td>";
// 				echo "<td".$class." style='text-align:right;'>" . formater($Classe, 0) . "&nbsp;</td>";
				echo "<td".$class." style='text-align:right;'>" . formater($Integre, 0) . "&nbsp;</td>";
				// un rang à zéro signifie une donnée non publiée par le concours (voir detailFiliere.php)
				echo "<td".$class." style='text-align:right;'>" . (($RangMedian <> 0 && $RangMedian <> '') ? formater($RangMedian, 0) : '') . "&nbsp;</td>";
				if ($Inscrit <> 0) {
					$selectivite = ($Dernier / $Inscrit) * 100; 
					$selectiviteMediane = ($RangMedian / $Inscrit) * 100;
				} else {
					$selectivite = 0;
					$selectiviteMediane = 0;
				}
				if ($selectiviteMediane <> 0) {
					echo "<td".$class." style='text-align:right;'>" . formater($selectiviteMediane, 1) . "%&nbsp;</td>";
				} else {
					echo "<td".$class." style='text-align:right;'>&nbsp;</td>";
				}
				echo "<td".$class." style='text-align:right;'>" . (($Dernier <> 0 && $Dernier <> '') ? formater($Dernier, 0) : '') . "&nbsp;</td>";
				if ($selectivite <> 0) {
					echo "<td".$class." style='text-align:right;'>" . formater($selectivite, 1) . "%&nbsp;</td>";
				} else {
					echo "<td".$class." style='text-align:right;'>&nbsp;</td>";
				}
 				echo "</tr>";	
				
				// mémorisation de la ligne pour la carte mobile équivalente (voir plus bas)
				$lignesCartes[] = [
					'filiere' => $Filiere,
					'concours' => $Concours,
					'ecole' => $Ecole,
					'an' => $An,
					'place' => $Place,
					'inscrit' => $Inscrit,
					'integre' => $Integre,
					'rangMedian' => $RangMedian,
					'selectiviteMediane' => $selectiviteMediane,
					'dernier' => $Dernier,
					'selectivite' => $selectivite,
					'nouvelleEcole' => ($class !== ""),
				];

				$ecoleCourante = $Ecole;
				$firstRecord = false;
				$class = "";
			}
			echo "</table>";
			echo "</div>"; // .table-responsive
			echo "</div>"; // .vue-tableau

			// --- Vue mobile/tablette (< lg) par défaut, ou forcée quel que soit
			// l'écran via .vue-cartes si l'utilisateur a cliqué "en liste" (voir CSS +
			// JS partagés) : une carte par ligne de données, pas d'accordéon puisque
			// chaque ligne est déjà une donnée atomique (une école, une année, un
			// concours) qui n'a rien à replier/regrouper.
			// Tri dédié : école (comme le tableau), puis année la plus récente
			// d'abord (le tableau, lui, garde l'ordre SQL/tri JS existant).
			usort($lignesCartes, function ($a, $b) {
				$comparaisonEcole = strcasecmp($a['ecole'], $b['ecole']);
				if ($comparaisonEcole !== 0) {
					return $comparaisonEcole;
				}
				return strcmp((string) $b['an'], (string) $a['an']);
			});
			// le repérage "nouvelle école" (ligne de séparation) doit être recalculé
			// après ce tri, l'ordre d'origine (celui du tableau) ne correspondant
			// plus à l'ordre d'affichage des cartes.
			$ecolePrecedenteCarte = null;
			$nombreEcolesDistinctes = 0;
			foreach ($lignesCartes as &$ligneCarteTri) {
				$ligneCarteTri['nouvelleEcole'] = ($ecolePrecedenteCarte !== null && strcasecmp($ligneCarteTri['ecole'], $ecolePrecedenteCarte) !== 0);
				if ($ecolePrecedenteCarte === null || $ligneCarteTri['nouvelleEcole']) {
					$nombreEcolesDistinctes++;
				}
				$ecolePrecedenteCarte = $ligneCarteTri['ecole'];
			}
			unset($ligneCarteTri);

			echo "<div class='d-lg-none vue-cartes'>";
			foreach ($lignesCartes as $indexCarte => $ligneCarte) {
				// Bandeau de groupe entre deux écoles, affiché AVANT chaque nouvelle
				// école (y compris la toute première) : un simple trait fin restait
				// trop discret pour être repéré pendant un défilement rapide. Un
				// bandeau avec le nom de l'école et une bordure colorée est un repère
				// visuel bien plus rapide à détecter à l'œil. Absent s'il n'y a
				// qu'une seule école dans la liste (rien à séparer dans ce cas :
				// le nom de l'école reste alors visible directement dans la carte).
				$detailUrlCarte = 'detail-resultat-admission-par-ecole.php?' . http_build_query([
					'origine' => 'filiere',
					'ecole' => $ligneCarte['ecole'],
				], '', '&', PHP_QUERY_RFC3986);
				if ($nombreEcolesDistinctes > 1 && ($indexCarte === 0 || $ligneCarte['nouvelleEcole'])) {
					echo "<a href='" . escapeHtml($detailUrlCarte) . "' class='d-block text-decoration-none bg-light border-start border-4 border-primary rounded-1 px-2 py-1 mt-4 mb-2 fw-bold'>"
						. "<i class='bi bi-bank2'></i>&nbsp; " . escapeHtml($ligneCarte['ecole']) . "</a>";
				}
				echo "<div class='p-2 mb-2 border rounded bg-white'>";
				echo "<div class='d-flex justify-content-between align-items-start'>";
				echo "<a href='" . escapeHtml($detailUrlCarte) . "' class='fw-bold'>" . escapeHtml($ligneCarte['ecole']) . "</a>";
				echo "<span class='text-muted small'>" . escapeHtml($ligneCarte['an']) . "</span>";
				echo "</div>";
				if (($filiere == "toute") or ($filiere == "")) {
					echo "<div class='text-muted small'>" . escapeHtml(strtoupper($ligneCarte['filiere'])) . "</div>";
				}
				if (($concours == "tous") or ($concours == "")) {
					echo "<div class='text-muted small'>" . escapeHtml($ligneCarte['concours']) . "</div>";
				}
				echo "<div class='d-flex flex-wrap gap-3 mt-1 small'>";
				echo "<span>Places : <strong>" . escapeHtml($ligneCarte['place']) . "</strong></span>";
				echo "<span>Inscrits : <strong>" . escapeHtml(formater($ligneCarte['inscrit'], 0)) . "</strong></span>";
				echo "<span>Intégrés : <strong>" . escapeHtml(formater($ligneCarte['integre'], 0)) . "</strong></span>";
				// un rang à zéro signifie une donnée non publiée par le concours (voir detailFiliere.php)
				if ($ligneCarte['rangMedian'] != 0) {
					echo "<span>Rang médian : <strong>" . escapeHtml(formater($ligneCarte['rangMedian'], 0)) . "</strong>"
						. ($ligneCarte['selectiviteMediane'] != 0 ? " (" . escapeHtml(formater($ligneCarte['selectiviteMediane'], 1)) . "%)" : "") . "</span>";
				}
				if ($ligneCarte['dernier'] != 0) {
					echo "<span>Rang dernier : <strong>" . escapeHtml(formater($ligneCarte['dernier'], 0)) . "</strong>"
						. ($ligneCarte['selectivite'] != 0 ? " (" . escapeHtml(formater($ligneCarte['selectivite'], 1)) . "%)" : "") . "</span>";
				}
				echo "</div>";
				echo "</div>";
			}
			echo "</div>";

			echo "</main>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Note : " . $erreur->getMessage();
		}

		// fermeture de la base
		if (isset($result)) {$result->closeCursor();}
		$db = null;
	
	?>

	<!-- retour en arrière vers le formulaire -->
	<footer class="container" style='margin-top:40px; margin-bottom:80px;'>
		<br/>
		<div class='d-flex justify-content-center'>
			<a class="btn btn-primary" href="#">&uarr; Haut de liste</a>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<button class="btn btn-primary" onclick="questionnaire()">&larr; Retour aux critères</button>
		</div>
	</footer>

	<?php
		// librairies javascript nécessaires à l'application (popper + bootstrap)
		include "php/librairie.php";
	?>

	<!-- bascule manuelle "en liste" / "en tableau" -->
	<script>
	<?php include "js/basculeAffichage.js"; ?>
	</script>

	<!-- activation tooltip Bootstrap 5 -->
	<script>
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl)
			})		
	</script>

	<!-- navigation -->
	<script>
		let etatTri = {
			colonne: null,
			direction: 'asc'
		};

		function majIndicateursTri(colonneActive, direction) {
			const correspondance = {
				ecole: 'ecole',
				concours: 'concours',
				selectiviteMediane: 'selectivite',
				selectiviteDernier: 'dernier'
			};

			Object.values(correspondance).forEach(idBouton => {
				const bouton = document.getElementById(idBouton);
				if (!bouton) return;

				bouton.textContent = '↓';
				bouton.classList.remove('btn-primary');
				bouton.classList.add('btn-secondary');
			});

			const idActif = correspondance[colonneActive];
			const boutonActif = idActif ? document.getElementById(idActif) : null;
			if (!boutonActif) return;

			boutonActif.textContent = direction === 'asc' ? '↑' : '↓';
			boutonActif.classList.remove('btn-secondary');
			boutonActif.classList.add('btn-primary');
		}

		// pour zoomer sur une école
		function zoom(ecole,an) {
			<?php
// 				echo "window.location.href='detail-resultat-admission-ecole-d-ingenieur-cpge-post-prepa.php?reference=" . $reference . "&an=' + an + '&filiere=" . $filiere . "&concours=" . $concours . "&ecole=' + ecole";
				$baseZoom = 'detail-resultat-admission-par-ecole.php?' . http_build_query([
					'origine' => 'filiere',
					'reference' => $reference,
					'an' => 'toutes',
					'filiere' => $filiere,
					'concours' => $concours,
				], '', '&', PHP_QUERY_RFC3986);
				echo 'window.location.href = ' . encodeJs($baseZoom) . " + '&ecole=' + encodeURIComponent(ecole);";
			?>
		}

		// Tri local du tableau sans rechargement de page
		function trierTableau(typeColonne) {
			const table = document.querySelector('#tableau-par-filiere');
			const tbody = table.querySelector('tbody');
			const lignes = Array.from(tbody.querySelectorAll('tr'));

			let direction = 'asc';
			if (etatTri.colonne === typeColonne) {
				direction = etatTri.direction === 'asc' ? 'desc' : 'asc';
			}
			etatTri = {
				colonne: typeColonne,
				direction: direction
			};
			majIndicateursTri(typeColonne, direction);

			// Trouver l'index de la colonne à trier en fonction du texte du header
			let colonneIndex = -1;
			const headers = table.querySelectorAll('th');

			for (let i = 0; i < headers.length; i++) {
				const headerText = headers[i].textContent;
				if (typeColonne === 'ecole' && headerText.includes('Ecole')) {
					colonneIndex = i;
					break;
				} else if (typeColonne === 'selectiviteMediane' && headerText.includes('Sélectivité médiane')) {
					colonneIndex = i;
					break;
				} else if (typeColonne === 'selectiviteDernier' && headerText.includes('Sélectivité') && !headerText.includes('médiane')) {
					colonneIndex = i;
					break;
				} else if (typeColonne === 'concours' && headerText.includes('Concours')) {
					colonneIndex = i;
					break;
				}
			}

			if (colonneIndex === -1) return; // Colonne non trouvée

			lignes.sort((a, b) => {
				let valeurA = a.cells[colonneIndex].textContent.trim();
				let valeurB = b.cells[colonneIndex].textContent.trim();

				if (typeColonne === 'ecole' || typeColonne === 'concours') {
					// Tri alphabétique
					const comparaison = valeurA.localeCompare(valeurB, 'fr');
					return direction === 'asc' ? comparaison : -comparaison;
				} else if (typeColonne === 'selectiviteMediane' || typeColonne === 'selectiviteDernier') {
					// Tri numérique (extraire le nombre avant le %)
					let numA = parseFloat(valeurA) || 0;
					let numB = parseFloat(valeurB) || 0;
					return direction === 'asc' ? (numA - numB) : (numB - numA);
				}
			});

			lignes.forEach(ligne => tbody.appendChild(ligne));

		// Mise à jour des séparateurs
		if (typeColonne === 'ecole') {
			// Trait plein au changement d'école, pointillés pour la même école
			let ecolePrec = null;
			lignes.forEach(ligne => {
				const ecoleActuelle = ligne.cells[colonneIndex].textContent.trim();
				Array.from(ligne.cells).forEach(cell => {
					if (ecoleActuelle !== ecolePrec && ecolePrec !== null) {
						cell.classList.add('nouvelEcole');
					} else {
						cell.classList.remove('nouvelEcole');
					}
				});
				ecolePrec = ecoleActuelle;
			});
		} else {
			// Autres tris : supprimer tous les séparateurs solides
			lignes.forEach(ligne => {
				Array.from(ligne.cells).forEach(cell => cell.classList.remove('nouvelEcole'));
			});
		}
		}

		function triSelectiviteMediane() {
			trierTableau('selectiviteMediane');
		}
		function triSelectiviteDernier() {
			trierTableau('selectiviteDernier');
		}
		function triEcole() {
			trierTableau('ecole');
		}
		function triConcours() {
			trierTableau('concours');
		}

		// tri par école au chargement de la page
		document.addEventListener('DOMContentLoaded', () => {
			trierTableau('ecole');
			etatTri = {
				colonne: 'ecole',
				direction: 'asc'
			};
			majIndicateursTri('ecole', 'asc');
		});

		// pour retourner en arrière dans l'historique du navigateur
		function questionnaire() {
			<?php
				$queryQuestionnaireParams = [];
				if ($reference !== '' && $reference !== 'toutes') {
					$queryQuestionnaireParams['reference'] = $reference;
				}
				if ($filiere !== '') {
					$queryQuestionnaireParams['filiere'] = $filiere;
				}
				if ($concours !== '') {
					$queryQuestionnaireParams['concours'] = $concours;
				}
				if ($ecole !== '' && $ecole !== 'toutes') {
					$queryQuestionnaireParams['ecole'] = $ecole;
				}
				$queryQuestionnaire = http_build_query($queryQuestionnaireParams, '', '&', PHP_QUERY_RFC3986);
				$questionnaireUrl = 'statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php';
				if ($queryQuestionnaire !== '') {
					$questionnaireUrl .= '?' . $queryQuestionnaire;
				}
				echo 'window.location.href = ' . encodeJs($questionnaireUrl) . ';';
			?>
		}
	</script>

	<script>
	<?php
		// fonction d'export des tableaus HTML en CSV
		include "js/tableToCSV.js";
	?>
	</script>

  </body>
</html>