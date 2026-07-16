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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
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
<div class="dashboard emp-theme">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>
<div class="app-content">

<div class="section active">

    <?php
    $today_att = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND date=CURDATE()"));

    // ---- Day-ring progress calc (shift assumed 9:00 - 18:00 = 540 min) ----
    $shift_minutes = 540;
    $elapsed = 0;
    if($today_att && !empty($today_att['check_in'])){
        $start_ts = strtotime(date('Y-m-d').' '.$today_att['check_in']);
        $end_ts   = !empty($today_att['check_out']) ? strtotime(date('Y-m-d').' '.$today_att['check_out']) : time();
        $elapsed  = max(0, round(($end_ts - $start_ts)/60));
    }
    $pct = max(0, min(100, round(($elapsed/$shift_minutes)*100)));
    $radius = 50; $circumference = 2*M_PI*$radius;
    $dashoffset = $circumference - ($pct/100)*$circumference;
    $hrs = floor($elapsed/60); $mins = $elapsed%60;

    $stat_att   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE emp_id='$emp_id'"))['t'];
    $stat_leave = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id'"))['t'];
    $stat_task  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE emp_id='$emp_id'"))['t'];
    $stat_pend  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND status='pending'"))['t'];
    $stat_wfh   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE emp_id='$emp_id' AND status='work_from_home' AND MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE())"))['t'];
    ?>

    <!-- Hero: greeting + day ring -->
    <div class="hero-card">
        <div class="hero-left">
            <div class="hero-eyebrow">Good <?php echo (date('H')<12?'Morning':(date('H')<17?'Afternoon':'Evening')); ?>, <?php echo date('l, d M Y'); ?></div>
            <div class="hero-name">Hi <?php echo htmlspecialchars($emp['first_name']); ?></div>
            <div class="hero-sub"><?php echo htmlspecialchars($emp['designation'] ?? ''); ?> · ID <?php echo htmlspecialchars($emp['employee_code'] ?? $emp_id); ?></div>
            <div class="hero-actions">
                <?php if(!$today_att || empty($today_att['check_in'])): ?>
                    <a href="my_attendance.php" class="hero-btn solid"><?php echo ems_icon('check-circle',15); ?> Check In</a>
                <?php elseif(empty($today_att['check_out'])): ?>
                    <a href="my_attendance.php" class="hero-btn solid"><?php echo ems_icon('clock',15); ?> Check Out</a>
                <?php else: ?>
                    <span class="hero-btn"><?php echo ems_icon('check-circle',15); ?> Day Completed</span>
                <?php endif; ?>
                <a href="my_leaves.php" class="hero-btn"><?php echo ems_icon('leaf',15); ?> Apply Leave</a>
                <a href="hr_requests.php" class="hero-btn"><?php echo ems_icon('inbox',15); ?> Raise HR Query</a>
            </div>
        </div>
        <div class="day-ring-wrap">
            <div class="day-ring">
                <svg width="118" height="118" viewBox="0 0 120 120">
                    <circle class="ring-bg" cx="60" cy="60" r="<?php echo $radius; ?>"/>
                    <circle class="ring-fill" cx="60" cy="60" r="<?php echo $radius; ?>"
                        stroke-dasharray="<?php echo $circumference; ?>"
                        stroke-dashoffset="<?php echo $dashoffset; ?>"/>
                </svg>
                <div class="day-ring-label">
                    <span class="t"><?php echo $hrs.'h '.$mins.'m'; ?></span>
                    <span class="s">of 9h shift</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Today timeline -->
    <div class="timeline-card">
        <h3 style="display:flex;align-items:center;gap:8px;"><?php echo ems_icon('clock',17); ?> Today at a Glance</h3>
        <div class="timeline-track">
            <div class="timeline-fill" style="width:<?php echo $pct; ?>%;"></div>
            <div class="timeline-point" style="left:<?php echo $pct; ?>%;"></div>
        </div>
        <div class="timeline-marks">
            <span><b><?php echo $today_att['check_in'] ?? '—'; ?></b>Check-In</span>
            <span style="text-align:center;"><b><?php echo $pct; ?>%</b>Shift Progress</span>
            <span style="text-align:right;"><b><?php echo $today_att['check_out'] ?? '6:00 PM (exp.)'; ?></b>Check-Out</span>
        </div>
    </div>

    <!-- Stats -->
    <div class="stat-tiles">
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('calendar',20); ?></div><div class="label">My Attendance</div><div class="val"><?php echo $stat_att; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('leaf',20); ?></div><div class="label">My Leaves</div><div class="val"><?php echo $stat_leave; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('check-square',20); ?></div><div class="label">My Tasks</div><div class="val"><?php echo $stat_task; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('clock',20); ?></div><div class="label">Pending Leaves</div><div class="val"><?php echo $stat_pend; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('building',20); ?></div><div class="label">WFH This Month</div><div class="val"><?php echo $stat_wfh; ?></div></div>
    </div>

    <!-- Quick actions -->
    <div class="qa-grid">
        <a href="my_leaves.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('leaf',18); ?></span>Apply Leave</a>
        <a href="hr_requests.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('inbox',18); ?></span>Raise HR Query</a>
        <a href="my_salary.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('wallet',18); ?></span>View Payslip</a>
        <a href="my_tasks.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('check-square',18); ?></span>My Tasks</a>
    </div>

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
        <div class="timeline-card" style="margin-top:0;">
            <h3 style="color:var(--role-accent);display:flex;align-items:center;gap:8px;"><?php echo ems_icon('bar-chart',17); ?> Monthly Attendance</h3>
            <canvas id="attendanceChart"></canvas>
        </div>
        <div class="timeline-card" style="margin-top:0;">
            <h3 style="color:var(--role-accent);display:flex;align-items:center;gap:8px;"><?php echo ems_icon('bar-chart',17); ?> Monthly Leaves</h3>
            <canvas id="leaveChart"></canvas>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <?php
    $uph=mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
    $uphrows=[]; while($u=mysqli_fetch_assoc($uph)) $uphrows[]=$u;
    if(!empty($uphrows)):
    ?>
    <div class="timeline-card">
        <h3 style="display:flex;align-items:center;gap:8px;"><?php echo ems_icon('flag',17); ?> Upcoming Holidays</h3>
        <div class="hscroll">
        <?php foreach($uphrows as $uh):
            $ht=$uh['holiday_type']??'National';
            $cc=['National'=>'#1d4ed8','Festival'=>'#d97706','State'=>'#16a34a']; $c=$cc[$ht]??'#6b7280';
        ?>
            <div class="hscroll-item" style="border-left-color:<?php echo $c;?>;">
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
</div>

<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
document.addEventListener('DOMContentLoaded', function(){
    new Chart(document.getElementById('attendanceChart'),{type:'bar',data:{labels:months,datasets:[{label:'Present',data:<?php echo json_encode($att_data);?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
    new Chart(document.getElementById('leaveChart'),{type:'bar',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo json_encode($leave_data);?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}});
});
</script>
<?php include 'common_js.php'; ?>
</body>
</html>