<?php
session_start();
require_once '../db_connection.php'; 

// Check if admin is logged in
if(!isset($_SESSION['admin_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Get stats
$total_events = $conn->query("SELECT COUNT(*) as total FROM events")->fetch_assoc()['total'];
$total_users = $conn->query("SELECT COUNT(*) as total FROM users WHERE is_admin = 0")->fetch_assoc()['total'];
$total_categories = $conn->query("SELECT COUNT(*) as total FROM event_categories")->fetch_assoc()['total'];
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];

// Get recent events
$recent_events = $conn->query("SELECT * FROM events ORDER BY created_at DESC LIMIT 5");

// Get upcoming events count
$upcoming_events = $conn->query("SELECT COUNT(*) as total FROM events WHERE event_date >= CURDATE()")->fetch_assoc()['total'];

// Get today's bookings
$today_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE DATE(booking_date) = CURDATE()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EventPro Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
            color: #333;
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
            overflow-y: auto;
        }
        
        .sidebar-header {
            padding: 25px 20px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
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
            transition: all 0.3s;
        }
        
        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: var(--primary);
            color: white;
        }
        
        .sidebar-nav i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-info i {
            color: var(--primary);
        }
        
        .logout-btn {
            background: var(--danger);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }
        
        .logout-btn:hover {
            background: #dc2626;
        }
        
        /* Dashboard Stats */
        .dashboard-content {
            padding: 30px;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            color: var(--dark);
            margin-bottom: 10px;
        }
        
        .dashboard-header p {
            color: var(--gray);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-1 .stat-icon { background: var(--primary); }
        .stat-2 .stat-icon { background: var(--success); }
        .stat-3 .stat-icon { background: var(--warning); }
        .stat-4 .stat-icon { background: var(--secondary); }
        
        .stat-info h3 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 14px;
        }
        
        /* Recent Events */
        .recent-events {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 30px;
        }
        
        .section-title {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .section-title h3 {
            color: var(--dark);
            font-size: 18px;
        }
        
        .event-list {
            list-style: none;
        }
        
        .event-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .event-item:last-child {
            border-bottom: none;
        }
        
        .event-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            overflow: hidden;
            margin-right: 15px;
        }
        
        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .event-details {
            flex: 1;
        }
        
        .event-title {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .event-meta {
            display: flex;
            gap: 15px;
            font-size: 12px;
            color: var(--gray);
        }
        
        .event-status {
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
        }
        
        .status-active { background: #d1fae5; color: #065f46; }
        .status-upcoming { background: #e0f2fe; color: #0369a1; }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .quick-actions h3 {
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .action-btn {
            background: white;
            border: 2px solid #e5e7eb;
            color: var(--dark);
            padding: 15px;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            border-color: var(--primary);
            background: #f8fafc;
            transform: translateY(-2px);
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }
        
        .action-1 .action-icon { background: var(--primary); }
        .action-2 .action-icon { background: var(--success); }
        .action-3 .action-icon { background: var(--warning); }
        .action-4 .action-icon { background: var(--info); }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>EventPro Admin</h3>
                <p style="font-size: 12px; opacity: 0.8;">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                    <li><a href="add_event.php"><i class="fas fa-plus-circle"></i> Add New Event</a></li>
                    <li><a href="manage_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="manage_users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li><a href="bookings.php"><i class="fas fa-ticket-alt"></i> Bookings</a></li>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Topbar -->
            <div class="topbar">
                <h2>Dashboard Overview</h2>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                    <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
            
            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <div class="dashboard-header">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>!</h1>
                    <p>Here's what's happening with your event management system</p>
                </div>
                
                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card stat-1">
                        <div class="stat-icon">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_events; ?></h3>
                            <p>Total Events</p>
                        </div>
                    </div>
                    
                    <div class="stat-card stat-2">
                        <div class="stat-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_users; ?></h3>
                            <p>Registered Users</p>
                        </div>
                    </div>
                    
                    <div class="stat-card stat-3">
                        <div class="stat-icon">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_bookings; ?></h3>
                            <p>Total Bookings</p>
                        </div>
                    </div>
                    
                    <div class="stat-card stat-4">
                        <div class="stat-icon">
                            <i class="fas fa-tags"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_categories; ?></h3>
                            <p>Categories</p>
                        </div>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
                    <!-- Recent Events -->
                    <div class="recent-events">
                        <div class="section-title">
                            <h3><i class="fas fa-history"></i> Recent Events</h3>
                            <a href="manage_events.php" style="font-size: 14px; color: var(--primary); text-decoration: none;">View All</a>
                        </div>
                        
                        <?php if($recent_events->num_rows > 0): ?>
                            <ul class="event-list">
                                <?php while($event = $recent_events->fetch_assoc()): 
                                    $is_upcoming = strtotime($event['event_date']) >= time();
                                ?>
                                    <li class="event-item">
                                        <div class="event-image">
                                            <?php if($event['image']): ?>
                                                <img src="../uploads/event_images/<?php echo htmlspecialchars($event['image']); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                                            <?php else: ?>
                                                <div style="width: 100%; height: 100%; background: #e5e7eb; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                                    <i class="fas fa-calendar"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="event-details">
                                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                            <div class="event-meta">
                                                <span><?php echo date('M d, Y', strtotime($event['event_date'])); ?></span>
                                                <span><?php echo $event['location']; ?></span>
                                                <span class="event-status <?php echo $is_upcoming ? 'status-upcoming' : 'status-active'; ?>">
                                                    <?php echo $is_upcoming ? 'Upcoming' : 'Active'; ?>
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                <?php endwhile; ?>
                            </ul>
                        <?php else: ?>
                            <div style="text-align: center; padding: 30px; color: var(--gray);">
                                <i class="fas fa-calendar-times" style="font-size: 40px; margin-bottom: 15px; opacity: 0.5;"></i>
                                <p>No events found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div style="display: flex; flex-direction: column; gap: 30px;">
                        <div class="recent-events">
                            <h3 style="margin-bottom: 20px;"><i class="fas fa-chart-line"></i> Quick Stats</h3>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: var(--primary); margin-bottom: 5px;">
                                        <?php echo $upcoming_events; ?>
                                    </div>
                                    <div style="font-size: 14px; color: var(--gray);">Upcoming Events</div>
                                </div>
                                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: var(--success); margin-bottom: 5px;">
                                        <?php echo $today_bookings; ?>
                                    </div>
                                    <div style="font-size: 14px; color: var(--gray);">Today's Bookings</div>
                                </div>
                                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: var(--warning); margin-bottom: 5px;">
                                        <?php echo $total_events > 0 ? '₹' . number_format($conn->query("SELECT SUM(price) as total FROM events")->fetch_assoc()['total'] ?? 0, 2) : '₹0'; ?>
                                    </div>
                                    <div style="font-size: 14px; color: var(--gray);">Total Event Value</div>
                                </div>
                                <div style="background: #f8fafc; padding: 20px; border-radius: 8px; text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: var(--info); margin-bottom: 5px;">
                                        <?php echo $total_users; ?>
                                    </div>
                                    <div style="font-size: 14px; color: var(--gray);">Active Users</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- System Info -->
                        <div class="recent-events">
                            <h3 style="margin-bottom: 20px;"><i class="fas fa-info-circle"></i> System Info</h3>
                            <div style="font-size: 14px; color: var(--gray);">
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>PHP Version:</span>
                                    <span style="font-weight: 500; color: var(--dark);"><?php echo phpversion(); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Database:</span>
                                    <span style="font-weight: 500; color: var(--dark);">MySQL</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee;">
                                    <span>Server Time:</span>
                                    <span style="font-weight: 500; color: var(--dark);"><?php echo date('Y-m-d H:i:s'); ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                                    <span>System Status:</span>
                                    <span style="color: var(--success); font-weight: 500;">Operational</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h3>Quick Actions</h3>
                    <div class="action-buttons">
                        <a href="add_event.php" class="action-btn action-1">
                            <div class="action-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Add New Event</div>
                                <div style="font-size: 12px; color: var(--gray);">Create a new event listing</div>
                            </div>
                        </a>
                        
                        <a href="manage_events.php" class="action-btn action-2">
                            <div class="action-icon">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;">Manage Events</div>
                                <div style="font-size: 12px; color: var(--gray);">Edit or delete existing events</div>
                            </div>
                        </a>
                        
                        <a href="manage_users.php" class="action-btn action-3">
                            <div class="action-icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;">User Management</div>
                                <div style="font-size: 12px; color: var(--gray);">Manage registered users</div>
                            </div>
                        </a>
                        
                        <a href="bookings.php" class="action-btn action-4">
                            <div class="action-icon">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600;">View Reports</div>
                                <div style="font-size: 12px; color: var(--gray);">Check booking statistics</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>