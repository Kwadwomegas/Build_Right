<?php
session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all users for management
$sql_users = "SELECT id, full_name, email, phone, role FROM users";
$result_users = $conn->query($sql_users);

// Fetch admin email for sending backup
$admin_id = $_SESSION['user_id'];
$sql_admin = "SELECT email FROM users WHERE id = ? AND role = 'admin'";
$stmt_admin = $conn->prepare($sql_admin);
$stmt_admin->bind_param("i", $admin_id);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();
$admin = $result_admin->fetch_assoc();
$admin_email = $admin['email'] ?? 'admin@example.com'; // Fallback email if not found
$stmt_admin->close();

// Function to perform a backup (used for both manual and scheduled backups)
function performBackup($db_config, $backup_dir) {
    $db_host = $db_config['host'];
    $db_user = $db_config['user'];
    $db_pass = $db_config['pass'];
    $db_name = $db_config['name'];

    // Clean up old backups (older than 7 days)
    if (is_dir($backup_dir)) {
        $files = glob($backup_dir . '*.sql');
        $now = time();
        foreach ($files as $file) {
            if (is_file($file) && ($now - filemtime($file)) > (7 * 24 * 60 * 60)) { // 7 days in seconds
                unlink($file);
            }
        }
    }

    // Create the backups directory if it doesn't exist
    if (!is_dir($backup_dir)) {
        mkdir($backup_dir, 0755, true);
    }

    // Generate a unique filename for the backup
    $backup_file = 'backup_' . $db_name . '_' . date('Ymd_His') . '.sql';
    $backup_path = $backup_dir . $backup_file;

    // Path to mysqldump (adjust based on your XAMPP installation)
    $mysqldump_path = '"C:\xampp\mysql\bin\mysqldump.exe"';

    // Construct the mysqldump command
    $command = "$mysqldump_path --host=$db_host --user=$db_user --password=$db_pass $db_name > \"$backup_path\" 2>&1";

    // Execute the command and capture output
    exec($command, $output, $return_var);

    if ($return_var === 0 && file_exists($backup_path)) {
        return $backup_file;
    } else {
        error_log("Backup failed: " . implode("\n", $output));
        return false;
    }
}

// Handle manual system backup
if (isset($_POST['backup_system'])) {
    $backup_dir = __DIR__ . '/../backups/';
    $backup_file = performBackup($db_config, $backup_dir);

    if ($backup_file) {
        // Send email with backup file as attachment
        require_once '../vendor/autoload.php'; // Include PHPMailer (adjust path as needed)
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Replace with your SMTP host
            $mail->SMTPAuth = true;
            $mail->Username = 'kwadwomegas@gmail.com'; // Replace with your SMTP username
            $mail->Password = 'Joojo123@@'; // Replace with your SMTP password
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('no-reply@yourdomain.com', 'Building Permit System');
            $mail->addAddress($admin_email);

            // Attachments
            $mail->addAttachment($backup_dir . $backup_file);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Database Backup - ' . date('Y-m-d H:i:s');
            $mail->Body    = 'Dear Admin,<br><br>A backup of the database has been created. Please find the backup file attached.<br><br>Regards,<br>Building Permit System';
            $mail->AltBody = 'Dear Admin,\n\nA backup of the database has been created. Please find the backup file attached.\n\nRegards,\nBuilding Permit System';

            $mail->send();
        } catch (Exception $e) {
            error_log("Failed to send email: {$mail->ErrorInfo}");
        }

        $_SESSION['backup_file'] = $backup_file;
        header("Location: settings.php?msg=System backup created successfully");
        exit();
    } else {
        header("Location: settings.php?error=Failed to create system backup");
        exit();
    }
}

