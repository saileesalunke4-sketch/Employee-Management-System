<?php

$current_page = basename($_SERVER['PHP_SELF']);

$nav_groups = [
    'My Space' => [
        ['emp_dashboard.php','emp_dashboard.php','grid','Dashboard'],
        ['my_attendance.php','my_attendance.php','clock','My Attendance'],
        ['my_leaves.php','my_leaves.php','leaf','My Leaves'],
        ['leave_calendar.php','leave_calendar.php','calendar','Leave Calendar'],
        ['my_tasks.php','my_tasks.php','check-square','My Tasks'],
        ['timesheet.php','timesheet.php','list','Timesheet'],
        ['my_performance.php','my_performance.php','target','My Performance'],
        ['daily_log.php','daily_log.php','file-text','Daily Work Log'],
        ['my_salary.php','my_salary.php','wallet','My Salary'],
        ['my_reimbursements.php','my_reimbursements.php','tag','My Reimbursements'],
        ['emp_profile.php','emp_profile.php','user','My Profile'],
        ['change_password.php','change_password.php','shield','Change Password'],
    ],
    'Team' => [
        ['org_chart.php','org_chart.php','sitemap','Organization Chart'],
        ['department_wall.php','department_wall.php','message-circle','Department Wall'],
        ['announcements.php','announcements.php','megaphone','Announcements'],
    ],
    'Company' => [
        ['emp_holidays.php','emp_holidays.php','flag','Holiday Calendar'],
        ['emp_rules.php','emp_rules.php','shield','Rules & Regulations'],
    ],
];

