<?php
require_once 'header.php';
require_once '../db_connection.php';

$page_title = "Manage Users";

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter
$search = $_GET['search'] ?? '';
$role = $_GET['role'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];

if(!empty($search)) {
    $where .= " AND (username LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if($role !== '') {
    $where .= " AND is_admin = ?";
    $params[] = (int)$role;
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM users $where";
$count_stmt = $conn->prepare($count_sql);
if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get users
$sql = "SELECT *, 
               IFNULL(status, 'active') as user_status,
               IFNULL(profile_image, '') as profile_image 
        FROM users $where 
        ORDER BY created_at DESC 
        LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Handle user actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user_id = (int)$_POST['user_id'];
    
    switch($action) {
        case 'toggle_status':
            // Check if status column exists
            $columns = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
            if($columns->num_rows > 0) {
                $current_status = $conn->query("SELECT status FROM users WHERE id = $user_id")->fetch_assoc()['status'];
                $new_status = $current_status == 'active' ? 'inactive' : 'active';
                $conn->query("UPDATE users SET status = '$new_status' WHERE id = $user_id");
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'message' => "User status updated to $new_status"
                ];
            } else {
                $_SESSION['toast'] = [
                    'type' => 'warning',
                    'message' => "Status feature not available"
                ];
            }
            header("Location: manage_users.php");
            exit();
            
        case 'make_admin':
            $conn->query("UPDATE users SET is_admin = 1 WHERE id = $user_id");
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => "User promoted to admin"
            ];
            header("Location: manage_users.php");
            exit();
            
        case 'remove_admin':
            // Prevent removing your own admin access
            if($user_id != $_SESSION['admin_id']) {
                $conn->query("UPDATE users SET is_admin = 0 WHERE id = $user_id");
                $_SESSION['toast'] = [
                    'type' => 'success',
                    'message' => "Admin privileges removed"
                ];
            } else {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'message' => "Cannot remove your own admin privileges"
                ];
            }
            header("Location: manage_users.php");
            exit();
            
        case 'delete':
            // Prevent deleting yourself
            if($user_id != $_SESSION['admin_id']) {
                // Check if user has bookings
                $booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id")->fetch_assoc()['count'];
                if($booking_count > 0) {
                    $_SESSION['toast'] = [
                        'type' => 'error',
                        'message' => "Cannot delete user with $booking_count booking(s). Transfer bookings first."
                    ];
                } else {
                    $conn->query("DELETE FROM users WHERE id = $user_id");
                    $_SESSION['toast'] = [
                        'type' => 'success',
                        'message' => "User deleted successfully"
                    ];
                }
            } else {
                $_SESSION['toast'] = [
                    'type' => 'error',
                    'message' => "Cannot delete your own account"
                ];
            }
            header("Location: manage_users.php");
            exit();
    }
}
?>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <span>Manage Users</span>
            </div>
            <div class="topbar-actions">
                <a href="javascript:void(0)" onclick="showAddUserModal()" class="btn btn-primary">
                    <i class="fas fa-user-plus"></i> Add User
                </a>
            </div>
        </div>
        
        <div class="content-wrapper">
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filter Users</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="search" placeholder="Search by name, email, phone..." 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <select name="role" class="form-control">
                                    <option value="">All Roles</option>
                                    <option value="1" <?php echo $role === '1' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="0" <?php echo $role === '0' ? 'selected' : ''; ?>>Regular User</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="manage_users.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Users (<?php echo $total_rows; ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Contact</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($user = $result->fetch_assoc()): 
                                        $booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = {$user['id']}")->fetch_assoc()['count'];
                                        $user_status = $user['user_status'] ?? 'active';
                                    ?>
                                        <tr>
                                            <td>#<?php echo $user['id']; ?></td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <?php if(!empty($user['profile_image']) && file_exists("../uploads/profiles/" . $user['profile_image'])): ?>
                                                            <img src="../uploads/profiles/<?php echo $user['profile_image']; ?>" 
                                                                 alt="<?php echo htmlspecialchars($user['username']); ?>">
                                                        <?php else: ?>
                                                            <div class="avatar-placeholder">
                                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="user-details">
                                                        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                        <small class="text-muted"><?php echo $user['email']; ?></small>
                                                        <div class="user-stats">
                                                            <span class="stat-badge"><?php echo $booking_count; ?> bookings</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="contact-info">
                                                    <div><i class="fas fa-envelope"></i> <?php echo $user['email']; ?></div>
                                                    <?php if(!empty($user['phone'])): ?>
                                                        <div><i class="fas fa-phone"></i> <?php echo $user['phone']; ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if($user['is_admin']): ?>
                                                    <span class="badge badge-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">User</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $user_status; ?>">
                                                    <?php echo ucfirst($user_status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                                                <?php if(!empty($user['last_login'])): ?>
                                                    <br><small>Last login: <?php echo date('M d', strtotime($user['last_login'])); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" onclick="showUserDetails(<?php echo $user['id']; ?>)" 
                                                            class="btn-action btn-view" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if($user['id'] != $_SESSION['admin_id']): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <button type="submit" class="btn-action btn-<?php echo $user_status == 'active' ? 'warning' : 'success'; ?>" 
                                                                    title="<?php echo $user_status == 'active' ? 'Deactivate' : 'Activate'; ?>">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>
                                                        </form>
                                                        
                                                        <?php if(!$user['is_admin']): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <input type="hidden" name="action" value="make_admin">
                                                                <button type="submit" class="btn-action btn-info" title="Make Admin">
                                                                    <i class="fas fa-shield-alt"></i>
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                                <input type="hidden" name="action" value="remove_admin">
                                                                <button type="submit" class="btn-action btn-warning" title="Remove Admin">
                                                                    <i class="fas fa-user-times"></i>
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <button onclick="confirmDeleteUser(<?php echo $user['id']; ?>)" 
                                                                class="btn-action btn-delete" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="badge badge-info">You</span>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if($page > 1): ?>
                                    <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $role !== '' ? '&role='.$role : ''; ?>" 
                                       class="page-link">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <div class="page-numbers">
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $role !== '' ? '&role='.$role : ''; ?>" 
                                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?><?php echo $role !== '' ? '&role='.$role : ''; ?>" 
                                       class="page-link">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-users-slash"></i>
                            <h4>No users found</h4>
                            <p>Try changing your filters or add a new user</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Add User Modal -->
<div id="addUserModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            <button class="close-modal" onclick="hideModal('addUserModal')">&times;</button>
        </div>
        <form method="POST" action="add_user_handler.php">
            <div class="form-group">
                <label>Username *</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label>Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Role</label>
                <select name="is_admin" class="form-control">
                    <option value="0">Regular User</option>
                    <option value="1">Administrator</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Create User
                </button>
                <button type="button" class="btn btn-secondary" onclick="hideModal('addUserModal')">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- User Details Modal -->
<div id="userDetailsModal" class="modal">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h3><i class="fas fa-user"></i> User Details</h3>
            <button class="close-modal" onclick="hideModal('userDetailsModal')">&times;</button>
        </div>
        <div id="userDetailsContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<script>
function showAddUserModal() {
    showModal('addUserModal');
}

function showUserDetails(userId) {
    fetch('get_user_details.php?id=' + userId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('userDetailsContent').innerHTML = data;
            showModal('userDetailsModal');
        })
        .catch(error => {
            document.getElementById('userDetailsContent').innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
            showModal('userDetailsModal');
        });
}

