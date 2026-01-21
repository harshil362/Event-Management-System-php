<?php
session_start();

// Database configuration
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "event_management";

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if already logged in
if(isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Check user in database
    $stmt = $conn->prepare("SELECT id, username, email, password, is_admin FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        
        // Check if user is admin
        if($user['is_admin'] == 1) {
            // Verify password (simple check for now - change to password_verify in production)
            if($password == 'admin123' || password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_username'] = $user['username'];
                $_SESSION['admin_email'] = $user['email'];
                $_SESSION['is_admin'] = 1;
                
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid password! Try 'admin123'";
            }
        } else {
            $error = "This user is not an administrator";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - EventPro</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 450px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .logo {
            font-size: 36px;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .login-header h2 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-header p {
            color: #666;
            font-size: 16px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        
        .input-with-icon {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .form-group input {
            width: 100%;
            padding: 14px 14px 14px 45px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            border-left: 4px solid #c33;
            font-size: 14px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        
        .login-footer p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
        }
        
        .credentials {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            font-size: 13px;
        }
        
        .credentials strong {
            color: #333;
        }
        
        .back-to-site {
            margin-top: 20px;
        }
        
        .back-to-site a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-to-site a:hover {
            text-decoration: underline;
        }
        
        .alert {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            border-left: 4px solid #10b981;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h2>EventPro Admin</h2>
            <p>Sign in to manage your events</p>
        </div>
        
        <?php if($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label>Username</label>
                <div class="input-with-icon">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" name="username" required placeholder="Enter username">
                </div>
            </div>
            
            <div class="form-group">
                <label>Password</label>
                <div class="input-with-icon">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" name="password" required placeholder="Enter password">
                </div>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>
        
        <div class="login-footer">
           
                <?php
                // Check if admin user exists
                $check_admin = $conn->query("SELECT * FROM users WHERE username = 'admin' AND is_admin = 1");
                if($check_admin->num_rows == 0) {
                    echo '<div class="alert">';
                    echo '<i class="fas fa-exclamation-circle"></i> ';
                    echo 'Admin user not found. Create it in phpMyAdmin with:<br>';
                    echo '<code>UPDATE users SET is_admin = 1 WHERE username = "admin"</code>';
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="back-to-site">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Back to Main Site
                </a>
            </div>
        </div>
    </div>
</body>
</html>
<?php $conn->close(); ?>