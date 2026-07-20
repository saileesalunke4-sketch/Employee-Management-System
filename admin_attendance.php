<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
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
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

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
                                <a href='handle_regularization.php?id={$rr['request_id']}&action=approved&redirect=admin_attendance.php&csrf=".csrf_token()."' class='approve-btn'>Approve</a>
                                <a href='handle_regularization.php?id={$rr['request_id']}&action=rejected&redirect=admin_attendance.php&csrf=".csrf_token()."' class='approve-btn' style='background:#dc2626;margin-left:6px;'>Reject</a>
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

        <!-- Month Filter + Download -->
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;">
            <div class="field" style="margin:0;"><label>Filter Month</label>
                <input type="month" name="ts_month" value="<?php echo htmlspecialchars(isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m')); ?>" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <button type="submit" class="submit-btn" style="margin:0;padding:8px 20px;">Filter</button>
            <a href="export_attendance.php?ts_month=<?php echo htmlspecialchars(isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m')); ?>"
               style="display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
               📥 Download Excel
            </a>
        </form>

        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th></tr></thead>
            <tbody>
            <?php
                // SECURITY: ts_month must match YYYY-MM before it's used in
                // SQL — it was going straight into the query unvalidated,
                // so a crafted ts_month value could break out of the quotes.
                $ts_month = isset($_GET['ts_month']) ? $_GET['ts_month'] : date('Y-m');
                if(!preg_match('/^\d{4}-\d{2}$/', $ts_month)) $ts_month = date('Y-m');
                $ts_year  = substr($ts_month,0,4);
                $ts_mon   = substr($ts_month,5,2);
                $res=mysqli_query($conn,"SELECT e.first_name,e.last_name,a.date,a.check_in,a.check_out,a.status FROM attendance a JOIN employees e ON a.emp_id=e.emp_id WHERE YEAR(a.date)='$ts_year' AND MONTH(a.date)='$ts_mon' ORDER BY a.date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $type=($row['status']=='work_from_home')?"<span class='pill blue'>&#127968; WFH</span>":"<span class='pill green'>&#127970; Office</span>";
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td>".ucfirst(str_replace('_',' ',$row['status']))."</td><td>{$type}</td></tr>";
                }
            ?>
            </tbody>
        </table>
        </div>

        <h3 class="section-title" style="margin-top:28px;">Late Coming Today</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Late By</th></tr></thead>
            <tbody>
            <?php
                $late=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id WHERE a.date=CURDATE() AND a.check_in>'09:00:00' AND a.status!='work_from_home' ORDER BY a.check_in DESC");
                if(mysqli_num_rows($late)>0){
                    while($row=mysqli_fetch_assoc($late)){
                        $secs=strtotime($row['check_in'])-strtotime('09:00:00');
                        $h=floor($secs/3600); $m=floor(($secs%3600)/60);
                        $str=$h>0?"{$h}h {$m}m late":"{$m}m late";
                        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td style='color:#ef4444;font-weight:bold;'>{$row['check_in']}</td><td><span class='pill red'>{$str}</span></td></tr>";
                    }
                } else echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No late comers today</td></tr>";
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
                if(mysqli_num_rows($wfh)>0){ while($row=mysqli_fetch_assoc($wfh)) echo "<tr><td>&#127968; {$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td></tr>"; }
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
