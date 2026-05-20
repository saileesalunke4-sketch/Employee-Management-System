<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Salary";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Salary - EMS</title>
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
        <h3 class="section-title">Add Salary</h3>
        <form action="save_salary.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Select Employee</label>
                    <select name="emp_id"><?php $res=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name FROM employees e"); while($row=mysqli_fetch_assoc($res)) echo "<option value='{$row['emp_id']}'>{$row['first_name']} {$row['last_name']}</option>"; ?></select>
                </div>
                <div class="field"><label>Basic Pay</label><input type="number" name="basic_pay" placeholder="Basic Pay" required></div>
                <div class="field"><label>Allowances</label><input type="number" name="allowances" placeholder="Allowances" required></div>
                <div class="field"><label>Deductions (PF)</label><input type="number" name="deductions" placeholder="Deductions" required></div>
                <div class="field"><label>LOP Days</label><input type="number" name="lop_days" placeholder="LOP Days" value="0" min="0"></div>
                <div class="field"><label>Month</label>
                    <select name="month"><option>January</option><option>February</option><option>March</option><option>April</option><option>May</option><option>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option></select>
                </div>
                <div class="field"><label>Year</label><input type="number" name="year" value="<?php echo date('Y');?>" required></div>
            </div>
            <button type="submit" class="submit-btn">Add Salary</button>
        </form>

        <h3 class="section-title" style="margin-top:28px;">Salary Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>LOP Days</th><th>LOP Amt</th><th>Net Pay</th><th>Month</th><th>Year</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT s.*,e.first_name,e.last_name FROM salary s JOIN employees e ON s.emp_id=e.emp_id ORDER BY s.year DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $ld=isset($row['lop_days'])?$row['lop_days']:0;
                    $la=isset($row['lop_amount'])?$row['lop_amount']:0;
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>&#8377;{$row['basic_pay']}</td><td>&#8377;{$row['allowances']}</td><td>&#8377;{$row['deductions']}</td>
                    <td><span class='pill red'>{$ld} days</span></td><td>&#8377;{$la}</td><td><b>&#8377;{$row['net_pay']}</b></td><td>{$row['month']}</td><td>{$row['year']}</td></tr>";
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
