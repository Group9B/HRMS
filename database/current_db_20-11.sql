-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 20, 2025 at 08:55 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hrms_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'User who performed the action',
  `action` varchar(150) NOT NULL COMMENT 'Action performed',
  `details` text DEFAULT NULL COMMENT 'Details about the action',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'IP address of the user',
  `user_agent` varchar(255) DEFAULT NULL COMMENT 'Browser or device info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','leave','half-day','holiday') DEFAULT 'present',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `date`, `check_in`, `check_out`, `status`, `remarks`, `created_at`) VALUES
(1, 1, '2025-09-14', '19:11:36', NULL, 'present', NULL, '2025-09-14 17:11:36'),
(2, 1, '2025-09-23', '15:43:26', NULL, 'present', NULL, '2025-09-23 13:43:26'),
(3, 4, '2025-09-29', '17:30:01', '17:30:02', 'present', NULL, '2025-09-29 15:30:01'),
(4, 5, '2025-09-01', NULL, NULL, 'present', NULL, '2025-09-30 09:26:01'),
(5, 4, '2025-09-30', '11:26:34', '11:29:58', 'present', NULL, '2025-09-30 09:26:34'),
(6, 3, '2025-09-02', NULL, NULL, 'leave', NULL, '2025-09-30 09:32:20'),
(7, 1, '2025-09-26', NULL, NULL, 'holiday', NULL, '2025-09-30 09:51:06'),
(8, 2, '2025-09-26', NULL, NULL, 'holiday', NULL, '2025-09-30 09:51:06'),
(9, 3, '2025-09-26', NULL, NULL, 'holiday', NULL, '2025-09-30 09:51:06'),
(10, 4, '2025-09-26', NULL, NULL, 'holiday', NULL, '2025-09-30 09:51:06'),
(11, 5, '2025-09-26', NULL, NULL, 'holiday', NULL, '2025-09-30 09:51:06'),
(12, 5, '2025-09-17', NULL, NULL, 'half-day', NULL, '2025-09-30 10:00:34'),
(13, 5, '2025-09-24', NULL, NULL, 'half-day', NULL, '2025-09-30 10:00:47'),
(14, 1, '2025-09-15', NULL, NULL, 'half-day', NULL, '2025-09-30 10:10:56'),
(15, 4, '2025-09-25', NULL, NULL, 'absent', NULL, '2025-09-30 11:19:12'),
(16, 4, '2025-09-04', NULL, NULL, 'present', NULL, '2025-09-30 13:04:13'),
(17, 1, '2025-09-11', NULL, NULL, 'present', NULL, '2025-09-30 13:18:17'),
(18, 4, '2025-09-30', NULL, NULL, 'absent', NULL, '2025-10-05 05:13:57'),
(19, 4, '2025-09-30', NULL, NULL, 'half-day', NULL, '2025-10-05 05:14:03'),
(20, 2, '2025-09-30', NULL, NULL, 'half-day', NULL, '2025-10-05 05:14:33'),
(21, 6, '2025-09-30', NULL, NULL, 'present', NULL, '2025-10-05 05:17:29'),
(22, 2, '2025-09-10', NULL, NULL, 'absent', NULL, '2025-10-05 05:19:03'),
(23, 1, '2025-09-10', NULL, NULL, 'half-day', NULL, '2025-10-05 05:19:14'),
(24, 2, '2025-09-21', NULL, NULL, 'half-day', NULL, '2025-10-05 05:20:50'),
(25, 2, '2025-09-15', NULL, NULL, 'present', NULL, '2025-10-05 05:20:53'),
(26, 2, '2025-09-17', NULL, NULL, 'present', NULL, '2025-10-05 05:20:58'),
(27, 6, '2025-09-02', NULL, NULL, 'present', NULL, '2025-10-05 05:21:02'),
(28, 6, '2025-09-03', NULL, NULL, 'present', NULL, '2025-10-05 05:21:06'),
(29, 6, '2025-09-04', NULL, NULL, 'present', NULL, '2025-10-05 05:21:09'),
(30, 5, '2025-10-10', NULL, NULL, 'present', NULL, '2025-10-05 05:24:32'),
(31, 5, '2025-11-12', NULL, NULL, 'present', NULL, '2025-11-20 07:19:03');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `status` enum('applied','shortlisted','interviewed','hired','rejected') DEFAULT 'applied',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `candidates`
--

