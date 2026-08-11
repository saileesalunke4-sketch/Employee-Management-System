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

// Same fix as emp_dashboard.php — one shared Y-axis max keeps both charts'
// gridlines aligned instead of each auto-scaling to its own data.
$charts_shared_max = max(1, max($att_data), max($leave_data)) + 1;

$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin Dashboard - EMS</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard admin-theme">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>
<div class="app-content">

<div class="section active">

    <?php
        // BUGFIX: was counting FROM users WHERE role='employee' — if any
        // employee's linked user record ever drifts out of sync with role
        // 'employee' (e.g. after a role change), this undercounts real
        // employees vs. the `employees` table itself. Count the actual
        // employees table instead — it's the authoritative employee registry.
        $total_emp   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM employees"))['t'];
    // BUGFIX (EMS-DASH-016): this used to only count status='present', so
    // anyone who checked in Late, Half-Day, or was on approved WFH wasn't
    // counted as "present today" at all, even though they clearly showed up
    // in some form. Now counts every attendance status except Absent.
    $present_tdy = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status IN ('present','late','half_day','work_from_home')"))['t'];
    // BUGFIX: this used to count every approved leave ever recorded (no date
    // filter at all), so the "On Leave" card only ever grew and never
    // reflected who's actually on leave today. Now scoped to leaves that
    // cover today's date.
    $on_leave    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved' AND from_date<=CURDATE() AND to_date>=CURDATE()"))['t'];
    $pend_tasks  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"))['t'];
    $comp_tasks  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"))['t'];
    $pend_leaves = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='pending'"))['t'];
    $checked_pct = $total_emp>0 ? round(($present_tdy/$total_emp)*100) : 0;
    ?>

    <!-- Hero -->
    <div class="hero-card">
        <div class="hero-left">
            <div class="hero-eyebrow">Team Control Center · <?php echo date('l, d M Y'); ?></div>
            <div class="hero-name">Hi <?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
            <div class="hero-sub"><?php echo $present_tdy; ?> of <?php echo $total_emp; ?> team members checked in today</div>
            <div class="hero-actions">
                <a href="admin_leaves.php" class="hero-btn solid"><?php echo ems_icon('leaf',15); ?> Pending Leaves (<?php echo $pend_leaves; ?>)</a>
                <a href="add_employee.php" class="hero-btn"><?php echo ems_icon('user-plus',15); ?> Add Employee</a>
                <a href="announcements.php" class="hero-btn"><?php echo ems_icon('megaphone',15); ?> Post Announcement</a>
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
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('users',20); ?></div><div class="label">Total Employees</div><div class="val"><?php echo $total_emp; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('check-circle',20); ?></div><div class="label">Present Today</div><div class="val"><?php echo $present_tdy; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('leaf',20); ?></div><div class="label">On Leave</div><div class="val"><?php echo $on_leave; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('clock',20); ?></div><div class="label">Pending Tasks</div><div class="val"><?php echo $pend_tasks; ?></div></div>
        <div class="stat-tile"><div class="ico"><?php echo ems_icon('check-square',20); ?></div><div class="label">Completed Tasks</div><div class="val"><?php echo $comp_tasks; ?></div></div>
    </div>

    <!-- Quick actions -->
    <div class="qa-grid">
        <a href="admin_leaves.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('leaf',18); ?></span>Review Leaves</a>
        <a href="all_employees.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('users',18); ?></span>All Employees</a>
        <a href="admin_hr_requests.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('inbox',18); ?></span>HR Requests</a>
        <a href="admin_rules.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('shield',18); ?></span>Rules & Regulations</a>
    </div>

    <!-- Real-Time Attendance Status Widget -->
    <div class="timeline-card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
            <h3 style="margin:0;display:flex;align-items:center;gap:8px;"><span style="color:var(--success);"><?php echo ems_icon('check-circle',17); ?></span> Live Attendance Status — Today</h3>
            <span id="attLastUpdated" style="font-size:11px;color:#9ca3af;"></span>
        </div>

        <div id="attSummaryRow" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px;"></div>

        <!-- Attendance filters -->
        <div id="attFilterRow" style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
            <button type="button" class="att-filter-btn active" data-filter="all">All</button>
            <button type="button" class="att-filter-btn" data-filter="wfh">WFH</button>
            <button type="button" class="att-filter-btn" data-filter="wfo">WFO</button>
            <button type="button" class="att-filter-btn" data-filter="late">Late</button>
            <button type="button" class="att-filter-btn" data-filter="absent">Absent</button>
            <button type="button" class="att-filter-btn" data-filter="half_day">Half Day</button>
        </div>
        <style>
        .att-filter-btn{border:1px solid #d1d5db;background:#fff;color:#374151;padding:6px 16px;border-radius:20px;font-size:12px;font-weight:600;cursor:pointer;}
        .att-filter-btn.active{background:#1d4ed8;border-color:#1d4ed8;color:#fff;}
        </style>

        <div style="max-height:340px;overflow-y:auto;">
            <table class="emp-table" style="width:100%;">
                <thead><tr><th>Employee</th><th>Department</th><th>Status</th><th>Work Mode</th><th>Check In</th></tr></thead>
                <tbody id="attStatusBody">
                    <tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Charts -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
        <div class="timeline-card" style="margin-top:0;min-width:0;">
            <h3 style="color:var(--role-accent);display:flex;align-items:center;gap:8px;"><?php echo ems_icon('bar-chart',17); ?> Monthly Attendance</h3>
            <div style="position:relative;height:280px;width:100%;">
                <canvas id="adminAttChart"></canvas>
            </div>
        </div>
        <div class="timeline-card" style="margin-top:0;min-width:0;">
            <h3 style="color:var(--role-accent);display:flex;align-items:center;gap:8px;"><?php echo ems_icon('bar-chart',17); ?> Monthly Leave Requests</h3>
            <div style="position:relative;height:280px;width:100%;">
                <canvas id="adminLeaveChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Upcoming Holidays -->
    <?php
    $uph = mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
    $uphrows = []; while($u=mysqli_fetch_assoc($uph)) $uphrows[]=$u;
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
    new Chart(document.getElementById('adminAttChart'),{type:'line',data:{labels:months,datasets:[{label:'Present',data:<?php echo json_encode($att_data);?>,backgroundColor:'rgba(59,130,246,0.15)',borderColor:'#3b82f6',borderWidth:2,pointBackgroundColor:'#3b82f6',pointRadius:4,tension:0.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:<?php echo $charts_shared_max; ?>,ticks:{stepSize:1}}}}});
    new Chart(document.getElementById('adminLeaveChart'),{type:'line',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo json_encode($leave_data);?>,backgroundColor:'rgba(239,68,68,0.15)',borderColor:'#ef4444',borderWidth:2,pointBackgroundColor:'#ef4444',pointRadius:4,tension:0.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:<?php echo $charts_shared_max; ?>,ticks:{stepSize:1}}}}});
});

