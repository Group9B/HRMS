-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 04, 2026 at 05:11 PM
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
(1, 3, '2025-12-01', NULL, NULL, 'present', NULL, '2025-12-27 17:45:40'),
(2, 3, '2025-12-02', NULL, NULL, 'present', NULL, '2025-12-27 17:45:45'),
(3, 3, '2025-12-03', NULL, NULL, 'present', NULL, '2025-12-27 17:45:47'),
(4, 3, '2025-12-04', NULL, NULL, 'present', NULL, '2025-12-27 17:45:51'),
(5, 3, '2025-12-05', NULL, NULL, 'present', NULL, '2025-12-27 17:45:54'),
(6, 3, '2025-12-08', NULL, NULL, 'present', NULL, '2025-12-27 17:45:57'),
(7, 3, '2025-12-09', NULL, NULL, 'present', NULL, '2025-12-27 17:46:00'),
(8, 3, '2025-12-12', NULL, NULL, 'present', NULL, '2025-12-27 17:46:03'),
(9, 3, '2025-12-13', NULL, NULL, 'present', NULL, '2025-12-27 17:46:07'),
(10, 3, '2025-12-19', NULL, NULL, 'present', NULL, '2025-12-27 17:46:15'),
(11, 3, '2025-12-10', NULL, NULL, 'present', NULL, '2025-12-27 17:46:19'),
(12, 3, '2025-12-17', NULL, NULL, 'absent', NULL, '2025-12-27 17:46:22'),
(13, 3, '2025-12-18', NULL, NULL, 'present', NULL, '2025-12-27 17:46:27'),
(14, 3, '2025-12-11', NULL, NULL, 'present', NULL, '2025-12-27 17:46:30'),
(15, 3, '2025-12-27', NULL, NULL, 'absent', NULL, '2025-12-27 17:46:34'),
(16, 3, '2025-12-26', NULL, NULL, 'present', NULL, '2025-12-27 17:46:36'),
(17, 3, '2025-12-23', NULL, NULL, 'present', NULL, '2025-12-27 17:46:39'),
(18, 3, '2025-12-16', NULL, NULL, 'present', NULL, '2025-12-27 17:46:42'),
(19, 3, '2025-12-15', NULL, NULL, 'present', NULL, '2025-12-27 17:46:44'),
(20, 3, '2025-12-22', NULL, NULL, 'present', NULL, '2025-12-27 17:46:48'),
(21, 6, '2025-12-01', NULL, NULL, 'present', NULL, '2025-12-27 17:46:55'),
(22, 6, '2025-12-02', NULL, NULL, 'present', NULL, '2025-12-27 17:46:57'),
(23, 6, '2025-12-03', NULL, NULL, 'present', NULL, '2025-12-27 17:47:00'),
(24, 6, '2025-12-04', NULL, NULL, 'present', NULL, '2025-12-27 17:47:02'),
(25, 6, '2025-12-05', NULL, NULL, 'present', NULL, '2025-12-27 17:47:05'),
(26, 6, '2025-12-11', NULL, NULL, 'absent', NULL, '2025-12-27 17:47:08'),
(27, 6, '2025-12-12', NULL, NULL, 'present', NULL, '2025-12-27 17:47:11'),
(28, 6, '2025-12-13', NULL, NULL, 'present', NULL, '2025-12-27 17:47:14'),
(29, 6, '2025-12-22', NULL, NULL, 'absent', NULL, '2025-12-27 17:47:17'),
(31, 6, '2025-12-10', NULL, NULL, 'present', NULL, '2025-12-27 17:47:21'),
(32, 6, '2025-12-09', NULL, NULL, 'present', NULL, '2025-12-27 17:47:27'),
(33, 6, '2025-12-08', NULL, NULL, 'present', NULL, '2025-12-27 17:47:30'),
(34, 6, '2025-12-15', NULL, NULL, 'present', NULL, '2025-12-27 17:47:38'),
(35, 6, '2025-12-16', NULL, NULL, 'absent', NULL, '2025-12-27 17:47:44'),
(36, 6, '2025-12-17', NULL, NULL, 'present', NULL, '2025-12-27 17:47:47'),
(37, 6, '2025-12-18', NULL, NULL, 'leave', NULL, '2025-12-27 17:47:52'),
(38, 6, '2025-12-19', NULL, NULL, 'leave', NULL, '2025-12-27 17:47:57'),
(39, 6, '2025-12-23', NULL, NULL, 'present', NULL, '2025-12-27 17:47:59'),
(40, 6, '2025-12-26', NULL, NULL, 'leave', NULL, '2025-12-27 17:48:02'),
(41, 6, '2025-12-27', NULL, NULL, 'absent', NULL, '2025-12-27 17:48:05'),
(42, 7, '2025-12-01', NULL, NULL, 'present', NULL, '2025-12-27 17:48:08'),
(43, 7, '2025-12-02', NULL, NULL, 'present', NULL, '2025-12-27 17:48:10'),
(44, 7, '2025-12-03', NULL, NULL, 'present', NULL, '2025-12-27 17:48:12'),
(45, 7, '2025-12-04', NULL, NULL, 'present', NULL, '2025-12-27 17:48:15'),
(46, 7, '2025-12-05', NULL, NULL, 'present', NULL, '2025-12-27 17:48:17'),
(47, 7, '2025-12-08', NULL, NULL, 'half-day', NULL, '2025-12-27 17:48:19'),
(48, 7, '2025-12-09', NULL, NULL, 'absent', NULL, '2025-12-27 17:48:22'),
(49, 7, '2025-12-10', NULL, NULL, 'present', NULL, '2025-12-27 17:48:25'),
(50, 7, '2025-12-18', NULL, NULL, 'present', NULL, '2025-12-27 17:48:28'),
(51, 7, '2025-12-19', NULL, NULL, 'half-day', NULL, '2025-12-27 17:48:30'),
(52, 7, '2025-12-12', NULL, NULL, 'present', NULL, '2025-12-27 17:48:33'),
(53, 7, '2025-12-13', NULL, NULL, 'present', NULL, '2025-12-27 17:48:36'),
(54, 7, '2025-12-11', NULL, NULL, 'present', NULL, '2025-12-27 17:48:38'),
(55, 7, '2025-12-17', NULL, NULL, 'absent', NULL, '2025-12-27 17:48:40'),
(56, 7, '2025-12-23', NULL, NULL, 'present', NULL, '2025-12-27 17:48:43'),
(57, 7, '2025-12-15', NULL, NULL, 'present', NULL, '2025-12-27 17:48:46'),
(58, 7, '2025-12-16', NULL, NULL, 'present', NULL, '2025-12-27 17:48:49'),
(59, 7, '2025-12-22', NULL, NULL, 'leave', NULL, '2025-12-27 17:48:52'),
(61, 7, '2025-12-27', NULL, NULL, 'leave', NULL, '2025-12-27 17:48:58'),
(62, 7, '2025-12-26', NULL, NULL, 'present', NULL, '2025-12-27 17:49:02'),
(63, 4, '2025-12-01', NULL, NULL, 'absent', NULL, '2025-12-27 17:49:15'),
(64, 4, '2025-12-02', NULL, NULL, 'present', NULL, '2025-12-27 17:49:18'),
(65, 4, '2025-12-03', NULL, NULL, 'present', NULL, '2025-12-27 17:49:20'),
(66, 4, '2025-12-04', NULL, NULL, 'present', NULL, '2025-12-27 17:49:23'),
(67, 4, '2025-12-05', NULL, NULL, 'present', NULL, '2025-12-27 17:49:25'),
(68, 4, '2025-12-08', NULL, NULL, 'present', NULL, '2025-12-27 17:49:28'),
(69, 4, '2025-12-09', NULL, NULL, 'present', NULL, '2025-12-27 17:49:31'),
(70, 4, '2025-12-10', NULL, NULL, 'present', NULL, '2025-12-27 17:49:34'),
(71, 4, '2025-12-11', NULL, NULL, 'present', NULL, '2025-12-27 17:49:39'),
(72, 4, '2025-12-12', NULL, NULL, 'absent', NULL, '2025-12-27 17:49:42'),
(73, 4, '2025-12-13', NULL, NULL, 'half-day', NULL, '2025-12-27 17:49:47'),
(74, 4, '2025-12-16', NULL, NULL, 'present', NULL, '2025-12-27 17:49:49'),
(75, 4, '2025-12-23', NULL, NULL, 'present', NULL, '2025-12-27 17:49:52'),
(76, 4, '2025-12-15', NULL, NULL, 'present', NULL, '2025-12-27 17:49:56'),
(77, 4, '2025-12-26', NULL, NULL, 'absent', NULL, '2025-12-27 17:50:00'),
(78, 4, '2025-12-19', NULL, NULL, 'present', NULL, '2025-12-27 17:50:03'),
(79, 4, '2025-12-22', NULL, NULL, 'present', NULL, '2025-12-27 17:50:05'),
(80, 4, '2025-12-17', NULL, NULL, 'present', NULL, '2025-12-27 17:50:08'),
(81, 4, '2025-12-27', NULL, NULL, 'present', NULL, '2025-12-27 17:50:11'),
(82, 4, '2025-12-18', NULL, NULL, 'absent', NULL, '2025-12-27 17:50:14'),
(83, 5, '2025-12-04', NULL, NULL, 'present', NULL, '2025-12-27 17:51:04'),
(84, 1, '2025-12-01', NULL, NULL, 'present', NULL, '2025-12-28 06:48:53'),
(85, 1, '2025-12-02', NULL, NULL, 'present', NULL, '2025-12-28 06:48:55'),
(86, 1, '2025-12-03', NULL, NULL, 'present', NULL, '2025-12-28 06:48:58'),
(87, 1, '2025-12-04', NULL, NULL, 'present', NULL, '2025-12-28 06:49:01'),
(88, 1, '2025-12-05', NULL, NULL, 'present', NULL, '2025-12-28 06:49:27'),
(89, 1, '2025-12-08', NULL, NULL, 'present', NULL, '2025-12-28 06:49:33'),
(90, 1, '2025-12-10', NULL, NULL, 'present', NULL, '2025-12-28 06:49:41'),
(91, 1, '2025-12-11', NULL, NULL, 'present', NULL, '2025-12-28 06:49:43'),
(92, 1, '2025-12-09', NULL, NULL, 'present', NULL, '2025-12-28 06:49:45'),
(93, 1, '2025-12-12', NULL, NULL, 'absent', NULL, '2025-12-28 06:49:47'),
(94, 1, '2025-12-13', NULL, NULL, 'present', NULL, '2025-12-28 06:49:50'),
(95, 1, '2025-12-15', NULL, NULL, 'absent', NULL, '2025-12-28 06:49:52'),
(96, 1, '2025-12-16', NULL, NULL, 'present', NULL, '2025-12-28 06:49:54'),
(97, 1, '2025-12-17', NULL, NULL, 'absent', NULL, '2025-12-28 06:49:56'),
(98, 1, '2025-12-18', NULL, NULL, 'half-day', NULL, '2025-12-28 06:49:58'),
(99, 1, '2025-12-19', NULL, NULL, 'present', NULL, '2025-12-28 06:50:01'),
(100, 1, '2025-12-22', NULL, NULL, 'absent', NULL, '2025-12-28 06:50:03'),
(101, 1, '2025-12-23', NULL, NULL, 'present', NULL, '2025-12-28 06:50:05'),
(102, 1, '2025-12-26', NULL, NULL, 'present', NULL, '2025-12-28 06:50:08'),
(117, 1, '2025-12-28', '21:30:01', NULL, 'present', NULL, '2025-12-28 16:00:01'),
(118, 3, '2025-12-29', '18:13:21', '18:40:42', 'half-day', NULL, '2025-12-29 12:43:21'),
(119, 1, '2025-12-29', '20:57:12', '20:57:17', 'half-day', NULL, '2025-12-29 15:27:12');

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
(1, 'Rathod Software', 'Jamnagar, Gujarat', 'Rsjamnagar@mail.com', '+91 9745681234', '2025-04-10 06:09:30');

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
(1, 1, '1st_3rd');

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
(1, 1, 'Information Technology', 'IT Deparment Managing the Infrastructure of Network'),
(2, 1, 'Software Development - Engineering', 'Development Dept.'),
(3, 1, 'Quality Assurance', 'Performance & Security Testing'),
(4, 1, 'Product Management', 'Requirement Analysis & Roadmap Planning'),
(5, 1, 'UI - UX Design', 'Wireframes Prototypes'),
(6, 1, 'Human Resource', 'Dept. for managing employees');

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
(1, 1, 'IT Manager', 'Manager Of IT Dept.'),
(2, 1, 'System Administrator', 'Adminitrator Of internal systems and networks'),
(3, 1, 'Infrastructure Engineer', 'Manager of whole cyber Infrastructure'),
(4, 4, 'Product Manager', 'Manager of all information about the product'),
(5, 4, 'Associate Product Manager', 'Assistant of Product Manager'),
(6, 4, 'Requirement Analyst', 'Requirement Analyser of the Product'),
(7, 3, 'Quality Assurance', 'Manager of QA Dept.'),
(8, 3, 'QA Engineer', 'QA Engineer testing and Assuring the quality'),
(9, 2, 'Senior Software Engineer', 'Experienced Developer'),
(10, 2, 'Junior Software Engineer', 'Newbie in this dept.'),
(11, 6, 'Senior Human Resource', 'Experienced HR'),
(12, 6, 'Human Resource Associate', 'Assistant of the HR.');

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

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `user_id`, `email_to`, `email_from`, `subject`, `body`, `template_id`, `status`, `error_message`, `sent_at`, `created_at`) VALUES
(1, 30, 'devesh@mail.com', 'groupno9.it.@gmail.com', 'Your payslip for 2025-11', '<div style=\"font-family:Arial,sans-serif; padding:16px\">\n  <h2>Rathod Software</h2>\n  <h3>Payslip - 2025-11</h3>\n  <p><strong>Employee:</strong> Devesh Shah (RS-2025-001)</p>\n  <p><strong>Department:</strong> Human Resource | <strong>Designation:</strong> Senior Human Resource</p>\n  <hr/>\n  <h4>Earnings</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    <tr><td>Gross</td><td align=\"right\">20,000.00</td></tr>\n  </table>\n  <h4 style=\"margin-top:16px\">Deductions</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    <tr><td>PF</td><td align=\"right\">1,000.00</td></tr>\n  </table>\n  <hr/>\n  <p><strong>Gross:</strong> INR 20,000.00 &nbsp; | &nbsp; <strong>Net Pay:</strong> INR 19,000.00</p>\n  <p><small>Generated on 2026-01-02 21:54:34</small></p>\n</div>', NULL, 'sent', NULL, '2026-01-02 16:28:34', '2026-01-02 16:28:34'),
(2, 31, 'arjav@mail.com', 'groupno9.it.@gmail.com', 'Your payslip for 2025-11', '<div style=\"font-family:Arial,sans-serif; padding:16px\">\n  <h2>Rathod Software</h2>\n  <h3>Payslip - 2025-11</h3>\n  <p><strong>Employee:</strong> Arjav Maheshwari (RS-2025-002)</p>\n  <p><strong>Department:</strong> Product Management | <strong>Designation:</strong> Product Manager</p>\n  <hr/>\n  <h4>Earnings</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    <tr><td>Gross</td><td align=\"right\">20,000.00</td></tr>\n  </table>\n  <h4 style=\"margin-top:16px\">Deductions</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    <tr><td>PF</td><td align=\"right\">1,000.00</td></tr>\n  </table>\n  <hr/>\n  <p><strong>Gross:</strong> INR 20,000.00 &nbsp; | &nbsp; <strong>Net Pay:</strong> INR 19,000.00</p>\n  <p><small>Generated on 2026-01-02 21:58:29</small></p>\n</div>', NULL, 'sent', NULL, '2026-01-02 16:28:38', '2026-01-02 16:28:38'),
(3, 35, 'monil@mail.com', 'groupno9.it.@gmail.com', 'Your payslip for 2025-11', '<div style=\"font-family:Arial,sans-serif; padding:16px\">\n  <h2>Rathod Software</h2>\n  <h3>Payslip - 2025-11</h3>\n  <p><strong>Employee:</strong> Monil Shah (RS-2025-006)</p>\n  <p><strong>Department:</strong> Software Development - Engineering | <strong>Designation:</strong> Junior Software Engineer</p>\n  <hr/>\n  <h4>Earnings</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    <tr><td>Gross</td><td align=\"right\">20,000.00</td></tr>\n  </table>\n  <h4 style=\"margin-top:16px\">Deductions</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    <tr><td>PF</td><td align=\"right\">1,000.00</td></tr>\n  </table>\n  <hr/>\n  <p><strong>Gross:</strong> INR 20,000.00 &nbsp; | &nbsp; <strong>Net Pay:</strong> INR 19,000.00</p>\n  <p><small>Generated on 2026-01-02 22:00:57</small></p>\n</div>', NULL, 'sent', NULL, '2026-01-02 16:31:05', '2026-01-02 16:31:05');

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
(1, 30, 'RS-2025-001', 'Devesh', 'Shah', '1996-04-18', 'male', '+91 9898754681', 'Serkhej, Ahmedabad ', 6, 11, 2, '2025-07-01', 'active', '0000-00-00 00:00:00'),
(2, 31, 'RS-2025-002', 'Arjav', 'Maheshwari', NULL, NULL, NULL, NULL, 4, 4, 3, '2025-10-08', 'active', '2025-12-27 17:32:17'),
(3, 32, 'RS-2025-003', 'Jayraj', 'Singh', '2000-06-29', 'male', '987456210', 'Ahmedabad', 1, 3, 2, '2025-11-03', 'active', '2025-12-27 17:34:23'),
(4, 33, 'RS-2025-004', 'Jay', 'Kothari', '1998-06-19', 'male', '+91 8796542136', 'Dehgam, Gandhinagar', 3, 7, 1, '2025-10-23', 'active', '2025-12-27 17:36:14'),
(5, 34, 'RS-2025-005', 'Meet', 'Nankani', NULL, NULL, NULL, NULL, 6, 12, 4, '2025-10-09', 'active', '2025-12-27 17:37:07'),
(6, 35, 'RS-2025-006', 'Monil', 'Shah', NULL, NULL, NULL, NULL, 2, 10, 1, '2025-12-01', 'active', '2025-12-27 17:38:29'),
(7, 36, 'RS-2025-007', 'Smit', 'Makawana', NULL, NULL, NULL, NULL, 2, 9, 4, '2025-08-09', 'active', '2025-12-27 17:39:54');

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
(11, 'Maha Shivaratri', '2025-02-26'),
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
(26, 'Bahag Bihu', '2025-04-15'),
(27, 'Good Friday', '2025-04-18'),
(28, 'Easter Day', '2025-04-20'),
(29, 'Birthday of Rabindranath', '2025-05-09'),
(30, 'Buddha Purnima', '2025-05-12'),
(31, 'Bakrid', '2025-06-07'),
(32, 'Rath Yatra', '2025-06-27'),
(33, 'Muharram/Ashura', '2025-07-06'),
(34, 'Raksha Bandhan', '2025-08-09'),
(35, 'Independence Day', '2025-08-15'),
(38, 'Janmashtami', '2025-08-16'),
(39, 'Ganesh Chaturthi', '2025-08-27'),
(40, 'Milad un-Nabi', '2025-09-05'),
(42, 'First Day of Sharad Navratri', '2025-09-22'),
(43, 'First Day of Durga Puja Festivities', '2025-09-28'),
(44, 'Maha Saptami', '2025-09-29'),
(45, 'Maha Ashtami', '2025-09-30'),
(46, 'Maha Navami', '2025-10-01'),
(47, 'Mahatma Gandhi Jayanti', '2025-10-02'),
(49, 'Maharishi Valmiki Jayanti', '2025-10-07'),
(50, 'Karaka Chaturthi', '2025-10-10'),
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
(1, 1, 'New Year\'s Day', '2025-01-01'),
(2, 1, 'Guru Govind Singh Jayanti', '2025-01-06'),
(3, 1, 'Makar Sankranti', '2025-01-14'),
(4, 1, 'Republic Day', '2025-01-26'),
(5, 1, 'Vasant Panchami', '2025-02-02'),
(6, 1, 'Guru Ravidas Jayanti', '2025-02-12'),
(7, 1, 'Shivaji Jayanti', '2025-02-19'),
(8, 1, 'Maharishi Dayanand Saraswati Jayanti', '2025-02-23'),
(9, 1, 'Maha Shivaratri', '2025-02-26'),
(10, 1, 'Ramadan Start', '2025-03-02'),
(11, 1, 'Holika Dahana', '2025-03-13'),
(12, 1, 'Holi', '2025-03-14'),
(13, 1, 'Jamat Ul-Vida', '2025-03-28'),
(14, 1, 'Ugadi', '2025-03-30'),
(15, 1, 'Ramzan Id', '2025-03-31'),
(16, 1, 'Rama Navami', '2025-04-06'),
(17, 1, 'Mahavir Jayanti', '2025-04-10'),
(18, 1, 'Vaisakhi', '2025-04-13'),
(19, 1, 'Ambedkar Jayanti', '2025-04-14'),
(20, 1, 'Bahag Bihu', '2025-04-15'),
(21, 1, 'Good Friday', '2025-04-18'),
(22, 1, 'Easter Day', '2025-04-20'),
(23, 1, 'Birthday of Rabindranath', '2025-05-09'),
(24, 1, 'Buddha Purnima', '2025-05-12'),
(25, 1, 'Bakrid', '2025-06-07'),
(26, 1, 'Rath Yatra', '2025-06-27'),
(27, 1, 'Muharram/Ashura', '2025-07-06'),
(28, 1, 'Raksha Bandhan', '2025-08-09'),
(29, 1, 'Independence Day', '2025-08-15'),
(30, 1, 'Janmashtami', '2025-08-16'),
(31, 1, 'Ganesh Chaturthi', '2025-08-27'),
(32, 1, 'Milad un-Nabi', '2025-09-05'),
(33, 1, 'First Day of Sharad Navratri', '2025-09-22'),
(34, 1, 'First Day of Durga Puja Festivities', '2025-09-28'),
(35, 1, 'Maha Saptami', '2025-09-29'),
(36, 1, 'Maha Ashtami', '2025-09-30'),
(37, 1, 'Maha Navami', '2025-10-01'),
(38, 1, 'Mahatma Gandhi Jayanti', '2025-10-02'),
(39, 1, 'Maharishi Valmiki Jayanti', '2025-10-07'),
(40, 1, 'Karaka Chaturthi', '2025-10-10'),
(41, 1, 'Diwali/Deepavali', '2025-10-20'),
(42, 1, 'Govardhan Puja', '2025-10-22'),
(43, 1, 'Bhai Duj', '2025-10-23'),
(44, 1, 'Chhat Puja (Pratihar Sashthi/Surya Sashthi)', '2025-10-28'),
(45, 1, 'Guru Nanak Jayanti', '2025-11-05'),
(46, 1, 'Guru Tegh Bahadur\'s Martyrdom Day', '2025-11-24'),
(47, 1, 'Christmas Eve', '2025-12-24'),
(48, 1, 'Christmas', '2025-12-25');

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

