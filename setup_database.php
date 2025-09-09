#!/usr/bin/env php
<?php
/**
 * Enhanced Database Setup Script for Live Shopping Platform
 * Supports both SQLite (development) and MySQL/MariaDB (production)
 * E-Commerce Platform with Live Shopping Features
 */

require_once __DIR__ . '/config/config.php';

echo "=== Live Shopping Platform Database Setup ===\n\n";

function executeSchemaFile($pdo, $schemaFile, $description = "") {
    echo "Setting up $description...\n";
    
    if (!file_exists($schemaFile)) {
        echo "⚠️  Schema file not found: $schemaFile\n";
        return 0;
    }
    
    $schema = file_get_contents($schemaFile);
    
    // Handle SQLite vs MySQL differences
    if (USE_SQLITE) {
        // Convert MySQL AUTO_INCREMENT to SQLite AUTOINCREMENT
        $schema = str_replace('AUTO_INCREMENT', 'AUTOINCREMENT', $schema);
        // Convert MySQL ENUM to SQLite TEXT 
        $schema = preg_replace('/ENUM\([^)]+\)/', 'TEXT', $schema);
        // Convert MySQL INT to SQLite INTEGER for primary keys
        $schema = preg_replace('/\bINT AUTO/', 'INTEGER AUTO/', $schema);
        $schema = preg_replace('/\bINT\b(?![^(]*\))/i', 'INTEGER', $schema);
        // Remove MySQL-specific syntax
        $schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
        $schema = preg_replace('/USE.*?;/i', '', $schema);
        $schema = preg_replace('/CHARACTER SET[^;]*?;/i', ';', $schema);
        $schema = preg_replace('/COLLATE[^;]*?;/i', ';', $schema);
        $schema = preg_replace('/ON UPDATE CURRENT_TIMESTAMP/i', '', $schema);
        // Remove MySQL KEY syntax
        $schema = preg_replace('/,\s*UNIQUE KEY[^,)]+/i', '', $schema);
        $schema = preg_replace('/,\s*INDEX[^,)]+/i', '', $schema);
        $schema = preg_replace('/,\s*KEY[^,)]+/i', '', $schema);
        // Fix timestamp defaults for SQLite
        $schema = str_replace('DEFAULT CURRENT_TIMESTAMP', "DEFAULT (datetime('now'))", $schema);
    } else {
        // Remove database creation commands since we handle it separately
        $schema = preg_replace('/CREATE DATABASE.*?;/', '', $schema);
        $schema = preg_replace('/USE.*?;/', '', $schema);
    }
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $successCount = 0;
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^\s*(--|#)/', $statement)) {
            try {
                echo "Executing: " . substr($statement, 0, 100) . "...\n";
                $pdo->exec($statement . ';');
                $successCount++;
            } catch (PDOException $e) {
                // Skip if table already exists
                if (strpos($e->getMessage(), 'already exists') === false && 
                    strpos($e->getMessage(), 'duplicate column') === false) {
                    echo "Warning: " . $e->getMessage() . "\nSQL: " . substr($statement, 0, 200) . "\n";
                }
            }
        }
    }
    
    echo "✓ Executed $successCount SQL statements for $description\n";
    return $successCount;
}

