<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$admin_id = $_SESSION['user']['id'];
$photo_res = mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$admin_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';
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
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Profile Photo</h3>
        <?php if(!empty($profile_photo) && file_exists('uploads/'.$profile_photo)): ?>
            <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #3b82f6;margin-bottom:16px;">
        <?php endif; ?>
        <form action="save_profile_photo.php" method="POST" enctype="multipart/form-data">
            <div class="field"><label>Upload Profile Photo</label><input type="file" name="profile_photo" accept="image/*" required></div>
            <button type="submit" class="submit-btn">Update Photo</button>
        </form>

        <h3 class="section-title" style="margin-top:28px;">My Details</h3>
        <table class="emp-table">
            <tr><td><b>Name</b></td><td><?php echo $_SESSION['user']['name']; ?></td></tr>
            <tr><td><b>Email</b></td><td><?php echo $_SESSION['user']['email']; ?></td></tr>
            <tr><td><b>Role</b></td><td><span class="pill blue"><?php echo ucfirst(str_replace('_',' ',$_SESSION['user']['role'])); ?></span></td></tr>
        </table>
    </div>
</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
