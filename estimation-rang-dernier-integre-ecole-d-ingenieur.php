<?php
/**
 * Page publique « méthode » : comment est estimé le rang du dernier admis quand le concours
 * ne le publie pas (minimum garanti, milieu « 3 chances sur 4 », maximum = nombre de classés).
 *
 * Les chiffres affichés (36 coefficients, validation, périmètre, date) viennent de
 * data/estimation/estimation-methode.json, généré par python/estimation_dernier/calibrer.py
 * (option --site) : la page est donc à jour à chaque campagne sans recopie manuelle.
 * L'exemple du thermomètre lit NoteEstimation ; il est masqué si la table est absente.
 * Documentation interne : procédure/Guide estimation rang dernier.md.
 */
require_once __DIR__ . '/php/fonctionConcours.php';
require_once __DIR__ . '/php/detailFiliere.php';
require_once __DIR__ . '/php/navigationRetour.php';

// Destination de retour : la page d'où vient le lecteur (fiche école, onglet filière compris), validée par
// la liste blanche du site. Sans retour valide (entrée directe, lien partagé), retour à l'accueil.
// La clé « valide » dit si la destination vient d'un retour reçu ; le fil d'Ariane s'en sert pour son lien « Ecole ».
function destinationRetourMethode(): array {
	$accueil = ['url' => 'statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php', 'libelle' => "Retour à l'accueil", 'valide' => false];
	if (!isset($_GET['retour'])) {
		return $accueil;
	}
	$url = validerUrlNavigation($_GET['retour'], 0, $motif);
	if ($url === null) {
		error_log('Page méthode estimation : retour invalide (' . $motif . ') retour=' . extraitJournalNavigation($_GET['retour'], 300));
		return $accueil;
	}
	return ['url' => $url, 'libelle' => libelleRetourNavigation($url), 'valide' => true];
}

// Chiffres du calibrage : null si le fichier est absent ou illisible (la page reste affichable).
function lireDonneesMethode(): ?array {
	$chemin = __DIR__ . '/data/estimation/estimation-methode.json';
	if (!is_readable($chemin)) {
		return null;
	}
	$donnees = json_decode((string) file_get_contents($chemin), true);
	return is_array($donnees) && isset($donnees['coefficients']) ? $donnees : null;
}

