<?php
/**
 * Admin Dashboard
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access
Session::requireRole('admin');

$user = new User();
$product = new Product();
$order = new Order();
$vendor = new Vendor();

// Get dashboard statistics
$stats = [
    'total_users' => $user->count(),
    'total_products' => $product->count(),
    'total_orders' => $order->count(),
    'total_vendors' => $vendor->count(),
    'pending_vendors' => count($vendor->getPending()),
    'order_stats' => $order->getOrderStats()
];

// Recent activity
$recentOrders = $order->findAll(10);
$pendingVendors = $vendor->getPending();

$page_title = 'Admin Dashboard';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>Admin Dashboard</h1>
        <div>
            <a href="/admin/users.php" class="btn btn-outline">Manage Users</a>
            <a href="/admin/products.php" class="btn btn-outline">Manage Products</a>
            <a href="/admin/orders.php" class="btn btn-outline">Manage Orders</a>
        </div>
    </div>
    
    <!-- Statistics Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #007bff; margin-bottom: 0.5rem;">👥</div>
                <h3><?php echo number_format($stats['total_users']); ?></h3>
                <p class="text-muted">Total Users</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;">📦</div>
                <h3><?php echo number_format($stats['total_products']); ?></h3>
                <p class="text-muted">Total Products</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #ffc107; margin-bottom: 0.5rem;">🛒</div>
                <h3><?php echo number_format($stats['total_orders']); ?></h3>
                <p class="text-muted">Total Orders</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #17a2b8; margin-bottom: 0.5rem;">🏪</div>
                <h3><?php echo number_format($stats['total_vendors']); ?></h3>
                <p class="text-muted">Total Vendors</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #dc3545; margin-bottom: 0.5rem;">⏳</div>
                <h3><?php echo number_format($stats['pending_vendors']); ?></h3>
                <p class="text-muted">Pending Vendors</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;">💰</div>
                <h3><?php echo formatPrice($stats['order_stats']['total_revenue'] ?? 0); ?></h3>
                <p class="text-muted">Total Revenue</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Orders -->
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Recent Orders</h3>
                    
                    <?php if (empty($recentOrders)): ?>
                        <p class="text-muted">No orders yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentOrders as $orderItem): ?>
                                        <tr>
                                            <td>
                                                <a href="/admin/order.php?id=<?php echo $orderItem['id']; ?>">
                                                    <?php echo htmlspecialchars($orderItem['order_number']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php 
                                                $customer = $user->find($orderItem['user_id']);
                                                echo htmlspecialchars($customer['first_name'] . ' ' . $customer['last_name']);
                                                ?>
                                            </td>
                                            <td><?php echo formatPrice($orderItem['total_amount']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $orderItem['status']; ?>">
                                                    <?php echo ucfirst($orderItem['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo formatDate($orderItem['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="/admin/orders.php" class="btn btn-outline">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Pending Vendor Applications -->
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Pending Vendor Applications</h3>
                    
                    <?php if (empty($pendingVendors)): ?>
                        <p class="text-muted">No pending applications.</p>
                    <?php else: ?>
                        <?php foreach ($pendingVendors as $vendorApp): ?>
                            <div style="border: 1px solid #eee; border-radius: 8px; padding: 1rem; margin-bottom: 1rem;">
                                <h5><?php echo htmlspecialchars($vendorApp['business_name']); ?></h5>
                                <p class="text-muted">
                                    Applicant: <?php echo htmlspecialchars($vendorApp['first_name'] . ' ' . $vendorApp['last_name']); ?><br>
                                    Email: <?php echo htmlspecialchars($vendorApp['email']); ?><br>
                                    Applied: <?php echo formatDate($vendorApp['created_at']); ?>
                                </p>
                                <div>
                                    <button class="btn btn-sm btn-success" onclick="approveVendor(<?php echo $vendorApp['id']; ?>)">
                                        Approve
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="rejectVendor(<?php echo $vendorApp['id']; ?>)">
                                        Reject
                                    </button>
                                    <a href="/admin/vendor.php?id=<?php echo $vendorApp['id']; ?>" class="btn btn-sm btn-outline">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="/admin/vendors.php" class="btn btn-outline">View All Vendors</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card mt-4">
        <div class="card-body">
            <h3 class="card-title">Quick Actions</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="/admin/product/add.php" class="btn btn-success">Add New Product</a>
                <a href="/admin/category/add.php" class="btn btn-primary">Add Category</a>
                <a href="/admin/user/add.php" class="btn btn-info">Add User</a>
                <a href="/admin/settings.php" class="btn btn-secondary">System Settings</a>
                <a href="/admin/reports.php" class="btn btn-warning">View Reports</a>
                <a href="/admin/backup.php" class="btn btn-danger">Backup System</a>
            </div>
        </div>
    </div>
</div>

<script>
function approveVendor(vendorId) {
    if (confirm('Approve this vendor application?')) {
        fetch('/admin/api/vendor-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'approve',
                vendor_id: vendorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}

function rejectVendor(vendorId) {
    if (confirm('Reject this vendor application?')) {
        fetch('/admin/api/vendor-action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'reject',
                vendor_id: vendorId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.error);
            }
        });
    }
}
</script>

<style>
.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: bold;
}

.badge-pending { background-color: #ffc107; color: #212529; }
.badge-processing { background-color: #17a2b8; color: white; }
.badge-shipped { background-color: #007bff; color: white; }
.badge-delivered { background-color: #28a745; color: white; }
.badge-cancelled { background-color: #dc3545; color: white; }
</style>

<?php includeFooter(); ?>