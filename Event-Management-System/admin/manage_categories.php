<?php
session_start();
require_once '../db_connection.php'; 
// Check if admin is logged in
if(!isset($_SESSION['admin_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

$page_title = "Manage Categories";
$success = '';
$error = '';

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if($action == 'add') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        
        $stmt = $conn->prepare("INSERT INTO event_categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if($stmt->execute()) {
            $success = "Category added successfully!";
        } else {
            $error = "Failed to add category: " . $conn->error;
        }
    } elseif($action == 'delete') {
        $id = (int)$_POST['id'];
        
        // Check if category is used in events
        $check = $conn->prepare("SELECT COUNT(*) as count FROM events WHERE category_id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $result = $check->get_result()->fetch_assoc();
        
        if($result['count'] > 0) {
            $error = "Cannot delete category. It is being used by " . $result['count'] . " event(s).";
        } else {
            $stmt = $conn->prepare("DELETE FROM event_categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if($stmt->execute()) {
                $success = "Category deleted successfully!";
            } else {
                $error = "Failed to delete category: " . $conn->error;
            }
        }
    } elseif($action == 'edit') {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        
        $stmt = $conn->prepare("UPDATE event_categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        
        if($stmt->execute()) {
            $success = "Category updated successfully!";
        } else {
            $error = "Failed to update category: " . $conn->error;
        }
    }
}

// Get all categories
$categories = $conn->query("SELECT * FROM event_categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - EventPro Admin</title>
     <link rel="stylesheet" href="../admin/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: white;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            height: 100vh;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .sidebar-header h3 {
            font-size: 20px;
            font-weight: 600;
        }
        
        .sidebar-nav ul {
            list-style: none;
        }
        
        .sidebar-nav li {
            border-bottom: 1px solid #eee;
        }
        
        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: #333;
            text-decoration: none;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: #667eea;
            color: white;
        }
        
        .sidebar-nav i {
            margin-right: 10px;
            width: 20px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 260px;
        }
        
        .topbar {
            background: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .breadcrumb {
            color: #666;
            font-size: 14px;
        }
        
        .breadcrumb a {
            color: #667eea;
            text-decoration: none;
        }
        
        .breadcrumb span {
            margin: 0 8px;
        }
        
        .logout-btn {
            background: #ef4444;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #eee;
        }
        
        .card-header h3 {
            font-size: 18px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #0da271;
        }
        
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-error {
            background: #fee;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f8f9fa;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #eee;
        }
        
        .data-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .data-table tr:hover {
            background: #f8f9fa;
        }
        
        .category-badge {
            background: #e0f2fe;
            color: #0369a1;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 8px;
        }
        
        .btn-action {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
        }
        
        .btn-edit {
            background: #3b82f6;
        }
        
        .btn-delete {
            background: #ef4444;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #666;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            width: 90%;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>EventPro Admin</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="add_event.php"><i class="fas fa-plus-circle"></i> Add Event</a></li>
                    <li><a href="manage_categories.php" class="active"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="topbar">
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a>
                    <span>/</span>
                    <span>Manage Categories</span>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
            
            <div class="content-wrapper">
                <?php if($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add Category Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h3><i class="fas fa-plus"></i> Add New Category</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Category Name *</label>
                                    <input type="text" name="name" class="form-control" required 
                                           placeholder="e.g., Wedding, Corporate, Birthday">
                                </div>
                                
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea name="description" class="form-control" 
                                              placeholder="Optional description"></textarea>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Add Category
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-tags"></i> All Categories</h3>
                    </div>
                    <div class="card-body">
                        <?php if($categories->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($category = $categories->fetch_assoc()): ?>
                                            <tr>
                                                <td>#<?php echo $category['id']; ?></td>
                                                <td>
                                                    <span class="category-badge">
                                                        <?php echo htmlspecialchars($category['name']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($category['description'] ?? 'No description'); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($category['created_at'])); ?></td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <button type="button" class="btn-action btn-edit" 
                                                                onclick="openEditModal(<?php echo $category['id']; ?>, '<?php echo addslashes($category['name']); ?>', '<?php echo addslashes($category['description'] ?? ''); ?>')">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                            <button type="submit" class="btn-action btn-delete" 
                                                                    onclick="return confirm('Are you sure you want to delete this category?')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-tags"></i>
                                <h4>No categories found</h4>
                                <p>Add your first category using the form above</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <!-- Edit Category Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Category</h3>
                <button class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="editCategoryId">
                
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" id="editCategoryName" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="editCategoryDescription" class="form-control"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Category
                    </button>
                    <button type="button" class="btn" onclick="closeModal()" style="background: #6c757d; color: white;">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    function openEditModal(id, name, description) {
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = name;
        document.getElementById('editCategoryDescription').value = description;
        document.getElementById('editModal').style.display = 'flex';
    }
    
    function closeModal() {
        document.getElementById('editModal').style.display = 'none';
    }
    
    // Close modal when clicking outside
    document.getElementById('editModal').addEventListener('click', function(e) {
        if(e.target === this) {
            closeModal();
        }
    });
    </script>
</body>
</html>