<?php

require_once __DIR__ . '/detailEntete.php';

// Affiche le contenu de l'onglet "Spécialités" à partir des tables Ecole, Diplome et Specialite.
// La collecte est en cours : un message d'attente est affiché tant qu'aucune donnée n'existe pour l'école.
function afficherSpecialites(PDO $db, string $ecole, bool $debug = false): void {
	afficherTitreSection($ecole, 'Spécialités');

	// Ecole.Ecole porte le nom canonique, alors que la page reçoit le libellé concours
	$ecoleCanonique = $ecole;
	try {
		$stmt = $db->prepare("SELECT Ecole FROM EcoleConcours WHERE EcoleConcours = :ecole AND Ecole IS NOT NULL LIMIT 1");
		$stmt->execute([':ecole' => $ecole]);
		$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		if ($ligne && $ligne['Ecole'] !== '') {
			$ecoleCanonique = $ligne['Ecole'];
		}
	}
	catch (PDOException $erreur) {
		echo "<div class='alert alert-warning'>Erreur SELECT EcoleConcours : " . escapeHtml($erreur->getMessage()) . "</div>";
		return;
	}

	// LEFT JOIN : un diplôme dont les spécialités ne sont pas encore collectées reste visible
	$sql = "SELECT d.IdDiplome, d.Diplome, d.UrlFormation,
				   s.Groupe, s.Specialite, s.TypeEtude, s.AnneeChoix
			FROM Ecole e
			JOIN Diplome d ON d.IdEcole = e.IdEcole
			LEFT JOIN Specialite s ON s.IdDiplome = d.IdDiplome
			WHERE e.Ecole = :ecoleCanonique OR e.Ecole = :ecoleDirecte
			ORDER BY d.Diplome ASC, s.Groupe ASC, s.Specialite ASC";
	if ($debug) {
		echo "SQL specialite = " . escapeHtml($sql) . "<br/>";
	}
	try {
		$stmt = $db->prepare($sql);
		$stmt->execute([':ecoleCanonique' => $ecoleCanonique, ':ecoleDirecte' => $ecole]);
		$lignes = $stmt->fetchAll(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
	}
	catch (PDOException $erreur) {
		echo "<div class='alert alert-warning'>Erreur SELECT Specialite : " . escapeHtml($erreur->getMessage()) . "</div>";
		return;
	}

	if (empty($lignes)) {
		echo "<div class='alert alert-info'>";
		echo "<i class='bi bi-hourglass-split'></i>&nbsp; <strong>Bientôt disponible.</strong><br>";
		echo "Les spécialités et parcours proposés par cette école sont en cours de collecte.";
		echo "</div>";
		echo "<div class='border-top mt-4 pt-3'>";
		echo "<ul class='text-muted small mb-0'>";
		echo "<li>Les spécialités recensées à ce jour sont celles des écoles des concours X-ENS, CentraleSupélec, Mines-Ponts, Mines-Télécom, CCINP, Groupe INSA et Polytech.</li>";
		echo "<li>Les autres concours seront couverts progressivement.</li>";
		echo "</ul>";
		echo "</div>";
		return;
	}

	$diplomes = regrouperSpecialites($lignes);
	$annees = [];
	foreach ($diplomes as $diplome) {
		foreach ($diplome['groupes'] as $specialites) {
			foreach ($specialites as $specialite) {
				if (!empty($specialite['AnneeChoix'])) {
					$annees[$specialite['AnneeChoix']] = true;
				}
			}
		}
	}

	// l'année n'est signalée par un badge que lorsqu'elle varie d'une spécialité à l'autre
	$anneeUnique = (count($annees) === 1) ? (string) array_key_first($annees) : '';

	// plusieurs diplômes : toujours l'accordéon, sinon deux écoles comparables
	// s'afficheraient différemment selon leur seul nombre de spécialités
	if (count($diplomes) > 1) {
		afficherSpecialitesRepliees($diplomes, $anneeUnique);
	} else {
		foreach ($diplomes as $diplome) {
			afficherTitreDiplome($diplome, $ecoleCanonique, count($diplomes));
			afficherGroupesDiplome($diplome, $anneeUnique);
		}
	}

	afficherReservesSpecialites();
}

// Limites de lecture des données, rappelées en fin d'onglet.
function afficherReservesSpecialites(): void {
	echo "<div class='border-top mt-4 pt-3'>";
	echo "<ul class='text-muted small mb-0'>";
	echo "<li>Le choix de la spécialité intervient le plus souvent en 3<sup>e</sup> et dernière année, parfois dès la 2<sup>e</sup>.</li>";
	echo "<li>Les spécialités listées correspondent au parcours sous statut étudiant (FISE). Elles ne sont pas toutes accessibles en apprentissage (FISA).</li>";
	echo "<li>Dans les écoles multi-campus, toutes les spécialités ne sont pas proposées sur chaque campus.</li>";
	echo "<li>En cas de doute, se référer au site de l'école (en haut de cette page).</li>";
	echo "</ul>";
	echo "</div>";
}

// Réorganise les lignes SQL à plat en diplômes -> groupes -> spécialités.
function regrouperSpecialites(array $lignes): array {
	$diplomes = [];
	foreach ($lignes as $ligne) {
		$id = (int) $ligne['IdDiplome'];
		if (!isset($diplomes[$id])) {
			$diplomes[$id] = [
				'id'      => $id,
				'nom'     => ($ligne['Diplome'] !== null && $ligne['Diplome'] !== '') ? $ligne['Diplome'] : 'Diplôme d\'ingénieur',
				'url'     => (string) $ligne['UrlFormation'],
				'groupes' => [],
			];
		}
		if ($ligne['Specialite'] === null || $ligne['Specialite'] === '') {
			continue;
		}
		$groupe = ($ligne['Groupe'] !== null) ? $ligne['Groupe'] : '';
		$diplomes[$id]['groupes'][$groupe][] = $ligne;
	}
	return $diplomes;
}

// Titre d'un diplôme, cliquable vers la page de formation de l'école quand elle est connue.
// Sur les 2/3 des écoles le diplôme unique porte le nom de l'école : le titre ne dirait rien
// de plus que le bandeau de la page, on ne garde alors que le lien vers la formation.
function afficherTitreDiplome(array $diplome, string $ecole, int $nombreDiplomes): void {
	if ($nombreDiplomes === 1 && memeLibelle($diplome['nom'], $ecole)) {
		if ($diplome['url'] !== '') {
			echo "<p class='mb-2'><a class='small' href='" . escapeHtml($diplome['url']) . "' target='_blank' rel='noopener'>";
			echo "<i class='bi bi-box-arrow-up-right'></i>&nbsp; Détail de la formation sur le site de l'école</a></p>";
		}
		return;
	}
	echo "<h3 class='h5 text-secondary mt-4 mb-2'><i class='bi bi-mortarboard'></i>&nbsp; ";
	if ($diplome['url'] !== '') {
		echo "<a href='" . escapeHtml($diplome['url']) . "' target='_blank' rel='noopener'>" . escapeHtml($diplome['nom']) . "</a>";
	} else {
		echo escapeHtml($diplome['nom']);
	}
	echo "</h3>";
}

// Comparaison de libellés tolérante à la casse et à la ponctuation.
function memeLibelle(string $a, string $b): bool {
	$normaliser = static function (string $texte): string {
		$texte = mb_strtolower($texte, 'UTF-8');
		return trim(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $texte));
	};
	return $normaliser($a) === $normaliser($b);
}

