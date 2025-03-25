<?php
session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if application_id is provided
if (!isset($_GET['application_id'])) {
    die("Error: Application ID not provided.");
}

$application_id = trim($_GET['application_id']);

// Validate application_id format (e.g., X/BAT/25/XXXX)
if (!preg_match('/^X\/BAT\/25\/\d{4}$/', $application_id)) {
    die("Error: Invalid application ID.");
}

// Fetch documents for the application from the uploaded_files table
$sql = "SELECT file_type, file_path FROM uploaded_files WHERE application_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $application_id);
$stmt->execute();
$result = $stmt->get_result();
$documents = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

if (empty($documents)) {
    die("Error: No documents found for Application ID: " . htmlspecialchars($application_id));
}

// If there's only one document, download it directly
if (count($documents) === 1) {
    $doc = $documents[0];
    $file_path = $doc['file_path'];
    // Derive the file name from the file_path
    $file_name = basename($file_path);

    if (file_exists($file_path)) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit();
    } else {
        die("Error: Document not found on server.");
    }
}

// If there are multiple documents, create a ZIP file
$zip = new ZipArchive();
$zip_name = "Documents_" . str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $application_id) . ".zip";

if ($zip->open($zip_name, ZipArchive::CREATE) !== TRUE) {
    die("Error: Could not create ZIP file.");
}

foreach ($documents as $doc) {
    $file_path = $doc['file_path'];
    $file_name = basename($file_path); // Use the basename of the file_path as the file name in the ZIP
    if (file_exists($file_path)) {
        $zip->addFile($file_path, $file_name);
    }
}

$zip->close();

// Download the ZIP file
if (file_exists($zip_name)) {
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($zip_name) . '"');
    header('Content-Length: ' . filesize($zip_name));
    readfile($zip_name);
    unlink($zip_name); // Delete the ZIP file after download
    exit();
} else {
    die("Error: Could not create ZIP file for download.");
}
?>