<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Attendance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Attendance - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">
    <?php if(isset($_GET['rr_msg']) && in_array($_GET['rr_msg'], ['approved','rejected'])):
        $rr_is_approved = $_GET['rr_msg'] === 'approved';
        $rr_emp  = htmlspecialchars($_GET['rr_emp'] ?? '');
        $rr_date = htmlspecialchars($_GET['rr_date'] ?? '');
    ?>
    <div style="background:<?php echo $rr_is_approved?'#dcfce7':'#fee2e2'; ?>;border:1px solid <?php echo $rr_is_approved?'#86efac':'#fca5a5'; ?>;color:<?php echo $rr_is_approved?'#166534':'#7f1d1d'; ?>;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-size:14px;">
        <?php echo $rr_is_approved ? '✅' : '❌'; ?>
        Regularization request for <b><?php echo $rr_emp; ?></b> (<?php echo $rr_date; ?>) has been
        <b><?php echo ucfirst($_GET['rr_msg']); ?></b>.
    </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Pending Regularization Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Requested Check In</th><th>Requested Check Out</th><th>Requested Status</th><th>Reason</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $rr_res = mysqli_query($conn, "SELECT r.*, e.first_name, e.last_name FROM regularization_requests r JOIN employees e ON r.emp_id=e.emp_id WHERE r.status='pending' ORDER BY r.request_id DESC");
                if(mysqli_num_rows($rr_res) === 0){
                    echo "<tr><td colspan='7' style='text-align:center;color:#9ca3af;padding:16px;'>No pending regularization requests.</td></tr>";
                } else {
                    while($rr = mysqli_fetch_assoc($rr_res)){
                        echo "<tr>
                            <td>{$rr['first_name']} {$rr['last_name']}</td>
                            <td>{$rr['att_date']}</td>
                            <td>".($rr['requested_check_in'] ?: '-')."</td>
                            <td>".($rr['requested_check_out'] ?: '-')."</td>
                            <td>".ucfirst(str_replace('_',' ',$rr['requested_status']))."</td>
                            <td>".htmlspecialchars($rr['reason'])."</td>
                            <td>
                                <a href='handle_regularization.php?id={$rr['request_id']}&action=approved&redirect=sa_attendance.php&csrf=".csrf_token()."' class='approve-btn'>Approve</a>
                                <a href='handle_regularization.php?id={$rr['request_id']}&action=rejected&redirect=sa_attendance.php&csrf=".csrf_token()."' class='approve-btn' style='background:#dc2626;margin-left:6px;'>Reject</a>
                            </td>
                        </tr>";
                    }
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="form-card">
        <h3 class="section-title">Attendance Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id ORDER BY a.date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $type=($row['status']=='work_from_home')?"<span class='pill blue'>WFH</span>":"<span class='pill green'>Office</span>";
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td>".ucfirst(str_replace('_',' ',$row['status']))."</td><td>{$type}</td><td><a href='regularize.php?id={$row['attendance_id']}' class='approve-btn'>Regularize</a></td></tr>";
                }
            ?>
            </tbody>
        </table>
        </div>

        <h3 class="section-title" style="margin-top:28px;">Work From Home Today</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th></tr></thead>
            <tbody>
            <?php
                $wfh=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id WHERE a.status='work_from_home' AND a.date=CURDATE()");
                if(mysqli_num_rows($wfh)>0){ while($row=mysqli_fetch_assoc($wfh)) echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td></tr>"; }
                else echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No WFH employees today</td></tr>";
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
