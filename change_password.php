<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Change Password";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Change Password - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard <?php echo $role==='employee' ? 'emp-theme' : ($role==='admin' ? 'admin-theme' : 'super-theme'); ?>">
<?php
    if($role === 'employee') include 'sidebar_emp.php';
    elseif($role === 'admin') include 'sidebar_admin.php';
    else include 'sidebar_sa.php';
?>
<div class="main-content">
<?php
    if($role === 'employee') include 'topbar_emp.php';
    elseif($role === 'admin') include 'topbar_admin.php';
    else include 'topbar_sa.php';
?>
<div class="app-content">

<div class="section active">
    <div class="form-card" style="max-width:480px;">
        <h3 class="section-title">Change Password</h3>

        <?php if(isset($_GET['ok'])): ?>
            <div style="background:#f0fdf4;border:1px solid #86efac;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-size:13px;">
                Password changed successfully.
            </div>
        <?php endif; ?>

        <form action="save_password_change.php" method="POST">
            <div class="field"><label>Current Password</label><input type="password" name="current_password" required></div>
            <div class="field"><label>New Password</label><input type="password" name="new_password" minlength="8" required></div>
            <div class="field"><label>Confirm New Password</label><input type="password" name="confirm_password" minlength="8" required></div>
            <p style="font-size:11.5px;color:var(--text-3,#9aa1ac);margin-top:-4px;">Minimum 8 characters.</p>
            <button type="submit" class="submit-btn">Update Password</button>
        </form>
    </div>
</div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
