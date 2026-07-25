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
$page_title = "Work From Home";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Work From Home - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard emp-theme">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>
<div class="app-content">

<div class="section active">

    <?php if(isset($_GET['sent'])): ?>
        <div class="form-card" style="background:#f0fdf4;border:1px solid #86efac;margin-bottom:16px;">
            WFH request submitted — waiting for admin approval.
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Request Work From Home</h3>
        <p style="font-size:12.5px;color:var(--text-3,#9aa1ac);margin-top:-6px;">
            This is for planning ahead — once approved, that day's attendance is automatically marked as Work From Home for you.
            (Need to work from home <em>today</em> without planning ahead? Just check the WFH box on the <a href="my_attendance.php" style="color:var(--role-accent,#4F46E5);font-weight:600;">Attendance</a> page instead — no approval needed for that.)
        </p>
        <form action="save_wfh_request.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Date</label><input type="date" name="wfh_date" min="<?php echo date('Y-m-d'); ?>" required></div>
                <div class="field" style="grid-column:1/-1"><label>Reason</label><textarea name="reason" rows="3" placeholder="Why are you requesting to work from home?" required></textarea></div>
            </div>
            <button type="submit" class="submit-btn">Submit Request</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My WFH Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res = mysqli_query($conn, "SELECT * FROM wfh_requests WHERE emp_id='$emp_id' ORDER BY request_id DESC");
                if(mysqli_num_rows($res) === 0){
                    echo "<tr><td colspan='4' style='text-align:center;color:var(--text-3,#9aa1ac);'>No WFH requests yet.</td></tr>";
                }
                while($row = mysqli_fetch_assoc($res)){
                    $can_cancel = ($row['status'] === 'pending');
                    echo "<tr>
                        <td>{$row['wfh_date']}</td>
                        <td>".htmlspecialchars($row['reason'])."</td>
                        <td><span class='status-pill {$row['status']}'>".ucfirst($row['status'])."</span></td>
                        <td>" . ($can_cancel
                            ? "<a href='cancel_wfh_request.php?id={$row['request_id']}&csrf=".urlencode(csrf_token())."' onclick=\"return confirm('Cancel this WFH request?');\" style='color:#dc2626;font-size:12px;font-weight:600;text-decoration:none;'>Cancel</a>"
                            : "<span style='color:#9ca3af;font-size:12px;'>-</span>") . "</td>
                    </tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</div>
</div>
</div>
<style>
.status-pill{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.cancelled{background:#f3f4f6;color:#6b7280;}
</style>
<?php include 'common_js.php'; ?>
</body>
</html>
