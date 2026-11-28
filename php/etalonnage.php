<?php

/**
 * Étalonnage des concours : mesure, pour chaque couple (concours, filière), de combien les
 * rangs de ses écoles sont "trop flatteurs" ou "trop sévères" par rapport au niveau national
 * des écoles, afin de pouvoir comparer des pourcentages issus de concours différents.
 *
 * Principe (étalonnage par ancre) :
 *  - le rang d'une école dans un concours est converti en échelle logit : y = logit(rang / (inscrits + 1)) ;
 *    plus y est bas, plus l'école est sélective dans ce concours ;
 *  - le "niveau moyen" DAUR (table Attractivite) sert d'ancre commune, car il est exprimé à
 *    l'échelle nationale ; on suppose y = b * t(niveau) + décalage(concours, filière) + bruit, où
 *    t(niveau) = logit(niveau / 100) : le niveau est une note bornée qui se tasse en haut (X 99, ENS
 *    Ulm 99, Mines Paris 97) ; sans cette dilatation, une relation linéaire écrase justement les
 *    meilleures écoles et sous-estime la force des banques d'élite (X-ENS) ;
 *  - la pente b est estimée à l'intérieur des concours (même pool de candidats), puis chaque
 *    concours reçoit un décalage : la moyenne, pondérée par les intégrés, de l'écart entre le y
 *    observé de ses écoles-repères et b * niveau ;
 *  - un concours comptant moins de $reperesMin écoles-repères n'est pas étalonné ;
 *  - les décalages sont exprimés par rapport à UN SEUL concours de référence pour toutes les
 *    filières : le plus large des concours étalonnés (donc le plus proche de la promotion entière).
 *    Une référence par filière fausserait les comparaisons entre filières (Allstar, comparaison par
 *    école) : chaque filière serait mesurée avec une règle différente. Comme la pente b et l'échelle
 *    du niveau DAUR sont communes à toutes les filières, les décalages sont comparables entre elles.
 *
 * Le niveau DAUR ne classe aucune école : il ne sert qu'à régler UN coefficient par concours.
 * Les fonctions de ce fichier sont pures (aucun accès base) pour pouvoir être testées.
 */

/**
 * Concours qui partagent les MÊMES épreuves écrites, donc le même groupe de candidats : leurs
 * décalages sont estimés ensemble (un seul décalage par famille et par filière). Seuls les oraux
 * diffèrent. Pour Mines-Ponts, Mines-Télécom et TPE-EIVP, l'écrit est commun (les candidats
 * admissibles aux deux concours ne passent que les oraux Mines-Ponts, dont les résultats sont
 * transmis au Mines-Télécom). À compléter si d'autres concours partagent leurs écrits.
 */
const FAMILLES_ECRITS = [
	'Mines-Ponts / Mines-Télécom / TPE' => ['Concours Commun MINES-PONTS', 'Concours Mines - Télécom', 'CONCOURS COMMUN TPE'],
];

/** Nom de la famille d'écrits d'un concours (ou le concours lui-même s'il n'en a pas). */
function familleEcrits(string $concours): string {
	foreach (FAMILLES_ECRITS as $famille => $membres) {
		if (in_array($concours, $membres, true)) return $famille;
	}
	return $concours;
}

/** Concours réels d'une famille d'écrits (ou le concours seul s'il n'appartient à aucune famille). */
function membresFamille(string $nom): array {
	return FAMILLES_ECRITS[$nom] ?? [$nom];
}

/**
 * Rend un nom comparable : minuscules, sans accents ni ponctuation, mots séparés par un espace.
 */
function normaliserNomEcole(string $nom): string {
	// strtr (octets UTF-8) plutôt que mbstring, pour ne dépendre d'aucune extension PHP
	$nom = strtr(str_replace(['’', "'"], ' ', $nom), [
		'à' => 'a', 'â' => 'a', 'ä' => 'a', 'á' => 'a', 'ã' => 'a', 'À' => 'a', 'Â' => 'a', 'Ä' => 'a',
		'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'É' => 'e', 'È' => 'e', 'Ê' => 'e', 'Ë' => 'e',
		'î' => 'i', 'ï' => 'i', 'í' => 'i', 'Î' => 'i', 'Ï' => 'i',
		'ô' => 'o', 'ö' => 'o', 'ó' => 'o', 'õ' => 'o', 'Ô' => 'o', 'Ö' => 'o',
		'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ú' => 'u', 'Ù' => 'u', 'Û' => 'u', 'Ü' => 'u',
		'ç' => 'c', 'Ç' => 'c', 'ñ' => 'n', 'œ' => 'oe', 'Œ' => 'oe', 'æ' => 'ae',
	]);
	$nom = preg_replace('/[^a-z0-9 ]+/', ' ', strtolower($nom));
	return trim(preg_replace('/\s+/', ' ', $nom));
}

