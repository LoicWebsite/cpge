# Statistiques d'intégration en école d'ingénieurs
Ce site Web est un visualisateur des statistiques d'intégration en école d'ingénieurs après concours. Il permet d'explorer les résultats d'admissions aux écoles d'ingénieurs post CPGE par filière, par concours ou par école. Les données sont issues du SCEI et enrichies du rang des derniers admis provenant des rapports des concours notamment Polytechnique, CentraleSupelec, Mines-Telecom et Mines-Ponts.

Mon site Web est public : https://loic.website/CPGE/
### Home page
La page d'accueil est une page HTML statique : `statistique-admission-ecole-d-ingenieur-cpge-post-prepa.php`
### Pages dynamiques
Les autres pages sont générées en PHP.
Les pages utilisent le framework Bootstrap.
### Base de données
La base de données est une base mySQL. Dans le repertoire `mySQL` il y a un fichier pour la création de la base vide : `cpge - tables.sql`, et autant de fichier de données que de tables. Il suffit de faire des imports de ces fichiers dans mySQL.

Par défaut le nom de la base est `cpge`. Dans chaque page qui se connecte à la base de données, il faut remplacer `USER` et `PASSE` par l'utilisateur et son mot de passe de votre base de données.
### Mise à jour des données
Les données sont mises à jour manuellement, tous les ans, après la sortie des résultats des concours, des rapports des jurys et des classements. Les fichiers  de données `xxx - data.sql` sont alors mis à jour dans le répertoire `mySQL`.
### Google Analytics
Si vous voulez conserver les statistiques des pages, il faut changer l'identifiant Google analytics `ID-GOOGLE` dans le script `php/style.php` . Sinon il faut enlever le code correspondant dans ce script.