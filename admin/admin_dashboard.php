<?php
session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get the admin's name safely
$admin_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Admin";

// Handle status updates (single application)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && isset($_POST['application_id'])) {
    $application_id = $_POST['application_id'];
    $action = isset($_POST['action']) ? strtolower(trim($_POST['action'])) : '';

    // Debug: Log the raw POST data
    error_log("Raw POST data: " . print_r($_POST, true));

    // Debug: Log the action value
    error_log("Action received: '$action' for application_id: $application_id");

    // Map the form action to the ENUM value
    $status_map = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'defer' => 'deferred'
    ];

    $valid_actions = array_keys($status_map);
    if (empty($action)) {
        $error = "Action is empty. Please try again.";
        error_log("Error: Action is empty for application_id: $application_id");
    } elseif (!in_array($action, $valid_actions)) {
        $error = "Invalid action: " . htmlspecialchars($action);
        error_log("Error: Invalid action '$action' for application_id: $application_id");
    } else {
        // Convert the action to the corresponding ENUM value
        $new_status = $status_map[$action];

        // Debug: Log the new status being set
        error_log("Updating status for application_id $application_id to '$new_status'");

        $stmt = $conn->prepare("UPDATE permit_applications SET status = ? WHERE application_id = ?");
        if (!$stmt) {
            $error = "Failed to prepare statement: " . $conn->error;
            error_log("Prepare statement failed: " . $conn->error);
        } else {
            $stmt->bind_param("ss", $new_status, $application_id);
            if ($stmt->execute()) {
                $msg = "Status updated to " . ucfirst($new_status);
                error_log("Status successfully updated to '$new_status' for application_id: $application_id");
            } else {
                $error = "Failed to update status: " . $stmt->error;
                error_log("Status update failed for application_id $application_id: " . $stmt->error);
            }
            $stmt->close();
        }
    }

    // Redirect without preserving filter parameters
    $query_params = [];
    if (isset($msg)) $query_params['msg'] = urlencode($msg);
    if (isset($error)) $query_params['error'] = urlencode($error);

    $redirect_url = "admin_dashboard.php";
    if (!empty($query_params)) {
        $redirect_url .= "?" . http_build_query($query_params);
    }
    header("Location: $redirect_url");
    exit();
}

// Handle bulk status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_status'])) {
    $bulk_action = isset($_POST['bulk_status']) ? strtolower(trim($_POST['bulk_status'])) : '';

    // Debug: Log the bulk action
    error_log("Bulk action received: '$bulk_action'");

    // Map the form action to the ENUM value
    $status_map = [
        'approve' => 'approved',
        'reject' => 'rejected',
        'defer' => 'deferred'
    ];

    $valid_actions = array_keys($status_map);
    if (empty($bulk_action)) {
        $error = "Bulk action is empty. Please try again.";
        error_log("Error: Bulk action is empty");
    } elseif (!in_array($bulk_action, $valid_actions)) {
        $error = "Invalid bulk action: " . htmlspecialchars($bulk_action);
        error_log("Error: Invalid bulk action '$bulk_action'");
    } elseif (!isset($_POST['select_all_pending']) || $_POST['select_all_pending'] != '1') {
        $error = "Please select 'Apply to All Pending' to perform a bulk action.";
    } else {
        $new_status = $status_map[$bulk_action];
        error_log("Applying bulk action: '$new_status' to all pending applications");

        $stmt = $conn->prepare("UPDATE permit_applications SET status = ? WHERE status = 'pending'");
        $stmt->bind_param("s", $new_status);
        if ($stmt->execute()) {
            $msg = "Bulk action '" . ucfirst($new_status) . "' applied to all pending applications.";
            error_log("Bulk action '$new_status' successfully applied");
        } else {
            $error = "Failed to apply bulk action: " . $stmt->error;
            error_log("Bulk status update failed: " . $stmt->error);
        }
        $stmt->close();
    }

    // Redirect without preserving filter parameters
    $query_params = [];
    if (isset($msg)) $query_params['msg'] = urlencode($msg);
    if (isset($error)) $query_params['error'] = urlencode($error);

    $redirect_url = "admin_dashboard.php";
    if (!empty($query_params)) {
        $redirect_url .= "?" . http_build_query($query_params);
    }
    header("Location: $redirect_url");
    exit();
}

// Initialize variables for filtering
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$permit_type = isset($_GET['permit_type']) ? trim($_GET['permit_type']) : '';
$start_date = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';

// Build the SQL query (no join needed)
$sql = "SELECT application_id, applicant_name, applicant_mobile, permit_type, status, created_at, permit_issued 
        FROM permit_applications WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= " AND (applicant_name LIKE ? OR applicant_mobile LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "ss";
}

if (!empty($status) && $status !== 'All') {
    $sql .= " AND status = ?";
    $params[] = $status;
    $types .= "s";
}

if (!empty($permit_type) && $permit_type !== 'All') {
    $sql .= " AND permit_type = ?";
    $params[] = $permit_type;
    $types .= "s";
}

