<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Leaves";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Leaves - EMS</title>
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
        <h3 class="section-title">Leave Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT l.*,e.first_name,e.last_name FROM leaves l JOIN employees e ON l.emp_id=e.emp_id ORDER BY l.leave_id DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $pc=['approved'=>'green','rejected'=>'red','pending'=>'yellow'][$row['status']]??'yellow';
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['leave_type']}</td><td>{$row['from_date']}</td><td>{$row['to_date']}</td><td>{$row['reason']}</td>
                    <td><span class='pill {$pc}'>".ucfirst($row['status'])."</span></td>
                    <td><a href='leave_action.php?id={$row['leave_id']}&action=approved&redirect=sa_leaves.php' class='approve-btn'>Approve</a>
                    <a href='leave_action.php?id={$row['leave_id']}&action=rejected&redirect=sa_leaves.php' class='reject-btn'>Reject</a></td></tr>";
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
