<?php
require_once '../db_connection.php';
session_start();

if(!isset($_SESSION['admin_id'])) {
    die('Unauthorized');
}

$booking_id = (int)$_GET['id'];

$booking = $conn->query("
    SELECT b.*, u.username, u.email, u.phone, 
           e.title as event_title, e.description, e.event_date, e.event_time, 
           e.location, e.price, e.image, e.capacity
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN events e ON b.event_id = e.id
    WHERE b.id = $booking_id
")->fetch_assoc();

if(!$booking) {
    die('Booking not found');
}
?>

<div style="padding: 20px;">
    <div class="booking-header" style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
        <h3 style="margin: 0 0 10px 0;">Booking: <?php echo $booking['booking_id']; ?></h3>
        <div style="display: flex; gap: 10px; font-size: 14px;">
            <span class="status-badge status-<?php echo $booking['status']; ?>" style="font-size: 12px;">
                <?php echo ucfirst($booking['status']); ?>
            </span>
            <span style="color: #666;">Booked on <?php echo date('F d, Y h:i A', strtotime($booking['booking_date'])); ?></span>
        </div>
    </div>
    
    <div class="booking-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin-bottom: 30px;">
        <div>
            <h4 style="color: #666; margin-bottom: 15px;">User Information</h4>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <p style="margin: 0 0 10px 0;"><strong>Name:</strong> <?php echo htmlspecialchars($booking['username']); ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Phone:</strong> <?php echo $booking['phone'] ?: 'Not provided'; ?></p>
            </div>
        </div>
        
        <div>
            <h4 style="color: #666; margin-bottom: 15px;">Event Information</h4>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                <p style="margin: 0 0 10px 0;"><strong>Event:</strong> <?php echo htmlspecialchars($booking['event_title']); ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Date:</strong> <?php echo date('F d, Y', strtotime($booking['event_date'])); ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Time:</strong> <?php echo $booking['event_time'] ? date('h:i A', strtotime($booking['event_time'])) : 'Not specified'; ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Location:</strong> <?php echo $booking['location']; ?></p>
                <p style="margin: 0 0 10px 0;"><strong>Price:</strong> ₹<?php echo number_format($booking['price'], 2); ?></p>
            </div>
        </div>
    </div>
    
    <?php if($booking['notes']): ?>
        <div class="booking-notes" style="margin-bottom: 20px;">
            <h4 style="color: #666; margin-bottom: 10px;">Booking Notes</h4>
            <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; font-size: 14px;">
                <?php echo nl2br(htmlspecialchars($booking['notes'])); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <div class="booking-actions" style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
        <form method="POST" action="bookings.php" style="display: inline;">
            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
            <input type="hidden" name="action" value="confirm">
            <button type="submit" class="btn btn-success" <?php echo $booking['status'] == 'confirmed' ? 'disabled' : ''; ?>>
                <i class="fas fa-check"></i> Confirm Booking
            </button>
        </form>
        
        <form method="POST" action="bookings.php" style="display: inline; margin-left: 10px;">
            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
            <input type="hidden" name="action" value="cancel">
            <button type="submit" class="btn btn-warning" <?php echo $booking['status'] == 'cancelled' ? 'disabled' : ''; ?>>
                <i class="fas fa-times"></i> Cancel Booking
            </button>
        </form>
    </div>
</div>