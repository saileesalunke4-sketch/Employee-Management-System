<?php
// sidebar_admin.php — include this in every admin page
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
        <?php navi('admin_dashboard.php','admin_dashboard.php','&#127968;','Dashboard'); ?>

        <div class="nav-section-label">Employees</div>
        <?php navi('add_employee.php','add_employee.php','&#43;','Add Employee'); ?>
        <?php navi('view_employees.php','view_employees.php','&#128100;','View Employees'); ?>
        <?php navi('departments.php','departments.php','&#127970;','Departments'); ?>
        <?php navi('org_chart.php','org_chart.php','&#127796;','Organization Chart'); ?>

        <div class="nav-section-label">Attendance &amp; Leave</div>
        <?php navi('admin_attendance.php','admin_attendance.php','&#128197;','Attendance'); ?>
        <?php navi('admin_leaves.php','admin_leaves.php','&#127809;','Leaves'); ?>
        <?php navi('leave_calendar.php','leave_calendar.php','&#128197;','Leave Calendar'); ?>
        <?php navi('leave_types.php','leave_types.php','&#128221;','Leave Types'); ?>
        <?php navi('admin_holidays.php','admin_holidays.php','&#127974;','Holiday Calendar'); ?>

        <div class="nav-section-label">Work &amp; Finance</div>
        <?php navi('admin_tasks.php','admin_tasks.php','&#9989;','Tasks'); ?>
        <?php navi('projects.php','projects.php','&#128196;','Projects'); ?>
        <?php navi('admin_salary.php','admin_salary.php','&#128176;','Salary'); ?>

        <div class="nav-section-label">Company</div>
        <?php navi('admin_hr_requests.php','admin_hr_requests.php','&#128203;','HR Process Requests'); ?>
        <?php navi('announcements.php','announcements.php','&#128227;','Announcements'); ?>
        <?php navi('admin_rules.php','admin_rules.php','&#128221;','Rules & Regulations'); ?>

        <div class="nav-section-label">Reports &amp; Account</div>
        <?php navi('employee_report.php','employee_report.php','&#128196;','Employee Report'); ?>
        <?php navi('admin_profile.php','admin_profile.php','&#128100;','My Profile'); ?>
    </nav>
    <a href="logout.php" class="logout-btn">Logout</a>
    <div style="padding:14px 16px;border-top:1px solid rgba(255,255,255,.07);">
        <p style="font-size:10px;color:rgba(255,255,255,.22);text-align:center;line-height:1.8;">&copy; <?php echo date('Y'); ?> Aller Technologies<br>All rights reserved.</p>
    </div>
</div>
