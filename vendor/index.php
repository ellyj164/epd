<?php
/**
 * Vendor Dashboard
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

// Check if user is logged in
Session::requireLogin();

$userId = Session::getUserId();
$vendor = new Vendor();
$product = new Product();
$order = new Order();

// Check if user is a vendor or has pending application
$vendorData = $vendor->findByUserId($userId);

if (!$vendorData) {
    // User is not a vendor, show registration form
    $page_title = 'Become a Vendor';
    includeHeader($page_title);
    ?>
    
    <div class="container">
        <div class="row justify-center">
            <div class="col-8">
                <div class="card">
                    <div class="card-body">
                        <h1 class="card-title">Become a Vendor</h1>
                        <p>Join our marketplace and start selling your products to thousands of customers!</p>
                        
                        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
                            <?php
                            $formData = [
                                'business_name' => sanitizeInput($_POST['business_name'] ?? ''),
                                'business_description' => sanitizeInput($_POST['business_description'] ?? ''),
                                'business_address' => sanitizeInput($_POST['business_address'] ?? ''),
                                'tax_id' => sanitizeInput($_POST['tax_id'] ?? '')
                            ];
                            
                            $errors = [];
                            
                            if (empty($formData['business_name'])) {
                                $errors[] = 'Business name is required';
                            }
                            
                            if (empty($formData['business_description'])) {
                                $errors[] = 'Business description is required';
                            }
                            
                            if (empty($errors)) {
                                try {
                                    $vendorId = $vendor->createVendorApplication($userId, $formData);
                                    
                                    if ($vendorId) {
                                        echo '<div class="alert alert-success">Vendor application submitted successfully! We will review your application and get back to you soon.</div>';
                                        Logger::info("Vendor application submitted: {$formData['business_name']}");
                                    } else {
                                        echo '<div class="alert alert-error">Failed to submit application. Please try again.</div>';
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-error">An error occurred. Please try again.</div>';
                                    Logger::error("Vendor application error: " . $e->getMessage());
                                }
                            } else {
                                echo '<div class="alert alert-error">' . implode('<br>', $errors) . '</div>';
                            }
                            ?>
                        <?php endif; ?>
                        
                        <form method="POST" class="validate-form">
                            <div class="form-group">
                                <label for="business_name" class="form-label">Business Name *</label>
                                <input type="text" id="business_name" name="business_name" class="form-control" required
                                       value="<?php echo htmlspecialchars($_POST['business_name'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="business_description" class="form-label">Business Description *</label>
                                <textarea id="business_description" name="business_description" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['business_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="business_address" class="form-label">Business Address</label>
                                <textarea id="business_address" name="business_address" class="form-control" rows="3"><?php echo htmlspecialchars($_POST['business_address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="tax_id" class="form-label">Tax ID/EIN</label>
                                <input type="text" id="tax_id" name="tax_id" class="form-control"
                                       value="<?php echo htmlspecialchars($_POST['tax_id'] ?? ''); ?>">
                            </div>
                            
                            <button type="submit" class="btn btn-lg">Submit Application</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    includeFooter();
    exit;
}

if ($vendorData['status'] === 'pending') {
    // Application is pending
    $page_title = 'Application Pending';
    includeHeader($page_title);
    ?>
    
    <div class="container">
        <div class="text-center" style="padding: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">⏳</div>
            <h1>Application Under Review</h1>
            <p class="text-muted">Your vendor application is currently being reviewed by our team. We'll notify you once it's approved.</p>
            <div class="card mt-4" style="max-width: 500px; margin: 0 auto;">
                <div class="card-body">
                    <h4>Application Details</h4>
                    <p><strong>Business Name:</strong> <?php echo htmlspecialchars($vendorData['business_name']); ?></p>
                    <p><strong>Status:</strong> <span class="text-warning">Pending Review</span></p>
                    <p><strong>Submitted:</strong> <?php echo formatDate($vendorData['created_at']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <?php
    includeFooter();
    exit;
}

if ($vendorData['status'] === 'suspended') {
    // Account is suspended
    $page_title = 'Account Suspended';
    includeHeader($page_title);
    ?>
    
    <div class="container">
        <div class="text-center" style="padding: 3rem;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🚫</div>
            <h1>Account Suspended</h1>
            <p class="text-muted">Your vendor account has been suspended. Please contact support for more information.</p>
        </div>
    </div>
    
    <?php
    includeFooter();
    exit;
}

// Vendor is approved, show dashboard
$vendorId = $vendorData['id'];

// Get vendor statistics
$vendorProducts = $product->getByVendor($vendorId, 10);
$vendorOrders = $order->getVendorOrders($vendorId, 10);
$vendorStats = $vendor->getVendorStats($vendorId);

$page_title = 'Vendor Dashboard';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <div>
            <h1>Vendor Dashboard</h1>
            <p class="text-muted">Welcome back, <?php echo htmlspecialchars($vendorData['business_name']); ?>!</p>
        </div>
        <div>
            <a href="/vendor/product/add.php" class="btn btn-success">Add Product</a>
            <a href="/vendor/products.php" class="btn btn-outline">Manage Products</a>
        </div>
    </div>
    
    <!-- Statistics -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #007bff; margin-bottom: 0.5rem;">📦</div>
                <h3><?php echo number_format($vendorStats['product_count']); ?></h3>
                <p class="text-muted">Products</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #28a745; margin-bottom: 0.5rem;">🛒</div>
                <h3><?php echo number_format($vendorStats['total_orders']); ?></h3>
                <p class="text-muted">Orders</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #ffc107; margin-bottom: 0.5rem;">💰</div>
                <h3><?php echo formatPrice($vendorStats['total_revenue']); ?></h3>
                <p class="text-muted">Revenue</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div style="font-size: 2rem; color: #17a2b8; margin-bottom: 0.5rem;">📊</div>
                <h3><?php echo formatPrice($vendorStats['average_order_value']); ?></h3>
                <p class="text-muted">Avg Order</p>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Recent Products -->
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Recent Products</h3>
                    
                    <?php if (empty($vendorProducts)): ?>
                        <p class="text-muted">No products yet. <a href="/vendor/product/add.php">Add your first product</a>!</p>
                    <?php else: ?>
                        <?php foreach ($vendorProducts as $prod): ?>
                            <div style="display: flex; align-items: center; padding: 1rem 0; border-bottom: 1px solid #eee;">
                                <img src="<?php echo getProductImageUrl($prod['image_url'] ?? ''); ?>" 
                                     alt="<?php echo htmlspecialchars($prod['name']); ?>"
                                     style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; margin-right: 1rem;">
                                <div style="flex: 1;">
                                    <h5><?php echo htmlspecialchars($prod['name']); ?></h5>
                                    <p class="text-muted">SKU: <?php echo htmlspecialchars($prod['sku']); ?></p>
                                    <p><?php echo formatPrice($prod['price']); ?> | Stock: <?php echo $prod['stock_quantity']; ?></p>
                                </div>
                                <div>
                                    <a href="/vendor/product/edit.php?id=<?php echo $prod['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="text-center mt-3">
                            <a href="/vendor/products.php" class="btn btn-outline">View All Products</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">Recent Orders</h3>
                    
                    <?php if (empty($vendorOrders)): ?>
                        <p class="text-muted">No orders yet.</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vendorOrders as $orderItem): ?>
                                        <tr>
                                            <td>
                                                <a href="/vendor/order.php?id=<?php echo $orderItem['id']; ?>">
                                                    <?php echo htmlspecialchars($orderItem['order_number']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo htmlspecialchars($orderItem['first_name'] . ' ' . $orderItem['last_name']); ?></td>
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
                            <a href="/vendor/orders.php" class="btn btn-outline">View All Orders</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

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