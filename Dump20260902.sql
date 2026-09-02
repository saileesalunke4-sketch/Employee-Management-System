CREATE DATABASE  IF NOT EXISTS `emp1_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `emp1_db`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: emp1_db
-- ------------------------------------------------------
-- Server version	9.6.0

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '0d1ac67a-0965-11f1-9547-a8934aace692:1-38971';

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `actor_id` int DEFAULT NULL,
  `actor_name` varchar(150) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `target_type` varchar(50) DEFAULT NULL,
  `target_name` varchar(150) DEFAULT NULL,
  `details` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_log`
--

LOCK TABLES `activity_log` WRITE;
/*!40000 ALTER TABLE `activity_log` DISABLE KEYS */;
INSERT INTO `activity_log` VALUES (1,11,'Sailee Salunke','cancelled','Leave Request','Sailee Salunke — Sick Leave','2026-05-31 to 2026-06-02','2026-07-22 08:53:02'),(2,1,'Admin User','created','Asset','Dell Latitude 5440 Laptop','Laptop','2026-07-23 07:51:41'),(3,1,'Admin User','assigned','Asset','Dell Latitude 5440 Laptop','To Sailee Salunke','2026-07-23 07:52:15'),(4,1,'Admin User','created','Asset','Dell Latitude 7450','Laptop','2026-07-23 07:57:05'),(5,1,'Admin User','assigned','Asset','Dell Latitude 7450','To Sneha  Singh','2026-07-23 07:57:42'),(6,1,'Admin User','created','Asset','Microsoft Surface Laptop 7','Laptop','2026-07-23 07:58:44'),(7,1,'Admin User','assigned','Asset','Microsoft Surface Laptop 7','To Shlok Thakur','2026-07-23 07:58:55'),(8,1,'Admin User','created','Asset','Apple MacBook Air 13-inch (M4)','Laptop','2026-07-23 07:59:40'),(9,1,'Admin User','assigned','Asset','Apple MacBook Air 13-inch (M4)','To Shanaya Khan','2026-07-23 08:00:00'),(10,1,'Admin User','created','Asset','HP EliteBook 840 G10','Laptop','2026-07-23 08:00:37'),(11,1,'Admin User','assigned','Asset','HP EliteBook 840 G10','To Ravi Mehta','2026-07-23 08:00:49'),(12,1,'Admin User','created','Asset','iPhone 15  Apple','Phone','2026-07-23 08:02:04'),(13,1,'Admin User','assigned','Asset','iPhone 15  Apple','To Rahul sharma','2026-07-23 08:02:16'),(14,1,'Admin User','created','Asset','HP 24\" Monitor  HP','Monitor','2026-07-23 08:03:44'),(15,1,'Admin User','assigned','Asset','HP 24\" Monitor  HP','To Priya Patel','2026-07-23 08:03:57'),(16,1,'Admin User','created','Shift','Morning','08:00:00-12:00:00','2026-07-23 08:08:18'),(17,1,'Admin User','assigned','Shift','Rahul sharma','Assigned to Morning','2026-07-23 08:08:53'),(18,1,'Admin User','assigned','Shift','Rahul sharma','Assigned to General Shift','2026-07-23 08:31:21'),(19,1,'Admin User','deleted','Shift','Morning','','2026-07-23 08:31:34'),(20,1,'Admin User','created','Shift','Morning Shift','08:00:00-11:00:00','2026-07-23 08:32:18'),(21,1,'Admin User','deleted','Shift','Morning Shift','','2026-07-23 08:40:40'),(22,1,'Admin User','created','Shift','Morning Shift','20:00:00-16:00:00','2026-07-23 08:41:12'),(23,1,'Admin User','created','Shift','Afternoon Shift','14:00:00-22:00:00','2026-07-23 08:43:09'),(24,1,'Admin User','created','Shift','Night Shift','22:00:00-06:00:00','2026-07-23 08:46:10'),(25,1,'Admin User','assigned','Shift','Shanaya Khan','Assigned to Afternoon Shift','2026-07-23 08:47:10'),(26,1,'Admin User','created','Asset','Wireless Mouse','Mouse','2026-07-23 09:11:27'),(27,1,'Admin User','assigned','Asset','Wireless Mouse','To Nita Seth','2026-07-23 09:11:52'),(28,1,'Admin User','approved','WFH Request','Sneha  Singh','2026-07-28','2026-07-23 09:14:37'),(29,1,'Admin User','approved','Leave Request','Nita Seth — Special Casual Leave','2026-06-11 to 2026-06-12','2026-08-01 05:23:21'),(30,1,'Admin User','approved','WFH Request','Sailee Salunke','2026-07-27','2026-08-04 08:43:23');
/*!40000 ALTER TABLE `activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `announcement_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(150) DEFAULT NULL,
  `message` text,
  `posted_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcement_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Open Enrollment for 2026 Benefits is now live','Hi Team,Our annual Open Enrollment period for company health and dental benefits is now open.The deadline to submit or change your selections is 15-07-2026','Admin User','2026-07-10 06:21:31');
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_assignments`
--

DROP TABLE IF EXISTS `asset_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `asset_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `asset_id` int NOT NULL,
  `emp_id` int NOT NULL,
  `assigned_date` date NOT NULL,
  `returned_date` date DEFAULT NULL,
  `condition_notes` varchar(255) DEFAULT NULL,
  `assigned_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `asset_id` (`asset_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_assignments`
--

LOCK TABLES `asset_assignments` WRITE;
/*!40000 ALTER TABLE `asset_assignments` DISABLE KEYS */;
INSERT INTO `asset_assignments` VALUES (1,1,8,'2026-07-23',NULL,NULL,1,'2026-07-23 07:52:15'),(2,2,6,'2026-07-23',NULL,NULL,1,'2026-07-23 07:57:42'),(3,3,10,'2026-07-23',NULL,NULL,1,'2026-07-23 07:58:55'),(4,4,14,'2026-07-23',NULL,NULL,1,'2026-07-23 08:00:00'),(5,5,7,'2026-07-23',NULL,NULL,1,'2026-07-23 08:00:49'),(6,6,3,'2026-07-23',NULL,NULL,1,'2026-07-23 08:02:16'),(7,7,4,'2026-07-23',NULL,NULL,1,'2026-07-23 08:03:57'),(8,8,9,'2026-07-23',NULL,NULL,1,'2026-07-23 09:11:52');
/*!40000 ALTER TABLE `asset_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assets` (
  `asset_id` int NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(150) NOT NULL,
  `asset_type` varchar(50) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `status` enum('available','assigned','under_repair','retired') NOT NULL DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
INSERT INTO `assets` VALUES (1,'Dell Latitude 5440 Laptop','Laptop','AST001','2026-02-02','assigned','2026-07-23 07:51:41'),(2,'Dell Latitude 7450','Laptop','AST002','2026-02-23','assigned','2026-07-23 07:57:05'),(3,'Microsoft Surface Laptop 7','Laptop','AST003','2026-02-23','assigned','2026-07-23 07:58:44'),(4,'Apple MacBook Air 13-inch (M4)','Laptop','AST004','2026-02-23','assigned','2026-07-23 07:59:40'),(5,'HP EliteBook 840 G10','Laptop','AST005','2026-02-23','assigned','2026-07-23 08:00:37'),(6,'iPhone 15  Apple','Phone','AST006','2026-02-23','assigned','2026-07-23 08:02:04'),(7,'HP 24\" Monitor  HP','Monitor','AST007','2026-02-23','assigned','2026-07-23 08:03:44'),(8,'Wireless Mouse','Mouse','AST007','2026-02-15','assigned','2026-07-23 09:11:27');
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance`
--

DROP TABLE IF EXISTS `attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance` (
  `attendance_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `date` date DEFAULT NULL,
  `status` enum('present','absent','late','half_day','work_from_home') DEFAULT NULL,
  `work_mode` enum('WFH','WFO') NOT NULL DEFAULT 'WFO',
  `overtime_hours` decimal(5,2) DEFAULT '0.00',
  `is_sunday` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`attendance_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=51 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (8,8,'09:00:00','18:00:00','2026-04-10','present','WFO',0.00,0),(9,8,'09:00:00','00:00:00','2026-04-10','half_day','WFO',0.00,0),(10,8,'09:00:00','17:40:00','2026-04-12','present','WFO',0.00,0),(11,8,'09:00:00','17:00:00','2026-04-11','present','WFO',0.00,0),(12,8,'09:00:00','18:00:00','2026-04-13','work_from_home','WFO',0.00,0),(19,8,'09:00:00','06:00:00','2026-04-24','present','WFO',0.00,0),(23,8,'21:00:00','18:00:00','2026-05-25','present','WFO',0.00,0),(25,8,'08:51:54','12:28:22','2026-06-21','present','WFO',0.00,0),(28,8,'14:35:58',NULL,'2026-06-24','work_from_home','WFO',0.00,0),(30,8,'14:42:20','16:32:04','2026-07-06','work_from_home','WFO',0.00,0),(31,8,'06:41:35',NULL,'2026-07-09','work_from_home','WFO',0.00,0),(33,8,'14:31:44',NULL,'2026-07-10','work_from_home','WFO',0.00,0),(34,8,'12:17:46',NULL,'2026-07-11','work_from_home','WFO',0.00,0),(35,8,'11:43:01',NULL,'2026-07-15','work_from_home','WFO',0.00,0),(36,8,'15:40:04','20:44:05','2026-07-16','work_from_home','WFO',0.00,0),(37,8,'09:40:50','20:24:32','2026-07-17','work_from_home','WFO',2.73,0),(38,8,'09:14:26',NULL,'2026-07-22','work_from_home','WFO',0.00,0),(49,8,NULL,NULL,'2026-07-27','work_from_home','WFO',0.00,0),(50,8,'09:18:00','09:00:00','2026-08-05','work_from_home','WFO',0.00,0);
/*!40000 ALTER TABLE `attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `daily_logs`
--

DROP TABLE IF EXISTS `daily_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `daily_logs` (
  `log_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `log_date` date DEFAULT NULL,
  `work_done` text,
  `hours_spent` decimal(4,2) DEFAULT NULL,
  `productivity_score` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `daily_logs_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `daily_logs`
--

LOCK TABLES `daily_logs` WRITE;
/*!40000 ALTER TABLE `daily_logs` DISABLE KEYS */;
INSERT INTO `daily_logs` VALUES (1,8,'2026-05-01','Fixed 30+ UI bugs, updated sidebar',5.50,40,'2026-05-01 11:21:57');
/*!40000 ALTER TABLE `daily_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `department_wall_posts`
--

DROP TABLE IF EXISTS `department_wall_posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `department_wall_posts` (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `dept_id` int DEFAULT NULL,
  `message` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`),
  KEY `dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `department_wall_posts`
--

LOCK TABLES `department_wall_posts` WRITE;
/*!40000 ALTER TABLE `department_wall_posts` DISABLE KEYS */;
/*!40000 ALTER TABLE `department_wall_posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `dept_id` int NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(100) NOT NULL,
  `dept_head` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Development','Rahul Sharma','2026-05-01 11:39:30'),(4,'UI Design','Shreya Sathe','2026-05-07 03:14:09'),(5,'QA','Nisha','2026-05-23 02:03:17'),(15,'Management','Arjun Malhotra','2026-07-20 07:23:23'),(16,'Human Resources (HR)','Priya Iyer','2026-07-20 07:23:58'),(17,'Administration','Vikram Rathore','2026-07-20 07:24:26'),(18,'IT Support','Anjali Deshmukh','2026-07-20 07:24:53'),(19,'Software Development','Rohan Kulkarni','2026-07-20 07:25:21'),(20,'DevOps & Cloud','Kavya Reddy','2026-07-20 07:26:06'),(21,'IoT Development','Aditya Bhatt','2026-07-20 07:26:38'),(22,'Digital Marketing','Meera Nair ','2026-07-20 07:27:11'),(23,'Sales & Business Development','Karan Chopra','2026-07-20 07:27:39'),(24,'Customer Support','Sanya Kapoor','2026-07-20 07:28:05'),(25,'Finance & Accounts','Siddharth Rao','2026-07-20 07:28:41'),(26,'Research & Development (R&D)','Divya Menon','2026-07-20 07:29:09'),(27,'Internship & Training','Rhea Kapoor','2026-07-20 07:30:04');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emp_performance`
--

DROP TABLE IF EXISTS `emp_performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emp_performance` (
  `id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int NOT NULL,
  `goal` varchar(255) NOT NULL,
  `description` text,
  `target_date` date NOT NULL,
  `status` enum('in_progress','completed','pending') DEFAULT 'pending',
  `added_date` date NOT NULL,
  `admin_remarks` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `emp_performance_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emp_performance`
--

LOCK TABLES `emp_performance` WRITE;
/*!40000 ALTER TABLE `emp_performance` DISABLE KEYS */;
/*!40000 ALTER TABLE `emp_performance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_documents`
--

DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_documents` (
  `doc_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `pan_card` varchar(255) DEFAULT NULL,
  `aadhar_card` varchar(255) DEFAULT NULL,
  `marks_card` varchar(255) DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`doc_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_documents`
--

LOCK TABLES `employee_documents` WRITE;
/*!40000 ALTER TABLE `employee_documents` DISABLE KEYS */;
INSERT INTO `employee_documents` VALUES (1,8,'pan_card_8_1780735142.png','aadhar_card_8_1780735142.png','marks_card_8_1780735142.png','2026-06-06 08:39:02');
/*!40000 ALTER TABLE `employee_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `emp_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `contact` varchar(15) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `address` text,
  `permanent_address` text,
  `biometric_id` varchar(50) DEFAULT NULL,
  `caste` varchar(50) DEFAULT NULL,
  `sub_caste` varchar(50) DEFAULT NULL,
  `common_address` text,
  `pan_card` varchar(255) DEFAULT NULL,
  `aadhar_card` varchar(255) DEFAULT NULL,
  `marks_card` varchar(255) DEFAULT NULL,
  `dept_id` int DEFAULT NULL,
  `work_location` varchar(100) DEFAULT NULL,
  `employee_code` varchar(20) DEFAULT NULL,
  `shift_id` int DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`emp_id`),
  KEY `user_id` (`user_id`),
  KEY `dept_id` (`dept_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (8,11,'Sailee','Salunke','9284466593','senior devloper','AB+','2002-07-28','','Nagar','',NULL,'','','','8_pan_card_1780726779.png','8_aadhar_card_1780726779.png','8_marks_card_1780726779.png',19,NULL,'EMP0008',1,'active');
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holidays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `holiday_name` varchar(100) NOT NULL,
  `holiday_date` date NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `holiday_type` varchar(50) DEFAULT 'National',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (2,'Republic Day','2026-01-26',NULL,'National'),(3,'Holi','2026-03-14',NULL,'Festival'),(4,'Gudi Padwa','2026-03-30',NULL,'State'),(5,'Good Friday','2026-04-03',NULL,'National'),(6,'Dr. Ambedkar Jayanti','2026-04-14',NULL,'National'),(7,'Maharashtra Day','2026-05-01',NULL,'State'),(8,'Independence Day','2026-08-15',NULL,'National'),(9,'Ganesh Chaturthi','2026-08-27',NULL,'Festival'),(10,'Gandhi Jayanti','2026-10-02',NULL,'National'),(11,'Dussehra','2026-10-02',NULL,'Festival'),(14,'Gurunanak Jayanti','2026-11-05',NULL,'National'),(15,'Christmas','2026-12-25',NULL,'National'),(16,'Eid','2026-03-21',NULL,'National'),(17,'Akshaya Tritiya ','2026-04-19',NULL,'Festival'),(18,'Maha Navami','2026-10-20',NULL,'State'),(19,'Vijayadashmi','2026-10-21',NULL,'Festival'),(20,'Diwali Laxmi Puja','2026-11-08',NULL,'Festival');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hr_process_requests`
--

DROP TABLE IF EXISTS `hr_process_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hr_process_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `request_type` varchar(30) DEFAULT NULL,
  `current_value` varchar(150) DEFAULT NULL,
  `requested_value` varchar(150) DEFAULT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hr_process_requests`
--

LOCK TABLES `hr_process_requests` WRITE;
/*!40000 ALTER TABLE `hr_process_requests` DISABLE KEYS */;
INSERT INTO `hr_process_requests` VALUES (1,8,'Department Change','-','QA','Shifted to QA team','approved','2026-07-10 06:02:26',1),(2,8,'Designation Change','Web Designer','senior devloper','working for month in this department','approved','2026-07-10 09:04:09',1),(3,8,'Department Change','QA','UI Design','xyz','pending','2026-07-11 06:54:02',NULL);
/*!40000 ALTER TABLE `hr_process_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hr_queries`
--

DROP TABLE IF EXISTS `hr_queries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hr_queries` (
  `query_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text,
  `status` enum('pending','resolved') DEFAULT 'pending',
  `admin_reply` text,
  `replied_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`query_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hr_queries`
--

LOCK TABLES `hr_queries` WRITE;
/*!40000 ALTER TABLE `hr_queries` DISABLE KEYS */;
INSERT INTO `hr_queries` VALUES (1,8,'For Payslip download','Hi , I need my last 3 months payslips for a loan application, but the portal is down. Could you please email them to me?','resolved','Our internal HR portal is currently undergoing scheduled maintenance ,If you cannot wait until then, please let me know, and I will manually download and email them to you shortly.','Admin User','2026-06-25 10:52:45','2026-06-25 10:58:26');
/*!40000 ALTER TABLE `hr_queries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `leave_type_name` varchar(100) NOT NULL,
  `total_days` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES (1,'Sick Leave',10,'2026-04-04 05:19:22'),(2,'Casual Leave',8,'2026-04-04 05:19:22'),(3,'Privilege Leave',15,'2026-04-04 05:19:22'),(4,'Unpaid Leave',0,'2026-04-04 05:19:22'),(5,'Materninty leave',90,'2026-04-04 06:15:30'),(6,'Sabbatical',90,'2026-05-24 03:23:20'),(7,'Special Casual Leave',10,'2026-06-09 08:46:18'),(8,'Earned Leave',10,'2026-06-09 08:46:56'),(9,'Paternity Leave',10,'2026-06-09 08:47:40'),(10,'Sub Artical Leave',10,'2026-06-09 08:48:37');
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `leave_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `leave_type` varchar(100) DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `is_half_day` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`leave_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
INSERT INTO `leaves` VALUES (8,8,'Unpaid Leave','2026-04-13','2026-04-14','xyz','pending',0),(11,8,'Casual Leave','2026-05-04','2026-05-05','Family Gathering','approved',0),(13,8,'Sick Leave','2026-05-31','2026-06-02','Fever ','cancelled',0),(14,8,'Sick Leave','2026-06-08','2026-06-09','fever','approved',0),(15,8,'Privilege Leave','2026-06-09','2026-06-10','Out of town','rejected',0);
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `login_otps`
--

DROP TABLE IF EXISTS `login_otps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `login_otps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `otp_code` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `attempts` int NOT NULL DEFAULT '0',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `login_otps`
--

LOCK TABLES `login_otps` WRITE;
/*!40000 ALTER TABLE `login_otps` DISABLE KEYS */;
INSERT INTO `login_otps` VALUES (1,11,'451257','2026-08-30 14:58:43',0,1,'2026-08-30 09:18:43'),(2,11,'504223','2026-08-30 20:24:33',0,1,'2026-08-30 14:44:33'),(3,5,'293459','2026-08-31 06:40:28',0,1,'2026-08-31 01:00:28'),(4,1,'523756','2026-08-31 06:45:47',0,1,'2026-08-31 01:05:47'),(5,11,'221500','2026-08-31 20:20:07',0,1,'2026-08-31 14:40:07'),(6,11,'686708','2026-08-31 20:35:35',0,1,'2026-08-31 14:55:35'),(7,11,'739714','2026-09-01 09:34:31',0,0,'2026-09-01 03:54:31'),(8,11,'585703','2026-09-02 06:59:43',0,0,'2026-09-02 01:19:43'),(9,11,'344730','2026-09-02 07:01:00',0,1,'2026-09-02 01:21:00'),(10,11,'613891','2026-09-02 07:06:45',0,1,'2026-09-02 01:26:45'),(11,11,'772436','2026-09-02 07:07:55',0,1,'2026-09-02 01:27:55'),(12,11,'788420','2026-09-02 07:42:40',0,1,'2026-09-02 02:02:40'),(13,11,'043754','2026-09-02 07:48:52',0,0,'2026-09-02 02:08:52'),(14,11,'255282','2026-09-02 07:54:12',0,0,'2026-09-02 02:14:12'),(15,11,'514344','2026-09-02 08:01:49',0,1,'2026-09-02 02:21:49'),(16,11,'592851','2026-09-02 08:06:39',0,0,'2026-09-02 02:26:39'),(17,11,'638913','2026-09-02 09:41:20',0,1,'2026-09-02 04:01:20'),(18,11,'635153','2026-09-02 09:47:22',1,1,'2026-09-02 04:07:22'),(19,11,'557049','2026-09-02 09:59:43',0,1,'2026-09-02 04:19:43'),(20,11,'353529','2026-09-02 10:07:27',0,1,'2026-09-02 04:27:27'),(21,11,'585966','2026-09-02 10:18:25',0,0,'2026-09-02 04:38:25'),(22,11,'680327','2026-09-02 10:25:37',0,1,'2026-09-02 04:45:37'),(23,11,'851677','2026-09-02 10:45:41',0,0,'2026-09-02 05:05:41'),(24,11,'059207','2026-09-02 10:48:34',0,0,'2026-09-02 05:08:34'),(25,11,'250031','2026-09-02 10:50:25',0,1,'2026-09-02 05:10:25');
/*!40000 ALTER TABLE `login_otps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `emp_id` varchar(20) NOT NULL,
  `emp_name` varchar(100) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `reason` text,
  `message` text,
  `type` varchar(50) DEFAULT 'leave',
  `for_role` varchar(20) DEFAULT 'admin',
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_is_read` (`is_read`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'7','Ravi Mehta','casual','2026-04-02','2026-04-04','Personal work',NULL,'leave','admin',1,'2026-04-01 12:14:50'),(2,'4','Priya Patel','Sick Leave','2026-04-01','2026-04-05','Fever',NULL,'leave','admin',1,'2026-04-04 06:24:45'),(3,'3','Rahul Sharma','Casual Leave','2026-04-08','2026-04-12','Out of Town',NULL,'leave','admin',1,'2026-04-08 06:38:48'),(4,'3','Rahul Sharma','Sick Leave','2026-04-11','2026-04-15','fever',NULL,'leave','admin',1,'2026-04-08 06:43:44'),(5,'8','Sailee Salunke','task_completion','2026-04-10','2026-04-10','Task completed by Sailee Salunke: creating Dashboard',NULL,'leave','admin',1,'2026-04-10 11:24:38'),(6,'6','Sneha  Singh','task_completion','2026-04-10','2026-04-10','Task completed by Sneha  Singh: Testing',NULL,'leave','admin',1,'2026-04-10 11:27:59'),(7,'8','Sailee Salunke','Unpaid Leave','2026-04-13','2026-04-14','xyz',NULL,'leave','admin',1,'2026-04-11 17:47:53'),(8,'6','Sneha Singh','Casual Leave','2026-04-13','2026-04-14','Personal work',NULL,'leave','admin',1,'2026-04-12 11:05:44'),(9,'3','Rahul Sharma','Privilege Leave','2026-05-04','2026-05-05','Family function',NULL,'leave','admin',1,'2026-04-30 04:03:41'),(10,'8','Sailee Salunke','Casual Leave','2026-05-04','2026-05-05','Family Gathering',NULL,'leave','admin',1,'2026-05-01 07:16:33'),(11,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Code review and documentation\' marked as \'in_progress\' by Sailee Salunke',NULL,'leave','admin',1,'2026-05-01 07:40:10'),(12,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Fixing bugs and improving performance\' marked as \'completed\' by Sailee Salunke',NULL,'leave','admin',1,'2026-05-01 07:40:36'),(13,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Code review and documentation\' marked as \'completed\' by Sailee Salunke',NULL,'leave','admin',1,'2026-05-01 07:40:55'),(14,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Code review and documentation\' marked as \'pending\' by Sailee Salunke',NULL,'leave','admin',1,'2026-05-01 07:41:02'),(15,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Developing new features for applications\' marked as \'completed\' by Sailee Salunke',NULL,'leave','admin',1,'2026-05-01 07:41:17'),(16,'6','Sneha Singh','Casual Leave','2026-05-22','2026-05-25','Family function',NULL,'leave','admin',1,'2026-05-22 08:38:02'),(17,'8','Sailee Salunke','Sick Leave','2026-05-31','2026-06-02','Fever ',NULL,'leave','admin',1,'2026-05-30 15:54:30'),(18,'8','Sailee Salunke','Sick Leave','2026-06-08','2026-06-09','fever',NULL,'leave','admin',1,'2026-06-06 07:46:40'),(19,'8','Sailee Salunke','Sick Leave','2026-06-08','2026-06-09','✅ Your Sick Leave request (2026-06-08 to 2026-06-09) has been Approved by Admin.','✅ Your Sick Leave request (2026-06-08 to 2026-06-09) has been Approved by Admin.','leave_status','employee',1,'2026-06-06 07:57:37'),(20,'8','Sailee Salunke','Privilege Leave','2026-06-09','2026-06-10','Out of town',NULL,'leave','admin',1,'2026-06-06 14:02:21'),(21,'8','Sailee Salunke','Privilege Leave','2026-06-09','2026-06-10','❌ Your Privilege Leave request (2026-06-09 to 2026-06-10) has been Rejected by Admin.','❌ Your Privilege Leave request (2026-06-09 to 2026-06-10) has been Rejected by Admin.','leave_status','employee',1,'2026-06-06 14:02:59'),(22,'9','Nita seth','Special Casual Leave','2026-06-11','2026-06-12','out of town',NULL,'leave','admin',1,'2026-06-09 08:51:11'),(23,'13','Asha Seth','Task Assigned','2026-06-11','2026-06-15','? New task assigned to you: Lifecycle Management. Target date: 2026-06-15.','? New task assigned to you: Lifecycle Management. Target date: 2026-06-15.','task','employee',1,'2026-06-11 08:10:17'),(24,'8','Sailee Salunke','Task Assigned','2026-06-24','2026-06-29','? New task assigned to you: Testing. Target date: 2026-06-29.','? New task assigned to you: Testing. Target date: 2026-06-29.','task','employee',1,'2026-06-24 11:50:39'),(25,'8','Sailee Salunke','Regularization','2026-04-24','2026-04-24','✅ Your attendance regularization request for 2026-04-24 has been Approved.','✅ Your attendance regularization request for 2026-04-24 has been Approved.','regularization_status','employee',1,'2026-07-10 05:17:09'),(26,'8','Sailee Salunke','Department Change','2026-07-10','2026-07-10','✅ Your Department Change request has been Approved.','✅ Your Department Change request has been Approved.','hr_request_status','employee',1,'2026-07-10 06:03:03'),(27,'8','Sailee Salunke','Designation Change','2026-07-10','2026-07-10','✅ Your Designation Change request has been Approved.','✅ Your Designation Change request has been Approved.','hr_request_status','employee',1,'2026-07-10 09:04:59'),(28,'8','Sailee Salunke','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: Dell Latitude 5440 Laptop (Laptop).','You\'ve been assigned: Dell Latitude 5440 Laptop (Laptop).','asset_status','employee',1,'2026-07-23 07:52:15'),(29,'6','Sneha  Singh','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: Dell Latitude 7450 (Laptop).','You\'ve been assigned: Dell Latitude 7450 (Laptop).','asset_status','employee',0,'2026-07-23 07:57:42'),(30,'10','Shlok Thakur','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: Microsoft Surface Laptop 7 (Laptop).','You\'ve been assigned: Microsoft Surface Laptop 7 (Laptop).','asset_status','employee',0,'2026-07-23 07:58:55'),(31,'14','Shanaya Khan','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: Apple MacBook Air 13-inch (M4) (Laptop).','You\'ve been assigned: Apple MacBook Air 13-inch (M4) (Laptop).','asset_status','employee',0,'2026-07-23 08:00:00'),(32,'7','Ravi Mehta','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: HP EliteBook 840 G10 (Laptop).','You\'ve been assigned: HP EliteBook 840 G10 (Laptop).','asset_status','employee',0,'2026-07-23 08:00:49'),(33,'3','Rahul sharma','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: iPhone 15  Apple (Phone).','You\'ve been assigned: iPhone 15  Apple (Phone).','asset_status','employee',0,'2026-07-23 08:02:16'),(34,'4','Priya Patel','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: HP 24\" Monitor  HP (Monitor).','You\'ve been assigned: HP 24\" Monitor  HP (Monitor).','asset_status','employee',0,'2026-07-23 08:03:57'),(35,'8','Sailee Salunke','WFH Request','2026-07-27','2026-07-27','Family Get Together','Sailee Salunke has requested Work From Home on 2026-07-27.','wfh_status','admin',1,'2026-07-23 08:53:16'),(36,'9','Nita Seth','Asset Assigned','2026-07-23','2026-07-23','You\'ve been assigned: Wireless Mouse (Mouse).','You\'ve been assigned: Wireless Mouse (Mouse).','asset_status','employee',0,'2026-07-23 09:11:52'),(37,'6','Sneha  Singh','WFH Request','2026-07-28','2026-07-28','Family Function','Sneha  Singh has requested Work From Home on 2026-07-28.','wfh_status','admin',1,'2026-07-23 09:13:49'),(38,'6','Sneha  Singh','WFH Request','2026-07-28','2026-07-28','✅ Your Work From Home request for 2026-07-28 has been Approved.','✅ Your Work From Home request for 2026-07-28 has been Approved.','wfh_status','employee',0,'2026-07-23 09:14:37'),(39,'9','Nita Seth','Special Casual Leave','2026-06-11','2026-06-12','✅ Your Special Casual Leave request (2026-06-11 to 2026-06-12) has been Approved by Admin.','✅ Your Special Casual Leave request (2026-06-11 to 2026-06-12) has been Approved by Admin.','leave_status','employee',0,'2026-08-01 05:23:20'),(40,'8','Sailee Salunke','WFH Request','2026-07-27','2026-07-27','✅ Your Work From Home request for 2026-07-27 has been Approved.','✅ Your Work From Home request for 2026-07-27 has been Approved.','wfh_status','employee',1,'2026-08-04 08:43:23');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `reset_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reset_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,1,'0de0fe76391aa052abd5a2ea12324f10f6c608061a1db5508247ec5cb6885b7b','2026-08-31 06:50:51',1,'2026-08-31 00:50:51'),(2,5,'339c34a1868a70a1478887c6e9e5139d6c7a02274bcaa6825bc5dd033eb8c50a','2026-08-31 06:53:08',1,'2026-08-31 00:53:08'),(3,5,'96b11f5532719ce60b04e3346915c97b5231be728383bc59392376554f8dc6de','2026-08-31 20:43:40',0,'2026-08-31 14:43:40');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `performance`
--

DROP TABLE IF EXISTS `performance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance` (
  `perf_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `skill_name` varchar(200) DEFAULT NULL,
  `description` text,
  `date_added` date DEFAULT NULL,
  PRIMARY KEY (`perf_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `performance`
--

LOCK TABLES `performance` WRITE;
/*!40000 ALTER TABLE `performance` DISABLE KEYS */;
INSERT INTO `performance` VALUES (1,8,'HTML, CSS, JavaScript','“Developed skills in HTML, CSS, and JavaScript with improved debugging and design abilities.”','2026-01-01'),(2,13,'communication skills','Exceptional communication, presentation, and negotiation skills','2026-06-11');
/*!40000 ALTER TABLE `performance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_assignments`
--

DROP TABLE IF EXISTS `project_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_assignments` (
  `assignment_id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `emp_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  UNIQUE KEY `project_emp` (`project_id`,`emp_id`),
  KEY `project_id` (`project_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=880 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_assignments`
--

LOCK TABLES `project_assignments` WRITE;
/*!40000 ALTER TABLE `project_assignments` DISABLE KEYS */;
INSERT INTO `project_assignments` VALUES (1,4,8,'2026-08-21 09:05:06');
/*!40000 ALTER TABLE `project_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `project_id` int NOT NULL AUTO_INCREMENT,
  `project_name` varchar(200) NOT NULL,
  `description` text,
  `dept_id` int DEFAULT NULL,
  `assigned_emp_id` int DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `target_date` date DEFAULT NULL,
  `status` enum('ongoing','completed','on_hold') DEFAULT 'ongoing',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`),
  KEY `dept_id` (`dept_id`),
  KEY `assigned_emp_id` (`assigned_emp_id`),
  CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`),
  CONSTRAINT `projects_ibfk_2` FOREIGN KEY (`assigned_emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (4,'EMS Project','',4,8,'2026-05-07','2026-05-12','ongoing','2026-05-07 03:15:00');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `regularization_requests`
--

DROP TABLE IF EXISTS `regularization_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regularization_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `att_date` date DEFAULT NULL,
  `requested_check_in` time DEFAULT NULL,
  `requested_check_out` time DEFAULT NULL,
  `requested_status` varchar(30) DEFAULT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `regularization_requests`
--

LOCK TABLES `regularization_requests` WRITE;
/*!40000 ALTER TABLE `regularization_requests` DISABLE KEYS */;
INSERT INTO `regularization_requests` VALUES (1,8,'2026-04-24','09:00:00','06:00:00','present','system issue','approved','2026-07-10 05:15:58',1),(2,9,'2026-06-21','09:00:00','18:00:00','present','Electricity cut-out','pending','2026-07-10 05:24:21',NULL);
/*!40000 ALTER TABLE `regularization_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reimbursement_requests`
--

DROP TABLE IF EXISTS `reimbursement_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reimbursement_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int NOT NULL,
  `category` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text,
  `receipt_filename` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reimbursement_requests`
--

LOCK TABLES `reimbursement_requests` WRITE;
/*!40000 ALTER TABLE `reimbursement_requests` DISABLE KEYS */;
/*!40000 ALTER TABLE `reimbursement_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `revenue`
--

DROP TABLE IF EXISTS `revenue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `revenue` (
  `revenue_id` int NOT NULL AUTO_INCREMENT,
  `month` varchar(20) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`revenue_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `revenue`
--

LOCK TABLES `revenue` WRITE;
/*!40000 ALTER TABLE `revenue` DISABLE KEYS */;
INSERT INTO `revenue` VALUES (1,'February',2026,500000.00);
/*!40000 ALTER TABLE `revenue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rules`
--

DROP TABLE IF EXISTS `rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rules` (
  `rule_id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(100) DEFAULT NULL,
  `title` varchar(200) DEFAULT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`rule_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rules`
--

LOCK TABLES `rules` WRITE;
/*!40000 ALTER TABLE `rules` DISABLE KEYS */;
INSERT INTO `rules` VALUES (1,'General','Office Timing Policy','All employees must report to office by 9:00 AM. Late arrivals beyond 9:15 AM will be marked as late. Three late marks in a month will result in half day deduction.','2026-05-24 12:10:53'),(3,'General','Company Vision & Mission','Vision: To create a business world full of prosperity, meaning, and delivering measurable results to clients, employees, and vendors. Mission: To consistently deliver transformative Digital Marketing and Web solutions, providing the best customer results possible with a WOW factor through our services.','2026-06-24 04:27:32'),(4,'General','Late Arrival Policy','Employees arriving late to the office are required to compensate for the lost working hours by extending their work time accordingly on the same day, in addition to standard late-mark tracking.','2026-06-24 04:28:05'),(5,'General','Work From Home (WFH) Policy','Work From Home requests will be approved based on the validity and genuineness of the reason provided, and are subject to management approval. Employees must mark their attendance status as WFH on days approved for remote work.','2026-06-24 04:28:41'),(6,'General','Notice Period Policy','Employees resigning from the organization are required to serve a notice period of 3 months, unless a shorter period is otherwise approved by management in writing.','2026-06-24 04:29:13'),(7,'General','Exit Clearance & Full and Final Settlement','Upon resignation or termination, employees must complete all clearance formalities including the return of company assets and handover of responsibilities. Final salary and dues will be processed only after successful completion of the exit clearance process.','2026-06-24 04:29:46'),(8,'Conduct','Professional Conduct & Workplace Ethics','All employees are expected to maintain professional conduct and uphold workplace ethics at all times, treating colleagues, clients, and vendors with respect and integrity.','2026-06-24 04:30:31'),(9,'Conduct','Equal Opportunity Policy','Aller Technologies is committed to providing equal employment opportunities to all employees and applicants without discrimination based on gender, religion, caste, disability, or any other protected status.','2026-06-24 04:31:11'),(10,'Conduct','Dress Code Policy','Employees are required to follow a formal or traditional dress code during the work week, in line with the company\'s professional image and workplace standards.','2026-06-24 04:31:50'),(11,'Conduct','Anti-Harassment Policy','The company maintains a zero-tolerance policy toward any form of harassment in the workplace. Any employee found violating this policy will be subject to strict disciplinary action.','2026-06-24 04:32:34'),(12,'Salary','Provident Fund (PF) Policy','PF benefits will be provided to all eligible employees in accordance with applicable statutory regulations. Contributions will be deducted and deposited as per government norms.','2026-06-24 04:33:35'),(13,'Salary','Employees\' State Insurance (ESI) Policy','ESI benefits will be applicable to all eligible employees as per government norms, providing medical and social security coverage.','2026-06-24 04:34:10'),(14,'Privacy','Email Usage Policy','Company email accounts must be used strictly for official business purposes. Sharing sensitive company information through unauthorized or personal channels is prohibited.','2026-06-24 04:34:53'),(15,'Privacy','Password Policy','Employees must use strong, confidential passwords for all company systems and accounts. Passwords must never be shared with any other person, including colleagues.','2026-06-24 04:35:27'),(16,'Privacy','Data Privacy Policy','Employee, client, and company data must be handled securely and confidentially at all times. Unauthorized access, sharing, or misuse of any data is strictly prohibited and may result in disciplinary action.','2026-06-24 04:36:04'),(17,'Privacy','NDA & Confidentiality Policy','Employees must maintain the confidentiality of all company information during and after their employment. Sharing proprietary, business, or client information without proper authorization is strictly prohibited.','2026-06-24 04:36:41'),(18,'General','Asset Allocation Policy','Company assets will be assigned to employees based on their job requirements. Employees are responsible for the proper use and safekeeping of all assigned assets.','2026-06-24 04:43:04'),(19,'General','Asset Return Policy','All company assets must be returned upon resignation, termination, or transfer. Asset clearance is mandatory as part of the exit process.','2026-06-24 04:44:07'),(20,'General','Lost or Damaged Assets Policy','Any loss, theft, or damage to company assets must be reported immediately. Employees may be held accountable for damages caused by negligence or misuse.\r\n','2026-06-24 04:44:38'),(21,'General','Asset Usage Policy','Company assets are intended strictly for official business purposes. Unauthorized use, transfer, or modification of company assets is prohibited.','2026-06-24 05:00:24');
/*!40000 ALTER TABLE `rules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary`
--

DROP TABLE IF EXISTS `salary`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary` (
  `salary_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `basic_pay` decimal(10,2) DEFAULT NULL,
  `allowances` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) DEFAULT NULL,
  `month` varchar(20) DEFAULT NULL,
  `year` int DEFAULT NULL,
  `lop_days` int DEFAULT '0',
  `lop_amount` decimal(10,2) DEFAULT '0.00',
  PRIMARY KEY (`salary_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `salary_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary`
--

LOCK TABLES `salary` WRITE;
/*!40000 ALTER TABLE `salary` DISABLE KEYS */;
INSERT INTO `salary` VALUES (8,8,30000.00,5000.00,2500.00,32500.00,'April',2026,0,0.00),(11,8,35000.00,7000.00,4000.00,38000.00,'May',2026,0,0.00),(12,8,30000.00,50000.00,2500.00,77500.00,'May',2026,0,0.00);
/*!40000 ALTER TABLE `salary` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts` (
  `shift_id` int NOT NULL AUTO_INCREMENT,
  `shift_name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_minutes` int NOT NULL DEFAULT '15',
  `half_day_after_minutes` int NOT NULL DEFAULT '180',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shift_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
INSERT INTO `shifts` VALUES (1,'General Shift','09:00:00','18:00:00',15,180,'2026-07-20 02:39:41'),(4,'Morning Shift','20:00:00','16:00:00',15,180,'2026-07-23 08:41:12'),(5,'Afternoon Shift','14:00:00','22:00:00',15,180,'2026-07-23 08:43:09'),(6,'Night Shift','22:00:00','06:00:00',15,180,'2026-07-23 08:46:10');
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `task_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int DEFAULT NULL,
  `task_name` varchar(200) DEFAULT NULL,
  `description` text,
  `target_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') DEFAULT NULL,
  `hours_worked` decimal(5,2) DEFAULT NULL,
  PRIMARY KEY (`task_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (7,8,'creating Dashboard','Responsive Dashboard with multiple pages','2026-04-15','completed',12.00),(10,8,'Code review and documentation','Maintain project documentation and update progress regularly.','2026-05-01','in_progress',4.00),(11,8,'Fixing bugs and improving performance','Fix reported bugs and ensure smooth application performance.','2026-05-07','completed',6.00),(12,8,'Developing new features for applications','Develop and implement new features based on project requirements.','2026-05-05','completed',7.00),(13,8,'Code review and documentation','Maintain project documentation and update progress regularly.','2026-05-02','completed',4.00),(14,8,'Code review and documentation','Maintain project documentation and update progress regularly.','2026-05-02','pending',4.00),(16,8,'Testing','Testing of Hr Queries','2026-06-29','in_progress',5.00);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','employee','super_admin') DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `failed_login_attempts` int DEFAULT '0',
  `lockout_until` datetime DEFAULT NULL,
  `must_change_password` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','ssysurendra@gmail.com','$2y$10$.XnRq.jppmJPhnC4C9hXS.zxShry2.5QVPK0SNx7xAd89wwQPX/m.','admin','profile_1_1775956446.jpg',0,NULL,0),(5,'Super Admin','skalpana1662@gmail.com','$2y$10$Vu0JLILsh5wXkVLEpsXIWeLmORpI87xgc1Cdw3PW0zd7h.9.xYpwS','super_admin','profile_5_1775956497.jpg',0,NULL,0),(11,'Sailee Salunke','saileesalunke4@gmail.com','$2y$10$orgPas7wTdnzyOaX6PpdnOGc0YooSXKeFmebscWIbRmzXCR8zIRhu','employee','profile_11_1775955652.jpg',0,NULL,0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wfh_requests`
--

DROP TABLE IF EXISTS `wfh_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wfh_requests` (
  `request_id` int NOT NULL AUTO_INCREMENT,
  `emp_id` int NOT NULL,
  `wfh_date` date NOT NULL,
  `reason` text,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` int DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wfh_requests`
--

LOCK TABLES `wfh_requests` WRITE;
/*!40000 ALTER TABLE `wfh_requests` DISABLE KEYS */;
INSERT INTO `wfh_requests` VALUES (1,8,'2026-07-27','Family Get Together','approved','2026-07-23 08:53:16',1),(2,6,'2026-07-28','Family Function','approved','2026-07-23 09:13:49',1);
/*!40000 ALTER TABLE `wfh_requests` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-02 11:46:51
