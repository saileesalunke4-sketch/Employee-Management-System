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

// Fetch uploaded documents
mysqli_query($conn,"CREATE TABLE IF NOT EXISTS employee_documents (doc_id INT AUTO_INCREMENT PRIMARY KEY, emp_id INT, pan_card VARCHAR(255), aadhar_card VARCHAR(255), marks_card VARCHAR(255), uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$docs = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM employee_documents WHERE emp_id='$emp_id'")) ?? [];
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
.doc-box{display:flex;align-items:center;justify-content:space-between;background:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;padding:12px 16px;margin-bottom:10px;}
.doc-name{font-size:13px;font-weight:600;color:#1a1a2e;}
.doc-status{font-size:12px;color:#16a34a;font-weight:600;}
.doc-missing{font-size:12px;color:#9ca3af;}
.view-btn{background:#eff6ff;color:#2563eb;padding:5px 14px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <!-- Profile Photo -->
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
    </div>

    <!-- My Details -->
    <div class="form-card" style="margin-top:0;">
        <h3 class="section-title">My Details</h3>
        <table class="emp-table">
            <tr><td><b>Employee ID</b></td><td><span class="pill blue" style="font-weight:700;"><?php echo htmlspecialchars($emp['employee_code'] ?: '-'); ?></span></td></tr>
            <tr><td><b>Name</b></td><td><?php echo $emp['first_name'].' '.$emp['last_name'];?></td></tr>
            <tr><td><b>Contact</b></td><td><?php echo $emp['contact'];?></td></tr>
            <tr><td><b>Designation</b></td><td><?php echo $emp['designation'];?></td></tr>
            <tr><td><b>Blood Group</b></td><td><?php echo $emp['blood_group'] ?: '-';?></td></tr>
            <tr><td><b>Date of Birth</b></td><td><?php echo $emp['dob'];?></td></tr>
            <tr><td><b>Address</b></td><td><?php echo $emp['address'];?></td></tr>
        </table>
    </div>

    <!-- Edit Profile -->
    <div class="form-card" style="margin-top:0;">
        <h3 class="section-title">Edit Profile</h3>
        <form action="update_profile.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>First Name</label><input type="text" name="first_name" value="<?php echo $emp['first_name'];?>" required></div>
                <div class="field"><label>Last Name</label><input type="text" name="last_name" value="<?php echo $emp['last_name'];?>" required></div>
                <div class="field"><label>Contact</label><input type="text" name="contact" value="<?php echo $emp['contact'];?>" required></div>
                <div class="field"><label>Designation</label><input type="text" name="designation" value="<?php echo $emp['designation'];?>" required></div>

                <!-- BLOOD GROUP DROPDOWN -->
                <div class="field"><label>Blood Group</label>
                    <select name="blood_group" style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;color:#1a1a2e;background:white;">
                        <option value="">-- Select --</option>
                        <?php
                        $bgroups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                        foreach($bgroups as $bg){
                            $sel = ($emp['blood_group']==$bg) ? 'selected' : '';
                            echo "<option value='$bg' $sel>$bg</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo $emp['dob'];?>" required></div>
                <div class="field" style="grid-column:1/-1;"><label>Address</label><input type="text" name="address" value="<?php echo $emp['address'];?>" required></div>
            </div>
            <button type="submit" class="submit-btn">Update Profile</button>
        </form>
    </div>

    <!-- Upload Documents -->
    <div class="form-card" style="margin-top:0;">
        <h3 class="section-title">Upload Documents</h3>
        <form action="upload_documents.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field"><label>PAN Card</label><input type="file" name="pan_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div class="field"><label>Aadhar Card</label><input type="file" name="aadhar_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                <div class="field"><label>Marks Card</label><input type="file" name="marks_card" accept=".pdf,.jpg,.jpeg,.png"></div>
            </div>
            <button type="submit" class="submit-btn">Upload Documents</button>
        </form>
    </div>

    <!-- View Uploaded Documents -->
    <div class="form-card" style="margin-top:0;">
        <h3 class="section-title">My Documents</h3>
        <?php
        $doc_list = [
            'pan_card'    => ' PAN Card',
            'aadhar_card' => ' Aadhar Card',
            'marks_card'  => ' Marks Card',
        ];
        foreach($doc_list as $col => $label){
            $file = $docs[$col] ?? '';
            echo "<div class='doc-box'>
                <div>
                    <div class='doc-name'>{$label}</div>";
            if(!empty($file) && file_exists('uploads/documents/'.$file)){
                echo "<div class='doc-status'> Uploaded</div>";
            } else {
                echo "<div class='doc-missing'> Not uploaded yet</div>";
            }
            echo "</div>";
            if(!empty($file) && file_exists('uploads/documents/'.$file)){
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if(in_array($ext,['jpg','jpeg','png'])){
                    echo "<a href='uploads/documents/{$file}' target='_blank' class='view-btn'> View</a>";
                } else {
                    echo "<a href='uploads/documents/{$file}' target='_blank' class='view-btn'>📥 Download</a>";
                }
            }
            echo "</div>";
        }
        ?>
    </div>

</div>
</div>
</div>

<?php include 'common_js.php'; ?>
</body>
</html>
