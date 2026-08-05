<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
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
<?php
    // BUGFIX: hardcoded to the Admin shell before, so a super_admin
    // clicking "Assign Department" from their dashboard would suddenly
    // see the Admin sidebar/topbar. Pick the shell for whoever is logged in.
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
        <h3 class="section-title">All Employees</h3>
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;flex-wrap:wrap;">
            <form method="GET" style="flex:1;min-width:220px;max-width:360px;" action="view_employees.php">
                <div class="topbar-search" style="margin-left:0;width:100%;max-width:100%;">
                    <?php echo ems_icon('search',16); ?>
                    <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search by name, email, ID, designation…">
                </div>
            </form>
            <?php if(!empty($_GET['q'])): ?>
                <a href="view_employees.php" style="font-size:12.5px;color:var(--text-3);text-decoration:none;font-weight:600;">Clear search &times;</a>
            <?php endif; ?>
            <a href="export_employees.php" style="margin-left:auto;display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;white-space:nowrap;">📥 Download Excel</a>
        </div>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Role</th><th>Department</th><th>Shift</th></tr></thead>
            <tbody>
            <?php
                $search_q = trim($_GET['q'] ?? '');
                $sql = "SELECT u.id,u.name,u.email,u.role,e.designation,e.contact,e.emp_id,e.dept_id,e.shift_id,e.employee_code FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'";
                if($search_q !== ''){
                    $esc = mysqli_real_escape_string($conn, $search_q);
                    $sql .= " AND (u.name LIKE '%{$esc}%' OR u.email LIKE '%{$esc}%' OR e.designation LIKE '%{$esc}%' OR e.employee_code LIKE '%{$esc}%')";
                }
                $res = mysqli_query($conn, $sql);
                $row_count = mysqli_num_rows($res);
                if($search_q !== '' && $row_count === 0){
                    echo "<tr><td colspan='8' style='text-align:center;padding:32px;color:var(--text-3);'>No employees found matching \"".htmlspecialchars($search_q)."\"</td></tr>";
                }
                while($row=mysqli_fetch_assoc($res)){
                    $depts_opt='';
                    $d_res=mysqli_query($conn,"SELECT * FROM departments ORDER BY dept_name");
                    while($d=mysqli_fetch_assoc($d_res)){
                        $sel=($row['dept_id']==$d['dept_id'])?'selected':'';
                        $depts_opt.="<option value='{$d['dept_id']}' {$sel}>{$d['dept_name']}</option>";
                    }
                    $shifts_opt='';
                    $s_res=mysqli_query($conn,"SELECT * FROM shifts ORDER BY start_time");
                    while($s=mysqli_fetch_assoc($s_res)){
                        $sel=($row['shift_id']==$s['shift_id'])?'selected':'';
                        $s_label = $s['shift_name'].' ('.date('h:i A',strtotime($s['start_time'])).'-'.date('h:i A',strtotime($s['end_time'])).')';
                        $shifts_opt.="<option value='{$s['shift_id']}' {$sel}>{$s_label}</option>";
                    }
                    echo "<tr>
                        <td><span class='pill blue' style='font-weight:700;'>".htmlspecialchars($row['employee_code'] ?: '-')."</span></td>
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
                        <td>
                            <form action='assign_shift.php' method='POST' style='display:flex;gap:6px;align-items:center;'>
                                <input type='hidden' name='emp_id' value='{$row['emp_id']}'>
                                <select name='shift_id' style='padding:5px 8px;border-radius:6px;border:1px solid #e0e0e0;font-size:12px;'>
                                    <option value=''>-- Select --</option>{$shifts_opt}
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
