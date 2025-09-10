-- E-Commerce Platform - Seed Data for Development and Testing
-- MariaDB seed data with sample records for all major tables

-- Insert default admin user (password: admin123)
INSERT INTO `users` (`id`, `username`, `email`, `pass_hash`, `first_name`, `last_name`, `role`, `status`, `verified_at`) VALUES 
(1, 'admin', 'admin@fezalogistics.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System', 'Administrator', 'admin', 'active', NOW()),
(2, 'johndoe', 'john@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John', 'Doe', 'customer', 'active', NOW()),
(3, 'janedoe', 'jane@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane', 'Doe', 'vendor', 'active', NOW()),
(4, 'testcustomer', 'customer@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'Customer', 'customer', 'active', NOW());

-- Insert product categories
INSERT INTO `categories` (`id`, `name`, `description`, `parent_id`, `slug`, `sort_order`, `status`) VALUES
(1, 'Electronics', 'Electronic devices and accessories', NULL, 'electronics', 1, 'active'),
(2, 'Clothing & Fashion', 'Apparel and fashion accessories', NULL, 'clothing-fashion', 2, 'active'),
(3, 'Home & Garden', 'Home improvement and garden supplies', NULL, 'home-garden', 3, 'active'),
(4, 'Books & Media', 'Books, movies, music and digital media', NULL, 'books-media', 4, 'active'),
(5, 'Sports & Outdoors', 'Sports equipment and outdoor gear', NULL, 'sports-outdoors', 5, 'active'),
(6, 'Smartphones', 'Mobile phones and accessories', 1, 'smartphones', 1, 'active'),
(7, 'Laptops', 'Laptop computers and accessories', 1, 'laptops', 2, 'active'),
(8, 'Audio & Video', 'Headphones, speakers, cameras', 1, 'audio-video', 3, 'active'),
(9, 'Men\'s Clothing', 'Men\'s apparel and accessories', 2, 'mens-clothing', 1, 'active'),
(10, 'Women\'s Clothing', 'Women\'s apparel and accessories', 2, 'womens-clothing', 2, 'active');

-- Insert sample vendor
INSERT INTO `vendors` (`id`, `user_id`, `business_name`, `business_type`, `business_email`, `description`, `status`, `approved_at`, `approved_by`) VALUES
(1, 3, 'TechGear Pro', 'business', 'contact@techgearpro.com', 'Leading provider of consumer electronics and tech accessories', 'approved', NOW(), 1);

-- Insert sample products
INSERT INTO `products` (`id`, `vendor_id`, `category_id`, `name`, `slug`, `description`, `short_description`, `sku`, `price`, `sale_price`, `stock_quantity`, `status`, `featured`, `tags`) VALUES
(1, 1, 6, 'iPhone 15 Pro Max', 'iphone-15-pro-max', 'The most advanced iPhone ever with titanium design, A17 Pro chip, and professional camera system.', 'Latest iPhone with titanium design and advanced camera', 'IP15PM-256GB', 1199.99, 1099.99, 50, 'active', 1, 'iPhone,Apple,smartphone,titanium,camera'),
(2, 1, 7, 'MacBook Pro 16-inch M3', 'macbook-pro-16-m3', 'Supercharged for pros. The most advanced chips. Exceptional battery life. The world\'s best laptop display.', 'Professional laptop with M3 chip and 16-inch display', 'MBP16-M3-512GB', 2499.99, NULL, 25, 'active', 1, 'MacBook,Apple,laptop,M3,professional'),
(3, 1, 8, 'AirPods Pro (3rd generation)', 'airpods-pro-3rd-gen', 'Adaptive Audio. Personalized Spatial Audio. Up to 2x more Active Noise Cancellation.', 'Premium wireless earbuds with adaptive audio', 'APP3-USB-C', 249.99, 199.99, 100, 'active', 1, 'AirPods,Apple,wireless,earbuds,noise-cancelling'),
(4, 1, 6, 'Samsung Galaxy S24 Ultra', 'samsung-galaxy-s24-ultra', 'The ultimate Galaxy experience with S Pen, 200MP camera, and Galaxy AI features.', 'Flagship Android phone with S Pen and AI features', 'SGS24U-256GB', 1299.99, 1199.99, 30, 'active', 1, 'Samsung,Galaxy,Android,S Pen,AI'),
(5, 1, 8, 'Sony WH-1000XM5 Headphones', 'sony-wh1000xm5', 'Industry-leading noise canceling with exceptional sound quality and comfortable design.', 'Premium noise-cancelling over-ear headphones', 'SONY-WH1000XM5', 399.99, 349.99, 75, 'active', 0, 'Sony,headphones,noise-cancelling,wireless'),
(6, 1, 7, 'Dell XPS 13 Plus', 'dell-xps-13-plus', 'Stunning 13.4-inch laptop with edge-to-edge display and premium performance.', 'Ultra-thin laptop with premium build quality', 'DELL-XPS13P-512GB', 1399.99, NULL, 20, 'active', 0, 'Dell,XPS,laptop,ultrabook,premium'),
(7, 1, 8, 'Canon EOS R6 Mark II', 'canon-eos-r6-mark-ii', 'Full-frame mirrorless camera with advanced autofocus and 4K video recording.', 'Professional mirrorless camera for photography and video', 'CANON-R6M2-BODY', 2499.99, 2299.99, 15, 'active', 1, 'Canon,camera,mirrorless,photography,4K'),
(8, 1, 6, 'Google Pixel 8 Pro', 'google-pixel-8-pro', 'The most helpful Pixel yet, with Google AI and advanced computational photography.', 'AI-powered Android phone with exceptional camera', 'PIXEL8P-256GB', 999.99, 899.99, 40, 'active', 0, 'Google,Pixel,Android,AI,camera');

-- Insert product images
INSERT INTO `product_images` (`product_id`, `image_url`, `alt_text`, `is_primary`, `sort_order`) VALUES
(1, '/uploads/products/iphone-15-pro-max-1.jpg', 'iPhone 15 Pro Max front view', 1, 1),
(1, '/uploads/products/iphone-15-pro-max-2.jpg', 'iPhone 15 Pro Max back view', 0, 2),
(2, '/uploads/products/macbook-pro-16-m3-1.jpg', 'MacBook Pro 16-inch M3 open view', 1, 1),
(2, '/uploads/products/macbook-pro-16-m3-2.jpg', 'MacBook Pro 16-inch M3 side view', 0, 2),
(3, '/uploads/products/airpods-pro-3-1.jpg', 'AirPods Pro 3rd generation with case', 1, 1),
(4, '/uploads/products/galaxy-s24-ultra-1.jpg', 'Samsung Galaxy S24 Ultra front view', 1, 1),
(5, '/uploads/products/sony-wh1000xm5-1.jpg', 'Sony WH-1000XM5 headphones', 1, 1),
(6, '/uploads/products/dell-xps-13-plus-1.jpg', 'Dell XPS 13 Plus laptop', 1, 1),
(7, '/uploads/products/canon-r6-mark-ii-1.jpg', 'Canon EOS R6 Mark II camera', 1, 1),
(8, '/uploads/products/pixel-8-pro-1.jpg', 'Google Pixel 8 Pro smartphone', 1, 1);

-- Insert sample addresses
INSERT INTO `addresses` (`user_id`, `type`, `first_name`, `last_name`, `address_line1`, `city`, `state`, `postal_code`, `country`, `is_default`) VALUES
(2, 'both', 'John', 'Doe', '123 Main Street', 'New York', 'NY', '10001', 'US', 1),
(4, 'both', 'Test', 'Customer', '456 Oak Avenue', 'Los Angeles', 'CA', '90210', 'US', 1);

-- Insert sample reviews
INSERT INTO `reviews` (`user_id`, `product_id`, `rating`, `title`, `comment`, `status`, `verified_purchase`) VALUES
(2, 1, 5, 'Amazing phone!', 'The iPhone 15 Pro Max is incredible. The camera quality is outstanding and the titanium build feels premium.', 'approved', 1),
(4, 1, 4, 'Great but expensive', 'Excellent phone with top-notch features, but the price is quite steep. Worth it for the camera alone.', 'approved', 1),
(2, 3, 5, 'Best earbuds ever', 'The AirPods Pro 3rd gen are fantastic. The noise cancellation is impressive and they fit perfectly.', 'approved', 1),
(4, 5, 4, 'Excellent sound quality', 'Sony delivers again with these headphones. Sound quality is superb and noise cancelling works great.', 'approved', 0);

-- Insert sample wishlists
INSERT INTO `wishlists` (`user_id`, `product_id`, `priority`, `price_alert`, `alert_price`) VALUES
(2, 2, 5, 1, 2200.00),
(2, 7, 4, 0, NULL),
(4, 4, 3, 1, 1100.00),
(4, 6, 2, 0, NULL);

-- Insert notification preferences for users
INSERT INTO `notification_preferences` (`user_id`, `type`, `enabled`, `email_enabled`, `push_enabled`) VALUES
(2, 'order', 1, 1, 1),
(2, 'promotion', 1, 1, 0),
(2, 'wishlist', 1, 1, 1),
(2, 'account', 1, 1, 1),
(2, 'system', 1, 1, 0),
(4, 'order', 1, 1, 1),
(4, 'promotion', 0, 0, 0),
(4, 'wishlist', 1, 1, 1),
(4, 'account', 1, 1, 1),
(4, 'system', 1, 0, 0);

-- Insert sample notifications
INSERT INTO `notifications` (`user_id`, `type`, `title`, `message`, `action_url`, `priority`) VALUES
(2, 'system', 'Welcome to FezaMarket!', 'Thank you for joining our marketplace. Start exploring amazing products from verified vendors.', '/products.php', 'normal'),
(4, 'system', 'Welcome to FezaMarket!', 'Thank you for joining our marketplace. Start exploring amazing products from verified vendors.', '/products.php', 'normal'),
(2, 'promotion', 'Flash Sale Alert', '25% off all electronics this weekend! Don\'t miss out on amazing deals.', '/deals.php', 'high'),
(4, 'wishlist', 'Price Drop Alert', 'Samsung Galaxy S24 Ultra in your wishlist is now $100 off!', '/product.php?id=4', 'high');

-- Insert sample coupons
INSERT INTO `coupons` (`code`, `type`, `value`, `minimum_amount`, `usage_limit`, `status`, `valid_from`, `valid_to`, `description`, `created_by`) VALUES
('WELCOME10', 'percentage', 10.00, 100.00, 1000, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), 'Welcome discount for new customers', 1),
('ELECTRONICS25', 'percentage', 25.00, 500.00, 500, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY), 'Weekend electronics sale', 1),
('SAVE50', 'fixed', 50.00, 200.00, 100, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY), 'Fixed $50 discount on orders over $200', 1);

