DROP TABLE IF EXISTS `agriculteur`;
CREATE TABLE IF NOT EXISTS `agriculteur` (
  `id` int(11) NOT NULL,
  `typeParcelle` varchar(100) DEFAULT NULL,
  `nbParcelles` int(11) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `commande`;
CREATE TABLE IF NOT EXISTS `commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) DEFAULT NULL,  -- Référence à utilisateur.id
  `dateCommande` date DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `totalPrix` double DEFAULT NULL,
  `adresseLivraison` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `client_id` (`client_id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: produit
-- --------------------------------------------------------
DROP TABLE IF EXISTS `produit`;
CREATE TABLE IF NOT EXISTS `produit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fournisseur_id` int(11) DEFAULT NULL,  -- Référence à utilisateur.id (rôle vendeur)
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
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `fournisseur`;
CREATE TABLE IF NOT EXISTS `fournisseur` (
  `id` int(11) NOT NULL,  -- Référence à utilisateur.id
  `typeProduits` varchar(100) DEFAULT NULL,
  `rating` double DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