INSERT INTO `candidates` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `dob`, `gender`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Samkit', 'Jain', 'can@mail.com', '08200700139', '1994-01-01', 'male', 'applied', '2025-09-21 12:28:41', '2025-09-21 12:28:41');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `email`, `phone`, `created_at`) VALUES
(1, 'Test Pvt. Ltd.', 'Test 1,Test 2 , India', 'test@mail.com', '1324567890', '2025-08-01 07:01:07'),
(2, 'Rathod Software', 'Jamnagar, Gujarat, India', 'rathod.software@gmail.com', '9876543210', '2025-09-23 13:00:08'),
(3, 'Hyperlink InfoSystem', 'Ahmedabad, Gujarat, India', 'hyper@gmail.com', '1234567890', '2025-09-23 13:01:29'),
(4, 'Space-O Technologies', 'Ahmedabad, Gujarat, India', 'SpaceO@gmail.com', '1239784650', '2025-09-23 13:02:07');

-- --------------------------------------------------------

--
-- Table structure for table `company_holiday_settings`
--

CREATE TABLE `company_holiday_settings` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `saturday_policy` enum('none','2nd_4th','1st_3rd','all') NOT NULL DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_holiday_settings`
--

INSERT INTO `company_holiday_settings` (`id`, `company_id`, `saturday_policy`) VALUES
(1, 1, 'all');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `company_id`, `name`, `description`) VALUES
(1, 1, 'social media', '--');

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `department_id`, `name`, `description`) VALUES
(1, 1, 'content creator', '--');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `doc_name` varchar(150) NOT NULL,
  `doc_type` varchar(50) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `doc_size` bigint(20) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `related_type` enum('employee','candidate','company','policy') NOT NULL,
  `related_id` int(11) NOT NULL,
  `version` int(11) DEFAULT 1,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `doc_name`, `doc_type`, `file_path`, `doc_size`, `mime_type`, `related_type`, `related_id`, `version`, `is_active`, `uploaded_at`) VALUES
(1, 'Resume/CV', 'resume', '/hrms/uploads/resumes/68cfef792a708-general_holidays_2025.pdf', 4509229, 'application/pdf', 'candidate', 1, 1, 1, '2025-09-21 12:28:41'),
(3, 'Company Test Leave Polciy Upload', 'policy', '/hrms/uploads/policies/68dabcdb7d6e7-ALLDONE_merged.pdf', 703003, 'application/pdf', 'policy', 1, 1, 1, '2025-09-29 17:07:39');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'Recipient user (if applicable)',
  `email_to` varchar(255) NOT NULL COMMENT 'Recipient email address',
  `email_from` varchar(255) DEFAULT NULL COMMENT 'Sender email address',
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `template_id` int(11) DEFAULT NULL COMMENT 'Reference to email_templates',
  `status` enum('pending','sent','failed') NOT NULL DEFAULT 'pending',
  `error_message` text DEFAULT NULL,
  `sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `email_templates`
--

