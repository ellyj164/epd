-- E-Commerce Platform - Shopping Cart and Orders Schema
-- MariaDB migration for cart, orders, and transaction management

-- Shopping Cart table
CREATE TABLE IF NOT EXISTS `cart` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `quantity` int(11) NOT NULL DEFAULT 1,
    `price` decimal(10,2) NOT NULL,
    `options` json DEFAULT NULL,
    `session_id` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_user_product` (`user_id`,`product_id`),
    KEY `idx_session_id` (`session_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_cart_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_cart_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Orders table - Main order information
CREATE TABLE IF NOT EXISTS `orders` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` enum('pending','processing','shipped','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `payment_status` enum('pending','paid','failed','refunded','partial_refund') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `payment_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00,
    `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `shipping_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `total` decimal(10,2) NOT NULL DEFAULT 0.00,
    `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
    `billing_address` json NOT NULL,
    `shipping_address` json NOT NULL,
    `shipping_method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `admin_notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `shipped_at` timestamp NULL DEFAULT NULL,
    `delivered_at` timestamp NULL DEFAULT NULL,
    `cancelled_at` timestamp NULL DEFAULT NULL,
    `refunded_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_order_number` (`order_number`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_status` (`status`),
    KEY `idx_payment_status` (`payment_status`),
    KEY `idx_created_at` (`created_at`),
    KEY `idx_payment_transaction_id` (`payment_transaction_id`),
    CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Order Items table - Individual items in orders
CREATE TABLE IF NOT EXISTS `order_items` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) NOT NULL,
    `product_id` int(11) NOT NULL,
    `vendor_id` int(11) NOT NULL,
    `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `sku` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `qty` int(11) NOT NULL DEFAULT 1,
    `price` decimal(10,2) NOT NULL,
    `subtotal` decimal(10,2) NOT NULL,
    `tax_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `discount_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
    `options` json DEFAULT NULL,
    `status` enum('pending','processing','shipped','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `tracking_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `shipped_at` timestamp NULL DEFAULT NULL,
    `delivered_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_product_id` (`product_id`),
    KEY `idx_vendor_id` (`vendor_id`),
    KEY `idx_status` (`status`),
    CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
    CONSTRAINT `fk_order_items_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment Methods table - Stored payment methods
CREATE TABLE IF NOT EXISTS `payment_methods` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `type` enum('credit_card','debit_card','paypal','bank_transfer','wallet') COLLATE utf8mb4_unicode_ci NOT NULL,
    `provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `last_four` varchar(4) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `exp_month` tinyint(2) DEFAULT NULL,
    `exp_year` smallint(4) DEFAULT NULL,
    `cardholder_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `brand` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `is_default` tinyint(1) NOT NULL DEFAULT 0,
    `is_active` tinyint(1) NOT NULL DEFAULT 1,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_is_default` (`is_default`),
    KEY `idx_is_active` (`is_active`),
    CONSTRAINT `fk_payment_methods_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Transactions table - Payment transaction logs
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `order_id` int(11) DEFAULT NULL,
    `user_id` int(11) NOT NULL,
    `type` enum('payment','refund','partial_refund','chargeback','fee') COLLATE utf8mb4_unicode_ci NOT NULL,
    `status` enum('pending','completed','failed','cancelled') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
    `amount` decimal(10,2) NOT NULL,
    `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'USD',
    `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `gateway` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `gateway_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `reference_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `gateway_response` json DEFAULT NULL,
    `fees` decimal(10,2) NOT NULL DEFAULT 0.00,
    `notes` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `processed_at` timestamp NULL DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_type` (`type`),
    KEY `idx_status` (`status`),
    KEY `idx_gateway_transaction_id` (`gateway_transaction_id`),
    KEY `idx_created_at` (`created_at`),
    CONSTRAINT `fk_transactions_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
    CONSTRAINT `fk_transactions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupons table - Discount codes and promotions
CREATE TABLE IF NOT EXISTS `coupons` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
    `type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'fixed',
    `value` decimal(10,2) NOT NULL,
    `minimum_amount` decimal(10,2) DEFAULT NULL,
    `maximum_discount` decimal(10,2) DEFAULT NULL,
    `usage_limit` int(11) DEFAULT NULL,
    `usage_count` int(11) NOT NULL DEFAULT 0,
    `user_usage_limit` int(11) DEFAULT NULL,
    `status` enum('active','inactive','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
    `valid_from` timestamp NULL DEFAULT NULL,
    `valid_to` timestamp NULL DEFAULT NULL,
    `applies_to` enum('all','categories','products','users') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'all',
    `applicable_items` json DEFAULT NULL,
    `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_by` int(11) DEFAULT NULL,
    `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    PRIMARY KEY (`id`),
    UNIQUE KEY `idx_code` (`code`),
    KEY `idx_status` (`status`),
    KEY `idx_valid_from` (`valid_from`),
    KEY `idx_valid_to` (`valid_to`),
    KEY `idx_created_by` (`created_by`),
    CONSTRAINT `fk_coupons_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Coupon Usage table - Track coupon usage by users
CREATE TABLE IF NOT EXISTS `coupon_usage` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `coupon_id` int(11) NOT NULL,
    `user_id` int(11) NOT NULL,
    `order_id` int(11) NOT NULL,
    `discount_amount` decimal(10,2) NOT NULL,
    `used_at` timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `idx_coupon_id` (`coupon_id`),
    KEY `idx_user_id` (`user_id`),
    KEY `idx_order_id` (`order_id`),
    KEY `idx_used_at` (`used_at`),
    CONSTRAINT `fk_coupon_usage_coupon` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_coupon_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_coupon_usage_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;