/**
 * Panorama des specialites : carte D3 et barres par nombre de specialites distinctes.
 *
 * Entree : JSON #donnees-paysage, produit par php/paysageSpecialites.php.
 * Le classement sur le libelle seul est calcule cote serveur ; aucun acces SQL ni AJAX.
 * Chaque domaine transmet son identifiant a Recherche pour retrouver exactement ses specialites.
 * Les specialites sans domaine (id 0) sont omises des graphiques et comptees dans une note.
 * Dependances : D3 7.9 pour la carte, ResizeObserver pour le rendu responsive.
 * Sans D3, les barres et leurs liens restent fonctionnels.
 */
(() => {
    'use strict';
    const donnees = JSON.parse(document.getElementById('donnees-paysage').textContent);
    const palette = ['#22766b', '#356e9f', '#a24255', '#70612d', '#5c6487', '#377a44', '#95613b', '#637479'];
    const format = new Intl.NumberFormat('fr-FR');
    /** Retrouve un controle de la page par son identifiant. */
    const element = (id) => document.getElementById(id);
    /** Formate un nombre de specialites avec l'accord du libelle pour les infobulles et les liens. */
    const nombreSpecialites = (nombre) => `${format.format(nombre)} ${nombre === 1 ? 'spécialité' : 'spécialités'}`;
    /** Conserve une couleur stable par domaine, independamment du tri. */
    const couleur = (domaine) => domaine.id === 0 ? '#637479' : palette[(domaine.id - 1) % palette.length];
    /** Cree un element HTML sans interpreter les libelles de la base comme du HTML. */
    const noeud = (balise, texte, classe) => {
        const resultat = document.createElement(balise);
        if (texte !== undefined) resultat.textContent = texte;
        if (classe) resultat.className = classe;
        return resultat;
    };

    /**
    * Construit l'URL avec l'identifiant du domaine, le mot-cle et le lancement
    * automatique sur les memes correspondances que le Panorama.
    * Renvoie null pour l'indicateur sans domaine afin de ne pas lancer une recherche vide.
     */
    function urlRecherche(domaine) {
        if (domaine.id === 0) return null;
        const parametres = new URLSearchParams({ specialite: domaine.nom, rechercher: '1', strict: '1', domaine: String(domaine.id) });
        return `statistique-integration-ecole-d-ingenieur-par-specialite-cpge-post-prepa.php?${parametres}#resultats`;
    }

    /**
    * Dispose les bulles avec d3.pack, leur surface etant proportionnelle au nombre de specialites.
     * Les liens SVG natifs permettent l'ouverture clavier ou dans un nouvel onglet.
     * En cas de CDN indisponible, masque seulement la carte, pas les barres.
     */
    function afficherCarte(domaines) {
        const conteneur = element('carte-domaines');
        conteneur.replaceChildren();
        if (!window.d3) {
            element('carte-indisponible').hidden = false;
            conteneur.hidden = true;
            return;
        }
        const largeur = Math.max(300, conteneur.clientWidth);
        const hauteur = largeur * parseFloat(getComputedStyle(conteneur).getPropertyValue('--paysage-ratio-carte'));
        const racine = d3.hierarchy({ children: domaines.filter((domaine) => domaine.id !== 0 && domaine.specialites.length > 0) })
            .sum((domaine) => domaine.specialites ? domaine.specialites.length : 0)
            .sort((premier, second) => second.value - premier.value);
        d3.pack().size([largeur, hauteur]).padding(5)(racine);
        const svg = d3.select(conteneur).append('svg').attr('viewBox', `0 0 ${largeur} ${hauteur}`)
            .attr('role', 'group').attr('aria-label', 'Domaines par nombre de spécialités');
        const bulles = svg.selectAll('a').data(racine.leaves()).join('a')
            .attr('class', 'paysage-bulle').attr('transform', (bulle) => `translate(${bulle.x},${bulle.y})`)
            .attr('href', (bulle) => urlRecherche(bulle.data))
            .attr('tabindex', (bulle) => bulle.data.id === 0 ? null : 0)
            .attr('role', (bulle) => bulle.data.id === 0 ? 'img' : 'link')
            .attr('aria-label', (bulle) => `${bulle.data.nom} : ${nombreSpecialites(bulle.value)}`);
        bulles.append('title').text((bulle) => `${bulle.data.nom} · ${nombreSpecialites(bulle.value)}`);
        bulles.append('circle').attr('r', (bulle) => bulle.r).attr('fill', (bulle) => couleur(bulle.data));
        /** Dessine le nom court et l'effectif, puis ajuste le bloc a son cercle reel. */
        bulles.each(function dessinerLibelleBulle(bulle) {
            if (bulle.r < 17) return;
            const groupe = d3.select(this);
            const libelleCourt = bulle.data.nom === 'Data / IA' ? bulle.data.nom : bulle.data.nom.split(' / ')[0];
            const mots = libelleCourt.split(' ');
            const motLePlusLong = Math.max(...mots.map((mot) => mot.length));
            const police = Math.max(7, Math.min(11, (bulle.r * 1.65) / (motLePlusLong * .62)));
            const maximum = Math.max(5, Math.floor((bulle.r * 1.65) / (police * .58)));
            const lignes = [];
            let ligne = '';
            for (const mot of mots) {
                if (ligne && `${ligne} ${mot}`.length > maximum) {
                    lignes.push(ligne);
                    ligne = mot;
                } else {
                    ligne = ligne ? `${ligne} ${mot}` : mot;
                }
            }
            if (ligne) lignes.push(ligne);
            const afficherLibelle = bulle.r >= 25 && lignes.length <= 3 && lignes.every((texte) => texte.length <= maximum);
            if (afficherLibelle) {
                const texte = groupe.append('text').attr('font-size', police);
                lignes.forEach((libelle, index) => texte.append('tspan').attr('x', 0).attr('y', (index - lignes.length / 2) * (police + 2)).text(libelle));
            }
            groupe.append('text').attr('class', 'bulle-valeur').attr('y', afficherLibelle ? lignes.length * (police + 2) / 2 + 10 : 6).text(bulle.value);
            // Les dimensions reelles des glyphes evitent de couper les libelles sur mobile.
            const textes = [...this.querySelectorAll('text')];
            const extension = Math.max(...textes.map((texte) => {
                const limites = texte.getBBox();
                return Math.max(...[
                    [limites.x, limites.y], [limites.x + limites.width, limites.y],
                    [limites.x, limites.y + limites.height], [limites.x + limites.width, limites.y + limites.height],
                ].map(([horizontal, vertical]) => Math.hypot(horizontal, vertical)));
            }));
            if (extension > bulle.r - 3) {
                const facteur = (bulle.r - 3) / extension;
                for (const texte of textes) {
                    texte.style.fontSize = `${parseFloat(getComputedStyle(texte).fontSize) * facteur}px`;
                    for (const partie of [texte, ...texte.querySelectorAll('tspan')]) {
                        if (partie.hasAttribute('y')) partie.setAttribute('y', Number(partie.getAttribute('y')) * facteur);
                    }
                }
            }
        });
    }

    /** Affiche les domaines tries par nombre de specialites distinctes, leurs liens et la carte. */
    function afficherGraphiques() {
        const domaines = donnees.domaines
            .filter((domaine) => domaine.id !== 0)
            .sort((premier, second) => second.specialites.length - premier.specialites.length || premier.nom.localeCompare(second.nom, 'fr'));
        const maximum = Math.max(1, ...domaines.map((domaine) => domaine.specialites.length));
        const barres = element('barres-domaines');
        barres.replaceChildren();
        for (const domaine of domaines) {
            const url = urlRecherche(domaine);
            const bouton = noeud(url ? 'a' : 'div', undefined, 'paysage-barre');
            if (url) bouton.href = url;
            bouton.style.setProperty('--domaine-couleur', couleur(domaine));
            bouton.setAttribute('aria-label', `${domaine.nom} : ${nombreSpecialites(domaine.specialites.length)}`);
            bouton.title = nombreSpecialites(domaine.specialites.length);
            const piste = noeud('span', undefined, 'barre-piste');
            piste.setAttribute('aria-hidden', 'true');
            const remplissage = noeud('span', undefined, 'barre-remplissage');
            remplissage.style.width = `${domaine.specialites.length / maximum * 100}%`;
            piste.append(remplissage);
            bouton.append(noeud('span', domaine.nom, 'barre-libelle'), piste, noeud('span', format.format(domaine.specialites.length), 'barre-valeur'));
            barres.append(bouton);
        }
        afficherCarte(domaines);
    }

    let largeurCarte = 0;
    /** Redessine seulement lorsque la largeur change, sans boucle liee a la hauteur du SVG. */
    function observerCarte(entrees) {
        const largeur = Math.round(entrees[0].contentRect.width);
        if (largeur !== largeurCarte) {
            largeurCarte = largeur;
            afficherCarte(donnees.domaines);
        }
    }
    new ResizeObserver(observerCarte).observe(element('carte-domaines'));
    afficherGraphiques();
})();