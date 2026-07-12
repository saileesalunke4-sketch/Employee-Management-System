<?php
session_start();
require 'db.php';

$error   = '';
$success = '';
$token   = $_GET['token'] ?? $_POST['token'] ?? '';
$token   = mysqli_real_escape_string($conn, $token);

// Validate the token: must exist, be unused, and not expired
$reset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, u.name, u.email FROM password_resets r JOIN users u ON r.user_id=u.id WHERE r.token='$token' AND r.used=0 AND r.expires_at > NOW()"));

if(!$reset){
    $error = "This password reset link is invalid or has expired. Please request a new one.";
}

if($reset && isset($_POST['reset_password'])){
    $password = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if(empty($password) || empty($confirm)){
        $error = "Password fields cannot be blank!";
    } elseif($password !== $confirm){
        $error = "Passwords do not match!";
    } elseif(!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/', $password)){
        $error = "Password must have uppercase letter, number & special character (e.g. Pass@123)";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id={$reset['user_id']}");
        mysqli_query($conn, "UPDATE password_resets SET used=1 WHERE reset_id={$reset['reset_id']}");
        $success = "Password updated successfully! You can now login.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - EMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .back-link { display:inline-block;margin-bottom:20px;color:#60a5fa;text-decoration:none;font-size:13px; }
        .back-link:hover { text-decoration:underline; }
        .success { background:rgba(34,197,94,0.1);border:0.5px solid rgba(34,197,94,0.3);color:#16a34a;padding:10px 16px;border-radius:8px;font-size:13px;margin-bottom:20px; }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-left">
            <div class="brand">
                <h1>EMS</h1>
                <p>Employee Management System</p>
            </div>
            <div class="features">
                <div class="feat">Attendance & Leave Tracking</div>
                <div class="feat">Salary & Payslip Management</div>
                <div class="feat">Performance & Task Reports</div>
            </div>
        </div>
        <div class="login-right">
            <a href="index.php" class="back-link">← Back to Login</a>
            <h2>Reset Password</h2>
            <p class="sub"><?php echo $reset ? 'Dear ' . htmlspecialchars($reset['name']) . ', choose a new password.' : 'Reset your account password'; ?></p>

            <?php if($error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success"><?php echo $success; ?>
                    <br><a href="index.php" style="color:#16a34a;">Click here to Login</a>
                </div>
            <?php elseif($reset): ?>
                <form method="POST">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="field">
                        <label>New Password</label>
                        <input type="password" name="new_password" placeholder="e.g. Pass@123" required>
                    </div>
                    <div class="field">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Re-enter new password" required>
                    </div>
                    <small style="color:#9ca3af;font-size:11px;display:block;margin-bottom:16px;">
                        Password must have uppercase letter, number & special character
                    </small>
                    <button type="submit" name="reset_password">Update Password</button>
                </form>
            <?php else: ?>
                <a href="forgot_password.php" style="color:#3b82f6;font-size:13px;">Request a new reset link &rarr;</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
