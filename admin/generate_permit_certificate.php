<?php
session_start();
include '../config/db.php';

// Ensure only logged-in admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Include the FPDF library
require '../lib/fpdf.php';

// Check if application_id is provided
if (!isset($_GET['application_id']) || empty(trim($_GET['application_id']))) {
    $_SESSION['error_msg'] = "Application ID not provided.";
    header("Location: admin_dashboard.php?error=1");
    exit();
}

$application_id = trim($_GET['application_id']);

// Validate application_id format (e.g., X/BAT/25/XXXX)
if (!preg_match('/^X\/BAT\/25\/\d{4}$/', $application_id)) {
    $_SESSION['error_msg'] = "Invalid application ID format.";
    header("Location: admin_dashboard.php?error=1");
    exit();
}

// Fetch permit details from multiple tables
$sql = "SELECT 
            pa.applicant_name, 
            pa.permit_type, 
            pa.created_at, 
            ad.construction_location, 
            p.permit_number, 
            p.issue_date, 
            p.ppo_name
        FROM permit_applications pa
        LEFT JOIN application_details ad ON pa.application_id = ad.application_id
        LEFT JOIN permits p ON pa.application_id = p.application_id
        WHERE pa.application_id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    $_SESSION['error_msg'] = "Database error: Failed to prepare query.";
    header("Location: admin_dashboard.php?error=1");
    exit();
}

$stmt->bind_param("s", $application_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_msg'] = "Data not found for Application ID: " . htmlspecialchars($application_id);
    header("Location: admin_dashboard.php?error=1");
    exit();
}

$data = $result->fetch_assoc();
$stmt->close();

// Extract data
$ownername = $data['applicant_name'] ?? 'Unknown';
$permit_type = $data['permit_type'] ?? 'Unknown';
$construction_location = $data['construction_location'] ?? 'Unknown';
$permit_number = $data['permit_number'] ?? 'N/A';
$issue_date = $data['issue_date'] ? date('jS F Y', strtotime($data['issue_date'])) : date('jS F Y', strtotime($data['created_at'] ?? 'now'));
$ppo_name = $data['ppo_name'] ? strtoupper(htmlspecialchars($data['ppo_name'])) : 'AMETAME EMMANUEL DOE';

// Create a new PDF document
class PDF extends FPDF {
    // Page header
    function Header() {
        // Log the current working directory for debugging
        error_log("Current working directory: " . getcwd());

        $logo_width = 30; // Width of each logo in mm
        $logo_height = 30; // Height of each logo in mm

        // Define possible paths for the images (using .jpg)
        $north_tongu_logo_paths = ['images/north_tongu_logo.jpg', '../images/north_tongu_logo.jpg'];
        $land_use_logo_paths = ['images/land_use_logo.jpg', '../images/land_use_logo.jpg'];

        // Log the absolute paths being checked
        foreach ($north_tongu_logo_paths as $path) {
            $absolute_path = realpath($path) ?: "Not resolved: $path";
            error_log("Checking North Tongu logo path: $path (absolute: $absolute_path)");
        }
        foreach ($land_use_logo_paths as $path) {
            $absolute_path = realpath($path) ?: "Not resolved: $path";
            error_log("Checking Land Use logo path: $path (absolute: $absolute_path)");
        }

        // North Tongu logo
        $north_tongu_logo_found = false;
        foreach ($north_tongu_logo_paths as $path) {
            if (file_exists($path)) {
                $this->Image($path, 10, 10, $logo_width, $logo_height);
                $north_tongu_logo_found = true;
                break;
            }
        }
        if (!$north_tongu_logo_found) {
            // Log an error and display a placeholder
            error_log("North Tongu logo not found in any path: " . implode(", ", $north_tongu_logo_paths));
            $this->SetFont('Arial', '', 10);
            $this->SetXY(10, 10);
            $this->Cell($logo_width, $logo_height, 'North Tongu Logo Missing', 1, 0, 'C');
        }

        // Land Use logo
        $land_use_logo_found = false;
        foreach ($land_use_logo_paths as $path) {
            if (file_exists($path)) {
                $this->Image($path, 210 - $logo_width - 10, 10, $logo_width, $logo_height);
                $land_use_logo_found = true;
                break;
            }
        }
        if (!$land_use_logo_found) {
            // Log an error and display a placeholder
            error_log("Land Use logo not found in any path: " . implode(", ", $land_use_logo_paths));
            $this->SetFont('Arial', '', 10);
            $this->SetXY(210 - $logo_width - 10, 10);
            $this->Cell($logo_width, $logo_height, 'Land Use Logo Missing', 1, 0, 'C');
        }

        // Move down after the logos (logo height + some spacing)
        $this->SetY($logo_height + 15);

        // Add the header text
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'LAND USE AND SPATIAL PLANNING REGULATIONS, 2019 (L.I 12384)', 0, 1, 'C');
        $this->Ln(5);

