-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: yitro_learning
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `completions`
--

DROP TABLE IF EXISTS `completions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `completions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `module_id` int NOT NULL,
  `cours_id` int NOT NULL,
  `date_completion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_id` (`utilisateur_id`,`module_id`),
  KEY `module_id` (`module_id`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `completions_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `completions_ibfk_2` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`),
  CONSTRAINT `completions_ibfk_3` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `completions`
--

LOCK TABLES `completions` WRITE;
/*!40000 ALTER TABLE `completions` DISABLE KEYS */;
INSERT INTO `completions` VALUES (2,4,6,9,'2025-11-02 11:00:05');
/*!40000 ALTER TABLE `completions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `sujet` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contact`
--

LOCK TABLES `contact` WRITE;
/*!40000 ALTER TABLE `contact` DISABLE KEYS */;
/*!40000 ALTER TABLE `contact` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `contenu_formations`
--

DROP TABLE IF EXISTS `contenu_formations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `contenu_formations` (
  `id_contenu` int NOT NULL AUTO_INCREMENT,
  `formation_id` int NOT NULL,
  `sous_formation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_contenu`),
  KEY `fk_contenu_formation_id` (`formation_id`),
  CONSTRAINT `fk_contenu_formation_id` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id_formation`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `contenu_formations`
--

LOCK TABLES `contenu_formations` WRITE;
/*!40000 ALTER TABLE `contenu_formations` DISABLE KEYS */;
INSERT INTO `contenu_formations` VALUES (1,4,'Excel et analyse de données','2025-10-16 06:32:43'),(2,1,'SEO / Référencement','2025-10-16 06:32:53'),(3,7,'Arts visuels','2025-10-16 06:33:22'),(4,5,'Finance personnelle','2025-10-22 06:53:30'),(5,5,'Bourse et trading','2025-10-22 06:53:47'),(6,2,'Formations Fitness','2025-10-22 06:54:03'),(10,7,'Design & graphisme','2025-11-02 10:31:14'),(12,6,'Productivité et gestion du temps','2025-11-02 10:32:08'),(13,1,'Marketing des réseaux sociaux','2025-11-02 10:32:31'),(14,1,'Publicité en ligne','2025-11-02 10:32:51'),(15,4,'Bureautique générale','2025-11-02 10:33:08');
/*!40000 ALTER TABLE `contenu_formations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cours`
--

DROP TABLE IF EXISTS `cours`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cours` (
  `id` int NOT NULL AUTO_INCREMENT,
  `formateur_id` int NOT NULL,
  `formation_id` int NOT NULL,
  `contenu_formation_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau` enum('Débutant','Intermédiaire','Avancé') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `formateur_id` (`formateur_id`),
  KEY `formation_id` (`formation_id`),
  KEY `contenu_formation_id` (`contenu_formation_id`),
  CONSTRAINT `cours_ibfk_1` FOREIGN KEY (`formateur_id`) REFERENCES `formateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cours_formation` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id_formation`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_cours_sous_formation` FOREIGN KEY (`contenu_formation_id`) REFERENCES `contenu_formations` (`id_contenu`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cours`
--

LOCK TABLES `cours` WRITE;
/*!40000 ALTER TABLE `cours` DISABLE KEYS */;
INSERT INTO `cours` VALUES (2,1,4,1,'Test_cours_excel','Description_Test_cours',20.00,'course_1760601459.png',NULL,'2025-09-18 10:11:12'),(4,1,4,1,'Developper avec Python','Apprendre a coder avec python',30.00,'course_1760619130.png','Débutant','2025-10-16 12:52:10'),(8,1,1,2,'titre cours','description coours',12.00,'course_1761042512.jpeg','Débutant','2025-10-21 10:28:32'),(9,1,7,3,'Arts pour deco','Comment apprendre a creer des arts pour deco chez soi?',50.00,'course_1762079870.png','Débutant','2025-11-02 10:37:50');
/*!40000 ALTER TABLE `cours` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formateurs`
--

DROP TABLE IF EXISTS `formateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_prenom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ville_pays` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `intitule_metier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `experience_formation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `detail_experience` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cv` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `categories` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `autre_domaine` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `titre_cours` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objectif` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `public_cible` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `detail_complementaire` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `formats` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `format_autre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree_estimee` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type_formation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `motivation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `valeurs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `profil_public` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `statut` enum('en_attente','verifie','premium','partenaire') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `code_entree` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formateurs`
--

LOCK TABLES `formateurs` WRITE;
/*!40000 ALTER TABLE `formateurs` DISABLE KEYS */;
INSERT INTO `formateurs` VALUES (1,'Noella','harmanohisitraka@gmail.com','0347852412','Madagascar','https://www.linkedin.com/in/noella-1','Developpeuse','1 à 2 ans','Travailler dans une entreprise blablabla','68cbc8d99c7c5_Doc1.docx','on, on','','Developper avec Python','Apprendre et capable de developper avec python','Débutants','blablab','on, on','','&lt; 1 heure','on','Pourqoui rejoindre ytro....','on','on','en_attente','2025-09-18 08:54:49',NULL,'$2y$12$YbBwZMf8U3FyfQfmAGQSZemZOxvPDMBdzOf/spa1wYJ3zadycxjAy');
/*!40000 ALTER TABLE `formateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `formations`
--

DROP TABLE IF EXISTS `formations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `formations` (
  `id_formation` int NOT NULL AUTO_INCREMENT,
  `nom_formation` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_formation`),
  UNIQUE KEY `nom_formation` (`nom_formation`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `formations`
--

LOCK TABLES `formations` WRITE;
/*!40000 ALTER TABLE `formations` DISABLE KEYS */;
INSERT INTO `formations` VALUES (1,'Marketing Digital','2025-10-16 06:31:15'),(2,'Santé & Bien-être','2025-10-16 06:31:24'),(3,'Business & Entrepreneuriat','2025-10-16 06:31:28'),(4,'Compétences Bureautiques & Outil','2025-10-16 06:31:53'),(5,'Finance & Investissement','2025-10-16 06:32:02'),(6,'Développement Personnel (Soft Skills, productivité)','2025-10-16 06:32:10'),(7,'Arts, Design & Artisanat','2025-10-16 06:32:23');
/*!40000 ALTER TABLE `formations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum`
--

DROP TABLE IF EXISTS `forum`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cours_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date_creation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `forum_ibfk_1` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum`
--

LOCK TABLES `forum` WRITE;
/*!40000 ALTER TABLE `forum` DISABLE KEYS */;
INSERT INTO `forum` VALUES (2,2,'Comment faire des calculs en excel,par Toto','description comment faire des calculs en excel,par Toto','2025-11-02 14:51:54'),(3,4,'Autre creer par Toto','blajbkjs','2025-11-02 16:12:18');
/*!40000 ALTER TABLE `forum` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forum_messages`
--

DROP TABLE IF EXISTS `forum_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forum_messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cours_id` int DEFAULT NULL,
  `utilisateur_id` int DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `date` datetime DEFAULT NULL,
  `lu` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `cours_id` (`cours_id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  CONSTRAINT `forum_messages_ibfk_1` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`),
  CONSTRAINT `forum_messages_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forum_messages`
--

LOCK TABLES `forum_messages` WRITE;
/*!40000 ALTER TABLE `forum_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `forum_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `cours_id` int NOT NULL,
  `date_inscription` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `statut_paiement` enum('en_attente','paye','echec') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'en_attente',
  PRIMARY KEY (`id`),
  UNIQUE KEY `utilisateur_id` (`utilisateur_id`,`cours_id`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscriptions`
--

LOCK TABLES `inscriptions` WRITE;
/*!40000 ALTER TABLE `inscriptions` DISABLE KEYS */;
INSERT INTO `inscriptions` VALUES (1,3,2,'2025-09-18 10:31:07','paye'),(2,3,8,'2025-10-26 09:55:11','paye'),(3,1,9,'2025-11-02 10:39:25','paye'),(4,4,8,'2025-11-02 10:55:38','paye'),(5,4,9,'2025-11-02 10:56:59','paye');
/*!40000 ALTER TABLE `inscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_activite`
--

DROP TABLE IF EXISTS `journal_activite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_activite` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `details` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `journal_activite_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_activite`
--

LOCK TABLES `journal_activite` WRITE;
/*!40000 ALTER TABLE `journal_activite` DISABLE KEYS */;
INSERT INTO `journal_activite` VALUES (1,1,'Visualisation progression apprenants','Consultation de la page de progression','2025-09-18 05:04:27'),(2,1,'Visualisation progression apprenants','Consultation de la page de progression','2025-09-18 05:32:37'),(3,1,'Visualisation progression apprenants','Consultation de la page de progression','2025-09-18 05:33:27'),(4,1,'Visualisation progression apprenants','Consultation de la page de progression','2025-09-18 05:33:35'),(5,1,'Envoi code formateur','Formateur ID: 1, Email: harmanohisitraka@gmail.com, Code: 5cd39b312dc6fa44','2025-09-18 09:00:40'),(6,1,'Ajout formation','Formation ajoutée: Marketing Digital','2025-10-16 06:31:15'),(7,1,'Ajout formation','Formation ajoutée: Santé & Bien-être','2025-10-16 06:31:24'),(8,1,'Ajout formation','Formation ajoutée: Business & Entrepreneuriat','2025-10-16 06:31:28'),(9,1,'Ajout formation','Formation ajoutée: Compétences Bureautiques & Outil','2025-10-16 06:31:54'),(10,1,'Ajout formation','Formation ajoutée: Finance & Investissement','2025-10-16 06:32:02'),(11,1,'Ajout formation','Formation ajoutée: Développement Personnel (Soft Skills, productivité)','2025-10-16 06:32:11'),(12,1,'Ajout formation','Formation ajoutée: Arts, Design & Artisanat','2025-10-16 06:32:24'),(13,1,'Ajout sous-formation','Sous-formation ajoutée: Excel et analyse de données (Formation ID: 4)','2025-10-16 06:32:44'),(14,1,'Ajout sous-formation','Sous-formation ajoutée: SEO / Référencement (Formation ID: 1)','2025-10-16 06:32:53'),(15,1,'Ajout sous-formation','Sous-formation ajoutée: Arts visuels (Formation ID: 7)','2025-10-16 06:33:22'),(16,1,'Ajout sous-formation','Sous-formation ajoutée: Finance personnelle (Formation ID: 5)','2025-10-22 06:53:30'),(17,1,'Ajout sous-formation','Sous-formation ajoutée: Bourse et trading (Formation ID: 5)','2025-10-22 06:53:47'),(18,1,'Ajout sous-formation','Sous-formation ajoutée: Formations Fitness (Formation ID: 2)','2025-10-22 06:54:03'),(19,1,'Ajout sous-formation','Sous-formation ajoutée: Arts visuels (Formation ID: 7)','2025-10-22 06:54:58'),(20,1,'Visualisation progression apprenants','Consultation de la page de progression','2025-11-02 07:29:09'),(21,1,'Ajout sous-formation','Sous-formation ajoutée: Arts visuels (Formation ID: 7)','2025-11-02 10:30:24'),(22,1,'Ajout sous-formation','Sous-formation ajoutée: Finance personnelle (Formation ID: 5)','2025-11-02 10:30:52'),(23,1,'Ajout sous-formation','Sous-formation ajoutée: Design & graphisme (Formation ID: 7)','2025-11-02 10:31:14'),(24,1,'Ajout sous-formation','Sous-formation ajoutée: Formations Fitness (Formation ID: 2)','2025-11-02 10:31:45'),(25,1,'Ajout sous-formation','Sous-formation ajoutée: Productivité et gestion du temps (Formation ID: 6)','2025-11-02 10:32:08'),(26,1,'Ajout sous-formation','Sous-formation ajoutée: Marketing des réseaux sociaux (Formation ID: 1)','2025-11-02 10:32:31'),(27,1,'Ajout sous-formation','Sous-formation ajoutée: Publicité en ligne (Formation ID: 1)','2025-11-02 10:32:51'),(28,1,'Ajout sous-formation','Sous-formation ajoutée: Bureautique générale (Formation ID: 4)','2025-11-02 10:33:08'),(29,1,'Suppression sous-formation','Suppression sous-formation ID: 7 (Formation ID: 7)','2025-11-02 10:33:32'),(30,1,'Suppression sous-formation','Suppression sous-formation ID: 8 (Formation ID: 7)','2025-11-02 10:33:36'),(31,1,'Suppression sous-formation','Suppression sous-formation ID: 11 (Formation ID: 2)','2025-11-02 10:34:00'),(32,1,'Suppression sous-formation','Suppression sous-formation ID: 9 (Formation ID: 5)','2025-11-02 10:34:14'),(33,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:11:40'),(34,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:14:21'),(35,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:26:51'),(36,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:36:07'),(37,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:38:29'),(38,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:44:19'),(39,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:45:14'),(40,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:45:48'),(41,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:46:24'),(42,1,'Visualisation des messages','Forum ID: 1','2025-11-02 14:46:33'),(43,1,'Visualisation des messages','Forum ID: 1','2025-11-02 15:00:38'),(44,1,'Visualisation des messages','Forum ID: 1','2025-11-02 15:01:41'),(45,1,'Visualisation des messages','Forum ID: 2','2025-11-02 15:01:52'),(46,1,'Visualisation des messages','Forum ID: 2','2025-11-02 15:02:05'),(47,4,'Visualisation des messages','Forum ID: 2','2025-11-02 16:12:56'),(48,4,'Visualisation des messages','Forum ID: 3','2025-11-02 16:13:13'),(49,4,'Visualisation des messages','Forum ID: 3','2025-11-02 16:13:52'),(50,1,'Visualisation des messages','Forum ID: 3','2025-11-02 16:15:15'),(51,1,'Visualisation des messages','Forum ID: 2','2025-11-02 17:19:35'),(52,1,'Visualisation des messages','Forum ID: 1','2025-11-02 17:19:42'),(53,1,'Suppression de forum','Forum ID: 1','2025-11-02 17:19:50'),(54,1,'Visualisation des messages','Forum ID: 2','2025-11-02 17:20:07'),(55,1,'Visualisation des messages','Forum ID: 3','2025-11-02 17:20:21'),(56,1,'Visualisation des messages','Forum ID: 2','2025-11-02 17:20:43'),(57,1,'Visualisation des messages','Forum ID: 2','2025-11-02 17:21:19'),(58,1,'Visualisation des messages','Forum ID: 3','2025-11-02 17:21:24'),(59,1,'Visualisation des messages','Forum ID: 3','2025-11-02 17:21:50'),(60,1,'Visualisation des messages','Forum ID: 3','2025-11-02 17:24:40'),(61,1,'Envoi code formateur','Formateur ID: 2, Email: harimino26noella@gmail.com, Code: 2385c1c7fa63f8a4','2025-11-02 17:43:55'),(62,1,'Suppression formateur','Formateur ID: 2','2025-11-02 17:47:30'),(63,1,'Visualisation des messages','Forum ID: 4','2025-11-02 17:49:53'),(64,1,'Suppression de forum','Forum ID: 4','2025-11-02 17:50:03'),(65,1,'Visualisation progression apprenants','Consultation de la page de progression','2025-11-02 15:54:47');
/*!40000 ALTER TABLE `journal_activite` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lecons`
--

DROP TABLE IF EXISTS `lecons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `format` enum('pdf','audio','video') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fichier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `lecons_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lecons`
--

LOCK TABLES `lecons` WRITE;
/*!40000 ALTER TABLE `lecons` DISABLE KEYS */;
INSERT INTO `lecons` VALUES (1,2,'Lecon voalohany','pdf','lecon_2_2_1758190272.pdf'),(2,5,'lecon du cours','pdf','lecon_8_5_1761042512_0.pdf'),(3,6,'lecon 1','pdf','lecon_9_6_1762079871_0.pdf');
/*!40000 ALTER TABLE `lecons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lecons_completees`
--

DROP TABLE IF EXISTS `lecons_completees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lecons_completees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int DEFAULT NULL,
  `lecon_id` int DEFAULT NULL,
  `date_completion` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `lecon_id` (`lecon_id`),
  CONSTRAINT `lecons_completees_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `lecons_completees_ibfk_2` FOREIGN KEY (`lecon_id`) REFERENCES `lecons` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lecons_completees`
--

LOCK TABLES `lecons_completees` WRITE;
/*!40000 ALTER TABLE `lecons_completees` DISABLE KEYS */;
/*!40000 ALTER TABLE `lecons_completees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cours_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cours_id` (`cours_id`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`cours_id`) REFERENCES `cours` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (2,2,'Module 1','Test_cours_module'),(5,8,'module du cours','Description module'),(6,9,'Module 1','Cours_arts');
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `post`
--

DROP TABLE IF EXISTS `post`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `post` (
  `id` int NOT NULL AUTO_INCREMENT,
  `auteur_id` int NOT NULL,
  `forum_id` int NOT NULL,
  `contenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_post` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`),
  KEY `forum_id` (`forum_id`),
  CONSTRAINT `post_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `post_ibfk_2` FOREIGN KEY (`forum_id`) REFERENCES `forum` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `post`
--

LOCK TABLES `post` WRITE;
/*!40000 ALTER TABLE `post` DISABLE KEYS */;
INSERT INTO `post` VALUES (4,4,3,'Bonjour par Toto','2025-11-02 16:12:50'),(5,4,3,'Salut ary oe','2025-11-02 16:13:42'),(6,3,3,'Bonjour,je suis MAnou','2025-11-02 16:14:38'),(8,1,3,'je suis le formateurs','2025-11-02 17:21:40');
/*!40000 ALTER TABLE `post` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `quiz_id` int NOT NULL,
  `texte` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reponse_correcte` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reponse_incorrecte_1` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reponse_incorrecte_2` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reponse_incorrecte_3` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (1,1,'Quelle est la sortie de ce code ? ```python print(type(5)) ```','<class \'int\'>','<class \'str\'>','<class \'list\'>','<class \'float\'>'),(2,2,'Quelle est la valeur de `x` après l\'exécution de ce code ? ```python x = 10 x += 5 ```','15','5','10','Une erreur'),(3,3,'Comment crée-t-on une boucle `for` qui parcourt les nombres de 0 à 4 (inclus) ?','for i in range(5):  Right answer  La fonction `range(5)` génère des nombres de 0 à 4. La boucle `for` les parcourt un par un.','for i in range(4):  Not quite  La fonction `range(4)` génère des nombres de 0 à 3, ce qui ne comprend pas 4.','while i < 5','for i from 0 to 4:');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quiz`
--

DROP TABLE IF EXISTS `quiz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quiz` (
  `id` int NOT NULL AUTO_INCREMENT,
  `module_id` int NOT NULL,
  `titre` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `score_minimum` int NOT NULL DEFAULT '70',
  PRIMARY KEY (`id`),
  KEY `module_id` (`module_id`),
  CONSTRAINT `quiz_ibfk_1` FOREIGN KEY (`module_id`) REFERENCES `modules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quiz`
--

LOCK TABLES `quiz` WRITE;
/*!40000 ALTER TABLE `quiz` DISABLE KEYS */;
INSERT INTO `quiz` VALUES (1,2,'Quelle est la sortie de ce code ? ```python print(type(5)) ```','A propos du learning python',10),(2,2,'Quelle est la valeur de `x` après l\'exécution de ce code ? ','Quelle est la valeur de `x` après l\'exécution de ce code ?\r\n```python\r\nx = 10\r\nx += 5\r\n```',10),(3,2,'Comment crée-t-on une boucle `for` qui parcourt les nombres de 0 à 4 ','Comment crée-t-on une boucle `for` qui parcourt les nombres de 0 à 4 (inclus) ?',10);
/*!40000 ALTER TABLE `quiz` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `resultats_quiz`
--

DROP TABLE IF EXISTS `resultats_quiz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `resultats_quiz` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `quiz_id` int NOT NULL,
  `score` int NOT NULL,
  `date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `quiz_id` (`quiz_id`),
  CONSTRAINT `resultats_quiz_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `resultats_quiz_ibfk_2` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `resultats_quiz`
--

LOCK TABLES `resultats_quiz` WRITE;
/*!40000 ALTER TABLE `resultats_quiz` DISABLE KEYS */;
/*!40000 ALTER TABLE `resultats_quiz` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pays` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `langue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `autre_langue` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `objectifs` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type_cours` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau_formation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `niveau_etude` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `acces_internet` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `appareil` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accessibilite` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `rgpd` tinyint(1) DEFAULT '0',
  `charte` tinyint(1) DEFAULT '0',
  `role` enum('apprenant','admin','moderator') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'apprenant',
  `actif` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Admin','admin@gmail.com','$2y$12$U6p2rY1CU09z9rni5JWRN.LUuAo8.eCo/9rC6uBxc/yNtrYNRWgcC',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,0,'admin',1,'2025-09-18 07:01:02'),(3,'Manohisitraka','manou@gmail.com','$2y$12$I7mqN3VWAOPj5lPUNUd28elF4nsv5H5SfvT.YGDx27PxDaA/4cl6.','0324578965','','Madagascar','Français','','Améliorer mes compétences pro','programmation','Je débute totalement','Aucune formation formelle','Oui','smartphone','lecture',1,1,'apprenant',1,'2025-09-18 10:23:02'),(4,'Toto','toto@gmail.com','$2y$12$NbCQ3HJNGBL2bS2MIJV17.5.xJnhBM5gfENJmOdavLFVf1v5pt4t2','0324578965','','Madagascar','Français','','Trouver un emploi ou me reconvertir, Apprendre pour moi-même','Anglais','Je débute totalement','Aucune formation formelle','Oui','smartphone','gros titre',1,1,'apprenant',1,'2025-10-26 10:12:53');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-11 14:14:33
