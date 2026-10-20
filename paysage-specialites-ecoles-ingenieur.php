<?php
/**
 * Panorama des specialites des ecoles d'ingenieur.
 *
 * Page de synthese, sans liste d'ecoles : les domaines ouvrent la page Recherche
 * avec l'identifiant du domaine, le mot-cle et le lancement automatique des memes correspondances.
 * Les affectations proviennent de SpecialiteDomaineAffectation ; les liens sont
 * donc ceux validés par le calcul automatique et les décisions manuelles/Copilot.
 * D3 realise la carte ; les barres restent disponibles si sa CDN est inaccessible.
 * Les donnees sont encodees avec encodeJs pour eviter toute fermeture du script JSON.
 */
require_once __DIR__ . '/php/fonctionConcours.php';
require_once __DIR__ . '/php/paysageSpecialites.php';
$paysage = null;
try {
    $paysage = chargerPaysageSpecialitesAffectees(openDatabase());
} catch (PDOException $erreur) {
    http_response_code(503);
    error_log('Paysage des specialites : ' . $erreur->getMessage());
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Panorama des spécialités des écoles d'ingénieur : domaines et répartition des formations.">
    <title>Panorama des spécialités des écoles d'ingénieur</title>
    <?php include __DIR__ . '/php/favicon.php'; include __DIR__ . '/php/style.php'; ?>
    <link rel="stylesheet" href="css/paysageSpecialites.css?v=<?php echo filemtime(__DIR__ . '/css/paysageSpecialites.css'); ?>">
</head>
<body class="page-paysage" id="hautdepage">
    <?php include __DIR__ . '/php/menu.php'; ?>
    <main class="container paysage">

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php" aria-label="Accueil"><i class="bi bi-house-door-fill" aria-hidden="true"></i></a></li>
                <li class="breadcrumb-item">Spécialité</li>
                <li class="breadcrumb-item active" aria-current="page">Panorama</li>
            </ol>
        </nav>

        <header class="paysage-entete">
            <h1 class="h3"><i class="bi bi-diagram-3 me-3" aria-hidden="true"></i>Panorama des spécialités</h1>
        </header>

        <?php if ($paysage === null): ?>
            <p class="alert alert-warning" role="alert">Les données sont temporairement indisponibles. Veuillez réessayer plus tard.</p>
        <?php else: ?>
            <?php
                $domainesParId = array_column($paysage['libelle']['domaines'], null, 'id');
                $nombreSpecialitesNonClassees = count($domainesParId[0]['specialites'] ?? []);
                $libelleSpecialitesNonClassees = $nombreSpecialitesNonClassees === 1 ? ' spécialité non associée' : ' spécialités non associées';
            ?>
            <dl class="paysage-chiffres">
                <div><dt>Spécialités</dt><dd><?php echo number_format($paysage['libelle']['totalSpecialites'], 0, ',', ' '); ?></dd></div>
                <div><dt>Diplômes</dt><dd><?php echo $paysage['libelle']['totalDiplomes']; ?></dd></div>
                <div><dt>Écoles</dt><dd><?php echo $paysage['libelle']['totalEcoles']; ?></dd></div>
                <div><dt>Domaines</dt><dd><?php echo count($paysage['libelle']['domaines']) - 1; ?></dd></div>
            </dl>
            <noscript><p class="alert alert-warning">JavaScript est nécessaire pour afficher les graphiques interactifs.</p></noscript>

            <p class="paysage-indication">Les chiffres indiquent le <strong>nombre de spécialités </strong> pour chaque domaine. Cliquez sur une bulle ou un domaine pour consulter exactement ces spécialités et les écoles qui les proposent.</p>
            <div class="paysage-graphiques">
                <section aria-labelledby="titre-carte">
                    <h2 id="titre-carte" class="h4">Carte des domaines</h2>
                    <div id="carte-domaines" class="paysage-carte"></div>
                    <p id="carte-indisponible" class="alert alert-warning" hidden>La carte est indisponible. Les effectifs restent accessibles dans la répartition ci-contre.</p>
                </section>
                <section aria-labelledby="titre-barres">
                    <div class="paysage-section-entete"><h2 id="titre-barres" class="h4">Répartition par domaine</h2><span>Spécialités distinctes</span></div>
                    <div id="barres-domaines" class="paysage-barres"></div>
                </section>
            </div>

            <!-- Un seul encart visible regroupe la methode et les reserves du referentiel. -->
            <aside class="paysage-methode mt-4 pt-3" aria-labelledby="titre-methode">
                <h2 id="titre-methode" class="h5">Périmètre et méthode de comptage</h2>
                <ul class="text-muted small mb-0">
                    <li>Les spécialités recensées à ce jour sont celles des écoles des concours Agro, CCINP, CentraleSupélec, Ecoles Militaires, Groupe INSA, Mines-Ponts, Mines-Télécom, Polytech, Puissance Alpha, Veto, X-ENS.</li>
                    <li>Les autres écoles seront couvertes progressivement.</li>
                    <li>Les spécialités correspondent aux spécialisations choisies par les étudiants. Elles se distinguent des enseignements fondamentaux et des orientations professionnelles (recherche, conseil, entrepreneuriat, etc.) qui peuvent compléter le parcours.</li>
                    <li>Chaque spécialité est comptée une seule fois par domaine. Une spécialité peut relever de plusieurs domaines. La surface des bulles représente le nombre de spécialités, leur position ne signifiant pas une proximité scientifique.</li>
                    <?php if ($nombreSpecialitesNonClassees > 0): ?>
                        <li>Les graphiques excluent <?php echo number_format($nombreSpecialitesNonClassees, 0, ',', ' ') . $libelleSpecialitesNonClassees; ?> à un domaine.</li>
                    <?php endif; ?>
                    <!-- <li>La recherche porte strictement sur le nom de la spécialité, avec les synonymes propres au domaine et les variantes d'accents et de pluriels, sans utiliser le groupe ni le diplôme. « Non classées » compte les spécialités sans correspondance dans ce référentiel. Ce classement lexical n'évalue pas la qualité des formations.</li> -->
                    <!-- <li>Le clic ouvre la recherche avec les mêmes spécialités que celles comptées dans le domaine du Panorama. Le résumé indique le nombre total de spécialités et d'écoles ; tous les résultats sont affichés dans une seule liste défilante, sans limite à 120. Une sélection de filière CPGE peut ensuite restreindre les résultats.</li> -->
                </ul>
            </aside>

            <div class="text-center mt-4">
                <a href="#hautdepage" class="btn btn-primary btn-sm"><i class="bi bi-arrow-up" aria-hidden="true"></i> Haut de page</a>
            </div>
            <?php // Aucun besoin de transmettre les libelles individuels : la recherche les affiche. ?>
            <script id="donnees-paysage" type="application/json"><?php echo encodeJs($paysage['libelle']); ?></script>
            <script src="https://cdn.jsdelivr.net/npm/d3@7.9.0/dist/d3.min.js" defer></script>
            <script src="js/paysageSpecialites.js?v=<?php echo filemtime(__DIR__ . '/js/paysageSpecialites.js'); ?>" defer></script>
        <?php endif; ?>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" defer></script>
</body>
</html>