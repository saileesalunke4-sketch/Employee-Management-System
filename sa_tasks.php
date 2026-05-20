<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Tasks";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Tasks - EMS</title>
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
        <h3 class="section-title">All Tasks</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Task</th><th>Description</th><th>Target Date</th><th>Status</th><th>Hours</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT t.*,e.first_name,e.last_name FROM tasks t JOIN employees e ON t.emp_id=e.emp_id ORDER BY t.target_date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $pc=['completed'=>'green','in_progress'=>'yellow','pending'=>'red'][$row['status']]??'yellow';
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td><b>{$row['task_name']}</b></td><td>{$row['description']}</td><td>{$row['target_date']}</td><td><span class='pill {$pc}'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td><td>{$row['hours_worked']} hrs</td></tr>";
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
