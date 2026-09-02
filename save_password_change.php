<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$user_id = (int) $_SESSION['user']['id'];
$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if(strlen($new_password) < 8){
    echo "<script>alert('New password must be at least 8 characters.'); window.history.back();</script>";
    exit();
}
if($new_password !== $confirm_password){
    echo "<script>alert('New password and confirmation do not match.'); window.history.back();</script>";
    exit();
}

$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT password FROM users WHERE id=$user_id"));
if(!$user || !password_verify($current_password, $user['password'])){
    echo "<script>alert('Current password is incorrect.'); window.history.back();</script>";
    exit();
}

$new_hash = password_hash($new_password, PASSWORD_DEFAULT);
// BUGFIX (Employee-024): clear the forced-change flag on success so the
// user isn't sent back here again on their next login.
mysqli_query($conn, "UPDATE users SET password='$new_hash', must_change_password=0 WHERE id=$user_id");

// Security notification email: lets the account owner know their password
// was changed, so they can act immediately (e.g. contact Admin) if they
// didn't do it themselves.
$user_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, email FROM users WHERE id=$user_id"));
if($user_info){
    $subject = "Your EMS password was changed";
    $body = "Hi {$user_info['name']},<br><br>This is a confirmation that your Employee Management System password was changed on " . date('d M Y, h:i A') . ".<br><br>If you did not make this change, please contact your Admin/Super Admin immediately.<br><br>— EMS Security Notice";
    sendEMSMail($user_info['email'], $user_info['name'], $subject, $body);
}

// Auto-logout after a password change: destroy the session immediately so
// the user (and anyone else on a shared device) must log back in with the
// new password rather than continuing to browse on the old session.
session_destroy();
header("Location: index.php?pwchanged=1");
exit();
?>
