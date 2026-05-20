<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Departments";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Departments - EMS</title>
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
        <h3 class="section-title">&#127970; Add Department</h3>
        <form action="save_department.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Department Name</label><input type="text" name="dept_name" placeholder="e.g. Development, HR, Design" required></div>
                <div class="field"><label>Department Head</label><input type="text" name="dept_head" placeholder="e.g. John Smith"></div>
            </div>
            <button type="submit" class="submit-btn">Add Department</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">All Departments</h3>
        <table class="emp-table">
            <thead><tr><th>SR No.</th><th>Department Name</th><th>Department Head</th><th>Total Employees</th></tr></thead>
            <tbody>
            <?php
                $depts=mysqli_query($conn,"SELECT d.*,COUNT(e.emp_id) as total_emp FROM departments d LEFT JOIN employees e ON d.dept_id=e.dept_id GROUP BY d.dept_id ORDER BY d.dept_id DESC");
                $found=false; $cnt=1;
                while($dept=mysqli_fetch_assoc($depts)){
                    $found=true;
                    echo "<tr><td>{$cnt}</td><td><b>{$dept['dept_name']}</b></td><td>{$dept['dept_head']}</td>
                    <td><span style='background:#eff6ff;color:#1a3a6e;padding:3px 10px;border-radius:20px;font-weight:600;font-size:12px;'>{$dept['total_emp']} employees</span></td></tr>";
                    $cnt++;
                }
                if(!$found) echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No departments added yet.</td></tr>";
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
