<?php
require_once '../db_connection.php'; 

session_start();
if(!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'] ?? 0;

if($id) {
    // Get image name first
    $stmt = $conn->prepare("SELECT image FROM events WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        $event = $result->fetch_assoc();
        
        // Delete image file if exists
        if($event['image'] && file_exists("../uploads/event_images/" . $event['image'])) {
            unlink("../uploads/event_images/" . $event['image']);
        }
        
        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $delete_stmt->bind_param("i", $id);
        
        if($delete_stmt->execute()) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Event deleted successfully!'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'error',
                'message' => 'Failed to delete event!'
            ];
        }
    }
}

header("Location: manage_events.php");
exit();
?>