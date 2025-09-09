<?php
/**
 * Forgot Password Page
 * Live Shopping E-Commerce Platform
 */

require_once __DIR__ . '/includes/init.php';

// Redirect if already logged in
if (Session::isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = sanitizeInput($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = 'Please enter your email address';
        } elseif (!validateEmail($email)) {
            $error = 'Please enter a valid email address';
        } else {
            $user = new User();
            $userData = $user->findByEmail($email);
            
            if ($userData) {
                // Generate password reset token
                $token = generatePasswordResetToken($userData['id']);
                
                // In a real application, you would send this via email
                // For demo purposes, we'll show it directly
                $resetLink = APP_URL . "/reset-password.php?token=" . $token;
                
                logSecurityEvent($userData['id'], 'password_reset_requested', 'user', $userData['id']);
                
                $success = "Password reset instructions have been sent to your email address.<br><br>
                           <strong>Demo Mode:</strong> Use this link: <a href='{$resetLink}'>Reset Password</a>";
            } else {
                // Don't reveal if email exists for security
                logSecurityEvent(null, 'password_reset_unknown_email', 'user', null, ['email' => $email]);
                $success = "If an account with that email exists, password reset instructions have been sent.";
            }
        }
    }
}

$page_title = 'Forgot Password';
includeHeader($page_title);
?>

<div class="container">
    <div class="row justify-center">
        <div class="col-6">
            <div class="card mt-4">
                <div class="card-body">
                    <h1 class="card-title text-center">Reset Your Password</h1>
                    <p class="text-center text-muted">Enter your email address and we'll send you a link to reset your password.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php else: ?>
                    
                    <form method="POST" class="validate-form">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        
                        <div class="form-group">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" id="email" name="email" class="form-control" required
                                   value="<?php echo htmlspecialchars($email); ?>"
                                   placeholder="Enter your email address">
                        </div>
                        
                        <button type="submit" class="btn btn-lg" style="width: 100%; margin-bottom: 1rem;">
                            Send Reset Link
                        </button>
                    </form>
                    
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <p><a href="/login.php">← Back to Login</a></p>
                        <p>Don't have an account? <a href="/register.php">Register here</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php includeFooter(); ?>