        // Add a title
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(0, 10, 'PLANNING PERMIT CERTIFICATE', 0, 1, 'C');
        $this->Ln(10);
    }

    // Page footer
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, 'Page ' . $this->PageNo(), 0, 0, 'C');
    }
}

// Create a new PDF instance
$pdf = new PDF();
$pdf->AddPage();

// Set font for the details
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Application Number: ' . htmlspecialchars($application_id), 0, 1, 'L');
$pdf->Cell(0, 10, 'Permit Number: ' . htmlspecialchars($permit_number), 0, 1, 'L');
$pdf->Ln(5);

// Main body text
$body_text = "This planning permit certifies that " . strtoupper(htmlspecialchars($ownername)) . 
             " having their land at " . strtoupper(htmlspecialchars($construction_location)) . 
             ", is approved from the North Tongu District Assembly to construct a building for " . 
             htmlspecialchars($permit_type) . " purpose only, subject to the attached conditions and in accordance with the attached plan.";
$pdf->MultiCell(0, 10, $body_text, 0, 'J');
$pdf->Ln(5);

// Date line
$additional_text = "DATED AT THE OFFICE OF THE NORTH TONGU DISTRICT ASSEMBLY THIS " . $issue_date;
$pdf->MultiCell(0, 10, $additional_text, 0, 'J');
$pdf->Ln(5);

// Add the former footer content here, aligned to the left
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 10, str_repeat('.', 50), 0, 1, 'L'); // Separator line
$pdf->Ln(2);
$pdf->Cell(0, 10, $issue_date, 0, 1, 'L');
$pdf->Ln(2);
$pdf->Cell(0, 10, $ppo_name, 0, 1, 'L');
$pdf->Ln(2);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 10, 'PHYSICAL PLANNING OFFICER, N.T.D.A', 0, 1, 'L');

// Sanitize the application_id for the filename
$sanitized_application_id = str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $application_id);

// Save the PDF to a temporary file
$filename = "Planning_Permit_Certificate_" . $sanitized_application_id . ".pdf";
$file_path = __DIR__ . '/temp/' . $filename;

// Create the temp directory if it doesn't exist
$temp_dir = __DIR__ . '/temp/';
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0755, true);
}

// Save the PDF file
$pdf->Output('F', $file_path);

// Update permit_issued in permit_applications (optional, if needed)
$update_stmt = $conn->prepare("UPDATE permit_applications SET permit_issued = 1 WHERE application_id = ?");
$update_stmt->bind_param("s", $application_id);
$update_stmt->execute();
$update_stmt->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Permit Certificate Generated</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .container { max-width: 600px; margin-top: 50px; }
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

    <!-- Success Message -->
    <div class="container">
        <div class="alert alert-success text-center" role="alert">
            <h4 class="alert-heading">Permit Certificate Generated Successfully!</h4>
            <p>The planning permit certificate for Application ID: <strong><?php echo htmlspecialchars($application_id); ?></strong> has been generated.</p>
            <hr>
            <p class="mb-0">
                <a href="temp/<?php echo htmlspecialchars($filename); ?>" class="btn btn-primary" download>Download Certificate</a>
                <a href="admin_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>