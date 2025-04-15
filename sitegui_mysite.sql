-- MariaDB dump 10.19  Distrib 10.11.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: sitegui_mysite
-- ------------------------------------------------------
-- Server version	10.11.6-MariaDB-0+deb12u1-log

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
-- Table structure for table `mysite16_activity`
--

DROP TABLE IF EXISTS `mysite16_activity`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_activity` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `app_type` varchar(50) DEFAULT NULL,
  `app_id` int(1) unsigned DEFAULT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'Info',
  `message` text DEFAULT NULL,
  `creator` int(1) NOT NULL,
  `created` int(1) NOT NULL,
  `processed` int(1) DEFAULT NULL,
  `retry` int(1) DEFAULT NULL,
  `meta` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_app_type` (`app_type`),
  KEY `idx_app_id` (`app_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_activity`
--

LOCK TABLES `mysite16_activity` WRITE;
/*!40000 ALTER TABLE `mysite16_activity` DISABLE KEYS */;
/*!40000 ALTER TABLE `mysite16_activity` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_config`
--

DROP TABLE IF EXISTS `mysite16_config`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_config` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(50) DEFAULT NULL,
  `object` varchar(70) DEFAULT NULL,
  `property` varchar(70) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `order` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `object_type_property` (`type`(25),`object`(50),`property`(25))
) ENGINE=InnoDB AUTO_INCREMENT=1799 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_config`
--

LOCK TABLES `mysite16_config` WRITE;
/*!40000 ALTER TABLE `mysite16_config` DISABLE KEYS */;
INSERT INTO `mysite16_config` VALUES
(311,'db','Core\\Tax','NY Sales Tax','{\"rate\":\"8.875\",\"level\":\"1\",\"compound\":\"0\",\"shipping\":\"1\",\"active\":\"0\"}','US','NY',NULL),
(1580,'db','Group','Reseller','Reseller',NULL,'This is reseller group',NULL),
(1581,'db','Group','Vendor','Vendor',NULL,'This is vendor group',NULL),
(1783,'config','Core\\Notification','channels','{\"0\":{\"channel\":\"0\"}}',NULL,NULL,NULL),
(1784,'config','Core\\Notification','selection','one',NULL,NULL,NULL),
(1785,'config','Core\\Notification','from_name','',NULL,NULL,NULL),
(1786,'config','Core\\Notification','from_mail','',NULL,NULL,NULL),
(1787,'config','Core\\Notification','signature','With 💝 From All Of Us',NULL,NULL,NULL),
(1793,'config','Notification\\Phpmail','quota','300',NULL,NULL,NULL);
/*!40000 ALTER TABLE `mysite16_config` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_location`
--

DROP TABLE IF EXISTS `mysite16_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_location` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `app_type` char(30) NOT NULL,
  `app_id` int(1) NOT NULL,
  `location` char(30) NOT NULL,
  `section` char(30) NOT NULL,
  `page_id` int(1) DEFAULT NULL,
  `sort` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `relation` (`app_type`,`app_id`,`location`,`section`,`page_id`)
) ENGINE=InnoDB AUTO_INCREMENT=685 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_location`
--

LOCK TABLES `mysite16_location` WRITE;
/*!40000 ALTER TABLE `mysite16_location` DISABLE KEYS */;
INSERT INTO `mysite16_location` VALUES
(1,'menu',1,'Site','top',NULL,NULL),
(2,'menu',2,'Site','footer',NULL,NULL),
(12,'widget',4,'Site','spotlight',NULL,NULL),
(19,'widget',7,'Site','footnote',NULL,NULL),
(683,'widget',54,'Site','spotlight',NULL,NULL),
(684,'widget',63,'Site','footnote',NULL,NULL);
/*!40000 ALTER TABLE `mysite16_location` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_menu`
--

DROP TABLE IF EXISTS `mysite16_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_menu` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  `data` text DEFAULT NULL,
  `cache` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_menu`
--

LOCK TABLES `mysite16_menu` WRITE;
/*!40000 ALTER TABLE `mysite16_menu` DISABLE KEYS */;
INSERT INTO `mysite16_menu` VALUES
(1,'Main','[{\"id\":1},{\"id\":4},{\"id\":2},{\"id\":5}]',NULL),
(2,'Footer','[{\"id\":1,\"children\":[{\"id\":6},{\"id\":7}]},{\"id\":4,\"children\":[{\"id\":5},{\"id\":2}]}]',NULL);
/*!40000 ALTER TABLE `mysite16_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_page`
--

DROP TABLE IF EXISTS `mysite16_page`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_page` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) NOT NULL,
  `subtype` varchar(50) DEFAULT NULL,
  `name` text NOT NULL,
  `slug` varchar(191) NOT NULL,
  `title` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `public` text DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `image` text DEFAULT NULL,
  `creator` int(1) NOT NULL,
  `created` int(1) NOT NULL,
  `updated` int(1) NOT NULL,
  `published` int(1) DEFAULT NULL,
  `expire` int(1) DEFAULT NULL,
  `private` varchar(200) DEFAULT NULL,
  `status` varchar(191) DEFAULT NULL,
  `menu_id` int(1) DEFAULT NULL,
  `breadcrumb` int(1) DEFAULT NULL,
  `layout` text DEFAULT NULL,
  `views` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=349 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_page`
--

LOCK TABLES `mysite16_page` WRITE;
/*!40000 ALTER TABLE `mysite16_page` DISABLE KEYS */;
INSERT INTO `mysite16_page` VALUES
(1,'Page',NULL,'{\"en\":\"Home\"}','index.html','{\"en\":\"Home\"}','{\"en\":\"\"}',NULL,'{\"wysiwyg\":\"1\",\"en\":\" \\r\\n           \\r\\n           \\r\\n          \\r\\n         \\r\\n  <div class=\\\"row\\\"><div class=\\\"sg-block-wrapper col-12\\\">          \\r\\n              \\r\\n            <div class=\\\"px-4 py-5 my-5 text-center sg-block-content\\\">\\r\\n  <img class=\\\"d-block mx-auto mb-4\\\" src=\\\"https:\\/\\/getbootstrap.com\\/docs\\/5.3\\/assets\\/brand\\/bootstrap-logo.svg\\\" alt=\\\"\\\" width=\\\"72\\\" height=\\\"57\\\">\\r\\n  <h1 class=\\\"display-5 fw-bold\\\">Build Beautiful Websites Without Code<\\/h1>\\r\\n  <div class=\\\"col-lg-6 mx-auto\\\">\\r\\n    <p class=\\\"lead mb-4\\\">Create stunning, responsive websites with our intuitive drag-and-drop builder. No coding required.<\\/p>\\r\\n    <div class=\\\"d-grid gap-2 d-sm-flex justify-content-sm-center\\\">\\r\\n      <a href=\\\"https:\\/\\/sitegui.com\\\" class=\\\"btn btn-warning btn-lg px-4 gap-3\\\">Start Building<\\/a>\\r\\n      \\r\\n    <\\/div>\\r\\n  <\\/div>\\r\\n<\\/div><\\/div><\\/div>\\r\\n         \\r\\n  \\r\\n         \\r\\n  \"}',NULL,1023,1724649766,1735200506,1,0,'0',NULL,1,0,'',414),
(2,'Product','Shipping','{\"en\":\"iPhone 15\"}','iphone-15','{\"en\":\"iPhone 15, iPhone 15 Pro 128GB, 256GB, 512GB Black, Blue, Green, Yellow, Pink\"}','{\"en\":\"iPhone 15, iPhone 15 Pro 128GB, 256GB, 512GB Black, Blue, Green, Yellow, Pink\"}',NULL,'{\"en\":\"<p>The iPhone 15 offers cutting-edge technology with the powerful A16 Bionic chip, delivering faster performance and efficiency. Its stunning Super Retina XDR display provides vibrant visuals, while the upgraded 48MP camera system captures incredible detail and clarity, even in low light. The iPhone 15 introduces USB-C charging for faster and more versatile connectivity, along with enhanced battery life to keep you going all day. Available in a range of stylish colors, the iPhone 15 also features Dynamic Island and satellite emergency SOS, making it the perfect blend of innovation and practicality.<\\/p><h5 style=\\\"text-align:center;\\\"><b>This is a sample product. Unpublish or Delete this product as you like<\\/b><\\/h5>\"}','https://cdn.pageee.com/public/uploads/site/5281/2024Q3/iphone-15.webp',1023,1724563192,1724563192,1,0,'0',NULL,1,1,'',25),
(3,'',NULL,'','',NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),
(4,'Link',NULL,'{\"en\":\"Store\"}','/store',NULL,NULL,NULL,NULL,NULL,1023,1724649861,1724649861,1,NULL,NULL,NULL,1,NULL,NULL,32),
(5,'Page',NULL,'{\"en\":\"Pricing\"}','pricing','{\"en\":\"Pricing Landing Page\"}','{\"en\":\"This landing page contains all about our pricing.\"}',NULL,'{\"wysiwyg\":\"1\",\"en\":\" <div class=\\\"row\\\"><div class=\\\"sg-block-wrapper col-12 position-absolute top-0 start-0 z-1 pt-2\\\" data-sg-func=\\\"system__.bootstrap5.button\\\">              <div class=\\\"sg-block-content text-end\\\" style=\\\"\\\"><a class=\\\"m-1 sg-editor-template sg-editor-removable text-secondary text-decoration-none\\\" href=\\\"\\/\\\" role=\\\"button\\\">Home<\\/a><\\/div><\\/div><div class=\\\"sg-block-wrapper col-12\\\" data-sg-func=\\\"system__.bootstrap5.blob\\\">              <div class=\\\"sg-block-content sg-blob-wrapper\\\" style=\\\"--bg-blob: #ffffff;\\\"><div class=\\\"sg-blob\\\"><!-- This SVG is from https:\\/\\/codepen.io\\/Ali_Farooq_\\/pen\\/gKOJqx --><svg xmlns:xlink=\\\"http:\\/\\/www.w3.org\\/1999\\/xlink\\\" version=\\\"1.1\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\" viewBox=\\\"0 0 310 350\\\"><path d=\\\"M156.4,339.5c31.8-2.5,59.4-26.8,80.2-48.5c28.3-29.5,40.5-47,56.1-85.1c14-34.3,20.7-75.6,2.3-111  c-18.1-34.8-55.7-58-90.4-72.3c-11.7-4.8-24.1-8.8-36.8-11.5l-0.9-0.9l-0.6,0.6c-27.7-5.8-56.6-6-82.4,3c-38.8,13.6-64,48.8-66.8,90.3c-3,43.9,17.8,88.3,33.7,128.8c5.3,13.5,10.4,27.1,14.9,40.9C77.5,309.9,111,343,156.4,339.5z\\\" fill=\\\"#23e18f\\\" fill-opacity=\\\"1\\\"><\\/path><\\/svg><\\/div><h1 class=\\\"sg-blob-text\\\" style=\\\"--text-blob: #7b53e9;\\\">Pricing<br>Landing Page<\\/h1><style type=\\\"text\\/css\\\">@import url(\'https:\\/\\/fonts.googleapis.com\\/css?family=Poppins:700\');.sg-blob-wrapper {overflow: hidden;position: relative;height:50vh;min-height: 300px;display: flex;align-items: center;background-color: var(--bg-blob);}.sg-blob-text {color: var(--text-blob);font-size: 10vmin;line-height: 1.2;font-weight: bold;letter-spacing: 2px;font-family: \'Poppins\', sans-serif;text-transform: uppercase;padding-left: 80px;z-index: 3;}.sg-blob {position: absolute;top: 0;left: 0;width: 35vmax;animation: blobmove 10s ease-in-out infinite;transform-origin: 50% 50%;}@keyframes blobmove {0%   { transform: scale(1)   translate(10px, -30px); }38%  { transform: scale(0.8, 1) translate(80vw, 30vh) rotate(160deg); }40%  { transform: scale(0.8, 1) translate(80vw, 30vh) rotate(160deg); }78%  { transform: scale(1.3) translate(0vw, 50vh) rotate(-20deg); }80%  { transform: scale(1.3) translate(0vw, 50vh) rotate(-20deg); }100% { transform: scale(1)   translate(10px, -30px); }}<\\/style><\\/div><\\/div><div class=\\\"sg-block-wrapper col-12\\\" data-sg-func=\\\"system__.bootstrap5.wave\\\">              <div class=\\\"sg-block-content position-relative\\\"><svg class=\\\"sg-waves\\\" xmlns=\\\"http:\\/\\/www.w3.org\\/2000\\/svg\\\" xmlns:xlink=\\\"http:\\/\\/www.w3.org\\/1999\\/xlink\\\" viewBox=\\\"0 24 150 28\\\" preserveAspectRatio=\\\"none\\\" shape-rendering=\\\"auto\\\"><defs><path id=\\\"sg-gentle-wave\\\" d=\\\"M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z\\\"><\\/path><linearGradient id=\\\"sg-wave-gradient\\\" x1=\\\"0%\\\" y1=\\\"0%\\\" x2=\\\"100%\\\" y2=\\\"100%\\\"><stop offset=\\\"0%\\\" style=\\\"stop-color:#ffffff;stop-opacity:1\\\" data-color=\\\"#ffffff\\\"><\\/stop><stop offset=\\\"100%\\\" style=\\\"stop-color:#ffffff;stop-opacity:1\\\"><\\/stop><\\/linearGradient><\\/defs><g class=\\\"sg-waves-parallax\\\"><use xlink:href=\\\"#sg-gentle-wave\\\" x=\\\"48\\\" y=\\\"0\\\" fill=\\\"url(#sg-wave-gradient)\\\" fill-opacity=\\\".7\\\"><\\/use><use xlink:href=\\\"#sg-gentle-wave\\\" x=\\\"48\\\" y=\\\"3\\\" fill=\\\"url(#sg-wave-gradient)\\\" fill-opacity=\\\".5\\\"><\\/use><use xlink:href=\\\"#sg-gentle-wave\\\" x=\\\"48\\\" y=\\\"5\\\" fill=\\\"url(#sg-wave-gradient)\\\" fill-opacity=\\\".3\\\"><\\/use><use xlink:href=\\\"#sg-gentle-wave\\\" x=\\\"48\\\" y=\\\"7\\\" fill=\\\"#ffffff\\\"><\\/use><\\/g><\\/svg><style type=\\\"text\\/css\\\">\\/* By SayanBarcha https:\\/\\/github.com\\/SayanBarcha\\/Simple-Waves *\\/  .sg-waves {position: absolute;top: -120px;height:15vh;width: 100%;margin-bottom:-7px; \\/*Fix for safari gap*\\/min-height:120px;max-height:150px;z-index: 3;}.sg-waves-parallax > use {animation: move-forever 25s cubic-bezier(.55,.5,.45,.5) infinite;}.sg-waves-parallax > use:nth-child(1) {animation-delay: -2s;animation-duration: 7s;}.sg-waves-parallax > use:nth-child(2) {animation-delay: -3s;animation-duration: 10s;}.sg-waves-parallax > use:nth-child(3) {animation-delay: -4s;animation-duration: 13s;}.sg-waves-parallax > use:nth-child(4) {animation-delay: -5s;animation-duration: 20s;}@keyframes move-forever {0% {transform: translate3d(-90px,0,0);}100% { transform: translate3d(85px,0,0);}}\\/*Shrinking for mobile*\\/@media (max-width: 768px) {.sg-waves {top: -40px;height:40px;min-height:40px;}}<\\/style><\\/div><\\/div><div class=\\\"col-12 mb-3 sg-block-wrapper\\\">              <div class=\\\"container sg-block-content\\\"><style type=\\\"text\\/css\\\">.bi-x-lg + span {color: var(--bs-secondary-color) !important;}<\\/style><div class=\\\"row row-cols-1 row-cols-md-3 g-4 py-5\\\"><div class=\\\"col mt-5 sg-block-wrapper\\\"><div class=\\\"card h-100 sg-block-content\\\"><div class=\\\"card-header bg-transparent border-bottom-0 py-4\\\"><h5 class=\\\"card-title text-muted text-uppercase text-center\\\">Free<\\/h5><h6 class=\\\"card-price text-center\\\"><span class=\\\"fs-1\\\">$0<\\/span>\\/month<\\/h6><div class=\\\"py-3 small text-center\\\">Billed yearly<\\/div><hr><\\/div><div class=\\\"card-body\\\"><ul class=\\\"ps-3\\\"> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Single User<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">5GB Storage<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Unlimited Public Projects<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Community Access<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-x-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Unlimited   Private Projects<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-x-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Dedicated   Phone Support<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-x-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Free Subdomain <\\/span><\\/li> <li class=\\\"list-group-item mb-3 sg-editor-template\\\"><i class=\\\"bi bi-x-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Monthly Status   Reports<\\/span><\\/li><\\/ul><\\/div><div class=\\\"card-footer bg-transparent border-top-0 mb-3\\\"><div class=\\\"d-grid\\\"> <a href=\\\"#signup1\\\" class=\\\"btn btn-dark text-uppercase\\\">Sign Up<\\/a><\\/div><\\/div><\\/div><\\/div><div class=\\\"col sg-block-wrapper\\\"><div class=\\\"card h-100 sg-block-content\\\"><div class=\\\"card-header bg-info-subtle border-bottom-0 py-5\\\"><h5 class=\\\"card-title text-muted text-uppercase text-center\\\">Pro<\\/h5><h6 class=\\\"card-price text-center\\\"><span class=\\\"fs-1\\\">$30<\\/span>\\/month<\\/h6><div class=\\\"py-3 small text-center\\\">Billed yearly<\\/div><\\/div><div class=\\\"card-body\\\"><ul class=\\\"ps-3\\\"> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <strong class=\\\"sg-editor-removable\\\">5 Users<\\/strong><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">50GB Storage<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Unlimited Public Projects<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Community Access<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Unlimited Private Projects<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Dedicated Phone Support<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Free Subdomain<\\/span><\\/li> <li class=\\\"list-group-item mb-3 sg-editor-template\\\"><i class=\\\"bi bi-x-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Monthly Status   Reports<\\/span><\\/li><\\/ul><\\/div><div class=\\\"card-footer bg-transparent border-top-0 mb-3\\\"><div class=\\\"d-grid\\\"> <a href=\\\"#signup2\\\" class=\\\"btn btn-warning text-uppercase\\\">Sign Up<\\/a><\\/div><\\/div><\\/div><\\/div><div class=\\\"col mt-5 sg-block-wrapper\\\"><div class=\\\"card h-100 sg-block-content\\\"><div class=\\\"card-header bg-transparent border-bottom-0 py-4\\\"><h5 class=\\\"card-title text-muted text-uppercase text-center\\\">Enterprise<\\/h5><h6 class=\\\"card-price text-center\\\"><span class=\\\"fs-1\\\">$100<\\/span>\\/month<\\/h6><div class=\\\"py-3 small text-center\\\">Billed yearly<\\/div><hr><\\/div><div class=\\\"card-body\\\"><ul class=\\\"ps-3\\\"> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <strong class=\\\"sg-editor-removable\\\">Unlimited Users<\\/strong><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">150GB Storage<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Unlimited Public Projects<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Community Access<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Unlimited Private Projects<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Dedicated Phone Support<\\/span><\\/li> <li class=\\\"list-group-item mb-3\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <strong class=\\\"sg-editor-removable\\\">Unlimited Free   Subdomains<\\/strong><\\/li> <li class=\\\"list-group-item mb-3 sg-editor-template\\\"><i class=\\\"bi bi-check-lg\\\"><\\/i> <span class=\\\"sg-editor-removable\\\">Monthly Status Reports<\\/span><\\/li><\\/ul><\\/div><div class=\\\"card-footer bg-transparent border-top-0 mb-3\\\"><div class=\\\"d-grid\\\"> <a href=\\\"#signup3\\\" class=\\\"btn btn-dark text-uppercase\\\">Sign Up<\\/a><\\/div><\\/div><\\/div><\\/div><\\/div><\\/div><\\/div><div class=\\\"sg-block-wrapper col-12\\\">              <div class=\\\"container sg-block-content\\\"><h2 class=\\\"display-6 text-center mb-4\\\">Compare plans<\\/h2><div class=\\\"table-responsive\\\"><table class=\\\"table text-center\\\"><thead><tr><th style=\\\"width: 34%;\\\"><\\/th><th style=\\\"width: 22%;\\\">Free<\\/th><th style=\\\"width: 22%;\\\">Pro<\\/th><th style=\\\"width: 22%;\\\">Enterprise<\\/th><\\/tr><\\/thead><tbody><tr><th scope=\\\"row\\\" class=\\\"text-start sg-editor-removable\\\">Public<\\/th><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><\\/tr><tr><th scope=\\\"row\\\" class=\\\"text-start sg-editor-removable\\\">Private<\\/th><td><i class=\\\"bi bi-check-0\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><\\/tr><tr><th scope=\\\"row\\\" class=\\\"text-start sg-editor-removable\\\">Permissions<\\/th><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><\\/tr><tr><th scope=\\\"row\\\" class=\\\"text-start sg-editor-removable\\\">Sharing<\\/th><td><i class=\\\"bi bi-check-0\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><\\/tr><tr><th scope=\\\"row\\\" class=\\\"text-start sg-editor-removable\\\">Unlimited members<\\/th><td><i class=\\\"bi bi-check-0\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><\\/tr><tr class=\\\"sg-editor-template\\\"><th scope=\\\"row\\\" class=\\\"text-start sg-editor-removable\\\">Extra security<\\/th><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><td><i class=\\\"bi bi-check-lg\\\"><\\/i><\\/td><\\/tr><\\/tbody><\\/table><\\/div><\\/div><\\/div><\\/div>\"}',NULL,1023,1724563192,1724563192,1,0,'0',NULL,2,0,'blank',10),
(6,'Link',NULL,'{\"en\":\"Login\"}','https://my.litegui.com/account',NULL,NULL,NULL,NULL,NULL,1023,1724563192,1735140647,1,0,'0',NULL,2,NULL,NULL,0),
(7,'Link',NULL,'{\"en\":\"Shopping Cart\"}','https://my.litegui.com/account/cart',NULL,NULL,NULL,NULL,NULL,1023,1724563192,1735140632,1,0,'0',NULL,2,NULL,NULL,0),
(8,'App','Task','{\"en\":\"Setup your Site\"}','setup-your-site-781724563192',NULL,NULL,NULL,'{\"en\":\"<p>Let\'s get started by configuring your site. The sub-task tab should have a few tasks to be completed.<\\/p>\"}',NULL,1023,1724563192,1724563192,NULL,NULL,NULL,'To Do',NULL,NULL,NULL,0),
(9,'App','Task','{\"en\":\"Upload your Logo\"}','upload-your-logo-801724563192',NULL,NULL,NULL,'{\"en\":\"<p>You should use the File Manager to upload your logo file to a public folder. Then click on App Listing menu and choose Site to view\\/change your Site settings. You should be able to select the newly uploaded file as your site\'s logo.\\u00a0<\\/p>\"}',NULL,1023,1724563192,1724563192,NULL,NULL,NULL,'To Do',NULL,NULL,NULL,0),
(10,'App','Task','{\"en\":\"Set the default language, timezone and currency\"}','set-the-default-language-timezone-and-currency-251724563192',NULL,NULL,NULL,'{\"en\":\"<p>Click on App Listing menu, choose Site to configure your Site. You should choose a language for your Site, this will become the default language for pages you create later. If your Site will serve content in multiple languages, y<span>ou may choose to add other languages<\\/span><span>. Timezone and currency (code, prefix, suffix) should also be set for your site to work properly.<\\/span><\\/p>\"}',NULL,1023,1724563192,1724563192,NULL,NULL,NULL,'To Do',NULL,NULL,NULL,0),
(348,'',NULL,'','',NULL,NULL,NULL,NULL,NULL,0,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4);
/*!40000 ALTER TABLE `mysite16_page` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_pagemeta`
--

DROP TABLE IF EXISTS `mysite16_pagemeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_pagemeta` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(1) unsigned DEFAULT NULL,
  `property` varchar(191) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `order` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `page_property` (`page_id`,`property`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_pagemeta`
--

LOCK TABLES `mysite16_pagemeta` WRITE;
/*!40000 ALTER TABLE `mysite16_pagemeta` DISABLE KEYS */;
INSERT INTO `mysite16_pagemeta` VALUES
(6,1,'upload_dir','2024Q3',NULL,NULL,5),
(109,2,'upload_dir','2024Q3',NULL,NULL,5);
/*!40000 ALTER TABLE `mysite16_pagemeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_product`
--

DROP TABLE IF EXISTS `mysite16_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_product` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `pid` int(1) NOT NULL,
  `sku` text DEFAULT NULL,
  `price` int(1) DEFAULT NULL,
  `was` decimal(12,2) DEFAULT NULL,
  `stock` int(1) unsigned NOT NULL DEFAULT 0,
  `shipping` text DEFAULT NULL,
  `options` text DEFAULT NULL,
  `meta` text DEFAULT NULL,
  `images` text DEFAULT NULL,
  `order` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `variant` (`pid`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=177 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_product`
--

LOCK TABLES `mysite16_product` WRITE;
/*!40000 ALTER TABLE `mysite16_product` DISABLE KEYS */;
INSERT INTO `mysite16_product` VALUES
(1,2,'IP15-PINK-128',799,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Pink\",\"Storage\":\"128GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pink.webp\"]',0),
(3,2,'IP15-GREEN-128',799,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Green\",\"Storage\":\"128GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-green.webp\"]',3),
(4,2,'IP15-YELLOW-128',799,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Yellow\",\"Storage\":\"128GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-yellow.webp\"]',5),
(5,2,'IP15-BLACK-128',799,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Black\",\"Storage\":\"128GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-black.webp\"]',8),
(6,2,'IP15-PINK-256',899,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Pink\",\"Storage\":\"256GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pink.webp\"]',1),
(7,2,'IP15-PINK-512',999,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Pink\",\"Storage\":\"512GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pink.webp\"]',2),
(8,2,'IP15-GREEN-256',899,999.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Green\",\"Storage\":\"256GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-green.webp\"]',4),
(9,2,'IP15-YELLOW-256',899,999.00,0,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Yellow\",\"Storage\":\"256GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-yellow.webp\"]',6),
(10,2,'IP15-YELLOW-512',999,999.00,2,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Base\",\"Color\":\"Yellow\",\"Storage\":\"512GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-yellow.webp\"]',7),
(11,2,'PR15-PINK-256',1099,1499.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Pro\",\"Color\":\"Pink\",\"Storage\":\"256GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pro-pink.webp\"]',9),
(12,2,'PR15-GREEN-256',1099,1499.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Pro\",\"Color\":\"Green\",\"Storage\":\"256GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pro-green.webp\"]',10),
(13,2,'PR15-YELLOW-512',1499,0.00,2,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Pro\",\"Color\":\"Yellow\",\"Storage\":\"512GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pro-yellow.webp\"]',12),
(14,2,'PR15-BLACK-256',1099,1499.00,99,'{\"weight\":\"1\",\"length\":\"5.81\",\"width\":\"2.82\",\"height\":\"0.31\",\"insurance_value\":\"799\"}','{\"Model\":\"Pro\",\"Color\":\"Black\",\"Storage\":\"256GB\"}','null','[\"https:\\/\\/cdn.sitegui.com\\/public\\/uploads\\/global\\/demo\\/iphone-15-pro-black.webp\"]',11);
/*!40000 ALTER TABLE `mysite16_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_user`
--

DROP TABLE IF EXISTS `mysite16_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_user` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `oauth_id` text DEFAULT NULL,
  `oauth_type` text DEFAULT NULL,
  `oauth_account` text DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `mobile` varchar(18) DEFAULT NULL,
  `name` varchar(191) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `handle` varchar(63) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `registered` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1600014 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_user`
--

LOCK TABLES `mysite16_user` WRITE;
/*!40000 ALTER TABLE `mysite16_user` DISABLE KEYS */;
INSERT INTO `mysite16_user` VALUES
(1600001,NULL,NULL,NULL,'anna@mail.com','$2y$10$rYnob7VIjHIaCvwsT1aEx.LRbzNGv9.1EppWYmWmhqDN1cI3fi/.O','','Customer Anna','',NULL,'','','1735502399','Inactive');
/*!40000 ALTER TABLE `mysite16_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_usermeta`
--

DROP TABLE IF EXISTS `mysite16_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_usermeta` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(1) unsigned DEFAULT NULL,
  `property` varchar(191) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `order` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_property` (`user_id`,`property`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_usermeta`
--

LOCK TABLES `mysite16_usermeta` WRITE;
/*!40000 ALTER TABLE `mysite16_usermeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `mysite16_usermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite16_widget`
--

DROP TABLE IF EXISTS `mysite16_widget`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite16_widget` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `type` text NOT NULL,
  `name` text NOT NULL,
  `data` longtext DEFAULT NULL,
  `cache` longtext DEFAULT NULL,
  `expire` int(1) DEFAULT NULL,
  `version` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite16_widget`
--

LOCK TABLES `mysite16_widget` WRITE;
/*!40000 ALTER TABLE `mysite16_widget` DISABLE KEYS */;
INSERT INTO `mysite16_widget` VALUES
(54,'Text','Spotlight','{\"en\":\"<style type=\\\"text\\/css\\\">div.ism-caption{\\r\\n    width:100% !important;\\r\\n    margin:auto !important;\\r\\n    left:0 !important;\\r\\n    background-color: transparent !important;\\r\\n}\\r\\n<\\/style>\\r\\n<link href=\\\"https:\\/\\/fonts.googleapis.com\\/css?family=Open+Sans\\\" rel=\\\"stylesheet\\\" type=\\\"text\\/css\\\" \\/>\\r\\n<link href=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/my-slider.css\\\" rel=\\\"stylesheet\\\" \\/>\\r\\n<script src=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/ism-2.2.min.js\\\"><\\/script>\\r\\n<div class=\\\"row\\\">\\r\\n<div class=\\\"ism-slider mb-3\\\" data-image_fx=\\\"zoomrotate\\\" data-play_type=\\\"loop\\\" id=\\\"my-slider1\\\">\\r\\n<ol>\\r\\n\\t<li><img src=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/spotlight2.jpg\\\" \\/>\\r\\n\\t<div class=\\\"ism-caption ism-caption-0\\\"><b>DO YOU NEED A NEW<\\/b><\\/div>\\r\\n\\r\\n\\t<div class=\\\"ism-caption ism-caption-1\\\" data-delay=\\\"500\\\"><b>WEB DESIGN or MOBILE APP?<\\/b><\\/div>\\r\\n\\t<\\/li>\\r\\n\\t<li><img src=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/spotlight1.jpg\\\" \\/>\\r\\n\\t<div class=\\\"ism-caption ism-caption-0\\\"><b>LET&#39;S START BUILDING<\\/b><\\/div>\\r\\n\\r\\n\\t<div class=\\\"ism-caption\\\"><a class=\\\"ism-caption ism-caption-1\\\" data-delay=\\\"500\\\" href=\\\"\\/\\\" target=\\\"_self\\\">Get a Quote<\\/a><\\/div>\\r\\n\\t<\\/li>\\r\\n<\\/ol>\\r\\n<\\/div>\\r\\n<\\/div>\\r\\n\"}','{\"en\":\"<style type=\\\"text\\/css\\\">div.ism-caption{\\r\\n    width:100% !important;\\r\\n    margin:auto !important;\\r\\n    left:0 !important;\\r\\n    background-color: transparent !important;\\r\\n}\\r\\n<\\/style>\\r\\n<link href=\\\"https:\\/\\/fonts.googleapis.com\\/css?family=Open+Sans\\\" rel=\\\"stylesheet\\\" type=\\\"text\\/css\\\" \\/>\\r\\n<link href=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/my-slider.css\\\" rel=\\\"stylesheet\\\" \\/>\\r\\n<script src=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/ism-2.2.min.js\\\"><\\/script>\\r\\n<div class=\\\"row\\\">\\r\\n<div class=\\\"ism-slider mb-3\\\" data-image_fx=\\\"zoomrotate\\\" data-play_type=\\\"loop\\\" id=\\\"my-slider1\\\">\\r\\n<ol>\\r\\n\\t<li><img src=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/spotlight2.jpg\\\" \\/>\\r\\n\\t<div class=\\\"ism-caption ism-caption-0\\\"><b>DO YOU NEED A NEW<\\/b><\\/div>\\r\\n\\r\\n\\t<div class=\\\"ism-caption ism-caption-1\\\" data-delay=\\\"500\\\"><b>WEB DESIGN or MOBILE APP?<\\/b><\\/div>\\r\\n\\t<\\/li>\\r\\n\\t<li><img src=\\\"\\/\\/cdn.litegui.com\\/public\\/uploads\\/site\\/16\\/slider\\/spotlight1.jpg\\\" \\/>\\r\\n\\t<div class=\\\"ism-caption ism-caption-0\\\"><b>LET&#39;S START BUILDING<\\/b><\\/div>\\r\\n\\r\\n\\t<div class=\\\"ism-caption\\\"><a class=\\\"ism-caption ism-caption-1\\\" data-delay=\\\"500\\\" href=\\\"\\/\\\" target=\\\"_self\\\">Get a Quote<\\/a><\\/div>\\r\\n\\t<\\/li>\\r\\n<\\/ol>\\r\\n<\\/div>\\r\\n<\\/div>\\r\\n\"}',2147483647,NULL),
(63,'Text','CTA','{\"en\":\"<div class=\\\"row\\\" style=\\\"background-color: rgb(35, 165, 52); min-height: 35px; padding:25px;\\\">\\r\\n<div class=\\\"col-md-8 offset-md-2 text-center\\\"><span style=\\\"font-size:30px;color: rgb(255, 255, 255);padding-top:5px;\\\">Have an interesting project in mind?&nbsp; <a class=\\\"btn btn-lg btn-light\\\" href=\\\"\\/\\\">Tell us about it<\\/a><\\/span><\\/div>\\r\\n<\\/div>\"}','{\"en\":\"<div class=\\\"row\\\" style=\\\"background-color: rgb(35, 165, 52); min-height: 35px; padding:25px;\\\">\\r\\n<div class=\\\"col-md-8 offset-md-2 text-center\\\"><span style=\\\"font-size:30px;color: rgb(255, 255, 255);padding-top:5px;\\\">Have an interesting project in mind?&nbsp; <a class=\\\"btn btn-lg btn-light\\\" href=\\\"\\/\\\">Tell us about it<\\/a><\\/span><\\/div>\\r\\n<\\/div>\"}',2147483647,NULL);
/*!40000 ALTER TABLE `mysite16_widget` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite1_user`
--

DROP TABLE IF EXISTS `mysite1_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite1_user` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `oauth_id` text DEFAULT NULL,
  `oauth_type` text DEFAULT NULL,
  `oauth_account` text DEFAULT NULL,
  `email` varchar(191) DEFAULT NULL,
  `password` varchar(191) DEFAULT NULL,
  `mobile` varchar(18) DEFAULT NULL,
  `name` varchar(191) DEFAULT NULL,
  `image` text DEFAULT NULL,
  `handle` varchar(63) DEFAULT NULL,
  `language` char(2) DEFAULT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `registered` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=528100059 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite1_user`
--

LOCK TABLES `mysite1_user` WRITE;
/*!40000 ALTER TABLE `mysite1_user` DISABLE KEYS */;
INSERT INTO `mysite1_user` VALUES
(1,NULL,NULL,NULL,'cron@bot.cron','asdfawqfnr2jbfkjsb23ourfno2nf',NULL,'Cron Admin',NULL,NULL,NULL,NULL,NULL,'Inactive'),
(1023,NULL,NULL,NULL,'sm@litegui.org','$2y$10$SIbr5y9s/8IflX4jTffv2ODg2ydmFDujJt0RsVu0uLJm4dbygVyBi','','Site Admin','','','','','1735442377','Active');
/*!40000 ALTER TABLE `mysite1_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite1_usermeta`
--

DROP TABLE IF EXISTS `mysite1_usermeta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite1_usermeta` (
  `id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(1) unsigned DEFAULT NULL,
  `property` varchar(191) DEFAULT NULL,
  `value` text DEFAULT NULL,
  `name` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `order` int(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_property` (`user_id`,`property`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite1_usermeta`
--

LOCK TABLES `mysite1_usermeta` WRITE;
/*!40000 ALTER TABLE `mysite1_usermeta` DISABLE KEYS */;
/*!40000 ALTER TABLE `mysite1_usermeta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite_admin`
--

DROP TABLE IF EXISTS `mysite_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite_admin` (
  `admin_id` int(1) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(100) NOT NULL,
  `site_id` int(1) NOT NULL,
  `role_id` int(1) NOT NULL,
  `status` varchar(25) DEFAULT NULL,
  `permissions` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `multiple_roles` (`user_id`,`site_id`,`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=899 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite_admin`
--

LOCK TABLES `mysite_admin` WRITE;
/*!40000 ALTER TABLE `mysite_admin` DISABLE KEYS */;
INSERT INTO `mysite_admin` VALUES
(10,'1023',16,1,'Active',NULL);
/*!40000 ALTER TABLE `mysite_admin` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mysite_system`
--

DROP TABLE IF EXISTS `mysite_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mysite_system` (
  `id` int(1) NOT NULL AUTO_INCREMENT,
  `type` varchar(100) NOT NULL,
  `object` varchar(100) NOT NULL,
  `property` varchar(100) NOT NULL,
  `value` text DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_object_property` (`type`(25),`object`(25),`property`(50))
) ENGINE=InnoDB AUTO_INCREMENT=15593 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mysite_system`
--

LOCK TABLES `mysite_system` WRITE;
/*!40000 ALTER TABLE `mysite_system` DISABLE KEYS */;
INSERT INTO `mysite_system` VALUES
(1,'role','global','Site Manager',',Page::create,Page::publish,Role::SiteManager,Site::update,Product::publish,Site::delete,Appstore::create,Page::design,User::manage,Inventory::create,File::manage,File::protect,Order::view,Order::process,Invoice::create,Invoice::publish,Product::read,Product::create,Role::ApiUser,Role::Server2Server,Role::Staff,Role::Partner,Role::Engineer,Role::Sales,Role::Accountant,Role::Marketing,Role::HR,Role::SeniorEngineer,Role::TechnicalManager,Role::SalesManager,Role::AccountingManager,Role::MarketingManager,Role::HRManager,Role::Leader,Role::Manager,Role::C*O,Role::Supervisor,Role::Supplier,',NULL,'Site Manager has permissions to create and publish pages.'),
(2,'role','global','Freelancer',',Appstore::create,File::manage,Page::create,',NULL,'Freelancer has permissions to create pages but not publish them'),
(5,'permission','','Page::create',',Page,Menu,App,Widget,Collection,Profile,','Create, Update, List',''),
(6,'permission','','Page::publish',',Page,Menu,App,Widget,Layout,Template,Collection,Profile,','Publish, Delete',''),
(59,'permission','','Role::SiteManager',',Administrator,Staff,Role,','SiteManager',''),
(71,'permission','','Site::update',',Site,Upgrade,Report,Activity,','Manage',NULL),
(76,'role','global','Customer',',Page::create,Role::Customer,',NULL,'This role defines what permissions a customer/user has'),
(99,'permission','','Product::publish',',Tax,Coupon,Shipping,Freelance,Product,','Publish',NULL),
(140,'permission','','Role::Server2Server',',Role,','Server2Server',NULL),
(145,'SiteAdmin_route','','Page::main','[\"GET\",\"/page.[json:format]?\"]',NULL,NULL),
(146,'SiteAdmin_route','','Page::delete','[\"POST\",\"/page/delete.[json:format]?/[POST:id]?\"]',NULL,NULL),
(147,'SiteAdmin_route','','Page::update','[\"POST\",\"/page/update.[json:format]?/[POST:page]?\"]',NULL,NULL),
(148,'SiteAdmin_route','','Page::action','[\"GET|POST\",\"/page/[edit|copy:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(149,'SiteAdmin_route','','Menu::main','[\"GET\",\"/menu.[json:format]?\"]',NULL,NULL),
(150,'SiteAdmin_route','','Menu::delete','[\"POST\",\"/menu/delete.[json:format]?/[POST:id]?\"]',NULL,NULL),
(151,'SiteAdmin_route','','Menu::update','[\"POST\",\"/menu/update.[json:format]?/[POST:menu]?\"]',NULL,NULL),
(152,'SiteAdmin_route','','Menu::action','[\"GET|POST\",\"/menu/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(153,'SiteAdmin_route','','Menu::deleteLocation','[\"POST\",\"/menu/delete/location.[json:format]?/[POST:id]?\"]',NULL,NULL),
(192,'permission','','Appstore::create',',Appstore,','Create',NULL),
(193,'permission','','Appstore::publish',',Appstore,','Publish',NULL),
(198,'role','global','Developer',',Page::create,Site::update,Appstore::create,Page::design,File::manage,Product::create,Role::Staff,',NULL,'Developer can create pages'),
(213,'system','','revision','1',NULL,NULL),
(341,'permission','','Role::Customer',',Order,Journal,Role,','Customer',NULL),
(431,'permission','','Page::design',',Layout,Template,File,','Design',NULL),
(440,'SiteAdmin_route','','Layout::delete','[\"POST\",\"/layout/delete.[json:format]?/[POST:id]?\"]',NULL,NULL),
(441,'SiteAdmin_route','','Layout::update','[\"POST\",\"/layout/update.[json:format]?/[POST:layout]?\"]',NULL,NULL),
(442,'SiteAdmin_route','','Layout::action','[\"GET|POST\",\"/layout/[edit|copy:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(443,'SiteAdmin_route','','Layout::main','[\"GET\",\"/layout.[json:format]?\"]',NULL,NULL),
(491,'SiteAdmin_route','','Widget::delete','[\"POST\",\"/widget/delete.[json:format]?/[POST:id]?\"]',NULL,NULL),
(492,'SiteAdmin_route','','Widget::update','[\"POST\",\"/widget/update.[json:format]?/[POST:widget]?\"]',NULL,NULL),
(493,'SiteAdmin_route','','Widget::action','[\"GET|POST\",\"/widget/[edit|copy:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(494,'SiteAdmin_route','','Widget::main','[\"GET\",\"/widget.[json:format]?\"]',NULL,NULL),
(495,'SiteAdmin_route','','Widget::deleteLocation','[\"POST\",\"/widget/delete/location.[json:format]?[POST:id]?\"]',NULL,NULL),
(496,'SiteAdmin_route','','Widget::preview','[\"POST\",\"/widget/preview.[json:format]?[POST:id]?\"]',NULL,NULL),
(540,'SiteAdmin_route','','Template::widget','[\"GET|POST\",\"/template/widget.[json:format]?[POST:id]?\"]',NULL,NULL),
(545,'SiteAdmin_route','','Template::delete','[\"POST\",\"/template/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(546,'SiteAdmin_route','','Template::update','[\"POST\",\"/template/update.[json:format]?[POST:template]?\"]',NULL,NULL),
(547,'SiteAdmin_route','','Template::action','[\"GET|POST\",\"/template/[edit|copy:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(548,'SiteAdmin_route','','Template::snippet','[\"GET|POST\",\"/template/snippet.[json:format]?[POST:id]?\"]',NULL,NULL),
(549,'SiteAdmin_route','','Template::main','[\"GET|POST\",\"/template/[*:template]?.[json:format]?\"]',NULL,NULL),
(550,'role','global','API User',',Role::ApiUser,',NULL,'User having this role will be able to perform API requests'),
(593,'SiteAdmin_route','','Tax::delete','[\"POST\",\"/tax/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(594,'SiteAdmin_route','','Tax::update','[\"POST\",\"/tax/update.[json:format]?[POST:tax]?\"]',NULL,NULL),
(595,'SiteAdmin_route','','Tax::action','[\"GET|POST\",\"/tax/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(596,'SiteAdmin_route','','Tax::main','[\"GET\",\"/tax.[json:format]?\"]',NULL,NULL),
(618,'SiteAdmin_route','','Collection::delete','[\"POST\",\"/collection/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(619,'SiteAdmin_route','','Collection::update','[\"POST\",\"/collection/update.[json:format]?/[POST:page]?\"]',NULL,NULL),
(620,'SiteAdmin_route','','Collection::action','[\"GET|POST\",\"/collection/[edit|copy:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(621,'SiteAdmin_route','','Collection::leave','[\"POST\",\"/collection/leave.[json:format]?/[POST:id]?\"]',NULL,NULL),
(622,'SiteAdmin_route','','Collection::main','[\"GET\",\"/collection.[json:format]?\"]',NULL,NULL),
(629,'permission','','Inventory::create',',Inventory,','Create',NULL),
(634,'permission','','File::manage',',File,','Manage',NULL),
(635,'permission','','File::protect',',File,','Protect',NULL),
(636,'SiteAdmin_route','','File::action','[\"GET|POST\",\"/file/[manage|view:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(637,'SiteAdmin_route','','File::main','[\"GET\",\"/file.[json:format]?\"]',NULL,NULL),
(638,'SiteUser_route','','File::clientView','[\"GET\",\"/file/view/[*:id]?.[json:format]?\"]',NULL,NULL),
(647,'permission','','Order::view',',Cart,Order,Subscription,','View',NULL),
(648,'permission','','Order::process',',Order,Subscription,','Process',NULL),
(760,'permission','','Invoice::create',',Invoice,Transaction,Wallet,Journal,Coa,','Create',NULL),
(761,'permission','','Invoice::publish',',Invoice,Journal,Coa,','Publish',NULL),
(816,'permission','','Role::ApiUser',',Staff,Role,','ApiUser',NULL),
(817,'permission','','Role::Staff',',Notification,Role,Assistant,','Staff',NULL),
(847,'role','global','Staff',',Page::create,File::manage,File::protect,Product::read,Role::Staff,',NULL,'Basic permissions for Staff, do not add higher level permissions, they should be specified in their own roles'),
(849,'SiteAdmin_route','','Notification::delete','[\"POST\",\"/notification/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(850,'SiteAdmin_route','','Notification::update','[\"POST\",\"/notification/update.[json:format]?[POST:notification]?\"]',NULL,NULL),
(851,'SiteAdmin_route','','Notification::action','[\"GET|POST\",\"/notification/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(852,'SiteAdmin_route','','Notification::main','[\"GET\",\"/notification.[json:format]?\"]',NULL,NULL),
(932,'permission','','Product::read',',Product,','Read',NULL),
(933,'permission','','Product::create',',Freelance,Product,','Create',NULL),
(1015,'SiteAdmin_route','','Staff::delete','[\"POST\",\"/staff/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(1016,'SiteAdmin_route','','Staff::update','[\"POST\",\"/staff/update.[json:format]?[POST:staff]?\"]',NULL,NULL),
(1017,'SiteAdmin_route','','Staff::action','[\"GET|POST\",\"/staff/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(1018,'SiteAdmin_route','','Staff::onboard','[\"GET|POST\",\"/staff/onboard/[POST:done]?.[json:format]?\"]',NULL,NULL),
(1019,'SiteAdmin_route','','Staff::main','[\"GET\",\"/staff.[json:format]?\"]',NULL,NULL),
(2056,'SiteAdmin_route','','Profile::delete','[\"POST\",\"/profile/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(2057,'SiteAdmin_route','','Profile::update','[\"POST\",\"/profile/update.[json:format]?[POST:page]?\"]',NULL,NULL),
(2058,'SiteAdmin_route','','Profile::action','[\"GET|POST\",\"/profile/[edit|copy:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(2059,'SiteAdmin_route','','Profile::main','[\"GET\",\"/profile.[json:format]?\"]',NULL,NULL),
(2060,'SiteUser_route','','Profile::clientView','[\"GET\",\"/profile/view/[*:id]?.[json:format]?\"]',NULL,NULL),
(2061,'SiteUser_route','','Profile::clientMain','[\"GET\",\"/profile.[json:format]?\"]',NULL,NULL),
(2062,'SiteUser_route','','Profile::clientUpdate','[\"GET|POST\",\"/profile/update.[json:format]?/[POST:page]?\"]',NULL,NULL),
(2063,'user_route','','Profile::renderCollection','[\"GET\",\"/profile/collection/[*:slug]?.[html|json:format]?\"]',NULL,NULL),
(2064,'user_route','','Profile::render','[\"GET\",\"/profile/[*:slug]?.[html|json:format]?\"]',NULL,NULL),
(8769,'permission','','Role::Supplier',',Role,','Supplier',NULL),
(8770,'permission','','Role::Partner',',Role,','Partner',NULL),
(8771,'permission','','Role::Engineer',',Role,','Engineer',NULL),
(8772,'permission','','Role::Sales',',Role,','Sales',NULL),
(8773,'permission','','Role::Accountant',',Role,','Accountant',NULL),
(8774,'permission','','Role::Marketing',',Role,','Marketing',NULL),
(8775,'permission','','Role::HR',',Role,','HR',NULL),
(8776,'permission','','Role::SeniorEngineer',',Role,','SeniorEngineer',NULL),
(8777,'permission','','Role::TechnicalManager',',Role,','TechnicalManager',NULL),
(8778,'permission','','Role::SalesManager',',Role,','SalesManager',NULL),
(8779,'permission','','Role::AccountingManager',',Role,','AccountingManager',NULL),
(8780,'permission','','Role::MarketingManager',',Role,','MarketingManager',NULL),
(8781,'permission','','Role::HRManager',',Role,','HRManager',NULL),
(8782,'permission','','Role::Supervisor',',Role,','Supervisor',NULL),
(8783,'permission','','Role::Manager',',Role,','Manager',NULL),
(8784,'permission','','Role::C*O',',Role,','C*O',NULL),
(8790,'SiteAdmin_route','','Role::delete','[\"POST\",\"/role/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(8791,'SiteAdmin_route','','Role::update','[\"POST\",\"/role/update.[json:format]?[POST:role]?\"]',NULL,NULL),
(8792,'SiteAdmin_route','','Role::action','[\"GET|POST\",\"/role/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(8793,'SiteAdmin_route','','Role::main','[\"GET\",\"/role.[json:format]?\"]',NULL,NULL),
(13792,'permission','','User::manage',',User,Group,','Manage',NULL),
(13793,'SiteAdmin_route','','User::delete','[\"POST\",\"/user/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(13794,'SiteAdmin_route','','User::update','[\"POST\",\"/user/update.[json:format]?[POST:user]?\"]',NULL,NULL),
(13795,'SiteAdmin_route','','User::action','[\"GET|POST\",\"/user/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(13796,'SiteAdmin_route','','User::main','[\"GET\",\"/user.[json:format]?\"]',NULL,NULL),
(13797,'SiteUser_route','','User::clientUpdate','[\"POST\",\"/update.[json:format]?[POST:user]?\"]',NULL,NULL),
(13798,'SiteUser_route','','User::clientView','[\"GET|POST\",\"/view.[json:format]?\"]',NULL,NULL),
(14174,'SiteAdmin_route','','Group::delete','[\"POST\",\"/group/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(14175,'SiteAdmin_route','','Group::update','[\"POST\",\"/group/update.[json:format]?[POST:group]?\"]',NULL,NULL),
(14176,'SiteAdmin_route','','Group::action','[\"GET|POST\",\"/group/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(14177,'SiteAdmin_route','','Group::main','[\"GET\",\"/group.[json:format]?\"]',NULL,NULL),
(14947,'SiteAdmin_route','','Activity::delete','[\"POST\",\"/activity/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(14948,'SiteAdmin_route','','Activity::update','[\"POST\",\"/activity/update.[json:format]?[POST:activity]?\"]',NULL,NULL),
(14949,'SiteAdmin_route','','Activity::action','[\"GET|POST\",\"/activity/[edit|copy:action]/[i:id]?.[json:format]?\"]',NULL,NULL),
(14950,'SiteAdmin_route','','Activity::main','[\"GET\",\"/activity.[json:format]?\"]',NULL,NULL),
(15587,'SiteAdmin_route','','Product::delete','[\"POST\",\"/product/delete.[json:format]?[POST:id]?\"]',NULL,NULL),
(15588,'SiteAdmin_route','','Product::update','[\"POST\",\"/product/update.[json:format]?/[POST:page]?\"]',NULL,NULL),
(15589,'SiteAdmin_route','','Product::action','[\"GET|POST\",\"/product/[edit|copy|manage:action]/[*:id]?.[json:format]?\"]',NULL,NULL),
(15590,'SiteAdmin_route','','Product::deleteVariant','[\"POST\",\"/product/variant/delete.[json:format]?/[POST:id]?\"]',NULL,NULL),
(15591,'SiteAdmin_route','','Product::updateStock','[\"POST\",\"/product/stock.[json:format]?/[POST:variants]?\"]',NULL,NULL),
(15592,'SiteAdmin_route','','Product::main','[\"GET\",\"/product.[json:format]?\"]',NULL,NULL);
/*!40000 ALTER TABLE `mysite_system` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-12-29 20:54:44
