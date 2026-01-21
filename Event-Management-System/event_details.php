
<?php
// Include database connection
require_once 'db_connection.php';

// Start session
session_start();

// Get event ID from URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($id == 0) {
    header("Location: index.php");
    exit();
}

// Fetch event with category name
// First, check if events table has 'category' column or uses 'category_id'
$check_column = $conn->query("SHOW COLUMNS FROM events LIKE 'category'");
if($check_column->num_rows > 0) {
    // If category column exists directly in events table
    $sql = "SELECT e.* FROM events e WHERE e.id = ? AND e.status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $category_name = $event['category'] ?? 'General';
} else {
    // If using category_id with join to event_categories
    $sql = "SELECT e.*, ec.name as category_name FROM events e 
            LEFT JOIN event_categories ec ON e.category_id = ec.id 
            WHERE e.id = ? AND e.status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $category_name = $event['category_name'] ?? 'General';
}

if(!$event) {
    header("Location: index.php");
    exit();
}

$stmt->close();

// Set image path
$image_path = 'image/default-event.jpg'; // Default image
if(!empty($event['image'])) {
    if(file_exists('uploads/event_images/' . $event['image'])) {
        $image_path = 'uploads/event_images/' . $event['image'];
    } else if(file_exists($event['image'])) {
        $image_path = $event['image'];
    }
}

// Format dates and prices
$event_date = date('F j, Y', strtotime($event['event_date']));
$event_time = !empty($event['event_time']) ? date('h:i A', strtotime($event['event_time'])) : 'To be announced';
$price = $event['price'] > 0 ? '₹' . number_format($event['price'], 2) : 'FREE';

// Include header
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?> - EventPro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 0;
        }
        
        .event-detail-container {
            max-width: 1200px;
            margin: 30px auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .event-header {
            display: flex;
            gap: 40px;
            padding: 40px;
        }
        
        .event-image {
            flex: 1;
            max-width: 500px;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .event-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .event-info {
            flex: 1;
        }
        
        .event-category {
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            display: inline-block;
            margin-bottom: 20px;
            font-weight: 500;
        }
        
        .event-title {
            color: #333;
            margin-bottom: 25px;
            font-size: 36px;
            font-weight: 600;
        }
        
        .event-meta {
            background: #f8fafc;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            color: #555;
        }
        
        .meta-item i {
            width: 24px;
            color: #667eea;
            margin-right: 10px;
        }
        
        .event-price {
            font-size: 32px;
            color: #10b981;
            font-weight: bold;
            margin: 30px 0;
            padding: 20px 0;
            border-top: 2px solid #f1f1f1;
            border-bottom: 2px solid #f1f1f1;
        }
        
        .event-description {
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
            font-size: 16px;
        }
        
        .capacity-info {
            background: #e0f2fe;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .btn-book {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            display: block;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .fade-in {
            animation: fadeIn 0.5s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @media (max-width: 992px) {
            .event-header {
                flex-direction: column;
                padding: 20px;
            }
            
            .event-image {
                max-width: 100%;
            }
            
            .event-title {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
    <div class="event-detail-container fade-in">
        <div class="event-header">
            <div class="event-image">
                <img src="<?php echo $image_path; ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
            </div>
            
            <div class="event-info">
                <span class="event-category"><?php echo htmlspecialchars($category_name); ?></span>
                <h1 class="event-title"><?php echo htmlspecialchars($event['title']); ?></h1>
                
                <div class="event-meta">
                    <div class="meta-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong>Date:</strong> <?php echo $event_date; ?><br>
                            <small>Time: <?php echo $event_time; ?></small>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <strong>Venue:</strong><br>
                            <?php echo htmlspecialchars($event['location']); ?>
                        </div>
                    </div>
                    
                    <div class="meta-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <strong>Capacity:</strong> <?php echo $event['capacity']; ?> seats
                        </div>
                    </div>
                    
                    <?php if(!empty($event['created_by'])): ?>
                    <div class="meta-item">
                        <i class="fas fa-user-tie"></i>
                        <div>
                            <strong>Organizer:</strong> EventPro Management
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="event-price">
                    <?php echo $price; ?>
                    <?php if($event['price'] > 0): ?>
                        <small style="font-size: 14px; color: #666; font-weight: normal;">per person</small>
                    <?php endif; ?>
                </div>
                
                <div class="event-description">
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                </div>
                
                <div class="capacity-info">
                    <p><i class="fas fa-info-circle"></i> 
                        <strong>Available Seats:</strong> <?php echo $event['capacity']; ?> out of <?php echo $event['capacity']; ?>
                    </p>
                    <p><small>Hurry! Book your spot before it's too late.</small></p>
                </div>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="booking.php?event_id=<?php echo $event['id']; ?>" class="btn-book">
                        <i class="fas fa-ticket-alt"></i> Book Now
                    </a>
                <?php else: ?>
                    <a href="registration.php?redirect=event_details.php?id=<?php echo $event['id']; ?>" class="btn-book">
                        <i class="fas fa-user-plus"></i> Register & Book
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
    // Add any JavaScript functionality here if needed
    </script>
</body>
</html>
