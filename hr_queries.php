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
$page_title = "HR Queries";

// Handle new query submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['subject'])){
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO hr_queries (emp_id, subject, message, status) VALUES ('$emp_id','$subject','$message','pending')");
    header("Location: hr_queries.php?sent=1");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>HR Queries - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.resolved{background:#dcfce7;color:#16a34a;}
.query-card{background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:14px;padding:18px 20px;}
.query-subject{font-size:14px;font-weight:700;color:#1a1a2e;margin:0 0 6px;}
.query-meta{font-size:11px;color:#9ca3af;margin-bottom:10px;}
.query-message{font-size:13px;color:#4b5563;line-height:1.6;background:#f9fafb;border-radius:8px;padding:12px 14px;}
.reply-box{margin-top:12px;background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px 14px;}
.reply-label{font-size:11px;font-weight:700;color:#1d4ed8;margin-bottom:4px;}
.reply-text{font-size:13px;color:#1e3a8a;line-height:1.6;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <?php if(isset($_GET['sent'])): ?>
    <div style="background:#dcfce7;color:#16a34a;border:1px solid #86efac;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:13px;font-weight:600;">
        ✅ Your query has been submitted to HR successfully!
    </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Raise a New HR Query</h3>
        <form action="hr_queries.php" method="POST">
            <div class="form-grid">
                <div class="field" style="grid-column:1/-1;"><label>Subject</label>
                    <input type="text" name="subject" placeholder="e.g. Salary discrepancy, Leave balance issue..." required>
                </div>
                <div class="field" style="grid-column:1/-1;"><label>Message</label>
                    <textarea name="message" rows="4" placeholder="Describe your query in detail..." required></textarea>
                </div>
            </div>
            <button type="submit" class="submit-btn">Submit Query</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My Query History</h3>
        <?php
        $queries = mysqli_query($conn,"SELECT * FROM hr_queries WHERE emp_id='$emp_id' ORDER BY query_id DESC");
        if(mysqli_num_rows($queries) == 0){
            echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>You haven't raised any HR queries yet.</p>";
        }
        while($q = mysqli_fetch_assoc($queries)){
            $status_label = ucfirst($q['status']);
            echo "<div class='query-card'>
                <p class='query-subject'>{$q['subject']} <span class='status-pill {$q['status']}' style='margin-left:8px;'>{$status_label}</span></p>
                <p class='query-meta'>Raised on " . date('d M Y, h:i A', strtotime($q['created_at'])) . "</p>
                <div class='query-message'>{$q['message']}</div>";
            if($q['status'] == 'resolved' && !empty($q['admin_reply'])){
                $replier = $q['replied_by'] ?: 'HR';
                echo "<div class='reply-box'>
                    <p class='reply-label'>💬 Response from {$replier}</p>
                    <p class='reply-text'>{$q['admin_reply']}</p>
                </div>";
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
