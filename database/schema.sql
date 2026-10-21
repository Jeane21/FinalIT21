-- =============================================================================
-- Final21 - Security & File Integrity Monitoring System Database Schema
-- Target RDBMS: MySQL 5.7+ / MySQL 8.0+ / MariaDB 10.3+ (XAMPP Environment)
-- Engine: InnoDB | Character Set: utf8mb4 | Collation: utf8mb4_unicode_ci
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `final21_db`
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE `final21_db`;

-- Disable foreign key checks during creation to prevent ordering issues
SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. Table: users
-- Core authentication and user management table
-- Roles: 'Administrator', 'Security Analyst'
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(150) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('Administrator', 'Security Analyst') NOT NULL,
  `status` ENUM('active', 'inactive', 'suspended') NOT NULL DEFAULT 'active',
  `last_login` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_username` (`username`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role` (`role`),
  KEY `idx_users_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. Table: monitored_paths
-- Directories/folders registered for File Integrity Monitoring (FIM)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `monitored_paths`;
CREATE TABLE `monitored_paths` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path` VARCHAR(1000) NOT NULL,
  `recursive` TINYINT(1) NOT NULL DEFAULT 1,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `created_by` INT UNSIGNED NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_monitored_paths_active` (`is_active`),
  KEY `fk_monitored_paths_user` (`created_by`),
  CONSTRAINT `fk_monitored_paths_user` 
    FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Table: monitored_files
-- Specific files cataloged under monitored paths or tracked individually
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `monitored_files`;
CREATE TABLE `monitored_files` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `path_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `file_path` VARCHAR(1000) NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_size` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `current_hash` VARCHAR(128) NULL DEFAULT NULL,
  `status` ENUM('normal', 'modified', 'deleted', 'quarantined') NOT NULL DEFAULT 'normal',
  `last_scanned_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_monitored_files_status` (`status`),
  KEY `idx_monitored_files_filename` (`file_name`),
  KEY `fk_monitored_files_path` (`path_id`),
  CONSTRAINT `fk_monitored_files_path` 
    FOREIGN KEY (`path_id`) REFERENCES `monitored_paths` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Table: file_snapshots
-- Baseline and historical integrity snapshots (SHA-256 hash, size, permissions)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `file_snapshots`;
CREATE TABLE `file_snapshots` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` BIGINT UNSIGNED NOT NULL,
  `file_hash` VARCHAR(128) NOT NULL,
  `file_size` BIGINT UNSIGNED NOT NULL,
  `permissions` VARCHAR(50) NULL DEFAULT NULL,
  `snapshot_time` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_baseline` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_file_snapshots_hash` (`file_hash`),
  KEY `idx_file_snapshots_time` (`snapshot_time`),
  KEY `fk_file_snapshots_file` (`file_id`),
  CONSTRAINT `fk_file_snapshots_file` 
    FOREIGN KEY (`file_id`) REFERENCES `monitored_files` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Table: file_activity_logs
-- Real-time audit logs of file system mutations (creation, modification, deletion)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `file_activity_logs`;
CREATE TABLE `file_activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `file_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `event_type` ENUM('CREATE', 'MODIFY', 'DELETE', 'RENAME', 'PERMISSION_CHANGE') NOT NULL,
  `file_path` VARCHAR(1000) NOT NULL,
  `previous_hash` VARCHAR(128) NULL DEFAULT NULL,
  `new_hash` VARCHAR(128) NULL DEFAULT NULL,
  `detected_by` VARCHAR(100) NOT NULL DEFAULT 'FIM_Agent',
  `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'low',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_file_activity_event` (`event_type`),
  KEY `idx_file_activity_severity` (`severity`),
  KEY `idx_file_activity_timestamp` (`timestamp`),
  KEY `fk_file_activity_file` (`file_id`),
  CONSTRAINT `fk_file_activity_file` 
    FOREIGN KEY (`file_id`) REFERENCES `monitored_files` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. Table: detection_logs
