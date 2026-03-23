<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'super_admin'){
    header("Location: index.php");
    exit();
}
require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Super Admin Dashboard - EMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard">

    <!-- Sidebar -->
    <div class="sidebar">
        <div class="side-brand">EMS</div>
        <nav>
            <a class="nav-item active" onclick="showSection('dashboard', this)">Dashboard</a>
            <a class="nav-item" onclick="showSection('employees', this)">All Employees</a>
            <a class="nav-item" onclick="showSection('attendance', this)">Attendance</a>
            <a class="nav-item" onclick="showSection('leaves', this)">Leaves</a>
            <a class="nav-item" onclick="showSection('salary', this)">Salary</a>
            <a class="nav-item" onclick="showSection('tasks', this)">Tasks</a>
            <a class="nav-item" onclick="showSection('revenue', this)">Monthly Revenue</a>
            <a class="nav-item" onclick="showSection('performance', this)">Performance</a>
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

        <!-- Dashboard -->
        <div id="dashboard" class="section active">
            <div class="cards">
                <div class="card">
                    <h3>Total Employees</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM employees");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>Present Today</h3>
                    <p class="num"><?php
                        $today = date('Y-m-d');
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM attendance WHERE date='$today' AND status='present'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>Pending Leaves</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM leaves WHERE status='pending'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>Total Tasks</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
            </div>
        </div>

        <!-- All Employees -->
        <div id="employees" class="section">
            <div class="form-card">
                <h3 class="section-title">All Employees</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Contact</th>
                            <th>Blood Group</th>
                            <th>DOB</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT u.id, u.name, u.email, u.role, e.designation, e.contact, e.blood_group, e.dob FROM users u LEFT JOIN employees e ON u.id = e.user_id WHERE u.role='employee'");                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['designation']}</td>
                                <td>{$row['contact']}</td>
                                <td>{$row['blood_group']}</td>
                                <td>{$row['dob']}</td>
                                <td>{$row['role']}</td>
                            </tr>";
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT a.*, e.first_name, e.last_name FROM attendance a JOIN employees e ON a.emp_id = e.emp_id ORDER BY a.date DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
                                <td>{$row['date']}</td>
                                <td>{$row['check_in']}</td>
                                <td>{$row['check_out']}</td>
                                <td>{$row['status']}</td>
                                <td><a href='regularize.php?id={$row['attendance_id']}' class='approve-btn'>Regularize</a></td>
                            </tr>";
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
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.emp_id = e.emp_id ORDER BY l.leave_id DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
                                <td>{$row['leave_type']}</td>
                                <td>{$row['from_date']}</td>
                                <td>{$row['to_date']}</td>
                                <td>{$row['reason']}</td>
                                <td>{$row['status']}</td>
                                <td>
                                    <a href='leave_action.php?id={$row['leave_id']}&action=approved' class='approve-btn'>Approve</a>
                                    <a href='leave_action.php?id={$row['leave_id']}&action=rejected' class='reject-btn'>Reject</a>
                                </td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Salary -->
        <div id="salary" class="section">
            <div class="form-card">
                <h3 class="section-title">All Salary Records</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Basic Pay</th>
                            <th>Allowances</th>
                            <th>Deductions</th>
                            <th>Net Pay</th>
                            <th>Month</th>
                            <th>Year</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT s.*, e.first_name, e.last_name FROM salary s JOIN employees e ON s.emp_id = e.emp_id ORDER BY s.year DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
                                <td>{$row['basic_pay']}</td>
                                <td>{$row['allowances']}</td>
                                <td>{$row['deductions']}</td>
                                <td>{$row['net_pay']}</td>
                                <td>{$row['month']}</td>
                                <td>{$row['year']}</td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tasks -->
        <div id="tasks" class="section">
            <div class="form-card">
                <h3 class="section-title">All Tasks</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Task</th>
                            <th>Description</th>
                            <th>Target Date</th>
                            <th>Status</th>
                            <th>Hours</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT t.*, e.first_name, e.last_name FROM tasks t JOIN employees e ON t.emp_id = e.emp_id ORDER BY t.target_date DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
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

        <!-- Monthly Revenue -->
        <div id="revenue" class="section">
            <div class="form-card">
                <h3 class="section-title">Add Monthly Revenue</h3>
                <form action="save_revenue.php" method="POST">
                    <div class="form-grid">
                        <div class="field">
                            <label>Month</label>
                            <select name="month">
                                <option>January</option>
                                <option>February</option>
                                <option>March</option>
                                <option>April</option>
                                <option>May</option>
                                <option>June</option>
                                <option>July</option>
                                <option>August</option>
                                <option>September</option>
                                <option>October</option>
                                <option>November</option>
                                <option>December</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Year</label>
                            <input type="number" name="year" value="2026" required>
                        </div>
                        <div class="field">
                            <label>Revenue Amount</label>
                            <input type="number" name="amount" placeholder="Enter amount" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Add Revenue</button>
                </form>

                <h3 class="section-title" style="margin-top:30px;">Revenue Records</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Year</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT * FROM revenue ORDER BY year DESC");
                        if($result){
                            while($row = mysqli_fetch_assoc($result)){
                                echo "<tr>
                                    <td>{$row['month']}</td>
                                    <td>{$row['year']}</td>
                                    <td>{$row['amount']}</td>
                                </tr>";
                            }
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Performance -->
        <div id="performance" class="section">
            <div class="form-card">
                <h3 class="section-title">Employee Performance</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Total Tasks</th>
                            <th>Completed</th>
                            <th>Pending</th>
                            <th>Attendance Days</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT e.first_name, e.last_name, e.emp_id,
                            (SELECT COUNT(*) FROM tasks WHERE emp_id = e.emp_id) as total_tasks,
                            (SELECT COUNT(*) FROM tasks WHERE emp_id = e.emp_id AND status='completed') as completed,
                            (SELECT COUNT(*) FROM tasks WHERE emp_id = e.emp_id AND status='pending') as pending,
                            (SELECT COUNT(*) FROM attendance WHERE emp_id = e.emp_id) as attendance_days
                            FROM employees e");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
                                <td>{$row['total_tasks']}</td>
                                <td>{$row['completed']}</td>
                                <td>{$row['pending']}</td>
                                <td>{$row['attendance_days']}</td>
                            </tr>";
                        }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

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