<?php
// BUGFIX: this used to call session_start() bare, before db.php's
// production error-handling config (which hides raw PHP warnings from
// users) even loads. On a server where the configured session save
// path isn't writable, this dumped a raw "session_start(): open(...)
// Permission denied" warning straight onto the page for every user to
// see. Suppressed here with @, matching the same session_start() call
// in db.php, which already does this for the same reason.
@session_start();
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
        // SECURITY: block login for a deactivated employee account (see
        // delete_employee.php) — password_verify would otherwise still
        // pass with their old password until it happens to have been
        // randomized, so this check is the actual, reliable gate.
        if($user['role'] === 'employee'){
            $emp_status = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM employees WHERE user_id='{$user['id']}'"));
            if($emp_status && $emp_status['status'] === 'inactive'){
                header("Location: index.php?error=1");
                exit();
            }
        }

        // Successful login — reset any failed-attempt tracking
        mysqli_query($conn, "UPDATE users SET failed_login_attempts=0, lockout_until=NULL WHERE id='{$user['id']}'");

        // SECURITY: regenerate the session ID as soon as the password is
        // confirmed correct (prevents session fixation), even though full
        // login isn't granted yet — that only happens after OTP succeeds.
        session_regenerate_id(true);

        // ===== LOGIN OTP / 2-STEP VERIFICATION =====
        // Password is correct, but $_SESSION['user'] is NOT set yet — the
        // account only becomes fully logged in once the OTP is verified
        // (see verify_otp_submit.php). Only a minimal "pending" marker is
        // stored here, not the full user row.
        $_SESSION['otp_pending_user_id'] = (int) $user['id'];
        generateAndSendOtp($conn, $user);
        header("Location: verify_otp.php");
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
