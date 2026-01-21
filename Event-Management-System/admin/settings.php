<?php
require_once 'header.php';
require_once '../db_connection.php'; 

$page_title = "Settings";

// Handle settings update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $site_title = $_POST['site_title'];
    $admin_email = $_POST['admin_email'];
    $timezone = $_POST['timezone'];
    
    // You can save these in a settings table or use a config file
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Settings updated successfully!'
    ];
}
?>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="topbar">
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <span>Settings</span>
            </div>
        </div>
        
        <div class="content-wrapper">
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-cog"></i> General Settings</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="form-group">
                            <label>Site Title</label>
                            <input type="text" name="site_title" class="form-control" 
                                   value="EventPro" placeholder="Enter site title">
                        </div>
                        
                        <div class="form-group">
                            <label>Admin Email</label>
                            <input type="email" name="admin_email" class="form-control" 
                                   value="admin@eventpro.com" placeholder="Enter admin email">
                        </div>
                        
                        <div class="form-group">
                            <label>Timezone</label>
                            <select name="timezone" class="form-control">
                                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                                <option value="America/New_York">America/New_York (EST)</option>
                                <option value="Europe/London">Europe/London (GMT)</option>
                                <option value="Asia/Tokyo">Asia/Tokyo (JST)</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Events per Page</label>
                            <input type="number" name="events_per_page" class="form-control" 
                                   value="10" min="5" max="100">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Danger Zone -->
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle"></i>
                        These actions are irreversible. Please proceed with caution.
                    </div>
                    
                    <div class="danger-actions">
                        <div class="danger-action">
                            <h4>Clear Cache</h4>
                            <p>Remove temporary files and cached data</p>
                            <button class="btn btn-warning">
                                <i class="fas fa-broom"></i> Clear Cache
                            </button>
                        </div>
                        
                        <div class="danger-action">
                            <h4>Reset Statistics</h4>
                            <p>Reset all visitor statistics to zero</p>
                            <button class="btn btn-warning">
                                <i class="fas fa-chart-bar"></i> Reset Stats
                            </button>
                        </div>
                        
                        <div class="danger-action">
                            <h4>Export Database</h4>
                            <p>Download complete database backup</p>
                            <a href="export_db.php" class="btn btn-success">
                                <i class="fas fa-download"></i> Export DB
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.border-danger {
    border: 2px solid var(--danger);
}

.bg-danger {
    background: var(--danger) !important;
}

.alert-warning {
    background: #fffbeb;
    color: #92400e;
    border: 1px solid #fbbf24;
    padding: 15px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 20px;
}

.danger-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.danger-action {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid var(--danger);
}

.danger-action h4 {
    color: var(--dark);
    margin-bottom: 10px;
}

.danger-action p {
    color: var(--gray);
    margin-bottom: 15px;
    font-size: 14px;
}

.btn-warning {
    background: var(--warning);
    color: white;
}

.btn-warning:hover {
    background: #e69a0d;
}

.btn-success {
    background: var(--success);
    color: white;
}

.btn-success:hover {
    background: #0da271;
}
</style>

<?php include 'footer.php'; ?>