/**
 * Rattache les formations DAUR (nom de formation, effectif, niveau moyen) aux écoles du site :
 * le nom de la formation doit commencer par le nom de l'école (mots entiers) ; l'école au nom le
 * plus long l'emporte. Le niveau d'une école est la moyenne de ses formations pondérée par l'effectif.
 *
 * @param array $formations Lignes ['Formation' => string, 'Effectif' => ?int, 'Niveau' => ?int].
 * @param string[] $ecoles Noms des écoles du référentiel.
 * @return array<string, float> Niveau moyen par nom d'école (écoles non rattachées absentes).
 */
function niveauParEcole(array $formations, array $ecoles): array {
	$mots = [];
	foreach ($ecoles as $ecole) {
		$normalise = normaliserNomEcole($ecole);
		if ($normalise !== '') $mots[$ecole] = $normalise . ' ';
	}
	$cumuls = [];
	foreach ($formations as $formation) {
		if (!isset($formation['Niveau']) || $formation['Niveau'] === null) continue;
		$nom = normaliserNomEcole((string) $formation['Formation']) . ' ';
		$meilleure = null;
		foreach ($mots as $ecole => $prefixe) {
			if (strncmp($nom, $prefixe, strlen($prefixe)) === 0
				&& ($meilleure === null || strlen($prefixe) > strlen($mots[$meilleure]))) {
				$meilleure = $ecole;
			}
		}
		if ($meilleure === null) continue;
		$poids = max(1, (int) ($formation['Effectif'] ?? 0));
		$cumuls[$meilleure][0] = ($cumuls[$meilleure][0] ?? 0) + $poids;
		$cumuls[$meilleure][1] = ($cumuls[$meilleure][1] ?? 0) + $poids * (float) $formation['Niveau'];
	}
	$niveaux = [];
	foreach ($cumuls as $ecole => $cumul) {
		$niveaux[$ecole] = $cumul[1] / $cumul[0];
	}
	return $niveaux;
}

/**
 * Dilate le haut de l'échelle du niveau moyen DAUR (note de 0 à 100) : logit((niveau + 0,5) / 101).
 */
function transformerNiveau(float $niveau): float {
	$niveau = min(100.0, max(0.0, $niveau));
	return log(($niveau + 0.5) / (100.5 - $niveau));
}

/**
 * Calcule la pente et les décalages d'étalonnage.
 *
 * @param array $lignes Une ligne par (école, concours, filière) :
 *   ['Ecole', 'Concours', 'Filiere', 'Poids' (intégrés), 'Y' (logit moyen), 'Inscrit' (inscrits moyens)].
 * @param array<string, float> $niveaux Niveau moyen DAUR par école.
 * @param int $reperesMin Nombre minimal d'écoles-repères pour étalonner un concours.
 * @param float $plancher Marge minimale (en logit) : l'étalonnage n'est jamais plus précis que cela.
 * @return array ['pente' => float, 'sigma' => float, 'groupes' => list<array>] où chaque groupe est
 *   ['Filiere', 'Concours', 'Decalage' (?float), 'Erreur' (?float), 'NbReperes' (int), 'Reference' (0|1)].
 */