if (!empty($start_date)) {
    $sql .= " AND created_at >= ?";
    $params[] = $start_date;
    $types .= "s";
}

if (!empty($end_date)) {
    $sql .= " AND created_at <= ?";
    $params[] = $end_date;
    $types .= "s";
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$applications = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch documents for each application
$documents = [];
foreach ($applications as $app) {
    $app_id = $app['application_id'];
    $sql_docs = "SELECT file_type, file_path FROM uploaded_files WHERE application_id = ?";
    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->bind_param("s", $app_id);
    $stmt_docs->execute();
    $result_docs = $stmt_docs->get_result();
    $documents[$app_id] = $result_docs->fetch_all(MYSQLI_ASSOC);
    $stmt_docs->close();
}

// Fetch statistics for the cards
$sql_total = "SELECT COUNT(*) as count FROM permit_applications";
$sql_pending = "SELECT COUNT(*) as count FROM permit_applications WHERE status = 'pending'";
$sql_approved = "SELECT COUNT(*) as count FROM permit_applications WHERE status = 'approved'";
$sql_deferred = "SELECT COUNT(*) as count FROM permit_applications WHERE status = 'deferred'";

$total = $conn->query($sql_total)->fetch_assoc()['count'];
$pending = $conn->query($sql_pending)->fetch_assoc()['count'];
$approved = $conn->query($sql_approved)->fetch_assoc()['count'];
$deferred = $conn->query($sql_deferred)->fetch_assoc()['count'];

// Handle Export to CSV
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="permit_applications.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['Application ID', 'Applicant Name', 'Mobile', 'Permit Type', 'Status', 'Created At', 'Permit Issued']);
    foreach ($applications as $app) {
        fputcsv($output, [
            $app['application_id'],
            $app['applicant_name'],
            $app['applicant_mobile'],
            $app['permit_type'],
            $app['status'],
            $app['created_at'],
            $app['permit_issued'] ? 'Yes' : 'No'
        ]);
    }
    fclose($output);
    exit();
}

