<?php
/**
 * Database Configuration
 * E-Commerce Platform
 */

// Database connection settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'ecommerce_platform');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Use SQLite for demo if MySQL is not available
define('USE_SQLITE', true);
define('SQLITE_PATH', __DIR__ . '/../database/ecommerce.sqlite');

// Application settings
define('APP_NAME', 'E-Commerce Platform');
define('APP_URL', 'http://localhost');
define('APP_VERSION', '1.0.0');

// Security settings
define('SECRET_KEY', 'your-secret-key-change-this-in-production');
define('SESSION_TIMEOUT', 3600); // 1 hour
define('BCRYPT_COST', 12);

// File upload settings
define('MAX_UPLOAD_SIZE', 5242880); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Pagination settings
define('PRODUCTS_PER_PAGE', 12);
define('ORDERS_PER_PAGE', 20);
define('REVIEWS_PER_PAGE', 10);

// Email settings (configure for production)
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', '');
define('SMTP_PASSWORD', '');
define('FROM_EMAIL', 'noreply@ecommerce.com');
define('FROM_NAME', APP_NAME);

// Payment gateway settings (sandbox/testing)
define('PAYMENT_GATEWAY', 'mock'); // 'mock', 'stripe', 'paypal'
define('STRIPE_PUBLISHABLE_KEY', '');
define('STRIPE_SECRET_KEY', '');
define('PAYPAL_CLIENT_ID', '');
define('PAYPAL_CLIENT_SECRET', '');

// AI/ML settings
define('AI_RECOMMENDATIONS_ENABLED', true);
define('MIN_RECOMMENDATIONS', 4);
define('MAX_RECOMMENDATIONS', 12);

// Cache settings
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600);

// Debug settings
define('DEBUG_MODE', true);
define('LOG_ERRORS', true);
define('ERROR_LOG_PATH', __DIR__ . '/../logs/');

// Timezone
date_default_timezone_set('UTC');

// Error reporting
if (DEBUG_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>