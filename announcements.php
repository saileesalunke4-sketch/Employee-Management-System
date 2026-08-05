<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Announcements";

if($role === 'employee'){
    $user_id = $_SESSION['user']['id'];
    $emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'"));
    $emp_id  = $emp_row['emp_id'];
}

// Handle new announcement post (admin/super_admin only)
if($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role, ['admin','super_admin'])){
    $title   = mysqli_real_escape_string($conn, trim($_POST['title'] ?? ''));
    $message = mysqli_real_escape_string($conn, trim($_POST['message'] ?? ''));
    $posted_by = mysqli_real_escape_string($conn, $_SESSION['user']['name'] ?? ucfirst($role));

    if($title !== '' && $message !== ''){
        mysqli_query($conn, "INSERT INTO announcements (title, message, posted_by) VALUES ('$title','$message','$posted_by')");

        // BUGFIX (EMS-SUPADM-020): posting an announcement never notified
        // anyone — added a notification for every employee so it actually
        // shows up in their notification bell.
        $notif_msg = mysqli_real_escape_string($conn, "New announcement: $title");
        $today = date('Y-m-d');
        $all_emp = mysqli_query($conn, "SELECT emp_id FROM employees");
        while($e = mysqli_fetch_assoc($all_emp)){
            $eid = (int) $e['emp_id'];
            mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                                  VALUES ($eid, '', 'Announcement', '$today', '$today', '$title', '$notif_msg', 'announcement_posted', 'employee', 0)");
        }
    }
    header("Location: announcements.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Announcements - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.announcement-card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:16px 18px;margin-bottom:14px;}
.announcement-card h4{margin:0 0 6px;font-size:15px;color:#1a1a2e;}
.announcement-card .meta{font-size:11px;color:#9ca3af;margin-bottom:8px;}
.announcement-card p{margin:0;font-size:13px;color:#374151;line-height:1.5;white-space:pre-wrap;}
</style>
</head>
<body>
<div class="dashboard">
<?php
    if($role === 'employee') include 'sidebar_emp.php';
    elseif($role === 'admin') include 'sidebar_admin.php';
    else include 'sidebar_sa.php';
?>
<div class="main-content">
<?php
    if($role === 'employee') include 'topbar_emp.php';
    elseif($role === 'admin') include 'topbar_admin.php';
    else include 'topbar_sa.php';
?>

<div class="section active">

    <?php if(in_array($role, ['admin','super_admin'])): ?>
    <div class="form-card">
        <h3 class="section-title">Post New Announcement</h3>
        <form action="announcements.php" method="POST">
            <div class="field"><label>Title</label><input type="text" name="title" required></div>
            <div class="field" style="margin-top:12px;"><label>Message</label><textarea name="message" rows="3" required></textarea></div>
            <button type="submit" class="submit-btn" style="margin-top:14px;">Post Announcement</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">Company Announcements</h3>
        <?php
            $res = mysqli_query($conn, "SELECT * FROM announcements ORDER BY announcement_id DESC");
            if(mysqli_num_rows($res) === 0){
                echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>No announcements yet.</p>";
            } else {
                while($a = mysqli_fetch_assoc($res)){
                    echo "<div class='announcement-card'>
                        <h4>📢 ".htmlspecialchars($a['title'])."</h4>
                        <div class='meta'>Posted by ".htmlspecialchars($a['posted_by'])." &middot; ".date('d M Y, h:i A', strtotime($a['created_at']))."</div>
                        <p>".htmlspecialchars($a['message'])."</p>
                    </div>";
                }
            }
        ?>
    </div>

</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
