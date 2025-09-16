-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: salonsync
-- ------------------------------------------------------
-- Server version	8.0.42

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'chimwemwe nambule','chimwemwe@gmail.com','$2y$10$dDwjnZH5oTMQe9YBdk8DluJEn.60K8iLgQ0ImUbUwAndcEehVAUZK','2025-07-15 10:19:02'),(2,'Veronica Mungandi','veronica@gmail.com','$2y$10$iY/2qPGoq1XzWgqZUXcHL.yll4aqSbVevzsyXkRnAnML.LGAknCaS','2025-07-24 09:39:02');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `appointments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `service_type` varchar(100) NOT NULL,
  `appointment_date` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('pending','pending_payment','confirmed','completed','cancelled') DEFAULT 'pending_payment',
  `notes` text,
  `payment_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `transaction_id` varchar(191) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `staff_id` int NOT NULL,
  `slot` enum('morning','afternoon') NOT NULL,
  `payment_status` enum('unpaid','paid') DEFAULT 'unpaid',
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `staff_id` (`staff_id`),
  CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,1,'knotless braids k400','2025-08-22 00:00:00',NULL,'pending','ill come it 30 minutes earlier',0.00,NULL,'2025-08-20 00:29:51',4,'morning','unpaid',NULL),(2,1,'spanish curl 12 inch ','2025-08-22 00:00:00',NULL,'pending','i want then in colour 33',0.00,NULL,'2025-08-21 10:15:29',1,'morning','unpaid',NULL),(3,1,'nails','2025-08-22 00:00:00',NULL,'pending','polygel french tips',0.00,NULL,'2025-08-21 10:16:02',4,'morning','unpaid',NULL),(4,1,'nails ','2025-08-22 00:00:00',NULL,'pending','french tip nails ',0.00,NULL,'2025-08-21 14:55:09',4,'morning','unpaid',NULL),(5,1,'sggsgvs','2025-08-22 00:00:00',NULL,'pending','shgsbhmnbsns',0.00,NULL,'2025-08-21 14:55:28',1,'morning','unpaid',NULL),(6,1,'gfhgbvnbcbv','2025-08-22 00:00:00',NULL,'pending','cgnbbhgdxfbvn ',0.00,NULL,'2025-08-21 14:56:09',1,'morning','unpaid',NULL),(7,1,'ggfvsbvdgv','2025-08-22 00:00:00',NULL,'pending','dnbmsnd ms',0.00,NULL,'2025-08-21 14:57:56',1,'morning','unpaid',NULL),(8,1,'knotless braids medium ','2025-08-25 00:00:00',NULL,'pending','i want them in colour 33',0.00,NULL,'2025-08-25 10:35:24',1,'morning','unpaid',NULL),(9,1,'fulani braids','2025-08-25 00:00:00',NULL,'pending','ghsvsa',0.00,NULL,'2025-08-25 12:45:06',1,'morning','unpaid',NULL),(10,1,'wig installation','2025-08-25 00:00:00',NULL,'pending','i want barrow curls',0.00,NULL,'2025-08-26 00:18:03',6,'morning','unpaid',NULL),(11,1,'blow out','2025-08-25 00:00:00',NULL,'pending','wash and blow',0.00,NULL,'2025-08-26 00:35:26',1,'morning','unpaid',NULL),(12,1,'gel nails','2025-08-26 00:00:00',NULL,'pending','ill come with an inspo picture',0.00,NULL,'2025-08-26 01:05:30',2,'morning','unpaid',NULL),(13,1,'blow out','2025-08-25 00:00:00',NULL,'pending','wash and blow',0.00,NULL,'2025-08-26 01:06:02',1,'morning','unpaid',NULL),(14,3,'fulani braids ','2025-09-10 00:00:00',NULL,'pending','with culs medium sized',0.00,NULL,'2025-09-10 10:35:02',1,'morning','unpaid',NULL),(15,3,'hair','2025-09-10 00:00:00',NULL,'pending','hair',0.00,NULL,'2025-09-10 10:39:02',1,'afternoon','unpaid',NULL);
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `products` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int DEFAULT '0',
  `image_url` text,
  `image_file` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'hot comb','straightens your hair with a maximam heat of 220v',320.00,5,NULL,NULL,'2025-07-15 17:27:51'),(6,'Brazillian crush body mist','cheirosa',300.00,0,'6878c4bd8b6c7.jpg',NULL,'2025-07-17 09:39:09'),(7,'dyson hair dryer','professional high class hair dryer',1500.00,2,'6878c6b30b80c.webp',NULL,'2025-07-17 09:47:31'),(8,'comb','very strong',20.00,4,'688200ff30bdc.jpg',NULL,'2025-07-24 09:46:39');
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `receipts`
--

DROP TABLE IF EXISTS `receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `receipts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `appointment_id` int DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `appointment_id` (`appointment_id`),
  CONSTRAINT `receipts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `receipts_ibfk_2` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `receipts`
--

LOCK TABLES `receipts` WRITE;
/*!40000 ALTER TABLE `receipts` DISABLE KEYS */;
/*!40000 ALTER TABLE `receipts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `staff` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `role` varchar(100) NOT NULL,
  `available` tinyint(1) DEFAULT '1',
  `available_days` varchar(300) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Selita','Hair dresser',1,NULL,NULL,NULL),(2,'Faith','Nail technician',1,NULL,NULL,NULL),(3,'Nambule','lash technician',0,NULL,NULL,NULL),(4,'Nancy','Nail Tech',1,'Mon,Tue,Wed,Thu,Fri','08:00:00','17:00:00'),(5,'Mary','Hairdresser',1,'Mon,Wed,Fri,Sat','09:00:00','18:00:00'),(6,'Maureen','Hairstylist',1,'Tue,Thu,Sat','10:00:00','16:00:00');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(40) NOT NULL,
  `email` varchar(60) NOT NULL,
  `role` enum('client','admin') DEFAULT 'client',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'faith nambule','selitafn@gmail.com','client','2025-06-24 22:51:00','$2y$10$ra.GwMwPhyrQuPfm48cFGe067vTTdkbM/Xf0tm5mEOXNbM7QYTC0S'),(2,'selita nambule','fnambule06@gmail.com','client','2025-07-02 08:52:12','$2y$10$i.oD/ZyNhuWnzVhJ.mXTh.sbbD6RLH5w1lSL24aWv3a5bI5JZA9yK'),(3,'selita nambule','selitanambule@gmail.com','client','2025-07-08 16:12:47','$2y$10$g63a/48ongREDfui3RngHu2Mzl3tdg/1QlFXUc7hVwDcjkjyEbLaq'),(4,'faith nambule','faithnambule@gmail.com','client','2025-08-10 14:44:09','$2y$10$bBqWpi67KMAHzuEqhtKtf.pOqoOpBN0ogebufE9gIynYEV1GqhBUu');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-16 16:59:15
