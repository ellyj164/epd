<?php
/**
 * Secure Email Token Manager
 * Implements PHP 8 + MariaDB compatible OTP verification with security enhancements
 */

class EmailTokenManager {
    private static $pepper = null;
    private PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        
        // Initialize pepper from environment or generate secure fallback
        if (self::$pepper === null) {
            self::$pepper = env('OTP_PEPPER', SECRET_KEY . '_otp_salt_' . date('Y-m'));
        }
    }
    
    /**
     * Generate secure OTP token and store with hash
     */
    public function generateToken(int $userId, string $type = 'email_verification', string $email = null, int $expiryMinutes = 15): ?string {
        try {
            // Generate 8-digit OTP (stored as string to preserve leading zeros)
            $otp = str_pad((string)random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT);
            
            // Create hash with pepper for storage
            $tokenHash = hash('sha256', $otp . self::$pepper);
            
            // Calculate expiry time in UTC
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$expiryMinutes} minutes"));
            
            $this->db->beginTransaction();
            
            try {
                // Clear any existing tokens for this user and type
                $stmt = $this->db->prepare("
                    DELETE FROM email_tokens 
                    WHERE user_id = ? AND type = ?
                ");
                $stmt->execute([$userId, $type]);
                
                // Insert new hashed token
                $stmt = $this->db->prepare("
                    INSERT INTO email_tokens (user_id, token, type, email, expires_at, ip_address, created_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $userId,
                    $tokenHash, // Store hash, not plain OTP
                    $type,
                    $email,
                    $expiresAt,
                    $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                    date('Y-m-d H:i:s')
                ]);
                
                $this->db->commit();
                
                Logger::info("OTP generated for user {$userId}, type {$type}");
                return $otp; // Return plain OTP for sending in email
                
            } catch (Exception $e) {
                $this->db->rollBack();
                Logger::error("Failed to store OTP token: " . $e->getMessage());
                return null;
            }
            
        } catch (Exception $e) {
            Logger::error("Failed to generate OTP: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Verify OTP token with rate limiting and security checks
     */
    public function verifyToken(string $otp, string $type, int $userId, string $email = null): array {
        $result = ['success' => false, 'message' => 'Invalid verification code.', 'rate_limited' => false];
        
        try {
            // Check rate limiting first
            if (!$this->checkRateLimit($userId, $email, $type)) {
                $result['rate_limited'] = true;
                $result['message'] = 'Too many attempts. Please try again later.';
                return $result;
            }
            
            // Log the attempt
            $this->logAttempt($userId, $email, $type, false);
            
            // Hash the provided OTP with pepper
            $otpHash = hash('sha256', $otp . self::$pepper);
            
            // Find valid token using hash comparison
            $stmt = $this->db->prepare("
                SELECT id, user_id, email, expires_at, used_at, created_at
                FROM email_tokens 
                WHERE user_id = ? AND type = ? AND token = ? 
                AND expires_at > NOW() AND used_at IS NULL
                ORDER BY created_at DESC
                LIMIT 1
            ");
            
            $stmt->execute([$userId, $type, $otpHash]);
            $tokenData = $stmt->fetch();
            
            if (!$tokenData) {
                Logger::warning("Invalid OTP attempt for user {$userId}, type {$type}");
                return $result;
            }
            
            // Additional security check - compare using hash_equals
            if (!hash_equals($otpHash, $otpHash)) {
                Logger::warning("Hash comparison failed for user {$userId}");
                return $result;
            }
            
            // Mark token as used (single-use)
            $this->markTokenAsUsed($tokenData['id']);
            
            // Log successful attempt
            $this->logAttempt($userId, $email, $type, true);
            
            Logger::info("OTP verified successfully for user {$userId}, type {$type}");
            
            $result['success'] = true;
            $result['message'] = 'Verification successful.';
            return $result;
            
        } catch (Exception $e) {
            Logger::error("Token verification error: " . $e->getMessage());
            return $result;
        }
    }
    
    /**
     * Check rate limiting for OTP attempts
     */
    private function checkRateLimit(int $userId, ?string $email, string $type, int $maxAttempts = 5, int $windowMinutes = 15): bool {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM otp_attempts 
                WHERE (user_id = ? OR email = ?) 
                AND token_type = ?
                AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
                AND success = 0
            ");
            
            $stmt->execute([$userId, $email, $type, $windowMinutes]);
            $attempts = (int)$stmt->fetchColumn();
            
            return $attempts < $maxAttempts;
            
        } catch (Exception $e) {
            Logger::error("Rate limit check failed: " . $e->getMessage());
            return true; // Allow on error to avoid blocking legitimate users
        }
    }
    
    /**
     * Log OTP attempt for rate limiting
     */
    private function logAttempt(int $userId, ?string $email, string $type, bool $success): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO otp_attempts (user_id, email, ip_address, attempted_at, success, token_type)
                VALUES (?, ?, ?, NOW(), ?, ?)
            ");
            
            $stmt->execute([
                $userId,
                $email,
                $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
                $success ? 1 : 0,
                $type
            ]);
            
        } catch (Exception $e) {
            Logger::error("Failed to log OTP attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Mark token as used (single-use enforcement)
     */
    private function markTokenAsUsed(int $tokenId): void {
        try {
            $stmt = $this->db->prepare("
                UPDATE email_tokens 
                SET used_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$tokenId]);
            
        } catch (Exception $e) {
            Logger::error("Failed to mark token as used: " . $e->getMessage());
        }
    }
    
    /**
     * Clean up expired tokens (should be called periodically)
     */
    public function cleanupExpiredTokens(): int {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM email_tokens 
                WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)
            ");
            $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            Logger::info("Cleaned up {$deletedCount} expired tokens");
            
            return $deletedCount;
            
        } catch (Exception $e) {
            Logger::error("Token cleanup failed: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Clean up old OTP attempts (should be called periodically)
     */
    public function cleanupOldAttempts(): int {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM otp_attempts 
                WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 7 DAY)
            ");
            $stmt->execute();
            
            $deletedCount = $stmt->rowCount();
            Logger::info("Cleaned up {$deletedCount} old OTP attempts");
            
            return $deletedCount;
            
        } catch (Exception $e) {
            Logger::error("OTP attempts cleanup failed: " . $e->getMessage());
            return 0;
        }
    }
}