-- --------------------------------------------------------

--
-- Table structure for table `job_applications`
--

CREATE TABLE `job_applications` (
  `id` int(11) NOT NULL,
  `candidate_id` int(11) NOT NULL,
  `job_id` int(11) NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','shortlisted','interviewed','offered','hired','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 3, 'Sick leave', '2025-12-31', '2026-01-03', 'I\'m Sick', 'pending', '2025-12-29 15:25:46', NULL),
(2, 7, 'Sick leave', '2026-01-07', '2026-01-09', 'k', 'pending', '2026-01-04 15:46:32', NULL);

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
(1, 1, 'Sick leave', 7, 0),
(2, 1, 'Annual leave', 12, 0),
(3, 1, 'Accurable Leave', 5, 1);

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
(7, 30, 'payroll', 'Payslip', 'Your payslip for 2025-11', 2, 'payslip', 0, '2026-01-02 16:28:34', NULL),
(8, 31, 'payroll', 'Payslip', 'Your payslip for 2025-11', 3, 'payslip', 0, '2026-01-02 16:28:38', NULL),
(9, 35, 'payroll', 'Payslip', 'Your payslip for 2025-11', 4, 'payslip', 0, '2026-01-02 16:31:05', NULL);

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
(1, 1, 2, '2025-05', 'INR', '[{\"name\":\"Gross\",\"amount\":12000}]', '[{\"name\":\"Base Deductions\",\"amount\":50}]', 12000.00, 11950.00, NULL, 'generated', 30, '2026-01-02 13:54:03', NULL, NULL),
(2, 1, 1, '2025-11', 'INR', '[{\"name\":\"Gross\",\"amount\":20000}]', '[{\"name\":\"PF\",\"amount\":1000}]', 20000.00, 19000.00, 1, 'sent', 29, '2026-01-02 16:24:34', '2026-01-02 16:28:34', NULL),
(3, 1, 2, '2025-11', 'INR', '[{\"name\":\"Gross\",\"amount\":20000}]', '[{\"name\":\"PF\",\"amount\":1000}]', 20000.00, 19000.00, 1, 'sent', 29, '2026-01-02 16:28:29', '2026-01-02 16:28:38', NULL),
(4, 1, 6, '2025-11', 'INR', '[{\"name\":\"Gross\",\"amount\":20000}]', '[{\"name\":\"PF\",\"amount\":1000}]', 20000.00, 19000.00, 1, 'sent', 29, '2026-01-02 16:30:57', '2026-01-02 16:31:05', NULL),
(5, 1, 5, '2026-01', 'INR', '[{\"name\":\"Gross\",\"amount\":20000}]', '[]', 20000.00, 20000.00, 1, 'generated', 29, '2026-01-02 16:33:08', NULL, NULL);

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
(1, NULL, 'Default Payslip', 'Your payslip for {{period}}', '<div style=\"font-family:Arial,sans-serif; padding:16px\">\n  <h2>{{company_name}}</h2>\n  <h3>Payslip - {{period}}</h3>\n  <p><strong>Employee:</strong> {{employee_name}} ({{employee_code}})</p>\n  <p><strong>Department:</strong> {{department_name}} | <strong>Designation:</strong> {{designation_name}}</p>\n  <hr/>\n  <h4>Earnings</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    {{earnings_rows}}\n  </table>\n  <h4 style=\"margin-top:16px\">Deductions</h4>\n  <table width=\"100%\" cellspacing=\"0\" cellpadding=\"6\" border=\"1\">\n    <tr><th align=\"left\">Component</th><th align=\"right\">Amount</th></tr>\n    {{deductions_rows}}\n  </table>\n  <hr/>\n  <p><strong>Gross:</strong> {{currency}} {{gross_salary}} &nbsp; | &nbsp; <strong>Net Pay:</strong> {{currency}} {{net_salary}}</p>\n  <p><small>Generated on {{generated_at}}</small></p>\n</div>', '[\"company_name\", \"period\", \"employee_name\", \"employee_code\", \"department_name\", \"designation_name\", \"earnings_rows\", \"deductions_rows\", \"gross_salary\", \"net_salary\", \"currency\", \"generated_at\"]', 1, NULL, '2025-09-16 10:01:56', NULL, '2025-09-16 10:01:56');

