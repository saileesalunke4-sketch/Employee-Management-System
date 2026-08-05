<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Leave Types";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Leave Types - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard">
<?php
    // BUGFIX: hardcoded to the Admin shell before — a super_admin landing
    // here would see the Admin sidebar/topbar. Pick the shell that
    // matches whoever is actually logged in.
    if($role === 'admin') include 'sidebar_admin.php';
    else include 'sidebar_sa.php';
?>
<div class="main-content">
<?php
    if($role === 'admin') include 'topbar_admin.php';
    else include 'topbar_sa.php';
?>

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Add Leave Type</h3>
        <form action="save_leave_type.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Leave Type Name</label><input type="text" name="leave_type_name" placeholder="e.g. Maternity Leave" required></div>
                <div class="field"><label>Total Days Allowed</label><input type="number" name="total_days" placeholder="e.g. 10" required></div>
            </div>
            <button type="submit" class="submit-btn">Add Leave Type</button>
        </form>

        <h3 class="section-title" style="margin-top:28px;">All Leave Types</h3>
        <table class="emp-table">
            <thead><tr><th>Leave Type</th><th>Total Days</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM leave_types ORDER BY id ASC");
                while($row=mysqli_fetch_assoc($res))
                    echo "<tr><td><b>{$row['leave_type_name']}</b></td><td>{$row['total_days']}</td><td><a href='delete_leave_type.php?id={$row['id']}' class='reject-btn' onclick='return confirm(\"Delete this leave type?\")'>Delete</a></td></tr>";
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
