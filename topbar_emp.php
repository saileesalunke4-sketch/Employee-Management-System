<?php
// topbar_emp.php
$user_id   = $_SESSION['user']['id'];
$emp_photo = mysqli_fetch_assoc(mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$user_id'"))['profile_photo'] ?? '';
$emp_unread = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as cnt FROM notifications WHERE emp_id='$emp_id' AND is_read=0"))['cnt'];
$emp_notifs = mysqli_query($conn,"SELECT * FROM notifications WHERE emp_id='$emp_id' ORDER BY created_at DESC LIMIT 10");
?>
<div class="topbar">
    <h2><?php echo $page_title ?? 'Dashboard'; ?></h2>
    <div class="topbar-right">
        <div class="notif-wrapper" id="notifWrapper">
            <div class="notif-bell" onclick="toggleNotif()">&#128276;
                <?php if($emp_unread>0): ?><span class="notif-badge"><?php echo $emp_unread; ?></span><?php endif; ?>
            </div>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>My Notifications</span>
                    <?php if($emp_unread>0): ?>
                        <a href="mark_emp_notifications_read.php" style="font-size:11px;color:#3b82f6;text-decoration:none;padding:4px 10px;border-radius:20px;border:1px solid #3b82f6;">Mark all read</a>
                    <?php endif; ?>
                </div>
                <div class="notif-list">
                <?php
                    $has_n = false;
                    while($n = mysqli_fetch_assoc($emp_notifs)){
                        $has_n = true;
                        $nw    = ($n['is_read']==0) ? 'notif-new' : '';
                        $icon  = ($n['type']=='task') ? '📋' : '🌿';
                        $type_label = ($n['type']=='task') ? 'Task Assigned' : 'Leave '.$n['leave_type'];
                        echo "<div class='notif-item {$nw}'>
                            <div class='notif-icon'>{$icon}</div>
                            <div class='notif-text'>
                                <span class='notif-type'>{$type_label}</span><br>
                                <small>".($n['message'] ?: $n['reason'])."</small><br>
                                <small style='color:#9ca3af;'>".date('d M Y', strtotime($n['created_at']))."</small>
                            </div>
                            ".($n['is_read']==0?"<span class='notif-dot'></span>":"")."
                        </div>";
                    }
                    if(!$has_n) echo "<div class='notif-empty'>No notifications yet</div>";
                ?>
                </div>
            </div>
        </div>
        <?php if(!empty($emp_photo)&&file_exists('uploads/'.$emp_photo)): ?>
            <a href="emp_profile.php"><img src="uploads/<?php echo htmlspecialchars($emp_photo);?>" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #3b82f6;"></a>
        <?php else: ?>
            <a href="emp_profile.php" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:15px;text-decoration:none;">
                <?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?>
            </a>
        <?php endif; ?>
        <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
    </div>
</div>
