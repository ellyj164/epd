<?php
/**
 * Database Setup Script
 * E-Commerce Platform
 */

require_once __DIR__ . '/includes/init.php';

try {
    // Read and execute the database schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Split into individual statements
    $statements = array_filter(array_map('trim', explode(';', $schema)));
    
    $db = Database::getInstance()->getConnection();
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->exec($statement);
        }
    }
    
    echo "Database setup completed successfully!\n";
    echo "Default admin user created:\n";
    echo "Email: admin@ecommerce.com\n";
    echo "Password: admin123\n";
    
} catch (Exception $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
?>