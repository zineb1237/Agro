-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3307
-- Généré le : lun. 06 avr. 2026 à 20:35
-- Version du serveur : 11.4.9-MariaDB
-- Version de PHP : 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `agriculture_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `agriculteur`
--

DROP TABLE IF EXISTS `agriculteur`;
CREATE TABLE IF NOT EXISTS `agriculteur` (
  `id` int(11) NOT NULL,
  `typeParcelle` varchar(100) DEFAULT NULL,
  `nbParcelles` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande`
--

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,
  `dateCommande` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `totalPrix` double DEFAULT NULL,
  `adresseLivraison` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commande_produit`
--

DROP TABLE IF EXISTS `commande_produit`;
CREATE TABLE IF NOT EXISTS `commande_produit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commande_id` int(11) DEFAULT NULL,
  `produit_id` int(11) DEFAULT NULL,
  `quantite` int(11) DEFAULT NULL,
  `prixUnitaire` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `commande_id` (`commande_id`),
  KEY `produit_id` (`produit_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `diagnostic_maladie`
--

DROP TABLE IF EXISTS `diagnostic_maladie`;
CREATE TABLE IF NOT EXISTS `diagnostic_maladie` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcelle_id` int(11) DEFAULT NULL,
  `maladie` varchar(100) DEFAULT NULL,
  `niveauGravite` varchar(50) DEFAULT NULL,
  `recommandation` text DEFAULT NULL,
  `dateDiagnostic` date DEFAULT NULL,
  `niveauConfiance` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcelle_id` (`parcelle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donnees_capteur`
--

DROP TABLE IF EXISTS `donnees_capteur`;
CREATE TABLE IF NOT EXISTS `donnees_capteur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcelle_id` int(11) DEFAULT NULL,
  `humiditeSol` double DEFAULT NULL,
  `pH` double DEFAULT NULL,
  `azote` double DEFAULT NULL,
  `phosphore` double DEFAULT NULL,
  `potassium` double DEFAULT NULL,
  `eauIrriguee` double DEFAULT NULL,
  `dateMesure` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcelle_id` (`parcelle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fournisseur`
--

DROP TABLE IF EXISTS `fournisseur`;
CREATE TABLE IF NOT EXISTS `fournisseur` (
  `id` int(11) NOT NULL,
  `typeProduits` varchar(100) DEFAULT NULL,
  `rating` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parcelle`
--

DROP TABLE IF EXISTS `parcelle`;
CREATE TABLE IF NOT EXISTS `parcelle` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `proprietaire_id` int(11) DEFAULT NULL,
  `superficie` double DEFAULT NULL,
  `localisation` varchar(255) DEFAULT NULL,
  `typeSol` varchar(100) DEFAULT NULL,
  `culture` varchar(100) DEFAULT NULL,
  `datePlantation` date DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proprietaire_id` (`proprietaire_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `passeport_numerique`
--

DROP TABLE IF EXISTS `passeport_numerique`;
CREATE TABLE IF NOT EXISTS `passeport_numerique` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcelle_id` int(11) DEFAULT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `qrCode` varchar(255) DEFAULT NULL,
  `historiqueActions` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcelle_id` (`parcelle_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `prediction_rendement`
--

DROP TABLE IF EXISTS `prediction_rendement`;
CREATE TABLE IF NOT EXISTS `prediction_rendement` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcelle_id` int(11) DEFAULT NULL,
  `datePrediction` date DEFAULT NULL,
  `rendementPrevu` double DEFAULT NULL,
  `niveauConfiance` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcelle_id` (`parcelle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `produit`
--

DROP TABLE IF EXISTS `produit`;
CREATE TABLE IF NOT EXISTS `produit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fournisseur_id` int(11) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `type` enum('Semence','Engrais','Materiel','Pesticide') DEFAULT NULL,
  `prixUnitaire` double DEFAULT NULL,
  `stock` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `dateAjout` datetime DEFAULT NULL,
  `promotion` tinyint(1) DEFAULT NULL,
  `certificationBio` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fournisseur_id` (`fournisseur_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `recommandation`
--

DROP TABLE IF EXISTS `recommandation`;
CREATE TABLE IF NOT EXISTS `recommandation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parcelle_id` int(11) DEFAULT NULL,
  `dateEmission` datetime DEFAULT NULL,
  `typeAction` varchar(50) DEFAULT NULL,
  `quantite` double DEFAULT NULL,
  `remarque` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `parcelle_id` (`parcelle_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `motDePasse` varchar(255) DEFAULT NULL,
  `role` varchar(50) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
