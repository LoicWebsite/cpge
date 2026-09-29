-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : mer. 09 sep. 2026 à 14:31
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

--
-- Déchargement des données de la table `SpecialiteDomaine`
--

INSERT INTO `SpecialiteDomaine` (`IdDomaine`, `Domaine`, `Description`, `Actif`) VALUES
(1, 'Santé', 'Ingénierie biomédicale, dispositifs médicaux, santé et e-santé.', 1),
(2, 'Bio', 'Biologie, biotechnologies, bio-ingénierie et vivant.', 1),
(3, 'Agro', 'Agronomie, agriculture, alimentation et agroalimentaire.', 1),
(4, 'Géosciences / Eau / Environnement', 'Géosciences, eau, environnement, climat et risques.', 1),
(5, 'Chimie', 'Chimie, formulation et chimie durable.', 1),
(6, 'Physique / Photonique / Quantique', 'Physique appliquée, optique, photonique et quantique.', 1),
(7, 'Matériaux / Nano', 'Matériaux, polymères, composites, métallurgie et nanomatériaux.', 1),
(8, 'Procédés', 'Génie des procédés, procédés chimiques et bioprocédés.', 1),
(9, 'Énergie', 'Énergie, nucléaire, hydrogène, conversion et stockage.', 1),
(10, 'Génie électrique', 'Électrotechnique, puissance, réseaux électriques et conversion.', 1),
(11, 'Mobilités', 'Transport, mobilité et véhicules.', 1),
(12, 'Automobile / Ferroviaire', 'Automobile, véhicules terrestres, ferroviaire et rail.', 1),
(13, 'Aéronautique / Spatial', 'Aéronautique, avionique, espace et systèmes spatiaux.', 1),
(14, 'Naval / Maritime', 'Construction navale, génie maritime, mer et océan.', 1),
(15, 'BTP / Génie civil / Génie urbain', 'Construction, bâtiment, travaux publics et ville.', 1),
(16, 'Mécanique', 'Mécanique, structures, fluides, conception et production.', 1),
(17, 'Robotique / Mécatronique', 'Robotique, mécatronique, automatique et systèmes autonomes.', 1),
(18, 'Électronique', 'Électronique, capteurs et microélectronique.', 1),
(19, 'Systèmes embarqués', 'Systèmes embarqués, temps réel, IoT et objets connectés.', 1),
(20, 'Télécoms / Réseaux', 'Télécommunications, réseaux et infrastructures numériques.', 1),
(21, 'Informatique', 'Logiciel, web, mobile, cloud et systèmes d\'information.', 1),
(22, 'Data / IA', 'Données, intelligence artificielle, modélisation et optimisation.', 1),
(23, 'Cybersécurité', 'Sécurité informatique, infrastructures et DevSecOps.', 1),
(24, 'Mathématiques / Finance de marché', 'Mathématiques appliquées, statistique, optimisation et finance quantitative.', 1),
(25, 'Économie / Finance d\'entreprise', 'Économie, gestion, finance d\'entreprise et audit.', 1),
(26, 'Génie industriel / Conception / Logistique / QHSE', 'Conception, production, logistique, qualité, sécurité et environnement.', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
