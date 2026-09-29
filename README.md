# Statistiques d'admission aux écoles d'ingénieurs après une CPGE scientifique

Ce dépôt présente le projet et son fonctionnement :

**https://loic.website/CPGE/statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php**

Ce site aide les étudiants des classes préparatoires scientifiques à comparer les écoles d'ingénieurs accessibles après les concours.

L'objectif est de rassembler dans une même interface des données provenant de différentes sources afin de permettre aux étudiants d'explorer les possibilités d'admission en fonction de leur filière de CPGE, de leur spécialité et de leur rang.

---

## Contenu du site

Le site permet notamment de consulter :

- les statistiques d'admission aux écoles d'ingénieurs,
- les rangs des derniers intégrés lorsque cette information est disponible,
- les données par année,
- les résultats selon la filière de CPGE,
- les spécialités proposées par les écoles,
- différents classements d'écoles,
- des statistiques permettant de comparer les écoles et les spécialités,
- les salaires des diplômés des écoles d'ingénieurs entre 12 et 30 mois après l'obtention de leur diplôme.

Les données d'admission actuellement disponibles couvrent les années **2016 à 2025**.

---

## Sources des données

Les données présentées sur le site sont recueillies auprès de différentes sources publiques.

### Données d'admission

Les statistiques d'admission proviennent principalement du **Service des Concours des Écoles d'Ingénieurs (SCEI)**.

Elles sont complétées, lorsque cela est possible, par les rapports et documents publiés par les différents concours, notamment :

- Concours Polytechnique,
- Concours CentraleSupélec,
- Concours Mines-Ponts,
- Concours Mines-Télécom,
- Concours Agro-Veto-Bio.

### Classements

Le site peut également utiliser des données provenant de différents classements publiés dans la presse ou par des organismes spécialisés.

Ces classements sont utilisés comme indicateurs complémentaires et ne constituent pas des données d'admission.

### Données sur les écoles

Les informations relatives aux écoles et à leurs spécialités sont recueillies à partir des informations publiques disponibles, notamment sur les sites des écoles.

### Données d'insertion et de salaire

Les données relatives au salaire des diplômés proviennent du dispositif **InserSup** du Ministère de l'Enseignement Supérieur. Elles permettent de consulter les salaires observés entre 12 et 30 mois après l'obtention du diplôme.

Toutes ces données sont présentées comme des indicateurs statistiques : elles peuvent varier selon l'année, la formation, la situation des diplômés et le périmètre de la source.

---

## Traitement des données

Les données provenant des différentes sources ne sont pas toujours présentées sous une forme homogène.

Elles sont donc :

1. collectées,
2. vérifiées,
3. normalisées lorsque cela est nécessaire (notamment le nom du concours et de l'école),
4. intégrées dans une base de données MySQL,
5. utilisées par les scripts PHP du site pour produire les tableaux, statistiques et outils de recherche.

**Le site n'est pas un site officiel de SCEI, des concours ou des écoles.**

---

## Mise à jour

Les données sont mises à jour chaque année après la publication des résultats des concours et des informations nécessaires à leur exploitation.

Les nouvelles données sont ensuite intégrées à la base MySQL et publiées sur le site.

La date de mise à jour peut varier selon la disponibilité des différentes sources.

---

## Organisation du dépôt

```text
.
├── css/                    # Feuilles de style
├── image/                  # Images utilisées par le site
├── icon/                   # Icônes
├── js/                     # JavaScript
├── mySQL/                  # Structure et données de la base MySQL
├── php/                    # Scripts PHP
├── *.php                   # Pages principales du site
├── README.md
└── LICENSE.md
```

### `mySQL/`

Ce répertoire contient les éléments nécessaires à la constitution de la base de données utilisée par le site.

Les données y sont organisées par tables et/ou fichiers d'importation SQL.

### `php/`

Ce répertoire contient les scripts PHP utilisés pour accéder aux données et générer les différentes fonctionnalités du site.

---

## Technologies utilisées

Le site repose principalement sur :

- **PHP**
- **MySQL**
- **HTML / CSS**
- **JavaScript**
- **Bootstrap**

Bootstrap est utilisé pour la mise en page et les composants d'interface. Certaines fonctionnalités dynamiques utilisent des appels AJAX en JavaScript afin de charger les données sans recharger toute la page.

---

## Installation

Le site a été développé pour fonctionner avec un serveur web disposant de PHP et d'un serveur MySQL. Les versions utilisées pour la production sont PHP 8.3 et MySQL 5.5 ou supérieur.

Pour reproduire le site :

1. installer un serveur web avec PHP ;
2. installer MySQL ;
3. créer une base de données, par exemple `cpge` ;
4. importer `mySQL/cpge - tables.sql` ;
5. importer les fichiers `*- data.sql` présents dans `mySQL/` ;
6. importer `mySQL/cpge - views.sql` en dernier ;
7. placer les fichiers du site dans le répertoire utilisé par le serveur web ;
8. configurer les paramètres de connexion à MySQL dans les scripts PHP concernés.

Les fichiers PHP utilisent les valeurs d'exemple suivantes :

```php
DB_NAME = 'cpge';
DB_USER = 'USER';
DB_PASS = 'PASSWORD';
```

`USER` et `PASSWORD` sont des placeholders. Ils doivent être remplacés par les identifiants d'un utilisateur MySQL disposant des droits nécessaires sur la base installée. Le nom de la base `cpge` peut également être remplacé si une autre base est utilisée ; dans ce cas, il faut le modifier dans les scripts PHP et dans les commandes d'importation.

La vue SQL utilise `USER` comme `DEFINER`. Cet utilisateur doit donc exister au moment de l'import de `mySQL/cpge - views.sql`, ou la clause `DEFINER` doit être adaptée à l'utilisateur choisi.

---

## Données et responsabilité

Ce projet est un outil indépendant.

Les données présentées sont issues de sources publiques et peuvent contenir des erreurs, des changements de définition ou des différences de méthode entre les années et les sources.

En cas de divergence, les publications officielles de l'organisme ayant produit la donnée doivent être considérées comme la référence.

Le site ne constitue pas une garantie sur les possibilités d'admission d'un candidat.

Les statistiques historiques permettent d'éclairer une décision d'orientation, mais ne permettent pas de prévoir avec certitude les résultats d'un futur concours.

---

## Code source

Le code source et les données nécessaires au fonctionnement du site sont publiés sur GitHub dans un souci de transparence et de reproductibilité.

Le dépôt permet notamment de consulter :

- le code PHP ;
- la structure de la base MySQL ;
- les données utilisées par le site ;
- les fichiers nécessaires à leur intégration.

---

## Licence

Voir le fichier [`LICENSE.md`](LICENSE.md).

Les données provenant de sources tierces restent soumises aux conditions et droits applicables à leurs sources respectives.

---

## Auteur

Projet personnel réalisé et maintenu bénévolement par Loïc.

Site :

https://loic.website/CPGE/statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php
