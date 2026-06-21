<?php
date_default_timezone_set('Asia/Kolkata');

$host     = "localhost";
$user     = "root";
$password = "root";
$database = "emp1_db";

$conn = mysqli_connect($host, $user, $password, $database);

if(!$conn){
    $conn = mysqli_connect($host, $user, "root", $database);
}

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Extend session lifetime safely
if (session_status() === PHP_SESSION_ACTIVE) {
    setcookie(session_name(), session_id(), time() + 1800, '/');
}

// ===== PHPMAILER CONFIG =====
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

function sendEMSMail($to_email, $to_name, $subject, $body){
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saileesalunke4@gmail.com';
        $mail->Password   = 'kvjy bqrl nvyo oilq';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->setFrom('saileesalunke4@gmail.com', 'EMS - Aller Technologies');
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (\Exception $e) {
        return false;
    }
}
?>
