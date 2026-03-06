-- ═══════════════════════════════════════════════════════════════
--  IoT Card Scanning Migration
--  Adds remote card scanning support to iot_devices table
--  Run once: mysql -u root YOUR_DB < iot_card_scan_migration.sql
-- ═══════════════════════════════════════════════════════════════

-- Add columns for remote card scanning from web UI
ALTER TABLE `iot_devices`
ADD COLUMN IF NOT EXISTS `add_card_mode` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = device should enter add card mode',
ADD COLUMN IF NOT EXISTS `pending_card_uid` VARCHAR(50) DEFAULT NULL COMMENT 'Scanned card UID waiting for HR to assign',
ADD COLUMN IF NOT EXISTS `card_scan_requested_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'When HR requested the scan',
ADD COLUMN IF NOT EXISTS `card_scanned_at` TIMESTAMP NULL DEFAULT NULL COMMENT 'When the card was scanned on device';

-- Allow employee_credentials.employee_id to be NULL for unassigned cards
ALTER TABLE `employee_credentials` MODIFY COLUMN `employee_id` int(11) DEFAULT NULL;
