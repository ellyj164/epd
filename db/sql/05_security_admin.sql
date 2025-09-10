-- E-Commerce Platform - Security, Admin and System Tables Schema
-- MariaDB migration for security logs, sessions, and administrative features

-- User Sessions table - Track active user sessions
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_id` int(11) NOT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_mobile` tinyint(1) NOT NULL DEFAULT 0,
    `is_bot` tinyint(1) NOT NULL DEFAULT 0,
    `country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    `expires_at` timestamp NOT NULL,
    `data` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_last_activity` (`last_activity`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Security Logs table - Track security events and suspicious activities
CREATE TABLE IF NOT EXISTS `security_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `event_type` enum('login_success','login_failed','login_blocked','logout','password_change','email_change','two_fa_enabled','two_fa_disabled','account_locked','account_unlocked','suspicious_activity','access_denied','data_breach','privilege_escalation') COLLATE utf8mb4_unicode_ci NOT NULL,
    `severity` enum('low','medium','high','critical') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `resource_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `resource_id` int(11) DEFAULT NULL,
    `details` json DEFAULT NULL,
    `risk_score` tinyint(3) unsigned DEFAULT NULL,
    `country` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_resolved` tinyint(1) NOT NULL DEFAULT 0,
    `resolved_by` int(11) DEFAULT NULL,
    `resolved_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_event_type` (`event_type`),
    KEY `idx_severity` (`severity`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_risk_score` (`risk_score`),
    KEY `idx_is_resolved` (`is_resolved`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_security_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_security_logs_resolver` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Login Attempts table - Track login attempts for rate limiting
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `identifier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
    `success` tinyint(1) NOT NULL DEFAULT 0,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `attempted_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_identifier` (`identifier`),
    KEY `idx_ip_address` (`ip_address`),
    KEY `idx_success` (`success`),
    KEY `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Tokens table - Verification and reset tokens
CREATE TABLE IF NOT EXISTS `email_tokens` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `type` enum('email_verification','password_reset','email_change','two_fa_backup') COLLATE utf8mb4_unicode_ci NOT NULL,
    `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `expires_at` timestamp NOT NULL,
    `used_at` timestamp NULL DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_token` (`token`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_expires_at` (`expires_at`),
    KEY `idx_used_at` (`used_at`),
    CONSTRAINT `fk_email_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Email Queue table - Queue system for email delivery
CREATE TABLE IF NOT EXISTS `email_queue` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `to_email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `to_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `subject` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `body` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
    `template` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `template_data` json DEFAULT NULL,
    `priority` tinyint(1) NOT NULL DEFAULT 3,
    `status` enum('pending','sending','sent','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
    `max_attempts` tinyint(3) unsigned NOT NULL DEFAULT 3,
    `error_message` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `scheduled_at` timestamp NULL DEFAULT NULL,
    `sent_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_status` (`status`),
    KEY `idx_priority` (`priority`),
    KEY `idx_scheduled_at` (`scheduled_at`),
    KEY `idx_attempts` (`attempts`),
    KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- System Settings table - Application configuration
CREATE TABLE IF NOT EXISTS `system_settings` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `setting_value` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `setting_type` enum('string','integer','decimal','boolean','json','text') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string',
    `category` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
    `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `is_encrypted` tinyint(1) NOT NULL DEFAULT 0,
    `updated_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_setting_key` (`setting_key`),
    KEY `idx_category` (`category`),
    KEY `idx_is_public` (`is_public`),
    KEY `idx_updated_by` (`updated_by`),
    CONSTRAINT `fk_system_settings_user` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin Activity Logs table - Track admin actions
CREATE TABLE IF NOT EXISTS `admin_activity_logs` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `admin_id` int(11) NOT NULL,
    `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `resource_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `resource_id` int(11) DEFAULT NULL,
    `old_values` json DEFAULT NULL,
    `new_values` json DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_admin_id` (`admin_id`),
    KEY `idx_action` (`action`),
    KEY `idx_resource_type` (`resource_type`),
    KEY `idx_resource_id` (`resource_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_admin_activity_logs_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- API Keys table - API access management
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `api_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
    `api_secret` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
    `permissions` json DEFAULT NULL,
    `rate_limit` int(11) NOT NULL DEFAULT 100,
    `rate_window` int(11) NOT NULL DEFAULT 3600,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `last_used_at` timestamp NULL DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_api_key` (`api_key`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_last_used_at` (`last_used_at`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_api_keys_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Database Migrations table - Track schema versions
CREATE TABLE IF NOT EXISTS `migrations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `batch` int(11) NOT NULL DEFAULT 1,
    `executed_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_filename` (`filename`),
    KEY `idx_batch` (`batch`),
    KEY `idx_executed_at` (`executed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- File Uploads table - Track uploaded files
CREATE TABLE IF NOT EXISTS `file_uploads` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `filename` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `file_path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `file_size` bigint(20) unsigned NOT NULL,
    `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `file_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `upload_type` enum('product_image','user_avatar','document','attachment','other') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other',
    `reference_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `reference_id` int(11) DEFAULT NULL,
    `is_public` tinyint(1) NOT NULL DEFAULT 0,
    `download_count` int(11) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_file_hash` (`file_hash`),
    KEY `idx_upload_type` (`upload_type`),
    KEY `idx_reference` (`reference_type`,`reference_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_file_uploads_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;