// Affiche les groupes d'un diplôme ; le niveau groupe est masqué quand il n'y en a qu'un,
// car il ne distingue alors rien et ajoute une ligne de titre inutile.
function afficherGroupesDiplome(array $diplome, string $anneeUnique): void {
	if (empty($diplome['groupes'])) {
		echo "<p class='text-muted small fst-italic'>Spécialités en cours de collecte.</p>";
		return;
	}

	$afficherGroupe = (count($diplome['groupes']) > 1);
	foreach ($diplome['groupes'] as $groupe => $specialites) {
		if ($afficherGroupe && $groupe !== '') {
			echo "<h4 class='h6 mt-3 mb-1'>" . escapeHtml((string) $groupe) . "</h4>";
		}
		echo "<ul class='liste-specialites'>";
		foreach ($specialites as $specialite) {
			echo "<li>" . escapeHtml($specialite['Specialite']) . badgesSpecialite($specialite, $anneeUnique) . "</li>";
		}
		echo "</ul>";
	}
}

// Badges affichés seulement quand l'information sort de l'ordinaire :
// l'année si elle varie au sein de l'école, le statut uniquement pour l'apprentissage.
function badgesSpecialite(array $specialite, string $anneeUnique): string {
	$html = '';
	if ($anneeUnique === '' && !empty($specialite['AnneeChoix'])) {
		$html .= " <span class='badge rounded-pill bg-white border text-secondary fw-normal'>"
			   . escapeHtml($specialite['AnneeChoix']) . "</span>";
	}
	if (isset($specialite['TypeEtude']) && strtoupper((string) $specialite['TypeEtude']) === 'FISA') {
		$html .= " <span class='badge rounded-pill bg-white border text-secondary fw-normal'"
			   . " data-bs-toggle='tooltip' title='FISA : formation par apprentissage.'>apprentissage</span>";
	}
	return $html;
}