-- Threat detections, heuristic anomalies, ransomware signatures, or policy breaches
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `detection_logs`;
CREATE TABLE `detection_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `rule_name` VARCHAR(150) NOT NULL,
  `detection_type` VARCHAR(100) NOT NULL,
  `target_path` VARCHAR(1000) NULL DEFAULT NULL,
  `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
  `confidence_score` DECIMAL(5,2) NULL DEFAULT NULL,
  `details` TEXT NULL DEFAULT NULL,
  `status` ENUM('unreviewed', 'investigating', 'resolved', 'false_positive') NOT NULL DEFAULT 'unreviewed',
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_detection_logs_severity` (`severity`),
  KEY `idx_detection_logs_status` (`status`),
  KEY `idx_detection_logs_type` (`detection_type`),
  KEY `idx_detection_logs_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. Table: alerts
-- Escalated actionable incidents requiring analyst review and triage
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `alerts`;
CREATE TABLE `alerts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `detection_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL,
  `status` ENUM('new', 'acknowledged', 'in_progress', 'resolved', 'dismissed') NOT NULL DEFAULT 'new',
  `assigned_to` INT UNSIGNED NULL DEFAULT NULL,
  `acknowledged_at` DATETIME NULL DEFAULT NULL,
  `resolved_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_alerts_status` (`status`),
  KEY `idx_alerts_severity` (`severity`),
  KEY `idx_alerts_created` (`created_at`),
  KEY `fk_alerts_detection` (`detection_id`),
  KEY `fk_alerts_assigned_user` (`assigned_to`),
  CONSTRAINT `fk_alerts_detection` 
    FOREIGN KEY (`detection_id`) REFERENCES `detection_logs` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_alerts_assigned_user` 
    FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. Table: notifications
-- In-app and user-targeted notifications for security alerts and operational events
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `notifications`;
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `alert_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'warning', 'danger', 'success') NOT NULL DEFAULT 'info',
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_user_read` (`user_id`, `is_read`),
  KEY `fk_notifications_alert` (`alert_id`),
  CONSTRAINT `fk_notifications_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_notifications_alert` 
    FOREIGN KEY (`alert_id`) REFERENCES `alerts` (`id`) 
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. Table: system_activity_logs
-- Audit trail of administrative and user actions within the web application
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `system_activity_logs`;
CREATE TABLE `system_activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `module` VARCHAR(100) NOT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `user_agent` VARCHAR(255) NULL DEFAULT NULL,
  `details` TEXT NULL DEFAULT NULL,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_system_activity_action` (`action`),
  KEY `idx_system_activity_module` (`module`),
  KEY `idx_system_activity_timestamp` (`timestamp`),
  KEY `fk_system_activity_user` (`user_id`),
  CONSTRAINT `fk_system_activity_user` 
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 10. Table: system_settings
-- System configuration parameters, scanning frequencies, thresholds, and modes
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `system_settings`;
CREATE TABLE `system_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL DEFAULT NULL,
  `category` VARCHAR(50) NOT NULL DEFAULT 'general',
  `description` VARCHAR(255) NULL DEFAULT NULL,
  `updated_by` INT UNSIGNED NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`),
  KEY `idx_settings_category` (`category`),
  KEY `fk_settings_updated_user` (`updated_by`),
  CONSTRAINT `fk_settings_updated_user` 
    FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 11. Table: reports
-- Generated security audit, compliance, and incident summary reports
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `reports`;
CREATE TABLE `reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `generated_by` INT UNSIGNED NULL DEFAULT NULL,
  `report_title` VARCHAR(200) NOT NULL,
  `report_type` ENUM('daily', 'weekly', 'incident', 'executive', 'audit') NOT NULL,
  `file_format` ENUM('pdf', 'csv', 'json', 'html') NOT NULL DEFAULT 'pdf',
  `file_path` VARCHAR(1000) NULL DEFAULT NULL,
  `summary` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reports_type` (`report_type`),
  KEY `idx_reports_created` (`created_at`),
  KEY `fk_reports_user` (`generated_by`),
  CONSTRAINT `fk_reports_user` 
    FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 12. Table: network_devices
-- Monitored endpoints, local network interfaces, host adapters, and targets
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `network_devices`;
CREATE TABLE `network_devices` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_name` VARCHAR(150) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `mac_address` VARCHAR(17) NULL DEFAULT NULL,
  `device_type` VARCHAR(50) NOT NULL DEFAULT 'Workstation',
  `status` ENUM('online', 'offline', 'suspicious', 'blocked') NOT NULL DEFAULT 'online',
  `last_seen` DATETIME NULL DEFAULT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_network_devices_ip` (`ip_address`),
  KEY `idx_network_devices_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 13. Table: packet_capture_logs
