<?php
session_start();
include '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Query user from the database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    $hashed_password = '$2y$10$VXBBs9q7Qn10zK/7N4j98.j1H9K5zIQhmkKbNja3IG2rNfBs/HE3.';
$input_password = "Megas";

if (password_verify($input_password, $hashed_password)) {
    echo "Password is correct!";
} else {
    echo "Invalid password!";
} {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') {
            header("Location: ../admin/admin_dashboard.php");
        } else {
            header("Location: ../user/user_dashboard.php");
        }
        exit();
    } //else {
        //$_SESSION['error'] = "Invalid email or password";
       // header("Location: ../login.php");
        //exit();
    }
//}
?>
