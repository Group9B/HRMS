-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2026 at 04:41 PM
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
-- Database: `hrms_db_template`
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
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(12,2) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `status` enum('Available','Assigned','Maintenance','Retired','Lost') NOT NULL DEFAULT 'Available',
  `condition_status` enum('New','Good','Fair','Poor','Damaged') NOT NULL DEFAULT 'New',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `company_id`, `category_id`, `asset_name`, `asset_tag`, `serial_number`, `purchase_date`, `purchase_cost`, `warranty_expiry`, `status`, `condition_status`, `description`, `created_at`, `updated_at`) VALUES
(1, 1, 1816, 'Lenovo ThinkPad X1', 'SUR-LAP-2954', 'BC62AC9C4ACE', '2026-03-02', 199526.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(2, 1, 1816, 'MacBook Pro M2', 'SUR-LAP-9968', '013F3D97E9B5', '2025-07-08', 59252.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(3, 1, 1816, 'MacBook Pro M2', 'SUR-LAP-1468', '34CF896C5ED6', '2026-01-22', 164851.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(4, 1, 1816, 'Dell XPS 15', 'SUR-LAP-2553', '2E1E6B9A5179', '2025-05-31', 139077.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(5, 1, 1816, 'HP EliteBook', 'SUR-LAP-3815', 'FA7BBAB1EFD3', '2025-09-04', 44216.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(6, 1, 1817, 'HP ProDesk', 'SUR-DES-7501', '2F16C7C08FA6', '2025-03-22', 136976.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(7, 1, 1817, 'iMac 24\"', 'SUR-DES-2556', '72823DC1D443', '2025-10-04', 61907.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(8, 1, 1817, 'Dell Optiplex', 'SUR-DES-9614', 'D1FBDE1FC357', '2025-05-16', 159391.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(9, 1, 1817, 'iMac 24\"', 'SUR-DES-2149', 'A1FEEDFA2925', '2025-09-22', 27174.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(10, 1, 1817, 'iMac 24\"', 'SUR-DES-6641', 'E268BDB367D4', '2025-09-04', 177375.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(11, 1, 1818, 'LG 24\" IPS', 'SUR-MON-1841', '0D33AB6C6A79', '2025-10-21', 60885.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(12, 1, 1818, 'LG 24\" IPS', 'SUR-MON-5692', 'E02033B1226B', '2025-06-05', 13739.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(13, 1, 1818, 'Dell Ultrasharp 27\"', 'SUR-MON-3963', '44C274157C4B', '2025-12-12', 19979.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(14, 1, 1818, 'Samsung Curved 32\"', 'SUR-MON-8101', '7658F62BD3F6', '2026-02-04', 182165.00, NULL, 'Maintenance', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(15, 1, 1818, 'LG 24\" IPS', 'SUR-MON-5201', '49AC868EEE7E', '2025-08-29', 92828.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(16, 1, 1819, 'Samsung S23', 'SUR-MOB-3637', 'AE64691944CF', '2025-08-01', 182853.00, NULL, 'Maintenance', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(17, 1, 1819, 'Google Pixel 7', 'SUR-MOB-7896', 'E2C1BC49A746', '2025-08-05', 72942.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(18, 1, 1819, 'Google Pixel 7', 'SUR-MOB-5759', 'ABBE51D520C3', '2025-07-29', 140741.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(19, 1, 1819, 'Samsung S23', 'SUR-MOB-2949', '2A6042BC264D', '2026-01-07', 60103.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(20, 1, 1820, 'Adobe Creative Cloud', 'SUR-SOF-6959', 'EE255F525FC2', '2026-02-18', 59478.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(21, 1, 1820, 'Office 365 E5', 'SUR-SOF-9644', '71A99C917D3F', '2025-09-11', 76348.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(22, 1, 1820, 'Office 365 E5', 'SUR-SOF-7107', '1E7302D467C8', '2026-02-27', 168504.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(23, 1, 1820, 'JetBrains All Products', 'SUR-SOF-8056', '89BAA3293919', '2025-04-06', 186834.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(24, 1, 1821, 'RFID Access Card', 'SUR-ACC-4569', '6E12CFA5182A', '2025-09-14', 14507.00, NULL, 'Maintenance', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(25, 1, 1821, 'Biometric ID', 'SUR-ACC-6795', 'C2D570BE6BA8', '2025-12-13', 79053.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(26, 1, 1821, 'Biometric ID', 'SUR-ACC-2316', 'AFEEC7A6C8C2', '2025-05-30', 192302.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(27, 1, 1821, 'RFID Access Card', 'SUR-ACC-9265', 'BC3DF2355906', '2025-05-11', 87047.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(28, 1, 1821, 'RFID Access Card', 'SUR-ACC-8592', 'E22265F531B6', '2025-04-18', 116758.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(29, 2, 1822, 'MacBook Pro M2', 'RAM-LAP-7923', '0C1AA10E507E', '2025-10-29', 33337.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(30, 2, 1822, 'HP EliteBook', 'RAM-LAP-8492', '28BF98811F38', '2025-06-09', 191509.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(31, 2, 1822, 'Dell XPS 15', 'RAM-LAP-9442', '404E42150AB2', '2026-01-24', 25510.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(32, 2, 1822, 'Dell XPS 15', 'RAM-LAP-9379', '0CA49B7FEB37', '2025-04-19', 91791.00, NULL, 'Maintenance', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(33, 2, 1823, 'Dell Optiplex', 'RAM-DES-2613', '8AF06A008D9C', '2025-12-01', 113066.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(34, 2, 1823, 'Dell Optiplex', 'RAM-DES-5182', 'FD529EAA80D5', '2025-04-22', 38756.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(35, 2, 1823, 'HP ProDesk', 'RAM-DES-9394', '615DE2FFADD8', '2025-07-09', 126184.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(36, 2, 1823, 'HP ProDesk', 'RAM-DES-3116', 'B2418C5AB217', '2025-11-28', 131005.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(37, 2, 1824, 'Dell Ultrasharp 27\"', 'RAM-MON-9393', '46F3059E99D6', '2025-06-09', 76766.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(38, 2, 1824, 'Dell Ultrasharp 27\"', 'RAM-MON-8606', 'FFD02364CF12', '2025-06-03', 32862.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(39, 2, 1824, 'Dell Ultrasharp 27\"', 'RAM-MON-4482', '1F98E73DC515', '2025-06-09', 76212.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(40, 2, 1824, 'Samsung Curved 32\"', 'RAM-MON-4554', '6A9F335B2DAD', '2026-01-07', 54365.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(41, 2, 1825, 'Samsung S23', 'RAM-MOB-8900', '5011F6E46250', '2025-07-04', 169181.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(42, 2, 1825, 'Google Pixel 7', 'RAM-MOB-4406', '3BD13928DF00', '2025-06-20', 198089.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(43, 2, 1825, 'Google Pixel 7', 'RAM-MOB-2149', '9B1AFE9FDB86', '2025-04-20', 199054.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(44, 2, 1825, 'iPhone 14', 'RAM-MOB-1995', 'D3A1C9ACC22B', '2025-04-07', 118924.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(45, 2, 1826, 'Office 365 E5', 'RAM-SOF-3074', '7F67D716E48F', '2025-09-19', 151681.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(46, 2, 1826, 'Office 365 E5', 'RAM-SOF-1418', '2E198B39088A', '2025-03-20', 97563.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(47, 2, 1826, 'JetBrains All Products', 'RAM-SOF-2766', 'CFE9044BEA2D', '2025-05-05', 114985.00, NULL, 'Maintenance', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(48, 2, 1826, 'JetBrains All Products', 'RAM-SOF-2918', 'EC718B67F26F', '2025-04-25', 26954.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(49, 2, 1827, 'Biometric ID', 'RAM-ACC-2021', 'EA1D1BBDF486', '2026-02-21', 194136.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(50, 2, 1827, 'Biometric ID', 'RAM-ACC-7477', '4D3C9C940800', '2025-08-05', 107818.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(51, 2, 1827, 'Biometric ID', 'RAM-ACC-1008', '0F10EDBE64EB', '2025-04-09', 124982.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(52, 2, 1827, 'Biometric ID', 'RAM-ACC-2668', '28C22E995D98', '2025-11-27', 42736.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(53, 2, 1827, 'Biometric ID', 'RAM-ACC-3657', '9D157206C913', '2025-05-06', 140446.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(54, 3, 1828, 'Dell XPS 15', 'IND-LAP-1233', '9C4FE63AD72C', '2025-05-28', 64951.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(55, 3, 1828, 'MacBook Pro M2', 'IND-LAP-7173', 'B7817A96D643', '2025-06-07', 175563.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(56, 3, 1828, 'MacBook Pro M2', 'IND-LAP-6403', '709CEB4A787C', '2025-09-24', 89433.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(57, 3, 1829, 'Dell Optiplex', 'IND-DES-7904', 'CAE492F35BEA', '2025-07-04', 83746.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(58, 3, 1829, 'Dell Optiplex', 'IND-DES-6274', '0B6D64E4A82B', '2026-01-18', 84112.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(59, 3, 1829, 'iMac 24\"', 'IND-DES-9679', '60CD1E206D0C', '2025-06-18', 100149.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(60, 3, 1829, 'HP ProDesk', 'IND-DES-7613', '73FCEB1E244B', '2026-02-01', 119329.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(61, 3, 1829, 'iMac 24\"', 'IND-DES-2300', '547938DFA0A2', '2025-09-30', 171486.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(62, 3, 1830, 'LG 24\" IPS', 'IND-MON-1551', '48EA3D2BA7CA', '2025-10-23', 8865.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(63, 3, 1830, 'Dell Ultrasharp 27\"', 'IND-MON-7010', '5C08FEFF96CF', '2025-07-22', 128808.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(64, 3, 1830, 'Dell Ultrasharp 27\"', 'IND-MON-5953', '8562889E6B27', '2026-02-12', 124295.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(65, 3, 1830, 'LG 24\" IPS', 'IND-MON-8040', '7DAB552AA719', '2025-05-21', 44567.00, NULL, 'Maintenance', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(66, 3, 1830, 'Dell Ultrasharp 27\"', 'IND-MON-1160', '66E8703E7D87', '2025-05-13', 50811.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(67, 3, 1831, 'Google Pixel 7', 'IND-MOB-5124', '2D43A29A4839', '2025-03-13', 8233.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(68, 3, 1831, 'iPhone 14', 'IND-MOB-2894', '45B75B4006D3', '2026-02-25', 16171.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(69, 3, 1831, 'Google Pixel 7', 'IND-MOB-9812', '26EBB6F87474', '2025-03-17', 148358.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(70, 3, 1831, 'Samsung S23', 'IND-MOB-5268', 'AA06AEEF815D', '2025-04-04', 96919.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(71, 3, 1832, 'Office 365 E5', 'IND-SOF-5366', '6F7B8D83F6F7', '2025-09-20', 191204.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(72, 3, 1832, 'Adobe Creative Cloud', 'IND-SOF-5768', 'D1B808DD79C1', '2025-09-01', 73353.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(73, 3, 1832, 'JetBrains All Products', 'IND-SOF-3719', 'FF78455617C8', '2025-05-27', 95941.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(74, 3, 1832, 'Office 365 E5', 'IND-SOF-9991', 'C6B1E0B4988D', '2025-10-06', 111050.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(75, 3, 1833, 'Biometric ID', 'IND-ACC-3571', 'DF23F4ED4526', '2025-08-28', 95163.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(76, 3, 1833, 'Biometric ID', 'IND-ACC-8147', '01F1C0D7CEF4', '2025-09-09', 160769.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(77, 3, 1833, 'RFID Access Card', 'IND-ACC-7041', '094F62017E3E', '2025-04-06', 197338.00, NULL, 'Assigned', 'New', NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(78, 1, 2, 'Dell Optiplex', 'AST-NEW-9382', 'SN32354', '2026-03-06', 35000.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(79, 1, 2, 'Dell Optiplex', 'AST-NEW-9380', 'SN58320', '2026-03-06', 35000.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(80, 1, 3, 'Samsung 24\"', 'AST-NEW-6778', 'SN47549', '2026-03-06', 12000.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(81, 1, 3, 'Samsung 24\"', 'AST-NEW-8665', 'SN41211', '2026-03-06', 12000.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(82, 1, 1, 'Dell Latitude', 'AST-NEW-3438', 'SN11209', '2026-03-06', 45000.00, NULL, 'Available', 'New', NULL, '2026-03-06 16:42:18', '2026-03-06 16:42:18');

-- --------------------------------------------------------

--
-- Table structure for table `asset_assignments`
--

CREATE TABLE `asset_assignments` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('Active','Returned') NOT NULL DEFAULT 'Active',
  `condition_on_assignment` enum('New','Good','Fair','Poor','Damaged') DEFAULT 'Good',
  `condition_on_return` enum('New','Good','Fair','Poor','Damaged') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_assignments`
--

INSERT INTO `asset_assignments` (`id`, `asset_id`, `employee_id`, `assigned_by`, `assigned_date`, `expected_return_date`, `actual_return_date`, `status`, `condition_on_assignment`, `condition_on_return`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 1, 63, 1, '2026-03-06', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(2, 2, 109, 1, '2025-07-30', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(3, 3, 80, 1, '2026-02-05', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(4, 4, 57, 1, '2025-06-17', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(5, 5, 98, 1, '2025-09-16', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(6, 7, 88, 1, '2025-10-22', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(7, 9, 75, 1, '2025-10-01', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(8, 12, 57, 1, '2025-06-09', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(9, 13, 77, 1, '2025-12-24', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(10, 15, 64, 1, '2025-09-19', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(11, 17, 62, 1, '2025-08-18', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(12, 18, 84, 1, '2025-08-25', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(13, 19, 69, 1, '2026-01-23', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(14, 20, 74, 1, '2026-03-06', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(15, 21, 68, 1, '2025-10-10', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(16, 22, 103, 1, '2026-03-04', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(17, 25, 74, 1, '2025-12-22', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(18, 26, 58, 1, '2025-06-01', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(19, 28, 89, 1, '2025-05-11', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(20, 29, 159, 1, '2025-11-04', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(21, 30, 146, 1, '2025-06-21', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(22, 33, 153, 1, '2025-12-26', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(23, 34, 119, 1, '2025-05-09', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(24, 35, 131, 1, '2025-07-18', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(25, 36, 112, 1, '2025-12-19', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(26, 37, 137, 1, '2025-06-25', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(27, 38, 159, 1, '2025-06-10', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(28, 39, 115, 1, '2025-07-09', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(29, 41, 132, 1, '2025-07-22', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(30, 42, 143, 1, '2025-06-25', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(31, 45, 151, 1, '2025-09-26', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(32, 46, 113, 1, '2025-04-01', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(33, 48, 131, 1, '2025-05-14', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(34, 49, 161, 1, '2026-03-01', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(35, 51, 142, 1, '2025-04-22', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(36, 52, 119, 1, '2025-12-02', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(37, 53, 150, 1, '2025-05-12', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(38, 54, 189, 1, '2025-05-31', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(39, 55, 206, 1, '2025-06-17', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(40, 57, 205, 1, '2025-07-16', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(41, 58, 175, 1, '2026-01-20', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(42, 60, 178, 1, '2026-03-01', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(43, 61, 201, 1, '2025-10-05', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(44, 63, 199, 1, '2025-08-12', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(45, 66, 212, 1, '2025-05-21', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(46, 68, 206, 1, '2026-03-06', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(47, 70, 187, 1, '2025-04-26', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(48, 71, 214, 1, '2025-09-30', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(49, 72, 3, 1, '2025-09-26', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(50, 73, 184, 1, '2025-06-19', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(51, 74, 193, 1, '2025-10-25', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(52, 76, 187, 1, '2025-09-13', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16'),
(53, 77, 207, 1, '2025-04-15', NULL, NULL, 'Active', 'New', NULL, NULL, '2026-03-06 16:42:16', '2026-03-06 16:42:16');

-- --------------------------------------------------------

--
-- Table structure for table `asset_categories`
--

CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Hardware','Software','Access','Security','Other') NOT NULL DEFAULT 'Other',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_categories`
--

INSERT INTO `asset_categories` (`id`, `company_id`, `name`, `type`, `description`, `created_at`) VALUES
(1, 1, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(2, 1, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(3, 1, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(4, 1, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(5, 1, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(6, 1, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(7, 1, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(8, 1, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(9, 1, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(10, 1, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(11, 1, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(12, 1, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(13, 1, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(14, 1, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(15, 1, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(16, 1, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(17, 1, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(18, 1, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(19, 1, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(20, 1, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(21, 1, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(22, 1, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(23, 1, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(24, 1, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(25, 1, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(26, 1, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(27, 1, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(28, 1, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(29, 1, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(30, 1, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(31, 1, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(32, 1, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(33, 1, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(34, 2, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(35, 2, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(36, 2, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(37, 2, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(38, 2, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(39, 2, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(40, 2, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(41, 2, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(42, 2, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(43, 2, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(44, 2, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(45, 2, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(46, 2, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(47, 2, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(48, 2, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(49, 2, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(50, 2, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(51, 2, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(52, 2, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(53, 2, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(54, 2, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(55, 2, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(56, 2, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(57, 2, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(58, 2, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(59, 2, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(60, 2, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(61, 2, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(62, 2, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(63, 2, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(64, 2, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(65, 2, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(66, 2, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(67, 3, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(68, 3, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(69, 3, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(70, 3, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(71, 3, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(72, 3, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(73, 3, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(74, 3, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(75, 3, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(76, 3, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(77, 3, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(78, 3, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(79, 3, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(80, 3, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(81, 3, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(82, 3, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(83, 3, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(84, 3, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(85, 3, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(86, 3, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(87, 3, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(88, 3, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(89, 3, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(90, 3, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(91, 3, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(92, 3, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(93, 3, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(94, 3, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(95, 3, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(96, 3, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(97, 3, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(98, 3, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(99, 3, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(100, 4, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(101, 4, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(102, 4, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(103, 4, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(104, 4, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(105, 4, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(106, 4, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(107, 4, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(108, 4, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(109, 4, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(110, 4, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(111, 4, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(112, 4, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(113, 4, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(114, 4, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(115, 4, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(116, 4, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(117, 4, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(118, 4, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(119, 4, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(120, 4, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(121, 4, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(122, 4, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(123, 4, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(124, 4, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(125, 4, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(126, 4, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(127, 4, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(128, 4, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(129, 4, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(130, 4, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(131, 4, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(132, 4, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(133, 5, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(134, 5, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(135, 5, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(136, 5, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(137, 5, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(138, 5, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(139, 5, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(140, 5, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(141, 5, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(142, 5, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(143, 5, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(144, 5, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(145, 5, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(146, 5, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(147, 5, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(148, 5, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(149, 5, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(150, 5, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(151, 5, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(152, 5, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(153, 5, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(154, 5, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(155, 5, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(156, 5, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(157, 5, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(158, 5, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(159, 5, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(160, 5, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(161, 5, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(162, 5, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(163, 5, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(164, 5, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(165, 5, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(166, 6, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(167, 6, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(168, 6, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(169, 6, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(170, 6, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(171, 6, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(172, 6, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(173, 6, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(174, 6, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(175, 6, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(176, 6, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(177, 6, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(178, 6, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(179, 6, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(180, 6, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(181, 6, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(182, 6, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(183, 6, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(184, 6, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(185, 6, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(186, 6, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(187, 6, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(188, 6, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(189, 6, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(190, 6, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(191, 6, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(192, 6, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(193, 6, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(194, 6, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(195, 6, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(196, 6, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(197, 6, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(198, 6, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(199, 7, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(200, 7, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(201, 7, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(202, 7, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(203, 7, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(204, 7, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(205, 7, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(206, 7, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(207, 7, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(208, 7, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(209, 7, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(210, 7, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(211, 7, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(212, 7, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(213, 7, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(214, 7, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(215, 7, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(216, 7, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(217, 7, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(218, 7, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(219, 7, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(220, 7, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(221, 7, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(222, 7, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(223, 7, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(224, 7, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(225, 7, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(226, 7, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(227, 7, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(228, 7, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(229, 7, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(230, 7, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(231, 7, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(232, 8, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(233, 8, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(234, 8, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(235, 8, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(236, 8, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(237, 8, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:11'),
(238, 8, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:11'),
(239, 8, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:11'),
(240, 8, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:11'),
(241, 8, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:11'),
(242, 8, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:11'),
(243, 8, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:11'),
(244, 8, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:11'),
(245, 8, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:11'),
(246, 8, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:11'),
(247, 8, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:11'),
(248, 8, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:11'),
(249, 8, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:11'),
(250, 8, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:11'),
(251, 8, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:11'),
(252, 8, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:11'),
(253, 8, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:11'),
(254, 8, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:11'),
(255, 8, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:11'),
(256, 8, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:11'),
(257, 8, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:11'),
(258, 8, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:11'),
(259, 8, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:11'),
(260, 8, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:11'),
(261, 8, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:11'),
(262, 8, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:11'),
(263, 8, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:11'),
(264, 8, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:11'),
(265, 9, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:11'),
(266, 9, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:11'),
(267, 9, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:11'),
(268, 9, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:11'),
(269, 9, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:11'),
(270, 9, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(271, 9, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(272, 9, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(273, 9, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(274, 9, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(275, 9, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(276, 9, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(277, 9, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(278, 9, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(279, 9, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(280, 9, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(281, 9, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(282, 9, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(283, 9, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(284, 9, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(285, 9, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(286, 9, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(287, 9, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(288, 9, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(289, 9, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(290, 9, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(291, 9, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(292, 9, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(293, 9, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(294, 9, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(295, 9, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(296, 9, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(297, 9, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(298, 10, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(299, 10, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(300, 10, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(301, 10, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(302, 10, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(303, 10, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(304, 10, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(305, 10, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(306, 10, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(307, 10, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(308, 10, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(309, 10, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(310, 10, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(311, 10, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(312, 10, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(313, 10, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(314, 10, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(315, 10, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(316, 10, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(317, 10, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(318, 10, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(319, 10, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(320, 10, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(321, 10, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(322, 10, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(323, 10, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(324, 10, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(325, 10, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(326, 10, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(327, 10, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(328, 10, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(329, 10, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(330, 10, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(331, 11, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(332, 11, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(333, 11, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(334, 11, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(335, 11, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(336, 11, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(337, 11, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(338, 11, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(339, 11, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(340, 11, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(341, 11, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(342, 11, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(343, 11, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(344, 11, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(345, 11, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(346, 11, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(347, 11, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(348, 11, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(349, 11, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(350, 11, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(351, 11, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(352, 11, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(353, 11, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(354, 11, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(355, 11, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(356, 11, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(357, 11, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(358, 11, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(359, 11, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(360, 11, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(361, 11, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(362, 11, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(363, 11, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(364, 12, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(365, 12, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(366, 12, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(367, 12, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(368, 12, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(369, 12, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(370, 12, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(371, 12, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(372, 12, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(373, 12, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(374, 12, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(375, 12, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(376, 12, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(377, 12, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(378, 12, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(379, 12, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(380, 12, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(381, 12, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(382, 12, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(383, 12, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(384, 12, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(385, 12, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(386, 12, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(387, 12, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(388, 12, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(389, 12, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(390, 12, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(391, 12, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(392, 12, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(393, 12, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(394, 12, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(395, 12, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(396, 12, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(397, 13, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(398, 13, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(399, 13, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(400, 13, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(401, 13, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(402, 13, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(403, 13, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(404, 13, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(405, 13, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(406, 13, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(407, 13, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(408, 13, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(409, 13, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(410, 13, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(411, 13, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(412, 13, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(413, 13, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(414, 13, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(415, 13, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(416, 13, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(417, 13, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(418, 13, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(419, 13, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(420, 13, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(421, 13, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(422, 13, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(423, 13, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(424, 13, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(425, 13, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(426, 13, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(427, 13, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(428, 13, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(429, 13, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(430, 14, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(431, 14, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(432, 14, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(433, 14, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(434, 14, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(435, 14, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(436, 14, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(437, 14, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(438, 14, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(439, 14, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(440, 14, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(441, 14, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(442, 14, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(443, 14, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(444, 14, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(445, 14, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(446, 14, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(447, 14, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(448, 14, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(449, 14, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(450, 14, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(451, 14, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(452, 14, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(453, 14, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(454, 14, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(455, 14, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(456, 14, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(457, 14, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(458, 14, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(459, 14, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(460, 14, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(461, 14, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(462, 14, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(463, 15, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(464, 15, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(465, 15, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(466, 15, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(467, 15, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(468, 15, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(469, 15, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(470, 15, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(471, 15, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(472, 15, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(473, 15, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(474, 15, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(475, 15, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(476, 15, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(477, 15, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(478, 15, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(479, 15, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(480, 15, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(481, 15, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(482, 15, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(483, 15, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(484, 15, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(485, 15, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(486, 15, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(487, 15, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(488, 15, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(489, 15, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(490, 15, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(491, 15, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(492, 15, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(493, 15, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(494, 15, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(495, 15, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(496, 16, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(497, 16, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(498, 16, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(499, 16, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(500, 16, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(501, 16, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(502, 16, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(503, 16, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(504, 16, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(505, 16, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(506, 16, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(507, 16, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(508, 16, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(509, 16, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(510, 16, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(511, 16, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(512, 16, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(513, 16, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(514, 16, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(515, 16, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(516, 16, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(517, 16, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(518, 16, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(519, 16, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(520, 16, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(521, 16, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(522, 16, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(523, 16, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(524, 16, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(525, 16, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(526, 16, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(527, 16, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(528, 16, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(529, 17, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(530, 17, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(531, 17, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(532, 17, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(533, 17, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(534, 17, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(535, 17, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(536, 17, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(537, 17, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(538, 17, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(539, 17, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(540, 17, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(541, 17, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(542, 17, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(543, 17, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(544, 17, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(545, 17, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(546, 17, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(547, 17, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(548, 17, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(549, 17, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(550, 17, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(551, 17, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(552, 17, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(553, 17, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(554, 17, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(555, 17, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(556, 17, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(557, 17, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(558, 17, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(559, 17, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(560, 17, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(561, 17, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(562, 18, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(563, 18, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(564, 18, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(565, 18, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(566, 18, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12');
INSERT INTO `asset_categories` (`id`, `company_id`, `name`, `type`, `description`, `created_at`) VALUES
(567, 18, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(568, 18, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(569, 18, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(570, 18, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(571, 18, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(572, 18, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(573, 18, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(574, 18, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(575, 18, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(576, 18, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(577, 18, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(578, 18, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(579, 18, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(580, 18, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(581, 18, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(582, 18, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(583, 18, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(584, 18, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:12'),
(585, 18, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:12'),
(586, 18, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:12'),
(587, 18, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:12'),
(588, 18, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:12'),
(589, 18, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:12'),
(590, 18, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:12'),
(591, 18, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:12'),
(592, 18, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:12'),
(593, 18, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:12'),
(594, 18, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:12'),
(595, 19, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:12'),
(596, 19, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:12'),
(597, 19, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:12'),
(598, 19, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:12'),
(599, 19, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:12'),
(600, 19, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:12'),
(601, 19, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:12'),
(602, 19, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:12'),
(603, 19, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:12'),
(604, 19, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:12'),
(605, 19, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:12'),
(606, 19, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:12'),
(607, 19, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:12'),
(608, 19, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:12'),
(609, 19, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:12'),
(610, 19, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:12'),
(611, 19, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:12'),
(612, 19, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:12'),
(613, 19, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:12'),
(614, 19, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:12'),
(615, 19, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:12'),
(616, 19, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:12'),
(617, 19, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(618, 19, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(619, 19, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(620, 19, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(621, 19, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(622, 19, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(623, 19, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(624, 19, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(625, 19, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(626, 19, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(627, 19, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(628, 20, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(629, 20, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(630, 20, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(631, 20, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(632, 20, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(633, 20, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(634, 20, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(635, 20, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(636, 20, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(637, 20, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(638, 20, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(639, 20, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(640, 20, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(641, 20, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(642, 20, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(643, 20, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(644, 20, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(645, 20, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(646, 20, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(647, 20, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(648, 20, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(649, 20, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(650, 20, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(651, 20, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(652, 20, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(653, 20, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(654, 20, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(655, 20, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(656, 20, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(657, 20, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(658, 20, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(659, 20, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(660, 20, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(661, 21, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(662, 21, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(663, 21, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(664, 21, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(665, 21, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(666, 21, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(667, 21, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(668, 21, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(669, 21, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(670, 21, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(671, 21, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(672, 21, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(673, 21, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(674, 21, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(675, 21, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(676, 21, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(677, 21, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(678, 21, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(679, 21, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(680, 21, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(681, 21, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(682, 21, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(683, 21, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(684, 21, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(685, 21, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(686, 21, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(687, 21, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(688, 21, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(689, 21, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(690, 21, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(691, 21, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(692, 21, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(693, 21, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(694, 22, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(695, 22, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(696, 22, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(697, 22, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(698, 22, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(699, 22, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(700, 22, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(701, 22, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(702, 22, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(703, 22, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(704, 22, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(705, 22, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(706, 22, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(707, 22, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(708, 22, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(709, 22, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(710, 22, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(711, 22, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(712, 22, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(713, 22, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(714, 22, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(715, 22, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(716, 22, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(717, 22, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(718, 22, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(719, 22, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(720, 22, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(721, 22, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(722, 22, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(723, 22, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(724, 22, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(725, 22, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(726, 22, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(727, 23, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(728, 23, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(729, 23, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(730, 23, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(731, 23, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(732, 23, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(733, 23, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(734, 23, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(735, 23, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(736, 23, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(737, 23, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(738, 23, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(739, 23, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(740, 23, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(741, 23, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(742, 23, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(743, 23, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(744, 23, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(745, 23, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(746, 23, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(747, 23, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(748, 23, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(749, 23, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(750, 23, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(751, 23, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(752, 23, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(753, 23, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(754, 23, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(755, 23, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(756, 23, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(757, 23, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(758, 23, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(759, 23, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(760, 24, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(761, 24, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(762, 24, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(763, 24, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(764, 24, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(765, 24, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(766, 24, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(767, 24, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(768, 24, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(769, 24, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(770, 24, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(771, 24, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(772, 24, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(773, 24, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(774, 24, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(775, 24, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(776, 24, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(777, 24, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(778, 24, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(779, 24, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(780, 24, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(781, 24, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(782, 24, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(783, 24, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(784, 24, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(785, 24, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(786, 24, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(787, 24, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(788, 24, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(789, 24, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(790, 24, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(791, 24, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(792, 24, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(793, 25, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(794, 25, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(795, 25, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(796, 25, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(797, 25, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(798, 25, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(799, 25, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(800, 25, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(801, 25, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(802, 25, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(803, 25, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(804, 25, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(805, 25, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(806, 25, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(807, 25, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(808, 25, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(809, 25, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(810, 25, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(811, 25, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(812, 25, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(813, 25, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(814, 25, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(815, 25, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(816, 25, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(817, 25, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(818, 25, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(819, 25, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(820, 25, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(821, 25, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(822, 25, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(823, 25, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(824, 25, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(825, 25, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(826, 26, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(827, 26, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(828, 26, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(829, 26, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(830, 26, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(831, 26, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(832, 26, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(833, 26, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(834, 26, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(835, 26, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(836, 26, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(837, 26, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(838, 26, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(839, 26, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(840, 26, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(841, 26, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(842, 26, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(843, 26, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(844, 26, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(845, 26, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(846, 26, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(847, 26, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(848, 26, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(849, 26, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(850, 26, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(851, 26, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(852, 26, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(853, 26, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(854, 26, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(855, 26, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(856, 26, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(857, 26, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(858, 26, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(859, 27, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(860, 27, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(861, 27, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(862, 27, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(863, 27, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(864, 27, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(865, 27, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(866, 27, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(867, 27, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(868, 27, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(869, 27, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(870, 27, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(871, 27, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(872, 27, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(873, 27, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(874, 27, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(875, 27, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(876, 27, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(877, 27, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(878, 27, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(879, 27, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(880, 27, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(881, 27, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(882, 27, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(883, 27, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(884, 27, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(885, 27, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(886, 27, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(887, 27, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(888, 27, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(889, 27, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(890, 27, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(891, 27, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(892, 28, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(893, 28, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(894, 28, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(895, 28, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(896, 28, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(897, 28, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(898, 28, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(899, 28, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(900, 28, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(901, 28, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(902, 28, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(903, 28, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(904, 28, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(905, 28, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(906, 28, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(907, 28, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(908, 28, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(909, 28, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(910, 28, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(911, 28, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(912, 28, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(913, 28, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(914, 28, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:13'),
(915, 28, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:13'),
(916, 28, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:13'),
(917, 28, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:13'),
(918, 28, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:13'),
(919, 28, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:13'),
(920, 28, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:13'),
(921, 28, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:13'),
(922, 28, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:13'),
(923, 28, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:13'),
(924, 28, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:13'),
(925, 29, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:13'),
(926, 29, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:13'),
(927, 29, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:13'),
(928, 29, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:13'),
(929, 29, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:13'),
(930, 29, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:13'),
(931, 29, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:13'),
(932, 29, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:13'),
(933, 29, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:13'),
(934, 29, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:13'),
(935, 29, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:13'),
(936, 29, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:13'),
(937, 29, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:13'),
(938, 29, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:13'),
(939, 29, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:13'),
(940, 29, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:13'),
(941, 29, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:13'),
(942, 29, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:13'),
(943, 29, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:13'),
(944, 29, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:13'),
(945, 29, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:13'),
(946, 29, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:13'),
(947, 29, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(948, 29, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(949, 29, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(950, 29, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(951, 29, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(952, 29, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(953, 29, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(954, 29, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(955, 29, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(956, 29, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(957, 29, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(958, 30, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(959, 30, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(960, 30, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(961, 30, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(962, 30, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(963, 30, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(964, 30, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(965, 30, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(966, 30, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(967, 30, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(968, 30, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(969, 30, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(970, 30, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(971, 30, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(972, 30, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(973, 30, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(974, 30, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(975, 30, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(976, 30, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(977, 30, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(978, 30, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(979, 30, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(980, 30, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(981, 30, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(982, 30, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(983, 30, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(984, 30, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(985, 30, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(986, 30, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(987, 30, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(988, 30, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(989, 30, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(990, 30, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(991, 31, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(992, 31, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(993, 31, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(994, 31, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(995, 31, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(996, 31, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(997, 31, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(998, 31, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(999, 31, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1000, 31, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1001, 31, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1002, 31, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1003, 31, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1004, 31, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1005, 31, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1006, 31, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1007, 31, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1008, 31, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1009, 31, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1010, 31, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1011, 31, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1012, 31, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1013, 31, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1014, 31, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1015, 31, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1016, 31, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1017, 31, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1018, 31, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1019, 31, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1020, 31, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1021, 31, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1022, 31, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1023, 31, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1024, 32, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1025, 32, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1026, 32, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1027, 32, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1028, 32, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1029, 32, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1030, 32, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1031, 32, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1032, 32, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1033, 32, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1034, 32, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1035, 32, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1036, 32, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1037, 32, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1038, 32, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1039, 32, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1040, 32, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1041, 32, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1042, 32, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1043, 32, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1044, 32, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1045, 32, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1046, 32, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1047, 32, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1048, 32, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1049, 32, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1050, 32, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1051, 32, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1052, 32, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1053, 32, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1054, 32, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1055, 32, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1056, 32, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1057, 33, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1058, 33, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1059, 33, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1060, 33, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1061, 33, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1062, 33, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1063, 33, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1064, 33, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1065, 33, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1066, 33, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1067, 33, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1068, 33, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1069, 33, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1070, 33, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1071, 33, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1072, 33, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1073, 33, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1074, 33, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1075, 33, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1076, 33, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1077, 33, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1078, 33, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1079, 33, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1080, 33, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1081, 33, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1082, 33, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1083, 33, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1084, 33, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1085, 33, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1086, 33, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1087, 33, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1088, 33, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1089, 33, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1090, 34, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1091, 34, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1092, 34, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1093, 34, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1094, 34, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1095, 34, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1096, 34, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1097, 34, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1098, 34, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1099, 34, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1100, 34, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1101, 34, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1102, 34, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1103, 34, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1104, 34, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1105, 34, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1106, 34, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1107, 34, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1108, 34, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1109, 34, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1110, 34, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1111, 34, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1112, 34, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1113, 34, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1114, 34, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1115, 34, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1116, 34, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1117, 34, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1118, 34, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1119, 34, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1120, 34, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1121, 34, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1122, 34, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1123, 35, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1124, 35, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1125, 35, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14');
INSERT INTO `asset_categories` (`id`, `company_id`, `name`, `type`, `description`, `created_at`) VALUES
(1126, 35, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1127, 35, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1128, 35, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1129, 35, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1130, 35, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1131, 35, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1132, 35, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1133, 35, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1134, 35, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1135, 35, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1136, 35, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1137, 35, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1138, 35, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1139, 35, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1140, 35, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1141, 35, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1142, 35, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1143, 35, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1144, 35, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1145, 35, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1146, 35, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1147, 35, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1148, 35, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1149, 35, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1150, 35, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1151, 35, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1152, 35, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1153, 35, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1154, 35, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1155, 35, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1156, 36, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1157, 36, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1158, 36, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1159, 36, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1160, 36, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1161, 36, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1162, 36, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1163, 36, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1164, 36, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1165, 36, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1166, 36, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1167, 36, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1168, 36, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1169, 36, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1170, 36, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1171, 36, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1172, 36, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1173, 36, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1174, 36, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1175, 36, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1176, 36, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1177, 36, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1178, 36, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1179, 36, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1180, 36, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1181, 36, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1182, 36, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1183, 36, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1184, 36, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1185, 36, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1186, 36, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1187, 36, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1188, 36, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1189, 37, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1190, 37, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1191, 37, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1192, 37, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1193, 37, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1194, 37, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1195, 37, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1196, 37, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1197, 37, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1198, 37, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1199, 37, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1200, 37, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1201, 37, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1202, 37, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1203, 37, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1204, 37, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1205, 37, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1206, 37, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1207, 37, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1208, 37, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1209, 37, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1210, 37, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1211, 37, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1212, 37, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1213, 37, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1214, 37, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1215, 37, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1216, 37, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1217, 37, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1218, 37, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1219, 37, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1220, 37, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1221, 37, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1222, 38, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1223, 38, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1224, 38, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1225, 38, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1226, 38, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1227, 38, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1228, 38, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1229, 38, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1230, 38, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1231, 38, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1232, 38, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1233, 38, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1234, 38, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1235, 38, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1236, 38, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1237, 38, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1238, 38, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1239, 38, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1240, 38, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1241, 38, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1242, 38, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1243, 38, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1244, 38, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1245, 38, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1246, 38, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1247, 38, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1248, 38, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1249, 38, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1250, 38, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1251, 38, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1252, 38, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1253, 38, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1254, 38, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1255, 39, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1256, 39, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1257, 39, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:14'),
(1258, 39, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:14'),
(1259, 39, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:14'),
(1260, 39, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:14'),
(1261, 39, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:14'),
(1262, 39, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:14'),
(1263, 39, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:14'),
(1264, 39, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:14'),
(1265, 39, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:14'),
(1266, 39, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:14'),
(1267, 39, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:14'),
(1268, 39, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:14'),
(1269, 39, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:14'),
(1270, 39, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:14'),
(1271, 39, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:14'),
(1272, 39, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:14'),
(1273, 39, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:14'),
(1274, 39, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:14'),
(1275, 39, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:14'),
(1276, 39, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:14'),
(1277, 39, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:14'),
(1278, 39, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:14'),
(1279, 39, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:14'),
(1280, 39, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:14'),
(1281, 39, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:14'),
(1282, 39, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:14'),
(1283, 39, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:14'),
(1284, 39, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:14'),
(1285, 39, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:14'),
(1286, 39, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:14'),
(1287, 39, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:14'),
(1288, 40, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:14'),
(1289, 40, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:14'),
(1290, 40, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1291, 40, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1292, 40, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1293, 40, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1294, 40, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1295, 40, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1296, 40, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1297, 40, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1298, 40, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1299, 40, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1300, 40, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1301, 40, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1302, 40, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1303, 40, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1304, 40, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1305, 40, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1306, 40, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1307, 40, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1308, 40, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1309, 40, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1310, 40, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1311, 40, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1312, 40, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1313, 40, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1314, 40, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1315, 40, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1316, 40, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1317, 40, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1318, 40, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1319, 40, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1320, 40, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1321, 41, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1322, 41, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1323, 41, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1324, 41, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1325, 41, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1326, 41, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1327, 41, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1328, 41, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1329, 41, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1330, 41, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1331, 41, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1332, 41, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1333, 41, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1334, 41, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1335, 41, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1336, 41, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1337, 41, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1338, 41, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1339, 41, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1340, 41, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1341, 41, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1342, 41, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1343, 41, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1344, 41, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1345, 41, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1346, 41, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1347, 41, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1348, 41, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1349, 41, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1350, 41, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1351, 41, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1352, 41, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1353, 41, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1354, 42, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1355, 42, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1356, 42, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1357, 42, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1358, 42, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1359, 42, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1360, 42, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1361, 42, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1362, 42, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1363, 42, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1364, 42, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1365, 42, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1366, 42, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1367, 42, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1368, 42, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1369, 42, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1370, 42, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1371, 42, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1372, 42, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1373, 42, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1374, 42, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1375, 42, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1376, 42, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1377, 42, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1378, 42, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1379, 42, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1380, 42, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1381, 42, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1382, 42, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1383, 42, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1384, 42, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1385, 42, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1386, 42, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1387, 43, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1388, 43, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1389, 43, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1390, 43, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1391, 43, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1392, 43, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1393, 43, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1394, 43, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1395, 43, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1396, 43, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1397, 43, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1398, 43, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1399, 43, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1400, 43, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1401, 43, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1402, 43, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1403, 43, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1404, 43, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1405, 43, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1406, 43, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1407, 43, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1408, 43, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1409, 43, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1410, 43, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1411, 43, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1412, 43, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1413, 43, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1414, 43, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1415, 43, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1416, 43, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1417, 43, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1418, 43, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1419, 43, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1420, 44, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1421, 44, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1422, 44, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1423, 44, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1424, 44, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1425, 44, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1426, 44, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1427, 44, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1428, 44, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1429, 44, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1430, 44, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1431, 44, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1432, 44, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1433, 44, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1434, 44, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1435, 44, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1436, 44, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1437, 44, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1438, 44, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1439, 44, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1440, 44, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1441, 44, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1442, 44, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1443, 44, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1444, 44, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1445, 44, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1446, 44, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1447, 44, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1448, 44, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1449, 44, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1450, 44, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1451, 44, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1452, 44, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1453, 45, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1454, 45, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1455, 45, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1456, 45, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1457, 45, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1458, 45, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1459, 45, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1460, 45, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1461, 45, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1462, 45, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1463, 45, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1464, 45, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1465, 45, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1466, 45, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1467, 45, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1468, 45, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1469, 45, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1470, 45, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1471, 45, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1472, 45, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1473, 45, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1474, 45, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1475, 45, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1476, 45, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1477, 45, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1478, 45, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1479, 45, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1480, 45, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1481, 45, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1482, 45, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1483, 45, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1484, 45, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1485, 45, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1486, 46, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1487, 46, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1488, 46, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1489, 46, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1490, 46, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1491, 46, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1492, 46, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1493, 46, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1494, 46, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1495, 46, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1496, 46, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1497, 46, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1498, 46, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1499, 46, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1500, 46, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1501, 46, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1502, 46, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1503, 46, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1504, 46, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1505, 46, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1506, 46, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1507, 46, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1508, 46, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1509, 46, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1510, 46, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1511, 46, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1512, 46, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1513, 46, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1514, 46, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1515, 46, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1516, 46, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1517, 46, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1518, 46, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1519, 47, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1520, 47, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1521, 47, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1522, 47, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1523, 47, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1524, 47, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1525, 47, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1526, 47, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1527, 47, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1528, 47, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1529, 47, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1530, 47, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1531, 47, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1532, 47, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1533, 47, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1534, 47, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1535, 47, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1536, 47, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1537, 47, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1538, 47, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1539, 47, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1540, 47, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1541, 47, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1542, 47, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1543, 47, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1544, 47, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1545, 47, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1546, 47, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1547, 47, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1548, 47, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1549, 47, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1550, 47, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1551, 47, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1552, 48, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1553, 48, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1554, 48, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1555, 48, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1556, 48, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1557, 48, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1558, 48, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1559, 48, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1560, 48, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1561, 48, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1562, 48, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1563, 48, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1564, 48, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1565, 48, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1566, 48, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1567, 48, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1568, 48, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1569, 48, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1570, 48, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1571, 48, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1572, 48, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1573, 48, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1574, 48, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1575, 48, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1576, 48, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1577, 48, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1578, 48, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1579, 48, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1580, 48, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1581, 48, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1582, 48, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1583, 48, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1584, 48, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1585, 49, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1586, 49, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1587, 49, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1588, 49, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1589, 49, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1590, 49, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1591, 49, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1592, 49, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1593, 49, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1594, 49, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1595, 49, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1596, 49, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1597, 49, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1598, 49, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:15'),
(1599, 49, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:15'),
(1600, 49, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:15'),
(1601, 49, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:15'),
(1602, 49, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:15'),
(1603, 49, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:15'),
(1604, 49, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:15'),
(1605, 49, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:15'),
(1606, 49, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:15'),
(1607, 49, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:15'),
(1608, 49, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:15'),
(1609, 49, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:15'),
(1610, 49, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:15'),
(1611, 49, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:15'),
(1612, 49, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:15'),
(1613, 49, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:15'),
(1614, 49, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:15'),
(1615, 49, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:15'),
(1616, 49, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:15'),
(1617, 49, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:15'),
(1618, 50, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:15'),
(1619, 50, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:15'),
(1620, 50, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:15'),
(1621, 50, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:15'),
(1622, 50, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:15'),
(1623, 50, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:15'),
(1624, 50, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:15'),
(1625, 50, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:15'),
(1626, 50, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:15'),
(1627, 50, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:15'),
(1628, 50, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:15'),
(1629, 50, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:15'),
(1630, 50, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:15'),
(1631, 50, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:16'),
(1632, 50, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:16'),
(1633, 50, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:16'),
(1634, 50, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:16'),
(1635, 50, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:16'),
(1636, 50, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:16'),
(1637, 50, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:16'),
(1638, 50, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:16'),
(1639, 50, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:16'),
(1640, 50, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:16'),
(1641, 50, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:16'),
(1642, 50, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:16'),
(1643, 50, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:16'),
(1644, 50, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:16'),
(1645, 50, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:16'),
(1646, 50, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:16'),
(1647, 50, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:16'),
(1648, 50, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:16'),
(1649, 50, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:16'),
(1650, 50, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:16'),
(1651, 51, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:16'),
(1652, 51, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:16'),
(1653, 51, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:16'),
(1654, 51, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:16'),
(1655, 51, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:16'),
(1656, 51, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:16'),
(1657, 51, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:16'),
(1658, 51, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:16'),
(1659, 51, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:16'),
(1660, 51, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:16'),
(1661, 51, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:16'),
(1662, 51, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:16'),
(1663, 51, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:16'),
(1664, 51, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:16'),
(1665, 51, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:16'),
(1666, 51, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:16'),
(1667, 51, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:16'),
(1668, 51, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:16'),
(1669, 51, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:16'),
(1670, 51, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:16'),
(1671, 51, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:16'),
(1672, 51, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:16'),
(1673, 51, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:16'),
(1674, 51, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:16'),
(1675, 51, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:16'),
(1676, 51, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:16'),
(1677, 51, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:16'),
(1678, 51, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:16'),
(1679, 51, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:16');
INSERT INTO `asset_categories` (`id`, `company_id`, `name`, `type`, `description`, `created_at`) VALUES
(1680, 51, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:16'),
(1681, 51, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:16'),
(1682, 51, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:16'),
(1683, 51, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:16'),
(1684, 52, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:16'),
(1685, 52, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:16'),
(1686, 52, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:16'),
(1687, 52, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:16'),
(1688, 52, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:16'),
(1689, 52, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:16'),
(1690, 52, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:16'),
(1691, 52, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:16'),
(1692, 52, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:16'),
(1693, 52, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:16'),
(1694, 52, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:16'),
(1695, 52, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:16'),
(1696, 52, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:16'),
(1697, 52, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:16'),
(1698, 52, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:16'),
(1699, 52, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:16'),
(1700, 52, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:16'),
(1701, 52, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:16'),
(1702, 52, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:16'),
(1703, 52, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:16'),
(1704, 52, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:16'),
(1705, 52, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:16'),
(1706, 52, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:16'),
(1707, 52, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:16'),
(1708, 52, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:16'),
(1709, 52, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:16'),
(1710, 52, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:16'),
(1711, 52, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:16'),
(1712, 52, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:16'),
(1713, 52, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:16'),
(1714, 52, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:16'),
(1715, 52, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:16'),
(1716, 52, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:16'),
(1717, 53, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:16'),
(1718, 53, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:16'),
(1719, 53, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:16'),
(1720, 53, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:16'),
(1721, 53, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:16'),
(1722, 53, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:16'),
(1723, 53, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:16'),
(1724, 53, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:16'),
(1725, 53, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:16'),
(1726, 53, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:16'),
(1727, 53, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:16'),
(1728, 53, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:16'),
(1729, 53, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:16'),
(1730, 53, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:16'),
(1731, 53, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:16'),
(1732, 53, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:16'),
(1733, 53, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:16'),
(1734, 53, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:16'),
(1735, 53, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:16'),
(1736, 53, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:16'),
(1737, 53, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:16'),
(1738, 53, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:16'),
(1739, 53, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:16'),
(1740, 53, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:16'),
(1741, 53, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:16'),
(1742, 53, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:16'),
(1743, 53, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:16'),
(1744, 53, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:16'),
(1745, 53, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:16'),
(1746, 53, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:16'),
(1747, 53, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:16'),
(1748, 53, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:16'),
(1749, 53, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:16'),
(1750, 54, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:16'),
(1751, 54, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:16'),
(1752, 54, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:16'),
(1753, 54, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:16'),
(1754, 54, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:16'),
(1755, 54, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:16'),
(1756, 54, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:16'),
(1757, 54, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:16'),
(1758, 54, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:16'),
(1759, 54, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:16'),
(1760, 54, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:16'),
(1761, 54, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:16'),
(1762, 54, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:16'),
(1763, 54, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:16'),
(1764, 54, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:16'),
(1765, 54, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:16'),
(1766, 54, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:16'),
(1767, 54, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:16'),
(1768, 54, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:16'),
(1769, 54, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:16'),
(1770, 54, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:16'),
(1771, 54, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:16'),
(1772, 54, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:16'),
(1773, 54, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:16'),
(1774, 54, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:16'),
(1775, 54, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:16'),
(1776, 54, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:16'),
(1777, 54, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:16'),
(1778, 54, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:16'),
(1779, 54, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:16'),
(1780, 54, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:16'),
(1781, 54, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:16'),
(1782, 54, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:16'),
(1783, 55, 'Laptop', 'Hardware', 'Portable computer', '2026-03-06 16:42:16'),
(1784, 55, 'Desktop', 'Hardware', 'Desktop computer', '2026-03-06 16:42:16'),
(1785, 55, 'Monitor', 'Hardware', 'Display monitor', '2026-03-06 16:42:16'),
(1786, 55, 'Keyboard', 'Hardware', 'Input keyboard', '2026-03-06 16:42:16'),
(1787, 55, 'Mouse', 'Hardware', 'Input mouse', '2026-03-06 16:42:16'),
(1788, 55, 'Docking Station', 'Hardware', 'Laptop docking station', '2026-03-06 16:42:16'),
(1789, 55, 'Headset', 'Hardware', 'Audio headset', '2026-03-06 16:42:16'),
(1790, 55, 'Webcam', 'Hardware', 'Video camera', '2026-03-06 16:42:16'),
(1791, 55, 'Mobile Phone', 'Hardware', 'Company mobile phone', '2026-03-06 16:42:16'),
(1792, 55, 'Tablet', 'Hardware', 'Tablet device', '2026-03-06 16:42:16'),
(1793, 55, 'External Hard Drive', 'Hardware', 'External storage device', '2026-03-06 16:42:16'),
(1794, 55, 'Power Adapter', 'Hardware', 'Power adapter/charger', '2026-03-06 16:42:16'),
(1795, 55, 'Windows License', 'Software', 'Microsoft Windows OS license', '2026-03-06 16:42:16'),
(1796, 55, 'macOS License', 'Software', 'Apple macOS license', '2026-03-06 16:42:16'),
(1797, 55, 'Microsoft 365 License', 'Software', 'Microsoft 365 subscription', '2026-03-06 16:42:16'),
(1798, 55, 'Adobe License', 'Software', 'Adobe Creative Suite license', '2026-03-06 16:42:16'),
(1799, 55, 'IDE / Developer Tool License', 'Software', 'Development environment license', '2026-03-06 16:42:16'),
(1800, 55, 'VPN Client License', 'Software', 'VPN client software license', '2026-03-06 16:42:16'),
(1801, 55, 'SaaS Application Account', 'Access', 'SaaS platform account', '2026-03-06 16:42:16'),
(1802, 55, 'Company Email Account', 'Access', 'Corporate email account', '2026-03-06 16:42:16'),
(1803, 55, 'SSO / Directory Account', 'Access', 'Single sign-on / directory access', '2026-03-06 16:42:16'),
(1804, 55, 'VPN Access', 'Access', 'VPN network access credentials', '2026-03-06 16:42:16'),
(1805, 55, 'Git Repository Access', 'Access', 'Source code repository access', '2026-03-06 16:42:16'),
(1806, 55, 'Cloud Console Access', 'Access', 'Cloud platform console access', '2026-03-06 16:42:16'),
(1807, 55, 'ERP / Internal System Access', 'Access', 'Internal system access', '2026-03-06 16:42:16'),
(1808, 55, 'Smart Card', 'Security', 'Smart card for authentication', '2026-03-06 16:42:16'),
(1809, 55, 'RFID Access Card', 'Security', 'RFID-based access card', '2026-03-06 16:42:16'),
(1810, 55, 'Biometric Token', 'Security', 'Biometric authentication token', '2026-03-06 16:42:16'),
(1811, 55, 'Hardware Security Key', 'Security', 'Physical security key (e.g., YubiKey)', '2026-03-06 16:42:16'),
(1812, 55, 'Employee ID Card', 'Security', 'Company ID badge', '2026-03-06 16:42:16'),
(1813, 55, 'Locker Key', 'Security', 'Physical locker key', '2026-03-06 16:42:16'),
(1814, 55, 'Company Vehicle', 'Other', 'Company-owned vehicle', '2026-03-06 16:42:16'),
(1815, 55, 'Specialized Tools / Instruments', 'Other', 'Specialized work tools', '2026-03-06 16:42:16'),
(1816, 1, 'Laptops', 'Hardware', 'Standard Laptops', '2026-03-06 16:42:16'),
(1817, 1, 'Desktops', 'Hardware', 'Standard Desktops', '2026-03-06 16:42:16'),
(1818, 1, 'Monitors', 'Hardware', 'Standard Monitors', '2026-03-06 16:42:16'),
(1819, 1, 'Mobile Phones', 'Hardware', 'Standard Mobile Phones', '2026-03-06 16:42:16'),
(1820, 1, 'Software Licenses', 'Software', 'Standard Software Licenses', '2026-03-06 16:42:16'),
(1821, 1, 'Access Cards', 'Access', 'Standard Access Cards', '2026-03-06 16:42:16'),
(1822, 2, 'Laptops', 'Hardware', 'Standard Laptops', '2026-03-06 16:42:16'),
(1823, 2, 'Desktops', 'Hardware', 'Standard Desktops', '2026-03-06 16:42:16'),
(1824, 2, 'Monitors', 'Hardware', 'Standard Monitors', '2026-03-06 16:42:16'),
(1825, 2, 'Mobile Phones', 'Hardware', 'Standard Mobile Phones', '2026-03-06 16:42:16'),
(1826, 2, 'Software Licenses', 'Software', 'Standard Software Licenses', '2026-03-06 16:42:16'),
(1827, 2, 'Access Cards', 'Access', 'Standard Access Cards', '2026-03-06 16:42:16'),
(1828, 3, 'Laptops', 'Hardware', 'Standard Laptops', '2026-03-06 16:42:16'),
(1829, 3, 'Desktops', 'Hardware', 'Standard Desktops', '2026-03-06 16:42:16'),
(1830, 3, 'Monitors', 'Hardware', 'Standard Monitors', '2026-03-06 16:42:16'),
(1831, 3, 'Mobile Phones', 'Hardware', 'Standard Mobile Phones', '2026-03-06 16:42:16'),
(1832, 3, 'Software Licenses', 'Software', 'Standard Software Licenses', '2026-03-06 16:42:16'),
(1833, 3, 'Access Cards', 'Access', 'Standard Access Cards', '2026-03-06 16:42:16');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `date` date NOT NULL,
  `check_in` time DEFAULT NULL,
  `check_out` time DEFAULT NULL,
  `status` enum('present','absent','leave','half-day','holiday') DEFAULT 'present',
  `auth_method` enum('rfid','fingerprint','face_id','manual') DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `employee_id`, `device_id`, `date`, `check_in`, `check_out`, `status`, `auth_method`, `remarks`, `created_at`) VALUES
(1, 1, NULL, '2026-02-04', '09:23:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(2, 56, NULL, '2026-02-04', '09:57:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(3, 57, NULL, '2026-02-04', '09:16:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(4, 58, NULL, '2026-02-04', '09:06:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(5, 60, NULL, '2026-02-04', '09:45:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(6, 63, NULL, '2026-02-04', '09:46:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(7, 64, NULL, '2026-02-04', '09:29:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(8, 78, NULL, '2026-02-04', '09:02:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(9, 90, NULL, '2026-02-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(10, 92, NULL, '2026-02-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(11, 97, NULL, '2026-02-04', '09:58:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(12, 62, NULL, '2026-02-04', '09:55:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(13, 65, NULL, '2026-02-04', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(14, 66, NULL, '2026-02-04', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(15, 68, NULL, '2026-02-04', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(16, 69, NULL, '2026-02-04', '09:23:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(17, 75, NULL, '2026-02-04', '09:13:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(18, 76, NULL, '2026-02-04', '09:26:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(19, 77, NULL, '2026-02-04', '09:26:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(20, 83, NULL, '2026-02-04', '09:12:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(21, 94, NULL, '2026-02-04', '09:39:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(22, 98, NULL, '2026-02-04', '09:33:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(23, 103, NULL, '2026-02-04', '09:05:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(24, 106, NULL, '2026-02-04', '09:50:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(25, 67, NULL, '2026-02-04', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(26, 70, NULL, '2026-02-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(27, 71, NULL, '2026-02-04', '09:14:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(28, 74, NULL, '2026-02-04', '09:25:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(29, 81, NULL, '2026-02-04', '09:44:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(30, 82, NULL, '2026-02-04', '09:54:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(31, 84, NULL, '2026-02-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(32, 86, NULL, '2026-02-04', '09:05:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(33, 88, NULL, '2026-02-04', '09:54:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(34, 96, NULL, '2026-02-04', '09:29:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(35, 101, NULL, '2026-02-04', '09:49:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(36, 110, NULL, '2026-02-04', '09:05:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(37, 61, NULL, '2026-02-04', '09:51:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(38, 79, NULL, '2026-02-04', '09:02:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(39, 85, NULL, '2026-02-04', '09:57:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(40, 91, NULL, '2026-02-04', '09:58:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(41, 95, NULL, '2026-02-04', '09:07:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(42, 100, NULL, '2026-02-04', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(43, 102, NULL, '2026-02-04', '09:03:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(44, 105, NULL, '2026-02-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(45, 108, NULL, '2026-02-04', '09:22:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(46, 59, NULL, '2026-02-04', '09:02:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(47, 72, NULL, '2026-02-04', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(48, 73, NULL, '2026-02-04', '09:59:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(49, 80, NULL, '2026-02-04', '09:47:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(50, 87, NULL, '2026-02-04', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(51, 89, NULL, '2026-02-04', '09:54:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(52, 93, NULL, '2026-02-04', '09:28:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(53, 99, NULL, '2026-02-04', '09:27:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(54, 104, NULL, '2026-02-04', '09:30:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(55, 107, NULL, '2026-02-04', '09:20:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(56, 109, NULL, '2026-02-04', '09:42:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(57, 1, NULL, '2026-02-05', '09:14:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(58, 56, NULL, '2026-02-05', '09:54:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(59, 57, NULL, '2026-02-05', '09:28:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(60, 58, NULL, '2026-02-05', '09:30:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(61, 60, NULL, '2026-02-05', '09:22:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(62, 63, NULL, '2026-02-05', '09:25:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(63, 64, NULL, '2026-02-05', '09:53:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(64, 78, NULL, '2026-02-05', '09:21:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(65, 90, NULL, '2026-02-05', '09:37:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(66, 92, NULL, '2026-02-05', '09:20:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(67, 97, NULL, '2026-02-05', '09:47:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(68, 62, NULL, '2026-02-05', '09:23:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(69, 65, NULL, '2026-02-05', '09:10:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(70, 66, NULL, '2026-02-05', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(71, 68, NULL, '2026-02-05', '09:52:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(72, 69, NULL, '2026-02-05', '09:21:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(73, 75, NULL, '2026-02-05', '09:22:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(74, 76, NULL, '2026-02-05', '09:09:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(75, 77, NULL, '2026-02-05', '09:59:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(76, 83, NULL, '2026-02-05', '09:36:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(77, 94, NULL, '2026-02-05', '09:23:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(78, 98, NULL, '2026-02-05', '09:20:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(79, 103, NULL, '2026-02-05', '09:15:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(80, 106, NULL, '2026-02-05', '09:32:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(81, 67, NULL, '2026-02-05', '09:12:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(82, 70, NULL, '2026-02-05', '09:32:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(83, 71, NULL, '2026-02-05', '09:27:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(84, 74, NULL, '2026-02-05', '09:03:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(85, 81, NULL, '2026-02-05', '09:45:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(86, 82, NULL, '2026-02-05', '09:51:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(87, 84, NULL, '2026-02-05', '09:23:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(88, 86, NULL, '2026-02-05', '09:53:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(89, 88, NULL, '2026-02-05', '09:38:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(90, 96, NULL, '2026-02-05', '09:39:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(91, 101, NULL, '2026-02-05', '09:33:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(92, 110, NULL, '2026-02-05', '09:25:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(93, 61, NULL, '2026-02-05', '09:57:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(94, 79, NULL, '2026-02-05', '09:45:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(95, 85, NULL, '2026-02-05', '09:30:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(96, 91, NULL, '2026-02-05', '09:45:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(97, 95, NULL, '2026-02-05', '09:54:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(98, 100, NULL, '2026-02-05', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(99, 102, NULL, '2026-02-05', '09:16:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(100, 105, NULL, '2026-02-05', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(101, 108, NULL, '2026-02-05', '09:43:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(102, 59, NULL, '2026-02-05', '09:09:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(103, 72, NULL, '2026-02-05', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(104, 73, NULL, '2026-02-05', '09:27:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(105, 80, NULL, '2026-02-05', '09:51:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(106, 87, NULL, '2026-02-05', '09:22:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(107, 89, NULL, '2026-02-05', '09:15:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(108, 93, NULL, '2026-02-05', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(109, 99, NULL, '2026-02-05', '09:05:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(110, 104, NULL, '2026-02-05', '09:52:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(111, 107, NULL, '2026-02-05', '09:11:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(112, 109, NULL, '2026-02-05', '09:57:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(113, 1, NULL, '2026-02-06', '09:31:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(114, 56, NULL, '2026-02-06', '09:55:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(115, 57, NULL, '2026-02-06', '09:50:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(116, 58, NULL, '2026-02-06', '09:59:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(117, 60, NULL, '2026-02-06', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(118, 63, NULL, '2026-02-06', '09:22:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(119, 64, NULL, '2026-02-06', '09:50:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(120, 78, NULL, '2026-02-06', '09:26:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(121, 90, NULL, '2026-02-06', '09:55:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(122, 92, NULL, '2026-02-06', '09:49:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(123, 97, NULL, '2026-02-06', '09:42:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(124, 62, NULL, '2026-02-06', '09:42:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(125, 65, NULL, '2026-02-06', '09:13:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(126, 66, NULL, '2026-02-06', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(127, 68, NULL, '2026-02-06', '09:12:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(128, 69, NULL, '2026-02-06', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(129, 75, NULL, '2026-02-06', '09:21:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(130, 76, NULL, '2026-02-06', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(131, 77, NULL, '2026-02-06', '09:04:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(132, 83, NULL, '2026-02-06', '09:56:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(133, 94, NULL, '2026-02-06', '09:18:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(134, 98, NULL, '2026-02-06', '09:48:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(135, 103, NULL, '2026-02-06', '09:21:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(136, 106, NULL, '2026-02-06', '09:16:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(137, 67, NULL, '2026-02-06', '09:07:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(138, 70, NULL, '2026-02-06', '09:07:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(139, 71, NULL, '2026-02-06', '09:48:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(140, 74, NULL, '2026-02-06', '09:27:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(141, 81, NULL, '2026-02-06', '09:05:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(142, 82, NULL, '2026-02-06', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(143, 84, NULL, '2026-02-06', '09:44:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(144, 86, NULL, '2026-02-06', '09:30:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(145, 88, NULL, '2026-02-06', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(146, 96, NULL, '2026-02-06', '09:15:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(147, 101, NULL, '2026-02-06', '09:36:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(148, 110, NULL, '2026-02-06', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(149, 61, NULL, '2026-02-06', '09:02:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(150, 79, NULL, '2026-02-06', '09:45:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(151, 85, NULL, '2026-02-06', '09:56:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(152, 91, NULL, '2026-02-06', '09:16:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(153, 95, NULL, '2026-02-06', '09:12:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(154, 100, NULL, '2026-02-06', '09:03:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(155, 102, NULL, '2026-02-06', '09:55:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(156, 105, NULL, '2026-02-06', '09:53:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(157, 108, NULL, '2026-02-06', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(158, 59, NULL, '2026-02-06', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(159, 72, NULL, '2026-02-06', '09:08:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(160, 73, NULL, '2026-02-06', '09:16:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(161, 80, NULL, '2026-02-06', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(162, 87, NULL, '2026-02-06', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(163, 89, NULL, '2026-02-06', '09:31:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(164, 93, NULL, '2026-02-06', '09:58:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(165, 99, NULL, '2026-02-06', '09:10:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(166, 104, NULL, '2026-02-06', '09:57:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(167, 107, NULL, '2026-02-06', '09:58:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(168, 109, NULL, '2026-02-06', '09:12:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(169, 1, NULL, '2026-02-07', '09:03:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(170, 56, NULL, '2026-02-07', '09:48:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(171, 57, NULL, '2026-02-07', '09:51:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(172, 58, NULL, '2026-02-07', '09:58:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(173, 60, NULL, '2026-02-07', '09:35:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(174, 63, NULL, '2026-02-07', '09:36:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(175, 64, NULL, '2026-02-07', '09:01:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(176, 78, NULL, '2026-02-07', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(177, 90, NULL, '2026-02-07', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(178, 92, NULL, '2026-02-07', '09:44:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(179, 97, NULL, '2026-02-07', '09:21:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(180, 62, NULL, '2026-02-07', '09:42:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(181, 65, NULL, '2026-02-07', '09:55:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(182, 66, NULL, '2026-02-07', '09:16:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(183, 68, NULL, '2026-02-07', '09:42:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(184, 69, NULL, '2026-02-07', '09:52:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(185, 75, NULL, '2026-02-07', '09:47:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(186, 76, NULL, '2026-02-07', '09:46:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(187, 77, NULL, '2026-02-07', '09:58:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(188, 83, NULL, '2026-02-07', '09:28:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(189, 94, NULL, '2026-02-07', '09:31:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(190, 98, NULL, '2026-02-07', '09:30:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(191, 103, NULL, '2026-02-07', '09:36:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(192, 106, NULL, '2026-02-07', '09:32:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(193, 67, NULL, '2026-02-07', '09:40:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(194, 70, NULL, '2026-02-07', '09:47:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(195, 71, NULL, '2026-02-07', '09:24:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(196, 74, NULL, '2026-02-07', '09:28:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(197, 81, NULL, '2026-02-07', '09:29:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(198, 82, NULL, '2026-02-07', '09:44:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(199, 84, NULL, '2026-02-07', '09:04:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(200, 86, NULL, '2026-02-07', '09:09:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(201, 88, NULL, '2026-02-07', '09:50:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(202, 96, NULL, '2026-02-07', '09:34:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(203, 101, NULL, '2026-02-07', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(204, 110, NULL, '2026-02-07', '09:42:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(205, 61, NULL, '2026-02-07', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(206, 79, NULL, '2026-02-07', '09:52:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(207, 85, NULL, '2026-02-07', '09:42:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(208, 91, NULL, '2026-02-07', '09:32:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(209, 95, NULL, '2026-02-07', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(210, 100, NULL, '2026-02-07', '09:42:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(211, 102, NULL, '2026-02-07', '09:10:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(212, 105, NULL, '2026-02-07', '09:34:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(213, 108, NULL, '2026-02-07', '09:50:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(214, 59, NULL, '2026-02-07', '09:58:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(215, 72, NULL, '2026-02-07', '09:11:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(216, 73, NULL, '2026-02-07', '09:15:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(217, 80, NULL, '2026-02-07', '09:49:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(218, 87, NULL, '2026-02-07', '09:30:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(219, 89, NULL, '2026-02-07', '09:58:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(220, 93, NULL, '2026-02-07', '09:33:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(221, 99, NULL, '2026-02-07', '09:01:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(222, 104, NULL, '2026-02-07', '09:36:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(223, 107, NULL, '2026-02-07', '09:17:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(224, 109, NULL, '2026-02-07', '09:41:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(225, 1, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(226, 56, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(227, 57, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(228, 58, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(229, 60, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(230, 63, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(231, 64, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(232, 78, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(233, 90, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(234, 92, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(235, 97, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(236, 62, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(237, 65, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(238, 66, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(239, 68, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(240, 69, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(241, 75, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(242, 76, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(243, 77, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(244, 83, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(245, 94, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(246, 98, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(247, 103, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(248, 106, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(249, 67, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(250, 70, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(251, 71, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(252, 74, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(253, 81, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(254, 82, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(255, 84, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(256, 86, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(257, 88, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(258, 96, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(259, 101, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(260, 110, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(261, 61, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(262, 79, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(263, 85, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(264, 91, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(265, 95, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(266, 100, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(267, 102, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(268, 105, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(269, 108, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(270, 59, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(271, 72, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(272, 73, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(273, 80, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(274, 87, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(275, 89, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(276, 93, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(277, 99, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(278, 104, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(279, 107, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(280, 109, NULL, '2026-02-08', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(281, 1, NULL, '2026-02-09', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(282, 56, NULL, '2026-02-09', '09:11:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(283, 57, NULL, '2026-02-09', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(284, 58, NULL, '2026-02-09', '09:05:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(285, 60, NULL, '2026-02-09', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(286, 63, NULL, '2026-02-09', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(287, 64, NULL, '2026-02-09', '09:02:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(288, 78, NULL, '2026-02-09', '09:02:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(289, 90, NULL, '2026-02-09', '09:43:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(290, 92, NULL, '2026-02-09', '09:09:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(291, 97, NULL, '2026-02-09', '09:14:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(292, 62, NULL, '2026-02-09', '09:21:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(293, 65, NULL, '2026-02-09', '09:06:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(294, 66, NULL, '2026-02-09', '09:53:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(295, 68, NULL, '2026-02-09', '09:07:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(296, 69, NULL, '2026-02-09', '09:51:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(297, 75, NULL, '2026-02-09', '09:28:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(298, 76, NULL, '2026-02-09', '09:30:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(299, 77, NULL, '2026-02-09', '09:03:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(300, 83, NULL, '2026-02-09', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(301, 94, NULL, '2026-02-09', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(302, 98, NULL, '2026-02-09', '09:45:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(303, 103, NULL, '2026-02-09', '09:01:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(304, 106, NULL, '2026-02-09', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(305, 67, NULL, '2026-02-09', '09:48:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(306, 70, NULL, '2026-02-09', '09:35:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(307, 71, NULL, '2026-02-09', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(308, 74, NULL, '2026-02-09', '09:06:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(309, 81, NULL, '2026-02-09', '09:57:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(310, 82, NULL, '2026-02-09', '09:33:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(311, 84, NULL, '2026-02-09', '09:30:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(312, 86, NULL, '2026-02-09', '09:39:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(313, 88, NULL, '2026-02-09', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(314, 96, NULL, '2026-02-09', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(315, 101, NULL, '2026-02-09', '09:44:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(316, 110, NULL, '2026-02-09', '09:28:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(317, 61, NULL, '2026-02-09', '09:58:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(318, 79, NULL, '2026-02-09', '09:51:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(319, 85, NULL, '2026-02-09', '09:11:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(320, 91, NULL, '2026-02-09', '09:17:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(321, 95, NULL, '2026-02-09', '09:50:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(322, 100, NULL, '2026-02-09', '09:35:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(323, 102, NULL, '2026-02-09', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(324, 105, NULL, '2026-02-09', '09:23:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(325, 108, NULL, '2026-02-09', '09:20:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(326, 59, NULL, '2026-02-09', '09:14:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(327, 72, NULL, '2026-02-09', '09:22:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(328, 73, NULL, '2026-02-09', '09:56:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(329, 80, NULL, '2026-02-09', '09:08:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(330, 87, NULL, '2026-02-09', '09:29:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(331, 89, NULL, '2026-02-09', '09:24:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(332, 93, NULL, '2026-02-09', '09:30:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(333, 99, NULL, '2026-02-09', '09:03:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(334, 104, NULL, '2026-02-09', '09:56:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(335, 107, NULL, '2026-02-09', '09:26:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(336, 109, NULL, '2026-02-09', '09:18:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(337, 1, NULL, '2026-02-10', '09:01:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(338, 56, NULL, '2026-02-10', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(339, 57, NULL, '2026-02-10', '09:15:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(340, 58, NULL, '2026-02-10', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(341, 60, NULL, '2026-02-10', '09:51:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(342, 63, NULL, '2026-02-10', '09:14:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(343, 64, NULL, '2026-02-10', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(344, 78, NULL, '2026-02-10', '09:24:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(345, 90, NULL, '2026-02-10', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(346, 92, NULL, '2026-02-10', '09:43:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(347, 97, NULL, '2026-02-10', '09:25:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(348, 62, NULL, '2026-02-10', '09:46:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(349, 65, NULL, '2026-02-10', '09:17:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(350, 66, NULL, '2026-02-10', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(351, 68, NULL, '2026-02-10', '09:03:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(352, 69, NULL, '2026-02-10', '09:29:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(353, 75, NULL, '2026-02-10', '09:29:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(354, 76, NULL, '2026-02-10', '09:01:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(355, 77, NULL, '2026-02-10', '09:50:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(356, 83, NULL, '2026-02-10', '09:46:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(357, 94, NULL, '2026-02-10', '09:49:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(358, 98, NULL, '2026-02-10', '09:29:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(359, 103, NULL, '2026-02-10', '09:59:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(360, 106, NULL, '2026-02-10', '09:29:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(361, 67, NULL, '2026-02-10', '09:50:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(362, 70, NULL, '2026-02-10', '09:22:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(363, 71, NULL, '2026-02-10', '09:05:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(364, 74, NULL, '2026-02-10', '09:40:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(365, 81, NULL, '2026-02-10', '09:45:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(366, 82, NULL, '2026-02-10', '09:22:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(367, 84, NULL, '2026-02-10', '09:01:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(368, 86, NULL, '2026-02-10', '09:24:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(369, 88, NULL, '2026-02-10', '09:56:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(370, 96, NULL, '2026-02-10', '09:29:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(371, 101, NULL, '2026-02-10', '09:31:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(372, 110, NULL, '2026-02-10', '09:23:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(373, 61, NULL, '2026-02-10', '09:52:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(374, 79, NULL, '2026-02-10', '09:35:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(375, 85, NULL, '2026-02-10', '09:15:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(376, 91, NULL, '2026-02-10', '09:07:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(377, 95, NULL, '2026-02-10', '09:11:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(378, 100, NULL, '2026-02-10', '09:55:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(379, 102, NULL, '2026-02-10', '09:41:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(380, 105, NULL, '2026-02-10', '09:35:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(381, 108, NULL, '2026-02-10', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(382, 59, NULL, '2026-02-10', '09:02:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(383, 72, NULL, '2026-02-10', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(384, 73, NULL, '2026-02-10', '09:42:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(385, 80, NULL, '2026-02-10', '09:10:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(386, 87, NULL, '2026-02-10', '09:51:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(387, 89, NULL, '2026-02-10', '09:23:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(388, 93, NULL, '2026-02-10', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(389, 99, NULL, '2026-02-10', '09:55:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(390, 104, NULL, '2026-02-10', '09:13:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(391, 107, NULL, '2026-02-10', '09:05:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(392, 109, NULL, '2026-02-10', '09:23:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(393, 1, NULL, '2026-02-11', '09:06:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(394, 56, NULL, '2026-02-11', '09:33:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(395, 57, NULL, '2026-02-11', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(396, 58, NULL, '2026-02-11', '09:27:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(397, 60, NULL, '2026-02-11', '09:04:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(398, 63, NULL, '2026-02-11', '09:48:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(399, 64, NULL, '2026-02-11', '09:16:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(400, 78, NULL, '2026-02-11', '09:45:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(401, 90, NULL, '2026-02-11', '09:09:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(402, 92, NULL, '2026-02-11', '09:44:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(403, 97, NULL, '2026-02-11', '09:24:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(404, 62, NULL, '2026-02-11', '09:43:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(405, 65, NULL, '2026-02-11', '09:32:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(406, 66, NULL, '2026-02-11', '09:09:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(407, 68, NULL, '2026-02-11', '09:33:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(408, 69, NULL, '2026-02-11', '09:28:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(409, 75, NULL, '2026-02-11', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(410, 76, NULL, '2026-02-11', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(411, 77, NULL, '2026-02-11', '09:47:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(412, 83, NULL, '2026-02-11', '09:51:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(413, 94, NULL, '2026-02-11', '09:17:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(414, 98, NULL, '2026-02-11', '09:46:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(415, 103, NULL, '2026-02-11', '09:57:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(416, 106, NULL, '2026-02-11', '09:28:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(417, 67, NULL, '2026-02-11', '09:36:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(418, 70, NULL, '2026-02-11', '09:28:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(419, 71, NULL, '2026-02-11', '09:37:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(420, 74, NULL, '2026-02-11', '09:20:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(421, 81, NULL, '2026-02-11', '09:32:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(422, 82, NULL, '2026-02-11', '09:44:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(423, 84, NULL, '2026-02-11', '09:47:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(424, 86, NULL, '2026-02-11', '09:08:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(425, 88, NULL, '2026-02-11', '09:12:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(426, 96, NULL, '2026-02-11', '09:34:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(427, 101, NULL, '2026-02-11', '09:22:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(428, 110, NULL, '2026-02-11', '09:01:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(429, 61, NULL, '2026-02-11', '09:10:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(430, 79, NULL, '2026-02-11', '09:45:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(431, 85, NULL, '2026-02-11', '09:50:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(432, 91, NULL, '2026-02-11', '09:59:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(433, 95, NULL, '2026-02-11', '09:11:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(434, 100, NULL, '2026-02-11', '09:18:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(435, 102, NULL, '2026-02-11', '09:19:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(436, 105, NULL, '2026-02-11', '09:44:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(437, 108, NULL, '2026-02-11', '09:33:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(438, 59, NULL, '2026-02-11', '09:26:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(439, 72, NULL, '2026-02-11', '09:33:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(440, 73, NULL, '2026-02-11', '09:32:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(441, 80, NULL, '2026-02-11', '09:59:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(442, 87, NULL, '2026-02-11', '09:22:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(443, 89, NULL, '2026-02-11', '09:54:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(444, 93, NULL, '2026-02-11', '09:47:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(445, 99, NULL, '2026-02-11', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(446, 104, NULL, '2026-02-11', '09:25:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(447, 107, NULL, '2026-02-11', '09:43:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(448, 109, NULL, '2026-02-11', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(449, 1, NULL, '2026-02-12', '09:31:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(450, 56, NULL, '2026-02-12', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(451, 57, NULL, '2026-02-12', '09:43:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(452, 58, NULL, '2026-02-12', '09:10:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(453, 60, NULL, '2026-02-12', '09:19:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(454, 63, NULL, '2026-02-12', '09:18:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(455, 64, NULL, '2026-02-12', '09:29:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(456, 78, NULL, '2026-02-12', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(457, 90, NULL, '2026-02-12', '09:59:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(458, 92, NULL, '2026-02-12', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(459, 97, NULL, '2026-02-12', '09:58:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(460, 62, NULL, '2026-02-12', '09:27:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(461, 65, NULL, '2026-02-12', '09:04:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(462, 66, NULL, '2026-02-12', '09:56:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(463, 68, NULL, '2026-02-12', '09:26:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(464, 69, NULL, '2026-02-12', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(465, 75, NULL, '2026-02-12', '09:59:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(466, 76, NULL, '2026-02-12', '09:08:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(467, 77, NULL, '2026-02-12', '09:36:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(468, 83, NULL, '2026-02-12', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(469, 94, NULL, '2026-02-12', '09:56:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(470, 98, NULL, '2026-02-12', '09:01:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(471, 103, NULL, '2026-02-12', '09:53:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(472, 106, NULL, '2026-02-12', '09:04:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(473, 67, NULL, '2026-02-12', '09:30:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(474, 70, NULL, '2026-02-12', '09:03:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(475, 71, NULL, '2026-02-12', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(476, 74, NULL, '2026-02-12', '09:35:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(477, 81, NULL, '2026-02-12', '09:09:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(478, 82, NULL, '2026-02-12', '09:33:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(479, 84, NULL, '2026-02-12', '09:30:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(480, 86, NULL, '2026-02-12', '09:58:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(481, 88, NULL, '2026-02-12', '09:24:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(482, 96, NULL, '2026-02-12', '09:58:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(483, 101, NULL, '2026-02-12', '09:58:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(484, 110, NULL, '2026-02-12', '09:01:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(485, 61, NULL, '2026-02-12', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(486, 79, NULL, '2026-02-12', '09:50:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18');
INSERT INTO `attendance` (`id`, `employee_id`, `device_id`, `date`, `check_in`, `check_out`, `status`, `auth_method`, `remarks`, `created_at`) VALUES
(487, 85, NULL, '2026-02-12', '09:22:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(488, 91, NULL, '2026-02-12', '09:50:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(489, 95, NULL, '2026-02-12', '09:13:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(490, 100, NULL, '2026-02-12', '09:57:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(491, 102, NULL, '2026-02-12', '09:07:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(492, 105, NULL, '2026-02-12', '09:12:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(493, 108, NULL, '2026-02-12', '09:50:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(494, 59, NULL, '2026-02-12', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(495, 72, NULL, '2026-02-12', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(496, 73, NULL, '2026-02-12', '09:41:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(497, 80, NULL, '2026-02-12', '09:06:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(498, 87, NULL, '2026-02-12', '09:08:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(499, 89, NULL, '2026-02-12', '09:54:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(500, 93, NULL, '2026-02-12', '09:59:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(501, 99, NULL, '2026-02-12', '09:20:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(502, 104, NULL, '2026-02-12', '09:31:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(503, 107, NULL, '2026-02-12', '09:14:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(504, 109, NULL, '2026-02-12', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(505, 1, NULL, '2026-02-13', '09:00:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(506, 56, NULL, '2026-02-13', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(507, 57, NULL, '2026-02-13', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(508, 58, NULL, '2026-02-13', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(509, 60, NULL, '2026-02-13', '09:12:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(510, 63, NULL, '2026-02-13', '09:14:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(511, 64, NULL, '2026-02-13', '09:16:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(512, 78, NULL, '2026-02-13', '09:46:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(513, 90, NULL, '2026-02-13', '09:08:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(514, 92, NULL, '2026-02-13', '09:40:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(515, 97, NULL, '2026-02-13', '09:00:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(516, 62, NULL, '2026-02-13', '09:00:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(517, 65, NULL, '2026-02-13', '09:29:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(518, 66, NULL, '2026-02-13', '09:09:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(519, 68, NULL, '2026-02-13', '09:07:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(520, 69, NULL, '2026-02-13', '09:06:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(521, 75, NULL, '2026-02-13', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(522, 76, NULL, '2026-02-13', '09:18:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(523, 77, NULL, '2026-02-13', '09:50:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(524, 83, NULL, '2026-02-13', '09:10:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(525, 94, NULL, '2026-02-13', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(526, 98, NULL, '2026-02-13', '09:57:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(527, 103, NULL, '2026-02-13', '09:26:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(528, 106, NULL, '2026-02-13', '09:41:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(529, 67, NULL, '2026-02-13', '09:44:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(530, 70, NULL, '2026-02-13', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(531, 71, NULL, '2026-02-13', '09:32:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(532, 74, NULL, '2026-02-13', '09:15:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(533, 81, NULL, '2026-02-13', '09:59:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(534, 82, NULL, '2026-02-13', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(535, 84, NULL, '2026-02-13', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(536, 86, NULL, '2026-02-13', '09:24:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(537, 88, NULL, '2026-02-13', '09:37:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(538, 96, NULL, '2026-02-13', '09:37:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(539, 101, NULL, '2026-02-13', '09:40:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(540, 110, NULL, '2026-02-13', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(541, 61, NULL, '2026-02-13', '09:39:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(542, 79, NULL, '2026-02-13', '09:31:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(543, 85, NULL, '2026-02-13', '09:23:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(544, 91, NULL, '2026-02-13', '09:05:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(545, 95, NULL, '2026-02-13', '09:55:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(546, 100, NULL, '2026-02-13', '09:40:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(547, 102, NULL, '2026-02-13', '09:55:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(548, 105, NULL, '2026-02-13', '09:39:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(549, 108, NULL, '2026-02-13', '09:10:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(550, 59, NULL, '2026-02-13', '09:02:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(551, 72, NULL, '2026-02-13', '09:57:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(552, 73, NULL, '2026-02-13', '09:31:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(553, 80, NULL, '2026-02-13', '09:12:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(554, 87, NULL, '2026-02-13', '09:16:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(555, 89, NULL, '2026-02-13', '09:20:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(556, 93, NULL, '2026-02-13', '09:02:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(557, 99, NULL, '2026-02-13', '09:13:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(558, 104, NULL, '2026-02-13', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(559, 107, NULL, '2026-02-13', '09:13:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(560, 109, NULL, '2026-02-13', '09:53:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(561, 1, NULL, '2026-02-14', '09:20:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(562, 56, NULL, '2026-02-14', '09:57:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(563, 57, NULL, '2026-02-14', '09:26:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(564, 58, NULL, '2026-02-14', '09:02:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(565, 60, NULL, '2026-02-14', '09:30:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(566, 63, NULL, '2026-02-14', '09:42:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(567, 64, NULL, '2026-02-14', '09:08:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(568, 78, NULL, '2026-02-14', '09:58:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(569, 90, NULL, '2026-02-14', '09:18:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(570, 92, NULL, '2026-02-14', '09:01:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(571, 97, NULL, '2026-02-14', '09:30:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(572, 62, NULL, '2026-02-14', '09:45:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(573, 65, NULL, '2026-02-14', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(574, 66, NULL, '2026-02-14', '09:12:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(575, 68, NULL, '2026-02-14', '09:09:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(576, 69, NULL, '2026-02-14', '09:04:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(577, 75, NULL, '2026-02-14', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(578, 76, NULL, '2026-02-14', '09:26:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(579, 77, NULL, '2026-02-14', '09:46:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(580, 83, NULL, '2026-02-14', '09:02:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(581, 94, NULL, '2026-02-14', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(582, 98, NULL, '2026-02-14', '09:14:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(583, 103, NULL, '2026-02-14', '09:27:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(584, 106, NULL, '2026-02-14', '09:42:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(585, 67, NULL, '2026-02-14', '09:42:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(586, 70, NULL, '2026-02-14', '09:42:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(587, 71, NULL, '2026-02-14', '09:48:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(588, 74, NULL, '2026-02-14', '09:36:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(589, 81, NULL, '2026-02-14', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(590, 82, NULL, '2026-02-14', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(591, 84, NULL, '2026-02-14', '09:44:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(592, 86, NULL, '2026-02-14', '09:38:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(593, 88, NULL, '2026-02-14', '09:57:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(594, 96, NULL, '2026-02-14', '09:36:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(595, 101, NULL, '2026-02-14', '09:16:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(596, 110, NULL, '2026-02-14', '09:24:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(597, 61, NULL, '2026-02-14', '09:32:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(598, 79, NULL, '2026-02-14', '09:39:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(599, 85, NULL, '2026-02-14', '09:04:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(600, 91, NULL, '2026-02-14', '09:03:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(601, 95, NULL, '2026-02-14', '09:20:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(602, 100, NULL, '2026-02-14', '09:09:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(603, 102, NULL, '2026-02-14', '09:21:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(604, 105, NULL, '2026-02-14', '09:21:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(605, 108, NULL, '2026-02-14', '09:22:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(606, 59, NULL, '2026-02-14', '09:38:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(607, 72, NULL, '2026-02-14', '09:00:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(608, 73, NULL, '2026-02-14', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(609, 80, NULL, '2026-02-14', '09:41:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(610, 87, NULL, '2026-02-14', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(611, 89, NULL, '2026-02-14', '09:58:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(612, 93, NULL, '2026-02-14', '09:26:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(613, 99, NULL, '2026-02-14', '09:34:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(614, 104, NULL, '2026-02-14', '09:42:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(615, 107, NULL, '2026-02-14', '09:40:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(616, 109, NULL, '2026-02-14', '09:30:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(617, 1, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(618, 56, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(619, 57, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(620, 58, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(621, 60, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(622, 63, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(623, 64, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(624, 78, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(625, 90, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(626, 92, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(627, 97, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(628, 62, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(629, 65, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(630, 66, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(631, 68, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(632, 69, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(633, 75, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(634, 76, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(635, 77, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(636, 83, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(637, 94, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(638, 98, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(639, 103, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(640, 106, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(641, 67, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(642, 70, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(643, 71, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(644, 74, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(645, 81, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(646, 82, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(647, 84, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(648, 86, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(649, 88, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(650, 96, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(651, 101, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(652, 110, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(653, 61, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(654, 79, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(655, 85, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(656, 91, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(657, 95, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(658, 100, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(659, 102, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(660, 105, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(661, 108, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(662, 59, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(663, 72, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(664, 73, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(665, 80, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(666, 87, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(667, 89, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(668, 93, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(669, 99, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(670, 104, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(671, 107, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(672, 109, NULL, '2026-02-15', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(673, 1, NULL, '2026-02-16', '09:45:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(674, 56, NULL, '2026-02-16', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(675, 57, NULL, '2026-02-16', '09:17:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(676, 58, NULL, '2026-02-16', '09:08:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(677, 60, NULL, '2026-02-16', '09:44:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(678, 63, NULL, '2026-02-16', '09:35:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(679, 64, NULL, '2026-02-16', '09:52:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(680, 78, NULL, '2026-02-16', '09:42:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(681, 90, NULL, '2026-02-16', '09:25:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(682, 92, NULL, '2026-02-16', '09:03:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(683, 97, NULL, '2026-02-16', '09:37:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(684, 62, NULL, '2026-02-16', '09:40:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(685, 65, NULL, '2026-02-16', '09:01:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(686, 66, NULL, '2026-02-16', '09:55:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(687, 68, NULL, '2026-02-16', '09:24:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(688, 69, NULL, '2026-02-16', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(689, 75, NULL, '2026-02-16', '09:47:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(690, 76, NULL, '2026-02-16', '09:57:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(691, 77, NULL, '2026-02-16', '09:33:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(692, 83, NULL, '2026-02-16', '09:35:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(693, 94, NULL, '2026-02-16', '09:53:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(694, 98, NULL, '2026-02-16', '09:16:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(695, 103, NULL, '2026-02-16', '09:14:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(696, 106, NULL, '2026-02-16', '09:41:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(697, 67, NULL, '2026-02-16', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(698, 70, NULL, '2026-02-16', '09:29:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(699, 71, NULL, '2026-02-16', '09:01:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(700, 74, NULL, '2026-02-16', '09:01:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(701, 81, NULL, '2026-02-16', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(702, 82, NULL, '2026-02-16', '09:46:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(703, 84, NULL, '2026-02-16', '09:06:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(704, 86, NULL, '2026-02-16', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(705, 88, NULL, '2026-02-16', '09:33:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(706, 96, NULL, '2026-02-16', '09:37:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(707, 101, NULL, '2026-02-16', '09:45:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(708, 110, NULL, '2026-02-16', '09:16:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(709, 61, NULL, '2026-02-16', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(710, 79, NULL, '2026-02-16', '09:44:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(711, 85, NULL, '2026-02-16', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(712, 91, NULL, '2026-02-16', '09:00:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(713, 95, NULL, '2026-02-16', '09:44:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(714, 100, NULL, '2026-02-16', '09:09:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(715, 102, NULL, '2026-02-16', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(716, 105, NULL, '2026-02-16', '09:59:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(717, 108, NULL, '2026-02-16', '09:55:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(718, 59, NULL, '2026-02-16', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(719, 72, NULL, '2026-02-16', '09:31:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(720, 73, NULL, '2026-02-16', '09:39:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(721, 80, NULL, '2026-02-16', '09:56:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(722, 87, NULL, '2026-02-16', '09:15:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(723, 89, NULL, '2026-02-16', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(724, 93, NULL, '2026-02-16', '09:51:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(725, 99, NULL, '2026-02-16', '09:29:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(726, 104, NULL, '2026-02-16', '09:19:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(727, 107, NULL, '2026-02-16', '09:14:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(728, 109, NULL, '2026-02-16', '09:45:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(729, 1, NULL, '2026-02-17', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(730, 56, NULL, '2026-02-17', '09:45:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(731, 57, NULL, '2026-02-17', '09:24:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(732, 58, NULL, '2026-02-17', '09:42:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(733, 60, NULL, '2026-02-17', '09:14:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(734, 63, NULL, '2026-02-17', '09:59:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(735, 64, NULL, '2026-02-17', '09:50:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(736, 78, NULL, '2026-02-17', '09:47:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(737, 90, NULL, '2026-02-17', '09:13:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(738, 92, NULL, '2026-02-17', '09:36:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(739, 97, NULL, '2026-02-17', '09:19:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(740, 62, NULL, '2026-02-17', '09:28:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(741, 65, NULL, '2026-02-17', '09:56:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(742, 66, NULL, '2026-02-17', '09:17:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(743, 68, NULL, '2026-02-17', '09:34:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(744, 69, NULL, '2026-02-17', '09:56:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(745, 75, NULL, '2026-02-17', '09:22:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(746, 76, NULL, '2026-02-17', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(747, 77, NULL, '2026-02-17', '09:41:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(748, 83, NULL, '2026-02-17', '09:28:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(749, 94, NULL, '2026-02-17', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(750, 98, NULL, '2026-02-17', '09:00:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(751, 103, NULL, '2026-02-17', '09:38:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(752, 106, NULL, '2026-02-17', '09:45:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(753, 67, NULL, '2026-02-17', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(754, 70, NULL, '2026-02-17', '09:08:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(755, 71, NULL, '2026-02-17', '09:37:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(756, 74, NULL, '2026-02-17', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(757, 81, NULL, '2026-02-17', '09:23:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(758, 82, NULL, '2026-02-17', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(759, 84, NULL, '2026-02-17', '09:39:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(760, 86, NULL, '2026-02-17', '09:00:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(761, 88, NULL, '2026-02-17', '09:35:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(762, 96, NULL, '2026-02-17', '09:36:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(763, 101, NULL, '2026-02-17', '09:47:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(764, 110, NULL, '2026-02-17', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(765, 61, NULL, '2026-02-17', '09:33:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(766, 79, NULL, '2026-02-17', '09:00:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(767, 85, NULL, '2026-02-17', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(768, 91, NULL, '2026-02-17', '09:27:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(769, 95, NULL, '2026-02-17', '09:13:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(770, 100, NULL, '2026-02-17', '09:46:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(771, 102, NULL, '2026-02-17', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(772, 105, NULL, '2026-02-17', '09:51:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(773, 108, NULL, '2026-02-17', '09:53:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(774, 59, NULL, '2026-02-17', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(775, 72, NULL, '2026-02-17', '09:18:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(776, 73, NULL, '2026-02-17', '09:54:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(777, 80, NULL, '2026-02-17', '09:25:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(778, 87, NULL, '2026-02-17', '09:54:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(779, 89, NULL, '2026-02-17', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(780, 93, NULL, '2026-02-17', '09:44:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(781, 99, NULL, '2026-02-17', '09:32:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(782, 104, NULL, '2026-02-17', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(783, 107, NULL, '2026-02-17', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(784, 109, NULL, '2026-02-17', '09:21:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(785, 1, NULL, '2026-02-18', '09:43:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(786, 56, NULL, '2026-02-18', '09:42:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(787, 57, NULL, '2026-02-18', '09:12:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(788, 58, NULL, '2026-02-18', '09:29:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(789, 60, NULL, '2026-02-18', '09:36:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(790, 63, NULL, '2026-02-18', '09:23:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(791, 64, NULL, '2026-02-18', '09:44:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(792, 78, NULL, '2026-02-18', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(793, 90, NULL, '2026-02-18', '09:09:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(794, 92, NULL, '2026-02-18', '09:19:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(795, 97, NULL, '2026-02-18', '09:09:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(796, 62, NULL, '2026-02-18', '09:06:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(797, 65, NULL, '2026-02-18', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(798, 66, NULL, '2026-02-18', '09:10:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(799, 68, NULL, '2026-02-18', '09:03:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(800, 69, NULL, '2026-02-18', '09:00:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(801, 75, NULL, '2026-02-18', '09:38:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(802, 76, NULL, '2026-02-18', '09:51:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(803, 77, NULL, '2026-02-18', '09:15:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(804, 83, NULL, '2026-02-18', '09:53:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(805, 94, NULL, '2026-02-18', '09:18:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(806, 98, NULL, '2026-02-18', '09:37:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(807, 103, NULL, '2026-02-18', '09:33:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(808, 106, NULL, '2026-02-18', '09:15:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(809, 67, NULL, '2026-02-18', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(810, 70, NULL, '2026-02-18', '09:41:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(811, 71, NULL, '2026-02-18', '09:27:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(812, 74, NULL, '2026-02-18', '09:18:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(813, 81, NULL, '2026-02-18', '09:20:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(814, 82, NULL, '2026-02-18', '09:11:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(815, 84, NULL, '2026-02-18', '09:40:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(816, 86, NULL, '2026-02-18', '09:18:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(817, 88, NULL, '2026-02-18', '09:09:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(818, 96, NULL, '2026-02-18', '09:31:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(819, 101, NULL, '2026-02-18', '09:23:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(820, 110, NULL, '2026-02-18', '09:37:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(821, 61, NULL, '2026-02-18', '09:21:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(822, 79, NULL, '2026-02-18', '09:03:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(823, 85, NULL, '2026-02-18', '09:08:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(824, 91, NULL, '2026-02-18', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(825, 95, NULL, '2026-02-18', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(826, 100, NULL, '2026-02-18', '09:50:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(827, 102, NULL, '2026-02-18', '09:03:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(828, 105, NULL, '2026-02-18', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(829, 108, NULL, '2026-02-18', '09:49:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(830, 59, NULL, '2026-02-18', '09:44:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(831, 72, NULL, '2026-02-18', '09:02:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(832, 73, NULL, '2026-02-18', '09:22:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(833, 80, NULL, '2026-02-18', '09:39:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(834, 87, NULL, '2026-02-18', '09:42:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(835, 89, NULL, '2026-02-18', '09:33:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(836, 93, NULL, '2026-02-18', '09:46:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(837, 99, NULL, '2026-02-18', '09:58:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(838, 104, NULL, '2026-02-18', '09:30:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(839, 107, NULL, '2026-02-18', '09:02:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(840, 109, NULL, '2026-02-18', '09:46:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(841, 1, NULL, '2026-02-19', '09:40:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(842, 56, NULL, '2026-02-19', '09:39:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(843, 57, NULL, '2026-02-19', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(844, 58, NULL, '2026-02-19', '09:14:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(845, 60, NULL, '2026-02-19', '09:07:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(846, 63, NULL, '2026-02-19', '09:02:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(847, 64, NULL, '2026-02-19', '09:59:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(848, 78, NULL, '2026-02-19', '09:50:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(849, 90, NULL, '2026-02-19', '09:36:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(850, 92, NULL, '2026-02-19', '09:23:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(851, 97, NULL, '2026-02-19', '09:57:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(852, 62, NULL, '2026-02-19', '09:23:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(853, 65, NULL, '2026-02-19', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(854, 66, NULL, '2026-02-19', '09:03:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(855, 68, NULL, '2026-02-19', '09:18:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(856, 69, NULL, '2026-02-19', '09:46:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(857, 75, NULL, '2026-02-19', '09:12:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(858, 76, NULL, '2026-02-19', '09:34:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(859, 77, NULL, '2026-02-19', '09:36:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(860, 83, NULL, '2026-02-19', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(861, 94, NULL, '2026-02-19', '09:21:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(862, 98, NULL, '2026-02-19', '09:15:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(863, 103, NULL, '2026-02-19', '09:30:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(864, 106, NULL, '2026-02-19', '09:48:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(865, 67, NULL, '2026-02-19', '09:06:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(866, 70, NULL, '2026-02-19', '09:35:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(867, 71, NULL, '2026-02-19', '09:49:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(868, 74, NULL, '2026-02-19', '09:18:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(869, 81, NULL, '2026-02-19', '09:29:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(870, 82, NULL, '2026-02-19', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(871, 84, NULL, '2026-02-19', '09:50:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(872, 86, NULL, '2026-02-19', '09:58:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(873, 88, NULL, '2026-02-19', '09:37:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(874, 96, NULL, '2026-02-19', '09:13:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(875, 101, NULL, '2026-02-19', '09:14:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(876, 110, NULL, '2026-02-19', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(877, 61, NULL, '2026-02-19', '09:56:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(878, 79, NULL, '2026-02-19', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(879, 85, NULL, '2026-02-19', '09:28:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(880, 91, NULL, '2026-02-19', '09:51:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(881, 95, NULL, '2026-02-19', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(882, 100, NULL, '2026-02-19', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(883, 102, NULL, '2026-02-19', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(884, 105, NULL, '2026-02-19', '09:42:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(885, 108, NULL, '2026-02-19', '09:46:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(886, 59, NULL, '2026-02-19', '09:02:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(887, 72, NULL, '2026-02-19', '09:17:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(888, 73, NULL, '2026-02-19', '09:55:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(889, 80, NULL, '2026-02-19', '09:18:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(890, 87, NULL, '2026-02-19', '09:13:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(891, 89, NULL, '2026-02-19', '09:26:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(892, 93, NULL, '2026-02-19', '09:16:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(893, 99, NULL, '2026-02-19', '09:10:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(894, 104, NULL, '2026-02-19', '09:46:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(895, 107, NULL, '2026-02-19', '09:37:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(896, 109, NULL, '2026-02-19', '09:32:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(897, 1, NULL, '2026-02-20', '09:06:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(898, 56, NULL, '2026-02-20', '09:50:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(899, 57, NULL, '2026-02-20', '09:20:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(900, 58, NULL, '2026-02-20', '09:48:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(901, 60, NULL, '2026-02-20', '09:31:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(902, 63, NULL, '2026-02-20', '09:06:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(903, 64, NULL, '2026-02-20', '09:02:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(904, 78, NULL, '2026-02-20', '09:22:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(905, 90, NULL, '2026-02-20', '09:41:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(906, 92, NULL, '2026-02-20', '09:01:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(907, 97, NULL, '2026-02-20', '09:54:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(908, 62, NULL, '2026-02-20', '09:23:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(909, 65, NULL, '2026-02-20', '09:00:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(910, 66, NULL, '2026-02-20', '09:27:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(911, 68, NULL, '2026-02-20', '09:30:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(912, 69, NULL, '2026-02-20', '09:14:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(913, 75, NULL, '2026-02-20', '09:47:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(914, 76, NULL, '2026-02-20', '09:57:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(915, 77, NULL, '2026-02-20', '09:46:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(916, 83, NULL, '2026-02-20', '09:05:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(917, 94, NULL, '2026-02-20', '09:50:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(918, 98, NULL, '2026-02-20', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(919, 103, NULL, '2026-02-20', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(920, 106, NULL, '2026-02-20', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(921, 67, NULL, '2026-02-20', '09:19:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(922, 70, NULL, '2026-02-20', '09:37:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(923, 71, NULL, '2026-02-20', '09:22:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(924, 74, NULL, '2026-02-20', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(925, 81, NULL, '2026-02-20', '09:30:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(926, 82, NULL, '2026-02-20', '09:14:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(927, 84, NULL, '2026-02-20', '09:09:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(928, 86, NULL, '2026-02-20', '09:06:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(929, 88, NULL, '2026-02-20', '09:25:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(930, 96, NULL, '2026-02-20', '09:14:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(931, 101, NULL, '2026-02-20', '09:15:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(932, 110, NULL, '2026-02-20', '09:05:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(933, 61, NULL, '2026-02-20', '09:34:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(934, 79, NULL, '2026-02-20', '09:45:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(935, 85, NULL, '2026-02-20', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(936, 91, NULL, '2026-02-20', '09:58:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(937, 95, NULL, '2026-02-20', '09:19:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(938, 100, NULL, '2026-02-20', '09:32:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(939, 102, NULL, '2026-02-20', '09:10:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(940, 105, NULL, '2026-02-20', '09:08:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(941, 108, NULL, '2026-02-20', '09:40:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(942, 59, NULL, '2026-02-20', '09:27:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(943, 72, NULL, '2026-02-20', '09:30:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(944, 73, NULL, '2026-02-20', '09:24:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(945, 80, NULL, '2026-02-20', '09:08:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(946, 87, NULL, '2026-02-20', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(947, 89, NULL, '2026-02-20', '09:41:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(948, 93, NULL, '2026-02-20', '09:18:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(949, 99, NULL, '2026-02-20', '09:32:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(950, 104, NULL, '2026-02-20', '09:41:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(951, 107, NULL, '2026-02-20', '09:01:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(952, 109, NULL, '2026-02-20', '09:36:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(953, 1, NULL, '2026-02-21', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(954, 56, NULL, '2026-02-21', '09:37:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(955, 57, NULL, '2026-02-21', '09:11:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(956, 58, NULL, '2026-02-21', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(957, 60, NULL, '2026-02-21', '09:48:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(958, 63, NULL, '2026-02-21', '09:43:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(959, 64, NULL, '2026-02-21', '09:42:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(960, 78, NULL, '2026-02-21', '09:12:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(961, 90, NULL, '2026-02-21', '09:17:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(962, 92, NULL, '2026-02-21', '09:30:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(963, 97, NULL, '2026-02-21', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(964, 62, NULL, '2026-02-21', '09:28:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(965, 65, NULL, '2026-02-21', '09:36:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(966, 66, NULL, '2026-02-21', '09:11:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(967, 68, NULL, '2026-02-21', '09:38:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(968, 69, NULL, '2026-02-21', '09:50:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(969, 75, NULL, '2026-02-21', '09:39:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(970, 76, NULL, '2026-02-21', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(971, 77, NULL, '2026-02-21', '09:27:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(972, 83, NULL, '2026-02-21', '09:06:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18');
INSERT INTO `attendance` (`id`, `employee_id`, `device_id`, `date`, `check_in`, `check_out`, `status`, `auth_method`, `remarks`, `created_at`) VALUES
(973, 94, NULL, '2026-02-21', '09:28:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(974, 98, NULL, '2026-02-21', '09:37:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(975, 103, NULL, '2026-02-21', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(976, 106, NULL, '2026-02-21', '09:52:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(977, 67, NULL, '2026-02-21', '09:45:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(978, 70, NULL, '2026-02-21', '09:04:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(979, 71, NULL, '2026-02-21', '09:12:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(980, 74, NULL, '2026-02-21', '09:36:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(981, 81, NULL, '2026-02-21', '09:52:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(982, 82, NULL, '2026-02-21', '09:48:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(983, 84, NULL, '2026-02-21', '09:00:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(984, 86, NULL, '2026-02-21', '09:43:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(985, 88, NULL, '2026-02-21', '09:32:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(986, 96, NULL, '2026-02-21', '09:50:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(987, 101, NULL, '2026-02-21', '09:11:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(988, 110, NULL, '2026-02-21', '09:23:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(989, 61, NULL, '2026-02-21', '09:30:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(990, 79, NULL, '2026-02-21', '09:27:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(991, 85, NULL, '2026-02-21', '09:21:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(992, 91, NULL, '2026-02-21', '09:09:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(993, 95, NULL, '2026-02-21', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(994, 100, NULL, '2026-02-21', '09:40:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(995, 102, NULL, '2026-02-21', '09:54:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(996, 105, NULL, '2026-02-21', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(997, 108, NULL, '2026-02-21', '09:50:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(998, 59, NULL, '2026-02-21', '09:03:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(999, 72, NULL, '2026-02-21', '09:13:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1000, 73, NULL, '2026-02-21', '09:32:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1001, 80, NULL, '2026-02-21', '09:58:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1002, 87, NULL, '2026-02-21', '09:53:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1003, 89, NULL, '2026-02-21', '09:57:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1004, 93, NULL, '2026-02-21', '09:27:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1005, 99, NULL, '2026-02-21', '09:26:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1006, 104, NULL, '2026-02-21', '09:59:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1007, 107, NULL, '2026-02-21', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(1008, 109, NULL, '2026-02-21', '09:36:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1009, 1, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1010, 56, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1011, 57, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1012, 58, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1013, 60, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1014, 63, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1015, 64, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1016, 78, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1017, 90, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1018, 92, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1019, 97, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1020, 62, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1021, 65, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1022, 66, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1023, 68, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1024, 69, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1025, 75, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1026, 76, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1027, 77, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1028, 83, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1029, 94, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1030, 98, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1031, 103, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1032, 106, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1033, 67, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1034, 70, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1035, 71, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1036, 74, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1037, 81, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1038, 82, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1039, 84, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1040, 86, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1041, 88, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1042, 96, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1043, 101, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1044, 110, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1045, 61, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1046, 79, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1047, 85, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1048, 91, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1049, 95, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1050, 100, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1051, 102, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1052, 105, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1053, 108, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1054, 59, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1055, 72, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1056, 73, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1057, 80, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1058, 87, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1059, 89, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1060, 93, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1061, 99, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1062, 104, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1063, 107, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1064, 109, NULL, '2026-02-22', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:18'),
(1065, 1, NULL, '2026-02-23', '09:55:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1066, 56, NULL, '2026-02-23', '09:49:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1067, 57, NULL, '2026-02-23', '09:23:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1068, 58, NULL, '2026-02-23', '09:30:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1069, 60, NULL, '2026-02-23', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(1070, 63, NULL, '2026-02-23', '09:22:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1071, 64, NULL, '2026-02-23', '09:48:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1072, 78, NULL, '2026-02-23', '09:27:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1073, 90, NULL, '2026-02-23', '09:43:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1074, 92, NULL, '2026-02-23', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:18'),
(1075, 97, NULL, '2026-02-23', '09:03:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1076, 62, NULL, '2026-02-23', '09:11:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1077, 65, NULL, '2026-02-23', '09:46:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1078, 66, NULL, '2026-02-23', '09:30:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1079, 68, NULL, '2026-02-23', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:18'),
(1080, 69, NULL, '2026-02-23', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:18'),
(1081, 75, NULL, '2026-02-23', '09:11:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1082, 76, NULL, '2026-02-23', '09:10:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1083, 77, NULL, '2026-02-23', '09:45:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1084, 83, NULL, '2026-02-23', '09:58:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1085, 94, NULL, '2026-02-23', '09:25:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1086, 98, NULL, '2026-02-23', '09:51:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1087, 103, NULL, '2026-02-23', '09:10:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1088, 106, NULL, '2026-02-23', '09:05:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1089, 67, NULL, '2026-02-23', '09:00:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:18'),
(1090, 70, NULL, '2026-02-23', '09:08:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1091, 71, NULL, '2026-02-23', '09:49:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1092, 74, NULL, '2026-02-23', '09:39:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1093, 81, NULL, '2026-02-23', '09:50:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1094, 82, NULL, '2026-02-23', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1095, 84, NULL, '2026-02-23', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1096, 86, NULL, '2026-02-23', '09:56:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1097, 88, NULL, '2026-02-23', '09:46:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1098, 96, NULL, '2026-02-23', '09:53:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1099, 101, NULL, '2026-02-23', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1100, 110, NULL, '2026-02-23', '09:14:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1101, 61, NULL, '2026-02-23', '09:43:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1102, 79, NULL, '2026-02-23', '09:56:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1103, 85, NULL, '2026-02-23', '09:04:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1104, 91, NULL, '2026-02-23', '09:59:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1105, 95, NULL, '2026-02-23', '09:15:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1106, 100, NULL, '2026-02-23', '09:19:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1107, 102, NULL, '2026-02-23', '09:06:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1108, 105, NULL, '2026-02-23', '09:44:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1109, 108, NULL, '2026-02-23', '09:57:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1110, 59, NULL, '2026-02-23', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1111, 72, NULL, '2026-02-23', '09:16:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1112, 73, NULL, '2026-02-23', '09:54:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1113, 80, NULL, '2026-02-23', '09:43:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1114, 87, NULL, '2026-02-23', '09:25:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1115, 89, NULL, '2026-02-23', '09:33:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1116, 93, NULL, '2026-02-23', '09:24:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1117, 99, NULL, '2026-02-23', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1118, 104, NULL, '2026-02-23', '09:20:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1119, 107, NULL, '2026-02-23', '09:57:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1120, 109, NULL, '2026-02-23', '09:58:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1121, 1, NULL, '2026-02-24', '09:49:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1122, 56, NULL, '2026-02-24', '09:23:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1123, 57, NULL, '2026-02-24', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1124, 58, NULL, '2026-02-24', '09:14:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1125, 60, NULL, '2026-02-24', '09:27:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1126, 63, NULL, '2026-02-24', '09:20:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1127, 64, NULL, '2026-02-24', '09:42:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1128, 78, NULL, '2026-02-24', '09:30:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1129, 90, NULL, '2026-02-24', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1130, 92, NULL, '2026-02-24', '09:08:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1131, 97, NULL, '2026-02-24', '09:05:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1132, 62, NULL, '2026-02-24', '09:41:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1133, 65, NULL, '2026-02-24', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1134, 66, NULL, '2026-02-24', '09:49:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1135, 68, NULL, '2026-02-24', '09:50:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1136, 69, NULL, '2026-02-24', '09:47:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1137, 75, NULL, '2026-02-24', '09:16:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1138, 76, NULL, '2026-02-24', '09:24:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1139, 77, NULL, '2026-02-24', '09:40:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1140, 83, NULL, '2026-02-24', '09:07:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1141, 94, NULL, '2026-02-24', '09:31:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1142, 98, NULL, '2026-02-24', '09:56:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1143, 103, NULL, '2026-02-24', '09:18:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1144, 106, NULL, '2026-02-24', '09:39:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1145, 67, NULL, '2026-02-24', '09:07:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1146, 70, NULL, '2026-02-24', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1147, 71, NULL, '2026-02-24', '09:25:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1148, 74, NULL, '2026-02-24', '09:40:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1149, 81, NULL, '2026-02-24', '09:06:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1150, 82, NULL, '2026-02-24', '09:23:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1151, 84, NULL, '2026-02-24', '09:31:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1152, 86, NULL, '2026-02-24', '09:14:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1153, 88, NULL, '2026-02-24', '09:28:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1154, 96, NULL, '2026-02-24', '09:53:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1155, 101, NULL, '2026-02-24', '09:59:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1156, 110, NULL, '2026-02-24', '09:38:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1157, 61, NULL, '2026-02-24', '09:07:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1158, 79, NULL, '2026-02-24', '09:40:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1159, 85, NULL, '2026-02-24', '09:33:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1160, 91, NULL, '2026-02-24', '09:58:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1161, 95, NULL, '2026-02-24', '09:26:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1162, 100, NULL, '2026-02-24', '09:15:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1163, 102, NULL, '2026-02-24', '09:36:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1164, 105, NULL, '2026-02-24', '09:31:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1165, 108, NULL, '2026-02-24', '09:59:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1166, 59, NULL, '2026-02-24', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1167, 72, NULL, '2026-02-24', '09:31:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1168, 73, NULL, '2026-02-24', '09:19:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1169, 80, NULL, '2026-02-24', '09:52:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1170, 87, NULL, '2026-02-24', '09:20:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1171, 89, NULL, '2026-02-24', '09:37:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1172, 93, NULL, '2026-02-24', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1173, 99, NULL, '2026-02-24', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1174, 104, NULL, '2026-02-24', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1175, 107, NULL, '2026-02-24', '09:23:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1176, 109, NULL, '2026-02-24', '09:07:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1177, 1, NULL, '2026-02-25', '09:28:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1178, 56, NULL, '2026-02-25', '09:54:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1179, 57, NULL, '2026-02-25', '09:51:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1180, 58, NULL, '2026-02-25', '09:32:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1181, 60, NULL, '2026-02-25', '09:51:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1182, 63, NULL, '2026-02-25', '09:38:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1183, 64, NULL, '2026-02-25', '09:34:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1184, 78, NULL, '2026-02-25', '09:54:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1185, 90, NULL, '2026-02-25', '09:08:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1186, 92, NULL, '2026-02-25', '09:12:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1187, 97, NULL, '2026-02-25', '09:14:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1188, 62, NULL, '2026-02-25', '09:34:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1189, 65, NULL, '2026-02-25', '09:09:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1190, 66, NULL, '2026-02-25', '09:36:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1191, 68, NULL, '2026-02-25', '09:48:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1192, 69, NULL, '2026-02-25', '09:55:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1193, 75, NULL, '2026-02-25', '09:37:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1194, 76, NULL, '2026-02-25', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1195, 77, NULL, '2026-02-25', '09:56:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1196, 83, NULL, '2026-02-25', '09:00:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1197, 94, NULL, '2026-02-25', '09:49:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1198, 98, NULL, '2026-02-25', '09:30:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1199, 103, NULL, '2026-02-25', '09:30:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1200, 106, NULL, '2026-02-25', '09:20:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1201, 67, NULL, '2026-02-25', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1202, 70, NULL, '2026-02-25', '09:49:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1203, 71, NULL, '2026-02-25', '09:55:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1204, 74, NULL, '2026-02-25', '09:25:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1205, 81, NULL, '2026-02-25', '09:41:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1206, 82, NULL, '2026-02-25', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1207, 84, NULL, '2026-02-25', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1208, 86, NULL, '2026-02-25', '09:49:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1209, 88, NULL, '2026-02-25', '09:40:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1210, 96, NULL, '2026-02-25', '09:15:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1211, 101, NULL, '2026-02-25', '09:18:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1212, 110, NULL, '2026-02-25', '09:38:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1213, 61, NULL, '2026-02-25', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1214, 79, NULL, '2026-02-25', '09:15:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1215, 85, NULL, '2026-02-25', '09:34:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1216, 91, NULL, '2026-02-25', '09:47:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1217, 95, NULL, '2026-02-25', '09:11:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1218, 100, NULL, '2026-02-25', '09:42:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1219, 102, NULL, '2026-02-25', '09:34:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1220, 105, NULL, '2026-02-25', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1221, 108, NULL, '2026-02-25', '09:10:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1222, 59, NULL, '2026-02-25', '09:26:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1223, 72, NULL, '2026-02-25', '09:50:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1224, 73, NULL, '2026-02-25', '09:41:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1225, 80, NULL, '2026-02-25', '09:06:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1226, 87, NULL, '2026-02-25', '09:29:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1227, 89, NULL, '2026-02-25', '09:31:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1228, 93, NULL, '2026-02-25', '09:53:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1229, 99, NULL, '2026-02-25', '09:34:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1230, 104, NULL, '2026-02-25', '09:16:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1231, 107, NULL, '2026-02-25', '09:03:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1232, 109, NULL, '2026-02-25', '09:43:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1233, 1, NULL, '2026-02-26', '09:53:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1234, 56, NULL, '2026-02-26', '09:24:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1235, 57, NULL, '2026-02-26', '09:30:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1236, 58, NULL, '2026-02-26', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1237, 60, NULL, '2026-02-26', '09:57:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1238, 63, NULL, '2026-02-26', '09:58:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1239, 64, NULL, '2026-02-26', '09:01:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1240, 78, NULL, '2026-02-26', '09:59:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1241, 90, NULL, '2026-02-26', '09:44:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1242, 92, NULL, '2026-02-26', '09:56:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1243, 97, NULL, '2026-02-26', '09:06:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1244, 62, NULL, '2026-02-26', '09:57:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1245, 65, NULL, '2026-02-26', '09:22:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1246, 66, NULL, '2026-02-26', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1247, 68, NULL, '2026-02-26', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1248, 69, NULL, '2026-02-26', '09:27:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1249, 75, NULL, '2026-02-26', '09:43:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1250, 76, NULL, '2026-02-26', '09:35:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1251, 77, NULL, '2026-02-26', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1252, 83, NULL, '2026-02-26', '09:10:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1253, 94, NULL, '2026-02-26', '09:59:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1254, 98, NULL, '2026-02-26', '09:04:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1255, 103, NULL, '2026-02-26', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1256, 106, NULL, '2026-02-26', '09:12:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1257, 67, NULL, '2026-02-26', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1258, 70, NULL, '2026-02-26', '09:10:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1259, 71, NULL, '2026-02-26', '09:21:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1260, 74, NULL, '2026-02-26', '09:06:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1261, 81, NULL, '2026-02-26', '09:16:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1262, 82, NULL, '2026-02-26', '09:47:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1263, 84, NULL, '2026-02-26', '09:26:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1264, 86, NULL, '2026-02-26', '09:45:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1265, 88, NULL, '2026-02-26', '09:27:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1266, 96, NULL, '2026-02-26', '09:20:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1267, 101, NULL, '2026-02-26', '09:22:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1268, 110, NULL, '2026-02-26', '09:42:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1269, 61, NULL, '2026-02-26', '09:37:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1270, 79, NULL, '2026-02-26', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1271, 85, NULL, '2026-02-26', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1272, 91, NULL, '2026-02-26', '09:38:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1273, 95, NULL, '2026-02-26', '09:54:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1274, 100, NULL, '2026-02-26', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1275, 102, NULL, '2026-02-26', '09:34:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1276, 105, NULL, '2026-02-26', '09:02:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1277, 108, NULL, '2026-02-26', '09:22:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1278, 59, NULL, '2026-02-26', '09:20:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1279, 72, NULL, '2026-02-26', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1280, 73, NULL, '2026-02-26', '09:02:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1281, 80, NULL, '2026-02-26', '09:43:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1282, 87, NULL, '2026-02-26', '09:35:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1283, 89, NULL, '2026-02-26', '09:12:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1284, 93, NULL, '2026-02-26', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1285, 99, NULL, '2026-02-26', '09:49:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1286, 104, NULL, '2026-02-26', '09:10:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1287, 107, NULL, '2026-02-26', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1288, 109, NULL, '2026-02-26', '09:27:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1289, 1, NULL, '2026-02-27', '09:24:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1290, 56, NULL, '2026-02-27', '09:15:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1291, 57, NULL, '2026-02-27', '09:47:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1292, 58, NULL, '2026-02-27', '09:59:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1293, 60, NULL, '2026-02-27', '09:08:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1294, 63, NULL, '2026-02-27', '09:40:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1295, 64, NULL, '2026-02-27', '09:30:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1296, 78, NULL, '2026-02-27', '09:24:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1297, 90, NULL, '2026-02-27', '09:34:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1298, 92, NULL, '2026-02-27', '09:08:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1299, 97, NULL, '2026-02-27', '09:10:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1300, 62, NULL, '2026-02-27', '09:12:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1301, 65, NULL, '2026-02-27', '09:00:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1302, 66, NULL, '2026-02-27', '09:14:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1303, 68, NULL, '2026-02-27', '09:59:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1304, 69, NULL, '2026-02-27', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1305, 75, NULL, '2026-02-27', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1306, 76, NULL, '2026-02-27', '09:04:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1307, 77, NULL, '2026-02-27', '09:46:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1308, 83, NULL, '2026-02-27', '09:29:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1309, 94, NULL, '2026-02-27', '09:57:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1310, 98, NULL, '2026-02-27', '09:14:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1311, 103, NULL, '2026-02-27', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1312, 106, NULL, '2026-02-27', '09:36:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1313, 67, NULL, '2026-02-27', '09:02:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1314, 70, NULL, '2026-02-27', '09:53:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1315, 71, NULL, '2026-02-27', '09:44:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1316, 74, NULL, '2026-02-27', '09:17:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1317, 81, NULL, '2026-02-27', '09:27:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1318, 82, NULL, '2026-02-27', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1319, 84, NULL, '2026-02-27', '09:37:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1320, 86, NULL, '2026-02-27', '09:45:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1321, 88, NULL, '2026-02-27', '09:53:00', '18:26:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1322, 96, NULL, '2026-02-27', '09:30:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1323, 101, NULL, '2026-02-27', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1324, 110, NULL, '2026-02-27', '09:40:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1325, 61, NULL, '2026-02-27', '09:43:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1326, 79, NULL, '2026-02-27', '09:04:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1327, 85, NULL, '2026-02-27', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1328, 91, NULL, '2026-02-27', '09:41:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1329, 95, NULL, '2026-02-27', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1330, 100, NULL, '2026-02-27', '09:41:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1331, 102, NULL, '2026-02-27', '09:25:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1332, 105, NULL, '2026-02-27', '09:52:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1333, 108, NULL, '2026-02-27', '09:32:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1334, 59, NULL, '2026-02-27', '09:38:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1335, 72, NULL, '2026-02-27', '09:32:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1336, 73, NULL, '2026-02-27', '09:14:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1337, 80, NULL, '2026-02-27', '09:25:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1338, 87, NULL, '2026-02-27', '09:04:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1339, 89, NULL, '2026-02-27', '09:35:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1340, 93, NULL, '2026-02-27', '09:28:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1341, 99, NULL, '2026-02-27', '09:07:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1342, 104, NULL, '2026-02-27', '09:31:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1343, 107, NULL, '2026-02-27', '09:22:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1344, 109, NULL, '2026-02-27', '09:13:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1345, 1, NULL, '2026-02-28', '09:25:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1346, 56, NULL, '2026-02-28', '09:18:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1347, 57, NULL, '2026-02-28', '09:41:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1348, 58, NULL, '2026-02-28', '09:41:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1349, 60, NULL, '2026-02-28', '09:24:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1350, 63, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1351, 64, NULL, '2026-02-28', '09:33:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1352, 78, NULL, '2026-02-28', '09:59:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1353, 90, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1354, 92, NULL, '2026-02-28', '09:23:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1355, 97, NULL, '2026-02-28', '09:10:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1356, 62, NULL, '2026-02-28', '09:27:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1357, 65, NULL, '2026-02-28', '09:34:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1358, 66, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1359, 68, NULL, '2026-02-28', '09:31:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1360, 69, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1361, 75, NULL, '2026-02-28', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1362, 76, NULL, '2026-02-28', '09:11:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1363, 77, NULL, '2026-02-28', '09:49:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1364, 83, NULL, '2026-02-28', '09:05:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1365, 94, NULL, '2026-02-28', '09:52:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1366, 98, NULL, '2026-02-28', '09:25:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1367, 103, NULL, '2026-02-28', '09:37:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1368, 106, NULL, '2026-02-28', '09:37:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1369, 67, NULL, '2026-02-28', '09:50:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1370, 70, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1371, 71, NULL, '2026-02-28', '09:35:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1372, 74, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1373, 81, NULL, '2026-02-28', '09:21:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1374, 82, NULL, '2026-02-28', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1375, 84, NULL, '2026-02-28', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1376, 86, NULL, '2026-02-28', '09:05:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1377, 88, NULL, '2026-02-28', '09:57:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1378, 96, NULL, '2026-02-28', '09:15:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1379, 101, NULL, '2026-02-28', '09:08:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1380, 110, NULL, '2026-02-28', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1381, 61, NULL, '2026-02-28', '09:49:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1382, 79, NULL, '2026-02-28', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1383, 85, NULL, '2026-02-28', '09:24:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1384, 91, NULL, '2026-02-28', '09:08:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1385, 95, NULL, '2026-02-28', '09:44:00', '18:38:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1386, 100, NULL, '2026-02-28', '09:26:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1387, 102, NULL, '2026-02-28', '09:57:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1388, 105, NULL, '2026-02-28', '09:51:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1389, 108, NULL, '2026-02-28', '09:29:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1390, 59, NULL, '2026-02-28', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1391, 72, NULL, '2026-02-28', '09:36:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1392, 73, NULL, '2026-02-28', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1393, 80, NULL, '2026-02-28', '09:00:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1394, 87, NULL, '2026-02-28', '09:18:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1395, 89, NULL, '2026-02-28', '09:51:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1396, 93, NULL, '2026-02-28', '09:39:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1397, 99, NULL, '2026-02-28', '09:54:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1398, 104, NULL, '2026-02-28', '09:30:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1399, 107, NULL, '2026-02-28', '09:29:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1400, 109, NULL, '2026-02-28', '09:05:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1401, 1, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1402, 56, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1403, 57, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1404, 58, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1405, 60, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1406, 63, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1407, 64, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1408, 78, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1409, 90, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1410, 92, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1411, 97, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1412, 62, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1413, 65, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1414, 66, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1415, 68, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1416, 69, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1417, 75, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1418, 76, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1419, 77, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1420, 83, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1421, 94, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1422, 98, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1423, 103, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1424, 106, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1425, 67, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1426, 70, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1427, 71, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1428, 74, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1429, 81, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1430, 82, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1431, 84, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1432, 86, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1433, 88, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1434, 96, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1435, 101, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1436, 110, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1437, 61, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1438, 79, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1439, 85, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1440, 91, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1441, 95, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1442, 100, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1443, 102, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1444, 105, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1445, 108, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1446, 59, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1447, 72, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1448, 73, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1449, 80, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1450, 87, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1451, 89, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1452, 93, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1453, 99, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1454, 104, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1455, 107, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1456, 109, NULL, '2026-03-01', NULL, NULL, 'holiday', NULL, 'Weekly Off', '2026-03-06 16:42:19'),
(1457, 1, NULL, '2026-03-02', '09:32:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1458, 56, NULL, '2026-03-02', '09:32:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19');
INSERT INTO `attendance` (`id`, `employee_id`, `device_id`, `date`, `check_in`, `check_out`, `status`, `auth_method`, `remarks`, `created_at`) VALUES
(1459, 57, NULL, '2026-03-02', '09:12:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1460, 58, NULL, '2026-03-02', '09:55:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1461, 60, NULL, '2026-03-02', '09:16:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1462, 63, NULL, '2026-03-02', '09:06:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1463, 64, NULL, '2026-03-02', '09:14:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1464, 78, NULL, '2026-03-02', '09:43:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1465, 90, NULL, '2026-03-02', '09:30:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1466, 92, NULL, '2026-03-02', '09:39:00', '18:54:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1467, 97, NULL, '2026-03-02', '09:39:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1468, 62, NULL, '2026-03-02', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1469, 65, NULL, '2026-03-02', '09:50:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1470, 66, NULL, '2026-03-02', '09:03:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1471, 68, NULL, '2026-03-02', '09:34:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1472, 69, NULL, '2026-03-02', '09:11:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1473, 75, NULL, '2026-03-02', '09:05:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1474, 76, NULL, '2026-03-02', '09:56:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1475, 77, NULL, '2026-03-02', '09:38:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1476, 83, NULL, '2026-03-02', '09:33:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1477, 94, NULL, '2026-03-02', '09:56:00', '18:17:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1478, 98, NULL, '2026-03-02', '09:48:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1479, 103, NULL, '2026-03-02', '09:29:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1480, 106, NULL, '2026-03-02', '09:31:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1481, 67, NULL, '2026-03-02', '09:25:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1482, 70, NULL, '2026-03-02', '09:31:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1483, 71, NULL, '2026-03-02', '09:18:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1484, 74, NULL, '2026-03-02', '09:20:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1485, 81, NULL, '2026-03-02', '09:04:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1486, 82, NULL, '2026-03-02', '09:30:00', '18:27:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1487, 84, NULL, '2026-03-02', '09:03:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1488, 86, NULL, '2026-03-02', '09:56:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1489, 88, NULL, '2026-03-02', '09:53:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1490, 96, NULL, '2026-03-02', '09:09:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1491, 101, NULL, '2026-03-02', '09:26:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1492, 110, NULL, '2026-03-02', '09:47:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1493, 61, NULL, '2026-03-02', '09:15:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1494, 79, NULL, '2026-03-02', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1495, 85, NULL, '2026-03-02', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1496, 91, NULL, '2026-03-02', '09:04:00', '18:05:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1497, 95, NULL, '2026-03-02', '09:01:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1498, 100, NULL, '2026-03-02', '09:59:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1499, 102, NULL, '2026-03-02', '09:34:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1500, 105, NULL, '2026-03-02', '09:15:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1501, 108, NULL, '2026-03-02', '09:01:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1502, 59, NULL, '2026-03-02', '09:11:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1503, 72, NULL, '2026-03-02', '09:32:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1504, 73, NULL, '2026-03-02', '09:13:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1505, 80, NULL, '2026-03-02', '09:38:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1506, 87, NULL, '2026-03-02', '09:04:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1507, 89, NULL, '2026-03-02', '09:04:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1508, 93, NULL, '2026-03-02', '09:06:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1509, 99, NULL, '2026-03-02', '09:06:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1510, 104, NULL, '2026-03-02', '09:19:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1511, 107, NULL, '2026-03-02', '09:52:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1512, 109, NULL, '2026-03-02', '09:09:00', '18:28:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1513, 1, NULL, '2026-03-03', '09:33:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1514, 56, NULL, '2026-03-03', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1515, 57, NULL, '2026-03-03', '09:07:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1516, 58, NULL, '2026-03-03', '09:33:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1517, 60, NULL, '2026-03-03', '09:50:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1518, 63, NULL, '2026-03-03', '09:13:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1519, 64, NULL, '2026-03-03', '09:19:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1520, 78, NULL, '2026-03-03', '09:48:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1521, 90, NULL, '2026-03-03', '09:04:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1522, 92, NULL, '2026-03-03', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1523, 97, NULL, '2026-03-03', '09:05:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1524, 62, NULL, '2026-03-03', '09:09:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1525, 65, NULL, '2026-03-03', '09:30:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1526, 66, NULL, '2026-03-03', '09:13:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1527, 68, NULL, '2026-03-03', '09:03:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1528, 69, NULL, '2026-03-03', '09:55:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1529, 75, NULL, '2026-03-03', '09:30:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1530, 76, NULL, '2026-03-03', '09:42:00', '18:02:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1531, 77, NULL, '2026-03-03', '09:54:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1532, 83, NULL, '2026-03-03', '09:46:00', '18:07:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1533, 94, NULL, '2026-03-03', '09:07:00', '18:11:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1534, 98, NULL, '2026-03-03', '09:54:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1535, 103, NULL, '2026-03-03', '09:49:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1536, 106, NULL, '2026-03-03', '09:49:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1537, 67, NULL, '2026-03-03', '09:58:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1538, 70, NULL, '2026-03-03', '09:54:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1539, 71, NULL, '2026-03-03', '09:17:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1540, 74, NULL, '2026-03-03', '09:10:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1541, 81, NULL, '2026-03-03', '09:49:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1542, 82, NULL, '2026-03-03', '09:07:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1543, 84, NULL, '2026-03-03', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1544, 86, NULL, '2026-03-03', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1545, 88, NULL, '2026-03-03', '09:27:00', '18:13:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1546, 96, NULL, '2026-03-03', '09:46:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1547, 101, NULL, '2026-03-03', '09:26:00', '18:48:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1548, 110, NULL, '2026-03-03', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1549, 61, NULL, '2026-03-03', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1550, 79, NULL, '2026-03-03', '09:06:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1551, 85, NULL, '2026-03-03', '09:16:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1552, 91, NULL, '2026-03-03', '09:02:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1553, 95, NULL, '2026-03-03', '09:23:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1554, 100, NULL, '2026-03-03', '09:00:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1555, 102, NULL, '2026-03-03', '09:06:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1556, 105, NULL, '2026-03-03', '09:08:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1557, 108, NULL, '2026-03-03', '09:35:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1558, 59, NULL, '2026-03-03', '09:02:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1559, 72, NULL, '2026-03-03', '09:57:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1560, 73, NULL, '2026-03-03', '09:04:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1561, 80, NULL, '2026-03-03', '09:38:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1562, 87, NULL, '2026-03-03', '09:25:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1563, 89, NULL, '2026-03-03', '09:22:00', '18:58:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1564, 93, NULL, '2026-03-03', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1565, 99, NULL, '2026-03-03', '09:01:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1566, 104, NULL, '2026-03-03', '09:19:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1567, 107, NULL, '2026-03-03', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1568, 109, NULL, '2026-03-03', '09:45:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1569, 1, NULL, '2026-03-04', '09:42:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1570, 56, NULL, '2026-03-04', '09:27:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1571, 57, NULL, '2026-03-04', '09:20:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1572, 58, NULL, '2026-03-04', '09:21:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1573, 60, NULL, '2026-03-04', '09:03:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1574, 63, NULL, '2026-03-04', '09:26:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1575, 64, NULL, '2026-03-04', '09:34:00', '18:15:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1576, 78, NULL, '2026-03-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1577, 90, NULL, '2026-03-04', '09:25:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1578, 92, NULL, '2026-03-04', '09:16:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1579, 97, NULL, '2026-03-04', '09:17:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1580, 62, NULL, '2026-03-04', '09:12:00', '18:56:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1581, 65, NULL, '2026-03-04', '09:16:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1582, 66, NULL, '2026-03-04', '09:40:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1583, 68, NULL, '2026-03-04', '09:28:00', '18:20:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1584, 69, NULL, '2026-03-04', '09:17:00', '18:42:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1585, 75, NULL, '2026-03-04', '09:35:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1586, 76, NULL, '2026-03-04', '09:43:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1587, 77, NULL, '2026-03-04', '09:04:00', '18:36:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1588, 83, NULL, '2026-03-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1589, 94, NULL, '2026-03-04', '09:23:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1590, 98, NULL, '2026-03-04', '09:32:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1591, 103, NULL, '2026-03-04', '09:02:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1592, 106, NULL, '2026-03-04', '09:58:00', '18:08:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1593, 67, NULL, '2026-03-04', '09:33:00', '18:59:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1594, 70, NULL, '2026-03-04', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1595, 71, NULL, '2026-03-04', '09:53:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1596, 74, NULL, '2026-03-04', '09:37:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1597, 81, NULL, '2026-03-04', '09:23:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1598, 82, NULL, '2026-03-04', '09:31:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1599, 84, NULL, '2026-03-04', '09:20:00', '18:52:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1600, 86, NULL, '2026-03-04', '09:08:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1601, 88, NULL, '2026-03-04', '09:44:00', '18:22:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1602, 96, NULL, '2026-03-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1603, 101, NULL, '2026-03-04', '09:16:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1604, 110, NULL, '2026-03-04', '09:56:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1605, 61, NULL, '2026-03-04', '09:00:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1606, 79, NULL, '2026-03-04', '09:22:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1607, 85, NULL, '2026-03-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1608, 91, NULL, '2026-03-04', '09:28:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1609, 95, NULL, '2026-03-04', '09:41:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1610, 100, NULL, '2026-03-04', '09:21:00', '18:51:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1611, 102, NULL, '2026-03-04', '09:34:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1612, 105, NULL, '2026-03-04', '09:53:00', '18:57:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1613, 108, NULL, '2026-03-04', '09:43:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1614, 59, NULL, '2026-03-04', '09:14:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1615, 72, NULL, '2026-03-04', '09:00:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1616, 73, NULL, '2026-03-04', '09:49:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1617, 80, NULL, '2026-03-04', '09:18:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1618, 87, NULL, '2026-03-04', NULL, NULL, 'absent', NULL, 'Uninformed', '2026-03-06 16:42:19'),
(1619, 89, NULL, '2026-03-04', '09:15:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1620, 93, NULL, '2026-03-04', '09:45:00', '18:12:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1621, 99, NULL, '2026-03-04', '09:29:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1622, 104, NULL, '2026-03-04', '09:34:00', '18:04:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1623, 107, NULL, '2026-03-04', '09:59:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1624, 109, NULL, '2026-03-04', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1625, 1, NULL, '2026-03-05', '09:40:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1626, 56, NULL, '2026-03-05', '09:15:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1627, 57, NULL, '2026-03-05', '09:12:00', '18:53:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1628, 58, NULL, '2026-03-05', '09:58:00', '18:47:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1629, 60, NULL, '2026-03-05', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1630, 63, NULL, '2026-03-05', '09:56:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1631, 64, NULL, '2026-03-05', '09:59:00', '18:19:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1632, 78, NULL, '2026-03-05', '09:23:00', '18:43:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1633, 90, NULL, '2026-03-05', '09:03:00', '18:16:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1634, 92, NULL, '2026-03-05', '09:26:00', '18:24:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1635, 97, NULL, '2026-03-05', '09:13:00', '18:03:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1636, 62, NULL, '2026-03-05', '09:15:00', '18:25:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1637, 65, NULL, '2026-03-05', '09:03:00', '18:06:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1638, 66, NULL, '2026-03-05', '09:17:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1639, 68, NULL, '2026-03-05', '09:19:00', '18:21:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1640, 69, NULL, '2026-03-05', '09:45:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1641, 75, NULL, '2026-03-05', '09:50:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1642, 76, NULL, '2026-03-05', '09:57:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1643, 77, NULL, '2026-03-05', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1644, 83, NULL, '2026-03-05', '09:15:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1645, 94, NULL, '2026-03-05', '09:19:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1646, 98, NULL, '2026-03-05', '09:04:00', '18:30:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1647, 103, NULL, '2026-03-05', '09:03:00', '18:50:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1648, 106, NULL, '2026-03-05', '09:38:00', '18:09:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1649, 67, NULL, '2026-03-05', '09:09:00', '18:10:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1650, 70, NULL, '2026-03-05', '09:12:00', '18:46:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1651, 71, NULL, '2026-03-05', '09:32:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1652, 74, NULL, '2026-03-05', '09:37:00', '18:01:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1653, 81, NULL, '2026-03-05', '09:03:00', '18:40:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1654, 82, NULL, '2026-03-05', '09:37:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1655, 84, NULL, '2026-03-05', '09:33:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1656, 86, NULL, '2026-03-05', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1657, 88, NULL, '2026-03-05', '09:25:00', '18:37:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1658, 96, NULL, '2026-03-05', '09:36:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1659, 101, NULL, '2026-03-05', '09:04:00', '18:34:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1660, 110, NULL, '2026-03-05', '09:58:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1661, 61, NULL, '2026-03-05', NULL, NULL, 'leave', NULL, 'Sick Leave', '2026-03-06 16:42:19'),
(1662, 79, NULL, '2026-03-05', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1663, 85, NULL, '2026-03-05', '09:49:00', '18:35:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1664, 91, NULL, '2026-03-05', '09:50:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1665, 95, NULL, '2026-03-05', '09:30:00', '13:30:00', 'half-day', NULL, 'Personal Work', '2026-03-06 16:42:19'),
(1666, 100, NULL, '2026-03-05', '09:27:00', '18:18:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1667, 102, NULL, '2026-03-05', '09:06:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1668, 105, NULL, '2026-03-05', '09:18:00', '18:41:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1669, 108, NULL, '2026-03-05', '09:52:00', '18:14:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1670, 59, NULL, '2026-03-05', '09:37:00', '18:45:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1671, 72, NULL, '2026-03-05', '09:46:00', '18:33:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1672, 73, NULL, '2026-03-05', '09:13:00', '18:00:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1673, 80, NULL, '2026-03-05', '09:39:00', '18:29:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1674, 87, NULL, '2026-03-05', '09:11:00', '18:31:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1675, 89, NULL, '2026-03-05', '09:47:00', '18:39:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1676, 93, NULL, '2026-03-05', '09:44:00', '18:55:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1677, 99, NULL, '2026-03-05', '09:09:00', '18:32:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1678, 104, NULL, '2026-03-05', '09:43:00', '18:44:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1679, 107, NULL, '2026-03-05', '09:02:00', '18:49:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1680, 109, NULL, '2026-03-05', '09:30:00', '18:23:00', 'present', NULL, 'Regular', '2026-03-06 16:42:19'),
(1702, 136, NULL, '2026-03-07', '21:52:34', '21:52:39', 'half-day', NULL, NULL, '2026-03-07 16:22:34'),
(1703, 162, 1, '2026-03-07', '21:58:52', '21:58:56', 'half-day', 'rfid', NULL, '2026-03-07 16:28:52'),
(1713, 125, 1, '2026-03-07', '22:36:32', '22:38:47', 'present', 'rfid', NULL, '2026-03-07 17:06:32'),
(1714, 156, 1, '2026-03-07', '22:38:33', '22:38:37', 'half-day', 'rfid', NULL, '2026-03-07 17:08:33'),
(1717, 125, 1, '2026-03-09', '20:32:16', '20:32:22', 'present', 'rfid', NULL, '2026-03-09 15:02:16'),
(1718, 162, 1, '2026-03-09', '20:32:28', NULL, 'present', 'rfid', NULL, '2026-03-09 15:02:28'),
(1719, 136, 1, '2026-03-09', '20:32:48', NULL, 'present', 'rfid', NULL, '2026-03-09 15:02:48');

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
(1, NULL, 'Aarav', 'Verma', 'aarav.verma517@example.com', '9876750406', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(2, NULL, 'Ishaan', 'Verma', 'ishaan.verma242@example.com', '9887223114', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(3, NULL, 'Sai', 'Malhotra', 'sai.malhotra584@example.com', '9893515046', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(4, NULL, 'Reyansh', 'Joshi', 'reyansh.joshi377@example.com', '9834108917', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(5, NULL, 'Aditya', 'Joshi', 'aditya.joshi746@example.com', '9876883119', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(6, NULL, 'Aarav', 'Sharma', 'aarav.sharma913@example.com', '9810218446', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(7, NULL, 'Arjun', 'Singh', 'arjun.singh208@example.com', '9874373391', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(8, NULL, 'Aditya', 'Mehta', 'aditya.mehta510@example.com', '9873897432', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(9, NULL, 'Ayaan', 'Sharma', 'ayaan.sharma355@example.com', '9897463149', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(10, NULL, 'Aarav', 'Patel', 'aarav.patel284@example.com', '9856418153', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(11, NULL, 'Vihaan', 'Bhatia', 'vihaan.bhatia877@example.com', '9817925789', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(12, NULL, 'Ishaan', 'Singh', 'ishaan.singh725@example.com', '9870006051', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(13, NULL, 'Sai', 'Bhatia', 'sai.bhatia523@example.com', '9814873637', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(14, NULL, 'Ishaan', 'Malhotra', 'ishaan.malhotra631@example.com', '9826680141', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(15, NULL, 'Aarav', 'Malhotra', 'aarav.malhotra991@example.com', '9846301619', NULL, NULL, 'applied', '2026-03-06 16:42:17', '2026-03-06 16:42:17'),
(16, NULL, 'Reyansh', 'Sharma', 'reyansh.sharma185@example.com', '9813160511', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(17, NULL, 'Arjun', 'Bhatia', 'arjun.bhatia223@example.com', '9826970482', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(18, NULL, 'Ayaan', 'Malhotra', 'ayaan.malhotra881@example.com', '9839512000', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(19, NULL, 'Ayaan', 'Verma', 'ayaan.verma406@example.com', '9874294422', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(20, NULL, 'Arjun', 'Sharma', 'arjun.sharma645@example.com', '9849876540', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(21, NULL, 'Ayaan', 'Patel', 'ayaan.patel421@example.com', '9869921445', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(22, NULL, 'Sai', 'Gupta', 'sai.gupta312@example.com', '9842544964', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(23, NULL, 'Sai', 'Bhatia', 'sai.bhatia145@example.com', '9878258189', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(24, NULL, 'Reyansh', 'Mehta', 'reyansh.mehta139@example.com', '9828042377', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(25, NULL, 'Aditya', 'Malhotra', 'aditya.malhotra910@example.com', '9876805012', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(26, NULL, 'Sai', 'Bhatia', 'sai.bhatia273@example.com', '9846819468', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(27, NULL, 'Ayaan', 'Joshi', 'ayaan.joshi826@example.com', '9885484882', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(28, NULL, 'Reyansh', 'Verma', 'reyansh.verma660@example.com', '9885876591', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(29, NULL, 'Aarav', 'Verma', 'aarav.verma981@example.com', '9820005541', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(30, NULL, 'Ayaan', 'Malhotra', 'ayaan.malhotra815@example.com', '9861958396', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(31, NULL, 'Reyansh', 'Saxena', 'reyansh.saxena148@example.com', '9853546914', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(32, NULL, 'Arjun', 'Saxena', 'arjun.saxena784@example.com', '9826749617', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(33, NULL, 'Ayaan', 'Saxena', 'ayaan.saxena777@example.com', '9863137683', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(34, NULL, 'Reyansh', 'Singh', 'reyansh.singh783@example.com', '9823233071', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(35, NULL, 'Ayaan', 'Sharma', 'ayaan.sharma600@example.com', '9897538150', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(36, NULL, 'Aarav', 'Gupta', 'aarav.gupta450@example.com', '9859796226', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(37, NULL, 'Vihaan', 'Gupta', 'vihaan.gupta222@example.com', '9862141822', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(38, NULL, 'Reyansh', 'Malhotra', 'reyansh.malhotra127@example.com', '9824298083', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(39, NULL, 'Krishna', 'Bhatia', 'krishna.bhatia133@example.com', '9823841838', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(40, NULL, 'Vivaan', 'Saxena', 'vivaan.saxena802@example.com', '9862729481', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(41, NULL, 'Reyansh', 'Sharma', 'reyansh.sharma209@example.com', '9856387927', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(42, NULL, 'Sai', 'Mehta', 'sai.mehta362@example.com', '9887672017', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(43, NULL, 'Aarav', 'Verma', 'aarav.verma347@example.com', '9897777072', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(44, NULL, 'Aditya', 'Bhatia', 'aditya.bhatia199@example.com', '9887127138', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(45, NULL, 'Vivaan', 'Mehta', 'vivaan.mehta290@example.com', '9863473603', NULL, NULL, 'applied', '2026-03-06 16:42:18', '2026-03-06 16:42:18');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subscription_status` enum('trial','active','expired','cancelled') DEFAULT 'trial',
  `trial_ends_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `address`, `email`, `phone`, `created_at`, `subscription_status`, `trial_ends_at`) VALUES
(1, 'Surya Holdings', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 'info@suryaholdings684.in', '+91 93339 80367', '2026-03-06 16:41:56', 'active', NULL),
(2, 'Ram Realty 4', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 'info@ramrealty4610.in', '+91 77516 37145', '2026-03-06 16:41:56', 'active', NULL),
(3, 'Indus Corporation', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 'info@induscorporation621.in', '+91 80015 28735', '2026-03-06 16:41:56', 'active', NULL),
(4, 'Lakshmi Textiles', 'Plot No 107, GIDC, Pune, Maharashtra - 411956', 'info@lakshmitextiles303.in', '+91 93233 14743', '2026-03-06 16:41:56', 'active', NULL),
(5, 'Chandra Ventures 86', 'Plot No 15, Business Hub, Patna, Bihar - 800775', 'info@chandraventures86637.in', '+91 98600 19783', '2026-03-06 16:41:56', 'active', NULL),
(6, 'Shakti Construct', 'Plot No 406, Ring Road, Jaipur, Rajasthan - 302891', 'info@shakticonstruct297.in', '+91 77904 34982', '2026-03-06 16:41:56', 'active', NULL),
(7, 'Ganga Works', 'Plot No 153, Market, Lucknow, Uttar Pradesh - 226709', 'info@gangaworks942.in', '+91 99924 24788', '2026-03-06 16:41:56', 'active', NULL),
(8, 'Durga Corporation', 'Plot No 463, Ring Road, Delhi, Delhi - 110791', 'info@durgacorporation546.in', '+91 79825 13269', '2026-03-06 16:41:56', 'active', NULL),
(9, 'Labh Associates 49', 'Plot No 440, Ring Road, Indore, Madhya Pradesh - 452215', 'info@labhassociates49835.in', '+91 92165 54215', '2026-03-06 16:41:56', 'active', NULL),
(10, 'Hindustan Systems', 'Plot No 196, Industrial Area, Thane, Maharashtra - 400183', 'info@hindustansystems854.in', '+91 84051 44518', '2026-03-06 16:41:56', 'active', NULL),
(11, 'Udyog Works', 'Plot No 184, Ring Road, Vadodara, Gujarat - 390692', 'info@udyogworks935.in', '+91 77128 99214', '2026-03-06 16:41:56', 'active', NULL),
(12, 'Shubh Systems', 'Plot No 92, Phase 2, Patna, Bihar - 800154', 'info@shubhsystems708.in', '+91 87587 76236', '2026-03-06 16:41:56', 'active', NULL),
(13, 'Shakti Services 38', 'Plot No 96, Sector 5, Nagpur, Maharashtra - 440547', 'info@shaktiservices38394.in', '+91 75662 05726', '2026-03-06 16:41:56', 'active', NULL),
(14, 'Anand Partners 62', 'Plot No 104, Sector 62, Visakhapatnam, Andhra Pradesh - 530476', 'info@anandpartners62375.in', '+91 71162 67362', '2026-03-06 16:41:56', 'active', NULL),
(15, 'Brahma Traders', 'Plot No 21, Ring Road, Chennai, Tamil Nadu - 600897', 'info@brahmatraders821.in', '+91 89047 84395', '2026-03-06 16:41:56', 'active', NULL),
(16, 'Vishnu Technologies 63', 'Plot No 201, Phase 2, Surat, Gujarat - 395434', 'info@vishnutechnologies63129.in', '+91 98406 83163', '2026-03-06 16:41:56', 'active', NULL),
(17, 'Surya Group 76', 'Plot No 233, GIDC, Bhopal, Madhya Pradesh - 462964', 'info@suryagroup76234.in', '+91 94531 45087', '2026-03-06 16:41:56', 'active', NULL),
(18, 'Kaveri Industries', 'Plot No 141, MIDC, Mumbai, Maharashtra - 400351', 'info@kaveriindustries600.in', '+91 97176 55110', '2026-03-06 16:41:56', 'active', NULL),
(19, 'Navyug Realty', 'Plot No 95, Ring Road, Chennai, Tamil Nadu - 600641', 'info@navyugrealty5.in', '+91 70951 14730', '2026-03-06 16:41:56', 'active', NULL),
(20, 'Jai Solutions', 'Plot No 17, Phase 2, Kanpur, Uttar Pradesh - 208937', 'info@jaisolutions106.in', '+91 81672 57544', '2026-03-06 16:41:56', 'active', NULL),
(21, 'Vyapar Agency', 'Plot No 249, Tech Park, Surat, Gujarat - 395325', 'info@vyaparagency322.in', '+91 96256 91953', '2026-03-06 16:41:56', 'active', NULL),
(22, 'Bharat Partners', 'Plot No 423, Main Road, Jaipur, Rajasthan - 302298', 'info@bharatpartners721.in', '+91 79115 19708', '2026-03-06 16:41:56', 'active', NULL),
(23, 'Jai Services 6', 'Plot No 216, MIDC, Thane, Maharashtra - 400751', 'info@jaiservices6915.in', '+91 82460 11049', '2026-03-06 16:41:56', 'active', NULL),
(24, 'Veda Traders', 'Plot No 132, Market, Chennai, Tamil Nadu - 600343', 'info@vedatraders929.in', '+91 90902 16783', '2026-03-06 16:41:56', 'active', NULL),
(25, 'Malwa Solutions', 'Plot No 471, Sector 62, Lucknow, Uttar Pradesh - 226655', 'info@malwasolutions486.in', '+91 83620 82303', '2026-03-06 16:41:56', 'active', NULL),
(26, 'Veda Industries', 'Plot No 286, Business Hub, Hyderabad, Telangana - 500658', 'info@vedaindustries50.in', '+91 79760 35816', '2026-03-06 16:41:56', 'active', NULL),
(27, 'Indus Works 84', 'Plot No 377, Business Hub, Bangalore, Karnataka - 560482', 'info@indusworks84683.in', '+91 88433 68838', '2026-03-06 16:41:56', 'active', NULL),
(28, 'Surya Works', 'Plot No 386, Sector 5, Patna, Bihar - 800906', 'info@suryaworks295.in', '+91 78184 55102', '2026-03-06 16:41:56', 'active', NULL),
(29, 'Saraswati Services', 'Plot No 188, Tech Park, Kanpur, Uttar Pradesh - 208967', 'info@saraswatiservices684.in', '+91 75253 47474', '2026-03-06 16:41:56', 'active', NULL),
(30, 'Sarvottam Textiles', 'Plot No 488, Tech Park, Lucknow, Uttar Pradesh - 226995', 'info@sarvottamtextiles633.in', '+91 78442 74590', '2026-03-06 16:41:56', 'active', NULL),
(31, 'Ganga Ventures', 'Plot No 183, Industrial Area, Indore, Madhya Pradesh - 452960', 'info@gangaventures584.in', '+91 97410 91810', '2026-03-06 16:41:56', 'active', NULL),
(32, 'Pragati Group', 'Plot No 414, Main Road, Pune, Maharashtra - 411156', 'info@pragatigroup408.in', '+91 96290 11596', '2026-03-06 16:41:56', 'active', NULL),
(33, 'Shreshth Services', 'Plot No 233, MIDC, Bangalore, Karnataka - 560679', 'info@shreshthservices943.in', '+91 91882 23030', '2026-03-06 16:41:56', 'active', NULL),
(34, 'Navyug Logistics 52', 'Plot No 352, Business Hub, Ahmedabad, Gujarat - 380436', 'info@navyuglogistics52779.in', '+91 94730 05278', '2026-03-06 16:41:56', 'active', NULL),
(35, 'Udyog Works', 'Plot No 18, Business Hub, Ahmedabad, Gujarat - 380114', 'info@udyogworks321.in', '+91 88671 98046', '2026-03-06 16:41:56', 'active', NULL),
(36, 'Anand Associates 2', 'Plot No 487, Phase 1, Delhi, Delhi - 110481', 'info@anandassociates2338.in', '+91 97666 29509', '2026-03-06 16:41:56', 'active', NULL),
(37, 'Vindhya Realty 54', 'Plot No 377, GIDC, Vadodara, Gujarat - 390646', 'info@vindhyarealty54213.in', '+91 81024 32746', '2026-03-06 16:41:56', 'active', NULL),
(38, 'Amrit Exports', 'Plot No 430, MIDC, Bhopal, Madhya Pradesh - 462138', 'info@amritexports221.in', '+91 97007 34700', '2026-03-06 16:41:56', 'active', NULL),
(39, 'Utkarsh Traders 50', 'Plot No 427, Station Road, Hyderabad, Telangana - 500157', 'info@utkarshtraders50837.in', '+91 87165 45549', '2026-03-06 16:41:56', 'active', NULL),
(40, 'Shreshth Associates', 'Plot No 87, Industrial Area, Ahmedabad, Gujarat - 380718', 'info@shreshthassociates339.in', '+91 84678 83448', '2026-03-06 16:41:56', 'active', NULL),
(41, 'Brahma Industries', 'Plot No 486, Tech Park, Kanpur, Uttar Pradesh - 208463', 'info@brahmaindustries120.in', '+91 96728 93964', '2026-03-06 16:41:56', 'active', NULL),
(42, 'Navyug Services', 'Plot No 496, Industrial Area, Surat, Gujarat - 395691', 'info@navyugservices113.in', '+91 73677 81552', '2026-03-06 16:41:56', 'active', NULL),
(43, 'Satpura Industries 42', 'Plot No 11, Phase 2, Pune, Maharashtra - 411543', 'info@satpuraindustries4228.in', '+91 70389 45433', '2026-03-06 16:41:56', 'active', NULL),
(44, 'Krishna Construct', 'Plot No 17, Business Hub, Bhopal, Madhya Pradesh - 462562', 'info@krishnaconstruct306.in', '+91 84348 90007', '2026-03-06 16:41:56', 'active', NULL),
(45, 'Malwa Partners', 'Plot No 220, Main Road, Bangalore, Karnataka - 560816', 'info@malwapartners896.in', '+91 76257 31945', '2026-03-06 16:41:56', 'active', NULL),
(46, 'Shakti Holdings', 'Plot No 466, MIDC, Jaipur, Rajasthan - 302118', 'info@shaktiholdings767.in', '+91 71901 66470', '2026-03-06 16:41:56', 'active', NULL),
(47, 'Himalaya Holdings', 'Plot No 235, Sector 5, Jaipur, Rajasthan - 302122', 'info@himalayaholdings713.in', '+91 91925 20893', '2026-03-06 16:41:56', 'active', NULL),
(48, 'Sarvottam Systems', 'Plot No 8, Station Road, Vadodara, Gujarat - 390771', 'info@sarvottamsystems199.in', '+91 87823 52898', '2026-03-06 16:41:56', 'active', NULL),
(49, 'Durga Ventures', 'Plot No 82, Ring Road, Lucknow, Uttar Pradesh - 226303', 'info@durgaventures441.in', '+91 75319 61008', '2026-03-06 16:41:56', 'active', NULL),
(50, 'Ganga Technologies', 'Plot No 421, Main Road, Mumbai, Maharashtra - 400724', 'info@gangatechnologies944.in', '+91 82328 78876', '2026-03-06 16:41:56', 'active', NULL),
(51, 'Ram Solutions', 'Plot No 73, MIDC, Chennai, Tamil Nadu - 600296', 'info@ramsolutions166.in', '+91 85968 22420', '2026-03-06 16:41:56', 'active', NULL),
(52, 'Godavari Group', 'Plot No 250, Industrial Area, Delhi, Delhi - 110207', 'info@godavarigroup745.in', '+91 74192 01608', '2026-03-06 16:41:56', 'active', NULL),
(53, 'Amrit Technologies', 'Plot No 148, Main Road, Nagpur, Maharashtra - 440283', 'info@amrittechnologies941.in', '+91 76712 00011', '2026-03-06 16:41:56', 'active', NULL),
(54, 'Surya Associates', 'Plot No 96, Tech Park, Hyderabad, Telangana - 500141', 'info@suryaassociates391.in', '+91 91883 35067', '2026-03-06 16:41:56', 'active', NULL),
(55, 'Saraswati Traders', 'Plot No 196, Sector 62, Pune, Maharashtra - 411420', 'info@saraswatitraders194.in', '+91 94928 30361', '2026-03-06 16:41:56', 'active', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `company_holiday_settings`
--

CREATE TABLE `company_holiday_settings` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `saturday_policy` enum('none','2nd_4th','1st_3rd','all') NOT NULL DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 'Administration', 'Company Administration'),
(2, 2, 'Administration', 'Company Administration'),
(3, 3, 'Administration', 'Company Administration'),
(4, 4, 'Administration', 'Company Administration'),
(5, 5, 'Administration', 'Company Administration'),
(6, 6, 'Administration', 'Company Administration'),
(7, 7, 'Administration', 'Company Administration'),
(8, 8, 'Administration', 'Company Administration'),
(9, 9, 'Administration', 'Company Administration'),
(10, 10, 'Administration', 'Company Administration'),
(11, 11, 'Administration', 'Company Administration'),
(12, 12, 'Administration', 'Company Administration'),
(13, 13, 'Administration', 'Company Administration'),
(14, 14, 'Administration', 'Company Administration'),
(15, 15, 'Administration', 'Company Administration'),
(16, 16, 'Administration', 'Company Administration'),
(17, 17, 'Administration', 'Company Administration'),
(18, 18, 'Administration', 'Company Administration'),
(19, 19, 'Administration', 'Company Administration'),
(20, 20, 'Administration', 'Company Administration'),
(21, 21, 'Administration', 'Company Administration'),
(22, 22, 'Administration', 'Company Administration'),
(23, 23, 'Administration', 'Company Administration'),
(24, 24, 'Administration', 'Company Administration'),
(25, 25, 'Administration', 'Company Administration'),
(26, 26, 'Administration', 'Company Administration'),
(27, 27, 'Administration', 'Company Administration'),
(28, 28, 'Administration', 'Company Administration'),
(29, 29, 'Administration', 'Company Administration'),
(30, 30, 'Administration', 'Company Administration'),
(31, 31, 'Administration', 'Company Administration'),
(32, 32, 'Administration', 'Company Administration'),
(33, 33, 'Administration', 'Company Administration'),
(34, 34, 'Administration', 'Company Administration'),
(35, 35, 'Administration', 'Company Administration'),
(36, 36, 'Administration', 'Company Administration'),
(37, 37, 'Administration', 'Company Administration'),
(38, 38, 'Administration', 'Company Administration'),
(39, 39, 'Administration', 'Company Administration'),
(40, 40, 'Administration', 'Company Administration'),
(41, 41, 'Administration', 'Company Administration'),
(42, 42, 'Administration', 'Company Administration'),
(43, 43, 'Administration', 'Company Administration'),
(44, 44, 'Administration', 'Company Administration'),
(45, 45, 'Administration', 'Company Administration'),
(46, 46, 'Administration', 'Company Administration'),
(47, 47, 'Administration', 'Company Administration'),
(48, 48, 'Administration', 'Company Administration'),
(49, 49, 'Administration', 'Company Administration'),
(50, 50, 'Administration', 'Company Administration'),
(51, 51, 'Administration', 'Company Administration'),
(52, 52, 'Administration', 'Company Administration'),
(53, 53, 'Administration', 'Company Administration'),
(54, 54, 'Administration', 'Company Administration'),
(55, 55, 'Administration', 'Company Administration'),
(56, 1, 'Human Resources', NULL),
(57, 1, 'Information Technology', NULL),
(58, 1, 'Sales & Marketing', NULL),
(59, 1, 'Finance', NULL),
(60, 1, 'Operations', NULL),
(61, 2, 'Human Resources', NULL),
(62, 2, 'Information Technology', NULL),
(63, 2, 'Sales & Marketing', NULL),
(64, 2, 'Finance', NULL),
(65, 2, 'Operations', NULL),
(66, 3, 'Human Resources', NULL),
(67, 3, 'Information Technology', NULL),
(68, 3, 'Sales & Marketing', NULL),
(69, 3, 'Finance', NULL),
(70, 3, 'Operations', NULL);

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
(1, 1, 'CEO / Owner', 'Company Owner'),
(2, 2, 'CEO / Owner', 'Company Owner'),
(3, 3, 'CEO / Owner', 'Company Owner'),
(4, 4, 'CEO / Owner', 'Company Owner'),
(5, 5, 'CEO / Owner', 'Company Owner'),
(6, 6, 'CEO / Owner', 'Company Owner'),
(7, 7, 'CEO / Owner', 'Company Owner'),
(8, 8, 'CEO / Owner', 'Company Owner'),
(9, 9, 'CEO / Owner', 'Company Owner'),
(10, 10, 'CEO / Owner', 'Company Owner'),
(11, 11, 'CEO / Owner', 'Company Owner'),
(12, 12, 'CEO / Owner', 'Company Owner'),
(13, 13, 'CEO / Owner', 'Company Owner'),
(14, 14, 'CEO / Owner', 'Company Owner'),
(15, 15, 'CEO / Owner', 'Company Owner'),
(16, 16, 'CEO / Owner', 'Company Owner'),
(17, 17, 'CEO / Owner', 'Company Owner'),
(18, 18, 'CEO / Owner', 'Company Owner'),
(19, 19, 'CEO / Owner', 'Company Owner'),
(20, 20, 'CEO / Owner', 'Company Owner'),
(21, 21, 'CEO / Owner', 'Company Owner'),
(22, 22, 'CEO / Owner', 'Company Owner'),
(23, 23, 'CEO / Owner', 'Company Owner'),
(24, 24, 'CEO / Owner', 'Company Owner'),
(25, 25, 'CEO / Owner', 'Company Owner'),
(26, 26, 'CEO / Owner', 'Company Owner'),
(27, 27, 'CEO / Owner', 'Company Owner'),
(28, 28, 'CEO / Owner', 'Company Owner'),
(29, 29, 'CEO / Owner', 'Company Owner'),
(30, 30, 'CEO / Owner', 'Company Owner'),
(31, 31, 'CEO / Owner', 'Company Owner'),
(32, 32, 'CEO / Owner', 'Company Owner'),
(33, 33, 'CEO / Owner', 'Company Owner'),
(34, 34, 'CEO / Owner', 'Company Owner'),
(35, 35, 'CEO / Owner', 'Company Owner'),
(36, 36, 'CEO / Owner', 'Company Owner'),
(37, 37, 'CEO / Owner', 'Company Owner'),
(38, 38, 'CEO / Owner', 'Company Owner'),
(39, 39, 'CEO / Owner', 'Company Owner'),
(40, 40, 'CEO / Owner', 'Company Owner'),
(41, 41, 'CEO / Owner', 'Company Owner'),
(42, 42, 'CEO / Owner', 'Company Owner'),
(43, 43, 'CEO / Owner', 'Company Owner'),
(44, 44, 'CEO / Owner', 'Company Owner'),
(45, 45, 'CEO / Owner', 'Company Owner'),
(46, 46, 'CEO / Owner', 'Company Owner'),
(47, 47, 'CEO / Owner', 'Company Owner'),
(48, 48, 'CEO / Owner', 'Company Owner'),
(49, 49, 'CEO / Owner', 'Company Owner'),
(50, 50, 'CEO / Owner', 'Company Owner'),
(51, 51, 'CEO / Owner', 'Company Owner'),
(52, 52, 'CEO / Owner', 'Company Owner'),
(53, 53, 'CEO / Owner', 'Company Owner'),
(54, 54, 'CEO / Owner', 'Company Owner'),
(55, 55, 'CEO / Owner', 'Company Owner'),
(56, 56, 'HR Manager', NULL),
(57, 56, 'HR Executive', NULL),
(58, 56, 'Recruiter', NULL),
(59, 57, 'IT Manager', NULL),
(60, 57, 'Senior Developer', NULL),
(61, 57, 'Junior Developer', NULL),
(62, 57, 'System Admin', NULL),
(63, 57, 'QA Engineer', NULL),
(64, 58, 'Sales Manager', NULL),
(65, 58, 'Marketing Executive', NULL),
(66, 58, 'Sales Representative', NULL),
(67, 59, 'Finance Manager', NULL),
(68, 59, 'Accountant', NULL),
(69, 59, 'Payroll Specialist', NULL),
(70, 60, 'Operations Manager', NULL),
(71, 60, 'Supervisor', NULL),
(72, 60, 'Operations Executive', NULL),
(73, 61, 'HR Manager', NULL),
(74, 61, 'HR Executive', NULL),
(75, 61, 'Recruiter', NULL),
(76, 62, 'IT Manager', NULL),
(77, 62, 'Senior Developer', NULL),
(78, 62, 'Junior Developer', NULL),
(79, 62, 'System Admin', NULL),
(80, 62, 'QA Engineer', NULL),
(81, 63, 'Sales Manager', NULL),
(82, 63, 'Marketing Executive', NULL),
(83, 63, 'Sales Representative', NULL),
(84, 64, 'Finance Manager', NULL),
(85, 64, 'Accountant', NULL),
(86, 64, 'Payroll Specialist', NULL),
(87, 65, 'Operations Manager', NULL),
(88, 65, 'Supervisor', NULL),
(89, 65, 'Operations Executive', NULL),
(90, 66, 'HR Manager', NULL),
(91, 66, 'HR Executive', NULL),
(92, 66, 'Recruiter', NULL),
(93, 67, 'IT Manager', NULL),
(94, 67, 'Senior Developer', NULL),
(95, 67, 'Junior Developer', NULL),
(96, 67, 'System Admin', NULL),
(97, 67, 'QA Engineer', NULL),
(98, 68, 'Sales Manager', NULL),
(99, 68, 'Marketing Executive', NULL),
(100, 68, 'Sales Representative', NULL),
(101, 69, 'Finance Manager', NULL),
(102, 69, 'Accountant', NULL),
(103, 69, 'Payroll Specialist', NULL),
(104, 70, 'Operations Manager', NULL),
(105, 70, 'Supervisor', NULL),
(106, 70, 'Operations Executive', NULL);

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

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `to_email` varchar(255) NOT NULL,
  `to_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `alt_body` longtext DEFAULT NULL,
  `status` enum('pending','processing','sent','failed') NOT NULL DEFAULT 'pending',
  `attempts` int(11) NOT NULL DEFAULT 0,
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `salary` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `employee_code`, `first_name`, `last_name`, `dob`, `gender`, `contact`, `address`, `department_id`, `designation_id`, `shift_id`, `date_of_joining`, `status`, `salary`, `created_at`) VALUES
(1, 2, 'ADM-001', 'Surya', 'Holdings', '1990-06-05', 'male', '+91 93339 80367', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 1, 1, 1, '2026-03-06', 'active', 97915.00, '2026-03-06 16:41:56'),
(2, 3, 'ADM-002', 'Ram', 'Realty', '1975-02-23', 'male', '+91 77516 37145', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 2, 2, 2, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(3, 4, 'ADM-003', 'Indus', 'Corporation', '1989-05-18', 'female', '+91 80015 28735', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 3, 3, 3, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(4, 5, 'ADM-004', 'Lakshmi', 'Textiles', '1989-05-21', 'male', '+91 93233 14743', 'Plot No 107, GIDC, Pune, Maharashtra - 411956', 4, 4, 4, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(5, 6, 'ADM-005', 'Chandra', 'Ventures', '1979-04-26', 'male', '+91 98600 19783', 'Plot No 15, Business Hub, Patna, Bihar - 800775', 5, 5, 5, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(6, 7, 'ADM-006', 'Shakti', 'Construct', '1976-03-17', 'female', '+91 77904 34982', 'Plot No 406, Ring Road, Jaipur, Rajasthan - 302891', 6, 6, 6, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(7, 8, 'ADM-007', 'Ganga', 'Works', '1988-10-01', 'male', '+91 99924 24788', 'Plot No 153, Market, Lucknow, Uttar Pradesh - 226709', 7, 7, 7, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(8, 9, 'ADM-008', 'Durga', 'Corporation', '1995-12-13', 'female', '+91 79825 13269', 'Plot No 463, Ring Road, Delhi, Delhi - 110791', 8, 8, 8, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(9, 10, 'ADM-009', 'Labh', 'Associates', '1977-11-06', 'female', '+91 92165 54215', 'Plot No 440, Ring Road, Indore, Madhya Pradesh - 452215', 9, 9, 9, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(10, 11, 'ADM-010', 'Hindustan', 'Systems', '1991-07-10', 'female', '+91 84051 44518', 'Plot No 196, Industrial Area, Thane, Maharashtra - 400183', 10, 10, 10, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:56'),
(11, 12, 'ADM-011', 'Udyog', 'Works', '1984-03-08', 'male', '+91 77128 99214', 'Plot No 184, Ring Road, Vadodara, Gujarat - 390692', 11, 11, 11, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(12, 13, 'ADM-012', 'Shubh', 'Systems', '1992-02-06', 'female', '+91 87587 76236', 'Plot No 92, Phase 2, Patna, Bihar - 800154', 12, 12, 12, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(13, 14, 'ADM-013', 'Shakti', 'Services', '1976-11-27', 'male', '+91 75662 05726', 'Plot No 96, Sector 5, Nagpur, Maharashtra - 440547', 13, 13, 13, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(14, 15, 'ADM-014', 'Anand', 'Partners', '1991-10-19', 'female', '+91 71162 67362', 'Plot No 104, Sector 62, Visakhapatnam, Andhra Pradesh - 530476', 14, 14, 14, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(15, 16, 'ADM-015', 'Brahma', 'Traders', '1986-09-07', 'female', '+91 89047 84395', 'Plot No 21, Ring Road, Chennai, Tamil Nadu - 600897', 15, 15, 15, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(16, 17, 'ADM-016', 'Vishnu', 'Technologies', '1979-05-14', 'male', '+91 98406 83163', 'Plot No 201, Phase 2, Surat, Gujarat - 395434', 16, 16, 16, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(17, 18, 'ADM-017', 'Surya', 'Group', '1991-05-01', 'female', '+91 94531 45087', 'Plot No 233, GIDC, Bhopal, Madhya Pradesh - 462964', 17, 17, 17, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(18, 19, 'ADM-018', 'Kaveri', 'Industries', '1985-06-06', 'female', '+91 97176 55110', 'Plot No 141, MIDC, Mumbai, Maharashtra - 400351', 18, 18, 18, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(19, 20, 'ADM-019', 'Navyug', 'Realty', '1979-08-08', 'female', '+91 70951 14730', 'Plot No 95, Ring Road, Chennai, Tamil Nadu - 600641', 19, 19, 19, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(20, 21, 'ADM-020', 'Jai', 'Solutions', '1983-05-04', 'male', '+91 81672 57544', 'Plot No 17, Phase 2, Kanpur, Uttar Pradesh - 208937', 20, 20, 20, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(21, 22, 'ADM-021', 'Vyapar', 'Agency', '1977-08-12', 'female', '+91 96256 91953', 'Plot No 249, Tech Park, Surat, Gujarat - 395325', 21, 21, 21, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(22, 23, 'ADM-022', 'Bharat', 'Partners', '1983-07-05', 'female', '+91 79115 19708', 'Plot No 423, Main Road, Jaipur, Rajasthan - 302298', 22, 22, 22, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(23, 24, 'ADM-023', 'Jai', 'Services', '1978-08-14', 'male', '+91 82460 11049', 'Plot No 216, MIDC, Thane, Maharashtra - 400751', 23, 23, 23, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(24, 25, 'ADM-024', 'Veda', 'Traders', '1982-07-01', 'female', '+91 90902 16783', 'Plot No 132, Market, Chennai, Tamil Nadu - 600343', 24, 24, 24, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(25, 26, 'ADM-025', 'Malwa', 'Solutions', '1987-12-18', 'male', '+91 83620 82303', 'Plot No 471, Sector 62, Lucknow, Uttar Pradesh - 226655', 25, 25, 25, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(26, 27, 'ADM-026', 'Veda', 'Industries', '1993-05-06', 'male', '+91 79760 35816', 'Plot No 286, Business Hub, Hyderabad, Telangana - 500658', 26, 26, 26, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:57'),
(27, 28, 'ADM-027', 'Indus', 'Works', '1989-07-10', 'male', '+91 88433 68838', 'Plot No 377, Business Hub, Bangalore, Karnataka - 560482', 27, 27, 27, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(28, 29, 'ADM-028', 'Surya', 'Works', '1993-07-24', 'male', '+91 78184 55102', 'Plot No 386, Sector 5, Patna, Bihar - 800906', 28, 28, 28, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(29, 30, 'ADM-029', 'Saraswati', 'Services', '1987-09-04', 'male', '+91 75253 47474', 'Plot No 188, Tech Park, Kanpur, Uttar Pradesh - 208967', 29, 29, 29, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(30, 31, 'ADM-030', 'Sarvottam', 'Textiles', '1992-09-26', 'male', '+91 78442 74590', 'Plot No 488, Tech Park, Lucknow, Uttar Pradesh - 226995', 30, 30, 30, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(31, 32, 'ADM-031', 'Ganga', 'Ventures', '1977-02-19', 'male', '+91 97410 91810', 'Plot No 183, Industrial Area, Indore, Madhya Pradesh - 452960', 31, 31, 31, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(32, 33, 'ADM-032', 'Pragati', 'Group', '1979-01-22', 'male', '+91 96290 11596', 'Plot No 414, Main Road, Pune, Maharashtra - 411156', 32, 32, 32, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(33, 34, 'ADM-033', 'Shreshth', 'Services', '1979-02-20', 'male', '+91 91882 23030', 'Plot No 233, MIDC, Bangalore, Karnataka - 560679', 33, 33, 33, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(34, 35, 'ADM-034', 'Navyug', 'Logistics', '1980-08-15', 'male', '+91 94730 05278', 'Plot No 352, Business Hub, Ahmedabad, Gujarat - 380436', 34, 34, 34, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(35, 36, 'ADM-035', 'Udyog', 'Works', '1982-06-22', 'female', '+91 88671 98046', 'Plot No 18, Business Hub, Ahmedabad, Gujarat - 380114', 35, 35, 35, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(36, 37, 'ADM-036', 'Anand', 'Associates', '1979-11-18', 'male', '+91 97666 29509', 'Plot No 487, Phase 1, Delhi, Delhi - 110481', 36, 36, 36, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(37, 38, 'ADM-037', 'Vindhya', 'Realty', '1986-05-21', 'male', '+91 81024 32746', 'Plot No 377, GIDC, Vadodara, Gujarat - 390646', 37, 37, 37, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(38, 39, 'ADM-038', 'Amrit', 'Exports', '1985-03-25', 'female', '+91 97007 34700', 'Plot No 430, MIDC, Bhopal, Madhya Pradesh - 462138', 38, 38, 38, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(39, 40, 'ADM-039', 'Utkarsh', 'Traders', '1981-08-18', 'female', '+91 87165 45549', 'Plot No 427, Station Road, Hyderabad, Telangana - 500157', 39, 39, 39, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(40, 41, 'ADM-040', 'Shreshth', 'Associates', '1987-12-07', 'male', '+91 84678 83448', 'Plot No 87, Industrial Area, Ahmedabad, Gujarat - 380718', 40, 40, 40, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(41, 42, 'ADM-041', 'Brahma', 'Industries', '1987-04-11', 'male', '+91 96728 93964', 'Plot No 486, Tech Park, Kanpur, Uttar Pradesh - 208463', 41, 41, 41, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(42, 43, 'ADM-042', 'Navyug', 'Services', '1976-08-13', 'female', '+91 73677 81552', 'Plot No 496, Industrial Area, Surat, Gujarat - 395691', 42, 42, 42, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:58'),
(43, 44, 'ADM-043', 'Satpura', 'Industries', '1982-05-10', 'male', '+91 70389 45433', 'Plot No 11, Phase 2, Pune, Maharashtra - 411543', 43, 43, 43, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(44, 45, 'ADM-044', 'Krishna', 'Construct', '1991-04-08', 'female', '+91 84348 90007', 'Plot No 17, Business Hub, Bhopal, Madhya Pradesh - 462562', 44, 44, 44, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(45, 46, 'ADM-045', 'Malwa', 'Partners', '1990-10-23', 'male', '+91 76257 31945', 'Plot No 220, Main Road, Bangalore, Karnataka - 560816', 45, 45, 45, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(46, 47, 'ADM-046', 'Shakti', 'Holdings', '1983-04-17', 'male', '+91 71901 66470', 'Plot No 466, MIDC, Jaipur, Rajasthan - 302118', 46, 46, 46, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(47, 48, 'ADM-047', 'Himalaya', 'Holdings', '1975-06-01', 'female', '+91 91925 20893', 'Plot No 235, Sector 5, Jaipur, Rajasthan - 302122', 47, 47, 47, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(48, 49, 'ADM-048', 'Sarvottam', 'Systems', '1980-03-03', 'male', '+91 87823 52898', 'Plot No 8, Station Road, Vadodara, Gujarat - 390771', 48, 48, 48, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(49, 50, 'ADM-049', 'Durga', 'Ventures', '1975-07-05', 'male', '+91 75319 61008', 'Plot No 82, Ring Road, Lucknow, Uttar Pradesh - 226303', 49, 49, 49, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(50, 51, 'ADM-050', 'Ganga', 'Technologies', '1976-09-09', 'female', '+91 82328 78876', 'Plot No 421, Main Road, Mumbai, Maharashtra - 400724', 50, 50, 50, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(51, 52, 'ADM-051', 'Ram', 'Solutions', '1994-04-14', 'male', '+91 85968 22420', 'Plot No 73, MIDC, Chennai, Tamil Nadu - 600296', 51, 51, 51, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(52, 53, 'ADM-052', 'Godavari', 'Group', '1981-11-26', 'female', '+91 74192 01608', 'Plot No 250, Industrial Area, Delhi, Delhi - 110207', 52, 52, 52, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(53, 54, 'ADM-053', 'Amrit', 'Technologies', '1992-01-23', 'female', '+91 76712 00011', 'Plot No 148, Main Road, Nagpur, Maharashtra - 440283', 53, 53, 53, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(54, 55, 'ADM-054', 'Surya', 'Associates', '1987-04-19', 'female', '+91 91883 35067', 'Plot No 96, Tech Park, Hyderabad, Telangana - 500141', 54, 54, 54, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(55, 56, 'ADM-055', 'Saraswati', 'Traders', '1987-11-19', 'male', '+91 94928 30361', 'Plot No 196, Sector 62, Pune, Maharashtra - 411420', 55, 55, 55, '2026-03-06', 'active', 0.00, '2026-03-06 16:41:59'),
(56, 57, 'EMP-1-57', 'Nitin', 'Desai', '1999-12-03', 'female', '9585057509', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 56, 56, '2024-07-14', 'active', 54812.00, '2026-03-06 16:41:59'),
(57, 58, 'EMP-1-58', 'Deepak', 'Siddiqui', '1994-01-11', 'female', '9874185569', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 57, 57, '2025-06-15', 'active', 51948.00, '2026-03-06 16:42:00'),
(58, 59, 'EMP-1-59', 'Jatin', 'Kapoor', '1996-01-10', 'female', '9486286484', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 58, 1, '2024-04-20', 'active', 137626.00, '2026-03-06 16:42:00'),
(59, 60, 'EMP-1-60', 'Vihaan', 'Chopra', '1981-02-11', 'male', '9640091946', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 57, '2025-05-11', 'active', 38585.00, '2026-03-06 16:42:00'),
(60, 61, 'EMP-1-61', 'Swati', 'Pathan', '1989-03-12', 'female', '9598077564', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 56, 56, '2025-04-13', 'active', 55329.00, '2026-03-06 16:42:00'),
(61, 62, 'EMP-1-62', 'Preeti', 'More', '1995-02-13', 'female', '9183416100', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 69, 1, '2024-03-09', 'active', 41587.00, '2026-03-06 16:42:00'),
(62, 63, 'EMP-1-63', 'Aditya', 'Gupta', '2000-05-12', 'male', '9689768691', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 59, 57, '2024-11-26', 'active', 145973.00, '2026-03-06 16:42:00'),
(63, 64, 'EMP-1-64', 'Rajesh', 'Das', '1985-01-12', 'female', '9478075346', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 56, 56, '2024-03-05', 'active', 85211.00, '2026-03-06 16:42:00'),
(64, 65, 'EMP-1-65', 'Priya', 'Tiwari', '1989-11-07', 'female', '9545672567', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 58, 57, '2024-01-12', 'active', 95295.00, '2026-03-06 16:42:00'),
(65, 66, 'EMP-1-66', 'Amit', 'Siddiqui', '1986-05-06', 'male', '9535100409', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 63, 57, '2024-03-07', 'active', 121633.00, '2026-03-06 16:42:00'),
(66, 67, 'EMP-1-67', 'Kavita', 'Reddy', '1992-11-01', 'female', '9967068209', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 63, 57, '2025-11-16', 'active', 102551.00, '2026-03-06 16:42:00'),
(67, 68, 'EMP-1-68', 'Aarav', 'Yadav', '1988-09-25', 'female', '9974654158', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 66, 1, '2024-03-12', 'active', 46421.00, '2026-03-06 16:42:00'),
(68, 69, 'EMP-1-69', 'Suresh', 'Saxena', '1982-03-25', 'female', '9764828951', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 61, 1, '2022-06-22', 'active', 103956.00, '2026-03-06 16:42:00'),
(69, 70, 'EMP-1-70', 'Ramesh', 'Nair', '1999-08-11', 'male', '9682214476', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 61, 57, '2023-11-28', 'active', 51327.00, '2026-03-06 16:42:00'),
(70, 71, 'EMP-1-71', 'Amit', 'Tripathi', '1982-01-06', 'female', '9841327307', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 65, 56, '2025-02-03', 'active', 58061.00, '2026-03-06 16:42:00'),
(71, 72, 'EMP-1-72', 'Kapil', 'Iyer', '1986-06-06', 'male', '9889518164', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 64, 1, '2024-04-03', 'active', 82448.00, '2026-03-06 16:42:00'),
(72, 73, 'EMP-1-73', 'Jyoti', 'Ansari', '1996-08-09', 'male', '9480269747', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 71, 57, '2025-12-02', 'active', 85168.00, '2026-03-06 16:42:01'),
(73, 74, 'EMP-1-74', 'Sneha', 'Roy', '2000-09-28', 'male', '9639991179', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 72, 1, '2022-05-07', 'active', 73838.00, '2026-03-06 16:42:01'),
(74, 75, 'EMP-1-75', 'Nitin', 'Shinde', '1990-02-10', 'female', '9164800891', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 64, 56, '2022-01-04', 'active', 101717.00, '2026-03-06 16:42:01'),
(75, 76, 'EMP-1-76', 'Nitin', 'Kapoor', '1997-01-24', 'female', '9817512048', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 63, 57, '2023-05-06', 'active', 117954.00, '2026-03-06 16:42:01'),
(76, 77, 'EMP-1-77', 'Sana', 'Kumar', '1992-02-03', 'female', '9331918253', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 61, 57, '2025-09-10', 'active', 52755.00, '2026-03-06 16:42:01'),
(77, 78, 'EMP-1-78', 'Vikram', 'Roy', '1983-03-16', 'male', '9423493986', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 63, 57, '2025-02-08', 'active', 80148.00, '2026-03-06 16:42:01'),
(78, 79, 'EMP-1-79', 'Sunil', 'Khan', '1993-05-02', 'female', '9678786838', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 57, 57, '2023-01-15', 'active', 61181.00, '2026-03-06 16:42:01'),
(79, 80, 'EMP-1-80', 'Vivek', 'Pathan', '1982-05-14', 'male', '9616294514', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 68, 57, '2025-11-21', 'active', 139463.00, '2026-03-06 16:42:01'),
(80, 81, 'EMP-1-81', 'Varun', 'Pandey', '1981-07-13', 'male', '9504192001', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 57, '2024-08-02', 'active', 87744.00, '2026-03-06 16:42:01'),
(81, 82, 'EMP-1-82', 'Riya', 'Kumar', '1985-07-13', 'female', '9288252904', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 65, 56, '2022-02-15', 'active', 77337.00, '2026-03-06 16:42:01'),
(82, 83, 'EMP-1-83', 'Aditya', 'Mishra', '1986-12-27', 'male', '9361100149', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 66, 56, '2022-12-26', 'active', 99397.00, '2026-03-06 16:42:01'),
(83, 84, 'EMP-1-84', 'Tarun', 'Singh', '1992-08-23', 'female', '9183232734', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 60, 1, '2023-08-20', 'active', 106973.00, '2026-03-06 16:42:01'),
(84, 85, 'EMP-1-85', 'Meera', 'Bhatia', '1996-10-11', 'female', '9347595909', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 66, 56, '2025-04-26', 'active', 97633.00, '2026-03-06 16:42:01'),
(85, 86, 'EMP-1-86', 'Karan', 'More', '1981-09-04', 'male', '9369086730', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 69, 57, '2025-09-09', 'active', 96238.00, '2026-03-06 16:42:01'),
(86, 87, 'EMP-1-87', 'Vijay', 'Mishra', '1988-10-09', 'female', '9994544949', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 64, 56, '2024-07-19', 'active', 35734.00, '2026-03-06 16:42:02'),
(87, 88, 'EMP-1-88', 'Kapil', 'More', '1999-07-03', 'female', '9108868823', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 1, '2025-08-03', 'active', 50492.00, '2026-03-06 16:42:02'),
(88, 89, 'EMP-1-89', 'Neha', 'Mehta', '1991-08-12', 'female', '9965341735', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 64, 56, '2023-12-03', 'active', 109571.00, '2026-03-06 16:42:02'),
(89, 90, 'EMP-1-90', 'Ishaan', 'Nair', '1999-05-04', 'female', '9761983791', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 57, '2025-11-25', 'active', 137657.00, '2026-03-06 16:42:02'),
(90, 91, 'EMP-1-91', 'Sana', 'Patil', '1994-11-04', 'female', '9284852552', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 57, 56, '2023-08-17', 'active', 147216.00, '2026-03-06 16:42:02'),
(91, 92, 'EMP-1-92', 'Vivek', 'Chaudhary', '1983-12-15', 'female', '9624640557', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 67, 1, '2025-09-24', 'active', 123186.00, '2026-03-06 16:42:02'),
(92, 93, 'EMP-1-93', 'Manoj', 'Shah', '1987-06-21', 'female', '9416457457', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 57, 1, '2022-10-25', 'active', 123988.00, '2026-03-06 16:42:02'),
(93, 94, 'EMP-1-94', 'Diya', 'Khan', '1988-03-27', 'male', '9260285802', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 71, 56, '2024-02-28', 'active', 38472.00, '2026-03-06 16:42:02'),
(94, 95, 'EMP-1-95', 'Kapil', 'Siddiqui', '2000-01-12', 'female', '9935322442', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 59, 56, '2023-07-27', 'active', 63731.00, '2026-03-06 16:42:02'),
(95, 96, 'EMP-1-96', 'Anil', 'Mishra', '1989-01-10', 'female', '9533182069', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 67, 1, '2024-04-15', 'active', 117179.00, '2026-03-06 16:42:02'),
(96, 97, 'EMP-1-97', 'Aditya', 'Das', '1995-01-05', 'male', '9783825009', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 64, 1, '2022-08-07', 'active', 84750.00, '2026-03-06 16:42:02'),
(97, 98, 'EMP-1-98', 'Vihaan', 'Das', '1989-01-03', 'male', '9406705452', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 56, 56, 56, '2023-03-08', 'active', 92585.00, '2026-03-06 16:42:02'),
(98, 99, 'EMP-1-99', 'Ajay', 'Iyer', '1984-06-28', 'male', '9825332057', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 63, 1, '2023-04-09', 'active', 68462.00, '2026-03-06 16:42:02'),
(99, 100, 'EMP-1-100', 'Amit', 'Rao', '1993-11-21', 'female', '9124591317', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 1, '2022-09-15', 'active', 39588.00, '2026-03-06 16:42:02'),
(100, 101, 'EMP-1-101', 'Manish', 'Joshi', '1994-08-28', 'female', '9760222157', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 68, 57, '2024-06-24', 'active', 108251.00, '2026-03-06 16:42:02'),
(101, 102, 'EMP-1-102', 'Suresh', 'Kapoor', '1982-10-16', 'male', '9646521993', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 64, 56, '2023-04-06', 'active', 50886.00, '2026-03-06 16:42:03'),
(102, 103, 'EMP-1-103', 'Gaurav', 'More', '1996-12-21', 'female', '9201672274', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 68, 56, '2024-10-07', 'active', 39414.00, '2026-03-06 16:42:03'),
(103, 104, 'EMP-1-104', 'Lalit', 'Pandey', '1985-12-23', 'male', '9331922122', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 59, 56, '2023-11-22', 'active', 132280.00, '2026-03-06 16:42:03'),
(104, 105, 'EMP-1-105', 'Arjun', 'Pathan', '1982-10-16', 'female', '9755903659', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 1, '2024-01-02', 'active', 117290.00, '2026-03-06 16:42:03'),
(105, 106, 'EMP-1-106', 'Manish', 'Tripathi', '2000-06-03', 'male', '9309820666', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 67, 1, '2023-01-25', 'active', 50139.00, '2026-03-06 16:42:03'),
(106, 107, 'EMP-1-107', 'Riya', 'Das', '1991-06-12', 'female', '9798200110', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 57, 60, 56, '2023-07-25', 'active', 126477.00, '2026-03-06 16:42:03'),
(107, 108, 'EMP-1-108', 'Suresh', 'Tiwari', '1994-08-09', 'female', '9402381889', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 70, 56, '2024-11-07', 'active', 48783.00, '2026-03-06 16:42:03'),
(108, 109, 'EMP-1-109', 'Jatin', 'Khan', '1986-08-04', 'female', '9445596017', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 59, 68, 56, '2023-12-05', 'active', 115818.00, '2026-03-06 16:42:03'),
(109, 110, 'EMP-1-110', 'Deepak', 'Das', '2000-12-20', 'female', '9409750327', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 60, 71, 56, '2024-06-06', 'active', 135290.00, '2026-03-06 16:42:03'),
(110, 111, 'EMP-1-111', 'Rahul', 'Roy', '1991-06-11', 'female', '9540934151', 'Plot No 15, Station Road, Pune, Maharashtra - 411776', 58, 65, 1, '2023-09-23', 'active', 134408.00, '2026-03-06 16:42:03'),
(111, 112, 'EMP-2-112', 'Suresh', 'Malhotra', '1994-02-09', 'female', '9602315499', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 73, 59, '2024-09-07', 'active', 137271.00, '2026-03-06 16:42:03'),
(112, 113, 'EMP-2-113', 'Ashish', 'Siddiqui', '1996-08-09', 'female', '9708431287', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 74, 2, '2023-02-13', 'active', 41532.00, '2026-03-06 16:42:03'),
(113, 114, 'EMP-2-114', 'Kapil', 'Chaudhary', '1999-07-10', 'male', '9789399636', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 75, 2, '2025-11-26', 'active', 133135.00, '2026-03-06 16:42:03'),
(114, 115, 'EMP-2-115', 'Rohan', 'Nair', '1987-04-04', 'male', '9300243169', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 87, 58, '2022-02-19', 'active', 75227.00, '2026-03-06 16:42:03'),
(115, 116, 'EMP-2-116', 'Vihaan', 'Nair', '1988-06-03', 'female', '9495583079', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 87, 59, '2023-03-09', 'active', 142001.00, '2026-03-06 16:42:04'),
(116, 117, 'EMP-2-117', 'Ramesh', 'Shaikh', '1989-01-03', 'male', '9106670645', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 83, 58, '2024-12-10', 'active', 57653.00, '2026-03-06 16:42:04'),
(117, 118, 'EMP-2-118', 'Nitin', 'Mishra', '1984-03-05', 'male', '9821795565', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 77, 58, '2023-10-25', 'active', 72103.00, '2026-03-06 16:42:04'),
(118, 119, 'EMP-2-119', 'Varun', 'Thakur', '1996-01-04', 'male', '9448953467', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 89, 58, '2024-04-25', 'active', 40455.00, '2026-03-06 16:42:04'),
(119, 120, 'EMP-2-120', 'Rajesh', 'Rao', '1993-06-17', 'female', '9121313481', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 81, 2, '2022-08-19', 'active', 95229.00, '2026-03-06 16:42:04'),
(120, 121, 'EMP-2-121', 'Isha', 'Ansari', '1980-05-06', 'female', '9208590238', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 89, 2, '2025-02-25', 'active', 45721.00, '2026-03-06 16:42:04'),
(121, 122, 'EMP-2-122', 'Ashish', 'Yadav', '1998-01-14', 'male', '9117844709', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 78, 58, '2023-11-24', 'active', 72690.00, '2026-03-06 16:42:04'),
(122, 123, 'EMP-2-123', 'Jyoti', 'Pandey', '1995-10-24', 'male', '9620859012', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 81, 59, '2024-04-18', 'active', 88711.00, '2026-03-06 16:42:04'),
(123, 124, 'EMP-2-124', 'Nitin', 'Malhotra', '1982-05-16', 'male', '9602483122', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 77, 2, '2024-01-04', 'active', 141097.00, '2026-03-06 16:42:04'),
(124, 125, 'EMP-2-125', 'Sneha', 'Joshi', '1993-02-08', 'female', '9491953303', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 89, 2, '2025-03-27', 'active', 68693.00, '2026-03-06 16:42:04'),
(125, 126, 'EMP-2-126', 'Ananya', 'Verma', '1993-06-15', 'male', '9692062319', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 84, 59, '2023-08-04', 'active', 111924.00, '2026-03-06 16:42:04'),
(126, 127, 'EMP-2-127', 'Rohan', 'Pathan', '2000-08-04', 'male', '9315552843', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 88, 59, '2023-02-19', 'active', 53544.00, '2026-03-06 16:42:04'),
(127, 128, 'EMP-2-128', 'Rohan', 'Saxena', '1980-01-01', 'female', '9107884249', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 87, 2, '2023-10-20', 'active', 86328.00, '2026-03-06 16:42:04'),
(128, 129, 'EMP-2-129', 'Ajay', 'Bhatia', '1984-12-28', 'male', '9348752164', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 81, 58, '2025-12-15', 'active', 106092.00, '2026-03-06 16:42:04'),
(129, 130, 'EMP-2-130', 'Jatin', 'Thakur', '1989-10-12', 'male', '9929514211', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 74, 58, '2024-03-05', 'active', 63737.00, '2026-03-06 16:42:05'),
(130, 131, 'EMP-2-131', 'Ashish', 'Desai', '1994-01-03', 'male', '9982413226', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 82, 59, '2024-11-04', 'active', 149048.00, '2026-03-06 16:42:05'),
(131, 132, 'EMP-2-132', 'Nisha', 'Patil', '1987-03-07', 'male', '9406010983', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 73, 59, '2025-09-10', 'active', 110279.00, '2026-03-06 16:42:05'),
(132, 133, 'EMP-2-133', 'Sai', 'Gaikwad', '1992-06-03', 'female', '9392799850', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 77, 2, '2025-04-26', 'active', 138473.00, '2026-03-06 16:42:05'),
(133, 134, 'EMP-2-134', 'Arjun', 'Iyer', '1986-11-09', 'female', '9771184016', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 76, 2, '2024-04-13', 'active', 38091.00, '2026-03-06 16:42:05'),
(134, 135, 'EMP-2-135', 'Sai', 'Bhatia', '1984-10-28', 'female', '9658693741', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 86, 59, '2024-07-10', 'active', 101515.00, '2026-03-06 16:42:05'),
(135, 136, 'EMP-2-136', 'Jyoti', 'Patel', '1997-06-01', 'female', '9805915947', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 86, 59, '2024-12-17', 'active', 86163.00, '2026-03-06 16:42:05'),
(136, 137, 'EMP-2-137', 'Aarav', 'Roy', '1984-11-16', 'female', '9121670131', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 86, 2, '2025-12-18', 'active', 85775.00, '2026-03-06 16:42:05'),
(137, 138, 'EMP-2-138', 'Swati', 'Rao', '1993-09-16', 'female', '9943814937', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 89, 2, '2023-04-18', 'active', 34652.00, '2026-03-06 16:42:05'),
(138, 139, 'EMP-2-139', 'Arjun', 'Pawar', '1995-07-01', 'male', '9633161006', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 75, 2, '2025-07-02', 'active', 143865.00, '2026-03-06 16:42:05'),
(139, 140, 'EMP-2-140', 'Pooja', 'Tiwari', '1987-11-03', 'female', '9854151139', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 86, 2, '2025-11-17', 'active', 136181.00, '2026-03-06 16:42:05'),
(140, 141, 'EMP-2-141', 'Rajesh', 'Verma', '1990-08-17', 'male', '9583816104', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 81, 59, '2025-03-01', 'active', 68858.00, '2026-03-06 16:42:05'),
(141, 142, 'EMP-2-142', 'Riya', 'Chopra', '1988-06-08', 'female', '9916880807', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 83, 58, '2022-10-09', 'active', 35763.00, '2026-03-06 16:42:05'),
(142, 143, 'EMP-2-143', 'Meera', 'Rao', '1985-12-10', 'male', '9430072664', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 83, 58, '2025-02-20', 'active', 62996.00, '2026-03-06 16:42:05'),
(143, 144, 'EMP-2-144', 'Sanjay', 'Roy', '1983-07-01', 'female', '9477318329', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 73, 59, '2024-09-05', 'active', 69933.00, '2026-03-06 16:42:05'),
(144, 145, 'EMP-2-145', 'Pooja', 'More', '1995-04-12', 'female', '9911146409', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 87, 58, '2022-06-10', 'active', 95916.00, '2026-03-06 16:42:06'),
(145, 146, 'EMP-2-146', 'Aditya', 'Shah', '1992-11-04', 'female', '9407072543', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 83, 2, '2023-08-26', 'active', 102025.00, '2026-03-06 16:42:06'),
(146, 147, 'EMP-2-147', 'Sai', 'Chaudhary', '1984-04-20', 'male', '9893053098', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 87, 2, '2022-04-04', 'active', 111031.00, '2026-03-06 16:42:06'),
(147, 148, 'EMP-2-148', 'Rohan', 'Pandey', '1985-08-13', 'female', '9842639499', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 85, 58, '2025-10-26', 'active', 109941.00, '2026-03-06 16:42:06'),
(148, 149, 'EMP-2-149', 'Isha', 'Shah', '1992-08-28', 'female', '9707296586', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 88, 59, '2023-08-13', 'active', 102071.00, '2026-03-06 16:42:06'),
(149, 150, 'EMP-2-150', 'Varun', 'Nair', '1997-04-18', 'female', '9701619918', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 77, 2, '2025-11-13', 'active', 140893.00, '2026-03-06 16:42:06'),
(150, 151, 'EMP-2-151', 'Diya', 'Rao', '1994-04-03', 'female', '9156648142', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 86, 2, '2024-07-03', 'active', 101589.00, '2026-03-06 16:42:06'),
(151, 152, 'EMP-2-152', 'Nitin', 'Ansari', '1994-01-16', 'female', '9651041135', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 84, 58, '2023-09-03', 'active', 31671.00, '2026-03-06 16:42:06'),
(152, 153, 'EMP-2-153', 'Neha', 'Khan', '1983-12-20', 'male', '9430616897', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 81, 2, '2024-10-16', 'active', 100960.00, '2026-03-06 16:42:06'),
(153, 154, 'EMP-2-154', 'Sana', 'Thakur', '1985-02-23', 'male', '9708669393', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 74, 59, '2023-07-15', 'active', 126396.00, '2026-03-06 16:42:06'),
(154, 155, 'EMP-2-155', 'Deepak', 'Pathan', '2000-05-02', 'female', '9822055706', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 86, 59, '2022-05-01', 'active', 139738.00, '2026-03-06 16:42:06'),
(155, 156, 'EMP-2-156', 'Pooja', 'Desai', '1997-02-25', 'male', '9874019962', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 74, 59, '2023-07-13', 'active', 72191.00, '2026-03-06 16:42:06'),
(156, 157, 'EMP-2-157', 'Ananya', 'Agarwal', '1983-08-21', 'male', '9785926264', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 80, 2, '2023-03-07', 'active', 68954.00, '2026-03-06 16:42:06'),
(157, 158, 'EMP-2-158', 'Riya', 'Tripathi', '1992-03-20', 'male', '9533028426', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 76, 59, '2023-07-07', 'active', 99543.00, '2026-03-06 16:42:06'),
(158, 159, 'EMP-2-159', 'Ananya', 'Thakur', '1984-02-09', 'male', '9538979925', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 75, 58, '2024-07-24', 'active', 82960.00, '2026-03-06 16:42:06'),
(159, 160, 'EMP-2-160', 'Jatin', 'Dubey', '1998-08-13', 'female', '9970677619', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 78, 2, '2025-06-25', 'active', 115571.00, '2026-03-06 16:42:07'),
(160, 161, 'EMP-2-161', 'Aditya', 'Chopra', '1986-10-25', 'male', '9515605754', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 63, 81, 58, '2023-07-25', 'active', 57910.00, '2026-03-06 16:42:07'),
(161, 162, 'EMP-2-162', 'Swati', 'Khan', '1995-06-12', 'male', '9228782566', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 61, 73, 59, '2024-04-22', 'active', 35774.00, '2026-03-06 16:42:07'),
(162, 163, 'EMP-2-163', 'Ajay', 'Das', '1994-10-23', 'female', '9311522989', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 85, 58, '2022-02-24', 'active', 55419.00, '2026-03-06 16:42:07'),
(163, 164, 'EMP-2-164', 'Sunil', 'More', '1989-09-13', 'female', '9335014246', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 62, 80, 58, '2025-11-11', 'active', 96726.00, '2026-03-06 16:42:07'),
(164, 165, 'EMP-2-165', 'Jatin', 'Reddy', '1993-10-24', 'male', '9101585115', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 65, 89, 58, '2025-11-12', 'active', 90926.00, '2026-03-06 16:42:07'),
(165, 166, 'EMP-2-166', 'Alok', 'Siddiqui', '1996-07-24', 'male', '9867835420', 'Plot No 380, Phase 2, Thane, Maharashtra - 400109', 64, 84, 59, '2022-08-06', 'active', 134748.00, '2026-03-06 16:42:07'),
(166, 167, 'EMP-3-167', 'Amit', 'Agarwal', '1990-05-16', 'male', '9244857190', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 90, 60, '2022-02-27', 'active', 51901.00, '2026-03-06 16:42:07'),
(167, 168, 'EMP-3-168', 'Rahul', 'Shaikh', '1996-01-07', 'male', '9407203645', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 91, 60, '2022-04-08', 'active', 76496.00, '2026-03-06 16:42:07'),
(168, 169, 'EMP-3-169', 'Isha', 'Shaikh', '1986-01-21', 'female', '9214736163', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 92, 60, '2024-10-24', 'active', 118696.00, '2026-03-06 16:42:07'),
(169, 170, 'EMP-3-170', 'Deepak', 'Desai', '1981-11-23', 'male', '9875830173', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 104, 60, '2024-06-11', 'active', 43657.00, '2026-03-06 16:42:07'),
(170, 171, 'EMP-3-171', 'Sanjay', 'Yadav', '1993-08-23', 'male', '9766703355', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 90, 60, '2024-08-27', 'active', 144539.00, '2026-03-06 16:42:07'),
(171, 172, 'EMP-3-172', 'Jyoti', 'Mishra', '1997-03-09', 'male', '9226015497', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 94, 60, '2024-02-16', 'active', 99548.00, '2026-03-06 16:42:07'),
(172, 173, 'EMP-3-173', 'Jyoti', 'Shaikh', '1998-05-26', 'male', '9400115429', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 99, 3, '2023-05-04', 'active', 101575.00, '2026-03-06 16:42:07'),
(173, 174, 'EMP-3-174', 'Riya', 'Rao', '1996-05-12', 'male', '9398828982', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 97, 61, '2023-03-02', 'active', 95078.00, '2026-03-06 16:42:08'),
(174, 175, 'EMP-3-175', 'Priya', 'Sharma', '1989-11-15', 'female', '9217284643', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 105, 3, '2025-10-08', 'active', 59719.00, '2026-03-06 16:42:08'),
(175, 176, 'EMP-3-176', 'Kapil', 'Nair', '1986-08-17', 'male', '9335927872', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 91, 3, '2025-03-17', 'active', 88669.00, '2026-03-06 16:42:08'),
(176, 177, 'EMP-3-177', 'Alok', 'Khan', '1987-04-09', 'male', '9258970690', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 105, 60, '2023-10-18', 'active', 35120.00, '2026-03-06 16:42:08'),
(177, 178, 'EMP-3-178', 'Alok', 'Patel', '1992-10-16', 'female', '9392836848', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 99, 3, '2024-05-11', 'active', 107943.00, '2026-03-06 16:42:08'),
(178, 179, 'EMP-3-179', 'Kapil', 'Chaudhary', '1980-04-01', 'male', '9850233729', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 106, 60, '2023-11-01', 'active', 147186.00, '2026-03-06 16:42:08'),
(179, 180, 'EMP-3-180', 'Vijay', 'Mehta', '1983-10-19', 'female', '9612747217', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 100, 60, '2024-10-27', 'active', 75417.00, '2026-03-06 16:42:08'),
(180, 181, 'EMP-3-181', 'Sanjay', 'Roy', '1983-04-15', 'female', '9214472680', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 98, 61, '2025-08-11', 'active', 47482.00, '2026-03-06 16:42:08'),
(181, 182, 'EMP-3-182', 'Manish', 'Pawar', '1980-02-18', 'male', '9165820840', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 106, 3, '2024-01-01', 'active', 80776.00, '2026-03-06 16:42:08'),
(182, 183, 'EMP-3-183', 'Aditya', 'Bhatia', '1983-12-16', 'female', '9756206276', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 98, 60, '2025-03-20', 'active', 120560.00, '2026-03-06 16:42:08'),
(183, 184, 'EMP-3-184', 'Sanjay', 'Jain', '1994-07-24', 'female', '9747623489', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 103, 3, '2025-03-05', 'active', 127393.00, '2026-03-06 16:42:08'),
(184, 185, 'EMP-3-185', 'Jyoti', 'Kapoor', '1990-10-06', 'female', '9152348636', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 106, 60, '2023-01-25', 'active', 103426.00, '2026-03-06 16:42:08'),
(185, 186, 'EMP-3-186', 'Amit', 'Ansari', '1989-07-05', 'female', '9692859171', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 105, 61, '2024-07-12', 'active', 102035.00, '2026-03-06 16:42:08'),
(186, 187, 'EMP-3-187', 'Simran', 'Pawar', '1993-02-19', 'female', '9986864867', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 101, 60, '2022-04-16', 'active', 104773.00, '2026-03-06 16:42:08'),
(187, 188, 'EMP-3-188', 'Neha', 'Agarwal', '1993-07-12', 'male', '9408247667', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 92, 60, '2022-02-27', 'active', 109223.00, '2026-03-06 16:42:09'),
(188, 189, 'EMP-3-189', 'Swati', 'Saxena', '1980-12-22', 'male', '9952988802', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 92, 61, '2023-07-01', 'active', 128383.00, '2026-03-06 16:42:09'),
(189, 190, 'EMP-3-190', 'Manish', 'Saxena', '1992-05-22', 'female', '9990241065', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 106, 60, '2022-11-16', 'active', 82984.00, '2026-03-06 16:42:09'),
(190, 191, 'EMP-3-191', 'Ananya', 'Mehta', '1990-05-15', 'male', '9213887218', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 103, 61, '2022-12-25', 'active', 116150.00, '2026-03-06 16:42:09'),
(191, 192, 'EMP-3-192', 'Ishaan', 'Jha', '1987-06-24', 'female', '9568867873', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 100, 60, '2023-07-04', 'active', 92867.00, '2026-03-06 16:42:09'),
(192, 193, 'EMP-3-193', 'Ananya', 'Shah', '1987-01-11', 'male', '9765877491', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 95, 3, '2025-04-25', 'active', 147338.00, '2026-03-06 16:42:09'),
(193, 194, 'EMP-3-194', 'Ajay', 'Kulkarni', '1999-08-28', 'male', '9155419812', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 105, 3, '2022-12-16', 'active', 110996.00, '2026-03-06 16:42:09'),
(194, 195, 'EMP-3-195', 'Manoj', 'Patil', '1981-02-16', 'male', '9949643678', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 96, 3, '2025-12-22', 'active', 137292.00, '2026-03-06 16:42:09'),
(195, 196, 'EMP-3-196', 'Rajesh', 'Jha', '1987-11-13', 'male', '9427177094', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 106, 60, '2022-08-05', 'active', 31168.00, '2026-03-06 16:42:09'),
(196, 197, 'EMP-3-197', 'Aarav', 'Pandey', '1999-09-21', 'female', '9424185803', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 97, 3, '2022-11-17', 'active', 85112.00, '2026-03-06 16:42:09'),
(197, 198, 'EMP-3-198', 'Vihaan', 'Roy', '1999-03-07', 'male', '9723937335', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 102, 60, '2024-08-20', 'active', 106859.00, '2026-03-06 16:42:09'),
(198, 199, 'EMP-3-199', 'Varun', 'Kumar', '1989-12-26', 'female', '9816274157', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 98, 3, '2025-09-18', 'active', 58927.00, '2026-03-06 16:42:09'),
(199, 200, 'EMP-3-200', 'Aditya', 'Khan', '1995-06-22', 'female', '9842888111', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 98, 3, '2022-04-19', 'active', 109723.00, '2026-03-06 16:42:09'),
(200, 201, 'EMP-3-201', 'Nitin', 'Joshi', '1982-08-12', 'female', '9627872878', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 93, 61, '2024-07-26', 'active', 94502.00, '2026-03-06 16:42:09'),
(201, 202, 'EMP-3-202', 'Suresh', 'Kapoor', '1982-04-23', 'female', '9744257893', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 99, 60, '2023-10-13', 'active', 56326.00, '2026-03-06 16:42:09'),
(202, 203, 'EMP-3-203', 'Ramesh', 'Shah', '1988-05-26', 'female', '9241372869', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 100, 61, '2022-04-10', 'active', 94528.00, '2026-03-06 16:42:10'),
(203, 204, 'EMP-3-204', 'Tarun', 'Ansari', '1987-09-25', 'male', '9595928420', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 104, 3, '2025-05-19', 'active', 108478.00, '2026-03-06 16:42:10'),
(204, 205, 'EMP-3-205', 'Sanjay', 'Patel', '1987-11-21', 'female', '9108060565', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 102, 60, '2025-02-25', 'active', 132591.00, '2026-03-06 16:42:10'),
(205, 206, 'EMP-3-206', 'Karan', 'Ansari', '1993-04-07', 'female', '9323250240', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 101, 60, '2024-04-06', 'active', 84162.00, '2026-03-06 16:42:10'),
(206, 207, 'EMP-3-207', 'Meera', 'Sharma', '1999-08-21', 'male', '9853276950', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 100, 3, '2022-10-03', 'active', 31911.00, '2026-03-06 16:42:10'),
(207, 208, 'EMP-3-208', 'Manish', 'Mishra', '1982-06-08', 'male', '9795451887', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 67, 95, 3, '2025-11-23', 'active', 106217.00, '2026-03-06 16:42:10'),
(208, 209, 'EMP-3-209', 'Karan', 'Saxena', '1983-10-09', 'female', '9482641234', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 92, 60, '2022-01-16', 'active', 128325.00, '2026-03-06 16:42:10'),
(209, 210, 'EMP-3-210', 'Karan', 'Shaikh', '1981-06-24', 'female', '9148008018', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 102, 61, '2023-09-09', 'active', 38507.00, '2026-03-06 16:42:10'),
(210, 211, 'EMP-3-211', 'Tarun', 'Tiwari', '1980-12-22', 'male', '9995935812', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 106, 61, '2023-08-25', 'active', 72242.00, '2026-03-06 16:42:10'),
(211, 212, 'EMP-3-212', 'Karan', 'Chaudhary', '1983-07-11', 'female', '9640199343', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 68, 99, 3, '2023-07-24', 'active', 93125.00, '2026-03-06 16:42:10'),
(212, 213, 'EMP-3-213', 'Amit', 'Verma', '1999-11-28', 'male', '9542318293', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 105, 61, '2023-07-04', 'active', 36459.00, '2026-03-06 16:42:10'),
(213, 214, 'EMP-3-214', 'Gaurav', 'Shaikh', '1986-11-15', 'male', '9130799613', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 90, 60, '2024-09-06', 'active', 94879.00, '2026-03-06 16:42:10'),
(214, 215, 'EMP-3-215', 'Vivek', 'Yadav', '1992-02-24', 'female', '9401395162', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 101, 60, '2024-07-27', 'active', 118435.00, '2026-03-06 16:42:10'),
(215, 216, 'EMP-3-216', 'Nitin', 'Mishra', '1994-04-08', 'male', '9127997971', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 91, 3, '2025-09-16', 'active', 52263.00, '2026-03-06 16:42:10'),
(216, 217, 'EMP-3-217', 'Preeti', 'Tiwari', '1987-04-22', 'male', '9334731190', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 91, 60, '2022-11-15', 'active', 134079.00, '2026-03-06 16:42:10'),
(217, 218, 'EMP-3-218', 'Aarav', 'Shaikh', '1986-02-12', 'male', '9680616451', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 70, 105, 60, '2023-11-27', 'active', 36609.00, '2026-03-06 16:42:11'),
(218, 219, 'EMP-3-219', 'Kavita', 'Mishra', '1995-04-24', 'male', '9109293157', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 66, 90, 60, '2025-01-09', 'active', 57444.00, '2026-03-06 16:42:11'),
(219, 220, 'EMP-3-220', 'Riya', 'Agarwal', '1995-07-15', 'female', '9362647170', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 101, 3, '2024-08-18', 'active', 69134.00, '2026-03-06 16:42:11'),
(220, 221, 'EMP-3-221', 'Neha', 'Verma', '1983-04-10', 'male', '9807281077', 'Plot No 50, Sector 5, Visakhapatnam, Andhra Pradesh - 530350', 69, 101, 3, '2023-11-23', 'active', 44797.00, '2026-03-06 16:42:11');

-- --------------------------------------------------------

--
-- Table structure for table `employee_credentials`
--

CREATE TABLE `employee_credentials` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `type` enum('rfid','fingerprint','face_id') NOT NULL,
  `identifier_value` varchar(255) NOT NULL COMMENT 'The RFID UID or Fingerprint ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_credentials`
--

INSERT INTO `employee_credentials` (`id`, `employee_id`, `type`, `identifier_value`, `created_at`) VALUES
(5, 136, 'rfid', '2389AC14', '2026-03-07 16:19:50'),
(6, 162, 'rfid', '21456CA2', '2026-03-07 16:28:49'),
(7, 125, 'rfid', '63DFB014', '2026-03-07 17:06:23');

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

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `employee_id`, `submitted_by`, `message`, `type`, `status`, `created_at`) VALUES
(1, 76, 64, 'Can we have more team outages?', 'suggestion', 'pending', '2026-03-06 16:42:18'),
(2, 76, 78, 'The new policy is very helpful.', 'feedback', 'pending', '2026-03-06 16:42:18'),
(3, 107, 103, 'Coffee machine is broken often.', 'complaint', 'pending', '2026-03-06 16:42:18'),
(4, 69, 2, 'The new policy is very helpful.', 'feedback', 'pending', '2026-03-06 16:42:18'),
(5, 1, 70, 'Can we have more team outages?', 'suggestion', 'pending', '2026-03-06 16:42:18'),
(6, 72, 84, 'Great work environment!', 'appreciation', 'pending', '2026-03-06 16:42:18'),
(7, 68, 75, 'The new policy is very helpful.', 'feedback', 'pending', '2026-03-06 16:42:18'),
(8, 108, 83, 'The new policy is very helpful.', 'feedback', 'pending', '2026-03-06 16:42:18');

-- --------------------------------------------------------

--
-- Table structure for table `global_holidays`
--

CREATE TABLE `global_holidays` (
  `id` int(11) NOT NULL,
  `holiday_name` varchar(255) NOT NULL,
  `holiday_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 1, 1, 2, '2026-03-03 17:42:17', 'online', 'Good communication skills.', 'pending', '2026-03-03 12:12:17'),
(2, 4, 1, 2, '2026-02-25 17:42:17', 'online', 'Good communication skills.', 'rejected', '2026-02-25 12:12:17'),
(3, 5, 3, 2, '2026-02-24 17:42:17', 'online', 'Good communication skills.', 'rejected', '2026-02-24 12:12:17'),
(4, 6, 5, 2, '2026-02-28 17:42:17', 'online', 'Good communication skills.', 'pending', '2026-02-28 12:12:17'),
(5, 7, 4, 2, '2026-02-24 17:42:17', 'online', 'Good communication skills.', 'rejected', '2026-02-24 12:12:17'),
(6, 8, 3, 2, '2026-03-03 17:42:17', 'online', 'Good communication skills.', 'selected', '2026-03-03 12:12:17'),
(7, 9, 2, 2, '2026-03-02 17:42:17', 'online', 'Good communication skills.', 'rejected', '2026-03-02 12:12:17'),
(8, 10, 4, 2, '2026-03-04 17:42:17', 'online', 'Good communication skills.', 'pending', '2026-03-04 12:12:17'),
(9, 11, 3, 2, '2026-03-01 17:42:17', 'online', 'Good communication skills.', 'selected', '2026-03-01 12:12:17'),
(10, 12, 1, 2, '2026-02-28 17:42:17', 'online', 'Good communication skills.', 'rejected', '2026-02-28 12:12:17'),
(11, 13, 2, 2, '2026-03-01 17:42:17', 'online', 'Good communication skills.', 'pending', '2026-03-01 12:12:17'),
(12, 14, 2, 2, '2026-02-25 17:42:17', 'online', 'Good communication skills.', 'selected', '2026-02-25 12:12:17'),
(13, 15, 5, 2, '2026-03-01 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-03-01 12:12:18'),
(14, 17, 8, 3, '2026-03-04 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-03-04 12:12:18'),
(15, 18, 7, 3, '2026-02-28 17:42:18', 'online', 'Good communication skills.', 'pending', '2026-02-28 12:12:18'),
(16, 21, 10, 3, '2026-02-25 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-25 12:12:18'),
(17, 22, 7, 3, '2026-03-05 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-03-05 12:12:18'),
(18, 23, 6, 3, '2026-02-24 17:42:18', 'online', 'Good communication skills.', 'pending', '2026-02-24 12:12:18'),
(19, 24, 10, 3, '2026-02-27 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-27 12:12:18'),
(20, 25, 6, 3, '2026-02-24 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-24 12:12:18'),
(21, 27, 9, 3, '2026-03-02 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-03-02 12:12:18'),
(22, 28, 10, 3, '2026-03-01 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-03-01 12:12:18'),
(23, 31, 13, 4, '2026-02-24 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-02-24 12:12:18'),
(24, 32, 11, 4, '2026-02-27 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-27 12:12:18'),
(25, 33, 11, 4, '2026-03-03 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-03-03 12:12:18'),
(26, 34, 15, 4, '2026-03-03 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-03-03 12:12:18'),
(27, 37, 11, 4, '2026-02-27 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-27 12:12:18'),
(28, 38, 11, 4, '2026-02-28 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-28 12:12:18'),
(29, 39, 15, 4, '2026-03-05 17:42:18', 'online', 'Good communication skills.', 'pending', '2026-03-05 12:12:18'),
(30, 40, 12, 4, '2026-03-05 17:42:18', 'online', 'Good communication skills.', 'pending', '2026-03-05 12:12:18'),
(31, 41, 15, 4, '2026-02-26 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-02-26 12:12:18'),
(32, 42, 15, 4, '2026-02-26 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-02-26 12:12:18'),
(33, 43, 11, 4, '2026-02-28 17:42:18', 'online', 'Good communication skills.', 'pending', '2026-02-28 12:12:18'),
(34, 44, 11, 4, '2026-03-02 17:42:18', 'online', 'Good communication skills.', 'selected', '2026-03-02 12:12:18'),
(35, 45, 12, 4, '2026-03-03 17:42:18', 'online', 'Good communication skills.', 'rejected', '2026-03-03 12:12:18');

-- --------------------------------------------------------

--
-- Table structure for table `iot_devices`
--

CREATE TABLE `iot_devices` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL COMMENT 'Device belongs to this company',
  `device_name` varchar(100) NOT NULL,
  `device_token` varchar(255) NOT NULL COMMENT 'Secret key for API Bearer Auth',
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `last_heartbeat` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `add_card_mode` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = device should enter add card mode',
  `pending_card_uid` varchar(50) DEFAULT NULL COMMENT 'Scanned card UID waiting for HR to assign',
  `card_scan_requested_at` timestamp NULL DEFAULT NULL COMMENT 'When HR requested the scan',
  `card_scanned_at` timestamp NULL DEFAULT NULL COMMENT 'When the card was scanned on device'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iot_devices`
--

INSERT INTO `iot_devices` (`id`, `company_id`, `device_name`, `device_token`, `location`, `status`, `last_heartbeat`, `created_at`, `add_card_mode`, `pending_card_uid`, `card_scan_requested_at`, `card_scanned_at`) VALUES
(1, 2, 'IOT_1', '34481d80d8ccb98a0dadd2799cafcc21e3946a1fdcf90dad100907e0c339d958', 'Front_Gate', 'active', '2026-03-09 15:40:58', '2026-03-06 17:03:09', 0, NULL, NULL, NULL);

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
(1, 1, 58, 'Software Engineer', 'Develop and maintain web applications.', 'full-time', 'Remote', 2, '2026-03-06 16:42:17', 'open'),
(2, 1, 1, 'HR Manager', 'Oversee recruitment and employee relations.', 'full-time', 'On-site', 1, '2026-03-06 16:42:17', 'open'),
(3, 1, 58, 'Marketing Intern', 'Assist in social media campaigns.', 'internship', 'Hybrid', 3, '2026-03-06 16:42:17', 'open'),
(4, 1, 58, 'Data Analyst', 'Analyze sales data and generate reports.', 'contract', 'Remote', 1, '2026-03-06 16:42:17', 'open'),
(5, 1, 58, 'Project Manager', 'Lead cross-functional teams.', 'full-time', 'On-site', 1, '2026-03-06 16:42:17', 'open'),
(6, 2, 63, 'Software Engineer', 'Develop and maintain web applications.', 'full-time', 'Remote', 2, '2026-03-06 16:42:18', 'open'),
(7, 2, 63, 'HR Manager', 'Oversee recruitment and employee relations.', 'full-time', 'On-site', 1, '2026-03-06 16:42:18', 'open'),
(8, 2, 2, 'Marketing Intern', 'Assist in social media campaigns.', 'internship', 'Hybrid', 3, '2026-03-06 16:42:18', 'open'),
(9, 2, 63, 'Data Analyst', 'Analyze sales data and generate reports.', 'contract', 'Remote', 1, '2026-03-06 16:42:18', 'open'),
(10, 2, 61, 'Project Manager', 'Lead cross-functional teams.', 'full-time', 'On-site', 1, '2026-03-06 16:42:18', 'open'),
(11, 3, 68, 'Software Engineer', 'Develop and maintain web applications.', 'full-time', 'Remote', 2, '2026-03-06 16:42:18', 'open'),
(12, 3, 70, 'HR Manager', 'Oversee recruitment and employee relations.', 'full-time', 'On-site', 1, '2026-03-06 16:42:18', 'open'),
(13, 3, 69, 'Marketing Intern', 'Assist in social media campaigns.', 'internship', 'Hybrid', 3, '2026-03-06 16:42:18', 'open'),
(14, 3, 66, 'Data Analyst', 'Analyze sales data and generate reports.', 'contract', 'Remote', 1, '2026-03-06 16:42:18', 'open'),
(15, 3, 67, 'Project Manager', 'Lead cross-functional teams.', 'full-time', 'On-site', 1, '2026-03-06 16:42:18', 'open');

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

--
-- Dumping data for table `job_applications`
--

INSERT INTO `job_applications` (`id`, `candidate_id`, `job_id`, `applied_at`, `updated_at`, `status`) VALUES
(1, 1, 1, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'interviewed'),
(2, 2, 2, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'shortlisted'),
(3, 3, 3, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'shortlisted'),
(4, 4, 1, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'rejected'),
(5, 5, 3, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'rejected'),
(6, 6, 5, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'interviewed'),
(7, 7, 4, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'rejected'),
(8, 8, 3, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'offered'),
(9, 9, 2, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'rejected'),
(10, 10, 4, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'interviewed'),
(11, 11, 3, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'hired'),
(12, 12, 1, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'rejected'),
(13, 13, 2, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'interviewed'),
(14, 14, 2, '2026-03-06 16:42:17', '2026-03-06 16:42:17', 'hired'),
(15, 15, 5, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected'),
(16, 16, 8, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'shortlisted'),
(17, 17, 8, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected'),
(18, 18, 7, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'interviewed'),
(19, 19, 6, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'shortlisted'),
(20, 20, 10, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'shortlisted'),
(21, 21, 10, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'offered'),
(22, 22, 7, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'hired'),
(23, 23, 6, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'interviewed'),
(24, 24, 10, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'offered'),
(25, 25, 6, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'hired'),
(26, 26, 10, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'pending'),
(27, 27, 9, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected'),
(28, 28, 10, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'hired'),
(29, 29, 8, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'pending'),
(30, 30, 10, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'shortlisted'),
(31, 31, 13, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected'),
(32, 32, 11, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'offered'),
(33, 33, 11, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'offered'),
(34, 34, 15, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected'),
(35, 35, 12, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'shortlisted'),
(36, 36, 14, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'pending'),
(37, 37, 11, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'hired'),
(38, 38, 11, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'hired'),
(39, 39, 15, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'interviewed'),
(40, 40, 12, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'interviewed'),
(41, 41, 15, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected'),
(42, 42, 15, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'offered'),
(43, 43, 11, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'interviewed'),
(44, 44, 11, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'hired'),
(45, 45, 12, '2026-03-06 16:42:18', '2026-03-06 16:42:18', 'rejected');

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
(1, 98, 'Sick Leave', '2026-02-27', '2026-02-28', 'Urgent medical issue', 'pending', '2026-03-06 16:42:17', NULL),
(2, 71, 'Sick Leave', '2026-02-07', '2026-02-10', 'Family function', 'approved', '2026-03-06 16:42:17', 1),
(3, 73, 'Sick Leave', '2026-02-08', '2026-02-10', 'Family function', 'approved', '2026-03-06 16:42:17', 1),
(4, 96, 'Earned Leave', '2026-01-28', '2026-01-28', 'Urgent medical issue', 'approved', '2026-03-06 16:42:17', 1),
(5, 64, 'Sick Leave', '2026-02-20', '2026-02-23', 'Vacation', 'pending', '2026-03-06 16:42:17', NULL),
(6, 80, 'Casual Leave', '2026-01-07', '2026-01-10', 'Not feeling well', 'cancelled', '2026-03-06 16:42:17', NULL),
(7, 57, 'Maternity Leave', '2026-01-18', '2026-01-19', 'Personal work', 'pending', '2026-03-06 16:42:17', NULL),
(8, 81, 'Casual Leave', '2026-03-05', '2026-03-05', 'Not feeling well', 'pending', '2026-03-06 16:42:17', NULL),
(9, 81, 'Sick Leave', '2026-02-11', '2026-02-12', 'Vacation', 'rejected', '2026-03-06 16:42:17', 1),
(10, 68, 'Sick Leave', '2026-02-12', '2026-02-15', 'Family function', 'cancelled', '2026-03-06 16:42:17', NULL),
(11, 76, 'Casual Leave', '2026-02-21', '2026-02-24', 'Not feeling well', 'pending', '2026-03-06 16:42:17', NULL),
(12, 57, 'Sick Leave', '2026-01-07', '2026-01-09', 'Personal work', 'pending', '2026-03-06 16:42:17', NULL),
(13, 102, 'Earned Leave', '2026-03-05', '2026-03-06', 'Not feeling well', 'approved', '2026-03-06 16:42:17', 1),
(14, 81, 'Sick Leave', '2026-01-28', '2026-01-31', 'Family function', 'pending', '2026-03-06 16:42:17', NULL),
(15, 80, 'Sick Leave', '2026-02-08', '2026-02-10', 'Personal work', 'approved', '2026-03-06 16:42:17', 1),
(16, 87, 'Sick Leave', '2026-01-25', '2026-01-28', 'Vacation', 'approved', '2026-03-06 16:42:17', 1),
(17, 99, 'Earned Leave', '2026-01-23', '2026-01-24', 'Personal work', 'rejected', '2026-03-06 16:42:17', 1),
(18, 72, 'Sick Leave', '2026-03-05', '2026-03-06', 'Vacation', 'pending', '2026-03-06 16:42:17', NULL),
(19, 76, 'Maternity Leave', '2026-01-09', '2026-01-10', 'Not feeling well', 'rejected', '2026-03-06 16:42:17', 1),
(20, 69, 'Sick Leave', '2026-03-06', '2026-03-06', 'Urgent medical issue', 'approved', '2026-03-06 16:42:17', 1),
(21, 90, 'Sick Leave', '2026-02-10', '2026-02-10', 'Not feeling well', 'pending', '2026-03-06 16:42:17', NULL),
(22, 105, 'Sick Leave', '2026-01-19', '2026-01-21', 'Vacation', 'approved', '2026-03-06 16:42:17', 1),
(23, 76, 'Casual Leave', '2026-02-07', '2026-02-08', 'Urgent medical issue', 'approved', '2026-03-06 16:42:17', 1),
(24, 102, 'Maternity Leave', '2026-01-07', '2026-01-07', 'Not feeling well', 'pending', '2026-03-06 16:42:17', NULL),
(25, 57, 'Casual Leave', '2026-01-06', '2026-01-07', 'Not feeling well', 'rejected', '2026-03-06 16:42:17', 1),
(26, 1, 'Casual Leave', '2026-01-29', '2026-01-30', 'Urgent medical issue', 'pending', '2026-03-06 16:42:17', NULL),
(27, 92, 'Earned Leave', '2026-03-04', '2026-03-06', 'Urgent medical issue', 'cancelled', '2026-03-06 16:42:17', NULL),
(28, 108, 'Maternity Leave', '2026-01-09', '2026-01-10', 'Not feeling well', 'approved', '2026-03-06 16:42:17', 1),
(29, 92, 'Sick Leave', '2026-02-03', '2026-02-05', 'Vacation', 'cancelled', '2026-03-06 16:42:17', NULL),
(30, 94, 'Casual Leave', '2026-01-16', '2026-01-17', 'Urgent medical issue', 'approved', '2026-03-06 16:42:17', 1),
(31, 77, 'Sick Leave', '2026-03-03', '2026-03-06', 'Urgent medical issue', 'pending', '2026-03-06 16:42:17', NULL),
(32, 90, 'Maternity Leave', '2026-01-31', '2026-02-03', 'Not feeling well', 'rejected', '2026-03-06 16:42:17', 1),
(33, 89, 'Casual Leave', '2026-01-21', '2026-01-21', 'Urgent medical issue', 'pending', '2026-03-06 16:42:17', NULL),
(34, 98, 'Sick Leave', '2026-03-06', '2026-03-08', 'Personal work', 'pending', '2026-03-06 16:42:17', NULL),
(35, 100, 'Maternity Leave', '2026-01-18', '2026-01-18', 'Urgent medical issue', 'cancelled', '2026-03-06 16:42:17', NULL),
(36, 77, 'Casual Leave', '2026-01-07', '2026-01-08', 'Not feeling well', 'cancelled', '2026-03-06 16:42:17', NULL),
(37, 60, 'Maternity Leave', '2026-01-05', '2026-01-08', 'Vacation', 'pending', '2026-03-06 16:42:17', NULL),
(38, 105, 'Sick Leave', '2026-03-06', '2026-03-06', 'Family function', 'cancelled', '2026-03-06 16:42:17', NULL),
(39, 75, 'Sick Leave', '2026-01-25', '2026-01-25', 'Family function', 'cancelled', '2026-03-06 16:42:17', NULL),
(40, 69, 'Casual Leave', '2026-01-15', '2026-01-16', 'Family function', 'pending', '2026-03-06 16:42:17', NULL),
(41, 62, 'Sick Leave', '2026-01-18', '2026-01-18', 'Vacation', 'rejected', '2026-03-06 16:42:17', 1),
(42, 62, 'Earned Leave', '2026-02-26', '2026-03-01', 'Urgent medical issue', 'cancelled', '2026-03-06 16:42:17', NULL),
(43, 78, 'Maternity Leave', '2026-01-15', '2026-01-18', 'Vacation', 'cancelled', '2026-03-06 16:42:17', NULL),
(44, 103, 'Earned Leave', '2026-02-01', '2026-02-03', 'Vacation', 'approved', '2026-03-06 16:42:17', 1),
(45, 72, 'Earned Leave', '2026-02-27', '2026-03-02', 'Family function', 'approved', '2026-03-06 16:42:17', 1),
(46, 82, 'Earned Leave', '2026-01-28', '2026-01-29', 'Family function', 'approved', '2026-03-06 16:42:17', 1),
(47, 87, 'Earned Leave', '2026-01-31', '2026-02-03', 'Personal work', 'cancelled', '2026-03-06 16:42:17', NULL),
(48, 78, 'Casual Leave', '2026-02-15', '2026-02-15', 'Family function', 'approved', '2026-03-06 16:42:17', 1),
(49, 99, 'Maternity Leave', '2026-02-04', '2026-02-05', 'Not feeling well', 'rejected', '2026-03-06 16:42:17', 1),
(50, 101, 'Sick Leave', '2026-01-30', '2026-01-30', 'Not feeling well', 'pending', '2026-03-06 16:42:17', NULL),
(51, 85, 'Earned Leave', '2026-01-06', '2026-01-06', 'Vacation', 'pending', '2026-03-06 16:42:17', NULL),
(52, 62, 'Earned Leave', '2026-01-27', '2026-01-27', 'Urgent medical issue', 'pending', '2026-03-06 16:42:17', NULL),
(53, 91, 'Maternity Leave', '2026-02-10', '2026-02-10', 'Family function', 'pending', '2026-03-06 16:42:17', NULL),
(54, 110, 'Earned Leave', '2026-03-06', '2026-03-06', 'Personal work', 'approved', '2026-03-06 16:42:17', 1),
(55, 56, 'Earned Leave', '2026-03-01', '2026-03-01', 'Family function', 'cancelled', '2026-03-06 16:42:17', NULL),
(56, 95, 'Earned Leave', '2026-03-06', '2026-03-06', 'Not feeling well', 'approved', '2026-03-06 16:42:17', 1),
(57, 66, 'Earned Leave', '2026-01-06', '2026-01-09', 'Family function', 'cancelled', '2026-03-06 16:42:17', NULL),
(58, 85, 'Earned Leave', '2026-03-06', '2026-03-06', 'Not feeling well', 'pending', '2026-03-06 16:42:17', NULL),
(59, 90, 'Casual Leave', '2026-03-05', '2026-03-06', 'Urgent medical issue', 'pending', '2026-03-06 16:42:17', NULL),
(60, 79, 'Sick Leave', '2026-01-27', '2026-01-28', 'Not feeling well', 'rejected', '2026-03-06 16:42:17', 1);

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

--
-- Dumping data for table `leave_balances`
--

INSERT INTO `leave_balances` (`id`, `employee_id`, `leave_policy_id`, `year`, `accrued_days`, `used_days`) VALUES
(1, 1, 1, '2026', 10.0, 0.0),
(2, 1, 2, '2026', 12.0, 0.0),
(3, 1, 3, '2026', 15.0, 0.0),
(4, 1, 4, '2026', 90.0, 0.0),
(5, 56, 1, '2026', 10.0, 0.0),
(6, 56, 2, '2026', 12.0, 0.0),
(7, 56, 3, '2026', 15.0, 0.0),
(8, 56, 4, '2026', 90.0, 0.0),
(9, 57, 1, '2026', 10.0, 0.0),
(10, 57, 2, '2026', 12.0, 0.0),
(11, 57, 3, '2026', 15.0, 0.0),
(12, 57, 4, '2026', 90.0, 0.0),
(13, 58, 1, '2026', 10.0, 0.0),
(14, 58, 2, '2026', 12.0, 0.0),
(15, 58, 3, '2026', 15.0, 0.0),
(16, 58, 4, '2026', 90.0, 0.0),
(17, 60, 1, '2026', 10.0, 0.0),
(18, 60, 2, '2026', 12.0, 0.0),
(19, 60, 3, '2026', 15.0, 0.0),
(20, 60, 4, '2026', 90.0, 0.0),
(21, 63, 1, '2026', 10.0, 0.0),
(22, 63, 2, '2026', 12.0, 0.0),
(23, 63, 3, '2026', 15.0, 0.0),
(24, 63, 4, '2026', 90.0, 0.0),
(25, 64, 1, '2026', 10.0, 0.0),
(26, 64, 2, '2026', 12.0, 0.0),
(27, 64, 3, '2026', 15.0, 0.0),
(28, 64, 4, '2026', 90.0, 0.0),
(29, 78, 1, '2026', 10.0, 0.0),
(30, 78, 2, '2026', 12.0, 1.0),
(31, 78, 3, '2026', 15.0, 0.0),
(32, 78, 4, '2026', 90.0, 0.0),
(33, 90, 1, '2026', 10.0, 0.0),
(34, 90, 2, '2026', 12.0, 0.0),
(35, 90, 3, '2026', 15.0, 0.0),
(36, 90, 4, '2026', 90.0, 0.0),
(37, 92, 1, '2026', 10.0, 0.0),
(38, 92, 2, '2026', 12.0, 0.0),
(39, 92, 3, '2026', 15.0, 0.0),
(40, 92, 4, '2026', 90.0, 0.0),
(41, 97, 1, '2026', 10.0, 0.0),
(42, 97, 2, '2026', 12.0, 0.0),
(43, 97, 3, '2026', 15.0, 0.0),
(44, 97, 4, '2026', 90.0, 0.0),
(45, 62, 1, '2026', 10.0, 0.0),
(46, 62, 2, '2026', 12.0, 0.0),
(47, 62, 3, '2026', 15.0, 0.0),
(48, 62, 4, '2026', 90.0, 0.0),
(49, 65, 1, '2026', 10.0, 0.0),
(50, 65, 2, '2026', 12.0, 0.0),
(51, 65, 3, '2026', 15.0, 0.0),
(52, 65, 4, '2026', 90.0, 0.0),
(53, 66, 1, '2026', 10.0, 0.0),
(54, 66, 2, '2026', 12.0, 0.0),
(55, 66, 3, '2026', 15.0, 0.0),
(56, 66, 4, '2026', 90.0, 0.0),
(57, 68, 1, '2026', 10.0, 0.0),
(58, 68, 2, '2026', 12.0, 0.0),
(59, 68, 3, '2026', 15.0, 0.0),
(60, 68, 4, '2026', 90.0, 0.0),
(61, 69, 1, '2026', 10.0, 1.0),
(62, 69, 2, '2026', 12.0, 0.0),
(63, 69, 3, '2026', 15.0, 0.0),
(64, 69, 4, '2026', 90.0, 0.0),
(65, 75, 1, '2026', 10.0, 0.0),
(66, 75, 2, '2026', 12.0, 0.0),
(67, 75, 3, '2026', 15.0, 0.0),
(68, 75, 4, '2026', 90.0, 0.0),
(69, 76, 1, '2026', 10.0, 0.0),
(70, 76, 2, '2026', 12.0, 2.0),
(71, 76, 3, '2026', 15.0, 0.0),
(72, 76, 4, '2026', 90.0, 0.0),
(73, 77, 1, '2026', 10.0, 0.0),
(74, 77, 2, '2026', 12.0, 0.0),
(75, 77, 3, '2026', 15.0, 0.0),
(76, 77, 4, '2026', 90.0, 0.0),
(77, 83, 1, '2026', 10.0, 0.0),
(78, 83, 2, '2026', 12.0, 0.0),
(79, 83, 3, '2026', 15.0, 0.0),
(80, 83, 4, '2026', 90.0, 0.0),
(81, 94, 1, '2026', 10.0, 0.0),
(82, 94, 2, '2026', 12.0, 2.0),
(83, 94, 3, '2026', 15.0, 0.0),
(84, 94, 4, '2026', 90.0, 0.0),
(85, 98, 1, '2026', 10.0, 0.0),
(86, 98, 2, '2026', 12.0, 0.0),
(87, 98, 3, '2026', 15.0, 0.0),
(88, 98, 4, '2026', 90.0, 0.0),
(89, 103, 1, '2026', 10.0, 0.0),
(90, 103, 2, '2026', 12.0, 0.0),
(91, 103, 3, '2026', 15.0, 3.0),
(92, 103, 4, '2026', 90.0, 0.0),
(93, 106, 1, '2026', 10.0, 0.0),
(94, 106, 2, '2026', 12.0, 0.0),
(95, 106, 3, '2026', 15.0, 0.0),
(96, 106, 4, '2026', 90.0, 0.0),
(97, 67, 1, '2026', 10.0, 0.0),
(98, 67, 2, '2026', 12.0, 0.0),
(99, 67, 3, '2026', 15.0, 0.0),
(100, 67, 4, '2026', 90.0, 0.0),
(101, 70, 1, '2026', 10.0, 0.0),
(102, 70, 2, '2026', 12.0, 0.0),
(103, 70, 3, '2026', 15.0, 0.0),
(104, 70, 4, '2026', 90.0, 0.0),
(105, 71, 1, '2026', 10.0, 4.0),
(106, 71, 2, '2026', 12.0, 0.0),
(107, 71, 3, '2026', 15.0, 0.0),
(108, 71, 4, '2026', 90.0, 0.0),
(109, 74, 1, '2026', 10.0, 0.0),
(110, 74, 2, '2026', 12.0, 0.0),
(111, 74, 3, '2026', 15.0, 0.0),
(112, 74, 4, '2026', 90.0, 0.0),
(113, 81, 1, '2026', 10.0, 0.0),
(114, 81, 2, '2026', 12.0, 0.0),
(115, 81, 3, '2026', 15.0, 0.0),
(116, 81, 4, '2026', 90.0, 0.0),
(117, 82, 1, '2026', 10.0, 0.0),
(118, 82, 2, '2026', 12.0, 0.0),
(119, 82, 3, '2026', 15.0, 2.0),
(120, 82, 4, '2026', 90.0, 0.0),
(121, 84, 1, '2026', 10.0, 0.0),
(122, 84, 2, '2026', 12.0, 0.0),
(123, 84, 3, '2026', 15.0, 0.0),
(124, 84, 4, '2026', 90.0, 0.0),
(125, 86, 1, '2026', 10.0, 0.0),
(126, 86, 2, '2026', 12.0, 0.0),
(127, 86, 3, '2026', 15.0, 0.0),
(128, 86, 4, '2026', 90.0, 0.0),
(129, 88, 1, '2026', 10.0, 0.0),
(130, 88, 2, '2026', 12.0, 0.0),
(131, 88, 3, '2026', 15.0, 0.0),
(132, 88, 4, '2026', 90.0, 0.0),
(133, 96, 1, '2026', 10.0, 0.0),
(134, 96, 2, '2026', 12.0, 0.0),
(135, 96, 3, '2026', 15.0, 1.0),
(136, 96, 4, '2026', 90.0, 0.0),
(137, 101, 1, '2026', 10.0, 0.0),
(138, 101, 2, '2026', 12.0, 0.0),
(139, 101, 3, '2026', 15.0, 0.0),
(140, 101, 4, '2026', 90.0, 0.0),
(141, 110, 1, '2026', 10.0, 0.0),
(142, 110, 2, '2026', 12.0, 0.0),
(143, 110, 3, '2026', 15.0, 1.0),
(144, 110, 4, '2026', 90.0, 0.0),
(145, 61, 1, '2026', 10.0, 0.0),
(146, 61, 2, '2026', 12.0, 0.0),
(147, 61, 3, '2026', 15.0, 0.0),
(148, 61, 4, '2026', 90.0, 0.0),
(149, 79, 1, '2026', 10.0, 0.0),
(150, 79, 2, '2026', 12.0, 0.0),
(151, 79, 3, '2026', 15.0, 0.0),
(152, 79, 4, '2026', 90.0, 0.0),
(153, 85, 1, '2026', 10.0, 0.0),
(154, 85, 2, '2026', 12.0, 0.0),
(155, 85, 3, '2026', 15.0, 0.0),
(156, 85, 4, '2026', 90.0, 0.0),
(157, 91, 1, '2026', 10.0, 0.0),
(158, 91, 2, '2026', 12.0, 0.0),
(159, 91, 3, '2026', 15.0, 0.0),
(160, 91, 4, '2026', 90.0, 0.0),
(161, 95, 1, '2026', 10.0, 0.0),
(162, 95, 2, '2026', 12.0, 0.0),
(163, 95, 3, '2026', 15.0, 1.0),
(164, 95, 4, '2026', 90.0, 0.0),
(165, 100, 1, '2026', 10.0, 0.0),
(166, 100, 2, '2026', 12.0, 0.0),
(167, 100, 3, '2026', 15.0, 0.0),
(168, 100, 4, '2026', 90.0, 0.0),
(169, 102, 1, '2026', 10.0, 0.0),
(170, 102, 2, '2026', 12.0, 0.0),
(171, 102, 3, '2026', 15.0, 2.0),
(172, 102, 4, '2026', 90.0, 0.0),
(173, 105, 1, '2026', 10.0, 3.0),
(174, 105, 2, '2026', 12.0, 0.0),
(175, 105, 3, '2026', 15.0, 0.0),
(176, 105, 4, '2026', 90.0, 0.0),
(177, 108, 1, '2026', 10.0, 0.0),
(178, 108, 2, '2026', 12.0, 0.0),
(179, 108, 3, '2026', 15.0, 0.0),
(180, 108, 4, '2026', 90.0, 2.0),
(181, 59, 1, '2026', 10.0, 0.0),
(182, 59, 2, '2026', 12.0, 0.0),
(183, 59, 3, '2026', 15.0, 0.0),
(184, 59, 4, '2026', 90.0, 0.0),
(185, 72, 1, '2026', 10.0, 0.0),
(186, 72, 2, '2026', 12.0, 0.0),
(187, 72, 3, '2026', 15.0, 4.0),
(188, 72, 4, '2026', 90.0, 0.0),
(189, 73, 1, '2026', 10.0, 3.0),
(190, 73, 2, '2026', 12.0, 0.0),
(191, 73, 3, '2026', 15.0, 0.0),
(192, 73, 4, '2026', 90.0, 0.0),
(193, 80, 1, '2026', 10.0, 3.0),
(194, 80, 2, '2026', 12.0, 0.0),
(195, 80, 3, '2026', 15.0, 0.0),
(196, 80, 4, '2026', 90.0, 0.0),
(197, 87, 1, '2026', 10.0, 4.0),
(198, 87, 2, '2026', 12.0, 0.0),
(199, 87, 3, '2026', 15.0, 0.0),
(200, 87, 4, '2026', 90.0, 0.0),
(201, 89, 1, '2026', 10.0, 0.0),
(202, 89, 2, '2026', 12.0, 0.0),
(203, 89, 3, '2026', 15.0, 0.0),
(204, 89, 4, '2026', 90.0, 0.0),
(205, 93, 1, '2026', 10.0, 0.0),
(206, 93, 2, '2026', 12.0, 0.0),
(207, 93, 3, '2026', 15.0, 0.0),
(208, 93, 4, '2026', 90.0, 0.0),
(209, 99, 1, '2026', 10.0, 0.0),
(210, 99, 2, '2026', 12.0, 0.0),
(211, 99, 3, '2026', 15.0, 0.0),
(212, 99, 4, '2026', 90.0, 0.0),
(213, 104, 1, '2026', 10.0, 0.0),
(214, 104, 2, '2026', 12.0, 0.0),
(215, 104, 3, '2026', 15.0, 0.0),
(216, 104, 4, '2026', 90.0, 0.0),
(217, 107, 1, '2026', 10.0, 0.0),
(218, 107, 2, '2026', 12.0, 0.0),
(219, 107, 3, '2026', 15.0, 0.0),
(220, 107, 4, '2026', 90.0, 0.0),
(221, 109, 1, '2026', 10.0, 0.0),
(222, 109, 2, '2026', 12.0, 0.0),
(223, 109, 3, '2026', 15.0, 0.0),
(224, 109, 4, '2026', 90.0, 0.0);

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
(1, 1, 'Sick Leave', 10, 0),
(2, 1, 'Casual Leave', 12, 0),
(3, 1, 'Earned Leave', 15, 1),
(4, 1, 'Maternity Leave', 90, 0);

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
(1, 1, 1, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":48957.5},{\"name\":\"House Rent Allowance\",\"amount\":19583},{\"name\":\"Special Allowance\",\"amount\":29374.5}]', '[{\"name\":\"Provident Fund\",\"amount\":5874.9},{\"name\":\"Income Tax\",\"amount\":4895.75}]', 97915.00, 87144.35, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(2, 1, 56, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":27406},{\"name\":\"House Rent Allowance\",\"amount\":10962.4},{\"name\":\"Special Allowance\",\"amount\":16443.6}]', '[{\"name\":\"Provident Fund\",\"amount\":3288.72},{\"name\":\"Income Tax\",\"amount\":2740.6}]', 54812.00, 48782.68, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(3, 1, 57, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":25974},{\"name\":\"House Rent Allowance\",\"amount\":10389.6},{\"name\":\"Special Allowance\",\"amount\":15584.4}]', '[{\"name\":\"Provident Fund\",\"amount\":3116.88},{\"name\":\"Income Tax\",\"amount\":2597.4}]', 51948.00, 46233.72, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(4, 1, 58, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":68813},{\"name\":\"House Rent Allowance\",\"amount\":27525.2},{\"name\":\"Special Allowance\",\"amount\":41287.8}]', '[{\"name\":\"Provident Fund\",\"amount\":8257.56},{\"name\":\"Income Tax\",\"amount\":6881.3}]', 137626.00, 122487.14, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(5, 1, 60, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":27664.5},{\"name\":\"House Rent Allowance\",\"amount\":11065.8},{\"name\":\"Special Allowance\",\"amount\":16598.7}]', '[{\"name\":\"Provident Fund\",\"amount\":3319.74},{\"name\":\"Income Tax\",\"amount\":2766.45}]', 55329.00, 49242.81, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(6, 1, 63, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":42605.5},{\"name\":\"House Rent Allowance\",\"amount\":17042.2},{\"name\":\"Special Allowance\",\"amount\":25563.3}]', '[{\"name\":\"Provident Fund\",\"amount\":5112.66},{\"name\":\"Income Tax\",\"amount\":4260.55}]', 85211.00, 75837.79, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(7, 1, 64, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":47647.5},{\"name\":\"House Rent Allowance\",\"amount\":19059},{\"name\":\"Special Allowance\",\"amount\":28588.5}]', '[{\"name\":\"Provident Fund\",\"amount\":5717.7},{\"name\":\"Income Tax\",\"amount\":4764.75}]', 95295.00, 84812.55, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(8, 1, 78, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":30590.5},{\"name\":\"House Rent Allowance\",\"amount\":12236.2},{\"name\":\"Special Allowance\",\"amount\":18354.3}]', '[{\"name\":\"Provident Fund\",\"amount\":3670.86},{\"name\":\"Income Tax\",\"amount\":3059.05}]', 61181.00, 54451.09, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(9, 1, 90, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":73608},{\"name\":\"House Rent Allowance\",\"amount\":29443.2},{\"name\":\"Special Allowance\",\"amount\":44164.8}]', '[{\"name\":\"Provident Fund\",\"amount\":8832.96},{\"name\":\"Income Tax\",\"amount\":7360.8}]', 147216.00, 131022.24, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(10, 1, 92, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":61994},{\"name\":\"House Rent Allowance\",\"amount\":24797.6},{\"name\":\"Special Allowance\",\"amount\":37196.4}]', '[{\"name\":\"Provident Fund\",\"amount\":7439.28},{\"name\":\"Income Tax\",\"amount\":6199.4}]', 123988.00, 110349.32, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(11, 1, 97, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":46292.5},{\"name\":\"House Rent Allowance\",\"amount\":18517},{\"name\":\"Special Allowance\",\"amount\":27775.5}]', '[{\"name\":\"Provident Fund\",\"amount\":5555.1},{\"name\":\"Income Tax\",\"amount\":4629.25}]', 92585.00, 82400.65, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(12, 1, 62, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":72986.5},{\"name\":\"House Rent Allowance\",\"amount\":29194.6},{\"name\":\"Special Allowance\",\"amount\":43791.9}]', '[{\"name\":\"Provident Fund\",\"amount\":8758.38},{\"name\":\"Income Tax\",\"amount\":7298.65}]', 145973.00, 129915.97, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(13, 1, 65, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":60816.5},{\"name\":\"House Rent Allowance\",\"amount\":24326.6},{\"name\":\"Special Allowance\",\"amount\":36489.9}]', '[{\"name\":\"Provident Fund\",\"amount\":7297.98},{\"name\":\"Income Tax\",\"amount\":6081.65}]', 121633.00, 108253.37, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(14, 1, 66, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":51275.5},{\"name\":\"House Rent Allowance\",\"amount\":20510.2},{\"name\":\"Special Allowance\",\"amount\":30765.3}]', '[{\"name\":\"Provident Fund\",\"amount\":6153.06},{\"name\":\"Income Tax\",\"amount\":5127.55}]', 102551.00, 91270.39, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(15, 1, 68, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":51978},{\"name\":\"House Rent Allowance\",\"amount\":20791.2},{\"name\":\"Special Allowance\",\"amount\":31186.8}]', '[{\"name\":\"Provident Fund\",\"amount\":6237.36},{\"name\":\"Income Tax\",\"amount\":5197.8}]', 103956.00, 92520.84, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(16, 1, 69, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":25663.5},{\"name\":\"House Rent Allowance\",\"amount\":10265.4},{\"name\":\"Special Allowance\",\"amount\":15398.1}]', '[{\"name\":\"Provident Fund\",\"amount\":3079.62},{\"name\":\"Income Tax\",\"amount\":2566.35}]', 51327.00, 45681.03, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(17, 1, 75, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":58977},{\"name\":\"House Rent Allowance\",\"amount\":23590.8},{\"name\":\"Special Allowance\",\"amount\":35386.2}]', '[{\"name\":\"Provident Fund\",\"amount\":7077.24},{\"name\":\"Income Tax\",\"amount\":5897.7}]', 117954.00, 104979.06, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(18, 1, 76, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":26377.5},{\"name\":\"House Rent Allowance\",\"amount\":10551},{\"name\":\"Special Allowance\",\"amount\":15826.5}]', '[{\"name\":\"Provident Fund\",\"amount\":3165.3},{\"name\":\"Income Tax\",\"amount\":2637.75}]', 52755.00, 46951.95, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(19, 1, 77, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":40074},{\"name\":\"House Rent Allowance\",\"amount\":16029.6},{\"name\":\"Special Allowance\",\"amount\":24044.4}]', '[{\"name\":\"Provident Fund\",\"amount\":4808.88},{\"name\":\"Income Tax\",\"amount\":4007.4}]', 80148.00, 71331.72, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(20, 1, 83, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":53486.5},{\"name\":\"House Rent Allowance\",\"amount\":21394.6},{\"name\":\"Special Allowance\",\"amount\":32091.9}]', '[{\"name\":\"Provident Fund\",\"amount\":6418.38},{\"name\":\"Income Tax\",\"amount\":5348.65}]', 106973.00, 95205.97, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(21, 1, 94, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":31865.5},{\"name\":\"House Rent Allowance\",\"amount\":12746.2},{\"name\":\"Special Allowance\",\"amount\":19119.3}]', '[{\"name\":\"Provident Fund\",\"amount\":3823.86},{\"name\":\"Income Tax\",\"amount\":3186.55}]', 63731.00, 56720.59, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(22, 1, 98, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":34231},{\"name\":\"House Rent Allowance\",\"amount\":13692.4},{\"name\":\"Special Allowance\",\"amount\":20538.6}]', '[{\"name\":\"Provident Fund\",\"amount\":4107.72},{\"name\":\"Income Tax\",\"amount\":3423.1}]', 68462.00, 60931.18, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(23, 1, 103, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":66140},{\"name\":\"House Rent Allowance\",\"amount\":26456},{\"name\":\"Special Allowance\",\"amount\":39684}]', '[{\"name\":\"Provident Fund\",\"amount\":7936.8},{\"name\":\"Income Tax\",\"amount\":6614}]', 132280.00, 117729.20, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(24, 1, 106, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":63238.5},{\"name\":\"House Rent Allowance\",\"amount\":25295.4},{\"name\":\"Special Allowance\",\"amount\":37943.1}]', '[{\"name\":\"Provident Fund\",\"amount\":7588.62},{\"name\":\"Income Tax\",\"amount\":6323.85}]', 126477.00, 112564.53, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(25, 1, 67, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":23210.5},{\"name\":\"House Rent Allowance\",\"amount\":9284.2},{\"name\":\"Special Allowance\",\"amount\":13926.3}]', '[{\"name\":\"Provident Fund\",\"amount\":2785.26},{\"name\":\"Income Tax\",\"amount\":2321.05}]', 46421.00, 41314.69, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(26, 1, 70, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":29030.5},{\"name\":\"House Rent Allowance\",\"amount\":11612.2},{\"name\":\"Special Allowance\",\"amount\":17418.3}]', '[{\"name\":\"Provident Fund\",\"amount\":3483.66},{\"name\":\"Income Tax\",\"amount\":2903.05}]', 58061.00, 51674.29, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(27, 1, 71, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":41224},{\"name\":\"House Rent Allowance\",\"amount\":16489.6},{\"name\":\"Special Allowance\",\"amount\":24734.4}]', '[{\"name\":\"Provident Fund\",\"amount\":4946.88},{\"name\":\"Income Tax\",\"amount\":4122.4}]', 82448.00, 73378.72, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(28, 1, 74, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":50858.5},{\"name\":\"House Rent Allowance\",\"amount\":20343.4},{\"name\":\"Special Allowance\",\"amount\":30515.1}]', '[{\"name\":\"Provident Fund\",\"amount\":6103.02},{\"name\":\"Income Tax\",\"amount\":5085.85}]', 101717.00, 90528.13, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(29, 1, 81, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":38668.5},{\"name\":\"House Rent Allowance\",\"amount\":15467.4},{\"name\":\"Special Allowance\",\"amount\":23201.1}]', '[{\"name\":\"Provident Fund\",\"amount\":4640.22},{\"name\":\"Income Tax\",\"amount\":3866.85}]', 77337.00, 68829.93, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(30, 1, 82, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":49698.5},{\"name\":\"House Rent Allowance\",\"amount\":19879.4},{\"name\":\"Special Allowance\",\"amount\":29819.1}]', '[{\"name\":\"Provident Fund\",\"amount\":5963.82},{\"name\":\"Income Tax\",\"amount\":4969.85}]', 99397.00, 88463.33, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(31, 1, 84, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":48816.5},{\"name\":\"House Rent Allowance\",\"amount\":19526.6},{\"name\":\"Special Allowance\",\"amount\":29289.9}]', '[{\"name\":\"Provident Fund\",\"amount\":5857.98},{\"name\":\"Income Tax\",\"amount\":4881.65}]', 97633.00, 86893.37, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(32, 1, 86, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":17867},{\"name\":\"House Rent Allowance\",\"amount\":7146.8},{\"name\":\"Special Allowance\",\"amount\":10720.2}]', '[{\"name\":\"Provident Fund\",\"amount\":2144.04},{\"name\":\"Income Tax\",\"amount\":1786.7}]', 35734.00, 31803.26, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(33, 1, 88, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":54785.5},{\"name\":\"House Rent Allowance\",\"amount\":21914.2},{\"name\":\"Special Allowance\",\"amount\":32871.3}]', '[{\"name\":\"Provident Fund\",\"amount\":6574.26},{\"name\":\"Income Tax\",\"amount\":5478.55}]', 109571.00, 97518.19, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(34, 1, 96, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":42375},{\"name\":\"House Rent Allowance\",\"amount\":16950},{\"name\":\"Special Allowance\",\"amount\":25425}]', '[{\"name\":\"Provident Fund\",\"amount\":5085},{\"name\":\"Income Tax\",\"amount\":4237.5}]', 84750.00, 75427.50, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(35, 1, 101, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":25443},{\"name\":\"House Rent Allowance\",\"amount\":10177.2},{\"name\":\"Special Allowance\",\"amount\":15265.8}]', '[{\"name\":\"Provident Fund\",\"amount\":3053.16},{\"name\":\"Income Tax\",\"amount\":2544.3}]', 50886.00, 45288.54, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(36, 1, 110, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":67204},{\"name\":\"House Rent Allowance\",\"amount\":26881.6},{\"name\":\"Special Allowance\",\"amount\":40322.4}]', '[{\"name\":\"Provident Fund\",\"amount\":8064.48},{\"name\":\"Income Tax\",\"amount\":6720.4}]', 134408.00, 119623.12, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(37, 1, 61, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":20793.5},{\"name\":\"House Rent Allowance\",\"amount\":8317.4},{\"name\":\"Special Allowance\",\"amount\":12476.1}]', '[{\"name\":\"Provident Fund\",\"amount\":2495.22},{\"name\":\"Income Tax\",\"amount\":2079.35}]', 41587.00, 37012.43, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(38, 1, 79, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":69731.5},{\"name\":\"House Rent Allowance\",\"amount\":27892.6},{\"name\":\"Special Allowance\",\"amount\":41838.9}]', '[{\"name\":\"Provident Fund\",\"amount\":8367.78},{\"name\":\"Income Tax\",\"amount\":6973.15}]', 139463.00, 124122.07, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(39, 1, 85, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":48119},{\"name\":\"House Rent Allowance\",\"amount\":19247.6},{\"name\":\"Special Allowance\",\"amount\":28871.4}]', '[{\"name\":\"Provident Fund\",\"amount\":5774.28},{\"name\":\"Income Tax\",\"amount\":4811.9}]', 96238.00, 85651.82, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(40, 1, 91, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":61593},{\"name\":\"House Rent Allowance\",\"amount\":24637.2},{\"name\":\"Special Allowance\",\"amount\":36955.8}]', '[{\"name\":\"Provident Fund\",\"amount\":7391.16},{\"name\":\"Income Tax\",\"amount\":6159.3}]', 123186.00, 109635.54, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(41, 1, 95, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":58589.5},{\"name\":\"House Rent Allowance\",\"amount\":23435.8},{\"name\":\"Special Allowance\",\"amount\":35153.7}]', '[{\"name\":\"Provident Fund\",\"amount\":7030.74},{\"name\":\"Income Tax\",\"amount\":5858.95}]', 117179.00, 104289.31, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(42, 1, 100, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":54125.5},{\"name\":\"House Rent Allowance\",\"amount\":21650.2},{\"name\":\"Special Allowance\",\"amount\":32475.3}]', '[{\"name\":\"Provident Fund\",\"amount\":6495.06},{\"name\":\"Income Tax\",\"amount\":5412.55}]', 108251.00, 96343.39, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(43, 1, 102, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":19707},{\"name\":\"House Rent Allowance\",\"amount\":7882.8},{\"name\":\"Special Allowance\",\"amount\":11824.2}]', '[{\"name\":\"Provident Fund\",\"amount\":2364.84},{\"name\":\"Income Tax\",\"amount\":1970.7}]', 39414.00, 35078.46, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(44, 1, 105, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":25069.5},{\"name\":\"House Rent Allowance\",\"amount\":10027.8},{\"name\":\"Special Allowance\",\"amount\":15041.7}]', '[{\"name\":\"Provident Fund\",\"amount\":3008.34},{\"name\":\"Income Tax\",\"amount\":2506.95}]', 50139.00, 44623.71, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(45, 1, 108, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":57909},{\"name\":\"House Rent Allowance\",\"amount\":23163.6},{\"name\":\"Special Allowance\",\"amount\":34745.4}]', '[{\"name\":\"Provident Fund\",\"amount\":6949.08},{\"name\":\"Income Tax\",\"amount\":5790.9}]', 115818.00, 103078.02, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(46, 1, 59, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":19292.5},{\"name\":\"House Rent Allowance\",\"amount\":7717},{\"name\":\"Special Allowance\",\"amount\":11575.5}]', '[{\"name\":\"Provident Fund\",\"amount\":2315.1},{\"name\":\"Income Tax\",\"amount\":1929.25}]', 38585.00, 34340.65, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(47, 1, 72, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":42584},{\"name\":\"House Rent Allowance\",\"amount\":17033.6},{\"name\":\"Special Allowance\",\"amount\":25550.4}]', '[{\"name\":\"Provident Fund\",\"amount\":5110.08},{\"name\":\"Income Tax\",\"amount\":4258.4}]', 85168.00, 75799.52, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(48, 1, 73, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":36919},{\"name\":\"House Rent Allowance\",\"amount\":14767.6},{\"name\":\"Special Allowance\",\"amount\":22151.4}]', '[{\"name\":\"Provident Fund\",\"amount\":4430.28},{\"name\":\"Income Tax\",\"amount\":3691.9}]', 73838.00, 65715.82, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(49, 1, 80, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":43872},{\"name\":\"House Rent Allowance\",\"amount\":17548.8},{\"name\":\"Special Allowance\",\"amount\":26323.2}]', '[{\"name\":\"Provident Fund\",\"amount\":5264.64},{\"name\":\"Income Tax\",\"amount\":4387.2}]', 87744.00, 78092.16, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(50, 1, 87, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":25246},{\"name\":\"House Rent Allowance\",\"amount\":10098.4},{\"name\":\"Special Allowance\",\"amount\":15147.6}]', '[{\"name\":\"Provident Fund\",\"amount\":3029.52},{\"name\":\"Income Tax\",\"amount\":2524.6}]', 50492.00, 44937.88, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(51, 1, 89, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":68828.5},{\"name\":\"House Rent Allowance\",\"amount\":27531.4},{\"name\":\"Special Allowance\",\"amount\":41297.1}]', '[{\"name\":\"Provident Fund\",\"amount\":8259.42},{\"name\":\"Income Tax\",\"amount\":6882.85}]', 137657.00, 122514.73, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(52, 1, 93, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":19236},{\"name\":\"House Rent Allowance\",\"amount\":7694.4},{\"name\":\"Special Allowance\",\"amount\":11541.6}]', '[{\"name\":\"Provident Fund\",\"amount\":2308.32},{\"name\":\"Income Tax\",\"amount\":1923.6}]', 38472.00, 34240.08, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(53, 1, 99, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":19794},{\"name\":\"House Rent Allowance\",\"amount\":7917.6},{\"name\":\"Special Allowance\",\"amount\":11876.4}]', '[{\"name\":\"Provident Fund\",\"amount\":2375.28},{\"name\":\"Income Tax\",\"amount\":1979.4}]', 39588.00, 35233.32, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(54, 1, 104, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":58645},{\"name\":\"House Rent Allowance\",\"amount\":23458},{\"name\":\"Special Allowance\",\"amount\":35187}]', '[{\"name\":\"Provident Fund\",\"amount\":7037.4},{\"name\":\"Income Tax\",\"amount\":5864.5}]', 117290.00, 104388.10, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(55, 1, 107, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":24391.5},{\"name\":\"House Rent Allowance\",\"amount\":9756.6},{\"name\":\"Special Allowance\",\"amount\":14634.9}]', '[{\"name\":\"Provident Fund\",\"amount\":2926.98},{\"name\":\"Income Tax\",\"amount\":2439.15}]', 48783.00, 43416.87, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL),
(56, 1, 109, '2026-02', 'INR', '[{\"name\":\"Basic Salary\",\"amount\":67645},{\"name\":\"House Rent Allowance\",\"amount\":27058},{\"name\":\"Special Allowance\",\"amount\":40587}]', '[{\"name\":\"Provident Fund\",\"amount\":8117.4},{\"name\":\"Income Tax\",\"amount\":6764.5}]', 135290.00, 120408.10, 1, 'generated', 1, '2026-03-06 16:42:17', NULL, NULL);

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

--
-- Dumping data for table `performance`
--

INSERT INTO `performance` (`id`, `employee_id`, `evaluator_id`, `approved_by`, `period`, `score`, `remarks`, `created_at`) VALUES
(12, 1, 30, 30, '2026-02', 84, 'Met expectations.', '2026-03-06 16:42:18'),
(13, 56, 30, 30, '2026-02', 71, 'Needs improvement.', '2026-03-06 16:42:18'),
(14, 57, 30, 30, '2026-02', 97, 'Outstanding performance.', '2026-03-06 16:42:18'),
(15, 58, 30, 30, '2026-02', 74, 'Needs improvement.', '2026-03-06 16:42:18'),
(16, 60, 30, 30, '2026-02', 75, 'Needs improvement.', '2026-03-06 16:42:18'),
(17, 63, 30, 30, '2026-02', 94, 'Outstanding performance.', '2026-03-06 16:42:18'),
(18, 64, 30, 30, '2026-02', 82, 'Met expectations.', '2026-03-06 16:42:18'),
(19, 78, 30, 30, '2026-02', 83, 'Met expectations.', '2026-03-06 16:42:18'),
(20, 90, 30, 30, '2026-02', 96, 'Outstanding performance.', '2026-03-06 16:42:18'),
(21, 92, 30, 30, '2026-02', 91, 'Outstanding performance.', '2026-03-06 16:42:18'),
(22, 97, 30, 30, '2026-02', 66, 'Needs improvement.', '2026-03-06 16:42:18'),
(23, 62, 30, 30, '2026-02', 76, 'Needs improvement.', '2026-03-06 16:42:18'),
(24, 65, 30, 30, '2026-02', 69, 'Needs improvement.', '2026-03-06 16:42:18'),
(25, 66, 30, 30, '2026-02', 70, 'Needs improvement.', '2026-03-06 16:42:18'),
(26, 68, 30, 30, '2026-02', 82, 'Met expectations.', '2026-03-06 16:42:18'),
(27, 69, 30, 30, '2026-02', 80, 'Needs improvement.', '2026-03-06 16:42:18'),
(28, 75, 30, 30, '2026-02', 75, 'Needs improvement.', '2026-03-06 16:42:18'),
(29, 76, 30, 30, '2026-02', 94, 'Outstanding performance.', '2026-03-06 16:42:18'),
(30, 77, 30, 30, '2026-02', 81, 'Met expectations.', '2026-03-06 16:42:18'),
(31, 83, 30, 30, '2026-02', 78, 'Needs improvement.', '2026-03-06 16:42:18'),
(32, 94, 30, 30, '2026-02', 72, 'Needs improvement.', '2026-03-06 16:42:18'),
(33, 98, 30, 30, '2026-02', 89, 'Met expectations.', '2026-03-06 16:42:18'),
(34, 103, 30, 30, '2026-02', 82, 'Met expectations.', '2026-03-06 16:42:18'),
(35, 106, 30, 30, '2026-02', 66, 'Needs improvement.', '2026-03-06 16:42:18'),
(36, 67, 30, 30, '2026-02', 98, 'Outstanding performance.', '2026-03-06 16:42:18'),
(37, 70, 30, 30, '2026-02', 85, 'Met expectations.', '2026-03-06 16:42:18'),
(38, 71, 30, 30, '2026-02', 76, 'Needs improvement.', '2026-03-06 16:42:18'),
(39, 74, 30, 30, '2026-02', 69, 'Needs improvement.', '2026-03-06 16:42:18'),
(40, 81, 30, 30, '2026-02', 83, 'Met expectations.', '2026-03-06 16:42:18'),
(41, 82, 30, 30, '2026-02', 93, 'Outstanding performance.', '2026-03-06 16:42:18'),
(42, 84, 30, 30, '2026-02', 93, 'Outstanding performance.', '2026-03-06 16:42:18'),
(43, 86, 30, 30, '2026-02', 70, 'Needs improvement.', '2026-03-06 16:42:18'),
(44, 88, 30, 30, '2026-02', 79, 'Needs improvement.', '2026-03-06 16:42:18'),
(45, 96, 30, 30, '2026-02', 76, 'Needs improvement.', '2026-03-06 16:42:18'),
(46, 101, 30, 30, '2026-02', 83, 'Met expectations.', '2026-03-06 16:42:18'),
(47, 110, 30, 30, '2026-02', 73, 'Needs improvement.', '2026-03-06 16:42:18'),
(48, 61, 30, 30, '2026-02', 82, 'Met expectations.', '2026-03-06 16:42:18'),
(49, 79, 30, 30, '2026-02', 73, 'Needs improvement.', '2026-03-06 16:42:18'),
(50, 85, 30, 30, '2026-02', 95, 'Outstanding performance.', '2026-03-06 16:42:18'),
(51, 91, 30, 30, '2026-02', 98, 'Outstanding performance.', '2026-03-06 16:42:18'),
(52, 95, 30, 30, '2026-02', 86, 'Met expectations.', '2026-03-06 16:42:18'),
(53, 100, 30, 30, '2026-02', 68, 'Needs improvement.', '2026-03-06 16:42:18'),
(54, 102, 30, 30, '2026-02', 74, 'Needs improvement.', '2026-03-06 16:42:18'),
(55, 105, 30, 30, '2026-02', 78, 'Needs improvement.', '2026-03-06 16:42:18'),
(56, 108, 30, 30, '2026-02', 78, 'Needs improvement.', '2026-03-06 16:42:18'),
(57, 59, 30, 30, '2026-02', 71, 'Needs improvement.', '2026-03-06 16:42:18'),
(58, 72, 30, 30, '2026-02', 67, 'Needs improvement.', '2026-03-06 16:42:18'),
(59, 73, 30, 30, '2026-02', 71, 'Needs improvement.', '2026-03-06 16:42:18'),
(60, 80, 30, 30, '2026-02', 67, 'Needs improvement.', '2026-03-06 16:42:18'),
(61, 87, 30, 30, '2026-02', 80, 'Needs improvement.', '2026-03-06 16:42:18'),
(62, 89, 30, 30, '2026-02', 71, 'Needs improvement.', '2026-03-06 16:42:18'),
(63, 93, 30, 30, '2026-02', 79, 'Needs improvement.', '2026-03-06 16:42:18'),
(64, 99, 30, 30, '2026-02', 71, 'Needs improvement.', '2026-03-06 16:42:18'),
(65, 104, 30, 30, '2026-02', 75, 'Needs improvement.', '2026-03-06 16:42:18'),
(66, 107, 30, 30, '2026-02', 65, 'Needs improvement.', '2026-03-06 16:42:18'),
(67, 109, 30, 30, '2026-02', 71, 'Needs improvement.', '2026-03-06 16:42:18');

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

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `company_id`, `policy_name`, `content`, `file_path`, `created_at`) VALUES
(1, 1, 'Code of Conduct', 'Guidelines outlining the social norms, rules, and responsibilities of...', NULL, '2026-03-06 16:42:18'),
(2, 1, 'IT Usage Policy', 'Rules regarding the use of company computers, internet, and data...', NULL, '2026-03-06 16:42:18'),
(3, 1, 'Remote Work Policy', 'Eligibility and expectations for employees working from home...', NULL, '2026-03-06 16:42:18'),
(4, 1, 'Travel & Expense Policy', 'Procedures for booking travel and claiming reimbursements...', NULL, '2026-03-06 16:42:18'),
(5, 1, 'Health & Safety', 'Workplace safety protocols and emergency procedures...', NULL, '2026-03-06 16:42:18');

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
(1, 1, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(2, 2, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(3, 3, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(4, 4, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(5, 5, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(6, 6, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(7, 7, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(8, 8, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(9, 9, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(10, 10, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(11, 11, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(12, 12, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(13, 13, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(14, 14, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(15, 15, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(16, 16, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(17, 17, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(18, 18, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(19, 19, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(20, 20, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(21, 21, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(22, 22, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(23, 23, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(24, 24, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(25, 25, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(26, 26, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(27, 27, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(28, 28, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(29, 29, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(30, 30, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(31, 31, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(32, 32, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(33, 33, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(34, 34, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(35, 35, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(36, 36, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(37, 37, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(38, 38, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(39, 39, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(40, 40, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(41, 41, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(42, 42, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(43, 43, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(44, 44, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(45, 45, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(46, 46, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(47, 47, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(48, 48, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(49, 49, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(50, 50, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(51, 51, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(52, 52, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(53, 53, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(54, 54, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(55, 55, 'General Shift', '09:00:00', '18:00:00', 'Standard Day Shift'),
(56, 1, 'Morning Shift', '06:00:00', '14:00:00', NULL),
(57, 1, 'Night Shift', '22:00:00', '06:00:00', NULL),
(58, 2, 'Morning Shift', '06:00:00', '14:00:00', NULL),
(59, 2, 'Night Shift', '22:00:00', '06:00:00', NULL),
(60, 3, 'Morning Shift', '06:00:00', '14:00:00', NULL),
(61, 3, 'Night Shift', '22:00:00', '06:00:00', NULL);

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

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `message`, `priority`, `status`, `created_at`, `updated_at`) VALUES
(1, 66, 'Need access to Jira', 'I am facing issues with Need access to Jira. Please help.', 'medium', 'open', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(2, 85, 'Need access to Jira', 'I am facing issues with Need access to Jira. Please help.', 'medium', 'closed', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(3, 67, 'Request for new monitor', 'I am facing issues with Request for new monitor. Please help.', 'medium', 'closed', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(4, 89, 'Need access to Jira', 'I am facing issues with Need access to Jira. Please help.', 'medium', 'in_progress', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(5, 88, 'VPN connection issues', 'I am facing issues with VPN connection issues. Please help.', 'high', 'in_progress', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(6, 74, 'VPN connection issues', 'I am facing issues with VPN connection issues. Please help.', 'high', 'open', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(7, 68, 'Laptop overheating', 'I am facing issues with Laptop overheating. Please help.', 'high', 'open', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(8, 100, 'Payroll discrepancy in last month', 'I am facing issues with Payroll discrepancy in last month. Please help.', 'high', 'in_progress', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(9, 69, 'Need access to Jira', 'I am facing issues with Need access to Jira. Please help.', 'medium', 'open', '2026-03-06 16:42:18', '2026-03-06 16:42:18'),
(10, 2, 'Need access to Jira', 'I am facing issues with Need access to Jira. Please help.', 'medium', 'open', '2026-03-06 16:42:18', '2026-03-06 16:42:18');

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
(1, 106, NULL, 'Generate Report 262', 'Please complete this task regarding Generate Report 262.', 1, '2026-03-08', 'completed', '2026-03-06 16:42:19'),
(2, 99, NULL, 'Update Database 841', 'Please complete this task regarding Update Database 841.', 1, '2026-03-17', 'pending', '2026-03-06 16:42:19'),
(3, 105, 2, 'Documentation 183', 'Please complete this task regarding Documentation 183.', 1, '2026-03-19', 'in_progress', '2026-03-06 16:42:19'),
(4, 86, 3, 'Server Maintenance 743', 'Please complete this task regarding Server Maintenance 743.', 1, '2026-03-19', 'pending', '2026-03-06 16:42:19'),
(5, 82, 6, 'Generate Report 834', 'Please complete this task regarding Generate Report 834.', 1, '2026-03-13', 'pending', '2026-03-06 16:42:19'),
(6, 90, 3, 'Team Sync 734', 'Please complete this task regarding Team Sync 734.', 1, '2026-03-18', 'cancelled', '2026-03-06 16:42:19'),
(7, 91, NULL, 'Fix Bugs 868', 'Please complete this task regarding Fix Bugs 868.', 1, '2026-03-17', 'completed', '2026-03-06 16:42:19'),
(8, 110, 4, 'Fix Bugs 470', 'Please complete this task regarding Fix Bugs 470.', 1, '2026-03-14', 'pending', '2026-03-06 16:42:19'),
(9, 85, NULL, 'Deployment 233', 'Please complete this task regarding Deployment 233.', 1, '2026-03-16', 'pending', '2026-03-06 16:42:19'),
(10, 63, NULL, 'Documentation 276', 'Please complete this task regarding Documentation 276.', 1, '2026-03-12', 'in_progress', '2026-03-06 16:42:19'),
(11, 96, NULL, 'Client Meeting 728', 'Please complete this task regarding Client Meeting 728.', 1, '2026-03-19', 'in_progress', '2026-03-06 16:42:19'),
(12, 57, 5, 'Client Meeting 391', 'Please complete this task regarding Client Meeting 391.', 1, '2026-03-10', 'in_progress', '2026-03-06 16:42:19'),
(13, 86, 2, 'Documentation 140', 'Please complete this task regarding Documentation 140.', 1, '2026-03-18', 'cancelled', '2026-03-06 16:42:19'),
(14, 107, NULL, 'Fix Bugs 906', 'Please complete this task regarding Fix Bugs 906.', 1, '2026-03-14', 'pending', '2026-03-06 16:42:19'),
(15, 59, NULL, 'Inventory Check 465', 'Please complete this task regarding Inventory Check 465.', 1, '2026-03-14', 'cancelled', '2026-03-06 16:42:19'),
(16, 70, NULL, 'Deployment 564', 'Please complete this task regarding Deployment 564.', 1, '2026-03-19', 'completed', '2026-03-06 16:42:19'),
(17, 100, 1, 'Audit Preparation 285', 'Please complete this task regarding Audit Preparation 285.', 1, '2026-03-08', 'in_progress', '2026-03-06 16:42:19'),
(18, 95, 4, 'Generate Report 125', 'Please complete this task regarding Generate Report 125.', 1, '2026-03-17', 'cancelled', '2026-03-06 16:42:19'),
(19, 63, NULL, 'Update Database 623', 'Please complete this task regarding Update Database 623.', 1, '2026-03-18', 'in_progress', '2026-03-06 16:42:19'),
(20, 103, 4, 'Fix Bugs 743', 'Please complete this task regarding Fix Bugs 743.', 1, '2026-03-14', 'in_progress', '2026-03-06 16:42:19'),
(21, 83, NULL, 'Client Meeting 387', 'Please complete this task regarding Client Meeting 387.', 1, '2026-03-10', 'cancelled', '2026-03-06 16:42:19'),
(22, 99, 1, 'Inventory Check 560', 'Please complete this task regarding Inventory Check 560.', 1, '2026-03-20', 'pending', '2026-03-06 16:42:19'),
(23, 58, 4, 'Team Sync 251', 'Please complete this task regarding Team Sync 251.', 1, '2026-03-10', 'completed', '2026-03-06 16:42:19'),
(24, 62, NULL, 'Deployment 910', 'Please complete this task regarding Deployment 910.', 1, '2026-03-08', 'in_progress', '2026-03-06 16:42:19'),
(25, 108, 3, 'Fix Bugs 449', 'Please complete this task regarding Fix Bugs 449.', 1, '2026-03-13', 'cancelled', '2026-03-06 16:42:19'),
(26, 110, 4, 'Inventory Check 351', 'Please complete this task regarding Inventory Check 351.', 1, '2026-03-16', 'in_progress', '2026-03-06 16:42:19'),
(27, 60, 4, 'Inventory Check 622', 'Please complete this task regarding Inventory Check 622.', 1, '2026-03-14', 'cancelled', '2026-03-06 16:42:19'),
(28, 68, 2, 'Generate Report 876', 'Please complete this task regarding Generate Report 876.', 1, '2026-03-18', 'pending', '2026-03-06 16:42:19'),
(29, 71, 1, 'Team Sync 325', 'Please complete this task regarding Team Sync 325.', 1, '2026-03-14', 'pending', '2026-03-06 16:42:19'),
(30, 74, NULL, 'Server Maintenance 413', 'Please complete this task regarding Server Maintenance 413.', 1, '2026-03-16', 'pending', '2026-03-06 16:42:19'),
(31, 59, 3, 'Fix Bugs 397', 'Please complete this task regarding Fix Bugs 397.', 1, '2026-03-16', 'pending', '2026-03-06 16:42:19'),
(32, 71, 3, 'Inventory Check 679', 'Please complete this task regarding Inventory Check 679.', 1, '2026-03-13', 'in_progress', '2026-03-06 16:42:19'),
(33, 72, 5, 'Audit Preparation 259', 'Please complete this task regarding Audit Preparation 259.', 1, '2026-03-19', 'in_progress', '2026-03-06 16:42:19'),
(34, 62, NULL, 'Audit Preparation 333', 'Please complete this task regarding Audit Preparation 333.', 1, '2026-03-10', 'in_progress', '2026-03-06 16:42:19'),
(35, 61, 6, 'Inventory Check 263', 'Please complete this task regarding Inventory Check 263.', 1, '2026-03-11', 'pending', '2026-03-06 16:42:19'),
(36, 90, NULL, 'Fix Bugs 497', 'Please complete this task regarding Fix Bugs 497.', 1, '2026-03-18', 'in_progress', '2026-03-06 16:42:19'),
(37, 56, NULL, 'Client Meeting 855', 'Please complete this task regarding Client Meeting 855.', 1, '2026-03-14', 'completed', '2026-03-06 16:42:19'),
(38, 60, NULL, 'Inventory Check 226', 'Please complete this task regarding Inventory Check 226.', 1, '2026-03-20', 'cancelled', '2026-03-06 16:42:19'),
(39, 86, NULL, 'Server Maintenance 858', 'Please complete this task regarding Server Maintenance 858.', 1, '2026-03-12', 'pending', '2026-03-06 16:42:19'),
(40, 73, 4, 'Documentation 676', 'Please complete this task regarding Documentation 676.', 1, '2026-03-17', 'pending', '2026-03-06 16:42:19'),
(41, 92, NULL, 'Fix Bugs 391', 'Please complete this task regarding Fix Bugs 391.', 1, '2026-03-15', 'cancelled', '2026-03-06 16:42:19'),
(42, 104, 5, 'Documentation 284', 'Please complete this task regarding Documentation 284.', 1, '2026-03-09', 'cancelled', '2026-03-06 16:42:19'),
(43, 69, 5, 'Client Meeting 515', 'Please complete this task regarding Client Meeting 515.', 1, '2026-03-14', 'pending', '2026-03-06 16:42:19'),
(44, 99, NULL, 'Inventory Check 209', 'Please complete this task regarding Inventory Check 209.', 1, '2026-03-14', 'cancelled', '2026-03-06 16:42:19'),
(45, 76, NULL, 'Team Sync 865', 'Please complete this task regarding Team Sync 865.', 1, '2026-03-11', 'completed', '2026-03-06 16:42:19'),
(46, 66, 4, 'Generate Report 112', 'Please complete this task regarding Generate Report 112.', 1, '2026-03-08', 'pending', '2026-03-06 16:42:19'),
(47, 71, 6, 'Inventory Check 238', 'Please complete this task regarding Inventory Check 238.', 1, '2026-03-19', 'cancelled', '2026-03-06 16:42:19'),
(48, 77, 4, 'Documentation 652', 'Please complete this task regarding Documentation 652.', 1, '2026-03-20', 'pending', '2026-03-06 16:42:19'),
(49, 100, 1, 'Deployment 857', 'Please complete this task regarding Deployment 857.', 1, '2026-03-14', 'pending', '2026-03-06 16:42:19'),
(50, 58, 3, 'Team Sync 173', 'Please complete this task regarding Team Sync 173.', 1, '2026-03-18', 'pending', '2026-03-06 16:42:19');

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
(1, 1, 'DevOps Team 20', 'Team for Information Technology tasks', 2, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(2, 1, 'Frontend Ninjas 84', 'Team for Information Technology tasks', 2, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(3, 1, 'Audit Team 64', 'Team for Finance tasks', 2, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(4, 1, 'Brand Warriors 36', 'Team for Sales & Marketing tasks', 2, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(5, 1, 'Quality Control 33', 'Team for Operations tasks', 2, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(6, 1, 'Brand Warriors 22', 'Team for Sales & Marketing tasks', 2, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(7, 2, 'Lead Gen Team 5', 'Team for Sales & Marketing tasks', 3, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(8, 2, 'Audit Team 86', 'Team for Finance tasks', 3, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(9, 2, 'Quality Control 84', 'Team for Operations tasks', 3, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(10, 2, 'Core Tech 21', 'Team for Information Technology tasks', 3, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(11, 2, 'DevOps Team 41', 'Team for Information Technology tasks', 3, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(12, 3, 'Logistics Unit 53', 'Team for Operations tasks', 4, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(13, 3, 'Budget Control 40', 'Team for Finance tasks', 4, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(14, 3, 'Core Tech 73', 'Team for Information Technology tasks', 4, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(15, 3, 'Administration Squad 26', 'Team for Administration tasks', 4, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(16, 3, 'Recruitment Squad 60', 'Team for Human Resources tasks', 4, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11'),
(17, 3, 'HR Ops 8', 'Team for Human Resources tasks', 4, '2026-03-06 12:12:11', NULL, '2026-03-06 16:42:11');

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
(1, 1, 62, NULL, 2, '2026-03-06 12:12:11'),
(2, 1, 77, NULL, 2, '2026-03-06 12:12:11'),
(3, 1, 106, NULL, 2, '2026-03-06 12:12:11'),
(4, 2, 62, NULL, 2, '2026-03-06 12:12:11'),
(5, 2, 65, NULL, 2, '2026-03-06 12:12:11'),
(6, 2, 68, NULL, 2, '2026-03-06 12:12:11'),
(7, 2, 98, NULL, 2, '2026-03-06 12:12:11'),
(8, 2, 103, NULL, 2, '2026-03-06 12:12:11'),
(9, 2, 106, NULL, 2, '2026-03-06 12:12:11'),
(10, 3, 61, NULL, 2, '2026-03-06 12:12:11'),
(11, 3, 91, NULL, 2, '2026-03-06 12:12:11'),
(12, 3, 95, NULL, 2, '2026-03-06 12:12:11'),
(13, 3, 100, NULL, 2, '2026-03-06 12:12:11'),
(14, 3, 102, NULL, 2, '2026-03-06 12:12:11'),
(15, 3, 105, NULL, 2, '2026-03-06 12:12:11'),
(16, 3, 108, NULL, 2, '2026-03-06 12:12:11'),
(17, 4, 70, NULL, 2, '2026-03-06 12:12:11'),
(18, 4, 71, NULL, 2, '2026-03-06 12:12:11'),
(19, 4, 82, NULL, 2, '2026-03-06 12:12:11'),
(20, 4, 88, NULL, 2, '2026-03-06 12:12:11'),
(21, 4, 96, NULL, 2, '2026-03-06 12:12:11'),
(22, 4, 101, NULL, 2, '2026-03-06 12:12:11'),
(23, 5, 80, NULL, 2, '2026-03-06 12:12:11'),
(24, 5, 89, NULL, 2, '2026-03-06 12:12:11'),
(25, 5, 99, NULL, 2, '2026-03-06 12:12:11'),
(26, 5, 107, NULL, 2, '2026-03-06 12:12:11'),
(27, 5, 109, NULL, 2, '2026-03-06 12:12:11'),
(28, 6, 67, NULL, 2, '2026-03-06 12:12:11'),
(29, 6, 70, NULL, 2, '2026-03-06 12:12:11'),
(30, 6, 71, NULL, 2, '2026-03-06 12:12:11'),
(31, 6, 74, NULL, 2, '2026-03-06 12:12:11'),
(32, 6, 82, NULL, 2, '2026-03-06 12:12:11'),
(33, 6, 86, NULL, 2, '2026-03-06 12:12:11'),
(34, 6, 101, NULL, 2, '2026-03-06 12:12:11'),
(35, 6, 110, NULL, 2, '2026-03-06 12:12:11'),
(36, 7, 140, NULL, 3, '2026-03-06 12:12:11'),
(37, 7, 141, NULL, 3, '2026-03-06 12:12:11'),
(38, 7, 160, NULL, 3, '2026-03-06 12:12:11'),
(39, 8, 125, NULL, 3, '2026-03-06 12:12:11'),
(40, 8, 134, NULL, 3, '2026-03-06 12:12:11'),
(41, 8, 135, NULL, 3, '2026-03-06 12:12:11'),
(42, 8, 136, NULL, 3, '2026-03-06 12:12:11'),
(43, 8, 139, NULL, 3, '2026-03-06 12:12:11'),
(44, 8, 147, NULL, 3, '2026-03-06 12:12:11'),
(45, 8, 151, NULL, 3, '2026-03-06 12:12:11'),
(46, 8, 154, NULL, 3, '2026-03-06 12:12:11'),
(47, 9, 124, NULL, 3, '2026-03-06 12:12:11'),
(48, 9, 126, NULL, 3, '2026-03-06 12:12:11'),
(49, 9, 146, NULL, 3, '2026-03-06 12:12:11'),
(50, 9, 164, NULL, 3, '2026-03-06 12:12:11'),
(51, 10, 117, NULL, 3, '2026-03-06 12:12:11'),
(52, 10, 123, NULL, 3, '2026-03-06 12:12:11'),
(53, 10, 132, NULL, 3, '2026-03-06 12:12:11'),
(54, 10, 149, NULL, 3, '2026-03-06 12:12:11'),
(55, 10, 156, NULL, 3, '2026-03-06 12:12:11'),
(56, 10, 157, NULL, 3, '2026-03-06 12:12:11'),
(57, 10, 159, NULL, 3, '2026-03-06 12:12:11'),
(58, 10, 163, NULL, 3, '2026-03-06 12:12:11'),
(59, 11, 121, NULL, 3, '2026-03-06 12:12:11'),
(60, 11, 123, NULL, 3, '2026-03-06 12:12:11'),
(61, 11, 132, NULL, 3, '2026-03-06 12:12:11'),
(62, 11, 133, NULL, 3, '2026-03-06 12:12:11'),
(63, 11, 149, NULL, 3, '2026-03-06 12:12:11'),
(64, 11, 156, NULL, 3, '2026-03-06 12:12:11'),
(65, 11, 157, NULL, 3, '2026-03-06 12:12:11'),
(66, 11, 163, NULL, 3, '2026-03-06 12:12:11'),
(67, 12, 178, NULL, 4, '2026-03-06 12:12:11'),
(68, 12, 181, NULL, 4, '2026-03-06 12:12:11'),
(69, 12, 185, NULL, 4, '2026-03-06 12:12:11'),
(70, 13, 190, NULL, 4, '2026-03-06 12:12:11'),
(71, 13, 204, NULL, 4, '2026-03-06 12:12:11'),
(72, 13, 205, NULL, 4, '2026-03-06 12:12:11'),
(73, 13, 214, NULL, 4, '2026-03-06 12:12:11'),
(74, 13, 220, NULL, 4, '2026-03-06 12:12:11'),
(75, 14, 171, NULL, 4, '2026-03-06 12:12:11'),
(76, 14, 173, NULL, 4, '2026-03-06 12:12:11'),
(77, 14, 192, NULL, 4, '2026-03-06 12:12:11'),
(78, 14, 194, NULL, 4, '2026-03-06 12:12:11'),
(79, 14, 207, NULL, 4, '2026-03-06 12:12:11'),
(80, 15, 3, NULL, 4, '2026-03-06 12:12:11'),
(81, 16, 166, NULL, 4, '2026-03-06 12:12:11'),
(82, 16, 167, NULL, 4, '2026-03-06 12:12:11'),
(83, 16, 170, NULL, 4, '2026-03-06 12:12:11'),
(84, 16, 187, NULL, 4, '2026-03-06 12:12:11'),
(85, 16, 188, NULL, 4, '2026-03-06 12:12:11'),
(86, 16, 208, NULL, 4, '2026-03-06 12:12:11'),
(87, 16, 213, NULL, 4, '2026-03-06 12:12:11'),
(88, 16, 218, NULL, 4, '2026-03-06 12:12:11'),
(89, 17, 167, NULL, 4, '2026-03-06 12:12:11'),
(90, 17, 175, NULL, 4, '2026-03-06 12:12:11'),
(91, 17, 188, NULL, 4, '2026-03-06 12:12:11'),
(92, 17, 215, NULL, 4, '2026-03-06 12:12:11');

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

--
-- Dumping data for table `team_performance`
--

INSERT INTO `team_performance` (`id`, `team_id`, `period`, `score`, `collaboration_score`, `achievements`, `challenges`, `remarks`, `evaluated_by`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-02', 80, 8, 'Delivered all milestones for 2026-02.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(2, 1, '2026-03', 81, 10, 'Delivered all milestones for 2026-03.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(3, 2, '2026-02', 97, 8, 'Delivered all milestones for 2026-02.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(4, 2, '2026-03', 75, 8, 'Delivered all milestones for 2026-03.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(5, 3, '2026-02', 73, 7, 'Delivered all milestones for 2026-02.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(6, 3, '2026-03', 71, 8, 'Delivered all milestones for 2026-03.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(7, 4, '2026-02', 85, 10, 'Delivered all milestones for 2026-02.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(8, 4, '2026-03', 75, 10, 'Delivered all milestones for 2026-03.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(9, 5, '2026-02', 79, 7, 'Delivered all milestones for 2026-02.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(10, 5, '2026-03', 93, 7, 'Delivered all milestones for 2026-03.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(11, 6, '2026-02', 96, 10, 'Delivered all milestones for 2026-02.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19'),
(12, 6, '2026-03', 94, 8, 'Delivered all milestones for 2026-03.', 'Minor resource constraints.', 'Good progress.', 1, 1, '2026-03-06 16:42:19', '2026-03-06 16:42:19');

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
  `reset_token_hash` varchar(64) DEFAULT NULL,
  `reset_token_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `company_id`, `role_id`, `username`, `email`, `password`, `status`, `reset_token_hash`, `reset_token_expires_at`, `created_at`) VALUES
(1, NULL, 1, 'admin', 'admin@mail.com', '$2y$10$4oXGSu5Ip7f2oJFXksjqA.927pO76waLG1YCGuyiQNj6QMoqrJW/W', 'active', NULL, NULL, '2025-09-06 06:34:45'),
(2, 1, 2, 'surya_holdings_19900605', 'surya.holdings.19900605@info.com', '$2y$10$pMD9kA1jvvmud8YYPJcs.uNcpKAAY4Y7i7rtb74fpPi6XUiDZuKLS', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(3, 2, 2, 'ram_realty_19750223', 'ram.realty.19750223@mail.com', '$2y$10$Xg0RxobnI0/zQJeUyBKl6OvA6pejiQD9HPMbYk/YOeWDTYLjXCVse', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(4, 3, 2, 'indus_corporation_19890518', 'indus.corporation.19890518@info.com', '$2y$10$l.iKwNY009SzgDElL928U.YFD4WEl4oIPCH6dTpVEVpsU2zaktKma', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(5, 4, 2, 'lakshmi_textiles_19890521', 'lakshmi.textiles.19890521@info.com', '$2y$10$vYGQddKPhzWRTQNSXpVW/.pZTY1/ZGivVtS39VmwtTz7Ly6KLac/C', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(6, 5, 2, 'chandra_ventures_19790426', 'chandra.ventures.19790426@info.com', '$2y$10$sCla.utKt4nyGuwYemEvL.KoplSXUpJcD.0ZJIQu8.2STAzF4nGU6', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(7, 6, 2, '19760317_shakti_construct', '19760317.shakti.construct@mail.com', '$2y$10$wZhjzYzGdfhUFcvAR9TkUOIIRVL.Rfse.OSUXCwClzJUv697t4V.i', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(8, 7, 2, 'ganga_works_19881001', 'ganga.works.19881001@info.com', '$2y$10$RXREdQ/eMp/zkwJiILKBFeq9WsnZxX1dxgdjw3TzNh1mEij7Mj2iK', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(9, 8, 2, '19951213_durga_corporation', '19951213.durga.corporation@info.com', '$2y$10$R6z9TJ7dxNy7hDm0ZIwy9uDEdssHXjgtKFEjq.Kla3Ij0LNmAptQi', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(10, 9, 2, 'labh_associates_19771106', 'labh.associates.19771106@info.com', '$2y$10$SDYepV1EEBqZD9fhmxdVfep5ImRvmBHU8a/Es9kFlNIyt3zxJz2VS', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(11, 10, 2, '19910710_hindustan_systems', '19910710.hindustan.systems@mail.com', '$2y$10$mCP9lLKGsHIb.RWvgTW/AeOI/XErivngwDYOhrQPyt0WDvGx0i9lK', 'active', NULL, NULL, '2026-03-06 16:41:56'),
(12, 11, 2, '19840308_udyog_works', '19840308.udyog.works@info.com', '$2y$10$9UBdjmleHXh2LYavmj3Iburs16G2tVI7X637cMtBhXFrhk/iAERXW', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(13, 12, 2, '19920206_shubh_systems', '19920206.shubh.systems@info.com', '$2y$10$tuEn3rnc9Jk0fZX4P7z9Re68LMXqlz0u6Iy/oEnH8aAlEMDzMIXw.', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(14, 13, 2, '19761127_shakti_services', '19761127.shakti.services@mail.com', '$2y$10$WlK.Mz/Vw7I1girT7xzPFevtttv7Q90w.n1gtdDdnM1FlqPyzQ/OK', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(15, 14, 2, '19911019_anand_partners', '19911019.anand.partners@info.com', '$2y$10$mQgl5YXCX55xagqE6pjK8.bCxFz5Lm3hLt23aQfYHc7bMbVyieK8S', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(16, 15, 2, 'brahma_traders_19860907', 'brahma.traders.19860907@mail.com', '$2y$10$Ur32Z5tFYzq8DlKOqgqDruZ0KlSH5ZpcBoW/eGwNWv0fNzGBtD1YW', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(17, 16, 2, '19790514_vishnu_technologies', '19790514.vishnu.technologies@mail.com', '$2y$10$NM3wKTYAosrAotA8A1oyNuU72cYGO2j2ojXStzRC4jAuGnh4r1ZR6', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(18, 17, 2, 'surya_group_19910501', 'surya.group.19910501@mail.com', '$2y$10$H9nAZozwkxZXtOqDp.XCxOfOGaY4tZX0fzSjZ178Q0EqovYOifDaO', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(19, 18, 2, '19850606_kaveri_industries', '19850606.kaveri.industries@info.com', '$2y$10$Ie7q1KZ4Pyc/Q4BxoIPWI.ldIe4NBlosADP1Y7Y6ax.O9CLTFFO8q', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(20, 19, 2, '19790808_navyug_realty', '19790808.navyug.realty@mail.com', '$2y$10$9Hzc2ht.Nbl7vZWmDBbAAOedsWYYezEJ3KDXwLjCgS6zsp5o7X7jm', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(21, 20, 2, '19830504_jai_solutions', '19830504.jai.solutions@info.com', '$2y$10$tgpwZwW33xQX3PktGMMss.QjOtwx.eTUlnPo2swzpibRNfgOy1Cfy', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(22, 21, 2, '19770812_vyapar_agency', '19770812.vyapar.agency@info.com', '$2y$10$YYcyD87Yrqu6k25zFasj7u6IxfvfvxVFI0DyD8kxuapFan2vkOGg6', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(23, 22, 2, 'bharat_partners_19830705', 'bharat.partners.19830705@info.com', '$2y$10$UVhTihmK9ZiOH1dbltzS5OYJaKoM2gfkbQ6/C2NNRiyul1p4uvsuG', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(24, 23, 2, '19780814_jai_services', '19780814.jai.services@mail.com', '$2y$10$9g1yY0zZyz9Nvyh9PXUl/uV5QpkwIBVk/5h3kzHu6rCnfeDWFRnwy', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(25, 24, 2, 'veda_traders_19820701', 'veda.traders.19820701@info.com', '$2y$10$A5lUKHfzsOqA8DVx6LD/rO7Co9kF3Yel5U9ZOTMOOWyviAr4agqiK', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(26, 25, 2, '19871218_malwa_solutions', '19871218.malwa.solutions@mail.com', '$2y$10$2YZ2DRwJKUV05sCjIWsTWemdq1vvZlIAY0TXcqnCr.UfrWr1Gvqji', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(27, 26, 2, 'veda_industries_19930506', 'veda.industries.19930506@info.com', '$2y$10$uTiJ9l3QNSWdXrurM53zouN1OLG8priTXcWa3qQnpktuyMpfTR8pW', 'active', NULL, NULL, '2026-03-06 16:41:57'),
(28, 27, 2, '19890710_indus_works', '19890710.indus.works@info.com', '$2y$10$6NEX1x0lu4EAxSEOr6Do5.rmlCzrDHMDf32ar0KOSISkvD5Nfvfa6', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(29, 28, 2, '19930724_surya_works', '19930724.surya.works@mail.com', '$2y$10$xiLPhqk8rhmdw.dKdO05f.Ed7ioDyKOcKedbvU8841B0EXmUcTJ92', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(30, 29, 2, 'saraswati_services_19870904', 'saraswati.services.19870904@info.com', '$2y$10$3swHOHmVX.hFGbvegqNKsO/ZFwneu9LHfxAFnykO/h2fzsYCggWtK', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(31, 30, 2, 'sarvottam_textiles_19920926', 'sarvottam.textiles.19920926@mail.com', '$2y$10$vCFHnwMrZXXkNCXdAmNRgeUvjJaOsGjcQum3AkJShAJrosFulDbuS', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(32, 31, 2, '19770219_ganga_ventures', '19770219.ganga.ventures@info.com', '$2y$10$4PqT7wMc/WnZIVVsXwHVTu3OwBHSN0/L2kPyjgA3hk91kNGunx6Qe', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(33, 32, 2, 'pragati_group_19790122', 'pragati.group.19790122@mail.com', '$2y$10$qWpJoKj4DuhAYlHC7EBcg.B/tPJKnzL.DPnydXZB1B3p8Tzu18oxS', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(34, 33, 2, '19790220_shreshth_services', '19790220.shreshth.services@info.com', '$2y$10$W9olyEXU5OuUTh.g0RVbYeWNK3tox2RKPccHu6chG/pn74fsqiGiC', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(35, 34, 2, 'navyug_logistics_19800815', 'navyug.logistics.19800815@info.com', '$2y$10$Q3LmHVC7fT4FOp6hEqCnHeuYqeEddtnuUj7V6MVlc6zFuu72liBkS', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(36, 35, 2, '19820622_udyog_works', '19820622.udyog.works@info.com', '$2y$10$O8j0hBnyx/AwZM4veF6sbeMOUmuLVKI5vS.mrpEtDoA8x.u/XE/CW', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(37, 36, 2, '19791118_anand_associates', '19791118.anand.associates@mail.com', '$2y$10$d9mbClv4KiNTAlze1mbFz.gQccb3LFhWV6QgT05rvQylSrLJBcTlO', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(38, 37, 2, '19860521_vindhya_realty', '19860521.vindhya.realty@mail.com', '$2y$10$HlpZhUzC15NWO6XG7Jploe5C/nHCU.mt5r3Uvv0wgNsayAQ8TOkwO', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(39, 38, 2, 'amrit_exports_19850325', 'amrit.exports.19850325@info.com', '$2y$10$zpn/OIKLwB6n1Q.nZK.k1OCD22IS78NI6znKjbnTcvNFd6RVAJgaC', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(40, 39, 2, '19810818_utkarsh_traders', '19810818.utkarsh.traders@info.com', '$2y$10$jmJmmKbQDCYH7Kq6HLUUzuEe5TCW/bMoZLzWkEUBSpGGtCxOTx5nC', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(41, 40, 2, '19871207_shreshth_associates', '19871207.shreshth.associates@mail.com', '$2y$10$d6NlbYmMCL6TjEeCU2LoI.X3yFRcZw6Nej.LGtd2cYBySCs0EuwR.', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(42, 41, 2, '19870411_brahma_industries', '19870411.brahma.industries@info.com', '$2y$10$/fNF7xfjobLXUC9Wp1VW1efZaapWOURbkG7U..fuFRppk4yqNNE.W', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(43, 42, 2, '19760813_navyug_services', '19760813.navyug.services@mail.com', '$2y$10$CR4.uW7SG1IlH753JEVKMuDVbAGZ9K9MiBBLk0QkpWgwiy8gAt8GG', 'active', NULL, NULL, '2026-03-06 16:41:58'),
(44, 43, 2, 'satpura_industries_19820510', 'satpura.industries.19820510@info.com', '$2y$10$0BTy1NLp6hhOQMQ2PWxd5ucOtHF1ncO7H6sgQJ1sVD2kIwDXrYTLy', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(45, 44, 2, '19910408_krishna_construct', '19910408.krishna.construct@mail.com', '$2y$10$mcnBPg3Qb1GQiOzaEbAGbeziAwkGJBRFEtm56hvmvpt7Hmj6T78L6', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(46, 45, 2, 'malwa_partners_19901023', 'malwa.partners.19901023@mail.com', '$2y$10$oARew96LpvZ64mjD5CtJveYOvzBORkkVC.hlaazUf6zI3B1S1.o1m', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(47, 46, 2, '19830417_shakti_holdings', '19830417.shakti.holdings@info.com', '$2y$10$Hzzus36i7u96/bBytR22XOwJbgXXljHmubTYOg53XS6VuRbjSUBOa', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(48, 47, 2, '19750601_himalaya_holdings', '19750601.himalaya.holdings@info.com', '$2y$10$jmBTZCNaLXFnDRYuAmtsTOZm0T6AyhqdJ35dotNiWjevc3xJ9XVHC', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(49, 48, 2, '19800303_sarvottam_systems', '19800303.sarvottam.systems@info.com', '$2y$10$a9OZZzrp4Q6NZt6iA3x1N.cPechOmYBlAkmgIaWJjo6ooGw9izely', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(50, 49, 2, 'durga_ventures_19750705', 'durga.ventures.19750705@mail.com', '$2y$10$FyIiGwPaJnTlSMp7f05qE.s5MwtrFguuFZOeWdOH2yXk0ArTSIhDK', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(51, 50, 2, '19760909_ganga_technologies', '19760909.ganga.technologies@mail.com', '$2y$10$i1XT/kh74x7iDzZ64A5qqeaPmaHkwft6Dha39ThjA.dvhLioBSJxy', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(52, 51, 2, '19940414_ram_solutions', '19940414.ram.solutions@info.com', '$2y$10$efmgUbGTR/k7ZkiCUpxFfOnkwul9UYkyUBeRoliwHMPwZr3bVSrMO', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(53, 52, 2, '19811126_godavari_group', '19811126.godavari.group@mail.com', '$2y$10$aEV2UwecQN66oU2hMy5fmOhhYHsMxj.X6vau6OVrkQR3lzaD/Om6m', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(54, 53, 2, 'amrit_technologies_19920123', 'amrit.technologies.19920123@info.com', '$2y$10$1zY9TssZU.S11c4xmLU56.8P2bKcINXoknQQJA.TEhKPwkxvYJkvS', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(55, 54, 2, '19870419_surya_associates', '19870419.surya.associates@mail.com', '$2y$10$.ZverH4lOlHk2ZCpd7NRZeN.kJLPjzhkMmL5qgBu9EfoLhuACVWGq', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(56, 55, 2, '19871119_saraswati_traders', '19871119.saraswati.traders@info.com', '$2y$10$ZNXs3GnDN9oaWl83GsefrOlTJUf6L9g5T/f1/NNA2WX6gfCqblDvW', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(57, 1, 3, 'nitin_desai_19991203', 'nitin.desai.19991203@mail.com', '$2y$10$C0qh0yR.JMqZ5Tcz5u9ik.mwQAeQdsh.HH0XgS9Xma/VMPV5SJMZW', 'active', NULL, NULL, '2026-03-06 16:41:59'),
(58, 1, 3, '19940111_deepak_siddiqui', '19940111.deepak.siddiqui@info.com', '$2y$10$nzc8M.bfF3yWdbUs40SKuOt5SuB5Bxn4uogOXWZEhLhME9pd411wu', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(59, 1, 3, 'jatin_kapoor_19960110', 'jatin.kapoor.19960110@mail.com', '$2y$10$V/jdWlsxn4a9bGQ1FcpCcuAx7oYxEVmhiaMcMtonQaRP1I8615l6K', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(60, 1, 6, '19810211_vihaan_chopra', '19810211.vihaan.chopra@mail.com', '$2y$10$PT20e/5HXsaXqEo1mf/34ubgDvgu9YgiPLes2ngZDjHsFnj4s2rIu', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(61, 1, 6, 'swati_pathan_19890312', 'swati.pathan.19890312@info.com', '$2y$10$wmvHo0OAkX.ELMhV3nAvIey05pIMCUWyrLNJxlpsWxZgqcpITGS0m', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(62, 1, 4, '19950213_preeti_more', '19950213.preeti.more@mail.com', '$2y$10$8MsfDAazTxcUnrqbtuAGQuv6nAVK08YHOKljG1MoYx13iQPHW6S9C', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(63, 1, 6, 'aditya_gupta_20000512', 'aditya.gupta.20000512@info.com', '$2y$10$1dXQsKnS9JWbQOARRjEOIuk9PXDzsK.x/ljKHMIQ8Uk2I6l9m.w7K', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(64, 1, 6, '19850112_rajesh_das', '19850112.rajesh.das@info.com', '$2y$10$VKnlSne9F0/Xm8ACMAxvnu5grdqCs3vnQ9opOEqCOMQlz/yV7jyvy', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(65, 1, 3, '19891107_priya_tiwari', '19891107.priya.tiwari@info.com', '$2y$10$7GTvxyrLZeYfEd36VxeieeWLM0t..WgblWDUN4gXSugT.ZBiGY.BC', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(66, 1, 4, 'amit_siddiqui_19860506', 'amit.siddiqui.19860506@mail.com', '$2y$10$1p72N5t1BfQWGqAo.NQ8V.YgsK0L7aSqdjRdVitiimuZN.X6mp896', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(67, 1, 4, '19921101_kavita_reddy', '19921101.kavita.reddy@info.com', '$2y$10$FcYyG.oFYGPSR/9H360r8eLYxazFBvfMgSraymZQOhp7gzyqJVDTu', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(68, 1, 4, 'aarav_yadav_19880925', 'aarav.yadav.19880925@info.com', '$2y$10$IoNdFpRAMvc8XBP02oGgdOgB6T0unxjzcJz31/M6AeNqAAUxt1u3m', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(69, 1, 4, 'suresh_saxena_19820325', 'suresh.saxena.19820325@info.com', '$2y$10$6xyA7GZ4z5EXNLq17GFf4eH8Kch8LCNVyD/LytS8Gazq5QeTYBOPS', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(70, 1, 4, '19990811_ramesh_nair', '19990811.ramesh.nair@mail.com', '$2y$10$yPDLa9ppXXH7WEqg.5pOa.n79DBWdPfGoGHrjGs5BuL5s2WwOlMJO', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(71, 1, 4, 'amit_tripathi_19820106', 'amit.tripathi.19820106@info.com', '$2y$10$B0UD4SiVrgeZNvFqVINdP./73UkPVE9TVawXnTTqbuOZHybKRXf3e', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(72, 1, 6, 'kapil_iyer_19860606', 'kapil.iyer.19860606@info.com', '$2y$10$aHiyaOXbLtdsb0G9U2M5H.co74XBWjOHaQbIDgUl84Q79PRtYKkXK', 'active', NULL, NULL, '2026-03-06 16:42:00'),
(73, 1, 4, '19960809_jyoti_ansari', '19960809.jyoti.ansari@info.com', '$2y$10$gJiXFWS1nKsOYyNuloDa4uhPl7LylLi8iL1nDlDY5itdLpwBNqVdK', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(74, 1, 4, '20000928_sneha_roy', '20000928.sneha.roy@mail.com', '$2y$10$hbsBfGHaUpTcp9JXd0yP5e09y8HwZ0XFfFny0NhIV6EpEeI167fGq', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(75, 1, 6, 'nitin_shinde_19900210', 'nitin.shinde.19900210@mail.com', '$2y$10$EL2rOtoJkCWJxR4Eu5AjxO/AZ76uNH0jr08ev2L3MEhYBcugtDuDq', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(76, 1, 4, 'nitin_kapoor_19970124', 'nitin.kapoor.19970124@info.com', '$2y$10$Yp45OI99U70WfHyLIncbQeZGLzZm.udXMnC.gZgxjV4njuIMzjdWe', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(77, 1, 4, '19920203_sana_kumar', '19920203.sana.kumar@info.com', '$2y$10$BT.68cceFPsYq4WIjPOTxOsNYFzbmCGOL4GxAQpD6QmyQrxzblW5K', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(78, 1, 4, 'vikram_roy_19830316', 'vikram.roy.19830316@mail.com', '$2y$10$TM0jCbr5mcffyA4mf.Z74eK5437yNuarFCX17lAVRs5n.yUQXB0rq', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(79, 1, 3, 'sunil_khan_19930502', 'sunil.khan.19930502@info.com', '$2y$10$GeLMy.ZhsHyv4LZsNbAzD.KwjnDVQkAlNW33CBTqTCSUhLFpfPDIq', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(80, 1, 4, '19820514_vivek_pathan', '19820514.vivek.pathan@info.com', '$2y$10$eZ4yDP7FOpLuJ2PCwM9JPedYLi1OJE4QVGGx5./YVc8fw0IH6hRzm', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(81, 1, 6, 'varun_pandey_19810713', 'varun.pandey.19810713@info.com', '$2y$10$qHnB.q4RndPJhOflc7foO.UeC2U2GNMhhGMyN4ReytDhPUvKPmt3C', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(82, 1, 4, 'riya_kumar_19850713', 'riya.kumar.19850713@info.com', '$2y$10$dc8AJyVof.HRfJbfkfjuaeX9FXYWIbOrTmZP8g6MmmkJ3sQMDHkMS', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(83, 1, 4, '19861227_aditya_mishra', '19861227.aditya.mishra@mail.com', '$2y$10$i6bjotBlY21Bf7eO2OUltuiOw1kKzRxHPnsrIWwxPWxkNxIKLQ0NC', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(84, 1, 4, '19920823_tarun_singh', '19920823.tarun.singh@mail.com', '$2y$10$zelXyeCkEJRSV1bFt2X9a.goNeAsiyi5ezlVbtbngICvNJnJ60dfq', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(85, 1, 4, '19961011_meera_bhatia', '19961011.meera.bhatia@info.com', '$2y$10$OtQWpUu5ikGxZKJ7MYlhUu5.IyN7gwiGd9XVaka/1TqZWfIw9SlL6', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(86, 1, 4, 'karan_more_19810904', 'karan.more.19810904@mail.com', '$2y$10$JTUcRQvtANuo9vstt/9DteBhSMkFfyU/qIBblNYGKiy1PPN7OSZ5O', 'active', NULL, NULL, '2026-03-06 16:42:01'),
(87, 1, 6, 'vijay_mishra_19881009', 'vijay.mishra.19881009@info.com', '$2y$10$LpJOgL.Udaik20gDCafcHuAJh1z3SMubeJvPkEbJYTkQqKdXNaZX6', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(88, 1, 6, '19990703_kapil_more', '19990703.kapil.more@mail.com', '$2y$10$eXk.fiKdHHODcD4VLNirEeJrBBOeh1ryTlqujMh2xfBtpfSkGOMLC', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(89, 1, 6, '19910812_neha_mehta', '19910812.neha.mehta@mail.com', '$2y$10$H/cauKH64QY8p.7ZSMqsc.Au4kO7gewPXvAJd1eEZFzCZAB/LlPie', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(90, 1, 6, '19990504_ishaan_nair', '19990504.ishaan.nair@info.com', '$2y$10$cYWeVQW3NAk/zxCJZ84Ar.lF1fY50J9R3BT2lY1F0plIvJ3hicbv.', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(91, 1, 3, '19941104_sana_patil', '19941104.sana.patil@info.com', '$2y$10$xab.XO1Ec1tV3K38L5frg.ipsBBADWiykevesAwGAGe6fJGybSrnK', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(92, 1, 6, 'vivek_chaudhary_19831215', 'vivek.chaudhary.19831215@mail.com', '$2y$10$ta6Ve5xZpsoE2/7GwYK6IO8/NUx7oD7dtwR.eiHoYLY2YPbUfnJzK', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(93, 1, 3, 'manoj_shah_19870621', 'manoj.shah.19870621@info.com', '$2y$10$Zd4mQUTDFpL6ohUwFG7BqOYTdRsuuAuPiO9n3HUC27WdG1TlaUZN6', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(94, 1, 4, 'diya_khan_19880327', 'diya.khan.19880327@mail.com', '$2y$10$KVjJ3grbtHIeinht3idcWOivIvcYMq3zWf8/RPPzRzR1wK90CBJA2', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(95, 1, 6, 'kapil_siddiqui_20000112', 'kapil.siddiqui.20000112@info.com', '$2y$10$Gzh32di7yy4WZ6g3s2TX6uWJm6zihescNA0TpMfdMk5senP/Jp7Da', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(96, 1, 6, 'anil_mishra_19890110', 'anil.mishra.19890110@info.com', '$2y$10$/u2X4/y0JkxpsnOtVJYa0uDgcv5xoZ57kzpzD.poaJiO5dRCShUYu', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(97, 1, 6, 'aditya_das_19950105', 'aditya.das.19950105@mail.com', '$2y$10$uUYUX0HDrC9BuYcQkE/M/ub6ieq0lSeMbPhS0m/O8Qku8Oq0NL4qi', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(98, 1, 6, '19890103_vihaan_das', '19890103.vihaan.das@info.com', '$2y$10$dtuIDPeRt1TS2H8/3B695OzHMPs3OzSPuM.018jurlR0ZepeCpZla', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(99, 1, 4, '19840628_ajay_iyer', '19840628.ajay.iyer@info.com', '$2y$10$35ADBfyXaeK7jzgH2kLyaug7Q5TAeSfu7Tk1AlAT2HYqPmgpXizNC', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(100, 1, 6, '19931121_amit_rao', '19931121.amit.rao@mail.com', '$2y$10$lVoi85f9pMDexYtSi6.uxOMGTVKFfEYi5sN5oASKu4NljOBpyqSLW', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(101, 1, 4, 'manish_joshi_19940828', 'manish.joshi.19940828@mail.com', '$2y$10$Fltm4DJ02kD5UHXaqWa94eho5xIIJQsd9aFyO35yeU1xRnN8CoR0O', 'active', NULL, NULL, '2026-03-06 16:42:02'),
(102, 1, 6, '19821016_suresh_kapoor', '19821016.suresh.kapoor@info.com', '$2y$10$/A4cjhSoitFFDsx16d87wex4k455.vJMG/g1t0N5ZPXYpU9PwU6c6', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(103, 1, 4, '19961221_gaurav_more', '19961221.gaurav.more@info.com', '$2y$10$2uw/ReMxQ/dfGGmfeW.UMeaAAGm19lNodP3afOsEVmKdrY7ZCoQUG', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(104, 1, 6, 'lalit_pandey_19851223', 'lalit.pandey.19851223@info.com', '$2y$10$wzJSl.hKF.6IbhpFJ6BTmeXZp219uN3FQQzADdxn2eZGE3nlS5W8S', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(105, 1, 6, '19821016_arjun_pathan', '19821016.arjun.pathan@info.com', '$2y$10$gvm0PJBJZZSr4xXis7B9XuOoCwA.StHp.1mVIOe60sCJ/j/zxbBAa', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(106, 1, 6, '20000603_manish_tripathi', '20000603.manish.tripathi@info.com', '$2y$10$jYGRG5rJyvkYoRSg0d4DGeKBnFcApUOZMXKR53fIQ6JJ2Wqhfpygy', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(107, 1, 4, 'riya_das_19910612', 'riya.das.19910612@info.com', '$2y$10$.CJZxSRvIjYIxaVhyBSrCuJWrdwDYv3tZq3ZR8sybL.4gB1rP65Sy', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(108, 1, 6, 'suresh_tiwari_19940809', 'suresh.tiwari.19940809@mail.com', '$2y$10$Mxh0qQp38TzwcgtMYR1Gr.zMLFspqQDDFyEkhnYKs13biLxIwhIbO', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(109, 1, 4, 'jatin_khan_19860804', 'jatin.khan.19860804@mail.com', '$2y$10$d3jik.zPqkwGiGtTvi9B3ujwq6GCfXIRSkuIzX3MBGR/2yuNCoNfi', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(110, 1, 4, 'deepak_das_20001220', 'deepak.das.20001220@mail.com', '$2y$10$TNrh4vcJC0xehBRBLFJLMO1ND4NX.nMYrmlV4zpbkzTcZ1oqpOe5a', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(111, 1, 4, '19910611_rahul_roy', '19910611.rahul.roy@info.com', '$2y$10$bhbPHFh0th.1i9NiK0GrmueK/vJX2JTN3mDH7D60WXMzZ9eywHkJW', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(112, 2, 3, 'suresh_malhotra_19940209', 'suresh.malhotra.19940209@mail.com', '$2y$10$eqo8O9rFWx2oLJoq4wegrezetW5O3000yRts8bs94jISHMYU6Y4GC', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(113, 2, 3, 'ashish_siddiqui_19960809', 'ashish.siddiqui.19960809@mail.com', '$2y$10$nTH7KkWUOfwmZm56foW/We4Jlk757yZZao/wQ6js3JEQXLTFkx.JC', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(114, 2, 3, 'kapil_chaudhary_19990710', 'kapil.chaudhary.19990710@mail.com', '$2y$10$Y1cxbjiR/Tu/DhtCsIHFBuRIdzxOV6b50oirejxQ0T75JxeQTvSaq', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(115, 2, 6, 'rohan_nair_19870404', 'rohan.nair.19870404@mail.com', '$2y$10$4rPWf0rYYGsKJr6YKr1qvOWUyIOtZnEqjiew4H0IIWMeG9q4TZ4g2', 'active', NULL, NULL, '2026-03-06 16:42:03'),
(116, 2, 6, 'vihaan_nair_19880603', 'vihaan.nair.19880603@info.com', '$2y$10$vquYlTFR541tMGA9MQab0.Mhu123oiy/Bt//4osPVgzwlsl1V7E6S', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(117, 2, 4, 'ramesh_shaikh_19890103', 'ramesh.shaikh.19890103@info.com', '$2y$10$cpnDEBDJVFrqvgbfgJScVep8XdRegyK2hI18bvnMl7fGOa/3CcauK', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(118, 2, 4, 'nitin_mishra_19840305', 'nitin.mishra.19840305@info.com', '$2y$10$Xku0/VhOF/X4hKo06nLcYeyoq3PkDg2TRmJXXW/cVM8sF6wIWmDqy', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(119, 2, 4, '19960104_varun_thakur', '19960104.varun.thakur@info.com', '$2y$10$Kh94k4dk3UlxCUJx0yMzpOSY6zVKoDYr4G9OVyxzG3FyRiYjZgTHy', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(120, 2, 6, '19930617_rajesh_rao', '19930617.rajesh.rao@info.com', '$2y$10$mfFItW7cspwXc.y30vyoXuuJKyov2nPc5CWarzhiH4aJH0MOITe2q', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(121, 2, 4, 'isha_ansari_19800506', 'isha.ansari.19800506@info.com', '$2y$10$1vbSgD4lHH6nUIHEyUVTmubiC0cJDOKfjsxGaZilqYt645DS.JJ8a', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(122, 2, 4, '19980114_ashish_yadav', '19980114.ashish.yadav@info.com', '$2y$10$2gNerHrEihAQOKgRdxmCTOiFCSJ9ByRw81L/SydKMVhkAArU0Dmpy', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(123, 2, 6, '19951024_jyoti_pandey', '19951024.jyoti.pandey@info.com', '$2y$10$KOY.AEFvRksL1K/e.iOaj.Gr7jkuN0HQ.sF3yf7o1YPKUxaBotqCC', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(124, 2, 4, '19820516_nitin_malhotra', '19820516.nitin.malhotra@mail.com', '$2y$10$BcYso5xUj3keYPS00jK2BeIqERClQAa/k/O5buPteJxSHop3n45Z.', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(125, 2, 4, '19930208_sneha_joshi', '19930208.sneha.joshi@mail.com', '$2y$10$cB5tNQzuPbeSxQfnMZtRN.mDWa1vWCR2EOmOGDx3C3f861Cd0evci', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(126, 2, 6, 'ananya_verma_19930615', 'ananya.verma.19930615@mail.com', '$2y$10$B0jAsDJfkfUaH2qcjd7FpOSxwLDskMtfPQvzUVEJ30K651s6fNdnq', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(127, 2, 4, '20000804_rohan_pathan', '20000804.rohan.pathan@mail.com', '$2y$10$PKA2GzkXv8AB4aZzTCBqXO8xtfJh94bqdan5pqmqIaPT3cZHDCBJ6', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(128, 2, 6, '19800101_rohan_saxena', '19800101.rohan.saxena@info.com', '$2y$10$seeu7kVMGNYrjTTFHVBzwu6E6L11gA.eS6nkLMcXqxPm4rPK9v/FS', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(129, 2, 6, '19841228_ajay_bhatia', '19841228.ajay.bhatia@mail.com', '$2y$10$QjzElQSWAxPo8pbShR6pvO8lZd7BS5QmvYFEUsdAXvSkKuTeiaIu6', 'active', NULL, NULL, '2026-03-06 16:42:04'),
(130, 2, 3, '19891012_jatin_thakur', '19891012.jatin.thakur@info.com', '$2y$10$35KPB.ntXkzwrA6a1.IREOafFGnvoFIiGbBVALIgxdeo4G0.k7rpS', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(131, 2, 4, 'ashish_desai_19940103', 'ashish.desai.19940103@info.com', '$2y$10$qt2x0OSvScfwsNrGgvrSsexJtiM7yS11Jes02GaY46l8O8PMMGtGa', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(132, 2, 6, '19870307_nisha_patil', '19870307.nisha.patil@info.com', '$2y$10$bDyKRfOK4fy5SOo2Xkq3xOcWYO7IUrgLA5d8DzA/TxLIuo8yxKUem', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(133, 2, 4, 'sai_gaikwad_19920603', 'sai.gaikwad.19920603@info.com', '$2y$10$j5.M8B3fthnSyZcfje.VOuQzz7OdCq76thPZ9yi7aIF9G.NpBDxLi', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(134, 2, 6, 'arjun_iyer_19861109', 'arjun.iyer.19861109@info.com', '$2y$10$rnb/vM.SfbWQqp4sRJOQeeivfEToxqpcMZ9XLWcLfz7apxjj8JZqu', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(135, 2, 4, 'sai_bhatia_19841028', 'sai.bhatia.19841028@mail.com', '$2y$10$8CjOOXTPqzwlwIHva8XTw.mIo5/kWsvIyp.cyMwZTTooVsyweNcMy', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(136, 2, 4, '19970601_jyoti_patel', '19970601.jyoti.patel@info.com', '$2y$10$/aT9xZ3qeb3rPj.RfgolN.vVJIYxAvoeR.6q3qEVjZ2n8E7/BYdjq', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(137, 2, 4, 'aarav_roy_19841116', 'aarav.roy.19841116@info.com', '$2y$10$ryRI/FxP5vUgGvVJTOSHCu04Iwpsc975aJnmpUbBKFaPebBCThKoK', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(138, 2, 4, 'swati_rao_19930916', 'swati.rao.19930916@info.com', '$2y$10$q9WlJ0xAUShmjEYwyXNhpOYySCsTg6cXRPM2WKiw0mHlLmA6xX/AK', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(139, 2, 3, 'arjun_pawar_19950701', 'arjun.pawar.19950701@mail.com', '$2y$10$.8dHor8U5si/Sg85AaGrkeOtfjVqvqsiy4FYvJ2uuPSxJN1gDWxIe', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(140, 2, 4, 'pooja_tiwari_19871103', 'pooja.tiwari.19871103@info.com', '$2y$10$YsebmiwjuyOd16PSFuUo8.5lEJKFj2.e.lboucJ1M8qWWSiWDkEJi', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(141, 2, 6, '19900817_rajesh_verma', '19900817.rajesh.verma@info.com', '$2y$10$Tso9yZLiuBlxGuMm1S..VeLjdsmjoyigjTl.kyXkztzBCyoNQL9Wa', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(142, 2, 4, 'riya_chopra_19880608', 'riya.chopra.19880608@mail.com', '$2y$10$qNbqcZaSk9Se2tmKoK.tXOAN4bIlSSOBQlHh0W4I7cC351j6Z7ibK', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(143, 2, 4, '19851210_meera_rao', '19851210.meera.rao@info.com', '$2y$10$NSgShyEUtT/CW2uglbqB8uOHa5Nmt.cwLZH37C8vDB4BHTCwc/f96', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(144, 2, 6, '19830701_sanjay_roy', '19830701.sanjay.roy@mail.com', '$2y$10$UMehgVqaovA06AXkxlQO3Oe/qmPfUVnchQQ9pmvoQkjC1nT2zjjoC', 'active', NULL, NULL, '2026-03-06 16:42:05'),
(145, 2, 6, '19950412_pooja_more', '19950412.pooja.more@info.com', '$2y$10$vjx2zWiCTfW5nJg2y3hQ8u35UDPN8XUQok.H8fej4ErVPokrvoL26', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(146, 2, 4, 'aditya_shah_19921104', 'aditya.shah.19921104@info.com', '$2y$10$lTHysLzXgcuIMloMVAcnGe0VJy2JU92.RpM0xDNfMvBPq8e7bkl3W', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(147, 2, 6, '19840420_sai_chaudhary', '19840420.sai.chaudhary@info.com', '$2y$10$agRKzBz4jQ.UvvPUoaksZ.MBdonoI42uxmaByJ7SHn8h5bgTOPOFC', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(148, 2, 4, 'rohan_pandey_19850813', 'rohan.pandey.19850813@info.com', '$2y$10$jtHCKCGCNDzkkW9gFmLXJeHyVfW0F.EtPwnjNGxo1r7uvTTemHklW', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(149, 2, 4, 'isha_shah_19920828', 'isha.shah.19920828@info.com', '$2y$10$iBhu6QMhMyvGBsUVHlGEG.nKA2/tQJ4Z25ndS7yuy7gB4dWWisHIK', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(150, 2, 4, 'varun_nair_19970418', 'varun.nair.19970418@info.com', '$2y$10$UKKKSfN.7gXAW2rAJfVwU.HlheTMaRES4xpvLC.2s0edUjZT32H6C', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(151, 2, 4, 'diya_rao_19940403', 'diya.rao.19940403@mail.com', '$2y$10$S5hUk8DmsIRyG1GmOCMPHu7KnClBrxtp5FzOJ5IMZsnaVU4qWuvOS', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(152, 2, 6, 'nitin_ansari_19940116', 'nitin.ansari.19940116@mail.com', '$2y$10$D.kfssNIu.ZcZBZSYOnnYeQ5HMQT5PGd2uQXLMS7q5xEX2STNMAGW', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(153, 2, 6, '19831220_neha_khan', '19831220.neha.khan@mail.com', '$2y$10$Q15iZHBp5hIKk9.45gjvZ.1UsuJjgONOggxR6L.zWWe/U94YGWOie', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(154, 2, 3, 'sana_thakur_19850223', 'sana.thakur.19850223@mail.com', '$2y$10$wsbvhe26s33wGupAJi7CsePT8FmMuKw8Epe2w5sk/c7t8gQsNbUNC', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(155, 2, 4, '20000502_deepak_pathan', '20000502.deepak.pathan@mail.com', '$2y$10$q1rNVPb3HYnr3dK76hMsvOUza.NlJQLB1Z7N2vPB4iqAoEZ38vl7i', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(156, 2, 3, '19970225_pooja_desai', '19970225.pooja.desai@info.com', '$2y$10$mhdM50.OfmIvKH2UAOhFseQsBNhq9c7fjM8iPN9VgLh8rvrGJESYO', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(157, 2, 4, '19830821_ananya_agarwal', '19830821.ananya.agarwal@mail.com', '$2y$10$YfriG7VKQogneOVEnPLoMOGRCeYreWtJ/RVme06apBzY.53HDLO3K', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(158, 2, 6, 'riya_tripathi_19920320', 'riya.tripathi.19920320@mail.com', '$2y$10$6tKMCNbMHGrGiu1unrhmC.jrjCzLebWxfM00IoBrW511inkjYg9qW', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(159, 2, 3, '19840209_ananya_thakur', '19840209.ananya.thakur@mail.com', '$2y$10$lcIRryPom.NAInanv7BalO5OczqtVNeSSbGDBzNBPx8Unvt/QUOgK', 'active', NULL, NULL, '2026-03-06 16:42:06'),
(160, 2, 4, 'jatin_dubey_19980813', 'jatin.dubey.19980813@mail.com', '$2y$10$a6SBSjTdDNeNQ.4uRI.7iu4Z3hYAMx4eI9/3O4UGcVG35ZD0ceddu', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(161, 2, 6, 'aditya_chopra_19861025', 'aditya.chopra.19861025@mail.com', '$2y$10$2eObVtG89XWcX/ELiJzge.KUU0HH9XoJjGd8GsZSLudJ6QlQYcMwe', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(162, 2, 6, 'swati_khan_19950612', 'swati.khan.19950612@mail.com', '$2y$10$YPMgUpjJl1P3PMvlH8NRDO1RH6ZtnFI3Yr9M372MXBJeLC.t0qioe', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(163, 2, 4, 'ajay_das_19941023', 'ajay.das.19941023@mail.com', '$2y$10$JZ/pZOMgbppDzF9EEtSa8uWnKE7AKTqPUFV2QJH1ZJ1Q6KU2aHcJG', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(164, 2, 4, '19890913_sunil_more', '19890913.sunil.more@info.com', '$2y$10$gaATob10DcbqlOl0T4VYn.Ubp9pFvsym25148HZGa0OuDsOqtmkXm', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(165, 2, 4, 'jatin_reddy_19931024', 'jatin.reddy.19931024@info.com', '$2y$10$UV9oqWzae1JTaojqBKVyDOOoS1/7dV2nc6SfV/ODlF6hcZ/aJNOQ6', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(166, 2, 6, 'alok_siddiqui_19960724', 'alok.siddiqui.19960724@info.com', '$2y$10$.aHqE3GTNN5OKn6M0/NQD.ovA5tRDBeNWBhcN6nJf1AJdI3zIwLHO', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(167, 3, 3, 'amit_agarwal_19900516', 'amit.agarwal.19900516@info.com', '$2y$10$BLV5cJAcQ8AG6pVFCSTbPOhJSUyPj9Hh9ApCALv1uPcNIaSUOQK6y', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(168, 3, 3, '19960107_rahul_shaikh', '19960107.rahul.shaikh@mail.com', '$2y$10$Pu2dnASFkfQggIjxQSoDUOpFmyPr5.adVXbpuY4ZsQZqtfCG/YWim', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(169, 3, 3, '19860121_isha_shaikh', '19860121.isha.shaikh@info.com', '$2y$10$2b9Gka0EPpUvGZ6Q/LKkKODB69TUmFqKB4UhHQU5h0GMhXkA0lUXO', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(170, 3, 6, '19811123_deepak_desai', '19811123.deepak.desai@mail.com', '$2y$10$TpAH07I1QUIegyIsij.bqeKjx1rLucMvYMPA1l.nwgXTA3WgOvSdW', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(171, 3, 6, '19930823_sanjay_yadav', '19930823.sanjay.yadav@mail.com', '$2y$10$GAaRcMDFN29gzQUIE0sSdO0U8GyJhcAy6KQGuCP42xbKpDY5gKKPK', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(172, 3, 4, 'jyoti_mishra_19970309', 'jyoti.mishra.19970309@mail.com', '$2y$10$ewgBkDjIN2XC7.4LDhx9j.Ogy067KrfhXsn7f0FbLmv.jKG0BdxZ.', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(173, 3, 4, 'jyoti_shaikh_19980526', 'jyoti.shaikh.19980526@mail.com', '$2y$10$4ON8yQKjGKpVFlQC0si9u.2Rx6F6BHnP2KIhI71PpiR93fz55twxG', 'active', NULL, NULL, '2026-03-06 16:42:07'),
(174, 3, 4, '19960512_riya_rao', '19960512.riya.rao@info.com', '$2y$10$A89wmuRANDD0tjJBX.JQZeUcc3fOVJ13EQ16DelwOevrlEpRjvN5G', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(175, 3, 4, '19891115_priya_sharma', '19891115.priya.sharma@info.com', '$2y$10$0n2BySlOy0KhE7MOeEyBC.DaF.d2HCwAtlj4qYCajM3CGBto4/Jbi', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(176, 3, 3, 'kapil_nair_19860817', 'kapil.nair.19860817@info.com', '$2y$10$Tv4sLOTheBYYGPARQJ7MxOgj/8/uUOtDMdwAqhaIi1VMmPtacBhJK', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(177, 3, 4, '19870409_alok_khan', '19870409.alok.khan@mail.com', '$2y$10$L9puLHUGZt.jkDzMC932vOPZ9DdisLKdyYgwRNp5k.jvqPVB9gIxW', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(178, 3, 4, 'alok_patel_19921016', 'alok.patel.19921016@mail.com', '$2y$10$T281Pq8y55fko1C0h4o1jOlMnv4cEHHpicvl6qxXCwmRuQnC.abX.', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(179, 3, 4, 'kapil_chaudhary_19800401', 'kapil.chaudhary.19800401@info.com', '$2y$10$ip2rK4FhnCCxcbFxbJn4FONBJOMSwFgQCwQvhsLIg1EI0ljOMi4wS', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(180, 3, 4, 'vijay_mehta_19831019', 'vijay.mehta.19831019@info.com', '$2y$10$mPMhnubaAa3kPlmZpBD0jOgZsZTmiGnFUFBgA/CXVAh7lRGhRDRH6', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(181, 3, 6, 'sanjay_roy_19830415', 'sanjay.roy.19830415@info.com', '$2y$10$siI4ho.akPdNz9mHgxG7Y.SqN24u0cd1NFfHA6/4h0CXzRtGBi6ei', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(182, 3, 4, 'manish_pawar_19800218', 'manish.pawar.19800218@mail.com', '$2y$10$Zg58eD9DAX8aEVnI9r8UquTDgBT/mXYk9lYLsAGG4LamDGM7CEMqu', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(183, 3, 6, 'aditya_bhatia_19831216', 'aditya.bhatia.19831216@mail.com', '$2y$10$UbJD6ZqkF42MyqgQrohAQe/ASG2p3QSCpGmbhOeyCSxi2v/k9W0C2', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(184, 3, 4, 'sanjay_jain_19940724', 'sanjay.jain.19940724@info.com', '$2y$10$x/uDzvWHT3Pk0h4vSdOhQODl2ueJ3O.7yryOxV9B4PygkNBdgIkM6', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(185, 3, 4, '19901006_jyoti_kapoor', '19901006.jyoti.kapoor@info.com', '$2y$10$JD/0CGezftBL0HKaSlw7cewF8kGz.cB2Yc8eJFNajjc6zfrszT0wO', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(186, 3, 4, '19890705_amit_ansari', '19890705.amit.ansari@mail.com', '$2y$10$ixv4pZGgL/egqoO1Huse6OW5ReXq.rA5uNTrqoXrJ/2XucflxtzBq', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(187, 3, 6, 'simran_pawar_19930219', 'simran.pawar.19930219@mail.com', '$2y$10$As7DGv9ZbSLx0sPfibkFd.WsOrHBlDR95B/1IwmouD1CY09EQgAki', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(188, 3, 3, '19930712_neha_agarwal', '19930712.neha.agarwal@info.com', '$2y$10$12Gug6OgdcB0sapi7v3rpeRhOuykqsqcEVT9opN6z15bF4t4sIkGK', 'active', NULL, NULL, '2026-03-06 16:42:08'),
(189, 3, 3, '19801222_swati_saxena', '19801222.swati.saxena@mail.com', '$2y$10$BPtChOmYdGD8.RQRY91XyO5ZLHPtaDL0rNd/jamJE18stHL7d02Bm', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(190, 3, 4, '19920522_manish_saxena', '19920522.manish.saxena@mail.com', '$2y$10$ocRhyN8R7ZASKraUhe9q6OMW17V7OnwhwSLaXgRm8JKxOl7psm2UC', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(191, 3, 4, 'ananya_mehta_19900515', 'ananya.mehta.19900515@info.com', '$2y$10$YXdNudh1sQy3C0QasHWOm.2wcLtWtGZINDspkSArxBaP2kbbTZHI2', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(192, 3, 4, '19870624_ishaan_jha', '19870624.ishaan.jha@mail.com', '$2y$10$skTX6IP1VOhBtN/hVoNu5uissDeTlakXlHEd8rKa.QHWf2PwgeSN6', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(193, 3, 4, '19870111_ananya_shah', '19870111.ananya.shah@info.com', '$2y$10$NAU4SSxW2urAPxtmMZfuxOzbCToFKW0V2Zx76r5wav.CWw4FGlm3q', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(194, 3, 4, '19990828_ajay_kulkarni', '19990828.ajay.kulkarni@info.com', '$2y$10$TVdI3wZFDzVmOdcLnQm2OOxal/9a6fkj1vGvu6NDuEkurUMjHJAvq', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(195, 3, 4, 'manoj_patil_19810216', 'manoj.patil.19810216@mail.com', '$2y$10$4Yy5Lq6O4tPZDHQI9xJLduCeCGIZIW9BGBr598I0a.SCZc5EE3Bo2', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(196, 3, 4, 'rajesh_jha_19871113', 'rajesh.jha.19871113@info.com', '$2y$10$cwqwldLi76oZlre4dK/Vze9qGgsFXXchJCN5tF.AAW7W3o9vdPSp.', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(197, 3, 4, '19990921_aarav_pandey', '19990921.aarav.pandey@info.com', '$2y$10$scVxKLMXufi20lbEwHBWeuFjyFbKSlWQ67x1hPFCLXL1Wwzs98CaW', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(198, 3, 4, 'vihaan_roy_19990307', 'vihaan.roy.19990307@mail.com', '$2y$10$g545QrTo8xL4fA8WIYLa8O8PJcgeRo9E5ysCBhd.ozjO3xvaUFE3.', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(199, 3, 6, 'varun_kumar_19891226', 'varun.kumar.19891226@mail.com', '$2y$10$u2egztJbcOlaoMGG1qmuEOpGvOqfbodDr9BLzWI6fAi7BlmYu0X.a', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(200, 3, 6, '19950622_aditya_khan', '19950622.aditya.khan@mail.com', '$2y$10$kK9U4P3KO5vj/PvXACgAW.vT0DCoOY4hppiWEuaBkm6i1ojSBckT2', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(201, 3, 6, 'nitin_joshi_19820812', 'nitin.joshi.19820812@info.com', '$2y$10$gt2eMcTN5cEfFhAV0aE9J.eoftHUQQi1lkxuSkhkiAaJClCkwMkmi', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(202, 3, 4, '19820423_suresh_kapoor', '19820423.suresh.kapoor@mail.com', '$2y$10$DiFiLG3OfsnaNE5g4BZGcu54Y2JFafHPKi6LTiHUY1QPmCFEpz9MC', 'active', NULL, NULL, '2026-03-06 16:42:09'),
(203, 3, 4, 'ramesh_shah_19880526', 'ramesh.shah.19880526@info.com', '$2y$10$fUQUzjrv2Ab8B/5o5a/YUeI/7Ck5qsqHDU/h5W.D9k3/Iv2DOuQYO', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(204, 3, 6, '19870925_tarun_ansari', '19870925.tarun.ansari@info.com', '$2y$10$2npAKcfwMT5l4rmhdQOyrOpTuo37SqYiDe/8C8L7bmEdBcC2gHYn6', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(205, 3, 4, '19871121_sanjay_patel', '19871121.sanjay.patel@mail.com', '$2y$10$s3EFRxbFoljObLf5lt5zMOJ279e1RTNFnvHUKBM2VDPU9zG6vFBM.', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(206, 3, 6, 'karan_ansari_19930407', 'karan.ansari.19930407@info.com', '$2y$10$y7oUuj8lza4uzzhxSALyEuKc28PGEsRGycA31eEW2fv1NOSquhnLK', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(207, 3, 4, 'meera_sharma_19990821', 'meera.sharma.19990821@info.com', '$2y$10$pRm22TVSrYEdL/83zeQSVOxFM79wTb.o1dmDFo7S5XH9FeURtfh0C', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(208, 3, 4, 'manish_mishra_19820608', 'manish.mishra.19820608@mail.com', '$2y$10$gWzhMLL65h6ehCo4JyDjYuljNBTM4l3xxbb/S8VTOu2J9NMq6nb2C', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(209, 3, 3, 'karan_saxena_19831009', 'karan.saxena.19831009@mail.com', '$2y$10$ZBE2pq7tttc/q90zG1Nfn.enyGKsagHsxxpil6U/1EAtNKvV2v4Xu', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(210, 3, 4, '19810624_karan_shaikh', '19810624.karan.shaikh@mail.com', '$2y$10$EzDuAZFXuQUnXjjRIiQcy.XvkHD05ihmowKWbVOyrNYDldEUx4ARu', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(211, 3, 4, '19801222_tarun_tiwari', '19801222.tarun.tiwari@info.com', '$2y$10$/ycQ5c5CySfzo3Fx8c/XX./pzjMhRZeKe86Afgpb0B/EqCX28qU46', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(212, 3, 4, 'karan_chaudhary_19830711', 'karan.chaudhary.19830711@info.com', '$2y$10$Ln.Ojfx.z5G/ZilJAn5NXOmX7KU3VIqKJxZisnHpxITdqYbKMNQja', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(213, 3, 4, 'amit_verma_19991128', 'amit.verma.19991128@mail.com', '$2y$10$xrIej6.GxAlhPeLJDQWBiO8fn3./MFJpbuOPO4Yxrn4k8ElHs4z/W', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(214, 3, 6, '19861115_gaurav_shaikh', '19861115.gaurav.shaikh@mail.com', '$2y$10$OY8tubiCZAfh/zAL9FZrAOmX.SO3G/lnZbdtNev8OFPARl71CGEbS', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(215, 3, 6, '19920224_vivek_yadav', '19920224.vivek.yadav@mail.com', '$2y$10$H9TZXcY41HA6VaYlTpr7s.7fXL20YdAqYLRk.JUSiZ.M8lbPrmgAK', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(216, 3, 3, '19940408_nitin_mishra', '19940408.nitin.mishra@mail.com', '$2y$10$v3EOTxpZjcJQeHZBcTf8..4YyKqvLta8bnfdnk2MH/h1XDtfEXLpa', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(217, 3, 3, '19870422_preeti_tiwari', '19870422.preeti.tiwari@mail.com', '$2y$10$Dw4xAaLQGJ9pZXqh1iut..0lsVOknvSTXMMbf.zO2kiVYIW6djJ2.', 'active', NULL, NULL, '2026-03-06 16:42:10'),
(218, 3, 4, '19860212_aarav_shaikh', '19860212.aarav.shaikh@info.com', '$2y$10$YP2/XZ/VAiW5PY82be57he.QyLx92RDAYOLY5AhJWiLAy1scmZBoC', 'active', NULL, NULL, '2026-03-06 16:42:11'),
(219, 3, 6, 'kavita_mishra_19950424', 'kavita.mishra.19950424@info.com', '$2y$10$9gweRVXrd45B4psj6Wn1e.Yx6Sr6qCdqEl/thwRBs10ripiYMRZHW', 'active', NULL, NULL, '2026-03-06 16:42:11'),
(220, 3, 6, 'riya_agarwal_19950715', 'riya.agarwal.19950715@mail.com', '$2y$10$AWbRwYZVQeB8uRqjKRyOXuz8vrkIsaXrYBkwvWxmIXvaSFsOlM3mS', 'active', NULL, NULL, '2026-03-06 16:42:11'),
(221, 3, 6, '19830410_neha_verma', '19830410.neha.verma@info.com', '$2y$10$YAKjOt5TetaqJ9M.kxDu4OvonopWoFlJ58b0ryn8axBmUqT3oQGcO', 'active', NULL, NULL, '2026-03-06 16:42:11');

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
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `asset_categories`
--
ALTER TABLE `asset_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category_per_company` (`company_id`,`name`),
  ADD KEY `company_id` (`company_id`);

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
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created_at` (`created_at`);

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
-- Indexes for table `employee_credentials`
--
ALTER TABLE `employee_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_credential` (`type`,`identifier_value`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_credential_lookup` (`type`,`identifier_value`);

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
-- Indexes for table `iot_devices`
--
ALTER TABLE `iot_devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_token` (`device_token`),
  ADD KEY `company_id` (`company_id`),
  ADD KEY `idx_device_token` (`device_token`,`status`);

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
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_reset_token_hash` (`reset_token_hash`);

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
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=83;

--
-- AUTO_INCREMENT for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `asset_categories`
--
ALTER TABLE `asset_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1834;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1720;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT for table `company_holiday_settings`
--
ALTER TABLE `company_holiday_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `email_templates`
--
ALTER TABLE `email_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=221;

--
-- AUTO_INCREMENT for table `employee_credentials`
--
ALTER TABLE `employee_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `global_holidays`
--
ALTER TABLE `global_holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `interviews`
--
ALTER TABLE `interviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `iot_devices`
--
ALTER TABLE `iot_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `job_applications`
--
ALTER TABLE `job_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- AUTO_INCREMENT for table `leave_balances`
--
ALTER TABLE `leave_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=225;

--
-- AUTO_INCREMENT for table `leave_policies`
--
ALTER TABLE `leave_policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `payslip_templates`
--
ALTER TABLE `payslip_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `performance`
--
ALTER TABLE `performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `support_tickets`
--
ALTER TABLE `support_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `team_members`
--
ALTER TABLE `team_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `team_performance`
--
ALTER TABLE `team_performance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `todo_list`
--
ALTER TABLE `todo_list`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=222;

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
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `fk_asset_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`);

--
-- Constraints for table `asset_assignments`
--
ALTER TABLE `asset_assignments`
  ADD CONSTRAINT `fk_assignment_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_assignment_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `employee_credentials`
--
ALTER TABLE `employee_credentials`
  ADD CONSTRAINT `employee_credentials_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `iot_devices`
--
ALTER TABLE `iot_devices`
  ADD CONSTRAINT `iot_devices_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

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
