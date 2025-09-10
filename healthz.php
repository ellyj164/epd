<?php
/**
 * Health Check Endpoint
 * Returns basic application health status
 */

require_once __DIR__ . '/includes/init.php';

header('Content-Type: application/json');

try {
    // Test database connection
    $db = Database::getInstance()->getConnection();
    $db->query("SELECT 1");
    
    $response = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => APP_VERSION,
        'environment' => APP_ENV,
        'checks' => [
            'database' => 'ok'
        ]
    ];
    
    http_response_code(200);
    
} catch (Exception $e) {
    $response = [
        'status' => 'unhealthy',
        'timestamp' => date('c'),
        'version' => APP_VERSION,
        'environment' => APP_ENV,
        'error' => DEBUG_MODE ? $e->getMessage() : 'Database connectivity issue',
        'checks' => [
            'database' => 'error'
        ]
    ];
    
    http_response_code(503);
}

echo json_encode($response, JSON_PRETTY_PRINT);
?>