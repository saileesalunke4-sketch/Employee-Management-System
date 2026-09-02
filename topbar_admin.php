<?php
// topbar_admin.php — premium top header bar (Admin role)
// Requires: $conn, $page_title
// Note: ems_icon() is defined in sidebar_admin.php, which is always
// included before this file on every admin page.
//
// The notification bell/count/list used to run 2 blocking DB queries on
// every single page load (on top of every other query that page needed),
// which is a big part of why the page felt "blank" for a second before
// painting. They're now fetched asynchronously via get_notifications.php
// right after the page paints, so the shell (sidebar/topbar/content)
// shows up immediately and the bell/dropdown fill in a beat later.
$admin_id = $_SESSION['user']['id'];
$photo_res = mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$admin_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';
?>
<header class="app-topbar">
    <button type="button" class="icon-btn sidebar-toggle-mobile" id="mobileSidebarBtn" onclick="if(window.emsToggleMobileSidebar) window.emsToggleMobileSidebar();" title="Menu">
        <?php echo ems_icon('menu'); ?>
    </button>

    <div class="topbar-title">
        <span class="eyebrow">Admin </span>
        <?php echo htmlspecialchars($page_title ?? 'Dashboard'); ?>
    </div>

    <label class="topbar-search">
        <?php echo ems_icon('search',16); ?>
        <input type="text" placeholder="Search employees, tasks, requests…" onkeydown="if(event.key==='Enter'){ window.location.href='global_search.php?q='+encodeURIComponent(this.value); }">
        <kbd>/</kbd>
    </label>

    <div class="topbar-actions">
        <a href="add_employee.php" class="icon-btn" title="Add Employee"><?php echo ems_icon('plus-circle'); ?></a>
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

        <a href="admin_profile.php" class="profile-chip">
            <?php if(!empty($profile_photo) && file_exists('uploads/'.$profile_photo)): ?>
                <img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>" alt="">
            <?php else: ?>
                <span class="avatar-fallback"><?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?></span>
            <?php endif; ?>
            <span class="who">
                <b><?php echo htmlspecialchars($_SESSION['user']['name']); ?></b>
                <span>Admin</span>
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

    // Maps a notification's `type` to the page an admin should land on
    // when they click it. Falls back to the dashboard for any type not
    // explicitly listed (e.g. a future new notification type).
    function notifTypeToUrl(type){
        var map = {
            'leave':        'admin_leaves.php',
            'wfh_status':   'wfh_requests.php',
            'reimbursement_status': 'reimbursements.php',
            'task_completion': 'admin_tasks.php'
        };
        return map[type] || 'admin_dashboard.php';
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
                    '<small><?php echo str_replace("'","\\'",ems_icon("calendar",11)); ?> ' + escapeHtml(n.from_date) + ' to ' + escapeHtml(n.to_date) + '</small><br>' +
                    '<small style="color:#9ca3af;font-style:italic;">Reason: ' + escapeHtml(n.reason) + '</small></div>' +
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
                        tag: 'ems-admin-' + (n.emp_name||'') + '-' + (n.from_date||'')
                    });
                    notif.onclick = function(){ window.focus(); notif.close(); };
                }
            });
        }
        lastUnreadCount = data.unread_count || 0;
    }

    function loadNotifications(){
        fetch('get_notifications.php')
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
