<?php
session_start();
include '../config/db.php';

// Adjust the path based on your directory structure
include __DIR__ . '/lib/send_email.php'; // Use __DIR__ to ensure correct path

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("Received POST data: " . print_r($_POST, true));

    $application_id = isset($_POST['application_id']) ? trim($_POST['application_id']) : '';
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';

    error_log("Processed - Application ID: '$application_id', Status: '$status'");

    $allowed_statuses = ['approved', 'rejected', 'deferred'];

    if (empty($application_id)) {
        $_SESSION['error_msg'] = "Application ID is missing";
        header("Location: admin_dashboard.php?error=1");
        exit();
    } elseif (!preg_match('/^X\/BAT\/25\/\d{4}$/', $application_id)) {
        $_SESSION['error_msg'] = "Invalid application ID format: $application_id";
        header("Location: admin_dashboard.php?error=1");
        exit();
    } elseif (!in_array($status, $allowed_statuses)) {
        $_SESSION['error_msg'] = "Invalid status value: $status";
        header("Location: admin_dashboard.php?error=1");
        exit();
    }

    $sql = "SELECT applicant_name, applicant_email, permit_type, status 
            FROM permit_applications 
            WHERE application_id = ? AND status = 'pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $application_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $application = $result->fetch_assoc();
    $stmt->close();

    if ($application) {
        $sql_update = "UPDATE permit_applications SET status = ? WHERE application_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ss", $status, $application_id);

        if ($stmt_update->execute()) {
            error_log("Successfully updated $application_id to status $status");

            // Send email notification (but don't crash if it fails)
            $applicant_name = $application['applicant_name'];
            $email = $application['applicant_email'];
            $permit_type = $application['permit_type'];

            $subject = "Application Status Update - Build Right System";
            $body = "<h3>Dear $applicant_name,</h3>
                     <p>Your $permit_type permit application (ID: $application_id) has been <strong>" . ucfirst($status) . "</strong>.</p>
                     <p>Please log in to the Build Right System for more details.</p>
                     <p>Best regards,<br>Build Right System Team</p>";

            try {
                if (function_exists('sendEmail')) {
                    if (!sendEmail($email, $subject, $body)) {
                        error_log("Failed to send email to $email for $application_id");
                    } else {
                        error_log("Email sent successfully to $email");
                    }
                } else {
                    error_log("sendEmail function not defined");
                }
            } catch (Exception $e) {
                error_log("Email sending failed: " . $e->getMessage());
            }

            $_SESSION['success_msg'] = "Application $application_id has been " . ucfirst($status) . " successfully";
            error_log("Redirecting to admin_dashboard.php?success=1");
            header("Location: admin_dashboard.php?success=1");
        } else {
            error_log("Update failed for $application_id: " . $stmt_update->error);
            $_SESSION['error_msg'] = "Failed to update application status: " . $stmt_update->error;
            header("Location: admin_dashboard.php?error=1");
        }
        $stmt_update->close();
    } else {
        error_log("Application $application_id not found or not pending");
        $_SESSION['error_msg'] = "Application not found or not in pending status";
        header("Location: admin_dashboard.php?error=1");
    }
} else {
    $_SESSION['error_msg'] = "Invalid request";
    header("Location: admin_dashboard.php?error=1");
}

$conn->close();
exit();
?>