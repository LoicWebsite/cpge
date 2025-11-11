-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : mar. 11 nov. 2025 à 15:00
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
-- Déchargement des données de la table `Concours`
--

INSERT INTO `Concours` (`Filiere`, `Concours`) VALUES
('bcpst', 'ARCH BIO'),
('bcpst', 'CENTRALE-SUPELEC'),
('bcpst', 'Concours ECOLE POLYTECHNIQUE Etr.'),
('bcpst', 'Concours POLYTECH BCPST'),
('bcpst', 'CPGE BCPST AGRO'),
('bcpst', 'CPGE BCPST VETO'),
('bcpst', 'CPGE PC AGRO'),
('bcpst', 'ENS'),
('bcpst', 'G2E'),
('bcpst', 'Groupe INSA'),
('bcpst', 'Lorraine INP - ENSTIB'),
('bcpst', 'X BIO'),
('mp', 'Autres écoles E3A'),
('mp', 'Avenir Prépas'),
('mp', 'Banque CENTRALE-SUPELEC'),
('mp', 'Banque ECOLE POLYTECHNIQUE - interENS'),
('mp', 'Banque épreuves CCINP'),
('mp', 'Banque épreuves CCINP Inter-Filière'),
('mp', 'CESI'),
('mp', 'Concours Commun INP'),
('mp', 'Concours Commun MINES-PONTS'),
('mp', 'CONCOURS COMMUN TPE'),
('mp', 'Concours ECOLE POLYTECHNIQUE Etr.'),
('mp', 'CONCOURS ENSAM'),
('mp', 'CONCOURS ESTP'),
('mp', 'Concours Ingeni’Up'),
('mp', 'Concours Mines - Télécom'),
('mp', 'Concours POLYTECH Inter-Filière'),
('mp', 'E3A - Inter-Filière'),
('mp', 'EPITA - ESME - IPSA'),
('mp', 'Groupe INSA'),
('mp', 'Puissance Alpha'),
('mpi', 'Autres écoles E3A'),
('mpi', 'Avenir Prépas'),
('mpi', 'Banque CENTRALE-SUPELEC'),
('mpi', 'Banque ECOLE POLYTECHNIQUE - interENS'),
('mpi', 'Banque épreuves CCINP'),
('mpi', 'Banque épreuves CCINP Inter-Filière'),
('mpi', 'CESI'),
('mpi', 'Concours Commun INP'),
('mpi', 'Concours Commun MINES-PONTS'),
('mpi', 'Concours ECOLE POLYTECHNIQUE Etr.'),
('mpi', 'Concours Ingeni’Up'),
('mpi', 'Concours Mines - Télécom'),
('mpi', 'Concours POLYTECH'),
('mpi', 'E3A - Inter-Filière'),
('mpi', 'EPITA - ESME - IPSA'),
('mpi', 'Groupe INSA'),
('mpi', 'Puissance Alpha'),
('pc', 'Autres écoles E3A'),
('pc', 'Avenir Prépas'),
('pc', 'Banque CENTRALE-SUPELEC'),
('pc', 'Banque ECOLE POLYTECHNIQUE - ESPCI - interENS'),
('pc', 'Banque épreuves CCINP'),
('pc', 'Banque épreuves CCINP Inter-Filière'),
('pc', 'CCINP : ECOLES RECRUTANT SUR LE CONCOURS CHIMIE'),
('pc', 'CCINP : ECOLES RECRUTANT SUR LE CONCOURS PHYSIQUE'),
('pc', 'Centrale Casablanca - CNC'),
('pc', 'CESI'),
('pc', 'Concours Commun MINES-PONTS'),
('pc', 'CONCOURS COMMUN TPE'),
('pc', 'Concours ECOLE POLYTECHNIQUE Etr.'),
('pc', 'CONCOURS ENSAM'),
('pc', 'CONCOURS ESTP'),
('pc', 'Concours Ingeni’Up'),
('pc', 'Concours Mines - Télécom'),
('pc', 'Concours POLYTECH Inter-Filière'),
('pc', 'E3A - Inter-Filière'),
('pc', 'EPITA - ESME - IPSA'),
('pc', 'Groupe INSA'),
('pc', 'Puissance Alpha'),
('psi', 'Autres écoles E3A'),
('psi', 'Avenir Prépas'),
('psi', 'Banque CENTRALE-SUPELEC'),
('psi', 'Banque ENS PARIS-SACLAY - ECOLE POLYTECHNIQUE'),
('psi', 'Banque épreuves CCINP'),
('psi', 'Banque épreuves CCINP Inter-Filière'),
('psi', 'CESI'),
('psi', 'Concours Commun INP'),
('psi', 'Concours Commun MINES-PONTS'),
('psi', 'CONCOURS COMMUN TPE'),
('psi', 'Concours ECOLE POLYTECHNIQUE Etr.'),
('psi', 'CONCOURS ENSAM'),
('psi', 'CONCOURS ESTP'),
('psi', 'Concours Ingeni’Up'),
('psi', 'Concours Mines - Télécom'),
('psi', 'Concours POLYTECH Inter-Filière'),
('psi', 'E3A - Inter-Filière'),
('psi', 'EPITA - ESME - IPSA'),
('psi', 'Groupe INSA'),
('psi', 'Puissance Alpha'),
('pt', 'AUTRES ECOLES'),
('pt', 'Avenir Prépas'),
('pt', 'CENTRALE-SUPELEC'),
('pt', 'CESI'),
('pt', 'Concours Commun ENSAM'),
('pt', 'Concours Commun INP'),
('pt', 'Concours Commun MINES-PONTS'),
('pt', 'Concours ECOLE POLYTECHNIQUE'),
('pt', 'Concours ENS'),
('pt', 'Concours Ingeni’Up'),
('pt', 'Concours Mines - Télécom'),
('pt', 'Concours POLYTECH'),
('pt', 'EPITA - ESME - IPSA'),
('pt', 'Groupe INSA'),
('pt', 'Puissance Alpha'),
('tb', 'Concours A TB ENS'),
('tb', 'Concours POLYTECH TB'),
('tb', 'CPGE TB AGRO'),
('tb', 'CPGE TB VETO'),
('tb', 'Groupe INSA'),
('tb', 'Lorraine INP - ENSTIB'),
('tpc', 'CESI'),
('tpc', 'Concours Commun INP'),
('tpc', 'Groupe INSA'),
('tsi', 'BANQUE DE NOTES CENTRALE'),
('tsi', 'Banque épreuves CCINP'),
('tsi', 'Banque épreuves CCINP Inter-Filière'),
('tsi', 'CESI'),
('tsi', 'Concours CENTRALE-SUPELEC'),
('tsi', 'Concours Commun ENSAM'),
('tsi', 'Concours Commun INP'),
('tsi', 'Concours Commun MINES-PONTS'),
('tsi', 'Concours ECOLE POLYTECHNIQUE Etr.'),
('tsi', 'Concours Ingeni’Up'),
('tsi', 'Concours Mines - Télécom'),
('tsi', 'Concours Polytechnique'),
('tsi', 'EPITA - ESME - IPSA'),
('tsi', 'Groupe INSA'),
('tsi', 'Réseau Polytech');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
