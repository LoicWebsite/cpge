-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : ven. 08 mars 2024 à 14:54
-- Version du serveur : 5.5.61-38.13-log
-- Version de PHP : 8.0.12

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

--
-- Déchargement des données de la table `Figaro`
--

INSERT INTO `Figaro` (`An`, `Rang`, `Ecole`, `Point`, `UrlFigaro`) VALUES
(2024, 12, 'AgroParisTech', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34622-agroparistech-masters-admission-et-salaire-a-la-sortie/'),
(2024, 23, 'Arts et Métiers', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/25264-ecole-nationale-superieure-d-arts-et-metiers-dg/'),
(2024, 80, 'Bordeaux INP - ENSC', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34662-ensc-bordeaux-presentation-et-prix/'),
(2024, 36, 'Bordeaux INP - ENSEIRB-MATMECA', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34664-enseirb-matmeca-admission-classement-et-alumni/'),
(2024, 84, 'Bordeaux Sciences Agro', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/26794-cpbx-bordeaux-sciences-agro/'),
(2024, 13, 'CENTRALE Lille', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/25244-centrale-lille/'),
(2024, 7, 'CENTRALE Lyon', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34616-centrale-lyon-diplomes-admission-et-classement/'),
(2024, 27, 'Centrale Méditerranée', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34644-centrale-mediterranee-formations-et-admission/'),
(2024, 11, 'CENTRALE Nantes', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34642-centrale-nantes-diplomes-et-admission/'),
(2024, 3, 'CentraleSupélec', 17, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34604-centrale-supelec-concours-classement-et-prix/'),
(2024, 17, 'Chimie ParisTech', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34618-chimie-paristech-admission-et-debouches/'),
(2024, 1, 'Ecole Polytechnique', 19, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/30608-ecole-polytechnique/'),
(2024, 29, 'ECPM Strasbourg', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34580-ecpm-concours-langues-disponibles-et-frais-de-scolarite/'),
(2024, 47, 'EIDD Paris', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34744-eidd-formations-classement-et-frais-de-scolarite/'),
(2024, 60, 'EIVP Paris', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34722-eivp-admission-concours-et-programmes/'),
(2024, 18, 'ENAC Toulouse', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34716-enac-admission-bac-requis-et-frais-de-scolarite/'),
(2024, 67, 'ENGEES Strasbourg', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34752-engees-admission-frais-de-scolarite-et-salaire/'),
(2024, 9, 'ENSAE Paris', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34614-ensae-paris-classement-admission-et-salaire/'),
(2024, 55, 'ENSAI Rennes', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34706-ensai-filieres-admission-et-prix/'),
(2024, 47, 'ENSAIA', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34684-ensaia-masters-admission-et-classement/'),
(2024, 47, 'ENSAIT Roubaix', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34678-ensait-masters-proposes-concours-et-prix/'),
(2024, 72, 'ENSAT Toulouse', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34712-ensat-formations-et-concours/'),
(2024, 32, 'ENSC Lille', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34754-ensc-lille-classement-admission-et-frais-de-scolarite/'),
(2024, 39, 'ENSC Montpellier', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34756-enscm-classement-frais-de-scolarite-et-inscription/'),
(2024, 80, 'ENSC Mulhouse', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34588-ensc-mulhouse-admission-et-classement/'),
(2024, 55, 'ENSC Rennes', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34586-ensc-rennes-admission-frais-de-scolarite-et-classement/'),
(2024, 36, 'ENSEA Cergy', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34656-ensea-filieres-admission-et-classement/'),
(2024, 18, 'ENSEEIHT', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34652-enseeiht-toulouse-inp-programmes-concours/'),
(2024, 67, 'ENSEGID', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34742-ensegid-diplomes-inscription-et-debouches/'),
(2024, 42, 'Ensem', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34686-ensem-inscription-debouches-et-prix/'),
(2024, 32, 'ENSG Nancy', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34688-ensg-nancy-diplomes-et-salaire-a-la-sortie/'),
(2024, 72, 'ENSG-Géomatique', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34728-ensg-geomatique-presentation-et-salaire-a-la-sortie/'),
(2024, 72, 'ENSGTI Pau', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34500-cpi-pau-ensgti/'),
(2024, 67, 'ENSI Poitiers', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34700-ensip-inscription-diplome-et-frais-de-scolarite/'),
(2024, 47, 'ENSIACET', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34714-ensiacet-specialites-admission-et-frais/'),
(2024, 32, 'ENSIC', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34690-ensic-admission-concours-et-tarif/'),
(2024, 42, 'ENSICAEN', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/32458-ensicaen/'),
(2024, 44, 'ENSIIE Evry', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34730-ensiie-concours-alternance-et-tarifs/'),
(2024, 67, 'ENSMAC', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34736-ensmac-admission-classement-et-frais-de-scolarite/'),
(2024, 60, 'ENSMM Besançon', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34658-ensmm-presentation-et-debouches/'),
(2024, 86, 'ENSPIMA', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34758-enspima-classement-frais-de-scolarite-et-salaire/'),
(2024, 60, 'ENSSAT Lannion', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34704-enssat-formations-et-admission/'),
(2024, 23, 'ENSTA Bretagne', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34650-ensta-bretagne-concours-et-classement/'),
(2024, 6, 'ENSTA Paris', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34620-ensta-paris-specialites-concours-et-salaire-a-la-sortie/'),
(2024, 55, 'ENSTBB Bordeaux', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34432-cpbx-enstbb/'),
(2024, 47, 'ENSTIB Epinal', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34692-enstib-masters-proposes-concours-et-frais/'),
(2024, 44, 'ENTPE Lyon', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34680-entpe-formations-concours-et-classement/'),
(2024, 39, 'EOST Strasbourg', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34708-eost-admission-et-frais-de-scolarite/'),
(2024, 55, 'EPISEN', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34726-episen-classement-frais-de-scolarite-et-concours/'),
(2024, 84, 'ESB Nantes', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34696-esb-tout-savoir-sur-l-ecole-du-bois/'),
(2024, 32, 'ESBS', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34710-esbs-programmes-prix-et-classement/'),
(2024, 65, 'ESIAB Brest', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34702-esiab-presentation-parcours-et-campus/'),
(2024, 78, 'Esix Normandie', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34670-esix-normandie-presentation-et-admission/'),
(2024, 10, 'ESPCI Paris', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34612-espci-paris-psl-admission-prepa-et-classement/'),
(2024, 60, 'ESTIA Bidart', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/28804-cpbx-estia/'),
(2024, 23, 'ESTP Paris', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34648-estp-prepa-admission-et-prix/'),
(2024, 29, 'Grenoble INP - Ense3', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34672-ense3-admission-et-tarif/'),
(2024, 18, 'Grenoble INP - Ensimag', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34638-ensimage-grenoble-inp-niveau-et-classement/'),
(2024, 27, 'Grenoble INP - Génie industriel', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34674-genie-industriel-grenoble-inp-admission-et-masters-proposes/'),
(2024, 72, 'Grenoble INP - Pagora', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34676-pagora-admission-frais-de-scolarite-et-classement/'),
(2024, 18, 'Grenoble INP - Phelma', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34646-phelma-grenoble-inp-filieres-admission-et-classement/'),
(2024, 14, 'IMT Atlantique', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34632-imt-atlantique-diplomes-concours-et-tarif/'),
(2024, 55, 'IMT Mines Albi', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34720-imt-mines-albi-formations-admission-et-frais/'),
(2024, 36, 'IMT Mines Alès', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34654-imt-mines-d-ales-formations-et-tarifs/'),
(2024, 44, 'IMT Nord Europe', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34482-imt-nord-europe/'),
(2024, 47, 'Institut Agro – Montpellier', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34682-institut-agro-montpellier-diplomes-prix-et-debouches/'),
(2024, 8, 'ISAE - SUPAERO', 15, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34634-isae-supaero-tout-sur-l-ecole-des-pilotes-de-ligne/'),
(2024, 39, 'ISAE-ENSMA Poitiers', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34698-isae-ensma-classement-concours-et-frais-de-scolarite/'),
(2024, 47, 'Isae-Supméca', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34724-supmeca-presentation-difficulte-et-classement/'),
(2024, 78, 'ISIFC Besançon', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34660-isifc-masters-et-admission/'),
(2024, 72, 'ISIMA', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34592-isima-admission-frais-de-scolarite-et-classement/'),
(2024, 67, 'ITECH Lyon', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34762-itech-lyon-classement-prix-et-salaire-a-la-sortie/'),
(2024, 14, 'MINES de NANCY', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34630-mines-nancy-programmes-et-salaire-a-la-sortie/'),
(2024, 2, 'MINES Paris', 17, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34606-mines-paris-psl-admission-et-classement/'),
(2024, 14, 'MINES Saint-Etienne', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34640-mines-saint-etienne-programmes-et-admission/'),
(2024, 80, 'ONIRIS Nantes', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/25222-ecole-nationale-veterinaire-agroalimentaire-et-de-l-alimentation-de-nantes-atlantique-oniris-site-de-la-chantrerie/'),
(2024, 83, 'Paoli Tech', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34732-paoli-tech-tout-sur-l-ecole-d-ingenieurs-corse/'),
(2024, 4, 'PONTS ParisTech', 16, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34608-ecole-des-ponts-paristech-masters-disponibles-et-admission/'),
(2024, 72, 'SeaTech Toulon', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34764-seatech-admission-classement-et-prix/'),
(2024, 47, 'Sigma Clermont', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34584-sigma-specialites-alternance-et-concours/'),
(2024, 26, 'SupOptique', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34626-supoptique-programmes-et-concours/'),
(2024, 60, 'Télécom Nancy', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34694-telecom-nancy-programmes-et-admission/'),
(2024, 5, 'Télécom Paris', 16, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34610-telecom-paris-formations-prepa-et-salaire-a-la-sortie/'),
(2024, 29, 'Télécom Physique Strasbourg', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34746-telecom-physique-strasbourg-presentation-concours-et-salaire/'),
(2024, 65, 'Télécom St Etienne', 13, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34596-telecom-saint-etienne-admission-classement-et-prix/'),
(2024, 22, 'Télécom SudParis', 14, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34624-telecom-sudparis-prepa-frais-de-scolarite-et-salaire/'),
(2024, 87, 'VetAgro Sup', 12, 'https://etudiant.lefigaro.fr/annuaire/ecole-d-ingenieur/34740-vetagro-sup-laboratoires-classement-et-prix/');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
