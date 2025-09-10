<?php
/**
 * Email Verification Page
 * E-Commerce Platform
 */

require_once __DIR__ . '/includes/init.php';

$message = '';
$success = false;
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $message = 'Invalid verification link. Please check your email for the correct link.';
} else {
    try {
        // Verify the token
        $tokenData = EmailTokenManager::verifyToken($token, 'email_verification');
        
        if (!$tokenData) {
            $message = 'This verification link is invalid or has expired. Please request a new verification email.';
        } else {
            // Token is valid, activate the user
            $user = new User();
            $userData = $user->find($tokenData['user_id']);
            
            if (!$userData) {
                $message = 'User account not found. Please contact support.';
            } elseif ($userData['status'] === 'active' && $userData['verified_at']) {
                // Already verified
                $success = true;
                $message = 'Your email has already been verified. You can now log in to your account.';
            } else {
                // Verify the user account
                $verified = $user->verifyEmail($tokenData['user_id']);
                
                if ($verified) {
                    $success = true;
                    $message = 'Email verified successfully! Your account is now active and you can log in.';
                    
                    // Log the verification
                    Logger::info("Email verified for user {$userData['email']}");
                    logSecurityEvent($tokenData['user_id'], 'email_verified', 'user', $tokenData['user_id']);
                    
                } else {
                    $message = 'Failed to verify your email. Please try again or contact support.';
                    Logger::error("Failed to verify email for user ID {$tokenData['user_id']}");
                }
            }
        }
        
    } catch (Exception $e) {
        $message = 'An error occurred during verification. Please try again or contact support.';
        Logger::error("Email verification error: " . $e->getMessage());
    }
}

$page_title = 'Email Verification';
includeHeader($page_title);
?>

<div class="container">
    <div class="row justify-center">
        <div class="col-6">
            <div class="card mt-4">
                <div class="card-body text-center">
                    <?php if ($success): ?>
                        <div class="verification-success">
                            <div class="success-icon">✅</div>
                            <h1 class="card-title">Email Verified!</h1>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="/login.php" class="btn btn-primary btn-lg">
                                    Sign In to Your Account
                                </a>
                                <a href="/" class="btn btn-outline">
                                    Continue Browsing
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="verification-error">
                            <div class="error-icon">❌</div>
                            <h1 class="card-title">Verification Failed</h1>
                            <div class="alert alert-error">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                            
                            <div class="action-buttons">
                                <a href="/resend-verification.php" class="btn btn-primary">
                                    Request New Verification Email
                                </a>
                                <a href="/register.php" class="btn btn-outline">
                                    Create New Account
                                </a>
                                <a href="/contact.php" class="btn btn-outline">
                                    Contact Support
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Help Section -->
            <div class="card mt-4">
                <div class="card-body">
                    <h2>Need Help?</h2>
                    <ul class="help-list">
                        <li>If you can't find the verification email, check your spam/junk folder</li>
                        <li>Verification links expire after 24 hours for security</li>
                        <li>You can request a new verification email if needed</li>
                        <li>Contact our support team if you continue to experience issues</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.verification-success, .verification-error {
    padding: 2rem 0;
}

.success-icon, .error-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.action-buttons {
    margin-top: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    align-items: center;
}

.action-buttons .btn {
    min-width: 250px;
}

.help-list {
    list-style: none;
    padding: 0;
}

.help-list li {
    padding: 0.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.help-list li:last-child {
    border-bottom: none;
}

.help-list li:before {
    content: "💡 ";
    margin-right: 0.5rem;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin: 1rem 0;
}

.alert-success {
    background: #d1fae5;
    border: 1px solid #a7f3d0;
    color: #065f46;
}

.alert-error {
    background: #fee2e2;
    border: 1px solid #fca5a5;
    color: #991b1b;
}

.card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.card-body {
    padding: 2rem;
}

.btn {
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-block;
    text-align: center;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border: 1px solid #3b82f6;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-outline {
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-outline:hover {
    background: #f9fafb;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.125rem;
}

@media (max-width: 768px) {
    .action-buttons .btn {
        min-width: 200px;
    }
}
</style>

<?php includeFooter(); ?>