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
$page_title = "My Leaves";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>My Leaves - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.completed{background:#dcfce7;color:#16a34a;}
.status-pill.in_progress{background:#fef3c7;color:#d97706;}
.skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

<?php
// Sabbatical: only 1 per career
$sab_used = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND leave_type='Sabbatical'"))['t'];
$sab_eligible = ($sab_used == 0);
?>

    <div class="form-card">
        <h3 class="section-title">Apply for Leave</h3>
        <form action="save_leave.php" method="POST" onsubmit="return confirmLeave()">
            <div class="form-grid">
                <div class="field"><label>Leave Type</label>
                    <select name="leave_type" id="leave_type_sel" required onchange="checkSabbatical(this.value)">
                        <option value="">-- Select --</option>
                        <?php
                            $lt=mysqli_query($conn,"SELECT * FROM leave_types");
                            while($l=mysqli_fetch_assoc($lt)){
                                if($l['leave_type_name']=='Sabbatical') continue; // handled separately below
                                echo "<option value='{$l['leave_type_name']}'>{$l['leave_type_name']}</option>";
                            }
                            if($sab_eligible){
                                echo "<option value='Sabbatical'>Sabbatical (Long Term Leave)</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="field"><label>From Date</label><input type="date" name="from_date" id="from_date" required onchange="calculateDays()"></div>
                <div class="field"><label>To Date</label><input type="date" name="to_date" id="to_date" required onchange="calculateDays()"></div>
                <div class="field" style="grid-column:1/-1"><label>Reason</label><textarea name="reason" rows="3" placeholder="Enter reason..." required></textarea></div>
            </div>

            <!-- Sabbatical Info Box -->
            <div id="sab_info" style="display:none;background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;margin:14px 0;font-size:13px;color:#1d4ed8;line-height:1.9;">
                <strong>📋 Sabbatical Leave Policy:</strong><br>
                • Minimum: <strong>30 days</strong> &nbsp;|&nbsp; Maximum: <strong>90 days</strong><br>
                • Type: <strong>Unpaid Leave</strong><br>
                • Allowed <strong>only once</strong> in entire career<br>
                • Requires <strong>30 days advance notice</strong> before start date
            </div>

            <!-- Day Calculator & Sandwich Warning -->
            <div id="day_info" style="display:none;margin:14px 0;padding:14px 18px;border-radius:10px;font-size:13px;line-height:1.9;"></div>

            <button type="submit" class="submit-btn">Apply Leave</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My Leave Records</h3>
        <div style="text-align:right;margin-bottom:12px;">
            <a href="export_my_leaves.php" style="display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">📥 Download Excel</a>
        </div>
        <table class="emp-table">
            <thead><tr><th>Leave Type</th><th>From</th><th>To</th><th>Days Deducted</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $days=(strtotime($row['to_date'])-strtotime($row['from_date']))/86400+1;
                    echo "<tr>
                        <td>{$row['leave_type']}</td>
                        <td>{$row['from_date']}</td>
                        <td>{$row['to_date']}</td>
                        <td><strong>{$days}</strong></td>
                        <td>{$row['reason']}</td>
                        <td><span class='status-pill {$row['status']}'>".ucfirst($row['status'])."</span></td>
                    </tr>";
                }
            ?>
            </tbody>
        </table>
    </div>

</div>
</div>
</div>

<script>
function calculateDays() {
    const fromVal   = document.getElementById('from_date').value;
    const toVal     = document.getElementById('to_date').value;
    const leaveType = document.getElementById('leave_type_sel').value;
    const infoBox   = document.getElementById('day_info');
    if (!fromVal || !toVal) { infoBox.style.display='none'; return; }

    const from    = new Date(fromVal);
    const to      = new Date(toVal);
    if (to < from) {
        infoBox.style.cssText='display:block;background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;margin:14px 0;padding:14px 18px;border-radius:10px;font-size:13px;';
        infoBox.innerHTML = '❌ To Date cannot be before From Date.'; return;
    }

    const fromDay  = from.getDay(); // 0=Sun,1=Mon,5=Fri,6=Sat
    const toDay    = to.getDay();
    const calDays  = Math.round((to - from) / 86400000) + 1;
    let sandwichDays = 0;
    let sandwichMsg  = '';

    if (fromDay === 5 && toDay === 1) {
        // Friday to Monday — entire weekend in between
        sandwichDays = 0; // already included in calDays
        sandwichMsg = '⚠️ Leave covers <strong>Friday to Monday</strong> — weekend days are included in deduction (Sandwich Policy)<br>';
    } else if (fromDay === 5) {
        sandwichDays = 2;
        sandwichMsg = '⚠️ Leave starts on <strong>Friday</strong> — Saturday & Sunday also counted (Sandwich Policy)<br>';
    } else if (toDay === 1) {
        sandwichDays = 1;
        sandwichMsg = '⚠️ Leave ends on <strong>Monday</strong> — Sunday also counted (Sandwich Policy)<br>';
    }

    const totalDays = calDays + sandwichDays;
    let html = `📅 <strong>Selected:</strong> ${calDays} day(s)`;

    if (sandwichDays > 0 || (fromDay===5 && toDay===1)) {
        html += `<br>${sandwichMsg}🔴 <strong>Total days that will be deducted: ${totalDays} day(s)</strong>`;
        infoBox.style.cssText='display:block;background:#fef3c7;border:1px solid #fcd34d;color:#92400e;margin:14px 0;padding:14px 18px;border-radius:10px;font-size:13px;line-height:1.9;';
    } else {
        html += ` &nbsp;|&nbsp; ✅ <strong>Total days: ${totalDays}</strong> (No sandwich applies)`;
        infoBox.style.cssText='display:block;background:#f0fdf4;border:1px solid #86efac;color:#166534;margin:14px 0;padding:14px 18px;border-radius:10px;font-size:13px;line-height:1.9;';
    }

    // Sabbatical validation message
    if (leaveType === 'Sabbatical') {
        if (totalDays < 30) {
            html += `<br>❌ Sabbatical minimum is <strong>30 days</strong>. Please extend dates.`;
            infoBox.style.cssText='display:block;background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;margin:14px 0;padding:14px 18px;border-radius:10px;font-size:13px;line-height:1.9;';
        } else if (totalDays > 90) {
            html += `<br>❌ Sabbatical maximum is <strong>90 days</strong>. Please shorten dates.`;
            infoBox.style.cssText='display:block;background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;margin:14px 0;padding:14px 18px;border-radius:10px;font-size:13px;line-height:1.9;';
        }
    }

    infoBox.innerHTML = html;
}

function checkSabbatical(val) {
    document.getElementById('sab_info').style.display = (val === 'Sabbatical') ? 'block' : 'none';
    calculateDays();
}

function confirmLeave() {
    const fromVal   = document.getElementById('from_date').value;
    const toVal     = document.getElementById('to_date').value;
    const leaveType = document.getElementById('leave_type_sel').value;
    if (!fromVal || !toVal || !leaveType) return true;

    const from    = new Date(fromVal);
    const to      = new Date(toVal);
    const fromDay = from.getDay();
    const toDay   = to.getDay();
    const calDays = Math.round((to - from) / 86400000) + 1;
    let sandwichDays = 0;
    if (fromDay===5 && toDay!==1) sandwichDays=2;
    else if (toDay===1 && fromDay!==5) sandwichDays=1;
    const totalDays = calDays + sandwichDays;

    if (leaveType === 'Sabbatical') {
        if (totalDays < 30) { alert('Sabbatical leave minimum is 30 days. Please extend your dates.'); return false; }
        if (totalDays > 90) { alert('Sabbatical leave maximum is 90 days. Please shorten your dates.'); return false; }
        const today = new Date();
        const noticeDays = Math.round((from - today) / 86400000);
        if (noticeDays < 30) { alert('Sabbatical requires 30 days advance notice.\nPlease select a start date at least 30 days from today.'); return false; }
    }

    if (sandwichDays > 0) {
        return confirm('⚠️ Sandwich Leave Policy Alert!\n\nYour leave falls on a weekend boundary.\nTotal days that will be deducted: ' + totalDays + ' days.\n\nDo you want to proceed?');
    }
    return true;
}
</script>

<?php include 'common_js.php'; ?>
</body>
</html>
