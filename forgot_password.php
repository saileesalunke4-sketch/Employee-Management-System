<?php
session_start();
require 'db.php';

$error   = '';
$success = '';
$email_found = false;
$email_val   = '';

if(isset($_POST['check_email'])){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $result = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    
    if(mysqli_num_rows($result) > 0){
        $email_found = true;
        $email_val   = $email;
    } else {
        $error = "No account found with this email!";
    }
}

if(isset($_POST['reset_password'])){
    $email    = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    if(empty($password) || empty($confirm)){
        $error = "Password fields cannot be blank!";
        $email_found = true;
        $email_val   = $email;
    } elseif($password !== $confirm){
        $error = "Passwords do not match!";
        $email_found = true;
        $email_val   = $email;
    } elseif(!preg_match('/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/', $password)){
        $error = "Password must have uppercase letter, number & special character (e.g. Pass@123)";
        $email_found = true;
        $email_val   = $email;
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE email='$email'");
        $success = "Password updated successfully! You can now login.";

        // Email bhejo
        require 'mailer.php';
        $subject = "Password Reset Successful — Aller Technologies EMS";
        $body = "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;'>
            <div style='background:#1a3a6e;padding:20px;text-align:center;'>
                <h2 style='color:white;margin:0;'>Aller Technologies EMS</h2>
            </div>
            <div style='padding:24px;'>
                <p style='font-size:15px;'>Hello,</p>
                <p style='font-size:14px;color:#444;'>Your EMS account password has been <b style='color:green;'>✅ Reset Successfully</b>.</p>
                <p style='font-size:13px;color:#666;'>If you did not request this change, please contact HR immediately.</p>
                <p style='font-size:13px;color:#666;margin-top:20px;'>Regards,<br><b>HR Team — Aller Technologies</b></p>
            </div>
        </div>";

        sendEmail($email, 'User', $subject, $body);
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
                <div class="success"><?php echo $success; ?>
                    <br><a href="index.php" style="color:#16a34a;">Click here to Login</a>
                </div>
            <?php endif; ?>

            <?php if(!$success): ?>

                <?php if(!$email_found): ?>
                <form method="POST">
                    <div class="field">
                        <label>Enter Your Registered Email</label>
                        <input type="email" name="email"
                        placeholder="Enter your email" required>
                    </div>
                    <button type="submit" name="check_email">Find Account</button>
                </form>

                <?php else: ?>
                <form method="POST">
                    <input type="hidden" name="email" value="<?php echo $email_val; ?>">
                    <div class="field">
                        <label>New Password</label>
                        <input type="password" name="new_password"
                        placeholder="e.g. Pass@123" required>
                    </div>
                    <div class="field">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password"
                        placeholder="Re-enter new password" required>
                    </div>
                    <small style="color:#9ca3af; font-size:11px; display:block; margin-bottom:16px;">
                        Password must have uppercase letter, number & special character
                    </small>
                    <button type="submit" name="reset_password">Update Password</button>
                </form>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>
</body>
</html>