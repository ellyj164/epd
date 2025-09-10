<?php
/**
 * Database Setup Script for E-Commerce Platform
 * Creates database, user, and runs migrations
 */

echo "=== Database Setup for E-Commerce Platform ===\n";

// Database configuration
$host = 'localhost';
$port = 3306;
$database = 'ecommerce_platform';
$username = 'duns1';
$password = 'Tumukunde';

try {
    // Connect to MySQL using debian-sys-maint user to create database and user
    echo "Connecting to MySQL server...\n";
    $rootPdo = new PDO("mysql:host={$host};port={$port}", 'debian-sys-maint', 'ZjxyniNqgIRZ060f', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Create database if it doesn't exist
    echo "Creating database '{$database}' if it doesn't exist...\n";
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Create user if it doesn't exist (MySQL 8.0+ syntax with fallback)
    echo "Creating user '{$username}' if it doesn't exist...\n";
    try {
        $rootPdo->exec("CREATE USER IF NOT EXISTS '{$username}'@'localhost' IDENTIFIED BY '{$password}'");
    } catch (PDOException $e) {
        // Fallback for older MySQL versions
        if (strpos($e->getMessage(), 'already exists') === false) {
            try {
                $rootPdo->exec("CREATE USER '{$username}'@'localhost' IDENTIFIED BY '{$password}'");
            } catch (PDOException $e2) {
                if (strpos($e2->getMessage(), 'already exists') === false) {
                    throw $e2;
                }
            }
        }
    }
    
    // Grant privileges
    echo "Granting privileges to user '{$username}'...\n";
    $rootPdo->exec("GRANT ALL PRIVILEGES ON `{$database}`.* TO '{$username}'@'localhost'");
    $rootPdo->exec("FLUSH PRIVILEGES");
    
    echo "✅ Database and user setup completed successfully!\n";
    
    // Test connection with new credentials
    echo "Testing connection with new credentials...\n";
    $testPdo = new PDO("mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "✅ Connection test successful!\n";
    
    // Check if tables exist
    $stmt = $testPdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($tables)) {
        echo "Database is empty. Tables need to be created.\n";
        echo "Run the migration script to create tables.\n";
    } else {
        echo "Found " . count($tables) . " existing tables:\n";
        foreach ($tables as $table) {
            echo "  - {$table}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n=== Setup completed successfully! ===\n";
echo "Database: {$database}\n";
echo "User: {$username}\n";
echo "Host: {$host}:{$port}\n";
?>