function confirmDeleteUser(userId) {
    if(confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const userIdInput = document.createElement('input');
        userIdInput.type = 'hidden';
        userIdInput.name = 'user_id';
        userIdInput.value = userId;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        form.appendChild(userIdInput);
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}

// Modal functions from footer.php
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) {
        modal.style.display = 'flex';
    }
}

function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(e) {
    if(e.target.classList.contains('modal')) {
        e.target.style.display = 'none';
    }
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const actionButtons = document.querySelectorAll('.btn-action');
    actionButtons.forEach(button => {
        button.addEventListener('mouseenter', function(e) {
            const title = this.getAttribute('title');
            if(title) {
                const tooltip = document.createElement('div');
                tooltip.className = 'tooltip';
                tooltip.textContent = title;
                document.body.appendChild(tooltip);
                
                const rect = this.getBoundingClientRect();
                tooltip.style.position = 'fixed';
                tooltip.style.left = (rect.left + rect.width/2 - tooltip.offsetWidth/2) + 'px';
                tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
                tooltip.style.background = '#333';
                tooltip.style.color = 'white';
                tooltip.style.padding = '5px 10px';
                tooltip.style.borderRadius = '4px';
                tooltip.style.fontSize = '12px';
                tooltip.style.zIndex = '10000';
                tooltip.style.whiteSpace = 'nowrap';
                
                this._tooltip = tooltip;
            }
        });
        
        button.addEventListener('mouseleave', function() {
            if(this._tooltip) {
                this._tooltip.remove();
                delete this._tooltip;
            }
        });
    });
});
</script>

