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
$page_title = "My Attendance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Attendance - EMS</title>
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
        <h3 class="section-title">Mark Attendance</h3>
        <form action="save_attendance.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Date</label><input type="date" name="date" value="<?php echo date('Y-m-d');?>" min="<?php echo date('Y-m-d');?>" max="<?php echo date('Y-m-d');?>" required></div>
                <div class="field"><label>Check In</label><input type="time" name="check_in" required></div>
                <div class="field"><label>Check Out</label><input type="time" name="check_out" required></div>
                <div class="field"><label>Status</label>
                    <select name="status"><option value="present">Present</option><option value="late">Late</option><option value="half_day">Half Day</option><option value="work_from_home">Work From Home</option></select>
                </div>
            </div>
            <button type="submit" class="submit-btn">Mark Attendance</button>
        </form>
    </div>
    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">All My Attendance Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Hours</th><th>Overtime</th><th>Sunday</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' ORDER BY date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $hrs=0;
                    if($row['check_in']&&$row['check_out']){ $in=strtotime($row['check_in']); $out=strtotime($row['check_out']); if($out>$in) $hrs=($out-$in)/3600; }
                    $st_map=['present'=>'approved','late'=>'pending','half_day'=>'pending','work_from_home'=>'approved','absent'=>'rejected'];
                    $pill=$st_map[$row['status']]??'pending';
                    echo "<tr><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td>
                    <td><span class='status-pill $pill'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                    <td>".($hrs>0?number_format($hrs,1)." hrs":"-")."</td>
                    <td>".($row['overtime_hours']>0?"<span style='color:#d97706;font-weight:600;'>".$row['overtime_hours']." hrs</span>":"-")."</td>
                    <td>".($row['is_sunday']?"<span style='color:#db2777;font-weight:600;'>✓ Sunday</span>":"-")."</td></tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

</div>

</div>
</div>

<?php include 'common_js.php'; ?>
</body>
</html>
