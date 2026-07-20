<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Shift Management";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Shift Management - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard <?php echo $role==='admin' ? 'admin-theme' : 'super-theme'; ?>">
<?php if($role === 'admin'){ include('sidebar_admin.php'); } else { include('sidebar_sa.php'); } ?>
<div class="main-content">
<?php if($role === 'admin'){ include('topbar_admin.php'); } else { include('topbar_sa.php'); } ?>
<div class="app-content">

<div class="section active">

    <?php if(isset($_GET['msg'])):
        $is_ok = $_GET['msg'] === 'deleted' ? true : ($_GET['msg'] === 'inuse' ? false : true);
    ?>
        <div class="form-card" style="background:<?php echo $_GET['msg']==='inuse' ? '#fef2f2' : '#f0fdf4'; ?>; border:1px solid <?php echo $_GET['msg']==='inuse' ? '#fca5a5' : '#86efac'; ?>; margin-bottom:16px;">
            <?php if($_GET['msg']==='inuse'): ?>
                Can't delete this shift — employees are still assigned to it. Reassign them to a different shift first.
            <?php elseif($_GET['msg']==='deleted'): ?>
                Shift deleted.
            <?php else: ?>
                Saved.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Add Shift</h3>
        <form action="save_shift.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Shift Name</label><input type="text" name="shift_name" placeholder="e.g. Morning Shift, Night Shift" required></div>
                <div class="field"><label>Start Time</label><input type="time" name="start_time" required></div>
                <div class="field"><label>End Time</label><input type="time" name="end_time" required></div>
                <div class="field"><label>Grace Period (minutes)</label><input type="number" name="grace_minutes" value="15" min="0" max="120" required></div>
                <div class="field"><label>Half-Day Cutoff (minutes after start)</label><input type="number" name="half_day_after_minutes" value="180" min="30" max="600" required></div>
            </div>
            <p style="font-size:11.5px;color:var(--text-3,#9aa1ac);margin-top:-6px;">
                Example: a shift starting at 09:00 with a 15-min grace and a 180-min half-day cutoff means:
                check-in by 09:15 → Present, 09:15–12:00 → Late, after 12:00 → Half Day, after the shift's end time → self check-in is closed for the day.
            </p>
            <button type="submit" class="submit-btn">Add Shift</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">All Shifts</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Shift Name</th><th>Timing</th><th>Grace</th><th>Half-Day Cutoff</th><th>Assigned Employees</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $shifts = mysqli_query($conn, "SELECT s.*, COUNT(e.emp_id) as emp_count
                                                FROM shifts s LEFT JOIN employees e ON e.shift_id = s.shift_id
                                                GROUP BY s.shift_id ORDER BY s.start_time ASC");
                if(mysqli_num_rows($shifts) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:var(--text-3,#9aa1ac);'>No shifts added yet.</td></tr>";
                }
                while($s = mysqli_fetch_assoc($shifts)){
                    $start_disp = date('h:i A', strtotime($s['start_time']));
                    $end_disp   = date('h:i A', strtotime($s['end_time']));
                    echo "<tr>
                        <td><b>".htmlspecialchars($s['shift_name'])."</b></td>
                        <td>{$start_disp} &ndash; {$end_disp}</td>
                        <td>{$s['grace_minutes']} min</td>
                        <td>{$s['half_day_after_minutes']} min after start</td>
                        <td><span class='pill blue'>{$s['emp_count']} employee(s)</span></td>
                        <td>
                            <a href='delete_shift.php?id={$s['shift_id']}&csrf=".urlencode(csrf_token())."'
                               onclick=\"return confirm('Delete this shift?');\"
                               style='color:#dc2626;font-size:12px;font-weight:600;text-decoration:none;'>Delete</a>
                        </td>
                    </tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
