<?php
// topbar_sa.php — premium top header bar (Super Admin role)
// Requires: $conn, $page_title
// ems_icon() is defined in sidebar_sa.php, included right before this file.
$sa_id    = $_SESSION['user']['id'];
$sa_photo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$sa_id'"))['profile_photo'] ?? '';
?>
<header class="app-topbar">
    <button type="button" class="icon-btn sidebar-toggle-mobile" id="mobileSidebarBtn" onclick="if(window.emsToggleMobileSidebar) window.emsToggleMobileSidebar();" title="Menu">
        <?php echo ems_icon('menu'); ?>
    </button>

    <div class="topbar-title" id="page-title">
        <span class="eyebrow">Super Admin</span>
        <?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?>
    </div>

    <label class="topbar-search">
        <?php echo ems_icon('search',16); ?>
        <input type="text" placeholder="Search employees, requests…" onkeydown="if(event.key==='Enter'){ window.location.href='global_search.php?q='+encodeURIComponent(this.value); }">
        <kbd>/</kbd>
    </label>

    <div class="topbar-actions">
        <a href="all_employees.php" class="icon-btn" title="All Employees"><?php echo ems_icon('users'); ?></a>
        <a href="leave_calendar.php" class="icon-btn" title="Leave Calendar"><?php echo ems_icon('calendar'); ?></a>

        <button type="button" class="icon-btn" id="pushEnableBtn" onclick="requestPushPermission()" title="Enable desktop notifications" style="display:none;">
            <?php echo ems_icon('bell', 16); ?>
        </button>
        <div class="notif-wrapper" id="notifWrapper">
            <div class="notif-bell icon-btn" onclick="toggleNotif()" title="Notifications">
                <?php echo ems_icon('bell'); ?>
                <span class="notif-badge" id="notifBadge" style="display:none;">0</span>
            </div>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <a href="mark_notifications_read.php" id="notifMarkAllRead" style="display:none;font-size:11px;color:#4F46E5;text-decoration:none;padding:4px 10px;border-radius:20px;border:1px solid #4F46E5;">Mark all read</a>
                </div>
                <div class="notif-list" id="notifList">
                    <div class="notif-empty">Loading…</div>
                </div>
            </div>
        </div>

        <a href="sa_profile.php" class="profile-chip">
            <?php if(!empty($sa_photo) && file_exists('uploads/'.$sa_photo)): ?>
                <img src="uploads/<?php echo htmlspecialchars($sa_photo); ?>" alt="">
            <?php else: ?>
                <span class="avatar-fallback"><?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?></span>
            <?php endif; ?>
            <span class="who">
                <b><?php echo htmlspecialchars($_SESSION['user']['name']); ?></b>
                <span>Super Admin</span>
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

    // Maps a notification's `type` to the page a super admin should land
    // on when they click it. Falls back to the dashboard for any type not
    // explicitly listed (e.g. a future new notification type).
    function notifTypeToUrl(type){
        var map = {
            'leave':        'sa_leaves.php',
            'wfh_status':   'wfh_requests.php',
            'reimbursement_status': 'reimbursements.php',
            'task_completion': 'sa_tasks.php'
        };
        return map[type] || 'super_admin_dashboard.php';
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
            var url = notifTypeToUrl(n.type);
            return '' +
                '<div class="notif-item ' + (isNew ? 'notif-new' : '') + '" onclick=\'window.location.href=' + JSON.stringify(url) + '\' style="cursor:pointer;" title="Click to open">' +
                    '<div class="notif-icon"><?php echo str_replace("'","\\'",ems_icon("inbox",15)); ?></div>' +
                    '<div class="notif-text"><strong>' + escapeHtml(n.emp_name) + '</strong> &mdash; <span class="notif-type">' + escapeHtml(n.leave_type) + '</span><br>' +
                    '<small><?php echo str_replace("'","\\'",ems_icon("calendar",11)); ?> ' + escapeHtml(n.from_date) + ' to ' + escapeHtml(n.to_date) + '</small></div>' +
                    (isNew ? '<span class="notif-dot"></span>' : '') +
                '</div>';
        }).join('');
    }

    window.requestPushPermission = function(){
        if(!('Notification' in window)) return;
        Notification.requestPermission().then(function(perm){
            document.getElementById('pushEnableBtn').style.display = (perm === 'default') ? 'inline-flex' : 'none';
        });
    };
    if('Notification' in window && Notification.permission === 'default'){
        document.getElementById('pushEnableBtn').style.display = 'inline-flex';
    }

    var lastUnreadCount = null;
    function maybePushNotify(data){
        if(!('Notification' in window) || Notification.permission !== 'granted') return;
        if(lastUnreadCount === null){ lastUnreadCount = data.unread_count || 0; return; }
        var newCount = (data.unread_count || 0) - lastUnreadCount;
        if(newCount > 0 && data.items && data.items.length){
            data.items.slice(0, newCount).forEach(function(n){
                if(n.is_read == 0){
                    var notif = new Notification('New Leave Request', {
                        body: (n.emp_name || '') + ' — ' + (n.leave_type || '') + ' (' + (n.from_date||'') + ' to ' + (n.to_date||'') + ')',
                        icon: 'https://cdn-icons-png.flaticon.com/512/3439/3439997.png',
                        tag: 'ems-sa-' + (n.emp_name||'') + '-' + (n.from_date||'')
                    });
                    notif.onclick = function(){ window.focus(); notif.close(); };
                }
            });
        }
        lastUnreadCount = data.unread_count || 0;
    }

    function loadNotifications(){
        fetch('get_sa_notifications.php')
            .then(function(r){ return r.json(); })
            .then(function(data){ if(!data.error){ renderNotifications(data); maybePushNotify(data); } })
            .catch(function(err){ console.error('Notification load failed', err); });
    }

    if(document.readyState === 'loading'){
        document.addEventListener('DOMContentLoaded', loadNotifications);
    } else {
        loadNotifications();
    }
    setInterval(loadNotifications, 25000);
})();
</script>
<?php include 'chat_widget.php'; ?>
