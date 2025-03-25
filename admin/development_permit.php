<?php
session_start();
include '../config/db.php';
include 'lib/send_email.php'; // Ensure this file exists at C:\xampp\htdocs\building_permit_system\admin\lib\send_email.php

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get the admin's name safely
$admin_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Admin";

// Initialize variables
$application_id = '';
$search_error = '';
$permit_number_search = isset($_POST['permit_number_search']) ? trim($_POST['permit_number_search']) : '';

// Handle permit number search
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($permit_number_search)) {
    // Validate permit number format (e.g., SPC/NTDA/DP-XXXX/YY)
    if (!preg_match('/^SPC\/NTDA\/DP-\d{4}\/\d{2}$/', $permit_number_search)) {
        $search_error = "Invalid permit number format. Expected format: SPC/NTDA/DP-XXXX/YY";
    } else {
        // Query the permits table to find the application_id
        $sql_search = "SELECT application_id FROM permits WHERE permit_number = ?";
        $stmt_search = $conn->prepare($sql_search);
        $stmt_search->bind_param("s", $permit_number_search);
        $stmt_search->execute();
        $result_search = $stmt_search->get_result();

        if ($result_search->num_rows > 0) {
            $row = $result_search->fetch_assoc();
            $application_id = $row['application_id'];
        } else {
            $search_error = "No application found for permit number: " . htmlspecialchars($permit_number_search);
        }
        $stmt_search->close();
    }
}

// If no application_id from search, check GET/POST
if (empty($application_id)) {
    $application_id = isset($_POST['application_id']) ? trim($_POST['application_id']) : (isset($_GET['application_id']) ? trim($_GET['application_id']) : '');
}

// Validate application_id format (e.g., X/BAT/25/XXXX)
if (empty($application_id) || !preg_match('/^X\/BAT\/25\/\d{4}$/', $application_id)) {
    if (empty($search_error)) { // Only set this error if search didn't already set an error
        $_SESSION['error_msg'] = "Invalid application ID";
        header("Location: admin_dashboard.php?error=1");
        exit();
    }
}

// Fetch application details, including land_location from application_details
$sql = "SELECT pa.applicant_name, pa.applicant_email, pa.permit_type, pa.status, pa.permit_issued, ad.land_location 
        FROM permit_applications pa 
        LEFT JOIN application_details ad ON pa.application_id = ad.application_id 
        WHERE pa.application_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $application_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();
$stmt->close();

if (!$application || $application['status'] !== 'approved' || $application['permit_issued'] == 1) {
    $_SESSION['error_msg'] = "Application not approved or permit already issued";
    header("Location: admin_dashboard.php?error=1");
    exit();
}

// Pre-fill form fields with application data
$permit_holder = $application['applicant_name'];
$construction_type = $application['permit_type'];
$land_location = $application['land_location'] ?? 'Not specified'; // Fallback if land_location is not found

// Generate the permit number in the format SPC/NTDA/DP-0000/25
$permit_number_digits = substr($application_id, -4); // Gets the last 4 characters (e.g., "0008")
$year = substr(date('Y'), -2); // Gets the last 2 digits of the year (e.g., "25")
$permit_number = "SPC/NTDA/DP-{$permit_number_digits}/{$year}";

