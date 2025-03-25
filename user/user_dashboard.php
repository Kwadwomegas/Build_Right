<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch the user's full name from the session
$full_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'User';

// Fetch application analytics
$query_total = "SELECT COUNT(*) AS total FROM permit_applications";
$query_approved = "SELECT COUNT(*) AS approved FROM permit_applications WHERE status='approved'";
$query_rejected = "SELECT COUNT(*) AS rejected FROM permit_applications WHERE status='rejected'";
$query_deferred = "SELECT COUNT(*) AS deferred FROM permit_applications WHERE status='deferred'";

$total_result = $conn->query($query_total);
$approved_result = $conn->query($query_approved);
$rejected_result = $conn->query($query_rejected);
$deferred_result = $conn->query($query_deferred);

$total = $total_result->fetch_assoc()['total'];
$approved = $approved_result->fetch_assoc()['approved'];
$rejected = $rejected_result->fetch_assoc()['rejected'];
$deferred = $deferred_result->fetch_assoc()['deferred'];

// Fetch all applications for the table
$query_applications = "SELECT application_id, applicant_name, applicant_mobile, permit_type, status, created_at 
                       FROM permit_applications 
                       ORDER BY created_at DESC";
$result_applications = $conn->query($query_applications);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard - Build_Right</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    /* Ensures the footer stays at the bottom */
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
      font-family: 'Arial', sans-serif;
    }
    .content {
      flex: 1;
    }
    /* Navbar Styling */
    .navbar {
      background-color: #007bff;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    .navbar-brand {
      font-weight: bold;
      font-size: 1.5rem;
    }
    .nav-link {
      color: white !important;
      font-weight: 500;
      transition: color 0.3s ease;
    }
    .nav-link:hover {
      color: #e0e0e0 !important;
    }
    .welcome-message {
      color: #ffd700;
      font-weight: 500;
      margin-right: 15px;
    }
    /* Analytics Cards */
    .card {
      border: none;
      border-radius: 10px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }
    .card-body {
      padding: 20px;
    }
    .card-title {
      font-size: 1.1rem;
      margin-bottom: 10px;
    }
    .card h2 {
      font-size: 2rem;
      margin: 0;
    }
    /* Table Styling */
    .table-container {
      margin-top: 30px;
      overflow-x: auto;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
    }
    .table-heading {
      text-align: center;
      margin-bottom: 20px;
      color: #333;
    }
    .table th, .table td {
      vertical-align: middle;
      font-size: 0.9rem;
    }
    .table th {
      background-color: #007bff;
      color: white;
    }
    .status-pending {
      color: #ffc107;
      font-weight: bold;
    }
    .status-approved {
      color: #28a745;
      font-weight: bold;
    }
    .status-rejected {
      color: #dc3545;
      font-weight: bold;
    }
    .status-deferred {
      color: #fd7e14;
      font-weight: bold;
    }
    /* Search Bar */
    .search-container {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 15px;
    }
    .search-container input {
      width: 250px;
      height: 38px;
      font-size: 14px;
      border-radius: 8px;
      border: 1px solid #ced4da;
      padding: 0 10px;
      transition: border-color 0.3s ease;
    }
    .search-container input:focus {
      border-color: #007bff;
      box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
      outline: none;
    }
    /* Footer */
    footer {
      background-color: #1a252f;
      color: white;
      padding: 20px 0;
      font-size: 0.9rem;
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
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item">
            <span class="welcome-message">Welcome, <?php echo $full_name; ?>!</span>
          </li>
          <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="application.php">Apply for Permit</a></li>
          <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container mt-4 content">
    <!-- Analytics Section -->
    <div class="row mt-4">
      <div class="col-md-3">
        <div class="card text-white bg-primary shadow">
          <div class="card-body">
            <h5 class="card-title">Total Applications</h5>
            <h2><?php echo $total; ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-success shadow">
          <div class="card-body">
            <h5 class="card-title">Approved Applications</h5>
            <h2><?php echo $approved; ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-danger shadow">
          <div class="card-body">
            <h5 class="card-title">Rejected Applications</h5>
            <h2><?php echo $rejected; ?></h2>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card text-white bg-warning shadow">
          <div class="card-body">
            <h5 class="card-title">Deferred Applications</h5>
            <h2><?php echo $deferred; ?></h2>
          </div>
        </div>
      </div>
    </div>

    <!-- Applications Table -->
    <div class="table-container">
          
  <!-- Search Bar -->
      <div class="search-container">
        <input type="text" id="searchInput" class="form-control" placeholder="Search by Application ID">
      </div>

      <table class="table table-striped table-bordered" id="applicationsTable">
        <thead>
          <tr>
            <th>Application ID</th>
            <th>Applicant Name</th>
            <th>Applicant Mobile</th>
            <th>Permit Type</th>
            <th>Status</th>
            <th>Application Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result_applications->num_rows > 0): ?>
            <?php while ($row = $result_applications->fetch_assoc()): ?>
              <tr>
                <td class="application-id"><?php echo htmlspecialchars($row['application_id']); ?></td>
                <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
                <td><?php echo htmlspecialchars($row['applicant_mobile']); ?></td>
                <td><?php echo htmlspecialchars($row['permit_type']); ?></td>
                <td>
                  <span class="status-<?php echo strtolower($row['status']); ?>">
                    <?php echo htmlspecialchars(ucfirst($row['status'])); ?>
                  </span>
                </td>
                <td><?php echo htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))); ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No applications found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <footer class="bg-dark text-white text-center py-3 mt-auto">
    <p>© <?php echo date("Y"); ?> Building Permit System. Designed by KabTech Consulting. All Rights Reserved.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Real-time search functionality
    document.getElementById('searchInput').addEventListener('input', function() {
      const searchValue = this.value.trim().toLowerCase();
      const table = document.getElementById('applicationsTable');
      const rows = table.getElementsByTagName('tr');

      // Loop through all table rows (skip the header row)
      for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        const applicationIdCell = row.getElementsByClassName('application-id')[0];
        const applicationId = applicationIdCell.textContent.toLowerCase();

        // Show or hide the row based on the search value
        if (applicationId.includes(searchValue)) {
          row.style.display = '';
        } else {
          row.style.display = 'none';
        }
      }

      // If the search input is empty, show all rows
      if (searchValue === '') {
        for (let i = 1; i < rows.length; i++) {
          rows[i].style.display = '';
        }
      }
    });
  </script>
</body>
</html>