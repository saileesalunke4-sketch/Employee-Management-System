<?php
$current_page = basename($_SERVER['PHP_SELF']);
function navi($page, $href, $icon, $label){
    global $current_page;
    $active = ($current_page == $page) ? 'active' : '';
    echo "<a class=\"nav-item $active\" href=\"$href\">$icon $label</a>";
}
?>
<div class="sidebar">
    <div style="padding:20px 16px 24px;text-align:center;border-bottom:1px solid rgba(255,255,255,.08);">
        <img src="allerlogo.png" alt="Aller" style="height:55px;display:block;margin:0 auto 8px;">
        <span style="font-size:13px;font-weight:bold;color:rgba(255,255,255,.5);letter-spacing:3px;text-transform:uppercase;">EMS</span>
    </div>
    <nav>
        <div class="nav-section-label">Main</div>
        <?php navi('emp_dashboard.php','emp_dashboard.php','&#127968;','Dashboard'); ?>

        <div class="nav-section-label">Attendance &amp; Leave</div>
        <?php navi('my_attendance.php','my_attendance.php','&#128197;','My Attendance'); ?>
        <?php navi('my_leaves.php','my_leaves.php','&#127809;','My Leaves'); ?>
        <?php navi('leave_calendar.php','leave_calendar.php','&#128197;','Leave Calendar'); ?>
        <?php navi('emp_holidays.php','emp_holidays.php','&#127974;','Holiday Calendar'); ?>

        <div class="nav-section-label">Work</div>
        <?php navi('my_tasks.php','my_tasks.php','&#9989;','My Tasks'); ?>
        <?php navi('timesheet.php','timesheet.php','&#9200;','Timesheet'); ?>
        <?php navi('my_performance.php','my_performance.php','&#127941;','My Performance'); ?>
        <?php navi('daily_log.php','daily_log.php','&#128221;','Daily Work Log'); ?>

        <div class="nav-section-label">Company</div>
        <?php navi('org_chart.php','org_chart.php','&#127970;','Organization Chart'); ?>
        <?php navi('hr_requests.php','hr_requests.php','&#128203;','HR Process Requests'); ?>
        <?php navi('announcements.php','announcements.php','&#128227;','Announcements'); ?>
        <?php navi('department_wall.php','department_wall.php','&#128172;','Department Wall'); ?>

        <div class="nav-section-label">Account</div>
        <?php navi('my_salary.php','my_salary.php','&#128176;','My Salary'); ?>
        <?php navi('emp_rules.php','emp_rules.php','&#128221;','Rules & Regulations'); ?>
        <?php navi('emp_profile.php','emp_profile.php','&#128100;','My Profile'); ?>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>