CREATE TABLE `email_templates` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL COMMENT 'Template name (e.g. Welcome, Leave Approved)',
  `subject` varchar(255) NOT NULL COMMENT 'Email subject',
  `body` text NOT NULL COMMENT 'HTML or text body with placeholders',
  `description` text DEFAULT NULL COMMENT 'Purpose or usage notes',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `designation_id` int(11) DEFAULT NULL,
  `shift_id` int(11) DEFAULT NULL,
  `date_of_joining` date DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_code`, `first_name`, `last_name`, `dob`, `gender`, `contact`, `address`, `department_id`, `designation_id`, `shift_id`, `date_of_joining`, `status`, `created_at`) VALUES
(1, 9, NULL, 'samkit', 'jain', NULL, NULL, NULL, NULL, 1, 1, 1, '2025-09-07', 'active', '2025-09-14 13:14:01'),
(2, 11, 'MGR001', 'John', 'Manager', NULL, NULL, NULL, NULL, 1, 1, 1, '2025-01-01', 'active', '2025-09-14 14:39:27'),
(3, 12, NULL, 'Krish', 'Jain', NULL, NULL, NULL, NULL, 1, 1, 1, '2025-09-06', 'active', '2025-09-23 13:05:20'),
(4, 13, NULL, 'Dhiraj', 'Jagwani', NULL, NULL, NULL, NULL, 1, 1, 1, '2024-12-31', 'active', '2025-09-23 13:06:05'),
(5, 14, NULL, 'Ashav', 'Shah', NULL, NULL, NULL, NULL, 1, 1, 1, '2025-09-29', 'active', '2025-09-29 12:19:09'),
(6, 10, 'TPL-2025-001', 'HR ', 'Manager', NULL, NULL, NULL, NULL, 1, 1, 1, '2025-08-14', 'active', '2025-09-30 10:50:10');

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `submitted_by` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` enum('feedback','suggestion','complaint','appreciation') DEFAULT 'feedback',
  `status` enum('pending','reviewed','resolved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `global_holidays`
--

CREATE TABLE `global_holidays` (
  `id` int(11) NOT NULL,
  `holiday_name` varchar(255) NOT NULL,
  `holiday_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `global_holidays`
--

INSERT INTO `global_holidays` (`id`, `holiday_name`, `holiday_date`) VALUES
(1, 'New Year\'s Day', '2025-01-01'),
(2, 'Guru Govind Singh Jayanti', '2025-01-06'),
(3, 'Makar Sankranti', '2025-01-14'),
(6, 'Republic Day', '2025-01-26'),
(7, 'Vasant Panchami', '2025-02-02'),
(8, 'Guru Ravidas Jayanti', '2025-02-12'),
(9, 'Shivaji Jayanti', '2025-02-19'),
(10, 'Maharishi Dayanand Saraswati Jayanti', '2025-02-23'),
(11, 'Maha Shivaratri/Shivaratri', '2025-02-26'),
(12, 'Ramadan Start', '2025-03-02'),
(13, 'Holika Dahana', '2025-03-13'),
(14, 'Holi', '2025-03-14'),
(16, 'Jamat Ul-Vida', '2025-03-28'),
(17, 'Ugadi', '2025-03-30'),
(20, 'Ramzan Id', '2025-03-31'),
(21, 'Rama Navami', '2025-04-06'),
(22, 'Mahavir Jayanti', '2025-04-10'),
(23, 'Vaisakhi', '2025-04-13'),
(24, 'Ambedkar Jayanti', '2025-04-14'),
(26, 'Bahag Bihu/Vaisakhadi', '2025-04-15'),
(27, 'Good Friday', '2025-04-18'),
(28, 'Easter Day', '2025-04-20'),
(29, 'Birthday of Rabindranath', '2025-05-09'),
(30, 'Buddha Purnima/Vesak', '2025-05-12'),
(31, 'Bakrid', '2025-06-07'),
(32, 'Rath Yatra', '2025-06-27'),
(33, 'Muharram/Ashura', '2025-07-06'),
(34, 'Raksha Bandhan (Rakhi)', '2025-08-09'),
(35, 'Independence Day', '2025-08-15'),
(38, 'Janmashtami', '2025-08-16'),
(39, 'Ganesh Chaturthi/Vinayaka Chaturthi', '2025-08-27'),
(40, 'Milad un-Nabi', '2025-09-05'),
(42, 'First Day of Sharad Navratri', '2025-09-22'),
(43, 'First Day of Durga Puja Festivities', '2025-09-28'),
(44, 'Maha Saptami', '2025-09-29'),
(45, 'Maha Ashtami', '2025-09-30'),
(46, 'Maha Navami', '2025-10-01'),
(47, 'Mahatma Gandhi Jayanti', '2025-10-02'),
(49, 'Maharishi Valmiki Jayanti', '2025-10-07'),
(50, 'Karaka Chaturthi (Karva Chauth)', '2025-10-10'),
(51, 'Diwali/Deepavali', '2025-10-20'),
(53, 'Govardhan Puja', '2025-10-22'),
(54, 'Bhai Duj', '2025-10-23'),
(55, 'Chhat Puja (Pratihar Sashthi/Surya Sashthi)', '2025-10-28'),
(56, 'Guru Nanak Jayanti', '2025-11-05'),
(57, 'Guru Tegh Bahadur\'s Martyrdom Day', '2025-11-24'),
(58, 'Christmas Eve', '2025-12-24'),
(59, 'Christmas', '2025-12-25');

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `holiday_name` varchar(100) NOT NULL,
  `holiday_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `company_id`, `holiday_name`, `holiday_date`) VALUES
(2, 1, 'New Year\'s Day', '2025-01-01'),
(3, 1, 'Guru Govind Singh Jayanti', '2025-01-06'),
(4, 1, 'Makar Sankranti', '2025-01-14'),
(5, 1, 'Republic Day', '2025-01-26'),
(6, 1, 'Vasant Panchami', '2025-02-02'),
(7, 1, 'Guru Ravidas Jayanti', '2025-02-12'),
(8, 1, 'Shivaji Jayanti', '2025-02-19'),
(9, 1, 'Maharishi Dayanand Saraswati Jayanti', '2025-02-23'),
(10, 1, 'Maha Shivaratri/Shivaratri', '2025-02-26'),
(11, 1, 'Ramadan Start', '2025-03-02'),
(12, 1, 'Holika Dahana', '2025-03-13'),
(13, 1, 'Holi', '2025-03-14'),
(14, 1, 'Jamat Ul-Vida', '2025-03-28'),
(15, 1, 'Ugadi', '2025-03-30'),
(16, 1, 'Ramzan Id', '2025-03-31'),
(17, 1, 'Rama Navami', '2025-04-06'),
(18, 1, 'Mahavir Jayanti', '2025-04-10'),
(19, 1, 'Vaisakhi', '2025-04-13'),
(20, 1, 'Ambedkar Jayanti', '2025-04-14'),
(21, 1, 'Bahag Bihu/Vaisakhadi', '2025-04-15'),
(22, 1, 'Good Friday', '2025-04-18'),
(23, 1, 'Easter Day', '2025-04-20'),
(24, 1, 'Birthday of Rabindranath', '2025-05-09'),
(25, 1, 'Buddha Purnima/Vesak', '2025-05-12'),
(26, 1, 'Bakrid', '2025-06-07'),
(27, 1, 'Rath Yatra', '2025-06-27'),
(28, 1, 'Muharram/Ashura', '2025-07-06'),
(29, 1, 'Raksha Bandhan (Rakhi)', '2025-08-09'),
(30, 1, 'Independence Day', '2025-08-15'),
(31, 1, 'Janmashtami', '2025-08-16'),
(32, 1, 'Ganesh Chaturthi/Vinayaka Chaturthi', '2025-08-27'),
(33, 1, 'Milad un-Nabi', '2025-09-05'),
(34, 1, 'First Day of Sharad Navratri', '2025-09-22'),
(35, 1, 'First Day of Durga Puja Festivities', '2025-09-28'),
(36, 1, 'Maha Saptami', '2025-09-29'),
(37, 1, 'Maha Ashtami', '2025-09-30'),
(38, 1, 'Maha Navami', '2025-10-01'),
(39, 1, 'Mahatma Gandhi Jayanti', '2025-10-02'),
(40, 1, 'Maharishi Valmiki Jayanti', '2025-10-07'),
(41, 1, 'Karaka Chaturthi (Karva Chauth)', '2025-10-10'),
(42, 1, 'Diwali/Deepavali', '2025-10-20'),
(43, 1, 'Govardhan Puja', '2025-10-22'),
(44, 1, 'Bhai Duj', '2025-10-23'),
(45, 1, 'Chhat Puja (Pratihar Sashthi/Surya Sashthi)', '2025-10-28'),
(46, 1, 'Guru Nanak Jayanti', '2025-11-05'),
(47, 1, 'Guru Tegh Bahadur\'s Martyrdom Day', '2025-11-24'),
(48, 1, 'Christmas Eve', '2025-12-24'),
(49, 1, 'Christmas', '2025-12-25');

-- --------------------------------------------------------

--
-- Table structure for table `interviews`
--

CREATE TABLE `interviews` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `interviewer_id` int(11) NOT NULL,
  `interview_date` datetime NOT NULL,
  `mode` enum('online','offline') DEFAULT 'offline',
  `feedback` text DEFAULT NULL,
  `result` enum('pending','selected','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interviews`
--

INSERT INTO `interviews` (`id`, `candidate_id`, `job_id`, `interviewer_id`, `interview_date`, `mode`, `feedback`, `result`, `created_at`) VALUES
(1, 1, 1, 11, '2025-09-23 18:00:00', 'offline', NULL, 'pending', '2025-09-21 12:30:19'),
(2, 1, 1, 9, '2025-09-22 18:09:00', 'online', NULL, 'pending', '2025-09-21 12:39:27');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `employment_type` enum('full-time','part-time','internship','contract') DEFAULT 'full-time',
  `location` varchar(150) DEFAULT NULL,
  `openings` int(11) DEFAULT 1,
  `posted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('open','closed') DEFAULT 'open'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `company_id`, `department_id`, `title`, `description`, `employment_type`, `location`, `openings`, `posted_at`, `status`) VALUES
(1, 1, 1, 'Editor', 'Need a editor, who familiar with Davinci Resolve and AI Tools.', 'part-time', 'Naroda-GIDC, Ahmedabad', 2, '2025-09-21 11:58:40', 'open');

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','shortlisted','interviewed','offered','hired','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `candidate_id`, `job_id`, `applied_at`, `status`) VALUES
(1, 1, 1, '2025-09-21 12:28:41', 'shortlisted');

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled') DEFAULT 'pending',
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `applied_at`, `approved_by`) VALUES
(1, 1, 'Sick', '2025-09-16', '2025-09-17', '', 'approved', '2025-09-15 07:28:52', 11),
(2, 1, 'Sick', '2025-09-16', '2025-09-17', '', 'approved', '2025-09-15 07:34:05', 11),
(3, 3, 'Sick', '2025-08-26', '2025-09-27', 'High Fever', 'approved', '2025-09-23 13:09:57', 11),
(4, 4, 'Privileage Leave', '2025-10-03', '2025-10-07', 'Going for international trip.', 'approved', '2025-09-29 15:28:03', 10),
(5, 4, 'Privileage Leave', '2025-10-09', '2025-10-11', 'NO3.0\r\n0 \r\n.', 'rejected', '2025-09-29 16:17:36', 8);

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_policy_id` int(11) NOT NULL,
  `year` year(4) NOT NULL,
  `accrued_days` decimal(4,1) NOT NULL DEFAULT 0.0,
  `used_days` decimal(4,1) NOT NULL DEFAULT 0.0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_policies`
--

CREATE TABLE `leave_policies` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `leave_type` varchar(100) NOT NULL,
  `days_per_year` int(11) NOT NULL,
  `is_accruable` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Can be carried over/encashed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_policies`
--

INSERT INTO `leave_policies` (`id`, `company_id`, `leave_type`, `days_per_year`, `is_accruable`) VALUES
(1, 1, 'Privileage Leave', 5, 0),
(2, 1, 'Sick', 12, 0);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'Recipient user',
  `type` enum('system','reminder','feedback','file','payroll','leave','attendance','performance','custom') NOT NULL DEFAULT 'system',
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_id` int(11) DEFAULT NULL COMMENT 'ID of related entity (file, leave, etc.)',
  `related_type` varchar(50) DEFAULT NULL COMMENT 'Type of related entity',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `type`, `title`, `message`, `related_id`, `related_type`, `is_read`, `created_at`, `read_at`) VALUES
(1, 9, 'payroll', 'Payslip', 'Your payslip for 2025-09', 1, 'payslip', 0, '2025-09-16 16:00:01', NULL),
(2, 9, 'payroll', 'Payslip', 'Your payslip for 2025-09', 1, 'payslip', 0, '2025-09-16 16:00:23', NULL),
(3, 9, 'payroll', 'Payslip', 'Your payslip for 2025-09', 2, 'payslip', 0, '2025-09-16 16:01:54', NULL),
(4, 11, 'payroll', 'Payslip', 'Your payslip for 2025-09', 5, 'payslip', 0, '2025-09-16 16:09:28', NULL),
(5, 9, 'payroll', 'Payslip', 'Your payslip for 2025-09', 6, 'payslip', 0, '2025-09-16 16:10:00', NULL),
(6, 9, 'payroll', 'Payslip', 'Your payslip for 2025-09Your payslip for 2025-09', 7, 'payslip', 0, '2025-09-16 16:21:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month` year(4) NOT NULL,
  `period` varchar(7) NOT NULL,
  `basic` decimal(10,2) NOT NULL,
  `hra` decimal(10,2) DEFAULT NULL,
  `allowances` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `status` enum('pending','processed','paid') DEFAULT 'pending',
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `employee_id` int(11) NOT NULL,
  `period` varchar(7) NOT NULL COMMENT 'YYYY-MM',
  `currency` varchar(10) DEFAULT 'INR',
  `earnings_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`earnings_json`)),
  `deductions_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`deductions_json`)),
  `gross_salary` decimal(12,2) NOT NULL,
  `net_salary` decimal(12,2) NOT NULL,
  `template_id` int(11) DEFAULT NULL,
  `status` enum('generated','sent','cancelled') NOT NULL DEFAULT 'generated',
  `generated_by` int(11) DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `sent_at` timestamp NULL DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`id`, `company_id`, `employee_id`, `period`, `currency`, `earnings_json`, `deductions_json`, `gross_salary`, `net_salary`, `template_id`, `status`, `generated_by`, `generated_at`, `sent_at`, `pdf_path`) VALUES