<style>
    /* Enhanced User Management CSS */

/* Main Layout */
.admin-container {
    display: flex;
    min-height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.main-content {
    flex: 1;
    margin-left: 260px;
    background: #f5f7fa;
    min-height: 100vh;
}

/* Topbar Enhancement */
.topbar {
    background: white;
    padding: 0 30px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: blur(10px);
    background: rgba(255, 255, 255, 0.95);
}

.breadcrumb {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 15px;
    color: #64748b;
}

.breadcrumb a {
    color: var(--primary);
    text-decoration: none;
    transition: all 0.3s;
    padding: 6px 12px;
    border-radius: 8px;
}

.breadcrumb a:hover {
    background: rgba(102, 126, 234, 0.1);
    color: #5a67d8;
}

.breadcrumb span {
    color: #cbd5e1;
}

.topbar-actions .btn {
    padding: 12px 24px;
    font-size: 15px;
    font-weight: 600;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.topbar-actions .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Content Wrapper */
.content-wrapper {
    padding: 30px;
    animation: fadeIn 0.5s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Card Enhancement */
.card {
    background: white;
    border-radius: 16px;
    box-shadow: var(--shadow);
    margin-bottom: 30px;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.card-header {
    padding: 25px 30px;
    border-bottom: 1px solid #e2e8f0;
    background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-header h3 {
    margin: 0;
    color: #1e293b;
    font-size: 1.5rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card-header h3 i {
    color: var(--primary);
    background: rgba(102, 126, 234, 0.1);
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.card-body {
    padding: 30px;
}

/* Filter Form Enhancement */
.filter-form .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    align-items: end;
}

.form-group {
    position: relative;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #475569;
    font-size: 14px;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    transition: all 0.3s;
    background: white;
    font-family: inherit;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    transform: translateY(-1px);
}

/* Button Enhancement */
.btn {
    padding: 14px 28px;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.btn::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 5px;
    height: 5px;
    background: rgba(255, 255, 255, 0.5);
    opacity: 0;
    border-radius: 100%;
    transform: scale(1, 1) translate(-50%);
    transform-origin: 50% 50%;
}

.btn:focus:not(:active)::after {
    animation: ripple 1s ease-out;
}

@keyframes ripple {
    0% {
        transform: scale(0, 0);
        opacity: 0.5;
    }
    100% {
        transform: scale(20, 20);
        opacity: 0;
    }
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
}

.btn-secondary:hover {
    background: linear-gradient(135deg, #475569 0%, #334155 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(100, 116, 139, 0.4);
}

/* Table Enhancement */
.table-responsive {
    overflow-x: auto;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
}

.data-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 14px;
}

.data-table thead {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
}

.data-table th {
    padding: 18px 20px;
    text-align: left;
    font-weight: 700;
    color: #334155;
    border-bottom: 2px solid #e2e8f0;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.data-table td {
    padding: 20px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    transition: background-color 0.2s;
}

.data-table tbody tr {
    transition: all 0.2s ease;
}

.data-table tbody tr:hover {
    background: rgba(102, 126, 234, 0.05);
    transform: scale(1.002);
}

.data-table tbody tr:last-child td {
    border-bottom: none;
}

/* User Info Enhancement */
.user-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.user-avatar {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    overflow: hidden;
    flex-shrink: 0;
    position: relative;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.user-avatar::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    opacity: 0.8;
}

.user-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: relative;
    z-index: 1;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 20px;
    color: white;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

.user-details {
    flex: 1;
    min-width: 0;
}

.user-details strong {
    display: block;
    font-size: 15px;
    color: #1e293b;
    margin-bottom: 4px;
    font-weight: 600;
}

.user-details .text-muted {
    font-size: 13px;
    color: #64748b;
    display: block;
    margin-bottom: 6px;
}

.user-stats {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

.stat-badge {
    background: rgba(102, 126, 234, 0.1);
    color: var(--primary);
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.contact-info div {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
    color: #475569;
}

.contact-info i {
    width: 20px;
    color: #94a3b8;
    font-size: 14px;
}

/* Badge Enhancement */
.badge {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    display: inline-block;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.badge-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
}

.badge-secondary {
    background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    color: white;
}

.badge-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.badge-info {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
}

.badge-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

/* Status Badge Enhancement */
.status-badge {
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s;
}

.status-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.status-active {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-left: 4px solid #10b981;
}

.status-active::before {
    background: #10b981;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.5);
    animation: pulse 2s infinite;
}

.status-inactive {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    color: #6b7280;
    border-left: 4px solid #9ca3af;
}

.status-inactive::before {
    background: #9ca3af;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
    }
}

/* Action Buttons Enhancement */
.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.btn-action {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    color: white;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.btn-action::after {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.3) 0%, rgba(255, 255, 255, 0) 70%);
    opacity: 0;
    transition: opacity 0.3s;
}

.btn-action:hover::after {
    opacity: 1;
}

.btn-action:hover {
    transform: translateY(-3px) scale(1.1);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
}

.btn-view {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
}

.btn-edit {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.btn-delete {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.btn-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.btn-info {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
}

/* Empty State Enhancement */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-radius: 16px;
    border: 2px dashed #cbd5e1;
}

.empty-state i {
    font-size: 72px;
    color: #cbd5e1;
    margin-bottom: 24px;
    background: white;
    width: 120px;
    height: 120px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 30px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
}

.empty-state h4 {
    color: #334155;
    margin-bottom: 12px;
    font-size: 22px;
    font-weight: 700;
}

.empty-state p {
    color: #64748b;
    margin-bottom: 30px;
    font-size: 15px;
    max-width: 400px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

/* Pagination Enhancement */
.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 40px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
}

.page-numbers {
    display: flex;
    gap: 8px;
    align-items: center;
}

.page-link {
    padding: 12px 18px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    text-decoration: none;
    color: #475569;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
    min-width: 46px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.page-link::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    opacity: 0;
    transition: opacity 0.3s;
    z-index: -1;
}

.page-link:hover {
    border-color: var(--primary);
    color: var(--primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.page-link.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.page-link.active::before {
    opacity: 1;
}

.page-link.active:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Modal Enhancement */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(5px);
    animation: modalFadeIn 0.3s ease;
}

@keyframes modalFadeIn {
    from {
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    to {
        opacity: 1;
        backdrop-filter: blur(5px);
    }
}

.modal-content {
    background: white;
    padding: 40px;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: modalSlideIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f1f5f9;
}

.modal-header h3 {
    color: #1e293b;
    font-size: 24px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
}

.close-modal {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #64748b;
    transition: all 0.3s;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-modal:hover {
    background: #f1f5f9;
    color: var(--danger);
    transform: rotate(90deg);
}

/* Form Actions */
.form-actions {
    display: flex;
    gap: 15px;
    padding-top: 30px;
    border-top: 1px solid #e2e8f0;
    margin-top: 30px;
}

/* Loading Animation */
.loading-spinner {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    backdrop-filter: blur(10px);
}

.spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Toast Notification */
.toast {
    position: fixed;
    top: 30px;
    right: 30px;
    padding: 20px 25px;
    border-radius: 12px;
    color: white;
    font-weight: 600;
    z-index: 10001;
    animation: slideInRight 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 15px;
    min-width: 300px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

.toast-success {
    background: linear-gradient(135deg, rgba(16, 185, 129, 0.9) 0%, rgba(5, 150, 105, 0.9) 100%);
}

.toast-error {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.9) 0%, rgba(220, 38, 38, 0.9) 100%);
}

.toast-warning {
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.9) 0%, rgba(217, 119, 6, 0.9) 100%);
}

.toast-info {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.9) 0%, rgba(29, 78, 216, 0.9) 100%);
}

/* Tooltip Enhancement */
.tooltip {
    position: fixed;
    background: #1e293b;
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 500;
    z-index: 10000;
    pointer-events: none;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    max-width: 200px;
    white-space: nowrap;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.1);
}

.tooltip::before {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 6px solid transparent;
    border-top-color: #1e293b;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .sidebar {
        width: 240px;
    }
    
    .main-content {
        margin-left: 240px;
    }
}

@media (max-width: 992px) {
    .sidebar {
        transform: translateX(-100%);
        box-shadow: none;
    }
    
    .sidebar.active {
        transform: translateX(0);
        box-shadow: 2px 0 15px rgba(0, 0, 0, 0.2);
    }
    
    .main-content {
        margin-left: 0;
    }
    
    .topbar {
        padding: 0 20px;
        flex-direction: column;
        height: auto;
        padding: 15px 20px;
        gap: 15px;
    }
    
    .filter-form .form-row {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .content-wrapper {
        padding: 20px;
    }
    
    .card-header, .card-body {
        padding: 20px;
    }
    
    .user-info {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .user-details {
        text-align: center;
    }
    
    .user-stats {
        justify-content: center;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .pagination {
        flex-direction: column;
        gap: 20px;
    }
    
    .page-numbers {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .toast {
        left: 20px;
        right: 20px;
        min-width: auto;
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    .main-content {
        background: #0f172a;
    }
    
    .card {
        background: #1e293b;
        border-color: #334155;
    }
    
    .card-header {
        background: linear-gradient(90deg, #1e293b 0%, #1a202c 100%);
    }
    
    .card-header h3 {
        color: #f1f5f9;
    }
    
    .form-control {
        background: #334155;
        border-color: #475569;
        color: #f1f5f9;
    }
    
    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }
    
    .data-table th {
        background: linear-gradient(135deg, #1e293b 0%, #1a202c 100%);
        color: #cbd5e1;
        border-bottom-color: #475569;
    }
    
    .data-table td {
        color: #e2e8f0;
        border-bottom-color: #334155;
    }
    
    .data-table tbody tr:hover {
        background: rgba(102, 126, 234, 0.1);
    }
    
    .user-details strong {
        color: #f1f5f9;
    }
    
    .user-details .text-muted {
        color: #94a3b8;
    }
    
    .contact-info div {
        color: #cbd5e1;
    }
    
    .empty-state {
        background: linear-gradient(135deg, #1e293b 0%, #1a202c 100%);
        border-color: #475569;
    }
    
    .empty-state h4 {
        color: #f1f5f9;
    }
    
    .empty-state p {
        color: #94a3b8;
    }
    
    .page-link {
        background: #334155;
        border-color: #475569;
        color: #cbd5e1;
    }
    
    .modal-content {
        background: #1e293b;
        border-color: #334155;
    }
    
    .modal-header h3 {
        color: #f1f5f9;
    }
    
    .close-modal {
        color: #94a3b8;
    }
    
    .close-modal:hover {
        background: #334155;
    }
}
</style>


<?php include 'footer.php'; ?>