function calculerEtalonnage(array $lignes, array $niveaux, int $reperesMin = 3, float $plancher = 0.35): array {
	$groupes = [];
	$reperes = [];
	$membres = []; // concours réels de chaque groupe (famille d'écrits)
	foreach ($lignes as $ligne) {
		$cle = $ligne['Filiere'] . "\x1f" . familleEcrits($ligne['Concours']);
		if (!isset($groupes[$cle])) {
			$groupes[$cle] = ['Filiere' => $ligne['Filiere'], 'Concours' => familleEcrits($ligne['Concours']), 'poidsInscrit' => 0.0, 'poids' => 0.0];
		}
		$membres[$cle][$ligne['Concours']] = true;
		$groupes[$cle]['poids'] += $ligne['Poids'];
		$groupes[$cle]['poidsInscrit'] += $ligne['Poids'] * $ligne['Inscrit'];
		if (isset($niveaux[$ligne['Ecole']])) {
			$reperes[$cle][] = $ligne + ['Niveau' => transformerNiveau($niveaux[$ligne['Ecole']])];
		}
	}

	// seuls les groupes ayant assez d'écoles-repères distinctes participent à l'estimation
	$utilisables = [];
	foreach ($reperes as $cle => $liste) {
		if (count(array_unique(array_column($liste, 'Ecole'))) >= $reperesMin) $utilisables[$cle] = $liste;
	}

	// estimation alternée : décalages (moyenne pondérée de y - b * niveau) puis pente intra-groupe
	$pente = -1.0;
	$decalages = [];
	for ($tour = 0; $tour < 500; $tour++) {
		foreach ($utilisables as $cle => $liste) {
			$somme = $poids = 0.0;
			foreach ($liste as $l) {
				$somme += $l['Poids'] * ($l['Y'] - $pente * $l['Niveau']);
				$poids += $l['Poids'];
			}
			$decalages[$cle] = $somme / $poids;
		}
		$numerateur = $denominateur = 0.0;
		foreach ($utilisables as $liste) {
			$poids = $mY = $mN = 0.0;
			foreach ($liste as $l) {
				$poids += $l['Poids'];
				$mY += $l['Poids'] * $l['Y'];
				$mN += $l['Poids'] * $l['Niveau'];
			}
			$mY /= $poids;
			$mN /= $poids;
			foreach ($liste as $l) {
				$numerateur += $l['Poids'] * ($l['Y'] - $mY) * ($l['Niveau'] - $mN);
				$denominateur += $l['Poids'] * ($l['Niveau'] - $mN) ** 2;
			}
		}
		if ($denominateur <= 0) break;
		$nouvelle = $numerateur / $denominateur;
		if (abs($nouvelle - $pente) < 1e-10) { $pente = $nouvelle; break; }
		$pente = $nouvelle;
	}

	// dispersion résiduelle des écoles-repères autour du modèle
	$carres = $poidsTotal = 0.0;
	foreach ($utilisables as $cle => $liste) {
		foreach ($liste as $l) {
			$carres += $l['Poids'] * ($l['Y'] - $decalages[$cle] - $pente * $l['Niveau']) ** 2;
			$poidsTotal += $l['Poids'];
		}
	}
	$sigma = $poidsTotal > 0 ? sqrt($carres / $poidsTotal) : 0.0;

	// concours de référence unique : le plus large (inscrits moyens) parmi tous les concours étalonnés
	$cleRef = null;
	$inscritsRef = -1.0;
	foreach ($utilisables as $cle => $_) {
		$inscrits = $groupes[$cle]['poidsInscrit'] / $groupes[$cle]['poids'];
		if ($inscrits > $inscritsRef) {
			$inscritsRef = $inscrits;
			$cleRef = $cle;
		}
	}

	$resultat = [];
	foreach ($groupes as $cle => $groupe) {
		$filiere = $groupe['Filiere'];
		$nbReperes = isset($reperes[$cle]) ? count(array_unique(array_column($reperes[$cle], 'Ecole'))) : 0;
		$decalage = $erreur = null;
		$estReference = 0;
		if (isset($utilisables[$cle]) && $cleRef !== null) {
			$decalage = $decalages[$cle] - $decalages[$cleRef];
			$nbRef = count(array_unique(array_column($utilisables[$cleRef], 'Ecole')));
			$erreur = sqrt($sigma ** 2 / $nbReperes + ($cle === $cleRef ? 0.0 : $sigma ** 2 / $nbRef));
			$erreur = max($erreur, $plancher);
			$estReference = ($cle === $cleRef) ? 1 : 0;
		}
		foreach (array_keys($membres[$cle]) as $concours) {
			$resultat[] = [
				'Filiere' => $filiere, 'Concours' => $concours,
				'Decalage' => $decalage, 'Erreur' => $erreur, 'NbReperes' => $nbReperes, 'Reference' => $estReference,
			];
		}
	}
	usort($resultat, fn($a, $b) => [$a['Filiere'], $a['Concours']] <=> [$b['Filiere'], $b['Concours']]);
	return ['pente' => $pente, 'sigma' => $sigma, 'groupes' => $resultat];
}

