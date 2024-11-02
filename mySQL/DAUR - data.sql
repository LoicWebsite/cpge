-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le : jeu. 24 oct. 2024 à 16:48
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
-- Déchargement des données de la table `DAUR`
--

INSERT INTO `DAUR` (`An`, `Rang`, `Groupe`, `Ecole`, `Point`, `UrlEcole`) VALUES
(2022, 119, 'CCC', '3IL Ingénieurs', 32, 'https://www.3il-ingenieurs.fr'),
(2022, 14, 'AA', 'AgroParisTech', 63, 'https://www.agroparistech.fr'),
(2022, 35, 'A', 'Arts et Métiers', 51, 'https://artsetmetiers.fr/fr/'),
(2022, 83, 'B', 'Bordeaux INP - ENSC', 38, 'https://ensc.bordeaux-inp.fr/fr'),
(2022, 42, 'BBB', 'Bordeaux INP - ENSEIRB-MATMECA', 46, 'https://enseirb-matmeca.bordeaux-inp.fr'),
(2022, 46, 'BBB', 'Bordeaux Sciences Agro', 46, 'http://www.agro-bordeaux.fr'),
(2022, 12, 'AA', 'CENTRALE Lille', 65, 'https://centralelille.fr'),
(2022, 10, 'AA', 'CENTRALE Lyon', 69, 'https://www.ec-lyon.fr'),
(2022, 25, 'AA', 'CENTRALE Méditerranée', 56, 'https://www.centrale-marseille.fr'),
(2022, 13, 'AA', 'CENTRALE Nantes', 64, 'https://www.ec-nantes.fr'),
(2022, 4, 'AAA', 'CentraleSupélec', 76, 'https://www.centralesupelec.fr'),
(2022, 11, 'AA', 'Chimie ParisTech', 65, 'https://www.chimieparistech.psl.eu'),
(2022, 87, 'B', 'CPE Lyon', 38, 'https://www.cpe.fr'),
(2022, 75, 'BB', 'CY Tech', 40, 'https://cytech.cyu.fr'),
(2022, 114, 'CCC', 'ECAM Lyon', 33, 'https://www.ecam.fr'),
(2022, 170, 'C', 'ECAM Rennes', 21, 'https://www.ecam-rennes.fr'),
(2022, 128, 'CC', 'ECAM-EPMI Cergy-Pontoise', 31, 'https://www.ecam-epmi.fr'),
(2022, 90, 'B', 'ECE', 37, 'https://www.ece.fr'),
(2022, 1, 'AAA', 'Ecole Polytechnique', 100, 'https://programmes.polytechnique.edu/'),
(2022, 21, 'AA', 'ECPM Strasbourg', 57, 'https://ecpm.unistra.fr'),
(2022, 39, 'BBB', 'EEIGM', 47, 'https://www.eeigm.univ-lorraine.fr'),
(2022, 65, 'BB', 'EFREI Paris', 43, 'https://www.efrei.fr'),
(2022, 118, 'CCC', 'EIDD Paris', 33, 'https://eidd.univ-paris-diderot.fr'),
(2022, 137, 'CC', 'EIGSI La Rochelle', 30, 'https://www.eigsi.fr'),
(2022, 135, 'CC', 'EIL Côte d\'Opale', 31, 'http://www.eilco-ulco.fr'),
(2022, 37, 'A', 'EIVP Paris', 49, 'https://www.eivp-paris.fr'),
(2022, 166, 'C', 'ELISA Aerospace', 23, 'https://www.elisa-aerospace.fr'),
(2022, 28, 'A', 'ENAC Toulouse', 54, 'https://www.enac.fr'),
(2022, 54, 'BBB', 'ENGEES Strasbourg', 44, 'https://engees.unistra.fr'),
(2022, 7, 'AAA', 'ENSAE Paris', 76, 'https://www.ensae.fr'),
(2022, 34, 'A', 'ENSAI Rennes', 51, 'https://www.ensai.fr'),
(2022, 45, 'BBB', 'ENSAIA', 46, 'https://www.ensaia.univ-lorraine.fr'),
(2022, 129, 'CC', 'ENSAIT Roubaix', 31, 'https://www.ensait.fr'),
(2022, 32, 'A', 'ENSAT Toulouse', 52, 'https://www.ensat.fr/'),
(2022, 49, 'BBB', 'ENSC Lille', 45, 'https://www.ensc-lille.fr'),
(2022, 22, 'AA', 'ENSC Montpellier', 56, 'https://www.enscm.fr/fr/'),
(2022, 47, 'BBB', 'ENSC Mulhouse', 46, 'https://www.enscmu.uha.fr'),
(2022, 52, 'BBB', 'ENSC Rennes', 45, 'https://www.ensc-rennes.fr'),
(2022, 101, 'B', 'ENSEA Cergy', 35, 'https://www.ensea.fr'),
(2022, 26, 'A', 'ENSEEIHT', 55, 'https://www.enseeiht.fr'),
(2022, 40, 'BBB', 'ENSEGID', 47, 'https://ensegid.bordeaux-inp.fr'),
(2022, 74, 'BB', 'ENSEM', 40, 'https://www.ensem.univ-lorraine.fr'),
(2022, 31, 'A', 'ENSG Nancy', 52, 'https://www.ensg.univ-lorraine.fr'),
(2022, 69, 'BB', 'ENSG-Géomatique', 41, 'https://www.ensg.eu'),
(2022, 94, 'B', 'ENSGSI - Lorraine', 36, 'https://www.ensgsi.univ-lorraine.fr'),
(2022, 79, 'BB', 'ENSGTI Pau', 39, 'https://ensgti.univ-pau.fr'),
(2022, 111, 'CCC', 'ENSI Poitiers', 34, 'https://ensip.univ-poitiers.fr'),
(2022, 58, 'BBB', 'ENSIACET', 44, 'https://www.ensiacet.fr'),
(2022, 147, 'C', 'ENSIBS', 29, 'https://www-ensibs.univ-ubs.fr/fr/index.html'),
(2022, 63, 'BB', 'ENSIC', 43, 'https://ensic.univ-lorraine.fr'),
(2022, 68, 'BB', 'ENSICAEN', 41, 'https://www.ensicaen.fr'),
(2022, 38, 'BBB', 'ENSIIE Évry', 48, 'https://www.ensiie.fr'),
(2022, 110, 'CCC', 'ENSIL', 34, 'https://www.ensil-ensci.unilim.fr'),
(2022, 160, 'C', 'ENSIM Le Mans', 26, 'http://ensim.univ-lemans.fr'),
(2022, 149, 'C', 'ENSISA Mulhouse', 29, 'https://www.ensisa.uha.fr'),
(2022, 41, 'BBB', 'ENSMAC', 47, 'https://enscbp.bordeaux-inp.fr'),
(2022, 104, 'B', 'ENSMM Besançon', 35, 'https://www.ens2m.fr'),
(2022, 92, 'B', 'ENSSAT Lannion', 37, 'https://www.enssat.fr/'),
(2022, 19, 'AA', 'ENSTA Bretagne', 58, 'https://www.ensta-bretagne.fr/fr'),
(2022, 5, 'AAA', 'ENSTA Paris', 76, 'https://www.ensta-paris.fr'),
(2022, 77, 'BB', 'ENSTBB Bordeaux', 39, 'https://enstbb.bordeaux-inp.fr'),
(2022, 139, 'CC', 'ENSTIB Épinal', 30, 'https://www.enstib.univ-lorraine.fr'),
(2022, 23, 'AA', 'ENTPE Lyon', 56, 'https://www.entpe.fr'),
(2022, 57, 'BBB', 'EOST Strasbourg', 44, 'https://eost.unistra.fr'),
(2022, 97, 'B', 'EPF', 36, 'http://www.epf.fr'),
(2022, 88, 'B', 'EPITA', 37, 'https://toulouse.epita.fr'),
(2022, 169, 'C', 'ESAIP Angers', 21, 'https://www.esaip.org'),
(2022, 175, 'C', 'ESB Nantes', 17, 'https://www.ecoledubois.fr'),
(2022, 29, 'A', 'ESBS', 53, 'https://www.esbs.unistra.fr'),
(2022, 141, 'CC', 'ESCOM', 30, 'https://www.escom.fr'),
(2022, 167, 'C', 'ESEO Angers', 23, 'https://www.eseo.fr'),
(2022, 174, 'C', 'ESGT', 19, 'https://www.esgt.cnam.fr/esgt/'),
(2022, 138, 'CC', 'ESI Reims', 30, 'https://www.univ-reims.fr/esireims/'),
(2022, 131, 'CC', 'ESIAB Brest', 31, 'https://www.univ-brest.fr/esiab'),
(2022, 152, 'C', 'ESIEA Paris', 28, 'https://www.esiea.fr'),
(2022, 172, 'C', 'ESIEE Amiens', 20, 'https://www.esiee.fr'),
(2022, 95, 'B', 'ESIEE Paris', 36, 'https://www.esiee.fr'),
(2022, 102, 'B', 'ESIGELEC Rouen', 35, 'https://www.esigelec.fr'),
(2022, 59, 'BBB', 'ESILV', 44, 'https://www.esilv.fr'),
(2022, 67, 'BB', 'ESIPE', 42, 'https://esipe.u-pem.fr'),
(2022, 148, 'C', 'ESIR Rennes', 29, 'http://www.esir.univ-rennes1.fr'),
(2022, 158, 'C', 'ESIREM Dijon', 26, 'https://esirem.u-bourgogne.fr'),
(2022, 150, 'C', 'ESIROI', 29, 'https://esiroi.univ-reunion.fr'),
(2022, 156, 'C', 'ESITC Caen', 27, 'https://www.esitc-caen.fr'),
(2022, 123, 'CCC', 'ESITech', 32, 'http://esitech.univ-rouen.fr'),
(2022, 116, 'CCC', 'ESIX', 33, 'http://esix.unicaen.fr'),
(2022, 140, 'CC', 'ESME', 30, 'https://www.esme.fr'),
(2022, 6, 'AAA', 'ESPCI Paris', 76, 'https://www.espci.psl.eu/fr'),
(2022, 56, 'BBB', 'ESTACA', 44, 'https://www.estaca.fr'),
(2022, 145, 'CC', 'ESTIA Bidart', 29, 'https://www.estia.fr'),
(2022, 76, 'BB', 'ESTP Paris', 40, 'https://www.estp.fr/troyes'),
(2022, 36, 'A', 'Grenoble INP - ENSE3', 50, 'https://ense3.grenoble-inp.fr'),
(2022, 16, 'AA', 'Grenoble INP - Ensimag', 62, 'https://ensimag.grenoble-inp.fr'),
(2022, 78, 'BB', 'Grenoble INP - Esisar', 39, 'https://esisar.grenoble-inp.fr'),
(2022, 62, 'BB', 'Grenoble INP - Génie industriel', 43, 'https://genie-industriel.grenoble-inp.fr'),
(2022, 165, 'C', 'Grenoble INP - Pagora', 24, 'https://pagora.grenoble-inp.fr'),
(2022, 20, 'AA', 'Grenoble INP - Phelma', 57, 'https://phelma.grenoble-inp.fr'),
(2022, 163, 'C', 'HEI Lille', 24, 'https://www.hei.fr'),
(2022, 17, 'AA', 'IMT Atlantique', 61, 'https://www.imt-atlantique.fr'),
(2022, 44, 'BBB', 'IMT Mines Albi', 46, 'https://www.imt-mines-albi.fr'),
(2022, 43, 'BBB', 'IMT Mines Alès', 46, 'https://www.mines-ales.fr'),
(2022, 61, 'BB', 'IMT Nord Europe', 43, 'https://www.imt-lille-douai.fr'),
(2022, 113, 'CCC', 'INSA Centre Val de Loire', 34, 'https://www.insa-centrevaldeloire.fr'),
(2022, 100, 'B', 'INSA Hauts de France', 35, 'https://www.insa-hautsdefrance.fr/insa-hdf.html'),
(2022, 60, 'BBB', 'INSA Lyon', 44, 'https://www.insa-lyon.fr'),
(2022, 72, 'BB', 'INSA Rennes', 41, 'https://www.insa-rennes.fr'),
(2022, 53, 'BBB', 'INSA Rouen', 44, 'https://www.insa-rouen.fr'),
(2022, 91, 'B', 'INSA Strasbourg', 37, 'https://www.insa-strasbourg.fr'),
(2022, 64, 'BB', 'INSA Toulouse', 43, 'https://www.insa-toulouse.fr'),
(2022, 66, 'BB', 'Institut Agro – Dijon', 43, 'https://www.agrosupdijon.fr'),
(2022, 48, 'BBB', 'Institut Agro – Rennes', 46, 'https://www.agrocampus-ouest.fr'),
(2022, 9, 'AAA', 'ISAE - SUPAERO', 72, 'https://www.isae-supaero.fr'),
(2022, 33, 'A', 'ISAE-ENSMA Poitiers', 52, 'https://www.ensma.fr'),
(2022, 55, 'BBB', 'ISAE-Supméca', 44, 'https://www.isae-supmeca.fr/'),
(2022, 136, 'CC', 'ISAT Nevers', 30, 'https://www.isat.fr'),
(2022, 161, 'C', 'ISEL Le Havre', 25, 'https://www.isel-logistique.fr'),
(2022, 164, 'C', 'ISEN Lille', 24, 'https://www.isen-lille.fr'),
(2022, 168, 'C', 'ISEN Méditerranée', 22, 'https://www.isen-mediterranee.fr'),
(2022, 151, 'C', 'ISEN Yncrea Ouest', 28, 'https://isen-brest.fr'),
(2022, 93, 'B', 'ISEP Paris', 36, 'https://www.isep.fr'),
(2022, 159, 'C', 'ISIFC Besançon', 26, 'https://isifc.univ-fcomte.fr'),
(2022, 81, 'BB', 'ISIMA Clermont-Ferrand', 39, 'https://www.isima.fr'),
(2022, 171, 'C', 'ISMANS', 20, 'https://ismans.cesi.fr'),
(2022, 173, 'C', 'ITECH', 20, 'https://www.itech.fr'),
(2022, 15, 'AA', 'MINES de NANCY', 63, 'https://www.mines-nancy.univ-lorraine.fr'),
(2022, 2, 'AAA', 'MINES Paris', 86, 'https://www.mines-paristech.fr'),
(2022, 24, 'AA', 'MINES Saint-Étienne', 56, 'https://www.mines-stetienne.fr'),
(2022, 30, 'A', 'Montpellier Sup Agro', 53, 'https://www.montpellier-supagro.fr'),
(2022, 80, 'BB', 'ONIRIS Nantes', 39, 'https://www.oniris-nantes.fr'),
(2022, 176, 'C', 'Paoli Tech', 16, 'https://paolitech.universita.corsica'),
(2022, 82, 'B', 'Polytech Angers', 39, 'http://www.polytech-angers.fr/fr/index.html'),
(2022, 117, 'CCC', 'Polytech Annecy-Chambéry', 33, 'https://www.polytech.univ-smb.fr'),
(2022, 115, 'CCC', 'Polytech Clermont-Ferrand', 33, 'https://polytech.univ-bpclermont.fr'),
(2022, 112, 'CCC', 'Polytech Grenoble', 34, 'https://www.polytech-grenoble.fr'),
(2022, 98, 'B', 'Polytech Lille', 36, 'https://www.polytech-lille.fr'),
(2022, 105, 'B', 'Polytech Lyon', 35, 'https://polytech.univ-lyon1.fr'),
(2022, 157, 'C', 'Polytech Marseille', 27, 'http://polytech.univ-amu.fr'),
(2022, 107, 'B', 'Polytech Montpellier', 35, 'https://www.polytech.umontpellier.fr'),
(2022, 109, 'CCC', 'Polytech Nancy', 34, 'https://www.polytech-nancy.univ-lorraine.fr'),
(2022, 124, 'CC', 'Polytech Nantes', 32, 'https://web.polytech.univ-nantes.fr'),
(2022, 89, 'B', 'Polytech Nice-Sophia', 37, 'https://polytech.univ-cotedazur.fr'),
(2022, 127, 'CC', 'Polytech Orléans', 32, 'https://www.univ-orleans.fr/polytech/'),
(2022, 73, 'BB', 'Polytech Sorbonne', 41, 'https://www.polytech.sorbonne-universite.fr'),
(2022, 130, 'CC', 'Polytech Tours', 31, 'https://www.polytech.univ-tours.fr'),
(2022, 3, 'AAA', 'PONTS ParisTech', 77, 'https://www.enpc.fr'),
(2022, 84, 'B', 'SeaTech Toulon', 38, 'https://www.seatech.fr'),
(2022, 108, 'B', 'Sigma Clermont', 35, 'https://www.sigma-clermont.fr'),
(2022, 103, 'B', 'Sup Galilée Villetaneuse', 35, 'https://galilee.univ-paris13.fr'),
(2022, 120, 'CCC', 'SupBiotech', 32, 'https://www.supbiotech.fr'),
(2022, 18, 'AA', 'SupOptique', 61, 'https://www.institutoptique.fr'),
(2022, 70, 'BB', 'Télécom Nancy', 41, 'https://telecomnancy.univ-lorraine.fr'),
(2022, 8, 'AAA', 'Télécom Paris', 75, 'https://www.telecom-paristech.fr'),
(2022, 51, 'BBB', 'Télécom Physique Strasbourg', 45, 'https://www.telecom-physique.fr'),
(2022, 96, 'B', 'Télécom St Étienne', 36, 'https://www.telecom-st-etienne.fr'),
(2022, 27, 'A', 'Télécom SudParis', 54, 'https://www.telecom-sudparis.eu'),
(2022, 126, 'CC', 'Toulouse INP - ENIT', 32, 'https://www.enit.fr/'),
(2022, 143, 'CC', 'UniLaSalle', 29, 'https://www.unilasalle.fr'),
(2022, 71, 'BB', 'UTT', 41, 'https://www.utt.fr'),
(2022, 86, 'B', 'VetAgro Sup', 38, 'https://www.vetagrosup.fr'),
(2023, 151, 'CC', '3IL Ingénieurs', 28, 'https://www.3il-ingenieurs.fr'),
(2023, 20, 'AA', 'AgroParisTech', 61, 'https://www.agroparistech.fr'),
(2023, 33, 'A', 'Arts et Métiers', 51, 'https://artsetmetiers.fr/fr/'),
(2023, 146, 'CC', 'Bordeaux INP - ENSC', 29, 'https://ensc.bordeaux-inp.fr/fr'),
(2023, 53, 'BBB', 'Bordeaux INP - ENSEIRB-MATMECA', 44, 'https://enseirb-matmeca.bordeaux-inp.fr'),
(2023, 84, 'BB', 'Bordeaux Sciences Agro', 37, 'http://www.agro-bordeaux.fr'),
(2023, 11, 'AA', 'CENTRALE Lille', 68, 'https://centralelille.fr'),
(2023, 10, 'AAA', 'CENTRALE Lyon', 70, 'https://www.ec-lyon.fr'),
(2023, 23, 'AA', 'Centrale Méditerranée', 59, 'https://www.centrale-marseille.fr'),
(2023, 19, 'AA', 'CENTRALE Nantes', 61, 'https://www.ec-nantes.fr'),
(2023, 4, 'AAA', 'CentraleSupélec', 79, 'https://www.centralesupelec.fr'),
(2023, 158, 'C', 'CESI', 27, 'https://www.cesi.fr'),
(2023, 13, 'AA', 'Chimie ParisTech', 68, 'https://www.chimieparistech.psl.eu'),
(2023, 116, 'CCC', 'CPE Lyon', 32, 'https://www.cpe.fr'),
(2023, 104, 'B', 'CY Tech', 34, 'https://cytech.cyu.fr'),
(2023, 64, 'BBB', 'ECAM Lyon', 41, 'https://www.ecam.fr'),
(2023, 181, 'C', 'ECAM Rennes', 18, 'https://www.ecam-rennes.fr'),
(2023, 171, 'C', 'ECAM-EPMI Cergy-Pontoise', 22, 'https://www.ecam-epmi.fr'),
(2023, 91, 'B', 'ECE', 36, 'https://www.ece.fr'),
(2023, 77, 'BB', 'ECOLE Météorologie', 38, 'http://ecole-meteo.fr'),
(2023, 1, 'AAA', 'Ecole Polytechnique', 95, 'https://programmes.polytechnique.edu/'),
(2023, 15, 'AA', 'ECPM Strasbourg', 64, 'https://ecpm.unistra.fr'),
(2023, 70, 'BB', 'EEIGM', 40, 'https://www.eeigm.univ-lorraine.fr'),
(2023, 98, 'B', 'EFREI Paris', 34, 'https://www.efrei.fr'),
(2023, 68, 'BB', 'EIDD Paris', 40, 'https://eidd.univ-paris-diderot.fr'),
(2023, 112, 'CCC', 'EIL Côte d\'Opale', 33, 'http://www.eilco-ulco.fr'),
(2023, 92, 'B', 'EIVP Paris', 35, 'https://www.eivp-paris.fr'),
(2023, 173, 'C', 'ELISA Aerospace', 22, 'https://www.elisa-aerospace.fr'),
(2023, 34, 'A', 'ENAC Toulouse', 51, 'https://www.enac.fr'),
(2023, 35, 'A', 'ENGEES Strasbourg', 50, 'https://engees.unistra.fr'),
(2023, 160, 'C', 'ENIB', 26, 'https://www.enib.fr'),
(2023, 7, 'AAA', 'ENSAE Paris', 73, 'https://www.ensae.fr'),
(2023, 31, 'AA', 'ENSAI Rennes', 54, 'https://www.ensai.fr'),
(2023, 78, 'BB', 'ENSAIA', 38, 'https://www.ensaia.univ-lorraine.fr'),
(2023, 95, 'B', 'ENSAIT Roubaix', 35, 'https://www.ensait.fr'),
(2023, 52, 'BBB', 'ENSAT Toulouse', 44, 'https://www.ensat.fr/'),
(2023, 27, 'AA', 'ENSC Lille', 56, 'https://www.ensc-lille.fr'),
(2023, 22, 'AA', 'ENSC Montpellier', 59, 'https://www.enscm.fr/fr/'),
(2023, 105, 'CCC', 'ENSC Mulhouse', 33, 'https://www.enscmu.uha.fr'),
(2023, 46, 'BBB', 'ENSC Rennes', 46, 'https://www.ensc-rennes.fr'),
(2023, 62, 'BBB', 'ENSEA Cergy', 41, 'https://www.ensea.fr'),
(2023, 37, 'A', 'ENSEEIHT', 50, 'https://www.enseeiht.fr'),
(2023, 67, 'BB', 'ENSEGID', 40, 'https://ensegid.bordeaux-inp.fr'),
(2023, 69, 'BB', 'ENSEM', 40, 'https://www.ensem.univ-lorraine.fr'),
(2023, 18, 'AA', 'ENSG Nancy', 62, 'https://www.ensg.univ-lorraine.fr'),
(2023, 129, 'CC', 'ENSG-Géomatique', 30, 'https://www.ensg.eu'),
(2023, 108, 'CCC', 'ENSGSI - Lorraine', 33, 'https://www.ensgsi.univ-lorraine.fr'),
(2023, 74, 'BB', 'ENSGTI Pau', 38, 'https://ensgti.univ-pau.fr'),
(2023, 103, 'B', 'ENSI Poitiers', 34, 'https://ensip.univ-poitiers.fr'),
(2023, 36, 'A', 'ENSIACET', 50, 'https://www.ensiacet.fr'),
(2023, 128, 'CC', 'ENSIBS', 30, 'https://www-ensibs.univ-ubs.fr/fr/index.html'),
(2023, 48, 'BBB', 'ENSIC', 46, 'https://ensic.univ-lorraine.fr'),
(2023, 56, 'BBB', 'ENSICAEN', 43, 'https://www.ensicaen.fr'),
(2023, 51, 'BBB', 'ENSIIE Évry', 45, 'https://www.ensiie.fr'),
(2023, 110, 'CCC', 'ENSIL', 33, 'https://www.ensil-ensci.unilim.fr'),
(2023, 117, 'CCC', 'ENSIM Le Mans', 32, 'http://ensim.univ-lemans.fr'),
(2023, 145, 'CC', 'ENSISA Mulhouse', 29, 'https://www.ensisa.uha.fr'),
(2023, 83, 'BB', 'ENSMAC', 37, 'https://ensmac.bordeaux-inp.fr/fr'),
(2023, 123, 'CCC', 'ENSMM Besançon', 32, 'https://www.ens2m.fr'),
(2023, 99, 'B', 'ENSPIMA', 34, 'https://enspima.bordeaux-inp.fr/fr'),
(2023, 111, 'CCC', 'ENSSAT Lannion', 33, 'https://www.enssat.fr/'),
(2023, 25, 'AA', 'ENSTA Bretagne', 57, 'https://www.ensta-bretagne.fr/fr'),
(2023, 6, 'AAA', 'ENSTA Paris', 76, 'https://www.ensta-paris.fr'),
(2023, 72, 'BB', 'ENSTBB Bordeaux', 39, 'https://enstbb.bordeaux-inp.fr'),
(2023, 176, 'C', 'ENSTIB Épinal', 21, 'https://www.enstib.univ-lorraine.fr'),
(2023, 28, 'AA', 'ENTPE Lyon', 56, 'https://www.entpe.fr'),
(2023, 40, 'A', 'EOST Strasbourg', 48, 'https://eost.unistra.fr'),
(2023, 80, 'BB', 'EPF', 37, 'http://www.epf.fr'),
(2023, 71, 'BB', 'EPISEN', 40, 'https://episen.u-pec.fr'),
(2023, 89, 'B', 'EPITA', 36, 'https://toulouse.epita.fr'),
(2023, 179, 'C', 'ESAIP Angers', 20, 'https://www.esaip.org'),
(2023, 175, 'C', 'ESB Nantes', 21, 'https://www.ecoledubois.fr'),
(2023, 17, 'AA', 'ESBS', 64, 'https://www.esbs.unistra.fr'),
(2023, 75, 'BB', 'ESCOM', 38, 'https://www.escom.fr'),
(2023, 165, 'C', 'ESEO Angers', 23, 'https://www.eseo.fr'),
(2023, 172, 'C', 'ESGT', 22, 'https://www.esgt.cnam.fr/esgt/'),
(2023, 133, 'CC', 'ESI Reims', 30, 'https://www.univ-reims.fr/esireims/'),
(2023, 121, 'CCC', 'ESIAB Brest', 32, 'https://www.univ-brest.fr/esiab'),
(2023, 79, 'BB', 'ESIEA Paris', 37, 'https://www.esiea.fr'),
(2023, 106, 'CCC', 'ESIEE Paris', 33, 'https://www.esiee.fr'),
(2023, 90, 'B', 'ESIGELEC Rouen', 36, 'https://www.esigelec.fr'),
(2023, 161, 'C', 'ESIR Rennes', 26, 'http://www.esir.univ-rennes1.fr'),
(2023, 147, 'CC', 'ESIREM Dijon', 28, 'https://esirem.u-bourgogne.fr'),
(2023, 174, 'C', 'ESIROI', 21, 'https://esiroi.univ-reunion.fr'),
(2023, 149, 'CC', 'ESITC Caen', 28, 'https://www.esitc-caen.fr'),
(2023, 143, 'CC', 'ESITech', 29, 'http://esitech.univ-rouen.fr'),
(2023, 150, 'CC', 'ESIX', 28, 'http://esix.unicaen.fr'),
(2023, 152, 'CC', 'ESME', 28, 'https://www.esme.fr'),
(2023, 3, 'AAA', 'ESPCI Paris', 79, 'https://www.espci.psl.eu/fr'),
(2023, 76, 'BB', 'ESTACA', 38, 'https://www.estaca.fr'),
(2023, 178, 'C', 'ESTIA Bidart', 20, 'https://www.estia.fr'),
(2023, 66, 'BBB', 'ESTP Paris', 41, 'https://www.estp.fr/troyes'),
(2023, 38, 'A', 'EURECOM', 50, 'https://www.eurecom.fr/fr'),
(2023, 39, 'A', 'Grenoble INP - ENSE3', 48, 'https://ense3.grenoble-inp.fr'),
(2023, 24, 'AA', 'Grenoble INP - Ensimag', 57, 'https://ensimag.grenoble-inp.fr'),
(2023, 124, 'CCC', 'Grenoble INP - Esisar', 32, 'https://esisar.grenoble-inp.fr'),
(2023, 44, 'A', 'Grenoble INP - Génie industriel', 47, 'https://genie-industriel.grenoble-inp.fr'),
(2023, 177, 'C', 'Grenoble INP - Pagora', 20, 'https://pagora.grenoble-inp.fr'),
(2023, 16, 'AA', 'Grenoble INP - Phelma', 64, 'https://phelma.grenoble-inp.fr'),
(2023, 153, 'C', 'ICAM', 28, 'https://www.icam.fr'),
(2023, 14, 'AA', 'IMT Atlantique', 65, 'https://www.imt-atlantique.fr'),
(2023, 57, 'BBB', 'IMT Mines Albi', 43, 'https://www.imt-mines-albi.fr'),
(2023, 54, 'BBB', 'IMT Mines Alès', 44, 'https://www.mines-ales.fr'),
(2023, 45, 'BBB', 'IMT Nord Europe', 46, 'https://www.imt-lille-douai.fr'),
(2023, 96, 'B', 'INSA Centre Val de Loire', 35, 'https://www.insa-centrevaldeloire.fr'),
(2023, 81, 'BB', 'INSA Hauts de France', 37, 'https://www.insa-hautsdefrance.fr/insa-hdf.html'),
(2023, 42, 'A', 'INSA Lyon', 48, 'https://www.insa-lyon.fr'),
(2023, 49, 'BBB', 'INSA Rennes', 45, 'https://www.insa-rennes.fr'),
(2023, 65, 'BBB', 'INSA Rouen', 41, 'https://www.insa-rouen.fr'),
(2023, 94, 'B', 'INSA Strasbourg', 35, 'https://www.insa-strasbourg.fr'),
(2023, 41, 'A', 'INSA Toulouse', 48, 'https://www.insa-toulouse.fr'),
(2023, 61, 'BBB', 'Institut Agro – Dijon', 41, 'https://www.agrosupdijon.fr'),
(2023, 32, 'AA', 'Institut Agro – Montpellier', 54, 'https://www.institut-agro-montpellier.fr'),
(2023, 82, 'BB', 'Institut Agro – Rennes', 37, 'https://www.agrocampus-ouest.fr'),
(2023, 101, 'B', 'IPSA', 34, 'https://www.ipsa.fr'),
(2023, 9, 'AAA', 'ISAE - SUPAERO', 73, 'https://www.isae-supaero.fr'),
(2023, 50, 'BBB', 'ISAE-ENSMA Poitiers', 45, 'https://www.ensma.fr'),
(2023, 58, 'BBB', 'ISAE-Supméca', 42, 'https://www.isae-supmeca.fr/'),
(2023, 148, 'CC', 'ISAT Nevers', 28, 'https://www.isat.fr'),
(2023, 154, 'C', 'ISEL Le Havre', 27, 'https://www.isel-logistique.fr'),
(2023, 183, 'C', 'ISEN Méditerranée', 18, 'https://www.isen-mediterranee.fr'),
(2023, 184, 'C', 'ISEN Yncrea Ouest', 18, 'https://isen-brest.fr'),
(2023, 140, 'CC', 'ISEP Paris', 29, 'https://www.isep.fr'),
(2023, 86, 'B', 'ISIFC Besançon', 36, 'https://isifc.univ-fcomte.fr'),
(2023, 130, 'CC', 'ISIMA Clermont-Ferrand', 31, 'https://www.isima.fr'),
(2023, 144, 'CC', 'ISIS Castres', 29, 'https://isis.univ-jfc.fr'),
(2023, 185, 'C', 'ISMANS', 15, 'https://ismans.cesi.fr'),
(2023, 157, 'C', 'ISTY', 27, 'https://www.isty.uvsq.fr'),
(2023, 167, 'C', 'ITECH', 23, 'https://www.itech.fr'),
(2023, 164, 'C', 'JUNIA HEI', 24, 'https://www.junia.com/fr/junia/programme-grande-ecole-hei/'),
(2023, 170, 'C', 'JUNIA ISEN', 22, 'https://www.junia.com/fr/junia/programme-grande-ecole-isen/'),
(2023, 12, 'AA', 'MINES de NANCY', 68, 'https://www.mines-nancy.univ-lorraine.fr'),
(2023, 2, 'AAA', 'MINES Paris', 87, 'https://www.mines-paristech.fr'),
(2023, 21, 'AA', 'MINES Saint-Étienne', 61, 'https://www.mines-stetienne.fr'),
(2023, 139, 'CC', 'ONIRIS Nantes', 29, 'https://www.oniris-nantes.fr'),
(2023, 168, 'C', 'Paoli Tech', 23, 'https://paolitech.universita.corsica'),
(2023, 136, 'CC', 'Polytech Angers', 29, 'http://www.polytech-angers.fr/fr/index.html'),
(2023, 156, 'C', 'Polytech Annecy-Chambéry', 27, 'https://www.polytech.univ-smb.fr'),
(2023, 137, 'CC', 'Polytech Clermont-Ferrand', 29, 'https://polytech.univ-bpclermont.fr'),
(2023, 126, 'CCC', 'Polytech Grenoble', 31, 'https://www.polytech-grenoble.fr'),
(2023, 134, 'CC', 'Polytech Lille', 30, 'https://www.polytech-lille.fr'),
(2023, 100, 'B', 'Polytech Lyon', 34, 'https://polytech.univ-lyon1.fr'),
(2023, 107, 'CCC', 'Polytech Marseille', 33, 'http://polytech.univ-amu.fr'),
(2023, 102, 'B', 'Polytech Montpellier', 34, 'https://www.polytech.umontpellier.fr'),
(2023, 135, 'CC', 'Polytech Nancy', 30, 'https://www.polytech-nancy.univ-lorraine.fr'),
(2023, 141, 'CC', 'Polytech Nantes', 29, 'https://web.polytech.univ-nantes.fr'),
(2023, 118, 'CCC', 'Polytech Nice-Sophia', 32, 'https://polytech.univ-cotedazur.fr'),
(2023, 125, 'CCC', 'Polytech Orléans', 31, 'https://www.univ-orleans.fr/polytech/'),
(2023, 60, 'BBB', 'Polytech Paris-Saclay', 42, 'https://www.polytech.universite-paris-saclay.fr'),
(2023, 93, 'B', 'Polytech Sorbonne', 35, 'https://www.polytech.sorbonne-universite.fr'),
(2023, 142, 'CC', 'Polytech Tours', 29, 'https://www.polytech.univ-tours.fr'),
(2023, 5, 'AAA', 'PONTS ParisTech', 79, 'https://www.enpc.fr'),
(2023, 155, 'C', 'SeaTech Toulon', 27, 'https://www.seatech.fr'),
(2023, 114, 'CCC', 'Sigma Clermont', 33, 'https://www.sigma-clermont.fr'),
(2023, 119, 'CCC', 'Sup Galilée Villetaneuse', 32, 'https://galilee.univ-paris13.fr'),
(2023, 88, 'B', 'SupBiotech', 36, 'https://www.supbiotech.fr'),
(2023, 55, 'BBB', 'SUPENR', 43, 'https://sup-enr.univ-perp.fr'),
(2023, 29, 'AA', 'SupOptique', 55, 'https://www.institutoptique.fr'),
(2023, 87, 'B', 'Télécom Nancy', 36, 'https://telecomnancy.univ-lorraine.fr'),
(2023, 8, 'AAA', 'Télécom Paris', 73, 'https://www.telecom-paristech.fr'),
(2023, 59, 'BBB', 'Télécom Physique Strasbourg', 42, 'https://www.telecom-physique.fr'),
(2023, 120, 'CCC', 'Télécom St Étienne', 32, 'https://www.telecom-st-etienne.fr'),
(2023, 30, 'AA', 'Télécom SudParis', 54, 'https://www.telecom-sudparis.eu'),
(2023, 166, 'C', 'UniLaSalle', 23, 'https://www.unilasalle.fr'),
(2023, 85, 'BB', 'UTT', 37, 'https://www.utt.fr'),
(2023, 122, 'CCC', 'VetAgro Sup', 32, 'https://www.vetagrosup.fr');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