// Shared inline-icon renderer (also used by topbar_emp.php, which is
// always included right after this file on every employee page).
if(!function_exists('ems_icon')){
    function ems_icon($name, $size = 18){
        $paths = [
            'grid'         => '<rect x="3" y="3" width="7" height="7" rx="1.6"/><rect x="14" y="3" width="7" height="7" rx="1.6"/><rect x="3" y="14" width="7" height="7" rx="1.6"/><rect x="14" y="14" width="7" height="7" rx="1.6"/>',
            'user'         => '<circle cx="12" cy="8" r="3.6"/><path d="M4.5 20c1.4-4 4.2-6 7.5-6s6.1 2 7.5 6"/>',
            'user-plus'    => '<circle cx="9.5" cy="8" r="3.4"/><path d="M2.8 20c1.3-3.8 3.9-5.7 6.7-5.7s5.4 1.9 6.7 5.7"/><path d="M18.5 8v6M15.5 11h6"/>',
            'users'        => '<circle cx="8.5" cy="8" r="3.2"/><path d="M2.5 19.5c1.2-3.5 3.5-5.2 6-5.2s4.8 1.7 6 5.2"/><circle cx="17" cy="8.5" r="2.6"/><path d="M15.8 14.6c2.1.2 3.7 1.7 4.7 4.7"/>',
            'building'     => '<rect x="4" y="3" width="12" height="18" rx="1.4"/><path d="M9 8h2M13 8h2M9 12h2M13 12h2M9 16h2M13 16h2"/><path d="M16 21h4v-9h-4"/>',
            'sitemap'      => '<rect x="9" y="3" width="6" height="5" rx="1.2"/><rect x="3" y="16" width="6" height="5" rx="1.2"/><rect x="15" y="16" width="6" height="5" rx="1.2"/><path d="M12 8v4M6 16v-2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/>',
            'clock'        => '<circle cx="12" cy="12" r="8.6"/><path d="M12 7.4V12l3.2 2"/>',
            'leaf'         => '<path d="M5 19c9 1 14-4 14-13 0 0-11-2-14 6-1.6 4 0 7 0 7Z"/><path d="M5 19c2-4 5-7 9-9"/>',
            'calendar'     => '<rect x="3.2" y="4.5" width="17.6" height="16" rx="2"/><path d="M3.2 9.5h17.6M8 3v3M16 3v3"/>',
            'tag'          => '<path d="M12.5 3.5H5.8A2.3 2.3 0 0 0 3.5 5.8v6.7c0 .6.24 1.2.67 1.63l8.7 8.7a2.3 2.3 0 0 0 3.26 0l6.7-6.7a2.3 2.3 0 0 0 0-3.26l-8.7-8.7a2.3 2.3 0 0 0-1.63-.67Z"/><circle cx="8.6" cy="9.6" r="1.4"/>',
            'flag'         => '<path d="M5 21V4"/><path d="M5 4.5c1.8-1.3 3.7-1.3 5.5 0s3.7 1.3 5.5 0v9c-1.8 1.3-3.7 1.3-5.5 0s-3.7-1.3-5.5 0Z"/>',
            'check-square' => '<rect x="3.5" y="3.5" width="17" height="17" rx="3"/><path d="M8 12.2l2.6 2.6L16.3 9"/>',
            'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="M7.8 12.4l2.7 2.7L16.3 9"/>',
            'folder'       => '<path d="M3.5 6.5A1.6 1.6 0 0 1 5.1 5h4l2 2.2h7.8a1.6 1.6 0 0 1 1.6 1.6v9.3a1.6 1.6 0 0 1-1.6 1.6H5.1a1.6 1.6 0 0 1-1.6-1.6Z"/>',
            'wallet'       => '<rect x="3" y="6.5" width="18" height="13" rx="2.2"/><path d="M3 10h18"/><circle cx="16.6" cy="14.6" r="1.1"/><path d="M7 6.5V5.3A1.8 1.8 0 0 1 8.8 3.5h6.9"/>',
            'inbox'        => '<path d="M3.5 12.5h4.7l1.6 2.6h4.4l1.6-2.6h4.7"/><path d="M5.4 5.6h13.2l1.9 6.9v7.3a1.7 1.7 0 0 1-1.7 1.7H5.2a1.7 1.7 0 0 1-1.7-1.7v-7.3Z"/>',
            'megaphone'    => '<path d="M3.5 10.5v3a1.4 1.4 0 0 0 1.4 1.4H6l1.6 5h2.1l-1.3-5h1.4l8.7 3.4V6.6L9.8 10H4.9a1.4 1.4 0 0 0-1.4 1.4Z"/>',
            'shield'       => '<path d="M12 3.3 19 6v5.6c0 4.6-3 8-7 9.1-4-1.1-7-4.5-7-9.1V6Z"/><path d="M9 12l2.2 2.2L15.5 9.5"/>',
            'bar-chart'    => '<path d="M4 20V10.5M12 20V4M20 20v-6.5"/><path d="M2.5 20h19"/>',
            'search'       => '<circle cx="10.8" cy="10.8" r="6.8"/><path d="M20 20l-4.4-4.4"/>',
            'plus-circle'  => '<circle cx="12" cy="12" r="9"/><path d="M12 8v8M8 12h8"/>',
            'bell'         => '<path d="M6 9a6 6 0 0 1 12 0c0 4.2 1.2 6 2 6.8H4c.8-.8 2-2.6 2-6.8Z"/><path d="M10 19.5a2.2 2.2 0 0 0 4 0"/>',
            'chevron-down' => '<path d="M6 9l6 6 6-6"/>',
            'log-out'      => '<path d="M9 21H5.5A2.5 2.5 0 0 1 3 18.5v-13A2.5 2.5 0 0 1 5.5 3H9"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>',
            'menu'         => '<path d="M3.5 6.5h17M3.5 12h17M3.5 17.5h17"/>',
            'list'         => '<path d="M8 6h13M8 12h13M8 18h13"/><circle cx="3.4" cy="6" r="1.3" fill="currentColor" stroke="none"/><circle cx="3.4" cy="12" r="1.3" fill="currentColor" stroke="none"/><circle cx="3.4" cy="18" r="1.3" fill="currentColor" stroke="none"/>',
            'target'       => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.4" fill="currentColor" stroke="none"/>',
            'file-text'    => '<path d="M6.5 3.5h7l4 4v13a1 1 0 0 1-1 1h-10a1 1 0 0 1-1-1v-16a1 1 0 0 1 1-1Z"/><path d="M13.2 3.5v4.3h4.2"/><path d="M8.5 12.5h7M8.5 15.8h7M8.5 9.2h3"/>',
            'message-circle'=> '<path d="M4 12a8 8 0 1 1 3.4 6.5L4 20l1.2-3.7A7.9 7.9 0 0 1 4 12Z"/>',
        ];
        $p = $paths[$name] ?? $paths['grid'];
        return '<svg class="ic-svg" width="'.$size.'" height="'.$size.'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">'.$p.'</svg>';
    }
}

