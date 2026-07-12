<?php
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
<div class="dashboard">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">

    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;">
        <div class="card"><h3>Total Employees</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM users WHERE role='employee'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
        <div class="card"><h3>Present Today</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status='present'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
        <div class="card"><h3>On Leave</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
        <div class="card"><h3>Pending Tasks</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
        <div class="card"><h3>Completed Tasks</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
    </div>

    <!-- Real-Time Attendance Status Widget -->
    <div style="background:#fff;border-radius:10px;padding:20px;margin-top:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #eee;">
            <h3 style="font-size:14px;color:#60a5fa;margin:0;">🟢 Live Attendance Status — Today</h3>
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
        <div style="background:#fff;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
            <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Attendance</h3>
            <canvas id="adminAttChart"></canvas>
        </div>
        <div style="background:#fff;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
            <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Leave Requests</h3>
            <canvas id="adminLeaveChart"></canvas>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <?php
    $uph = mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
    $uphrows = []; while($u=mysqli_fetch_assoc($uph)) $uphrows[]=$u;
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