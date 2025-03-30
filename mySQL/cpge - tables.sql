-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : dim. 30 mars 2025 à 16:26
-- Version du serveur : 5.7.39
-- Version de PHP : 8.2.0

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
  `An` int(4) NOT NULL,
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
-- Index pour la table `Classement`
--
ALTER TABLE `Classement`
  ADD PRIMARY KEY (`An`,`Ecole`);

--
-- Index pour la table `Concours`
--
ALTER TABLE `Concours`
  ADD PRIMARY KEY (`Filiere`,`Concours`);

--
-- Index pour la table `DAUR`
--
ALTER TABLE `DAUR`
  ADD PRIMARY KEY (`An`,`Ecole`);

--
-- Index pour la table `Ecole`
--
ALTER TABLE `Ecole`
  ADD PRIMARY KEY (`Filiere`,`Concours`,`Ecole`);

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
-- Index pour la table `Note`
--
ALTER TABLE `Note`
  ADD PRIMARY KEY (`Filiere`,`An`,`Concours`,`Ecole`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