// Handle scheduled backup settings
if (isset($_POST['schedule_backup'])) {
    $schedule = $_POST['schedule'];
    $schedule_file = __DIR__ . '/../backup_schedule.txt';

    // Save the schedule to a file (for simplicity; in production, use a database)
    file_put_contents($schedule_file, $schedule);

    // Note: Actual scheduling is handled by a cron job or scheduled task (see instructions below)
    header("Location: settings.php?msg=Backup schedule updated successfully");
    exit();
}

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    header("Location: settings.php?msg=User deleted successfully");
    exit();
}

// Handle password reset
if (isset($_POST['reset_password'])) {
    $user_id = $_POST['user_id'];
    $new_password = password_hash("default123", PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $new_password, $user_id);
    $stmt->execute();
    header("Location: settings.php?msg=Password reset successfully");
    exit();
}

// Handle backup deletion
if (isset($_POST['delete_backup'])) {
    $backup_file = $_POST['backup_file'];
    $backup_path = __DIR__ . '/../backups/' . $backup_file;

    if (file_exists($backup_path)) {
        unlink($backup_path);
        header("Location: settings.php?msg=Backup deleted successfully");
        exit();
    } else {
        header("Location: settings.php?error=Backup file not found");
        exit();
    }
}

// Handle database restore
if (isset($_POST['restore_database'])) {
    $db_host = $db_config['host'];
    $db_user = $db_config['user'];
    $db_pass = $db_config['pass'];
    $db_name = $db_config['name'];

    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
        $uploaded_file = $_FILES['backup_file']['tmp_name'];
        $file_name = $_FILES['backup_file']['name'];

        if (pathinfo($file_name, PATHINFO_EXTENSION) !== 'sql') {
            header("Location: settings.php?error=Invalid file format. Please upload a .sql file");
            exit();
        }

        $mysql_path = '"C:\xampp\mysql\bin\mysql.exe"';
        $command = "$mysql_path --host=$db_host --user=$db_user --password=$db_pass $db_name < \"$uploaded_file\" 2>&1";
        exec($command, $output, $return_var);

        if ($return_var === 0) {
            header("Location: settings.php?msg=Database restored successfully");
            exit();
        } else {
            error_log("Restore failed: " . implode("\n", $output));
            header("Location: settings.php?error=Failed to restore database");
            exit();
        }
    } else {
        header("Location: settings.php?error=No file uploaded or upload error");
        exit();
    }
}

