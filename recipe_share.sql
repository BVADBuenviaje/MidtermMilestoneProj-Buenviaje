-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: recipe_share
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Ulam (Main Dishes)','Classic hearty meat and seafood dishes'),(2,'Sabaw (Soups & Stews)','Comforting broth-based meals'),(3,'Gulay (Vegetable Dishes)','Wholesome local greens and vegetable sautés'),(4,'Merienda & Kakanin','Traditional snacks and rice cakes'),(5,'Panghimagas (Desserts)','Sweet delicacies and cold treats');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `is_edited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_comments_recipe` (`recipe_id`),
  KEY `fk_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,1,2,'Tried this with a splash of toasted garlic oil at the end—absolute perfection, Maria!',0,'2026-10-09 07:02:00','2026-10-09 07:02:00'),(2,2,1,'Adding the gabi early to let it soften and thicken the broth is the real secret. Reminds me of my lola\'s cooking!',1,'2026-10-09 07:02:00','2026-10-09 07:02:00'),(3,4,1,'The extra leche flan on top made this a crowd favorite at our family salu-salo!',0,'2026-10-09 07:02:00','2026-10-09 07:02:00');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `favorites`
--

DROP TABLE IF EXISTS `favorites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_recipe_fav` (`user_id`,`recipe_id`),
  KEY `fk_favs_recipe` (`recipe_id`),
  CONSTRAINT `fk_favs_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_favs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `favorites`
--