// Display success or error messages
$msg = isset($msg) ? htmlspecialchars($msg) : (isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : '');
$error = isset($error) ? htmlspecialchars($error) : (isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '');

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .navbar { background-color: #007bff; }
        .navbar-brand, .nav-link { color: #fff !important; }
        .container { margin-top: 20px; }
        .card { margin-bottom: 20px; }
        .card-total { background-color: #007bff; color: white; }
        .card-pending { background-color: #ffc107; }
        .card-approved { background-color: #28a745; color: white; }
        .card-deferred { background-color: #6c757d; color: white; }
        .table { background-color: #fff; }
        .table.table-bordered thead th { background-color: #007bff !important; color: #fff !important; }
        .btn-apply { background-color: #28a745; color: white; }
        .btn-sm { margin-right: 5px; }
        .status-pending { color: #ffc107; font-weight: bold; }
        .status-approved { color: #28a745; font-weight: bold; }
        .status-rejected { color: #dc3545; font-weight: bold; }
        .status-deferred { color: #6c757d; font-weight: bold; }
        .permit-issued { color: #28a745; font-weight: bold; }
        .permit-not-issued { color: #dc3545; font-weight: bold; }
        .bulk-actions { margin-bottom: 20px; }
        .btn-print { background-color: #17a2b8; color: white; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Build_Right</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Welcome, <strong><?php echo htmlspecialchars($admin_name); ?>!</strong></a></li>
                    <li class="nav-item"><a class="nav-link" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php if ($msg): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Form -->
        <form method="GET" action="admin_dashboard.php" class="mb-4">
            <div class="row">
                <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Search by Name or Mobile" value="<?php echo htmlspecialchars($search); ?>"></div>
                <div class="col-md-2">
                    <select name="status" class="form-control">
                        <option value="All" <?php echo $status === 'All' ? 'selected' : ''; ?>>All Statuses</option>
                        <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                        <option value="deferred" <?php echo $status === 'deferred' ? 'selected' : ''; ?>>Deferred</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="permit_type" class="form-control">
                        <option value="All" <?php echo $permit_type === 'All' ? 'selected' : ''; ?>>All Permit Types</option>
                        <option value="Residential" <?php echo $permit_type === 'Residential' ? 'selected' : ''; ?>>Residential</option>
                        <option value="Commercial" <?php echo $permit_type === 'Commercial' ? 'selected' : ''; ?>>Commercial</option>
                        <option value="Industrial" <?php echo $permit_type === 'Industrial' ? 'selected' : ''; ?>>Industrial</option>
                    </select>
                </div>
                <div class="col-md-2"><input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($start_date); ?>"></div>
                <div class="col-md-2"><input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($end_date); ?>"></div>
                <div class="col-md-1"><button type="submit" class="btn btn-primary w-100">Filter</button></div>
            </div>
            <div class="row mt-2">
                <div class="col-md-2"><a href="admin_dashboard.php" class="btn btn-secondary w-100">Clear</a></div>
                <div class="col-md-2"><a href="admin_dashboard.php?export=csv" class="btn btn-info w-100">Export to CSV</a></div>
                <div class="col-md-2 offset-md-6">
                    <a href="view_permits.php" class="btn btn-print w-100">Print Certificate</a>
                </div>
            </div>
        </form>

        <!-- Apply for Permit Button -->
        <a href="../user/application.php" class="btn btn-apply mb-4">Apply for Permit</a>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3"><div class="card card-total text-center"><div class="card-body"><h5 class="card-title">Total Applications</h5><p class="card-text display-4"><?php echo $total; ?></p></div></div></div>
            <div class="col-md-3"><div class="card card-pending text-center"><div class="card-body"><h5 class="card-title">Pending Applications</h5><p class="card-text display-4"><?php echo $pending; ?></p></div></div></div>
            <div class="col-md-3"><div class="card card-approved text-center"><div class="card-body"><h5 class="card-title">Approved Applications</h5><p class="card-text display-4"><?php echo $approved; ?></p></div></div></div>
            <div class="col-md-3"><div class="card card-deferred text-center"><div class="card-body"><h5 class="card-title">Deferred Applications</h5><p class="card-text display-4"><?php echo $deferred; ?></p></div></div></div>
        </div>

        <!-- Bulk Actions Form -->
        <form method="POST" action="admin_dashboard.php" class="bulk-actions">
            <div class="row mb-2">
                <div class="col-md-12">
                    <button type="submit" name="bulk_status" value="approve" class="btn btn-success me-2">Bulk Approve</button>
                    <button type="submit" name="bulk_status" value="reject" class="btn btn-danger me-2">Bulk Reject</button>
                    <button type="submit" name="bulk_status" value="defer" class="btn btn-warning me-2">Bulk Defer</button>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="select_all_pending" id="selectAllPending" value="1">
                        <label class="form-check-label" for="selectAllPending">Apply to All Pending</label>
                    </div>
                </div>
            </div>

            <!-- Applications Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Application ID</th>
                        <th>Applicant Name</th>
                        <th>Mobile</th>
                        <th>Permit Type</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Permit Issued</th>
                        <th>Issue Permit</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr><td colspan="9" class="text-center">No applications found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($app['application_id']); ?></td>
                                <td><?php echo htmlspecialchars($app['applicant_name']); ?></td>
                                <td><?php echo htmlspecialchars($app['applicant_mobile']); ?></td>
                                <td><?php echo htmlspecialchars($app['permit_type']); ?></td>
                                <td>
                                    <?php
                                    $status = strtolower(trim($app['status']));
                                    if ($status === 'pending') {
                                        echo '<form method="POST" action="admin_dashboard.php" style="display:inline;">';
                                        echo '<input type="hidden" name="application_id" value="' . htmlspecialchars($app['application_id']) . '">';
                                        echo '<button type="submit" name="action" value="approve" class="btn btn-success btn-sm">Approve</button>';
                                        echo '</form> ';
                                        echo '<form method="POST" action="admin_dashboard.php" style="display:inline;">';
                                        echo '<input type="hidden" name="application_id" value="' . htmlspecialchars($app['application_id']) . '">';
                                        echo '<button type="submit" name="action" value="reject" class="btn btn-danger btn-sm">Reject</button>';
                                        echo '</form> ';
                                        echo '<form method="POST" action="admin_dashboard.php" style="display:inline;">';
                                        echo '<input type="hidden" name="application_id" value="' . htmlspecialchars($app['application_id']) . '">';
                                        echo '<button type="submit" name="action" value="defer" class="btn btn-warning btn-sm">Defer</button>';
                                        echo '</form>';
                                    } else {
                                        $status_class = "status-$status";
                                        echo "<span class='$status_class'>" . ($status ? ucfirst($status) : 'Empty') . "</span>";
                                    }
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($app['created_at']); ?></td>
                                <td>
                                    <?php
                                    $permit_issued = (int)$app['permit_issued'] === 1;
                                    echo $permit_issued ? "<span class='permit-issued'>Yes</span>" : "<span class='permit-not-issued'>No</span>";
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    if ($status === 'approved' && !$permit_issued) {
                                        echo "<a href='development_permit.php?application_id=" . urlencode($app['application_id']) . "' class='btn btn-primary btn-sm'>Generate Permit</a>";
                                    } elseif ($permit_issued) {
                                        echo "<span class='text-muted'>Permit Issued</span>";
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $has_documents = !empty($documents[$app['application_id']]);
                                    echo '<a href="download_documents.php?application_id=' . urlencode($app['application_id']) . '" class="btn btn-info btn-sm' . ($has_documents ? '' : ' disabled') . '" ' . ($has_documents ? '' : 'title="No documents available"') . '>Download</a>';
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3">
        © <?php echo date("Y"); ?> Development Permit System. Designed by KabTech Consulting. All Rights Reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>