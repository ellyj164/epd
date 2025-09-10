<?php
/**
 * Enhanced Admin Dashboard
 * E-Commerce Platform - Complete RBAC Implementation
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access using new RBAC system
RoleMiddleware::requireAdmin();

$user = new User();
$product = new Product();
$order = new Order();
$vendor = new Vendor();

// Get comprehensive dashboard statistics
$stats = [
    'total_users' => $user->count(),
    'active_users' => $user->count("status = 'active'"),
    'pending_users' => $user->count("status = 'pending'"),
    'total_products' => $product->count(),
    'active_products' => $product->count("status = 'active'"),
    'total_orders' => $order->count(),
    'pending_orders' => $order->count("status = 'pending'"),
    'processing_orders' => $order->count("status = 'processing'"),
    'total_vendors' => $vendor->count(),
    'pending_vendors' => count($vendor->getPending()),
    'order_stats' => $order->getOrderStats()
];

// Recent activity with enhanced details
$recentOrders = $order->findAll(5);
$pendingVendors = $vendor->getPending();

// Get recent user registrations
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT u.*, p.avatar_url FROM users u LEFT JOIN profiles p ON u.id = p.user_id ORDER BY u.created_at DESC LIMIT 5");
$recentUsers = $stmt->fetchAll();

// Get system health status
$healthChecks = performHealthCheck();

$page_title = 'Admin Dashboard';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>Admin Dashboard</h1>
        <div class="d-flex gap-2">
            <a href="/admin/users.php" class="btn btn-outline">👥 Users</a>
            <a href="/admin/products.php" class="btn btn-outline">📦 Products</a>
            <a href="/admin/orders.php" class="btn btn-outline">📋 Orders</a>
            <a href="/admin/vendors.php" class="btn btn-outline">🏪 Vendors</a>
            <a href="/admin/analytics.php" class="btn btn-outline">📊 Analytics</a>
            <a href="/admin/settings.php" class="btn btn-primary">⚙️ Settings</a>
        </div>
    </div>

    <!-- System Health Status -->
    <div class="card mb-4" style="border-left: 4px solid #28a745;">
        <div class="card-body">
            <h5 class="card-title">🏥 System Health</h5>
            <div class="row">
                <?php foreach ($healthChecks as $checkName => $check): ?>
                <div class="col-md-3">
                    <div class="d-flex align-items-center">
                        <span style="color: <?php echo $check['status'] === 'ok' ? '#28a745' : ($check['status'] === 'warning' ? '#ffc107' : '#dc3545'); ?>; font-size: 18px; margin-right: 8px;">
                            <?php echo $check['status'] === 'ok' ? '✅' : ($check['status'] === 'warning' ? '⚠️' : '❌'); ?>
                        </span>
                        <div>
                            <strong><?php echo ucfirst(str_replace('_', ' ', $checkName)); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($check['message']); ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Enhanced Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <!-- Users Stats -->
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #007bff; margin-bottom: 0.5rem;">👥</div>
                <h3><?php echo number_format($stats['total_users']); ?></h3>
                <p class="text-muted">Total Users</p>
                <div class="d-flex justify-between text-small">
                    <span class="text-success">Active: <?php echo $stats['active_users']; ?></span>
                    <span class="text-warning">Pending: <?php echo $stats['pending_users']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Products Stats -->
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;">📦</div>
                <h3><?php echo number_format($stats['total_products']); ?></h3>
                <p class="text-muted">Total Products</p>
                <div class="d-flex justify-between text-small">
                    <span class="text-success">Active: <?php echo $stats['active_products']; ?></span>
                    <span class="text-info">Categories: <?php echo $product->count('DISTINCT category_id'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Orders Stats -->
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #ffc107; margin-bottom: 0.5rem;">📋</div>
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p class="text-muted">Total Orders</p>
                <div class="d-flex justify-between text-small">
                    <span class="text-warning">Pending: <?php echo $stats['pending_orders']; ?></span>
                    <span class="text-info">Processing: <?php echo $stats['processing_orders']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Revenue Stats -->
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #17a2b8; margin-bottom: 0.5rem;">💰</div>
                <h3>$<?php echo number_format($stats['order_stats']['total_revenue'] ?? 0, 2); ?></h3>
                <p class="text-muted">Total Revenue</p>
                <div class="d-flex justify-between text-small">
                    <span class="text-success">Today: $<?php echo number_format($stats['order_stats']['today_revenue'] ?? 0, 2); ?></span>
                    <span class="text-info">Avg: $<?php echo number_format($stats['order_stats']['avg_order'] ?? 0, 2); ?></span>
                </div>
            </div>
        </div>

        <!-- Vendors Stats -->
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #6f42c1; margin-bottom: 0.5rem;">🏪</div>
                <h3><?php echo number_format($stats['total_vendors']); ?></h3>
                <p class="text-muted">Total Vendors</p>
                <div class="d-flex justify-between text-small">
                    <span class="text-warning">Pending: <?php echo $stats['pending_vendors']; ?></span>
                    <span class="text-success">Active: <?php echo $stats['total_vendors'] - $stats['pending_vendors']; ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Dashboard Content -->
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h5>📋 Recent Orders</h5>
                    <a href="/admin/orders.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-center text-muted py-4">No recent orders</p>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <div class="d-flex justify-between align-center py-2 border-bottom">
                                <div>
                                    <strong>#<?php echo htmlspecialchars($order['order_number']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo formatDate($order['created_at']); ?> | 
                                        $<?php echo number_format($order['total'], 2); ?>
                                    </small>
                                </div>
                                <div class="text-right">
                                    <span class="badge badge-<?php echo getOrderStatusColor($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($order['user_email'] ?? 'Guest'); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h5>👥 Recent Registrations</h5>
                    <a href="/admin/users.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <?php if (empty($recentUsers)): ?>
                        <p class="text-center text-muted py-4">No recent users</p>
                    <?php else: ?>
                        <?php foreach ($recentUsers as $user): ?>
                            <div class="d-flex align-center py-2 border-bottom">
                                <div class="mr-3">
                                    <?php if (!empty($user['avatar_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" 
                                             alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <div style="width: 40px; height: 40px; border-radius: 50%; background: #6c757d; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold;">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-grow-1">
                                    <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong><br>
                                    <small class="text-muted">
                                        <?php echo htmlspecialchars($user['email']); ?> | 
                                        <?php echo ucfirst($user['role']); ?>
                                    </small>
                                </div>
                                <div>
                                    <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : ($user['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span><br>
                                    <small class="text-muted"><?php echo formatDate($user['created_at']); ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Vendor Applications -->
    <?php if (!empty($pendingVendors)): ?>
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-between align-center">
                    <h5>🏪 Pending Vendor Applications</h5>
                    <a href="/admin/vendors.php" class="btn btn-sm btn-outline">View All</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach (array_slice($pendingVendors, 0, 3) as $vendorApp): ?>
                            <div class="col-md-4 mb-3">
                                <div class="border rounded p-3">
                                    <h5><?php echo htmlspecialchars($vendorApp['business_name']); ?></h5>
                                    <p class="text-muted">
                                        Applicant: <?php echo htmlspecialchars($vendorApp['first_name'] . ' ' . $vendorApp['last_name']); ?><br>
                                        Email: <?php echo htmlspecialchars($vendorApp['email']); ?><br>
                                        Applied: <?php echo formatDate($vendorApp['created_at']); ?>
                                    </p>
                                    <div class="d-flex gap-2">
                                        <button class="btn btn-sm btn-success" onclick="approveVendor(<?php echo $vendorApp['id']; ?>)">
                                            ✅ Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="rejectVendor(<?php echo $vendorApp['id']; ?>)">
                                            ❌ Reject
                                        </button>
                                        <a href="/admin/vendor.php?id=<?php echo $vendorApp['id']; ?>" class="btn btn-sm btn-outline">
                                            👁️ View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>🚀 Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="/admin/products/create.php" class="btn btn-outline btn-block">
                                ➕ Add Product
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/users/create.php" class="btn btn-outline btn-block">
                                👤 Create User
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/categories.php" class="btn btn-outline btn-block">
                                🗂️ Manage Categories
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/reports.php" class="btn btn-outline btn-block">
                                📊 View Reports
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-3">
                            <a href="/admin/coupons.php" class="btn btn-outline btn-block">
                                🎟️ Manage Coupons
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/emails.php" class="btn btn-outline btn-block">
                                📧 Email Campaigns
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/shipping.php" class="btn btn-outline btn-block">
                                🚚 Shipping Settings
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="/admin/security.php" class="btn btn-outline btn-block">
                                🔒 Security & Audit
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Admin JavaScript -->
<script>
async function approveVendor(vendorId) {
    if (!confirm('Are you sure you want to approve this vendor application?')) return;
    
    try {
        const response = await fetch('/admin/api/vendors/approve.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ vendor_id: vendorId, csrf_token: '<?php echo csrfToken(); ?>' })
        });
        
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error approving vendor: ' + error.message);
    }
}

async function rejectVendor(vendorId) {
    const reason = prompt('Please provide a reason for rejection:');
    if (!reason) return;
    
    try {
        const response = await fetch('/admin/api/vendors/reject.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ vendor_id: vendorId, reason: reason, csrf_token: '<?php echo csrfToken(); ?>' })
        });
        
        const result = await response.json();
        if (result.success) {
            location.reload();
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        alert('Error rejecting vendor: ' + error.message);
    }
}

// Auto-refresh health status every 30 seconds
setInterval(async function() {
    try {
        const response = await fetch('/healthz');
        const health = await response.json();
        // Update health status indicators if needed
    } catch (error) {
        console.warn('Health check failed:', error);
    }
}, 30000);
</script>

<?php 
// Helper functions for the dashboard
function getOrderStatusColor($status) {
    switch ($status) {
        case 'pending': return 'warning';
        case 'processing': return 'info';
        case 'shipped': return 'primary';
        case 'delivered': return 'success';
        case 'cancelled': return 'danger';
        case 'refunded': return 'secondary';
        default: return 'light';
    }
}

function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}

includeFooter(); 
?>