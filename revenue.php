<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Monthly Revenue";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Monthly Revenue - EMS</title>
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
        <h3 class="section-title">Add Monthly Revenue</h3>
        <form action="save_revenue.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Month</label>
                    <select name="month"><option>January</option><option>February</option><option>March</option><option>April</option><option>May</option><option>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option></select>
                </div>
                <div class="field"><label>Year</label><input type="number" name="year" value="<?php echo date('Y');?>" required></div>
                <div class="field"><label>Revenue Amount (&#8377;)</label><input type="number" name="amount" placeholder="Enter amount" required></div>
            </div>
            <button type="submit" class="submit-btn">Add Revenue</button>
        </form>
        <h3 class="section-title" style="margin-top:28px;">Revenue Records</h3>
        <table class="emp-table">
            <thead><tr><th>Month</th><th>Year</th><th>Amount</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM revenue ORDER BY year DESC,revenue_id DESC");
                if($res) while($row=mysqli_fetch_assoc($res)) echo "<tr><td>{$row['month']}</td><td>{$row['year']}</td><td><b>&#8377;".number_format($row['amount'],2)."</b></td></tr>";
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
