<?php
/**
 * Application Bootstrap
 * E-Commerce Platform
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load configuration
require_once __DIR__ . '/config/config.php';

// Load core functions
require_once __DIR__ . '/includes/functions.php';

// Load database connection
require_once __DIR__ . '/includes/database.php';

// Load models
require_once __DIR__ . '/includes/models.php';
require_once __DIR__ . '/includes/models_extended.php';

// Initialize session
Session::start();

// Global template variables
$site_name = 'E-Commerce Platform';
$current_user = null;
$cart_count = 0;

if (Session::isLoggedIn()) {
    $user = new User();
    $current_user = $user->find(Session::getUserId());
    
    $cart = new Cart();
    $cart_count = $cart->getCartCount(Session::getUserId());
}

/**
 * Template Helper Functions
 */
function includeHeader($title = '') {
    global $site_name, $current_user, $cart_count;
    $page_title = $title ? $title . ' - ' . $site_name : $site_name;
    include __DIR__ . '/templates/header.php';
}

function includeFooter() {
    include __DIR__ . '/templates/footer.php';
}

function includeNavigation() {
    global $current_user, $cart_count;
    include __DIR__ . '/templates/navigation.php';
}

/**
 * Route Helper
 */
function getCurrentPage() {
    return basename($_SERVER['REQUEST_URI'], '?' . $_SERVER['QUERY_STRING']);
}

/**
 * Image Helper
 */
function getProductImageUrl($imagePath, $default = 'images/placeholder-product.jpg') {
    if (empty($imagePath) || !file_exists(__DIR__ . '/' . $imagePath)) {
        return $default;
    }
    return $imagePath;
}

/**
 * Error Handler
 */
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $message = "Error: [$errno] $errstr in $errfile on line $errline";
    Logger::error($message);
    
    if (DEBUG_MODE) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong>Debug Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline";
        echo "</div>";
    }
    
    return true;
}

set_error_handler('handleError');

/**
 * Exception Handler
 */
function handleException($exception) {
    $message = "Uncaught exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine();
    Logger::error($message);
    
    if (DEBUG_MODE) {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "<strong>Exception:</strong> " . $exception->getMessage() . "<br>";
        echo "<strong>File:</strong> " . $exception->getFile() . "<br>";
        echo "<strong>Line:</strong> " . $exception->getLine() . "<br>";
        echo "<strong>Stack Trace:</strong><pre>" . $exception->getTraceAsString() . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; margin: 10px 0; border-radius: 4px;'>";
        echo "An error occurred. Please try again later.";
        echo "</div>";
    }
}

set_exception_handler('handleException');

/**
 * Initialize directories
 */
$directories = [
    __DIR__ . '/uploads',
    __DIR__ . '/uploads/products',
    __DIR__ . '/uploads/vendors',
    __DIR__ . '/logs'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}
?>