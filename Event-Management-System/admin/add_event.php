<?php
session_start();

// Check if admin is logged in
if(!isset($_SESSION['admin_id']) || $_SESSION['is_admin'] != 1) {
    header("Location: index.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "event_management");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $capacity = (int)$_POST['capacity'];
    $category_id = (int)$_POST['category_id'];
    $price = (float)$_POST['price'];
    $created_by = $_SESSION['admin_id'];
    
    // Get category name for display
    $cat_result = $conn->query("SELECT name FROM event_categories WHERE id = $category_id");
    $category_name = $cat_result->fetch_assoc()['name'] ?? 'General';
    
    // Handle image upload
    $image_name = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        
        if(in_array($file_type, $allowed_types)) {
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $ext;
            $upload_path = '../uploads/event_images/' . $image_name;
            
            // Create directory if doesn't exist
            if(!is_dir('../uploads/event_images/')) {
                mkdir('../uploads/event_images/', 0777, true);
            }
            
            move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
        }
    }
    
    // Insert into database
    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, location, capacity, category_id, created_by, price, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssiiids", $title, $description, $event_date, $location, $capacity, $category_id, $created_by, $price, $image_name);
    
    if($stmt->execute()) {
        $success = "✅ Event added successfully! Users can now see this event on the website.";
    } else {
        $error = "❌ Error: " . $conn->error;
    }
}

// Get categories for dropdown
$categories = $conn->query("SELECT * FROM event_categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - EventPro Admin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: #f5f5f5; }
        .admin-container { display: flex; min-height: 100vh; }
        .sidebar {
            width: 260px; background: white; box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed; height: 100vh;
        }
        .sidebar-header {
            padding: 25px 20px; background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        .sidebar-nav ul { list-style: none; }
        .sidebar-nav li { border-bottom: 1px solid #eee; }
        .sidebar-nav a {
            display: flex; align-items: center; padding: 15px 20px;
            color: #333; text-decoration: none;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            background: #667eea; color: white;
        }
        .sidebar-nav i { margin-right: 10px; width: 20px; }
        .main-content { flex: 1; margin-left: 260px; }
        .topbar {
            background: white; padding: 15px 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .content-wrapper { padding: 30px; }
        .card {
            background: white; border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 30px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; margin-bottom: 8px; font-weight: 500; color: #333;
        }
        .form-control {
            width: 100%; padding: 12px 15px; border: 2px solid #ddd;
            border-radius: 6px; font-size: 14px;
        }
        .form-control:focus { outline: none; border-color: #667eea; }
        textarea.form-control { min-height: 120px; resize: vertical; }
        .btn {
            padding: 12px 25px; border: none; border-radius: 6px; cursor: pointer;
            font-size: 14px; font-weight: 500; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a67d8; }
        .alert {
            padding: 15px; border-radius: 6px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert-error { background: #fee; color: #b91c1c; border-left: 4px solid #ef4444; }
        .image-preview {
            width: 200px; height: 150px; border: 2px dashed #ddd;
            border-radius: 8px; margin-top: 10px; overflow: hidden;
            display: flex; align-items: center; justify-content: center;
        }
        .image-preview img { max-width: 100%; max-height: 100%; }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h3>EventPro Admin</h3>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage_events.php"><i class="fas fa-calendar-alt"></i> Events</a></li>
                    <li><a href="add_event.php" class="active"><i class="fas fa-plus-circle"></i> Add Event</a></li>
                    <li><a href="manage_categories.php"><i class="fas fa-tags"></i> Categories</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="topbar">
                <h2>Add New Event</h2>
                <a href="manage_events.php" style="color: #667eea; text-decoration: none;">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>
            
            <div class="content-wrapper">
                <div class="card">
                    <?php if($success): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <br><small>Check the main website to see your event live!</small>
                        </div>
                    <?php endif; ?>
                    
                    <?php if($error): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="form-group">
                            <label>Event Title *</label>
                            <input type="text" name="title" class="form-control" required placeholder="Enter event title">
                        </div>
                        
                        <div class="form-group">
                            <label>Description *</label>
                            <textarea name="description" class="form-control" required placeholder="Describe your event"></textarea>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Event Date *</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Location *</label>
                                <input type="text" name="location" class="form-control" required placeholder="Event venue">
                            </div>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <div class="form-group">
                                <label>Capacity *</label>
                                <input type="number" name="capacity" class="form-control" required min="1" value="50">
                            </div>
                            
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category_id" class="form-control" required>
                                    <option value="">Select Category</option>
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Price (₹)</label>
                            <input type="number" name="price" class="form-control" min="0" step="0.01" value="0">
                            <small style="color: #666;">Enter 0 for free events</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Event Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
                            <div id="imagePreview" class="image-preview">
                                <i class="fas fa-image" style="font-size: 40px; color: #ccc;"></i>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Publish Event
                        </button>
                    </form>
                </div>
                
                <div class="card" style="margin-top: 30px;">
                    <h3><i class="fas fa-info-circle"></i> How it works:</h3>
                    <ul style="margin-top: 15px; padding-left: 20px; color: #666;">
                        <li>Events added here appear immediately on the main website</li>
                        <li>Users can view, search, and filter these events</li>
                        <li>Users can register/book events through the website</li>
                        <li>You can manage all events from the admin panel</li>
                    </ul>
                </div>
            </div>
        </main>
    </div>
    
    <script>
    function previewImage(input) {
        const preview = document.getElementById('imagePreview');
        if(input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" alt="Preview">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    
    // Set minimum date to today
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="event_date"]').min = today;
    });
    </script>
</body>
</html>
<?php $conn->close(); ?>