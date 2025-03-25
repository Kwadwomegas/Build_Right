<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Get user role for dashboard redirection
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';
$dashboard_link = ($user_role === 'admin') ? '../admin/admin_dashboard.php' : 'user_dashboard.php';

// Get application_id from URL
$application_id = isset($_GET['application_id']) ? htmlspecialchars($_GET['application_id']) : 'Unknown';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted Successfully</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #007BFF;
        }
        .navbar-brand, .nav-link {
            color: white !important;
        }
        .success-container {
            margin-top: 100px;
            text-align: center;
            background-color: #e6f4ea;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .success-container h2 {
            color: #28a745;
        }
        .btn-primary {
            background-color: #007BFF;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Build Right</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="<?php echo $dashboard_link; ?>">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Success Message -->
    <div class="container success-container">
        <h2>Application Submitted Successfully!</h2>
        <p>The application with ID: <strong><?php echo $application_id; ?></strong> has been submitted.</p>
        <a href="<?php echo $dashboard_link; ?>" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>