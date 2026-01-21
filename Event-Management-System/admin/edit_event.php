<?php
require_once 'header.php';
require_once '../db_connection.php'; 

$page_title = "Edit Event";

$id = $_GET['id'] ?? 0;
$success = '';
$error = '';

// Get event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

if(!$event) {
    header("Location: manage_events.php");
    exit();
}

// Handle image removal
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $remove_image = $_POST['remove_image'] ?? 0;
    
    // Handle image removal
    $image_name = $event['image'];
    if($remove_image && $image_name) {
        if(file_exists("../uploads/event_images/" . $image_name)) {
            unlink("../uploads/event_images/" . $image_name);
        }
        $image_name = '';
    }
    
    // Handle new image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['image']['type'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if($_FILES['image']['size'] > $max_size) {
            $error = "Image size must be less than 2MB.";
        } elseif(in_array($file_type, $allowed_types)) {
            // Delete old image if exists
            if($image_name && file_exists("../uploads/event_images/" . $image_name)) {
                unlink("../uploads/event_images/" . $image_name);
            }
            
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $image_name = uniqid() . '.' . $ext;
            $upload_path = '../uploads/event_images/' . $image_name;
            
            if(!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $error = "Failed to upload image.";
            }
        } else {
            $error = "Invalid image type. Only JPG, PNG, GIF allowed.";
        }
    }
    
    if(!$error) {
        $stmt = $conn->prepare("UPDATE events SET title=?, description=?, category=?, event_date=?, event_time=?, location=?, price=?, image=? WHERE id=?");
        $stmt->bind_param("ssssssdsi", $title, $description, $category, $event_date, $event_time, $location, $price, $image_name, $id);
        
        if($stmt->execute()) {
            $success = "Event updated successfully!";
            // Refresh event data
            $event['title'] = $title;
            $event['description'] = $description;
            $event['category'] = $category;
            $event['event_date'] = $event_date;
            $event['event_time'] = $event_time;
            $event['location'] = $location;
            $event['price'] = $price;
            $event['image'] = $image_name;
        } else {
            $error = "Failed to update event: " . $conn->error;
        }
    }
}

