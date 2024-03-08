<?php
		// récupération du paramètre de la page actuelle
		$page = "";
		$menu = "";
		if (isset($_GET['page']))  {
			$page = $_GET['page'];
		}
		
		// le nom de la page sur laquelle on est n'est pas passée en paramètre
		// on récupère dans ce cas le nom de la page dans l'URL
		if ($page == "") {
			$pageComplete = basename($_SERVER['REQUEST_URI']);
			$fin = strpos($pageComplete,".");
			$page = substr($pageComplete, 0, $fin);

			if ($page == "statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa") {
				$menu = "filiere";
			} elseif ($page == "statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa") {
				$menu = "ecole";
			} elseif ($page == "resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa") {
				$menu = "filiere";
			} elseif ($page == "resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa") {
				$menu = "ecole";
			} elseif ($page == "detail-resultat-admission-ecole-d-ingenieur-cpge-post-prepa") {
				$menu = "filiere";
			} elseif ($page == "detail-resultat-admission-par-ecole") {
				$menu = "ecole";
			} elseif ($page == "classement-ecole-d-ingenieur") {
				$menu = "classement";
			}

		// si elle est renseignée on récupère le nom de la page passée en paramètre
		} else {
				$menu = $page;
		}
?>

<nav id="navigation" class="navbar fixed-top navbar-expand-md navbar-dark" style="background-color:DarkSlateBlue">
	<div class="container-fluid">
		&nbsp;&nbsp;
		<a class="navbar-brand" href="statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php#"><img src="image/logo.png" width="32" height="23" alt="logo écran avec roue dentelée pour symboliser la technologie" loading="lazy"> &nbsp;Stat concours</a>
		<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-controls="menu" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div id="menu" class="collapse navbar-collapse">
			<ul class="navbar-nav me-auto">
<?php
				echo "<li class='nav-item'>";
				if ($menu == "ecole") {
					echo "<a class='nav-link active' href='statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php' onclick='collapseMenu(this);'>Ecole</a>";
				} else {
					echo "<a class='nav-link' href='statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php' onclick='collapseMenu(this);'>Ecole</a>";
				}
				echo "</li>";

				echo "<li class='nav-item'>";
				if ($menu == "filiere") {
					echo "<a class='nav-link active' href='statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php' onclick='collapseMenu(this);'>Filière</a>";
				} else {
					echo "<a class='nav-link' href='statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php' onclick='collapseMenu(this);'>Filière</a>";
				}
				echo "</li>";

				echo "<li class='nav-item'>";
				if ($menu == "classement") {
					echo "<a class='nav-link active' href='classement-ecole-d-ingenieur.php' onclick='collapseMenu(this);'>Classement</a>";
				} else {
					echo "<a class='nav-link' href='classement-ecole-d-ingenieur.php' onclick='collapseMenu(this);'>Classement</a>";
				}
				echo "</li>";

				echo "<li class='nav-item'>";
				if ($menu == "propos") {
					echo "<a class='nav-link active' href='statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php?page=propos#propos' onclick='collapseMenu(this);'>A propos</a>";
				} else {
					echo "<a class='nav-link' href='statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php?page=propos#propos' onclick='collapseMenu(this);'>A propos</a>";
				}
				echo "</li>";

				echo "<li class='nav-item'>";
				if ($menu == "contact") {
					echo "<a class='nav-link active' href='statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php?page=contact#contact' onclick='collapseMenu(this);'>Contact</a>";
				} else {
					echo "<a class='nav-link' href='statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php?page=contact#contact' onclick='collapseMenu(this);'>Contact</a>";
				}
				echo "</li>";
?>
			</ul>
		</div>
	</div>
</nav>

<script>

	function collapseMenu(element) {

		// collapse du menu burger au click d'une option de menu
 		var monMenu = document.getElementById("menu");
		console.log(monMenu.className);
 		monMenu.classList.remove("show");

		// rend active l'option de menu cliquée quand on reste sur la même page
  		var monOption = document.getElementsByClassName("active")[0];
 		if ((monOption === undefined) || (monOption === null)) {
 			element.classList.add("active");
 		} else {
 			monOption.classList.remove("active");
 			element.classList.add("active");
 		}
	}

</script>