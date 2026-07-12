<?php
session_start();
require 'db.php';

$error   = '';
$success = '';

if(isset($_POST['check_email'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    $user = mysqli_fetch_assoc($result);

    // SECURITY: always show the same generic message whether or not the
    // email exists — this prevents an attacker from using this form to
    // discover which email addresses are registered in the system.
    $success = "If an account exists with that email, a password reset link has been sent to it. Please check your inbox.";

    if($user){
        // Generate a secure, single-use, time-limited reset token
        $token      = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 minutes'));

        mysqli_query($conn, "INSERT INTO password_resets (user_id, token, expires_at) VALUES ({$user['id']}, '$token', '$expires_at')");

        $reset_link = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/reset_password.php?token=' . $token;

        $subject = "Password Reset Request — EMS Aller Technologies";
        $body = "
        <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
            <div style='background:#1a3a6e;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
                <h2 style='color:white;margin:0;'>Aller Technologies — EMS</h2>
            </div>
            <div style='background:#f9fafb;padding:24px;border-radius:0 0 10px 10px;border:1px solid #e5e7eb;'>
                <p>Dear <strong>{$user['name']}</strong>,</p>
                <p>We received a request to reset your EMS password. Click the button below to set a new password. This link is valid for <strong>30 minutes</strong> and can only be used once.</p>
                <p style='text-align:center;margin:24px 0;'>
                    <a href='{$reset_link}' style='background:#2563eb;color:white;padding:12px 28px;border-radius:8px;text-decoration:none;font-weight:600;'>Reset My Password</a>
                </p>
                <p style='color:#6b7280;font-size:12px;'>If you didn't request this, you can safely ignore this email — your password will not be changed.</p>
                <p style='color:#6b7280;font-size:12px;'>This is an auto-generated email from EMS — Aller Technologies.</p>
            </div>
        </div>";

        sendEMSMail($email, $user['name'], $subject, $body);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - EMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #60a5fa;
            text-decoration: none;
            font-size: 13px;
        }
        .back-link:hover { text-decoration: underline; }

        .success {
            background: rgba(34,197,94,0.1);
            border: 0.5px solid rgba(34,197,94,0.3);
            color: #16a34a;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
        }
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
            <h2>Forgot Password</h2>
            <p class="sub">Reset your account password</p>

            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php else: ?>
                <form method="POST">
                    <div class="field">
                        <label>Enter Your Registered Email</label>
                        <input type="email" name="email" placeholder="Enter your email" required>
                    </div>
                    <button type="submit" name="check_email">Send Reset Link</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