$active_group = array_key_first($nav_groups);
foreach($nav_groups as $g => $items){
    foreach($items as $it){ if($it[0] === $current_page){ $active_group = $g; break 2; } }
}
?>
<aside class="app-sidebar" id="appSidebar">
    <div class="sidebar-brand">
        <img src="allerlogo.png" alt="Aller Technologies">
        <div class="brand-text">
            <b>Aller EMS</b>
            <span>EMPLOYEE PORTAL</span>
        </div>
        <button type="button" class="sidebar-toggle" id="sidebarToggle" title="Collapse sidebar"><?php echo ems_icon('chevron-down',14); ?></button>
    </div>

    <nav class="sidebar-scroll">
        <?php foreach($nav_groups as $g => $items): ?>
        <div class="sidebar-group">
            <div class="sidebar-group-label"><?php echo htmlspecialchars($g); ?></div>
            <?php foreach($items as $it):
                $is_active = ($it[0] === $current_page);
            ?>
            <a href="<?php echo $it[1]; ?>" class="sidebar-link <?php echo $is_active?'active':''; ?>" data-tip="<?php echo htmlspecialchars($it[3]); ?>">
                <span class="ic"><?php echo ems_icon($it[2]); ?></span>
                <span class="label"><?php echo htmlspecialchars($it[3]); ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="sidebar-logout" data-tip="Log Out">
            <span class="ic"><?php echo ems_icon('log-out'); ?></span>
            <span class="label">Log Out</span>
        </a>
    </div>
</aside>

<script>
(function(){
    var sidebar = document.getElementById('appSidebar');
    var toggle  = document.getElementById('sidebarToggle');
    if(!sidebar || !toggle) return;

    var saved = localStorage.getItem('ems_sidebar_collapsed');
    if(saved === '1') sidebar.classList.add('is-collapsed');

    toggle.addEventListener('click', function(){
        sidebar.classList.toggle('is-collapsed');
        localStorage.setItem('ems_sidebar_collapsed', sidebar.classList.contains('is-collapsed') ? '1' : '0');
    });

    window.emsToggleMobileSidebar = function(){
        sidebar.classList.toggle('is-open');
    };
    document.addEventListener('click', function(e){
        if(window.innerWidth > 900) return;
        if(sidebar.classList.contains('is-open') && !sidebar.contains(e.target) && !e.target.closest('#mobileSidebarBtn')){
            sidebar.classList.remove('is-open');
        }
    });
})();
</script>

<script>
(function(){
    var bar = document.createElement('div');
    bar.id = 'emsProgressBar';
    document.body.appendChild(bar);
    document.addEventListener('click', function(e){
        var a = e.target.closest('a[href]');
        if(!a) return;
        var href = a.getAttribute('href');
        if(!href || href.charAt(0) === '#' || href.indexOf('javascript:') === 0) return;
        if(a.target === '_blank' || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
        requestAnimationFrame(function(){ bar.style.width = '72%'; });
    }, true);
    document.addEventListener('submit', function(){
        requestAnimationFrame(function(){ bar.style.width = '72%'; });
    }, true);
})();
</script>
