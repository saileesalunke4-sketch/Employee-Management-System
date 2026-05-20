<?php
// sidebar_admin.php — include this in every admin page
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);">
        <img src="allerlogo.png" alt="Aller" style="height:55px;display:block;margin:0 auto 8px;">
        <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
    </div>
    <nav>
        <a class="nav-item <?php echo ($current_page=='admin_dashboard.php')?'active':''; ?>" href="admin_dashboard.php">&#127968; Dashboard</a>
        <a class="nav-item <?php echo ($current_page=='add_employee.php')?'active':''; ?>" href="add_employee.php">&#43; Add Employee</a>
        <a class="nav-item <?php echo ($current_page=='view_employees.php')?'active':''; ?>" href="view_employees.php">&#128100; View Employees</a>
        <a class="nav-item <?php echo ($current_page=='admin_attendance.php')?'active':''; ?>" href="admin_attendance.php">&#128197; Attendance</a>
        <a class="nav-item <?php echo ($current_page=='admin_leaves.php')?'active':''; ?>" href="admin_leaves.php">&#127809; Leaves</a>
        <a class="nav-item <?php echo ($current_page=='admin_salary.php')?'active':''; ?>" href="admin_salary.php">&#128176; Salary</a>
        <a class="nav-item <?php echo ($current_page=='admin_tasks.php')?'active':''; ?>" href="admin_tasks.php">&#9989; Tasks</a>
        <a class="nav-item <?php echo ($current_page=='leave_types.php')?'active':''; ?>" href="leave_types.php">&#128221; Leave Types</a>
        <a class="nav-item <?php echo ($current_page=='departments.php')?'active':''; ?>" href="departments.php">&#127970; Departments</a>
        <a class="nav-item <?php echo ($current_page=='projects.php')?'active':''; ?>" href="projects.php">&#128196; Projects</a>
        <a class="nav-item <?php echo ($current_page=='admin_holidays.php')?'active':''; ?>" href="admin_holidays.php">&#127974; Holiday Calendar</a>
        <a class="nav-item <?php echo ($current_page=='admin_profile.php')?'active':''; ?>" href="admin_profile.php">&#128100; My Profile</a>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>
