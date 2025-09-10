<?php
/**
 * User Account Dashboard
 * E-Commerce Platform
 */

require_once __DIR__ . '/includes/init.php';

// Require user login
Session::requireLogin();

$user = new User();
$order = new Order();
$vendor = new Vendor();

$current_user = $user->find(Session::getUserId());
$recentOrders = $order->getUserOrders(Session::getUserId(), 5);
$isVendor = $vendor->findByUserId(Session::getUserId());

// Get current tab from query parameter
$currentTab = $_GET['tab'] ?? 'overview';
$validTabs = ['overview', 'orders', 'addresses', 'payments', 'security', 'preferences'];

if (!in_array($currentTab, $validTabs)) {
    $currentTab = 'overview';
}

$page_title = 'My FezaMarket Account';
includeHeader($page_title);
?>

<div class="container">
    <div class="account-header">
        <h1>Hello, <?php echo htmlspecialchars($current_user['first_name']); ?>!</h1>
        <p class="account-subtitle">Manage your account and view your activity</p>
    </div>

    <!-- Account Navigation Tabs -->
    <div class="account-navigation">
        <nav class="nav-tabs">
            <a href="?tab=overview" class="nav-tab <?php echo $currentTab === 'overview' ? 'active' : ''; ?>">
                <span class="tab-icon">🏠</span>
                Overview
            </a>
            <a href="?tab=orders" class="nav-tab <?php echo $currentTab === 'orders' ? 'active' : ''; ?>">
                <span class="tab-icon">📦</span>
                Orders
            </a>
            <a href="?tab=addresses" class="nav-tab <?php echo $currentTab === 'addresses' ? 'active' : ''; ?>">
                <span class="tab-icon">📍</span>
                Addresses
            </a>
            <a href="?tab=payments" class="nav-tab <?php echo $currentTab === 'payments' ? 'active' : ''; ?>">
                <span class="tab-icon">💳</span>
                Payment Methods
            </a>
            <a href="?tab=security" class="nav-tab <?php echo $currentTab === 'security' ? 'active' : ''; ?>">
                <span class="tab-icon">🔒</span>
                Security
            </a>
            <a href="?tab=preferences" class="nav-tab <?php echo $currentTab === 'preferences' ? 'active' : ''; ?>">
                <span class="tab-icon">⚙️</span>
                Preferences
            </a>
        </nav>
    </div>

    <!-- Tab Content -->
    <div class="account-content">
        <?php if ($currentTab === 'overview'): ?>
            <!-- Overview Tab -->
            <div class="account-grid">
                <!-- Account Summary -->
                <div class="account-section">
                    <div class="account-card">
                        <h2>Account Summary</h2>
                        <div class="account-info">
                            <div class="info-item">
                                <span class="info-label">Name:</span>
                                <span class="info-value"><?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Email:</span>
                                <span class="info-value"><?php echo htmlspecialchars($current_user['email']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Member since:</span>
                                <span class="info-value"><?php echo formatDate($current_user['created_at']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Account type:</span>
                                <span class="info-value">
                                    <?php echo ucfirst(htmlspecialchars($current_user['role'])); ?>
                                    <?php if ($isVendor): ?>
                                        <span class="badge badge-seller">Seller</span>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="account-section">
                    <div class="account-card">
                        <h2>Quick Stats</h2>
                        <div class="stats-grid">
                            <div class="stat-item">
                                <div class="stat-number"><?php echo count($recentOrders); ?></div>
                                <div class="stat-label">Recent Orders</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number"><?php echo $cart_count ?? 0; ?></div>
                                <div class="stat-label">Items in Cart</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-number">
                                    <?php 
                                    // Get wishlist count (implement if wishlist exists)
                                    echo '0'; // Placeholder
                                    ?>
                                </div>
                                <div class="stat-label">Wishlist Items</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="account-section full-width">
                <div class="account-card">
                    <h2>Quick Actions</h2>
                    <div class="quick-actions">
                        <a href="?tab=orders" class="action-link">
                            <span class="action-icon">📦</span>
                            <div>
                                <div class="action-title">Your Orders</div>
                                <div class="action-subtitle">Track and manage your orders</div>
                            </div>
                        </a>
                        <a href="/wishlist.php" class="action-link">
                            <span class="action-icon">❤️</span>
                            <div>
                                <div class="action-title">Your Wishlist</div>
                                <div class="action-subtitle">Items you want to buy later</div>
                            </div>
                        </a>
                        <a href="/cart.php" class="action-link">
                            <span class="action-icon">🛒</span>
                            <div>
                                <div class="action-title">Your Cart</div>
                                <div class="action-subtitle">Review items ready to purchase</div>
                            </div>
                        </a>
                        <?php if ($isVendor): ?>
                        <a href="/seller-center.php" class="action-link">
                            <span class="action-icon">🏪</span>
                            <div>
                                <div class="action-title">Seller Center</div>
                                <div class="action-subtitle">Manage your store</div>
                            </div>
                        </a>
                        <?php else: ?>
                        <a href="<?php echo sellerUrl('register'); ?>" class="action-link">
                            <span class="action-icon">💰</span>
                            <div>
                                <div class="action-title">Start Selling</div>
                                <div class="action-subtitle">Become a seller on FezaMarket</div>
                            </div>
                        </a>
                        <?php endif; ?>
                        <a href="?tab=security" class="action-link">
                            <span class="action-icon">🔒</span>
                            <div>
                                <div class="action-title">Security Settings</div>
                                <div class="action-subtitle">Password, 2FA, and more</div>
                            </div>
                        </a>
                        <a href="?tab=addresses" class="action-link">
                            <span class="action-icon">📍</span>
                            <div>
                                <div class="action-title">Addresses</div>
                                <div class="action-subtitle">Manage shipping addresses</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Preview -->
            <?php if (!empty($recentOrders)): ?>
            <div class="account-section full-width">
                <div class="account-card">
                    <div class="card-header">
                        <h2>Recent Orders</h2>
                        <a href="?tab=orders" class="btn btn-outline">View All Orders</a>
                    </div>
                    <div class="orders-list">
                        <?php foreach (array_slice($recentOrders, 0, 3) as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-number">Order #<?php echo $order['id']; ?></div>
                                    <div class="order-date"><?php echo formatDate($order['created_at']); ?></div>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                                <div class="order-total">
                                    <?php echo formatCurrency($order['total'] ?? 0); ?>
                                </div>
                                <div class="order-actions">
                                    <a href="/order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        <?php elseif ($currentTab === 'orders'): ?>
            <!-- Orders Tab -->
            <div class="account-section full-width">
                <div class="account-card">
                    <div class="card-header">
                        <h2>Your Orders</h2>
                    </div>
                    <?php if (!empty($recentOrders)): ?>
                        <div class="orders-list">
                            <?php foreach ($recentOrders as $order): ?>
                                <div class="order-item detailed">
                                    <div class="order-info">
                                        <div class="order-number">Order #<?php echo $order['id']; ?></div>
                                        <div class="order-date"><?php echo formatDate($order['created_at']); ?></div>
                                        <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </div>
                                    </div>
                                    <div class="order-details">
                                        <div class="order-total"><?php echo formatCurrency($order['total'] ?? 0); ?></div>
                                        <div class="order-description">
                                            <?php 
                                            // Get order items (implement if order items exist)
                                            echo 'Order details...';
                                            ?>
                                        </div>
                                    </div>
                                    <div class="order-actions">
                                        <a href="/order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View Details</a>
                                        <a href="/order.php?id=<?php echo $order['id']; ?>&action=track" class="btn btn-sm btn-outline">Track Order</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">📦</div>
                            <h3>No orders yet</h3>
                            <p>When you place your first order, it will appear here.</p>
                            <a href="/products.php" class="btn btn-primary">Start Shopping</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($currentTab === 'security'): ?>
            <!-- Security Tab -->
            <div class="account-section full-width">
                <div class="account-card">
                    <div class="card-header">
                        <h2>Security Settings</h2>
                        <p class="card-description">Keep your account safe and secure</p>
                    </div>
                    
                    <div class="security-options">
                        <div class="security-item">
                            <div class="security-info">
                                <h4>Password</h4>
                                <p>Change your account password</p>
                            </div>
                            <div class="security-action">
                                <a href="/reset-password.php" class="btn btn-outline">Change Password</a>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h4>Two-Factor Authentication</h4>
                                <p>Add an extra layer of security to your account</p>
                            </div>
                            <div class="security-action">
                                <span class="badge badge-disabled">Not Enabled</span>
                                <a href="#" class="btn btn-outline">Enable 2FA</a>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h4>Login Devices</h4>
                                <p>Manage devices that can access your account</p>
                            </div>
                            <div class="security-action">
                                <a href="#" class="btn btn-outline">Manage Devices</a>
                            </div>
                        </div>
                        
                        <div class="security-item">
                            <div class="security-info">
                                <h4>Login Alerts</h4>
                                <p>Get notified when someone logs into your account</p>
                            </div>
                            <div class="security-action">
                                <span class="badge badge-enabled">Enabled</span>
                                <a href="#" class="btn btn-outline">Configure</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Other tabs (placeholder) -->
            <div class="account-section full-width">
                <div class="account-card">
                    <div class="card-header">
                        <h2><?php echo ucfirst($currentTab); ?></h2>
                        <p class="card-description">This section is being developed.</p>
                    </div>
                    
                    <div class="empty-state">
                        <div class="empty-icon">🚧</div>
                        <h3>Coming Soon</h3>
                        <p>This feature is currently under development.</p>
                        <a href="?tab=overview" class="btn btn-primary">Back to Overview</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}

.account-header {
    margin-bottom: 2rem;
    padding: 1.5rem 0;
    border-bottom: 1px solid #e5e7eb;
}

.account-header h1 {
    font-size: 2rem;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.account-subtitle {
    color: #6b7280;
    font-size: 1.1rem;
}

/* Tab Navigation */
.account-navigation {
    margin-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
}

.nav-tabs {
    display: flex;
    gap: 0;
    overflow-x: auto;
}

.nav-tab {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    color: #6b7280;
    text-decoration: none;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    white-space: nowrap;
}

.nav-tab:hover {
    color: #374151;
    background-color: #f9fafb;
}

.nav-tab.active {
    color: #0064d2;
    border-bottom-color: #0064d2;
    background-color: #f0f9ff;
}

.tab-icon {
    font-size: 1.1rem;
}

/* Content Layout */
.account-content {
    min-height: 400px;
}

.account-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.account-section.full-width {
    grid-column: 1 / -1;
}

.account-card {
    background: white;
    border-radius: 8px;
    padding: 1.5rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    border: 1px solid #e5e7eb;
}

.account-card h2 {
    color: #1f2937;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}

.card-header {
    display: flex;
    justify-content: between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.card-header h2 {
    margin-bottom: 0;
}

.card-description {
    color: #6b7280;
    margin-top: 0.5rem;
}

/* Account Info */
.account-info {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.info-label {
    font-weight: 500;
    color: #374151;
}

.info-value {
    color: #1f2937;
}

.badge {
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge-seller {
    background-color: #dcfdf7;
    color: #065f46;
}

.badge-enabled {
    background-color: #dcfce7;
    color: #166534;
}

.badge-disabled {
    background-color: #fef2f2;
    color: #991b1b;
}

/* Stats */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    text-align: center;
}

.stat-item {
    padding: 1rem;
    background: #f9fafb;
    border-radius: 6px;
}

.stat-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #0064d2;
    margin-bottom: 0.25rem;
}

.stat-label {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.action-link {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    text-decoration: none;
    color: inherit;
    transition: all 0.2s;
}

.action-link:hover {
    border-color: #0064d2;
    box-shadow: 0 2px 4px rgba(0, 100, 210, 0.1);
    transform: translateY(-1px);
}

.action-icon {
    font-size: 1.5rem;
    flex-shrink: 0;
}

.action-title {
    font-weight: 500;
    color: #1f2937;
    margin-bottom: 0.25rem;
}

.action-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
}

/* Orders */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    transition: border-color 0.2s;
}

.order-item:hover {
    border-color: #d1d5db;
}

.order-item.detailed {
    flex-direction: column;
    align-items: stretch;
}

.order-item.detailed .order-info,
.order-item.detailed .order-details,
.order-item.detailed .order-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0.5rem;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.order-number {
    font-weight: 500;
    color: #1f2937;
}

.order-date {
    font-size: 0.875rem;
    color: #6b7280;
}

.order-status {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 500;
}

.status-pending {
    background-color: #fef3c7;
    color: #92400e;
}

.status-shipped {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-delivered {
    background-color: #dcfce7;
    color: #166534;
}

.status-cancelled {
    background-color: #fef2f2;
    color: #991b1b;
}

.order-total {
    font-weight: 600;
    color: #1f2937;
}

.order-actions {
    display: flex;
    gap: 0.5rem;
}

/* Security Settings */
.security-options {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.security-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
}

.security-info h4 {
    margin-bottom: 0.25rem;
    color: #1f2937;
}

.security-info p {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.security-action {
    display: flex;
    align-items: center;
    gap: 1rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 3rem 1rem;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.empty-state h3 {
    color: #374151;
    margin-bottom: 0.5rem;
}

.empty-state p {
    color: #6b7280;
    margin-bottom: 1.5rem;
}

/* Buttons */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 4px;
    font-size: 0.875rem;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    transition: all 0.2s;
    font-weight: 500;
}

.btn-primary {
    background: #0064d2;
    color: white;
}

.btn-primary:hover {
    background: #0056b3;
}

.btn-outline {
    background: white;
    color: #0064d2;
    border: 1px solid #0064d2;
}

.btn-outline:hover {
    background: #0064d2;
    color: white;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.8rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .account-grid {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .nav-tabs {
        padding-bottom: 0;
    }
    
    .nav-tab {
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
    }
    
    .order-item {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .security-item {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
    
    .card-header {
        flex-direction: column;
        align-items: stretch;
        gap: 1rem;
    }
}
</style>

.action-link {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f9fafb;
    border-radius: 6px;
    text-decoration: none;
    color: inherit;
    transition: background-color 0.3s ease;
}

.action-link:hover {
    background: #f3f4f6;
}

.action-icon {
    font-size: 24px;
}

.action-title {
    font-weight: 600;
    color: #1f2937;
}

.action-subtitle {
    color: #6b7280;
    font-size: 14px;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.orders-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9fafb;
    border-radius: 6px;
}

.order-info {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.order-number {
    font-weight: 600;
    color: #1f2937;
}

.order-date {
    color: #6b7280;
    font-size: 14px;
}

.order-status {
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    display: inline-block;
}

.status-pending { background: #fef3c7; color: #92400e; }
.status-processing { background: #dbeafe; color: #1e40af; }
.status-shipped { background: #dcfce7; color: #166534; }
.status-delivered { background: #dcfce7; color: #166534; }
.status-cancelled { background: #fee2e2; color: #991b1b; }

.order-total {
    font-weight: 600;
    color: #1f2937;
    font-size: 16px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}

@media (max-width: 768px) {
    .account-grid {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
}
</style>

<?php includeFooter(); ?>