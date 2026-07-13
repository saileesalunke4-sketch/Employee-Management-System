<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

// Holiday setup
mysqli_query($conn,"CREATE TABLE IF NOT EXISTS `holidays` (`id` INT NOT NULL AUTO_INCREMENT,`holiday_name` VARCHAR(200),`holiday_date` DATE,`holiday_type` VARCHAR(50) DEFAULT 'National',PRIMARY KEY(`id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$col_check = mysqli_query($conn,"SHOW COLUMNS FROM holidays LIKE 'holiday_type'");
if(mysqli_num_rows($col_check)==0){ mysqli_query($conn,"ALTER TABLE holidays ADD COLUMN holiday_type VARCHAR(50) DEFAULT 'National'"); }
$hcount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as cnt FROM holidays"));
if($hcount['cnt']==0){
    $y=date('Y');
    $defaults=[["Republic Day","$y-01-26","National"],["Holi","$y-03-14","Festival"],["Gudi Padwa","$y-03-30","State"],["Good Friday","$y-04-18","National"],["Dr. Ambedkar Jayanti","$y-04-14","National"],["Maharashtra Day","$y-05-01","State"],["Independence Day","$y-08-15","National"],["Ganesh Chaturthi","$y-08-27","Festival"],["Gandhi Jayanti","$y-10-02","National"],["Diwali","$y-10-20","Festival"],["Diwali Laxmi Puja","$y-10-21","Festival"],["Gurunanak Jayanti","$y-11-05","National"],["Christmas","$y-12-25","National"]];
    foreach($defaults as $h){ $nm=mysqli_real_escape_string($conn,$h[0]); mysqli_query($conn,"INSERT INTO holidays (holiday_name,holiday_date,holiday_type) VALUES ('$nm','$h[1]','$h[2]')"); }
}
$h_res = mysqli_query($conn,"SELECT holiday_date,holiday_name,holiday_type FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())");
$holiday_map = [];
while($hrow=mysqli_fetch_assoc($h_res)) $holiday_map[$hrow['holiday_date']] = ['name'=>$hrow['holiday_name'],'type'=>($hrow['holiday_type']??'National')];
$holidays_json = json_encode($holiday_map);

// Chart data
$att_data = array_fill(0,12,0);
$att_res = mysqli_query($conn,"SELECT MONTH(date) as mon,COUNT(*) as cnt FROM attendance WHERE status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
while($r=mysqli_fetch_assoc($att_res)) $att_data[$r['mon']-1]=$r['cnt'];
$leave_data = array_fill(0,12,0);
$leave_res = mysqli_query($conn,"SELECT MONTH(from_date) as mon,COUNT(*) as cnt FROM leaves WHERE YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
while($r=mysqli_fetch_assoc($leave_res)) $leave_data[$r['mon']-1]=$r['cnt'];

$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard - EMS</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'common_styles.php'; ?>
<style>
body { overflow: hidden; }
* { scrollbar-width: none; }
*::-webkit-scrollbar { display: none; }
</style>
</head>
<body>
<div class="dashboard admin-theme">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">

    <?php
    $total_emp   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM users WHERE role='employee'"))['t'];
    $present_tdy = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status='present'"))['t'];
    $on_leave    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved'"))['t'];
    $pend_tasks  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"))['t'];
    $comp_tasks  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"))['t'];
    $pend_leaves = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='pending'"))['t'];
    $checked_pct = $total_emp>0 ? round(($present_tdy/$total_emp)*100) : 0;
    ?>

    <!-- Hero -->
    <div class="hero-card">
        <div class="hero-left">
            <div class="hero-eyebrow">Team Control Center · <?php echo date('l, d M Y'); ?></div>
            <div class="hero-name">Hi <?php echo htmlspecialchars($_SESSION['user']['name']); ?> 👋</div>
            <div class="hero-sub"><?php echo $present_tdy; ?> of <?php echo $total_emp; ?> team members checked in today</div>
            <div class="hero-actions">
                <a href="admin_leaves.php" class="hero-btn solid">🌿 Pending Leaves (<?php echo $pend_leaves; ?>)</a>
                <a href="add_employee.php" class="hero-btn">➕ Add Employee</a>
                <a href="announcements.php" class="hero-btn">📣 Post Announcement</a>
            </div>
        </div>
        <div class="day-ring-wrap">
            <div class="day-ring">
                <svg width="118" height="118" viewBox="0 0 120 120">
                    <circle class="ring-bg" cx="60" cy="60" r="50"/>
                    <circle class="ring-fill" cx="60" cy="60" r="50"
                        stroke-dasharray="<?php echo 2*M_PI*50; ?>"
                        stroke-dashoffset="<?php echo (2*M_PI*50) - ($checked_pct/100)*(2*M_PI*50); ?>"/>
                </svg>
                <div class="day-ring-label">
                    <span class="t"><?php echo $checked_pct; ?>%</span>
                    <span class="s">checked in</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="stat-tiles">
        <div class="stat-tile"><div class="ico">👥</div><div class="label">Total Employees</div><div class="val"><?php echo $total_emp; ?></div></div>
        <div class="stat-tile"><div class="ico">✅</div><div class="label">Present Today</div><div class="val"><?php echo $present_tdy; ?></div></div>
        <div class="stat-tile"><div class="ico">🌿</div><div class="label">On Leave</div><div class="val"><?php echo $on_leave; ?></div></div>
        <div class="stat-tile"><div class="ico">⏳</div><div class="label">Pending Tasks</div><div class="val"><?php echo $pend_tasks; ?></div></div>
        <div class="stat-tile"><div class="ico">🏁</div><div class="label">Completed Tasks</div><div class="val"><?php echo $comp_tasks; ?></div></div>
    </div>

    <!-- Quick actions -->
    <div class="qa-grid">
        <a href="admin_leaves.php" class="qa-btn"><span class="qa-ico">🌿</span>Review Leaves</a>
        <a href="all_employees.php" class="qa-btn"><span class="qa-ico">👥</span>All Employees</a>
        <a href="admin_hr_requests.php" class="qa-btn"><span class="qa-ico">📋</span>HR Requests</a>
        <a href="admin_rules.php" class="qa-btn"><span class="qa-ico">📜</span>Rules & Regulations</a>
    </div>

    <!-- Real-Time Attendance Status Widget -->
    <div class="timeline-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="margin:0;">🟢 Live Attendance Status — Today</h3>
            <span id="attLastUpdated" style="font-size:11px;color:#9ca3af;"></span>
        </div>

        <div id="attSummaryRow" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;"></div>

        <div style="max-height:340px;overflow-y:auto;">
            <table class="emp-table" style="width:100%;">
                <thead><tr><th>Employee</th><th>Department</th><th>Status</th><th>Check In</th></tr></thead>
                <tbody id="attStatusBody">
                    <tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
        <div class="timeline-card" style="margin-top:0;">
            <h3 style="color:var(--role-accent);">📊 Monthly Attendance</h3>
            <canvas id="adminAttChart"></canvas>
        </div>
        <div class="timeline-card" style="margin-top:0;">
            <h3 style="color:var(--role-accent);">📊 Monthly Leave Requests</h3>
            <canvas id="adminLeaveChart"></canvas>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <?php
    $uph = mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
    $uphrows = []; while($u=mysqli_fetch_assoc($uph)) $uphrows[]=$u;
    if(!empty($uphrows)):
    ?>
    <div class="timeline-card">
        <h3>🏖 Upcoming Holidays</h3>
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

<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
new Chart(document.getElementById('adminAttChart'),{type:'bar',data:{labels:months,datasets:[{label:'Present',data:<?php echo json_encode($att_data);?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});
new Chart(document.getElementById('adminLeaveChart'),{type:'bar',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo json_encode($leave_data);?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}});

// ===== LIVE ATTENDANCE STATUS WIDGET =====
const attStatusColors = {
    present:         { bg:'#dcfce7', color:'#16a34a', label:'Present' },
    late:            { bg:'#fef3c7', color:'#d97706', label:'Late' },
    half_day:        { bg:'#fef3c7', color:'#d97706', label:'Half Day' },
    work_from_home:  { bg:'#dbeafe', color:'#1d4ed8', label:'WFH' },
    not_checked_in:  { bg:'#fee2e2', color:'#dc2626', label:'Not Checked-in' }
};

function loadAttendanceStatus(){
    fetch('get_attendance_status.php')
        .then(r => r.json())
        .then(data => {
            if(data.error) return;

            // Summary pills
            const counts = data.counts;
            const summaryHtml = Object.keys(attStatusColors).map(key => {
                const c = attStatusColors[key];
                const n = counts[key] || 0;
                return `<div style="background:${c.bg};color:${c.color};padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">${c.label}: ${n}</div>`;
            }).join('');
            document.getElementById('attSummaryRow').innerHTML = summaryHtml;

            // Employee rows
            const body = document.getElementById('attStatusBody');
            if(!data.employees.length){
                body.innerHTML = '<tr><td colspan="4" style="text-align:center;color:#9ca3af;padding:20px;">No employees found.</td></tr>';
            } else {
                body.innerHTML = data.employees.map(emp => {
                    const c = attStatusColors[emp.status] || attStatusColors.not_checked_in;
                    return `<tr>
                        <td>${emp.name}</td>
                        <td>${emp.department || '-'}</td>
                        <td><span style="background:${c.bg};color:${c.color};padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;">${c.label}</span></td>
                        <td>${emp.check_in || '-'}</td>
                    </tr>`;
                }).join('');
            }

            document.getElementById('attLastUpdated').textContent = 'Last updated: ' + data.as_of;
        })
        .catch(err => console.error('Attendance status fetch failed', err));
}

loadAttendanceStatus();               // initial load
setInterval(loadAttendanceStatus, 20000); // auto-refresh every 20 seconds
</script>
<?php include 'common_js.php'; ?>
</body>
</html>