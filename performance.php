<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Performance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Performance - EMS</title>
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
        <h3 class="section-title">Employee Performance Overview</h3>
        <div style="text-align:right;margin-bottom:12px;">
            <a href="export_performance.php" style="display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">📥 Download Excel</a>
        </div>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Total Tasks</th><th>Completed</th><th>Pending</th><th>Attendance Days</th><th>Skills Added</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT e.first_name,e.last_name,e.emp_id,
                    (SELECT COUNT(*) FROM tasks WHERE emp_id=e.emp_id) as total_tasks,
                    (SELECT COUNT(*) FROM tasks WHERE emp_id=e.emp_id AND status='completed') as completed,
                    (SELECT COUNT(*) FROM tasks WHERE emp_id=e.emp_id AND status='pending') as pending,
                    (SELECT COUNT(*) FROM attendance WHERE emp_id=e.emp_id) as att_days,
                    (SELECT COUNT(*) FROM performance WHERE emp_id=e.emp_id) as skills
                    FROM employees e");
                while($row=mysqli_fetch_assoc($res)){
                    echo "<tr><td><b>{$row['first_name']} {$row['last_name']}</b></td>
                    <td>{$row['total_tasks']}</td>
                    <td><span class='pill green'>{$row['completed']}</span></td>
                    <td><span class='pill red'>{$row['pending']}</span></td>
                    <td>{$row['att_days']}</td>
                    <td><span class='pill blue'>{$row['skills']} skills</span></td></tr>";
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
