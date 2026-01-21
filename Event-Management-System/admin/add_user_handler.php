<?php
session_start();
require_once '../db_connection.php';

if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = trim($_POST['phone'] ?? '');
    $is_admin = (int)($_POST['is_admin'] ?? 0);
    
    // Check if user already exists
    $check = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $check->bind_param("ss", $username, $email);
    $check->execute();
    
    if($check->get_result()->num_rows > 0) {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Username or email already exists'
        ];
        header("Location: manage_users.php");
        exit();
    }
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, phone, is_admin, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssi", $username, $email, $password, $phone, $is_admin);
    
    if($stmt->execute()) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'User created successfully'
        ];
    } else {
        $_SESSION['toast'] = [
            'type' => 'error',
            'message' => 'Failed to create user: ' . $conn->error
        ];
    }
    
    header("Location: manage_users.php");
    exit();
}
?>