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
$page_title = "My Profile";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Profile - EMS</title>
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
        <h3 class="section-title">Profile Photo</h3>
        <?php
        $ph=mysqli_fetch_assoc(mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$user_id'"))['profile_photo']??'';
        if(!empty($ph)&&file_exists('uploads/'.$ph)): ?>
            <img src="uploads/<?php echo htmlspecialchars($ph);?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #3b82f6;margin-bottom:16px;">
        <?php endif;?>
        <form action="save_profile_photo.php" method="POST" enctype="multipart/form-data">
            <div class="field"><label>Upload Profile Photo</label><input type="file" name="profile_photo" accept="image/*" required></div>
            <button type="submit" class="submit-btn">Update Photo</button>
        </form>
        <h3 class="section-title" style="margin-top:28px;">My Details</h3>
        <table class="emp-table">
            <tr><td><b>Name</b></td><td><?php echo $emp['first_name'].' '.$emp['last_name'];?></td></tr>
            <tr><td><b>Contact</b></td><td><?php echo $emp['contact'];?></td></tr>
            <tr><td><b>Designation</b></td><td><?php echo $emp['designation'];?></td></tr>
            <tr><td><b>Blood Group</b></td><td><?php echo $emp['blood_group'];?></td></tr>
            <tr><td><b>Date of Birth</b></td><td><?php echo $emp['dob'];?></td></tr>
            <tr><td><b>Address</b></td><td><?php echo $emp['address'];?></td></tr>
        </table>
        <h3 class="section-title" style="margin-top:28px;">Edit Profile</h3>
        <form action="update_profile.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>First Name</label><input type="text" name="first_name" value="<?php echo $emp['first_name'];?>" required></div>
                <div class="field"><label>Last Name</label><input type="text" name="last_name" value="<?php echo $emp['last_name'];?>" required></div>
                <div class="field"><label>Contact</label><input type="text" name="contact" value="<?php echo $emp['contact'];?>" required></div>
                <div class="field"><label>Designation</label><input type="text" name="designation" value="<?php echo $emp['designation'];?>" required></div>
                <div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo $emp['dob'];?>" required></div>
                <div class="field"><label>Address</label><input type="text" name="address" value="<?php echo $emp['address'];?>" required></div>
            </div>
            <button type="submit" class="submit-btn">Update Profile</button>
        </form>
        <h3 class="section-title" style="margin-top:28px;">Upload Documents</h3>
        <form action="upload_documents.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field"><label>PAN Card</label><input type="file" name="pan_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div class="field"><label>Aadhar Card</label><input type="file" name="aadhar_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div class="field"><label>Marks Card</label><input type="file" name="marks_card" accept=".pdf,.jpg,.jpeg,.png"></div>
            </div>
            <button type="submit" class="submit-btn">Upload Documents</button>
        </form>
    </div>

</div>

</div>
</div>

<?php include 'common_js.php'; ?>
</body>
</html>
