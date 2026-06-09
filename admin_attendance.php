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
    <div class="form-card">
        <h3 class="section-title">Attendance Records</h3>

        <!-- Month Filter + Download -->
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;">
            <div class="field" style="margin:0;"><label>Filter Month</label>
                <input type="month" name="ts_month" value="<?php echo isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m'); ?>" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <button type="submit" class="submit-btn" style="margin:0;padding:8px 20px;">Filter</button>
            <a href="export_attendance.php?ts_month=<?php echo isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m'); ?>"
               style="display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
               📥 Download Excel
            </a>
        </form>

        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th></tr></thead>
            <tbody>
            <?php
                $ts_month = isset($_GET['ts_month']) ? $_GET['ts_month'] : date('Y-m');
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
