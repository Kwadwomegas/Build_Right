<?php
session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php?error=Invalid request method");
    exit();
}

// Get form data
$application_id = isset($_POST['application_id']) ? trim($_POST['application_id']) : '';
$permit_number = isset($_POST['permit_number']) ? trim($_POST['permit_number']) : '';
$permit_holder = isset($_POST['permit_holder']) ? trim($_POST['permit_holder']) : '';
$land_location = isset($_POST['land_location']) ? trim($_POST['land_location']) : '';
$construction_type = isset($_POST['construction_type']) ? trim($_POST['construction_type']) : '';
$issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : '';
$ppo_signature = isset($_POST['ppo_signature']) ? trim($_POST['ppo_signature']) : '';
$we_signature = isset($_POST['we_signature']) ? trim($_POST['we_signature']) : '';
$ppo_date = isset($_POST['ppo_date']) ? trim($_POST['ppo_date']) : '';
$we_date = isset($_POST['we_date']) ? trim($_POST['we_date']) : '';
$ppo_name = isset($_POST['ppo_name']) ? trim($_POST['ppo_name']) : '';
$we_name = isset($_POST['we_name']) ? trim($_POST['we_name']) : '';

// Validate required fields
if (empty($application_id) || empty($permit_number) || empty($issue_date) || empty($ppo_signature) || empty($we_signature) || empty($ppo_date) || empty($we_date) || empty($ppo_name) || empty($we_name)) {
    header("Location: development_permit.php?application_id=" . urlencode($application_id) . "&error=All fields are required");
    exit();
}

// Update the permit_applications table
$update_sql = "UPDATE permit_applications 
               SET permit_issued = 1, 
                   permit_number = ?, 
                   issue_date = ?, 
                   ppo_signature = ?, 
                   we_signature = ?, 
                   ppo_date = ?, 
                   we_date = ?, 
                   ppo_name = ?, 
                   we_name = ? 
               WHERE application_id = ?";
$update_stmt = $conn->prepare($update_sql);
$update_stmt->bind_param("sssssssss", $permit_number, $issue_date, $ppo_signature, $we_signature, $ppo_date, $we_date, $ppo_name, $we_name, $application_id);

if (!$update_stmt->execute()) {
    header("Location: development_permit.php?application_id=" . urlencode($application_id) . "&error=Error updating permit: " . urlencode($conn->error));
    exit();
}

$update_stmt->close();

// Redirect to generate_permit_certificate.php to generate the PDF
header("Location: generate_permit_certificate.php?application_id=" . urlencode($application_id));
exit();
?>