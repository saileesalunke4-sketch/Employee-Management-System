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
$page_title = "My Tasks";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Tasks - EMS</title>
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

    <!-- BUGFIX (BUG-005): a project assigned to an employee via
         Admin/Super Admin > Projects was never shown anywhere on the
         employee side — Projects and Tasks are separate tables with no
         link between them in the UI. Added here so an assigned project
         is actually visible to the employee it was assigned to. -->
    <?php
        // BUGFIX: a project can now be assigned to multiple employees via
        // project_assignments — query through that instead of the old
        // single assigned_emp_id column, so this employee sees every
        // project they're assigned to, not just ones where they happened
        // to be the sole/first assignee.
        $proj_res = mysqli_query($conn, "SELECT p.*, d.dept_name FROM projects p JOIN project_assignments pa ON p.project_id=pa.project_id LEFT JOIN departments d ON p.dept_id=d.dept_id WHERE pa.emp_id='$emp_id' ORDER BY p.target_date ASC");
        if($proj_res && mysqli_num_rows($proj_res) > 0){
    ?>
    <div class="form-card">
        <h3 class="section-title">My Assigned Projects</h3>
        <table class="emp-table">
            <thead><tr><th>Project Name</th><th>Description</th><th>Department</th><th>Start Date</th><th>Target Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php
                $proj_pill = ['ongoing'=>'in_progress','completed'=>'completed','on_hold'=>'pending'];
                while($p = mysqli_fetch_assoc($proj_res)){
                    $pill = $proj_pill[$p['status']] ?? 'pending';
                    echo "<tr><td><b>{$p['project_name']}</b></td><td>{$p['description']}</td><td>".($p['dept_name']??'-')."</td><td>{$p['start_date']}</td><td>{$p['target_date']}</td>
                    <td><span class='status-pill {$pill}'>".ucfirst(str_replace('_',' ',$p['status']))."</span></td></tr>";
                }
            ?>
            </tbody>
        </table>
    </div>
    <?php } ?>

    <div class="form-card">
        <h3 class="section-title">My Tasks</h3>
        <form method="GET" style="max-width:340px;margin-bottom:14px;">
            <div class="topbar-search" style="margin-left:0;width:100%;max-width:100%;">
                <?php echo ems_icon('search',16); ?>
                <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search my tasks…">
            </div>
        </form>
        <table class="emp-table">
            <thead><tr><th>Task Name</th><th>Description</th><th>Target Date</th><th>Status</th><th>Hours</th><th>Update</th></tr></thead>
            <tbody>
            <?php
                $task_q = trim($_GET['q'] ?? '');
                $sql = "SELECT * FROM tasks WHERE emp_id='$emp_id'";
                if($task_q !== ''){
                    $esc = mysqli_real_escape_string($conn, $task_q);
                    $sql .= " AND (task_name LIKE '%{$esc}%' OR description LIKE '%{$esc}%')";
                }
                $sql .= " ORDER BY target_date DESC";
                $result=mysqli_query($conn,$sql);
                if($task_q !== '' && mysqli_num_rows($result) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;padding:28px;color:var(--text-3);'>No tasks found matching \"".htmlspecialchars($task_q)."\"</td></tr>";
                }
                while($row=mysqli_fetch_assoc($result)){
                    $pill=['completed'=>'completed','in_progress'=>'in_progress','pending'=>'pending'][$row['status']]??'pending';
                    echo "<tr><td><b>{$row['task_name']}</b></td><td>{$row['description']}</td><td>{$row['target_date']}</td>
                    <td><span class='status-pill {$pill}'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                    <td>{$row['hours_worked']} hrs</td>
                    <td><form action='update_task_status.php' method='POST' style='display:flex;gap:6px;align-items:center;'>
                        <input type='hidden' name='task_id' value='{$row['task_id']}'>
                        <select name='status' style='padding:5px 8px;border-radius:6px;border:1px solid #e0e0e0;font-size:12px;'>
                            <option value='pending' ".($row['status']=='pending'?'selected':'').">Pending</option>
                            <option value='in_progress' ".($row['status']=='in_progress'?'selected':'').">In Progress</option>
                            <option value='completed' ".($row['status']=='completed'?'selected':'').">Completed</option>
                        </select>
                        <button type='submit' style='padding:5px 10px;background:#1a3a6e;color:white;border:none;border-radius:6px;font-size:12px;cursor:pointer;'>Update</button>
                    </form></td></tr>";
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
