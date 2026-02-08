-- Asset Management System Migration
-- Run this SQL to create the necessary tables for the asset management module.

-- Table to store asset categories
CREATE TABLE IF NOT EXISTS `asset_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('Hardware', 'Software', 'Access', 'Security', 'Other') NOT NULL DEFAULT 'Other',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  UNIQUE KEY `unique_category_per_company` (`company_id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to store company assets
CREATE TABLE IF NOT EXISTS `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `asset_name` varchar(255) NOT NULL,
  `asset_tag` varchar(100) DEFAULT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_cost` decimal(12,2) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `status` enum('Available', 'Assigned', 'Maintenance', 'Retired', 'Lost') NOT NULL DEFAULT 'Available',
  `condition_status` enum('New', 'Good', 'Fair', 'Poor', 'Damaged') NOT NULL DEFAULT 'New',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `fk_asset_category` FOREIGN KEY (`category_id`) REFERENCES `asset_categories` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table to track asset assignments to employees
CREATE TABLE IF NOT EXISTS `asset_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `assigned_by` int(11) NOT NULL,
  `assigned_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('Active', 'Returned') NOT NULL DEFAULT 'Active',
  `condition_on_assignment` enum('New', 'Good', 'Fair', 'Poor', 'Damaged') DEFAULT 'Good',
  `condition_on_return` enum('New', 'Good', 'Fair', 'Poor', 'Damaged') DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `employee_id` (`employee_id`),
  KEY `assigned_by` (`assigned_by`),
  CONSTRAINT `fk_assignment_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_assignment_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed default categories (replace COMPANY_ID with actual company IDs after running)
-- You can run this INSERT for each company, or run via the setup script below.