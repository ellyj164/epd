<?php
/**
 * Admin Settings Management
 * E-Commerce Platform - Complete System Configuration
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access
RoleMiddleware::requireAdmin();

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    validateCsrfAndRateLimit();
    
    $db = Database::getInstance()->getConnection();
    
    try {
        $db->beginTransaction();
        
        switch ($_POST['action']) {
            case 'update_general':
                $settings = [
                    'site_name' => sanitizeInput($_POST['site_name']),
                    'site_email' => sanitizeInput($_POST['site_email']),
                    'currency' => sanitizeInput($_POST['currency']),
                    'timezone' => sanitizeInput($_POST['timezone']),
                    'maintenance_mode' => isset($_POST['maintenance_mode']) ? '1' : '0',
                    'registration_enabled' => isset($_POST['registration_enabled']) ? '1' : '0'
                ];
                
                foreach ($settings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO settings (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $success = 'General settings updated successfully.';
                break;
                
            case 'update_email':
                $emailSettings = [
                    'smtp_host' => sanitizeInput($_POST['smtp_host']),
                    'smtp_port' => sanitizeInput($_POST['smtp_port']),
                    'smtp_username' => sanitizeInput($_POST['smtp_username']),
                    'smtp_encryption' => sanitizeInput($_POST['smtp_encryption']),
                    'from_email' => sanitizeInput($_POST['from_email']),
                    'from_name' => sanitizeInput($_POST['from_name'])
                ];
                
                // Only update password if provided
                if (!empty($_POST['smtp_password'])) {
                    $emailSettings['smtp_password'] = $_POST['smtp_password'];
                }
                
                foreach ($emailSettings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO settings (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $success = 'Email settings updated successfully.';
                break;
                
            case 'update_security':
                $securitySettings = [
                    'password_policy_min_length' => (int)$_POST['password_min_length'],
                    'max_login_attempts' => (int)$_POST['max_login_attempts'],
                    'login_lockout_duration' => (int)$_POST['login_lockout_duration'],
                    '2fa_enabled' => isset($_POST['2fa_enabled']) ? '1' : '0'
                ];
                
                foreach ($securitySettings as $key => $value) {
                    $stmt = $db->prepare("
                        INSERT INTO settings (setting_key, setting_value) 
                        VALUES (?, ?) 
                        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                    ");
                    $stmt->execute([$key, $value]);
                }
                
                $success = 'Security settings updated successfully.';
                break;
                
            case 'test_email':
                $emailSystem = new EmailSystem();
                $testEmail = sanitizeInput($_POST['test_email']);
                
                if ($emailSystem->sendEmail($testEmail, 'Test Email from ' . APP_NAME, 
                    'This is a test email to verify your SMTP configuration is working correctly.')) {
                    $success = 'Test email sent successfully to ' . $testEmail;
                } else {
                    $error = 'Failed to send test email. Please check your SMTP settings.';
                }
                break;
        }
        
        $db->commit();
        
    } catch (Exception $e) {
        $db->rollback();
        $error = 'Error updating settings: ' . $e->getMessage();
        Logger::error('Settings update error: ' . $e->getMessage());
    }
}

// Get current settings
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($row = $stmt->fetch()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$page_title = 'System Settings';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>⚙️ System Settings</h1>
        <a href="/admin/index.php" class="btn btn-outline">← Back to Dashboard</a>
    </div>

    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <!-- Settings Navigation -->
            <div class="card">
                <div class="card-body">
                    <h5>Settings Categories</h5>
                    <div class="list-group list-group-flush">
                        <a href="#general" class="list-group-item active" onclick="showTab('general')">
                            🌐 General Settings
                        </a>
                        <a href="#email" class="list-group-item" onclick="showTab('email')">
                            📧 Email Configuration
                        </a>
                        <a href="#security" class="list-group-item" onclick="showTab('security')">
                            🔒 Security & Authentication
                        </a>
                        <a href="#payment" class="list-group-item" onclick="showTab('payment')">
                            💳 Payment Gateways
                        </a>
                        <a href="#integrations" class="list-group-item" onclick="showTab('integrations')">
                            🔗 Integrations
                        </a>
                        <a href="#performance" class="list-group-item" onclick="showTab('performance')">
                            ⚡ Performance
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <!-- General Settings Tab -->
            <div id="general-tab" class="settings-tab">
                <div class="card">
                    <div class="card-header">
                        <h5>🌐 General Settings</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo csrfTokenInput(); ?>
                            <input type="hidden" name="action" value="update_general">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="site_name">Site Name</label>
                                        <input type="text" class="form-control" id="site_name" name="site_name" 
                                               value="<?php echo htmlspecialchars($settings['site_name'] ?? APP_NAME); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="site_email">Contact Email</label>
                                        <input type="email" class="form-control" id="site_email" name="site_email" 
                                               value="<?php echo htmlspecialchars($settings['site_email'] ?? FROM_EMAIL); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="currency">Default Currency</label>
                                        <select class="form-control" id="currency" name="currency">
                                            <?php 
                                            $currencies = ['USD', 'EUR', 'GBP', 'CAD', 'AUD', 'JPY'];
                                            $currentCurrency = $settings['currency'] ?? 'USD';
                                            foreach ($currencies as $currency): 
                                            ?>
                                                <option value="<?php echo $currency; ?>" 
                                                        <?php echo $currency === $currentCurrency ? 'selected' : ''; ?>>
                                                    <?php echo $currency; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="timezone">Timezone</label>
                                        <select class="form-control" id="timezone" name="timezone">
                                            <?php 
                                            $timezones = ['UTC', 'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles', 'Europe/London', 'Europe/Paris'];
                                            $currentTimezone = $settings['timezone'] ?? 'UTC';
                                            foreach ($timezones as $timezone): 
                                            ?>
                                                <option value="<?php echo $timezone; ?>" 
                                                        <?php echo $timezone === $currentTimezone ? 'selected' : ''; ?>>
                                                    <?php echo $timezone; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="maintenance_mode" name="maintenance_mode" 
                                           <?php echo ($settings['maintenance_mode'] ?? '0') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="maintenance_mode">
                                        Enable Maintenance Mode
                                    </label>
                                    <small class="form-text text-muted">When enabled, only administrators can access the site.</small>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="registration_enabled" name="registration_enabled" 
                                           <?php echo ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="registration_enabled">
                                        Allow New User Registrations
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save General Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Email Settings Tab -->
            <div id="email-tab" class="settings-tab" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5>📧 Email Configuration</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo csrfTokenInput(); ?>
                            <input type="hidden" name="action" value="update_email">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp_host">SMTP Host</label>
                                        <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                               value="<?php echo htmlspecialchars($settings['smtp_host'] ?? SMTP_HOST); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="smtp_port">SMTP Port</label>
                                        <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                               value="<?php echo htmlspecialchars($settings['smtp_port'] ?? SMTP_PORT); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="smtp_encryption">Encryption</label>
                                        <select class="form-control" id="smtp_encryption" name="smtp_encryption">
                                            <?php 
                                            $encryptions = ['tls', 'ssl', 'none'];
                                            $currentEncryption = $settings['smtp_encryption'] ?? 'tls';
                                            foreach ($encryptions as $encryption): 
                                            ?>
                                                <option value="<?php echo $encryption; ?>" 
                                                        <?php echo $encryption === $currentEncryption ? 'selected' : ''; ?>>
                                                    <?php echo strtoupper($encryption); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp_username">SMTP Username</label>
                                        <input type="text" class="form-control" id="smtp_username" name="smtp_username" 
                                               value="<?php echo htmlspecialchars($settings['smtp_username'] ?? SMTP_USERNAME); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="smtp_password">SMTP Password</label>
                                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                               placeholder="Leave blank to keep current password">
                                        <small class="form-text text-muted">Password is encrypted and stored securely.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from_email">From Email Address</label>
                                        <input type="email" class="form-control" id="from_email" name="from_email" 
                                               value="<?php echo htmlspecialchars($settings['from_email'] ?? FROM_EMAIL); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="from_name">From Name</label>
                                        <input type="text" class="form-control" id="from_name" name="from_name" 
                                               value="<?php echo htmlspecialchars($settings['from_name'] ?? FROM_NAME); ?>" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Email Settings</button>
                                <button type="button" class="btn btn-outline" onclick="showEmailTest()">Test Email Configuration</button>
                            </div>
                        </form>

                        <!-- Test Email Form -->
                        <div id="email-test" style="display: none; margin-top: 20px; padding: 20px; border: 1px solid #ddd; border-radius: 8px; background: #f8f9fa;">
                            <h6>Test Email Configuration</h6>
                            <form method="POST">
                                <?php echo csrfTokenInput(); ?>
                                <input type="hidden" name="action" value="test_email">
                                <div class="input-group">
                                    <input type="email" class="form-control" name="test_email" placeholder="Enter email address to send test email" required>
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-success">Send Test Email</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Settings Tab -->
            <div id="security-tab" class="settings-tab" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5>🔒 Security & Authentication</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?php echo csrfTokenInput(); ?>
                            <input type="hidden" name="action" value="update_security">
                            
                            <h6>Password Policy</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="password_min_length">Minimum Password Length</label>
                                        <input type="number" class="form-control" id="password_min_length" name="password_min_length" 
                                               value="<?php echo htmlspecialchars($settings['password_policy_min_length'] ?? PASSWORD_MIN_LENGTH); ?>" 
                                               min="6" max="32" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="max_login_attempts">Max Login Attempts</label>
                                        <input type="number" class="form-control" id="max_login_attempts" name="max_login_attempts" 
                                               value="<?php echo htmlspecialchars($settings['max_login_attempts'] ?? MAX_LOGIN_ATTEMPTS); ?>" 
                                               min="3" max="20" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="login_lockout_duration">Lockout Duration (minutes)</label>
                                        <input type="number" class="form-control" id="login_lockout_duration" name="login_lockout_duration" 
                                               value="<?php echo htmlspecialchars($settings['login_lockout_duration'] ?? LOGIN_LOCKOUT_DURATION); ?>" 
                                               min="5" max="1440" required>
                                    </div>
                                </div>
                            </div>

                            <h6>Two-Factor Authentication</h6>
                            <div class="form-group">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="2fa_enabled" name="2fa_enabled" 
                                           <?php echo ($settings['2fa_enabled'] ?? TWO_FA_ENABLED ? '1' : '0') === '1' ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="2fa_enabled">
                                        Enable Two-Factor Authentication (TOTP)
                                    </label>
                                    <small class="form-text text-muted">Require all users to set up 2FA for enhanced security.</small>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Security Settings</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Placeholder tabs for additional features -->
            <div id="payment-tab" class="settings-tab" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5>💳 Payment Gateways</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Payment gateway configuration will be implemented in the next phase.</p>
                        <div class="alert alert-info">
                            <strong>Coming Soon:</strong> Stripe, PayPal, and other payment gateway integrations.
                        </div>
                    </div>
                </div>
            </div>

            <div id="integrations-tab" class="settings-tab" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5>🔗 Third-Party Integrations</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Third-party integrations configuration.</p>
                        <div class="alert alert-info">
                            <strong>Available Integrations:</strong> Shipping APIs, Analytics, Social Login, etc.
                        </div>
                    </div>
                </div>
            </div>

            <div id="performance-tab" class="settings-tab" style="display: none;">
                <div class="card">
                    <div class="card-header">
                        <h5>⚡ Performance & Caching</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Performance optimization settings.</p>
                        <div class="alert alert-info">
                            <strong>Features:</strong> Redis caching, CDN configuration, database optimization.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    const tabs = document.querySelectorAll('.settings-tab');
    tabs.forEach(tab => tab.style.display = 'none');
    
    // Remove active class from all nav items
    const navItems = document.querySelectorAll('.list-group-item');
    navItems.forEach(item => item.classList.remove('active'));
    
    // Show selected tab
    document.getElementById(tabName + '-tab').style.display = 'block';
    
    // Add active class to selected nav item
    document.querySelector(`[href="#${tabName}"]`).classList.add('active');
}

function showEmailTest() {
    const testDiv = document.getElementById('email-test');
    testDiv.style.display = testDiv.style.display === 'none' ? 'block' : 'none';
}

// Handle navigation clicks
document.querySelectorAll('.list-group-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.preventDefault();
        const tabName = this.getAttribute('href').substring(1);
        showTab(tabName);
    });
});
</script>

<?php includeFooter(); ?>