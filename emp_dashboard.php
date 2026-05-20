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
$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard - EMS</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.completed{background:#dcfce7;color:#16a34a;}
.status-pill.in_progress{background:#fef3c7;color:#d97706;}
.skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
.hl-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.hl-badge.National{background:#dbeafe;color:#1d4ed8;}
.hl-badge.Festival{background:#fef3c7;color:#d97706;}
.hl-badge.State{background:#dcfce7;color:#16a34a;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <?php
    $today_att = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND date=CURDATE()"));
    ?>

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;">
        <div class="card"><h3>My Attendance</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE emp_id='$emp_id'"))['t'];?></p></div>
        <div class="card"><h3>My Leaves</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id'"))['t'];?></p></div>
        <div class="card"><h3>My Tasks</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE emp_id='$emp_id'"))['t'];?></p></div>
        <div class="card"><h3>Pending Leaves</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND status='pending'"))['t'];?></p></div>
        <div class="card"><h3>WFH This Month</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE emp_id='$emp_id' AND status='work_from_home' AND MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE())"))['t'];?></p></div>
    </div>

    <!-- Check In / Out -->
    <?php if($today_att): ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px;">
        <div style="background:#eff6ff;border-radius:10px;padding:20px;text-align:center;">
            <p style="color:#6b7280;font-size:12px;margin:4px 0;">Today Check-In</p>
            <p style="font-size:22px;font-weight:700;color:#1d4ed8;"><?php echo $today_att['check_in'];?></p>
        </div>
        <div style="background:#f0fdf4;border-radius:10px;padding:20px;text-align:center;">
            <p style="color:#6b7280;font-size:12px;margin:4px 0;">Today Check-Out</p>
            <p style="font-size:22px;font-weight:700;color:#16a34a;"><?php echo $today_att['check_out'];?></p>
        </div>
    </div>
    <?php else: ?>
    <div style="background:#fef3c7;border-radius:10px;padding:16px;margin-top:20px;text-align:center;color:#92400e;font-size:14px;">
        &#9888; Attendance not marked yet for today!
    </div>
    <?php endif; ?>

    <!-- Charts -->
    <?php
    $att_data=array_fill(0,12,0);
    $att_res=mysqli_query($conn,"SELECT MONTH(date) as mon,COUNT(*) as cnt FROM attendance WHERE emp_id='$emp_id' AND status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
    while($r=mysqli_fetch_assoc($att_res)) $att_data[$r['mon']-1]=$r['cnt'];
    $leave_data=array_fill(0,12,0);
    $leave_res=mysqli_query($conn,"SELECT MONTH(from_date) as mon,COUNT(*) as cnt FROM leaves WHERE emp_id='$emp_id' AND YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
    while($r=mysqli_fetch_assoc($leave_res)) $leave_data[$r['mon']-1]=$r['cnt'];
    ?>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
        <div style="background:#fff;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
            <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Attendance</h3>
            <canvas id="attendanceChart"></canvas>
        </div>
        <div style="background:#fff;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
            <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Leaves</h3>
            <canvas id="leaveChart"></canvas>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <?php
    $uph=mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
    $uphrows=[]; while($u=mysqli_fetch_assoc($uph)) $uphrows[]=$u;
    if(!empty($uphrows)):
    ?>
    <div style="background:#fff;border-radius:10px;padding:20px;margin-top:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
        <h3 style="font-size:14px;color:#60a5fa;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #eee;">&#127974; Upcoming Holidays</h3>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
        <?php foreach($uphrows as $uh):
            $ht=$uh['holiday_type']??'National';
            $cc=['National'=>'#1d4ed8','Festival'=>'#d97706','State'=>'#16a34a']; $c=$cc[$ht]??'#6b7280';
        ?>
            <div style="border-left:4px solid <?php echo $c;?>;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                <p style="font-size:11px;color:#9ca3af;margin:0;"><?php echo date('D, d M',strtotime($uh['holiday_date']));?></p>
                <p style="font-size:13px;font-weight:700;color:#1a1a2e;margin:4px 0;"><?php echo $uh['holiday_name'];?></p>
                <span class="hl-badge <?php echo $ht;?>"><?php echo $ht;?></span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

</div>
</div>
</div>

<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
new Chart(document.getElementById('attendanceChart'),{type:'bar',data:{labels:months,datasets:[{label:'Present',data:<?php echo json_encode($att_data);?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
new Chart(document.getElementById('leaveChart'),{type:'bar',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo json_encode($leave_data);?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
</script>
<?php include 'common_js.php'; ?>
</body>
</html>