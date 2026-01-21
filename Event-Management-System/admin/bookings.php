<?php
require_once 'header.php';
require_once '../db_connection.php';

$page_title = "Bookings & Reports";

// Pagination
$limit = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? '';
$event_id = $_GET['event_id'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

// Build query
$where = "WHERE 1=1";
$params = [];

if(!empty($search)) {
    $where .= " AND (b.booking_id LIKE ? OR u.username LIKE ? OR u.email LIKE ? OR e.title LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if(!empty($status)) {
    $where .= " AND b.status = ?";
    $params[] = $status;
}

if(!empty($event_id)) {
    $where .= " AND b.event_id = ?";
    $params[] = $event_id;
}

if(!empty($date_from)) {
    $where .= " AND DATE(b.booking_date) >= ?";
    $params[] = $date_from;
}

if(!empty($date_to)) {
    $where .= " AND DATE(b.booking_date) <= ?";
    $params[] = $date_to;
}

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM bookings b
              JOIN users u ON b.user_id = u.id
              JOIN events e ON b.event_id = e.id
              $where";
$count_stmt = $conn->prepare($count_sql);
if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_result = $count_stmt->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// Get bookings
$sql = "SELECT b.*, u.username, u.email, u.phone, e.title as event_title, 
               e.event_date, e.location, e.price, e.image
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN events e ON b.event_id = e.id
        $where 
        ORDER BY b.booking_date DESC 
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

// Get events for filter dropdown
$events = $conn->query("SELECT id, title FROM events ORDER BY title");

// Get statistics
$stats = [
    'total_bookings' => $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'],
    'confirmed_bookings' => $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'")->fetch_assoc()['total'],
    'pending_bookings' => $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")->fetch_assoc()['total'],
    'cancelled_bookings' => $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'cancelled'")->fetch_assoc()['total'],
    'total_revenue' => $conn->query("SELECT SUM(e.price) as total FROM bookings b JOIN events e ON b.event_id = e.id WHERE b.status = 'confirmed'")->fetch_assoc()['total'] ?? 0,
    'today_bookings' => $conn->query("SELECT COUNT(*) as total FROM bookings WHERE DATE(booking_date) = CURDATE()")->fetch_assoc()['total']
];

// Handle booking actions
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $booking_id = (int)$_POST['booking_id'];
    
    switch($action) {
        case 'confirm':
            $conn->query("UPDATE bookings SET status = 'confirmed' WHERE id = $booking_id");
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Booking confirmed successfully'
            ];
            break;
            
        case 'cancel':
            $conn->query("UPDATE bookings SET status = 'cancelled' WHERE id = $booking_id");
            $_SESSION['toast'] = [
                'type' => 'warning',
                'message' => 'Booking cancelled'
            ];
            break;
            
        case 'delete':
            $conn->query("DELETE FROM bookings WHERE id = $booking_id");
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Booking deleted successfully'
            ];
            break;
            
        case 'export_csv':
            exportBookingsCSV();
            exit();
    }
    
    header("Location: bookings.php");
    exit();
}

