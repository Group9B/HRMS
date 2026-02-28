-- IoT Attendance System Migration
-- Run this SQL in phpMyAdmin or XAMPP MySQL shell
-- Date: 2026-02-22

-- 1. Create employee_credentials table for storing RFID/Fingerprint/FaceID
CREATE TABLE IF NOT EXISTS `employee_credentials` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `type` enum('rfid', 'fingerprint', 'face_id') NOT NULL,
  `identifier_value` varchar(255) NOT NULL COMMENT 'The RFID UID or Fingerprint ID',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_credential` (`type`, `identifier_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Create iot_devices table for registered ESP32 devices
CREATE TABLE IF NOT EXISTS `iot_devices` (
  `id` int(11) PRIMARY KEY AUTO_INCREMENT,
  `company_id` int(11) NOT NULL COMMENT 'Device belongs to this company',
  `device_name` varchar(100) NOT NULL,
  `device_token` varchar(255) UNIQUE NOT NULL COMMENT 'Secret key for API Bearer Auth',
  `location` varchar(100) DEFAULT NULL,
  `status` enum('active', 'inactive') DEFAULT 'active',
  `last_heartbeat` timestamp NULL DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`company_id`) REFERENCES `companies`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Add device_id and auth_method columns to attendance table
-- Check if columns exist before adding (run these one by one if needed)
ALTER TABLE `attendance`
ADD COLUMN IF NOT EXISTS `device_id` int(11) NULL AFTER `employee_id`,
ADD COLUMN IF NOT EXISTS `auth_method` enum('rfid', 'fingerprint', 'face_id', 'manual') NULL AFTER `status`;

-- 4. Add foreign key constraint for device_id (only if not exists)
-- Note: Run this separately and skip if constraint already exists
-- ALTER TABLE `attendance`
-- ADD CONSTRAINT `fk_attendance_device` FOREIGN KEY (`device_id`) REFERENCES `iot_devices`(`id`) ON DELETE SET NULL;

-- 5. Create index for faster credential lookups
CREATE INDEX IF NOT EXISTS `idx_credential_lookup` ON `employee_credentials` (`type`, `identifier_value`);

-- 6. Create index for device token lookups
CREATE INDEX IF NOT EXISTS `idx_device_token` ON `iot_devices` (`device_token`, `status`);

-- Sample test data (uncomment to use)
-- INSERT INTO `iot_devices` (`company_id`, `device_name`, `device_token`, `location`, `status`) VALUES
-- (1, 'Main Entrance Scanner', 'test_token_abc123xyz789', 'Main Gate', 'active');
