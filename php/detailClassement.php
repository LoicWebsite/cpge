<?php

require_once __DIR__ . '/detailEntete.php';

// Configuration des classements affichés : l'année de la table peut différer de l'année du palmarès.
function configurationClassements(): array {
	return [
		[
			'cle'      => 'daur2025',
			'table'    => 'DAUR',
			'an'       => '2024',
			'libelle'  => 'DAUR 2025',
			'total'    => 32,
			'groupe'   => true,
			'tooltip'  => "La notation de l&apos;école en 2025 est définie par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 60<br>AA : 59 à 47<br>Le Rang est le résultat du classement par note finale décroissante (de 1 à 32)<br>La note finale est attribuée à partir de 6 critères détaillés sur le site de DAUR Rankings.",
		],
		[
			'cle'      => 'daur2024',
			'table'    => 'DAUR',
			'an'       => '2023',
			'libelle'  => 'DAUR 2024',
			'total'    => 185,
			'groupe'   => true,
			'tooltip'  => "La notation de l&apos;école en 2024 est définie par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 54<br>A : 53 à 47 points<br>BBB : 46 à 41<br>BB : 40 à 37<br>B : 36 à 34<br>CCC : 33 à 31<br>CC : 30 à 28<br>C : 27 à 0<br>Le Rang est le résultat du classement par note finale décroissante (de 1 à 185)<br>La note finale est attribuée à partir de 5 critères détaillés sur le site de DAUR Rankings.",
		],
		[
			'cle'      => 'daur2023',
			'table'    => 'DAUR',
			'an'       => '2022',
			'libelle'  => 'DAUR 2023',
			'total'    => 176,
			'groupe'   => true,
			'tooltip'  => "La notation de l&apos;école en 2023 est définie par le site DAUR Rankings selon la note finale obtenue par l&apos;école dans son classement.<br>AAA : 100 à 70<br>AA : 69 à 56<br>A : 55 à 49 points<br>BBB : 48 à 44<br>BB : 43 à 39<br>B : 39 à 35<br>CCC : 34 à 32<br>CC : 31 à 29<br>C : 29 à 0<br>Le Rang est le résultat du classement par note finale décroissante (de 1 à 176)<br>La note finale est attribuée à partir de 5 critères détaillés sur le site de DAUR Rankings.",
		],
		[
			'cle'      => 'figaro2025',
			'table'    => 'Figaro',
			'an'       => '2025',
			'libelle'  => 'Le Figaro 2025',
			'total'    => 87,
			'groupe'   => false,
			'tooltip'  => "Le Rang est le résultat du classement par note finale décroissante (de 1 à 87).<br>Les écoles sont notées en 2025 de 0 à 20. C&apos;est la moyenne pondérée de trois notes évaluant leur excellence académique (coefficient 2), leur ouverture à l&apos;international (coefficient 1) et l&apos;emploi, ou réussite professionnelle des diplômés (coefficient 3).",
		],
		[
			'cle'      => 'figaro2024',
			'table'    => 'Figaro',
			'an'       => '2023',
			'libelle'  => 'Le Figaro 2024',
			'total'    => 87,
			'groupe'   => false,
			'tooltip'  => "Le Rang est le résultat du classement par note finale décroissante (de 1 à 87).<br>Les écoles sont notées en 2024 de 0 à 20. Cette note résulte de l&apos;évaluation de 14 critères.",
		],
		[
			'cle'      => 'etudiant2023',
			'table'    => 'Letudiant',
			'an'       => '2023',
			'libelle'  => "L'Etudiant 2023",
			'total'    => 169,
			'groupe'   => true,
			'tooltip'  => "Le nombre de Points attribués est au maximum de 111 en 2023. Il résulte de l&apos;évaluation de 11 critères.<br>Le Rang est le résultat du classement par points (de 1 à 169).<br>Le Groupe d&apos;appartenance de l&apos;école en 2023 est désormais défini par le magasine L&apos;Etudiant comme étant un simple quartile.<br>A+ : 97 à 63 points<br>A : 62 à 51 points<br>B : 50 à 44 points<br>C : 0 à 43 points",
		],
		[
			'cle'      => 'etudiant2022',
			'table'    => 'Letudiant',
			'an'       => '2022',
			'libelle'  => "L'Etudiant 2022",
			'total'    => 172,
			'groupe'   => true,
			'tooltip'  => "Le classement du magasine L&apos;Etudiant en 2022 est réalisé en comptabilisant le nombre de points sur une cinquantaine de critères. 172 écoles y sont notées.<br>Le Groupe d&apos;appartenance de l&apos;école en 2022 est défini par le magasine L&apos;Etudiant selon la note obtenue par l&apos;école dans leur classement.<br>A+ : 42 à 58 points<br>A : 34 à 41 points<br>B : 24 à 33 points<br>C : 0 à 23 points",
		],
	];
}

