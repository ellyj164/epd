<?php
/**
 * Test Registration Functionality
 * Tests user registration with email verification
 */

require_once __DIR__ . '/includes/init.php';

echo "=== Testing Registration Functionality ===\n";

try {
    // Test data
    $testUser = [
        'username' => 'testuser' . time(),
        'email' => 'testuser' . time() . '@example.com',
        'first_name' => 'Test',
        'last_name' => 'User',
        'phone' => '1234567890',
        'password' => 'testpassword123'
    ];
    
    echo "Testing registration for: {$testUser['email']}\n";
    
    // Test User model registration
    $user = new User();
    $userId = $user->register($testUser);
    
    if ($userId) {
        echo "✅ User registration successful! User ID: {$userId}\n";
        
        // Check if user exists in database
        $createdUser = $user->findByEmail($testUser['email']);
        if ($createdUser) {
            echo "✅ User found in database\n";
            echo "   - Username: {$createdUser['username']}\n";
            echo "   - Email: {$createdUser['email']}\n";
            echo "   - Status: {$createdUser['status']}\n";
            echo "   - Verified: " . ($createdUser['verified_at'] ? 'Yes' : 'No') . "\n";
        } else {
            echo "❌ User not found in database\n";
        }
        
        // Check if email token was created
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM email_tokens WHERE user_id = ? AND type = 'email_verification'");
        $stmt->execute([$userId]);
        $token = $stmt->fetch();
        
        if ($token) {
            echo "✅ Email verification token created\n";
            echo "   - Token ID: {$token['id']}\n";
            echo "   - Expires: {$token['expires_at']}\n";
        } else {
            echo "❌ Email verification token not found\n";
        }
        
    } else {
        echo "❌ User registration failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Registration test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Testing Login Functionality ===\n";

try {
    // Test login with wrong password (should track login attempts)
    $user = new User();
    $result = $user->authenticate('test@example.com', 'wrongpassword');
    
    if (isset($result['error'])) {
        echo "✅ Login failed as expected: {$result['error']}\n";
        
        // Check if login attempt was recorded
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM login_attempts WHERE email = 'test@example.com' ORDER BY attempted_at DESC LIMIT 1");
        $stmt->execute();
        $attempt = $stmt->fetch();
        
        if ($attempt) {
            echo "✅ Login attempt recorded\n";
            echo "   - Email: {$attempt['email']}\n";
            echo "   - Success: " . ($attempt['success'] ? 'Yes' : 'No') . "\n";
            echo "   - Failure Reason: {$attempt['failure_reason']}\n";
        } else {
            echo "❌ Login attempt not recorded\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Login test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>