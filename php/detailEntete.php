<?php

require_once __DIR__ . '/detailClassement.php';

// Libellés longs des filières CPGE.
function libellesFilieres(): array {
	return [
		'mp'    => 'Mathématiques - Physique',
		'mpi'   => 'Mathématiques, Physique, Informatique',
		'pc'    => 'Physique - Chimie',
		'psi'   => 'Physique et Sciences de l\'Ingénieur',
		'pt'    => 'Physique - Technologie',
		'tsi'   => 'Technologie et Sciences Industrielles',
		'tpc'   => 'Technologie, Physique et Chimie',
		'bcpst' => 'Biologie, Chimie, Physique et Sciences de la Terre',
		'tb'    => 'Technologie et Biologie',
	];
}

// Ordre d'affichage habituel des filières sur le site.
function ordreFilieres(): array {
	return ['mp', 'mpi', 'pc', 'psi', 'pt', 'tsi', 'tpc', 'bcpst', 'tb'];
}

// Identifiant d'ancre / d'onglet sûr pour le HTML.
function identifiantOnglet(string $cle): string {
	return 'onglet-' . preg_replace('/[^a-z0-9\-]/', '', strtolower($cle));
}

function badgesVoiesFormation(?string $voies): string {
	if ($voies === null || $voies === '') {
		return '';
	}
	$libelles = [
		'FISE' => 'FISE : formation initiale sous statut étudiant.',
		'FISA' => 'FISA : formation initiale sous statut apprenti.',
		'FISEA' => 'FISEA : formation initiale sous statut étudiant puis apprenti.',
	];
	$codes = explode(',', $voies);
	foreach ($codes as $code) {
		if (!isset($libelles[$code]) && $code !== 'FC') {
			throw new UnexpectedValueException('Voie de formation non reconnue : ' . $code);
		}
	}
	$html = '';
	foreach ($libelles as $code => $libelle) {
		if (in_array($code, $codes, true)) {
			$html .= " <span class='badge-voie badge rounded-pill bg-white border text-secondary fw-normal'"
				. " data-bs-toggle='tooltip' title='" . escapeHtml($libelle) . "'>" . $code . "</span>";
		}
	}
	return $html;
}

function obtenirNomEcoleFiche(PDO $db, string $ecole): string {
	$stmt = $db->prepare("SELECT DISTINCT Ecole FROM EcoleConcours WHERE EcoleConcours = :ecole AND Ecole IS NOT NULL AND Ecole <> ''");
	$stmt->execute([':ecole' => $ecole]);
	$noms = $stmt->fetchAll(PDO::FETCH_COLUMN);
	$stmt->closeCursor();
	if (count($noms) > 1) {
		throw new RuntimeException("Plusieurs écoles sont rattachées à cette voie d'admission.");
	}
	return $noms ? $noms[0] : $ecole;
}

function libelleFormationFiche(string $ecole, string $formation): string {
	$prefixe = $ecole . ' - ';
	return strpos($formation, $prefixe) === 0 ? substr($formation, strlen($prefixe)) : $formation;
}

// Résumé éditorial de l'école (colonne Ecole.Resume, alimentée par
// data/Résumé école/importer_resumes_vers_sql.py). Chaîne vide si absent.
function obtenirResumeEcole(PDO $db, string $ecole): string {
	try {
		$stmt = $db->prepare("SELECT Resume FROM Ecole WHERE Ecole = :ecole AND Resume IS NOT NULL AND Resume <> '' LIMIT 1");
		$stmt->execute([':ecole' => $ecole]);
		$resume = $stmt->fetchColumn();
		$stmt->closeCursor();
		return $resume !== false ? trim((string) $resume) : '';
	}
	catch (PDOException $erreur) {
		// Le résumé est un plus éditorial : une erreur ne doit pas casser la fiche.
		error_log('Erreur SELECT Resume : ' . $erreur->getMessage());
		return '';
	}
}

function choisirFraisCTIFiche(array $tarifs): array {
	if (!$tarifs) {
		return [];
	}
	$campagne = $tarifs[0]['Campagne']; // La requête trie du plus récent au plus ancien.
	$montant = $tarifs[0]['Valeur'];
	$montantD1 = null;
	$variables = false;
	foreach ($tarifs as $tarif) {
		if ($tarif['Campagne'] !== $campagne) {
			break;
		}
		$variables = $variables || $tarif['Valeur'] !== $montant;
		if ($tarif['CodeDiplomeCTI'] === 'D1') {
			$montantD1 = $tarif['Valeur'];
		}
	}
	return [
		'Frais' => $variables ? $montantD1 : $montant,
		'FraisCampagne' => $campagne,
		'FraisVariables' => $variables,
	];
}

