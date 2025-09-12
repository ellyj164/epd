<?php
/**
 * Admin Debug Page
 * E-Commerce Platform - Admin Panel Enhancement
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access
RoleMiddleware::requireAdmin();

$page_title = 'Admin Debug';
includeHeader($page_title);

// Collect debug information
$debugInfo = [
    'system' => [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'memory_limit' => ini_get('memory_limit'),
        'max_execution_time' => ini_get('max_execution_time'),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'post_max_size' => ini_get('post_max_size'),
        'timezone' => date_default_timezone_get(),
        'current_time' => date('Y-m-d H:i:s'),
    ],
    'app' => [
        'name' => APP_NAME,
        'version' => APP_VERSION,
        'environment' => APP_ENV,
        'debug_mode' => APP_DEBUG ? 'Enabled' : 'Disabled',
        'app_url' => APP_URL,
    ],
    'database' => [],
    'error_log' => [],
    'health_checks' => []
];

// Database information
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT VERSION() as version");
    $version = $stmt->fetch();
    $debugInfo['database']['version'] = $version['version'] ?? 'Unknown';
    $debugInfo['database']['status'] = 'Connected';
    
    // Count tables
    $stmt = $db->query("SHOW TABLES");
    $debugInfo['database']['table_count'] = $stmt->rowCount();
    
    // Database size
    $stmt = $db->query("
        SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'size_mb'
        FROM information_schema.tables 
        WHERE table_schema = DATABASE()
    ");
    $size = $stmt->fetch();
    $debugInfo['database']['size_mb'] = $size['size_mb'] ?? 'Unknown';
    
} catch (Exception $e) {
    $debugInfo['database']['status'] = 'Error: ' . $e->getMessage();
}

// Recent error log entries
$logFile = ERROR_LOG_PATH . 'app.log';
if (file_exists($logFile)) {
    $logLines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $debugInfo['error_log'] = array_slice(array_reverse($logLines), 0, 20);
} else {
    $debugInfo['error_log'] = ['No error log file found'];
}

// Health checks
$debugInfo['health_checks'] = performHealthCheck();

// Session information
$sessionInfo = [
    'user_id' => Session::getUserId(),
    'user_role' => Session::getUserRole(),
    'session_id' => session_id(),
    'session_status' => session_status(),
];
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>🔧 Admin Debug Dashboard</h1>
        <div class="d-flex gap-2">
            <a href="/admin/" class="btn btn-outline">← Back to Dashboard</a>
            <button onclick="location.reload()" class="btn btn-primary">🔄 Refresh</button>
        </div>
    </div>
    
    <!-- System Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>🖥️ System Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($debugInfo['system'] as $key => $value): ?>
                    <div class="col-md-4 mb-2">
                        <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong><br>
                        <code><?php echo htmlspecialchars($value); ?></code>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Application Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>🚀 Application Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($debugInfo['app'] as $key => $value): ?>
                    <div class="col-md-4 mb-2">
                        <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong><br>
                        <code><?php echo htmlspecialchars($value); ?></code>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Database Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>🗄️ Database Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($debugInfo['database'] as $key => $value): ?>
                    <div class="col-md-4 mb-2">
                        <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong><br>
                        <code class="<?php echo $key === 'status' && strpos($value, 'Error') !== false ? 'text-danger' : ''; ?>">
                            <?php echo htmlspecialchars($value); ?>
                        </code>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Session Information -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>👤 Session Information</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($sessionInfo as $key => $value): ?>
                    <div class="col-md-4 mb-2">
                        <strong><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</strong><br>
                        <code><?php echo htmlspecialchars($value ?? 'null'); ?></code>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Health Checks -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>❤️ Health Checks</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <?php foreach ($debugInfo['health_checks'] as $check => $result): ?>
                    <div class="col-md-4 mb-3">
                        <div class="d-flex align-center">
                            <span class="status-indicator <?php echo $result['status'] === 'ok' ? 'text-success' : ($result['status'] === 'error' ? 'text-danger' : 'text-warning'); ?>">
                                <?php echo $result['status'] === 'ok' ? '✅' : ($result['status'] === 'error' ? '❌' : '⚠️'); ?>
                            </span>
                            <div class="ml-2">
                                <strong><?php echo ucwords(str_replace('_', ' ', $check)); ?></strong><br>
                                <small><?php echo htmlspecialchars($result['message']); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Recent Error Log -->
    <div class="card mb-4">
        <div class="card-header">
            <h3>📝 Recent Error Log (Last 20 entries)</h3>
        </div>
        <div class="card-body">
            <?php if (empty($debugInfo['error_log']) || $debugInfo['error_log'][0] === 'No error log file found'): ?>
                <p class="text-muted">No recent errors found.</p>
            <?php else: ?>
                <div style="max-height: 400px; overflow-y: auto; background: #f8f9fa; padding: 15px; border-radius: 4px;">
                    <pre style="font-size: 12px; margin: 0;"><?php echo htmlspecialchars(implode("\n", $debugInfo['error_log'])); ?></pre>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Debug Actions -->
    <div class="card">
        <div class="card-header">
            <h3>🛠️ Debug Actions</h3>
        </div>
        <div class="card-body">
            <div class="d-flex gap-2 flex-wrap">
                <button onclick="if(confirm('Clear error log?')) { window.location.href='?action=clear_log'; }" class="btn btn-warning">
                    🗑️ Clear Error Log
                </button>
                <button onclick="if(confirm('Run health checks?')) { window.location.href='?action=health_check'; }" class="btn btn-primary">
                    ❤️ Run Health Checks
                </button>
                <a href="/healthz" class="btn btn-outline" target="_blank">🔍 View Health Endpoint</a>
                <a href="/readyz" class="btn btn-outline" target="_blank">⚡ View Ready Endpoint</a>
            </div>
        </div>
    </div>
</div>

<?php
// Handle debug actions
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'clear_log':
            $logFile = ERROR_LOG_PATH . 'app.log';
            if (file_exists($logFile)) {
                file_put_contents($logFile, '');
                Logger::info("Error log cleared by admin user " . Session::getUserId());
            }
            echo "<script>alert('Error log cleared!'); window.location.href='/admin/debug.php';</script>";
            break;
            
        case 'health_check':
            $checks = performHealthCheck();
            Logger::info("Health checks performed by admin user " . Session::getUserId());
            echo "<script>alert('Health checks completed!'); window.location.href='/admin/debug.php';</script>";
            break;
    }
}

includeFooter();
?>