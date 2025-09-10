<?php
/**
 * Admin Product Management
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access
Session::requireRole('admin');

$product = new Product();
$category = new Category();

// Handle product actions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_status':
            if (isset($_POST['product_id'], $_POST['status'])) {
                $product->update($_POST['product_id'], ['status' => $_POST['status']]);
                $message = 'Product status updated successfully.';
            }
            break;
        case 'update_featured':
            if (isset($_POST['product_id'], $_POST['featured'])) {
                $product->update($_POST['product_id'], ['featured' => $_POST['featured']]);
                $message = 'Product featured status updated.';
            }
            break;
    }
}

// Get products with pagination and filters
$page = $_GET['page'] ?? 1;
$status = $_GET['status'] ?? '';
$category_filter = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';

$limit = 25;
$offset = ($page - 1) * $limit;

// Build where clause
$whereConditions = [];
$params = [];

if ($status) {
    $whereConditions[] = "status = ?";
    $params[] = $status;
}

if ($category_filter) {
    $whereConditions[] = "category_id = ?";
    $params[] = $category_filter;
}

if ($search) {
    $whereConditions[] = "(name LIKE ? OR sku LIKE ?)";
    $params[] = "%{$search}%";
    $params[] = "%{$search}%";
}

$whereClause = !empty($whereConditions) ? "WHERE " . implode(" AND ", $whereConditions) : "";

// Get products
$sql = "SELECT p.*, c.name as category_name, v.business_name as vendor_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        {$whereClause} 
        ORDER BY p.created_at DESC 
        LIMIT {$limit} OFFSET {$offset}";

$stmt = Database::getInstance()->getConnection()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get total count
$countSql = "SELECT COUNT(*) FROM products p {$whereClause}";
$countStmt = Database::getInstance()->getConnection()->prepare($countSql);
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

// Get categories for filter
$categories = $category->findAll();

$page_title = 'Product Management - Admin';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>Product Management</h1>
        <div>
            <a href="/admin" class="btn btn-outline">← Back to Dashboard</a>
            <a href="/admin/products/create" class="btn btn-primary">Add New Product</a>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="filters-form">
                <div class="row">
                    <div class="col-3">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($search); ?>" class="form-control">
                    </div>
                    <div class="col-2">
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            <option value="out_of_stock" <?php echo $status === 'out_of_stock' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    <div class="col-2">
                        <select name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category_filter == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        <a href="/admin/products" class="btn btn-outline">Clear</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <h2>Products (<?php echo number_format($totalProducts); ?>)</h2>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>SKU</th>
                            <th>Category</th>
                            <th>Vendor</th>
                            <th>Price</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $productData): ?>
                        <tr>
                            <td><?php echo $productData['id']; ?></td>
                            <td>
                                <img src="/uploads/products/<?php echo $productData['id']; ?>.jpg" 
                                     alt="Product Image" 
                                     style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;"
                                     onerror="this.src='/images/placeholder-product.jpg'">
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($productData['name']); ?></strong><br>
                                <small class="text-muted"><?php echo substr(htmlspecialchars($productData['description']), 0, 100); ?>...</small>
                            </td>
                            <td><code><?php echo htmlspecialchars($productData['sku']); ?></code></td>
                            <td><?php echo htmlspecialchars($productData['category_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($productData['vendor_name'] ?? 'Unknown'); ?></td>
                            <td><?php echo formatPrice($productData['price']); ?></td>
                            <td>
                                <?php if ($productData['stock_quantity'] > $productData['min_stock_level']): ?>
                                    <span class="badge badge-success"><?php echo $productData['stock_quantity']; ?></span>
                                <?php elseif ($productData['stock_quantity'] > 0): ?>
                                    <span class="badge badge-warning"><?php echo $productData['stock_quantity']; ?></span>
                                <?php else: ?>
                                    <span class="badge badge-danger">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $productData['status'] === 'active' ? 'success' : ($productData['status'] === 'inactive' ? 'secondary' : 'danger'); ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $productData['status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($productData['featured']): ?>
                                    <span class="badge badge-warning">★ Featured</span>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline" onclick="editProduct(<?php echo $productData['id']; ?>)">Edit</button>
                                    <a href="/product.php?id=<?php echo $productData['id']; ?>" class="btn btn-sm btn-info" target="_blank">View</a>
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
                        <a href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&category=<?php echo urlencode($category_filter); ?>&search=<?php echo urlencode($search); ?>" 
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

<!-- Edit Product Modal -->
<div id="editProductModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Edit Product</h3>
        <form id="editProductForm" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="product_id" id="editProductId">
            
            <div class="form-group">
                <label for="editProductStatus">Status:</label>
                <select name="status" id="editProductStatus" class="form-control">
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="out_of_stock">Out of Stock</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="featured" value="1" id="editProductFeatured">
                    Featured Product
                </label>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save Changes</button>
                <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function editProduct(productId) {
    const row = event.target.closest('tr');
    const cells = row.querySelectorAll('td');
    
    document.getElementById('editProductId').value = productId;
    
    // Extract status from badge
    const statusBadge = cells[8].querySelector('.badge').textContent.toLowerCase().replace(' ', '_');
    document.getElementById('editProductStatus').value = statusBadge;
    
    // Check if featured
    const featuredCell = cells[9];
    const isFeatured = featuredCell.textContent.includes('Featured');
    document.getElementById('editProductFeatured').checked = isFeatured;
    
    document.getElementById('editProductModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editProductModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('editProductModal').addEventListener('click', function(e) {
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

.filters-form .col-2, .filters-form .col-3 {
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
.badge-secondary { background: #6c757d; color: white; }
.badge-info { background: #17a2b8; color: white; }

.table img {
    border: 1px solid #ddd;
}
</style>

<?php includeFooter(); ?>