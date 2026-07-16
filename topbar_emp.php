<?php
// topbar_emp.php — premium top header bar (Employee role)
// Requires: $conn, $page_title, $emp_id (set by the including page)
// ems_icon() is defined in sidebar_emp.php, included right before this file.
$user_id   = $_SESSION['user']['id'];
$emp_photo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$user_id'"))['profile_photo'] ?? '';
?>
<header class="app-topbar">
    <button type="button" class="icon-btn sidebar-toggle-mobile" id="mobileSidebarBtn" onclick="if(window.emsToggleMobileSidebar) window.emsToggleMobileSidebar();" title="Menu">
        <?php echo ems_icon('menu'); ?>
    </button>

    <div class="topbar-title">
        <span class="eyebrow">Employee Portal</span>
        <?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?>
    </div>

    <label class="topbar-search">
        <?php echo ems_icon('search',16); ?>
        <input type="text" placeholder="Search tasks, leaves, payslips…" onkeydown="if(event.key==='Enter'){ window.location.href='my_tasks.php?q='+encodeURIComponent(this.value); }">
        <kbd>/</kbd>
    </label>

    <div class="topbar-actions">
        <a href="my_leaves.php" class="icon-btn" title="Apply Leave"><?php echo ems_icon('leaf'); ?></a>
        <a href="leave_calendar.php" class="icon-btn" title="Leave Calendar"><?php echo ems_icon('calendar'); ?></a>

        <div class="notif-wrapper" id="notifWrapper">
            <div class="notif-bell icon-btn" onclick="toggleNotif()" title="Notifications">
                <?php echo ems_icon('bell'); ?>
                <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
            </div>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>My Notifications</span>
                    <a href="mark_emp_notifications_read.php" id="notifMarkAllRead" style="display:none;font-size:11px;color:#4F46E5;text-decoration:none;padding:4px 10px;border-radius:20px;border:1px solid #4F46E5;">Mark all read</a>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Loading…</div>
                </div>
            </div>
        </div>

        <a href="emp_profile.php" class="profile-chip">
            <?php if(!empty($emp_photo) && file_exists('uploads/'.$emp_photo)): ?>
                <img src="uploads/<?php echo htmlspecialchars($emp_photo); ?>" alt="">
            <?php else: ?>
                <span class="avatar-fallback"><?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?></span>
            <?php endif; ?>
            <span class="who">
                <b><?php echo htmlspecialchars($_SESSION['user']['name']); ?></b>
                <span>Employee</span>
            </span>
        </a>
    </div>
</header>

<script>
(function(){
    function escapeHtml(s){
        return String(s ?? '').replace(/[&<>"']/g, function(c){
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }

    function renderNotifications(data){
        var badge = document.getElementById('notifBadge');
        var list  = document.getElementById('notifList');
        var mark  = document.getElementById('notifMarkAllRead');
        var count = data.unread_count || 0;

        if(count > 0){
            badge.textContent = count;
            badge.style.display = 'flex';
            mark.style.display = 'inline-block';
        } else {
            badge.style.display = 'none';
            mark.style.display = 'none';
        }

        if(!data.items || !data.items.length){
            list.innerHTML = '<div class="notif-empty">No notifications yet</div>';
            return;
        }

        list.innerHTML = data.items.map(function(n){
            var isNew = n.is_read == 0;
            var icon  = n.is_task ? '<?php echo str_replace("'","\\'",ems_icon("check-square",15)); ?>' : '<?php echo str_replace("'","\\'",ems_icon("leaf",15)); ?>';
            return '' +
                '<div class="notif-item ' + (isNew ? 'notif-new' : '') + '">' +
                    '<div class="notif-icon">' + icon + '</div>' +
                    '<div class="notif-text"><span class="notif-type">' + escapeHtml(n.type_label) + '</span><br>' +
                    '<small>' + escapeHtml(n.message) + '</small><br>' +
                    '<small style="color:#9ca3af;">' + escapeHtml(n.date) + '</small></div>' +
                    (isNew ? '<span class="notif-dot"></span>' : '') +
                '</div>';
        }).join('');
    }

    function loadNotifications(){
        fetch('get_emp_notifications.php')
            .then(function(r){ return r.json(); })
            .then(function(data){ if(!data.error) renderNotifications(data); })
            .catch(function(err){ console.error('Notification load failed', err); });
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', loadNotifications);
    } else {
        loadNotifications();
    }
})();
</script>
