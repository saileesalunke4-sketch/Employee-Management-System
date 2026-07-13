<?php
$current_page = basename($_SERVER['PHP_SELF']);

$nav_groups = [
    'My Space' => [
        ['super_admin_dashboard.php','super_admin_dashboard.php','&#127968;','Dashboard'],
        ['sa_profile.php','sa_profile.php','&#128100;','My Profile'],
    ],
    'Employees' => [
        ['all_employees.php','all_employees.php','&#128100;','All Employees'],
        ['org_chart.php','org_chart.php','&#127796;','Organization Chart'],
    ],
    'Operations' => [
        ['sa_attendance.php','sa_attendance.php','&#128197;','Attendance'],
        ['sa_leaves.php','sa_leaves.php','&#127809;','Leaves'],
        ['leave_calendar.php','leave_calendar.php','&#128197;','Leave Calendar'],
        ['sa_holidays.php','sa_holidays.php','&#127974;','Holiday Calendar'],
        ['sa_tasks.php','sa_tasks.php','&#9989;','Tasks'],
        ['sa_salary.php','sa_salary.php','&#128176;','Salary'],
        ['revenue.php','revenue.php','&#128200;','Monthly Revenue'],
        ['performance.php','performance.php','&#127941;','Performance'],
    ],
    'Company' => [
        ['admin_hr_requests.php','admin_hr_requests.php','&#128203;','HR Process Requests'],
        ['announcements.php','announcements.php','&#128227;','Announcements'],
        ['sa_rules.php','sa_rules.php','&#128221;','Rules & Regulations'],
        ['employee_report.php','employee_report.php','&#128196;','Employee Report'],
    ],
];

$active_group = array_key_first($nav_groups);
foreach($nav_groups as $g => $items){
    foreach($items as $it){ if($it[0] === $current_page){ $active_group = $g; break 2; } }
}
function slugify($s){ return strtolower(str_replace(' ','-',$s)); }
?>
<div class="topnav-primary">
    <div class="topnav-brand">
        <img src="allerlogo.png" alt="Aller">
        <span>EMS</span>
    </div>
    <div class="ptabs">
        <?php foreach($nav_groups as $g => $items):
            $slug = slugify($g);
            $is_active = ($g === $active_group);
        ?>
        <span class="ptab <?php echo $is_active?'active':''; ?>" data-group="<?php echo $slug; ?>"><?php echo htmlspecialchars($g); ?></span>
        <?php endforeach; ?>
    </div>
    <a href="logout.php" class="topnav-logout">Logout</a>
</div>

<div class="topnav-secondary">
    <?php foreach($nav_groups as $g => $items):
        $slug = slugify($g);
        $is_active = ($g === $active_group);
    ?>
    <div class="stab-group <?php echo $is_active?'shown':''; ?>" data-group="<?php echo $slug; ?>">
        <?php foreach($items as $it):
            $active_link = ($it[0] === $current_page) ? 'active' : '';
        ?>
        <a href="<?php echo $it[1]; ?>" class="stab <?php echo $active_link; ?>"><?php echo $it[2]; ?> <?php echo htmlspecialchars($it[3]); ?></a>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.ptab').forEach(function(tab){
    tab.addEventListener('click', function(){
        document.querySelectorAll('.ptab').forEach(function(t){ t.classList.remove('active'); });
        document.querySelectorAll('.stab-group').forEach(function(g){ g.classList.remove('shown'); });
        tab.classList.add('active');
        var target = document.querySelector('.stab-group[data-group="'+tab.dataset.group+'"]');
        if(target) target.classList.add('shown');
    });
});
</script>
