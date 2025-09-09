<?php
/**
 * Password Reset Page
 * Live Shopping E-Commerce Platform
 */

require_once __DIR__ . '/includes/init.php';

// Redirect if already logged in
if (Session::isLoggedIn()) {
    redirect('/');
}

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    redirect('/forgot-password.php');
}

// Verify token exists and is valid
$tokenData = verifyPasswordResetToken($token);
if (!$tokenData) {
    $error = 'Invalid or expired reset token. Please request a new password reset.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
    // CSRF protection
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!verifyCsrfToken($csrfToken)) {
        $error = 'Invalid request. Please try again.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($password) || empty($confirmPassword)) {
            $error = 'Please fill in all fields';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long';
        } elseif ($password !== $confirmPassword) {
            $error = 'Passwords do not match';
        } else {
            // Reset password
            if (usePasswordResetToken($token, $password)) {
                logSecurityEvent($tokenData['user_id'], 'password_reset_completed', 'user', $tokenData['user_id']);
                $success = 'Your password has been reset successfully. You can now <a href="/login.php">login</a> with your new password.';
            } else {
                $error = 'Failed to reset password. Please try again.';
            }
        }
    }
}

$page_title = 'Reset Password';
includeHeader($page_title);
?>

<div class="container">
    <div class="row justify-center">
        <div class="col-6">
            <div class="card mt-4">
                <div class="card-body">
                    <h1 class="card-title text-center">Create New Password</h1>
                    <p class="text-center text-muted">Enter your new password below.</p>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error"><?php echo $error; ?></div>
                        <?php if (strpos($error, 'Invalid or expired') !== false): ?>
                            <div class="text-center">
                                <a href="/forgot-password.php" class="btn">Request New Reset Link</a>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                    <?php elseif (!$error || strpos($error, 'Invalid or expired') === false): ?>
                    
                    <form method="POST" class="validate-form">
                        <input type="hidden" name="csrf_token" value="<?php echo csrfToken(); ?>">
                        
                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <input type="password" id="password" name="password" class="form-control" required
                                   minlength="8" placeholder="Enter your new password">
                            <small class="form-text">Password must be at least 8 characters long</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required
                                   minlength="8" placeholder="Confirm your new password">
                        </div>
                        
                        <button type="submit" class="btn btn-lg" style="width: 100%; margin-bottom: 1rem;">
                            Update Password
                        </button>
                    </form>
                    
                    <?php endif; ?>
                    
                    <div class="text-center">
                        <p><a href="/login.php">← Back to Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Client-side password confirmation
document.addEventListener('DOMContentLoaded', function() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    function validatePassword() {
        if (password.value && confirmPassword.value && password.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity('Passwords do not match');
        } else {
            confirmPassword.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validatePassword);
});
</script>

<?php includeFooter(); ?>