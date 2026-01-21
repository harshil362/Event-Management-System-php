<aside class="sidebar">
    <div class="sidebar-header">
        <div class="admin-info">
            <div class="admin-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div>
                <h4><?php echo $_SESSION['admin_username']; ?></h4>
                <p class="admin-role">Administrator</p>
            </div>
        </div>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span>Event Management</span>
            </li>
            <li>
                <a href="manage_events.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>All Events</span>
                </a>
            </li>
            <li>
                <a href="add_event.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_event.php' ? 'active' : ''; ?>">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add New Event</span>
                </a>
            </li>
            <li>
                <a href="upcoming_events.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'upcoming_events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-clock"></i>
                    <span>Upcoming Events</span>
                </a>
            </li>
            <li>
                <a href="past_events.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'past_events.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Past Events</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span>Categories</span>
            </li>
            <li>
                <a href="manage_categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_categories.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tags"></i>
                    <span>Manage Categories</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span>Users & Bookings</span>
            </li>
            <li>
                <a href="manage_users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_users.php' ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
            </li>
            <li>
                <a href="bookings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-ticket-alt"></i>
                    <span>Bookings</span>
                </a>
            </li>
            
            <li class="nav-section">
                <span>Settings</span>
            </li>
            <li>
                <a href="settings.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
                    <i class="fas fa-cog"></i>
                    <span>General Settings</span>
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user-cog"></i>
                    <span>Profile Settings</span>
                </a>
            </li>
            
            <li class="logout-li">
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </nav>
</aside>

<style>
.sidebar {
    width: 260px;
    background: linear-gradient(180deg, var(--dark) 0%, #2d3748 100%);
    color: white;
    height: 100vh;
    position: fixed;
    overflow-y: auto;
    transition: width 0.3s;
    z-index: 100;
}

.sidebar-header {
    padding: 25px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.admin-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.admin-avatar {
    width: 50px;
    height: 50px;
    background: var(--primary);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
}

.admin-info h4 {
    font-size: 16px;
    margin-bottom: 5px;
}

.admin-role {
    font-size: 12px;
    color: #a0aec0;
}

.sidebar-nav ul {
    list-style: none;
    padding: 20px 0;
}

.sidebar-nav li {
    margin-bottom: 2px;
}

.sidebar-nav a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #cbd5e0;
    text-decoration: none;
    transition: all 0.3s;
}

.sidebar-nav a:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    padding-left: 25px;
}

.sidebar-nav a.active {
    background: var(--primary);
    color: white;
    border-left: 4px solid white;
}

.sidebar-nav i {
    width: 24px;
    margin-right: 12px;
    font-size: 16px;
    text-align: center;
}

.nav-section {
    padding: 15px 20px 5px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #718096;
    font-weight: 600;
}

.logout-li {
    margin-top: 30px;
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 10px;
}

.logout-link {
    color: #fc8181 !important;
}

.logout-link:hover {
    background: rgba(252, 129, 129, 0.1) !important;
}
</style>