/**
 * Lit en base les lignes (école, concours, filière) des dernières années et les formations DAUR.
 * Mêmes filtres de qualité que la page de sélectivité (rang cohérent, concours étrangers ou
 * inter-filières écartés, au moins 40 % des admissibles classés).
 *
 * @return array [lignes, formations, ecoles]
 */
function chargerDonneesEtalonnage(PDO $db, int $nbAnnees = 4): array {
	$sql = "SELECT ec.Ecole, n.Concours, n.Filiere,
				SUM(n.Integre) AS Poids,
				SUM(n.Integre * LN((n.RangMedian / (n.Inscrit + 1)) / (1 - n.RangMedian / (n.Inscrit + 1)))) / SUM(n.Integre) AS Y,
				AVG(n.Inscrit) AS Inscrit
			FROM Note n
			INNER JOIN (SELECT DISTINCT Filiere, Concours, EcoleConcours, Ecole FROM EcoleConcours) ec
				ON ec.Filiere = n.Filiere AND ec.Concours = n.Concours AND ec.EcoleConcours = n.EcoleConcours
			WHERE n.An > (SELECT MAX(An) FROM Note) - :nbAnnees
				AND ec.Ecole IS NOT NULL
				AND n.Integre > 0 AND n.RangMedian > 0 AND n.Inscrit > 0 AND n.RangMedian <= n.Inscrit
				AND (n.Admissible IS NULL OR n.Admissible = 0 OR n.Classe >= 0.4 * n.Admissible)
				AND n.Concours NOT LIKE '%Inter-Fili_re%' AND n.Concours NOT LIKE '% Etr.%'
				AND n.EcoleConcours NOT LIKE '% Etr.%' AND n.EcoleConcours NOT LIKE '% Etranger%'
			GROUP BY ec.Ecole, n.Concours, n.Filiere";
	$stmt = $db->prepare($sql);
	$stmt->execute([':nbAnnees' => $nbAnnees]);
	$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
	foreach ($lignes as &$ligne) {
		$ligne['Poids'] = (float) $ligne['Poids'];
		$ligne['Y'] = (float) $ligne['Y'];
		$ligne['Inscrit'] = (float) $ligne['Inscrit'];
	}
	unset($ligne);
	$formations = $db->query("SELECT Formation, Effectif, `Niveau moyen` AS Niveau FROM Attractivite")->fetchAll(PDO::FETCH_ASSOC);
	$ecoles = $db->query("SELECT Ecole FROM Ecole")->fetchAll(PDO::FETCH_COLUMN);
	return [$lignes, $formations, $ecoles];
}

/**
 * Recalcule et remplace le contenu de la table ConcoursEtalonnage.
 *
 * @return array Résultat de calculerEtalonnage (pente, sigma, groupes).
 */
function enregistrerEtalonnage(PDO $db): array {
	[$lignes, $formations, $ecoles] = chargerDonneesEtalonnage($db);
	$resultat = calculerEtalonnage($lignes, niveauParEcole($formations, $ecoles));
	$db->beginTransaction();
	$db->exec("DELETE FROM ConcoursEtalonnage");
	$stmt = $db->prepare("INSERT INTO ConcoursEtalonnage (Filiere, Concours, Decalage, Erreur, NbReperes, Reference)
		VALUES (:filiere, :concours, :decalage, :erreur, :reperes, :reference)");
	foreach ($resultat['groupes'] as $groupe) {
		$stmt->execute([
			':filiere' => $groupe['Filiere'], ':concours' => $groupe['Concours'],
			':decalage' => $groupe['Decalage'], ':erreur' => $groupe['Erreur'],
			':reperes' => $groupe['NbReperes'], ':reference' => $groupe['Reference'],
		]);
	}
	$db->commit();
	return $resultat;
}
