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
$page_title = "My Salary";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Salary - EMS</title>
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

    <div class="form-card">
        <h3 class="section-title">My Salary Details</h3>
        <table class="emp-table">
            <thead><tr><th>Month</th><th>Year</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>LOP Days</th><th>Net Pay</th><th>Slip</th></tr></thead>
            <tbody>
            <?php
                $result=mysqli_query($conn,"SELECT * FROM salary WHERE emp_id='$emp_id' ORDER BY year DESC,salary_id DESC");
                while($row=mysqli_fetch_assoc($result)){
                    $lop_d=isset($row['lop_days'])?$row['lop_days']:0;
                    echo "<tr><td>{$row['month']}</td><td>{$row['year']}</td>
                    <td>&#8377;".number_format($row['basic_pay'],2)."</td>
                    <td>&#8377;".number_format($row['allowances'],2)."</td>
                    <td>&#8377;".number_format($row['deductions'],2)."</td>
                    <td><span style='color:#ef4444;'>{$lop_d} days</span></td>
                    <td><b>&#8377;".number_format($row['net_pay'],2)."</b></td>
                    <td><a href='generate_salary_slip.php?salary_id={$row['salary_id']}' style='background:#1a3a6e;color:white;padding:4px 12px;border-radius:6px;text-decoration:none;font-size:12px;'>&#128196; Download</a></td></tr>";
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