-- --------------------------------------------------------

--
-- Table structure for table `performance`
--

CREATE TABLE `performance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `evaluator_id` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL COMMENT 'User ID of the approver',
  `period` varchar(7) NOT NULL,
  `score` int(11) NOT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Admin', 'System-wide administrator'),
(2, 'Company Owner', 'Company-level administrator'),
(3, 'Human Resource', 'Manages HR operations'),
(4, 'Employee', 'Regular employee'),
(5, 'Auditor', 'Read-only access for audits'),
(6, 'Manager', 'Department Manager'),
(7, 'Candidate', 'This role is for the candidate who applies for job role in company');

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
(1, 1, 'Normal Shift', '09:00:00', '17:00:00', '9-5 Normal Shift'),
(2, 1, 'Early Morning Shift', '07:00:00', '15:00:00', '7-3 Early Morning Shift'),
(3, 1, 'After-Noon Shift', '00:00:00', '17:00:00', 'After Noon/Lunch Time Shift'),
(4, 1, 'Noon Shift', '13:00:00', '18:00:00', 'After Lunch Shift');

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
(6, 'maintenance_mode', '0', 'Temporarily disable access for non-admin users.', 1, '2025-12-24 13:14:31', '2025-08-15 00:19:12'),
(7, 'Upload Size Limit', '5242880', 'Maximum File Upload Size.', NULL, '2025-08-15 00:21:14', '2025-08-15 00:21:14');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
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

INSERT INTO `tasks` (`id`, `employee_id`, `team_id`, `title`, `description`, `assigned_by`, `due_date`, `status`, `created_at`) VALUES
(1, 6, NULL, 'Test ', 'Test', 33, '2026-01-30', 'completed', '2026-01-03 15:09:32'),
(2, 7, NULL, 'do this', 'vsfvn', 33, '2026-01-31', 'completed', '2026-01-04 08:02:07'),
(3, 6, NULL, 'do this', 'vfvsss', 36, '2026-01-20', 'completed', '2026-01-04 09:59:11'),
(4, 3, NULL, 'do it ', ' ', 36, '2026-01-30', 'pending', '2026-01-04 10:22:48'),
(5, 2, NULL, 'eqfd', 'fwe', 33, '2026-01-14', 'cancelled', '2026-01-04 14:46:05'),
(6, 2, 1, 'cwsc', 'csws', 33, '2026-01-29', 'pending', '2026-01-04 14:54:56'),
(7, 2, NULL, 'vdfvd', 'vdvdvdvd', 33, '2026-02-02', 'cancelled', '2026-01-04 15:02:08'),
(8, 6, NULL, 'vdfvd', 'vdvdvdvd', 33, '2026-02-02', 'cancelled', '2026-01-04 15:02:08'),
(9, 7, NULL, 'vdfvd', 'vdvdvdvd', 33, '2026-02-02', 'cancelled', '2026-01-04 15:02:08'),
(10, 2, 16, 'hgcvhcvh', 'vvn v', 33, '2025-02-04', 'cancelled', '2026-01-04 16:05:47'),
(11, 2, 1, 'fytfh', 'vghc', 33, '2026-01-04', 'cancelled', '2026-01-04 16:09:11');

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
(1, 1, 'Backend Developers', 'Team of the backend developers ', 33, '2025-12-29 12:11:21', NULL, '2025-12-29 12:11:21'),
(16, 1, 'acds', 'dcvs', 33, '2026-01-04 14:34:06', NULL, '2026-01-04 14:34:06');

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
(1, 1, 6, NULL, 33, '2025-12-29 12:12:11'),
(2, 1, 7, 'Team Leader', 33, '2025-12-29 12:12:11'),
(22, 1, 2, NULL, 33, '2026-01-04 08:02:52'),
(26, 16, 2, NULL, 33, '2026-01-04 14:34:06');

-- --------------------------------------------------------

--
-- Table structure for table `team_performance`
--

CREATE TABLE `team_performance` (
  `id` int(11) NOT NULL,
  `team_id` int(11) NOT NULL,
  `period` varchar(7) NOT NULL,
  `score` int(11) NOT NULL,
  `collaboration_score` int(11) DEFAULT NULL,
  `achievements` text DEFAULT NULL,
  `challenges` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `evaluated_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL COMMENT 'User ID of the approver',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