function calculerFourchetteSalairesCTI(array $salaires): array {
	if (!$salaires) {
		return [];
	}
	$reference = $salaires[0]; // Campagne et promotion les plus récentes d'après le tri SQL.
	$minimum = $maximum = (float) $reference['Valeur'];
	$reponsesManquantes = false;
	foreach ($salaires as $salaire) {
		if ($salaire['Campagne'] !== $reference['Campagne'] || $salaire['Promotion'] !== $reference['Promotion']) {
			break;
		}
		$minimum = min($minimum, (float) $salaire['Valeur']);
		$maximum = max($maximum, (float) $salaire['Valeur']);
		$reponsesManquantes = $reponsesManquantes || !isset($salaire['NbReponses']);
	}
	return ['SalaireCTImin' => $minimum, 'SalaireCTImax' => $maximum,
		'SalaireCTIPromotion' => $reference['Promotion'],
		'SalaireCTIReponsesManquantes' => $reponsesManquantes];
}

function chargerDonneesCTI(PDO $db, string $ecole): array {
	$stmt = $db->prepare("SELECT IdEcole, IdCti, Promo, CPGE, Statut FROM Ecole WHERE Ecole = :ecole");
	$stmt->execute([':ecole' => $ecole]);
	$donnees = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	if (!$donnees || !isset($donnees['IdCti'])) {
		return $donnees ?: [];
	}
	$sql = "SELECT DISTINCT m.Campagne, m.CodeDiplomeCTI, m.Valeur, m.Indicateur, m.Promotion, m.NbReponses
		FROM Diplome d
		JOIN DiplomeIndicateurCTI m ON m.IdCti = :idCti
			AND m.CodeDiplomeCTI = d.CodeDiplomeCTI AND m.Voie = 'FISE'
		WHERE d.IdEcole = :idEcole
			AND (m.Indicateur = 'Frais' OR (m.Indicateur = 'SalaireCTI'
				AND m.Promotion IS NOT NULL AND (m.NbReponses IS NULL OR m.NbReponses > 0)))
		ORDER BY m.Campagne DESC, m.Promotion DESC";
	$params = [':idEcole' => $donnees['IdEcole'], ':idCti' => $donnees['IdCti']];
	try {
		$stmt = $db->prepare($sql);
		$stmt->execute($params);
	} catch (PDOException $erreur) {
		// Permet de déployer le PHP avant la migration SQL.
		if (($erreur->errorInfo[1] ?? null) !== 1054
			|| strpos($erreur->getMessage(), 'd.CodeDiplomeCTI') === false) {
			throw $erreur;
		}
		$stmt = $db->prepare(str_replace(
			["FROM Diplome d", "m.CodeDiplomeCTI = d.CodeDiplomeCTI"],
			["FROM Diplome d JOIN DiplomeCorrespondanceCTI c ON c.IdDiplome = d.IdDiplome",
			 "m.IdCti = c.IdCti AND m.CodeDiplomeCTI = c.CodeDiplomeCTI AND m.Campagne = c.Campagne"],
			$sql
		));
		$stmt->execute($params);
	}
	$tarifs = $salaires = [];
	while ($mesure = $stmt->fetch(PDO::FETCH_ASSOC)) {
		if ($mesure['Indicateur'] === 'Frais') {
			$tarifs[] = $mesure;
		} else {
			$salaires[] = $mesure;
		}
	}
	$stmt->closeCursor();
	// Repli temporaire ENSTA Paris/Bretagne, montant communiqué le 22/11/2026 :
	// 2 936 euros par an, hors données certifiées CTI indisponibles après la fusion.
	// À supprimer dès que les tarifs CTI sont disponibles pour ces diplômes.
	if (!$tarifs && in_array((int) $donnees['IdEcole'], [52, 65], true)) {
		$donnees['Frais'] = 2936;
		$donnees['FraisAide'] = 'Montant annuel renseigné manuellement pour ENSTA Paris et ENSTA Bretagne : 2 936 € (information communiquée le 22/11/2026). Données certifiées CTI indisponibles à la suite de la fusion.';
	}
	return $donnees + choisirFraisCTIFiche($tarifs) + calculerFourchetteSalairesCTI($salaires);
}

