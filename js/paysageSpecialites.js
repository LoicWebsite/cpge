/**
 * Panorama des specialites : carte D3 et barres par nombre de specialites distinctes.
 *
 * Entree : JSON #donnees-paysage, produit par php/paysageSpecialites.php.
 * Le classement sur le libelle seul est calcule cote serveur ; aucun acces SQL ni AJAX.
 * Chaque domaine transmet son identifiant a Recherche pour retrouver exactement ses specialites.
 * Les specialites sans domaine (id 0) sont omises des graphiques et comptees dans une note.
 * Dependances : D3 7.9 pour la carte, ResizeObserver pour le rendu responsive.
 * Sans D3, les barres et leurs liens restent fonctionnels.
 *
 * Hierarchie des domaines
 * -----------------------
 * Chaque domaine du JSON porte {id, nom, code, idParent, specialites[], ecoles[]}.
 * idParent vaut null pour une racine, sinon l'id du parent (deux niveaux au maximum).
 * Exemple : Informatique (parent, 419) a pour enfants Data / IA (176) et Cybersecurite (72).
 * Un parent est derive cote serveur : son effectif est l'union de ses specialites propres et
 * de celles de ses enfants. Les effectifs d'un parent et de ses enfants se recouvrent donc
 * et ne s'additionnent pas ; la surface d'une bulle parent recouvre en partie celle des enfants.
 * - Carte : une bulle par domaine, comme les entrees du menu de la page Recherche. Chaque famille
 *   (parent + enfants) est une forme rigide : les enfants sont sous le parent, relies par un trait.
 *   Les formes sont rapprochees du centre sans chevauchement (placerFormes), puis la carte est
 *   recadree sur son contenu pour utiliser toute la largeur. Les enfants prennent la couleur du
 *   parent en plus clair ; le parent garde la couleur pleine, sans contour.
 * - Barres : arbre indente. Les racines sont triees par effectif decroissant, chaque parent est
 *   suivi de ses enfants tries par effectif. Le clic ouvre la meme recherche que le menu.
 * - Mise en avant : survoler (ou focaliser) une bulle allume sa ligne dans la liste et dessine une
 *   « loupe » au-dessus de la bulle (disque agrandi avec le nom complet, un composant par ligne, et l effectif ; les autres bulles sont estompees) ;
 *   survoler une ligne fait de meme sur la carte. Sur ecran tactile, le premier appui sur une bulle
 *   l'epingle avec l'invite « Voir › » et le second ouvre la recherche ; sur ordinateur, un clic ouvre
 *   directement la recherche. Les petites bulles, sans libelle, restent ainsi lisibles.
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
    /**
     * Conserve une couleur stable par famille de domaines, independamment du tri.
     * Un enfant reprend la couleur de son parent : couleur(Data / IA) = couleur(Informatique).
     */
    const couleur = (domaine) => domaine.id === 0 ? '#637479' : palette[((domaine.idParent || domaine.id) - 1) % palette.length];
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
     * Prepare les formes a placer sur la carte : une forme par domaine sans parent, ou par famille
     * (parent + enfants visibles). Une forme est un ensemble de cercles {domaine, dx, dy, r}, decales
     * par rapport au centre du parent, qui se deplacent ensemble sans tourner. Dans une famille, le
     * parent est a l'origine et ses enfants sont repartis sous lui, le plus grand au milieu, a
     * `ecart` du bord du parent (place du trait) : de gauche a droite, deuxieme, premier, troisieme...
     * Exemple : Informatique (419) au centre, Data / IA (176) et Cybersécurité (72) en dessous.
     */
    function formesCarte(visibles, rayon, ecart, marge) {
        return visibles.filter((domaine) => !domaine.idParent).map((domaine) => {
            const parent = { domaine, dx: 0, dy: 0, r: rayon(domaine) };
            const enfants = visibles.filter((candidat) => candidat.idParent === domaine.id)
                .sort((premier, second) => second.specialites.length - premier.specialites.length)
                .map((enfant) => ({ domaine: enfant, r: rayon(enfant) }));
            if (enfants.length === 0) return { cercles: [parent] };
            const ordre = [];
            enfants.forEach((enfant, index) => (index % 2 === 0 ? ordre.push(enfant) : ordre.unshift(enfant)));
            const distance = (enfant) => parent.r + enfant.r + ecart;
            // Demi-angle occupe par un enfant autour du parent, avec la marge entre voisins.
            const demiAngle = (enfant) => Math.asin(Math.min(1, (enfant.r + marge / 2) / distance(enfant)));
            let angle = -ordre.reduce((somme, enfant) => somme + 2 * demiAngle(enfant), 0) / 2;
            const cercles = [parent];
            for (const enfant of ordre) {
                angle += demiAngle(enfant);
                cercles.push({ domaine: enfant.domaine, dx: distance(enfant) * Math.sin(angle), dy: distance(enfant) * Math.cos(angle), r: enfant.r });
                angle += demiAngle(enfant);
            }
            return { cercles };
        });
    }

    /**
     * Place les formes en les rapprochant du centre sans chevauchement : une gravite douce vers le
     * centre, des poussees entre cercles qui se touchent, puis quelques tours de poussees seules.
     * Le calcul est deterministe (positions de depart en spirale, pas d'aleatoire) et supprime les
     * grands vides d'un d3.pack, qui range les bulles en cercle dans un carre.
     * `ratio` (hauteur / largeur) allonge ou aplatit l'ensemble : 1 sur ordinateur, 1,3 sur mobile.
     * Retourne les formes avec leur position {x, y} ; chaque cercle est a (x + dx, y + dy).
     */
    function placerFormes(formes, ratio, marge) {
        const rayonEnglobant = (forme) => Math.max(...forme.cercles.map((cercle) => Math.hypot(cercle.dx, cercle.dy) + cercle.r));
        const noeuds = formes.slice().sort((premier, second) => rayonEnglobant(second) - rayonEnglobant(premier));
        const moyenne = noeuds.reduce((somme, forme) => somme + rayonEnglobant(forme), 0) / noeuds.length;
        noeuds.forEach((forme, index) => {
            forme.x = Math.sqrt(index + 0.5) * moyenne * 1.4 * Math.cos(index * 2.399963);
            forme.y = Math.sqrt(index + 0.5) * moyenne * 1.4 * Math.sin(index * 2.399963) * ratio;
        });
        const separer = () => {
            for (let i = 0; i < noeuds.length; i++) {
                for (let j = i + 1; j < noeuds.length; j++) {
                    for (const premier of noeuds[i].cercles) {
                        for (const second of noeuds[j].cercles) {
                            const dx = noeuds[j].x + second.dx - noeuds[i].x - premier.dx;
                            const dy = noeuds[j].y + second.dy - noeuds[i].y - premier.dy;
                            const distance = Math.hypot(dx, dy) || 0.001;
                            const manque = premier.r + second.r + marge - distance;
                            if (manque <= 0) continue;
                            noeuds[i].x -= dx / distance * manque / 2;
                            noeuds[i].y -= dy / distance * manque / 2;
                            noeuds[j].x += dx / distance * manque / 2;
                            noeuds[j].y += dy / distance * manque / 2;
                        }
                    }
                }
            }
        };
        for (let tour = 0; tour < 500; tour++) {
            for (const forme of noeuds) {
                forme.x -= forme.x * 0.06;
                forme.y -= forme.y * 0.06 / ratio;
            }
            separer();
        }
        for (let tour = 0; tour < 80; tour++) separer();
        return noeuds;
    }

    /**
     * Calcule les bulles et les traits de la carte, en pixels, pour une largeur donnee.
     * Les formes sont placees dans un repere libre puis recadrees sur leur contenu : le dessin
     * occupe toute la largeur disponible, sans marge vide, et sa hauteur en decoule.
     * Retourne {bulles: [{data: domaine, value: effectif, r, x, y}], traits: [{couleur, x1, y1, x2, y2}], hauteur}.
     */
    function disposerCarte(visibles, largeur, ratio) {
        const unite = 10;
        const marge = unite;
        const rayon = (domaine) => unite * Math.sqrt(domaine.specialites.length);
        const formes = placerFormes(formesCarte(visibles, rayon, 3 * unite, marge), ratio, marge);
        const cercles = formes.flatMap((forme) => forme.cercles.map((cercle) => ({ ...cercle, x: forme.x + cercle.dx, y: forme.y + cercle.dy })));
        const gauche = Math.min(...cercles.map((cercle) => cercle.x - cercle.r)) - marge / 2;
        const droite = Math.max(...cercles.map((cercle) => cercle.x + cercle.r)) + marge / 2;
        const haut = Math.min(...cercles.map((cercle) => cercle.y - cercle.r)) - marge / 2;
        const bas = Math.max(...cercles.map((cercle) => cercle.y + cercle.r)) + marge / 2;
        const echelle = largeur / (droite - gauche);
        const bulles = cercles.map((cercle) => ({
            data: cercle.domaine, value: cercle.domaine.specialites.length,
            r: cercle.r * echelle, x: (cercle.x - gauche) * echelle, y: (cercle.y - haut) * echelle,
        }));
        const traits = [];
        for (const parent of bulles.filter((bulle) => !bulle.data.idParent)) {
            for (const enfant of bulles.filter((bulle) => bulle.data.idParent === parent.data.id)) {
                const distance = Math.hypot(enfant.x - parent.x, enfant.y - parent.y);
                const ux = (enfant.x - parent.x) / distance;
                const uy = (enfant.y - parent.y) / distance;
                traits.push({
                    couleur: couleur(parent.data),
                    x1: parent.x + ux * parent.r, y1: parent.y + uy * parent.r,
                    x2: enfant.x - ux * enfant.r, y2: enfant.y - uy * enfant.r,
                });
            }
        }
        return { bulles, traits, hauteur: (bas - haut) * echelle };
    }

    /** Nom court affiche dans une bulle : premier composant d'un nom compose, sauf « Data / IA ». */
    const libelleCourt = (nom) => nom === 'Data / IA' ? nom : nom.split(' / ')[0];

    // Mise en avant d'un domaine, partagee entre la carte et la liste (barres).
    // - Survol (souris), focus clavier : mise en avant temporaire, retiree en quittant.
    // - Appui tactile : le premier appui sur une bulle l'epingle (loupe + invite « Voir ›»), le second ouvre
    //   la recherche ; un appui ailleurs annule. Sur ordinateur, un clic ouvre directement la recherche.
    // La mise en avant allume la ligne de la liste ET dessine une loupe sur la bulle : un domaine
    // survole dans la liste montre sa bulle, une bulle survolee montre sa ligne.
    const RAYON_LOUPE = 46; // rayon minimal de la loupe : nom et effectif y restent lisibles
    const RAYON_LOUPE_MAX = 72; // plafond pour les noms longs (4 composants et plus)
    const LARGEUR_LIGNE_LOUPE = 16; // caracteres maximum par ligne du nom dans la loupe
    let actif = null; // id du domaine mis en avant, ou null
    let epingle = false; // true apres un appui tactile
    let dessinCarte = null; // {svg, conteneur, disposition, largeur, hauteur} de la carte affichee
    let dernierTactile = false; // le dernier appui venait d'un ecran tactile

    /**
     * Lignes du nom complet d'un domaine pour la loupe : un composant par ligne, coupe si trop long.
     * Exemple : « Génie industriel / Conception / Logistique / QHSE » donne [Génie industriel, Conception,
     * Logistique, QHSE] ; « Finance d'entreprise » donne [Finance, d'entreprise] ; « Data / IA » reste en une ligne.
     */
    function lignesNomComplet(nom) {
        const lignes = [];
        for (const composant of nom === 'Data / IA' ? [nom] : nom.split(' / ')) {
            let ligne = '';
            for (const mot of composant.split(' ')) {
                if (ligne && `${ligne} ${mot}`.length > LARGEUR_LIGNE_LOUPE) {
                    lignes.push(ligne);
                    ligne = mot;
                } else {
                    ligne = ligne ? `${ligne} ${mot}` : mot;
                }
            }
            lignes.push(ligne);
        }
        return lignes;
    }

    /**
     * Dessine la loupe : un disque de la couleur du domaine, plus grand que la bulle, avec le nom complet
     * (un composant par ligne), l'effectif et, apres un appui tactile, l'invite « Voir › ».
     * Le rayon s'adapte au texte : RAYON_LOUPE au minimum, RAYON_LOUPE_MAX au plus (nom le plus long :
     * environ 70 px, soit 140 px de diametre). Elle est dessinee au-dessus des autres bulles et recentree
     * vers l'interieur de la carte si la bulle est au bord, sans jamais decouvrir la bulle d'origine.
     * Au survol elle laisse passer la souris (pointer-events: none, sans clignotement) ;
     * epinglee, elle devient un lien vers la recherche.
     */
    function dessinerLoupe(bulle, tactile) {
        const { svg, largeur, hauteur } = dessinCarte;
        // Bloc de texte : lignes du nom (13 px chacune), effectif (24 px) et, au toucher, invite (14 px).
        const lignes = lignesNomComplet(bulle.data.nom);
        const hauteurBloc = lignes.length * 13 + 24 + (tactile ? 14 : 0);
        const largeurBloc = Math.max(...lignes.map((texte) => texte.length)) * 6.6;
        const besoin = Math.hypot(largeurBloc / 2, hauteurBloc / 2) + 6;
        // Le plafond ne vaut que pour le texte : une grosse bulle reste recouverte (rayon x 1,1).
        const rayon = Math.max(bulle.r * 1.1, Math.min(RAYON_LOUPE_MAX, Math.max(RAYON_LOUPE, besoin)));
        const decalageMax = Math.max(0, rayon - bulle.r - 2);
        const recentrer = (valeur, borne) => {
            const voulu = Math.min(Math.max(valeur, rayon), borne - rayon);
            return valeur + Math.max(-decalageMax, Math.min(decalageMax, voulu - valeur));
        };
        const url = tactile ? urlRecherche(bulle.data) : null;
        const racine = url ? svg.append('a').attr('class', 'paysage-loupe-lien').attr('href', url).attr('tabindex', -1) : svg;
        const groupe = racine.append('g').attr('class', 'paysage-loupe' + (url ? ' paysage-loupe-epinglee' : ''))
            .attr('transform', `translate(${recentrer(bulle.x, largeur)},${recentrer(bulle.y, hauteur)})`).attr('aria-hidden', 'true');
        groupe.append('circle').attr('r', rayon).attr('fill', couleur(bulle.data));
        let y = -hauteurBloc / 2 + 11;
        for (const texte of lignes) {
            groupe.append('text').attr('class', 'loupe-nom').attr('y', y).text(texte);
            y += 13;
        }
        groupe.append('text').attr('class', 'loupe-valeur').attr('y', y + 13).text(format.format(bulle.value));
        if (tactile) groupe.append('text').attr('class', 'loupe-invite').attr('y', y + 28).text('Voir ›');
    }

    /** Retire la mise en avant : ligne de la liste, loupe et estompage des autres bulles. */
    function effacerMiseEnAvant() {
        if (actif === null) return;
        const barre = element('barres-domaines').querySelector('.paysage-barre-active');
        if (barre) barre.classList.remove('paysage-barre-active');
        if (dessinCarte) {
            dessinCarte.svg.selectAll('.paysage-loupe-lien').remove();
            dessinCarte.svg.selectAll('.paysage-loupe').remove();
            dessinCarte.svg.selectAll('.paysage-bulle').classed('paysage-bulle-active', false);
            dessinCarte.conteneur.classList.remove('carte-focus');
        }
        actif = null;
        epingle = false;
    }

    /** Met un domaine en avant (survol, focus ou, avec `pin`, appui tactile), en remplacant l'eventuel precedent. */
    function mettreEnAvant(id, pin) {
        effacerMiseEnAvant();
        actif = id;
        epingle = pin;
        const barre = element('barres-domaines').querySelector(`[data-domaine="${id}"]`);
        if (barre) barre.classList.add('paysage-barre-active');
        if (!dessinCarte) return;
        const bulle = dessinCarte.disposition.find((candidate) => candidate.data.id === id);
        if (!bulle) return;
        dessinCarte.conteneur.classList.add('carte-focus');
        dessinCarte.svg.selectAll('.paysage-bulle').classed('paysage-bulle-active', (candidate) => candidate.data.id === id);
        dessinerLoupe(bulle, pin);
    }

    /** Fin d'un survol ou d'un focus : retire la mise en avant, sauf si un appui tactile l'a epinglee. */
    function quitterMiseEnAvant(id) {
        if (actif === id && !epingle) effacerMiseEnAvant();
    }

    /**
     * Dessine la carte : une bulle par domaine, de surface proportionnelle au nombre de specialites.
     * Les liens SVG natifs permettent l'ouverture clavier ou dans un nouvel onglet.
     * Les familles (parent + enfants) restent groupees et reliees par des traits legers.
     * Chaque bulle declenche la mise en avant (survol, focus, premier appui tactile).
     * En cas de CDN indisponible, masque seulement la carte, pas les barres.
     */
    function afficherCarte(domaines) {
        const conteneur = element('carte-domaines');
        effacerMiseEnAvant();
        dessinCarte = null;
        conteneur.replaceChildren();
        if (!window.d3) {
            element('carte-indisponible').hidden = false;
            conteneur.hidden = true;
            return;
        }
        const largeur = Math.max(300, conteneur.clientWidth);
        const ratio = parseFloat(getComputedStyle(conteneur).getPropertyValue('--paysage-ratio-carte'));
        const visibles = domaines.filter((domaine) => domaine.id !== 0 && domaine.specialites.length > 0);
        const { bulles: disposition, traits, hauteur } = disposerCarte(visibles, largeur, ratio);
        const svg = d3.select(conteneur).append('svg').attr('viewBox', `0 0 ${largeur} ${hauteur}`)
            .attr('role', 'group').attr('aria-label', 'Domaines par nombre de spécialités');
        svg.selectAll('line').data(traits).join('line').attr('class', 'paysage-trait')
            .attr('x1', (trait) => trait.x1).attr('y1', (trait) => trait.y1)
            .attr('x2', (trait) => trait.x2).attr('y2', (trait) => trait.y2).attr('stroke', (trait) => trait.couleur);
        const bulles = svg.selectAll('a').data(disposition).join('a')
            .attr('class', (bulle) => 'paysage-bulle' + (bulle.data.idParent ? ' paysage-bulle-enfant' : ''))
            .attr('transform', (bulle) => `translate(${bulle.x},${bulle.y})`)
            .attr('href', (bulle) => urlRecherche(bulle.data))
            .attr('tabindex', (bulle) => bulle.data.id === 0 ? null : 0)
            .attr('role', (bulle) => bulle.data.id === 0 ? 'img' : 'link')
            .attr('aria-label', (bulle) => `${bulle.data.nom} : ${nombreSpecialites(bulle.value)}`);
        dessinCarte = { svg, conteneur, disposition, largeur, hauteur };
        // Pointeur souris ou stylet : survol. Tactile : le premier appui epingle la loupe, le second ouvre le lien.
        bulles.on('pointerenter', (evenement, bulle) => {
            if (evenement.pointerType !== 'touch') mettreEnAvant(bulle.data.id, false);
        }).on('pointerleave', (evenement, bulle) => quitterMiseEnAvant(bulle.data.id))
            .on('focus', (evenement, bulle) => {
                if (!dernierTactile) mettreEnAvant(bulle.data.id, false);
            }).on('blur', (evenement, bulle) => quitterMiseEnAvant(bulle.data.id))
            .on('click', (evenement, bulle) => {
                if (!dernierTactile || (actif === bulle.data.id && epingle)) return;
                evenement.preventDefault();
                mettreEnAvant(bulle.data.id, true);
            });
        bulles.append('circle').attr('r', (bulle) => bulle.r).attr('fill', (bulle) => couleur(bulle.data));
        /** Dessine le nom court et l'effectif, puis ajuste le bloc a son cercle reel. */
        bulles.each(function dessinerLibelleBulle(bulle) {
            if (bulle.r < 17) return;
            const groupe = d3.select(this);
            const mots = libelleCourt(bulle.data.nom).split(' ');
            const motLePlusLong = Math.max(...mots.map((mot) => mot.length));
            const police = Math.max(7, Math.min(11, (bulle.r * 1.65) / (motLePlusLong * .62)));
            const maximum = Math.max(5, Math.floor((bulle.r * 2) / (police * .58)));
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

    /**
     * Ordonne les domaines en arbre : racines par effectif decroissant (puis nom), chaque parent
     * suivi de ses enfants par effectif decroissant. Retourne des lignes {domaine, enfant}.
     * Exemple : Informatique 419, Data / IA 176, Cybersécurité 72, Génie industriel 208, ...
     * (le tri global ne vaut que pour les racines : un enfant reste sous son parent).
     */
    function lignesBarres(domaines) {
        const parEffectif = (premier, second) => second.specialites.length - premier.specialites.length || premier.nom.localeCompare(second.nom, 'fr');
        const visibles = domaines.filter((domaine) => domaine.id !== 0);
        const lignes = [];
        for (const racine of visibles.filter((domaine) => !domaine.idParent).sort(parEffectif)) {
            lignes.push({ domaine: racine, enfant: false });
            for (const enfant of visibles.filter((domaine) => domaine.idParent === racine.id).sort(parEffectif)) {
                lignes.push({ domaine: enfant, enfant: true });
            }
        }
        return lignes;
    }

    /** Affiche l'arbre des domaines avec leurs liens, puis la carte. */
    function afficherGraphiques() {
        const lignes = lignesBarres(donnees.domaines);
        const maximum = Math.max(1, ...lignes.map((ligne) => ligne.domaine.specialites.length));
        const parentsAvecEnfants = new Set(lignes.filter((ligne) => ligne.enfant).map((ligne) => ligne.domaine.idParent));
        const barres = element('barres-domaines');
        barres.replaceChildren();
        for (const { domaine, enfant } of lignes) {
            const url = urlRecherche(domaine);
            const bouton = noeud(url ? 'a' : 'div', undefined, 'paysage-barre' + (enfant ? ' paysage-barre-enfant' : '') + (parentsAvecEnfants.has(domaine.id) ? ' paysage-barre-parent' : ''));
            if (url) bouton.href = url;
            bouton.dataset.domaine = String(domaine.id);
            // Survol ou focus d'une ligne : la bulle correspondante est mise en avant sur la carte.
            bouton.addEventListener('pointerenter', (evenement) => {
                if (evenement.pointerType !== 'touch') mettreEnAvant(domaine.id, false);
            });
            bouton.addEventListener('pointerleave', () => quitterMiseEnAvant(domaine.id));
            bouton.addEventListener('focus', () => {
                if (!dernierTactile) mettreEnAvant(domaine.id, false);
            });
            bouton.addEventListener('blur', () => quitterMiseEnAvant(domaine.id));
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
        afficherCarte(donnees.domaines);
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
    // Type du dernier appui (capture, avant les autres gestionnaires) et annulation d'un appui tactile epingle
    // par un appui hors d'une bulle ou de sa loupe.
    document.addEventListener('pointerdown', (evenement) => { dernierTactile = evenement.pointerType === 'touch'; }, true);
    document.addEventListener('click', (evenement) => {
        if (epingle && !evenement.target.closest('.paysage-bulle, .paysage-loupe-lien')) effacerMiseEnAvant();
    });
    afficherGraphiques();
})();