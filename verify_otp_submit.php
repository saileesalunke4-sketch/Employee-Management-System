<?php
@session_start();
require 'db.php';

if(!isset($_SESSION['otp_pending_user_id'])){
    header("Location: index.php");
    exit();
}
$user_id = (int) $_SESSION['otp_pending_user_id'];
$entered_code = trim($_POST['otp_code'] ?? '');

if($_SERVER['REQUEST_METHOD'] !== 'POST' || !preg_match('/^[0-9]{6}$/', $entered_code)){
    header("Location: verify_otp.php?error=invalid");
    exit();
}

// Most recent OTP row for this user, whether or not it's already been
// verified/expired — we check those conditions explicitly below so the
// error message can be specific (expired vs wrong vs too many attempts).
$otp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM login_otps WHERE user_id=$user_id ORDER BY id DESC LIMIT 1"));

if(!$otp_row){
    header("Location: verify_otp.php?error=invalid");
    exit();
}

if((int) $otp_row['attempts'] >= OTP_MAX_ATTEMPTS){
    header("Location: verify_otp.php?error=too_many");
    exit();
}

if(strtotime($otp_row['expires_at']) < time()){
    header("Location: verify_otp.php?error=expired");
    exit();
}

if(!hash_equals($otp_row['otp_code'], $entered_code)){
    mysqli_query($conn, "UPDATE login_otps SET attempts=attempts+1 WHERE id={$otp_row['id']}");
    header("Location: verify_otp.php?error=invalid");
    exit();
}

// ===== OTP CORRECT — complete the login =====
mysqli_query($conn, "UPDATE login_otps SET verified=1 WHERE id={$otp_row['id']}");

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$user_id"));
if(!$user){
    unset($_SESSION['otp_pending_user_id']);
    header("Location: index.php");
    exit();
}

unset($_SESSION['otp_pending_user_id']);

// SECURITY: regenerate the session ID again now that full login is
// actually being granted (on top of the regeneration already done right
// after the password check in login.php).
session_regenerate_id(true);

$_SESSION['user'] = $user;

// BUGFIX (Employee-024): new employees log in with the temporary
// password an admin set for them in Add Employee — there was no
// prompt to set their own. Route them to Change Password first;
// existing accounts (must_change_password=0 by default) are
// completely unaffected.
if(!empty($user['must_change_password'])){
    header("Location: change_password.php?first=1");
    exit();
}

if($user['role'] == 'admin'){
    header("Location: admin_dashboard.php");
} elseif($user['role'] == 'super_admin'){
    header("Location: super_admin_dashboard.php");
} else {
    header("Location: emp_dashboard.php");
}
exit();
?>
