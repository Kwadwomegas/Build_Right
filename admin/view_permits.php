<?php
session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$search = '';
$records = [];

// Handle search functionality
if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
    $search = trim($_GET['search']);
    // Prepare the SQL query with search filter
    $sql = "SELECT p.application_id, p.permit_number, p.issue_date, p.ppo_name, 
                   pa.applicant_name, pa.permit_type, ad.construction_location
            FROM permits p
            LEFT JOIN permit_applications pa ON p.application_id = pa.application_id
            LEFT JOIN application_details ad ON p.application_id = ad.application_id
            WHERE p.permit_number LIKE ? 
               OR p.application_id LIKE ? 
               OR p.ppo_name LIKE ?
            ORDER BY p.issue_date DESC";
    $stmt = $conn->prepare($sql);
    $search_param = "%$search%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
    $stmt->close();
} else {
    // Fetch all records if no search term is provided
    $sql = "SELECT p.application_id, p.permit_number, p.issue_date, p.ppo_name, 
                   pa.applicant_name, pa.permit_type, ad.construction_location
            FROM permits p
            LEFT JOIN permit_applications pa ON p.application_id = pa.application_id
            LEFT JOIN application_details ad ON p.application_id = ad.application_id
            ORDER BY p.issue_date DESC";
    $result = $conn->query($sql);
    if (!$result) {
        // Log the error and redirect
        error_log("SQL Error: " . $conn->error);
        $_SESSION['error_msg'] = "Error fetching permits: " . $conn->error;
        header("Location: admin_dashboard.php?error=1");
        exit();
    }
    while ($row = $result->fetch_assoc()) {
        $records[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Permits</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 1200px;
            margin-top: 30px;
        }
        .table-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .search-form {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Build_Right</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <h2 class="text-center mb-4">View Permits</h2>

        <!-- Search Form -->
        <div class="search-form">
            <form method="GET" action="view_permits.php" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Search by Permit Number, Application ID, or PPO Name" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Permits Table -->
        <div class="table-container">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Permit Number</th>
                        <th>Application ID</th>
                        <th>Applicant Name</th>
                        <th>Permit Type</th>
                        <th>Construction Location</th>
                        <th>Issue Date</th>
                        <th>PPO Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($records)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No permits found.</td>
                        </tr>
                    <?php else: ?>
                        <?php $counter = 1; ?>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?php echo $counter++; ?></td>
                                <td><?php echo htmlspecialchars($record['permit_number']); ?></td>
                                <td><?php echo htmlspecialchars($record['application_id']); ?></td>
                                <td><?php echo htmlspecialchars($record['applicant_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['permit_type'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($record['construction_location'] ?? 'N/A'); ?></td>
                                <td><?php echo $record['issue_date'] ? date('jS F Y', strtotime($record['issue_date'])) : 'N/A'; ?></td>
                                <td><?php echo htmlspecialchars($record['ppo_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <a href="generate_permit_certificate.php?application_id=<?php echo urlencode($record['application_id']); ?>" class="btn btn-sm btn-primary">View Certificate</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>