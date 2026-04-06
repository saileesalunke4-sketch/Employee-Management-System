<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}
require 'db.php';

// Employee ka data lao
$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];
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
        <div class="side-brand">EMS</div>
        <nav>
            <a class="nav-item active" onclick="showSection('dashboard', this)">Dashboard</a>
            <a class="nav-item" onclick="showSection('attendance', this)">My Attendance</a>
            <a class="nav-item" onclick="showSection('leaves', this)">My Leaves</a>
            <a class="nav-item" onclick="showSection('salary', this)">My Salary</a>
            <a class="nav-item" onclick="showSection('tasks', this)">My Tasks</a>
            <a class="nav-item" onclick="showSection('profile', this)">My Profile</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2 id="page-title">Dashboard</h2>
            <div class="user-info">
                Welcome, <?php echo $_SESSION['user']['name']; ?>
            </div>
        </div>

        <!-- Dashboard Section -->
        <div id="dashboard" class="section active">
            <div class="cards">
                <div class="card">
                    <h3>My Attendance</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE emp_id='$emp_id'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>My Leaves</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM leaves WHERE emp_id='$emp_id'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>My Tasks</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE emp_id='$emp_id'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>Pending Leaves</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM leaves WHERE emp_id='$emp_id' AND status='pending'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:24px;">
            
            <!-- Attendance Chart -->
            <div style="background:white; padding:24px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                <h3 style="font-size:14px; color:#60a5fa; margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid #eee;">
                    Monthly Attendance
                </h3>
                <canvas id="attendanceChart"></canvas>
            </div>

            <!-- Leave Chart -->
            <div style="background:white; padding:24px; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.06);">
                <h3 style="font-size:14px; color:#60a5fa; margin-bottom:16px; padding-bottom:8px; border-bottom:1px solid #eee;">
                    Monthly Leaves
                </h3>
                <canvas id="leaveChart"></canvas>
            </div>

        </div>

        <!-- Attendance Section -->
        <div id="attendance" class="section">
            <div class="form-card">
                <h3 class="section-title">Add Attendance</h3>
                <form action="save_attendance.php" method="POST">
                    <div class="form-grid">
                        <div class="field">
                            <label>Date</label>
                            <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div class="field">
                            <label>Check In</label>
                            <input type="time" name="check_in" required>
                        </div>
                        <div class="field">
                            <label>Check Out</label>
                            <input type="time" name="check_out" required>
                        </div>
                        <div class="field">
                            <label>Status</label>
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
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id='$emp_id' ORDER BY date DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['date']}</td>
                                <td>{$row['check_in']}</td>
                                <td>{$row['check_out']}</td>
                                <td>{$row['status']}</td>
                            </tr>";
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
                        <div class="field">
                            <label>Leave Type</label>
                            <select name="leave_type">
                               <?php
                                $lt_result = mysqli_query($conn, "SELECT * FROM leave_types ORDER BY id ASC");
                                while($lt = mysqli_fetch_assoc($lt_result)){
                                    echo "<option value='{$lt['leave_type_name']}'>{$lt['leave_type_name']} ({$lt['total_days']} days)</option>";
                                }
                            ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>From Date</label>
                            <input type="date" name="from_date" required>
                        </div>
                        <div class="field">
                            <label>To Date</label>
                            <input type="date" name="to_date" required>
                        </div>
                        <div class="field">
                            <label>Reason</label>
                            <input type="text" name="reason" placeholder="Reason for leave" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Apply Leave</button>
                </form>

                <!-- Leave Balance -->
                <h3 class="section-title" style="margin-top:30px;">My Leave Balance</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>Total Allowed</th>
                            <th>Used</th>
                            <th>Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $lt_res = mysqli_query($conn, "SELECT * FROM leave_types ORDER BY id ASC");
                        while($lt = mysqli_fetch_assoc($lt_res)){
                            $used_res = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM leaves 
                                                             WHERE emp_id='$emp_id' 
                                                             AND leave_type='{$lt['leave_type_name']}'
                                                             AND status='approved'");
                            $used_row  = mysqli_fetch_assoc($used_res);
                            $used      = $used_row['cnt'];
                            $total     = $lt['total_days'];
                            $remaining = $total - $used;
                            if($remaining < 0) $remaining = 0;

                            $color = $remaining == 0 ? 'color:#ef4444;' : 'color:#16a34a;';

                            echo "<tr>
                                <td>{$lt['leave_type_name']}</td>
                                <td>{$total}</td>
                                <td>{$used}</td>
                                <td style='{$color}'><b>{$remaining}</b></td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>

                <h3 class="section-title" style="margin-top:30px;">My Leave Records</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['leave_type']}</td>
                                <td>{$row['from_date']}</td>
                                <td>{$row['to_date']}</td>
                                <td>{$row['reason']}</td>
                                <td>{$row['status']}</td>
                            </tr>";
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
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Basic Pay</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT * FROM salary WHERE emp_id='$emp_id' ORDER BY year DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['month']}</td>
                                <td>{$row['year']}</td>
                                <td>{$row['basic_pay']}</td>
                                <td>{$row['allowances']}</td>
                                <td>{$row['deductions']}</td>
                                <td>{$row['net_pay']}</td>
                            </tr>";
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
                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>Description</th>
                            <th>Target Date</th>
                            <th>Status</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT * FROM tasks WHERE emp_id='$emp_id' ORDER BY target_date DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['task_name']}</td>
                                <td>{$row['description']}</td>
                                <td>{$row['target_date']}</td>
                                <td>{$row['status']}</td>
                                <td>{$row['hours_worked']}</td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Profile Section -->
        
        <div id="profile" class="section">
            <div class="form-card">

                <!-- View Profile -->
                <h3 class="section-title">My Profile</h3>
                <table class="emp-table">
                    <tr>
                        <td><b>Name</b></td>
                        <td><?php echo $emp['first_name'].' '.$emp['last_name']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Contact</b></td>
                        <td><?php echo $emp['contact']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Designation</b></td>
                        <td><?php echo $emp['designation']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Blood Group</b></td>
                        <td><?php echo $emp['blood_group']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Date of Birth</b></td>
                        <td><?php echo $emp['dob']; ?></td>
                    </tr>
                   <tr>
                        <td><b>Address</b></td>
                        <td><?php echo $emp['address']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Religion</b></td>
                        <td><?php echo $emp['religion']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Caste</b></td>
                        <td><?php echo $emp['caste']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Sub Caste</b></td>
                        <td><?php echo $emp['sub_caste']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Permanent Address</b></td>
                        <td><?php echo $emp['permanent_address']; ?></td>
                    </tr>
                    <tr>
                        <td><b>Common Address</b></td>
                        <td><?php echo $emp['common_address']; ?></td>
                    </tr>
                </table>

                <!-- Upload Documents -->
                <h3 class="section-title" style="margin-top:30px;">My Documents</h3>
                <table class="emp-table">
                    <tr>
                        <td><b>PAN Card</b></td>
                        <td>
                            <?php if($emp['pan_card']): ?>
                                <a href="uploads/<?php echo $emp['pan_card']; ?>" target="_blank" style="color:#3b82f6;">View PAN Card</a>
                            <?php else: ?>
                                <span style="color:#9ca3af;">Not uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Aadhar Card</b></td>
                        <td>
                            <?php if($emp['aadhar_card']): ?>
                                <a href="uploads/<?php echo $emp['aadhar_card']; ?>" target="_blank" style="color:#3b82f6;">View Aadhar Card</a>
                            <?php else: ?>
                                <span style="color:#9ca3af;">Not uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><b>Marks Card</b></td>
                        <td>
                            <?php if($emp['marks_card']): ?>
                                <a href="uploads/<?php echo $emp['marks_card']; ?>" target="_blank" style="color:#3b82f6;">View Marks Card</a>
                            <?php else: ?>
                                <span style="color:#9ca3af;">Not uploaded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>

                <!-- Upload Form -->
                <h3 class="section-title" style="margin-top:30px;">Upload Documents</h3>
                <form action="upload_documents.php" method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="field">
                            <label>PAN Card (PDF/Image)</label>
                            <input type="file" name="pan_card" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="field">
                            <label>Aadhar Card (PDF/Image)</label>
                            <input type="file" name="aadhar_card" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                        <div class="field">
                            <label>Marks Card (PDF/Image)</label>
                            <input type="file" name="marks_card" accept=".pdf,.jpg,.jpeg,.png">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Upload Documents</button>
                </form>

                <!-- Edit Profile Form -->
                <h3 class="section-title" style="margin-top:30px;">Edit Profile</h3>
                <form action="update_profile.php" method="POST">
                    <div class="form-grid">
                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="first_name" 
                            value="<?php echo $emp['first_name']; ?>" required>
                        </div>
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="last_name" 
                            value="<?php echo $emp['last_name']; ?>" required>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <input type="text" name="contact" 
                            value="<?php echo $emp['contact']; ?>" required>
                        </div>
                        <div class="field">
                            <label>Designation</label>
                            <input type="text" name="designation" 
                            value="<?php echo $emp['designation']; ?>" required>
                        </div>
                        <div class="field">
                            <label>Blood Group</label>
                            <select name="blood_group">
                                <?php
                                $blood_groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                                foreach($blood_groups as $bg){
                                    $selected = ($emp['blood_group'] == $bg) ? 'selected' : '';
                                    echo "<option value='$bg' $selected>$bg</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="field">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" 
                            value="<?php echo $emp['dob']; ?>" required>
                        </div>
                        <div class="field">
                            <label>Address</label>
                            <input type="text" name="address" 
                            value="<?php echo $emp['address']; ?>" required>
                        </div>
                        <div class="field">
                            <label>Religion</label>
                            <input type="text" name="religion" 
                            value="<?php echo $emp['religion']; ?>">
                        </div>
                        <div class="field">
                            <label>Caste</label>
                            <input type="text" name="caste" 
                            value="<?php echo $emp['caste']; ?>">
                        </div>
                        <div class="field">
                            <label>Sub Caste</label>
                            <input type="text" name="sub_caste" 
                            value="<?php echo $emp['sub_caste']; ?>">
                        </div>
                        <div class="field">
                            <label>Permanent Address</label>
                            <input type="text" name="permanent_address" 
                            value="<?php echo $emp['permanent_address']; ?>">
                        </div>
                        <div class="field">
                            <label>Common Address</label>
                            <input type="text" name="common_address" 
                            value="<?php echo $emp['common_address']; ?>">
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Update Profile</button>
                </form>

            </div>
        </div>

<?php
    // Attendance data - count present days per month
    $att_data = array_fill(0, 12, 0);
    $att_result = mysqli_query($conn, "SELECT MONTH(date) as mon, COUNT(*) as cnt 
                                       FROM attendance 
                                       WHERE emp_id='$emp_id' AND status='present'
                                       AND YEAR(date) = YEAR(CURDATE())
                                       GROUP BY MONTH(date)");
    while($row = mysqli_fetch_assoc($att_result)){
        $att_data[$row['mon'] - 1] = $row['cnt'];
    }

    // Leave data - count leaves per month
    $leave_data = array_fill(0, 12, 0);
    $leave_result = mysqli_query($conn, "SELECT MONTH(from_date) as mon, COUNT(*) as cnt 
                                         FROM leaves 
                                         WHERE emp_id='$emp_id'
                                         AND YEAR(from_date) = YEAR(CURDATE())
                                         GROUP BY MONTH(from_date)");
    while($row = mysqli_fetch_assoc($leave_result)){
        $leave_data[$row['mon'] - 1] = $row['cnt'];
    }

    $att_json   = json_encode($att_data);
    $leave_json = json_encode($leave_data);
?>

<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

// Attendance Bar Chart
new Chart(document.getElementById('attendanceChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Days Present',
            data: <?php echo $att_json; ?>,
            backgroundColor: 'rgba(59,130,246,0.7)',
            borderColor: '#3b82f6',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// Leave Bar Chart
new Chart(document.getElementById('leaveChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Leaves Taken',
            data: <?php echo $leave_json; ?>,
            backgroundColor: 'rgba(239,68,68,0.7)',
            borderColor: '#ef4444',
            borderWidth: 1,
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>



<script>
function showSection(name, el) {
    document.querySelectorAll('.section').forEach(s => {
        s.classList.remove('active');
    });
    document.querySelectorAll('.nav-item').forEach(n => {
        n.classList.remove('active');
    });
    document.getElementById(name).classList.add('active');
    el.classList.add('active');
    document.getElementById('page-title').innerText = el.innerText;
}
</script>

</body>
</html>