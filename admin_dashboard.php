<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$admin_id = $_SESSION['user']['id'];
$photo_res = mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$admin_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';

// ---- Holiday setup ----

mysqli_query($conn,"CREATE TABLE IF NOT EXISTS `holidays` (
  `holiday_id` INT NOT NULL AUTO_INCREMENT,
  `holiday_name` VARCHAR(200) DEFAULT NULL,
  `holiday_date` DATE DEFAULT NULL,
  `holiday_type` VARCHAR(50) DEFAULT 'National',
  PRIMARY KEY (`holiday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ✅ FIX: Add holiday_type column if missing
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM holidays LIKE 'holiday_type'");
if(mysqli_num_rows($col_check) == 0){
    mysqli_query($conn, "ALTER TABLE holidays ADD COLUMN holiday_type VARCHAR(50) DEFAULT 'National'");
    mysqli_query($conn, "UPDATE holidays SET holiday_type='National' WHERE holiday_type IS NULL");
}

$hcount = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as cnt FROM holidays"));
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
    foreach($defaults as $h){ $nm=mysqli_real_escape_string($conn,$h[0]); mysqli_query($conn,"INSERT INTO holidays (holiday_name,holiday_date,holiday_type) VALUES ('$nm','$h[1]','$h[2]')"); }
}

// Build holidays JSON for JS
$h_res=mysqli_query($conn,"SELECT holiday_date,holiday_name,holiday_type FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())");
$holiday_map=[];
while($hrow=mysqli_fetch_assoc($h_res)){
    $holiday_map[$hrow['holiday_date']]=['name'=>$hrow['holiday_name'],'type'=>($hrow['holiday_type']??'National')];
}
$holidays_json=json_encode($holiday_map);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - EMS</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ---- Responsive ---- */
*{box-sizing:border-box;}
@media(max-width:900px){
  .dashboard{flex-direction:column;}
  .sidebar{width:100%!important;min-height:auto;}
  .main-content{margin-left:0!important;}
  .cards,.five-cards{grid-template-columns:repeat(2,1fr)!important;}
  .form-grid{grid-template-columns:1fr!important;}
  .cal-grid{gap:2px!important;}
  .cal-cell{min-height:40px!important;font-size:11px!important;}
  .hol-cards{grid-template-columns:1fr 1fr!important;}
}
@media(max-width:600px){
  .cards,.five-cards{grid-template-columns:1fr!important;}
  .cal-cell{min-height:34px!important;padding:2px 3px!important;}
  .hol-cards{grid-template-columns:1fr!important;}
  .topbar h2{font-size:15px;}
}
/* ---- Notif ---- */
.notif-wrapper{position:relative;}
.notif-bell{font-size:20px;cursor:pointer;position:relative;display:inline-block;padding:4px 8px;border-radius:8px;transition:background .2s;}
.notif-bell:hover{background:rgba(59,130,246,.1);}
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
/* ---- Holiday Calendar ---- */
.cal-outer{background:#fff;border-radius:14px;box-shadow:0 2px 14px rgba(0,0,0,.07);overflow:hidden;}
.cal-top{display:flex;justify-content:space-between;align-items:center;padding:16px 20px;background:linear-gradient(135deg,#1a3a6e,#3b82f6);color:#fff;}
.cal-top h3{font-size:16px;font-weight:700;margin:0;}
.cal-nav-btn{background:rgba(255,255,255,.2);color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px;transition:background .2s;}
.cal-nav-btn:hover{background:rgba(255,255,255,.35);}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;padding:12px;}
.cal-day-name{text-align:center;font-size:11px;font-weight:700;color:#6b7280;padding:6px 0;text-transform:uppercase;}
.cal-cell{min-height:56px;border-radius:8px;padding:5px 7px;font-size:12px;border:1px solid #f0f0f0;cursor:default;background:#fff;transition:background .15s;}
.cal-cell:hover{background:#f8fafc;}
.cal-cell.today{background:#eff6ff!important;border:2px solid #3b82f6;}
.cal-cell.holiday{background:#fef2f2!important;border-color:#fca5a5;}
.cal-cell.sunday{background:#fafafa;color:#9ca3af;}
.cal-cell.empty{border:none;background:none;}
.cal-num{font-weight:700;color:#374151;font-size:13px;}
.cal-cell.holiday .cal-num{color:#dc2626;}
.cal-cell.today .cal-num{color:#1d4ed8;}
.cal-hname{font-size:9px;color:#dc2626;line-height:1.3;margin-top:2px;word-break:break-word;}
/* ---- Holiday type badges ---- */
.hl-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;}
.hl-badge.National{background:#dbeafe;color:#1d4ed8;}
.hl-badge.Festival{background:#fef3c7;color:#d97706;}
.hl-badge.State{background:#dcfce7;color:#16a34a;}
.hl-badge.Government{background:#f3e8ff;color:#7c3aed;}/* ---- Status pills ---- */
.pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.pill.green{background:#dcfce7;color:#16a34a;}
.pill.red{background:#fee2e2;color:#dc2626;}
.pill.yellow{background:#fef3c7;color:#d97706;}
.pill.blue{background:#dbeafe;color:#1d4ed8;}
/* ---- Hol cards grid ---- */
.hol-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;}
</style>
</head>
<body>
<div class="dashboard">

<!-- Sidebar -->
<div class="sidebar">
    <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);">
        <img src="allerlogo.png" alt="Aller" style="height:55px;display:block;margin:0 auto 8px;">
        <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
    </div>
    <nav>
        <a class="nav-item active" onclick="showSection('dashboard',this)">&#127968; Dashboard</a>
        <a class="nav-item" onclick="showSection('add_employee',this)">&#43; Add Employee</a>
        <a class="nav-item" onclick="showSection('view_employees',this)">&#128100; View Employees</a>
        <a class="nav-item" onclick="showSection('attendance',this)">&#128197; Attendance</a>
        <a class="nav-item" onclick="showSection('leaves',this)">&#127809; Leaves</a>
        <a class="nav-item" onclick="showSection('salary',this)">&#128176; Salary</a>
        <a class="nav-item" onclick="showSection('tasks',this)">&#9989; Tasks</a>
        <a class="nav-item" onclick="showSection('leave_types',this)">&#128221; Leave Types</a>
        <a class="nav-item" onclick="showSection('holidays',this)">&#127974; Holiday Calendar</a>
        <a class="nav-item" onclick="showSection('my_profile',this)">&#128100; My Profile</a>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>

<!-- Main -->
<div class="main-content">

    <!-- Topbar -->
    <div class="topbar">
        <h2 id="page-title">Dashboard</h2>
        <div class="topbar-right">
            <?php
            $unread_res=mysqli_query($conn,"SELECT COUNT(*) as cnt FROM notifications WHERE is_read=0");
            $unread_count=mysqli_fetch_assoc($unread_res)['cnt'];
            $all_notif=mysqli_query($conn,"SELECT * FROM notifications ORDER BY created_at DESC LIMIT 15");
            ?>
            <div class="notif-wrapper" id="notifWrapper">
                <div class="notif-bell" onclick="toggleNotif()">&#128276;
                    <?php if($unread_count>0): ?><span class="notif-badge"><?php echo $unread_count; ?></span><?php endif; ?>
                </div>
                <div class="notif-dropdown" id="notifDropdown">
                    <div class="notif-header">
                        <span>Notifications</span>
                        <?php if($unread_count>0): ?><a href="mark_notifications_read.php" style="font-size:11px;color:#3b82f6;text-decoration:none;padding:4px 10px;border-radius:20px;border:1px solid #3b82f6;">Mark all read</a><?php endif; ?>
                    </div>
                    <div class="notif-list">
                    <?php
                        $has_n=false;
                        while($n=mysqli_fetch_assoc($all_notif)){
                            $has_n=true; $nw=($n['is_read']==0)?'notif-new':'';
                            echo "<div class='notif-item {$nw}'><div class='notif-icon'>&#128203;</div>
                                <div class='notif-text'><strong>{$n['emp_name']}</strong> &mdash; <span class='notif-type'>{$n['leave_type']}</span><br>
                                <small>&#128197; {$n['from_date']} to {$n['to_date']}</small><br>
                                <small style='color:#9ca3af;font-style:italic;'>Reason: {$n['reason']}</small></div>
                                ".($n['is_read']==0?"<span class='notif-dot'></span>":"")."</div>";
                        }
                        if(!$has_n) echo "<div class='notif-empty'>No notifications yet</div>";
                    ?>
                    </div>
                </div>
            </div>
            <?php if(!empty($profile_photo)&&file_exists('uploads/'.$profile_photo)): ?>
                <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>"
                     onclick="showSection('my_profile',document.querySelector('[onclick*=my_profile]'))"
                     style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #3b82f6;cursor:pointer;">
            <?php else: ?>
                <div onclick="showSection('my_profile',document.querySelector('[onclick*=my_profile]'))"
                     style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:15px;cursor:pointer;">
                    <?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?>
                </div>
            <?php endif; ?>
            <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
        </div>
    </div>

    <!-- ===== DASHBOARD ===== -->
    <div id="dashboard" class="section active">
        <div class="cards">
            <div class="card"><h3>Total Employees</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM users WHERE role='employee'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
            <div class="card"><h3>Present Today</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status='present'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
            <div class="card"><h3>On Leave</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
            <div class="card"><h3>Pending Tasks</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
            <div class="card"><h3>Completed Tasks</h3><p class="num"><?php $r=mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"); echo mysqli_fetch_assoc($r)['t']; ?></p></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
            <div style="background:#fff;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);min-width:0;">
                <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Attendance</h3>
                <canvas id="adminAttChart"></canvas>
            </div>
            <div style="background:#fff;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);min-width:0;">
                <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Leave Requests</h3>
                <canvas id="adminLeaveChart"></canvas>
            </div>
        </div>
        <!-- Upcoming Holidays strip -->
        <?php
        $uph=mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date>=CURDATE() ORDER BY holiday_date LIMIT 4");
        $uphrows=[];
        while($u=mysqli_fetch_assoc($uph)) $uphrows[]=$u;
        if(!empty($uphrows)):
        ?>
        <div style="background:#fff;border-radius:10px;padding:20px;margin-top:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
            <h3 style="font-size:14px;color:#60a5fa;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #eee;">&#127974; Upcoming Holidays</h3>
            <div class="hol-cards" style="grid-template-columns:repeat(4,1fr);">
            <?php foreach($uphrows as $uh):
                $ht=$uh['holiday_type']??'National';
                $cc=['National'=>'#1d4ed8','Festival'=>'#d97706','State'=>'#16a34a'];
                $c=$cc[$ht]??'#6b7280';
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

    <!-- ===== ADD EMPLOYEE ===== -->
    <div id="add_employee" class="section">
        <div class="form-card">
            <form action="save_employee.php" method="POST" id="addEmpForm">
                <h3 class="section-title">Login Details</h3>
                <div class="form-grid">
                    <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="Full Name" required></div>
                    <div class="field"><label>Email</label><input type="email" name="email" placeholder="Email" required></div>
                    <div class="field"><label>Password</label><input type="password" name="password" placeholder="Min 8 chars, uppercase, number, special" required></div>
                    <div class="field"><label>Role</label>
                        <select name="role"><option value="employee">Employee</option><option value="admin">Admin</option><option value="super_admin">Super Admin</option></select>
                    </div>
                </div>
                <h3 class="section-title">Personal Details</h3>
                <div class="form-grid">
                    <div class="field"><label>First Name</label><input type="text" name="first_name" id="first_name" placeholder="Letters only" required></div>
                    <div class="field"><label>Last Name</label><input type="text" name="last_name" id="last_name" placeholder="Letters only" required></div>
                    <div class="field"><label>Contact (10 digits)</label><input type="text" name="contact" id="contact" placeholder="10-digit number" maxlength="10" required></div>
                    <div class="field"><label>Designation</label><input type="text" name="designation" placeholder="Designation" required></div>
                    <div class="field"><label>Blood Group</label>
                        <select name="blood_group"><option>A+</option><option>A-</option><option>B+</option><option>B-</option><option>O+</option><option>O-</option><option>AB+</option><option>AB-</option></select>
                    </div>
                    <div class="field"><label>Date of Birth</label><input type="date" name="dob" required></div>
                    <div class="field"><label>Religion</label><input type="text" name="religion" placeholder="Religion" required></div>
                    <div class="field"><label>Address</label><input type="text" name="address" placeholder="Address" required></div>
                </div>
                <button type="submit" class="submit-btn">Add Employee</button>
            </form>
        </div>
    </div>

    <!-- ===== VIEW EMPLOYEES ===== -->
    <div id="view_employees" class="section">
        <div class="form-card">
            <h3 class="section-title">All Employees</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Role</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT u.id,u.name,u.email,u.role,e.designation,e.contact FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'");
                    while($row=mysqli_fetch_assoc($res)) echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['designation']}</td><td>{$row['contact']}</td><td><span class='pill blue'>".ucfirst($row['role'])."</span></td></tr>";
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
                <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT e.first_name,e.last_name,a.date,a.check_in,a.check_out,a.status FROM attendance a JOIN employees e ON a.emp_id=e.emp_id ORDER BY a.date DESC");
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
                            $h=floor($secs/3600);$m=floor(($secs%3600)/60);
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

    <!-- ===== LEAVES ===== -->
    <div id="leaves" class="section">
        <div class="form-card">
            <h3 class="section-title">Leave Requests</h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>Employee</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT l.*,e.first_name,e.last_name FROM leaves l JOIN employees e ON l.emp_id=e.emp_id ORDER BY l.leave_id DESC");
                    while($row=mysqli_fetch_assoc($res)){
                        $days=(strtotime($row['to_date'])-strtotime($row['from_date']))/86400+1;
                        $pc=['approved'=>'green','rejected'=>'red','pending'=>'yellow'][$row['status']]??'yellow';
                        echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['leave_type']}</td><td>{$row['from_date']}</td><td>{$row['to_date']}</td><td>{$days}</td><td>{$row['reason']}</td>
                        <td><span class='pill {$pc}'>".ucfirst($row['status'])."</span></td>
                        <td><a href='leave_action.php?id={$row['leave_id']}&action=approved' class='approve-btn'>Approve</a>
                        <a href='leave_action.php?id={$row['leave_id']}&action=rejected' class='reject-btn'>Reject</a></td></tr>";
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
            <h3 class="section-title">Add Salary</h3>
            <form action="save_salary.php" method="POST">
                <div class="form-grid">
                    <div class="field"><label>Select Employee</label>
                        <select name="emp_id"><?php $res=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name FROM employees e"); while($row=mysqli_fetch_assoc($res)) echo "<option value='{$row['emp_id']}'>{$row['first_name']} {$row['last_name']}</option>"; ?></select>
                    </div>
                    <div class="field"><label>Basic Pay</label><input type="number" name="basic_pay" placeholder="Basic Pay" required></div>
                    <div class="field"><label>Allowances</label><input type="number" name="allowances" placeholder="Allowances" required></div>
                    <div class="field"><label>Deductions (PF)</label><input type="number" name="deductions" placeholder="Deductions" required></div>
                    <div class="field"><label>LOP Days</label><input type="number" name="lop_days" placeholder="LOP Days" value="0" min="0"></div>
                    <div class="field"><label>Month</label>
                        <select name="month"><option>January</option><option>February</option><option>March</option><option>April</option><option>May</option><option>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option></select>
                    </div>
                    <div class="field"><label>Year</label><input type="number" name="year" value="<?php echo date('Y');?>" required></div>
                </div>
                <button type="submit" class="submit-btn">Add Salary</button>
            </form>
            <h3 class="section-title" style="margin-top:28px;">Salary Records</h3>
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
            <h3 class="section-title">Assign Task</h3>
            <form action="save_task.php" method="POST">
                <div class="form-grid">
                    <div class="field"><label>Select Employee</label>
                        <select name="emp_id"><?php $res=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name FROM employees e"); while($row=mysqli_fetch_assoc($res)) echo "<option value='{$row['emp_id']}'>{$row['first_name']} {$row['last_name']}</option>"; ?></select>
                    </div>
                    <div class="field"><label>Task Name</label><input type="text" name="task_name" placeholder="Task Name" required></div>
                    <div class="field"><label>Description</label><input type="text" name="description" placeholder="Description" required></div>
                    <div class="field"><label>Target Date</label><input type="date" name="target_date" required></div>
                    <div class="field"><label>Status</label>
                        <select name="status"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select>
                    </div>
                    <div class="field"><label>Hours Worked</label><input type="number" name="hours_worked" placeholder="Hours" value="0" required></div>
                </div>
                <button type="submit" class="submit-btn">Assign Task</button>
            </form>
            <h3 class="section-title" style="margin-top:28px;">Task Records</h3>
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

    <!-- ===== LEAVE TYPES ===== -->
    <div id="leave_types" class="section">
        <div class="form-card">
            <h3 class="section-title">Add Leave Type</h3>
            <form action="save_leave_type.php" method="POST">
                <div class="form-grid">
                    <div class="field"><label>Leave Type Name</label><input type="text" name="leave_type_name" placeholder="e.g. Maternity Leave" required></div>
                    <div class="field"><label>Total Days Allowed</label><input type="number" name="total_days" placeholder="e.g. 10" required></div>
                </div>
                <button type="submit" class="submit-btn">Add Leave Type</button>
            </form>
            <h3 class="section-title" style="margin-top:28px;">All Leave Types</h3>
            <table class="emp-table">
                <thead><tr><th>Leave Type</th><th>Total Days</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                    $res=mysqli_query($conn,"SELECT * FROM leave_types ORDER BY id ASC");
                    while($row=mysqli_fetch_assoc($res))
                        echo "<tr><td><b>{$row['leave_type_name']}</b></td><td>{$row['total_days']}</td><td><a href='delete_leave_type.php?id={$row['id']}' class='reject-btn' onclick='return confirm(\"Delete this leave type?\")'>Delete</a></td></tr>";
                ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- ===== HOLIDAY CALENDAR ===== -->
    <div id="holidays" class="section">
        <!-- Summary stats -->
        <?php
        $totalH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())"))['c'];
        $natH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='National'"))['c'];
        $festH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='Festival'"))['c'];
        $stateH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='State'"))['c'];
        ?>
        <div class="hol-cards">
            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #3b82f6;">
                <p style="font-size:11px;color:#6b7280;margin:0;">Total Holidays <?php echo date('Y');?></p>
                <p style="font-size:32px;font-weight:800;color:#1a3a6e;margin:6px 0;"><?php echo $totalH;?></p>
            </div>
            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #1d4ed8;">
                <p style="font-size:11px;color:#6b7280;margin:0;">National Holidays</p>
                <p style="font-size:32px;font-weight:800;color:#1d4ed8;margin:6px 0;"><?php echo $natH;?></p>
            </div>
            <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #d97706;">
                <p style="font-size:11px;color:#6b7280;margin:0;">Festivals</p>
                <p style="font-size:32px;font-weight:800;color:#d97706;margin:6px 0;"><?php echo $festH;?></p>
            </div>
        </div>

        <!-- Calendar widget -->
        <div class="form-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
            <div id="calContainer"></div>
        </div>

        <!-- Add Holiday Form -->
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

        <!-- Holiday List -->
        <div class="form-card" style="margin-top:0;">
            <h3 class="section-title">&#127974; All Holidays <?php echo date('Y');?></h3>
            <div style="overflow-x:auto;">
            <table class="emp-table">
                <thead><tr><th>#</th><th>Holiday Name</th><th>Date</th><th>Day</th><th>Type</th><th>Action</th></tr></thead>
                <tbody>
                <?php
                    $hres=mysqli_query($conn,"SELECT * FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) ORDER BY holiday_date ASC");
                    $cnt=1;
                    while($h=mysqli_fetch_assoc($hres)){
                        $ht=$h['holiday_type']??'National';
                        $today_hi=($h['holiday_date']==date('Y-m-d'))?"background:#eff6ff;":"";
                        echo "<tr style='{$today_hi}'>
                            <td>{$cnt}</td>
                            <td><b>{$h['holiday_name']}</b></td>
                            <td>".date('d M Y',strtotime($h['holiday_date']))."</td>
                            <td>".date('l',strtotime($h['holiday_date']))."</td>
                            <td><span class='hl-badge {$ht}'>{$ht}</span></td>
                            <td><a href='delete_holiday.php?id={$h['id']}' class='reject-btn' onclick='return confirm(\"Delete this holiday?\")'>&#128465; Delete</a></td>                        </tr>";
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
            <?php if(!empty($profile_photo)&&file_exists('uploads/'.$profile_photo)): ?>
                <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #3b82f6;margin-bottom:16px;">
            <?php endif; ?>
            <form action="save_profile_photo.php" method="POST" enctype="multipart/form-data">
                <div class="field"><label>Upload Profile Photo</label><input type="file" name="profile_photo" accept="image/*" required></div>
                <button type="submit" class="submit-btn">Update Photo</button>
            </form>
            <h3 class="section-title" style="margin-top:28px;">My Details</h3>
            <table class="emp-table">
                <tr><td><b>Name</b></td><td><?php echo $_SESSION['user']['name']; ?></td></tr>
                <tr><td><b>Email</b></td><td><?php echo $_SESSION['user']['email']; ?></td></tr>
                <tr><td><b>Role</b></td><td><span class="pill blue"><?php echo ucfirst(str_replace('_',' ',$_SESSION['user']['role'])); ?></span></td></tr>
            </table>
        </div>
    </div>

</div><!-- /main-content -->
</div><!-- /dashboard -->

<?php
$att_data=array_fill(0,12,0);
$att_res=mysqli_query($conn,"SELECT MONTH(date) as mon,COUNT(*) as cnt FROM attendance WHERE status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
while($r=mysqli_fetch_assoc($att_res)) $att_data[$r['mon']-1]=$r['cnt'];
$leave_data=array_fill(0,12,0);
$leave_res=mysqli_query($conn,"SELECT MONTH(from_date) as mon,COUNT(*) as cnt FROM leaves WHERE YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
while($r=mysqli_fetch_assoc($leave_res)) $leave_data[$r['mon']-1]=$r['cnt'];
?>
<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const fullMonths=['January','February','March','April','May','June','July','August','September','October','November','December'];
const dayNames=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const holidays=<?php echo $holidays_json; ?>;
const todayStr="<?php echo date('Y-m-d'); ?>";
let calYear=<?php echo date('Y'); ?>;
let calMonth=<?php echo date('n')-1; ?>;

new Chart(document.getElementById('adminAttChart'),{
    type:'bar',data:{labels:months,datasets:[{label:'Present',data:<?php echo json_encode($att_data);?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
new Chart(document.getElementById('adminLeaveChart'),{
    type:'bar',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo json_encode($leave_data);?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});

function buildCalendar(year,month){
    const container=document.getElementById('calContainer');
    if(!container) return;
    const firstDay=new Date(year,month,1).getDay();
    const daysInMonth=new Date(year,month+1,0).getDate();
    let html=`<div class="cal-top">
        <h3>&#128197; ${fullMonths[month]} ${year}</h3>
        <div><button class="cal-nav-btn" onclick="changeMonth(-1)">&#8592; Prev</button>
             <button class="cal-nav-btn" style="margin-left:8px;" onclick="changeMonth(1)">Next &#8594;</button></div>
    </div><div class="cal-grid">`;
    dayNames.forEach(d=>{html+=`<div class="cal-day-name">${d}</div>`;});
    for(let i=0;i<firstDay;i++) html+=`<div class="cal-cell empty"></div>`;
    for(let d=1;d<=daysInMonth;d++){
        const mm=String(month+1).padStart(2,'0');
        const dd=String(d).padStart(2,'0');
        const ds=`${year}-${mm}-${dd}`;
        const isSun=new Date(year,month,d).getDay()===0;
        const isToday=ds===todayStr;
        const isHol=holidays[ds]!==undefined;
        let cls='cal-cell';
        if(isToday) cls+=' today';
        else if(isHol) cls+=' holiday';
        else if(isSun) cls+=' sunday';
        let hname=isHol?`<div class="cal-hname">${holidays[ds].name}</div>`:(isSun?`<div class="cal-hname" style="color:#9ca3af;">Sunday</div>`:'');
        html+=`<div class="${cls}"><span class="cal-num">${d}</span>${hname}</div>`;
    }
    html+=`</div>`;
    container.innerHTML=html;
}
function changeMonth(dir){
    calMonth+=dir;
    if(calMonth>11){calMonth=0;calYear++;}
    if(calMonth<0){calMonth=11;calYear--;}
    buildCalendar(calYear,calMonth);
}
buildCalendar(calYear,calMonth);

function showSection(name,el){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n=>n.classList.remove('active'));
    document.getElementById(name).classList.add('active');
    el.classList.add('active');
    document.getElementById('page-title').innerText=el.innerText;
    if(name==='holidays') setTimeout(()=>buildCalendar(calYear,calMonth),50);
}
function toggleNotif(){document.getElementById('notifDropdown').classList.toggle('open');}
document.addEventListener('click',function(e){
    const w=document.getElementById('notifWrapper');
    if(w&&!w.contains(e.target)) document.getElementById('notifDropdown').classList.remove('open');
});
document.getElementById('addEmpForm').addEventListener('submit',function(e){
    const fn=document.getElementById('first_name').value.trim();
    const ln=document.getElementById('last_name').value.trim();
    const ct=document.getElementById('contact').value.trim();
    if(!/^[a-zA-Z\s]+$/.test(fn)||!/^[a-zA-Z\s]+$/.test(ln)){
        e.preventDefault();alert('Name should contain letters only!');return;
    }
    if(!/^[0-9]{10}$/.test(ct)){
        e.preventDefault();alert('Contact must be exactly 10 digits!');return;
    }
});
let timeLeft=1800;
function resetTimer(){timeLeft=1800;}
['mousemove','keydown','click','scroll'].forEach(e=>document.addEventListener(e,resetTimer,{passive:true}));
setInterval(()=>{timeLeft--;if(timeLeft<=0){alert('Session expired. Logging out...');window.location.href='logout.php';}},1000);
</script>
</body>
</html>