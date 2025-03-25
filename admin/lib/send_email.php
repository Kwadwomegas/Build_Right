<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once '../vendor/autoload.php'; // If using Composer
// If installed manually, uncomment the following lines:
// require_once '../lib/PHPMailer/src/Exception.php';
// require_once '../lib/PHPMailer/src/PHPMailer.php';
// require_once '../lib/PHPMailer/src/SMTP.php';

function sendEmail($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kabslink@gmail.com'; // Replace with your SMTP username
        $mail->Password   = 'Zayne123@@'; // Replace with your SMTP password or app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient
        $mail->setFrom('kabslink@gmail.com', 'Build Right System');
        $mail->addAddress($to);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body); // Plain text version for non-HTML email clients

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Log the error (you can replace this with your preferred logging mechanism)
        error_log("Email sending failed: {$mail->ErrorInfo}");
        return false;
    }
}
?>