// L'identité et le contexte sont portés par le bandeau, pas répétés dans chaque onglet.
function afficherTitreSection(string $ecole, string $section): void {
	echo "<h2 class='h5 text-center mb-1'>";
	echo escapeHtml($section);
	echo "</h2>";
}

// Cherche le meilleur rang d'attractivité parmi les formations de l'école.
function chargerAttractivite(PDO $db, string $ecole): array {
	$noms = [$ecole];
	$stmt = $db->prepare("SELECT Ecole FROM EcoleConcours WHERE EcoleConcours = :ecole AND Ecole IS NOT NULL LIMIT 1");
	$stmt->execute([':ecole' => $ecole]);
	$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	if ($ligne && $ligne['Ecole'] !== '') {
		$noms[] = $ligne['Ecole'];
	}

	$conditions = [];
	$params = [];
	foreach (array_values(array_unique($noms)) as $index => $nom) {
		$param = ':nom' . $index;
		$conditions[] = "(a.Formation = $param OR a.Formation LIKE CONCAT($param, ' (%)'))";
		$params[$param] = $nom;
	}
	$sql = "SELECT MIN(a.Rang) AS Rang, (SELECT COUNT(*) FROM Attractivite WHERE Rang IS NOT NULL) AS Total
			FROM Attractivite a
			WHERE a.Rang IS NOT NULL AND (" . implode(' OR ', $conditions) . ')';
	$stmt = $db->prepare($sql);
	$stmt->execute($params);
	$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();

	return ($ligne && $ligne['Rang'] !== null)
		? ['rang' => (int) $ligne['Rang'], 'total' => (int) $ligne['Total']]
		: ['rang' => '', 'total' => 0];
}

// Dernière médiane à 12 mois disponible pour une école, toutes ses formations
// InserSup rattachées confondues.
function chargerSalaireReference(PDO $db, string $ecole): ?array {
	$sql = "SELECT Salaire.promo_annee AS promotion,
				   AVG(NULLIF(NULLIF(Salaire.salaire_q2_12, 'nd'), '')) AS mediane
			FROM Salaire
			INNER JOIN EcoleSalaire
				ON EcoleSalaire.uo_lib = Salaire.uo_lib
			   AND (EcoleSalaire.denomination_principale = ''
					OR EcoleSalaire.denomination_principale = Salaire.denomination_principale)
			   AND EcoleSalaire.Ecole = :ecole
			WHERE Salaire.type_diplome = 'Formation ingénieur'
			  AND Salaire.libelle_diplome = 'Tout diplôme d\\'ingénieur'
			  AND Salaire.obtention_diplome = 'diplômé'
			  AND Salaire.nationalite = 'ensemble'
			  AND Salaire.genre = 'ensemble'
			  AND Salaire.regime_inscription = 'ensemble'
			  AND Salaire.promo_annee IS NOT NULL
			  AND Salaire.promo_annee NOT IN ('2023','2024')
			  AND Salaire.uo_lib <> 'National'
			GROUP BY Salaire.promo_annee
			HAVING mediane IS NOT NULL AND mediane > 0
			ORDER BY Salaire.promo_annee DESC
			LIMIT 1";
	$stmt = $db->prepare($sql);
	$stmt->execute([':ecole' => $ecole]);
	$reference = $stmt->fetch(PDO::FETCH_ASSOC);
	$stmt->closeCursor();
	return $reference ?: null;
}

// Affiche l'en-tête de la page : nom de l'école, résumé éditorial, site web et
// repères CTI, salaire et classements. Seul le statut CTI est rendu immédiatement ;
// les autres repères sont chargés en arrière-plan dans #badges-entete.
// La voie d'admission ou le diplôme consulté n'est plus rappelé ici : cette
// précision de périmètre est affichée dans l'onglet Admissions (detail.php).
function afficherEnteteEcole(string $ecole, string $urlEcole, array $filieres, string $resume = '', array $cti = []): void {
	$libelles = libellesFilieres();

	echo "<header class='container'>";
	echo "<div class='p-3 p-md-4 border bg-light rounded'>";

	echo "<h1 class='h3 mb-3 text-center'><i class='bi bi-bank2'></i>&nbsp;&nbsp;" . escapeHtml($ecole);
	if (isset($cti['Statut']) && $cti['Statut'] !== '') {
		$statut = ['Public' => 'Publique', 'Privé' => 'Privée'][$cti['Statut']] ?? $cti['Statut'];
		echo "<span class='statut-ecole repere-aide ms-2' tabindex='0' data-bs-toggle='tooltip' title='Statut déclaré par l’école (source CTI)'>"
		   . escapeHtml($statut) . "</span>";
	}
	echo "</h1>";

	// Résumé éditorial sous le titre, avant le bouton du site officiel.
	if ($resume !== '') {
		echo "<p class='fiche-resume text-center text-secondary mb-3'>" . escapeHtml($resume) . "</p>";
	}

	if ($urlEcole !== '') {
		echo "<p class='text-center mb-3'>";
		// même rendu que « Détail de la formation sur le site de l'école » (php/detailSpecialite.php) : simple lien
		echo "<a class='small' href='" . escapeHtml($urlEcole) . "' target='_blank' rel='noopener'>";
		echo "<i class='bi bi-box-arrow-up-right'></i>&nbsp; Site officiel de l'école</a>";
		echo "</p>";
	}

	// rempli après coup par ajax/detailOnglet.php?onglet=entete (voir script en bas de detail.php)
	echo "<div id='badges-entete'><p class='text-center mb-3'><span class='spinner-border spinner-border-sm text-secondary' role='status' aria-label='Chargement des repères'></span></p></div>";

	if (!empty($filieres)) {
		echo "<p class='text-center mb-0 small text-secondary'>Admissions disponibles depuis ";
		echo count($filieres) > 1 ? "les filières " : "la filière ";
		$noms = [];
		foreach ($filieres as $cle) {
			$noms[] = "<span data-bs-toggle='tooltip' title='" . escapeHtml(isset($libelles[$cle]) ? $libelles[$cle] : $cle) . "'>" . escapeHtml(strtoupper($cle)) . "</span>";
		}
		echo implode(', ', $noms);
		echo "</p>";
	}

	echo "</div>";
	echo "</header>";
}

// Une pastille de repère du bandeau : icône, libellé en gris suivi de deux-points, valeur en gras foncé.
// $complement (facultatif) suit la valeur en gris léger, par exemple « (promotion 2022) ».
// Libellés et valeurs sont échappés ici.
function afficherPastilleRepere(string $icone, string $libelle, string $valeur, string $complement = '', string $aide = ''): void {
	echo "<span class='pastille-repere" . ($aide !== '' ? " repere-aide" : "") . "'";
	if ($aide !== '') {
		echo " tabindex='0' data-bs-toggle='tooltip' title='" . escapeHtml($aide) . "'";
	}
	echo "><i class='bi " . $icone . "' aria-hidden='true'></i>"
	   . "<span class='pastille-libelle'>" . escapeHtml($libelle) . "&nbsp;:</span>"
	   . "<strong class='pastille-valeur'>" . escapeHtml($valeur) . "</strong>";
	if ($complement !== '') {
		echo "<span class='pastille-complement'>" . escapeHtml($complement) . "</span>";
	}
	echo "</span>";
}

// Fragment des repères CTI, salaire et classements, calculé une fois les données
// disponibles (chargement direct ou via AJAX) et injecté dans #badges-entete.
function afficherBadgesEntete(array $classements, array $attractivite = [], ?array $salaireReference = null, array $cti = []): void {
	ob_start();
	if (isset($cti['Frais']) || isset($cti['IdCti'])) {
		$aideFrais = 'Maximum annuel obligatoire pour un étudiant communautaire sous statut étudiant, hors CVEC (source CTI';
		$aideFrais .= isset($cti['FraisCampagne']) ? ' ' . $cti['FraisCampagne'] . ')' : ')';
		if (!empty($cti['FraisVariables'])) {
			$aideFrais .= isset($cti['Frais'])
				? '. Les tarifs varient selon le diplôme ; montant du diplôme D1.'
				: '. Les tarifs varient selon le diplôme ; aucun montant disponible pour le D1.';
		}
		$aideFrais = $cti['FraisAide'] ?? $aideFrais;
		afficherPastilleRepere(
			'bi-wallet2', 'Frais de scolarité',
			isset($cti['Frais']) ? number_format($cti['Frais'], 0, ',', ' ') . ' € / an' : 'Non renseigné',
			'', $aideFrais
		);
	}
	if (isset($cti['Promo'])) {
		afficherPastilleRepere(
			'bi-people', 'Promo entrante', number_format($cti['Promo'], 0, ',', ' '),
			'', 'Entrants en cycle ingénieur à la rentrée, tous diplômes et voies de l’école. (source CTI)'
		);
	}
	if (isset($cti['CPGE'])) {
		afficherPastilleRepere(
			'bi-person-check', 'Issus de CPGE', number_format($cti['CPGE'], 1, ',', ' ') . ' %',
			'', 'Part des entrants en cycle ingénieur issus de CPGE y compris ATS, tous diplômes et voies de l’école confondus. (source CTI)'
		);
	}
	if ($salaireReference !== null) {
		afficherPastilleRepere(
			'bi-cash-stack', 'Salaire à 12 mois', formater($salaireReference['mediane'], 0) . ' €', '',
			'Salaire mensuel net médian en équivalent temps plein provenant de la base InserSup, primes incluses, pour les emplois en France. (promotion ' . $salaireReference['promotion'] . ')'
		);
	} elseif (isset($cti['SalaireCTImin']) || isset($cti['SalaireCTImax'])) {
		$minimum = $cti['SalaireCTImin'] ?? null;
		$maximum = $cti['SalaireCTImax'] ?? null;
		if (!is_numeric($minimum) || !is_numeric($maximum)
			|| !is_finite((float) $minimum) || !is_finite((float) $maximum)
			|| $minimum < 0 || $maximum < $minimum) {
			error_log('Fourchette salaire CTI invalide pour IdCti ' . (int) ($cti['IdCti'] ?? 0));
		} else {
			$valeur = number_format($minimum, 0, ',', ' ');
			if ((float) $minimum !== (float) $maximum) {
				$valeur .= ' – ' . number_format($maximum, 0, ',', ' ');
			}
			afficherPastilleRepere(
				'bi-cash-stack', 'Salaire CTI', $valeur . ' €', '',
				'Salaire brut annuel : médianes minimales et maximales par diplôme, '
				. (isset($cti['SalaireCTIPromotion']) ? 'promotion ' . $cti['SalaireCTIPromotion'] : 'même promotion')
				. ', primes incluses, en France, statut étudiant (FISE), hors thèses et services civiques.'
				. (!empty($cti['SalaireCTIReponsesManquantes']) ? ' Nombre de réponses non renseigné pour au moins un diplôme.' : '')
			);
		}
	}
	$reperes = ob_get_clean();
	if ($reperes !== '') {
		echo "<p class='text-center mb-3' id='reperes-ecole'>" . $reperes . "</p>";
	}

	// repères rapides : le classement DAUR et le classement Le Figaro les plus récents disponibles
	$icones = ['DAUR' => 'bi-trophy', 'Figaro' => 'bi-newspaper'];
	$badges = [];
	$famillesRetenues = ['DAUR' => false, 'Figaro' => false];
	foreach (configurationClassements() as $source) {
		if (!isset($famillesRetenues[$source['table']]) || $famillesRetenues[$source['table']]) {
			continue;
		}
		$ligne = isset($classements['lignes'][$source['cle']]) ? $classements['lignes'][$source['cle']] : null;
		if ($ligne === null || ($ligne['rang'] === '' && $ligne['groupe'] === '')) {
			continue;
		}
		$valeur = ($ligne['groupe'] !== '') ? $ligne['groupe'] . ' ' : '';
		$valeur .= ($ligne['rang'] !== '') ? $ligne['rang'] . '/' . $source['total'] : '';
		$badges[] = ['icone' => $icones[$source['table']], 'libelle' => $source['libelle'], 'valeur' => trim($valeur)];
		$famillesRetenues[$source['table']] = true;
	}
	ob_start();
	if (!empty($attractivite) && $attractivite['rang'] !== '') {
		afficherPastilleRepere('bi-graph-up-arrow', 'Attractivité', $attractivite['rang'] . '/' . $attractivite['total']);
	}
	foreach ($badges as $badge) {
		afficherPastilleRepere($badge['icone'], $badge['libelle'], $badge['valeur']);
	}
	$reperes = ob_get_clean();
	if ($reperes !== '') {
		echo "<p class='text-center mb-3 reperes-secondaires'>" . $reperes . "</p>";
	}
}
