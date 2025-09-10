<?php
/**
 * Debug Registration Issues
 */

require_once __DIR__ . '/includes/init.php';

echo "=== Debug Registration Process ===\n";

try {
    $testData = [
        'username' => 'debuguser' . time(),
        'email' => 'debug' . time() . '@test.com',
        'first_name' => 'Debug',
        'last_name' => 'User',
        'password' => 'password123'
    ];
    
    echo "Test data: " . json_encode($testData, JSON_PRETTY_PRINT) . "\n";
    
    $user = new User();
    $db = Database::getInstance()->getConnection();
    
    echo "Database connection: OK\n";
    
    // Start transaction manually for debugging
    $db->beginTransaction();
    echo "Transaction started\n";
    
    // Prepare user data with pending status
    $userData = [
        'username' => $testData['username'],
        'email' => $testData['email'],
        'pass_hash' => hashPassword($testData['password']),
        'first_name' => $testData['first_name'],
        'last_name' => $testData['last_name'],
        'phone' => $testData['phone'] ?? null,
        'role' => 'customer',
        'status' => 'pending', // User starts as pending
        'verified_at' => null,  // Not verified yet
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    echo "Prepared user data: " . json_encode($userData, JSON_PRETTY_PRINT) . "\n";
    
    // Insert user using BaseModel create method
    $userId = $user->create($userData);
    echo "User create result: " . var_export($userId, true) . "\n";
    
    if ($userId) {
        $db->commit();
        echo "Transaction committed\n";
        
        // Test EmailTokenManager
        echo "Testing EmailTokenManager...\n";
        $token = EmailTokenManager::generateToken($userId, 'email_verification', 1440);
        if ($token) {
            echo "Token generated: " . substr($token, 0, 10) . "...\n";
        } else {
            echo "Token generation failed\n";
        }
        
        // Test EmailService
        echo "Testing EmailService...\n";
        $emailService = EmailService::getInstance();
        
        // Try to send email (this might fail if SMTP not configured, but should not crash)
        $emailResult = $emailService->send(
            $userData['email'],
            'Email Verification Required',
            'email_verification',
            [
                'user' => $userData,
                'token' => $token,
                'verification_url' => 'http://localhost/verify-email.php?token=' . $token
            ]
        );
        
        echo "Email send result: " . var_export($emailResult, true) . "\n";
        
    } else {
        $db->rollBack();
        echo "Transaction rolled back due to user creation failure\n";
    }
    
} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
        $db->rollBack();
    }
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>