LOCK TABLES `favorites` WRITE;
/*!40000 ALTER TABLE `favorites` DISABLE KEYS */;
INSERT INTO `favorites` VALUES (1,1,2,'2026-10-09 07:02:00'),(2,2,1,'2026-10-09 07:02:00');
/*!40000 ALTER TABLE `favorites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ingredients`
--

DROP TABLE IF EXISTS `ingredients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_id` int(11) NOT NULL,
  `item` varchar(150) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ingredients_recipe` (`recipe_id`),
  CONSTRAINT `fk_ingredients_recipe` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ingredients`
--

LOCK TABLES `ingredients` WRITE;
/*!40000 ALTER TABLE `ingredients` DISABLE KEYS */;
INSERT INTO `ingredients` VALUES (39,1,'1 kg chicken cut into serving pieces'),(40,1,'1/2 cup soy sauce'),(41,1,'1/2 cup cane vinegar'),(42,1,'1 head garlic, minced'),(43,1,'1 tsp whole black peppercorns'),(44,1,'3 dried bay leaves'),(45,1,'2 tbsp cooking oil'),(46,2,'1 kg pork ribs cut into chunks'),(47,2,'1 packet tamarind soup base mix'),(48,2,'1 bunch kangkong (water spinach)'),(49,2,'2 medium gabi (taro), quartered'),(50,2,'1 medium white radish (labanos), sliced'),(51,2,'2 medium tomatoes, quartered'),(52,2,'1 medium red onion, wedged'),(53,2,'2 pieces siling haba (finger chili)'),(54,2,'2 tbsp fish sauce (patis)'),(55,3,'400g squash (kalabasa), cut into cubes'),(56,3,'1 bunch yardlong string beans (sitaw)'),(57,3,'250g fresh shrimp, cleaned'),(58,3,'2 cups pure coconut milk'),(59,3,'1 medium onion, sliced'),(60,3,'3 cloves garlic, minced'),(61,3,'1 thumb ginger, sliced'),(62,3,'1 tbsp shrimp paste (bagoong)'),(63,4,'2 cups finely shaved ice'),(64,4,'1/2 cup evaporated milk'),(65,4,'2 slices creamy leche flan'),(66,4,'2 tbsp ube halaya'),(67,4,'2 scoops ube ice cream'),(68,4,'2 tbsp sweetened red beans'),(69,4,'2 tbsp nata de coco'),(70,4,'2 tbsp kaong'),(71,4,'2 tbsp sweetened saba banana'),(72,5,'2 cups glutinous rice (malagkit)'),(73,5,'3 cups rich coconut milk'),(74,5,'1 1/2 cups dark brown sugar'),(75,5,'Wilted banana leaves for lining'),(76,5,'1/4 tsp salt');
/*!40000 ALTER TABLE `ingredients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `recipes`
--

DROP TABLE IF EXISTS `recipes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `description` varchar(300) NOT NULL,
  `instructions` text NOT NULL,
  `cooking_time_mins` int(11) DEFAULT 30,
  `servings` int(11) DEFAULT 4,
  `is_edited` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_recipes_user` (`user_id`),
  KEY `fk_recipes_category` (`category_id`),
  CONSTRAINT `fk_recipes_category` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_recipes_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `recipes`
--

LOCK TABLES `recipes` WRITE;
/*!40000 ALTER TABLE `recipes` DISABLE KEYS */;
INSERT INTO `recipes` VALUES (1,1,1,'Classic Chicken Adobo','Tender chicken cuts braised in soy sauce, vinegar, crushed garlic, whole peppercorns, and bay leaves.','1. Combine chicken, soy sauce, and garlic in a bowl. Marinate for 30 minutes.\n2. Heat oil in a pan over medium heat. Brown chicken cuts on all sides.\n3. Pour in marinade, cane vinegar, whole peppercorns, and bay leaves. Bring to a boil without stirring.\n4. Lower heat, cover, and simmer for 25 minutes until chicken is cooked through.\n5. Uncover and simmer for another 5 minutes to reduce sauce to a savory glaze. Serve with hot steamed rice.',45,4,0,'2026-10-09 07:02:00','2026-10-09 07:02:00'),(2,2,2,'Sinigang na Baboy','Comforting tamarind-based sour soup made with tender pork ribs, fresh kangkong leaves, gabi, and tomatoes.','1. Place pork ribs in a large pot with 6 cups of water. Bring to a boil and skim off scum.\n2. Add quartered onions, tomatoes, and sliced gabi. Simmer on low heat for 45 minutes until pork is tender.\n3. Add tamarind soup mix, radish, and finger chili. Simmer for 5 minutes.\n4. Stir in kangkong leaves and season with fish sauce. Turn off heat and cover for 2 minutes before serving.',60,6,1,'2026-10-09 07:02:00','2026-10-09 07:02:00'),(3,1,3,'Ginataang Kalabasa at Sitaw','Wholesome squash and yardlong string beans stewed gently in rich coconut milk with shrimp.','1. Sauté garlic, onion, and fresh ginger in cooking oil until aromatic.\n2. Add fresh shrimp and cook until slightly pink, then set shrimp aside.\n3. Pour in pure coconut cream and bring to a gentle simmer.\n4. Add cubed squash and cook for 10 minutes until tender.\n5. Add string beans and green chili. Return shrimp to the pot and season with shrimp paste.\n6. Simmer for 3 more minutes until coconut sauce thickens.',35,4,0,'2026-10-09 07:02:00','2026-10-09 07:02:00'),(4,2,5,'Special Halo-Halo','The quintessential Filipino shaved ice dessert loaded with sweetened beans, jellies, leche flan, and ube.','1. In tall serving glasses, layer sweetened red beans, white beans, saba banana, nata de coco, and kaong.\n2. Fill glass with finely crushed shaved ice to the rim.\n3. Drizzle generously with evaporated milk.\n4. Top each glass with a slice of rich leche flan, a spoonful of ube halaya, and a scoop of ube ice cream.\n5. Sprinkle with toasted pinipig and serve immediately.',15,2,0,'2026-10-09 07:02:00','2026-10-09 07:02:00'),(5,1,4,'Bibingka Malagkit','Sticky sweet coconut rice cake with a luscious golden brown caramelized coconut glaze.','1. Wash glutinous rice and cook with 2 cups coconut milk and water until liquid is absorbed.\n2. In a saucepan, combine dark brown sugar and 1 cup coconut milk to make sweet syrup.\n3. Mix 3/4 of syrup into cooked sticky rice.\n4. Transfer rice into a greased baking pan lined with banana leaves.\n5. Pour remaining caramel topping evenly over the surface.\n6. Bake at 180°C for 25-30 minutes until topping is bubbling and golden brown.',50,8,0,'2026-10-09 07:02:00','2026-10-09 07:02:00');
/*!40000 ALTER TABLE `recipes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'maria_cooks','maria@example.com','$2y$10$PC6ZVFA.JtCgXJg5I21L4.5BWPnEuu0rPlZkEUaEXt1xbfpGJMJO6','2026-10-09 07:02:00'),(2,'juan_kusinero','juan@example.com','$2y$10$PC6ZVFA.JtCgXJg5I21L4.5BWPnEuu0rPlZkEUaEXt1xbfpGJMJO6','2026-10-09 07:02:00');
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

-- Dump completed on 2026-10-09 15:03:16
