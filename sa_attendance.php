<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Attendance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Attendance - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">Attendance Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id ORDER BY a.date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $type=($row['status']=='work_from_home')?"<span class='pill blue'>WFH</span>":"<span class='pill green'>Office</span>";
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td>".ucfirst(str_replace('_',' ',$row['status']))."</td><td>{$type}</td><td><a href='regularize.php?id={$row['attendance_id']}' class='approve-btn'>Regularize</a></td></tr>";
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
