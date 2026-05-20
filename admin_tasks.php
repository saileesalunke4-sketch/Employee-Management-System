<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
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
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Assign Task</h3>
        <form action="save_task.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Select Employee</label>
                    <select name="emp_id"><?php $res=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name FROM employees e"); while($row=mysqli_fetch_assoc($res)) echo "<option value='{$row['emp_id']}'>{$row['first_name']} {$row['last_name']}</option>"; ?></select>
                </div>
                <div class="field"><label>Task Name</label><input type="text" name="task_name" placeholder="Task Name" required></div>
                <div class="field"><label>Description</label><input type="text" name="description" placeholder="Description" required></div>
                <div class="field"><label>Target Date</label><input type="date" name="target_date" required></div>
                <div class="field"><label>Status</label>
                    <select name="status"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select>
                </div>
                <div class="field"><label>Hours Worked</label><input type="number" name="hours_worked" placeholder="Hours" value="0" required></div>
            </div>
            <button type="submit" class="submit-btn">Assign Task</button>
        </form>

        <h3 class="section-title" style="margin-top:28px;">Task Records</h3>
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
