<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Liste des écoles classées par attractivité selon les étudiants">

    <?php
        include "php/favicon.php";
    ?>
    <title>Attractivité des ecoles d'ingénieurs</title>
    <?php
        include "php/style.php";
    ?>
	<style>
		th, td {font-size:100%}
	</style>
  </head>
  <body id="hautdepage">

    <?php
        include "php/menu.php";
    ?>

    <?php
        include "php/controleParametre.php";
        include "php/fonctionConcours.php";

        $tri = "attractivite";
        if (isset($_GET['tri'])) {
            if (in_array($_GET['tri'], ['ecole', 'attractivite', 'effectif'])) {
                $tri = $_GET['tri'];
            }
        }

        // récupération des données selon l'ordre choisi
        try {
            $db = openDatabase();

            if ($tri == 'ecole') {
                $order = 'ORDER BY Formation ASC';
            } elseif ($tri == 'effectif') {
                $order = 'ORDER BY Effectif DESC';
            } else {
                // attractivite
                $order = 'ORDER BY Attractivite DESC';
            }

            $sql = "SELECT 
                        Formation, 
                        Effectif, 
                        Attractivite,
                        Rang
                    FROM Attractivite " . $order;
            $result = $db->query($sql);
        } catch (PDOException $e) {
            // table missing or other error
            $msg = $e->getMessage();
            if (stripos($msg, 'attractivite') !== false && stripos($msg, 'doesn\'t exist') !== false) {
                echo "<div class='container alert alert-danger' style='margin-top:80px;'>";
                echo "Table <strong>Attractivite</strong> introuvable&nbsp;!<br/>";
                echo "Importez le fichier <code>data/Attractivité/Attractivite.sql</code> dans la base <code>cpge</code>.<br/>";
                echo "(utilisez phpMyAdmin ou la ligne de commande MySQL.)";
                echo "</div>";
                exit;
            }
            die('Erreur connexion base : ' . $e->getMessage());
        }
    ?>

    <nav class="container" style='margin-top:80px;' aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php">
                <i class="bi bi-house-door-fill"></i>
            </a>
        </li>
        <li class="breadcrumb-item">Palmarès</li>
        <li class="breadcrumb-item active" aria-current="page">Attractivité</li>
      </ol>
    </nav>

    <header class="container">
        <h1 class="h3"><i class="bi bi-clipboard2-heart"></i>&nbsp;&nbsp;&nbsp;Attractivité des écoles d'ingénieurs post prépa<br><small>(du site <a href="https://www.daur-rankings.com" target="_blank" rel="noopener">DAUR</a>)</small></h1>
        <br/>
    
		<p>
		  <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#detail" onclick="changerTexte(this)">
			+  Voir l'explication
		  </button>
		</p>
		
		<div class="collapse" id="detail" style="margin-left:20px;">
			<p>
                Pour établir le score d'attractivité, le site DAUR a analysé les désistements de 385 formations dans 177 écoles. Les données anlysées sont celles des concours 2024.<br>
                Le score d’attractivité représente la préférence entre paires d’écoles. Ce qui est analysé n’est pas la sélectivité brute d’une école, mais le fait que, lorsqu’un étudiant a le choix entre deux écoles, il choisit l’une plutôt que l’autre.
                Les explications détaillées de la méthodologie et des résultats sont disponibles dans le très bon article de DAUR : <a href="https://www.daur-rankings.com/blog/attractivity-engineering-2025" target="_blank" rel="noopener">https://www.daur-rankings.com/blog/attractivity-engineering-2025</a><br> 
            </p>
			<br>
		</div>
    </header>

    <main class="container">
        <table id="tableau-attractivite">
            <caption style='caption-side:top;'><small>Double cliquer &nbsp;<i class='bi bi-cursor-fill' aria-hidden='true'></i>&nbsp; sur une ligne pour voir le détail de cette école.
            <br>Cliquer sur le bouton pour télécharger le tableau au format CSV : </small><button type="button" class="btn btn-secondary btn-sm" onclick="tableToCSV('#tableau-attractivite','rang;formation;effectif;attractivité')"><i class="bi bi-download"></i> csv</button></caption>
            <thead class="text-center">
                <tr>
                    <th>&nbsp;Rang&nbsp;
                        <br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Les formations qui ont le même score d&apos;attractivité on le même rang.'></i>
                    </th>
                    <th>&nbsp;<button id="ecole" type="button" class="btn btn-secondary btn-sm" title="Trier par école" onclick="triEcole()">&darr;</button>&nbsp;&nbsp;Formation&nbsp;
                        <br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Une même école peut offrir plusieurs formations, par exemple en statut étudiant ou en apprentissage. Ce sont les formations qui sont évaluées et non pas seulement l&apos;école.'></i>
                    </th>
                    <th>&nbsp;<button id="effectif" type="button" class="btn btn-secondary btn-sm" title="Trier par effectif" onclick="triEffectif()">&darr;</button>&nbsp;&nbsp;Effectif&nbsp;
                        <br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Les effectifs sont ceux aux concours 2024.'></i>
                    </th>
                    <th>&nbsp;<button id="attr" type="button" class="btn btn-secondary btn-sm" title="Trier par attractivité" onclick="triAttractivite()">&darr;</button>&nbsp;&nbsp;Attractivité&nbsp;
                        <br><i class='bi bi-info-circle-fill' data-bs-toggle='tooltip' data-bs-html='true' title='Le score d’attractivité mesure la préférence relative entre écoles en observant laquelle est choisie lorsqu’un étudiant est admis dans plusieurs, plutôt que leur sélectivité intrinsèque.'></i>
                    </th>
                </tr>
            </thead>
            <tbody>
            <?php
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    extract($row);
                    // utiliser la fonction qui extrait le nom d'école adapté pour la recherche
                    echo '<tr ondblclick="zoom(&apos;'.supprimerApostrophe(extraireNomEcolePourRecherche($Formation)).'&apos;)">';
                    echo "<td style='text-align:center'>" . htmlspecialchars($Rang, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . "</td>";
                    echo "<td style='padding-left:10px'><strong>" . htmlspecialchars($Formation, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . "</strong></td>";
                    echo "<td style='text-align:center'>" . htmlspecialchars($Effectif, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . "</td>";
                    echo "<td style='text-align:center'>" . htmlspecialchars($Attractivite, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8') . "</td>";
                    echo "</tr>";
                }
            ?>
            </tbody>
        </table>
        <div class="text-center mt-2">
            <br>
            <a href="#hautdepage" class="btn btn-primary btn-sm"><i class="bi bi-arrow-up"></i> Haut de page</a>
        </div>
    </main>

    <footer style='margin-top:40px; margin-bottom:80px;'>&nbsp;</footer>

    <?php
        include "php/librairie.php";
    ?>

<!-- activation tooltip Bootstrap 5 -->
   	<script>
			var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
			var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
				return new bootstrap.Tooltip(tooltipTriggerEl)
			})		
	</script>

    <script>
        // pour zoomer sur une école
        function zoom(ecole) {
            <?php
                echo "window.location.href='resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php?origine=attractivite&recherche=' + ecole";
            ?>
        }
        function triEcole() {
            window.location.href = 'statistique-attractivite-ecoles.php?tri=ecole';
        }
        function triEffectif() {
            window.location.href = 'statistique-attractivite-ecoles.php?tri=effectif';
        }
        function triAttractivite() {
            window.location.href = 'statistique-attractivite-ecoles.php?tri=attractivite';
        }
    
  		// pour changer le texte et le symbole du bouton détail
		function changerTexte(bouton) {
			if (bouton.innerText.indexOf("Voir") == -1) {
				bouton.innerText = "+  Voir l'explication";
			} else {
				bouton.innerText = "-  Masquer l'explication";
			}
		}
    </script>

    <script src="js/tableToCSV.js"></script>

  </body>
</html>