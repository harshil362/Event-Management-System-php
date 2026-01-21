

<?php
// Include your database connection
require_once 'db_connection.php';

// Get filter parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$date = $_GET['date'] ?? '';
$sort = $_GET['sort'] ?? 0;

// Build SQL query
$sql = "SELECT e.*, ec.name as category_name FROM events e 
        LEFT JOIN event_categories ec ON e.category_id = ec.id 
        WHERE e.status = 'active'";
$params = [];
$types = "";

// Add search filter
if(!empty($search)) {
    $sql .= " AND (e.title LIKE ? OR e.description LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ss";
}

// Add category filter (using category_name directly since events table has category column)
if(!empty($category)) {
    // Check if events table has 'category' column or 'category_id'
    $check_column = $conn->query("SHOW COLUMNS FROM events LIKE 'category'");
    if($check_column->num_rows > 0) {
        // If category column exists
        $sql .= " AND e.category = ?";
    } else {
        // If using category_id with join
        $sql .= " AND ec.name = ?";
    }
    $params[] = $category;
    $types .= "s";
}

// Add date filter
if(!empty($date)) {
    $sql .= " AND DATE(e.event_date) >= ?";
    $params[] = $date;
    $types .= "s";
}

// Add sorting
if($sort) {
    $sql .= " ORDER BY e.event_date ASC";
} else {
    $sql .= " ORDER BY e.created_at DESC";
}

// Prepare and execute statement
if(!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query($sql);
}

// Show event cards
if ($result->num_rows == 0) {
    echo "<div class='col-12'><p class='no-result text-center'>No events found!</p></div>";
} else {
    while($event = $result->fetch_assoc()) {
        // Get image path
        $image_path = 'image/default-event.jpg'; // Default image
        
        if(!empty($event['image'])) {
            // Check if image exists in uploads folder
            if(file_exists('uploads/event_images/' . $event['image'])) {
                $image_path = 'uploads/event_images/' . $event['image'];
            } else if(file_exists($event['image'])) {
                $image_path = $event['image'];
            }
        }
        
        // Format date
        $event_date = date('Y-m-d', strtotime($event['event_date']));
        
        // Get category name
        $category_name = $event['category_name'] ?? $event['category'] ?? 'General';
        
        // Format description preview
        $description_preview = substr($event['description'], 0, 80);
        if(strlen($event['description']) > 80) {
            $description_preview .= '...';
        }
        
        // Price display
        $price_display = ($event['price'] > 0) ? '₹' . number_format($event['price'], 2) : 'FREE';
        
        echo "
        <div class='col-4'>
            <div class='event-card'>
                <div class='event-image'>
                    <img src='{$image_path}' alt='{$event['title']}'>
                    <div class='event-category'>{$category_name}</div>
                </div>
                <div class='event-content'>
                    <h3>{$event['title']}</h3>
                    <p class='event-date'><i class='fas fa-calendar'></i> {$event_date}</p>
                    <p class='event-location'><i class='fas fa-map-marker-alt'></i> {$event['location']}</p>
                    <p class='event-description'>{$description_preview}</p>
                    <div class='event-footer'>
                        <span class='event-price'>{$price_display}</span>
                        <a href='event_details.php?id={$event['id']}' class='btn view-btn'>View Details</a>
                    </div>
                </div>
            </div>
        </div>";
    }
}

// Close statement if exists
if(isset($stmt)) {
    $stmt->close();
}
?>