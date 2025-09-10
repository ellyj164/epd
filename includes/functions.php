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
    // Use ARGON2ID for maximum security - upgraded from bcrypt
    if (defined('PASSWORD_ARGON2ID') && PASSWORD_ARGON2ID) {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations  
            'threads' => 3,         // 3 parallel threads
        ]);
    }
    
    // Fallback to bcrypt if Argon2ID is not available (PHP < 7.2)
    return password_hash($password, PASSWORD_DEFAULT, [
        'cost' => BCRYPT_COST
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
        AND attempted_at > DATE_SUB(NOW(), INTERVAL {$windowMinutes} MINUTE)
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
        VALUES (?, ?, NOW(), ?)
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
        VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW(), NOW())
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
        WHERE user_id = ? AND session_token = ? AND expires_at > NOW()
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
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
    ");
    $stmt->execute([$userId, $token]);
    
    return $token;
}

// Verify email token
function verifyEmailToken($token) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT user_id FROM email_verification_tokens 
        WHERE token = ? AND expires_at > NOW() AND used = 0
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
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR), NOW())
    ");
    $stmt->execute([$userId, $token]);
    
    return $token;
}

// Verify password reset token
function verifyPasswordResetToken($token) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT user_id FROM password_reset_tokens 
        WHERE token = ? AND expires_at > NOW() AND used = 0
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
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
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

/**
 * Two-Factor Authentication (2FA) TOTP Functions
 */

// Generate TOTP secret
function generateTotpSecret($length = 32) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $secret = '';
    for ($i = 0; $i < $length; $i++) {
        $secret .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $secret;
}

// Generate TOTP code for verification
function generateTotpCode($secret, $timestamp = null) {
    if ($timestamp === null) {
        $timestamp = time();
    }
    
    // Convert secret from base32
    $key = base32Decode($secret);
    
    // Calculate time counter (30-second window)
    $timeCounter = floor($timestamp / 30);
    
    // Pack time counter as binary string
    $timeBytes = pack('N*', 0) . pack('N*', $timeCounter);
    
    // HMAC-SHA1
    $hash = hash_hmac('sha1', $timeBytes, $key, true);
    
    // Dynamic truncation
    $offset = ord($hash[19]) & 0xf;
    $code = (
        ((ord($hash[$offset]) & 0x7f) << 24) |
        ((ord($hash[$offset+1]) & 0xff) << 16) |
        ((ord($hash[$offset+2]) & 0xff) << 8) |
        (ord($hash[$offset+3]) & 0xff)
    ) % pow(10, 6);
    
    return str_pad($code, 6, '0', STR_PAD_LEFT);
}

// Verify TOTP code with time window tolerance
function verifyTotpCode($secret, $userCode, $tolerance = 1) {
    $currentTime = time();
    
    // Check current window and adjacent windows (±30 seconds)
    for ($i = -$tolerance; $i <= $tolerance; $i++) {
        $testTime = $currentTime + ($i * 30);
        $validCode = generateTotpCode($secret, $testTime);
        
        if (hash_equals($validCode, $userCode)) {
            return true;
        }
    }
    
    return false;
}

// Base32 decoding function for TOTP
function base32Decode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $data = strtoupper($data);
    $output = '';
    $v = 0;
    $vbits = 0;
    
    for ($i = 0, $j = strlen($data); $i < $j; $i++) {
        $v <<= 5;
        if (($x = strpos($alphabet, $data[$i])) !== false) {
            $v += $x;
            $vbits += 5;
            if ($vbits >= 8) {
                $output .= chr(($v >> ($vbits - 8)) & 255);
                $vbits -= 8;
            }
        }
    }
    
    return $output;
}

// Enable 2FA for user
function enable2FA($userId, $secret) {
    $db = Database::getInstance()->getConnection();
    
    // Generate backup codes
    $backupCodes = [];
    for ($i = 0; $i < 10; $i++) {
        $backupCodes[] = strtoupper(bin2hex(random_bytes(4))); // 8-character codes
    }
    
    $stmt = $db->prepare("
        INSERT INTO user_totp_secrets (user_id, secret, backup_codes, enabled) 
        VALUES (?, ?, ?, TRUE)
        ON DUPLICATE KEY UPDATE 
        secret = VALUES(secret), 
        backup_codes = VALUES(backup_codes), 
        enabled = TRUE,
        updated_at = CURRENT_TIMESTAMP
    ");
    
    try {
        $stmt->execute([$userId, $secret, json_encode($backupCodes)]);
        return $backupCodes;
    } catch (PDOException $e) {
        error_log("2FA enable error: " . $e->getMessage());
        return false;
    }
}

// Verify user has 2FA enabled
function is2FAEnabled($userId) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT enabled FROM user_totp_secrets 
        WHERE user_id = ? AND enabled = TRUE
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchColumn() ? true : false;
}

// Get user's TOTP secret
function getUserTotpSecret($userId) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        SELECT secret FROM user_totp_secrets 
        WHERE user_id = ? AND enabled = TRUE
    ");
    $stmt->execute([$userId]);
    
    return $stmt->fetchColumn();
}

// Disable 2FA for user
function disable2FA($userId) {
    $db = Database::getInstance()->getConnection();
    
    $stmt = $db->prepare("
        UPDATE user_totp_secrets 
        SET enabled = FALSE, updated_at = CURRENT_TIMESTAMP 
        WHERE user_id = ?
    ");
    
    try {
        $stmt->execute([$userId]);
        return true;
    } catch (PDOException $e) {
        error_log("2FA disable error: " . $e->getMessage());
        return false;
    }
}

/**
 * Enhanced Rate Limiting Functions
 */
function checkRateLimit($identifier, $maxRequests = null, $windowSeconds = null) {
    if (!RATE_LIMIT_ENABLED) {
        return true;
    }
    
    $maxRequests = $maxRequests ?? RATE_LIMIT_REQUESTS;
    $windowSeconds = $windowSeconds ?? RATE_LIMIT_WINDOW;
    
    $cacheKey = 'rate_limit_' . hash('sha256', $identifier);
    $currentTime = time();
    $windowStart = $currentTime - $windowSeconds;
    
    // For now, use a simple file-based implementation
    // In production, use Redis or Memcached
    $cacheFile = ERROR_LOG_PATH . "rate_limit_$cacheKey.json";
    
    $requests = [];
    if (file_exists($cacheFile)) {
        $data = json_decode(file_get_contents($cacheFile), true);
        if ($data && is_array($data)) {
            // Filter out expired requests
            $requests = array_filter($data, function($timestamp) use ($windowStart) {
                return $timestamp > $windowStart;
            });
        }
    }
    
    // Check if limit exceeded
    if (count($requests) >= $maxRequests) {
        return false;
    }
    
    // Add current request
    $requests[] = $currentTime;
    
    // Save updated requests
    file_put_contents($cacheFile, json_encode($requests));
    
    return true;
}

/**
 * CSRF Protection Enhancement
 */
function validateCsrfAndRateLimit($identifier = null) {
    // CSRF validation
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        http_response_code(403);
        die(json_encode(['error' => 'Invalid CSRF token']));
    }
    
    // Rate limiting
    $identifier = $identifier ?? (getClientIP() . ':' . ($_SERVER['REQUEST_URI'] ?? ''));
    if (!checkRateLimit($identifier)) {
        http_response_code(429);
        die(json_encode(['error' => 'Too many requests. Please try again later.']));
    }
    
    return true;
}
?>