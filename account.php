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
$isVendor = $vendor->getByUserId(Session::getUserId());

$page_title = 'My FezaMarket Account';
includeHeader($page_title);
?>

<div class="container">
    <div class="account-header">
        <h1>Hello, <?php echo htmlspecialchars($current_user['first_name']); ?>!</h1>
        <p class="account-subtitle">Manage your account and view your activity</p>
    </div>

    <div class="account-grid">
        <!-- Account Overview -->
        <div class="account-section">
            <div class="account-card">
                <h2>Account Information</h2>
                <div class="account-info">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($current_user['first_name'] . ' ' . $current_user['last_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($current_user['email']); ?></p>
                    <p><strong>Member since:</strong> <?php echo formatDate($current_user['created_at']); ?></p>
                    <p><strong>Role:</strong> <?php echo ucfirst(htmlspecialchars($current_user['role'])); ?></p>
                </div>
                <div class="account-actions">
                    <a href="/account/edit.php" class="btn btn-outline">Edit Profile</a>
                    <a href="/account/security.php" class="btn btn-outline">Security Settings</a>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="account-section">
            <div class="account-card">
                <h2>Quick Actions</h2>
                <div class="quick-actions">
                    <a href="/orders.php" class="action-link">
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
                    <a href="/sell.php" class="action-link">
                        <span class="action-icon">💰</span>
                        <div>
                            <div class="action-title">Start Selling</div>
                            <div class="action-subtitle">Become a seller on FezaMarket</div>
                        </div>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="account-section full-width">
            <div class="account-card">
                <div class="card-header">
                    <h2>Recent Orders</h2>
                    <a href="/orders.php" class="btn btn-outline">View All Orders</a>
                </div>
                <?php if (!empty($recentOrders)): ?>
                    <div class="orders-list">
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="order-item">
                                <div class="order-info">
                                    <div class="order-number">Order #<?php echo $order['id']; ?></div>
                                    <div class="order-date"><?php echo formatDate($order['created_at']); ?></div>
                                    <div class="order-status status-<?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </div>
                                </div>
                                <div class="order-total">
                                    <?php echo formatPrice($order['total']); ?>
                                </div>
                                <div class="order-actions">
                                    <a href="/order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm">View Details</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You haven't placed any orders yet.</p>
                        <a href="/products.php" class="btn">Start Shopping</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.account-header {
    margin-bottom: 30px;
    padding: 20px 0;
    border-bottom: 1px solid #e5e7eb;
}

.account-header h1 {
    font-size: 28px;
    color: #1f2937;
    margin-bottom: 5px;
}

.account-subtitle {
    color: #6b7280;
    font-size: 16px;
}

.account-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.account-section.full-width {
    grid-column: 1 / -1;
}

.account-card {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.account-card h2 {
    color: #1f2937;
    font-size: 18px;
    margin-bottom: 15px;
}

.account-info p {
    margin-bottom: 8px;
    color: #374151;
}

.account-actions {
    margin-top: 15px;
    display: flex;
    gap: 10px;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

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