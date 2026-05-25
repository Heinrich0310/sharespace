-- MySQL dump 10.13  Distrib 5.7.44, for osx11.0 (x86_64)
--
-- Host: 127.0.0.1    Database: sharespace_db
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `category_id` int NOT NULL AUTO_INCREMENT,
  `category_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Tools & Equipment','?'),(2,'Furniture & Chairs','?'),(3,'Electronics & Sound','?'),(4,'Gardening','?');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `listings`
--

DROP TABLE IF EXISTS `listings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `listings` (
  `listing_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `category_id` int NOT NULL,
  `title` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '',
  `price_per_day` decimal(10,2) NOT NULL,
  `availability_status` enum('available','unavailable') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  `delivery_option` enum('collection','delivery','both') COLLATE utf8mb4_unicode_ci DEFAULT 'collection',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`listing_id`),
  KEY `user_id` (`user_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `listings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `listings_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `listings`
--

LOCK TABLES `listings` WRITE;
/*!40000 ALTER TABLE `listings` DISABLE KEYS */;
INSERT INTO `listings` VALUES (1,2,1,'Heavy-duty Power Drill','Bosch 18V cordless drill with 2 batteries and charger included. Perfect for home renovations. Good condition, well maintained.','',80.00,'available','collection','2026-04-12 14:41:25'),(2,3,1,'Angle Grinder','Makita 125mm angle grinder. Includes 5 cutting discs. Great for metal cutting and grinding work.','',65.00,'available','collection','2026-04-12 14:41:25'),(3,4,1,'Cement Mixer (Mini)','Small electric cement mixer, 60L drum. Ideal for small building projects and repairs. Collect from Khayelitsha.','',200.00,'available','collection','2026-04-12 14:41:25'),(4,5,1,'Pressure Washer','Karcher K4 pressure washer. Great for cleaning driveways, cars, and walls. Includes 10m hose.','',150.00,'available','collection','2026-04-12 14:41:25'),(5,6,1,'Scaffolding Set','Aluminium scaffolding set, 3m height. Safe and sturdy. Perfect for painting or roof repairs.','',300.00,'available','collection','2026-04-12 14:41:25'),(6,2,2,'Plastic Chairs (30 pack)','30 white plastic chairs in excellent condition. Perfect for parties, funerals, or any community event. Delivery available.','',120.00,'available','collection','2026-04-12 14:41:25'),(7,3,2,'Party Tent (6x6m)','Large white party tent, 6x6 meters. Waterproof. Includes poles and pegs. Fits up to 40 guests comfortably.','',250.00,'available','collection','2026-04-12 14:41:25'),(8,4,2,'Folding Tables (10 pack)','10 strong folding tables, 1.8m long each. Great for events, catering, or market stalls.','',150.00,'available','collection','2026-04-12 14:41:25'),(9,5,2,'Chafing Dishes Set (8)','Set of 8 stainless steel chafing dishes with lids and stands. Includes fuel canisters. Perfect for catering.','',180.00,'available','collection','2026-04-12 14:41:25'),(10,6,2,'Inflatable Jumping Castle','Kids jumping castle with blower. Fits children up to 12 years. Great for birthday parties. Bright colours.','',350.00,'available','collection','2026-04-12 14:41:25'),(11,2,3,'Sound System + 2 Speakers','Professional 2000W sound system with 2 large speakers, subwoofer, and microphone. Perfect for parties and events.','',350.00,'available','collection','2026-04-12 14:41:25'),(12,3,3,'DJ Controller & Mixer','Pioneer DJ controller with mixer. Includes all cables. Bluetooth and USB compatible. Great for any event.','',280.00,'available','collection','2026-04-12 14:41:25'),(13,4,3,'Projector & Screen','Full HD projector with 2m pull-down screen. Perfect for outdoor movie nights, church, or business presentations.','',220.00,'available','collection','2026-04-12 14:41:25'),(14,5,3,'Generator (3.5kVA)','Petrol generator, 3.5kVA. Handles 4-6 appliances. Perfect for load shedding or outdoor events. Fuel not included.','',400.00,'available','collection','2026-04-12 14:41:25'),(15,6,3,'LED Party Lights Set','Set of 10 LED colour changing lights with remote. Includes strobe, disco ball, and UV light. Great atmosphere!','',120.00,'available','collection','2026-04-12 14:41:25'),(16,2,4,'Petrol Lawn Mower','Honda petrol lawn mower. Self-propelled. Cuts up to 1 acre on one tank. Sharp blade, well serviced.','',90.00,'available','collection','2026-04-12 14:41:25'),(17,3,4,'Electric Hedge Trimmer','Ryobi electric hedge trimmer, 550mm blade. Perfect for shaping hedges and shrubs. 10m cable included.','',55.00,'available','collection','2026-04-12 14:41:25'),(18,4,4,'Garden Chipper/Shredder','Electric garden shredder for branches up to 40mm. Great for clearing garden waste after pruning.','',130.00,'available','collection','2026-04-12 14:41:25'),(19,5,4,'Wheelbarrow + Garden Tools','Heavy duty wheelbarrow plus full set of garden tools: spade, fork, rake, hoe. Perfect for a big garden day.','',70.00,'available','collection','2026-04-12 14:41:25'),(20,6,4,'Water Pump (Submersible)','Submersible water pump for pools, flooded areas or irrigation. 1200W, 20m head. Includes 10m hose.','',110.00,'available','collection','2026-04-12 14:41:25'),(21,1,1,'laptop','laptop','uploads/listings/listing_69df8303341e0.jpg',200.00,'available','collection','2026-04-15 12:22:27'),(22,7,1,'Test Bicycle','Test listing created by automated test','',50.00,'available','collection','2026-05-19 18:02:56');
/*!40000 ALTER TABLE `listings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `listing_id` int DEFAULT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `messages`
--

LOCK TABLES `messages` WRITE;
/*!40000 ALTER TABLE `messages` DISABLE KEYS */;
INSERT INTO `messages` VALUES (1,1,3,2,'testing',0,'2026-04-15 10:44:07'),(2,7,2,1,'Hi, is the drill still available?',0,'2026-05-19 18:04:51');
/*!40000 ALTER TABLE `messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `payment_id` int NOT NULL AUTO_INCREMENT,
  `rental_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `method` enum('EFT','cash','card') DEFAULT 'cash',
  `status` enum('pending','paid','failed') DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `rental_id` (`rental_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rentals`
--

DROP TABLE IF EXISTS `rentals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rentals` (
  `rental_id` int NOT NULL AUTO_INCREMENT,
  `listing_id` int NOT NULL,
  `renter_id` int NOT NULL,
  `start_date` date NOT NULL,
  `num_days` int NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','active','completed','cancelled') DEFAULT 'pending',
  `booked_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rental_id`),
  KEY `listing_id` (`listing_id`),
  KEY `renter_id` (`renter_id`),
  CONSTRAINT `rentals_ibfk_1` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`),
  CONSTRAINT `rentals_ibfk_2` FOREIGN KEY (`renter_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rentals`
--

LOCK TABLES `rentals` WRITE;
/*!40000 ALTER TABLE `rentals` DISABLE KEYS */;
INSERT INTO `rentals` VALUES (1,1,3,'2026-04-10',3,240.00,'completed','2026-04-12 14:41:25'),(2,7,4,'2026-04-12',2,500.00,'active','2026-04-12 14:41:25'),(3,11,5,'2026-04-13',1,350.00,'pending','2026-04-12 14:41:25'),(4,4,6,'2026-04-14',2,300.00,'pending','2026-04-12 14:41:25'),(5,3,1,'2026-04-12',2,400.00,'pending','2026-04-12 17:26:00'),(6,1,7,'2026-05-25',3,240.00,'pending','2026-05-19 17:51:56');
/*!40000 ALTER TABLE `rentals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `rental_id` int NOT NULL,
  `reviewer_id` int NOT NULL,
  `rating` int DEFAULT NULL,
  `comment` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `rental_id` (`rental_id`),
  KEY `reviewer_id` (`reviewer_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `rentals` (`rental_id`),
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reviews`
--

LOCK TABLES `reviews` WRITE;
/*!40000 ALTER TABLE `reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role` enum('user','moderator','admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `is_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'heinrich','hpotgieter0310@gmail.com','0829339132','$2y$10$h3.j0DUkJGwqiyovC0aG9.k5PJSqiu3.JKOcmq1mfrriFknRseWXS','Other','admin',0,'2026-04-12 14:34:45'),(2,'Sipho Mokoena','sipho@demo.com','0821234567','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Soweto','user',0,'2026-04-12 14:41:25'),(3,'Nomsa Khumalo','nomsa@demo.com','0837654321','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Alexandra','user',0,'2026-04-12 14:41:25'),(4,'Thabo Dlamini','thabo@demo.com','0761112233','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Khayelitsha','user',0,'2026-04-12 14:41:25'),(5,'Zanele Petersen','zanele@demo.com','0849998877','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Diepsloot','user',0,'2026-04-12 14:41:25'),(6,'Bongani Nkosi','bongani@demo.com','0715556644','$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','Mitchells Plain','user',0,'2026-04-12 14:41:25'),(7,'Test User','testuser@sharespace.co.za','0821234567','$2y$10$ZEmJUv9fLiLMP.bqJ8OLXOi3pYw3m.SdCQnfOuPZCBWHm8CcF.UNe','Soweto','user',0,'2026-05-19 17:49:19'),(8,'Heinrich Potgieter','hpotgieter000@gmail.com','0829339132','$2y$10$hhwvTAT4rv5z4iQrayljteVMRYnxQTckxr0qe7pRcAoAMtLiXD6pm','Soweto','user',0,'2026-05-19 18:25:11'),(9,'Thandi Nkosi','thandi.test2026@gmail.com','0731234567','$2y$10$iHJMQ2WFAEU49SKMiFaj/eKd12hqRmHjztlpWLtd9x86gHJ.RsZFu','Khayelitsha','user',0,'2026-05-20 16:40:59'),(10,'Sipho Test','sipho.newtest@mail.com','0821112222','$2y$10$qxW/u6Acn4KLYdvp2O.0CeelegrsRUXSlou8hb5Zu..w9HqlNzR5m','Soweto','user',0,'2026-05-20 16:41:42'),(11,'Bongani Flow','bongani.flowtest@mail.com','0741234567','$2y$10$TCARBiQImrOXVTI9olUd6u4Sbq4DyYP0iEpqb2SZWti98h8pqdAku','Alexandra','user',0,'2026-05-20 16:44:00');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wishlists` (
  `wishlist_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `listing_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`wishlist_id`),
  UNIQUE KEY `unique_wishlist` (`user_id`,`listing_id`),
  KEY `listing_id` (`listing_id`),
  CONSTRAINT `wishlists_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `wishlists_ibfk_2` FOREIGN KEY (`listing_id`) REFERENCES `listings` (`listing_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wishlists`
--

LOCK TABLES `wishlists` WRITE;
/*!40000 ALTER TABLE `wishlists` DISABLE KEYS */;
INSERT INTO `wishlists` VALUES (1,1,3,'2026-04-15 10:31:34');
/*!40000 ALTER TABLE `wishlists` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-20 18:53:26
