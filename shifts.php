<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Shift Management";

// BUGFIX (EMS-ADM-012): the success/error banner used to be driven by a
// ?msg= URL parameter, so refreshing the page (or revisiting the URL) kept
// showing "Shift deleted" long after it actually happened. A session flash
// message shows exactly once, right after the action, then clears itself.
$flash = $_SESSION['shift_flash'] ?? null;
unset($_SESSION['shift_flash']);
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

    <?php if($flash): ?>
        <div class="form-card" style="background:<?php echo $flash['ok'] ? '#f0fdf4' : '#fef2f2'; ?>;border:1px solid <?php echo $flash['ok'] ? '#86efac' : '#fca5a5'; ?>;margin-bottom:16px;">
            <?php echo htmlspecialchars($flash['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Add Shift</h3>
        <form action="save_shift.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Shift Name</label><input type="text" name="shift_name" placeholder="e.g. Morning Shift, Night Shift" required></div>
                <div class="field">
                    <label>Start Time</label>
                    <input type="time" name="start_time" id="shiftStartTime" required oninput="updateAmPmPreview('shiftStartTime','startTimePreview')">
                    <div id="startTimePreview" style="font-size:11.5px;color:#0F766E;font-weight:600;margin-top:4px;">&nbsp;</div>
                </div>
                <div class="field">
                    <label>End Time</label>
                    <input type="time" name="end_time" id="shiftEndTime" required oninput="updateAmPmPreview('shiftEndTime','endTimePreview')">
                    <div id="endTimePreview" style="font-size:11.5px;color:#0F766E;font-weight:600;margin-top:4px;">&nbsp;</div>
                </div>
                <div class="field"><label>Grace Period (minutes)</label><input type="number" name="grace_minutes" value="15" min="0" max="120" required></div>
                <div class="field"><label>Half-Day Cutoff (minutes after start)</label><input type="number" name="half_day_after_minutes" value="180" min="30" max="600" required></div>
            </div>
            <p style="font-size:11.5px;color:var(--text-3,#9aa1ac);margin-top:-6px;">
                Example: a shift starting at 09:00 with a 15-min grace and a 180-min half-day cutoff means:
                check-in by 09:15 → Present, 09:15–12:00 → Late, after 12:00 → Half Day, after the shift's end time → self check-in is closed for the day.
            </p>
            <script>
            // BUGFIX (EMS-011): the native time input's AM/PM display depends
            // entirely on the browser/OS locale — many locales (including
            // most non-US ones) show it in 24-hour format with no AM/PM at
            // all, making it easy to enter a shift 12 hours off from what
            // was intended. This shows an explicit, always-visible 12-hour
            // readout next to each field so there's never ambiguity.
            function updateAmPmPreview(inputId, previewId){
                var val = document.getElementById(inputId).value; // "HH:MM" 24-hour
                var preview = document.getElementById(previewId);
                if(!val){ preview.innerHTML = '&nbsp;'; return; }
                var parts = val.split(':');
                var h = parseInt(parts[0], 10);
                var m = parts[1];
                var ampm = h >= 12 ? 'PM' : 'AM';
                var h12 = h % 12;
                if(h12 === 0) h12 = 12;
                preview.textContent = h12 + ':' + m + ' ' + ampm;
            }
            </script>
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
