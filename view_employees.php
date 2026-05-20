<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "View Employees";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>View Employees - EMS</title>
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
        <h3 class="section-title">All Employees</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Role</th><th>Department</th></tr></thead>
            <tbody>
            <?php
                $res = mysqli_query($conn,"SELECT u.id,u.name,u.email,u.role,e.designation,e.contact,e.emp_id,e.dept_id FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'");
                while($row=mysqli_fetch_assoc($res)){
                    $depts_opt='';
                    $d_res=mysqli_query($conn,"SELECT * FROM departments ORDER BY dept_name");
                    while($d=mysqli_fetch_assoc($d_res)){
                        $sel=($row['dept_id']==$d['dept_id'])?'selected':'';
                        $depts_opt.="<option value='{$d['dept_id']}' {$sel}>{$d['dept_name']}</option>";
                    }
                    echo "<tr>
                        <td>{$row['id']}</td>
                        <td>{$row['name']}</td>
                        <td>{$row['email']}</td>
                        <td>{$row['designation']}</td>
                        <td>{$row['contact']}</td>
                        <td><span class='pill blue'>".ucfirst($row['role'])."</span></td>
                        <td>
                            <form action='assign_department.php' method='POST' style='display:flex;gap:6px;align-items:center;'>
                                <input type='hidden' name='emp_id' value='{$row['emp_id']}'>
                                <select name='dept_id' style='padding:5px 8px;border-radius:6px;border:1px solid #e0e0e0;font-size:12px;'>
                                    <option value=''>-- Select --</option>{$depts_opt}
                                </select>
                                <button type='submit' style='padding:5px 10px;background:#1a3a6e;color:white;border:none;border-radius:6px;font-size:12px;cursor:pointer;'>Assign</button>
                            </form>
                        </td>
                    </tr>";
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
