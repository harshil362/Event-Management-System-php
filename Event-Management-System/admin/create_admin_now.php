<?php
// create_admin_now.php - Create admin user immediately
echo "<h2>🛠️ Creating Admin User...</h2>";

$conn = new mysqli("localhost", "root", "", "event_management");

if ($conn->connect_error) {
    die("❌ Connection failed: " . $conn->connect_error);
}

// Method 1: Simple insert (no password hashing for testing)
$sql = "INSERT INTO users (username, email, phone, password, created_at, is_admin) 
        VALUES ('admin', 'admin@eventpro.com', '1234567890', 'admin123', NOW(), 1)";

if($conn->query($sql)) {
    echo "✅ Admin user created successfully!<br>";
    echo "<strong>Username:</strong> admin<br>";
    echo "<strong>Password:</strong> admin123<br>";
    echo "<strong>Email:</strong> admin@eventpro.com<br>";
    echo "<strong>is_admin:</strong> 1<br>";
} else {
    echo "⚠️ Trying alternative method...<br>";
    
    // Method 2: Check if admin already exists
    $check = $conn->query("SELECT * FROM users WHERE username = 'admin'");
    if($check->num_rows > 0) {
        echo "✅ Admin user already exists!<br>";
        $admin = $check->fetch_assoc();
        echo "<strong>Username:</strong> " . $admin['username'] . "<br>";
        echo "<strong>Email:</strong> " . $admin['email'] . "<br>";
        echo "<strong>Is Admin:</strong> " . ($admin['is_admin'] ? 'Yes' : 'No') . "<br>";
        
        // If not admin, make it admin
        if(!$admin['is_admin']) {
            $conn->query("UPDATE users SET is_admin = 1 WHERE username = 'admin'");
            echo "✅ Updated user to admin!<br>";
        }
    } else {
        echo "❌ Could not create admin user. Error: " . $conn->error . "<br>";
    }
}

echo "<hr>";
echo "<h3>📋 All Users:</h3>";

$users = $conn->query("SELECT id, username, email, is_admin FROM users");
echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
echo "<tr style='background: #667eea; color: white;'><th>ID</th><th>Username</th><th>Email</th><th>Is Admin</th></tr>";

while($user = $users->fetch_assoc()) {
    $bg = $user['is_admin'] ? 'background: #d1fae5;' : '';
    echo "<tr style='$bg'>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td><strong>" . $user['username'] . "</strong></td>";
    echo "<td>" . $user['email'] . "</td>";
    echo "<td>" . ($user['is_admin'] ? '✅ Yes' : '❌ No') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><br>";
echo "<a href='admin/index.php' style='background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold;'>🚀 Go to Admin Login</a>";

$conn->close();
?>