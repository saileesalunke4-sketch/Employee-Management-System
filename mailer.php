<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendEmail($to_email, $to_name, $subject, $body){
    $mail = new PHPMailer(true);
    try {
        //$mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'saileesalunke4@gmail.com'; // apni Gmail
        $mail->Password   = 'pssjxueocublnqvr';   // 16 digit app password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('saileesalunke4@gmail.com', 'Aller Technologies EMS');
        $mail->addAddress($to_email, $to_name);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>