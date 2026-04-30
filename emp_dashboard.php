<?php

session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}
require 'db.php';

$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];

$photo_res = mysqli_query($conn, "SELECT profile_photo FROM users WHERE id='$user_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';

// Check if lop columns exist in salary table
$lop_check = mysqli_query($conn, "SHOW COLUMNS FROM salary LIKE 'lop_days'");
$has_lop = mysqli_num_rows($lop_check) > 0;
if(!$has_lop){
    mysqli_query($conn, "ALTER TABLE salary ADD COLUMN lop_days INT DEFAULT 0");
    mysqli_query($conn, "ALTER TABLE salary ADD COLUMN lop_amount DECIMAL(10,2) DEFAULT 0");
}

// Check/create performance table
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `performance` (
  `perf_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT DEFAULT NULL,
  `skill_name` VARCHAR(200) DEFAULT NULL,
  `description` TEXT,
  `date_added` DATE DEFAULT NULL,
  PRIMARY KEY (`perf_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Check/create holidays table
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `holidays` (
  `holiday_id` INT NOT NULL AUTO_INCREMENT,
  `holiday_name` VARCHAR(200) DEFAULT NULL,
  `holiday_date` DATE DEFAULT NULL,
  `holiday_type` VARCHAR(50) DEFAULT 'National',
  PRIMARY KEY (`holiday_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ✅ FIX: Add holiday_type column if it doesn't exist (for older tables)
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM holidays LIKE 'holiday_type'");
if(mysqli_num_rows($col_check) == 0){
    mysqli_query($conn, "ALTER TABLE holidays ADD COLUMN holiday_type VARCHAR(50) DEFAULT 'National'");
    mysqli_query($conn, "UPDATE holidays SET holiday_type='National' WHERE holiday_type IS NULL");
}

// Seed default Indian holidays if empty
$hcount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as cnt FROM holidays"));
if($hcount['cnt'] == 0){
    $year = date('Y');
    $defaults = [
        ["Republic Day",          "$year-01-26", "National"],
        ["Holi",                  "$year-03-14", "Festival"],
        ["Good Friday",           "$year-04-18", "National"],
        ["Dr. Ambedkar Jayanti",  "$year-04-14", "National"],
        ["Maharashtra Day",       "$year-05-01", "State"],
        ["Independence Day",      "$year-08-15", "National"],
        ["Ganesh Chaturthi",      "$year-08-27", "Festival"],
        ["Gandhi Jayanti",        "$year-10-02", "National"],
        ["Dussehra",              "$year-10-02", "Festival"],
        ["Diwali",                "$year-10-20", "Festival"],
        ["Diwali (Laxmi Puja)",   "$year-10-21", "Festival"],
        ["Gurunanak Jayanti",     "$year-11-05", "National"],
        ["Christmas",             "$year-12-25", "National"],
    ];
    foreach($defaults as $h){
        $nm = mysqli_real_escape_string($conn, $h[0]);
        mysqli_query($conn, "INSERT INTO holidays (holiday_name, holiday_date, holiday_type) VALUES ('$nm','$h[1]','$h[2]')");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Dashboard - EMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    /* ---------- Notification Bell ---------- */
    .notif-wrapper{position:relative;}
    .notif-bell{font-size:20px;cursor:pointer;position:relative;display:inline-block;padding:4px 8px;border-radius:8px;transition:background .2s;}
    .notif-bell:hover{background:rgba(59,130,246,.1);}
    .notif-badge{position:absolute;top:-4px;right:-4px;background:#ef4444;color:#fff;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;animation:pulse 1.5s infinite;}
    @keyframes pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.2)}}
    .notif-dropdown{display:none;position:absolute;right:0;top:42px;width:320px;background:#fff;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,.15);z-index:9999;overflow:hidden;border:1px solid #e5e7eb;}
    .notif-dropdown.open{display:block;animation:slideDown .2s ease;}
    @keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
    .notif-header{display:flex;justify-content:space-between;align-items:center;padding:14px 16px;border-bottom:1px solid #f0f0f0;font-size:14px;font-weight:600;color:#1a1a2e;background:#f8fafc;}
    .notif-list{max-height:320px;overflow-y:auto;}
    .notif-item{display:flex;align-items:flex-start;gap:10px;padding:12px 16px;border-bottom:1px solid #f5f5f5;transition:background .2s;}
    .notif-item:hover{background:#f8fafc;}
    .notif-item.notif-new{background:#eff6ff;}
    .notif-icon{font-size:18px;flex-shrink:0;}
    .notif-text{flex:1;font-size:13px;color:#374151;line-height:1.6;}
    .notif-type{background:#dbeafe;color:#1d4ed8;font-size:11px;padding:1px 7px;border-radius:20px;font-weight:600;}
    .notif-dot{width:8px;height:8px;background:#3b82f6;border-radius:50%;flex-shrink:0;margin-top:6px;}
    .notif-empty{text-align:center;padding:30px;color:#9ca3af;font-size:13px;}
    .topbar-right{display:flex;align-items:center;gap:16px;}
    .five-cards{display:grid;grid-template-columns:repeat(5,1fr);gap:16px;}

    /* ---------- Holiday Calendar ---------- */
    .cal-wrap{background:#fff;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:24px;}
    .cal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;}
    .cal-header h3{font-size:16px;font-weight:700;color:#1a1a2e;}
    .cal-nav button{background:#1a3a6e;color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;font-size:13px;}
    .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
    .cal-day-name{text-align:center;font-size:12px;font-weight:700;color:#6b7280;padding:6px 0;}
    .cal-cell{min-height:52px;border-radius:8px;padding:4px 6px;font-size:12px;border:1px solid #f0f0f0;cursor:default;position:relative;}
    .cal-cell.today{background:#eff6ff;border-color:#3b82f6;font-weight:700;}
    .cal-cell.holiday{background:#fef2f2;border-color:#fca5a5;}
    .cal-cell.sunday{background:#f9fafb;}
    .cal-cell.empty{border:none;background:none;}
    .cal-cell .date-num{font-weight:600;color:#374151;}
    .cal-cell.holiday .date-num{color:#dc2626;}
    .cal-cell .hname{font-size:9px;color:#dc2626;line-height:1.2;margin-top:2px;word-break:break-word;}
    .holiday-list{margin-top:20px;}
    .holiday-list table{width:100%;}
    .hl-badge{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:600;}
    .hl-badge.National{background:#dbeafe;color:#1d4ed8;}
    .hl-badge.Festival{background:#fef3c7;color:#d97706;}
    .hl-badge.State{background:#dcfce7;color:#16a34a;}

    /* ---------- Performance / Timesheet ---------- */
    .skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
    .ts-row{display:grid;grid-template-columns:1fr 1fr 1fr 1fr 1fr 1fr;gap:12px;margin-bottom:12px;}
    .status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
    .status-pill.approved{background:#dcfce7;color:#16a34a;}
    .status-pill.rejected{background:#fee2e2;color:#dc2626;}
    .status-pill.pending{background:#fef3c7;color:#d97706;}
    .status-pill.completed{background:#dcfce7;color:#16a34a;}
    .status-pill.in_progress{background:#fef3c7;color:#d97706;}
    </style>
</head>
<body>
<div class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.08);">
            <img src="allerlogo.png" alt="Aller Technologies" style="height:55px;display:block;margin:0 auto 8px auto;">
            <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,0.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
        </div>
        <nav>
            <a class="nav-item active" onclick="showSection('dashboard',this)">&#127968; Dashboard</a>
            <a class="nav-item" onclick="showSection('attendance',this)">&#128197; My Attendance</a>
            <a class="nav-item" onclick="showSection('leaves',this)">&#127809; My Leaves</a>
            <a class="nav-item" onclick="showSection('salary',this)">&#128176; My Salary</a>
            <a class="nav-item" onclick="showSection('tasks',this)">&#9989; My Tasks</a>
            <a class="nav-item" onclick="showSection('timesheet',this)">&#9200; Timesheet</a>
            <a class="nav-item" onclick="showSection('performance',this)">&#127941; My Performance</a>
            <a class="nav-item" onclick="showSection('holidays',this)">&#127974; Holiday Calendar</a>
            <a class="nav-item" onclick="showSection('profile',this)">&#128100; My Profile</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,0.07);">
            <p style="font-size:10px;color:rgba(255,255,255,0.22);text-align:center;line-height:1.8;">
                &copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <h2 id="page-title">Dashboard</h2>
            <div class="topbar-right">
                <?php
                $emp_notif_res  = mysqli_query($conn, "SELECT * FROM notifications WHERE emp_id='$emp_id' ORDER BY created_at DESC LIMIT 10");
                $emp_unread_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM notifications WHERE emp_id='$emp_id' AND is_read=0");
                $emp_unread_row = mysqli_fetch_assoc($emp_unread_res);
                $emp_unread_count = $emp_unread_row['cnt'];
                ?>
                <div class="notif-wrapper" id="notifWrapper">
                    <div class="notif-bell" onclick="toggleNotif()">
                        &#128276;
                        <?php if($emp_unread_count > 0): ?>
                            <span class="notif-badge"><?php echo $emp_unread_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <span>My Notifications</span>
                            <?php if($emp_unread_count > 0): ?>
                                <a href="mark_notifications_read.php" style="font-size:11px;color:#3b82f6;text-decoration:none;padding:4px 10px;border-radius:20px;border:1px solid #3b82f6;">Mark all read</a>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                        <?php
                            $has_notif = false;
                            while($n = mysqli_fetch_assoc($emp_notif_res)){
                                $has_notif = true;
                                $is_new = ($n['is_read'] == 0) ? 'notif-new' : '';
                                echo "<div class='notif-item {$is_new}'>
                                    <div class='notif-icon'>&#128203;</div>
                                    <div class='notif-text'>
                                        <span class='notif-type'>{$n['leave_type']}</span><br>
                                        <small>{$n['reason']}</small><br>
                                        <small style='color:#9ca3af;'>&#128197; {$n['from_date']}</small>
                                    </div>
                                    ".($n['is_read']==0?"<span class='notif-dot'></span>":"")."
                                </div>";
                            }
                            if(!$has_notif) echo "<div class='notif-empty'>No notifications yet</div>";
                        ?>
                        </div>
                    </div>
                </div>
                <?php if(!empty($profile_photo) && file_exists('uploads/'.$profile_photo)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>"
                         style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #3b82f6;">
                <?php endif; ?>
                <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
            </div>
        </div>

        <!-- ===================== DASHBOARD ===================== -->
        <div id="dashboard" class="section active">
            <div class="five-cards">
                <div class="card">
                    <h3>My Attendance</h3>
                    <p class="num"><?php
                        $r = mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE emp_id='$emp_id'");
                        echo mysqli_fetch_assoc($r)['t'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>My Leaves</h3>
                    <p class="num"><?php
                        $r = mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id'");
                        echo mysqli_fetch_assoc($r)['t'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>My Tasks</h3>
                    <p class="num"><?php
                        $r = mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE emp_id='$emp_id'");
                        echo mysqli_fetch_assoc($r)['t'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>Pending Leaves</h3>
                    <p class="num"><?php
                        $r = mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND status='pending'");
                        echo mysqli_fetch_assoc($r)['t'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>WFH This Month</h3>
                    <p class="num"><?php
                        $r = mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE emp_id='$emp_id' AND status='work_from_home' AND MONTH(date)=MONTH(CURDATE()) AND YEAR(date)=YEAR(CURDATE())");
                        echo mysqli_fetch_assoc($r)['t'];
                    ?></p>
                </div>
            </div>

            <!-- Today Check-in / Check-out -->
            <?php
            $today_att = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND date=CURDATE()"));
            ?>
            <?php if($today_att): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:20px;">
                <div style="background:#eff6ff;border-radius:10px;padding:20px;text-align:center;">
                    <div style="font-size:28px;">&#9200;</div>
                    <p style="color:#6b7280;font-size:12px;margin:4px 0;">Today Check-In</p>
                    <p style="font-size:22px;font-weight:700;color:#1d4ed8;"><?php echo $today_att['check_in']; ?></p>
                </div>
                <div style="background:#f0fdf4;border-radius:10px;padding:20px;text-align:center;">
                    <div style="font-size:28px;">&#9654;</div>
                    <p style="color:#6b7280;font-size:12px;margin:4px 0;">Today Check-Out</p>
                    <p style="font-size:22px;font-weight:700;color:#16a34a;"><?php echo $today_att['check_out']; ?></p>
                </div>
            </div>
            <?php else: ?>
            <div style="background:#fef3c7;border-radius:10px;padding:16px;margin-top:20px;text-align:center;color:#92400e;font-size:14px;">
                &#9888; Attendance not marked yet for today!
            </div>
            <?php endif; ?>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
                <div style="background:white;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
                    <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Attendance</h3>
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div style="background:white;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
                    <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Leaves</h3>
                    <canvas id="leaveChart"></canvas>
                </div>
            </div>

            <!-- Upcoming Holidays strip -->
            <?php
            $upcoming = mysqli_query($conn,"SELECT * FROM holidays WHERE holiday_date >= CURDATE() ORDER BY holiday_date LIMIT 4");
            $urows = [];
            while($uh = mysqli_fetch_assoc($upcoming)) $urows[] = $uh;
            ?>
            <?php if(!empty($urows)): ?>
            <div style="background:white;border-radius:10px;padding:20px;margin-top:20px;box-shadow:0 2px 10px rgba(0,0,0,.06);">
                <h3 style="font-size:14px;color:#60a5fa;margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid #eee;">&#127974; Upcoming Holidays</h3>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;">
                <?php foreach($urows as $uh):
                    $dt = date('d M', strtotime($uh['holiday_date']));
                    $day = date('D', strtotime($uh['holiday_date']));
                    $colors = ['National'=>'#1d4ed8','Festival'=>'#d97706','State'=>'#16a34a'];
                    $htype = $uh['holiday_type'] ?? 'National'; $c = $colors[$htype] ?? '#6b7280';
                ?>
                    <div style="border-left:4px solid <?php echo $c;?>;padding:10px 14px;background:#f8fafc;border-radius:8px;">
                        <p style="font-size:11px;color:#9ca3af;margin:0;"><?php echo $day.', '.$dt;?></p>
                        <p style="font-size:13px;font-weight:700;color:#1a1a2e;margin:4px 0;"><?php echo $uh['holiday_name'];?></p>
                        <span class="hl-badge <?php echo $htype;?>"><?php echo $uh['holiday_type'];?></span>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- ===================== ATTENDANCE ===================== -->
        <div id="attendance" class="section">
            <div class="form-card">
                <h3 class="section-title">Add Attendance</h3>
                <form action="save_attendance.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Date</label>
                            <input type="date" name="date"
                                value="<?php echo date('Y-m-d'); ?>"
                                min="<?php echo date('Y-m-d'); ?>"
                                max="<?php echo date('Y-m-d'); ?>"
                                required>
                        </div>
                        <div class="field"><label>Check In</label><input type="time" name="check_in" required></div>
                        <div class="field"><label>Check Out</label><input type="time" name="check_out" required></div>
                        <div class="field"><label>Status</label>
                            <select name="status">
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="half_day">Half Day</option>
                                <option value="work_from_home">Work From Home</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Mark Attendance</button>
                </form>
            </div>

            <div class="form-card" style="margin-top:20px;">
                <h3 class="section-title">My Late Coming Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                        $res = mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND (status='late' OR (TIME(check_in)>'09:30:00')) ORDER BY date DESC");
                        $found=false;
                        while($row=mysqli_fetch_assoc($res)){
                            $found=true;
                            echo "<tr><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td><span class='status-pill pending'>Late</span></td></tr>";
                        }
                        if(!$found) echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No late coming records found.</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="form-card" style="margin-top:20px;">
                <h3 class="section-title">All My Attendance Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Hours Worked</th></tr></thead>
                    <tbody>
                    <?php
                        $res = mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' ORDER BY date DESC");
                        $found=false;
                        while($row=mysqli_fetch_assoc($res)){
                            $found=true;
                            $hrs = '';
                            if($row['check_in'] && $row['check_out']){
                                $in  = strtotime($row['check_in']);
                                $out = strtotime($row['check_out']);
                                if($out > $in){
                                    $diff = ($out - $in)/3600;
                                    $hrs  = number_format($diff,1).' hrs';
                                }
                            }
                            $st_map = ['present'=>'approved','late'=>'pending','half_day'=>'pending','work_from_home'=>'approved','absent'=>'rejected'];
                            $pill = $st_map[$row['status']] ?? 'pending';
                            echo "<tr>
                                <td>{$row['date']}</td>
                                <td>{$row['check_in']}</td>
                                <td>{$row['check_out']}</td>
                                <td><span class='status-pill $pill'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                                <td>{$hrs}</td>
                            </tr>";
                        }
                        if(!$found) echo "<tr><td colspan='5' style='text-align:center;color:#9ca3af;'>No attendance records found.</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== LEAVES ===================== -->
        <div id="leaves" class="section">
            <div class="form-card">
                <h3 class="section-title">Apply for Leave</h3>
                <form action="save_leave.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Leave Type</label>
                            <select name="leave_type" required>
                                <option value="">-- Select --</option>
                                <?php
                                $lt = mysqli_query($conn,"SELECT * FROM leave_types");
                                while($l=mysqli_fetch_assoc($lt)) echo "<option value='{$l['leave_type_name']}'>{$l['leave_type_name']}</option>";                                ?>
                            </select>
                        </div>
                        <div class="field"><label>From Date</label><input type="date" name="from_date" required></div>
                        <div class="field"><label>To Date</label><input type="date" name="to_date" required></div>
                        <div class="field" style="grid-column:1/-1"><label>Reason</label><textarea name="reason" rows="3" placeholder="Enter reason for leave..." required></textarea></div>
                    </div>
                    <button type="submit" class="submit-btn">Apply Leave</button>
                </form>
            </div>

            <div class="form-card" style="margin-top:20px;">
                <h3 class="section-title">My Leave Balance</h3>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                <?php
                $lt_all = mysqli_query($conn,"SELECT * FROM leave_types");
                while($lt_row=mysqli_fetch_assoc($lt_all)){
                    $used_r = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM leaves WHERE emp_id='$emp_id' AND leave_type='{$lt_row['leave_type_name']}' AND status='approved'"));                     $used = $used_r['c'];
                    $total = 12;
                    $remaining = max(0,$total - $used);
                    echo "<div style='background:#f8fafc;border-radius:10px;padding:16px;text-align:center;border:1px solid #e5e7eb;'>
                        <p style='font-size:12px;color:#6b7280;margin:0;'>{$lt_row['leave_type_name']}</p>
                        <p style='font-size:24px;font-weight:700;color:#1a3a6e;margin:6px 0;'>{$remaining}</p>
                        <p style='font-size:11px;color:#9ca3af;margin:0;'>Remaining / {$total}</p>
                    </div>";
                }
                ?>
                </div>
            </div>

            <div class="form-card" style="margin-top:20px;">
                <h3 class="section-title">My Leave Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Leave Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                        $res = mysqli_query($conn,"SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC");
                        $found=false;
                        while($row=mysqli_fetch_assoc($res)){
                            $found=true;
                            $days = (strtotime($row['to_date'])-strtotime($row['from_date']))/86400 + 1;
                            echo "<tr>
                                <td>{$row['leave_type']}</td>
                                <td>{$row['from_date']}</td>
                                <td>{$row['to_date']}</td>
                                <td>{$days}</td>
                                <td>{$row['reason']}</td>
                                <td><span class='status-pill {$row['status']}'>".ucfirst($row['status'])."</span></td>
                            </tr>";
                        }
                        if(!$found) echo "<tr><td colspan='6' style='text-align:center;color:#9ca3af;'>No leave records found.</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== SALARY ===================== -->
        <div id="salary" class="section">
            <div class="form-card">
                <h3 class="section-title">My Salary Details</h3>
                <?php
                    $sal_check = mysqli_query($conn,"SELECT * FROM salary WHERE emp_id='$emp_id'");
                    if(mysqli_num_rows($sal_check) == 0){
                        echo "<div style='text-align:center;padding:30px;color:#9ca3af;'>No salary records found. Please contact Admin.</div>";
                    }
                ?>
                <table class="emp-table">
                    <thead><tr><th>Month</th><th>Year</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>LOP Days</th><th>Net Pay</th><th>Slip</th></tr></thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn,"SELECT * FROM salary WHERE emp_id='$emp_id' ORDER BY year DESC, salary_id DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            $lop_d = isset($row['lop_days']) ? $row['lop_days'] : 0;
                            echo "<tr>
                                <td>{$row['month']}</td>
                                <td>{$row['year']}</td>
                                <td>&#8377; ".number_format($row['basic_pay'],2)."</td>
                                <td>&#8377; ".number_format($row['allowances'],2)."</td>
                                <td>&#8377; ".number_format($row['deductions'],2)."</td>
                                <td><span style='color:#ef4444;'>{$lop_d} days</span></td>
                                <td><b>&#8377; ".number_format($row['net_pay'],2)."</b></td>
                                <td><a href='generate_salary_slip.php?salary_id={$row['salary_id']}'
                                    style='background:#1a3a6e;color:white;padding:4px 12px;border-radius:6px;text-decoration:none;font-size:12px;'>
                                    &#128196; Download</a></td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== TASKS ===================== -->
        <div id="tasks" class="section">
            <div class="form-card">
                <h3 class="section-title">My Tasks</h3>
                <?php
                    $task_check = mysqli_query($conn,"SELECT * FROM tasks WHERE emp_id='$emp_id'");
                    if(mysqli_num_rows($task_check) == 0){
                        echo "<div style='text-align:center;padding:30px;color:#9ca3af;'>No tasks assigned yet. Please contact Admin.</div>";
                    }
                ?>
                <?php
                $t_total    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE emp_id='$emp_id'"))['c'];
                $t_done     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE emp_id='$emp_id' AND status='completed'"))['c'];
                $t_inprog   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE emp_id='$emp_id' AND status='in_progress'"))['c'];
                $t_pending  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE emp_id='$emp_id' AND status='pending'"))['c'];
                ?>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;">
                    <div style="background:#eff6ff;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">Total</p><p style="font-size:22px;font-weight:700;color:#1a3a6e;margin:4px 0;"><?php echo $t_total;?></p></div>
                    <div style="background:#dcfce7;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">Completed</p><p style="font-size:22px;font-weight:700;color:#16a34a;margin:4px 0;"><?php echo $t_done;?></p></div>
                    <div style="background:#fef3c7;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">In Progress</p><p style="font-size:22px;font-weight:700;color:#d97706;margin:4px 0;"><?php echo $t_inprog;?></p></div>
                    <div style="background:#fee2e2;border-radius:8px;padding:14px;text-align:center;"><p style="font-size:11px;color:#6b7280;margin:0;">Pending</p><p style="font-size:22px;font-weight:700;color:#dc2626;margin:4px 0;"><?php echo $t_pending;?></p></div>
                </div>
                <table class="emp-table">
                    <thead><tr><th>Task Name</th><th>Description</th><th>Target Date</th><th>Status</th><th>Hours</th></tr></thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn,"SELECT * FROM tasks WHERE emp_id='$emp_id' ORDER BY target_date DESC");
                        $found=false;
                        while($row = mysqli_fetch_assoc($result)){
                            $found=true;
                            $pill = ['completed'=>'completed','in_progress'=>'in_progress','pending'=>'pending'][$row['status']] ?? 'pending';
                            echo "<tr>
                                <td><b>{$row['task_name']}</b></td>
                                <td>{$row['description']}</td>
                                <td>{$row['target_date']}</td>
                                <td><span class='status-pill {$pill}'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                                <td>{$row['hours_worked']} hrs</td>
                            </tr>";
                        }
                        if(!$found) echo "<tr><td colspan='5' style='text-align:center;color:#9ca3af;'>No tasks found.</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== TIMESHEET ===================== -->
        <div id="timesheet" class="section">
            <div class="form-card">
                <h3 class="section-title">&#9200; My Timesheet</h3>
                <p style="color:#6b7280;font-size:13px;margin-bottom:20px;">Your daily check-in/check-out summary. Submitted timesheets are reviewed by Super Admin.</p>

                <form method="GET" style="display:flex;gap:12px;align-items:flex-end;margin-bottom:20px;">
                    <div class="field" style="margin:0;">
                        <label>Filter Month</label>
                        <input type="month" name="ts_month" value="<?php echo isset($_GET['ts_month']) ? $_GET['ts_month'] : date('Y-m'); ?>" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;">
                    </div>
                    <button type="submit" class="submit-btn" style="margin:0;padding:8px 20px;">Filter</button>
                    <a href="emp_dashboard.php" style="padding:8px 16px;background:#f3f4f6;color:#374151;border-radius:8px;text-decoration:none;font-size:13px;">Reset</a>
                </form>

                <?php
                $ts_month  = isset($_GET['ts_month']) ? $_GET['ts_month'] : date('Y-m');
                $ts_year   = substr($ts_month,0,4);
                $ts_mon    = substr($ts_month,5,2);
                $ts_res    = mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND YEAR(date)='$ts_year' AND MONTH(date)='$ts_mon' ORDER BY date ASC");
                $total_hrs = 0;
                $rows_ts   = [];
                while($r=mysqli_fetch_assoc($ts_res)){
                    $hrs=0;
                    if($r['check_in']&&$r['check_out']){
                        $in=strtotime($r['check_in']); $out=strtotime($r['check_out']);
                        if($out>$in) $hrs=($out-$in)/3600;
                    }
                    $r['computed_hrs']=$hrs;
                    $total_hrs+=$hrs;
                    $rows_ts[]=$r;
                }
                ?>

                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px;">
                    <div style="background:#eff6ff;border-radius:8px;padding:14px;text-align:center;">
                        <p style="font-size:11px;color:#6b7280;margin:0;">Total Days Marked</p>
                        <p style="font-size:22px;font-weight:700;color:#1a3a6e;margin:4px 0;"><?php echo count($rows_ts);?></p>
                    </div>
                    <div style="background:#dcfce7;border-radius:8px;padding:14px;text-align:center;">
                        <p style="font-size:11px;color:#6b7280;margin:0;">Total Hours Worked</p>
                        <p style="font-size:22px;font-weight:700;color:#16a34a;margin:4px 0;"><?php echo number_format($total_hrs,1);?> hrs</p>
                    </div>
                    <div style="background:#fef3c7;border-radius:8px;padding:14px;text-align:center;">
                        <p style="font-size:11px;color:#6b7280;margin:0;">Avg Hours/Day</p>
                        <p style="font-size:22px;font-weight:700;color:#d97706;margin:4px 0;"><?php echo count($rows_ts)>0 ? number_format($total_hrs/count($rows_ts),1) : '0';?> hrs</p>
                    </div>
                </div>

                <table class="emp-table">
                    <thead><tr><th>Date</th><th>Day</th><th>Check In</th><th>Check Out</th><th>Hours Worked</th><th>Status</th><th>Overtime</th></tr></thead>
                    <tbody>
                    <?php
                    if(empty($rows_ts)){
                        echo "<tr><td colspan='7' style='text-align:center;color:#9ca3af;'>No records for selected month.</td></tr>";
                    }
                    foreach($rows_ts as $row){
                        $hrs = $row['computed_hrs'];
                        $ot  = max(0,$hrs-8);
                        $day_name = date('D', strtotime($row['date']));
                        $pill_map = ['present'=>'approved','late'=>'pending','half_day'=>'pending','work_from_home'=>'approved','absent'=>'rejected'];
                        $pill = $pill_map[$row['status']] ?? 'pending';
                        echo "<tr>
                            <td>{$row['date']}</td>
                            <td>{$day_name}</td>
                            <td>{$row['check_in']}</td>
                            <td>{$row['check_out']}</td>
                            <td>".($hrs>0 ? number_format($hrs,1)." hrs" : "-")."</td>
                            <td><span class='status-pill $pill'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                            <td>".($ot>0 ? "<span style='color:#16a34a;font-weight:600;'>+".number_format($ot,1)." hrs</span>" : "-")."</td>
                        </tr>";
                    }
                    ?>
                    </tbody>
                </table>
                <?php if(!empty($rows_ts)): ?>
                <p style="text-align:right;font-size:12px;color:#9ca3af;margin-top:12px;">* Overtime = Hours worked beyond 8 hrs/day.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- ===================== PERFORMANCE ===================== -->
        <div id="performance" class="section">
            <div class="form-card">
                <h3 class="section-title">&#127941; Add New Skill / Improvement</h3>
                <form action="save_performance.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Skill / Course Name</label><input type="text" name="skill_name" placeholder="e.g. PHP, MySQL, React JS" required></div>
                        <div class="field"><label>Date Learned</label><input type="date" name="date_added" value="<?php echo date('Y-m-d');?>" required></div>
                        <div class="field" style="grid-column:1/-1"><label>Description / Details</label><textarea name="description" rows="3" placeholder="Describe what you learned or improved..." required></textarea></div>
                    </div>
                    <button type="submit" class="submit-btn">Save</button>
                </form>
            </div>

            <div class="form-card" style="margin-top:20px;">
                <h3 class="section-title">My Skills</h3>
                <?php
                $skills = mysqli_query($conn,"SELECT skill_name FROM performance WHERE emp_id='$emp_id'");
                if(mysqli_num_rows($skills)==0){
                    echo "<p style='color:#9ca3af;text-align:center;padding:20px;'>No skills added yet.</p>";
                } else {
                    echo "<div style='padding:10px 0;'>";
                    mysqli_data_seek($skills,0);
                    while($sk=mysqli_fetch_assoc($skills)){
                        echo "<span class='skill-tag'>&#10003; {$sk['skill_name']}</span>";
                    }
                    echo "</div>";
                }
                ?>
            </div>

            <div class="form-card" style="margin-top:20px;">
                <h3 class="section-title">My Improvement Plan Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Skill/Course</th><th>Description</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php
                        $res = mysqli_query($conn,"SELECT * FROM performance WHERE emp_id='$emp_id' ORDER BY date_added DESC");
                        $found=false;
                        while($row=mysqli_fetch_assoc($res)){
                            $found=true;
                            echo "<tr>
                                <td><b>{$row['skill_name']}</b></td>
                                <td>{$row['description']}</td>
                                <td>{$row['date_added']}</td>
                            </tr>";
                        }
                        if(!$found) echo "<tr><td colspan='3' style='text-align:center;color:#9ca3af;'>No records found.</td></tr>";
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ===================== HOLIDAY CALENDAR ===================== -->
        <div id="holidays" class="section">
            <div class="form-card">
                <h3 class="section-title">&#127974; Holiday Calendar <?php echo date('Y'); ?></h3>

                <!-- Calendar widget -->
                <div id="calContainer" style="margin-bottom:28px;"></div>

                <!-- Full Year Holiday List -->
                <div class="holiday-list">
                    <h3 class="section-title">All Holidays <?php echo date('Y');?></h3>
                    <table class="emp-table">
                        <thead><tr><th>#</th><th>Holiday</th><th>Date</th><th>Day</th><th>Type</th></tr></thead>
                        <tbody>
                        <?php
                           
                            $hres = mysqli_query($conn,"SELECT * FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) ORDER BY holiday_date ASC");
                           
                            $cnt=1;
                            $found=false;
                            while($h=mysqli_fetch_assoc($hres)){
                                $found=true;
                                $day_name = date('l', strtotime($h['holiday_date']));
                                $htype = $h['holiday_type'] ?? 'National';
                                $is_today = ($h['holiday_date'] == date('Y-m-d')) ? "style='background:#eff6ff;'" : "";
                                echo "<tr {$is_today}>
                                    <td>{$cnt}</td>
                                    <td><b>{$h['holiday_name']}</b></td>
                                    <td>".date('d M Y',strtotime($h['holiday_date']))."</td>
                                    <td>{$day_name}</td>
                                    <td><span class='hl-badge {$htype}'>{$htype}</span></td>
                                </tr>";
                                $cnt++;
                            }
                            if(!$found) echo "<tr><td colspan='5' style='text-align:center;color:#9ca3af;'>No holidays found.</td></tr>";
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ===================== PROFILE ===================== -->
        <div id="profile" class="section">
            <div class="form-card">

                <h3 class="section-title">Profile Photo</h3>
                <?php if(!empty($profile_photo) && file_exists('uploads/'.$profile_photo)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>"
                         style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:3px solid #3b82f6;margin-bottom:16px;">
                <?php endif; ?>
                <form action="save_profile_photo.php" method="POST" enctype="multipart/form-data">
                    <div class="field">
                        <label>Upload Profile Photo (JPG/PNG, max 2MB)</label>
                        <input type="file" name="profile_photo" accept="image/*" required>
                    </div>
                    <button type="submit" class="submit-btn">Update Photo</button>
                </form>

                <h3 class="section-title" style="margin-top:30px;">My Profile</h3>
                <table class="emp-table">
                    <tr><td><b>Name</b></td><td><?php echo $emp['first_name'].' '.$emp['last_name']; ?></td></tr>
                    <tr><td><b>Contact</b></td><td><?php echo $emp['contact']; ?></td></tr>
                    <tr><td><b>Designation</b></td><td><?php echo $emp['designation']; ?></td></tr>
                    <tr><td><b>Blood Group</b></td><td><?php echo $emp['blood_group']; ?></td></tr>
                    <tr><td><b>Date of Birth</b></td><td><?php echo $emp['dob']; ?></td></tr>
                    <tr><td><b>Address</b></td><td><?php echo $emp['address']; ?></td></tr>
                    <tr><td><b>Religion</b></td><td><?php echo $emp['religion']; ?></td></tr>
                    <tr><td><b>Caste</b></td><td><?php echo $emp['caste']; ?></td></tr>
                    <tr><td><b>Sub Caste</b></td><td><?php echo $emp['sub_caste']; ?></td></tr>
                    <tr><td><b>Permanent Address</b></td><td><?php echo $emp['permanent_address']; ?></td></tr>
                    <tr><td><b>Common Address</b></td><td><?php echo $emp['common_address']; ?></td></tr>
                </table>

                <h3 class="section-title" style="margin-top:30px;">My Documents</h3>
                <table class="emp-table">
                    <thead><tr><th>Document</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php
                        $emp_fresh = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM employees WHERE emp_id='$emp_id'"));
                        $docs = ['pan_card'=>'PAN Card','aadhar_card'=>'Aadhar Card','marks_card'=>'Marks Card'];
                        foreach($docs as $col=>$label){
                            if(!empty($emp_fresh[$col])){
                                echo "<tr>
                                    <td><b>{$label}</b></td>
                                    <td><span style='color:#16a34a;font-weight:bold;'>&#10003; Uploaded</span></td>
                                    <td><a href='uploads/{$emp_fresh[$col]}' target='_blank' style='color:#3b82f6;'>View File</a></td>
                                </tr>";
                            } else {
                                echo "<tr>
                                    <td><b>{$label}</b></td>
                                    <td><span style='color:#ef4444;'>&#10005; Not Uploaded</span></td>
                                    <td>-</td>
                                </tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table>

                <h3 class="section-title" style="margin-top:30px;">Upload Documents</h3>
                <form action="upload_documents.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="field"><label>PAN Card (PDF/Image)</label><input type="file" name="pan_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="field"><label>Aadhar Card (PDF/Image)</label><input type="file" name="aadhar_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                        <div class="field"><label>Marks Card (PDF/Image)</label><input type="file" name="marks_card" accept=".pdf,.jpg,.jpeg,.png"></div>
                    </div>
                    <button type="submit" class="submit-btn">Upload Documents</button>
                </form>

                <h3 class="section-title" style="margin-top:30px;">Edit Profile</h3>
                <form action="update_profile.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>First Name</label><input type="text" name="first_name" value="<?php echo $emp['first_name']; ?>" required></div>
                        <div class="field"><label>Last Name</label><input type="text" name="last_name" value="<?php echo $emp['last_name']; ?>" required></div>
                        <div class="field"><label>Contact Number</label><input type="text" name="contact" value="<?php echo $emp['contact']; ?>" required></div>
                        <div class="field"><label>Designation</label><input type="text" name="designation" value="<?php echo $emp['designation']; ?>" required></div>
                        <div class="field"><label>Blood Group</label>
                            <select name="blood_group">
                                <?php foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg){ $sel=($emp['blood_group']==$bg)?'selected':''; echo "<option value='$bg' $sel>$bg</option>"; } ?>
                            </select>
                        </div>
                        <div class="field"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo $emp['dob']; ?>" required></div>
                        <div class="field"><label>Address</label><input type="text" name="address" value="<?php echo $emp['address']; ?>" required></div>
                        <div class="field"><label>Religion</label><input type="text" name="religion" value="<?php echo $emp['religion']; ?>"></div>
                        <div class="field"><label>Caste</label><input type="text" name="caste" value="<?php echo $emp['caste']; ?>"></div>
                        <div class="field"><label>Sub Caste</label><input type="text" name="sub_caste" value="<?php echo $emp['sub_caste']; ?>"></div>
                        <div class="field"><label>Permanent Address</label><input type="text" name="permanent_address" value="<?php echo $emp['permanent_address']; ?>"></div>
                        <div class="field"><label>Common Address</label><input type="text" name="common_address" value="<?php echo $emp['common_address']; ?>"></div>
                    </div>
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>
            </div>
        </div>

    </div><!-- /main-content -->
</div><!-- /dashboard -->

<?php
    // ---- Charts data ----
    $att_data = array_fill(0,12,0);
    $att_result = mysqli_query($conn,"SELECT MONTH(date) as mon, COUNT(*) as cnt FROM attendance WHERE emp_id='$emp_id' AND status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
    while($row = mysqli_fetch_assoc($att_result)) $att_data[$row['mon']-1] = $row['cnt'];

    $leave_data = array_fill(0,12,0);
    $leave_result = mysqli_query($conn,"SELECT MONTH(from_date) as mon, COUNT(*) as cnt FROM leaves WHERE emp_id='$emp_id' AND YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
    while($row = mysqli_fetch_assoc($leave_result)) $leave_data[$row['mon']-1] = $row['cnt'];

    // ---- Holiday dates for calendar JS ----
    // Column fix already done at top of file, safe to query now
    $holiday_js = [];
    $h_res = mysqli_query($conn,"SELECT * FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) ORDER BY holiday_date ASC");
    if($h_res){
        while($hrow = mysqli_fetch_assoc($h_res)){
            if(isset($holiday_js[$hrow['holiday_date']])){
                $holiday_js[$hrow['holiday_date']]['name'] .= ' & ' . $hrow['holiday_name'];
            } else {
                $holiday_js[$hrow['holiday_date']] = [
                    'name' => $hrow['holiday_name'],
                    'type' => $hrow['holiday_type'] ?? 'National'
                ];
            }
        }
    }
    $holidays_json = json_encode($holiday_js);
    $att_json      = json_encode($att_data);
    $leave_json    = json_encode($leave_data);
    $today_str     = date('Y-m-d');
    $cur_year      = (int)date('Y');
    $cur_month     = (int)date('m');
?>

<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const fullMonths = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const dayNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const holidays = <?php echo $holidays_json; ?>;
const todayStr = "<?php echo $today_str; ?>";

// ---- Charts ----
new Chart(document.getElementById('attendanceChart'),{
    type:'bar',
    data:{labels:months,datasets:[{label:'Days Present',data:<?php echo $att_json;?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
new Chart(document.getElementById('leaveChart'),{
    type:'bar',
    data:{labels:months,datasets:[{label:'Leaves Taken',data:<?php echo $leave_json;?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});

// ---- Holiday Calendar ----
let calYear  = <?php echo $cur_year; ?>;
let calMonth = <?php echo $cur_month - 1; ?>; // 0-indexed

function buildCalendar(year, month){
    const container = document.getElementById('calContainer');
    if(!container) return;

    const firstDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month+1, 0).getDate();

    let html = `<div class="cal-wrap">
        <div class="cal-header">
            <h3>&#128197; ${fullMonths[month]} ${year}</h3>
            <div class="cal-nav">
                <button onclick="changeMonth(-1)">&#8592; Prev</button>
                <button onclick="changeMonth(1)" style="margin-left:8px;">Next &#8594;</button>
            </div>
        </div>
        <div class="cal-grid">`;

    dayNames.forEach(d => { html += `<div class="cal-day-name">${d}</div>`; });

    for(let i=0; i<firstDay; i++) html += `<div class="cal-cell empty"></div>`;

    for(let d=1; d<=daysInMonth; d++){
        const mm = String(month+1).padStart(2,'0');
        const dd = String(d).padStart(2,'0');
        const dateStr = `${year}-${mm}-${dd}`;
        const weekday = new Date(year,month,d).getDay();
        const isSunday = weekday === 0;
        const isToday  = dateStr === todayStr;
        const isHol    = holidays[dateStr] !== undefined;

        let cls = 'cal-cell';
        if(isToday)       cls += ' today';
        if(isHol)         cls += ' holiday';
        else if(isSunday) cls += ' sunday';

        let hname = isHol
            ? `<div class="hname">${holidays[dateStr].name}</div>`
            : (isSunday ? `<div class="hname" style="color:#6b7280;">Sunday</div>` : '');

        html += `<div class="${cls}"><span class="date-num">${d}</span>${hname}</div>`;
    }

    html += `</div></div>`;
    container.innerHTML = html;
}

function changeMonth(dir){
    calMonth += dir;
    if(calMonth > 11){ calMonth=0; calYear++; }
    if(calMonth < 0){ calMonth=11; calYear--; }
    buildCalendar(calYear, calMonth);
}

buildCalendar(calYear, calMonth);

// ---- Notification ----
function toggleNotif(){
    document.getElementById('notifDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e){
    const wrapper = document.getElementById('notifWrapper');
    if(wrapper && !wrapper.contains(e.target)){
        document.getElementById('notifDropdown').classList.remove('open');
    }
});

// ---- Section switcher ----
function showSection(name, el){
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById(name).classList.add('active');
    el.classList.add('active');
    document.getElementById('page-title').innerText = el.innerText;
    if(name === 'holidays') buildCalendar(calYear, calMonth);
}

// ---- Auto logout (30 min) ----
let timeLeft = 1800;
function resetTimer(){ timeLeft = 1800; }
['mousemove','keydown','click','scroll'].forEach(e => document.addEventListener(e, resetTimer, {passive:true}));
setInterval(()=>{
    timeLeft--;
    if(timeLeft <= 0){ alert('Session expired. Logging out...'); window.location.href='logout.php'; }
},1000);
</script>
</body>
</html>