function exportBookingsCSV() {
    global $conn;
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bookings_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Booking ID', 'User', 'Email', 'Event', 'Event Date', 'Booking Date', 'Status', 'Amount', 'Location']);
    
    $result = $conn->query("
        SELECT b.booking_id, u.username, u.email, e.title, e.event_date, 
               b.booking_date, b.status, e.price, e.location
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        JOIN events e ON b.event_id = e.id
        ORDER BY b.booking_date DESC
    ");
    
    while($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['booking_id'],
            $row['username'],
            $row['email'],
            $row['title'],
            $row['event_date'],
            $row['booking_date'],
            $row['status'],
            $row['price'],
            $row['location']
        ]);
    }
    
    fclose($output);
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
                <span>Bookings & Reports</span>
            </div>
            <div class="topbar-actions">
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="export_csv">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-file-export"></i> Export CSV
                    </button>
                </form>
            </div>
        </div>
        
        <div class="content-wrapper">
            <!-- Stats Overview -->
            <div class="stats-grid mb-4">
                <div class="stat-card stat-1">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_bookings']; ?></h3>
                        <p>Total Bookings</p>
                    </div>
                </div>
                
                <div class="stat-card stat-2">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['confirmed_bookings']; ?></h3>
                        <p>Confirmed</p>
                    </div>
                </div>
                
                <div class="stat-card stat-3">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['pending_bookings']; ?></h3>
                        <p>Pending</p>
                    </div>
                </div>
                
                <div class="stat-card stat-4">
                    <div class="stat-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>₹<?php echo number_format($stats['total_revenue'], 2); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h3><i class="fas fa-filter"></i> Filter Bookings</h3>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <input type="text" name="search" placeholder="Search bookings..." 
                                       value="<?php echo htmlspecialchars($search); ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="confirmed" <?php echo $status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="cancelled" <?php echo $status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <select name="event_id" class="form-control">
                                    <option value="">All Events</option>
                                    <?php while($event = $events->fetch_assoc()): ?>
                                        <option value="<?php echo $event['id']; ?>" 
                                                <?php echo $event_id == $event['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>From Date</label>
                                <input type="date" name="date_from" value="<?php echo $date_from; ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label>To Date</label>
                                <input type="date" name="date_to" value="<?php echo $date_to; ?>" 
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                                <a href="bookings.php" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Bookings Table -->
            <div class="card">
                <div class="card-header">
                    <h3>Bookings (<?php echo $total_rows; ?>)</h3>
                </div>
                <div class="card-body">
                    <?php if($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Booking ID</th>
                                        <th>User</th>
                                        <th>Event</th>
                                        <th>Date & Time</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo $booking['booking_id']; ?></strong>
                                                <br><small><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></small>
                                            </td>
                                            <td>
                                                <div class="user-info">
                                                    <div class="user-avatar">
                                                        <div class="avatar-placeholder">
                                                            <?php echo strtoupper(substr($booking['username'], 0, 1)); ?>
                                                        </div>
                                                    </div>
                                                    <div class="user-details">
                                                        <strong><?php echo htmlspecialchars($booking['username']); ?></strong>
                                                        <small class="text-muted"><?php echo $booking['email']; ?></small>
                                                        <?php if($booking['phone']): ?>
                                                            <small class="text-muted"><?php echo $booking['phone']; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['event_title']); ?></strong>
                                                <br><small><?php echo date('M d, Y', strtotime($booking['event_date'])); ?></small>
                                                <br><small><?php echo $booking['location']; ?></small>
                                            </td>
                                            <td>
                                                <?php echo date('M d, Y h:i A', strtotime($booking['booking_date'])); ?>
                                            </td>
                                            <td>
                                                <strong class="text-primary">₹<?php echo number_format($booking['price'], 2); ?></strong>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['status']; ?>">
                                                    <?php echo ucfirst($booking['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <button type="button" onclick="showBookingDetails(<?php echo $booking['id']; ?>)" 
                                                            class="btn-action btn-view" title="View Details">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if($booking['status'] == 'pending'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="action" value="confirm">
                                                            <button type="submit" class="btn-action btn-success" title="Confirm Booking">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($booking['status'] != 'cancelled'): ?>
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                            <input type="hidden" name="action" value="cancel">
                                                            <button type="submit" class="btn-action btn-warning" title="Cancel Booking">
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <button onclick="confirmDeleteBooking(<?php echo $booking['id']; ?>)" 
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
                            <h4>No bookings found</h4>
                            <p>Try changing your filters</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reports Section -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3><i class="fas fa-chart-bar"></i> Reports & Analytics</h3>
                </div>
                <div class="card-body">
                    <div class="reports-grid">
                        <div class="report-card">
                            <h4>Daily Bookings</h4>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="dailyBookingsChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="report-card">
                            <h4>Booking Status Distribution</h4>
                            <div class="chart-container" style="height: 200px;">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                        
                        <div class="report-card">
                            <h4>Top Events by Bookings</h4>
                            <div class="top-events-list">
                                <?php
                                $top_events = $conn->query("
                                    SELECT e.title, COUNT(b.id) as booking_count
                                    FROM events e
                                    LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
                                    GROUP BY e.id
                                    ORDER BY booking_count DESC
                                    LIMIT 5
                                ");
                                
                                while($event = $top_events->fetch_assoc()): ?>
                                    <div class="event-item">
                                        <span class="event-name"><?php echo htmlspecialchars($event['title']); ?></span>
                                        <span class="booking-count"><?php echo $event['booking_count']; ?> bookings</span>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Booking Details Modal -->
<div id="bookingDetailsModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h3><i class="fas fa-ticket-alt"></i> Booking Details</h3>
            <button class="close-modal" onclick="hideModal('bookingDetailsModal')">&times;</button>
        </div>
        <div id="bookingDetailsContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Daily Bookings Chart
const dailyCtx = document.getElementById('dailyBookingsChart')?.getContext('2d');
if(dailyCtx) {
    fetch('get_daily_bookings.php')
        .then(response => response.json())
        .then(data => {
            new Chart(dailyCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Bookings',
                        data: data.values,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.3,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });
        });
}

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart')?.getContext('2d');
if(statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Cancelled'],
            datasets: [{
                data: [
                    <?php echo $stats['confirmed_bookings']; ?>,
                    <?php echo $stats['pending_bookings']; ?>,
                    <?php echo $stats['cancelled_bookings']; ?>
                ],
                backgroundColor: [
                    '#10b981',
                    '#f59e0b',
                    '#ef4444'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

function showBookingDetails(bookingId) {
    fetch('get_booking_details.php?id=' + bookingId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('bookingDetailsContent').innerHTML = data;
            showModal('bookingDetailsModal');
        });
}

function confirmDeleteBooking(bookingId) {
    if(confirm('Are you sure you want to delete this booking? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        
        const bookingIdInput = document.createElement('input');
        bookingIdInput.type = 'hidden';
        bookingIdInput.name = 'booking_id';
        bookingIdInput.value = bookingId;
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete';
        
        form.appendChild(bookingIdInput);
        form.appendChild(actionInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
    /* Enhanced Bookings & Reports CSS */

/* Stats Grid Enhancement */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 24px;
    margin-bottom: 40px;
}

.stat-card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 24px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
    border: 1px solid rgba(226, 232, 240, 0.8);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    opacity: 0;
    transition: opacity 0.3s;
}

.stat-card:hover::before {
    opacity: 1;
}

.stat-card:hover {
    transform: translateY(-6px) scale(1.02);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: white;
    flex-shrink: 0;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    position: relative;
    overflow: hidden;
}

.stat-icon::before {
    content: '';
    position: absolute;
    width: 100%;
    height: 100%;
    background: inherit;
    opacity: 0.8;
    filter: brightness(0.9);
}

.stat-icon i {
    position: relative;
    z-index: 1;
}

.stat-1 .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-2 .stat-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-3 .stat-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-4 .stat-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.stat-info {
    flex: 1;
    min-width: 0;
}

.stat-info h3 {
    font-size: 42px;
    margin-bottom: 8px;
    color: #1e293b;
    font-weight: 800;
    background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    line-height: 1;
}

.stat-info p {
    color: #64748b;
    font-size: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-trend {
    font-size: 14px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 8px;
}

.trend-up {
    background: rgba(16, 185, 129, 0.1);
    color: #10b981;
}

.trend-down {
    background: rgba(239, 68, 68, 0.1);
    color: #ef4444;
}

/* Reports Grid Enhancement */
.reports-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 24px;
    margin-top: 24px;
}

.report-card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: var(--shadow);
    border: 1px solid rgba(226, 232, 240, 0.8);
    transition: all 0.3s ease;
}

.report-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-lg);
}

.report-card h4 {
    color: #1e293b;
    margin-bottom: 20px;
    font-size: 18px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f1f5f9;
}

.report-card h4 i {
    color: var(--primary);
    background: rgba(102, 126, 234, 0.1);
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.chart-container {
    position: relative;
    height: 240px;
    margin: -10px;
}

/* Top Events List Enhancement */
.top-events-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.event-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 18px;
    background: #f8fafc;
    border-radius: 12px;
    transition: all 0.3s ease;
    border-left: 4px solid var(--primary);
}

.event-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.event-item:nth-child(2) {
    border-left-color: #10b981;
}

.event-item:nth-child(3) {
    border-left-color: #f59e0b;
}

.event-item:nth-child(4) {
    border-left-color: #8b5cf6;
}

.event-item:nth-child(5) {
    border-left-color: #ef4444;
}

.event-name {
    font-weight: 600;
    color: #334155;
    flex: 1;
    min-width: 0;
    padding-right: 20px;
}

.booking-count {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 700;
    min-width: 100px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    transition: all 0.3s ease;
}

.event-item:hover .booking-count {
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

/* Status Badge Variations */
.status-pending {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #92400e;
    border-left: 4px solid #f59e0b;
}

.status-pending::before {
    background: #f59e0b;
    animation: pulse 2s infinite;
}

.status-confirmed {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    color: #065f46;
    border-left: 4px solid #10b981;
}

.status-confirmed::before {
    background: #10b981;
    animation: pulse 2s infinite;
}

.status-cancelled {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #991b1b;
    border-left: 4px solid #ef4444;
}

.status-cancelled::before {
    background: #ef4444;
}

.status-processing {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    color: #1e40af;
    border-left: 4px solid #3b82f6;
}

.status-processing::before {
    background: #3b82f6;
    animation: pulse 2s infinite;
}

/* Amount Styling */
.amount {
    font-weight: 700;
    font-size: 16px;
    color: #1e293b;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.amount::before {
    content: '₹';
    font-size: 14px;
    opacity: 0.7;
}

.amount-positive {
    color: #10b981;
}

.amount-negative {
    color: #ef4444;
}

/* Export Button Enhancement */
.btn-export {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 14px 28px;
    border-radius: 12px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    box-shadow: 0 6px 20px rgba(16, 185, 129, 0.3);
    position: relative;
    overflow: hidden;
}

.btn-export::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-export:hover::before {
    left: 100%;
}

.btn-export:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.4);
}

/* Date Range Picker Enhancement */
.date-range {
    display: flex;
    gap: 12px;
    align-items: end;
}

.date-range .form-group {
    flex: 1;
}

.date-range label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #475569;
    font-size: 14px;
}

/* Quick Filters */
.quick-filters {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-chip {
    padding: 10px 20px;
    background: #f1f5f9;
    border: 2px solid #e2e8f0;
    border-radius: 25px;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.filter-chip:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
    transform: translateY(-2px);
}

.filter-chip.active {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: white;
    border-color: var(--primary);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.filter-chip i {
    font-size: 12px;
}

/* Booking Details Enhancement */
.booking-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
    margin-bottom: 30px;
}

.info-card {
    background: #f8fafc;
    padding: 24px;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.info-card:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
}

.info-card h4 {
    color: #475569;
    margin-bottom: 16px;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 10px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}

.info-card h4 i {
    color: var(--primary);
}

.info-card p {
    margin: 0 0 12px 0;
    color: #334155;
    font-size: 14px;
    line-height: 1.5;
}

.info-card strong {
    color: #1e293b;
    font-weight: 600;
    min-width: 120px;
    display: inline-block;
}

/* Timeline View */
.timeline-view {
    position: relative;
    padding-left: 30px;
}

.timeline-view::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 2px;
    background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
}

.timeline-item {
    position: relative;
    padding: 0 0 24px 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -29px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--primary);
    border: 3px solid white;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
}

.timeline-date {
    font-size: 12px;
    color: #64748b;
    font-weight: 600;
    margin-bottom: 4px;
}

.timeline-content {
    background: white;
    padding: 16px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-left: 4px solid var(--primary);
}

/* Chart Tooltip Enhancement */
.chart-tooltip {
    background: #1e293b !important;
    border: 1px solid rgba(255, 255, 255, 0.1) !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
    backdrop-filter: blur(10px) !important;
}

.chart-tooltip .label {
    color: #94a3b8 !important;
    font-size: 12px !important;
    font-weight: 600 !important;
    margin-bottom: 4px !important;
}

.chart-tooltip .value {
    color: white !important;
    font-size: 16px !important;
    font-weight: 700 !important;
}

/* Animation for Charts */
@keyframes chartAppear {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chart-container canvas {
    animation: chartAppear 0.8s ease-out;
}

/* Progress Bars */
.progress-bar {
    height: 8px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
    margin: 10px 0;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 4px;
    transition: width 1s ease-out;
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Dark Mode for Bookings */
@media (prefers-color-scheme: dark) {
    .stat-card {
        background: #1e293b;
        border-color: #334155;
    }
    
    .stat-info h3 {
        background: linear-gradient(135deg, #f1f5f9 0%, #cbd5e1 100%);
     
        -webkit-text-fill-color: transparent;
    }
    
    .stat-info p {
        color: #94a3b8;
    }
    
    .report-card {
        background: #1e293b;
        border-color: #334155;
    }
    
    .report-card h4 {
        color: #f1f5f9;
        border-bottom-color: #334155;
    }
    
    .event-item {
        background: #334155;
        border-left-color: var(--primary);
    }
    
    .event-item:hover {
        background: #475569;
    }
    
    .event-name {
        color: #e2e8f0;
    }
    
    .info-card {
        background: #334155;
        border-color: #475569;
    }
    
    .info-card:hover {
        background: #475569;
    }
    
    .info-card h4 {
        color: #cbd5e1;
        border-bottom-color: #475569;
    }
    
    .info-card p {
        color: #e2e8f0;
    }
    
    .info-card strong {
        color: #f1f5f9;
    }
    
    .timeline-content {
        background: #334155;
        border-left-color: var(--primary);
    }
    
    .filter-chip {
        background: #334155;
        border-color: #475569;
        color: #cbd5e1;
    }
    
    .filter-chip:hover {
        background: #475569;
        border-color: #64748b;
    }
    
    .filter-chip.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    }
}



/* Loading States */
.loading {
    position: relative;
    overflow: hidden;
}

.loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% {
        transform: translateX(-100%);
    }
    100% {
        transform: translateX(100%);
    }
}

/* Print Styles */
@media print {
    .sidebar, .topbar, .btn-export, .action-buttons, .filter-form {
        display: none !important;
    }
    
    .main-content {
        margin-left: 0 !important;
        padding: 0 !important;
    }
    
    .card {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        page-break-inside: avoid;
    }
    
    .stat-card {
        break-inside: avoid;
    }
    
    .reports-grid {
        break-inside: avoid;
    }
}
</style>
<?php include 'footer.php'; ?>