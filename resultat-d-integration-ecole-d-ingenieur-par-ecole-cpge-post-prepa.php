<!doctype html>

<?php
	/**
	 * ============================================================================
	 * Page : Résultats d'intégration par école (tableau EcoleConcours x Filière
	 *        x Concours x Année, 2016-2026)
	 * ============================================================================
	 *
	 * PROBLÈME ERGONOMIQUE RÉSOLU PAR CETTE VERSION :
	 * Le tableau original affiche les 10 années disponibles en colonnes
	 * horizontales. Chaque nouvelle rentrée ajoute une colonne, ce qui :
	 *   - allonge sans cesse la largeur du tableau,
	 *   - impose un défilement horizontal de plus en plus pénible sur mobile,
	 *   - rend la lecture ligne par ligne difficile (l'œil doit balayer loin
	 *     à droite pour comparer une année ancienne).
	 *
	 * INTERFACE HYBRIDE MISE EN PLACE :
	 *   1) DESKTOP (≥ breakpoint Bootstrap "lg") : le tableau classique est
	 *      conservé tel quel (toutes les écoles/filières/concours en lignes,
	 *      années en colonnes), mais seules les 4 années les plus récentes
	 *      sont visibles par défaut. Un bouton "Afficher toutes les années"
	 *      permet de révéler l'historique complet en un clic, sans recharger
	 *      la page (bascule d'une classe CSS en JavaScript).
	 *   2) MOBILE / TABLETTE (< "lg") : le tableau est totalement masqué et
	 *      remplacé par une pile d'accordéons Bootstrap, un par combinaison
	 *      École + Filière + Concours. Chaque accordéon, une fois déplié,
	 *      liste les années verticalement (plus de défilement horizontal).
	 *      Là aussi, seules les années récentes sont visibles par défaut,
	 *      avec un bouton "Voir plus d'années" par carte.
	 *
	 * Les deux rendus (tableau + accordéons) sont générés à partir de la
	 * MÊME lecture en base (une seule boucle de requêtes SQL), afin de ne
	 * pas doubler la charge sur le serveur : voir le tableau PHP $lignes /
	 * $accordeon construit juste après la connexion à la base.
	 *
	 * Sauvegarde de la version précédente (avant cette réécriture) :
	 * save/resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa-20260827.php
	 * ============================================================================
	 */

	// récupération-contrôle des paramètres
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";

	// estimation du rang du dernier quand le concours ne le publie pas (table NoteEstimation)
	require_once "php/estimation.php";

	// Année de référence transmise par le radio "Année de référence" du formulaire
	// critères (page statistique-integration-...-par-ecole) : filtre les lignes ET
	// réduit à une seule colonne, sans changer le comportement par défaut ("toutes").
	$anneeEcole = isset($_GET['anneeEcole']) ? trim($_GET['anneeEcole']) : 'toutes';
	$anneeEcole = preg_replace('/[^0-9a-zA-Z]/', '', $anneeEcole);
	$anneeEcole = substr($anneeEcole, 0, 6);
	if (!in_array($anneeEcole, $allowed_years, true)) {
		$anneeEcole = 'toutes';
	}
	$anneeUnique = ($anneeEcole !== 'toutes');

	// URL canonique: conserve uniquement les paramètres qui changent réellement le contenu.
	$canonicalBase = 'https://loic.website/CPGE/resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php';
	$canonicalParams = [];
	if (($ecole !== "") && ($ecole !== "toutes")) {
		$canonicalParams['ecole'] = $ecole;
	} elseif ($recherche !== "") {
		$canonicalParams['recherche'] = $recherche;
	}
	if ($anneeUnique) {
		$canonicalParams['anneeEcole'] = $anneeEcole;
	}
	$canonicalUrl = $canonicalBase;
	if (!empty($canonicalParams)) {
		$canonicalUrl .= '?' . http_build_query($canonicalParams, '', '&', PHP_QUERY_RFC3986);
	}
	$retourListe = urlPageNavigation(basename(__FILE__), $_GET);
	$urlCriteres = urlPageNavigation('statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php', [
		'ecole' => $ecole, 'recherche' => $recherche, 'anneeEcole' => $anneeEcole,
	]);
	$navigationRetour = destinationRetourNavigation(array_replace($_GET, [
		'origine' => $origine, 'ecole' => $ecole, 'recherche' => $recherche, 'anneeEcole' => $anneeEcole,
	]), $_SERVER);
?>


