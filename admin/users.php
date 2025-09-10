<?php
/**
 * Admin User Management
 * E-Commerce Platform
 */

require_once __DIR__ . '/../includes/init.php';

// Require admin access
Session::requireRole('admin');

$user = new User();

// Handle user actions
if ($_POST && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_status':
            if (isset($_POST['user_id'], $_POST['status'])) {
                $user->update($_POST['user_id'], ['status' => $_POST['status']]);
                $message = 'User status updated successfully.';
            }
            break;
        case 'update_role':
            if (isset($_POST['user_id'], $_POST['role'])) {
                $user->update($_POST['user_id'], ['role' => $_POST['role']]);
                $message = 'User role updated successfully.';
            }
            break;
    }
}

// Get users with pagination
$page = $_GET['page'] ?? 1;
$limit = 25;
$offset = ($page - 1) * $limit;

$users = $user->findAll($limit, $offset);
$totalUsers = $user->count();
$totalPages = ceil($totalUsers / $limit);

$page_title = 'User Management - Admin';
includeHeader($page_title);
?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>User Management</h1>
        <div>
            <a href="/admin" class="btn btn-outline">← Back to Dashboard</a>
            <a href="/admin/users/create" class="btn btn-primary">Add New User</a>
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body">
            <h2>Users (<?php echo number_format($totalUsers); ?>)</h2>
            
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Verified</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $userData): ?>
                        <tr>
                            <td><?php echo $userData['id']; ?></td>
                            <td><?php echo htmlspecialchars($userData['username']); ?></td>
                            <td><?php echo htmlspecialchars($userData['email']); ?></td>
                            <td><?php echo htmlspecialchars($userData['first_name'] . ' ' . $userData['last_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $userData['role'] === 'admin' ? 'danger' : ($userData['role'] === 'vendor' ? 'warning' : 'primary'); ?>">
                                    <?php echo ucfirst($userData['role']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $userData['status'] === 'active' ? 'success' : ($userData['status'] === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($userData['status']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($userData['verified_at']): ?>
                                    <span class="badge badge-success">✓</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Pending</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($userData['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline" onclick="editUser(<?php echo $userData['id']; ?>)">Edit</button>
                                    <?php if ($userData['id'] != Session::getUserId()): ?>
                                        <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $userData['id']; ?>)">Delete</button>
                                    <?php endif; ?>
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
                        <a href="?page=<?php echo $i; ?>" class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="editUserModal" class="modal" style="display: none;">
    <div class="modal-content">
        <h3>Edit User</h3>
        <form id="editUserForm" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="user_id" id="editUserId">
            
            <div class="form-group">
                <label for="editUserRole">Role:</label>
                <select name="role" id="editUserRole" class="form-control">
                    <option value="customer">Customer</option>
                    <option value="vendor">Vendor</option>
                    <option value="admin">Admin</option>
                    <option value="mod">Moderator</option>
                    <option value="support">Support</option>
                    <option value="finance">Finance</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="editUserStatus">Status:</label>
                <select name="status" id="editUserStatus" class="form-control">
                    <option value="pending">Pending</option>
                    <option value="active">Active</option>
                    <option value="suspended">Suspended</option>
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
function editUser(userId) {
    // Get user data from the table row
    const row = event.target.closest('tr');
    const cells = row.querySelectorAll('td');
    
    document.getElementById('editUserId').value = userId;
    
    // Extract role and status from badges
    const roleBadge = cells[4].querySelector('.badge').textContent.toLowerCase();
    const statusBadge = cells[5].querySelector('.badge').textContent.toLowerCase();
    
    document.getElementById('editUserRole').value = roleBadge;
    document.getElementById('editUserStatus').value = statusBadge;
    
    document.getElementById('editUserModal').style.display = 'block';
}

function closeEditModal() {
    document.getElementById('editUserModal').style.display = 'none';
}

function confirmDelete(userId) {
    if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        // You would implement user deletion here
        alert('User deletion not implemented yet - this would delete user ID: ' + userId);
    }
}

// Close modal when clicking outside
document.getElementById('editUserModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>

<style>
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

.badge-primary { background: #007bff; color: white; }
.badge-success { background: #28a745; color: white; }
.badge-warning { background: #ffc107; color: black; }
.badge-danger { background: #dc3545; color: white; }
</style>

<?php includeFooter(); ?>