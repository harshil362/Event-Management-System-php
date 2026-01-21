<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management System</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>
    <header class="header" style="background: linear-gradient(135deg, #667eea, #764ba2);">
        <div class="header-container">
            <!-- Logo -->
            
            <a href="index.php" class="logo">
                 <img alt="EventPro Logo" src="image/logo1.png"> <!-- Updated logo path -->
                <span class="logo-text">EventPro</span>
            </a>

            <!-- Navigation Menu -->
            <nav>
                <div class="menu-toggle" id="mobile-menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                
                <ul class="nav-menu" id="nav-menu">
                    <li class="nav-item">
                        <a href="index.php" class="nav-link">Home</a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="events.php" class="nav-link">Events</a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="services.php" class="nav-link">Services</a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="gallery.php" class="nav-link">Gallery</a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="about.php" class="nav-link">About Us</a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="contact.php" class="nav-link">Contact Us</a>
                    </li>
                    
                    <li class="nav-item">
                        <a href="reviews.php" class="nav-link">Reviews</a>
                    </li>
                    
                    <li class="nav-item auth-buttons">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <a href="profile.php" class="btn btn-profile">
                                <i class="fas fa-user"></i> Profile
                            </a>
                            <a href="logout.php" class="btn btn-logout">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="btn btn-login">
                                <i class="fas fa-sign-in-alt"></i> Login
                            </a>
                            <a href="registration.php" class="btn btn-register">
                                <i class="fas fa-user-plus"></i> Register
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </nav>
        </div>
    </header>


</body>
</html>
