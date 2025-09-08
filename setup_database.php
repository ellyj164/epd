#!/usr/bin/env php
<?php
/**
 * Database Setup Script for MySQL/MariaDB
 * E-Commerce Platform
 * 
 * This script creates the MySQL/MariaDB database and sets up the initial schema.
 * Make sure MySQL/MariaDB is installed and running before executing this script.
 */

require_once __DIR__ . '/config/config.php';

echo "=== E-Commerce Platform Database Setup ===\n\n";

// Check if MySQL extension is available
if (!extension_loaded('pdo_mysql')) {
    die("ERROR: PDO MySQL extension is not installed. Please install php-mysql package.\n");
}

try {
    // First, connect without specifying database to create it
    $dsn = "mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✓ Connected to MySQL/MariaDB server\n";
    
    // Create database if it doesn't exist
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "✓ Database '" . DB_NAME . "' created/verified\n";
    
    // Switch to the database
    $pdo->exec("USE `" . DB_NAME . "`");
    echo "✓ Using database '" . DB_NAME . "'\n\n";
    
    // Read and execute the schema
    echo "Setting up database schema...\n";
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Remove database creation commands since we already handled it
    $schema = preg_replace('/CREATE DATABASE.*?;/', '', $schema);
    $schema = preg_replace('/USE.*?;/', '', $schema);
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $successCount = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*(--|#)/', $statement)) {
            try {
                $pdo->exec($statement);
                $successCount++;
            } catch (PDOException $e) {
                // Skip if table already exists
                if (strpos($e->getMessage(), 'already exists') === false) {
                    echo "Warning: " . $e->getMessage() . "\n";
                }
            }
        }
    }
    
    echo "✓ Executed $successCount SQL statements\n\n";
    
    // Verify tables were created
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Created " . count($tables) . " tables: " . implode(', ', $tables) . "\n\n";
    
    // Check if admin user exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    $adminCount = $stmt->fetchColumn();
    
    if ($adminCount > 0) {
        echo "=== DEFAULT ADMIN LOGIN CREDENTIALS ===\n";
        echo "Email: admin@ecommerce.com\n";
        echo "Password: admin123\n";
        echo "Role: admin\n\n";
        echo "⚠️  IMPORTANT: Please change the default admin password after first login!\n\n";
    }
    
    echo "=== Database Setup Complete ===\n";
    echo "✓ Your e-commerce platform database is ready!\n";
    echo "✓ User registration and login should work properly now.\n";
    echo "✓ You can now start the application server.\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting tips:\n";
    echo "1. Make sure MySQL/MariaDB is running\n";
    echo "2. Check database credentials in config/config.php\n";
    echo "3. Ensure the database user has CREATE privileges\n";
    echo "4. Verify PHP PDO MySQL extension is installed\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>