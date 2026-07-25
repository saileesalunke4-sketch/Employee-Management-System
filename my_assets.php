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
$page_title = "My Assets";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Assets - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard emp-theme">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>
<div class="app-content">

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Currently Assigned</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Asset</th><th>Type</th><th>Serial No.</th><th>Assigned Since</th></tr></thead>
            <tbody>
            <?php
                $current = mysqli_query($conn, "SELECT a.*, aa.assigned_date FROM asset_assignments aa
                                                 JOIN assets a ON aa.asset_id = a.asset_id
                                                 WHERE aa.emp_id='$emp_id' AND aa.returned_date IS NULL
                                                 ORDER BY aa.assigned_date DESC");
                if(mysqli_num_rows($current) === 0){
                    echo "<tr><td colspan='4' style='text-align:center;color:var(--text-3,#9aa1ac);'>No assets currently assigned to you.</td></tr>";
                }
                while($row = mysqli_fetch_assoc($current)){
                    echo "<tr>
                        <td><b>".htmlspecialchars($row['asset_name'])."</b></td>
                        <td>".htmlspecialchars($row['asset_type'])."</td>
                        <td>".htmlspecialchars($row['serial_number'] ?: '-')."</td>
                        <td>{$row['assigned_date']}</td>
                    </tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">Past Assets</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Asset</th><th>Type</th><th>Assigned</th><th>Returned</th></tr></thead>
            <tbody>
            <?php
                $past = mysqli_query($conn, "SELECT a.*, aa.assigned_date, aa.returned_date FROM asset_assignments aa
                                              JOIN assets a ON aa.asset_id = a.asset_id
                                              WHERE aa.emp_id='$emp_id' AND aa.returned_date IS NOT NULL
                                              ORDER BY aa.returned_date DESC");
                if(mysqli_num_rows($past) === 0){
                    echo "<tr><td colspan='4' style='text-align:center;color:var(--text-3,#9aa1ac);'>No past assets on record.</td></tr>";
                }
                while($row = mysqli_fetch_assoc($past)){
                    echo "<tr>
                        <td>".htmlspecialchars($row['asset_name'])."</td>
                        <td>".htmlspecialchars($row['asset_type'])."</td>
                        <td>{$row['assigned_date']}</td>
                        <td>{$row['returned_date']}</td>
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
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
