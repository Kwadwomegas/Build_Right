<?php
include '../config/db.php';

if (isset($_GET['search'])) {
    $search = $conn->real_escape_string($_GET['search']);

    $sql = "SELECT * FROM applications 
            WHERE applicant_name LIKE '%$search%' 
               OR applicant_mobile LIKE '%$search%' 
            ORDER BY id DESC";

    $result = $conn->query($sql);

    while ($row = $result->fetch_assoc()):
?>
<tr>
    <td><?php echo $row['id']; ?></td>
    <td><?php echo htmlspecialchars($row['applicant_name']); ?></td>
    <td><?php echo htmlspecialchars($row['applicant_mobile']); ?></td>
    <td><?php echo htmlspecialchars($row['permit_type']); ?></td>
    <td><?php echo htmlspecialchars($row['submission_date']); ?></td>
    <td><?php echo ucfirst($row['status']); ?></td>
    <td><?php echo htmlspecialchars($row['admin_comment']); ?></td>
    <td><?php echo !empty($row['document_path']) ? '<a href="'.$row['document_path'].'" class="btn btn-primary" download>Download</a>' : 'No Documents'; ?></td>
</tr>
<?php
    endwhile;
}
?>