(1, 29, 'Create the Departments of ( HR and QA)', 1, '2025-12-27 17:53:34'),
(2, 29, 'Hire new Employees in Software Dept.', 0, '2025-12-27 17:54:09'),
(3, 29, 'Meeting with Australia Client', 1, '2025-12-27 17:54:43'),
(4, 29, 'LAN Party on 31st Dec.', 0, '2025-12-27 17:55:03'),
(5, 29, 'Wife\'s birthday on 2nd Jan.', 0, '2025-12-27 17:55:29'),
(6, 30, 'Hire Interns ', 0, '2025-12-28 06:31:38'),
(7, 30, 'Compete the onboarding process of few employees', 1, '2025-12-28 06:32:06'),
(8, 30, 'Schedule Interviews for New Candiadates', 0, '2025-12-28 06:32:33'),
(9, 30, 'Verify Attendence ', 1, '2025-12-28 06:32:51');

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
(1, NULL, 1, 'admin', 'admin@mail.com', '$2y$10$4oXGSu5Ip7f2oJFXksjqA.927pO76waLG1YCGuyiQNj6QMoqrJW/W', 'active', '2025-09-06 06:34:45'),
(29, 1, 2, 'Vishal_rathod', 'rsc_admin@mail.com', '$2y$10$IitvhR3mufph8Y7OaJkIz.aBaxBl2l02uoL8zXWILglVkU0NxUKmu', 'active', '2025-12-27 06:30:33'),
(30, 1, 3, 'devesh_shah', 'devesh@mail.com', '$2y$10$qSX3BPxpszg0QXi2yd4nCeprYeNT8odESE1IfeaBJPgq4Brjwd5Ue', 'active', '2025-12-27 17:26:57'),
(31, 1, 4, 'arjav_maheswari', 'arjav@mail.com', '$2y$10$iz0T.Dvf7ol9vGOuCmGOMuLvoLkwxFZ63OV9sXLBQwdy8ZELxYCT2', 'active', '2025-12-27 17:31:34'),
(32, 1, 4, 'jayraj_singh', 'jayraj@mail.com', '$2y$10$/0OJPPfKegcSfqsfbHKl5Ol2Ok0Yq4ANP3QQ7GmHNRJX1tRHvh4hm', 'active', '2025-12-27 17:33:49'),
(33, 1, 6, 'Jay_kothari', 'jay@mail.com', '$2y$10$76sT3cCVm7MlCEyRAYjx/OGl9TnNXNzNoq98rSjcmV1kh0kl/N6hO', 'active', '2025-12-27 17:35:22'),
(34, 1, 3, 'meet_nankani', 'meet@mail.com', '$2y$10$rW7whjwBQHzg9u5uNMpoFuI4Pk.eXbdQ4YL8UlUeOyjySqsZvK52i', 'active', '2025-12-27 17:36:44'),
(35, 1, 4, 'monil_shah', 'monil@mail.com', '$2y$10$Bsh.ZYvuFFlI6Mg/S74qXuqzbFAuwBx2qJNXA.Zh6P5cUvAQREd1G', 'active', '2025-12-27 17:38:03'),
(36, 1, 4, 'Smit_Makawana', 'smit@mail.com', '$2y$10$bBvXCD3dPY7PxVv.KEOTKe9flYoXHVBfDy02y6.tdZtXEMI5G2p/G', 'active', '2025-12-27 17:39:14');

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
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `preference_key`, `preference_value`, `created_at`, `updated_at`) VALUES
(1, 1, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(2, 1, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(3, 1, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(4, 1, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(5, 1, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(6, 1, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(7, 1, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(8, 1, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(9, 1, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(10, 1, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(11, 1, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(12, 29, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(13, 29, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(14, 29, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(15, 29, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(16, 29, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(17, 29, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(18, 29, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(19, 29, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(20, 29, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(21, 29, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(22, 29, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(23, 30, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(24, 30, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(25, 30, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(26, 30, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(27, 30, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(28, 30, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(29, 30, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(30, 30, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(31, 30, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(32, 30, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(33, 30, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(34, 31, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(35, 31, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(36, 31, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(37, 31, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(38, 31, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(39, 31, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(40, 31, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(41, 31, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(42, 31, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(43, 31, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(44, 31, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(45, 32, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(46, 32, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(47, 32, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(48, 32, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(49, 32, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(50, 32, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(51, 32, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(52, 32, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(53, 32, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(54, 32, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(55, 32, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(56, 33, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(57, 33, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(58, 33, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(59, 33, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(60, 33, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(61, 33, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(62, 33, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(63, 33, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(64, 33, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(65, 33, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(66, 33, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(67, 34, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(68, 34, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(69, 34, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(70, 34, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(71, 34, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(72, 34, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(73, 34, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(74, 34, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(75, 34, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(76, 34, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(77, 34, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(78, 35, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(79, 35, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(80, 35, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(81, 35, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(82, 35, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(83, 35, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(84, 35, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(85, 35, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(86, 35, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(87, 35, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(88, 35, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(89, 36, 'notif_leave_status', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(90, 36, 'notif_attendance', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(91, 36, 'notif_payslip', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(92, 36, 'notif_announcements', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(93, 36, 'notif_system', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(94, 36, 'privacy_profile_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(95, 36, 'privacy_phone_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(96, 36, 'privacy_email_visible', '1', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(97, 36, 'deactivation_requested', '0', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(98, 36, 'deactivation_reason', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04'),
(99, 36, 'deactivation_comments', '', '2026-01-04 15:59:04', '2026-01-04 15:59:04');

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
  ADD UNIQUE KEY `unique_employee_date` (`employee_id`,`date`),
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
  ADD KEY `assigned_by` (`assigned_by`),
  ADD KEY `fk_tasks_team_id` (`team_id`);

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
-- Indexes for table `team_performance`
--
ALTER TABLE `team_performance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`),
  ADD KEY `evaluated_by` (`evaluated_by`),
  ADD KEY `approved_by` (`approved_by`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `company_holiday_settings`
--
ALTER TABLE `company_holiday_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_policies`
--
ALTER TABLE `leave_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payslip_templates`
--
ALTER TABLE `payslip_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `performance`
--
ALTER TABLE `performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `team_performance`
--
ALTER TABLE `team_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `todo_list`
--
ALTER TABLE `todo_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=100;

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
  ADD CONSTRAINT `fk_tasks_team_id` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
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
-- Constraints for table `team_performance`
--
ALTER TABLE `team_performance`
  ADD CONSTRAINT `team_performance_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_performance_ibfk_2` FOREIGN KEY (`evaluated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `team_performance_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
