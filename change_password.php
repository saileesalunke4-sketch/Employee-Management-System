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
        <?php if(isset($_GET['first'])): ?>
            <div style="background:#fffbeb;border:1px solid #fcd34d;color:#92400e;padding:12px 16px;border-radius:10px;margin-bottom:14px;font-size:13px;">
                Welcome! For security, please set your own password before continuing. Use the temporary password you were given as your Current Password.
            </div>
        <?php endif; ?>

        <form action="save_password_change.php" method="POST">
            <div class="field">
                <label>Current Password</label>
                <div style="position:relative;">
                    <input type="password" name="current_password" id="curPwField" required style="padding-right:42px;">
                    <span onclick="togglePwField('curPwField','curPwEye')" title="Show/hide password" role="button" tabindex="0" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#9aa1ac;display:flex;">
                        <svg id="curPwEye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                </div>
            </div>
            <div class="field">
                <label>New Password</label>
                <div style="position:relative;">
                    <input type="password" name="new_password" id="newPwField" minlength="8" required style="padding-right:42px;">
                    <span onclick="togglePwField('newPwField','newPwEye')" title="Show/hide password" role="button" tabindex="0" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#9aa1ac;display:flex;">
                        <svg id="newPwEye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                </div>
            </div>
            <div class="field">
                <label>Confirm New Password</label>
                <div style="position:relative;">
                    <input type="password" name="confirm_password" id="confPwField" minlength="8" required style="padding-right:42px;">
                    <span onclick="togglePwField('confPwField','confPwEye')" title="Show/hide password" role="button" tabindex="0" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#9aa1ac;display:flex;">
                        <svg id="confPwEye" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    </span>
                </div>
            </div>
            <p style="font-size:11.5px;color:var(--text-3,#9aa1ac);margin-top:-4px;">Minimum 8 characters.</p>
            <button type="submit" class="submit-btn">Update Password</button>
        </form>
        <script>
        // Show/hide password toggle, reused across all 3 fields on this page.
        function togglePwField(inputId, iconId){
            var field = document.getElementById(inputId);
            var icon  = document.getElementById(iconId);
            if(field.type === 'password'){
                field.type = 'text';
                icon.innerHTML = '<path d="M3 3l18 18"/><path d="M10.6 5.1A9.9 9.9 0 0 1 12 5c6.4 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.5 6.6C3.7 8.4 2 12 2 12s3.6 7 10 7a10 10 0 0 0 3.4-.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>';
            } else {
                field.type = 'password';
                icon.innerHTML = '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
            }
        }
        </script>
    </div>
</div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