-- Insert system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `category`, `description`, `is_public`, `updated_by`) VALUES
('site_name', 'FezaMarket', 'string', 'general', 'Site name displayed in headers and emails', 1, 1),
('site_description', 'Your trusted e-commerce marketplace', 'string', 'general', 'Site description for SEO', 1, 1),
('maintenance_mode', 'false', 'boolean', 'general', 'Enable maintenance mode', 0, 1),
('default_currency', 'USD', 'string', 'payments', 'Default currency code', 1, 1),
('tax_rate', '8.5', 'decimal', 'payments', 'Default tax rate percentage', 1, 1),
('free_shipping_threshold', '100.00', 'decimal', 'shipping', 'Minimum order value for free shipping', 1, 1),
('max_file_upload_size', '5242880', 'integer', 'uploads', 'Maximum file upload size in bytes (5MB)', 0, 1),
('email_verification_required', 'true', 'boolean', 'security', 'Require email verification for new accounts', 0, 1),
('two_factor_auth_enabled', 'false', 'boolean', 'security', 'Enable two-factor authentication', 0, 1),
('session_timeout', '3600', 'integer', 'security', 'Session timeout in seconds', 0, 1);

-- Update product statistics (average ratings and review counts)
UPDATE `products` p 
SET 
    `average_rating` = (
        SELECT AVG(rating) 
        FROM `reviews` r 
        WHERE r.product_id = p.id AND r.status = 'approved'
    ),
    `review_count` = (
        SELECT COUNT(*) 
        FROM `reviews` r 
        WHERE r.product_id = p.id AND r.status = 'approved'
    )
WHERE p.id IN (1, 3, 5);

-- Insert sample live stream (for demonstration)
INSERT INTO `live_streams` (`vendor_id`, `title`, `description`, `status`, `scheduled_at`) VALUES
(1, 'Tech Tuesday: Latest Smartphone Showcase', 'Join us for an exclusive look at the newest smartphones with special live pricing!', 'scheduled', DATE_ADD(NOW(), INTERVAL 2 DAY));

-- Insert migration tracking record
INSERT INTO `migrations` (`filename`, `batch`) VALUES
('01_initial_schema.sql', 1),
('02_shopping_orders.sql', 1),
('03_social_reviews.sql', 1),
('04_live_notifications.sql', 1),
('05_security_admin.sql', 1),
('99_seed_data.sql', 1);

-- Note: All passwords in this seed data use 'password123' as the plain text password
-- The hash shown is for demonstration - in production, use proper password hashing