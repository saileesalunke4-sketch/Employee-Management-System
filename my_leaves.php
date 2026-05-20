<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';
$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];
$page_title = "My Leaves";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Leaves - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.completed{background:#dcfce7;color:#16a34a;}
.status-pill.in_progress{background:#fef3c7;color:#d97706;}
.skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">Apply for Leave</h3>
        <form action="save_leave.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Leave Type</label>
                    <select name="leave_type" required>
                        <option value="">-- Select --</option>
                        <?php $lt=mysqli_query($conn,"SELECT * FROM leave_types"); while($l=mysqli_fetch_assoc($lt)) echo "<option value='{$l['leave_type_name']}'>{$l['leave_type_name']}</option>"; ?>
                    </select>
                </div>
                <div class="field"><label>From Date</label><input type="date" name="from_date" required></div>
                <div class="field"><label>To Date</label><input type="date" name="to_date" required></div>
                <div class="field" style="grid-column:1/-1"><label>Reason</label><textarea name="reason" rows="3" placeholder="Enter reason..." required></textarea></div>
            </div>
            <button type="submit" class="submit-btn">Apply Leave</button>
        </form>
    </div>
    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My Leave Records</h3>
        <table class="emp-table">
            <thead><tr><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $days=(strtotime($row['to_date'])-strtotime($row['from_date']))/86400+1;
                    echo "<tr><td>{$row['leave_type']}</td><td>{$row['from_date']}</td><td>{$row['to_date']}</td><td>{$days}</td><td>{$row['reason']}</td>
                    <td><span class='status-pill {$row['status']}'>".ucfirst($row['status'])."</span></td></tr>";
                }
            ?>
            </tbody>
        </table>
    </div>

</div>

</div>
</div>

<?php include 'common_js.php'; ?>
</body>
</html>
