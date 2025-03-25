<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $application_id = $_POST['application_id'];
    $status = $_POST['status']; // Expected values: 'approved' or 'rejected'
    $admin_comment = $_POST['admin_comment'];
    
    // Update application status and store admin comment if provided
    $stmt = $conn->prepare("UPDATE applications SET status = ?, admin_comment = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $admin_comment, $application_id);
    
    if ($stmt->execute()) {
        // Optionally, you can integrate PHPMailer to send an email notification to the user here
        
        header("Location: ../admin/admin_dashboard.php?msg=Application updated successfully");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
} else {
    header("Location: ../admin/admin_dashboard.php");
    exit();
}
?>
