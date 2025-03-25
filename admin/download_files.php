<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if application_id is provided
if (!isset($_GET['application_id'])) {
    header("Location: admin_dashboard.php?error=Application ID not provided");
    exit();
}

$application_id = $_GET['application_id'];

// Fetch all file paths for the application
$sql = "SELECT file_path FROM uploaded_files WHERE application_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: admin_dashboard.php?error=No files found for this application");
    exit();
}

// Define the documents directory and application-specific subfolder
$documentsDir = __DIR__ . '/../documents';
$applicationDir = $documentsDir . '/' . $application_id;

// Create the directories if they don't exist
if (!is_dir($documentsDir) && !mkdir($documentsDir, 0777, true)) {
    die("Failed to create documents directory: $documentsDir");
}

if (!is_dir($applicationDir) && !mkdir($applicationDir, 0777, true)) {
    die("Failed to create application directory: $applicationDir");
}

// Define the base path for the uploads folder
$uploadsBasePath = realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR . 'uploads';

// Copy files to the application folder
while ($row = $result->fetch_assoc()) {
    $relativeFilePath = ltrim($row['file_path'], '/\\');
    $relativeFilePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $relativeFilePath);
    
    $filePath = file_exists($relativeFilePath) ? $relativeFilePath : $uploadsBasePath . DIRECTORY_SEPARATOR . $relativeFilePath;
    
    if (file_exists($filePath)) {
        $destinationPath = $applicationDir . DIRECTORY_SEPARATOR . basename($filePath);
        copy($filePath, $destinationPath);
    }
}

// Generate the downloadable link
$downloadLink = "../documents/$application_id/";

// Redirect to admin dashboard with the download link and success message
header("Location: admin_dashboard.php?success=Files saved. <a href='$downloadLink'>Download Folder</a>. After downloading, you will see a success message.");
exit();
?>
