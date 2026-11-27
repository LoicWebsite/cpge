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
	$anneeLibelle = ($an === "toutes") ? "toutes les années (2016-2026)" : $an;

	// en dessous de ce nombre d'intégrés, la médiane n'est pas représentative
	// (5 en Allstar : beaucoup de couples école + filière n'ont que quelques intégrés)
	$integresMinimum = $modeAllstar ? 5 : 3;

	// URL canonique : on garde uniquement les paramètres qui modifient le contenu
	$criteresPage = $modeEcole ? ['ecole' => $ecoleChoisie, 'an' => $an] : ['filiere' => $modeAllstar ? 'allstar' : $filiere, 'an' => $an];
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
			+  Voir l'explication
		  </button>
		</p>

		<div class="collapse" id="detail" style="margin-left:20px;">
			<p>Ce classement ordonne les écoles selon la <strong>sélectivité</strong> observée aux concours SCEI pour la filière de CPGE choisie.</p>
			<ul>
				<li><strong>Sélectivité médiane</strong> : rang du candidat médian parmi les intégrés, divisé par le nombre d'inscrits au concours. Par exemple 12 % signifie que la moitié des intégrés avaient un rang meilleur que 12 % des inscrits. <strong>Plus le pourcentage est faible, plus l'école est sélective.</strong> Ce critère sert au classement.</li>
				<li><strong>Dernier intégré</strong> : rang du dernier intégré divisé par le nombre d'inscrits, lorsque le concours le publie. Il n'est affiché que s'il est publié pour au moins 90 % des intégrés de l'école (sinon la valeur ne représenterait qu'une partie des concours de l'école) et s'il est cohérent avec le rang médian (un dernier intégré meilleur que le médian est une erreur de saisie ou de source). C'est un indicateur complémentaire, plus sensible aux désistements et aux appels de liste d'attente.</li>
				<li><strong>Classement</strong> : le tableau est classé par défaut sur le <strong>dernier intégré</strong>, qui indique jusqu'où l'on peut descendre pour entrer. Ce rang n'est pas publié par tous les concours (surtout pour les années récentes) : les écoles sans dernier intégré sont alors listées en fin de tableau, sans rang, classées par sélectivité médiane. Le bouton &darr; de la colonne « Sélectivité médiane » reclasse sur ce critère, disponible pour presque toutes les écoles. Un second clic inverse l'ordre. Le choix « Toutes les années » réduit le nombre d'écoles sans dernier intégré.</li>
				<li><strong>Allstar</strong> : le choix « Allstar » dans la liste des filières classe ensemble toutes les écoles de toutes les filières. Chaque ligne est un couple école + filière, par exemple « Ecole Polytechnique (MP) ». Les concours communs à plusieurs filières (Groupe INSA, CESI, Ingeni'Up) donnent les mêmes chiffres dans chaque filière : ils ne sont pas repris dans l'Allstar, mais restent visibles filière par filière. Pour limiter le bruit, seuls les couples avec au moins 5 intégrés sur la période sont classés (3 dans les autres vues). Le pourcentage étant rapporté aux inscrits de chaque filière, ce classement reste un repère (voir la réserve sur la comparaison entre filières ci-dessous).</li>
				<li><strong>Comparer à l'échelle de la promotion</strong> : la case à cocher au-dessus du tableau rapporte tous les rangs à la promotion complète de la filière (environ 7 000 candidats en MP, 5 000 en PC, 5 400 en PSI) au lieu des inscrits du concours. Elle corrige un biais : les concours ciblés comme Polytechnique n'ont que quelques centaines d'inscrits, déjà très forts, et paraissent donc moins sélectifs qu'ils ne sont. À utiliser pour comparer des écoles qui recrutent par des concours différents.</li>
				<li>Un concours n'a pas le même nombre d'inscrits qu'un autre : on compare donc des rapports et non des rangs bruts.</li>
				<li>Quand une école recrute par plusieurs concours ou plusieurs formations, les valeurs sont moyennées en pondérant par le nombre d'intégrés.</li>
				<li>Les écoles avec moins de <?php echo $integresMinimum; ?> intégrés sur la période choisie ne sont pas classées (médiane peu représentative). Les écoles dont le concours ne publie pas de rang médian ne figurent pas non plus dans ce classement.</li>
				<li>Les concours <strong>Inter-Filière</strong> (par exemple Polytech) classent ensemble les candidats de toutes les filières : leur rang n'est pas comparable au nombre d'inscrits d'une seule filière. Ils sont exclus, tout comme les lignes dont le rang médian dépasse le nombre d'inscrits et les concours réservés aux candidats étrangers (Polytechnique Etr., filières « Etranger »), dont le classement est séparé. Certaines écoles peuvent donc être absentes de la liste d'une filière.</li>
				<li><strong>Petits concours</strong> : quand un concours ne classe qu'une faible part de ses inscrits (par exemple Avenir Prépas ou les concours d'architecture), le rapport rang / inscrits est très faible alors que le niveau d'entrée ne l'est pas forcément. Les écoles qui recrutent surtout par ces concours peuvent apparaître trop sélectives : à interpréter avec prudence.</li>
				<li><strong>ENS</strong> (*) : les classés et les entrants ne portent que sur les candidats fonctionnarisables alors que les inscrits portent sur tous les candidats ; la sélectivité y est à interpréter avec prudence.</li>
			</ul>
			<p><strong>Comparer les filières pour une école</strong> : choisissez une école dans la liste pour voir, filière par filière, la sélectivité à l'entrée. Le pourcentage est rapporté aux inscrits de chaque filière : la filière où il est le plus élevé est celle où l'école est la plus accessible, à rang relatif égal. Les effectifs (candidats, niveau moyen) diffèrent d'une filière à l'autre : le résultat est un repère, pas une garantie. Regardez aussi le nombre d'intégrés : une école qui prend peu de candidats d'une filière y est plus difficile d'accès que le pourcentage ne le laisse croire.</p>
			<p>La sélectivité mesure le niveau d'entrée, pas la qualité de la formation. Elle est à croiser avec les autres palmarès du site.</p>
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
		<!-- bascule de la base de calcul : inscrits du concours / promotion complète de la filière -->
		<div class="form-check mb-3">
			<input class="form-check-input" type="checkbox" id="basePromo" onchange="appliquerBase(this.checked)">
			<label class="form-check-label" for="basePromo">
				Comparer à l'échelle de la promotion
				<i class="bi bi-info-circle-fill" data-bs-toggle="tooltip" data-bs-html="true" title="Par défaut, les pourcentages sont rapportés aux inscrits du concours qui recrute. Un concours très ciblé (par exemple Polytechnique) a peu d'inscrits, tous très forts : un bon rang y paraît moins sélectif qu'il ne l'est. Cochez pour tout rapporter à la promotion complète de la filière."></i>
			</label>
		</div>
	<?php
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . escapeHtml($erreur->getMessage()));
		}

		// Base "promotion complète" : pour chaque filière et chaque année, le plus grand nombre
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
		$jointurePromo = "
				LEFT JOIN " . $sousRequetePromo . " p
					ON p.An = n.An AND p.Filiere = n.Filiere";

		// Effectifs de référence par filière (ceux utilisés par la base "promotion"), pour la note
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
		echo "Pourcentages rapportés à la <strong>promotion complète de la filière</strong> (plus grand nombre d'inscrits d'un concours de la filière la même année) et non aux inscrits du concours. Approximation : un rang dans un concours ciblé est celui d'un groupe resserré, pas un rang national exact. Cette base <strong>avantage aussi les petits concours</strong> qui ne classent que quelques candidats (Avenir Prépas, architecture, certaines écoles spécialisées) : ils peuvent apparaître très sélectifs alors que leur classement n'est pas national. Elle est surtout fiable pour comparer les grandes écoles qui recrutent sur de gros concours ou des banques très ciblées (X, ENS, Mines-Ponts, Centrale).";
		if (!empty($detailEffectifs)) {
			echo "<br><strong>Effectifs de référence (" . ($an === "toutes" ? "moyenne annuelle 2016-2026" : "concours " . escapeHtml($an)) . ") :</strong> environ "
				. str_replace(' ', '&nbsp;', formater(round($totalPromo), 0)) . "&nbsp;étudiants en tout, dont " . implode(" &middot; ", $detailEffectifs) . ".";
		}
		echo "</div>";

		$colonnesPromo = ",
					100 * SUM(n.Integre * n.RangMedian / p.Promo) / SUM(n.Integre) AS SelectivitePromoMediane,
					CASE WHEN SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END) >= 0.9 * SUM(n.Integre)
							THEN 100 * SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre * n.Dernier / p.Promo END)
								/ SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END)
						END AS SelectivitePromoDernier";
		$jointureAllstar = "";

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
		$sql = "SELECT ec.Ecole, n.Filiere,
					SUM(n.Integre) AS Integres,
					SUM(n.Integre * n.RangMedian) / SUM(n.Integre) AS RangMedianMoyen,
					100 * SUM(n.Integre * n.RangMedian / n.Inscrit) / SUM(n.Integre) AS SelectiviteMediane,
					CASE WHEN SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END) >= 0.9 * SUM(n.Integre)
							THEN 100 * SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre * n.Dernier / n.Inscrit END)
								/ SUM(CASE WHEN n.Dernier > 0 AND n.Dernier >= n.RangMedian THEN n.Integre END)
						END AS SelectiviteDernier" . $colonnesPromo . "
				FROM Note n
				INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
					ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
				" . $jointurePromo . $jointureAllstar . "
				WHERE 1 = 1" . $filtreFiliere . $filtreAn . "
					AND ec.Ecole IS NOT NULL
					AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0
					AND n.RangMedian <= n.Inscrit
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
			echo "<div class='alert alert-info'>Aucune école n'a au moins " . $integresMinimum . " intégrés avec un rang médian publié en " . $libelleFiliere . " (" . escapeHtml($anneeLibelle) . ").</div>";
		} else {
			$maxSelectivite = max(array_filter(array_column($lignes, 'SelectiviteDernier'), fn($v) => $v !== null) ?: [0.1]);
			$idTableau = '"#tableau-selectivite","rang;école;sélectivité médiane (%);rang médian moyen;dernier intégré (%);intégrés"';

			echo "<h2 class='h5'>" . count($lignes) . ($modeAllstar ? " couples école (filière) &ndash; toutes filières confondues &ndash; concours " : " écoles en " . $libelleFiliere . " &ndash; concours ") . escapeHtml($anneeLibelle) . "</h2>";
			echo "<table id='tableau-selectivite' data-direction='asc' data-rang='1'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(" . $idTableau . ")'><i class='bi bi-download'></i> csv</button></caption>";
			echo "<thead class='text-center'><tr>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Classement par dernier intégré croissant (par défaut). Les écoles sans dernier intégré publié sont listées en fin de tableau, sans rang, classées par sélectivité médiane. Les écoles dont la valeur affichée est identique partagent le même rang.'></i></th>";
			echo "<th>&nbsp;Ecole&nbsp;</th>";
			echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='mediane' title='Trier par sélectivité médiane' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Sélectivité médiane&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang médian des intégrés divisé par le nombre d&apos;inscrits. Plus la valeur est faible, plus l&apos;école est sélective.'></i></th>";
			echo "<th>&nbsp;Rang médian&nbsp;<br>moyen&nbsp;<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang médian brut des intégrés, moyenné par école (pondéré par les intégrés). Il dépend du nombre d&apos;inscrits du concours : à ne comparer qu&apos;avec prudence.'></i></th>";
			echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='dernier' title='Trier par dernier intégré' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Dernier intégré&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang du dernier intégré divisé par le nombre d&apos;inscrits, quand le concours le publie. Vide sinon.'></i></th>";
			echo "<th>&nbsp;Intégrés&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Nombre d&apos;intégrés pris en compte sur la période.'></i></th>";
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
					. "' data-mediane-promo='" . ($ligne['SelectivitePromoMediane'] !== null ? number_format((float) $ligne['SelectivitePromoMediane'], 1, '.', '') : '')
					. "' data-dernier-promo='" . ($ligne['SelectivitePromoDernier'] !== null ? number_format((float) $ligne['SelectivitePromoDernier'], 1, '.', '') : '') . "'>";
				echo "<td style='text-align:center'>" . ($dernier === null ? "&ndash;" : $rang) . "</td>";
				echo "<td style='padding-left:10px'><a href='" . $urlDetail . "'><strong>" . escapeHtml($libelleLigne) . "</strong></a>" . ($ens ? " *" : "")
					. " <a href='?ecole=" . rawurlencode($nomEcole) . "&amp;an=" . rawurlencode($an) . "' title='Comparer les filières pour cette école' aria-label='Comparer les filières pour " . escapeHtml($nomEcole) . "'><i class='bi bi-bar-chart-steps'></i></a></td>";
				echo "<td style='text-align:right; white-space:nowrap'>" . "<span class='v'>" . formater($mediane, 1) . "%</span>&nbsp;</td>";
				echo "<td style='text-align:right'>" . formater($ligne['RangMedianMoyen'], 0) . "&nbsp;</td>";
				echo "<td style='text-align:right'>" . ($ligne['SelectiviteDernier'] !== null ? "<span class='v'>" . formater($ligne['SelectiviteDernier'], 1) . "%</span>&nbsp;<span class='jauge-selectivite' style='width:" . $largeurJauge . "px' aria-hidden='true'></span>" : "") . "</td>";
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
						END AS SelectiviteDernier" . $colonnesPromo . "
					FROM Note n
					INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
						ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
					" . $jointurePromo . "
					WHERE ec.Ecole = :ecole" . $filtreAn . "
						AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0
						AND n.RangMedian <= n.Inscrit
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
				echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='mediane' title='Trier par sélectivité médiane' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Sélectivité médiane&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang médian des intégrés divisé par le nombre d&apos;inscrits de la filière. Plus la valeur est élevée, plus l&apos;école est accessible dans cette filière.'></i></th>";
				echo "<th>&nbsp;Rang médian&nbsp;<br>moyen&nbsp;</th>";
				echo "<th>&nbsp;<button type='button' class='btn btn-secondary btn-sm' data-tri='dernier' title='Trier par dernier intégré' onclick='trierSelectivite(this)'>&darr;</button>&nbsp;&nbsp;Dernier intégré&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Rang du dernier intégré divisé par le nombre d&apos;inscrits, quand le concours le publie. Vide sinon.'></i></th>";
				echo "<th>&nbsp;Places&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Places offertes sur la période choisie.'></i></th>";
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
					. "' data-mediane-promo='" . ($ligne['SelectivitePromoMediane'] !== null ? number_format((float) $ligne['SelectivitePromoMediane'], 1, '.', '') : '')
					. "' data-dernier-promo='" . ($ligne['SelectivitePromoDernier'] !== null ? number_format((float) $ligne['SelectivitePromoDernier'], 1, '.', '') : '') . "'>";
					echo "<td style='padding-left:10px'><a href='" . $urlDetail . "'><strong>" . escapeHtml(strtoupper($ligne['Filiere'])) . "</strong></a>";
					if ($i === $accessible && $ecartNet) {
						echo " <span class='badge bg-success' title='Selon le dernier intégré'>la plus accessible</span>";
					}
					if ($peuRepresentatif) {
						echo " <small><i class='bi bi-exclamation-triangle' data-bs-toggle='tooltip' title='Moins de " . $integresMinimum . " intégrés : valeur peu représentative, exclue de la comparaison.'></i></small>";
					}
					echo "</td>";
					echo "<td style='text-align:right; white-space:nowrap'>" . "<span class='v'>" . formater($mediane, 1) . "%</span>&nbsp;</td>";
					echo "<td style='text-align:right'>" . formater($ligne['RangMedianMoyen'], 0) . "&nbsp;</td>";
					echo "<td style='text-align:right'>" . ($ligne['SelectiviteDernier'] !== null ? "<span class='v'>" . formater($ligne['SelectiviteDernier'], 1) . "%</span>&nbsp;<span class='jauge-selectivite' style='width:" . $largeurJauge . "px' aria-hidden='true'></span>" : "") . "</td>";
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
		var etatBase = 'concours'; // 'concours' (inscrits du concours) ou 'promo' (promotion complète)

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
			var valeur = function (ligne) { var v = parseFloat(ligne.dataset[colonne + (etatBase === 'promo' ? 'Promo' : '')]); return isNaN(v) ? null : v; };
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

		// Bascule de la base de calcul : met à jour les pourcentages affichés, les jauges et
		// le classement (renuméroté sur la base choisie).
		function appliquerBase(promo) {
			var table = document.querySelector('#tableau-selectivite, #tableau-comparaison');
			if (!table) return;
			etatBase = promo ? 'promo' : 'concours';
			var suffixe = promo ? 'Promo' : '';
			var lignes = Array.from(table.tBodies[0].rows);
			var format = function (v) { return v.toFixed(1).replace('.', ',') + '%'; };
			var maxDernier = 0.1;
			lignes.forEach(function (l) {
				var d = parseFloat(l.dataset['dernier' + suffixe]);
				if (!isNaN(d) && d > maxDernier) maxDernier = d;
			});
			lignes.forEach(function (l) {
				var m = parseFloat(l.dataset['mediane' + suffixe]);
				var d = parseFloat(l.dataset['dernier' + suffixe]);
				var cases = l.querySelectorAll('span.v');
				if (cases[0]) cases[0].textContent = isNaN(m) ? '' : format(m);
				if (cases[1]) cases[1].textContent = isNaN(d) ? '' : format(d);
				var jauge = l.querySelector('.jauge-selectivite');
				if (jauge) jauge.style.width = (isNaN(d) ? 0 : Math.max(2, Math.round(60 * d / maxDernier))) + 'px';
			});
			document.getElementById('note-base').hidden = !promo;
			document.getElementById('basePromo').checked = promo;
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
			if (document.getElementById('basePromo').checked) appliquerBase(true);
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
			if (parametres.get('base') === 'promo') appliquerBase(true);
		});
		NavigationRetour.configurer(function () {
			return {tri: etatTri.colonne, direction: etatTri.direction, base: etatBase === 'promo' ? 'promo' : ''};
		});

		// pour changer le texte et le symbole du bouton détail
		function changerTexte(bouton) {
			if (bouton.innerText.indexOf("Voir") == -1) {
				bouton.innerText = "+  Voir l'explication";
			} else {
				bouton.innerText = "-  Masquer l'explication";
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
