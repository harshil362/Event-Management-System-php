<?php
require_once '../db_connection.php';
session_start();

if(!isset($_SESSION['admin_id'])) {
    die('Unauthorized');
}

$user_id = (int)$_GET['id'];

$user = $conn->query("
    SELECT *, 
           IFNULL(status, 'active') as user_status,
           IFNULL(profile_image, '') as profile_image 
    FROM users 
    WHERE id = $user_id
")->fetch_assoc();

if(!$user) {
    die('<div class="alert alert-danger">User not found</div>');
}

$bookings = $conn->query("
    SELECT b.*, e.title, e.event_date, e.location 
    FROM bookings b 
    JOIN events e ON b.event_id = e.id 
    WHERE b.user_id = $user_id 
    ORDER BY b.booking_date DESC 
    LIMIT 10
");

$booking_count = $conn->query("SELECT COUNT(*) as count FROM bookings WHERE user_id = $user_id")->fetch_assoc()['count'];
?>

<div style="padding: 20px;">
    <div class="user-header" style="display: flex; align-items: center; gap: 20px; margin-bottom: 30px;">
        <div class="avatar-large" style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: bold;">
            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
        </div>
        <div>
            <h3 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($user['username']); ?></h3>
            <p style="color: #666; margin: 0 0 5px 0;"><?php echo $user['email']; ?></p>
            <div style="display: flex; gap: 10px; font-size: 12px;">
                <span class="badge <?php echo $user['is_admin'] ? 'badge-danger' : 'badge-secondary'; ?>">
                    <?php echo $user['is_admin'] ? 'Administrator' : 'Regular User'; ?>
                </span>
                <span class="status-badge status-<?php echo $user['user_status']; ?>">
                    <?php echo ucfirst($user['user_status']); ?>
                </span>
            </div>
        </div>
    </div>
    
    <div class="user-info-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <div class="info-card">
            <h4 style="color: #666; margin-bottom: 10px;">Contact Information</h4>
            <div style="font-size: 14px;">
                <p><strong>Email:</strong> <?php echo $user['email']; ?></p>
                <p><strong>Phone:</strong> <?php echo $user['phone'] ?: 'Not provided'; ?></p>
                <p><strong>Member since:</strong> <?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
            </div>
        </div>
        
        <div class="info-card">
            <h4 style="color: #666; margin-bottom: 10px;">Statistics</h4>
            <div style="font-size: 14px;">
                <p><strong>Total Bookings:</strong> <?php echo $booking_count; ?></p>
                <p><strong>Last Login:</strong> <?php echo !empty($user['last_login']) ? date('M d, Y h:i A', strtotime($user['last_login'])) : 'Never'; ?></p>
                <p><strong>Account Status:</strong> <?php echo ucfirst($user['user_status']); ?></p>
            </div>
        </div>
    </div>
    
    <?php if($booking_count > 0): ?>
        <div class="recent-bookings">
            <h4 style="color: #666; margin-bottom: 15px;">Recent Bookings (<?php echo $booking_count; ?> total)</h4>
            <div style="max-height: 300px; overflow-y: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                    <thead>
                        <tr style="background: #f8f9fa;">
                            <th style="padding: 10px; text-align: left;">Booking ID</th>
                            <th style="padding: 10px; text-align: left;">Event</th>
                            <th style="padding: 10px; text-align: left;">Date</th>
                            <th style="padding: 10px; text-align: left;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($booking = $bookings->fetch_assoc()): 
                            $booking_status = !empty($booking['status']) ? $booking['status'] : 'confirmed';
                        ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 10px;"><?php echo $booking['id']; ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($booking['title']); ?></td>
                                <td style="padding: 10px;"><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                <td style="padding: 10px;">
                                    <span class="status-badge status-<?php echo $booking_status; ?>">
                                        <?php echo ucfirst($booking_status); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>