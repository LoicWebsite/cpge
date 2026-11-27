<!doctype html>

<?php
	/**
	 * ============================================================================
	 * Page : Classement des écoles par sélectivité, pour une filière de CPGE
	 * ============================================================================
	 *
	 * Même définition de la sélectivité que sur les fiches école et la page par
	 * filière : rang du médian des intégrés (ou du dernier intégré) divisé par le
	 * nombre d'inscrits au concours. Plus le pourcentage est faible, plus l'école
	 * est sélective. Comme un concours a un nombre d'inscrits qui lui est propre,
	 * seul ce rapport est comparable d'un concours à l'autre (pas le rang brut).
	 *
	 * Une école peut recruter via plusieurs concours (ou plusieurs formations) :
	 * les lignes de la table Note sont agrégées par école, pondérées par le
	 * nombre d'intégrés.
	 */

	// récupération-contrôle des paramètres ($filiere, $an)
	include "php/controleParametre.php";

	// fonctions communes du site
	include "php/fonctionConcours.php";

	// filière par défaut : MP (la plus représentée)
	if ($filiere === "") {
		$filiere = "mp";
	}
	$filiere = strtolower($filiere);
	// mode "école" : comparer les filières entre elles pour une école donnée
	$ecoleChoisie = trim($ecole);
	$modeEcole = ($ecoleChoisie !== "");
	// mode "Allstar" : toutes les écoles de toutes les filières dans un seul classement,
	// une ligne par couple école + filière (filière entre parenthèses)
	$modeAllstar = !$modeEcole && isset($_GET['filiere']) && strtolower(trim((string) $_GET['filiere'])) === 'allstar';
	$libelleFiliere = $modeAllstar ? "toutes filières" : strtoupper($filiere);

	// filtre par spécialité : seules les écoles qui proposent une spécialité du domaine choisi
	// (un domaine parent reprend ses domaines enfants, comme sur le Panorama des spécialités)
	$domaineChoisi = (!$modeEcole && isset($_GET['domaine']) && ctype_digit((string) $_GET['domaine'])) ? (int) $_GET['domaine'] : 0;
	$nomDomaine = "";
	$listeDomaines = [];
	try {
		$listeDomaines = openDatabase()->query("SELECT IdDomaine, IdParent, Domaine FROM SpecialiteDomaine
				WHERE Actif = 1 ORDER BY COALESCE(IdParent, IdDomaine), (IdParent IS NOT NULL), IdDomaine")->fetchAll(PDO::FETCH_ASSOC);
	} catch (PDOException $erreur) {
		$listeDomaines = [];
	}
	foreach ($listeDomaines as $ligneDomaine) {
		if ((int) $ligneDomaine['IdDomaine'] === $domaineChoisi) $nomDomaine = $ligneDomaine['Domaine'];
	}
	if ($nomDomaine === "") $domaineChoisi = 0;
	$anneeLibelle = ($an === "toutes") ? "toutes les années (2016-2026)" : $an;

	// en dessous de ce nombre d'intégrés, la médiane n'est pas représentative
	// (5 en Allstar : beaucoup de couples école + filière n'ont que quelques intégrés)
	$integresMinimum = $modeAllstar ? 5 : 3;

	// Fiabilité du rang : un rang n'est comparable au nombre d'inscrits que si le classement
	// porte sur la plupart des admissibles. Quand seule une petite partie des admissibles est
	// classée (candidats ayant demandé l'école : Avenir Prépas, Banque CCINP, E3A "autres écoles"...),
	// "6e sur 17 classés" ne veut pas dire "dans les 6 premiers de 355 inscrits". Les lignes dont
	// moins de 40 % des admissibles sont classés sont donc écartées (si l'information existe).
	$partClassesMinimum = 0.4;

	// Attributs data-* d'une ligne : valeurs de l'échelle étalonnée et leur fourchette, lues par le
	// JavaScript quand la case « échelle commune » est cochée.
	function attributsEtal(array $ligne): string {
		$nb = fn($cle) => ($ligne[$cle] !== null) ? number_format((float) $ligne[$cle], 1, '.', '') : '';
		return " data-mediane-etal='" . $nb('SelectiviteEtalMediane') . "' data-mediane-etal-bas='" . $nb('EtalBasMediane')
			. "' data-mediane-etal-haut='" . $nb('EtalHautMediane') . "' data-dernier-etal='" . $nb('SelectiviteEtalDernier')
			. "' data-dernier-etal-bas='" . $nb('EtalBasDernier') . "' data-dernier-etal-haut='" . $nb('EtalHautDernier')
			. "' data-non-etalonne='" . (!empty($ligne['NonEtalonne']) ? '1' : '0') . "'";
	}

	// URL canonique : on garde uniquement les paramètres qui modifient le contenu
	$criteresPage = $modeEcole ? ['ecole' => $ecoleChoisie, 'an' => $an] : ['filiere' => $modeAllstar ? 'allstar' : $filiere, 'an' => $an];
	if ($domaineChoisi > 0) $criteresPage['domaine'] = $domaineChoisi;
	$canonicalUrl = 'https://loic.website/CPGE/selectivite-ecoles-ingenieur-par-filiere.php?'
		. http_build_query($criteresPage, '', '&', PHP_QUERY_RFC3986);
	$retourListe = urlPageNavigation(basename(__FILE__), $criteresPage);
?>

<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Classement des écoles d'ingénieurs par sélectivité selon la filière de CPGE (MP, MPI, PC, PSI, PT, TSI, BCPST...)">
		<link rel="canonical" href="<?php echo escapeHtml($canonicalUrl); ?>" />

	<?php
		// favicons générés par https://realfavicongenerator.net
		include "php/favicon.php";
	?>

    <title>Sélectivité des écoles d'ingénieurs par filière</title>

	<?php
		// styles nécessaires à l'application (bootstrap + fontawasome + ECN)
		include "php/style.php";
	?>

	<style>
		th, td {font-size:100%}
		/* petite jauge : plus elle est courte, plus l'école est sélective */
		.jauge-selectivite {display:inline-block; height:8px; border-radius:4px; background:DarkSlateBlue; vertical-align:middle; margin-left:6px;}
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
					<li class="breadcrumb-item">Palmarès</li>
					<li class="breadcrumb-item active" aria-current="page">Sélectivité</li>
				</ol>
			</div>
		</div>
	</nav>

	<header class='container'>
		<h1 class='h3'><i class="bi bi-funnel"></i>&nbsp;&nbsp;&nbsp;Sélectivité des écoles d'ingénieurs par filière</h1><br>

		<p>
		  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail" onclick="changerTexte(this)">
			+  Comment lire ce classement ?
		  </button>
		</p>

		<div class="collapse" id="detail" style="margin-left:20px;">
			<p>Cette page classe les écoles d'ingénieurs selon leur <strong>sélectivité aux concours SCEI</strong> après une CPGE scientifique : pour une filière, toutes les filières ensemble (« Allstar »), une année ou toutes, et éventuellement une spécialité. La sélectivité mesure le niveau d'entrée, pas la qualité de la formation : à croiser avec les autres palmarès du site.</p>

			<h2 class="h6 mt-3">1. Lire le tableau</h2>
			<ul>
				<li><strong>Sélectivité médiane</strong> : rang du candidat médian parmi les intégrés, divisé par le nombre d'inscrits du concours. 12 % signifie que la moitié des intégrés étaient dans les 12 % premiers inscrits. <strong>Plus le pourcentage est bas, plus l'école est sélective.</strong> Disponible pour presque toutes les écoles.</li>
				<li><strong>Dernier intégré</strong> : même calcul avec le rang du dernier candidat admis. Il indique jusqu'où l'on peut descendre pour entrer, mais il est sensible aux désistements et peu de concours le publient : il n'est affiché que s'il est connu pour au moins 90 % des intégrés de l'école. Les écoles sans dernier intégré sont en fin de tableau, sans rang.</li>
				<li><strong>Classement</strong> : par défaut sur le dernier intégré. Les boutons &darr; des colonnes permettent de classer sur la médiane ; un second clic inverse l'ordre. Les valeurs égales partagent le même rang.</li>
				<li><strong>Rang médian moyen</strong> : la position réelle dans la liste du concours, pour information. Elle dépend de la taille du concours : ne la comparez pas d'un concours à l'autre.</li>
				<li><strong>Intégrés</strong> : nombre d'intégrés pris en compte sur la période. Une école de moins de <?php echo $integresMinimum; ?> intégrés n'est pas classée, car sa médiane serait trop instable. Quand une école recrute par plusieurs concours ou formations, les valeurs sont moyennées en pondérant par les intégrés.</li>
				<li><strong>Triangle d'avertissement</strong> <i class="bi bi-exclamation-triangle-fill text-warning"></i> : l'école recrute sur une liste d'inscription propre, bien plus petite (moins du quart) que celle des autres écoles de la même banque (par exemple l'École navale). Son pourcentage flatte la sélectivité : à lire avec prudence.</li>
				<li><strong>Astérisque</strong> (*) : pour les ENS, les classés portent sur les candidats fonctionnarisables alors que les inscrits portent sur tous les candidats. Le pourcentage est à interpréter avec prudence.</li>
			</ul>

			<h2 class="h6 mt-3">2. Comparer des écoles qui recrutent sur des concours différents</h2>
			<p>Un pourcentage n'est comparable <strong>exactement</strong> qu'entre écoles d'un même concours (même liste de candidats). Entre concours, un « 5 % » dans la banque X-ENS, réservée à des candidats d'élite, ne vaut pas un « 5 % » dans un petit concours ouvert à tous. La case <strong>« Comparer sur une échelle commune »</strong> corrige cet écart de niveau :</p>
			<ol>
				<li>chaque rang est converti en échelle logit (un « log des chances »), où les écarts deviennent additifs ;</li>
				<li>le <strong>niveau moyen DAUR</strong> des écoles sert de repère national commun : on mesure, pour chaque concours, de combien les rangs de ses écoles s'écartent de ce que leur niveau prévoit. Cet écart est le <em>décalage</em> du concours (le niveau DAUR ne classe aucune école, il ne règle qu'un coefficient par concours) ;</li>
				<li>on retire ce décalage des rangs de toutes les écoles du concours, par rapport au plus large concours de la filière (proche de la promotion entière) ;</li>
				<li>la valeur est affichée avec une <strong>fourchette à 95 %</strong> entre crochets.</li>
			</ol>
			<p>L'étalonnage est une estimation : deux écoles de concours différents dont les valeurs diffèrent de <strong>moins de 2 fois</strong> ne sont pas significativement différentes. Un concours ayant trop peu d'écoles-repères (moins de 3) est « non étalonné » : il n'est pas classé dans cette vue. Les décalages sont recalculés à chaque mise à jour des données, sur les 4 dernières années. Contrôle : en retirant une école et en la prédisant avec les autres, l'erreur reste de l'ordre de 0,5 en logit.</p>

			<h2 class="h6 mt-3">3. Les filtres</h2>
			<ul>
				<li><strong>Filière ou Allstar</strong> : Allstar classe ensemble tous les couples école + filière (par exemple « Ecole Polytechnique (MP) »), avec au moins 5 intégrés par couple. Les concours communs à plusieurs filières (Groupe INSA, CESI, Ingeni'Up), qui recopient la même ligne partout, n'y figurent pas.</li>
				<li><strong>Concours</strong> : une année, ou toutes les années (moyenne pondérée par les intégrés, plus stable).</li>
				<li><strong>Spécialité</strong> : ne garde que les écoles qui proposent au moins une spécialité du domaine choisi (un domaine général reprend ses sous-domaines). La sélectivité reste celle du recrutement de l'école dans sa filière, pas celle de la spécialité. Les écoles dont les spécialités ne sont pas référencées sur le site n'apparaissent pas.</li>
				<li><strong>Comparer les filières pour une école</strong> : pour une école, la sélectivité filière par filière, de la plus accessible à la plus sélective, avec le nombre de places et d'intégrés. Le badge « la plus accessible » n'apparaît que si l'écart avec la filière suivante est net (au moins 20 %). Penser aussi au nombre de places : peu de places rend l'entrée plus difficile que le pourcentage ne le laisse croire.</li>
			</ul>

			<h2 class="h6 mt-3">4. Ce qui est écarté du calcul</h2>
			<ul>
				<li>les concours <strong>Inter-Filière</strong> (par exemple Polytech), dont le rang porte sur toutes les filières à la fois, et les concours ou formations réservés aux candidats étrangers ;</li>
				<li>les lignes dont le rang médian dépasse le nombre d'inscrits, et les « derniers intégrés » meilleurs que le rang médian (incohérents) ;</li>
				<li>les lignes où moins de <?php echo (int) round($partClassesMinimum * 100); ?> % des admissibles sont classés (Avenir Prépas, Banque CCINP, « autres écoles » E3A...) : un « 6<sup>e</sup> sur 17 classés » n'a aucun rapport avec le nombre d'inscrits.</li>
			</ul>

			<h2 class="h6 mt-3">5. Limites</h2>
			<p>Le niveau moyen DAUR dont dépend l'échelle commune est publié sans formule détaillée ; son rattachement aux écoles est automatique. Les listes de candidats d'un même concours ne sont pas toujours identiques (par exemple les options de Polytechnique et les ENS dans la banque X-ENS), ce que l'étalonnage ne corrige pas. Lisez le haut du classement par <strong>blocs</strong>, pas au rang près.</p>
			<br>
		</div>

		<!-- choix de la filière et de l'année -->
		<form method="get" class="row g-2 align-items-end" style="margin-bottom:20px;" aria-label="Choix de la filière et de l'année">
			<div class="col-auto">
				<label for="filiere" class="form-label mb-0">Filière</label>
				<select id="filiere" name="filiere" class="form-select" onchange="this.form.submit()"<?php echo $modeEcole ? " disabled title=\"Désactivé : toutes les filières sont comparées pour l'école choisie\"" : ""; ?>>
				<?php
					$filieresAffichees = ["mp" => "MP", "mpi" => "MPI", "pc" => "PC", "psi" => "PSI", "pt" => "PT", "tsi" => "TSI", "bcpst" => "BCPST", "tb" => "TB", "tpc" => "TPC"];
					echo "<option value='allstar'" . ($modeAllstar ? " selected" : "") . ">Allstar (toutes les filières)</option>";
					foreach ($filieresAffichees as $code => $libelle) {
						echo "<option value='" . $code . "'" . (!$modeAllstar && $code === $filiere ? " selected" : "") . ">" . $libelle . "</option>";
					}
				?>
				</select>
			</div>
			<div class="col-auto">
				<label for="domaine" class="form-label mb-0">Spécialité</label>
				<select id="domaine" name="domaine" class="form-select" onchange="this.form.submit()"<?php echo $modeEcole ? " disabled title=\"Désactivé : la comparaison par école porte sur toutes ses filières\"" : ""; ?>>
					<option value="0">&mdash; Toutes les spécialités &mdash;</option>
				<?php
					foreach ($listeDomaines as $ligneDomaine) {
						$estEnfant = !empty($ligneDomaine['IdParent']);
						echo "<option value='" . (int) $ligneDomaine['IdDomaine'] . "'" . ((int) $ligneDomaine['IdDomaine'] === $domaineChoisi ? " selected" : "") . ">"
							. ($estEnfant ? "&nbsp;&nbsp;&nbsp;&rarr; " : "") . escapeHtml($ligneDomaine['Domaine']) . "</option>";
					}
				?>
				</select>
			</div>
			<div class="col-auto">
				<label for="an" class="form-label mb-0">Concours</label>
				<select id="an" name="an" class="form-select" onchange="this.form.submit()">
				<?php
					foreach ($allowed_years as $annee) {
						$texte = ($annee === "toutes") ? "Toutes les années" : $annee;
						echo "<option value='" . $annee . "'" . ($annee === $an ? " selected" : "") . ">" . $texte . "</option>";
					}
				?>
				</select>
			</div>
			<div class="col-auto">
				<label for="ecole" class="form-label mb-0">Comparer les filières pour une école</label>
				<select id="ecole" name="ecole" class="form-select" onchange="this.form.submit()">
					<option value="">&mdash; Classer toutes les écoles &mdash;</option>
				<?php
					// liste des écoles ayant des résultats (référentiel du site)
					try {
						$dbListe = openDatabase();
						$listeEcoles = $dbListe->query("SELECT DISTINCT Ecole FROM EcoleConcours WHERE Ecole IS NOT NULL ORDER BY Ecole")->fetchAll(PDO::FETCH_COLUMN);
					} catch (PDOException $erreur) {
						$listeEcoles = [];
					}
					foreach ($listeEcoles as $nomListe) {
						echo "<option value='" . escapeHtml($nomListe) . "'" . ($nomListe === $ecoleChoisie ? " selected" : "") . ">" . escapeHtml($nomListe) . "</option>";
					}
				?>
				</select>
			</div>
			<noscript><div class="col-auto"><button type="submit" class="btn btn-primary">Afficher</button></div></noscript>
		</form>
	</header>

	<main class='container'>
		<!-- bascule : pourcentage du concours / échelle commune étalonnée -->
		<div class="form-check mb-3" id="bloc-etal">
			<input class="form-check-input" type="checkbox" id="baseEtal" onchange="appliquerEchelle(this.checked)">
			<label class="form-check-label" for="baseEtal">
				Comparer sur une échelle commune (étalonnée)
				<i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" data-bs-html="true" title="Par défaut, un pourcentage est rapporté aux inscrits de son concours : il ne se compare exactement qu'entre écoles du même concours. Cochez pour corriger l'écart de niveau entre concours (repère : niveau moyen DAUR) et voir une fourchette à 95 %. Deux écoles de concours différents à moins de 2 fois d'écart ne sont pas significativement différentes."></i>
			</label>
		</div>
	<?php
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . escapeHtml($erreur->getMessage()));
		}

		// L'échelle étalonnée suppose la table ConcoursEtalonnage (php/calculerEtalonnage.php).
		$etalDisponible = false;
		try {
			$etalDisponible = (bool) $db->query("SHOW TABLES LIKE 'ConcoursEtalonnage'")->fetchColumn();
		} catch (PDOException $erreur) {
			$etalDisponible = false;
		}

		// Repère affiché dans la note : pour chaque filière et chaque année, le plus grand nombre
		// d'inscrits d'un concours propre à la filière. Les concours communs à plusieurs filières
		// (même nombre d'inscrits recopié dans chacune) et les concours inter-filières ou
		// étrangers sont écartés : leur effectif ne représente pas la filière.
		$sousRequetePromo = "(SELECT n2.An, n2.Filiere, MAX(n2.Inscrit) AS Promo
						FROM Note n2
						LEFT JOIN (SELECT An, Concours, Inscrit FROM Note
								GROUP BY An, Concours, Inscrit HAVING COUNT(DISTINCT Filiere) > 1) c
							ON c.An = n2.An AND c.Concours = n2.Concours AND c.Inscrit = n2.Inscrit
						WHERE c.An IS NULL
							AND n2.Concours NOT LIKE '%Inter-Fili_re%' AND n2.Concours NOT LIKE '% Etr.%'
						GROUP BY n2.An, n2.Filiere)";
		// Étalonnage : décalage de niveau du concours (échelle logit) et son incertitude.
		$jointureEtal = $etalDisponible ? "
				LEFT JOIN ConcoursEtalonnage et ON et.Filiere = n.Filiere AND et.Concours = n.Concours" : "";
		$decalage = $etalDisponible ? "COALESCE(et.Decalage, 0)" : "0";
		$erreurEtal = $etalDisponible ? "COALESCE(et.Erreur, 0)" : "0";
		$nonEtalonne = $etalDisponible ? "et.Decalage IS NULL" : "1 = 1";

		// Effectifs de référence par filière (plus grand concours propre à la filière), pour la note
		// de la case à cocher. Une année : valeur de l'année ; toutes les années : moyenne annuelle.
		$effectifsPromo = [];
		try {
			$paramsPromo = [];
			$filtrePromo = "";
			if ($an !== "toutes") {
				$filtrePromo = " WHERE p.An = :an";
				$paramsPromo[':an'] = $an;
			}
			$stmtPromo = $db->prepare("SELECT p.Filiere, AVG(p.Promo) AS Promo FROM " . $sousRequetePromo . " p" . $filtrePromo . " GROUP BY p.Filiere");
			$stmtPromo->execute($paramsPromo);
			foreach ($stmtPromo->fetchAll(PDO::FETCH_ASSOC) as $lignePromo) {
				$effectifsPromo[strtolower($lignePromo['Filiere'])] = (float) $lignePromo['Promo'];
			}
		} catch (PDOException $erreur) {
			$effectifsPromo = [];
		}
		$detailEffectifs = [];
		$totalPromo = 0;
		foreach ($filieresAffichees as $codeEffectif => $libelleEffectif) {
			if (!isset($effectifsPromo[$codeEffectif])) continue;
			$totalPromo += $effectifsPromo[$codeEffectif];
			$texteEffectif = $libelleEffectif . "&nbsp;" . str_replace(' ', '&nbsp;', formater(round($effectifsPromo[$codeEffectif]), 0));
			$detailEffectifs[] = (!$modeEcole && !$modeAllstar && $codeEffectif === $filiere) ? "<strong>" . $texteEffectif . "</strong>" : $texteEffectif;
		}
		echo "<div id='note-base' class='alert alert-info py-2' hidden>";
		echo "Pourcentages <strong>étalonnés sur une échelle commune</strong> : chaque rang est corrigé de l'écart de niveau de son concours (un « 5 % » dans un petit concours peu sélectif ne vaut pas un « 5 % » dans une banque d'élite). Cet écart est estimé avec le niveau moyen DAUR des écoles qui y recrutent ; il ne sert qu'à régler un coefficient par concours, jamais à classer une école. L'échelle de référence est le plus large concours de la filière (proche de la promotion entière). La fourchette entre crochets (95 %) donne l'incertitude de cet étalonnage, d'un facteur 2 environ dans chaque sens au minimum : <strong>deux écoles de concours différents dont les valeurs diffèrent de moins de 2 fois ne sont pas significativement différentes</strong>. Un concours comptant trop peu d'écoles-repères n'est pas étalonné (« non étalonné ») : son pourcentage reste celui du concours. Les comparaisons au sein d'un même concours restent exactes.";
		if (!empty($detailEffectifs)) {
			echo "<br><strong>Effectifs de référence (" . ($an === "toutes" ? "moyenne annuelle 2016-2026" : "concours " . escapeHtml($an)) . ") :</strong> environ "
				. str_replace(' ', '&nbsp;', formater(round($totalPromo), 0)) . "&nbsp;étudiants en tout, dont " . implode(" &middot; ", $detailEffectifs) . ".";
		}
		echo "</div>";
		if (!$etalDisponible) {
			echo "<script>document.getElementById('bloc-etal').hidden = true;</script>";
		}

		// Valeurs étalonnées : rang converti en logit, corrigé du décalage du concours, puis reconverti
		// en pourcentage ; la fourchette applique +/- 1,96 fois l'incertitude de l'étalonnage.
		// Un concours non étalonné (décalage NULL) garde son pourcentage brut, sans fourchette.
		$logitMed = "LN((n.RangMedian / (n.Inscrit + 1)) / (1 - n.RangMedian / (n.Inscrit + 1)))";
		$logitDer = "LN((n.Dernier / (n.Inscrit + 1)) / (1 - n.Dernier / (n.Inscrit + 1)))";
		$dernierValide = "n.Dernier > 0 AND n.Dernier >= n.RangMedian AND n.Dernier <= n.Inscrit";
		$etalMed = fn($signe) => "100 * SUM(n.Integre / (1 + EXP(-(" . $logitMed . " - " . $decalage . $signe . " 1.96 * " . $erreurEtal . ")))) / SUM(n.Integre)";
		$etalDer = fn($signe) => "CASE WHEN SUM(CASE WHEN " . $dernierValide . " THEN n.Integre END) >= 0.9 * SUM(n.Integre)
							THEN 100 * SUM(CASE WHEN " . $dernierValide . " THEN n.Integre / (1 + EXP(-(" . $logitDer . " - " . $decalage . $signe . " 1.96 * " . $erreurEtal . "))) END)
								/ SUM(CASE WHEN " . $dernierValide . " THEN n.Integre END)
						END";
		$colonnesEtal = ",
					" . $etalMed("+ 0 *") . " AS SelectiviteEtalMediane,
					" . $etalMed("-") . " AS EtalBasMediane,
					" . $etalMed("+") . " AS EtalHautMediane,
					" . $etalDer("+ 0 *") . " AS SelectiviteEtalDernier,
					" . $etalDer("-") . " AS EtalBasDernier,
					" . $etalDer("+") . " AS EtalHautDernier,
					CASE WHEN SUM(CASE WHEN " . $nonEtalonne . " THEN n.Integre END) >= 0.5 * SUM(n.Integre) THEN 1 ELSE 0 END AS NonEtalonne";
		$jointureAllstar = "";

		// Bassin de candidats restreint : une école dont le concours a une liste d'inscription propre,
		// bien plus petite (moins du quart) que le plus gros groupe de la même banque, la même année
		// et dans la même filière (ex. École navale : 818 inscrits contre 3 700 pour Centrale dans la
		// banque Centrale-Supélec). Son pourcentage flatte la sélectivité : on le signale par un triangle.
		$jointureBassin = "
				LEFT JOIN (SELECT An, Filiere, Concours, MAX(Inscrit) AS PoolMax
						FROM Note GROUP BY An, Filiere, Concours) pm
					ON pm.An = n.An AND pm.Filiere = n.Filiere AND pm.Concours = n.Concours";
		$colonneBassin = ",
					CASE WHEN SUM(CASE WHEN n.Inscrit < 0.25 * pm.PoolMax THEN n.Integre END) >= 0.5 * SUM(n.Integre) THEN 1 ELSE 0 END AS BassinRestreint";
		$triangleBassin = " <i class='bi bi-exclamation-triangle-fill text-warning' data-bs-toggle='tooltip' title='Bassin de candidats restreint : ce concours a une liste d&apos;inscription propre à l&apos;école, bien plus petite que celle des autres écoles de la même banque. Le pourcentage est à interpréter avec prudence.'></i>";

		if (!$modeEcole) {
		// Agrégation par école. Le DISTINCT évite de compter deux fois une ligne de
		// Note si le rattachement EcoleConcours existait en double.
		$params = [':minimum' => $integresMinimum];
		$filtreFiliere = "";
		if (!$modeAllstar) {
			$filtreFiliere = " AND n.Filiere = :filiere";
			$params[':filiere'] = $filiere;
		} else {
			// Allstar : un concours commun à plusieurs filières (Groupe INSA, CESI, Ingeni'Up...)
			// recopie la même ligne dans chaque filière. On l'écarte pour ne pas créer de
			// lignes jumelles (même école, mêmes chiffres, filières différentes).
			// (jointure calculée une seule fois plutôt qu'un NOT EXISTS par ligne de Note)
			$jointureAllstar = "
				LEFT JOIN (SELECT An, Concours, EcoleConcours, Inscrit, Integre, RangMedian FROM Note
						GROUP BY An, Concours, EcoleConcours, Inscrit, Integre, RangMedian HAVING COUNT(DISTINCT Filiere) > 1) j
					ON j.An = n.An AND j.Concours = n.Concours AND j.EcoleConcours = n.EcoleConcours
						AND j.Inscrit = n.Inscrit AND j.Integre = n.Integre AND j.RangMedian = n.RangMedian";
			$filtreFiliere = " AND j.An IS NULL";
		}
		$filtreAn = "";
		if ($an !== "toutes") {
			$filtreAn = " AND n.An = :an";
			$params[':an'] = $an;
		}
		// Filtre par spécialité : écoles ayant au moins une spécialité retenue (Decision = inclure)
		// dans le domaine choisi ou dans l'un de ses domaines enfants.
		$filtreDomaine = "";
		if ($domaineChoisi > 0) {
			$filtreDomaine = " AND ec.Ecole IN (SELECT e.Ecole
					FROM SpecialiteDomaineAffectation sa
					INNER JOIN SpecialiteDomaine sd ON sd.IdDomaine = sa.IdDomaine
					INNER JOIN Specialite sp ON sp.IdSpecialite = sa.IdSpecialite
					INNER JOIN Diplome di ON di.IdDiplome = sp.IdDiplome
					INNER JOIN Ecole e ON e.IdEcole = di.IdEcole
					WHERE sa.Decision = 'inclure' AND sd.Actif = 1
						AND (sd.IdDomaine = :domaine1 OR sd.IdParent = :domaine2))";
			$params[':domaine1'] = $domaineChoisi;
			$params[':domaine2'] = $domaineChoisi;
		}
		$sql = "SELECT ec.Ecole, n.Filiere,
					SUM(n.Integre) AS Integres,
					SUM(n.Integre * n.RangMedian) / SUM(n.Integre) AS RangMedianMoyen,
					100 * SUM(n.Integre * n.RangMedian / n.Inscrit) / SUM(n.Integre) AS SelectiviteMediane,
					CASE WHEN SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END) >= 0.9 * SUM(n.Integre)
							THEN 100 * SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre * n.Dernier / n.Inscrit END)
								/ SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END)
						END AS SelectiviteDernier" . $colonnesEtal . $colonneBassin . "
				FROM Note n
				INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
					ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
				" . $jointureEtal . $jointureBassin . $jointureAllstar . "
				WHERE 1 = 1" . $filtreFiliere . $filtreAn . $filtreDomaine . "
					AND ec.Ecole IS NOT NULL
					AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0
					AND n.RangMedian <= n.Inscrit
					AND (n.Admissible IS NULL OR n.Admissible = 0 OR n.Classe >= " . $partClassesMinimum . " * n.Admissible)
					AND n.Concours NOT LIKE '%Inter-Fili_re%'
						AND n.Concours NOT LIKE '% Etr.%' AND n.EcoleConcours NOT LIKE '% Etr.%' AND n.EcoleConcours NOT LIKE '% Etranger%'
				GROUP BY ec.Ecole, n.Filiere
				HAVING SUM(n.Integre) >= :minimum
				ORDER BY (SelectiviteDernier IS NULL), SelectiviteDernier ASC, SelectiviteMediane ASC, ec.Ecole ASC, n.Filiere ASC";
		if ($debug) echo "SQL = " . escapeHtml($sql) . "<br>";

		try {
			$stmt = $db->prepare($sql);
			$stmt->execute($params);
			$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
		}
		catch(PDOException $erreur)	{
			echo "<div class='alert alert-warning'>Erreur SELECT sélectivité : " . escapeHtml($erreur->getMessage()) . "</div>";
			$lignes = [];
		}

		if (empty($lignes)) {
			echo "<div class='alert alert-info'>Aucune école n'a au moins " . $integresMinimum . " intégrés avec un rang médian publié en " . $libelleFiliere . ($domaineChoisi > 0 ? ", spécialité " . escapeHtml($nomDomaine) : "") . " (" . escapeHtml($anneeLibelle) . ").</div>";
		} else {
			$maxSelectivite = max(array_filter(array_column($lignes, 'SelectiviteDernier'), fn($v) => $v !== null) ?: [0.1]);
			$idTableau = '"#tableau-selectivite","rang;école;sélectivité médiane (%);rang médian moyen;dernier intégré (%);intégrés"';

			echo "<h2 class='h5'>" . count($lignes) . ($modeAllstar ? " couples école (filière) &ndash; toutes filières confondues &ndash; concours " : " écoles en " . $libelleFiliere . " &ndash; concours ") . escapeHtml($anneeLibelle) . ($domaineChoisi > 0 ? " &ndash; spécialité " . escapeHtml($nomDomaine) : "") . "</h2>";
			echo "<table id='tableau-selectivite' data-direction='asc' data-rang='1'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(" . $idTableau . ")'><i class='bi bi-download'></i> csv</button></caption>";
			echo "<thead class='text-center'><tr>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Classement sur le critère dont le bouton &darr; est en bleu (le dernier intégré par défaut). Les valeurs égales partagent le même rang ; les écoles sans valeur sont en fin de tableau, sans rang.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='mediane' title='Trier par sélectivité médiane' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Sélectivité médiane&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang du candidat médian parmi les intégrés, divisé par les inscrits du concours. Plus c&apos;est bas, plus l&apos;école est sélective. Avec la case « échelle commune » : valeur corrigée de l&apos;écart de niveau entre concours, suivie d&apos;une fourchette à 95 %.'></i></th>";
			echo "<th>&nbsp;Rang médian&nbsp;<br>moyen&nbsp;<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Position réelle du candidat médian dans la liste du concours, moyennée par école. Elle dépend de la taille du concours : ne pas la comparer d&apos;un concours à l&apos;autre.'></i></th>";
			echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='dernier' title='Trier par dernier intégré' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Dernier intégré&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang du dernier candidat admis, divisé par les inscrits. Indique jusqu&apos;où l&apos;on peut descendre pour entrer, mais il est sensible aux désistements. Vide s&apos;il n&apos;est pas publié pour au moins 90 % des intégrés.'></i></th>";
			echo "<th>&nbsp;Intégrés&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Nombre d&apos;intégrés pris en compte sur la période. Moins de 3 (5 en Allstar) : l&apos;école n&apos;est pas classée ; moins de 5 : valeur fragile.'></i></th>";
			echo "</tr></thead><tbody>";

			$rang = 0;
			$position = 0;
			$valeurPrecedente = null;
			foreach ($lignes as $ligne) {
				$position++;
				$mediane = round((float)$ligne['SelectiviteMediane'], 1);
				$dernier = ($ligne['SelectiviteDernier'] === null) ? null : round((float)$ligne['SelectiviteDernier'], 1);
				// égalité sur la valeur affichée : même rang
				if ($dernier !== null && $dernier !== $valeurPrecedente) {
					$rang = $position;
					$valeurPrecedente = $dernier;
				}
				$nomEcole = $ligne['Ecole'];
				$filiereLigne = $modeAllstar ? strtolower($ligne['Filiere']) : $filiere;
				$libelleLigne = $nomEcole . ($modeAllstar ? " (" . strtoupper($ligne['Filiere']) . ")" : "");
				$ens = (bool) preg_match('/^(ENS\b|[ÉE]cole normale)/iu', $nomEcole);
				$largeurJauge = ($dernier === null) ? 0 : max(2, (int) round(60 * $dernier / max($maxSelectivite, 0.1)));
				$urlDetail = 'detail-resultat-admission-par-ecole.php?origine=selectivite&amp;retour=' . rawurlencode($retourListe)
					. '&amp;ecole=' . rawurlencode($nomEcole) . '&amp;filiere=' . rawurlencode($filiereLigne);

				echo "<tr ondblclick='zoom(" . encodeJs($nomEcole) . ", " . encodeJs($filiereLigne) . ")'" . " data-mediane='" . number_format($mediane, 1, '.', '') . "' data-dernier='" . ($ligne['SelectiviteDernier'] !== null ? number_format((float) $ligne['SelectiviteDernier'], 1, '.', '') : '')
					. "'" . attributsEtal($ligne) . ">";
				echo "<td style='text-align:center'>" . ($dernier === null ? "&ndash;" : $rang) . "</td>";
				echo "<td style='padding-left:10px'><a href='" . $urlDetail . "'><strong>" . escapeHtml($libelleLigne) . "</strong></a>" . ($ens ? " *" : "") . (!empty($ligne['BassinRestreint']) ? $triangleBassin : "")
					. " <a href='?ecole=" . rawurlencode($nomEcole) . "&amp;an=" . rawurlencode($an) . "' title='Comparer les filières pour cette école' aria-label='Comparer les filières pour " . escapeHtml($nomEcole) . "'><i class='bi bi-bar-chart-steps'></i></a></td>";
				echo "<td style='text-align:right; white-space:nowrap'>" . "<span class='v'>" . formater($mediane, 1) . "%</span><small class='fourchette text-muted'></small>&nbsp;</td>";
				echo "<td style='text-align:right'>" . formater($ligne['RangMedianMoyen'], 0) . "&nbsp;</td>";
				echo "<td style='text-align:right'>" . ($ligne['SelectiviteDernier'] !== null ? "<span class='v'>" . formater($ligne['SelectiviteDernier'], 1) . "%</span><small class='fourchette text-muted'></small>&nbsp;<span class='jauge-selectivite' style='width:" . $largeurJauge . "px' aria-hidden='true'></span>" : "") . "</td>";
				echo "<td style='text-align:center'>" . (int) $ligne['Integres'] . "</td>";
				echo "</tr>";
			}
			echo "</tbody></table>";
		}

		} else {
			// Comparaison des filières pour une école : même calcul, regroupé par filière.
			$params = [':ecole' => $ecoleChoisie];
			$filtreAn = "";
			if ($an !== "toutes") {
				$filtreAn = " AND n.An = :an";
				$params[':an'] = $an;
			}
			$sql = "SELECT n.Filiere,
						SUM(n.Integre) AS Integres,
						SUM(n.Place) AS Places,
						SUM(n.Integre * n.RangMedian) / SUM(n.Integre) AS RangMedianMoyen,
						100 * SUM(n.Integre * n.RangMedian / n.Inscrit) / SUM(n.Integre) AS SelectiviteMediane,
						CASE WHEN SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END) >= 0.9 * SUM(n.Integre)
							THEN 100 * SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre * n.Dernier / n.Inscrit END)
								/ SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END)
						END AS SelectiviteDernier" . $colonnesEtal . $colonneBassin . "
					FROM Note n
					INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
						ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
					" . $jointureEtal . $jointureBassin . "
					WHERE ec.Ecole = :ecole" . $filtreAn . "
						AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0
						AND n.RangMedian <= n.Inscrit
					AND (n.Admissible IS NULL OR n.Admissible = 0 OR n.Classe >= " . $partClassesMinimum . " * n.Admissible)
						AND n.Concours NOT LIKE '%Inter-Fili_re%'
						AND n.Concours NOT LIKE '% Etr.%' AND n.EcoleConcours NOT LIKE '% Etr.%' AND n.EcoleConcours NOT LIKE '% Etranger%'
					GROUP BY n.Filiere
					ORDER BY (SelectiviteDernier IS NULL), SelectiviteDernier DESC, SelectiviteMediane DESC";
			if ($debug) echo "SQL = " . escapeHtml($sql) . "<br>";
			try {
				$stmt = $db->prepare($sql);
				$stmt->execute($params);
				$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
			}
			catch(PDOException $erreur)	{
				echo "<div class='alert alert-warning'>Erreur SELECT comparaison : " . escapeHtml($erreur->getMessage()) . "</div>";
				$lignes = [];
			}

			$ens = (bool) preg_match('/^(ENS\b|[ÉE]cole normale)/iu', $ecoleChoisie);
			echo "<h2 class='h5'>" . escapeHtml($ecoleChoisie) . ($ens ? " *" : "") . " &ndash; sélectivité par filière, concours " . escapeHtml($anneeLibelle) . "</h2>";

			if (count($lignes) < 1) {
				echo "<div class='alert alert-info'>Aucun rang médian publié pour cette école (" . escapeHtml($anneeLibelle) . "). Essayez une autre année ou « Toutes les années ».</div>";
			} else {
				// la filière la plus accessible : sélectivité la plus haute parmi les filières assez représentées
				// le badge n'est affiché que si l'écart avec la suivante est net (au moins 20 % en relatif)
				$accessible = null;
				$valeursRepresentees = [];
				foreach ($lignes as $i => $ligne) {
					if ((int) $ligne['Integres'] >= $integresMinimum) {
						if ($ligne['SelectiviteDernier'] === null) continue;
						if ($accessible === null) $accessible = $i;
						$valeursRepresentees[] = (float) $ligne['SelectiviteDernier'];
					}
				}
				$ecartNet = (count($valeursRepresentees) >= 2) && ($valeursRepresentees[0] >= 1.2 * $valeursRepresentees[1]);
				$maxSelectivite = max(array_filter(array_column($lignes, 'SelectiviteDernier'), fn($v) => $v !== null) ?: [0.1]);
				$idTableau = '"#tableau-comparaison","filière;sélectivité médiane (%);rang médian moyen;dernier intégré (%);places;intégrés"';

				echo "<table id='tableau-comparaison' data-direction='desc'>";
				echo "<caption style='caption-side:top;'><small>Classé de la filière où l'école est la plus accessible à la plus sélective. Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une filière pour voir le détail des admissions.";
				echo "<br>Cliquer sur le bouton pour télécharger le tableau au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(" . $idTableau . ")'><i class='bi bi-download'></i> csv</button></caption>";
				echo "<thead class='text-center'><tr>";
				echo "<th>&nbsp;Filière&nbsp;</th>";
				echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='mediane' title='Trier par sélectivité médiane' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Sélectivité médiane&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang du candidat médian parmi les intégrés, divisé par les inscrits du concours. Plus la valeur est élevée, plus l&apos;école est accessible dans cette filière. Avec la case « échelle commune » : valeur corrigée, suivie d&apos;une fourchette à 95 %.'></i></th>";
				echo "<th>&nbsp;Rang médian&nbsp;<br>moyen&nbsp;</th>";
				echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='dernier' title='Trier par dernier intégré' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Dernier intégré&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang du dernier candidat admis, divisé par les inscrits. Indique jusqu&apos;où l&apos;on peut descendre pour entrer, mais il est sensible aux désistements. Vide s&apos;il n&apos;est pas publié pour au moins 90 % des intégrés.'></i></th>";
				echo "<th>&nbsp;Places&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Places offertes sur la période choisie. Peu de places rend l&apos;entrée plus difficile que le pourcentage ne le laisse croire.'></i></th>";
				echo "<th>&nbsp;Intégrés&nbsp;</th>";
				echo "</tr></thead><tbody>";

				foreach ($lignes as $i => $ligne) {
					$codeFiliere = strtolower($ligne['Filiere']);
					$mediane = round((float)$ligne['SelectiviteMediane'], 1);
				$dernier = ($ligne['SelectiviteDernier'] === null) ? null : round((float)$ligne['SelectiviteDernier'], 1);
					$peuRepresentatif = ((int) $ligne['Integres'] < $integresMinimum);
					$largeurJauge = ($dernier === null) ? 0 : max(2, (int) round(60 * $dernier / max($maxSelectivite, 0.1)));
					$styleLigne = $peuRepresentatif ? " style='color:#888'" : "";
					$urlDetail = 'detail-resultat-admission-par-ecole.php?origine=selectivite&amp;retour=' . rawurlencode($retourListe)
						. '&amp;ecole=' . rawurlencode($ecoleChoisie) . '&amp;filiere=' . rawurlencode($codeFiliere);

					echo "<tr ondblclick='zoomFiliere(" . encodeJs($codeFiliere) . ")'" . $styleLigne . " data-mediane='" . number_format($mediane, 1, '.', '') . "' data-dernier='" . ($ligne['SelectiviteDernier'] !== null ? number_format((float) $ligne['SelectiviteDernier'], 1, '.', '') : '')
					. "'" . attributsEtal($ligne) . ">";
					echo "<td style='padding-left:10px'><a href='" . $urlDetail . "'><strong>" . escapeHtml(strtoupper($ligne['Filiere'])) . "</strong></a>" . (!empty($ligne['BassinRestreint']) ? $triangleBassin : "");
					if ($i === $accessible && $ecartNet) {
						echo " <span class='badge bg-success' title='Selon le dernier intégré'>la plus accessible</span>";
					}
					if ($peuRepresentatif) {
						echo " <small><i class='bi bi-exclamation-triangle' data-bs-toggle='tooltip' title='Moins de " . $integresMinimum . " intégrés : valeur peu représentative, exclue de la comparaison.'></i></small>";
					}
					echo "</td>";
					echo "<td style='text-align:right; white-space:nowrap'>" . "<span class='v'>" . formater($mediane, 1) . "%</span><small class='fourchette text-muted'></small>&nbsp;</td>";
					echo "<td style='text-align:right'>" . formater($ligne['RangMedianMoyen'], 0) . "&nbsp;</td>";
					echo "<td style='text-align:right'>" . ($ligne['SelectiviteDernier'] !== null ? "<span class='v'>" . formater($ligne['SelectiviteDernier'], 1) . "%</span><small class='fourchette text-muted'></small>&nbsp;<span class='jauge-selectivite' style='width:" . $largeurJauge . "px' aria-hidden='true'></span>" : "") . "</td>";
					echo "<td style='text-align:center'>" . (int) $ligne['Places'] . "</td>";
					echo "<td style='text-align:center'>" . (int) $ligne['Integres'] . "</td>";
					echo "</tr>";
				}
				echo "</tbody></table>";
				echo "<p class='mt-3'><small>Quand les pourcentages sont proches (écart inférieur à 20 % en relatif), aucune filière n'est mise en avant : la différence n'est pas significative. Pour un élève en CPGE : si votre rang attendu en pourcentage des inscrits est inférieur à la valeur de la filière, vous avez de bonnes chances d'intégrer. Les filières absentes n'ont aucun intégré avec rang médian publié (ou passent par un concours inter-filière).</small></p>";
			}
		}

		$db = null;
	?>
	</main>

	<!-- bas de page -->
	<footer class="container" style='margin-top:40px; margin-bottom:80px;'>
		<br>
		<div class='d-flex justify-content-center'>
			<a class="btn btn-primary" href="#">&uarr; Haut de liste</a>
		</div>
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

	<!-- navigation -->
	<script>
		// pour zoomer sur une école (fiche école, onglet admissions de la filière choisie)
		function zoom(ecole, filiereLigne) {
			<?php
				echo 'window.location.href = NavigationRetour.urlDestination(' . encodeJs('detail-resultat-admission-par-ecole.php?origine=selectivite&filiere=') . ' + encodeURIComponent(filiereLigne || ' . encodeJs($filiere) . ') + "&ecole=" + encodeURIComponent(ecole));';
			?>
		}

		// double-clic sur une filière de la comparaison : fiche de l'école, onglet de cette filière
		function zoomFiliere(filiere) {
			<?php
				$baseZoomFiliere = 'detail-resultat-admission-par-ecole.php?origine=selectivite&ecole=' . rawurlencode($ecoleChoisie) . '&filiere=';
				echo 'window.location.href = NavigationRetour.urlDestination(' . encodeJs($baseZoomFiliere) . ' + encodeURIComponent(filiere));';
			?>
		}

		// Tri local du tableau sans rechargement de page (comme la page attractivité).
		// Premier clic : ordre par défaut du tableau ; second clic : ordre inverse.
		// Les lignes sans valeur (dernier intégré non publié) restent toujours en fin de tableau.
		var etatTri = {colonne: 'dernier', direction: null};
		var echelle = 'concours'; // 'concours' (pourcentage du concours) ou 'etal' (échelle étalonnée)

		function trierSelectivite(bouton) {
			appliquerTri(bouton.closest('table'), bouton.dataset.tri, null);
		}

		function appliquerTri(table, colonne, directionImposee) {
			var defaut = table.dataset.direction;
			var direction = directionImposee;
			if (!direction) {
				if (etatTri.colonne === colonne) {
					direction = (etatTri.direction || defaut) === 'asc' ? 'desc' : 'asc';
				} else {
					direction = defaut;
				}
			}
			etatTri = {colonne: colonne, direction: direction};

			var tbody = table.tBodies[0];
			var lignes = Array.from(tbody.rows);
			var valeur = function (ligne) {
				// sur l'échelle étalonnée, un concours non étalonné n'est pas classé : son pourcentage brut
				// pourrait être flatté (ex. petit concours) et fausserait le classement
				if (echelle === 'etal' && ligne.dataset.nonEtalonne === '1') return null;
				var v = parseFloat(ligne.dataset[colonne + (echelle === 'etal' ? 'Etal' : '')]);
				return isNaN(v) ? null : v;
			};
			// tri stable : l'ordre initial du tableau départage les égalités
			var ordreInitial = new Map(lignes.map(function (l, i) { return [l, i]; }));
			lignes.sort(function (a, b) {
				var va = valeur(a), vb = valeur(b);
				if (va === null && vb === null) return ordreInitial.get(a) - ordreInitial.get(b);
				if (va === null) return 1;
				if (vb === null) return -1;
				if (va === vb) return ordreInitial.get(a) - ordreInitial.get(b);
				return direction === 'asc' ? va - vb : vb - va;
			});
			lignes.forEach(function (l) { tbody.appendChild(l); });

			// numérotation du classement : égalité sur la valeur affichée = même rang
			if (table.dataset.rang) {
				var rang = 0, precedente = null;
				lignes.forEach(function (l, i) {
					var v = valeur(l);
					if (v !== null && v !== precedente) { rang = i + 1; precedente = v; }
					l.cells[0].textContent = (v === null) ? '\u2013' : rang;
				});
			}

			majBoutons(table, colonne, direction);
		}

		// Bascule de l'échelle : pourcentage du concours / échelle étalonnée (avec fourchette).
		// Met à jour les valeurs, les jauges et le classement (renuméroté sur l'échelle choisie).
		function appliquerEchelle(etal) {
			var table = document.querySelector('#tableau-selectivite, #tableau-comparaison');
			if (!table) return;
			echelle = etal ? 'etal' : 'concours';
			var suffixe = etal ? 'Etal' : '';
			var lignes = Array.from(table.tBodies[0].rows);
			var format = function (v) { return v.toFixed(1).replace('.', ',') + '%'; };
			var nombre = function (l, cle) { var v = parseFloat(l.dataset[cle]); return isNaN(v) ? null : v; };
			var maxDernier = 0.1;
			lignes.forEach(function (l) {
				var d = nombre(l, 'dernier' + suffixe);
				if (d !== null && d > maxDernier) maxDernier = d;
			});
			lignes.forEach(function (l) {
				var cases = l.querySelectorAll('span.v');
				var fourchettes = l.querySelectorAll('small.fourchette');
				['mediane', 'dernier'].forEach(function (nom, i) {
					var v = nombre(l, nom + suffixe);
					if (cases[i]) cases[i].textContent = (v === null) ? '' : format(v);
					if (!fourchettes[i]) return;
					var bas = nombre(l, nom + 'Etal' + 'Bas'), haut = nombre(l, nom + 'Etal' + 'Haut');
					if (!etal || v === null) { fourchettes[i].textContent = ''; return; }
					fourchettes[i].textContent = (l.dataset.nonEtalonne === '1' || bas === null || haut === null)
						? ' (non étalonné)'
						: ' [' + bas.toFixed(1).replace('.', ',') + '–' + haut.toFixed(1).replace('.', ',') + ']';
				});
				var d = nombre(l, 'dernier' + suffixe);
				var jauge = l.querySelector('.jauge-selectivite');
				if (jauge) jauge.style.width = (d === null ? 0 : Math.max(2, Math.round(60 * d / maxDernier))) + 'px';
			});
			document.getElementById('note-base').hidden = !etal;
			document.getElementById('baseEtal').checked = etal;
			appliquerTri(table, etatTri.colonne, etatTri.direction || table.dataset.direction);
		}

		// boutons : actif en bleu avec le sens du tri
		function majBoutons(table, colonne, direction) {
			table.querySelectorAll('button[data-tri]').forEach(function (b) {
				var actif = (b.dataset.tri === colonne);
				b.textContent = (actif && direction === 'asc') ? '\u2191' : '\u2193';
				b.classList.toggle('btn-primary', actif);
				b.classList.toggle('btn-secondary', !actif);
			});
		}

		// tri par défaut : dernier intégré (déjà appliqué par le serveur) ; restauration du tri
		// choisi au retour depuis une fiche école
		document.addEventListener('DOMContentLoaded', function () {
			// un rechargement peut restaurer la case cochée : on réaligne l'affichage
			if (document.getElementById('baseEtal').checked) appliquerEchelle(true);
			var parametres = new URLSearchParams(window.location.search);
			var table = document.querySelector('#tableau-selectivite, #tableau-comparaison');
			if (!table) return;
			var defaut = table.dataset.direction;
			var colonne = (parametres.get('tri') === 'mediane') ? 'mediane' : 'dernier';
			var direction = parametres.get('direction');
			if (direction !== 'asc' && direction !== 'desc') direction = defaut;
			if (colonne === 'dernier' && direction === defaut) {
				etatTri = {colonne: 'dernier', direction: defaut};
				majBoutons(table, 'dernier', defaut);
			} else {
				appliquerTri(table, colonne, direction);
			}
			if (parametres.get('base') === 'etal') appliquerEchelle(true);
		});
		NavigationRetour.configurer(function () {
			return {tri: etatTri.colonne, direction: etatTri.direction, base: echelle === 'etal' ? 'etal' : ''};
		});

		// pour changer le texte et le symbole du bouton détail
		function changerTexte(bouton) {
			if (bouton.innerText.indexOf("Masquer") == -1) {
				bouton.innerText = "-  Masquer l'explication";
			} else {
				bouton.innerText = "+  Comment lire ce classement ?";
			}
		}
	</script>

	<script>
	<?php
		// fonction d'export des tableaux HTML en CSV
		include "js/tableToCSV.js";
	?>
	</script>

  </body>
</html>
