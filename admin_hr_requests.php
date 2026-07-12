<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "HR Process Requests";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>HR Process Requests - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
</style>
</head>
<body>
<div class="dashboard">
<?php if($role === 'admin'){ include('sidebar_admin.php'); } else { include('sidebar_sa.php'); } ?>
<div class="main-content">
<?php if($role === 'admin'){ include('topbar_admin.php'); } else { include('topbar_sa.php'); } ?>

<div class="section active">

    <?php if(isset($_GET['hr_msg']) && in_array($_GET['hr_msg'], ['approved','rejected'])):
        $hr_is_approved = $_GET['hr_msg'] === 'approved';
        $hr_emp  = htmlspecialchars($_GET['hr_emp'] ?? '');
        $hr_type = htmlspecialchars($_GET['hr_type'] ?? '');
    ?>
    <div style="background:<?php echo $hr_is_approved?'#dcfce7':'#fee2e2'; ?>;border:1px solid <?php echo $hr_is_approved?'#86efac':'#fca5a5'; ?>;color:<?php echo $hr_is_approved?'#166534':'#7f1d1d'; ?>;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-size:14px;">
        <?php echo $hr_is_approved ? '✅' : '❌'; ?>
        <b><?php echo $hr_type; ?></b> request for <b><?php echo $hr_emp; ?></b> has been
        <b><?php echo ucfirst($_GET['hr_msg']); ?></b>.
    </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Pending HR Process Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Type</th><th>Current</th><th>Requested</th><th>Reason</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $hr_res = mysqli_query($conn, "SELECT h.*, e.first_name, e.last_name FROM hr_process_requests h JOIN employees e ON h.emp_id=e.emp_id WHERE h.status='pending' ORDER BY h.request_id DESC");
                if(mysqli_num_rows($hr_res) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:#9ca3af;padding:16px;'>No pending HR process requests.</td></tr>";
                } else {
                    while($hr = mysqli_fetch_assoc($hr_res)){
                        echo "<tr>
                            <td>{$hr['first_name']} {$hr['last_name']}</td>
                            <td>".htmlspecialchars($hr['request_type'])."</td>
                            <td>".htmlspecialchars($hr['current_value'])."</td>
                            <td>".htmlspecialchars($hr['requested_value'])."</td>
                            <td>".htmlspecialchars($hr['reason'])."</td>
                            <td>
                                <a href='handle_hr_request.php?id={$hr['request_id']}&action=approved&csrf=".csrf_token()."' class='approve-btn'>Approve</a>
                                <a href='handle_hr_request.php?id={$hr['request_id']}&action=rejected&csrf=".csrf_token()."' class='approve-btn' style='background:#dc2626;margin-left:6px;'>Reject</a>
                            </td>
                        </tr>";
                    }
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">All HR Process Requests (History)</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Type</th><th>Current</th><th>Requested</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php
                $hist_res = mysqli_query($conn, "SELECT h.*, e.first_name, e.last_name FROM hr_process_requests h JOIN employees e ON h.emp_id=e.emp_id WHERE h.status!='pending' ORDER BY h.request_id DESC LIMIT 50");
                if(mysqli_num_rows($hist_res) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:#9ca3af;padding:16px;'>No history yet.</td></tr>";
                } else {
                    while($hr = mysqli_fetch_assoc($hist_res)){
                        echo "<tr>
                            <td>{$hr['first_name']} {$hr['last_name']}</td>
                            <td>".htmlspecialchars($hr['request_type'])."</td>
                            <td>".htmlspecialchars($hr['current_value'])."</td>
                            <td>".htmlspecialchars($hr['requested_value'])."</td>
                            <td><span class='status-pill {$hr['status']}'>".ucfirst($hr['status'])."</span></td>
                            <td>".date('d M Y', strtotime($hr['created_at']))."</td>
                        </tr>";
                    }
                }
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
