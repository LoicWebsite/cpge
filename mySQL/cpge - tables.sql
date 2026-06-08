-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 08 juin 2026 à 10:05
-- Version du serveur : 5.5.61-38.13-log
-- Version de PHP : 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `cpge`
--

-- --------------------------------------------------------

--
-- Structure de la table `Attractivite`
--

CREATE TABLE `Attractivite` (
  `Rang` int(11) DEFAULT NULL,
  `Formation` varchar(72) CHARACTER SET utf8 NOT NULL,
  `Effectif` int(3) DEFAULT NULL,
  `Niveau moyen` int(3) DEFAULT NULL,
  `Attractivite` int(3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_roman_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Classement`
--

CREATE TABLE `Classement` (
  `An` int(4) NOT NULL,
  `Rang` int(3) DEFAULT NULL,
  `Groupe` varchar(2) DEFAULT NULL,
  `Ecole` varchar(50) NOT NULL,
  `Point` int(2) DEFAULT NULL,
  `UrlEtudiant` varchar(250) DEFAULT NULL,
  `UrlEcole` varchar(250) CHARACTER SET utf8 COLLATE utf8_roman_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Concours`
--

CREATE TABLE `Concours` (
  `Filiere` varchar(16) NOT NULL,
  `Concours` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `DAUR`
--

CREATE TABLE `DAUR` (
  `An` int(11) NOT NULL,
  `Rang` int(3) DEFAULT NULL,
  `Groupe` varchar(3) DEFAULT NULL,
  `Ecole` varchar(50) NOT NULL,
  `Point` int(3) DEFAULT NULL,
  `UrlEcole` varchar(250) CHARACTER SET utf8 COLLATE utf8_roman_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Ecole`
--

CREATE TABLE `Ecole` (
  `Filiere` varchar(16) NOT NULL,
  `Concours` varchar(256) NOT NULL,
  `Ecole` varchar(256) NOT NULL,
  `EcoleClassement` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Figaro`
--

CREATE TABLE `Figaro` (
  `An` int(4) NOT NULL,
  `Rang` int(3) DEFAULT NULL,
  `Ecole` varchar(50) NOT NULL,
  `Point` float DEFAULT NULL,
  `UrlFigaro` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `Filiere`
--

CREATE TABLE `Filiere` (
  `Filiere` varchar(16) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `InsersupRaw`
--

CREATE TABLE `InsersupRaw` (
  `date_jeu` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_nom` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reg_id` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aca_nom` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `aca_id` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uo_lib` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uo_lib_actuel` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_paysage` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_paysage_actuel` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `etablissement` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `denomination_principale` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_diplome_long` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_diplome` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dom_lib` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dom` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipli_lib` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `discipli` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sectdis_lib` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sectdis` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `libelle_diplome` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `diplome` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `obtention_diplome` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `genre` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationalite` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regime_inscription` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_poursuivants` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promo` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_6` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_12` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_18` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_24` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `flag_30` varchar(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exception_6` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exception_12` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exception_18` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exception_24` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exception_30` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_etranger_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_etranger_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_etranger_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_sal_fr_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_sal_fr_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_sal_fr_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_sal_fr_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_sal_fr_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_sal_fr_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_sal_fr_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_sal_fr_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_sal_fr_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_sal_fr_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_non_sal_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_non_sal_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_non_sal_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_non_sal_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_non_sal_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_non_sal_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_non_sal_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_non_sal_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_non_sal_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_non_sal_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_stable_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_stable_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_stable_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_stable_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nb_sortants_en_emploi_stable_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_stable_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_stable_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_stable_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_stable_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tx_sortants_en_emploi_stable_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q1_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q1_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q1_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q1_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q1_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q2_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q2_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q2_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q2_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q2_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q3_6` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q3_12` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q3_18` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q3_24` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `salaire_q3_30` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `promo_annee` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `Note`
--

CREATE TABLE `Note` (
  `Filiere` varchar(16) NOT NULL,
  `An` int(4) NOT NULL,
  `Concours` varchar(256) NOT NULL,
  `Ecole` varchar(256) NOT NULL,
  `Inscrit` int(5) DEFAULT NULL,
  `Admissible` int(5) DEFAULT NULL,
  `Classe` int(5) DEFAULT NULL,
  `Integre` int(5) DEFAULT NULL,
  `RangMedian` int(5) DEFAULT NULL,
  `RangMoyen` int(5) DEFAULT NULL,
  `Place` int(5) DEFAULT NULL,
  `Dernier` int(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `Attractivite`
--
ALTER TABLE `Attractivite`
  ADD PRIMARY KEY (`Formation`);

--
-- Index pour la table `Classement`
--
ALTER TABLE `Classement`
  ADD PRIMARY KEY (`An`,`Ecole`);

--
-- Index pour la table `DAUR`
--
ALTER TABLE `DAUR`
  ADD PRIMARY KEY (`An`,`Ecole`);

--
-- Index pour la table `Figaro`
--
ALTER TABLE `Figaro`
  ADD PRIMARY KEY (`An`,`Ecole`);

--
-- Index pour la table `Filiere`
--
ALTER TABLE `Filiere`
  ADD PRIMARY KEY (`Filiere`);

--
-- Index pour la table `InsersupRaw`
--
ALTER TABLE `InsersupRaw`
  ADD KEY `idx_uo_lib` (`uo_lib`),
  ADD KEY `idx_type_diplome` (`type_diplome_long`(40)),
  ADD KEY `idx_promo` (`promo`),
  ADD KEY `idx_genre` (`genre`),
  ADD KEY `idx_nationalite` (`nationalite`),
  ADD KEY `idx_ingenieur` (`type_diplome`(30),`genre`(10),`regime_inscription`(15),`promo_annee`),
  ADD KEY `idx_salaire_base_classement` (`type_diplome`(30),`libelle_diplome`(60),`obtention_diplome`(12),`nationalite`(10),`genre`(10),`regime_inscription`(15),`promo_annee`,`etablissement`,`uo_lib`(100)),
  ADD KEY `idx_salaire_source_lookup` (`denomination_principale`(100),`type_diplome`(30),`libelle_diplome`(60),`obtention_diplome`(12),`nationalite`(10),`genre`(10),`regime_inscription`(15),`promo_annee`,`etablissement`,`uo_lib`(100)),
  ADD KEY `idx_salaire_uo_lookup` (`uo_lib`(100),`type_diplome`(30),`libelle_diplome`(60),`obtention_diplome`(12),`nationalite`(10),`genre`(10),`regime_inscription`(15),`promo_annee`,`etablissement`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
