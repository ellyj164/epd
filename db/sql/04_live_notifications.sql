-- E-Commerce Platform - Live Shopping, Notifications and Communication Schema
-- MariaDB migration for live features, notifications, and messaging

-- Notifications table - User notification system
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` enum('order','promotion','wishlist','account','system','vendor','live_shopping','security') COLLATE utf8mb4_unicode_ci NOT NULL,
    `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `action_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `action_text` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `priority` enum('low','normal','high','urgent') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
    `read_at` timestamp NULL DEFAULT NULL,
    `data` json DEFAULT NULL,
    `expires_at` timestamp NULL DEFAULT NULL,
    `sent_via_email` tinyint(1) NOT NULL DEFAULT 0,
    `sent_via_push` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_read_at` (`read_at`),
    KEY `idx_priority` (`priority`),
    KEY `idx_expires_at` (`expires_at`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notification Preferences table - User notification settings
CREATE TABLE IF NOT EXISTS `notification_preferences` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` enum('order','promotion','wishlist','account','system','vendor','live_shopping','security') COLLATE utf8mb4_unicode_ci NOT NULL,
    `enabled` tinyint(1) NOT NULL DEFAULT 1,
    `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `push_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `sms_enabled` tinyint(1) NOT NULL DEFAULT 0,
    `frequency` enum('immediate','hourly','daily','weekly') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'immediate',
    `quiet_hours_start` time DEFAULT NULL,
    `quiet_hours_end` time DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_type` (`user_id`,`type`),
    CONSTRAINT `fk_notification_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Live Streams table - Live shopping sessions
CREATE TABLE IF NOT EXISTS `live_streams` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `vendor_id` int(11) NOT NULL,
    `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `thumbnail_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `stream_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `chat_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `status` enum('scheduled','live','ended','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'scheduled',
    `viewer_count` int(11) NOT NULL DEFAULT 0,
    `max_viewers` int(11) NOT NULL DEFAULT 0,
    `total_revenue` decimal(10,2) NOT NULL DEFAULT 0.00,
    `scheduled_at` timestamp NULL DEFAULT NULL,
    `started_at` timestamp NULL DEFAULT NULL,
    `ended_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_vendor_id` (`vendor_id`),
    KEY `idx_status` (`status`),
    KEY `idx_scheduled_at` (`scheduled_at`),
    KEY `idx_started_at` (`started_at`),
    KEY `idx_viewer_count` (`viewer_count`),
    CONSTRAINT `fk_live_streams_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Live Stream Products table - Products featured in live streams
CREATE TABLE IF NOT EXISTS `live_stream_products` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `stream_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `special_price` decimal(10,2) DEFAULT NULL,
    `discount_percentage` decimal(5,2) DEFAULT NULL,
    `limited_quantity` int(11) DEFAULT NULL,
    `sold_quantity` int(11) NOT NULL DEFAULT 0,
    `featured_order` int(11) NOT NULL DEFAULT 0,
    `featured_at` timestamp NULL DEFAULT NULL,
    `active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_stream_product` (`stream_id`,`product_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_featured_order` (`featured_order`),
    KEY `idx_active` (`active`),
    CONSTRAINT `fk_live_stream_products_stream` FOREIGN KEY (`stream_id`) REFERENCES `live_streams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_live_stream_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Live Chat Messages table - Real-time chat during live streams
CREATE TABLE IF NOT EXISTS `live_chat_messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `stream_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `message_type` enum('chat','system','product','reaction') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'chat',
    `is_highlighted` tinyint(1) NOT NULL DEFAULT 0,
    `is_moderated` tinyint(1) NOT NULL DEFAULT 0,
    `moderated_by` int(11) DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_stream_id` (`stream_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_message_type` (`message_type`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_is_moderated` (`is_moderated`),
    CONSTRAINT `fk_live_chat_messages_stream` FOREIGN KEY (`stream_id`) REFERENCES `live_streams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_live_chat_messages_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_live_chat_messages_moderator` FOREIGN KEY (`moderated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stream Viewers table - Track who is watching live streams
CREATE TABLE IF NOT EXISTS `stream_viewers` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `stream_id` int(11) NOT NULL,
    `user_id` int(11) DEFAULT NULL,
    `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `joined_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `left_at` timestamp NULL DEFAULT NULL,
    `watch_duration` int(11) DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    KEY `idx_stream_id` (`stream_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_joined_at` (`joined_at`),
    KEY `idx_is_active` (`is_active`),
    CONSTRAINT `fk_stream_viewers_stream` FOREIGN KEY (`stream_id`) REFERENCES `live_streams` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_stream_viewers_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Messages table - Private messaging between users
CREATE TABLE IF NOT EXISTS `messages` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `conversation_id` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
    `sender_id` int(11) NOT NULL,
    `recipient_id` int(11) NOT NULL,
    `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
    `message_type` enum('text','image','file','system') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'text',
    `attachment_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `attachment_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `read_at` timestamp NULL DEFAULT NULL,
    `is_system` tinyint(1) NOT NULL DEFAULT 0,
    `parent_message_id` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_conversation_id` (`conversation_id`),
    KEY `idx_sender_id` (`sender_id`),
    KEY `idx_recipient_id` (`recipient_id`),
    KEY `idx_read_at` (`read_at`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_parent_message_id` (`parent_message_id`),
    CONSTRAINT `fk_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_messages_parent` FOREIGN KEY (`parent_message_id`) REFERENCES `messages` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Push Subscriptions table - Web push notification subscriptions
CREATE TABLE IF NOT EXISTS `push_subscriptions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `endpoint` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `p256dh_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `auth_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `last_used` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_endpoint` (`endpoint`(255)),
    KEY `idx_is_active` (`is_active`),
    KEY `idx_last_used` (`last_used`),
    CONSTRAINT `fk_push_subscriptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;