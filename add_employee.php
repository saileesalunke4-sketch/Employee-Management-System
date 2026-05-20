<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: index.php");
    exit();
}

$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $blood_group = $_POST['blood_group'];
    $dob = $_POST['dob'];
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    $user_query = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
    if(mysqli_query($conn, $user_query)){
        $user_id = mysqli_insert_id($conn);
        $emp_query = "INSERT INTO employees (user_id, first_name, last_name, contact, designation, blood_group, dob, religion, address) VALUES ('$user_id','$first_name','$last_name','$contact','$designation','$blood_group','$dob','$religion','$address')";
        if(mysqli_query($conn, $emp_query)){
            $success = "Employee added successfully!";
        }
    } else {
        $error = "Email already exists!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Employee - EMS</title>
    <link rel="stylesheet" href="style.css">
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
            <a href="admin_dashboard.php" class="nav-item">&#127968; Dashboard</a>
            <a href="add_employee.php" class="nav-item active">&#43; Add Employee</a>
            <a href="view_employees.php" class="nav-item">&#128100; View Employees</a>
            <a href="admin_attendance.php" class="nav-item">&#128197; Attendance</a>
            <a href="admin_leaves.php" class="nav-item">&#127809; Leaves</a>
            <a href="admin_salary.php" class="nav-item">&#128176; Salary</a>
            <a href="admin_tasks.php" class="nav-item">&#9989; Tasks</a>
            <a href="leave_types.php" class="nav-item">&#128221; Leave Types</a>
            <a href="departments.php" class="nav-item">&#127970; Departments</a>
            <a href="projects.php" class="nav-item">&#128196; Projects</a>
            <a href="admin_holidays.php" class="nav-item">&#127974; Holiday Calendar</a>
            <a href="admin_profile.php" class="nav-item">&#128100; My Profile</a>
        </nav>
        <a href="logout.php" class="logout-btn">Logout</a>
        <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
            <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Add New Employee</h2>
            <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
        </div>

        <?php if($success){ ?>
            <div style="background:#dcfce7;color:#16a34a;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;">&#10003; <?php echo $success; ?></div>
        <?php } ?>
        <?php if($error){ ?>
            <div style="background:#fee2e2;color:#dc2626;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;">&#10007; <?php echo $error; ?></div>
        <?php } ?>

        <div class="form-card">
            <form action="add_employee.php" method="POST">
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
</div>
</body>
</html>