// Load current backup schedule
$schedule_file = __DIR__ . '/../backup_schedule.txt';
$current_schedule = file_exists($schedule_file) ? file_get_contents($schedule_file) : 'none';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Build_Right</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* General Styling */
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Arial', sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .container {
            margin-top: 30px;
            flex: 1;
        }
        h2, h4 {
            color: #333;
            font-weight: 600;
        }
        h4 {
            margin-bottom: 20px;
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

        /* Card Styling */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 30px;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }
        .card-header {
            background-color: #007bff;
            color: white;
            font-weight: 500;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
        }
        .card-body {
            padding: 20px;
        }

        /* Table Styling */
        .table-container {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .table.table-bordered thead th {
            background-color: #007bff !important;
            color: #fff !important;
            font-weight: 500;
        }
        .table th, .table td {
            vertical-align: middle;
        }

        /* Button Styling */
        .btn {
            border-radius: 5px;
            transition: background-color 0.3s ease, transform 0.1s ease;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #c82333;
        }
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }
        .btn-info {
            background-color: #17a2b8;
            border-color: #17a2b8;
        }
        .btn-info:hover {
            background-color: #138496;
            border-color: #138496;
        }

        /* Form Styling */
        .form-control, .form-select {
            border-radius: 5px;
            border: 1px solid #ced4da;
            transition: border-color 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        /* Alert Styling */
        .alert {
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Footer Styling */
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
            <a class="navbar-brand" href="admin_dashboard.php">Build_Right</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="settings.php">Settings</a></li>
                    <li class="nav-item"><a class="nav-link" href="../logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Admin Settings</h2>
            <!-- Backup Button -->
            <form method="POST" id="backupForm">
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#backupModal">Perform System Backup</button>
            </form>
        </div>

        <!-- Display Success Message -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <?php if (isset($_SESSION['backup_file'])): ?>
                    <p class="mt-2">
                        <a href="../backups/<?php echo htmlspecialchars($_SESSION['backup_file']); ?>" class="btn btn-primary btn-sm" download>Download Backup</a>
                    </p>
                    <?php unset($_SESSION['backup_file']); ?>
                <?php endif; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Display Error Message -->
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Schedule Backup -->
        <div class="card">
            <div class="card-header">Schedule Automatic Backups</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="schedule" class="form-label">Backup Schedule</label>
                        <select name="schedule" id="schedule" class="form-select">
                            <option value="none" <?php echo $current_schedule === 'none' ? 'selected' : ''; ?>>None</option>
                            <option value="daily" <?php echo $current_schedule === 'daily' ? 'selected' : ''; ?>>Daily</option>
                            <option value="weekly" <?php echo $current_schedule === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                        </select>
                    </div>
                    <button type="submit" name="schedule_backup" class="btn btn-primary">Save Schedule</button>
                </form>
            </div>
        </div>

        <!-- List Available Backups -->
        <div class="card">
            <div class="card-header">Available Backups</div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Backup File</th>
                                <th>Size</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $backup_dir = __DIR__ . '/../backups/';
                            $backup_files = glob($backup_dir . '*.sql');
                            if (empty($backup_files)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No backups available</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($backup_files as $file): ?>
                                    <?php
                                    $file_name = basename($file);
                                    $file_size = round(filesize($file) / 1024, 2);
                                    $created_at = date('Y-m-d H:i:s', filemtime($file));
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($file_name); ?></td>
                                        <td><?php echo $file_size; ?> KB</td>
                                        <td><?php echo $created_at; ?></td>
                                        <td>
                                            <a href="../backups/<?php echo htmlspecialchars($file_name); ?>" class="btn btn-primary btn-sm" download>Download</a>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteBackupModal" data-file="<?php echo htmlspecialchars($file_name); ?>">Delete</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Restore Database -->
        <div class="card">
            <div class="card-header">Restore Database</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="restoreForm">
                    <div class="mb-3">
                        <label for="backup_file" class="form-label">Upload Backup File (.sql)</label>
                        <input type="file" name="backup_file" id="backup_file" class="form-control" accept=".sql" required>
                    </div>
                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#restoreModal">Restore Database</button>
                </form>
            </div>
        </div>

        <!-- Manage Users -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>Manage Users</span>
                <a href="../register.php" class="btn btn-success btn-sm">Add New User</a>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result_users->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-danger btn-sm">Delete</button>
                                    </form>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" name="reset_password" class="btn btn-warning btn-sm">Reset Password</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Confirmation Modal -->
    <div class="modal fade" id="backupModal" tabindex="-1" aria-labelledby="backupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="backupModalLabel">Confirm Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to perform a system backup? This may take a few moments.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="backupForm" name="backup_system" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Backup Confirmation Modal -->
    <div class="modal fade" id="deleteBackupModal" tabindex="-1" aria-labelledby="deleteBackupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteBackupModalLabel">Confirm Delete Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this backup?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deleteBackupForm">
                        <input type="hidden" name="backup_file" id="deleteBackupFile">
                        <button type="submit" name="delete_backup" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1" aria-labelledby="restoreModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="restoreModalLabel">Confirm Restore</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to restore the database? This will overwrite the current database and cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="restoreForm" name="restore_database" class="btn btn-warning">Restore</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-auto">
        © <?php echo date("Y"); ?> Development Permit System. Designed by KabTech Consulting. All Rights Reserved.
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass the backup file name to the delete modal
        const deleteBackupModal = document.getElementById('deleteBackupModal');
        deleteBackupModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const file = button.getAttribute('data-file');
            const input = deleteBackupModal.querySelector('#deleteBackupFile');
            input.value = file;
        });
    </script>
</body>
</html>