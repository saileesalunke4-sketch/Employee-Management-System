<?php
session_start();
require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $user   = mysqli_fetch_assoc($result);

    // ===== BRUTE-FORCE LOCKOUT CHECK =====
    // If this account is currently locked out, reject immediately —
    // don't even check the password, and don't say whether the account
    // exists (keeps the error message generic either way).
    if($user && $user['lockout_until'] && strtotime($user['lockout_until']) > time()){
        $minutes_left = ceil((strtotime($user['lockout_until']) - time()) / 60);
        header("Location: index.php?error=locked&minutes=$minutes_left");
        exit();
    }

    if($user && password_verify($password, $user['password'])){
        // Successful login — reset any failed-attempt tracking
        mysqli_query($conn, "UPDATE users SET failed_login_attempts=0, lockout_until=NULL WHERE id='{$user['id']}'");

        // SECURITY: regenerate the session ID on login (prevents session
        // fixation — an attacker who set a session ID before login can no
        // longer reuse it afterwards).
        session_regenerate_id(true);

        $_SESSION['user'] = $user;

        if($user['role'] == 'admin'){
            header("Location: admin_dashboard.php");
        } elseif($user['role'] == 'super_admin'){
            header("Location: super_admin_dashboard.php");
        } else {
            header("Location: emp_dashboard.php");
        }
        exit();
    } else {
        // Wrong password (or no such user) — track failed attempts only
        // when the account genuinely exists, and lock it after 5 in a row.
        if($user){
            $attempts = (int)$user['failed_login_attempts'] + 1;
            if($attempts >= 5){
                mysqli_query($conn, "UPDATE users SET failed_login_attempts=$attempts, lockout_until=DATE_ADD(NOW(), INTERVAL 15 MINUTE) WHERE id='{$user['id']}'");
                header("Location: index.php?error=locked&minutes=15");
                exit();
            } else {
                mysqli_query($conn, "UPDATE users SET failed_login_attempts=$attempts WHERE id='{$user['id']}'");
            }
        }
        header("Location: index.php?error=1");
        exit();
    }
}
?>