// Get categories
$categories_result = $conn->query("SELECT DISTINCT category FROM events ORDER BY category");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --secondary: #6c757d;
            --success: #28a745;
            --danger: #dc3545;
            --warning: #ffc107;
            --light: #f8f9fa;
            --dark: #343a40;
            --sidebar-width: 250px;
            --topbar-height: 70px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: #f5f7fa;
            color: #333;
            overflow-x: hidden;
        }
        
        .admin-wrapper {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        /* Main Content Area */
        .main-content {
            flex: 1;
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }
        
        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
            color: #64748b;
        }
        
        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s;
        }
        
        .breadcrumb a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .breadcrumb span {
            color: #94a3b8;
        }
        
        .topbar-actions {
            display: flex;
            gap: 15px;
        }
        
        /* Content Wrapper */
        .content-wrapper {
            padding: 30px;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        
        /* Card */
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            margin-bottom: 30px;
            overflow: hidden;
        }
        
        .card-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e2e8f0;
            background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);
        }
        
        .card-header h3 {
            margin: 0;
            color: #1e293b;
            font-size: 1.5rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .card-header i {
            color: var(--primary);
        }
        
        .card-body {
            padding: 30px;
        }
        
        /* Alerts */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }
        
        .alert i {
            font-size: 18px;
        }
        
        /* Form Styles */
        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        
        .form-group {
            padding: 0 15px;
            margin-bottom: 25px;
            flex: 1 0 100%;
        }
        
        .col-md-6 {
            flex: 0 0 50%;
            max-width: 50%;
        }
        
        .col-md-4 {
            flex: 0 0 33.333%;
            max-width: 33.333%;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #374151;
            font-size: 14px;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            background: white;
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }
        
        select.form-control {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%236B7280'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            background-size: 16px;
            padding-right: 40px;
        }
        
        textarea.form-control {
            min-height: 120px;
            resize: vertical;
            line-height: 1.5;
        }
        
        /* Current Image */
        .current-image {
            display: flex;
            align-items: flex-start;
            gap: 25px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        
        .preview-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
        }
        
        .no-image-preview {
            width: 150px;
            height: 150px;
            background: #f1f5f9;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
        }
        
        .no-image-preview i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #cbd5e1;
        }
        
        .image-info {
            flex: 1;
        }
        
        .image-info p {
            margin: 0 0 15px 0;
            color: #475569;
            font-size: 14px;
        }
        
        .image-info strong {
            color: #334155;
        }
        
        .checkbox-wrapper {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }
        
        .checkbox-wrapper input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        
        .checkbox-label {
            margin: 0;
            cursor: pointer;
            color: #475569;
            font-size: 14px;
        }
        
        .text-muted {
            color: #64748b !important;
            font-size: 13px;
            margin-top: 6px;
            display: block;
            line-height: 1.4;
        }
        
        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            padding-top: 30px;
            border-top: 1px solid #e2e8f0;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-align: center;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(67, 97, 238, 0.3);
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(100, 116, 139, 0.3);
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(239, 68, 68, 0.3);
        }
        
        /* File Input */
        input[type="file"] {
            padding: 10px;
            border: 2px dashed #d1d5db;
            background: #f8fafc;
        }
        
        input[type="file"]:hover {
            border-color: var(--primary);
            background: #f0f7ff;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .content-wrapper {
                padding: 20px;
            }
        }
        
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.active {
                margin-left: var(--sidebar-width);
            }
            
            .col-md-6,
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .current-image {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            
            .form-actions {
                flex-wrap: wrap;
            }
            
            .form-actions .btn {
                flex: 1;
                min-width: 200px;
            }
        }
        
        @media (max-width: 768px) {
            .topbar {
                padding: 0 20px;
                flex-direction: column;
                height: auto;
                padding: 15px 20px;
                gap: 15px;
            }
            
            .breadcrumb {
                font-size: 13px;
            }
            
            .card-header,
            .card-body {
                padding: 20px;
            }
            
            .card-header h3 {
                font-size: 1.3rem;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .form-actions .btn {
                width: 100%;
                justify-content: center;
            }
            
            .content-wrapper {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .current-image {
                padding: 15px;
            }
            
            .preview-image,
            .no-image-preview {
                width: 120px;
                height: 120px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 13px;
            }
        }
        
        /* Mobile Toggle Button */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1001;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            width: 40px;
            height: 40px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
        }
        
        @media (max-width: 992px) {
            .sidebar-toggle {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <button class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <div class="admin-wrapper">
        <?php include 'sidebar.php'; ?>
        
        <main class="main-content" id="mainContent">
            <div class="topbar">
                <div class="breadcrumb">
                    <a href="dashboard.php">Dashboard</a>
                    <span>/</span>
                    <a href="manage_events.php">Events</a>
                    <span>/</span>
                    <span>Edit Event</span>
                </div>
                <div class="topbar-actions">
                    <a href="manage_events.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>
            </div>
            
            <div class="content-wrapper">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-edit"></i> Edit Event: <?php echo htmlspecialchars($event['title']); ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if($success): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if($error): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" enctype="multipart/form-data">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="title">Event Title *</label>
                                    <input type="text" id="title" name="title" class="form-control" 
                                           value="<?php echo htmlspecialchars($event['title']); ?>" required>
                                </div>
                                
                                <div class="form-group col-md-6">
                                    <label for="category">Category *</label>
                                    <select id="category" name="category" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <?php while($cat = $categories_result->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                                    <?php echo $event['category'] == $cat['category'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['category']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Description *</label>
                                <textarea id="description" name="description" class="form-control" rows="5" required><?php echo htmlspecialchars($event['description']); ?></textarea>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="event_date">Event Date *</label>
                                    <input type="date" id="event_date" name="event_date" class="form-control" 
                                           value="<?php echo $event['event_date']; ?>" required>
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label for="event_time">Event Time</label>
                                    <input type="time" id="event_time" name="event_time" class="form-control" 
                                           value="<?php echo $event['event_time']; ?>">
                                </div>
                                
                                <div class="form-group col-md-4">
                                    <label for="price">Price ($)</label>
                                    <input type="number" id="price" name="price" step="0.01" class="form-control" 
                                           value="<?php echo $event['price']; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="location">Location</label>
                                <input type="text" id="location" name="location" class="form-control" 
                                       value="<?php echo htmlspecialchars($event['location'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Current Image</label>
                                <div class="current-image">
                                    <?php if($event['image'] && file_exists("../uploads/event_images/" . $event['image'])): ?>
                                        <img src="../uploads/event_images/<?php echo $event['image']; ?>?t=<?php echo time(); ?>" 
                                             alt="Current Event Image" class="preview-image">
                                        <div class="image-info">
                                            <p><strong>Current Image:</strong> <?php echo $event['image']; ?></p>
                                            <div class="checkbox-wrapper">
                                                <input type="checkbox" id="remove_image" name="remove_image" value="1">
                                                <label for="remove_image" class="checkbox-label">Remove current image</label>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="no-image-preview">
                                            <i class="fas fa-image"></i>
                                            <span>No image uploaded</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="image">New Event Image (Optional)</label>
                                <input type="file" id="image" name="image" class="form-control" accept="image/*">
                                <small class="text-muted">Leave blank to keep current image. Max 2MB. Allowed: JPG, PNG, GIF</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Event
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Reset Form
                                </button>
                                <a href="delete_event.php?id=<?php echo $event['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete Event
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Toggle sidebar on mobile
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
            document.getElementById('mainContent').classList.toggle('active');
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.querySelector('.sidebar');
            const mainContent = document.getElementById('mainContent');
            const sidebarToggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 992) {
                if (sidebar.classList.contains('active') && 
                    !sidebar.contains(event.target) && 
                    !sidebarToggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    mainContent.classList.remove('active');
                }
            }
        });
        
        // Preview image when selected
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // If there's a current image preview, update it
                    const previewImg = document.querySelector('.preview-image');
                    if (previewImg) {
                        previewImg.src = e.target.result;
                    } else {
                        // Replace no-image preview with new image
                        const noImagePreview = document.querySelector('.no-image-preview');
                        if (noImagePreview) {
                            const newImg = document.createElement('img');
                            newImg.src = e.target.result;
                            newImg.className = 'preview-image';
                            newImg.alt = 'New Event Image';
                            noImagePreview.parentNode.replaceChild(newImg, noImagePreview);
                        }
                    }
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Handle remove image checkbox
        document.getElementById('remove_image')?.addEventListener('change', function(e) {
            const fileInput = document.getElementById('image');
            if (e.target.checked) {
                fileInput.disabled = true;
                fileInput.value = '';
                // Show message
                const imageInfo = document.querySelector('.image-info');
                if (imageInfo) {
                    const message = document.createElement('p');
                    message.className = 'text-muted';
                    message.textContent = 'Current image will be removed when you save.';
                    imageInfo.appendChild(message);
                }
            } else {
                fileInput.disabled = false;
                // Remove message
                const message = document.querySelector('.image-info .text-muted');
                if (message) {
                    message.remove();
                }
            }
        });
    </script>
</body>
</html>