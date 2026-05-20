<?php
// topbar_admin.php — include this in every admin page
// Requires: $conn, $page_title
$admin_id = $_SESSION['user']['id'];
$photo_res = mysqli_query($conn,"SELECT profile_photo FROM users WHERE id='$admin_id'");
$photo_row = mysqli_fetch_assoc($photo_res);
$profile_photo = $photo_row['profile_photo'] ?? '';

$unread_res = mysqli_query($conn,"SELECT COUNT(*) as cnt FROM notifications WHERE is_read=0");
$unread_count = mysqli_fetch_assoc($unread_res)['cnt'];
$all_notif = mysqli_query($conn,"SELECT * FROM notifications ORDER BY created_at DESC LIMIT 15");
?>
<div class="topbar">
    <h2><?php echo $page_title ?? 'Dashboard'; ?></h2>
    <div class="topbar-right">
        <div class="notif-wrapper" id="notifWrapper">
            <div class="notif-bell" onclick="toggleNotif()">&#128276;
                <?php if($unread_count>0): ?><span class="notif-badge"><?php echo $unread_count; ?></span><?php endif; ?>
            </div>
            <div class="notif-dropdown" id="notifDropdown">
                <div class="notif-header">
                    <span>Notifications</span>
                    <?php if($unread_count>0): ?><a href="mark_notifications_read.php" style="font-size:11px;color:#3b82f6;text-decoration:none;padding:4px 10px;border-radius:20px;border:1px solid #3b82f6;">Mark all read</a><?php endif; ?>
                </div>
                <div class="notif-list">
                <?php
                    $has_n = false;
                    while($n = mysqli_fetch_assoc($all_notif)){
                        $has_n = true;
                        $nw = ($n['is_read']==0) ? 'notif-new' : '';
                        echo "<div class='notif-item {$nw}'>
                            <div class='notif-icon'>&#128203;</div>
                            <div class='notif-text'><strong>{$n['emp_name']}</strong> &mdash; <span class='notif-type'>{$n['leave_type']}</span><br>
                            <small>&#128197; {$n['from_date']} to {$n['to_date']}</small><br>
                            <small style='color:#9ca3af;font-style:italic;'>Reason: {$n['reason']}</small></div>
                            ".($n['is_read']==0?"<span class='notif-dot'></span>":"")."
                        </div>";
                    }
                    if(!$has_n) echo "<div class='notif-empty'>No notifications yet</div>";
                ?>
                </div>
            </div>
        </div>
        <?php if(!empty($profile_photo) && file_exists('uploads/'.$profile_photo)): ?>
            <a href="admin_profile.php"><img src="uploads/<?php echo htmlspecialchars($profile_photo); ?>"
                 style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #3b82f6;cursor:pointer;"></a>
        <?php else: ?>
            <a href="admin_profile.php" style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#6366f1);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:15px;cursor:pointer;text-decoration:none;">
                <?php echo strtoupper(substr($_SESSION['user']['name'],0,1)); ?>
            </a>
        <?php endif; ?>
        <div class="user-info">Welcome, <?php echo $_SESSION['user']['name']; ?></div>
    </div>
</div>