// Lit les rangs / groupes de l'école dans chaque classement, ainsi que l'URL du site de l'école.
function chargerClassements(PDO $db, string $ecole, bool $debug = false): array {
	$resultats = ['url' => '', 'lignes' => []];

	foreach (configurationClassements() as $source) {
		// Les noms de table et de colonne proviennent uniquement de la configuration interne.
		$colonnes = "Rang" . ($source['groupe'] ? ", Groupe" : "");
		$sql = "SELECT " . $colonnes . " FROM " . $source['table']
			 . " WHERE (Ecole = :ecoleCanonique OR Ecole IN (SELECT DISTINCT Ecole FROM EcoleConcours WHERE EcoleConcours.EcoleConcours = :ecoleDirecte))"
			 . " AND An = :an ORDER BY Rang ASC LIMIT 1";
		if ($debug) {
			echo "SQL classement " . escapeHtml($source['cle']) . " = " . escapeHtml($sql) . "<br/>";
		}
		try {
			$stmt = $db->prepare($sql);
			$stmt->execute([':ecoleCanonique' => $ecole, ':ecoleDirecte' => $ecole, ':an' => $source['an']]);
			$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
			$stmt->closeCursor();
			$resultats['lignes'][$source['cle']] = [
				'rang'   => ($ligne && isset($ligne['Rang'])) ? $ligne['Rang'] : '',
				'groupe' => ($ligne && isset($ligne['Groupe'])) ? $ligne['Groupe'] : '',
			];
		}
		catch (PDOException $erreur) {
			echo "<div class='alert alert-warning'>Erreur SELECT " . escapeHtml($source['libelle']) . " : " . escapeHtml($erreur->getMessage()) . "</div>";
			$resultats['lignes'][$source['cle']] = ['rang' => '', 'groupe' => ''];
		}
	}

	$resultats['url'] = obtenirUrlEcole($db, $ecole);

	return $resultats;
}

// URL officielle de l'école : seule donnée du bandeau nécessaire dès le premier
// affichage, isolée des autres requêtes de classement (plus lentes, chargées à la demande).
function obtenirUrlEcole(PDO $db, string $ecole): string {
	try {
		$stmt = $db->prepare("SELECT e.UrlEcole FROM Ecole e"
			. " LEFT JOIN EcoleConcours ec ON ec.Ecole = e.Ecole"
			. " WHERE (e.Ecole = :ecoleCanonique OR ec.EcoleConcours = :ecoleDirecte) AND e.UrlEcole <> '' LIMIT 1");
		$stmt->execute([':ecoleCanonique' => $ecole, ':ecoleDirecte' => $ecole]);
		$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return ($ligne && !empty($ligne['UrlEcole'])) ? $ligne['UrlEcole'] : '';
	}
	catch (PDOException $erreur) {
		echo "<div class='alert alert-warning'>Erreur SELECT UrlEcole : " . escapeHtml($erreur->getMessage()) . "</div>";
		return '';
	}
}

// Affiche le contenu de l'onglet "Classements".
function afficherClassements(array $classements, string $ecole = '', array $attractivite = []): void {
	$lignes = $classements['lignes'];

	afficherTitreSection($ecole, 'Classements');

	$aUneValeur = false;
	foreach ($lignes as $ligne) {
		if ($ligne['rang'] !== '' || $ligne['groupe'] !== '') { $aUneValeur = true; break; }
	}

	if (!$aUneValeur) {
		echo "<div class='alert alert-info'><i class='bi bi-info-circle'></i>&nbsp; Cette école n'apparaît dans aucun des classements suivis.</div>";
		return;
	}

	echo "<br><p class='text-muted small'>Position de l'école dans les principaux classements d'écoles d'ingénieurs. "
	   . "Passez sur <i class='bi bi-info-circle-fill'></i> pour connaître la méthode de chaque palmarès.</p>";
	if (!empty($attractivite) && $attractivite['rang'] !== '') {
		echo "<div class='p-3 border bg-white rounded text-center mb-3'>";
		echo "<div class='text-secondary small'><i class='bi bi-clipboard2-heart'></i>&nbsp; Attractivité DAUR</div>";
		echo "<div class='h4 mb-1 mt-1 text-primary'>" . escapeHtml($attractivite['rang'] . '/' . $attractivite['total']) . "</div>";
		// echo "<div class='text-muted small'>Meilleur rang de l'école parmi les formations évaluées.</div>";
		echo "</div>";
	}

	echo "<div class='table-responsive'>";
	echo "<table class='table-classements'>";
	echo "<thead><tr><th scope='col'>&nbsp;Classement&nbsp;</th><th scope='col'>&nbsp;Notation&nbsp;</th><th scope='col'>&nbsp;Rang&nbsp;</th></tr></thead>";
	echo "<tbody>";
	foreach (configurationClassements() as $source) {
		$ligne = $lignes[$source['cle']];
		if ($ligne['rang'] === '' && $ligne['groupe'] === '') {
			continue;
		}
		echo "<tr>";
		echo "<td style='padding-left:10px'><strong>" . escapeHtml($source['libelle']) . "</strong>&nbsp;&nbsp;"
		   . "<i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='" . $source['tooltip'] . "'></i></td>";
		echo "<td style='text-align:center'>";
		if ($ligne['groupe'] !== '') {
			echo "<span class='badge bg-secondary'>" . escapeHtml($ligne['groupe']) . "</span>";
		} else {
			echo "&mdash;";
		}
		echo "</td>";
		echo "<td style='text-align:center'>";
		if ($ligne['rang'] !== '') {
			echo "<strong>" . escapeHtml($ligne['rang']) . "</strong> <span class='text-muted'>/ " . (int)$source['total'] . "</span>";
		} else {
			echo "&mdash;";
		}
		echo "</td>";
		echo "</tr>";
	}
	echo "</tbody></table>";
	echo "</div>";

	echo "<br><p class='text-muted small mb-0'>Les classements portent sur l'école dans son ensemble, toutes voies d'admission confondues.</p>";
}