-- Network packet capture streams, protocol flows, and suspicious payload events
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `packet_capture_logs`;
CREATE TABLE `packet_capture_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `device_id` BIGINT UNSIGNED NULL DEFAULT NULL,
  `interface_name` VARCHAR(100) NULL DEFAULT NULL,
  `protocol` VARCHAR(20) NOT NULL,
  `source_ip` VARCHAR(45) NOT NULL,
  `source_port` INT UNSIGNED NULL DEFAULT NULL,
  `destination_ip` VARCHAR(45) NOT NULL,
  `destination_port` INT UNSIGNED NULL DEFAULT NULL,
  `packet_size` INT UNSIGNED NULL DEFAULT NULL,
  `flags` VARCHAR(50) NULL DEFAULT NULL,
  `payload_preview` TEXT NULL DEFAULT NULL,
  `is_suspicious` TINYINT(1) NOT NULL DEFAULT 0,
  `timestamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_packet_logs_protocol` (`protocol`),
  KEY `idx_packet_logs_src_ip` (`source_ip`),
  KEY `idx_packet_logs_dst_ip` (`destination_ip`),
  KEY `idx_packet_logs_suspicious` (`is_suspicious`),
  KEY `idx_packet_logs_timestamp` (`timestamp`),
  KEY `fk_packet_logs_device` (`device_id`),
  CONSTRAINT `fk_packet_logs_device` 
    FOREIGN KEY (`device_id`) REFERENCES `network_devices` (`id`) 
    ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- DEVELOPMENT SEED DATA (Single Administrator Account for Testing)
-- Password: Admin@12345
-- Hash: $2y$10$jFIzMJyUs4gXfAHnyl.99es0X6D60XpSvCHJ2opNmm3dH3PBuwwa. (bcrypt)
-- =============================================================================
INSERT INTO `users` (
  `full_name`,
  `username`,
  `email`,
  `password_hash`,
  `role`,
  `status`,
  `last_login`,
  `created_at`
) VALUES (
  'System Administrator',
  'admin',
  'admin@final21.local',
  '$2y$10$jFIzMJyUs4gXfAHnyl.99es0X6D60XpSvCHJ2opNmm3dH3PBuwwa.',
  'Administrator',
  'active',
  NULL,
  NOW()
) ON DUPLICATE KEY UPDATE `id`=`id`;

-- Default core system settings (operational baseline)
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `category`, `description`) VALUES
('fim_scan_interval_seconds', '60', 'fim', 'Interval in seconds between automated file integrity scans'),
('fim_default_hash_algorithm', 'SHA-256', 'fim', 'Cryptographic hashing algorithm used for file snapshots'),
('network_packet_inspection_enabled', '1', 'network', 'Enables real-time packet inspection and anomaly logging'),
('alert_critical_notification_email', 'admin@final21.local', 'notifications', 'Email recipient for critical security escalation alerts')
ON DUPLICATE KEY UPDATE `id`=`id`;
