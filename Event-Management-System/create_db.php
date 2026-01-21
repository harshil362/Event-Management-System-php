<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection without database
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS event_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db("event_management");

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(50) NOT NULL UNIQUE,
    phone VARCHAR(15),
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table users created successfully<br>";
} else {
    echo "Error creating table users: " . $conn->error . "<br>";
}

// Create event_categories table
$sql = "CREATE TABLE IF NOT EXISTS event_categories (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table event_categories created successfully<br>";
} else {
    echo "Error creating table event_categories: " . $conn->error . "<br>";
}

// Create events table
$sql = "CREATE TABLE IF NOT EXISTS events (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    event_date DATETIME NOT NULL,
    location VARCHAR(100) NOT NULL,
    capacity INT(6) UNSIGNED NOT NULL,
    category_id INT(6) UNSIGNED,
    created_by INT(6) UNSIGNED NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES event_categories(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table events created successfully<br>";
} else {
    echo "Error creating table events: " . $conn->error . "<br>";
}

// Create bookings table
$sql = "CREATE TABLE IF NOT EXISTS bookings (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    event_id INT(6) UNSIGNED NOT NULL,
    num_tickets INT(3) UNSIGNED DEFAULT 1,
    total_amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'confirmed', 'cancelled') DEFAULT 'pending',
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_booking (user_id, event_id)
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table bookings created successfully<br>";
} else {
    echo "Error creating table bookings: " . $conn->error . "<br>";
}

// Create reviews table
$sql = "CREATE TABLE IF NOT EXISTS reviews (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(6) UNSIGNED NOT NULL,
    event_id INT(6) UNSIGNED NOT NULL,
    rating TINYINT(1) UNSIGNED NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (user_id, event_id)
) ENGINE=InnoDB";

if ($conn->query($sql) === TRUE) {
    echo "Table reviews created successfully<br>";
} else {
    echo "Error creating table reviews: " . $conn->error . "<br>";
}

// Insert default user
$default_username = 'admin';
$default_email = 'admin@example.com';
$default_phone = '1234567890';
$default_password = password_hash('admin123', PASSWORD_DEFAULT);

$sql = "INSERT IGNORE INTO users (username, email, phone, password) VALUES ('$default_username', '$default_email', '$default_phone', '$default_password')";

if ($conn->query($sql) === TRUE) {
    echo "Default admin user inserted successfully<br>";
} else {
    echo "Error inserting default user: " . $conn->error . "<br>";
}

// Insert sample categories
$categories = [
    ['name' => 'Wedding', 'description' => 'Wedding event planning and management'],
    ['name' => 'Birthday', 'description' => 'Birthday party celebrations'],
    ['name' => 'Corporate', 'description' => 'Corporate events and conferences'],
    ['name' => 'Private Party', 'description' => 'Private party arrangements'],
    ['name' => 'Special Occasion', 'description' => 'Special occasion events']
];

foreach ($categories as $category) {
    $name = $conn->real_escape_string($category['name']);
    $desc = $conn->real_escape_string($category['description']);
    $sql = "INSERT IGNORE INTO event_categories (name, description) VALUES ('$name', '$desc')";
    if ($conn->query($sql) !== TRUE) {
        echo "Error inserting category {$category['name']}: " . $conn->error . "<br>";
    }
}
echo "Sample categories inserted successfully<br>";

$conn->close();
?>
