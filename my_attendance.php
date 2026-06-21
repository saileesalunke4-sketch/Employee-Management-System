<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';
$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];
$page_title = "My Attendance";

// Check today's attendance status
$today = date('Y-m-d');
$today_att = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$today'"));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Attendance - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.completed{background:#dcfce7;color:#16a34a;}
.status-pill.in_progress{background:#fef3c7;color:#d97706;}
.checkin-box{background:linear-gradient(135deg,#1a3a6e,#3b82f6);border-radius:14px;padding:28px;text-align:center;color:white;margin-bottom:20px;}
.time-display{font-size:32px;font-weight:800;margin:6px 0;}
.checkin-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px;}
.checkin-card{background:rgba(255,255,255,0.12);border-radius:10px;padding:16px;}
.action-btn{display:inline-block;padding:12px 28px;border-radius:10px;font-size:14px;font-weight:700;border:none;cursor:pointer;margin-top:10px;}
.btn-checkin{background:#16a34a;color:white;}
.btn-checkout{background:#dc2626;color:white;}
.btn-disabled{background:#9ca3af;color:white;cursor:not-allowed;}
.live-clock{font-size:14px;opacity:0.85;margin-bottom:8px;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <!-- Check In / Check Out Box -->
    <div class="checkin-box">
        <div class="live-clock" id="liveClock"></div>
        <h3 style="margin:0;font-size:16px;">📍 Today's Attendance — <?php echo date('d M Y'); ?></h3>

        <div class="checkin-grid">
            <div class="checkin-card">
                <p style="font-size:12px;opacity:0.8;margin:0;">CHECK IN TIME</p>
                <p class="time-display"><?php echo ($today_att && $today_att['check_in']) ? date('h:i A', strtotime($today_att['check_in'])) : '--:--'; ?></p>                <?php if(!$today_att): ?>
                    <form action="save_attendance.php" method="POST">
                        <input type="hidden" name="action" value="check_in">
                        <select name="status" style="padding:6px 10px;border-radius:6px;border:none;margin-bottom:8px;color:#1a1a2e;">
                            <option value="present">🏢 Office</option>
                            <option value="work_from_home">🏠 Work From Home</option>
                        </select><br>
                        <button type="submit" class="action-btn btn-checkin">✅ Check In Now</button>
                    </form>
                <?php else: ?>
                    <button class="action-btn btn-disabled" disabled>✅ Already Checked In</button>
                <?php endif; ?>
            </div>

            <div class="checkin-card">
                <p style="font-size:12px;opacity:0.8;margin:0;">CHECK OUT TIME</p>
                <p class="time-display"><?php echo ($today_att && $today_att['check_out']) ? date('h:i A', strtotime($today_att['check_out'])) : '--:--'; ?></p>                <?php if($today_att && empty($today_att['check_out'])): ?>
                    <form action="save_attendance.php" method="POST">
                        <input type="hidden" name="action" value="check_out">
                        <button type="submit" class="action-btn btn-checkout">🚪 Check Out Now</button>
                    </form>
                <?php elseif($today_att && !empty($today_att['check_out'])): ?>
                    <button class="action-btn btn-disabled" disabled>🚪 Already Checked Out</button>
                <?php else: ?>
                    <button class="action-btn btn-disabled" disabled>Check In First</button>
                <?php endif; ?>
            </div>
        </div>

        <p style="font-size:11px;opacity:0.7;margin-top:14px;">
            ⏱️ Time is automatically captured by the system using server time. Manual time entry is not allowed for accuracy.<br>
            ⚠️ Check-in after <strong>9:15 AM</strong> will be automatically marked as <strong>Late</strong>.
        </p>
    </div>

    <!-- Attendance History -->
    <div class="form-card">
        <h3 class="section-title">All My Attendance Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Hours</th><th>Overtime</th><th>Sunday</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' ORDER BY date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $hrs=0;
                    if($row['check_in']&&$row['check_out']){ $in=strtotime($row['check_in']); $out=strtotime($row['check_out']); if($out>$in) $hrs=($out-$in)/3600; }
                    $st_map=['present'=>'approved','late'=>'pending','half_day'=>'pending','work_from_home'=>'approved','absent'=>'rejected'];
                    $pill=$st_map[$row['status']]??'pending';
                    echo "<tr><td>{$row['date']}</td><td>".($row['check_in']?:'-')."</td><td>".($row['check_out']?:'-')."</td>
                    <td><span class='status-pill $pill'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                    <td>".($hrs>0?number_format($hrs,1)." hrs":"-")."</td>
                    <td>".($row['overtime_hours']>0?"<span style='color:#d97706;font-weight:600;'>".$row['overtime_hours']." hrs</span>":"-")."</td>
                    <td>".($row['is_sunday']?"<span style='color:#db2777;font-weight:600;'>✓ Sunday</span>":"-")."</td></tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

</div>

</div>
</div>

<script>
function updateClock(){
    const now = new Date();
    document.getElementById('liveClock').innerText = '🕐 ' + now.toLocaleTimeString();
}
setInterval(updateClock, 1000);
updateClock();
</script>

<?php include 'common_js.php'; ?>
</body>
</html>
