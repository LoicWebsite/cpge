# Navigation de retour

Les fiches et les resultats par ecole utilisent
[navigationRetour.php](./navigationRetour.php) pour produire un lien de retour
explicite, sans `history.back()`.

## Validation des destinations

Le parametre `retour` contient une URL relative vers une page de liste ou de
criteres connue. Seuls les parametres autorises pour cette page et une ancre
simple sont acceptes. Les destinations externes ou invalides sont refusees et
journalisees ; les criteres disponibles fournissent alors un retour local.
La ligne de journal precise le motif du refus (parametre non autorise, page
inconnue, imbrication...), puis `page`, `retour`, `referer` et `ua`, tronques et
limites a l'ASCII visible. Un `retour` refuse sans `referer` interne evoque un
robot ou un lien partage altere ; un parametre comme `utm_source` indique un
defaut du site.
Les anciens liens peuvent recuperer les criteres du referent de meme origine.
Un retour imbrique conserve la chaine recherche -> liste -> fiche.

## Page de méthode de l'estimation

La fiche école (`detail-resultat-admission-par-ecole.php`) est aussi une destination
de retour valide, avec ses seuls paramètres (`ecole`, `origine`, `diplome`, `filiere`,
`concours`, `reference`, `specialite`, `strict`), une ancre `#onglet-xx` et un retour imbriqué.
Le lien « Comment cette estimation est-elle calculée ? » de l'onglet Admissions
(`a[data-methode-estimation]`, fragment AJAX) reçoit au clic, via `php/detail.php`, un
`retour` égal à la fiche courante, onglet filière compris. La page
`estimation-rang-dernier-integre-ecole-d-ingenieur.php` valide ce `retour` et affiche
« Retour à la fiche école » (`libelleRetourNavigation()`, sans le nom de l'école) en bas de page ; le lien « Ecole » du fil d'Ariane (accueil / Ecole / Rang du dernier admis) mène à la même destination.
Sans retour valide (entrée directe, lien partagé, URL externe refusée et journalisée),
le bouton ramène à l'accueil et le lien « Ecole » du fil d'Ariane à la liste des écoles.

Les pages de liste (par filière, par école) renvoient vers cette même page de méthode
avec le lien « Comment est-il estimé ? » : `navigationRetour.js` lui ajoute le `retour`
(liste courante avec tri et position) comme pour une fiche. Le fil d'Ariane de la page
affiche alors « Filière » au lieu de « Ecole » quand on vient d'une liste par filière.

## Conservation du contexte

[navigationRetour.js](../js/navigationRetour.js) complete les liens au moment du
clic et les navigations par double-clic via `NavigationRetour.urlDestination()`.
Les pages declarent leur etat dynamique avec `NavigationRetour.configurer()` :
recherche, tris, promotion, horizon, etablissement et filtre. Les onglets,
accordeons et la position verticale sont aussi transmis, sans stockage dans le
navigateur. Les pages AJAX restaurent la position apres avoir affiche leurs
resultats.

Les liens de retour portent `data-retour-navigation` pour ne pas etre traites
comme de nouveaux liens vers une fiche. Les liens rendus par PHP restent
utilisables sans JavaScript ; l'etat purement dynamique necessite JavaScript.

## Validation

Tests sans base de donnees, a lancer depuis la racine du site :

```sh
php php/tests/navigationRetour_test.php
```

Verifications navigateur :

- Liste par filiere et liste par ecole.
- Recherche par specialite avec domaine et filiere.
- Classement avec onglet et attractivite avec tri.
- Salaires avec promotion, horizon et etablissement.
- Deux retours consecutifs : fiche -> liste -> recherche.
- Entree directe, nouvel onglet et retour apres une ancre de la fiche.
