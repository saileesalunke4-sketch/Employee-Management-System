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
        <?php navi('super_admin_dashboard.php','super_admin_dashboard.php','&#127968;','Dashboard'); ?>

        <div class="nav-section-label">Employees</div>
        <?php navi('all_employees.php','all_employees.php','&#128100;','All Employees'); ?>
        <?php navi('org_chart.php','org_chart.php','&#127796;','Organization Chart'); ?>

        <div class="nav-section-label">Attendance &amp; Leave</div>
        <?php navi('sa_attendance.php','sa_attendance.php','&#128197;','Attendance'); ?>
        <?php navi('sa_leaves.php','sa_leaves.php','&#127809;','Leaves'); ?>
        <?php navi('leave_calendar.php','leave_calendar.php','&#128197;','Leave Calendar'); ?>
        <?php navi('sa_holidays.php','sa_holidays.php','&#127974;','Holiday Calendar'); ?>

        <div class="nav-section-label">Work &amp; Finance</div>
        <?php navi('sa_tasks.php','sa_tasks.php','&#9989;','Tasks'); ?>
        <?php navi('sa_salary.php','sa_salary.php','&#128176;','Salary'); ?>
        <?php navi('revenue.php','revenue.php','&#128200;','Monthly Revenue'); ?>
        <?php navi('performance.php','performance.php','&#127941;','Performance'); ?>

        <div class="nav-section-label">Company</div>
        <?php navi('admin_hr_requests.php','admin_hr_requests.php','&#128203;','HR Process Requests'); ?>
        <?php navi('announcements.php','announcements.php','&#128227;','Announcements'); ?>
        <?php navi('sa_rules.php','sa_rules.php','&#128221;','Rules & Regulations'); ?>

        <div class="nav-section-label">Reports &amp; Account</div>
        <?php navi('employee_report.php','employee_report.php','&#128196;','Employee Report'); ?>
        <?php navi('sa_profile.php','sa_profile.php','&#128100;','My Profile'); ?>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>