// Exemple réel pour le thermomètre : la ligne la plus récente estimée de l'école d'exemple, ou null.
function chargerExempleThermometre(): ?array {
	try {
		$db = openDatabase();
		if (!estimationDisponible($db)) {
			return null;
		}
		$stmt = $db->prepare("SELECT n.An, n.Integre, n.RangMedian, e.Statut AS EstimationStatut, e.Plancher AS EstimationPlancher,
				e.Milieu AS EstimationMilieu, e.Plafond AS EstimationPlafond
			FROM Note n
			JOIN NoteEstimation e ON e.Filiere = n.Filiere AND e.An = n.An AND e.Concours = n.Concours AND e.EcoleConcours = n.EcoleConcours
			WHERE n.EcoleConcours = 'Centrale Méditerranée' AND n.Filiere = 'mp' AND e.Statut = 'estime'
				AND e.Milieu IS NOT NULL AND e.Plafond IS NOT NULL
			ORDER BY n.An DESC LIMIT 1");
		$stmt->execute();
		$ligne = $stmt->fetch(PDO::FETCH_ASSOC);
		$stmt->closeCursor();
		return $ligne === false ? null : $ligne;
	}
	catch (PDOException $erreur) {
		error_log('Exemple thermomètre (méthode) : ' . $erreur->getMessage());
		return null;
	}
}

// Coefficient en pourcentage signé, virgule décimale : « +10,2 % ».
function formaterCoefficient(float $c25): string {
	return ($c25 > 0 ? '+' : '') . number_format($c25 * 100, 1, ',', ' ') . ' %';
}

$donnees = lireDonneesMethode();
$exemple = chargerExempleThermometre();
$retour = destinationRetourMethode();
// Le lien du fil d'Ariane (« Filière » si l'on vient d'une liste par filière, sinon « Ecole ») joue le rôle du
// bouton de retour ; sans retour valide, il mène à la liste des écoles.
$urlEcoleFilAriane = $retour['valide'] ? $retour['url'] : 'statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php';
$pageRetour = $retour['valide'] ? parse_url($retour['url'], PHP_URL_PATH) : '';
$libelleFilAriane = strpos((string) $pageRetour, 'par-filiere') !== false ? 'Filière' : 'Ecole';
$libellesBandes = ['1' => '1 admis', '2-9' => '2 à 9', '10-29' => '10 à 29', '30+' => '30 et plus'];
$coefficients = [];
if ($donnees !== null) {
	foreach ($donnees['coefficients'] as $c) {
		$coefficients[$c['filiere']][$c['bande']] = $c;
	}
}
$libelles = libellesFilieres();
?>
<!doctype html>
<html lang="fr">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="description" content="Comment le site estime le rang du dernier admis d'une école d'ingénieurs quand le concours ne le publie pas : minimum garanti, repère « 3 chances sur 4 » et nombre de classés. Méthode, coefficients et limites.">
	<title>Rang du dernier admis en école d'ingénieurs : comment il est estimé</title>
	<link rel="canonical" href="https://loic.website/CPGE/estimation-rang-dernier-integre-ecole-d-ingenieur.php" />
	<?php include __DIR__ . '/php/favicon.php'; include __DIR__ . '/php/style.php'; ?>
	<link rel="stylesheet" href="css/concours.css?v=<?php echo filemtime(__DIR__ . '/css/concours.css'); ?>">
	<link rel="stylesheet" href="css/methodeEstimation.css?v=<?php echo filemtime(__DIR__ . '/css/methodeEstimation.css'); ?>">
</head>
<body class="page-methode" id="hautdepage">
	<?php include __DIR__ . '/php/menu.php'; ?>
	<main class="container methode">

		<nav aria-label="breadcrumb">
			<ol class="breadcrumb">
				<li class="breadcrumb-item"><a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php" aria-label="Accueil"><i class="bi bi-house-door-fill" aria-hidden="true"></i></a></li>
				<li class="breadcrumb-item"><a data-retour-navigation href="<?php echo escapeHtml($urlEcoleFilAriane); ?>"><?php echo escapeHtml($libelleFilAriane); ?></a></li>
				<li class="breadcrumb-item active" aria-current="page">Rang du dernier admis</li>
			</ol>
		</nav>

		<h1 class="h3 text-center mb-3">Comment est estimé le rang du dernier admis ?</h1>

		<p class="lead-methode">Depuis 2018, la plupart des concours ne publient plus le rang du dernier candidat admis dans chaque école : le SCEI ne donne que le <strong>rang médian</strong> et le <strong>rang moyen</strong> des admis. Pour vous aider à situer votre classement, ce site <strong>estime</strong> ce rang à partir des données publiées. Voici comment, et surtout ce que cette estimation vaut.</p>

		<section aria-labelledby="titre-30s" class="mb-4">
			<h2 id="titre-30s" class="h5">En 30 secondes</h2>
			<div class="row g-3 mb-3">
				<div class="col-md-4">
					<div class="carte-valeur carte-assure h-100">
						<div class="carte-valeur-titre"><i class="bi bi-shield-check"></i>&nbsp; Minimum</div>
						<p class="mb-1"><strong>Admission assurée</strong> jusqu'à ce rang.</p>
						<p class="small mb-0">Garanti par un calcul à partir du rang médian et du nombre d'admis.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="carte-valeur carte-probable h-100">
						<div class="carte-valeur-titre"><i class="bi bi-percent"></i>&nbsp; Milieu</div>
						<p class="mb-1"><strong>3 chances sur 4</strong> d'être admis à ce rang.</p>
						<p class="small mb-0">Repère statistique mesuré sur les années passées. Ce n'est pas une garantie.</p>
					</div>
				</div>
				<div class="col-md-4">
					<div class="carte-valeur carte-maximum h-100">
						<div class="carte-valeur-titre"><i class="bi bi-slash-circle"></i>&nbsp; Maximum</div>
						<p class="mb-1"><strong>Impossible</strong> d'être admis au-delà.</p>
						<p class="small mb-0">C'est le nombre de candidats classés par le concours.</p>
					</div>
				</div>
			</div>

			<?php if ($exemple !== null): ?>
				<?php $estimationExemple = estimationDeLigne(array_merge($exemple, ['Dernier' => 0])); ?>
				<?php if ($estimationExemple !== null): ?>
					<div class="exemple-estimation" role="group" aria-label="Exemple">
						<span class="exemple-etiquette">Exemple</span>
						<p class="exemple-legende">Centrale Méditerranée, MP, concours <?php echo escapeHtml($exemple['An']); ?> · <?php echo escapeHtml(formater($exemple['Integre'], 0)); ?> admis, rang médian <?php echo escapeHtml(formater($exemple['RangMedian'], 0)); ?></p>
						<?php afficherThermometreDernier($estimationExemple, false); ?>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</section>

		<div class="accordion" id="accordeon-methode">

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-pourquoi" aria-expanded="false" aria-controls="methode-pourquoi">Pourquoi estimer le rang du dernier ?</button></h2>
				<div id="methode-pourquoi" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<p>Jusqu'en 2017, le SCEI publiait pour chaque école le rang du dernier candidat appelé. Depuis 2018, il a été remplacé par le rang médian et le rang moyen. Seuls quelques concours continuent à donner le rang du dernier admis.</p>
					<p class="mb-0">Le rang médian est une information utile, mais il est <strong>nettement plus favorable que le dernier admis</strong> : la moitié des admis sont classés après lui. S'y fier pour juger ses chances conduit à les sous-estimer. L'estimation donne une fourchette plus réaliste.</p>
				</div></div>
			</section>

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-minimum" aria-expanded="false" aria-controls="methode-minimum">Le minimum : une garantie mathématique</button></h2>
				<div id="methode-minimum" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<p>Les rangs des admis sont tous différents. Si l'école compte <em>N</em> admis, au moins la moitié d'entre eux sont classés après le rang médian, chacun à une place différente. Le dernier admis ne peut donc pas être plus près que :</p>
					<p class="formule">rang médian + ⌈(N − 1) / 2⌉</p>
					<p>Exemple (Arts et Métiers, PT, 2025) : rang médian 577, 544 admis, donc 577 + 272 = <strong>849</strong>. Un candidat classé jusqu'au rang 849 est sûr d'être admis.</p>
					<p class="mb-0">Avec un seul admis, le dernier est le rang médian : le minimum est alors exact.</p>
				</div></div>
			</section>

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-milieu" aria-expanded="false" aria-controls="methode-milieu">Le milieu : « 3 chances sur 4 »</button></h2>
				<div id="methode-milieu" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<p>Pour les écoles dont le rang du dernier était publié, on peut mesurer de combien il dépasse le rang médian : c'est l'<em>écart relatif</em>, (dernier − médian) / médian. On a rassemblé toutes ces mesures<?php if ($donnees !== null): ?> (<?php echo escapeHtml(formater($donnees['lignes_calibrage'], 0)); ?> lignes, de <?php echo escapeHtml($donnees['annee_min']); ?> à <?php echo escapeHtml($donnees['annee_max']); ?>)<?php endif; ?> et cherché l'écart <strong>dépassé trois fois sur quatre</strong> : c'est le coefficient <em>c25</em> (le quartile inférieur).</p>
					<p class="formule">milieu = rang médian × (1 + c25)</p>
					<p>À ce rang, le dernier admis s'est trouvé au-dessus dans environ trois cas sur quatre : un candidat classé là aurait donc été admis environ trois fois sur quatre. Plus le rang est élevé, moins les chances sont grandes ; c'est pourquoi on retient le quartile <em>inférieur</em> pour rassurer à bon escient.</p>
					<ul>
						<li>Le milieu n'est <strong>jamais en dessous du minimum</strong> ni au-dessus du nombre de classés.</li>
						<li>Quand il ne dépasse pas le minimum, il n'est <strong>pas affiché</strong> : le minimum garanti est déjà plus haut. C'est fréquent pour les très grosses écoles.</li>
						<li>Ce n'est pas une garantie : c'est une fréquence observée.</li>
					</ul>
					<?php if ($donnees !== null && !empty($donnees['validation'])): ?>
						<p class="mb-2"><strong>Ce repère tient-il sa promesse ?</strong> On a refait le calcul en laissant de côté chaque année, puis vérifié sur l'année laissée de côté. Part des cas où le dernier admis réel se trouvait au rang « milieu » ou plus loin (cible : 75 %) :</p>
						<div class="table-responsive"><table class="table table-sm table-methode mb-2">
							<thead><tr><th scope="col">Nombre d'admis</th><th scope="col">Cas vérifiés</th><th scope="col">Dernier admis au rang « milieu » ou après</th></tr></thead>
							<tbody>
							<?php foreach ($donnees['validation'] as $v): ?>
								<?php if ($v['bande'] === '1') { continue; } ?>
								<tr><td><?php echo escapeHtml($libellesBandes[$v['bande']] ?? $v['bande']); ?></td><td><?php echo escapeHtml(formater($v['lignes'], 0)); ?></td><td><?php echo escapeHtml(number_format($v['couverture'] * 100, 0, ',', ' ')); ?> %</td></tr>
							<?php endforeach; ?>
							</tbody>
						</table></div>
						<p class="small text-secondary mb-0">D'une année à l'autre, ce taux varie (de l'ordre de 70 % à 85 %) : « environ 3 chances sur 4 » est un ordre de grandeur, pas une probabilité précise. Avec un seul admis, le dernier est le rang médian : rien à estimer.</p>
					<?php endif; ?>
				</div></div>
			</section>

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-coefficients" aria-expanded="false" aria-controls="methode-coefficients">Les coefficients (par filière et par taille d'école)</button></h2>
				<div id="methode-coefficients" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<p>Le coefficient c25 n'est pas unique. Il y en a <strong>un par filière</strong> (MP, PC, PSI…) et <strong>un par taille d'école</strong>, mesurée par le nombre d'admis :</p>
					<ul>
						<li><strong>Par taille d'école.</strong> Plus une école recrute, plus son dernier admis s'éloigne du rang médian : avec un seul admis, le dernier <em>est</em> le médian (+0 %) ; avec des dizaines d'admis, il est nettement plus loin. D'où quatre tailles : 1 admis, 2 à 9, 10 à 29, 30 et plus.</li>
						<li><strong>Par filière.</strong> Les filières n'ont pas le même profil de classement, donc pas le même écart. Quand une filière a moins de <?php echo escapeHtml($donnees['effectif_min_filiere'] ?? 40); ?> mesures dans une taille donnée, elle reprend le coefficient de l'ensemble des filières (valeur en italique avec un astérisque).</li>
						<li><strong>Pas de distinction par concours ni par école.</strong> Les mesures de tous les concours et de toutes les écoles sont rassemblées. Séparer davantage laisserait trop peu de mesures par groupe (la plupart des concours n'ont publié le dernier que quelques années) et rendrait les coefficients instables. Le prix à payer : un concours dont les écoles se comportent différemment n'est pas traité à part.</li>
					</ul>
					<?php if ($donnees !== null): ?>
						<div class="table-responsive"><table class="table table-sm table-methode table-coefficients mb-2">
							<caption class="small text-secondary">Coefficient c25 : milieu = rang médian × (1 + c25). Calibrage <?php echo escapeHtml($donnees['annee_min']); ?>-<?php echo escapeHtml($donnees['annee_max']); ?>.</caption>
							<thead><tr><th scope="col">Filière</th>
							<?php foreach ($donnees['bandes'] as $bande): ?><th scope="col"><?php echo escapeHtml($libellesBandes[$bande] ?? $bande); ?></th><?php endforeach; ?>
							</tr></thead>
							<tbody>
							<?php
								$ordreLignes = array_merge(['toutes'], array_filter(ordreFilieres(), function ($f) use ($coefficients) { return isset($coefficients[$f]); }));
								foreach ($ordreLignes as $filiere):
									$titre = $filiere === 'toutes' ? 'Toutes filières' : strtoupper($filiere);
							?>
								<tr><th scope="row"><?php echo escapeHtml($titre); ?><?php if ($filiere !== 'toutes' && isset($libelles[$filiere])): ?><span class="d-none d-md-inline small text-secondary fw-normal"> &nbsp;<?php echo escapeHtml($libelles[$filiere]); ?></span><?php endif; ?></th>
								<?php foreach ($donnees['bandes'] as $bande): ?>
									<?php
										$entree = $coefficients[$filiere][$bande] ?? null;
										$repli = ($filiere !== 'toutes') && ($entree === null || $entree['source'] === 'toutes');
										$valeur = $entree !== null ? $entree : ($coefficients['toutes'][$bande] ?? null);
									?>
									<td<?php echo $repli ? " class='repli'" : ''; ?>><?php echo $valeur === null ? '&mdash;' : escapeHtml(formaterCoefficient((float) $valeur['c25'])) . ($repli ? '*' : ''); ?></td>
								<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
							</tbody>
						</table></div>
						<p class="small text-secondary mb-0">* Valeur de l'ensemble des filières, faute d'assez de mesures pour cette filière et cette taille d'école.</p>
					<?php else: ?>
						<p class="alert alert-warning mb-0">Le tableau des coefficients est momentanément indisponible.</p>
					<?php endif; ?>
				</div></div>
			</section>

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-maximum" aria-expanded="false" aria-controls="methode-maximum">Le maximum : le nombre de classés</button></h2>
				<div id="methode-maximum" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<p>Aucun candidat ne peut être admis au-delà du dernier rang classé du concours : le maximum est le <strong>nombre de candidats classés</strong> publié par le SCEI.</p>
					<p class="mb-0"><?php if ($donnees !== null): ?>Vérifié sur <?php echo escapeHtml(formater($donnees['maximum']['lignes_k_fiable'], 0)); ?> cas où le dernier admis est connu : <strong><?php echo escapeHtml($donnees['maximum']['depassements']); ?> dépassement</strong>. <?php endif; ?>C'est une borne large (le dernier admis est le plus souvent bien en deçà), mais elle est sûre. Elle n'est pas affichée quand le nombre de classés ne correspond pas à l'école (voir les limites).</p>
				</div></div>
			</section>

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-limites" aria-expanded="false" aria-controls="methode-limites">Les limites à connaître</button></h2>
				<div id="methode-limites" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<ul class="mb-0">
						<li><strong>Seuls le minimum et le maximum sont des garanties.</strong> Le milieu est une fréquence observée sur le passé : une année peut s'écarter, notamment avec le jeu des désistements et des listes d'appel.</li>
						<li><strong>Concours à classement commun à plusieurs filières</strong><?php if ($donnees !== null && !empty($donnees['concours_interfilieres'])): ?> (<?php echo escapeHtml(implode(', ', $donnees['concours_interfilieres'])); ?>)<?php endif; ?> : le nombre de classés est celui de toutes les filières réunies, pas de la filière consultée. Seul le <strong>minimum</strong> est affiché.</li>
						<li><strong>Écoles « Fonctionnaire » et École de l'Air</strong> : leur nombre de classés ne porte que sur une partie des candidats alors que les rangs sont ceux du classement général. <strong>Aucune estimation</strong> n'est affichée.</li>
						<li><strong>ENS</strong> : classés et entrants ne portent que sur les candidats fonctionnarisables, alors que les inscrits portent sur tous les candidats : la sélectivité (rang divisé par le nombre d'inscrits) y est à interpréter avec prudence.</li>
						<li>Les données du SCEI contiennent parfois des incohérences. Les lignes manifestement incohérentes sont écartées du calibrage<?php if ($donnees !== null): ?> (<?php echo escapeHtml($donnees['exclusions']['anomalies']); ?> cas)<?php endif; ?>.</li>
						<li>Une estimation ne remplace pas le jugement : un rang proche d'une limite se discute avec l'école, les anciens et les statistiques de l'année.</li>
					</ul>
				</div></div>
			</section>

			<section class="accordion-item">
				<h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#methode-donnees" aria-expanded="false" aria-controls="methode-donnees">Les données et leur mise à jour</button></h2>
				<div id="methode-donnees" class="accordion-collapse collapse" data-bs-parent="#accordeon-methode"><div class="accordion-body">
					<ul class="mb-0">
						<li><strong>Source :</strong> statistiques d'admission du SCEI (rang médian, nombre d'admis, nombre de classés, et rang du dernier quand il est publié).</li>
						<?php if ($donnees !== null): ?>
							<li><strong>Calibrage :</strong> <?php echo escapeHtml(formater($donnees['lignes_calibrage'], 0)); ?> mesures de <?php echo escapeHtml($donnees['annee_min']); ?> à <?php echo escapeHtml($donnees['annee_max']); ?>. Écartées : <?php echo escapeHtml(formater($donnees['exclusions']['interfiliere'], 0)); ?> mesures de concours à classement commun, <?php echo escapeHtml(formater($donnees['exclusions']['statut_particulier'], 0)); ?> d'écoles « Fonctionnaire » ou École de l'Air et <?php echo escapeHtml($donnees['exclusions']['anomalies']); ?> incohérences.</li>
							<li><strong>Dernière mise à jour :</strong> <?php echo escapeHtml(date('d/m/Y', strtotime($donnees['genere']))); ?>. Les coefficients sont recalculés à chaque nouvelle campagne.</li>
						<?php endif; ?>
						<li>Les estimations sont enregistrées une fois par campagne et ne changent pas entre deux mises à jour.</li>
					</ul>
				</div></div>
			</section>

		</div>

		<div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
			<a class="btn btn-primary" href="#hautdepage">&uarr; Haut de page</a>
			<a class="btn btn-primary" data-retour-navigation href="<?php echo escapeHtml($retour['url']); ?>">&larr; <?php echo escapeHtml($retour['libelle']); ?></a>
		</div>
	</main>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" defer></script>
	<script>
		// Bootstrap est chargé en defer : on attend DOMContentLoaded pour activer les tooltips HTML.
		document.addEventListener('DOMContentLoaded', function () {
			document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (element) {
				new bootstrap.Tooltip(element);
			});
		});
	</script>
</body>
</html>
