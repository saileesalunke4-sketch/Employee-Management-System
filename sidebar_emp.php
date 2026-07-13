<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Zoho-style structure: primary groups (My Space / Team / Company),
// each with its own set of contextual links shown in row 2.
$nav_groups = [
    'My Space' => [
        ['emp_dashboard.php','emp_dashboard.php','&#127968;','Dashboard'],
        ['my_attendance.php','my_attendance.php','&#128197;','My Attendance'],
        ['my_leaves.php','my_leaves.php','&#127809;','My Leaves'],
        ['leave_calendar.php','leave_calendar.php','&#128197;','Leave Calendar'],
        ['my_tasks.php','my_tasks.php','&#9989;','My Tasks'],
        ['timesheet.php','timesheet.php','&#9200;','Timesheet'],
        ['my_performance.php','my_performance.php','&#127941;','My Performance'],
        ['daily_log.php','daily_log.php','&#128221;','Daily Work Log'],
        ['my_salary.php','my_salary.php','&#128176;','My Salary'],
        ['emp_profile.php','emp_profile.php','&#128100;','My Profile'],
    ],
    'Team' => [
        ['org_chart.php','org_chart.php','&#127796;','Organization Chart'],
        ['department_wall.php','department_wall.php','&#128172;','Department Wall'],
        ['announcements.php','announcements.php','&#128227;','Announcements'],
    ],
    'Company' => [
        ['hr_requests.php','hr_requests.php','&#128203;','HR Process Requests'],
        ['emp_holidays.php','emp_holidays.php','&#127974;','Holiday Calendar'],
        ['emp_rules.php','emp_rules.php','&#128221;','Rules & Regulations'],
    ],
];

// Which group contains the current page? (defaults to first group)
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
