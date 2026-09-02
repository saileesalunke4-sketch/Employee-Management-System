<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Projects";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Projects - EMS</title>
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
        <h3 class="section-title">&#128203; Add New Project</h3>
        <form action="save_project.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Project Name</label><input type="text" name="project_name" placeholder="e.g. EMS Development" required></div>
                <div class="field"><label>Department</label>
                    <select name="dept_id" required>
                        <option value="">-- Select Department --</option>
                        <?php $depts=mysqli_query($conn,"SELECT * FROM departments ORDER BY dept_name"); while($d=mysqli_fetch_assoc($depts)) echo "<option value='{$d['dept_id']}'>{$d['dept_name']}</option>"; ?>
                    </select>
                </div>
                <div class="field"><label>Assign To Employee(s)</label>
                    <div style="max-height:160px;overflow-y:auto;border:1px solid #e0e0e0;border-radius:8px;padding:10px 14px;background:#fff;">
                        <?php $emps=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name,e.designation FROM employees e JOIN users u ON e.user_id=u.id WHERE u.role='employee' ORDER BY e.first_name"); while($e=mysqli_fetch_assoc($emps)){ ?>
                            <label style="display:flex;align-items:center;gap:8px;font-weight:400;padding:4px 0;">
                                <input type="checkbox" name="assigned_emp_ids[]" value="<?php echo $e['emp_id']; ?>" style="width:auto;">
                                <?php echo htmlspecialchars($e['first_name'].' '.$e['last_name'].' — '.$e['designation']); ?>
                            </label>
                        <?php } ?>
                    </div>
                    <p style="font-size:11.5px;color:#9ca3af;margin-top:4px;">Select one or more employees for this project.</p>
                </div>
                <div class="field"><label>Status</label>
                    <select name="status"><option value="ongoing">Ongoing</option><option value="completed">Completed</option><option value="on_hold">On Hold</option></select>
                </div>
                <div class="field"><label>Start Date</label><input type="date" name="start_date" required></div>
                <div class="field"><label>Target Date</label><input type="date" name="target_date" required></div>
                <div class="field" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="3" placeholder="Project details..."></textarea></div>
            </div>
            <button type="submit" class="submit-btn">Add Project</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">All Projects</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Sr No.</th><th>Project Name</th><th>Department</th><th>Assigned To</th><th>Start Date</th><th>Target Date</th><th>Status</th></tr></thead>
            <tbody>
            <?php
                $projects=mysqli_query($conn,"SELECT p.*,d.dept_name FROM projects p LEFT JOIN departments d ON p.dept_id=d.dept_id ORDER BY p.project_id DESC");
                $found=false; $cnt=1;
                while($proj=mysqli_fetch_assoc($projects)){
                    $found=true;
                    $sc=['ongoing'=>'background:#fef3c7;color:#d97706;','completed'=>'background:#dcfce7;color:#16a34a;','on_hold'=>'background:#fee2e2;color:#dc2626;'][$proj['status']]??'background:#f3f4f6;color:#6b7280;';
                    // BUGFIX: a project can now be assigned to multiple
                    // employees (project_assignments table), not just the
                    // single old assigned_emp_id column — list all of them.
                    $assignees_res = mysqli_query($conn, "SELECT e.first_name, e.last_name, e.designation FROM project_assignments pa JOIN employees e ON pa.emp_id=e.emp_id WHERE pa.project_id={$proj['project_id']} ORDER BY e.first_name");
                    $assignee_names = [];
                    while($a = mysqli_fetch_assoc($assignees_res)){
                        $assignee_names[] = htmlspecialchars($a['first_name'].' '.$a['last_name']." ({$a['designation']})");
                    }
                    $assignees_display = $assignee_names ? implode('<br>', $assignee_names) : "<span style='color:#9ca3af;'>Unassigned</span>";
                    echo "<tr><td>{$cnt}</td>
                        <td><b>{$proj['project_name']}</b><br><small style='color:#9ca3af;'>{$proj['description']}</small></td>
                        <td>{$proj['dept_name']}</td>
                        <td>{$assignees_display}</td>
                        <td>{$proj['start_date']}</td><td>{$proj['target_date']}</td>
                        <td><span style='padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;{$sc}'>".ucfirst(str_replace('_',' ',$proj['status']))."</span></td></tr>";
                    $cnt++;
                }
                if(!$found) echo "<tr><td colspan='7' style='text-align:center;color:#9ca3af;'>No projects added yet.</td></tr>";
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
