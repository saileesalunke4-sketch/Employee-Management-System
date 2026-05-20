<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">

    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;">
        <div class="card"><h3>Total Employees</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM users WHERE role='employee'"))['t']; ?></p></div>
        <div class="card"><h3>Present Today</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status='present'"))['t']; ?></p></div>
        <div class="card"><h3>Pending Leaves</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='pending'"))['t']; ?></p></div>
        <div class="card"><h3>Approved Leaves</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved'"))['t']; ?></p></div>
        <div class="card"><h3>Pending Tasks</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"))['t']; ?></p></div>
        <div class="card"><h3>Completed Tasks</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"))['t']; ?></p></div>
    </div>

</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
