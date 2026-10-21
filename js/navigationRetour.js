(function () {
    'use strict';

    // Enrichit les liens PHP avec l'état visible au départ, sans stockage local
    // ni dépendance à history.back(). La destination reste contrôlée côté PHP.
    var pagesSources = [
        'resultat-d-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php',
        'resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php',
        'statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php',
        'statistique-integration-ecole-d-ingenieur-par-filiere-cpge-post-prepa.php',
        'statistique-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php',
        'classement-ecole-d-ingenieur.php',
        'statistique-attractivite-ecoles.php',
        'salaire-ingenieur-cpge-post-prepa.php'
    ];
    var pageCourante = window.location.pathname.split('/').pop();
    var parametres = new URLSearchParams(window.location.search);
    var lireEtat = function () { return {}; };
    var position = Number(parametres.get('positionRetour'));
    var positionRestauree = false;

    function restaurerPosition() {
        if (positionRestauree || !parametres.has('positionRetour') || !Number.isFinite(position) || position < 0) return;
        // Un accordéon en cours d'ouverture modifie encore la hauteur de la page.
        if (document.querySelector('.collapsing')) {
            document.addEventListener('shown.bs.collapse', restaurerPosition, { once: true });
            return;
        }
        positionRestauree = true;
        window.requestAnimationFrame(function () { window.scrollTo(0, position); });
    }

    function urlCourante() {
        // L'URL seule peut être périmée : les tris, filtres AJAX et formulaires
        // sont relus au départ pour retrouver le dernier état réellement affiché.
        var url = new URL(window.location.href);
        var etat = lireEtat();
        if (document.forms.critere) {
            new FormData(document.forms.critere).forEach(function (valeur, cle) {
                if (typeof valeur === 'string') etat[cle] = valeur;
            });
        }
        Object.keys(etat).forEach(function (cle) {
            if (etat[cle] === '' || etat[cle] === null) url.searchParams.delete(cle);
            else url.searchParams.set(cle, etat[cle]);
        });
        url.searchParams.set('positionRetour', String(Math.round(window.scrollY)));
        var panneaux = Array.from(document.querySelectorAll('.accordion-collapse.show[id]')).map(function (element) { return element.id; });
        if (panneaux.length) url.searchParams.set('panneaux', panneaux.join(','));
        else url.searchParams.delete('panneaux');
        var onglet = document.querySelector('.nav-tabs .nav-link.active[data-bs-target]');
        if (onglet) url.hash = onglet.getAttribute('data-bs-target');
        return pageCourante + url.search + url.hash;
    }

    function urlDestination(destination) {
        var url = new URL(destination, window.location.href);
        if (pagesSources.indexOf(pageCourante) === -1 || url.origin !== window.location.origin) return destination;
        var cible = url.pathname.split('/').pop();
        if (cible !== 'detail-resultat-admission-par-ecole.php'
            && cible !== 'detail-resultat-admission-ecole-d-ingenieur-cpge-post-prepa.php'
            && cible !== 'resultat-d-integration-ecole-d-ingenieur-par-ecole-cpge-post-prepa.php') return destination;
        // L'URL courante garde son éventuel retour : on préserve ainsi plusieurs niveaux.
        url.searchParams.set('retour', urlCourante());
        return url.href;
    }

    // La capture prépare le lien avant les gestionnaires de la page et la navigation
    // native, y compris le clic molette. Un vrai lien de retour ne doit pas être enrichi.
    ['click', 'auxclick'].forEach(function (evenement) {
        document.addEventListener(evenement, function (event) {
            var lien = event.target.closest('a[href]');
            if (!lien || lien.hasAttribute('data-retour-navigation')) return;
            lien.href = urlDestination(lien.href);
        }, true);
    });
    document.addEventListener('DOMContentLoaded', function () {
        if (pagesSources.indexOf(pageCourante) === -1 || typeof bootstrap === 'undefined') return;
        // Les identifiants d'onglet et de panneaux ne ciblent que des composants existants.
        document.querySelectorAll('.nav-tabs [data-bs-target]').forEach(function (bouton) {
            if (bouton.getAttribute('data-bs-target') === window.location.hash) bootstrap.Tab.getOrCreateInstance(bouton).show();
        });
        (parametres.get('panneaux') || '').split(',').forEach(function (id) {
            var panneau = document.getElementById(id);
            if (panneau && panneau.classList.contains('accordion-collapse')) bootstrap.Collapse.getOrCreateInstance(panneau, { toggle: false }).show();
        });
    });
    window.addEventListener('load', function () {
        // Ces deux pages attendent leurs résultats AJAX avant d'appeler la restauration.
        if (pageCourante !== 'salaire-ingenieur-cpge-post-prepa.php'
            && pageCourante !== 'statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php') restaurerPosition();
    });
    // Points d'intégration : état propre à une page, double-clic et fin de rendu AJAX.
    window.NavigationRetour = {
        configurer: function (lecture) { lireEtat = lecture; },
        urlDestination: urlDestination,
        restaurerPosition: restaurerPosition
    };
})();
