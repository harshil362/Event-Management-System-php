<?php
require_once 'header.php';
require_once '../db_connection.php'; 

$page_title = "Manage Events";

// Pagination
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Search and filter
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];

if(!empty($search)) {
    $where .= " AND (title LIKE ? OR description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
}

if(!empty($category)) {
    $where .= " AND category = ?";
    $params[] = $category;
}

if($status == 'upcoming') {
    $where .= " AND event_date >= CURDATE()";
} elseif($status == 'past') {
    $where .= " AND event_date < CURDATE()";
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM events $where";
$count_stmt = $conn->prepare($count_sql);
if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get events
$sql = "SELECT * FROM events $where ORDER BY event_date DESC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($sql);
if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category FROM events ORDER BY category");
?>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <span>Manage Events</span>
            </div>
            <div class="topbar-actions">
                <a href="add_event.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
        </div>
        
        <div class="content-wrapper">
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filter Events</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="search" placeholder="Search events..." 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <select name="category" class="form-control">
                                    <option value="">All Categories</option>
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['category']; ?>" 
                                                <?php echo $category == $cat['category'] ? 'selected' : ''; ?>>
                                            <?php echo $cat['category']; ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Events</option>
                                    <option value="upcoming" <?php echo $status == 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                    <option value="past" <?php echo $status == 'past' ? 'selected' : ''; ?>>Past Events</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="manage_events.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Events Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Events (<?php echo $total_rows; ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Date</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($event = $result->fetch_assoc()): 
                                        $is_upcoming = strtotime($event['event_date']) >= time();
                                        $status_class = $is_upcoming ? 'status-upcoming' : 'status-past';
                                        $status_text = $is_upcoming ? 'Upcoming' : 'Past';
                                    ?>
                                        <tr>
                                            <td>#<?php echo $event['id']; ?></td>
                                            <td>
                                                <?php if($event['image']): ?>
                                                    <img src="../uploads/event_images/<?php echo $event['image']; ?>" 
                                                         alt="<?php echo $event['title']; ?>" 
                                                         class="table-image">
                                                <?php else: ?>
                                                    <div class="no-image">No Image</div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo $event['title']; ?></strong>
                                                <small class="text-muted"><?php echo substr($event['description'], 0, 50); ?>...</small>
                                            </td>
                                            <td>
                                                <span class="category-badge"><?php echo $event['category']; ?></span>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                                <?php if($event['event_time']): ?>
                                                    <br><small><?php echo date('h:i A', strtotime($event['event_time'])); ?></small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $event['price'] ? '$' . number_format($event['price'], 2) : 'FREE'; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $status_class; ?>">
                                                    <?php echo $status_text; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" 
                                                       class="btn-action btn-edit" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="view_event.php?id=<?php echo $event['id']; ?>" 
                                                       class="btn-action btn-view" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button onclick="confirmDelete(<?php echo $event['id']; ?>)" 
                                                            class="btn-action btn-delete" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
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
                                    <a href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" 
                                       class="page-link">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <div class="page-numbers">
                                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                        <a href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" 
                                           class="page-link <?php echo $i == $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.$search : ''; ?>" 
                                       class="page-link">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-calendar-times"></i>
                            <h4>No events found</h4>
                            <p>Try changing your filters or add a new event</p>
                            <a href="add_event.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Your First Event
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.main-content {
    margin-left: 260px;
    min-height: 100vh;
    background: #f5f5f5;
}

.topbar {
    background: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.breadcrumb {
    color: var(--gray);
    font-size: 14px;
}

.breadcrumb a {
    color: var(--primary);
    text-decoration: none;
}

.breadcrumb span {
    margin: 0 8px;
}

.topbar-actions .btn {
    padding: 8px 16px;
    font-size: 14px;
}

.content-wrapper {
    padding: 30px;
}

.card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 30px;
}

.card-header {
    padding: 20px 25px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    font-size: 18px;
    color: var(--dark);
}

.card-body {
    padding: 25px;
}

.filter-form .form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.form-group {
    margin-bottom: 0;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
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
    transition: all 0.3s;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: #5a67d8;
    transform: translateY(-1px);
}

.btn-secondary {
    background: var(--gray);
    color: white;
}

.btn-secondary:hover {
    background: #4b5563;
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
    color: var(--dark);
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

.table-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 6px;
}

.no-image {
    width: 60px;
    height: 60px;
    background: #f1f1f1;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #999;
    font-size: 12px;
}

.category-badge {
    background: #e0f2fe;
    color: #0369a1;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-badge {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
}

.status-upcoming {
    background: #d1fae5;
    color: #065f46;
}

.status-past {
    background: #f3f4f6;
    color: #6b7280;
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
    transition: all 0.2s;
}

.btn-edit {
    background: var(--info);
}

.btn-view {
    background: var(--success);
}

.btn-delete {
    background: var(--danger);
    border: none;
    cursor: pointer;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.page-numbers {
    display: flex;
    gap: 8px;
}

.page-link {
    padding: 8px 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    text-decoration: none;
    color: var(--dark);
    font-size: 14px;
}

.page-link:hover {
    background: #f1f1f1;
}

.page-link.active {
    background: var(--primary);
    color: white;
    border-color: var(--primary);
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
    color: var(--dark);
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--gray);
    margin-bottom: 30px;
}
</style>

<script>
function confirmDelete(eventId) {
    if(confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
        window.location.href = 'delete_event.php?id=' + eventId;
    }
}
</script>

<?php include 'footer.php'; ?>