<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Statistique SCEI d'admissions par école aux écoles d'ingénieurs post prépa CPGE">
		<link rel="canonical" href="<?php echo escapeHtml($canonicalUrl); ?>" />

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Admissions en écoles d'ingénieurs par école</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>

	<!--
		CSS de l'interface hybride tableau/accordéons (voir commentaire d'en-tête
		du script). Les colonnes/lignes "années anciennes" sont masquées par
		défaut et révélées via JS en retirant la classe .anneesToutesAffichees
		du conteneur, ou en ajoutant .voir-tout sur la carte mobile concernée.
	-->
	<style>
		/* Desktop : colonnes des années hors des 4 plus récentes, masquées par défaut */
		#tableau-par-ecole .colonne-annee-ancienne { display: none; }
		#tableau-par-ecole.anneesToutesAffichees .colonne-annee-ancienne { display: table-cell; }

		/* Desktop : lignes (école/filière/concours) sans aucune donnée sur les années
		   récentes, masquées par défaut avec les colonnes anciennes. Les classes
		   *Defaut recréent le trait de séparation d'école et le trait de fermeture
		   du tableau qui seraient perdus si la ligne qui les porte est masquée ;
		   elles ne s'appliquent donc que tant que les lignes anciennes sont masquées. */
		#tableau-par-ecole tr.ligne-ancienne { display: none; }
		#tableau-par-ecole.anneesToutesAffichees tr.ligne-ancienne { display: table-row; }
		#tableau-par-ecole:not(.anneesToutesAffichees) td.premiereLigneEcoleDefaut { border-top-style: solid; }
		#tableau-par-ecole:not(.anneesToutesAffichees) td.derniereLigneDefaut { border-bottom-style: solid; }

		/* Mobile : même bouton global que sur le tableau. Il révèle les cartes sans
		   donnée récente ET les années anciennes de toutes les cartes ; le bouton
		   "Voir plus d'années" propre à chaque carte devient alors inutile. */
		#accordeon-par-ecole .accordion-item.carte-ancienne { display: none; }
		#accordeon-par-ecole.toutesAnneesAffichees .accordion-item.carte-ancienne { display: block; }
		#accordeon-par-ecole.toutesAnneesAffichees .ligne-annee-mobile.annee-ancienne { display: flex !important; }
		#accordeon-par-ecole.toutesAnneesAffichees .bouton-voir-plus-annees { display: none; }

		/* Mobile : lignes d'années anciennes dans chaque carte accordéon, masquées par défaut.
		   !important nécessaire : ces lignes portent aussi la classe Bootstrap .d-flex,
		   qui impose elle-même display:flex !important et écraserait un display:none simple. */
		#accordeon-par-ecole .ligne-annee-mobile.annee-ancienne { display: none !important; }
		#accordeon-par-ecole .accordion-item.voir-tout .ligne-annee-mobile.annee-ancienne { display: flex !important; }

		/*
		 * Colonne Ecole fixée à gauche pendant le défilement horizontal (même
		 * recette validée sur resultat-d-integration-ecole-d-ingenieur-par-
		 * filiere-cpge-post-prepa.php, elle-même reprise de ECN/tableau-
		 * specialite.php) : border-collapse:separate scopé à CE tableau
		 * uniquement (le reste du site garde border-collapse:collapse), la
		 * bordure propre au <table> retirée, et une seule bordure par frontière
		 * de ligne/colonne pour éviter les doublons d'épaisseur.
		 */
		#tableau-par-ecole {
			border-collapse: separate;
			border-spacing: 0;
			border-style: none;
		}
		#tableau-par-ecole td, #tableau-par-ecole th {
			border-bottom-style: none;
			border-right-style: none;
		}
		#tableau-par-ecole tbody tr:last-child td {
			border-bottom-style: solid;
		}
		#tableau-par-ecole td:last-child, #tableau-par-ecole th:last-child {
			border-right-style: solid;
		}
		#tableau-par-ecole .colonne-ecole-fixe {
			position: sticky;
			left: 0;
			background-color: #fff;
			/* sans z-index supérieur, les th/td plus à droite (même z-index:1 hérité
			   des th) passent AU-DESSUS de cette colonne quand ils la recouvrent en
			   défilant horizontalement -> l'en-tête Ecole semblait "disparaitre". */
			z-index: 2;
		}
		#tableau-par-ecole th.colonne-ecole-fixe {
			background-color: DarkSlateBlue;
			/* les th héritent aussi de "position:sticky; top:50px" (règle globale
			   th { ... }) : coller un même en-tête à la fois en haut ET à gauche
			   est peu fiable selon les navigateurs (bug fréquent sur les cellules
			   d'angle). On désactive donc ici le collage vertical (top:auto ne
			   participe plus au calcul sticky) pour ne garder que le collage
			   horizontal, comme sur les cellules de données du corps du tableau. */
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
				<li class="breadcrumb-item"><a data-retour-navigation href="<?php echo escapeHtml($urlCriteres); ?>">Ecole</a></li>
				<li class="breadcrumb-item active" aria-current="page">Statistiques</li>
			  </ol>
			</div>
		</div>
	</nav>

	<!-- en tête du tableau -->

	<?php

		// titre de la page
		echo "<header class='container'>";
		if (($ecole <> "") and ($ecole <> "toutes")) {
			echo "<h1 class='h3'><i class='bi bi-mortarboard-fill'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions<br/>pour l'école ".escapeHtml($ecole)."</h1>";
		} else {
			echo "<h1 class='h3'><i class='bi bi-mortarboard-fill'></i>&nbsp;&nbsp;&nbsp;Statistiques d'admissions</h1>";
		}
		echo "<br/>";
		echo "</header>";

		// section principale de la page
		echo "<main class='container-fluid'>";

		// conexion à la base concours cpge
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . $erreur->getMessage());
		}

		// construction de la clause WHERE (requête préparée)
				$paramsEcole = [];
				$conditionsEcole = [];
				$sql = "SELECT ec.Filiere, ec.Concours, ec.EcoleConcours AS Ecole FROM EcoleConcours ec";
		if (($ecole <> "") and ($ecole <> "toutes")) {
			// Le select envoie le nom canonique (EcoleConcours.Ecole) : match exact qui
			// regroupe toutes les voies/diplômes de l'école, y compris ses anciens
			// libellés (ex. SUPELEC -> CentraleSupélec). Repli sur le libellé concours
			// exact pour les anciennes URLs (ex. "Arts et Métiers campus Rabat").
			$conditionsEcole[] = "(ec.Ecole = :ecole OR ec.EcoleConcours = :ecole_libelle)";
			$paramsEcole[':ecole'] = $ecole;
			$paramsEcole[':ecole_libelle'] = $ecole;
		} elseif ($recherche <> "") {
			$conditionsEcole[] = "ec.EcoleConcours LIKE :recherche_like";
			$paramsEcole[':recherche_like'] = '%' . $recherche . '%';
		}
		if ($anneeUnique) {
			// Ne garde que les combinaisons ayant un résultat publié cette année-là :
			// une recherche large (ex. "INSA") retombe sinon sur des dizaines de lignes
			// issues d'années où l'école ne concourait plus ou pas encore.
			$conditionsEcole[] = "EXISTS (SELECT 1 FROM Note n WHERE n.EcoleConcours = ec.EcoleConcours AND n.Filiere = ec.Filiere AND n.Concours = ec.Concours AND n.An = :annee_unique)";
			$paramsEcole[':annee_unique'] = $anneeEcole;
		}
		if (!empty($conditionsEcole)) {
			$sql .= " WHERE " . implode(" AND ", $conditionsEcole);
		}
		$sql .= " ORDER BY ec.EcoleConcours ASC, ec.Filiere ASC";
		if ($debug) {
			echo "SQL Ecole = " . escapeHtml($sql) . "<br/>";
			echo "PARAMS Ecole = " . escapeHtml(json_encode($paramsEcole, JSON_UNESCAPED_UNICODE)) . "<br/>";
		}
		try {
			$stmtEcole = $db->prepare($sql);
			$stmtEcole->execute($paramsEcole);
			$result = $stmtEcole;

			// la table NoteEstimation, si elle existe, est jointe sur la clé de Note : l'estimation du
			// rang du dernier remplace alors le rang médian quand le dernier n'est pas publié
			$colonnesEstimation = '';
			$jointureEstimation = '';
			if (estimationDisponible($db)) {
				$colonnesEstimation = ', e.Statut AS EstimationStatut, e.Plancher AS EstimationPlancher, e.Milieu AS EstimationMilieu, e.Plafond AS EstimationPlafond';
				$jointureEstimation = ' LEFT JOIN NoteEstimation e ON e.Filiere = n.Filiere AND e.An = n.An AND e.Concours = n.Concours AND e.EcoleConcours = n.EcoleConcours';
			}
			$sqlNote = 'SELECT n.Inscrit, n.Integre, n.RangMedian, n.Dernier' . $colonnesEstimation
				. ' FROM Note n' . $jointureEstimation
				. ' WHERE n.EcoleConcours = :ecole AND n.Filiere = :filiere AND n.Concours = :concours AND n.An = :an';
			$stmtNote = $db->prepare($sqlNote);
			$annees = $anneeUnique ? [$anneeEcole] : ['2026', '2025', '2024', '2023', '2022', '2021', '2020', '2019', '2018', '2017', '2016'];

			// Nombre d'années affichées par défaut : plus l'historique grandit (une
			// nouvelle colonne par an), plus le tableau devient large et le défilement
			// horizontal pénible, surtout sur smartphone. On limite donc l'affichage
			// initial aux années récentes, avec un bouton pour dérouler l'historique.
			// Une seule année demandée : rien à masquer, $anneesRecentes = $annees.
			$anneesRecentes = $anneeUnique ? $annees : array_slice($annees, 0, 4);

			// Une seule lecture en base alimente à la fois :
			//   - le tableau large (desktop, colonnes = années)
			//   - les accordéons empilés (mobile, une carte par école/filière/concours)
			// $lignes garde l'ordre de tri SQL pour construire le tableau tel quel ;
			// $accordeon regroupe la même donnée par clé "école|filière|concours".
			$lignes = [];
			$accordeon = [];

			while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
				extract($row);

				$detailUrl = 'detail-resultat-admission-par-ecole.php?' . http_build_query([
					'origine' => 'ecole',
					'reference' => $reference,
					'an' => 'toutes',
					'filiere' => $Filiere,
					'concours' => $Concours,
					'ecole' => $Ecole,
					'retour' => $retourListe,
				], '', '&', PHP_QUERY_RFC3986);

				$notesAnnee = [];
				foreach ($annees as $annee) {
					if ($debug) {
						echo "SQL Note = " . escapeHtml($sqlNote) . " | PARAMS = " . escapeHtml(json_encode([
							'ecole' => $Ecole,
							'filiere' => $Filiere,
							'concours' => $Concours,
							'an' => $annee
						], JSON_UNESCAPED_UNICODE)) . "<br/>";
					}
					try {
						$stmtNote->execute([
							':ecole' => $Ecole,
							':filiere' => $Filiere,
							':concours' => $Concours,
							':an' => $annee,
						]);
						$rowNote = $stmtNote->fetch(PDO::FETCH_ASSOC);
						$notesAnnee[$annee] = ($rowNote === false) ? null : $rowNote;
					}
					catch(PDOException $erreur)	{
						echo "Erreur SELECT Note " . escapeHtml($annee) . " : " . escapeHtml($erreur->getMessage());
						$notesAnnee[$annee] = null;
					}
				}

				$ligne = [
					'ecole' => $Ecole,
					'filiere' => $Filiere,
					'concours' => $Concours,
					'detailUrl' => $detailUrl,
					'notes' => $notesAnnee,
					'recente' => false,
				];
				// une ligne est "récente" si au moins une des années visibles par défaut
				// affiche une valeur (mêmes critères que l'affichage des cellules)
				foreach ($anneesRecentes as $anneeRecente) {
					$noteRecente = $notesAnnee[$anneeRecente];
					if ($noteRecente !== null && (
						(($noteRecente['Dernier'] <> 0) and ($noteRecente['Dernier'] <> ""))
						or (($noteRecente['RangMedian'] <> 0) and ($noteRecente['RangMedian'] <> ""))
					)) {
						$ligne['recente'] = true;
						break;
					}
				}
				$lignes[] = $ligne;

				// clé de regroupement pour l'accordéon : une carte par combinaison réelle
				$cleGroupe = $Ecole . '|' . $Filiere . '|' . $Concours;
				$accordeon[$cleGroupe] = $ligne;
			}

			// Lignes sans donnée sur les années visibles par défaut (anciens concours,
			// écoles disparues...) : masquées avec les colonnes anciennes, car une ligne
			// vide ne permet aucune comparaison. Rien n'est masqué si aucune ligne n'a de
			// donnée récente (sinon la page serait vide) ni pour une année unique.
			$nbLignesRecentes = count(array_filter($lignes, fn($l) => $l['recente']));
			$nbLignesAnciennes = count($lignes) - $nbLignesRecentes;
			$masquerLignesAnciennes = !$anneeUnique && $nbLignesRecentes > 0 && $nbLignesAnciennes > 0;
			foreach ($lignes as $i => $l) {
				$lignes[$i]['masquee'] = $masquerLignesAnciennes && !$l['recente'];
				$lignes[$i]['premiereEcoleDefaut'] = false;
				$lignes[$i]['derniereDefaut'] = false;
			}
			if ($masquerLignesAnciennes) {
				// repère, parmi les lignes visibles par défaut, la 1ère de chaque école
				// (sauf la toute première du tableau) et la dernière du tableau
				$ecolePrecedenteVisible = null;
				$derniereVisible = null;
				foreach ($lignes as $i => $l) {
					if ($l['masquee']) {
						continue;
					}
					if ($ecolePrecedenteVisible !== null && strcasecmp($l['ecole'], $ecolePrecedenteVisible) != 0) {
						$lignes[$i]['premiereEcoleDefaut'] = true;
					}
					$ecolePrecedenteVisible = $l['ecole'];
					$derniereVisible = $i;
				}
				$lignes[$derniereVisible]['derniereDefaut'] = true;
			}
			foreach ($accordeon as $cleGroupe => $l) {
				$accordeon[$cleGroupe]['masquee'] = $masquerLignesAnciennes && !$l['recente'];
			}

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

			// --- Rendu 1/2 : tableau large, réservé aux écrans desktop par défaut
			// (d-none d-lg-block), ou forcé quel que soit l'écran via .vue-tableau
			// si l'utilisateur a cliqué sur "en tableau" (voir CSS + JS partagés).
			echo "<div class='d-none d-lg-block vue-tableau'>";
			echo "<div class='table-responsive'>";
			echo "<table class='table-hover' style='width:100%;' id='tableau-par-ecole'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une ligne pour voir le détail de cette école.<br/>
				<span style='color:darkslategray; white-space:nowrap;'>50 : <strong>649</strong> / 2540</span> en <strong>noir</strong> : rang médian &nbsp;&middot;&nbsp;
				<span style='color:#0000FF; white-space:nowrap;'>50 : <strong>649</strong> / 2540</span> en <strong>bleu</strong> : rang du dernier &nbsp;&middot;&nbsp;
				<span style='color:#0000FF; white-space:nowrap;'>50 : <span class='estimation-dernier'>&ge;&nbsp;649</span> / 2540</span> en <strong>bleu italique</strong> : rang du dernier estimé (non publié) <a href='" . URL_METHODE_ESTIMATION . "'>Comment est-il estimé ?</a>";
			$idTableau = '"#tableau-par-ecole","école;filière;concours;' . implode(';', $annees) . '"';
			echo "<br>Cliquer sur le bouton pour télécharger le tableau au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(".$idTableau.")'><i class='bi bi-download'></i> csv</button>";
			if (!$anneeUnique) {
				$libelleAfficher = 'Afficher toutes les années' . ($masquerLignesAnciennes ? ' (+' . $nbLignesAnciennes . ' ancien' . ($nbLignesAnciennes > 1 ? 's' : '') . ' concours)' : '');
				echo "\n\t\t\t\t&nbsp;&nbsp;<button type='button' class='btn btn-secondary btn-sm' id='toggleAnneesAnciennes' data-libelle-afficher=\"" . escapeHtml($libelleAfficher) . "\" onclick='basculerAnneesAnciennes(this)'><i class='bi bi-arrows-expand'></i> " . escapeHtml($libelleAfficher) . "</button>";
			}
			echo "</small></caption>";
			echo "<thead class='text-center'>";

			// Ligne d'en-tête d'explication du code couleur (rang médian / rang dernier).
			// La cellule qui couvre toutes les années sauf 2017 et 2016 (colspan = nombre
			// d'années - 2, mélange d'années récentes et anciennes)
			// reste TOUJOURS visible : la masquer en mode "4 années récentes" ferait
			// disparaître un texte qui reste pertinent pour les colonnes encore affichées,
			// et cassait auparavant l'encadrement du tableau. En revanche la cellule
			// colspan=2 (2017-2016, exclusivement des années anciennes) porte la classe
			// .colonne-annee-ancienne comme les colonnes qu'elle décrit.
			echo "<tr>";
			// 3 cellules de coin vides (au lieu d'une seule fusionnée colspan=3) au
			// dessus des colonnes Ecole/Filière/Concours, sans bordure entre elles
			// (visuellement toujours un seul bandeau continu). Ce découpage permet
			// à la 1ère (position Ecole) de porter .colonne-ecole-fixe et de rester
			// sticky pendant le défilement horizontal, ce qu'une cellule fusionnée
			// sur 3 colonnes ne pourrait pas faire correctement (voir limitation
			// notée plus haut).
			echo "<th class='colonne-ecole-fixe'></th>";
			echo "<th style='position:static; border-left-style:none;'></th>";
			echo "<th style='position:static; border-left-style:none;'></th>";
			// Année unique demandée : une seule colonne, donc une seule cellule
			// d'explication (colspan=1) au lieu du découpage récent/ancien.
			echo "<th style='position:static;' colspan=" . ($anneeUnique ? 1 : count($annees) - 2) . ">Intégrés : Rang médian / Inscrits&nbsp;&nbsp;<span style='font-weight:normal'>(en noir)</span><br/>Intégrés : Rang dernier / Inscrits&nbsp;&nbsp;<span style='font-weight:normal'>(en bleu. <i>italique</i> &ge; si estimé)</span><br/>
								<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='&bull; Lorsque le rang du dernier admis est connu c&apos;est celui-ci qui est affiché (en bleu).<br/>
								<br/>&bull; Sinon il est estimé quand c&apos;est possible : valeur en bleu <i>italique</i> précédée de &ge; (minimum garanti ; passer la souris sur la valeur pour voir les autres repères).<br/>
								<br/>&bull; À défaut d&apos;estimation possible (par exemple les écoles recrutant des fonctionnaires) c&apos;est le rang médian qui est affiché (en noir).<br/>
								<br/>&bull; Le rang du dernier appelé a été supprimé des statistiques SCEI à partir de 2018. Il a été remplacé par le rang médian et le rang moyen.<br/>
								<br/>&bull; Seuls certains concours continuent à publier le rang du dernier admis par école (voir la note d&apos;information &#x24D8; en page d&apos;accueil pour plus de détails).'></i></th>";
			if (!$anneeUnique) {
				// Cette cellule ne couvre que 2017 et 2016 : ces deux années sont TOUJOURS
				// dans le groupe "années anciennes" (voir $anneesRecentes), elle peut donc
				// porter sans risque la classe qui la masque en mode "4 années récentes"
				// (contrairement à la cellule ci-dessus, qui mélange années
				// récentes et anciennes et doit rester visible en permanence).
				echo "<th style='position:static;' colspan=2 class='colonne-annee-ancienne'>Intégrés : Rang dernier / Inscrits<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='En 2017 et 2016 le rang du dernier appelé est sytématiquement renseigné dans SCEI. C&apos;est lui qui est affiché.'></i></th>";
			}
			echo "</tr>";

			echo "<tr>";
			echo "<th class='colonne-ecole-fixe'>Ecole<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='&bull;Lorsqu&apos;une école a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>
								<br/>&bull; Lorsque plusieurs écoles ont fusionné, les différentes écoles apparaissent séparément avant la fusion.<br/>
								<br/>&bull; Lorsqu&apos;une école change de concours, elle apparaît soit dans le nouveau concours soit dans l&apos;ancien suivant la date.<br/>
								<br/>&bull; A noter que le nom affiché est celui qui apparaît dans SCEI.'></i></th>";
			echo "<th>&nbsp;Filière&nbsp;</th>";
			echo "<th>Concours<br/><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Lorsqu&apos;un concours a changé de nom, c&apos;est le nom le plus récent qui est affiché.<br/>Exemple CCP devenu CCINP en 2019.'></i></th>";
			foreach ($annees as $annee) {
				// Les années hors des 4 plus récentes portent une classe qui les masque
				// par défaut (voir CSS ci-dessous) ; le bouton "Afficher toutes les
				// années" retire cette classe côté JS, sans nouvelle requête serveur.
				$classeAnnee = in_array($annee, $anneesRecentes, true) ? '' : ' colonne-annee-ancienne';
				echo "<th class='" . $classeAnnee . "'>" . $annee . "</th>";
			}
			echo "</tr>";
			echo "</thead>";

			$ecoleCourante = "";
			$class = "";
			$firstRecord = true;

			foreach ($lignes as $ligne) {
				$Ecole = $ligne['ecole'];
				$Filiere = $ligne['filiere'];
				$Concours = $ligne['concours'];

				// affichage d'un trait plein au changement d'école
				if (!$firstRecord) {
					if (strcasecmp($Ecole, $ecoleCourante) == 0) {		// ne tient pas compte des minuscules ou majuscules
						$class = "";
					} else {	
						$class = " class='nouvelEcole'";
					}
				}

				// affichage du début de la ligne du tableau
				$jsEcole = encodeJs($Ecole);
				$jsFiliere = encodeJs($Filiere);
				$jsConcours = encodeJs($Concours);
				echo "<tr" . ($ligne['masquee'] ? " class='ligne-ancienne'" : "") . " ondblclick='zoom(" . $jsEcole . "," . $jsFiliere . "," . $jsConcours . ")'>";

				// classes communes à toutes les cellules de la ligne (trait de séparation
				// entre écoles et variantes "par défaut"), fusionnées ensuite avec la
				// classe d'année ancienne sur un seul attribut class= par cellule.
				$nomClasseLigne = trim(str_replace(['class=', "'", '"'], '', $class)
					. ($ligne['premiereEcoleDefaut'] ? ' premiereLigneEcoleDefaut' : '')
					. ($ligne['derniereDefaut'] ? ' derniereLigneDefaut' : ''));
				$attributClasseLigne = $nomClasseLigne !== '' ? " class='" . $nomClasseLigne . "'" : '';
				echo "<td class='colonne-ecole-fixe" . ($nomClasseLigne !== '' ? ' ' . $nomClasseLigne : '') . "'><a href='" . escapeHtml($ligne['detailUrl']) . "'><strong>" . escapeHtml($Ecole) . "</strong></a></td>";
				echo "<td".$attributClasseLigne." style='text-align:center'><strong>". escapeHtml(strtoupper($Filiere)) ."</strong></td>";
				echo "<td".$attributClasseLigne.">". escapeHtml($Concours) ."</td>";

				foreach ($annees as $annee) {
					$classeAnnee = in_array($annee, $anneesRecentes, true) ? '' : 'colonne-annee-ancienne';
					$classesCellule = trim($nomClasseLigne . ' ' . $classeAnnee);
					$attributClasse = $classesCellule !== '' ? " class='" . $classesCellule . "'" : '';
					$rowNote = $ligne['notes'][$annee];
					if ($rowNote === null) {
						echo "<td".$attributClasse." style='text-align:center'></td>";
						continue;
					}
					$Inscrit = $rowNote['Inscrit'];
					$Integre = $rowNote['Integre'];
					$RangMedian = $rowNote['RangMedian'];
					$Dernier = $rowNote['Dernier'];

					if (($Dernier <> 0) and ($Dernier <> "")) {
						echo "<td".$attributClasse." style='text-align:center; color:#0000FF'><span style='font-size:120%'>".escapeHtml($Integre)." : <strong>".escapeHtml($Dernier)."</strong></span> / ".escapeHtml($Inscrit)."</td>";
					} elseif (($estimationNote = estimationDeLigne($rowNote)) !== null) {
						// dernier non publié : estimation (minimum garanti), bleu italique précédé de ≥, avec infobulle
						echo "<td".$attributClasse." style='text-align:center; color:#0000FF' data-bs-toggle='tooltip' data-bs-html='true' title='"
							. aideEstimation($estimationNote) . "'><span style='font-size:120%'>".escapeHtml($Integre)." : <span class='estimation-dernier'>" . escapeHtml(valeurEstimationBrute($estimationNote)) . "</span></span> / ".escapeHtml($Inscrit)."</td>";
					} elseif (($RangMedian <> 0) and ($RangMedian <> "")) {
						echo "<td".$attributClasse." style='text-align:center'><span style='font-size:120%'>".escapeHtml($Integre)." : <strong>".escapeHtml($RangMedian)."</strong></span> / ".escapeHtml($Inscrit)."</td>";
					} else {
						echo "<td".$attributClasse." style='text-align:center'></td>";
					}
				}

				echo "</tr>\n";
				$ecoleCourante = $Ecole;
				$firstRecord = false;
				$class = "";
			}
			echo "</table>";
			echo "</div>"; // .table-responsive
			echo "</div>"; // .vue-tableau

			// --- Rendu 2/2 : accordéons empilés, réservés aux écrans mobile/tablette
			// par défaut (d-lg-none), ou forcés quel que soit l'écran via .vue-cartes
			// si l'utilisateur a cliqué sur "en liste" (voir CSS + JS partagés).
			// Chaque carte = une combinaison école/filière/concours ; les années sont
			// listées verticalement dans le corps, ce qui évite tout défilement horizontal.
			echo "<div class='d-lg-none vue-cartes' id='accordeon-par-ecole'>";
			if (!$anneeUnique) {
				echo "<p class='text-muted small'>Touchez une ligne pour déplier le détail par année. Les 4 années les plus récentes sont affichées ; touchez « Voir plus d'années » pour l'historique complet.</p>";
				echo "<p class='text-muted small'>Valeurs : intégrés : rang / inscrits. En noir le rang médian, en bleu le rang du dernier, en <span style='color:#0000FF'><span class='estimation-dernier'>bleu italique &ge;</span></span> le rang du dernier estimé car non publié. <a href='" . URL_METHODE_ESTIMATION . "'>Comment est-il estimé ?</a></p>";
				echo "<p><button type='button' class='btn btn-secondary btn-sm' data-libelle-afficher=\"" . escapeHtml($libelleAfficher) . "\" onclick='basculerAnneesAnciennesMobileGlobal(this)'><i class='bi bi-arrows-expand'></i> " . escapeHtml($libelleAfficher) . "</button></p>";
			} else {
				echo "<p class='text-muted small'>Touchez une ligne pour déplier le détail.</p>";
			}
			echo "<div class='accordion' id='accordeonEcoles'>";
			$indexCarte = 0;
			foreach ($accordeon as $cleGroupe => $ligne) {
				$indexCarte++;
				$idPanneau = 'panneau-ecole-' . $indexCarte;
				echo "<div class='accordion-item" . ($ligne['masquee'] ? " carte-ancienne" : "") . "'>";
				echo "<h2 class='accordion-header'>";
				echo "<button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#" . $idPanneau . "'>";
				echo "<span class='fw-bold'>" . escapeHtml($ligne['ecole']) . "</span>&nbsp;&middot;&nbsp;" . escapeHtml(strtoupper($ligne['filiere'])) . "&nbsp;&middot;&nbsp;<span class='text-muted small'>" . escapeHtml($ligne['concours']) . "</span>";
				echo "</button></h2>";
				echo "<div id='" . $idPanneau . "' class='accordion-collapse collapse' data-bs-parent='#accordeonEcoles'>";
				echo "<div class='accordion-body'>";
				echo "<p class='mb-2'><a class='btn btn-outline-primary btn-sm' href='" . escapeHtml($ligne['detailUrl']) . "'><i class='bi bi-box-arrow-up-right'></i>&nbsp; Voir le détail complet de l'école</a></p>";

				foreach ($annees as $annee) {
					$classeAnnee = in_array($annee, $anneesRecentes, true) ? '' : ' annee-ancienne';
					$rowNote = $ligne['notes'][$annee];
					echo "<div class='ligne-annee-mobile" . $classeAnnee . " d-flex justify-content-between align-items-center border-bottom py-1'>";
					echo "<span class='fw-semibold'>" . escapeHtml($annee) . "</span>";
					if ($rowNote === null) {
						echo "<span class='text-muted'>&mdash;</span>";
					} else {
						$Inscrit = $rowNote['Inscrit'];
						$Integre = $rowNote['Integre'];
						$RangMedian = $rowNote['RangMedian'];
						$Dernier = $rowNote['Dernier'];
						if (($Dernier <> 0) and ($Dernier <> "")) {
							echo "<span style='color:#0000FF'>" . escapeHtml($Integre) . " : <strong>" . escapeHtml($Dernier) . "</strong> / " . escapeHtml($Inscrit) . "</span>";
						} elseif (($estimationNote = estimationDeLigne($rowNote)) !== null) {
							echo "<span style='color:#0000FF'>" . escapeHtml($Integre) . " : <span class='estimation-dernier'>" . escapeHtml(valeurEstimationBrute($estimationNote)) . "</span> / " . escapeHtml($Inscrit) . "</span>";
						} elseif (($RangMedian <> 0) and ($RangMedian <> "")) {
							echo "<span>" . escapeHtml($Integre) . " : <strong>" . escapeHtml($RangMedian) . "</strong> / " . escapeHtml($Inscrit) . "</span>";
						} else {
							echo "<span class='text-muted'>&mdash;</span>";
						}
					}
					echo "</div>";
				}
				if (!$anneeUnique) {
					echo "<button type='button' class='btn btn-link btn-sm px-0 mt-2 bouton-voir-plus-annees' onclick='basculerAnneesAnciennesMobile(this)'>Voir plus d'années</button>";
				}
				echo "</div></div></div>";
			}
			echo "</div>";
			echo "</div>";

			echo "</main>";
		}
		catch(PDOException $erreur)	{
			echo "Erreur SELECT Ecole : " . $erreur->getMessage();
		}

		// fermeture de la base
		if (isset($result)) {$result->closeCursor();}
		$db = null;
	
	?>

	<!-- naivigation en bas de page -->
	<footer style='margin-top:40px; margin-bottom:80px;'>
		<br/>

		<!-- retour vers le formulaire -->
		<div class='d-flex justify-content-center'>
			<a class="btn btn-primary" href="#">&uarr; Haut de liste</a>
			&nbsp;&nbsp;&nbsp;&nbsp;

	<?php
		echo "<a class='btn btn-primary' data-retour-navigation href='" . escapeHtml($navigationRetour['url']) . "'>&larr; " . escapeHtml($navigationRetour['libelle']) . "</a>";
	?>
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
		// pour zoomer sur une école
		function zoom(ecole,filiere,concours) {
			<?php
				$baseZoom = 'detail-resultat-admission-par-ecole.php?' . http_build_query([
					'origine' => 'ecole',
					'reference' => $reference,
					'an' => 'toutes',
					'retour' => $retourListe,
				], '', '&', PHP_QUERY_RFC3986);
				echo 'window.location.href = NavigationRetour.urlDestination(' . encodeJs($baseZoom) . " + '&filiere=' + encodeURIComponent(filiere) + '&concours=' + encodeURIComponent(concours) + '&ecole=' + encodeURIComponent(ecole));";
			?>
		}
	</script>

	<!-- interface hybride tableau/accordéons : bascule d'affichage des années anciennes -->
	<script>
		// Desktop : bouton unique au-dessus du tableau, révèle/masque toutes les
		// colonnes "colonne-annee-ancienne" en ajoutant/retirant une classe sur
		// le tableau lui-même (voir CSS dans le <head>).
		function basculerAnneesAnciennes(bouton) {
			var tableau = document.getElementById('tableau-par-ecole');
			var afficheToutesLesAnnees = tableau.classList.toggle('anneesToutesAffichees');
			bouton.innerHTML = afficheToutesLesAnnees
				? "<i class='bi bi-arrows-collapse'></i> Masquer les années anciennes"
				: "<i class='bi bi-arrows-expand'></i> " + bouton.dataset.libelleAfficher;
		}

		// Mobile : bouton global (même libellé et même effet que sur le tableau).
		function basculerAnneesAnciennesMobileGlobal(bouton) {
			var liste = document.getElementById('accordeon-par-ecole');
			var affichees = liste.classList.toggle('toutesAnneesAffichees');
			bouton.innerHTML = affichees
				? "<i class='bi bi-arrows-collapse'></i> Masquer les années anciennes"
				: "<i class='bi bi-arrows-expand'></i> " + bouton.dataset.libelleAfficher;
		}

		// Mobile : bouton "Voir plus d'années" présent dans chaque carte accordéon,
		// ne révèle que les lignes d'années anciennes de SA propre carte.
		function basculerAnneesAnciennesMobile(bouton) {
			var carte = bouton.closest('.accordion-item');
			var afficheToutesLesAnnees = carte.classList.toggle('voir-tout');
			bouton.textContent = afficheToutesLesAnnees ? "Voir moins d'années" : "Voir plus d'années";
		}

		// Correctif du saut de défilement mobile propre au composant accordéon
		// Bootstrap : certains navigateurs mobiles recadrent automatiquement la
		// vue sur le bouton qui vient de recevoir le focus (le déclencheur de
		// l'accordéon) pendant l'animation d'ouverture, provoquant un double
		// saut (vers le haut puis vers le bas) au lieu de laisser la page immobile
		// et de simplement pousser le contenu suivant vers le bas. On retire donc
		// le focus du bouton dès le début de l'ouverture pour supprimer ce recadrage.
		var accordeonEcoles = document.getElementById('accordeonEcoles');
		if (accordeonEcoles) {
			accordeonEcoles.addEventListener('show.bs.collapse', function (evenement) {
				var carte = evenement.target.closest('.accordion-item');
				var entete = carte ? carte.querySelector('.accordion-button') : null;
				if (entete) {
					entete.blur();
				}
			});
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