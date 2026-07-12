<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';
$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id  = $emp['emp_id'];
$dept_id = $emp['dept_id'];
$page_title = "Department Wall";

$dept_name = 'No Department Assigned';
if($dept_id){
    $d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_name FROM departments WHERE dept_id=".(int)$dept_id));
    $dept_name = $d ? $d['dept_name'] : $dept_name;
}

// Handle new post
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $message = trim($_POST['message'] ?? '');
    if($message !== '' && $dept_id){
        $message_esc = mysqli_real_escape_string($conn, $message);
        mysqli_query($conn, "INSERT INTO department_wall_posts (emp_id, dept_id, message) VALUES ($emp_id, ".(int)$dept_id.", '$message_esc')");
    }
    header("Location: department_wall.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Department Wall - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.wall-post{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:14px 18px;margin-bottom:12px;}
.wall-post .meta{font-size:12px;color:#1d4ed8;font-weight:600;margin-bottom:4px;}
.wall-post .time{font-size:11px;color:#9ca3af;font-weight:400;margin-left:8px;}
.wall-post p{margin:0;font-size:13px;color:#374151;line-height:1.5;white-space:pre-wrap;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">🏢 <?php echo htmlspecialchars($dept_name); ?> — Department Wall</h3>

        <?php if($dept_id): ?>
        <form action="department_wall.php" method="POST" style="margin-bottom:20px;">
            <textarea name="message" rows="3" placeholder="Post a message to your department..." required style="width:100%;padding:10px 14px;border:1px solid #e0e0e0;border-radius:8px;font-size:13px;font-family:inherit;"></textarea>
            <button type="submit" class="submit-btn" style="margin-top:10px;">Post</button>
        </form>

        <?php
            $res = mysqli_query($conn, "SELECT w.*, e.first_name, e.last_name FROM department_wall_posts w JOIN employees e ON w.emp_id=e.emp_id WHERE w.dept_id=".(int)$dept_id." ORDER BY w.post_id DESC");
            if(mysqli_num_rows($res) === 0){
                echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>No posts yet. Be the first to post something to your department!</p>";
            } else {
                while($p = mysqli_fetch_assoc($res)){
                    $pname = htmlspecialchars($p['first_name'].' '.$p['last_name']);
                    $ptime = date('d M Y, h:i A', strtotime($p['created_at']));
                    echo "<div class='wall-post'>
                        <div class='meta'>👤 {$pname} <span class='time'>{$ptime}</span></div>
                        <p>".htmlspecialchars($p['message'])."</p>
                    </div>";
                }
            }
        ?>
        <?php else: ?>
            <p style="color:#9ca3af;text-align:center;padding:20px;">You are not assigned to a department yet — contact Admin to get added to one.</p>
        <?php endif; ?>
    </div>

</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
