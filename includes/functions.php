<?php
/**
 * Core Functions and Utilities
 * E-Commerce Platform
 */

/**
 * Session Management
 */
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
        session_destroy();
        session_unset();
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
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            // Store intended URL for redirect after login
            if (!empty($_SERVER['REQUEST_URI'])) {
                self::set('intended_url', $_SERVER['REQUEST_URI']);
            }
            redirect('/login.php');
        }
        
        // Validate session token if present
        $userId = self::getUserId();
        $sessionToken = self::get('session_token');
        
        if ($userId && $sessionToken && !validateSessionToken($userId, $sessionToken)) {
            // Invalid session, logout user
            self::destroy();
            redirect('/login.php?error=session_expired');
        }
    }
    
    public static function requireRole($role) {
        self::requireLogin();
        
        if (self::getUserRole() !== $role && self::getUserRole() !== 'admin') {
            logSecurityEvent(self::getUserId(), 'access_denied', 'page', null, [
                'required_role' => $role,
                'user_role' => self::getUserRole(),
                'url' => $_SERVER['REQUEST_URI'] ?? ''
            ]);
            redirect('/login.php?error=access_denied');
        }
    }
    
    public static function getIntendedUrl() {
        $url = self::get('intended_url', '/');
        self::remove('intended_url');
        return $url;
    }
}

/**
 * Security Functions
 */
function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hashPassword($password) {
    // Use ARGON2ID for maximum security
    return password_hash($password, PASSWORD_ARGON2ID, [
        'memory_cost' => 65536, // 64 MB
        'time_cost' => 4,       // 4 iterations
        'threads' => 3,         // 3 parallel threads
    ]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function csrfToken() {
    if (!Session::get('csrf_token')) {
        Session::set('csrf_token', generateToken());
    }
    return Session::get('csrf_token');
}

function csrfTokenInput() {
    $token = csrfToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

function verifyCsrfToken($token) {
    return hash_equals(Session::get('csrf_token', ''), $token);
}

/**
 * Enhanced Security Functions for Live Shopping Platform
 */

// Rate limiting for login attempts
function checkLoginAttempts($email, $maxAttempts = 5, $windowMinutes = 15) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT COUNT(*) 
        FROM login_attempts 
        WHERE email = ? 
        AND attempted_at > datetime('now', '-{$windowMinutes} minutes')
        AND success = 0
    ");
    $stmt->execute([$email]);
    $attempts = $stmt->fetchColumn();
    
    return $attempts < $maxAttempts;
}

// Log login attempt
function logLoginAttempt($email, $success = false) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO login_attempts (email, ip_address, attempted_at, success) 
        VALUES (?, ?, datetime('now'), ?)
    ");
    $stmt->execute([$email, getClientIP(), $success ? 1 : 0]);
}

// Clear successful login attempts
function clearLoginAttempts($email) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("DELETE FROM login_attempts WHERE email = ?");
    $stmt->execute([$email]);
}

// Generate secure session token
function generateSecureToken($length = 64) {
    return bin2hex(random_bytes($length));
}

// Create secure session
function createSecureSession($userId) {
    // Regenerate session ID for security
    session_regenerate_id(true);
    
    $db = Database::getInstance()->getConnection();
    $sessionToken = generateSecureToken();
    $csrfToken = generateToken();
    
    // Store session in database
    $stmt = $db->prepare("
        INSERT INTO user_sessions (user_id, session_token, csrf_token, ip_address, user_agent, expires_at, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, datetime('now', '+1 hour'), datetime('now'), datetime('now'))
    ");
    
    $stmt->execute([
        $userId,
        $sessionToken,
        $csrfToken,
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? '',
    ]);
    
    // Set session data
    Session::set('user_id', $userId);
    Session::set('session_token', $sessionToken);
    Session::set('csrf_token', $csrfToken);
    
    // Set secure cookies
    setSecureCookie('session_token', $sessionToken);
    
    return $sessionToken;
}

// Validate session token
function validateSessionToken($userId, $sessionToken) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT id FROM user_sessions 
        WHERE user_id = ? AND session_token = ? AND expires_at > datetime('now')
    ");
    $stmt->execute([$userId, $sessionToken]);
    
    return $stmt->fetchColumn() !== false;
}

