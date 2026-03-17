<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}
require 'db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard - EMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="dashboard">

    <div class="sidebar">
        <div class="side-brand">EMS</div>
        <nav>
            <a class="nav-item active" onclick="showSection('dashboard', this)">Dashboard</a>
            <a class="nav-item" onclick="showSection('add_employee', this)">Add Employee</a>
            <a class="nav-item" onclick="showSection('view_employees', this)">View Employees</a>
            <a class="nav-item" onclick="showSection('attendance', this)">Attendance</a>
            <a class="nav-item" onclick="showSection('leaves', this)">Leaves</a>
            <a class="nav-item" onclick="showSection('salary', this)">Salary</a>
            <a class="nav-item" onclick="showSection('tasks', this)">Tasks</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

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
                    <h3>On Leave</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM leaves WHERE status='approved'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
                <div class="card">
                    <h3>Pending Tasks</h3>
                    <p class="num"><?php
                        $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE status='pending'");
                        $row = mysqli_fetch_assoc($res);
                        echo $row['total'];
                    ?></p>
                </div>
            </div>
        </div>

        <!-- Add Employee -->
        <div id="add_employee" class="section">
            <div class="form-card">
                <form action="save_employee.php" method="POST">
                    <h3 class="section-title">Login Details</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label>Full Name</label>
                            <input type="text" name="name" placeholder="Full Name" required>
                        </div>
                        <div class="field">
                            <label>Email</label>
                            <input type="email" name="email" placeholder="Email" required>
                        </div>
                        <div class="field">
                            <label>Password</label>
                            <input type="password" name="password" placeholder="Password" required>
                        </div>
                        <div class="field">
                            <label>Role</label>
                            <select name="role">
                                <option value="employee">Employee</option>
                                <option value="admin">Admin</option>
                                <option value="super_admin">Super Admin</option>
                            </select>
                        </div>
                    </div>
                    <h3 class="section-title">Personal Details</h3>
                    <div class="form-grid">
                        <div class="field">
                            <label>First Name</label>
                            <input type="text" name="first_name" placeholder="First Name" required>
                        </div>
                        <div class="field">
                            <label>Last Name</label>
                            <input type="text" name="last_name" placeholder="Last Name" required>
                        </div>
                        <div class="field">
                            <label>Contact</label>
                            <input type="text" name="contact" placeholder="Contact Number" required>
                        </div>
                        <div class="field">
                            <label>Designation</label>
                            <input type="text" name="designation" placeholder="Designation" required>
                        </div>
                        <div class="field">
                            <label>Blood Group</label>
                            <select name="blood_group">
                                <option>A+</option>
                                <option>A-</option>
                                <option>B+</option>
                                <option>B-</option>
                                <option>O+</option>
                                <option>O-</option>
                                <option>AB+</option>
                                <option>AB-</option>
                            </select>
                        </div>
                        <div class="field">
                            <label>Date of Birth</label>
                            <input type="date" name="dob" required>
                        </div>
                        <div class="field">
                            <label>Religion</label>
                            <input type="text" name="religion" placeholder="Religion" required>
                        </div>
                        <div class="field">
                            <label>Address</label>
                            <input type="text" name="address" placeholder="Address" required>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">Add Employee</button>
                </form>
            </div>
        </div>

        <!-- View Employees -->
        <div id="view_employees" class="section">
            <div class="form-card">
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Designation</th>
                            <th>Contact</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT u.id, u.name, u.email, u.role, e.designation, e.contact FROM users u LEFT JOIN employees e ON u.id = e.user_id");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['id']}</td>
                                <td>{$row['name']}</td>
                                <td>{$row['email']}</td>
                                <td>{$row['designation']}</td>
                                <td>{$row['contact']}</td>
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
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                        $result = mysqli_query($conn, "SELECT e.first_name, e.last_name, a.date, a.check_in, a.check_out, a.status FROM attendance a JOIN employees e ON a.emp_id = e.emp_id ORDER BY a.date DESC");
                        while($row = mysqli_fetch_assoc($result)){
                            echo "<tr>
                                <td>{$row['first_name']} {$row['last_name']}</td>
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
                <h3 class="section-title">Salary Records</h3>
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
                <h3 class="section-title">Task Records</h3>
                <table class="emp-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Task</th>
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