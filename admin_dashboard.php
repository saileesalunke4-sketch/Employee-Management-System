<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}
require 'db.php';

$admin_id = $_SESSION['user']['id'];
$photo_res = mysqli_query($conn, "SELECT profile_photo FROM users WHERE id='$admin_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - EMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    .notif-wrapper { position: relative; }
    .notif-bell { font-size: 20px; cursor: pointer; position: relative; display: inline-block; padding: 4px 8px; border-radius: 8px; transition: background 0.2s; }
    .notif-bell:hover { background: rgba(59,130,246,0.1); }
    .notif-badge { position: absolute; top: -4px; right: -4px; background: #ef4444; color: white; font-size: 10px; font-weight: bold; min-width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: pulse 1.5s infinite; }
    @keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.2); } }
    .notif-dropdown { display: none; position: absolute; right: 0; top: 42px; width: 340px; background: white; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); z-index: 999; overflow: hidden; border: 1px solid #e5e7eb; }
    .notif-dropdown.open { display: block; animation: slideDown 0.2s ease; }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    .notif-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-bottom: 1px solid #f0f0f0; font-size: 14px; font-weight: 600; color: #1a1a2e; background: #f8fafc; }
    .mark-read-btn { font-size: 11px; color: #3b82f6; text-decoration: none; padding: 4px 10px; border-radius: 20px; border: 1px solid #3b82f6; transition: all 0.2s; }
    .mark-read-btn:hover { background: #3b82f6; color: white; }
    .notif-list { max-height: 360px; overflow-y: auto; }
    .notif-item { display: flex; align-items: flex-start; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #f5f5f5; position: relative; transition: background 0.2s; }
    .notif-item:hover { background: #f8fafc; }
    .notif-item.notif-new { background: #eff6ff; }
    .notif-icon { font-size: 20px; margin-top: 2px; flex-shrink: 0; }
    .notif-text { flex: 1; font-size: 13px; color: #374151; line-height: 1.6; }
    .notif-type { background: #dbeafe; color: #1d4ed8; font-size: 11px; padding: 1px 7px; border-radius: 20px; font-weight: 600; }
    .notif-reason { color: #9ca3af; font-style: italic; }
    .notif-dot { width: 8px; height: 8px; background: #3b82f6; border-radius: 50%; flex-shrink: 0; margin-top: 6px; }
    .notif-empty { text-align: center; padding: 30px; color: #9ca3af; font-size: 13px; }
    </style>
</head>
<body>
<div class="dashboard">

    <div class="sidebar">
        <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,0.08);">
            <img src="allerlogo.png" alt="Aller Technologies" style="height:55px;display:block;margin:0 auto 8px auto;">
            <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,0.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
        </div>
        <nav>
            <a class="nav-item active" onclick="showSection('dashboard', this)">Dashboard</a>
            <a class="nav-item" onclick="showSection('add_employee', this)">Add Employee</a>
            <a class="nav-item" onclick="showSection('view_employees', this)">View Employees</a>
            <a class="nav-item" onclick="showSection('attendance', this)">Attendance</a>
            <a class="nav-item" onclick="showSection('leaves', this)">Leaves</a>
            <a class="nav-item" onclick="showSection('salary', this)">Salary</a>
            <a class="nav-item" onclick="showSection('tasks', this)">Tasks</a>
            <a class="nav-item" onclick="showSection('leave_types', this)">Leave Types</a>
            <a class="nav-item" onclick="showSection('my_profile', this)">My Profile</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,0.07);">
            <p style="font-size:10px;color:rgba(255,255,255,0.22);text-align:center;line-height:1.8;">
                © <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.
            </p>
        </div>
    </div>

    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <h2 id="page-title">Dashboard</h2>
            <div class="topbar-right">
                <?php
                    $unread_res=mysqli_query($conn,"SELECT COUNT(*) as cnt FROM notifications WHERE is_read=0");
                    $unread_row=mysqli_fetch_assoc($unread_res);
                    $unread_count=$unread_row['cnt'];
                    $all_notif=mysqli_query($conn,"SELECT * FROM notifications ORDER BY created_at DESC LIMIT 15");
                ?>
                <div class="notif-wrapper" id="notifWrapper">
                    <div class="notif-bell" onclick="toggleNotif()">
                        &#128276;
                        <?php if($unread_count>0): ?>
                            <span class="notif-badge"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-header">
                            <span>Notifications</span>
                            <?php if($unread_count>0): ?>
                                <a href="mark_notifications_read.php" class="mark-read-btn">Mark all read</a>
                            <?php endif; ?>
                        </div>
                        <div class="notif-list">
                        <?php
                            $has_notif=false;
                            while($n=mysqli_fetch_assoc($all_notif)){
                                $has_notif=true;
                                $is_new=($n['is_read']==0)?'notif-new':'';
                                echo "<div class='notif-item {$is_new}'>
                                    <div class='notif-icon'>&#128203;</div>
                                    <div class='notif-text'>
                                        <strong>{$n['emp_name']}</strong> — <span class='notif-type'>{$n['leave_type']}</span><br>
                                        <small>&#128197; {$n['from_date']} to {$n['to_date']}</small><br>
                                        <small class='notif-reason'>Reason: {$n['reason']}</small>
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
                         onclick="showSection('my_profile', document.querySelector('[onclick*=my_profile]'))"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #3b82f6;cursor:pointer;"
                         title="My Profile">
                <?php else: ?>
                    <div onclick="showSection('my_profile', document.querySelector('[onclick*=my_profile]'))"
                         style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);
                                display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:15px;cursor:pointer;">
                        <?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?>
                    </div>
                <?php endif; ?>
                <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
            </div>
        </div>

        <!-- Dashboard -->
        <div id="dashboard" class="section active">
            <div class="cards">
                <div class="card"><h3>Total Employees</h3><p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM employees"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p></div>
                <div class="card"><h3>Present Today</h3><p class="num"><?php $today=date('Y-m-d'); $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM attendance WHERE date='$today' AND status='present'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p></div>
                <div class="card"><h3>On Leave</h3><p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM leaves WHERE status='approved'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p></div>
                <div class="card"><h3>Pending Tasks</h3><p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM tasks WHERE status='pending'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
                <div style="background:white;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.06);min-width:0;">
                    <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Attendance (Present)</h3>
                    <canvas id="adminAttChart"></canvas>
                </div>
                <div style="background:white;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.06);min-width:0;">
                    <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Leave Requests</h3>
                    <canvas id="adminLeaveChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Add Employee -->
        <div id="add_employee" class="section">
            <div class="form-card">
                <form action="save_employee.php" method="POST">
                    <h3 class="section-title">Login Details</h3>
                    <div class="form-grid">
                        <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="Full Name" required></div>
                        <div class="field"><label>Email</label><input type="email" name="email" placeholder="Email" required></div>
                        <div class="field"><label>Password</label><input type="password" name="password" placeholder="Password" required></div>
                        <div class="field"><label>Role</label>
                            <select name="role">
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <h3 class="section-title">Personal Details</h3>
                    <div class="form-grid">
                        <div class="field"><label>First Name</label><input type="text" name="first_name" placeholder="First Name" required></div>
                        <div class="field"><label>Last Name</label><input type="text" name="last_name" placeholder="Last Name" required></div>
                        <div class="field"><label>Contact</label><input type="text" name="contact" placeholder="Contact Number" required></div>
                        <div class="field"><label>Designation</label><input type="text" name="designation" placeholder="Designation" required></div>
                        <div class="field"><label>Blood Group</label>
                            <select name="blood_group">
                                <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                                <option>O+</option><option>O-</option><option>AB+</option><option>AB-</option>
                            </select>
                        </div>
                        <div class="field"><label>Date of Birth</label><input type="date" name="dob" required></div>
                        <div class="field"><label>Religion</label><input type="text" name="religion" placeholder="Religion" required></div>
                        <div class="field"><label>Address</label><input type="text" name="address" placeholder="Address" required></div>
                    </div>
                    <button type="submit" class="submit-btn">Add Employee</button>
                </form>
            </div>
        </div>

        <!-- View Employees -->
        <div id="view_employees" class="section">
            <div class="form-card">
                <table class="emp-table">
                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Designation</th><th>Contact</th><th>Role</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT u.id,u.name,u.email,u.role,e.designation,e.contact FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.role='employee'");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>{$row['email']}</td><td>{$row['designation']}</td><td>{$row['contact']}</td><td>{$row['role']}</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Attendance -->
        <div id="attendance" class="section">
            <div class="form-card">
               <h3 class="section-title">Attendance Records</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT e.first_name,e.last_name,a.date,a.check_in,a.check_out,a.status FROM attendance a JOIN employees e ON a.emp_id=e.emp_id ORDER BY a.date DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            $type = ($row['status'] == 'work_from_home')
                                ? "<span style='background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:bold;'>&#127968; WFH</span>"
                                : "<span style='background:#dcfce7;color:#16a34a;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:bold;'>&#127970; Office</span>";
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['check_in']}</td>
                                <td>{$row['check_out']}</td>
                                <td>{$row['status']}</td>
                                <td>{$type}</td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>

                <!-- Today's WFH Employees -->
                <h3 class="section-title" style="margin-top:30px;">Work From Home Today</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $today = date('Y-m-d');
                        $wfh_result = mysqli_query($conn, "SELECT a.*, e.first_name, e.last_name 
                                                           FROM attendance a 
                                                           JOIN employees e ON a.emp_id = e.emp_id 
                                                           WHERE a.status='work_from_home' 
                                                           AND a.date='$today'");
                        if(mysqli_num_rows($wfh_result) > 0){
                            while($row = mysqli_fetch_assoc($wfh_result)){
                                echo "<tr>
                                    <td>&#127968; {$row['first_name']} {$row['last_name']}</td>
                                    <td>{$row['date']}</td>
                                    <td>{$row['check_in']}</td>
                                    <td>{$row['check_out']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No employees working from home today</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>

                <!-- Monthly WFH Count -->
                <h3 class="section-title" style="margin-top:30px;">Monthly WFH Count</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>WFH Days This Month</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $wfh_monthly = mysqli_query($conn, "SELECT e.first_name, e.last_name, COUNT(*) as wfh_count 
                                                            FROM attendance a 
                                                            JOIN employees e ON a.emp_id = e.emp_id 
                                                            WHERE a.status='work_from_home' 
                                                            AND MONTH(a.date)=MONTH(CURDATE()) 
                                                            AND YEAR(a.date)=YEAR(CURDATE())
                                                            GROUP BY a.emp_id");
                        if(mysqli_num_rows($wfh_monthly) > 0){
                            while($row = mysqli_fetch_assoc($wfh_monthly)){
                                echo "<tr>
                                    <td>{$row['first_name']} {$row['last_name']}</td>
                                    <td><span style='color:#3b82f6;font-weight:bold;'>&#127968; {$row['wfh_count']} days</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' style='text-align:center;color:#9ca3af;'>No WFH records this month</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leaves -->
        <div id="leaves" class="section">
            <div class="form-card">
                <h3 class="section-title">Leave Requests</h3>
                <table class="emp-table">
                    <thead><tr><th>Employee</th><th>Leave Type</th><th>From</th><th>To</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT l.*,e.first_name,e.last_name FROM leaves l JOIN employees e ON l.emp_id=e.emp_id ORDER BY l.leave_id DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['leave_type']}</td><td>{$row['from_date']}</td><td>{$row['to_date']}</td><td>{$row['reason']}</td><td>{$row['status']}</td>
                            <td><a href='leave_action.php?id={$row['leave_id']}&action=approved' class='approve-btn'>Approve</a>
                            <a href='leave_action.php?id={$row['leave_id']}&action=rejected' class='reject-btn'>Reject</a></td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Salary -->
        <div id="salary" class="section">
            <div class="form-card">
                <h3 class="section-title">Add Salary</h3>
                <form action="save_salary.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Select Employee</label>
                            <select name="emp_id"><?php $result=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name FROM employees e"); while($row=mysqli_fetch_assoc($result)){ echo "<option value='{$row['emp_id']}'>{$row['first_name']} {$row['last_name']}</option>"; } ?></select>
                        </div>
                        <div class="field"><label>Basic Pay</label><input type="number" name="basic_pay" placeholder="Basic Pay" required></div>
                        <div class="field"><label>Allowances</label><input type="number" name="allowances" placeholder="Allowances" required></div>
                        <div class="field"><label>Deductions</label><input type="number" name="deductions" placeholder="Deductions" required></div>
                        <div class="field"><label>Month</label>
                            <select name="month"><option>January</option><option>February</option><option>March</option><option>April</option><option>May</option><option>June</option><option>July</option><option>August</option><option>September</option><option>October</option><option>November</option><option>December</option></select>
                        </div>
                        <div class="field"><label>Year</label><input type="number" name="year" value="2026" required></div>
                    </div>
                    <button type="submit" class="submit-btn">Add Salary</button>
                </form>
                <h3 class="section-title" style="margin-top:30px;">Salary Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Employee</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>Net Pay</th><th>Month</th><th>Year</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT s.*,e.first_name,e.last_name FROM salary s JOIN employees e ON s.emp_id=e.emp_id ORDER BY s.year DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['basic_pay']}</td><td>{$row['allowances']}</td><td>{$row['deductions']}</td><td>{$row['net_pay']}</td><td>{$row['month']}</td><td>{$row['year']}</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tasks -->
        <div id="tasks" class="section">
            <div class="form-card">
                <h3 class="section-title">Assign Task</h3>
                <form action="save_task.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Select Employee</label>
                            <select name="emp_id"><?php $result=mysqli_query($conn,"SELECT e.emp_id,e.first_name,e.last_name FROM employees e"); while($row=mysqli_fetch_assoc($result)){ echo "<option value='{$row['emp_id']}'>{$row['first_name']} {$row['last_name']}</option>"; } ?></select>
                        </div>
                        <div class="field"><label>Task Name</label><input type="text" name="task_name" placeholder="Task Name" required></div>
                        <div class="field"><label>Description</label><input type="text" name="description" placeholder="Description" required></div>
                        <div class="field"><label>Target Date</label><input type="date" name="target_date" required></div>
                        <div class="field"><label>Status</label>
                            <select name="status"><option value="pending">Pending</option><option value="in_progress">In Progress</option><option value="completed">Completed</option></select>
                        </div>
                        <div class="field"><label>Hours Worked</label><input type="number" name="hours_worked" placeholder="Hours" required></div>
                    </div>
                    <button type="submit" class="submit-btn">Assign Task</button>
                </form>
                <h3 class="section-title" style="margin-top:30px;">Task Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Employee</th><th>Task</th><th>Description</th><th>Target Date</th><th>Status</th><th>Hours</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT t.*,e.first_name,e.last_name FROM tasks t JOIN employees e ON t.emp_id=e.emp_id ORDER BY t.target_date DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['task_name']}</td><td>{$row['description']}</td><td>{$row['target_date']}</td><td>{$row['status']}</td><td>{$row['hours_worked']}</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leave Types -->
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
                <h3 class="section-title" style="margin-top:30px;">All Leave Types</h3>
                <table class="emp-table">
                    <thead><tr><th>Leave Type</th><th>Total Days Allowed</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT * FROM leave_types ORDER BY id ASC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['leave_type_name']}</td><td>{$row['total_days']}</td><td><a href='delete_leave_type.php?id={$row['id']}' class='reject-btn' onclick='return confirm(\"Delete this?\")'>Delete</a></td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- My Profile -->
        <div id="my_profile" class="section">
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
                <h3 class="section-title" style="margin-top:30px;">My Details</h3>
                <table class="emp-table">
                    <tr><td><b>Name</b></td><td><?php echo $_SESSION['user']['name']; ?></td></tr>
                    <tr><td><b>Email</b></td><td><?php echo $_SESSION['user']['email']; ?></td></tr>
                    <tr><td><b>Role</b></td><td><?php echo ucfirst($_SESSION['user']['role']); ?></td></tr>
                </table>
            </div>
        </div>

    </div><!-- /main-content -->
</div><!-- /dashboard -->

<?php
$att_data=array_fill(0,12,0);
$att_res=mysqli_query($conn,"SELECT MONTH(date) as mon, COUNT(*) as cnt FROM attendance WHERE status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
while($r=mysqli_fetch_assoc($att_res)) $att_data[$r['mon']-1]=$r['cnt'];

$leave_data=array_fill(0,12,0);
$leave_res=mysqli_query($conn,"SELECT MONTH(from_date) as mon, COUNT(*) as cnt FROM leaves WHERE YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
while($r=mysqli_fetch_assoc($leave_res)) $leave_data[$r['mon']-1]=$r['cnt'];

$att_json=json_encode($att_data);
$leave_json=json_encode($leave_data);
?>
<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
new Chart(document.getElementById('adminAttChart'),{
    type:'bar',data:{labels:months,datasets:[{label:'Present',data:<?php echo $att_json;?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
new Chart(document.getElementById('adminLeaveChart'),{
    type:'bar',data:{labels:months,datasets:[{label:'Leaves',data:<?php echo $leave_json;?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
</script>
<script>
function showSection(name, el) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById(name).classList.add('active');
    el.classList.add('active');
    document.getElementById('page-title').innerText = el.innerText;
}
function toggleNotif() {
    document.getElementById('notifDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
    const wrapper=document.getElementById('notifWrapper');
    if(wrapper && !wrapper.contains(e.target)){
        document.getElementById('notifDropdown').classList.remove('open');
    }
});
let timeLeft=1800;
function resetTimer(){ timeLeft=1800; }
['mousemove','keydown','click','scroll'].forEach(e=>document.addEventListener(e,resetTimer,{passive:true}));
setInterval(()=>{
    timeLeft--;
    if(timeLeft<=0){ alert('Session expired. Logging out...'); window.location.href='logout.php'; }
},1000);
</script>
</body>
</html>