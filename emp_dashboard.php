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

// Profile photo fetch
$photo_res = mysqli_query($conn, "SELECT profile_photo FROM users WHERE id='$user_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Employee Dashboard - EMS</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            <a class="nav-item active" onclick="showSection('dashboard', this)">Dashboard</a>
            <a class="nav-item" onclick="showSection('attendance', this)">My Attendance</a>
            <a class="nav-item" onclick="showSection('leaves', this)">My Leaves</a>
            <a class="nav-item" onclick="showSection('salary', this)">My Salary</a>
            <a class="nav-item" onclick="showSection('tasks', this)">My Tasks</a>
            <a class="nav-item" onclick="showSection('profile', this)">My Profile</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,0.07);">
            <p style="font-size:10px;color:rgba(255,255,255,0.22);text-align:center;line-height:1.8;">
                © <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Topbar -->
        <div class="topbar">
            <h2 id="page-title">Dashboard</h2>
            <div class="topbar-right">
                <?php if(!empty($profile_photo) && file_exists('uploads/'.$profile_photo)): ?>
                    <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>"
                         onclick="showSection('profile', document.querySelector('[onclick*=profile]'))"
                         style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #3b82f6;cursor:pointer;"
                         title="My Profile">
                <?php else: ?>
                    <div onclick="showSection('profile', document.querySelector('[onclick*=profile]'))"
                         style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);
                                display:flex;align-items:center;justify-content:center;
                                color:white;font-weight:bold;font-size:15px;cursor:pointer;"
                         title="My Profile">
                        <?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?>
                    </div>
                <?php endif; ?>
                <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="section active">
            <div class="cards">
                <div class="card">
                    <h3>My Attendance</h3>
                    <p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM attendance WHERE emp_id='$emp_id'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p>
                </div>
                <div class="card">
                    <h3>My Leaves</h3>
                    <p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM leaves WHERE emp_id='$emp_id'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p>
                </div>
                <div class="card">
                    <h3>My Tasks</h3>
                    <p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM tasks WHERE emp_id='$emp_id'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p>
                </div>
                <div class="card">
                    <h3>Pending Leaves</h3>
                    <p class="num"><?php $res=mysqli_query($conn,"SELECT COUNT(*) as total FROM leaves WHERE emp_id='$emp_id' AND status='pending'"); $row=mysqli_fetch_assoc($res); echo $row['total']; ?></p>
                </div>
            </div>

            <!-- Charts -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:24px;">
                <div style="background:white;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                    <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Attendance</h3>
                    <canvas id="attendanceChart"></canvas>
                </div>
                <div style="background:white;padding:24px;border-radius:10px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                    <h3 style="font-size:14px;color:#60a5fa;margin-bottom:16px;padding-bottom:8px;border-bottom:1px solid #eee;">Monthly Leaves</h3>
                    <canvas id="leaveChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Attendance Section -->
        <div id="attendance" class="section">
            <div class="form-card">
                <h3 class="section-title">Add Attendance</h3>
                <form action="save_attendance.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Date</label><input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required></div>
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
                    <button type="submit" class="submit-btn">Add Attendance</button>
                </form>
                <h3 class="section-title" style="margin-top:30px;">My Attendance Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' ORDER BY date DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td>{$row['status']}</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Leaves Section -->
        <div id="leaves" class="section">
            <div class="form-card">
                <h3 class="section-title">Apply for Leave</h3>
                <form action="save_leave.php" method="POST">
                    <div class="form-grid">
                        <div class="field"><label>Leave Type</label>
                            <select name="leave_type">
                            <?php $lt_result=mysqli_query($conn,"SELECT * FROM leave_types ORDER BY id ASC"); while($lt=mysqli_fetch_assoc($lt_result)){ echo "<option value='{$lt['leave_type_name']}'>{$lt['leave_type_name']} ({$lt['total_days']} days)</option>"; } ?>
                            </select>
                        </div>
                        <div class="field"><label>From Date</label><input type="date" name="from_date" required></div>
                        <div class="field"><label>To Date</label><input type="date" name="to_date" required></div>
                        <div class="field"><label>Reason</label><input type="text" name="reason" placeholder="Reason for leave" required></div>
                    </div>
                    <button type="submit" class="submit-btn">Apply Leave</button>
                </form>
                <h3 class="section-title" style="margin-top:30px;">My Leave Balance</h3>
                <table class="emp-table">
                    <thead><tr><th>Leave Type</th><th>Total Allowed</th><th>Used</th><th>Remaining</th></tr></thead>
                    <tbody>
                    <?php
                        $lt_res=mysqli_query($conn,"SELECT * FROM leave_types ORDER BY id ASC");
                        while($lt=mysqli_fetch_assoc($lt_res)){
                            $used_res=mysqli_query($conn,"SELECT COUNT(*) as cnt FROM leaves WHERE emp_id='$emp_id' AND leave_type='{$lt['leave_type_name']}' AND status='approved'");
                            $used_row=mysqli_fetch_assoc($used_res);
                            $used=$used_row['cnt'];
                            $total=$lt['total_days'];
                            $remaining=max(0,$total-$used);
                            $color=$remaining==0?'color:#ef4444;':'color:#16a34a;';
                            echo "<tr><td>{$lt['leave_type_name']}</td><td>{$total}</td><td>{$used}</td><td style='{$color}'><b>{$remaining}</b></td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
                <h3 class="section-title" style="margin-top:30px;">My Leave Records</h3>
                <table class="emp-table">
                    <thead><tr><th>Leave Type</th><th>From</th><th>To</th><th>Reason</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['leave_type']}</td><td>{$row['from_date']}</td><td>{$row['to_date']}</td><td>{$row['reason']}</td><td>{$row['status']}</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Salary Section -->
        <div id="salary" class="section">
            <div class="form-card">
                <h3 class="section-title">My Salary Details</h3>
                <table class="emp-table">
                    <thead><tr><th>Month</th><th>Year</th><th>Basic Pay</th><th>Allowances</th><th>Deductions</th><th>Net Pay</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT * FROM salary WHERE emp_id='$emp_id' ORDER BY year DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            echo "<tr><td>{$row['month']}</td><td>{$row['year']}</td><td>{$row['basic_pay']}</td><td>{$row['allowances']}</td><td>{$row['deductions']}</td><td>{$row['net_pay']}</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tasks Section -->
        <div id="tasks" class="section">
            <div class="form-card">
                <h3 class="section-title">My Tasks</h3>
                <table class="emp-table">
                    <thead><tr><th>Task</th><th>Description</th><th>Target Date</th><th>Status</th><th>Hours</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php
                        $result=mysqli_query($conn,"SELECT * FROM tasks WHERE emp_id='$emp_id' ORDER BY target_date DESC");
                        while($row=mysqli_fetch_assoc($result)){
                            $is_done=($row['status']=='completed');
                            $btn=$is_done
                                ?"<button disabled style='background:#9ca3af;color:white;border:none;padding:6px 12px;border-radius:6px;'>✓ Done</button>"
                                :"<form action='notify_task_complete.php' method='POST' onsubmit=\"return confirm('Notify admin this task is complete?')\">
                                    <input type='hidden' name='task_id' value='{$row['task_id']}'>
                                    <button type='submit' style='background:#10b981;color:white;border:none;padding:6px 12px;border-radius:6px;cursor:pointer;'>✔ Mark Done</button>
                                  </form>";
                            echo "<tr><td>{$row['task_name']}</td><td>{$row['description']}</td><td>{$row['target_date']}</td><td>{$row['status']}</td><td>{$row['hours_worked']}</td><td>$btn</td></tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile Section -->
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
                    <tr><td><b>PAN Card</b></td><td><?php if($emp['pan_card']): ?><a href="uploads/<?php echo $emp['pan_card']; ?>" target="_blank" style="color:#3b82f6;">View PAN Card</a><?php else: ?><span style="color:#9ca3af;">Not uploaded</span><?php endif; ?></td></tr>
                    <tr><td><b>Aadhar Card</b></td><td><?php if($emp['aadhar_card']): ?><a href="uploads/<?php echo $emp['aadhar_card']; ?>" target="_blank" style="color:#3b82f6;">View Aadhar Card</a><?php else: ?><span style="color:#9ca3af;">Not uploaded</span><?php endif; ?></td></tr>
                    <tr><td><b>Marks Card</b></td><td><?php if($emp['marks_card']): ?><a href="uploads/<?php echo $emp['marks_card']; ?>" target="_blank" style="color:#3b82f6;">View Marks Card</a><?php else: ?><span style="color:#9ca3af;">Not uploaded</span><?php endif; ?></td></tr>
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
    $att_data=array_fill(0,12,0);
    $att_result=mysqli_query($conn,"SELECT MONTH(date) as mon, COUNT(*) as cnt FROM attendance WHERE emp_id='$emp_id' AND status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
    while($row=mysqli_fetch_assoc($att_result)) $att_data[$row['mon']-1]=$row['cnt'];

    $leave_data=array_fill(0,12,0);
    $leave_result=mysqli_query($conn,"SELECT MONTH(from_date) as mon, COUNT(*) as cnt FROM leaves WHERE emp_id='$emp_id' AND YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
    while($row=mysqli_fetch_assoc($leave_result)) $leave_data[$row['mon']-1]=$row['cnt'];

    $att_json=json_encode($att_data);
    $leave_json=json_encode($leave_data);
?>
<script>
const months=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
new Chart(document.getElementById('attendanceChart'),{
    type:'bar',data:{labels:months,datasets:[{label:'Days Present',data:<?php echo $att_json;?>,backgroundColor:'rgba(59,130,246,0.7)',borderColor:'#3b82f6',borderWidth:1,borderRadius:6}]},
    options:{responsive:true,plugins:{legend:{display:false}},scales:{y:{beginAtZero:true,ticks:{stepSize:1}}}}
});
new Chart(document.getElementById('leaveChart'),{
    type:'bar',data:{labels:months,datasets:[{label:'Leaves Taken',data:<?php echo $leave_json;?>,backgroundColor:'rgba(239,68,68,0.7)',borderColor:'#ef4444',borderWidth:1,borderRadius:6}]},
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
let timeLeft=1800;
function formatTime(s){ return String(Math.floor(s/60)).padStart(2,'0')+':'+String(s%60).padStart(2,'0'); }
function resetTimer(){ timeLeft=1800; }
['mousemove','keydown','click','scroll'].forEach(e=>document.addEventListener(e,resetTimer,{passive:true}));
setInterval(()=>{
    timeLeft--;
    if(timeLeft<=0){ alert('Session expired. Logging out...'); window.location.href='logout.php'; }
},1000);
</script>
</body>
</html>