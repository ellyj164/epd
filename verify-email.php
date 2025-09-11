<?php
/**
 * Email Verification Page - OTP Based
 * E-Commerce Platform
 */

require_once __DIR__ . '/includes/init.php';

$email = $_GET['email'] ?? '';
$errors = [];
$success_message = '';

// Redirect to register if no email provided
if (empty($email)) {
    redirect('/register.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = $_POST['otp'] ?? '';
    
    if (empty($otp)) {
        $errors[] = 'Please enter the verification code.';
    } else {
        try {
            // Find user by email
            $user = new User();
            $userData = $user->findByEmail($email);
            
            if ($userData) {
                if ($userData['status'] === 'active' && $userData['verified_at']) {
                    $success_message = 'This email has already been verified. You can now log in.';
                } else {
                    // Look for valid OTP in email_tokens table
                    $db = Database::getInstance()->getConnection();
                    $stmt = $db->prepare("
                        SELECT * FROM email_tokens 
                        WHERE user_id = ? AND token = ? AND type = 'email_verification' 
                        AND expires_at > NOW() AND used_at IS NULL
                    ");
                    $stmt->execute([$userData['id'], $otp]);
                    $tokenData = $stmt->fetch();
                    
                    if ($tokenData) {
                        // Valid OTP - verify the user
                        $verified = $user->verifyEmail($userData['id']);
                        
                        if ($verified) {
                            // Mark token as used
                            $updateStmt = $db->prepare("
                                UPDATE email_tokens 
                                SET used_at = NOW() 
                                WHERE id = ?
                            ");
                            $updateStmt->execute([$tokenData['id']]);
                            
                            $success_message = 'Email verified successfully! You can now proceed to login.';
                            Logger::info("Email verified for user {$userData['email']}");
                            
                        } else {
                            $errors[] = 'Failed to verify your email. Please try again.';
                        }
                    } else {
                        $errors[] = 'Invalid or expired verification code. Please try again.';
                    }
                }
            } else {
                $errors[] = 'User not found.';
            }
            
        } catch (Exception $e) {
            $errors[] = 'Database error. Please try again.';
            Logger::error("Email verification error: " . $e->getMessage());
        }
    }
}

$page_title = 'Verify Email';
includeHeader($page_title);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <style>
        :root { 
            --primary-color: #0052cc; 
            --primary-hover: #0041a3; 
            --secondary-color: #f4f7f6; 
            --text-color: #333; 
            --light-text-color: #777; 
            --border-color: #ddd; 
            --error-bg: #f8d7da; 
            --error-text: #721c24; 
            --success-bg: #d4edda; 
            --success-text: #155724; 
            --footer-bg: #ffffff; 
            --footer-text: #555555; 
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            margin: 0; 
            display: flex; 
            flex-direction: column; 
            min-height: 100vh; 
            background-color: var(--secondary-color); 
        }
        main.auth-container { 
            flex-grow: 1; 
            display: flex; 
            width: 100%; 
        }
        .auth-panel { 
            flex: 1; 
            background: linear-gradient(135deg, #0052cc, #007bff); 
            color: white; 
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            justify-content: center; 
            padding: 50px; 
            text-align: center; 
        }
        .auth-panel h2 { 
            font-size: 2rem; 
            margin-bottom: 15px; 
        }
        .auth-panel p { 
            font-size: 1.1rem; 
            line-height: 1.6; 
            max-width: 350px; 
        }
        .auth-form-section { 
            flex: 1; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 50px; 
            background: #fff; 
        }
        .form-box { 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
        }
        .form-box h1 { 
            color: var(--text-color); 
            margin-bottom: 10px; 
            font-size: 2.2rem; 
        }
        .form-box .form-subtitle { 
            color: var(--light-text-color); 
            margin-bottom: 30px; 
        }
        .form-box .form-subtitle strong { 
            color: var(--text-color); 
        }
        .form-group input { 
            width: 100%; 
            padding: 12px 15px; 
            border: 1px solid var(--border-color); 
            border-radius: 5px; 
            box-sizing: border-box; 
            font-size: 1.5rem; 
            transition: border-color 0.3s; 
            text-align: center; 
            letter-spacing: 0.5em; 
        }
        .form-group input:focus { 
            outline: none; 
            border-color: var(--primary-color); 
        }
        .auth-button { 
            width: 100%; 
            padding: 14px; 
            background-color: var(--primary-color); 
            color: white; 
            border: none; 
            border-radius: 5px; 
            cursor: pointer; 
            font-size: 1.1rem; 
            font-weight: 700; 
            transition: background-color 0.3s; 
            margin-top: 20px; 
        }
        .auth-button:hover { 
            background-color: var(--primary-hover); 
        }
        .message-area { 
            margin-bottom: 20px; 
        }
        .message { 
            padding: 15px; 
            border-radius: 5px; 
            text-align: center; 
        }
        .error-message { 
            color: var(--error-text); 
            background-color: var(--error-bg); 
        }
        .success-message { 
            color: var(--success-text); 
            background-color: var(--success-bg); 
        }
        .bottom-link { 
            margin-top: 25px; 
        }
        .bottom-link a { 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 600; 
        }
        @media (max-width: 992px) { 
            .auth-panel { 
                display: none; 
            } 
            .auth-form-section { 
                padding: 30px; 
            } 
        }
    </style>
</head>
<body>
    <main class="auth-container">
        <div class="auth-panel">
            <h2>One Last Step</h2>
            <p>Confirm your email to secure your account and unlock all features.</p>
        </div>
        <div class="auth-form-section">
            <div class="form-box">
                <h1>Verify Your Email</h1>
                <p class="form-subtitle">An 8-digit code has been sent to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
                
                <div class="message-area">
                    <?php if (!empty($errors)): ?>
                        <div class="message error-message"><?php echo htmlspecialchars($errors[0]); ?></div>
                    <?php endif; ?>
                    <?php if ($success_message): ?>
                        <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$success_message): ?>
                    <form action="verify-email.php?email=<?php echo urlencode($email); ?>" method="post">
                        <div class="form-group">
                            <input type="text" name="otp" maxlength="8" required placeholder="Enter 8-digit code">
                        </div>
                        <button type="submit" class="auth-button">Verify Account</button>
                    </form>
                <?php endif; ?>
                
                <?php if ($success_message): ?>
                    <div class="bottom-link">
                        <a href="login.php">Proceed to Login</a>
                    </div>
                <?php endif; ?>
                
                <div class="bottom-link">
                    <a href="resend-verification.php?email=<?php echo urlencode($email); ?>">Resend Code</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>

<?php includeFooter(); ?>