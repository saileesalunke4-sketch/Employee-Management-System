<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Activity Log";

// Optional filter by action type (approved / rejected / all)
$filter = $_GET['filter'] ?? 'all';
$where  = '';
if(in_array($filter, ['approved','rejected'], true)){
    $where = "WHERE action='".mysqli_real_escape_string($conn,$filter)."'";
}

$logs = [];
$res = mysqli_query($conn, "SELECT * FROM activity_log $where ORDER BY created_at DESC LIMIT 200");
while($row = mysqli_fetch_assoc($res)){ $logs[] = $row; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Activity Log - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.log-table{ width:100%; border-collapse:collapse; }
.log-table th{ text-align:left; font-size:12px; color:var(--text-3,#9aa1ac); font-weight:600; padding:10px 12px; border-bottom:1px solid var(--border,#e5e7eb); }
.log-table td{ padding:10px 12px; font-size:13px; border-bottom:1px solid var(--border-soft,#eef0f3); vertical-align:top; }
.log-action-pill{ display:inline-block; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.log-action-pill.approved{ background:#dcfce7; color:#16a34a; }
.log-action-pill.rejected{ background:#fee2e2; color:#dc2626; }
.log-target-type{ font-size:11px; color:var(--text-3,#9aa1ac); }
.log-filter-tabs{ display:flex; gap:8px; margin-bottom:16px; }
.log-filter-tabs a{ padding:6px 14px; border-radius:20px; font-size:12px; font-weight:600; text-decoration:none; color:var(--text-2,#666d7a); background:var(--surface-soft,#f3f4f7); }
.log-filter-tabs a.active{ background:var(--role-accent,#4F46E5); color:#fff; }
</style>
</head>
<body>
<div class="dashboard <?php echo $role==='admin' ? 'admin-theme' : 'super-theme'; ?>">
<?php if($role === 'admin'){ include('sidebar_admin.php'); } else { include('sidebar_sa.php'); } ?>
<div class="main-content">
<?php if($role === 'admin'){ include('topbar_admin.php'); } else { include('topbar_sa.php'); } ?>
<div class="app-content">

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Activity Log</h3>
        <p style="color:var(--text-3,#9aa1ac); font-size:13px; margin-top:-8px;">
            Who approved, rejected, or changed what — most recent first. Shows the last 200 actions.
        </p>

        <div class="log-filter-tabs">
            <a href="admin_activity_log.php" class="<?php echo $filter==='all'?'active':''; ?>">All</a>
            <a href="admin_activity_log.php?filter=approved" class="<?php echo $filter==='approved'?'active':''; ?>">Approved</a>
            <a href="admin_activity_log.php?filter=rejected" class="<?php echo $filter==='rejected'?'active':''; ?>">Rejected</a>
        </div>

        <?php if(empty($logs)): ?>
            <p style="color:var(--text-3,#9aa1ac);">No activity recorded yet.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Action</th>
                        <th>Type</th>
                        <th>Target</th>
                        <th>Details</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach($logs as $log): ?>
                    <tr>
                        <td><?php echo date('d M Y, h:i A', strtotime($log['created_at'])); ?></td>
                        <td><span class="log-action-pill <?php echo htmlspecialchars($log['action']); ?>"><?php echo htmlspecialchars(ucfirst($log['action'])); ?></span></td>
                        <td><span class="log-target-type"><?php echo htmlspecialchars($log['target_type']); ?></span></td>
                        <td><?php echo htmlspecialchars($log['target_name']); ?></td>
                        <td style="color:var(--text-2,#666d7a);"><?php echo htmlspecialchars($log['details']); ?></td>
                        <td><?php echo htmlspecialchars($log['actor_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
