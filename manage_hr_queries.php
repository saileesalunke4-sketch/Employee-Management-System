<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "HR Queries";
$current_role = $_SESSION['user']['role'];
$sidebar_file = ($current_role == 'super_admin') ? 'sidebar_sa.php' : 'sidebar_admin.php';
$topbar_file  = ($current_role == 'super_admin') ? 'topbar_sa.php'  : 'topbar_admin.php';

// Handle reply submission
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['query_id'])){
    $query_id    = (int) $_POST['query_id'];
    $admin_reply = mysqli_real_escape_string($conn, $_POST['admin_reply']);
    $replied_by  = mysqli_real_escape_string($conn, $_SESSION['user']['name']);
    mysqli_query($conn, "UPDATE hr_queries SET admin_reply='$admin_reply', status='resolved', replied_by='$replied_by', resolved_at=NOW() WHERE query_id='$query_id'");
    header("Location: manage_hr_queries.php?replied=1");
    exit();
}

$pending_count  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM hr_queries WHERE status='pending'"))['t'];
$resolved_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM hr_queries WHERE status='resolved'"))['t'];
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
.reply-form textarea{width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;color:#1a1a2e;resize:vertical;margin-top:10px;}
.reply-btn{margin-top:8px;background:#1a3a6e;color:white;border:none;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;}
.qstat-cards{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;}
.qstat-box{background:white;border-radius:12px;padding:18px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,0.06);}
.qstat-num{font-size:28px;font-weight:800;}
</style>
</head>
<body>
<div class="dashboard">
<?php include $sidebar_file; ?>
<div class="main-content">
<?php include $topbar_file; ?>

<div class="section active">

    <?php if(isset($_GET['replied'])): ?>
    <div style="background:#dcfce7;color:#16a34a;border:1px solid #86efac;border-radius:10px;padding:12px 18px;margin-bottom:20px;font-size:13px;font-weight:600;">
        ✅ Reply sent to employee successfully!
    </div>
    <?php endif; ?>

    <div class="qstat-cards">
        <div class="qstat-box"><p class="qstat-num" style="color:#d97706;"><?php echo $pending_count; ?></p><p style="font-size:12px;color:#6b7280;">Pending Queries</p></div>
        <div class="qstat-box"><p class="qstat-num" style="color:#16a34a;"><?php echo $resolved_count; ?></p><p style="font-size:12px;color:#6b7280;">Resolved Queries</p></div>
    </div>

    <div class="form-card">
        <h3 class="section-title">All HR Queries</h3>
        <?php
        $queries = mysqli_query($conn,"SELECT q.*, e.first_name, e.last_name FROM hr_queries q JOIN employees e ON q.emp_id=e.emp_id ORDER BY (q.status='pending') DESC, q.query_id DESC");
        if(mysqli_num_rows($queries) == 0){
            echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>No HR queries raised yet.</p>";
        }
        while($q = mysqli_fetch_assoc($queries)){
            $status_label = ucfirst($q['status']);
            echo "<div class='query-card'>
                <p class='query-subject'>{$q['subject']} <span class='status-pill {$q['status']}' style='margin-left:8px;'>{$status_label}</span></p>
                <p class='query-meta'>From <strong>{$q['first_name']} {$q['last_name']}</strong> &middot; " . date('d M Y, h:i A', strtotime($q['created_at'])) . "</p>
                <div class='query-message'>{$q['message']}</div>";

            if($q['status'] == 'resolved'){
                $replier = $q['replied_by'] ?: 'HR';
                echo "<div class='reply-box'>
                    <p class='reply-label'>💬 Replied by {$replier}</p>
                    <p class='reply-text'>{$q['admin_reply']}</p>
                </div>";
            } else {
                echo "<form class='reply-form' action='manage_hr_queries.php' method='POST'>
                    <input type='hidden' name='query_id' value='{$q['query_id']}'>
                    <textarea name='admin_reply' rows='2' placeholder='Type your response here...' required></textarea>
                    <button type='submit' class='reply-btn'>Send Reply & Mark Resolved</button>
                </form>";
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
