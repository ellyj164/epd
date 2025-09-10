<?php
/**
 * Email Notification System
 * E-Commerce Platform
 */

/**
 * Email Service Class
 */
class EmailService {
    private static $instance = null;
    private $queue = [];
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Send email notification
     */
    public function send($to, $subject, $templateName, $data = [], $options = []) {
        try {
            $emailData = [
                'to' => $to,
                'subject' => $subject,
                'template' => $templateName,
                'data' => $data,
                'options' => $options,
                'created_at' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];
            
            if (isset($options['immediate']) && $options['immediate']) {
                return $this->sendImmediate($emailData);
            } else {
                return $this->queueEmail($emailData);
            }
        } catch (Exception $e) {
            Logger::error("Email send failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Queue email for later processing
     */
    private function queueEmail($emailData) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO mail_queue (to_email, subject, template_name, template_data, options, status, created_at)
            VALUES (?, ?, ?, ?, ?, 'pending', ?)
        ");
        
        return $stmt->execute([
            $emailData['to'],
            $emailData['subject'],
            $emailData['template'],
            json_encode($emailData['data']),
            json_encode($emailData['options']),
            $emailData['created_at']
        ]);
    }
    
    /**
     * Send email immediately
     */
    private function sendImmediate($emailData) {
        $body = $this->renderTemplate($emailData['template'], $emailData['data']);
        
        if (!$body) {
            throw new Exception("Failed to render email template: " . $emailData['template']);
        }
        
        // Use PHP mail() function for now - can be upgraded to PHPMailer later
        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=UTF-8',
            'From: ' . FROM_NAME . ' <' . FROM_EMAIL . '>',
            'Reply-To: ' . FROM_EMAIL,
            'X-Mailer: PHP/' . phpversion()
        ];
        
        $success = mail(
            $emailData['to'],
            $emailData['subject'],
            $body,
            implode("\r\n", $headers)
        );
        
        // Log the attempt
        $this->logEmailAttempt($emailData, $success);
        
        return $success;
    }
    
    /**
     * Render email template
     */
    private function renderTemplate($templateName, $data) {
        $templatePath = __DIR__ . "/../templates/emails/{$templateName}.php";
        
        if (!file_exists($templatePath)) {
            Logger::error("Email template not found: {$templatePath}");
            return false;
        }
        
        // Extract data variables for template
        extract($data);
        
        // Start output buffering
        ob_start();
        
        try {
            include $templatePath;
            $content = ob_get_contents();
        } catch (Exception $e) {
            Logger::error("Error rendering template {$templateName}: " . $e->getMessage());
            $content = false;
        } finally {
            ob_end_clean();
        }
        
        return $content;
    }
    
    /**
     * Log email attempt
     */
    private function logEmailAttempt($emailData, $success) {
        $db = Database::getInstance()->getConnection();
        
        try {
            $stmt = $db->prepare("
                INSERT INTO email_log (to_email, subject, template_name, status, sent_at, created_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $emailData['to'],
                $emailData['subject'],
                $emailData['template'],
                $success ? 'sent' : 'failed',
                date('Y-m-d H:i:s'),
                $emailData['created_at']
            ]);
        } catch (Exception $e) {
            Logger::error("Failed to log email attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Process email queue (for cron job)
     */
    public function processQueue($limit = 10) {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            SELECT * FROM mail_queue 
            WHERE status = 'pending' 
            ORDER BY created_at ASC 
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        $emails = $stmt->fetchAll();
        
        foreach ($emails as $email) {
            try {
                $emailData = [
                    'to' => $email['to_email'],
                    'subject' => $email['subject'],
                    'template' => $email['template_name'],
                    'data' => json_decode($email['template_data'], true) ?: [],
                    'options' => json_decode($email['options'], true) ?: [],
                    'created_at' => $email['created_at']
                ];
                
                $success = $this->sendImmediate($emailData);
                
                // Update queue status
                $updateStmt = $db->prepare("
                    UPDATE mail_queue 
                    SET status = ?, processed_at = ? 
                    WHERE id = ?
                ");
                $updateStmt->execute([
                    $success ? 'sent' : 'failed',
                    date('Y-m-d H:i:s'),
                    $email['id']
                ]);
                
            } catch (Exception $e) {
                Logger::error("Queue processing error for email {$email['id']}: " . $e->getMessage());
                
                // Mark as failed
                $updateStmt = $db->prepare("
                    UPDATE mail_queue 
                    SET status = 'failed', processed_at = ?, error_message = ?
                    WHERE id = ?
                ");
                $updateStmt->execute([
                    date('Y-m-d H:i:s'),
                    $e->getMessage(),
                    $email['id']
                ]);
            }
        }
        
        return count($emails);
    }
}

/**
 * Email Token Management
 */
class EmailTokenManager {
    /**
     * Generate secure email token
     */
    public static function generateToken($userId, $type, $expiresInMinutes = 60) {
        $db = Database::getInstance()->getConnection();
        
        // Clean up old tokens
        self::cleanupExpiredTokens();
        
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + ($expiresInMinutes * 60));
        
        $stmt = $db->prepare("
            INSERT INTO email_tokens (user_id, type, token_hash, expires_at, created_at)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $success = $stmt->execute([
            $userId,
            $type,
            $tokenHash,
            $expiresAt,
            date('Y-m-d H:i:s')
        ]);
        
        return $success ? $token : false;
    }
    
    /**
     * Verify email token
     */
    public static function verifyToken($token, $type) {
        $db = Database::getInstance()->getConnection();
        
        $tokenHash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');
        
        $stmt = $db->prepare("
            SELECT * FROM email_tokens 
            WHERE token_hash = ? AND type = ? AND used_at IS NULL AND expires_at > ?
        ");
        $stmt->execute([$tokenHash, $type, $now]);
        $tokenData = $stmt->fetch();
        
        if (!$tokenData) {
            return false;
        }
        
        // Mark token as used
        $updateStmt = $db->prepare("
            UPDATE email_tokens 
            SET used_at = ? 
            WHERE id = ?
        ");
        $updateStmt->execute([
            date('Y-m-d H:i:s'),
            $tokenData['id']
        ]);
        
        return $tokenData;
    }
    
    /**
     * Clean up expired tokens
     */
    public static function cleanupExpiredTokens() {
        $db = Database::getInstance()->getConnection();
        
        $now = date('Y-m-d H:i:s');
        $stmt = $db->prepare("DELETE FROM email_tokens WHERE expires_at < ?");
        $stmt->execute([$now]);
    }
}

/**
 * Notification Helpers
 */

/**
 * Send welcome email to new user
 */
function sendWelcomeEmail($user) {
    $emailService = EmailService::getInstance();
    
    return $emailService->send(
        $user['email'],
        'Welcome to FezaMarket!',
        'welcome',
        [
            'user' => $user,
            'login_url' => url('login.php')
        ]
    );
}

/**
 * Send email verification
 */
function sendEmailVerification($user) {
    $token = EmailTokenManager::generateToken($user['id'], 'email_verification', 1440); // 24 hours
    
    if (!$token) {
        return false;
    }
    
    $emailService = EmailService::getInstance();
    
    return $emailService->send(
        $user['email'],
        'Verify Your Email Address',
        'email_verification',
        [
            'user' => $user,
            'verification_url' => url('verify-email.php?token=' . $token)
        ]
    );
}

/**
 * Send password reset email
 */
function sendPasswordResetEmail($user) {
    $token = EmailTokenManager::generateToken($user['id'], 'password_reset', 60); // 1 hour
    
    if (!$token) {
        return false;
    }
    
    $emailService = EmailService::getInstance();
    
    return $emailService->send(
        $user['email'],
        'Reset Your Password',
        'password_reset',
        [
            'user' => $user,
            'reset_url' => url('reset-password.php?token=' . $token)
        ]
    );
}

/**
 * Send order confirmation email
 */
function sendOrderConfirmationEmail($order, $user) {
    $emailService = EmailService::getInstance();
    
    return $emailService->send(
        $user['email'],
        "Order Confirmation - Order #{$order['id']}",
        'order_confirmation',
        [
            'user' => $user,
            'order' => $order,
            'order_url' => url('order.php?id=' . $order['id'])
        ]
    );
}

/**
 * Send seller approval email
 */
function sendSellerApprovalEmail($vendor, $user) {
    $emailService = EmailService::getInstance();
    
    return $emailService->send(
        $user['email'],
        'Your Seller Account Has Been Approved!',
        'seller_approval',
        [
            'user' => $user,
            'vendor' => $vendor,
            'seller_center_url' => url('seller-center.php')
        ]
    );
}

/**
 * Send login alert email
 */
function sendLoginAlertEmail($user, $deviceInfo) {
    $emailService = EmailService::getInstance();
    
    return $emailService->send(
        $user['email'],
        'New Sign-In to Your Account',
        'login_alert',
        [
            'user' => $user,
            'device_info' => $deviceInfo,
            'security_url' => url('account.php?tab=security')
        ]
    );
}
?>