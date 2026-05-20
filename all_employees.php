<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "All Employees";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>All Employees - EMS</title>
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
        <h3 class="section-title">All Employees</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Blood Group</th><th>DOB</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT u.id,u.name,u.email,e.designation,e.contact,e.blood_group,e.dob FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'");
                while($row=mysqli_fetch_assoc($res)) echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['designation']}</td><td>{$row['contact']}</td><td>{$row['blood_group']}</td><td>{$row['dob']}</td></tr>";
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
