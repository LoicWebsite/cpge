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
	$anneeLibelle = ($an === "toutes") ? "toutes les années (2016-2026)" : $an;

	// en dessous de ce nombre d'intégrés, la médiane n'est pas représentative
	$integresMinimum = 3;

	// URL canonique : on garde uniquement les paramètres qui modifient le contenu
	$criteresPage = $modeEcole ? ['ecole' => $ecoleChoisie, 'an' => $an] : ['filiere' => $filiere, 'an' => $an];
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
				<li><strong>Dernier intégré</strong> : rang du dernier intégré divisé par le nombre d'inscrits, lorsque le concours le publie. C'est un indicateur complémentaire, plus sensible aux désistements et aux appels de liste d'attente.</li>
				<li><strong>Changer le classement</strong> : le tableau est classé par défaut sur la sélectivité médiane, disponible pour presque toutes les écoles. Le bouton &darr; de la colonne « Dernier intégré » reclasse sur ce critère, qui indique jusqu'où l'on peut descendre pour entrer. Ce rang n'est pas publié par tous les concours (surtout pour les années récentes) : les écoles sans dernier intégré restent alors en fin de tableau, sans rang. Un second clic inverse l'ordre.</li>
				<li>Un concours n'a pas le même nombre d'inscrits qu'un autre : on compare donc des rapports et non des rangs bruts.</li>
				<li>Quand une école recrute par plusieurs concours ou plusieurs formations, les valeurs sont moyennées en pondérant par le nombre d'intégrés.</li>
				<li>Les écoles avec moins de <?php echo $integresMinimum; ?> intégrés sur la période choisie ne sont pas classées (médiane peu représentative). Les écoles dont le concours ne publie pas de rang médian ne figurent pas non plus dans ce classement.</li>
				<li>Les concours <strong>Inter-Filière</strong> (par exemple Polytech) classent ensemble les candidats de toutes les filières : leur rang n'est pas comparable au nombre d'inscrits d'une seule filière. Ils sont exclus, tout comme les lignes dont le rang médian dépasse le nombre d'inscrits. Certaines écoles peuvent donc être absentes de la liste d'une filière.</li>
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
					foreach ($filieresAffichees as $code => $libelle) {
						echo "<option value='" . $code . "'" . ($code === $filiere ? " selected" : "") . ">" . $libelle . "</option>";
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
	<?php
		try {
			$db = openDatabase();
		}
		catch(PDOException $erreur)	{
			die('Erreur connexion base : ' . escapeHtml($erreur->getMessage()));
		}

		if (!$modeEcole) {
		// Agrégation par école. Le DISTINCT évite de compter deux fois une ligne de
		// Note si le rattachement EcoleConcours existait en double.
		$params = [':filiere' => $filiere, ':minimum' => $integresMinimum];
		$filtreAn = "";
		if ($an !== "toutes") {
			$filtreAn = " AND n.An = :an";
			$params[':an'] = $an;
		}
		$sql = "SELECT ec.Ecole,
					SUM(n.Integre) AS Integres,
					SUM(n.Integre * n.RangMedian) / SUM(n.Integre) AS RangMedianMoyen,
					100 * SUM(n.Integre * n.RangMedian / n.Inscrit) / SUM(n.Integre) AS SelectiviteMediane,
					100 * SUM(CASE WHEN n.Dernier > 0 THEN n.Integre * n.Dernier / n.Inscrit END)
						/ SUM(CASE WHEN n.Dernier > 0 THEN n.Integre END) AS SelectiviteDernier
				FROM Note n
				INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
					ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
				WHERE n.Filiere = :filiere" . $filtreAn . "
					AND ec.Ecole IS NOT NULL
					AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0
					AND n.RangMedian <= n.Inscrit
					AND n.Concours NOT LIKE '%Inter-Fili_re%'
				GROUP BY ec.Ecole
				HAVING SUM(n.Integre) >= :minimum
				ORDER BY SelectiviteMediane ASC, ec.Ecole ASC";
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
			echo "<div class='alert alert-info'>Aucune école n'a au moins " . $integresMinimum . " intégrés avec un rang médian publié en " . strtoupper($filiere) . " (" . escapeHtml($anneeLibelle) . ").</div>";
		} else {
			$maxSelectivite = max(array_column($lignes, 'SelectiviteMediane'));
			$idTableau = '"#tableau-selectivite","rang;école;sélectivité médiane (%);rang médian moyen;dernier intégré (%);intégrés"';

			echo "<h2 class='h5'>" . count($lignes) . " écoles en " . strtoupper($filiere) . " &ndash; concours " . escapeHtml($anneeLibelle) . "</h2>";
			echo "<table id='tableau-selectivite' data-direction='asc' data-rang='1'>";
			echo "<caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une école pour voir ses statistiques d'admissions.";
			echo "<br>Cliquer sur le bouton pour télécharger le classement au format CSV : </small><button type='button' class='btn btn-secondary btn-sm' onclick='tableToCSV(" . $idTableau . ")'><i class='bi bi-download'></i> csv</button></caption>";
			echo "<thead class='text-center'><tr>";
			echo "<th>&nbsp;Rang&nbsp;<br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Classement par sélectivité médiane croissante. Les écoles dont la sélectivité médiane affichée est identique partagent le même rang.'></i></th>";
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
				// égalité sur la valeur affichée : même rang
				if ($mediane !== $valeurPrecedente) {
					$rang = $position;
					$valeurPrecedente = $mediane;
				}
				$nomEcole = $ligne['Ecole'];
				$ens = (bool) preg_match('/^(ENS\b|[ÉE]cole normale)/iu', $nomEcole);
				$largeurJauge = max(2, (int) round(60 * $mediane / max($maxSelectivite, 0.1)));
				$urlDetail = 'detail-resultat-admission-par-ecole.php?origine=selectivite&amp;retour=' . rawurlencode($retourListe)
					. '&amp;ecole=' . rawurlencode($nomEcole) . '&amp;filiere=' . rawurlencode($filiere);

				echo "<tr ondblclick='zoom(" . encodeJs($nomEcole) . ")'" . " data-mediane='" . number_format($mediane, 1, '.', '') . "' data-dernier='" . ($ligne['SelectiviteDernier'] !== null ? number_format((float) $ligne['SelectiviteDernier'], 1, '.', '') : '') . "'>";
				echo "<td style='text-align:center'>" . $rang . "</td>";
				echo "<td style='padding-left:10px'><a href='" . $urlDetail . "'><strong>" . escapeHtml($nomEcole) . "</strong></a>" . ($ens ? " *" : "")
					. " <a href='?ecole=" . rawurlencode($nomEcole) . "&amp;an=" . rawurlencode($an) . "' title='Comparer les filières pour cette école' aria-label='Comparer les filières pour " . escapeHtml($nomEcole) . "'><i class='bi bi-bar-chart-steps'></i></a></td>";
				echo "<td style='text-align:right; white-space:nowrap'>" . formater($mediane, 1) . "%&nbsp;<span class='jauge-selectivite' style='width:" . $largeurJauge . "px' aria-hidden='true'></span></td>";
				echo "<td style='text-align:right'>" . formater($ligne['RangMedianMoyen'], 0) . "&nbsp;</td>";
				echo "<td style='text-align:right'>" . ($ligne['SelectiviteDernier'] !== null ? formater($ligne['SelectiviteDernier'], 1) . "%&nbsp;" : "") . "</td>";
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
						100 * SUM(CASE WHEN n.Dernier > 0 THEN n.Integre * n.Dernier / n.Inscrit END)
							/ SUM(CASE WHEN n.Dernier > 0 THEN n.Integre END) AS SelectiviteDernier
					FROM Note n
					INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
						ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
					WHERE ec.Ecole = :ecole" . $filtreAn . "
						AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0
						AND n.RangMedian <= n.Inscrit
						AND n.Concours NOT LIKE '%Inter-Fili_re%'
					GROUP BY n.Filiere
					ORDER BY SelectiviteMediane DESC";
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
						if ($accessible === null) $accessible = $i;
						$valeursRepresentees[] = (float) $ligne['SelectiviteMediane'];
					}
				}
				$ecartNet = (count($valeursRepresentees) >= 2) && ($valeursRepresentees[0] >= 1.2 * $valeursRepresentees[1]);
				$maxSelectivite = max(array_column($lignes, 'SelectiviteMediane'));
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
					$peuRepresentatif = ((int) $ligne['Integres'] < $integresMinimum);
					$largeurJauge = max(2, (int) round(60 * $mediane / max($maxSelectivite, 0.1)));
					$styleLigne = $peuRepresentatif ? " style='color:#888'" : "";
					$urlDetail = 'detail-resultat-admission-par-ecole.php?origine=selectivite&amp;retour=' . rawurlencode($retourListe)
						. '&amp;ecole=' . rawurlencode($ecoleChoisie) . '&amp;filiere=' . rawurlencode($codeFiliere);

					echo "<tr ondblclick='zoomFiliere(" . encodeJs($codeFiliere) . ")'" . $styleLigne . " data-mediane='" . number_format($mediane, 1, '.', '') . "' data-dernier='" . ($ligne['SelectiviteDernier'] !== null ? number_format((float) $ligne['SelectiviteDernier'], 1, '.', '') : '') . "'>";
					echo "<td style='padding-left:10px'><a href='" . $urlDetail . "'><strong>" . escapeHtml(strtoupper($ligne['Filiere'])) . "</strong></a>";
					if ($i === $accessible && $ecartNet) {
						echo " <span class='badge bg-success' title='Selon la sélectivité médiane'>la plus accessible</span>";
					}
					if ($peuRepresentatif) {
						echo " <small><i class='bi bi-exclamation-triangle' data-bs-toggle='tooltip' title='Moins de " . $integresMinimum . " intégrés : valeur peu représentative, exclue de la comparaison.'></i></small>";
					}
					echo "</td>";
					echo "<td style='text-align:right; white-space:nowrap'>" . formater($mediane, 1) . "%&nbsp;<span class='jauge-selectivite' style='width:" . $largeurJauge . "px' aria-hidden='true'></span></td>";
					echo "<td style='text-align:right'>" . formater($ligne['RangMedianMoyen'], 0) . "&nbsp;</td>";
					echo "<td style='text-align:right'>" . ($ligne['SelectiviteDernier'] !== null ? formater($ligne['SelectiviteDernier'], 1) . "%&nbsp;" : "") . "</td>";
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
		function zoom(ecole) {
			<?php
				$baseZoom = 'detail-resultat-admission-par-ecole.php?origine=selectivite&filiere=' . rawurlencode($filiere) . '&ecole=';
				echo 'window.location.href = NavigationRetour.urlDestination(' . encodeJs($baseZoom) . ' + encodeURIComponent(ecole));';
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
		var etatTri = {colonne: 'mediane', direction: null};

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
			var valeur = function (ligne) { var v = parseFloat(ligne.dataset[colonne]); return isNaN(v) ? null : v; };
			// tri stable : l'ordre initial (sélectivité médiane puis nom) départage les égalités
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

			// boutons : actif en bleu avec le sens du tri
			table.querySelectorAll('button[data-tri]').forEach(function (b) {
				var actif = (b.dataset.tri === colonne);
				b.textContent = (actif && direction === 'asc') ? '\u2191' : '\u2193';
				b.classList.toggle('btn-primary', actif);
				b.classList.toggle('btn-secondary', !actif);
			});
		}

		// restauration du tri au retour depuis une fiche école
		document.addEventListener('DOMContentLoaded', function () {
			var parametres = new URLSearchParams(window.location.search);
			var table = document.querySelector('#tableau-selectivite, #tableau-comparaison');
			if (!table || parametres.get('tri') !== 'dernier') return;
			var direction = parametres.get('direction');
			appliquerTri(table, 'dernier', (direction === 'asc' || direction === 'desc') ? direction : null);
		});
		NavigationRetour.configurer(function () {
			return etatTri.colonne === 'dernier' ? {tri: 'dernier', direction: etatTri.direction} : {};
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
