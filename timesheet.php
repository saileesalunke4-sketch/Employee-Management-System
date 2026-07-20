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
$page_title = "Timesheet";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Timesheet - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.completed{background:#dcfce7;color:#16a34a;}
.status-pill.in_progress{background:#fef3c7;color:#d97706;}
.skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">&#9200; My Timesheet</h3>
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;">
            <div class="field" style="margin:0;"><label>Filter Month</label>
                <input type="month" name="ts_month" value="<?php echo htmlspecialchars(isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m'));?>" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;">
            </div>
            <button type="submit" class="submit-btn" style="margin:0;padding:8px 20px;">Filter</button>
            <a id="excel_link" href="export_timesheet.php?ts_month=<?php echo htmlspecialchars(isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m')); ?>" 
               style="display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
               📥 Download Excel
            </a>
        </form>
        <?php
        // SECURITY: ts_month must match YYYY-MM before it's used in SQL —
        // it was going straight into the query unvalidated.
        $ts_month=isset($_GET['ts_month'])?$_GET['ts_month']:date('Y-m');
        if(!preg_match('/^\d{4}-\d{2}$/', $ts_month)) $ts_month = date('Y-m');
        $ts_year=substr($ts_month,0,4); $ts_mon=substr($ts_month,5,2);
        $ts_res=mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND YEAR(date)='$ts_year' AND MONTH(date)='$ts_mon' ORDER BY date ASC");
        $total_hrs=0; $rows_ts=[];
        while($r=mysqli_fetch_assoc($ts_res)){
            $hrs=0;
            if($r['check_in']&&$r['check_out']){ $in=strtotime($r['check_in']); $out=strtotime($r['check_out']); if($out>$in) $hrs=($out-$in)/3600; }
            $r['computed_hrs']=$hrs; $total_hrs+=$hrs; $rows_ts[]=$r;
        }
        ?>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
            <div style="background:#eff6ff;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">Total Days</p><p style="font-size:22px;font-weight:700;color:#1a3a6e;margin:4px 0;"><?php echo count($rows_ts);?></p></div>
            <div style="background:#dcfce7;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">Total Hours</p><p style="font-size:22px;font-weight:700;color:#16a34a;margin:4px 0;"><?php echo number_format($total_hrs,1);?> hrs</p></div>
            <div style="background:#fef3c7;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">Avg Hrs/Day</p><p style="font-size:22px;font-weight:700;color:#d97706;margin:4px 0;"><?php echo count($rows_ts)>0?number_format($total_hrs/count($rows_ts),1):'0';?> hrs</p></div>
        </div>
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Day</th><th>Check In</th><th>Check Out</th><th>Hours</th><th>Status</th><th>Overtime</th></tr></thead>
            <tbody>
            <?php
            if(empty($rows_ts)) echo "<tr><td colspan='7' style='text-align:center;color:#9ca3af;'>No records for selected month.</td></tr>";
            foreach($rows_ts as $row){
                $hrs=$row['computed_hrs']; $ot=max(0,$hrs-8);
                $day_name=date('D',strtotime($row['date']));
                $pill_map=['present'=>'approved','late'=>'pending','half_day'=>'pending','work_from_home'=>'approved','absent'=>'rejected'];
                $pill=$pill_map[$row['status']]??'pending';
                echo "<tr><td>{$row['date']}</td><td>{$day_name}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td>
                <td>".($hrs>0?number_format($hrs,1)." hrs":"-")."</td>
                <td><span class='status-pill $pill'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                <td>".($ot>0?"<span style='color:#16a34a;font-weight:600;'>+".number_format($ot,1)." hrs</span>":"-")."</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>

</div>

</div>
</div>

<?php include 'common_js.php'; ?>
</body>
</html>
