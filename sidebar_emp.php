<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);">
        <img src="allerlogo.png" alt="Aller" style="height:55px;display:block;margin:0 auto 8px;">
        <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
    </div>
    <nav>
        <a class="nav-item <?php echo ($current_page=='emp_dashboard.php')?'active':''; ?>" href="emp_dashboard.php">&#127968; Dashboard</a>
        <a class="nav-item <?php echo ($current_page=='my_attendance.php')?'active':''; ?>" href="my_attendance.php">&#128197; My Attendance</a>
        <a class="nav-item <?php echo ($current_page=='my_leaves.php')?'active':''; ?>" href="my_leaves.php">&#127809; My Leaves</a>
        <a class="nav-item <?php echo ($current_page=='my_salary.php')?'active':''; ?>" href="my_salary.php">&#128176; My Salary</a>
        <a class="nav-item <?php echo ($current_page=='my_tasks.php')?'active':''; ?>" href="my_tasks.php">&#9989; My Tasks</a>
        <a class="nav-item <?php echo ($current_page=='timesheet.php')?'active':''; ?>" href="timesheet.php">&#9200; Timesheet</a>
        <a class="nav-item <?php echo ($current_page=='my_performance.php')?'active':''; ?>" href="my_performance.php">&#127941; My Performance</a>
        <a class="nav-item <?php echo ($current_page=='daily_log.php')?'active':''; ?>" href="daily_log.php">&#128221; Daily Work Log</a>
        <a class="nav-item <?php echo ($current_page=='emp_holidays.php')?'active':''; ?>" href="emp_holidays.php">&#127974; Holiday Calendar</a>
        <a class="nav-item <?php echo ($current_page=='emp_profile.php')?'active':''; ?>" href="emp_profile.php">&#128100; My Profile</a>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>
