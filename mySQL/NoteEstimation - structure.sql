-- Structure de la table `NoteEstimation`
-- Estimation du rang du dernier intégré pour chaque ligne de `Note` dont le dernier n'est pas publié.
-- Table précalculée par python/estimation_dernier/calibrer.py : à régénérer et recharger à chaque
-- modification de `Note` (nouvelle campagne SCEI, correction). Même clé que `Note`.
-- Voir procédure/Guide estimation rang dernier.md.
--
--   Statut    exact    un seul intégré : le dernier est la médiane (Plancher exact)
--             plancher Plancher seul (concours interfilière, ou aucune estimation complémentaire)
--             estime   Plancher + Milieu et/ou Plafond
--   Plancher  rang du dernier garanti au moins égal : RangMedian + CEIL((Integre - 1) / 2)
--   Milieu    rang « bonnes chances » : environ 3 chances sur 4 d'être admis à ce rang (NULL s'il ne dépasse pas le plancher)
--   Plafond   nombre de classés du concours : aucun intégré au-delà (NULL si non fiable)
--
-- Absentes de la table : lignes au dernier publié, sans médiane, et écoles à statut particulier.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

DROP TABLE IF EXISTS `NoteEstimation`;

CREATE TABLE `NoteEstimation` (
  `Filiere` varchar(16) NOT NULL,
  `An` int(4) NOT NULL,
  `Concours` varchar(256) NOT NULL,
  `EcoleConcours` varchar(256) NOT NULL,
  `Statut` varchar(10) NOT NULL COMMENT 'exact | plancher | estime',
  `Plancher` int(5) NOT NULL,
  `Milieu` int(5) DEFAULT NULL,
  `Plafond` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

ALTER TABLE `NoteEstimation`
  ADD PRIMARY KEY (`Filiere`,`An`,`Concours`,`EcoleConcours`);
COMMIT;
