<?php
// BUGFIX: this used to call session_start() bare, before db.php's
// production error-handling config (which hides raw PHP warnings from
// users) even loads. Suppressed with @, matching every other page.
@session_start();
require 'db.php';

// This page only makes sense mid-login — if there's no pending OTP user
// (either they never logged in with a password, or they're already fully
// logged in), send them to the right place instead.
if(isset($_SESSION['user'])){
    $role = $_SESSION['user']['role'];
    header("Location: " . ($role === 'admin' ? 'admin_dashboard.php' : ($role === 'super_admin' ? 'super_admin_dashboard.php' : 'emp_dashboard.php')));
    exit();
}
if(!isset($_SESSION['otp_pending_user_id'])){
    header("Location: index.php");
    exit();
}

$user_id = (int) $_SESSION['otp_pending_user_id'];
$user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT name, email FROM users WHERE id=$user_id"));
if(!$user){
    unset($_SESSION['otp_pending_user_id']);
    header("Location: index.php");
    exit();
}

// Mask the email for display (e.g. sa*****@company.com) — no need to show
// it in full on a page reachable before full login.
$email_parts = explode('@', $user['email']);
$masked_email = (strlen($email_parts[0]) > 2 ? substr($email_parts[0], 0, 2) : $email_parts[0]) . str_repeat('*', max(3, strlen($email_parts[0]) - 2)) . '@' . ($email_parts[1] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Verify Login - Aller EMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css">
    <style>
        html,body{ height:100%; }
        body {
            min-height: 100vh;
            background:
                radial-gradient(560px circle at 8% 8%, rgba(79,70,229,.07), transparent 60%),
                radial-gradient(520px circle at 94% 92%, rgba(13,148,136,.07), transparent 60%),
                var(--canvas, #F7F8FA);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .otp-card {
            width: 420px;
            max-width: 100%;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 30px 70px -20px rgba(16,24,40,0.18), 0 2px 8px rgba(16,24,40,0.04);
            border: 1px solid #EEF0F3;
            padding: 40px 36px;
        }
        .otp-icon { width:56px;height:56px;border-radius:16px;background:var(--brand-soft);color:var(--brand);display:flex;align-items:center;justify-content:center;margin-bottom:18px; }
        .otp-card h1 { font-size:22px;font-weight:800;color:var(--text-1);margin-bottom:6px; }
        .otp-card p.sub { font-size:13.5px;color:var(--text-2);margin-bottom:26px;line-height:1.5; }
        .otp-inputs { display:flex; gap:10px; margin-bottom:8px; }
        .otp-inputs input {
            width: 100%; aspect-ratio: 1; text-align:center; font-size:22px; font-weight:700;
            border:1.5px solid var(--border); border-radius:12px; color:var(--text-1);
            outline:none; transition:all 0.2s ease;
        }
        .otp-inputs input:focus { border-color:var(--brand); box-shadow:0 0 0 4px var(--brand-soft); }
        .otp-error { background:var(--danger-soft); color:var(--danger); padding:10px 14px; border-radius:10px; font-size:13px; margin-bottom:16px; }
        .otp-submit { width:100%; padding:13px; background:var(--brand); color:#fff; border:none; border-radius:12px; font-size:14.5px; font-weight:700; cursor:pointer; margin-top:16px; }
        .otp-submit:hover { background:var(--brand-dark); }
        .otp-resend { text-align:center; margin-top:18px; font-size:13px; color:var(--text-2); }
        .otp-resend a { color:var(--brand); font-weight:600; text-decoration:none; }
        .otp-back { text-align:center; margin-top:10px; }
        .otp-back a { color:var(--text-3); font-size:12.5px; text-decoration:none; }
    </style>
</head>
<body>
    <div class="otp-card">
        <div class="otp-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M7.5 10.5V7.2a4.5 4.5 0 0 1 9 0v3.3"/></svg>
        </div>
        <h1>Verify your login</h1>
        <p class="sub">We've sent a 6-digit code to <b><?php echo htmlspecialchars($masked_email); ?></b> (and by SMS, if a phone number is on file). Enter it below to continue.</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="otp-error">
                <?php
                    $err = $_GET['error'];
                    if($err === 'invalid') echo "Incorrect code. Please try again.";
                    elseif($err === 'expired') echo "This code has expired. Please request a new one.";
                    elseif($err === 'too_many') echo "Too many incorrect attempts. Please request a new code.";
                    elseif($err === 'cooldown') echo "Please wait " . (int)($_GET['wait'] ?? 60) . " more second(s) before requesting another code.";
                    else echo "Something went wrong. Please try again.";
                ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET['resent'])): ?>
            <div class="otp-error" style="background:var(--success-soft);color:var(--success);">A new code has been sent.</div>
        <?php endif; ?>

        <form action="verify_otp_submit.php" method="POST" id="otpForm">
            <div class="otp-inputs">
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" required>
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" required>
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" required>
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" required>
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" required>
                <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" required>
            </div>
            <input type="hidden" name="otp_code" id="otpCombined">
            <button type="submit" class="otp-submit">Verify &amp; Continue</button>
        </form>

        <div class="otp-resend">
            Didn't get the code? <a href="resend_otp.php">Resend</a>
        </div>
        <div class="otp-back">
            <a href="logout.php">&larr; Back to login</a>
        </div>
    </div>

    <script>
    // Auto-advance between the 6 boxes and combine into one hidden field.
    var digits = document.querySelectorAll('.otp-digit');
    digits[0].focus();
    digits.forEach(function(el, i){
        el.addEventListener('input', function(){
            this.value = this.value.replace(/[^0-9]/g, '');
            if(this.value && i < digits.length - 1) digits[i+1].focus();
        });
        el.addEventListener('keydown', function(e){
            if(e.key === 'Backspace' && !this.value && i > 0) digits[i-1].focus();
        });
    });
    document.getElementById('otpForm').addEventListener('submit', function(){
        var combined = '';
        digits.forEach(function(el){ combined += el.value; });
        document.getElementById('otpCombined').value = combined;
    });
    </script>
</body>
</html>