// Accordéon utilisé dès que l'école a plusieurs diplômes : le premier reste ouvert
// pour que l'onglet ne s'affiche jamais entièrement vide, sauf s'il n'a qu'une
// seule spécialité (son titre y ressemble déjà, le déplier n'apporterait rien).
function afficherSpecialitesRepliees(array $diplomes, string $anneeUnique): void {
	echo "<div class='accordion' id='accordeon-specialites'>";
	$premier = true;
	foreach ($diplomes as $diplome) {
		$idPanneau = 'specialite-diplome-' . $diplome['id'];
		$nombre = 0;
		foreach ($diplome['groupes'] as $specialites) {
			$nombre += count($specialites);
		}
		$ouvrir = $premier && $nombre > 1;

		echo "<div class='accordion-item'>";
		echo "<h3 class='accordion-header'>";
		echo "<button class='accordion-button" . ($ouvrir ? "" : " collapsed") . "' type='button'"
		   . " data-bs-toggle='collapse' data-bs-target='#" . $idPanneau . "'"
		   . " aria-expanded='" . ($ouvrir ? 'true' : 'false') . "' aria-controls='" . $idPanneau . "'>";
		echo "<i class='bi bi-mortarboard'></i>&nbsp; " . escapeHtml($diplome['nom']);
		if ($nombre > 0) {
			echo " <span class='badge rounded-pill bg-white border text-secondary fw-normal ms-2'>" . $nombre . "</span>";
		}
		echo "</button></h3>";
		echo "<div id='" . $idPanneau . "' class='accordion-collapse collapse" . ($ouvrir ? " show" : "") . "'"
		   . " data-bs-parent='#accordeon-specialites'>";
		echo "<div class='accordion-body'>";
		if ($diplome['url'] !== '') {
			echo "<p class='mb-2'><a class='small' href='" . escapeHtml($diplome['url']) . "' target='_blank' rel='noopener'>";
			echo "<i class='bi bi-box-arrow-up-right'></i>&nbsp; Détail de la formation sur le site de l'école</a></p>";
		}
		afficherGroupesDiplome($diplome, $anneeUnique);
		echo "</div></div></div>";
		$premier = false;
	}
	echo "</div>";
}