// Set secure cookie
function setSecureCookie($name, $value, $expire = 3600) {
    setcookie($name, $value, [
        'expires' => time() + $expire,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Email verification token
function generateEmailVerificationToken($userId) {
    $db = Database::getInstance()->getConnection();
    $token = generateSecureToken();
    
    $stmt = $db->prepare("
        INSERT INTO email_verification_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, datetime('now', '+24 hours'), datetime('now'))
    ");
    $stmt->execute([$userId, $token]);
    
    return $token;
}

// Verify email token
function verifyEmailToken($token) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT user_id FROM email_verification_tokens 
        WHERE token = ? AND expires_at > datetime('now') AND used = 0
    ");
    $stmt->execute([$token]);
    $result = $stmt->fetch();
    
    if ($result) {
        // Mark token as used
        $updateStmt = $db->prepare("UPDATE email_verification_tokens SET used = 1 WHERE token = ?");
        $updateStmt->execute([$token]);
        
        // Mark user as verified
        $userStmt = $db->prepare("UPDATE users SET email_verified = 1 WHERE id = ?");
        $userStmt->execute([$result['user_id']]);
        
        return $result['user_id'];
    }
    
    return false;
}

// Password reset token
function generatePasswordResetToken($userId) {
    $db = Database::getInstance()->getConnection();
    $token = generateSecureToken();
    
    $stmt = $db->prepare("
        INSERT INTO password_reset_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, datetime('now', '+1 hour'), datetime('now'))
    ");
    $stmt->execute([$userId, $token]);
    
    return $token;
}

// Verify password reset token
function verifyPasswordResetToken($token) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT user_id FROM password_reset_tokens 
        WHERE token = ? AND expires_at > datetime('now') AND used = 0
    ");
    $stmt->execute([$token]);
    
    return $stmt->fetch();
}

// Use password reset token
function usePasswordResetToken($token, $newPassword) {
    $db = Database::getInstance()->getConnection();
    
    $tokenData = verifyPasswordResetToken($token);
    if (!$tokenData) {
        return false;
    }
    
    try {
        $db->beginTransaction();
        
        // Update password
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([hashPassword($newPassword), $tokenData['user_id']]);
        
        // Mark token as used
        $stmt = $db->prepare("UPDATE password_reset_tokens SET used = 1 WHERE token = ?");
        $stmt->execute([$token]);
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollBack();
        return false;
    }
}

// Audit logging
function logSecurityEvent($userId, $action, $resourceType = null, $resourceId = null, $metadata = []) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        INSERT INTO audit_logs (user_id, action, resource_type, resource_id, ip_address, user_agent, metadata, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, datetime('now'))
    ");
    
    $stmt->execute([
        $userId,
        $action,
        $resourceType,
        $resourceId,
        getClientIP(),
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        json_encode($metadata)
    ]);
}

/**
 * Utility Functions
 */
function redirect($url) {
    header("Location: {$url}");
    exit;
}

function formatPrice($price, $currency = 'USD') {
    return '$' . number_format($price, 2);
}

function formatDate($date, $format = 'M j, Y') {
    return date($format, strtotime($date));
}

function formatDateTime($datetime, $format = 'M j, Y g:i A') {
    return date($format, strtotime($datetime));
}

function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    return empty($text) ? 'n-a' : $text;
}

function getClientIP() {
    $ipKeys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($ipKeys as $key) {
        if (array_key_exists($key, $_SERVER) === true) {
            foreach (explode(',', $_SERVER[$key]) as $ip) {
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                    return $ip;
                }
            }
        }
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * File Upload Functions
 */
function uploadImage($file, $directory = 'products') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $uploadDir = UPLOAD_PATH . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($fileExtension, ALLOWED_IMAGE_TYPES)) {
        return false;
    }
    
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return false;
    }
    
    $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    
    if (move_uploaded_file($file['tmp_name'], $filePath)) {
        return "uploads/{$directory}/{$fileName}";
    }
    
    return false;
}

/**
 * Pagination Helper
 */
class Pagination {
    public static function create($totalItems, $itemsPerPage, $currentPage, $baseUrl) {
        $totalPages = ceil($totalItems / $itemsPerPage);
        $currentPage = max(1, min($currentPage, $totalPages));
        
        $pagination = [
            'current_page' => $currentPage,
            'total_pages' => $totalPages,
            'total_items' => $totalItems,
            'items_per_page' => $itemsPerPage,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $totalPages,
            'previous_page' => max(1, $currentPage - 1),
            'next_page' => min($totalPages, $currentPage + 1),
            'offset' => ($currentPage - 1) * $itemsPerPage,
            'links' => []
        ];
        
        // Generate page links
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $pagination['links'][] = [
                'page' => $i,
                'url' => $baseUrl . '?page=' . $i,
                'current' => $i === $currentPage
            ];
        }
        
        return $pagination;
    }
}

/**
 * Response Helper
 */
function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function errorResponse($message, $status = 400) {
    jsonResponse(['error' => $message], $status);
}

function successResponse($data = [], $message = 'Success') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

/**
 * Template Helper
 */
function renderTemplate($template, $data = []) {
    extract($data);
    ob_start();
    include "templates/{$template}.php";
    return ob_get_clean();
}

/**
 * Validation Helper
 */
class Validator {
    public static function required($value) {
        return !empty(trim($value));
    }
    
    public static function email($value) {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    public static function minLength($value, $min) {
        return strlen($value) >= $min;
    }
    
    public static function maxLength($value, $max) {
        return strlen($value) <= $max;
    }
    
    public static function numeric($value) {
        return is_numeric($value);
    }
    
    public static function positive($value) {
        return is_numeric($value) && $value > 0;
    }
    
    public static function unique($value, $table, $field, $excludeId = null) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$field} = ?";
        $params = [$value];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() == 0;
    }
}

/**
 * Logger
 */
class Logger {
    public static function log($message, $level = 'INFO') {
        if (!LOG_ERRORS) return;
        
        $logDir = ERROR_LOG_PATH;
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
        
        $logFile = $logDir . 'app_' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $logMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;
        
        file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
    }
    
    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    public static function info($message) {
        self::log($message, 'INFO');
    }
    
    public static function warning($message) {
        self::log($message, 'WARNING');
    }
}
?>