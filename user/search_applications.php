<?php
include '../config/db.php';
$search = isset($_POST['search']) ? trim($_POST['search']) : '';
$query = "SELECT application_id, applicant_name, applicant_mobile, permit_type, status, created_at 
          FROM permit_applications 
          WHERE application_id LIKE ? 
          ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$search_param = "%$search%";
$stmt->bind_param("s", $search_param);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td class='application-id'>" . htmlspecialchars($row['application_id']) . "</td>";
    echo "<td>" . htmlspecialchars($row['applicant_name']) . "</td>";
    echo "<td>" . htmlspecialchars($row['applicant_mobile']) . "</td>";
    echo "<td>" . htmlspecialchars($row['permit_type']) . "</td>";
    echo "<td><span class='status-" . strtolower($row['status']) . "'>" . htmlspecialchars(ucfirst($row['status'])) . "</span></td>";
    echo "<td>" . htmlspecialchars(date('Y-m-d', strtotime($row['created_at']))) . "</td>";
    echo "</tr>";
  }
} else {
  echo "<tr><td colspan='6' class='text-center'>No applications found.</td></tr>";
}
?>