try {
    if (USE_SQLITE) {
        echo "Using SQLite database for development\n";
        
        // Ensure database directory exists
        $dbDir = dirname(SQLITE_PATH);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        
        // Create SQLite connection
        $pdo = new PDO("sqlite:" . SQLITE_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON;');
        
        echo "✓ Connected to SQLite database\n";
    } else {
        echo "Using MySQL/MariaDB database for production\n";
        
        // Check if MySQL extension is available
        if (!extension_loaded('pdo_mysql')) {
            die("ERROR: PDO MySQL extension is not installed. Please install php-mysql package.\n");
        }
        
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
        echo "✓ Using database '" . DB_NAME . "'\n";
    }
    
    echo "\n";
    
    // Execute base schema
    if (USE_SQLITE) {
        $baseCount = executeSchemaFile($pdo, __DIR__ . '/database/sqlite_schema.sql', 'SQLite live shopping schema');
    } else {
        $baseCount = executeSchemaFile($pdo, __DIR__ . '/database/schema.sql', 'base e-commerce schema');
        // Execute live shopping extensions for MySQL
        $liveCount = executeSchemaFile($pdo, __DIR__ . '/database/live_shopping_schema.sql', 'live shopping features');
    }
    
    echo "\n";
    
    // Verify tables were created
    if (USE_SQLITE) {
        $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name != 'sqlite_sequence'");
    } else {
        $stmt = $pdo->query("SHOW TABLES");
    }
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "✓ Created " . count($tables) . " tables\n";
    
    // Check if admin user exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $adminCount = $stmt->fetchColumn();
        
        if ($adminCount == 0) {
            // Create default admin user with ARGON2ID password
            $adminPassword = 'admin123';
            $hashedPassword = password_hash($adminPassword, PASSWORD_ARGON2ID);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, role, email_verified, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'admin',
                'admin@ecommerce.com',
                $hashedPassword,
                'Admin',
                'User',
                'admin',
                1,
                date('Y-m-d H:i:s')
            ]);
            echo "✓ Created default admin user\n";
        }
        
        // Create demo vendor for live streaming
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'vendor'");
        $stmt->execute();
        $vendorCount = $stmt->fetchColumn();
        
        if ($vendorCount == 0) {
            // Create demo vendor user
            $vendorPassword = 'vendor123';
            $hashedPassword = password_hash($vendorPassword, PASSWORD_ARGON2ID);
            
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password_hash, first_name, last_name, role, email_verified, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                'demovendor',
                'vendor@ecommerce.com',
                $hashedPassword,
                'Demo',
                'Vendor',
                'vendor',
                1,
                date('Y-m-d H:i:s')
            ]);
            $vendorUserId = $pdo->lastInsertId();
            
            // Create vendor profile
            $stmt = $pdo->prepare("
                INSERT INTO vendors (user_id, business_name, business_description, status, created_at) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $vendorUserId,
                'Demo Live Shop',
                'Demo vendor for live shopping demonstrations',
                'approved',
                date('Y-m-d H:i:s')
            ]);
            
            echo "✓ Created demo vendor user\n";
        }
        
    } catch (PDOException $e) {
        echo "⚠️  Could not check/create default users: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== DEFAULT LOGIN CREDENTIALS ===\n";
    echo "Admin User:\n";
    echo "  Email: admin@ecommerce.com\n";
    echo "  Password: admin123\n";
    echo "  Role: admin\n\n";
    echo "Demo Vendor:\n";
    echo "  Email: vendor@ecommerce.com\n";
    echo "  Password: vendor123\n";
    echo "  Role: vendor\n\n";
    echo "⚠️  IMPORTANT: Change these default passwords after first login!\n\n";
    
    echo "=== Database Setup Complete ===\n";
    echo "✓ Your live shopping platform database is ready!\n";
    echo "✓ Database type: " . (USE_SQLITE ? 'SQLite (development)' : 'MySQL/MariaDB (production)') . "\n";
    echo "✓ User authentication with ARGON2ID hashing enabled\n";
    echo "✓ Live streaming features available\n";
    echo "✓ Real-time chat and engagement features ready\n";
    echo "✓ You can now start testing the application!\n\n";
    
} catch (PDOException $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n\n";
    echo "Troubleshooting tips:\n";
    if (USE_SQLITE) {
        echo "1. Check if the database directory is writable\n";
        echo "2. Ensure SQLite extension is installed (php-sqlite3)\n";
    } else {
        echo "1. Make sure MySQL/MariaDB is running\n";
        echo "2. Check database credentials in config/config.php\n";
        echo "3. Ensure the database user has CREATE privileges\n";
        echo "4. Verify PHP PDO MySQL extension is installed\n";
    }
    exit(1);
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>