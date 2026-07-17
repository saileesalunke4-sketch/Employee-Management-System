<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "All Employees";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>All Employees - EMS</title>
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
        <h3 class="section-title">All Employees</h3>
        <form method="GET" style="max-width:340px;margin-bottom:14px;">
            <div class="topbar-search" style="margin-left:0;width:100%;max-width:100%;">
                <?php echo ems_icon('search',16); ?>
                <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" placeholder="Search by name, email, designation…">
            </div>
        </form>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Blood Group</th><th>DOB</th></tr></thead>
            <tbody>
            <?php
                $sa_q = trim($_GET['q'] ?? '');
                $sql = "SELECT u.id,u.name,u.email,e.designation,e.contact,e.blood_group,e.dob FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'";
                if($sa_q !== ''){
                    $esc = mysqli_real_escape_string($conn, $sa_q);
                    $sql .= " AND (u.name LIKE '%{$esc}%' OR u.email LIKE '%{$esc}%' OR e.designation LIKE '%{$esc}%')";
                }
                $res=mysqli_query($conn,$sql);
                if($sa_q !== '' && mysqli_num_rows($res) === 0){
                    echo "<tr><td colspan='7' style='text-align:center;padding:28px;color:var(--text-3);'>No employees found matching \"".htmlspecialchars($sa_q)."\"</td></tr>";
                }
                while($row=mysqli_fetch_assoc($res)) echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['designation']}</td><td>{$row['contact']}</td><td>{$row['blood_group']}</td><td>{$row['dob']}</td></tr>";
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
