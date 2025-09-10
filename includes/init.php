<?php
/**
 * Application Bootstrap
 * E-Commerce Platform - PHP 8 Enhanced
 */

// Start session with secure settings
if (session_status() === PHP_SESSION_NONE) {
    // Secure session configuration
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
    ini_set('session.cookie_samesite', 'Strict');
    
    session_start();
}

// Load configuration (includes environment variables)
require_once __DIR__ . '/../config/config.php';

// Load standardized database access
require_once __DIR__ . '/db.php';

// Load core functions
require_once __DIR__ . '/functions.php';

// Load URL helpers and routing
require_once __DIR__ . '/helpers.php';

// Load database connection
require_once __DIR__ . '/database.php';

// Load enhanced email system
require_once __DIR__ . '/email_system.php';

// Load legacy email system for compatibility
if (file_exists(__DIR__ . '/email.php')) {
    require_once __DIR__ . '/email.php';
}

// Load RBAC middleware
require_once __DIR__ . '/../middleware/RoleMiddleware.php';

// Load models
require_once __DIR__ . '/models.php';
require_once __DIR__ . '/models_extended.php';

// Load UI components if available
if (file_exists(__DIR__ . '/../assets/components/autoload.php')) {
    require_once __DIR__ . '/../assets/components/autoload.php';
}

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
    include __DIR__ . '/../templates/header.php';
}

function includeFooter() {
    include __DIR__ . '/../templates/footer.php';
}

function includeNavigation() {
    global $current_user, $cart_count;
    include __DIR__ . '/../templates/navigation.php';
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
 * Initialize required directories
 */
$directories = [
    UPLOAD_PATH,
    UPLOAD_PATH . 'products/',
    UPLOAD_PATH . 'vendors/',
    UPLOAD_PATH . 'avatars/',
    ERROR_LOG_PATH
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

/**
 * Health Check Function
 */
function performHealthCheck() {
    $checks = [];
    
    // Database connectivity
    try {
        Database::getInstance()->getConnection();
        $checks['database'] = ['status' => 'ok', 'message' => 'Database connection successful'];
    } catch (Exception $e) {
        $checks['database'] = ['status' => 'error', 'message' => 'Database connection failed: ' . $e->getMessage()];
    }
    
    // Required directories
    $requiredDirs = [UPLOAD_PATH, ERROR_LOG_PATH];
    $dirCheck = true;
    foreach ($requiredDirs as $dir) {
        if (!is_dir($dir) || !is_writable($dir)) {
            $dirCheck = false;
            break;
        }
    }
    $checks['directories'] = ['status' => $dirCheck ? 'ok' : 'error', 'message' => $dirCheck ? 'All directories writable' : 'Directory permissions issue'];
    
    // Configuration
    $configCheck = !empty(SECRET_KEY) && SECRET_KEY !== 'your-secret-key-change-this-in-production-minimum-32-chars';
    $checks['config'] = ['status' => $configCheck ? 'ok' : 'warning', 'message' => $configCheck ? 'Configuration secure' : 'Default secret key detected'];
    
    return $checks;
}
?>