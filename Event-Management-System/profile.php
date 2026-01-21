<?php
session_start();
include 'db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, phone, created_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Get user's booking statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_bookings, SUM(num_tickets) as total_tickets FROM bookings WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$booking_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Get user's review statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total_reviews, AVG(rating) as avg_rating FROM reviews WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$review_stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Handle profile update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_profile'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // Server-side validation
    if (empty($username) || strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long.";
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required.";
    }
    if (empty($phone) || !preg_match('/^[0-9]{10,15}$/', $phone)) {
        $errors[] = "Valid phone number is required (10-15 digits).";
    }

    // Check if username or email already exists (excluding current user)
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
        $stmt->bind_param("ssi", $username, $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Username or email already exists.";
        }
        $stmt->close();
    }

    if (empty($errors)) {
        // Update user data
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
        $stmt->bind_param("sssi", $username, $email, $phone, $user_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully.";
            $_SESSION['username'] = $username;
            $user = ['username' => $username, 'email' => $email, 'phone' => $phone, 'created_at' => $user['created_at']];
        } else {
            $errors[] = "Profile update failed.";
        }
        $stmt->close();
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Server-side validation
    if (empty($old_password)) {
        $errors[] = "Old password is required.";
    }
    if (empty($new_password) || strlen($new_password) < 6) {
        $errors[] = "New password must be at least 6 characters long.";
    }
    if ($new_password !== $confirm_password) {
        $errors[] = "New passwords do not match.";
    }

    if (empty($errors)) {
        // Verify old password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        $stmt->close();

        if (password_verify($old_password, $user_data['password'])) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user_id);
            if ($stmt->execute()) {
                $success = "Password changed successfully.";
            } else {
                $errors[] = "Password change failed.";
            }
            $stmt->close();
        } else {
            $errors[] = "Old password is incorrect.";
        }
    }
}

include 'header.php';
?>

<main>
    <section class="section">
        <div class="container">
            <div class="row justify-center">
                <div class="col-10">
                    <!-- Profile Header -->
                    <div class="card mb-4">
                        <div class="card-body text-center">
                            <div class="profile-avatar mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary"></i>
                            </div>
                            <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                            <p class="text-muted">User Account</p>
                            <p class="text-muted">Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-calendar-check fa-2x text-success mb-2"></i>
                                    <h4><?php echo $booking_stats['total_bookings'] ?? 0; ?></h4>
                                    <p class="text-muted">Total Bookings</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-ticket-alt fa-2x text-info mb-2"></i>
                                    <h4><?php echo $booking_stats['total_tickets'] ?? 0; ?></h4>
                                    <p class="text-muted">Tickets Booked</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="card text-center">
                                <div class="card-body">
                                    <i class="fas fa-star fa-2x text-warning mb-2"></i>
                                    <h4><?php echo number_format($review_stats['avg_rating'] ?? 0, 1); ?></h4>
                                    <p class="text-muted">Avg Rating (<?php echo $review_stats['total_reviews'] ?? 0; ?> reviews)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Profile Content -->
                    <div class="row">
                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="fas fa-edit"></i> Edit Profile</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($errors)): ?>
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                <?php foreach ($errors as $error): ?>
                                                    <li><?php echo htmlspecialchars($error); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($success): ?>
                                        <div class="alert alert-success">
                                            <?php echo htmlspecialchars($success); ?>
                                        </div>
                                    <?php endif; ?>

                                    <form action="" method="POST" id="profileForm">
                                        <input type="hidden" name="update_profile" value="1">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" id="username" name="username" required class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>">
                                            <i class="fas fa-user form-icon"></i>
                                        </div>

                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" id="email" name="email" required class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>">
                                            <i class="fas fa-envelope form-icon"></i>
                                        </div>

                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" id="phone" name="phone" required class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
                                            <i class="fas fa-phone form-icon"></i>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fas fa-save"></i> Update Profile
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="card">
                                <div class="card-header">
                                    <h4><i class="fas fa-key"></i> Change Password</h4>
                                </div>
                                <div class="card-body">
                                    <form action="" method="POST" id="passwordForm">
                                        <input type="hidden" name="change_password" value="1">
                                        <div class="form-group">
                                            <label for="old_password">Current Password</label>
                                            <input type="password" id="old_password" name="old_password" required class="form-control" placeholder="Enter current password">
                                            <i class="fas fa-lock form-icon"></i>
                                        </div>

                                        <div class="form-group">
                                            <label for="new_password">New Password</label>
                                            <input type="password" id="new_password" name="new_password" required class="form-control" placeholder="Enter new password">
                                            <i class="fas fa-lock form-icon"></i>
                                        </div>

                                        <div class="form-group">
                                            <label for="confirm_password">Confirm New Password</label>
                                            <input type="password" id="confirm_password" name="confirm_password" required class="form-control" placeholder="Confirm new password">
                                            <i class="fas fa-lock form-icon"></i>
                                        </div>

                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-key"></i> Change Password
                                        </button>
                                    </form>

                                    <hr>

                                    <div class="text-center">
                                        <a href="logout.php" class="btn btn-danger btn-sm">
                                            <i class="fas fa-sign-out-alt"></i> Logout
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();

    let errors = [];

    if (username.length < 3) {
        errors.push('Username must be at least 3 characters long.');
    }
    if (email === '' || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        errors.push('Valid email is required.');
    }
    if (phone === '' || !/^[0-9]{10,15}$/.test(phone)) {
        errors.push('Valid phone number is required (10-15 digits).');
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});

document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const oldPassword = document.getElementById('old_password').value;
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;

    let errors = [];

    if (oldPassword === '') {
        errors.push('Old password is required.');
    }
    if (newPassword.length < 6) {
        errors.push('New password must be at least 6 characters long.');
    }
    if (newPassword !== confirmPassword) {
        errors.push('New passwords do not match.');
    }

    if (errors.length > 0) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});
</script>

<?php include 'footer.php'; ?>
</body>
</html>
