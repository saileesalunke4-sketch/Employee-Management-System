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

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '0d1ac67a-0965-11f1-9547-a8934aace692:1-472';

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
  `overtime_hours` decimal(5,2) DEFAULT '0.00',
  `is_sunday` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`attendance_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance`
--

LOCK TABLES `attendance` WRITE;
/*!40000 ALTER TABLE `attendance` DISABLE KEYS */;
INSERT INTO `attendance` VALUES (2,3,'09:00:00','05:03:00','2026-03-20','present',0.00,0),(3,4,'09:00:00','17:49:00','2026-03-23','present',0.00,0),(4,5,'09:00:00','02:00:00','2026-03-20','half_day',0.00,0),(5,6,'09:00:00','17:00:00','2026-03-20','work_from_home',0.00,0),(6,7,'09:00:00','17:00:00','2026-03-23','present',0.00,0),(7,3,'09:00:00','18:00:00','2026-04-08','present',0.00,0),(8,8,'09:00:00','18:00:00','2026-04-10','present',0.00,0),(9,8,'09:00:00','00:00:00','2026-04-10','half_day',0.00,0),(10,8,'09:00:00','17:40:00','2026-04-12','present',0.00,0),(11,8,'09:00:00','17:00:00','2026-04-11','present',0.00,0),(12,8,'09:00:00','18:00:00','2026-04-13','work_from_home',0.00,0),(13,7,'09:00:00','17:00:00','2026-04-12','work_from_home',0.00,0),(14,5,'21:00:00','17:00:00','2026-04-12','work_from_home',0.00,0),(15,6,'21:00:00','18:00:00','2026-04-12','present',0.00,0),(16,5,'21:00:00','21:00:00','2026-04-12','present',0.00,0),(17,4,'09:00:00','17:00:00','2026-04-20','present',0.00,0),(18,9,'09:00:00','18:00:00','2026-04-24','present',0.00,0),(19,8,'09:00:00','14:00:00','2026-04-24','half_day',0.00,0),(20,6,'09:09:00','17:02:00','2026-04-30','present',0.00,0),(21,5,'09:00:00','19:19:00','2026-05-01','present',1.32,0),(22,4,'21:21:00','19:00:00','2026-05-01','present',1.00,0);
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Development','Rahul Sharma','2026-05-01 11:39:30'),(4,'UI Design','Shreya Sathe','2026-05-07 03:14:09');
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
  PRIMARY KEY (`emp_id`),
  KEY `user_id` (`user_id`),
  KEY `dept_id` (`dept_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`dept_id`) REFERENCES `departments` (`dept_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (3,6,'Rahul','sharma','1234567890','Software Developer','A+','2000-12-12','Hindu','Mumbai',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),(4,7,'Priya','Patel','1234567895','Web Designer','A+','1999-03-31','Hindu','Mumbai',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(5,8,'Amit','Kumarr','5678234569','Backend Devloper','O+','2000-03-04','Hindu','Nashik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1),(6,9,'Sneha ','Singh','2184734592','Tester','B+','1998-12-19','Hindu','Pune',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(7,10,'Ravi','Mehta','2356128945','Project Manager','A+','2002-06-23','Hindu','Nepal','Jaipur',NULL,'General','xyz','Pune',NULL,NULL,NULL,NULL),(8,11,'Sailee','Salunke','2356128945','Web Designer','A+','2002-07-28','Hindu','Nagar','nagar',NULL,'General','xyz','Pune','8_pan_card_1777306446.png','8_aadhar_card_1777306446.png','8_marks_card_1777306446.png',NULL),(9,12,'Nita','Seth','3278487358','QA','B-','2002-09-23','Hindu','Delhi',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL),(10,13,'Shlok','Thakur','3287487502','Devop Engineer','A+','2000-11-16','Hindu','Nashik',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (2,'Republic Day','2026-01-26',NULL,'National'),(3,'Holi','2026-03-14',NULL,'Festival'),(4,'Gudi Padwa','2026-03-30',NULL,'State'),(5,'Good Friday','2026-04-03',NULL,'National'),(6,'Dr. Ambedkar Jayanti','2026-04-14',NULL,'National'),(7,'Maharashtra Day','2026-05-01',NULL,'State'),(8,'Independence Day','2026-08-15',NULL,'National'),(9,'Ganesh Chaturthi','2026-08-27',NULL,'Festival'),(10,'Gandhi Jayanti','2026-10-02',NULL,'National'),(11,'Dussehra','2026-10-02',NULL,'Festival'),(13,'Diwali Laxmi Puja','2026-10-21',NULL,'Festival'),(14,'Gurunanak Jayanti','2026-11-05',NULL,'National'),(15,'Christmas','2026-12-25',NULL,'National'),(16,'Eid','2026-03-21',NULL,'National'),(17,'Akshaya Tritiya ','2026-04-19',NULL,'Festival');
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES (1,'Sick Leave',10,'2026-04-04 05:19:22'),(2,'Casual Leave',8,'2026-04-04 05:19:22'),(3,'Privilege Leave',15,'2026-04-04 05:19:22'),(4,'Unpaid Leave',0,'2026-04-04 05:19:22'),(5,'Materninty leave',90,'2026-04-04 06:15:30');
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
  `status` enum('pending','approved','rejected') DEFAULT NULL,
  PRIMARY KEY (`leave_id`),
  KEY `emp_id` (`emp_id`),
  CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
INSERT INTO `leaves` VALUES (2,3,'sick','2026-03-10','2026-03-20','Fever','pending'),(3,5,'casual','2026-03-12','2026-03-19','Personal work','pending'),(4,7,'casual','2026-04-02','2026-04-04','Personal work','pending'),(5,4,'Sick Leave','2026-04-01','2026-04-05','Fever','approved'),(6,3,'Casual Leave','2026-04-08','2026-04-12','Out of Town','pending'),(7,3,'Sick Leave','2026-04-11','2026-04-15','fever','approved'),(8,8,'Unpaid Leave','2026-04-13','2026-04-14','xyz','pending'),(9,6,'Casual Leave','2026-04-13','2026-04-14','Personal work','pending'),(10,3,'Privilege Leave','2026-05-04','2026-05-05','Family function','approved'),(11,8,'Casual Leave','2026-05-04','2026-05-05','Family Gathering','approved');
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
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
  `is_read` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,'7','Ravi Mehta','casual','2026-04-02','2026-04-04','Personal work',1,'2026-04-01 12:14:50'),(2,'4','Priya Patel','Sick Leave','2026-04-01','2026-04-05','Fever',1,'2026-04-04 06:24:45'),(3,'3','Rahul Sharma','Casual Leave','2026-04-08','2026-04-12','Out of Town',1,'2026-04-08 06:38:48'),(4,'3','Rahul Sharma','Sick Leave','2026-04-11','2026-04-15','fever',1,'2026-04-08 06:43:44'),(5,'8','Sailee Salunke','task_completion','2026-04-10','2026-04-10','Task completed by Sailee Salunke: creating Dashboard',1,'2026-04-10 11:24:38'),(6,'6','Sneha  Singh','task_completion','2026-04-10','2026-04-10','Task completed by Sneha  Singh: Testing',1,'2026-04-10 11:27:59'),(7,'8','Sailee Salunke','Unpaid Leave','2026-04-13','2026-04-14','xyz',1,'2026-04-11 17:47:53'),(8,'6','Sneha Singh','Casual Leave','2026-04-13','2026-04-14','Personal work',1,'2026-04-12 11:05:44'),(9,'3','Rahul Sharma','Privilege Leave','2026-05-04','2026-05-05','Family function',0,'2026-04-30 04:03:41'),(10,'8','Sailee Salunke','Casual Leave','2026-05-04','2026-05-05','Family Gathering',0,'2026-05-01 07:16:33'),(11,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Code review and documentation\' marked as \'in_progress\' by Sailee Salunke',0,'2026-05-01 07:40:10'),(12,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Fixing bugs and improving performance\' marked as \'completed\' by Sailee Salunke',0,'2026-05-01 07:40:36'),(13,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Code review and documentation\' marked as \'completed\' by Sailee Salunke',0,'2026-05-01 07:40:55'),(14,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Code review and documentation\' marked as \'pending\' by Sailee Salunke',0,'2026-05-01 07:41:02'),(15,'8','Sailee Salunke','task_update','2026-05-01','2026-05-01','Task \'Developing new features for applications\' marked as \'completed\' by Sailee Salunke',0,'2026-05-01 07:41:17');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `performance`
--

LOCK TABLES `performance` WRITE;
/*!40000 ALTER TABLE `performance` DISABLE KEYS */;
INSERT INTO `performance` VALUES (1,8,'HTML, CSS, JavaScript','“Developed skills in HTML, CSS, and JavaScript with improved debugging and design abilities.”','2026-01-01');
/*!40000 ALTER TABLE `performance` ENABLE KEYS */;
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
INSERT INTO `projects` VALUES (2,'EMS Development','',1,5,'2026-04-15','2026-04-28','ongoing','2026-05-01 12:18:08'),(3,'EMS Project','',1,3,'2026-04-15','2026-04-29','ongoing','2026-05-01 12:21:23'),(4,'EMS Project','',4,8,'2026-05-07','2026-05-12','ongoing','2026-05-07 03:15:00');
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary`
--

LOCK TABLES `salary` WRITE;
/*!40000 ALTER TABLE `salary` DISABLE KEYS */;
INSERT INTO `salary` VALUES (2,3,25000.00,5000.00,3000.00,27000.00,'February',2026,0,0.00),(3,4,22000.00,4000.00,2500.00,23500.00,'February',2026,0,0.00),(4,5,28000.00,6000.00,3500.00,30500.00,'February',2026,0,0.00),(5,6,20000.00,3500.00,2000.00,21500.00,'February',2026,0,0.00),(6,7,35000.00,7000.00,4000.00,38000.00,'February',2026,0,0.00),(7,3,30000.00,5000.00,2000.00,30000.00,'April',2026,3,3000.00),(8,8,30000.00,5000.00,2500.00,32500.00,'April',2026,0,0.00),(9,9,22000.00,4000.00,2500.00,23500.00,'April',2026,0,0.00),(10,7,25000.00,5000.00,3000.00,27000.00,'April',2026,0,0.00);
/*!40000 ALTER TABLE `salary` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (2,3,'Login Module','Create login page','2026-03-31','completed',9.00),(3,4,'UI Design','Design dashboard UI','2026-04-05','in_progress',8.00),(4,5,'API Development','Build REST APIs','2026-04-05','pending',0.00),(5,6,'Testing','Test all modules','2026-04-10','completed',0.00),(6,7,'Project Planning','Plan next meeting','2026-03-22','completed',4.00),(7,8,'creating Dashboard','Responsive Dashboard with multiple pages','2026-04-15','completed',12.00),(8,9,'Database creation','create and Manage data','2026-04-24','completed',5.00),(9,9,'web Design','login page','2026-05-03','pending',0.00),(10,8,'Code review and documentation','Maintain project documentation and update progress regularly.','2026-05-01','in_progress',4.00),(11,8,'Fixing bugs and improving performance','Fix reported bugs and ensure smooth application performance.','2026-05-07','completed',6.00),(12,8,'Developing new features for applications','Develop and implement new features based on project requirements.','2026-05-05','completed',7.00),(13,8,'Code review and documentation','Maintain project documentation and update progress regularly.','2026-05-02','completed',4.00),(14,8,'Code review and documentation','Maintain project documentation and update progress regularly.','2026-05-02','pending',4.00);
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Admin User','admin@ems.com','$2y$10$0zfTk1gl5cAbVDarV6nesuyoSzRC63On/P/2M.3g4SnAiUooJ5lJK','admin','profile_1_1775956446.jpg'),(5,'Super Admin','superadmin@ems.com','$2y$10$B02yvdU1TDnlDERtjPSt.O1h4tvCqo6wb6npiXJUNAm.pLBva7vaK','super_admin','profile_5_1775956497.jpg'),(6,'Rahul Sharma','rahul@ems.com','$2y$10$OIkr0Ye3muXcdHCdpRtUjuWWvWeYFyArIWm9eF.wVsNod2Tcciewe','employee','profile_6_1775955728.jpg'),(7,'Priya Patel','priya@ems.com','$2y$10$kMP/mzyBQWjiUHiV8zBR/OSlSBK8CPyOPXO6hGKGrvYMnVqFPIUmm','employee','profile_7_1775955549.jpg'),(8,'Amit Kumar','amit@ems.com','$2y$10$cqhNQ2LWXGGUbZ.2jRt3MeUrO8qGlDmVA70k23iTXyRife8eiVtX2','employee','profile_8_1775955762.jpg'),(9,'Sneha Singh','sneha@ems.com','$2y$10$xlTcrXKS46Pz4Rij7DZmrOQG0R5J2s.ntkTPchFAJbyx8PK6gtz7a','employee','profile_9_1775955596.jpg'),(10,'Ravi Mehta','ravi@ems.com','$2y$10$bADPjTRhBZuP.qIWbCsy2.b2m7VfJWcSh5eODGZBsMv0s/1nbByVu','employee','profile_10_1775955953.jpg'),(11,'Sailee Salunke','saileesalunke4@gmail.com','$2y$10$ComV8fpfdqU.fskquwwYbe2Zyw5daT4o1JcJfoY6cLskzrkRYHO2C','employee','profile_11_1775955652.jpg'),(12,'Nita seth','nita@ems.com','$2y$10$Toir4P791bcM0u//SShTC.WLpCe6MZuuoi7.B9w380So9LizoCDf6','employee',NULL),(13,'Shlok Thakur','shlok@ems.com','$2y$10$BAVD3/8qQ8De60h5mKhd8uYZ538IG8tFwId/o6jextkbZsXoLcs..','employee',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
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

-- Dump completed on 2026-05-20 10:57:28
