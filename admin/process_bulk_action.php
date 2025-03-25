<?php
session_start();
include '../config/db.php';
include '../lib/send_email.php'; // Include the email sending function

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_status'])) {
    $status = $_POST['bulk_status'];
    $select_all_pending = isset($_POST['select_all_pending']) && $_POST['select_all_pending'] == '1';

    // Validate status
    $allowed_statuses = ['approve', 'reject', 'defer'];
    if (!in_array($status, $allowed_statuses)) {
        header("Location: admin_dashboard.php?error=Invalid status");
        exit();
    }

    // Map action to database status
    $db_status = $status === 'approve' ? 'approved' : ($status === 'reject' ? 'rejected' : 'deferred');

    // If "Apply to All Pending" is not checked, show an error
    if (!$select_all_pending) {
        header("Location: admin_dashboard.php?error=Please check 'Apply to All Pending' to perform a bulk action");
        exit();
    }

    // Fetch applicants' details before updating (for approval notifications)
    $applicants_to_notify = [];
    if ($db_status === 'approved') {
        // Fetch all pending applications matching the filters
        $search = isset($_POST['search']) ? trim($_POST['search']) : '';
        $status_filter = isset($_POST['status_filter']) ? trim($_POST['status_filter']) : '';
        $permit_type_filter = isset($_POST['permit_type_filter']) ? trim($_POST['permit_type_filter']) : '';
        $date_from = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
        $date_to = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

        $sql_fetch = "SELECT application_id, applicant_name, applicant_email, permit_type FROM permit_applications p WHERE p.status = 'pending'";
        $params = [];
        $types = '';

        if (!empty($search)) {
            $sql_fetch .= " AND (p.applicant_name LIKE ? OR p.applicant_mobile LIKE ?)";
            $searchTerm = "%{$search}%";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }

        if (!empty($status_filter) && $status_filter !== 'All') {
            $sql_fetch .= " AND p.status = ?";
            $params[] = $status_filter;
            $types .= "s";
        }

        if (!empty($permit_type_filter) && $permit_type_filter !== 'All') {
            $sql_fetch .= " AND p.permit_type = ?";
            $params[] = $permit_type_filter;
            $types .= "s";
        }

        if (!empty($date_from)) {
            $sql_fetch .= " AND p.created_at >= ?";
            $params[] = $date_from . " 00:00:00";
            $types .= "s";
        }

        if (!empty($date_to)) {
            $sql_fetch .= " AND p.created_at <= ?";
            $params[] = $date_to . " 23:59:59";
            $types .= "s";
        }

        $stmt_fetch = $conn->prepare($sql_fetch);
        if (!empty($params)) {
            $stmt_fetch->bind_param($types, ...$params);
        }
        $stmt_fetch->execute();
        $result = $stmt_fetch->get_result();

        while ($row = $result->fetch_assoc()) {
            $applicants_to_notify[] = $row;
        }
        $stmt_fetch->close();
    }

    // Perform the bulk update
    $search = isset($_POST['search']) ? trim($_POST['search']) : '';
    $status_filter = isset($_POST['status_filter']) ? trim($_POST['status_filter']) : '';
    $permit_type_filter = isset($_POST['permit_type_filter']) ? trim($_POST['permit_type_filter']) : '';
    $date_from = isset($_POST['date_from']) ? trim($_POST['date_from']) : '';
    $date_to = isset($_POST['date_to']) ? trim($_POST['date_to']) : '';

    $sql = "UPDATE permit_applications p SET p.status = ? WHERE p.status = 'pending'";
    $params = [$db_status];
    $types = 's';

    if (!empty($search)) {
        $sql .= " AND (p.applicant_name LIKE ? OR p.applicant_mobile LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "ss";
    }

    if (!empty($status_filter) && $status_filter !== 'All') {
        $sql .= " AND p.status = ?";
        $params[] = $status_filter;
        $types .= "s";
    }

    if (!empty($permit_type_filter) && $permit_type_filter !== 'All') {
        $sql .= " AND p.permit_type = ?";
        $params[] = $permit_type_filter;
        $types .= "s";
    }

    if (!empty($date_from)) {
        $sql .= " AND p.created_at >= ?";
        $params[] = $date_from . " 00:00:00";
        $types .= "s";
    }

    if (!empty($date_to)) {
        $sql .= " AND p.created_at <= ?";
        $params[] = $date_to . " 23:59:59";
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        header("Location: admin_dashboard.php?error=Failed to prepare statement: " . $conn->error);
        exit();
    }

    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        $affected_rows = $stmt->affected_rows;

        // Send emails to all approved applicants
        if ($db_status === 'approved' && !empty($applicants_to_notify)) {
            $approval_date = date('Y-m-d H:i:s');
            foreach ($applicants_to_notify as $applicant) {
                $application_id = $applicant['application_id'];
                $applicant_name = $applicant['applicant_name'];
                $email = $applicant['applicant_email'];
                $permit_type = $applicant['permit_type'];

                $subject = "Your Permit Application Has Been Approved";
                $body = "
                    <h2>Application Approved</h2>
                    <p>Dear $applicant_name,</p>
                    <p>We are pleased to inform you that your permit application (ID: $application_id) has been approved on $approval_date.</p>
                    <p><strong>Permit Type:</strong> $permit_type</p>
                    <p>You can now proceed to the next steps. If a permit issuance is required, you will be notified once it is issued.</p>
                    <p>Thank you for using the Build Right System.</p>
                    <p>Best regards,<br>The Build Right Team</p>
                ";

                if (!sendEmail($email, $subject, $body)) {
                    error_log("Failed to send approval email to $email for application ID $application_id");
                }
            }
        }

        header("Location: admin_dashboard.php?msg=Successfully $status" . "d $affected_rows application(s)");
    } else {
        header("Location: admin_dashboard.php?error=Failed to perform bulk action: " . $stmt->error);
    }

    $stmt->close();
} else {
    header("Location: admin_dashboard.php?error=Invalid request");
}

$conn->close();
exit();
?>