// ===== LIVE ATTENDANCE STATUS WIDGET =====
const attStatusColors = {
    present:         { bg:'#dcfce7', color:'#16a34a', label:'Present' },
    late:            { bg:'#fef3c7', color:'#d97706', label:'Late' },
    half_day:        { bg:'#fef3c7', color:'#d97706', label:'Half Day' },
    work_from_home:  { bg:'#dbeafe', color:'#1d4ed8', label:'WFH' },
    absent:          { bg:'#fee2e2', color:'#dc2626', label:'Absent' },
    not_checked_in:  { bg:'#fee2e2', color:'#dc2626', label:'Not Checked-in' }
};
const workModeColors = {
    WFH: { bg:'#dbeafe', color:'#1d4ed8', label:'🏠 WFH' },
    WFO: { bg:'#dcfce7', color:'#16a34a', label:'🏢 WFO' }
};

let lastAttendanceData = null; // kept in memory so filter clicks don't need a re-fetch
let currentAttFilter = 'all';

// BUGFIX/ENHANCEMENT: filters (All/WFH/WFO/Late/Absent/Half Day) run against
// the same data already fetched by loadAttendanceStatus(), so clicking a
// filter is instant and doesn't hit the server again.
function attMatchesFilter(emp, filter){
    if(filter === 'all') return true;
    if(filter === 'wfh') return emp.work_mode === 'WFH';
    if(filter === 'wfo') return emp.work_mode === 'WFO';
    if(filter === 'late') return emp.status === 'late';
    if(filter === 'half_day') return emp.status === 'half_day';
    if(filter === 'absent') return emp.status === 'not_checked_in' || emp.status === 'absent';
    return true;
}

function renderAttendanceRows(){
    if(!lastAttendanceData) return;
    const body = document.getElementById('attStatusBody');
    const filtered = lastAttendanceData.employees.filter(emp => attMatchesFilter(emp, currentAttFilter));
    if(!filtered.length){
        body.innerHTML = '<tr><td colspan="5" style="text-align:center;color:#9ca3af;padding:20px;">No employees match this filter.</td></tr>';
        return;
    }
    body.innerHTML = filtered.map(emp => {
        const c  = attStatusColors[emp.status] || attStatusColors.not_checked_in;
        const wm = emp.work_mode ? workModeColors[emp.work_mode] : null;
        const wmHtml = wm
            ? `<span style="background:${wm.bg};color:${wm.color};padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;">${wm.label}</span>`
            : '<span style="color:#9ca3af;">-</span>';
        return `<tr>
            <td>${emp.name}</td>
            <td>${emp.department || '-'}</td>
            <td><span style="background:${c.bg};color:${c.color};padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;">${c.label}</span></td>
            <td>${wmHtml}</td>
            <td>${emp.check_in || '-'}</td>
        </tr>`;
    }).join('');
}

document.querySelectorAll('.att-filter-btn').forEach(btn => {
    btn.addEventListener('click', function(){
        document.querySelectorAll('.att-filter-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        currentAttFilter = this.dataset.filter;
        renderAttendanceRows();
    });
});

function loadAttendanceStatus(){
    fetch('get_attendance_status.php')
        .then(r => r.json())
        .then(data => {
            if(data.error) return;
            lastAttendanceData = data;

            // Summary pills: existing status counts, plus a WFH/WFO split
            const counts = data.counts;
            const wmCounts = data.work_mode_counts || {WFH:0, WFO:0};
            let summaryHtml = Object.keys(attStatusColors).map(key => {
                const c = attStatusColors[key];
                const n = counts[key] || 0;
                return `<div style="background:${c.bg};color:${c.color};padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">${c.label}: ${n}</div>`;
            }).join('');
            summaryHtml += `<div style="background:${workModeColors.WFH.bg};color:${workModeColors.WFH.color};padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">${workModeColors.WFH.label}: ${wmCounts.WFH || 0}</div>`;
            summaryHtml += `<div style="background:${workModeColors.WFO.bg};color:${workModeColors.WFO.color};padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">${workModeColors.WFO.label}: ${wmCounts.WFO || 0}</div>`;
            document.getElementById('attSummaryRow').innerHTML = summaryHtml;

            renderAttendanceRows();

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