(1, 1, 1, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":0.21}]', '[]', 0.21, 0.21, 1, 'sent', 8, '2025-09-16 15:59:53', '2025-09-16 16:00:23', NULL),
(2, 1, 1, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":100000}]', '[]', 100000.00, 100000.00, 1, 'sent', 8, '2025-09-16 16:00:11', '2025-09-16 16:01:54', NULL),
(3, 1, 1, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":100000}]', '[]', 100000.00, 100000.00, 1, 'generated', 8, '2025-09-16 16:00:12', NULL, NULL),
(4, 1, 1, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":100000}]', '[]', 100000.00, 100000.00, 1, 'generated', 8, '2025-09-16 16:00:12', NULL, NULL),
(5, 1, 2, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":200000}]', '[]', 200000.00, 200000.00, 1, 'sent', 8, '2025-09-16 16:09:16', '2025-09-16 16:09:28', NULL),
(6, 1, 1, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":200000}]', '[]', 200000.00, 200000.00, 1, 'sent', 8, '2025-09-16 16:09:47', '2025-09-16 16:10:00', NULL),
(7, 1, 1, '2025-09', 'INR', '[{\"name\":\"Gross\",\"amount\":200000000000}]', '[]', 9999999999.99, 9999999999.99, 4, 'sent', 8, '2025-09-16 16:21:10', '2025-09-16 16:21:20', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payslip_templates`
--

CREATE TABLE `payslip_templates` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL COMMENT 'NULL = global template',
  `name` varchar(150) NOT NULL,
  `subject` varchar(255) DEFAULT 'Your payslip for {{period}}',
  `body_html` mediumtext NOT NULL COMMENT 'HTML with placeholders like {{employee_name}}, {{net_salary}}',
  `placeholders` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Optional list/metadata of placeholders' CHECK (json_valid(`placeholders`)),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslip_templates`
--

INSERT INTO `payslip_templates` (`id`, `company_id`, `name`, `subject`, `body_html`, `placeholders`, `is_active`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, NULL, 'Default Payslip', 'Your payslip for {{period}}', '<div style=\"font-family:Arial,sans-serif; padding:16px\">\n  <h2>{{company_name}}</h2>\n  <h3>Payslip - {{period}}</h3>\n  <p><strong>Employee:</strong> {{employee_name}} ({{employee_code}})</p>\n  <p><strong>Department:</strong> {{department_name}} | <strong>Designation:</strong> {{designation_name}}</p>\n  <hr/>\n  <h4>Earnings</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    {{earnings_rows}}\n  </table>\n  <h4 style=\"margin-top:16px\">Deductions</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    {{deductions_rows}}\n  </table>\n  <hr/>\n  <p><strong>Gross:</strong> {{currency}} {{gross_salary}} &nbsp; | &nbsp; <strong>Net Pay:</strong> {{currency}} {{net_salary}}</p>\n  <p><small>Generated on {{generated_at}}</small></p>\n</div>', '[\"company_name\", \"period\", \"employee_name\", \"employee_code\", \"department_name\", \"designation_name\", \"earnings_rows\", \"deductions_rows\", \"gross_salary\", \"net_salary\", \"currency\", \"generated_at\"]', 1, NULL, '2025-09-16 15:31:56', NULL, '2025-09-16 15:31:56'),
(2, 1, 'new', 'Your payslip for {{period}}Your payslip for {{period}}', 'Your payslip for {{period}}', '[\"company_name\",\"period\",\"employee_name\",\"employee_code\",\"department_name\",\"designation_name\",\"earnings_rows\",\"deductions_rows\",\"gross_salary\",\"net_salary\",\"currency\",\"generated_at\",\"insurance\",\"pf\",\"shares\"]', 1, 8, '2025-09-16 16:20:46', NULL, '2025-09-16 16:20:46'),
(3, 1, 'new', 'Your payslip for {{period}}Your payslip for {{period}}', 'Your payslip for {{period}}', '[\"company_name\",\"period\",\"employee_name\",\"employee_code\",\"department_name\",\"designation_name\",\"earnings_rows\",\"deductions_rows\",\"gross_salary\",\"net_salary\",\"currency\",\"generated_at\",\"insurance\",\"pf\",\"shares\"]', 1, 8, '2025-09-16 16:20:47', NULL, '2025-09-16 16:20:47'),
(4, 1, 'new', 'Your payslip for {{period}}Your payslip for {{period}}', 'Your payslip for {{period}}', '[\"company_name\",\"period\",\"employee_name\",\"employee_code\",\"department_name\",\"designation_name\",\"earnings_rows\",\"deductions_rows\",\"gross_salary\",\"net_salary\",\"currency\",\"generated_at\",\"insurance\",\"pf\",\"shares\"]', 1, 8, '2025-09-16 16:20:47', NULL, '2025-09-16 16:20:47');

-- --------------------------------------------------------

--
-- Table structure for table `performance`
--

CREATE TABLE `performance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `evaluator_id` int(11) DEFAULT NULL,
  `period` varchar(7) NOT NULL,
  `score` int(11) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance`
--

INSERT INTO `performance` (`id`, `employee_id`, `evaluator_id`, `period`, `score`, `remarks`, `created_at`) VALUES
(1, 1, 11, '2025-09', 100, 'best employee ever', '2025-09-14 17:10:42');

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `policy_name` varchar(255) NOT NULL,
  `content` text DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`) VALUES
(1, 'Super Admin', 'System-wide administrator'),
(2, 'Company Admin', 'Company-level administrator'),
(3, 'HR Manager', 'Manages HR operations'),
(4, 'Employee', 'Regular employee'),
(5, 'Auditor', 'Read-only access for audits'),
(6, 'Manager', 'Department Manager'),
(7, 'candidate', 'This role is for the candidate who applies for job role in company');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `description` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `company_id`, `name`, `start_time`, `end_time`, `description`) VALUES
(1, 1, 'shift1', '12:00:00', '08:00:00', '--');

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

CREATE TABLE `support_tickets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `status` enum('open','in_progress','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL COMMENT 'Unique key for the setting',
  `setting_value` text DEFAULT NULL COMMENT 'Value of the setting',
  `description` text DEFAULT NULL COMMENT 'Description of the setting',
  `updated_by` int(11) DEFAULT NULL COMMENT 'User who last updated',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_by`, `updated_at`, `created_at`) VALUES
(1, 'site_name', 'StaffSync', 'Name of Our Application', 1, '2025-08-15 00:04:43', '2025-08-15 00:04:43'),
(2, 'company_email', 'groupno9.it.@gmail.com', 'The default email for system notifications.', 1, '2025-08-15 00:04:43', '2025-08-15 00:04:43'),
(5, 'records_per_page', '11', 'Default number of items to show in tables.', 1, '2025-08-15 00:06:37', '2025-08-15 00:06:28'),
(6, 'maintenance_mode', '0', 'Temporarily disable access for non-admin users.', 1, '2025-09-29 11:28:45', '2025-08-15 00:19:12'),
(7, 'Upload Size Limit', '5242880', 'Maximum File Upload Size.', NULL, '2025-08-15 00:21:14', '2025-08-15 00:21:14');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `employee_id`, `title`, `description`, `assigned_by`, `due_date`, `status`, `created_at`) VALUES
(1, 1, 'create ad', '--', 11, '2025-09-15', 'cancelled', '2025-09-14 17:01:13'),
(2, 1, 'Create a reel for timepass', 'okie dokie', 11, '2025-09-15', 'pending', '2025-09-15 03:19:09'),
(3, 1, 'create 10 reel within 1 days', 'create 10 reel wihtihn 1 day or you have to pay penalty of 1000 RS per minute delay', 11, '2025-09-16', 'pending', '2025-09-15 07:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `company_id`, `name`, `description`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 1, 'social media', '--', 11, '2025-09-14 17:09:36', NULL, '2025-09-14 17:09:36');

-- --------------------------------------------------------

--
-- Table structure for table `team_members`
--

CREATE TABLE `team_members` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `role_in_team` varchar(100) DEFAULT NULL,
  `assigned_by` int(11) DEFAULT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `team_members`
--

INSERT INTO `team_members` (`id`, `team_id`, `employee_id`, `role_in_team`, `assigned_by`, `assigned_at`) VALUES
(1, 1, 1, NULL, 11, '2025-09-14 17:09:48');

-- --------------------------------------------------------

--
-- Table structure for table `todo_list`
--

CREATE TABLE `todo_list` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `task` text NOT NULL,
  `is_completed` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `todo_list`
--

INSERT INTO `todo_list` (`id`, `user_id`, `task`, `is_completed`, `created_at`) VALUES
(1, 9, 'Create Frontend of Client Application', 0, '2025-09-23 13:43:05'),
(2, 9, 'Syncronize API Data Rendering', 0, '2025-09-23 13:43:24'),
(3, 9, 'Create New Layout Dashboard for Staff Sync Admin User', 0, '2025-09-23 13:43:55');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `role_id`, `username`, `email`, `password`, `status`, `created_at`) VALUES
(1, NULL, 1, 'admin', 'super_admin@mail.com', '$2y$10$4oXGSu5Ip7f2oJFXksjqA.927pO76waLG1YCGuyiQNj6QMoqrJW/W', 'active', '2025-09-06 06:34:45'),
(8, 1, 2, 'c_admin', 'company_admin@mail.com', '$2y$10$pkSBG/PAMUf6fVcAkhchtusI6AjO3x7cIum2xrf8.zbnrxaYFVSbq', 'active', '2025-09-06 07:01:56'),
(9, 1, 4, 'notsanki', 'sanki@mail.com', '$2y$10$prTss54rC5oBGJzTznCyuOlCyaKSSePhQUnycXmTpNUyCWy/kgSga', 'active', '2025-09-14 13:13:37'),
(10, 1, 3, 'hr_manager', 'hr_manager@mail.com', '$2y$10$HHrZ/qGLppUqmVYzQkqrZuOW.EwPPaDypJVRsN4VOwOtUiYNJrDlK', 'active', '2025-09-14 13:15:29'),
(11, 1, 6, 'manager', 'c_manager@mail.com', '$2y$10$NpYBaT4R4WpJ9CSxL.H7XuCRCuzj.B.8it1R98Jd66esRPTvPbf6K', 'active', '2025-09-14 13:27:32'),
(12, 1, 6, 'Krish_Jain', 'krish@mail.com', '$2y$10$adsMpyMsciPXATSq2ux24OUCuLvSNZoktbtLGI4jC5C7/5J69n4ze', 'active', '2025-09-23 13:04:58'),
(13, 1, 4, 'Dhiraj_Jagwani', 'dhiraj@mail.com', '$2y$10$rCIbL2obgGdJBNmIMrBXOuZAsX6SBg3WN3fuUfdYO/JSukAvQNroq', 'active', '2025-09-23 13:05:41'),
(14, 1, 3, 'ashav_shah', 'ashav@mail.com', '$2y$10$u55X5gg2O./rJvkeXd7qXO7NxD6ZLzlzngtxo88r77BBGhyTRyUuG', 'active', '2025-09-29 12:13:38');

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `preference_key` varchar(100) NOT NULL,
  `preference_value` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_candidates_user` (`user_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_holiday_settings`
--
ALTER TABLE `company_holiday_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `company_id` (`company_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_code` (`employee_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `designation_id` (`designation_id`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `submitted_by` (`submitted_by`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_submitted_by` (`submitted_by`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `global_holidays`
--
ALTER TABLE `global_holidays`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `holiday_date` (`holiday_date`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `interviews`
--
ALTER TABLE `interviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_interviews_candidate` (`candidate_id`),
  ADD KEY `fk_interviews_job` (`job_id`),
  ADD KEY `fk_interviews_interviewer` (`interviewer_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jobs_company` (`company_id`),
  ADD KEY `fk_jobs_department` (`department_id`);

--
-- Indexes for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_job_applications_candidate` (`candidate_id`),
  ADD KEY `fk_job_applications_job` (`job_id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_employee_policy_year` (`employee_id`,`leave_policy_id`,`year`),
  ADD KEY `leave_policy_id` (`leave_policy_id`);

--
-- Indexes for table `leave_policies`
--
ALTER TABLE `leave_policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_read` (`is_read`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_period` (`period`),
  ADD KEY `idx_template_id` (`template_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `payslips_ibfk_generated_by` (`generated_by`);

--
-- Indexes for table `payslip_templates`
--
ALTER TABLE `payslip_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_is_active` (`is_active`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `performance`
--
ALTER TABLE `performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `evaluator_id` (`evaluator_id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `updated_by` (`updated_by`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_company_id` (`company_id`),
  ADD KEY `idx_created_by` (`created_by`),
  ADD KEY `idx_updated_by` (`updated_by`);

--
-- Indexes for table `team_members`
--
ALTER TABLE `team_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_team_employee` (`team_id`,`employee_id`),
  ADD KEY `idx_team_id` (`team_id`),
  ADD KEY `idx_employee_id` (`employee_id`),
  ADD KEY `idx_assigned_by` (`assigned_by`);

--
-- Indexes for table `todo_list`
--
ALTER TABLE `todo_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_preference` (`user_id`,`preference_key`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `company_holiday_settings`
--
ALTER TABLE `company_holiday_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `global_holidays`
--
ALTER TABLE `global_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_policies`
--
ALTER TABLE `leave_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `payslip_templates`
--
ALTER TABLE `payslip_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `performance`
--
ALTER TABLE `performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `todo_list`
--
ALTER TABLE `todo_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `candidates`
--
ALTER TABLE `candidates`
  ADD CONSTRAINT `fk_candidates_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `company_holiday_settings`
--
ALTER TABLE `company_holiday_settings`
  ADD CONSTRAINT `company_holiday_settings_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designations_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `email_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `email_logs_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `email_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `email_templates`
--
ALTER TABLE `email_templates`
  ADD CONSTRAINT `email_templates_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `email_templates_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `employees_ibfk_4` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `feedback_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `feedback_ibfk_2` FOREIGN KEY (`submitted_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `holidays`
--
ALTER TABLE `holidays`
  ADD CONSTRAINT `holidays_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `interviews`
--
ALTER TABLE `interviews`
  ADD CONSTRAINT `fk_interviews_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_interviews_interviewer` FOREIGN KEY (`interviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_interviews_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jobs`
--
ALTER TABLE `jobs`
  ADD CONSTRAINT `fk_jobs_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jobs_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `job_applications`
--
ALTER TABLE `job_applications`
  ADD CONSTRAINT `fk_job_applications_candidate` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_job_applications_job` FOREIGN KEY (`job_id`) REFERENCES `jobs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leaves_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `leave_balances_ibfk_2` FOREIGN KEY (`leave_policy_id`) REFERENCES `leave_policies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `leave_policies`
--
ALTER TABLE `leave_policies`
  ADD CONSTRAINT `leave_policies_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_ibfk_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payslips_ibfk_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payslips_ibfk_generated_by` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payslips_ibfk_template` FOREIGN KEY (`template_id`) REFERENCES `payslip_templates` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payslip_templates`
--
ALTER TABLE `payslip_templates`
  ADD CONSTRAINT `payslip_templates_ibfk_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payslip_templates_ibfk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `payslip_templates_ibfk_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `performance`
--
ALTER TABLE `performance`
  ADD CONSTRAINT `performance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `performance_ibfk_2` FOREIGN KEY (`evaluator_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `policies`
--
ALTER TABLE `policies`
  ADD CONSTRAINT `policies_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `shifts`
--
ALTER TABLE `shifts`
  ADD CONSTRAINT `shifts_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD CONSTRAINT `system_settings_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `teams_ibfk_3` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `team_members`
--
ALTER TABLE `team_members`
  ADD CONSTRAINT `team_members_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_members_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `todo_list`
--
ALTER TABLE `todo_list`
  ADD CONSTRAINT `todo_list_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
