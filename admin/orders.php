<?php
/**
 * Admin Order Management
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access
Session::requireRole('admin');

$order = new Order();

// Handle order actions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_status':
            if (isset($_POST['order_id'], $_POST['status'])) {
                $order->update($_POST['order_id'], ['status' => $_POST['status']]);
                $message = 'Order status updated successfully.';
            }
            break;
        case 'update_payment_status':
            if (isset($_POST['order_id'], $_POST['payment_status'])) {
                $order->update($_POST['order_id'], ['payment_status' => $_POST['payment_status']]);
                $message = 'Payment status updated successfully.';
            }
            break;
    }
}

// Get orders with pagination and filters
$page = $_GET['page'] ?? 1;
$status = $_GET['status'] ?? '';
$payment_status = $_GET['payment_status'] ?? '';
$search = $_GET['search'] ?? '';

$limit = 25;
$offset = ($page - 1) * $limit;

// Build where clause
$whereConditions = [];
$params = [];

if ($status) {
    $whereConditions[] = "o.status = ?";
    $params[] = $status;
}

if ($payment_status) {
    $whereConditions[] = "o.payment_status = ?";
    $params[] = $payment_status;
}

if ($search) {
    $whereConditions[] = "(o.order_number LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get orders with customer info
$sql = "SELECT o.*, u.first_name, u.last_name, u.email,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o 
        LEFT JOIN users u ON o.user_id = u.id 
        {$whereClause} 
        ORDER BY o.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}";

$stmt = Database::getInstance()->getConnection()->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Get total count
$countSql = "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON o.user_id = u.id {$whereClause}";
$countStmt = Database::getInstance()->getConnection()->prepare($countSql);
$countStmt->execute($params);
$totalOrders = $countStmt->fetchColumn();
$totalPages = ceil($totalOrders / $limit);

// Get summary stats
$stats = $order->getOrderStats();

$page_title = 'Order Management - Admin';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>Order Management</h1>
        <div>
            <a href="/admin" class="btn btn-outline">← Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Order Statistics -->
    <div class="row mb-4">
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo number_format($stats['total_orders'] ?? 0); ?></h3>
                    <p class="text-muted">Total Orders</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo formatPrice($stats['total_revenue'] ?? 0); ?></h3>
                    <p class="text-muted">Total Revenue</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo number_format($stats['pending_orders'] ?? 0); ?></h3>
                    <p class="text-muted">Pending Orders</p>
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="card">
                <div class="card-body text-center">
                    <h3><?php echo formatPrice($stats['average_order_value'] ?? 0); ?></h3>
                    <p class="text-muted">Average Order</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="filters-form">
                <div class="row">
                    <div class="col-3">
                        <input type="text" name="search" placeholder="Search orders, customers..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                    </div>
                    <div class="col-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="processing" <?php echo $status === 'processing' ? 'selected' : ''; ?>>Processing</option>
                            <option value="shipped" <?php echo $status === 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                            <option value="delivered" <?php echo $status === 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                            <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            <option value="refunded" <?php echo $status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <select name="payment_status" class="form-control">
                            <option value="">All Payment Status</option>
                            <option value="pending" <?php echo $payment_status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo $payment_status === 'paid' ? 'selected' : ''; ?>>Paid</option>
                            <option value="failed" <?php echo $payment_status === 'failed' ? 'selected' : ''; ?>>Failed</option>
                            <option value="refunded" <?php echo $payment_status === 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="/admin/orders" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2>Orders (<?php echo number_format($totalOrders); ?>)</h2>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $orderData): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($orderData['order_number']); ?></strong>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($orderData['first_name'] . ' ' . $orderData['last_name']); ?><br>
                                <small class="text-muted"><?php echo htmlspecialchars($orderData['email']); ?></small>
                            </td>
                            <td>
                                <span class="badge badge-info"><?php echo $orderData['item_count']; ?> items</span>
                            </td>
                            <td>
                                <strong><?php echo formatPrice($orderData['total']); ?></strong>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $orderData['status'] === 'delivered' ? 'success' : 
                                         ($orderData['status'] === 'pending' ? 'warning' : 
                                          ($orderData['status'] === 'cancelled' || $orderData['status'] === 'refunded' ? 'danger' : 'primary')); ?>">
                                    <?php echo ucfirst($orderData['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php 
                                    echo $orderData['payment_status'] === 'paid' ? 'success' : 
                                         ($orderData['payment_status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($orderData['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo date('M j, Y', strtotime($orderData['created_at'])); ?><br>
                                <small class="text-muted"><?php echo date('g:i A', strtotime($orderData['created_at'])); ?></small>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline" onclick="editOrder(<?php echo $orderData['id']; ?>)">Edit</button>
                                    <a href="/admin/orders/<?php echo $orderData['id']; ?>" class="btn btn-sm btn-info">View</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="pagination-nav">
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&payment_status=<?php echo urlencode($payment_status); ?>&search=<?php echo urlencode($search); ?>" 
                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Order Modal -->
<div id="editOrderModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Edit Order</h3>
        <form id="editOrderForm" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" id="editOrderId">
            
            <div class="form-group">
                <label for="editOrderStatus">Order Status:</label>
                <select name="status" id="editOrderStatus" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="processing">Processing</option>
                    <option value="shipped">Shipped</option>
                    <option value="delivered">Delivered</option>
                    <option value="cancelled">Cancelled</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="editPaymentStatus">Payment Status:</label>
                <select name="payment_status" id="editPaymentStatus" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="paid">Paid</option>
                    <option value="failed">Failed</option>
                    <option value="refunded">Refunded</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editOrder(orderId) {
    const row = event.target.closest('tr');
    const cells = row.querySelectorAll('td');
    
    document.getElementById('editOrderId').value = orderId;
    
    // Extract status from badges
    const statusBadge = cells[4].querySelector('.badge').textContent.toLowerCase();
    const paymentBadge = cells[5].querySelector('.badge').textContent.toLowerCase();
    
    document.getElementById('editOrderStatus').value = statusBadge;
    document.getElementById('editPaymentStatus').value = paymentBadge;
    
    document.getElementById('editOrderModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editOrderModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editOrderModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<style>
.filters-form .row {
    display: flex;
    gap: 1rem;
    align-items: center;
}

.row {
    display: flex;
    gap: 1rem;
}

.col-2, .col-3 {
    flex: 0 0 auto;
}

.col-3 { width: 25%; }
.col-2 { width: 16.666%; }

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    max-width: 500px;
    width: 90%;
}

.btn-group {
    display: flex;
    gap: 0.5rem;
}

.pagination-nav {
    margin-top: 2rem;
    text-align: center;
}

.pagination {
    display: inline-flex;
    gap: 0.5rem;
}

.page-link {
    padding: 0.5rem 1rem;
    border: 1px solid #ddd;
    text-decoration: none;
    color: #007bff;
}

.page-link.active {
    background: #007bff;
    color: white;
}

.badge {
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 600;
}

.badge-success { background: #28a745; color: white; }
.badge-warning { background: #ffc107; color: black; }
.badge-danger { background: #dc3545; color: white; }
.badge-primary { background: #007bff; color: white; }
.badge-info { background: #17a2b8; color: white; }

.card {
    border: 1px solid #ddd;
    border-radius: 8px;
}

.card-body {
    padding: 1.5rem;
}
</style>

<?php includeFooter(); ?>