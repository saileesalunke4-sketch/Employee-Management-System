<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$attendance_id = (int) $_GET['id'];

// Get current attendance record
$att_res = mysqli_query($conn, "SELECT a.*, e.first_name, e.last_name FROM attendance a JOIN employees e ON a.emp_id = e.emp_id WHERE a.attendance_id='$attendance_id'");
$att = mysqli_fetch_assoc($att_res);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Regularize Attendance - EMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div style="max-width:500px;margin:60px auto;background:white;padding:30px;border-radius:12px;box-shadow:0 4px 20px rgba(0,0,0,0.1);">
    <h2 style="font-size:20px;color:#1a1a2e;margin-bottom:20px;">Regularize Attendance</h2>

    <table style="width:100%;margin-bottom:24px;font-size:14px;">
        <tr><td style="padding:8px 0;color:#888;">Employee</td><td><b><?php echo $att['first_name'].' '.$att['last_name']; ?></b></td></tr>
        <tr><td style="padding:8px 0;color:#888;">Date</td><td><b><?php echo $att['date']; ?></b></td></tr>
        <tr><td style="padding:8px 0;color:#888;">Check In</td><td><b><?php echo $att['check_in']; ?></b></td></tr>
        <tr><td style="padding:8px 0;color:#888;">Check Out</td><td><b><?php echo $att['check_out']; ?></b></td></tr>
        <tr><td style="padding:8px 0;color:#888;">Current Status</td><td><b><?php echo $att['status']; ?></b></td></tr>
    </table>

    <form action="save_regularize.php" method="POST">
        <input type="hidden" name="attendance_id" value="<?php echo $attendance_id; ?>">
        <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">

        <div class="field">
            <label>Update Status</label>
            <select name="status">
                <option value="present" <?php echo ($att['status']=='present')?'selected':''; ?>>Present</option>
                <option value="late" <?php echo ($att['status']=='late')?'selected':''; ?>>Late</option>
                <option value="half_day" <?php echo ($att['status']=='half_day')?'selected':''; ?>>Half Day</option>
                <option value="work_from_home" <?php echo ($att['status']=='work_from_home')?'selected':''; ?>>Work From Home</option>
            </select>
        </div>

       <div class="field">
            <label style="display:block;font-size:12px;color:#888;margin-bottom:6px;">Check In Time</label>
            <input type="time" name="check_in" 
                   value="<?php echo substr($att['check_in'], 0, 5); ?>"
                   style="width:100%;padding:10px 14px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;outline:none;">
        </div>

        <div class="field" style="margin-top:16px;">
            <label style="display:block;font-size:12px;color:#888;margin-bottom:6px;">Check Out Time</label>
            <input type="time" name="check_out" 
                   value="<?php echo substr($att['check_out'], 0, 5); ?>"
                   style="width:100%;padding:10px 14px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;outline:none;">
        </div>

        <button type="submit" class="submit-btn" style="margin-top:16px;">Update Attendance</button>
        <a href="javascript:history.back()" style="display:inline-block;margin-top:12px;margin-left:16px;color:#3b82f6;font-size:14px;">Cancel</a>
    </form>
</div>
</body>
</html>