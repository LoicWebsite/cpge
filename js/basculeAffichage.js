/**
 * Bascule manuelle "en liste" / "en tableau" / "auto", indépendante de la
 * largeur d'écran.
 *
 * "auto" (comportement par défaut, aucune préférence enregistrée) applique le
 * comportement responsive habituel du site (tableau desktop / cartes mobile
 * via les classes Bootstrap d-none/d-lg-none). "liste" et "tableau" forcent
 * l'affichage correspondant quelle que soit la taille de l'écran, mémorisé
 * (localStorage) et appliqué sur toutes les pages qui incluent ce script.
 *
 * Utilisation côté HTML/PHP :
 *   - conteneur du tableau desktop : ajouter la classe "vue-tableau"
 *   - conteneur des cartes mobile   : ajouter la classe "vue-cartes"
 *   - boutons de bascule : onclick="choisirVue('liste'|'tableau'|'auto')"
 *     avec les classes "bouton-vue-liste" / "bouton-vue-tableau" /
 *     "bouton-vue-auto" pour la mise à jour visuelle de l'état actif.
 *   Le bouton représentant le mode actuellement actif est désactivé
 *   (inutile de recliquer dessus) ; les deux autres restent cliquables.
 */
(function () {
	var CLE_STOCKAGE = 'affichageResultatsPreference';

	function appliquerPreference(preference) {
		// "auto" = pas de préférence explicite mémorisée : comportement
		// responsive par défaut (aucune classe .forcer-* sur le <body>).
		if (preference !== 'liste' && preference !== 'tableau') {
			preference = 'auto';
		}

		document.body.classList.remove('forcer-tableau', 'forcer-liste');
		if (preference === 'tableau') {
			document.body.classList.add('forcer-tableau');
		} else if (preference === 'liste') {
			document.body.classList.add('forcer-liste');
		}

		var groupes = {
			liste: document.querySelectorAll('.bouton-vue-liste'),
			tableau: document.querySelectorAll('.bouton-vue-tableau'),
			auto: document.querySelectorAll('.bouton-vue-auto'),
		};
		Object.keys(groupes).forEach(function (mode) {
			var estActif = (mode === preference);
			groupes[mode].forEach(function (bouton) {
				bouton.classList.toggle('btn-primary', estActif);
				bouton.classList.toggle('btn-secondary', !estActif);
				bouton.disabled = estActif;
			});
		});
	}

	function choisirVue(preference) {
		try {
			if (preference === 'auto') {
				localStorage.removeItem(CLE_STOCKAGE);
			} else {
				localStorage.setItem(CLE_STOCKAGE, preference);
			}
		} catch (erreur) {
			// stockage indisponible (navigation privée, quota...) : la préférence
			// ne sera pas mémorisée d'une page à l'autre, mais reste appliquée ici.
		}
		appliquerPreference(preference);
	}
	window.choisirVue = choisirVue;

	document.addEventListener('DOMContentLoaded', function () {
		var preference = 'auto';
		try {
			preference = localStorage.getItem(CLE_STOCKAGE) || 'auto';
		} catch (erreur) {
			// ignoré : pas de préférence à restaurer
		}
		appliquerPreference(preference);
	});
})();
