<?php
session_start();
if(!isset($_SESSION['user'])||$_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';

$sa_id=$_SESSION['user']['id'];
$sa_photo_row=mysqli_fetch_assoc(mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$sa_id'"));
$sa_photo=$sa_photo_row['profile_photo']??'';

// ---- Holiday setup ----

// BUGFIX: this used to declare the auto-increment column as `holiday_id`
// but then set PRIMARY KEY on a column called `id`, which doesn't exist —
// on a brand-new database (no existing `holidays` table) this CREATE TABLE
// throws a SQL error ("Key column 'id' doesn't exist in table") and the
// whole holiday feature silently breaks. Column name now matches the
// actual production schema (`id`), consistent with every other file that
// reads/writes this table.
mysqli_query($conn,"CREATE TABLE IF NOT EXISTS `holidays` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `holiday_name` VARCHAR(200) DEFAULT NULL,
  `holiday_date` DATE DEFAULT NULL,
  `holiday_type` VARCHAR(50) DEFAULT 'National',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

//  Add holiday_type column if missing
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM holidays LIKE 'holiday_type'");
if(mysqli_num_rows($col_check) == 0){
    mysqli_query($conn, "ALTER TABLE holidays ADD COLUMN holiday_type VARCHAR(50) DEFAULT 'National'");
    mysqli_query($conn, "UPDATE holidays SET holiday_type='National' WHERE holiday_type IS NULL");
}

$hcount=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as cnt FROM holidays"));
if($hcount['cnt']==0){
    $y=date('Y');
    $defaults=[
        ["Republic Day","$y-01-26","National"],["Holi","$y-03-14","Festival"],
        ["Gudi Padwa","$y-03-30","State"],["Good Friday","$y-04-18","National"],
        ["Dr. Ambedkar Jayanti","$y-04-14","National"],["Maharashtra Day","$y-05-01","State"],
        ["Independence Day","$y-08-15","National"],["Ganesh Chaturthi","$y-08-27","Festival"],
        ["Gandhi Jayanti","$y-10-02","National"],["Diwali","$y-10-20","Festival"],
        ["Diwali Laxmi Puja","$y-10-21","Festival"],["Gurunanak Jayanti","$y-11-05","National"],
        ["Christmas","$y-12-25","National"]
    ];
    foreach($defaults as $h){$nm=mysqli_real_escape_string($conn,$h[0]);mysqli_query($conn,"INSERT INTO holidays (holiday_name,holiday_date,holiday_type) VALUES ('$nm','$h[1]','$h[2]')");}
}
$h_res=mysqli_query($conn,"SELECT holiday_date,holiday_name,holiday_type FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())");
$holiday_map=[];
while($hrow=mysqli_fetch_assoc($h_res)) $holiday_map[$hrow['holiday_date']]=['name'=>$hrow['holiday_name'],'type'=>($hrow['holiday_type']??'National')];
$holidays_json=json_encode($holiday_map);

// ---- Performance table ----
mysqli_query($conn,"CREATE TABLE IF NOT EXISTS `performance` (`perf_id` INT NOT NULL AUTO_INCREMENT,`emp_id` INT DEFAULT NULL,`skill_name` VARCHAR(200) DEFAULT NULL,`description` TEXT,`date_added` DATE DEFAULT NULL,PRIMARY KEY (`perf_id`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Super Admin Dashboard - EMS</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
*{box-sizing:border-box;}
@media(max-width:900px){
  .dashboard{flex-direction:column;}
  .sidebar{width:100%!important;min-height:auto;}
  .main-content{margin-left:0!important;}
  .cards,.six-cards{grid-template-columns:repeat(2,1fr)!important;}
  .form-grid{grid-template-columns:1fr!important;}
  .cal-grid{gap:2px!important;}
  .cal-cell{min-height:40px!important;font-size:11px!important;}
  .hol-cards{grid-template-columns:1fr 1fr!important;}
}
@media(max-width:600px){
  .cards,.six-cards{grid-template-columns:1fr!important;}
  .hol-cards{grid-template-columns:1fr!important;}
  .topbar h2{font-size:15px;}
}
.notif-wrapper{position:relative;}
.notif-bell{font-size:20px;cursor:pointer;position:relative;display:inline-block;padding:4px 8px;border-radius:8px;transition:background .2s;}
.notif-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;animation:pulse 1.5s infinite;}
@keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
.notif-dropdown{display:none;position:absolute;right:0;top:42px;width:340px;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15);z-index:9999;overflow:hidden;border:1px solid #e5e7eb;}
.notif-dropdown.open{display:block;animation:slideDown .2s ease;}
@keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
.notif-header{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;font-weight:600;background:#f8fafc;}
.notif-list{max-height:340px;overflow-y:auto;}
.notif-item{display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-bottom:1px solid #f5f5f5;transition:background .2s;}
.notif-item:hover{background:#f8fafc;}
.notif-item.notif-new{background:#eff6ff;}
.notif-icon{font-size:18px;flex-shrink:0;}
.notif-text{flex:1;font-size:13px;color:#374151;line-height:1.6;}
.notif-type{background:#dbeafe;color:#1d4ed8;font-size:11px;padding:1px 7px;border-radius:20px;font-weight:600;}
.notif-dot{width:8px;height:8px;background:#3b82f6;border-radius:50%;flex-shrink:0;margin-top:6px;}
.notif-empty{text-align:center;padding:28px;color:#9ca3af;font-size:13px;}
.topbar-right{display:flex;align-items:center;gap:14px;flex-wrap:wrap;}
.six-cards{display:grid;grid-template-columns:repeat(6,1fr);gap:14px;}
/* Calendar */
.cal-outer{background:#fff;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden;}
.cal-top{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:linear-gradient(135deg,#1a3a6e,#3b82f6);color:#fff;}
.cal-top h3{font-size:16px;font-weight:700;margin:0;}
.cal-nav-btn{background:rgba(255,255,255,.2);color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px;transition:background .2s;}
.cal-nav-btn:hover{background:rgba(255,255,255,.35);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;padding:12px;}
.cal-day-name{text-align:center;font-size:11px;font-weight:700;color:#6b7280;padding:6px 0;text-transform:uppercase;}
.cal-cell{min-height:56px;border-radius:8px;padding:5px 7px;font-size:12px;border:1px solid #f0f0f0;background:#fff;transition:background .15s;}
.cal-cell.today{background:#eff6ff!important;border:2px solid #3b82f6;}
.cal-cell.holiday{background:#fef2f2!important;border-color:#fca5a5;}
.cal-cell.sunday{background:#fafafa;color:#9ca3af;}
.cal-cell.empty{border:none;background:none;}
.cal-num{font-weight:700;color:#374151;font-size:13px;}
.cal-cell.holiday .cal-num{color:#dc2626;}
.cal-cell.today .cal-num{color:#1d4ed8;}
.cal-hname{font-size:9px;color:#dc2626;line-height:1.3;margin-top:2px;word-break:break-word;}
.hl-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.hl-badge.National{background:#dbeafe;color:#1d4ed8;}
.hl-badge.Festival{background:#fef3c7;color:#d97706;}
.hl-badge.State{background:#dcfce7;color:#16a34a;}
.hl-badge.Government{background:#f3e8ff;color:#7c3aed;}.pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.pill.green{background:#dcfce7;color:#16a34a;}
.pill.red{background:#fee2e2;color:#dc2626;}
.pill.yellow{background:#fef3c7;color:#d97706;}
.pill.blue{background:#dbeafe;color:#1d4ed8;}
.hol-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
.skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
</style>
</head>
<body>
<div class="dashboard super-theme">

<!-- Sidebar -->
<?php include 'sidebar_sa.php'; ?>

<div class="main-content">

<?php $page_title = "Dashboard"; include 'topbar_sa.php'; ?>

<div class="app-content">

    <!-- ===== DASHBOARD ===== -->
    <div id="dashboard" class="section active">
        <?php
        // BUGFIX: was counting FROM users WHERE role='employee' — see the
        // same fix in admin_dashboard.php. Using the employees table itself
        // is the correct, authoritative count.
        $sa_total_emp   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM employees"))['t'];
        $sa_present_tdy = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status IN ('present','late','half_day','work_from_home')"))['t'];
        $sa_pend_leaves = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='pending'"))['t'];
        $sa_appr_leaves = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved'"))['t'];
        $sa_pend_tasks  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"))['t'];
        $sa_comp_tasks  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"))['t'];
        $sa_pct = $sa_total_emp>0 ? round(($sa_present_tdy/$sa_total_emp)*100) : 0;
        ?>

        <!-- Hero -->
        <div class="hero-card">
            <div class="hero-left">
                <div class="hero-eyebrow">Organization Insights · <?php echo date('l, d M Y'); ?></div>
                <div class="hero-name">Hi <?php echo htmlspecialchars($_SESSION['user']['name']); ?></div>
                <div class="hero-sub">Full visibility across <?php echo $sa_total_emp; ?> employees, org-wide</div>
                <div class="hero-actions">
                    <a href="#" onclick="showSection('employees',document.querySelector('[onclick*=employees]'));return false;" class="hero-btn solid"><?php echo ems_icon('users',15); ?> View Employees</a>
                    <a href="departments.php" class="hero-btn"><?php echo ems_icon('building',15); ?> Departments</a>
                    <a href="announcements.php" class="hero-btn"><?php echo ems_icon('megaphone',15); ?> Announcements</a>
                </div>
            </div>
            <div class="day-ring-wrap">
                <div class="day-ring">
                    <svg width="118" height="118" viewBox="0 0 120 120">
                        <circle class="ring-bg" cx="60" cy="60" r="50"/>
                        <circle class="ring-fill" cx="60" cy="60" r="50"
                            stroke-dasharray="<?php echo 2*M_PI*50; ?>"
                            stroke-dashoffset="<?php echo (2*M_PI*50) - ($sa_pct/100)*(2*M_PI*50); ?>"/>
                    </svg>
                    <div class="day-ring-label">
                        <span class="t"><?php echo $sa_pct; ?>%</span>
                        <span class="s">present today</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stat-tiles" style="grid-template-columns:repeat(6,1fr);">
            <div class="stat-tile"><div class="ico"><?php echo ems_icon('users',20); ?></div><div class="label">Total Employees</div><div class="val"><?php echo $sa_total_emp; ?></div></div>
            <div class="stat-tile"><div class="ico"><?php echo ems_icon('check-circle',20); ?></div><div class="label">Present Today</div><div class="val"><?php echo $sa_present_tdy; ?></div></div>
            <div class="stat-tile"><div class="ico"><?php echo ems_icon('clock',20); ?></div><div class="label">Pending Leaves</div><div class="val"><?php echo $sa_pend_leaves; ?></div></div>
            <div class="stat-tile"><div class="ico"><?php echo ems_icon('leaf',20); ?></div><div class="label">Approved Leaves</div><div class="val"><?php echo $sa_appr_leaves; ?></div></div>
            <div class="stat-tile"><div class="ico"><?php echo ems_icon('check-square',20); ?></div><div class="label">Pending Tasks</div><div class="val"><?php echo $sa_pend_tasks; ?></div></div>
            <div class="stat-tile"><div class="ico"><?php echo ems_icon('check-circle',20); ?></div><div class="label">Completed Tasks</div><div class="val"><?php echo $sa_comp_tasks; ?></div></div>
        </div>

        <!-- Quick actions -->
        <div class="qa-grid">
            <a href="#" onclick="showSection('employees',document.querySelector('[onclick*=employees]'));return false;" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('users',18); ?></span>All Employees</a>
            <a href="departments.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('building',18); ?></span>Departments</a>
            <a href="sa_rules.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('shield',18); ?></span>Rules & Regulations</a>
            <a href="view_employees.php" class="qa-btn"><span class="qa-ico"><?php echo ems_icon('sitemap',18); ?></span>Assign Department</a>
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

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
            <div class="timeline-card" style="margin-top:0;min-width:0;">
                <h3 style="color:var(--role-accent);display:flex;align-items:center;gap:8px;"><?php echo ems_icon('bar-chart',17); ?> Company Monthly Attendance</h3>
                <div style="position:relative;height:280px;width:100%;">
                    <canvas id="attendanceChart"></canvas>
                </div>
            </div>
            <div class="timeline-card" style="margin-top:0;min-width:0;">
                <h3 style="color:var(--role-accent);display:flex;align-items:center;gap:8px;"><?php echo ems_icon('bar-chart',17); ?> Company Monthly Leaves</h3>
                <div style="position:relative;height:280px;width:100%;">
                    <canvas id="leaveChart"></canvas>
                </div>
            </div>
        </div>
        <!-- Upcoming holidays -->
        <?php
        $uph=mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
        $uphrows=[];while($u=mysqli_fetch_assoc($uph))$uphrows[]=$u;
        if(!empty($uphrows)):?>
        <div class="timeline-card">
            <h3 style="display:flex;align-items:center;gap:8px;"><?php echo ems_icon('flag',17); ?> Upcoming Holidays</h3>
            <div class="hscroll">
            <?php foreach($uphrows as $uh):
                $ht=$uh['holiday_type']??'National';
                $cc=['National'=>'#1d4ed8','Festival'=>'#d97706','State'=>'#16a34a'];$c=$cc[$ht]??'#6b7280';
            ?>
                <div class="hscroll-item" style="border-left-color:<?php echo $c;?>;">
                    <p style="font-size:11px;color:#9ca3af;margin:0;"><?php echo date('D, d M',strtotime($uh['holiday_date']));?></p>
                    <p style="font-size:13px;font-weight:700;color:#1a1a2e;margin:4px 0;"><?php echo $uh['holiday_name'];?></p>
                    <span class="hl-badge <?php echo $ht;?>"><?php echo $ht;?></span>
                </div>
            <?php endforeach;?>
            </div>
        </div>
        <?php endif;?>
    </div>

    <!-- ===== EMPLOYEES ===== -->
    <div id="employees" class="section">
        <div class="form-card">
            <h3 class="section-title">All Employees</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Blood Group</th><th>DOB</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT u.id,u.name,u.email,e.designation,e.contact,e.blood_group,e.dob FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'");
                    while($row=mysqli_fetch_assoc($res)) echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['designation']}</td><td>{$row['contact']}</td><td>{$row['blood_group']}</td><td>{$row['dob']}</td></tr>";
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ===== ATTENDANCE ===== -->
    <div id="attendance" class="section">
        <div class="form-card">
            <h3 class="section-title">Attendance Records</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id ORDER BY a.date DESC");
                    while($row=mysqli_fetch_assoc($res)){
                        $type=($row['status']=='work_from_home')?"<span class='pill blue'>&#127968; WFH</span>":"<span class='pill green'>&#127970; Office</span>";
                        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td>".ucfirst(str_replace('_',' ',$row['status']))."</td><td>{$type}</td><td><a href='regularize.php?id={$row['attendance_id']}' class='approve-btn'>Regularize</a></td></tr>";
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
                    $late=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id WHERE a.date=CURDATE() AND a.check_in>'09:00:00' AND a.status!='work_from_home'");
                    if(mysqli_num_rows($late)>0){
                        while($row=mysqli_fetch_assoc($late)){
                            $secs=strtotime($row['check_in'])-strtotime('09:00:00');
                            $h=floor($secs/3600);$m=floor(($secs%3600)/60);
                            echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td style='color:#ef4444;font-weight:bold;'>{$row['check_in']}</td><td><span class='pill red'>".($h>0?"{$h}h {$m}m":"{$m}m")." late</span></td></tr>";
                        }
                    } else echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No late comers today</td></tr>";
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ===== LEAVES ===== -->
    <div id="leaves" class="section">
        <div class="form-card">
            <h3 class="section-title">Leave Requests</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT l.*,e.first_name,e.last_name FROM leaves l JOIN employees e ON l.emp_id=e.emp_id ORDER BY l.leave_id DESC");
                    while($row=mysqli_fetch_assoc($res)){
                        $pc=['approved'=>'green','rejected'=>'red','pending'=>'yellow'][$row['status']]??'yellow';
                        $csrf_tok = csrf_token();
                        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['leave_type']}</td><td>{$row['from_date']}</td><td>{$row['to_date']}</td><td>{$row['reason']}</td>
                        <td><span class='pill {$pc}'>".ucfirst($row['status'])."</span></td>
                        <td><a href='leave_action.php?id={$row['leave_id']}&action=approved&csrf={$csrf_tok}' class='approve-btn'>Approve</a>
                        <a href='leave_action.php?id={$row['leave_id']}&action=rejected&csrf={$csrf_tok}' class='reject-btn'>Reject</a></td></tr>";
                    }
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ===== SALARY ===== -->
    <div id="salary" class="section">
        <div class="form-card">
            <h3 class="section-title">All Salary Records</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>Employee</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>LOP Days</th><th>LOP Amt</th><th>Net Pay</th><th>Month</th><th>Year</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT s.*,e.first_name,e.last_name FROM salary s JOIN employees e ON s.emp_id=e.emp_id ORDER BY s.year DESC");
                    while($row=mysqli_fetch_assoc($res)){
                        $ld=isset($row['lop_days'])?$row['lop_days']:0;
                        $la=isset($row['lop_amount'])?$row['lop_amount']:0;
                        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>&#8377;{$row['basic_pay']}</td><td>&#8377;{$row['allowances']}</td><td>&#8377;{$row['deductions']}</td>
                        <td><span class='pill red'>{$ld} days</span></td><td>&#8377;{$la}</td><td><b>&#8377;{$row['net_pay']}</b></td><td>{$row['month']}</td><td>{$row['year']}</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ===== TASKS ===== -->
    <div id="tasks" class="section">
        <div class="form-card">
            <h3 class="section-title">All Tasks</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>Employee</th><th>Task</th><th>Description</th><th>Target Date</th><th>Status</th><th>Hours</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT t.*,e.first_name,e.last_name FROM tasks t JOIN employees e ON t.emp_id=e.emp_id ORDER BY t.target_date DESC");
                    while($row=mysqli_fetch_assoc($res)){
                        $pc=['completed'=>'green','in_progress'=>'yellow','pending'=>'red'][$row['status']]??'yellow';
                        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td><b>{$row['task_name']}</b></td><td>{$row['description']}</td><td>{$row['target_date']}</td><td><span class='pill {$pc}'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td><td>{$row['hours_worked']} hrs</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ===== MONTHLY REVENUE ===== -->
    <div id="revenue" class="section">
        <div class="form-card">
            <h3 class="section-title">Add Monthly Revenue</h3>
            <form action="save_revenue.php" method="POST">
                <div class="form-grid">
                    <div class="field"><label>Month</label>
                        <select name="month"><option>January</option><option>February</option><option>March</option><option>April</option><option>May</option><option>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option></select>
                    </div>
                    <div class="field"><label>Year</label><input type="number" name="year" value="<?php echo date('Y');?>" required></div>
                    <div class="field"><label>Revenue Amount (&#8377;)</label><input type="number" name="amount" placeholder="Enter amount" required></div>
                </div>
                <button type="submit" class="submit-btn">Add Revenue</button>
            </form>
            <h3 class="section-title" style="margin-top:28px;">Revenue Records</h3>
            <table class="emp-table">
                <thead><tr><th>Month</th><th>Year</th><th>Amount</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT * FROM revenue ORDER BY year DESC,revenue_id DESC");
                    if($res) while($row=mysqli_fetch_assoc($res)) echo "<tr><td>{$row['month']}</td><td>{$row['year']}</td><td><b>&#8377;".number_format($row['amount'],2)."</b></td></tr>";
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== PERFORMANCE ===== -->
    <div id="performance" class="section">
        <div class="form-card">
            <h3 class="section-title">Employee Performance Overview</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>Employee</th><th>Total Tasks</th><th>Completed</th><th>Pending</th><th>Attendance Days</th><th>Skills Added</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT e.first_name,e.last_name,e.emp_id,
                        (SELECT COUNT(*) FROM tasks WHERE emp_id=e.emp_id) as total_tasks,
                        (SELECT COUNT(*) FROM tasks WHERE emp_id=e.emp_id AND status='completed') as completed,
                        (SELECT COUNT(*) FROM tasks WHERE emp_id=e.emp_id AND status='pending') as pending,
                        (SELECT COUNT(*) FROM attendance WHERE emp_id=e.emp_id) as att_days,
                        (SELECT COUNT(*) FROM performance WHERE emp_id=e.emp_id) as skills
                        FROM employees e");
                    while($row=mysqli_fetch_assoc($res)){
                        echo "<tr><td><b>{$row['first_name']} {$row['last_name']}</b></td>
                        <td>{$row['total_tasks']}</td>
                        <td><span class='pill green'>{$row['completed']}</span></td>
                        <td><span class='pill red'>{$row['pending']}</span></td>
                        <td>{$row['att_days']}</td>
                        <td><span class='pill blue'>{$row['skills']} skills</span></td></tr>";
                    }
                ?>
                </tbody>
            </table>
            </div>
        </div>
        <!-- Employee Skills -->
        <div class="form-card" style="margin-top:0;">
            <h3 class="section-title">Employee Skills / Improvement Plans</h3>
            <?php
            $pemp=mysqli_query($conn,"SELECT DISTINCT e.emp_id,e.first_name,e.last_name FROM employees e JOIN performance p ON e.emp_id=p.emp_id");
            if(mysqli_num_rows($pemp)==0) echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>No skills added by employees yet.</p>";
            while($emp_row=mysqli_fetch_assoc($pemp)){
                echo "<div style='margin-bottom:16px;padding:14px;background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;'>
                    <p style='font-size:14px;font-weight:700;color:#1a1a2e;margin:0 0 8px;'>&#128100; {$emp_row['first_name']} {$emp_row['last_name']}</p>";
                $skills=mysqli_query($conn,"SELECT * FROM performance WHERE emp_id='{$emp_row['emp_id']}' ORDER BY date_added DESC");
                while($sk=mysqli_fetch_assoc($skills)) echo "<span class='skill-tag'>&#10003; {$sk['skill_name']}</span>";
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <!-- ===== HOLIDAY CALENDAR ===== -->
    <div id="holidays" class="section">
        <?php
        $totalH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())"))['c'];
        $natH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='National'"))['c'];
        $festH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='Festival'"))['c'];
        ?>
        <div class="hol-cards">
            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #3b82f6;">
                <p style="font-size:11px;color:#6b7280;margin:0;">Total Holidays <?php echo date('Y');?></p>
                <p style="font-size:32px;font-weight:800;color:#1a3a6e;margin:6px 0;"><?php echo $totalH;?></p>
            </div>
            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #1d4ed8;">
                <p style="font-size:11px;color:#6b7280;margin:0;">National</p>
                <p style="font-size:32px;font-weight:800;color:#1d4ed8;margin:6px 0;"><?php echo $natH;?></p>
            </div>
            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #d97706;">
                <p style="font-size:11px;color:#6b7280;margin:0;">Festivals</p>
                <p style="font-size:32px;font-weight:800;color:#d97706;margin:6px 0;"><?php echo $festH;?></p>
            </div>
        </div>

        <div class="form-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
            <div id="calContainer"></div>
        </div>

        <div class="form-card">
            <h3 class="section-title">&#43; Add New Holiday</h3>
            <form action="save_holiday.php" method="POST">
                <div class="form-grid">
                    <div class="field"><label>Holiday Name</label><input type="text" name="holiday_name" placeholder="e.g. Eid al-Fitr" required></div>
                    <div class="field"><label>Date</label><input type="date" name="holiday_date" required></div>
                    <div class="field"><label>Type</label>
                    <select name="holiday_type">
                        <option value="National">National</option>
                        <option value="Festival">Festival</option>
                        <option value="State">State</option>
                        <option value="Government">Government</option>
                     </select>
                    </div>
                </div>
                <button type="submit" class="submit-btn">&#43; Add Holiday</button>
            </form>
        </div>

        <div class="form-card" style="margin-top:0;">
            <h3 class="section-title">All Holidays <?php echo date('Y');?></h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>#</th><th>Holiday Name</th><th>Date</th><th>Day</th><th>Type</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                    $hres=mysqli_query($conn,"SELECT * FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) ORDER BY holiday_date ASC");
                    $cnt=1;
                    while($h=mysqli_fetch_assoc($hres)){
                        $ht=$h['holiday_type']??'National';
                        $hi=($h['holiday_date']==date('Y-m-d'))?"background:#eff6ff;":"";
                        echo "<tr style='{$hi}'><td>{$cnt}</td><td><b>{$h['holiday_name']}</b></td>
                        <td>".date('d M Y',strtotime($h['holiday_date']))."</td>
                        <td>".date('l',strtotime($h['holiday_date']))."</td>
                        <td><span class='hl-badge {$ht}'>{$ht}</span></td>
                        <td><a href='delete_holiday.php?id={$h['id']}' class='reject-btn' onclick='return confirm(\"Delete this holiday?\")'>&#128465; Delete</a></td></tr>";
                        $cnt++;
                    }
                ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- ===== MY PROFILE ===== -->
    <div id="my_profile" class="section">
        <div class="form-card">
            <h3 class="section-title">Profile Photo</h3>
            <?php if(!empty($sa_photo)&&file_exists('uploads/'.$sa_photo)): ?>
                <img src="uploads/<?php echo htmlspecialchars($sa_photo); ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #3b82f6;margin-bottom:16px;">
            <?php endif; ?>
            <form action="save_profile_photo.php" method="POST" enctype="multipart/form-data">
                <div class="field"><label>Upload Profile Photo</label><input type="file" name="profile_photo" accept="image/*" required></div>
                <button type="submit" class="submit-btn">Update Photo</button>
            </form>
            <h3 class="section-title" style="margin-top:28px;">My Details</h3>
            <table class="emp-table">
                <tr><td><b>Name</b></td><td><?php echo $_SESSION['user']['name']; ?></td></tr>
                <tr><td><b>Email</b></td><td><?php echo $_SESSION['user']['email']; ?></td></tr>
                <tr><td><b>Role</b></td><td><span class="pill blue">Super Admin</span></td></tr>
            </table>
        </div>
    </div>

</div>
</div></div>

<?php
$att_data=array_fill(0,12,0);
$r=mysqli_query($conn,"SELECT MONTH(date) as mon,COUNT(*) as cnt FROM attendance WHERE status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
while($row=mysqli_fetch_assoc($r))$att_data[$row['mon']-1]=$row['cnt'];
$leave_data=array_fill(0,12,0);
$r=mysqli_query($conn,"SELECT MONTH(from_date) as mon,COUNT(*) as cnt FROM leaves WHERE YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
while($row=mysqli_fetch_assoc($r))$leave_data[$row['mon']-1]=$row['cnt'];

// Same fix as emp_dashboard.php / admin_dashboard.php — one shared Y-axis
// max keeps both charts' gridlines aligned instead of each auto-scaling
// to its own data.
$charts_shared_max = max(1, max($att_data), max($leave_data)) + 1;
?>
<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const fullMonths=['January','February','March','April','May','June','July','August','September','October','November','December'];
const dayNames=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const holidays=<?php echo $holidays_json; ?>;
const todayStr="<?php echo date('Y-m-d'); ?>";
let calYear=<?php echo date('Y'); ?>;
let calMonth=<?php echo date('n')-1; ?>;

new Chart(document.getElementById('attendanceChart'),{type:'line',data:{labels:months,datasets:[{label:'Present',data:<?php echo json_encode($att_data);?>,backgroundColor:'rgba(59,130,246,0.15)',borderColor:'#3b82f6',borderWidth:2,pointBackgroundColor:'#3b82f6',pointRadius:4,tension:0.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:<?php echo $charts_shared_max; ?>,ticks:{stepSize:1}}}}});
new Chart(document.getElementById('leaveChart'),{type:'line',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo json_encode($leave_data);?>,backgroundColor:'rgba(239,68,68,0.15)',borderColor:'#ef4444',borderWidth:2,pointBackgroundColor:'#ef4444',pointRadius:4,tension:0.35,fill:true}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,max:<?php echo $charts_shared_max; ?>,ticks:{stepSize:1}}}}});

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

let lastAttendanceData = null;
let currentAttFilter = 'all';

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

loadAttendanceStatus();
setInterval(loadAttendanceStatus, 20000);

function buildCalendar(year,month){
    const container=document.getElementById('calContainer');
    if(!container)return;
    const firstDay=new Date(year,month,1).getDay();
    const daysInMonth=new Date(year,month+1,0).getDate();
    let html=`<div class="cal-top"><h3>&#128197; ${fullMonths[month]} ${year}</h3>
        <div><button class="cal-nav-btn" onclick="changeMonth(-1)">&#8592; Prev</button>
             <button class="cal-nav-btn" style="margin-left:8px" onclick="changeMonth(1)">Next &#8594;</button></div></div>
        <div class="cal-grid">`;
    dayNames.forEach(d=>{html+=`<div class="cal-day-name">${d}</div>`;});
    for(let i=0;i<firstDay;i++)html+=`<div class="cal-cell empty"></div>`;
    for(let d=1;d<=daysInMonth;d++){
        const mm=String(month+1).padStart(2,'0'),dd=String(d).padStart(2,'0'),ds=`${year}-${mm}-${dd}`;
        const isSun=new Date(year,month,d).getDay()===0,isToday=ds===todayStr,isHol=holidays[ds]!==undefined;
        let cls='cal-cell';
        if(isToday)cls+=' today';else if(isHol)cls+=' holiday';else if(isSun)cls+=' sunday';
        let hname=isHol?`<div class="cal-hname">${holidays[ds].name}</div>`:(isSun?`<div class="cal-hname" style="color:#9ca3af">Sunday</div>`:'');
        html+=`<div class="${cls}"><span class="cal-num">${d}</span>${hname}</div>`;
    }
    html+=`</div>`;
    container.innerHTML=html;
}
function changeMonth(dir){calMonth+=dir;if(calMonth>11){calMonth=0;calYear++;}if(calMonth<0){calMonth=11;calYear--;}buildCalendar(calYear,calMonth);}
buildCalendar(calYear,calMonth);

function showSection(name,el){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
    document.getElementById(name).classList.add('active');
    el.classList.add('active');
    document.getElementById('page-title').innerText=el.innerText;
    if(name==='holidays')setTimeout(()=>buildCalendar(calYear,calMonth),50);
}
function toggleNotif(){document.getElementById('notifDropdown').classList.toggle('open');}
document.addEventListener('click',function(e){
    const w=document.getElementById('notifWrapper');
    if(w&&!w.contains(e.target))document.getElementById('notifDropdown').classList.remove('open');
});
let timeLeft=1800;
function resetTimer(){timeLeft=1800;}
['mousemove','keydown','click','scroll'].forEach(e=>document.addEventListener(e,resetTimer,{passive:true}));
setInterval(()=>{timeLeft--;if(timeLeft<=0){alert('Session expired.');window.location.href='logout.php';}},1000);
</script>
</body></html>