// Initialize a variable to track if the permit was issued
$permit_issued_successfully = false;
$success_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($permit_number_search)) { // Only process form submission if not a search
    $permit_number = isset($_POST['permit_number']) ? trim($_POST['permit_number']) : '';
    $issue_date = isset($_POST['issue_date']) ? trim($_POST['issue_date']) : '';
    $ppo_signature = isset($_POST['ppo_signature']) ? trim($_POST['ppo_signature']) : '';
    $we_signature = isset($_POST['we_signature']) ? trim($_POST['we_signature']) : '';
    $ppo_date = isset($_POST['ppo_date']) ? trim($_POST['ppo_date']) : '';
    $we_date = isset($_POST['we_date']) ? trim($_POST['we_date']) : '';
    $ppo_name = isset($_POST['ppo_name']) ? trim($_POST['ppo_name']) : '';
    $we_name = isset($_POST['we_name']) ? trim($_POST['we_name']) : '';

    // Calculate expiry date (5 years from issue date)
    if (!empty($issue_date)) {
        $expiry_date = date('Y-m-d', strtotime($issue_date . ' +5 years'));
    } else {
        $expiry_date = '';
    }

    // Validate required fields
    if (!empty($permit_number) && !empty($issue_date) && !empty($ppo_signature) && !empty($we_signature) && !empty($ppo_date) && !empty($we_date) && !empty($ppo_name) && !empty($we_name)) {
        // Start a transaction to ensure data consistency
        $conn->begin_transaction();

        try {
            // Insert into permits table
            $sql_permit = "INSERT INTO permits (permit_number, application_id, permit_holder, land_location, construction_type, issue_date, expiry_date, ppo_signature, we_signature, ppo_date, we_date, ppo_name, we_name) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_permit = $conn->prepare($sql_permit);
            $stmt_permit->bind_param("sssssssssssss", 
                $permit_number, 
                $application_id, 
                $permit_holder, 
                $land_location, 
                $construction_type, 
                $issue_date, 
                $expiry_date, 
                $ppo_signature, 
                $we_signature, 
                $ppo_date, 
                $we_date, 
                $ppo_name, 
                $we_name
            );

            if (!$stmt_permit->execute()) {
                throw new Exception("Failed to insert permit: " . $stmt_permit->error);
            }

            // Update permit_applications to mark permit as issued
            $sql_update = "UPDATE permit_applications SET permit_issued = 1 WHERE application_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("s", $application_id);

            if (!$stmt_update->execute()) {
                throw new Exception("Failed to update permit issued status: " . $stmt_update->error);
            }

            // Send email notification to applicant
            $applicant_name = $application['applicant_name'];
            $email = $application['applicant_email'];
            $permit_type = $application['permit_type'];

            $subject = "Development Permit Issued - Build Right System";
            $body = "<h3>Dear $applicant_name,</h3>
                     <p>We are pleased to inform you that your $permit_type development permit has been issued.</p>
                     <p><strong>Permit Details:</strong></p>
                     <ul>
                         <li>Permit Number: $permit_number</li>
                         <li>Issue Date: $issue_date</li>
                         <li>Expiry Date: $expiry_date</li>
                     </ul>
                     <p>Please ensure compliance with all terms and conditions.</p>
                     <p>Best regards,<br>Build Right System Team</p>";

            if (!sendEmail($email, $subject, $body)) {
                error_log("Failed to send permit issuance email to $email for application ID $application_id");
            }

            // Commit the transaction
            $conn->commit();

            // Set success flag and message
            $permit_issued_successfully = true;
            $success_msg = "Permit issued successfully for application $application_id";
        } catch (Exception $e) {
            // Roll back the transaction on error
            $conn->rollback();
            $_SESSION['error_msg'] = $e->getMessage();
            header("Location: development_permit.php?application_id=" . urlencode($application_id) . "&error=1");
            exit();
        }

        $stmt_permit->close();
        $stmt_update->close();
    } else {
        $_SESSION['error_msg'] = "Please fill in all required fields";
        header("Location: development_permit.php?application_id=" . urlencode($application_id) . "&error=1");
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Development Permit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }
        .form-header {
            background: black;
            color: white;
            padding: 10px;
            border-radius: 5px;
        }
        h2, h5 {
            font-weight: bold;
            margin-top: 15px;
        }
        label {
            font-weight: bold;
        }
        .form-control {
            border-radius: 5px;
        }
        .btn {
            margin-top: 20px;
        }
        .form-control[readonly] {
            background-color: #e9ecef;
            opacity: 1;
        }
        .error-message-box {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .success-message-box {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        .search-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Build_Right</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Welcome, <strong><?php echo htmlspecialchars($admin_name); ?></strong></a>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Search Form -->
        <div class="search-form">
            <form action="development_permit.php" method="POST">
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="permit_number_search" placeholder="Enter Permit Number (e.g., SPC/NTDA/DP-0001/25)" value="<?php echo htmlspecialchars($permit_number_search); ?>">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary w-100">Search</button>
                    </div>
                </div>
            </form>
        </div>

        <?php if (!empty($search_error)): ?>
            <div class="error-message-box">
                <?php echo htmlspecialchars($search_error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['error']) && isset($_SESSION['error_msg'])): ?>
            <div class="error-message-box">
                <?php echo htmlspecialchars($_SESSION['error_msg']); ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <?php if ($permit_issued_successfully): ?>
            <div class="success-message-box">
                <h4 class="alert-heading">Success!</h4>
                <p><?php echo htmlspecialchars($success_msg); ?></p>
                <a href="admin_dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                <a href="generate_permit_certificate.php?application_id=<?php echo urlencode($application_id); ?>" class="btn btn-success">Generate Certificate</a>
            </div>
        <?php else: ?>
            <div class="form-header text-center">
                <h2>DEVELOPMENT PERMIT</h2>
            </div>

            <form action="development_permit.php" method="POST">
                <!-- Hidden field for application_id -->
                <input type="hidden" name="application_id" value="<?php echo htmlspecialchars($application_id); ?>">

                <!-- Permit Certification -->
                <div class="mb-3">
                    <label class="form-label">Permit Number</label>
                    <input type="text" class="form-control" name="permit_number" value="<?php echo htmlspecialchars($permit_number); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Permit certifies that</label>
                    <input type="text" class="form-control" name="permit_holder" value="<?php echo htmlspecialchars($permit_holder); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Having land at</label>
                    <input type="text" class="form-control" name="land_location" value="<?php echo htmlspecialchars($land_location); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Has approval from the North Tongu District Assembly to construct a</label>
                    <input type="text" class="form-control" name="construction_type" value="<?php echo htmlspecialchars($construction_type); ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Subject to the attached conditions and in accordance with the attached plan.</label>
                </div>

                <h5 class="text-center">DATED AT THE OFFICE OF THE NORTH TONGU DISTRICT ASSEMBLY</h5>

                <div class="mb-3">
                    <label class="form-label">Issue Date</label>
                    <input type="date" class="form-control" name="issue_date" required>
                </div>

                <!-- Signatures Section -->
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label">Signature (Physical Planning Officer, NTDA)</label>
                        <input type="text" class="form-control" name="ppo_signature" placeholder="Signature" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Signature (Works Engineer, NTDA)</label>
                        <input type="text" class="form-control" name="we_signature" placeholder="Signature" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Date (PPO)</label>
                        <input type="date" class="form-control" name="ppo_date" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date (Works Engineer)</label>
                        <input type="date" class="form-control" name="we_date" required>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label">Name (Physical Planning Officer, NTDA)</label>
                        <input type="text" class="form-control" name="ppo_name" placeholder="Enter name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Name (Works Engineer, NTDA)</label>
                        <input type="text" class="form-control" name="we_name" placeholder="Enter name" required>
                    </div>
                </div>

                <!-- Notes Section -->
                <h5 class="mt-4">NOTE:</h5>
                <ul>
                    <li>The set-out and foundation trenches are done under the supervision of the District Physical Planning Officer and the Building Inspector.</li>
                    <li>This development permit does not relieve the applicant from compliance with any building regulations.</li>
                    <li>The work must be completed within <b>five (5) years</b> from the date of issue.</li>
                    <li>This development permit does not confirm the right or title of the applicant to the land.</li>
                </ul>

                <button type="submit" class="btn btn-primary w-100">Submit Permit</button>
            </form>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-white text-center py-2 mt-5">
        <div class="container">
            <p class="mb-1">© <?php echo date("Y"); ?> Build_Right. All Rights Reserved.</p>
            <p class="mb-1">Designed & Developed by KabTech Consulting</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>