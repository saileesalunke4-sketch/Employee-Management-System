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
$page_title = "My Performance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Performance - EMS</title>
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
        <h3 class="section-title">Add New Skill / Improvement</h3>
        <form action="save_performance.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Skill / Course Name</label><input type="text" name="skill_name" placeholder="e.g. PHP, MySQL, React JS" required></div>
                <div class="field"><label>Date Learned</label><input type="date" name="date_added" value="<?php echo date('Y-m-d');?>" required></div>
                <div class="field" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="3" placeholder="Describe what you learned..." required></textarea></div>
            </div>
            <button type="submit" class="submit-btn">Save</button>
        </form>
    </div>
    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My Skills</h3>
        <?php
        $skills=mysqli_query($conn,"SELECT skill_name FROM performance WHERE emp_id='$emp_id'");
        if(mysqli_num_rows($skills)==0) echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>No skills added yet.</p>";
        else { echo "<div style='padding:10px 0;'>"; while($sk=mysqli_fetch_assoc($skills)) echo "<span class='skill-tag'>&#10003; {$sk['skill_name']}</span>"; echo "</div>"; }
        ?>
    </div>
    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">Improvement Plan Records</h3>
        <table class="emp-table">
            <thead><tr><th>Skill/Course</th><th>Description</th><th>Date</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM performance WHERE emp_id='$emp_id' ORDER BY date_added DESC");
                while($row=mysqli_fetch_assoc($res)) echo "<tr><td><b>{$row['skill_name']}</b></td><td>{$row['description']}</td><td>{$row['date_added']}</td></tr>";
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
