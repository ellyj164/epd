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

// Set UTC timezone for consistent timestamp handling (Security requirement)
date_default_timezone_set('UTC');

// Load configuration (includes environment variables)
require_once __DIR__ . '/../config/config.php';

// Load core functions first (defines Session, Logger, CSRF helpers, sanitizers, etc.)
require_once __DIR__ . '/functions.php';

// Load standardized database access and compatibility wrappers
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/database.php';

// Load URL helpers and routing
require_once __DIR__ . '/helpers.php';

// Load enhanced email system
require_once __DIR__ . '/email_system.php';

// Load secure EmailTokenManager for OTP verification
require_once __DIR__ . '/EmailTokenManager.php';

// Load global error handler
require_once __DIR__ . '/GlobalErrorHandler.php';

// Initialize global error handling
GlobalErrorHandler::initialize();

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

/**
 * Defensive guards: ensure core classes and functions exist before use.
 * If functions.php wasn't loaded for any reason, attempt to load it again,
 * and if still missing, define minimal fallbacks to avoid fatals.
 */
@require_once __DIR__ . '/functions.php';

if (!class_exists('Session')) {
    // Minimal fallback Session implementation
    class Session {
        public static function start() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
        }
        public static function set($key, $value) {
            self::start();
            $_SESSION[$key] = $value;
        }
        public static function get($key, $default = null) {
            self::start();
            return $_SESSION[$key] ?? $default;
        }
        public static function remove($key) {
            self::start();
            unset($_SESSION[$key]);
        }
        public static function destroy() {
            self::start();
            session_unset();
            session_destroy();
        }
        public static function isLoggedIn() {
            return self::get('user_id') !== null;
        }
        public static function getUserId() {
            return self::get('user_id');
        }
        public static function getUserRole() {
            return self::get('user_role', 'customer');
        }
        public static function getIntendedUrl() {
            $url = self::get('intended_url', '/');
            self::remove('intended_url');
            return $url;
        }
    }
}

if (!class_exists('Logger')) {
    // Minimal fallback Logger implementation (logs to PHP error_log)
    class Logger {
        public static function log($message, $level = 'INFO') {
            error_log('[' . $level . '] ' . $message);
        }
        public static function error($message)   { self::log($message, 'ERROR'); }
        public static function info($message)    { self::log($message, 'INFO'); }
        public static function warning($message) { self::log($message, 'WARNING'); }
    }
}

// CSRF helpers fallback (in case functions.php failed to load)
if (!function_exists('csrfToken')) {
    function csrfToken() {
        if (!Session::get('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }
}
if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        return hash_equals(Session::get('csrf_token', ''), $token ?? '');
    }
}

// Sanitization and validation fallbacks
if (!function_exists('sanitizeInput')) {
    function sanitizeInput($input) {
        return htmlspecialchars(trim((string)$input), ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('validateEmail')) {
    function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}
if (!function_exists('redirect')) {
    function redirect($url) {
        header("Location: {$url}");
        exit;
    }
}

// Secure session helper fallbacks used by login and auth flow
if (!function_exists('generateSecureToken')) {
    function generateSecureToken($length = 64) {
        return bin2hex(random_bytes($length));
    }
}
if (!function_exists('setSecureCookie')) {
    function setSecureCookie($name, $value, $expire = 3600) {
        setcookie($name, $value, [
            'expires'  => time() + $expire,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
    }
}
if (!function_exists('createSecureSession')) {
    // Minimal fallback that does not persist to DB, but keeps the app functional
    function createSecureSession($userId) {
        session_regenerate_id(true);
        $sessionToken = generateSecureToken();
        $csrfToken = bin2hex(random_bytes(32));

        Session::set('user_id', $userId);
        Session::set('session_token', $sessionToken);
        Session::set('csrf_token', $csrfToken);

        setSecureCookie('session_token', $sessionToken);

        return $sessionToken;
    }
}
if (!function_exists('validateSessionToken')) {
    // Fallback that validates against session only (no DB lookup)
    function validateSessionToken($userId, $sessionToken) {
        $stored = Session::get('session_token', '');
        return $userId === Session::get('user_id') && hash_equals((string)$stored, (string)$sessionToken);
    }
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
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    return basename(parse_url($uri, PHP_URL_PATH));
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

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
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

    if (defined('DEBUG_MODE') && DEBUG_MODE) {
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
    $configCheck = defined('SECRET_KEY') && !empty(SECRET_KEY) && SECRET_KEY !== 'your-secret-key-change-this-in-production-minimum-32-chars';
    $checks['config'] = ['status' => $configCheck ? 'ok' : 'warning', 'message' => $configCheck ? 'Configuration secure' : 'Default secret key detected'];

    return $checks;
}