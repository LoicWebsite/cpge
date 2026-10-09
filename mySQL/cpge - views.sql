-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : ven. 09 oct. 2026 à 13:54
-- Version du serveur : 5.7.44
-- Version de PHP : 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `CPGE`
--

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vuespecialite`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vuespecialite` (
`IdEcole` int(5)
,`Ecole` varchar(64)
,`UrlEcole` varchar(256)
,`IdDiplome` int(5)
,`Diplome` varchar(250)
,`UrlFormation` varchar(512)
,`IdSpecialite` int(5)
,`Groupe` varchar(250)
,`Specialite` varchar(250)
,`TypeEtude` varchar(16)
,`AnneeChoix` varchar(16)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vuespecialitedomainesynonyme`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vuespecialitedomainesynonyme` (
`Domaine` varchar(100)
,`Description` varchar(250)
,`Actif` tinyint(1)
,`TermeRecherche` varchar(100)
,`Poids` int(3)
);

-- --------------------------------------------------------

--
-- Structure de la vue `vuespecialite`
--
DROP TABLE IF EXISTS `vuespecialite`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cpge`@`%` SQL SECURITY DEFINER VIEW `vuespecialite`  AS SELECT `e`.`IdEcole` AS `IdEcole`, `e`.`Ecole` AS `Ecole`, `e`.`UrlEcole` AS `UrlEcole`, `d`.`IdDiplome` AS `IdDiplome`, `d`.`Diplome` AS `Diplome`, `d`.`UrlFormation` AS `UrlFormation`, `s`.`IdSpecialite` AS `IdSpecialite`, `s`.`Groupe` AS `Groupe`, `s`.`Specialite` AS `Specialite`, `s`.`TypeEtude` AS `TypeEtude`, `s`.`AnneeChoix` AS `AnneeChoix` FROM ((`ecole` `e` join `diplome` `d` on((`d`.`IdEcole` = `e`.`IdEcole`))) left join `specialite` `s` on((`s`.`IdDiplome` = `d`.`IdDiplome`))) ;

-- --------------------------------------------------------

--
-- Structure de la vue `vuespecialitedomainesynonyme`
--
DROP TABLE IF EXISTS `vuespecialitedomainesynonyme`;

CREATE ALGORITHM=UNDEFINED DEFINER=`cpge`@`%` SQL SECURITY INVOKER VIEW `vuespecialitedomainesynonyme`  AS SELECT `d`.`Domaine` AS `Domaine`, `d`.`Description` AS `Description`, `d`.`Actif` AS `Actif`, `s`.`TermeRecherche` AS `TermeRecherche`, `s`.`Poids` AS `Poids` FROM (`specialitedomaine` `d` join `specialitesynonyme` `s` on((`s`.`IdDomaine` = `d`.`IdDomaine`))) ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
