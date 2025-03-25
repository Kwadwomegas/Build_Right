<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $permit_type = $_POST['permit_type'];
    
    // Insert a new permit application
    $stmt = $conn->prepare("INSERT INTO applications (user_id, permit_type, status) VALUES (?, ?, 'pending')");
    $stmt->bind_param("is", $user_id, $permit_type);
    
    if ($stmt->execute()) {
        $application_id = $stmt->insert_id;

        // Process file upload if a document is provided
        if (isset($_FILES['document']) && $_FILES['document']['error'] == 0) {
            $allowed_extensions = ['pdf', 'jpg', 'png', 'docx'];
            $file_name = $_FILES['document']['name'];
            $file_tmp = $_FILES['document']['tmp_name'];
            $file_size = $_FILES['document']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Check file size (max 20MB)
            if ($file_size > 20971520) {
                die("File too large. Maximum allowed size is 20MB.");
            }
            
            // Check allowed file types
            if (!in_array($file_ext, $allowed_extensions)) {
                die("Invalid file type.");
            }
            
            // Rename file to avoid collisions and move it to the uploads folder
            $new_file_name = uniqid() . "." . $file_ext;
            $upload_path = "../uploads/" . $new_file_name;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                $stmt_doc = $conn->prepare("INSERT INTO documents (application_id, file_name, file_path) VALUES (?, ?, ?)");
                $stmt_doc->bind_param("iss", $application_id, $file_name, $upload_path);
                $stmt_doc->execute();
            }
        }
        
        header("Location: ../users/user_dashboard.php?msg=Application submitted successfully");
        exit();
    } else {
        die("Error: " . $stmt->error);
    }
} else {
    header("Location: ../users/apply.php");
    exit();
}
?>
