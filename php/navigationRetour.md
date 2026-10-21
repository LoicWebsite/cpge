# Navigation de retour

Les fiches et les resultats par ecole utilisent
[navigationRetour.php](./navigationRetour.php) pour produire un lien de retour
explicite, sans `history.back()`.

## Validation des destinations

Le parametre `retour` contient une URL relative vers une page de liste ou de
criteres connue. Seuls les parametres autorises pour cette page et une ancre
simple sont acceptes. Les destinations externes ou invalides sont refusees et
journalisees ; les criteres disponibles fournissent alors un retour local.
Les anciens liens peuvent recuperer les criteres du referent de meme origine.
Un retour imbrique conserve la chaine recherche -> liste -> fiche.

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
