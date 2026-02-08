ALTER TABLE `users` 
ADD COLUMN `reset_token_hash` VARCHAR(64) NULL DEFAULT NULL AFTER `status`,
ADD COLUMN `reset_token_expires_at` DATETIME NULL DEFAULT NULL AFTER `reset_token_hash`;

ALTER TABLE `users` ADD INDEX `idx_reset_token_hash` (`reset_token_hash`);
