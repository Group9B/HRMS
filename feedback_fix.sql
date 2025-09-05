-- Fix for feedback table to support employee feedback system
-- Run this SQL in your MySQL client or phpMyAdmin

-- Add missing columns to feedback table
ALTER TABLE `feedback` 
ADD COLUMN `type` ENUM('feedback','suggestion','complaint','appreciation') DEFAULT 'feedback' AFTER `message`,
ADD COLUMN `status` ENUM('pending','reviewed','resolved') DEFAULT 'pending' AFTER `type`;

-- Add indexes for better performance
ALTER TABLE `feedback` 
ADD INDEX `idx_employee_id` (`employee_id`),
ADD INDEX `idx_submitted_by` (`submitted_by`),
ADD INDEX `idx_type` (`type`),
ADD INDEX `idx_status` (`status`);

-- Insert sample data for testing (optional)
-- INSERT INTO `feedback` (`employee_id`, `submitted_by`, `message`, `type`, `status`, `created_at`) VALUES
-- (3, 3, 'Great work environment!', 'appreciation', 'reviewed', NOW()),\-- (3, 3, 'Need better coffee machine', 'suggestion', 'pending', NOW());