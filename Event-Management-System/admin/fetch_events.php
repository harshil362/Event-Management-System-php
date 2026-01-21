<?php
require_once '../db_connection.php'; 


$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';
$sort = $_GET['sort'] ?? 0;

// Build query
$sql = "SELECT e.*, u.username as organizer FROM events e 
        LEFT JOIN users u ON e.created_by = u.id 
        WHERE e.status = 'active'";
$params = [];

if(!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR e.description LIKE ? OR e.location LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

if(!empty($category)) {
    $sql .= " AND e.category = ?";
    $params[] = $category;
}

if(!empty($date)) {
    $sql .= " AND DATE(e.event_date) = ?";
    $params[] = $date;
}

if($sort) {
    $sql .= " ORDER BY e.event_date ASC";
} else {
    $sql .= " ORDER BY e.created_at DESC";
}

$stmt = $conn->prepare($sql);

if(!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    while($event = $result->fetch_assoc()) {
        $is_upcoming = strtotime($event['event_date']) >= time();
        $status_class = $is_upcoming ? 'upcoming' : 'past';
        $status_text = $is_upcoming ? 'Upcoming' : 'Past';
        ?>
        <div class="col-4">
            <div class="event-card">
                <div class="event-image">
                    <?php if($event['image']): ?>
                        <img src="uploads/event_images/<?php echo htmlspecialchars($event['image']); ?>" 
                             alt="<?php echo htmlspecialchars($event['title']); ?>">
                    <?php else: ?>
                        <img src="images/default-event.jpg" alt="Default Event">
                    <?php endif; ?>
                    <div class="event-category"><?php echo htmlspecialchars($event['category']); ?></div>
                </div>
                <div class="event-content">
                    <h3 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h3>
                    <p class="event-description"><?php echo substr(htmlspecialchars($event['description']), 0, 100); ?>...</p>
                    <div class="event-details">
                        <div class="event-date">
                            <i class="fas fa-calendar"></i>
                            <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                        </div>
                        <?php if($event['event_time']): ?>
                        <div class="event-time">
                            <i class="fas fa-clock"></i>
                            <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                        </div>
                        <?php endif; ?>
                        <div class="event-location">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($event['location']); ?>
                        </div>
                    </div>
                    <div class="event-footer">
                        <div class="event-price">
                            <?php echo $event['price'] > 0 ? '₹' . number_format($event['price'], 2) : 'FREE'; ?>
                        </div>
                        <div class="event-status <?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                    </div>
                    <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-outline">View Details</a>
                </div>
            </div>
        </div>
        <?php
    }
} else {
    echo '<div class="col-12"><p class="text-center">No events found. Try different filters.</p></div>';
}
?>