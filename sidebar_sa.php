<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);">
        <img src="allerlogo.png" alt="Aller" style="height:55px;display:block;margin:0 auto 8px;">
        <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
    </div>
    <nav>
        <a class="nav-item <?php echo ($current_page=='super_dashboard.php')?'active':''; ?>" href="super_dashboard.php">&#127968; Dashboard</a>
        <a class="nav-item <?php echo ($current_page=='all_employees.php')?'active':''; ?>" href="all_employees.php">&#128100; All Employees</a>
        <a class="nav-item <?php echo ($current_page=='sa_attendance.php')?'active':''; ?>" href="sa_attendance.php">&#128197; Attendance</a>
        <a class="nav-item <?php echo ($current_page=='sa_leaves.php')?'active':''; ?>" href="sa_leaves.php">&#127809; Leaves</a>
        <a class="nav-item <?php echo ($current_page=='sa_salary.php')?'active':''; ?>" href="sa_salary.php">&#128176; Salary</a>
        <a class="nav-item <?php echo ($current_page=='sa_tasks.php')?'active':''; ?>" href="sa_tasks.php">&#9989; Tasks</a>
        <a class="nav-item <?php echo ($current_page=='revenue.php')?'active':''; ?>" href="revenue.php">&#128200; Monthly Revenue</a>
        <a class="nav-item <?php echo ($current_page=='performance.php')?'active':''; ?>" href="performance.php">&#127941; Performance</a>
        <a class="nav-item <?php echo ($current_page=='sa_holidays.php')?'active':''; ?>" href="sa_holidays.php">&#127974; Holiday Calendar</a>
        <a class="nav-item <?php echo ($current_page=='sa_profile.php')?'active':''; ?>" href="sa_profile.php">&#128100; My Profile</a>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>
