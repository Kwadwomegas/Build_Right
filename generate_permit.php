<?php
require('fpdf/fpdf.php');
session_start();
include 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access");
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT applications.*, users.full_name, users.email FROM applications 
        JOIN users ON applications.user_id = users.id 
        WHERE applications.user_id = ? AND applications.status = 'approved' LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$application = $result->fetch_assoc();

if (!$application) {
    die("No approved permit found.");
}

$pdf = new FPDF();
$pdf->AddPage();

// Add Logo
$pdf->Image('logo.png', 10, 10, 30);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(190, 10, 'Building Permit Certificate', 0, 1, 'C');
$pdf->Ln(10);

// Add User Info
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'Issued To: ');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, $application['full_name'], 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'Email: ');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, $application['email'], 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'Permit Type: ');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, $application['permit_type'], 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'Status: ');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, 'Approved', 0, 1);

$pdf->SetFont('Arial', '', 12);
$pdf->Cell(40, 10, 'Issued Date: ');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(100, 10, date('Y-m-d'), 0, 1);

$pdf->Ln(20);

// Signature Section
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 10, 'Authorized Signature:', 0, 1, 'L');
$pdf->Ln(10);
$pdf->Cell(190, 10, '_________________________', 0, 1, 'L');

$pdf->Output('D', 'Building_Permit.pdf');
?>
