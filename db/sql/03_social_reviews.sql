-- E-Commerce Platform - Social Features, Reviews and Wishlists Schema
-- MariaDB migration for user-generated content and social interactions

-- Product Reviews table
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `order_item_id` int(11) DEFAULT NULL,
    `rating` tinyint(1) NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `pros` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `cons` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `status` enum('pending','approved','rejected','hidden') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `helpful_count` int(11) NOT NULL DEFAULT 0,
    `unhelpful_count` int(11) NOT NULL DEFAULT 0,
    `verified_purchase` tinyint(1) NOT NULL DEFAULT 0,
    `images` json DEFAULT NULL,
    `admin_response` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `responded_at` timestamp NULL DEFAULT NULL,
    `responded_by` int(11) DEFAULT NULL,
    `approved_at` timestamp NULL DEFAULT NULL,
    `approved_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_order_item_id` (`order_item_id`),
    KEY `idx_status` (`status`),
    KEY `idx_rating` (`rating`),
    KEY `idx_verified_purchase` (`verified_purchase`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_reviews_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_reviews_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_responder` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_reviews_approver` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Review Helpfulness table - Track user votes on reviews
CREATE TABLE IF NOT EXISTS `review_helpfulness` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `review_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `is_helpful` tinyint(1) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_review_user` (`review_id`,`user_id`),
    KEY `idx_user_id` (`user_id`),
    CONSTRAINT `fk_review_helpfulness_review` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_review_helpfulness_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Wishlists table - User product wishlists
CREATE TABLE IF NOT EXISTS `wishlists` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `priority` tinyint(1) NOT NULL DEFAULT 3,
    `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `price_alert` tinyint(1) NOT NULL DEFAULT 0,
    `alert_price` decimal(10,2) DEFAULT NULL,
    `notify_on_restock` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_product` (`user_id`,`product_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_priority` (`priority`),
    KEY `idx_price_alert` (`price_alert`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_wishlists_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_wishlists_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- User Follows table - Users following other users/vendors
CREATE TABLE IF NOT EXISTS `user_follows` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `follower_id` int(11) NOT NULL,
    `following_id` int(11) NOT NULL,
    `type` enum('user','vendor') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
    `notifications_enabled` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_follower_following` (`follower_id`,`following_id`,`type`),
    KEY `idx_following_id` (`following_id`),
    KEY `idx_type` (`type`),
    CONSTRAINT `fk_user_follows_follower` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_user_follows_following` FOREIGN KEY (`following_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Views table - Track product view history
CREATE TABLE IF NOT EXISTS `product_views` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `product_id` int(11) NOT NULL,
    `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `user_agent` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `referrer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `view_duration` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_ip_address` (`ip_address`),
    CONSTRAINT `fk_product_views_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_product_views_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Search Queries table - Track search behavior for analytics
CREATE TABLE IF NOT EXISTS `search_queries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) DEFAULT NULL,
    `query` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
    `results_count` int(11) NOT NULL DEFAULT 0,
    `clicked_product_id` int(11) DEFAULT NULL,
    `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `filters_used` json DEFAULT NULL,
    `sort_order` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_query` (`query`),
    KEY `idx_clicked_product_id` (`clicked_product_id`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_search_queries_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_search_queries_product` FOREIGN KEY (`clicked_product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Recommendations table - AI/ML generated recommendations
CREATE TABLE IF NOT EXISTS `product_recommendations` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `recommended_product_id` int(11) NOT NULL,
    `type` enum('viewed_together','bought_together','similar','complementary','trending') COLLATE utf8mb4_unicode_ci NOT NULL,
    `score` decimal(5,4) NOT NULL DEFAULT 0.0000,
    `algorithm` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `context` json DEFAULT NULL,
    `clicked` tinyint(1) NOT NULL DEFAULT 0,
    `purchased` tinyint(1) NOT NULL DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `expires_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_product_recommended` (`user_id`,`product_id`,`recommended_product_id`,`type`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_recommended_product_id` (`recommended_product_id`),
    KEY `idx_type` (`type`),
    KEY `idx_score` (`score`),
    KEY `idx_expires_at` (`expires_at`),
    CONSTRAINT `fk_product_recommendations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_recommendations_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_product_recommendations_recommended` FOREIGN KEY (`recommended_product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;