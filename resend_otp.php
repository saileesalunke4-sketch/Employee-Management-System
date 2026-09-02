<?php
@session_start();
require 'db.php';

if(!isset($_SESSION['otp_pending_user_id'])){
    header("Location: index.php");
    exit();
}
$user_id = (int) $_SESSION['otp_pending_user_id'];

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
if(!$user){
    unset($_SESSION['otp_pending_user_id']);
    header("Location: index.php");
    exit();
}

// RATE LIMIT: block resend requests within OTP_RESEND_COOLDOWN_SECONDS of
// the last OTP sent for this user, to stop repeated-request abuse/spam
// (each resend is a real email/SMS send, which costs money on the SMS side).
$last_otp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT created_at FROM login_otps WHERE user_id=$user_id ORDER BY id DESC LIMIT 1"));
if($last_otp){
    $seconds_since = time() - strtotime($last_otp['created_at']);
    if($seconds_since < OTP_RESEND_COOLDOWN_SECONDS){
        $wait = OTP_RESEND_COOLDOWN_SECONDS - $seconds_since;
        header("Location: verify_otp.php?error=cooldown&wait=$wait");
        exit();
    }
}

generateAndSendOtp($conn, $user);
header("Location: verify_otp.php?resent=1");
exit();
?>
