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
        // Check today's attendance status for this employee (server-side truth)
        $today = date('Y-m-d');
        $today_res = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$today'");
        $today_att = mysqli_fetch_assoc($today_res);
    ?>
    <div class="form-card">
        <h3 class="section-title">Today's Attendance — <?php echo date('d M Y'); ?></h3>

        <?php if(!$today_att): ?>
            <!-- Not checked in yet -->
            <form action="save_attendance.php" method="POST" id="checkinForm">
                <div class="field" style="margin-bottom:14px;">
                    <label style="display:block;margin-bottom:6px;font-weight:600;">Work Mode</label>
                    <label style="margin-right:18px;font-weight:400;"><input type="radio" name="work_mode" id="wfoRadio" value="WFO" checked style="width:auto;margin-right:6px;">Work From Office (WFO)</label>
                    <label style="font-weight:400;"><input type="radio" name="work_mode" id="wfhRadio" value="WFH" style="width:auto;margin-right:6px;">Work From Home (WFH)</label>
                </div>
                <input type="hidden" name="lat" id="checkinLat">
                <input type="hidden" name="lng" id="checkinLng">
                <div id="locStatusIn" style="font-size:12px;color:#d97706;margin-bottom:10px;">📍 Detecting your location...</div>
                <button type="submit" class="submit-btn" id="checkinBtn" disabled>Check In Now</button>
            </form>
            <p style="font-size:12px;color:#888;margin-top:10px;">Time is captured automatically from the server when you check in. Work From Office check-in needs to happen from within office premises; Work From Home skips the location check but requires an <a href="my_wfh.php" style="color:#1d4ed8;font-weight:600;">approved WFH request</a> for today's date.</p>

        <?php elseif($today_att && !$today_att['check_out']): ?>
            <!-- Checked in, not checked out -->
            <p style="font-size:14px;color:#1d4ed8;margin-bottom:14px;">
                Checked in at <b><?php echo $today_att['check_in']; ?></b> —
                status: <b><?php echo ucfirst(str_replace('_',' ',$today_att['status'])); ?></b>
            </p>
            <form action="save_checkout.php" method="POST" id="checkoutForm">
                <input type="hidden" name="lat" id="checkoutLat">
                <input type="hidden" name="lng" id="checkoutLng">
                <?php if($today_att['status'] !== 'work_from_home'): ?>
                <div id="locStatusOut" style="font-size:12px;color:#d97706;margin-bottom:10px;">📍 Detecting your location...</div>
                <?php endif; ?>
                <button type="submit" class="submit-btn" id="checkoutBtn" <?php echo ($today_att['status'] !== 'work_from_home') ? 'disabled' : ''; ?>>Check Out Now</button>
            </form>

        <?php else: ?>
            <!-- Already completed for today -->
            <p style="font-size:14px;color:#16a34a;">
                Attendance completed for today — Check In: <b><?php echo $today_att['check_in']; ?></b>,
                Check Out: <b><?php echo $today_att['check_out']; ?></b>
            </p>
        <?php endif; ?>
    </div>

    <script>
    // Office coordinates, exposed to JS just for a live "in office or not" preview.
    // Real validation always happens again on the server (this is client-side UX only).
    var OFFICE_LAT    = <?php echo OFFICE_LAT; ?>;
    var OFFICE_LNG    = <?php echo OFFICE_LNG; ?>;
    var OFFICE_RADIUS = <?php echo OFFICE_RADIUS_METERS; ?>;

    function distanceMeters(lat1, lon1, lat2, lon2){
        var R = 6371000;
        var dLat = (lat2-lat1) * Math.PI/180;
        var dLon = (lon2-lon1) * Math.PI/180;
        var a = Math.sin(dLat/2)*Math.sin(dLat/2) +
                Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180) *
                Math.sin(dLon/2)*Math.sin(dLon/2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    }

    // Capture browser geolocation, populate hidden fields, and show whether
    // the detected location falls inside or outside the office radius.
    function captureLocation(latFieldId, lngFieldId, statusId, btnId){
        var statusEl = document.getElementById(statusId);
        var btnEl = document.getElementById(btnId);
        if(!navigator.geolocation){
            if(statusEl){ statusEl.innerHTML = '❌ Geolocation not supported by your browser. You can still try to check in/out — enable Work From Home if you\'re off-site.'; statusEl.style.color = '#dc2626'; }
            // BUGFIX (EMS-EMP-015): don't leave the button stuck disabled
            // forever just because geolocation isn't available — the server
            // re-validates location on submit either way.
            if(btnEl) btnEl.disabled = false;
            return;
        }
        navigator.geolocation.getCurrentPosition(function(pos){
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            document.getElementById(latFieldId).value = lat;
            document.getElementById(lngFieldId).value = lng;

            var dist = distanceMeters(lat, lng, OFFICE_LAT, OFFICE_LNG);
            if(statusEl){
                if(dist <= OFFICE_RADIUS){
                    statusEl.innerHTML = '✅ You are at office premises (' + Math.round(dist) + ' m from office).';
                    statusEl.style.color = '#16a34a';
                } else {
                    var distKm = (dist/1000).toFixed(2);
                    statusEl.innerHTML = '⚠️ You are ' + distKm + ' km away from office — check-in will be rejected unless you tick Work From Home.';
                    statusEl.style.color = '#d97706';
                }
            }
            if(btnEl) btnEl.disabled = false; // server still re-checks distance on submit
        }, function(err){
            // BUGFIX (EMS-EMP-015): this used to only update the status
            // text and leave the button disabled with no way forward at
            // all — if someone denied/lost location permission, they could
            // never check in or out again. The server already re-validates
            // distance on submit (and Work From Home bypasses it entirely),
            // so it's safe to let them try instead of hard-blocking here.
            if(statusEl){ statusEl.innerHTML = '❌ Location access denied or unavailable. You can still submit — tick Work From Home if you\'re off-site, or contact Admin if this keeps happening.'; statusEl.style.color = '#dc2626'; }
            if(btnEl) btnEl.disabled = false;
        }, { enableHighAccuracy: true, timeout: 10000 });
    }

    var wfhRadio    = document.getElementById('wfhRadio');
    var wfoRadio    = document.getElementById('wfoRadio');
    var checkinBtn  = document.getElementById('checkinBtn');
    var locStatusIn = document.getElementById('locStatusIn');

    function onWorkModeChange(){
        if(wfhRadio && wfhRadio.checked){
            // WFH selected: location not required
            if(locStatusIn){ locStatusIn.innerHTML = '🏠 Work From Home — location check skipped.'; locStatusIn.style.color = '#1d4ed8'; }
            if(checkinBtn) checkinBtn.disabled = false;
        } else {
            captureLocation('checkinLat','checkinLng','locStatusIn','checkinBtn');
        }
    }

    if(wfhRadio && wfoRadio){
        wfhRadio.addEventListener('change', onWorkModeChange);
        wfoRadio.addEventListener('change', onWorkModeChange);
        // Try capturing location right away for the default (WFO) case
        captureLocation('checkinLat','checkinLng','locStatusIn','checkinBtn');
    }

    if(document.getElementById('checkoutForm') && document.getElementById('locStatusOut')){
        captureLocation('checkoutLat','checkoutLng','locStatusOut','checkoutBtn');
    }
    </script>
    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">Request Attendance Regularization</h3>
        <p style="font-size:12px;color:#888;margin-top:-6px;margin-bottom:14px;">Forgot to check in/out on a past day, or attendance marked incorrectly? Request a correction here — your Admin will review and approve/reject it.</p>
        <form action="save_regularization_request.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Date</label>
                    <input type="date" name="att_date" max="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="field"><label>Requested Check In</label><input type="time" name="requested_check_in"></div>
                <div class="field"><label>Requested Check Out</label><input type="time" name="requested_check_out"></div>
                <div class="field"><label>Requested Status</label>
                    <select name="requested_status">
                        <option value="present">Present</option>
                        <option value="late">Late</option>
                        <option value="half_day">Half Day</option>
                        <option value="work_from_home">Work From Home</option>
                    </select>
                </div>
                <div class="field" style="grid-column:1/-1"><label>Reason</label><textarea name="reason" rows="2" placeholder="e.g. Forgot to check out, system issue, etc." required></textarea></div>
            </div>
            <button type="submit" class="submit-btn">Submit Request</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My Regularization Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Requested Check In</th><th>Requested Check Out</th><th>Requested Status</th><th>Reason</th><th>Status</th></tr></thead>
            <tbody>
            <?php
                $rr_res = mysqli_query($conn, "SELECT * FROM regularization_requests WHERE emp_id='$emp_id' ORDER BY request_id DESC");
                if(mysqli_num_rows($rr_res) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:#9ca3af;padding:16px;'>No regularization requests yet.</td></tr>";
                } else {
                    while($rr = mysqli_fetch_assoc($rr_res)){
                        $rr_pill_map = ['pending'=>'pending','approved'=>'approved','rejected'=>'rejected'];
                        $rr_pill = $rr_pill_map[$rr['status']] ?? 'pending';
                        echo "<tr>
                            <td>{$rr['att_date']}</td>
                            <td>".($rr['requested_check_in'] ?: '-')."</td>
                            <td>".($rr['requested_check_out'] ?: '-')."</td>
                            <td>".ucfirst(str_replace('_',' ',$rr['requested_status']))."</td>
                            <td>".htmlspecialchars($rr['reason'])."</td>
                            <td><span class='status-pill {$rr_pill}'>".ucfirst($rr['status'])."</span></td>
                        </tr>";
                    }
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">All My Attendance Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Work Mode</th><th>Hours</th><th>Overtime</th><th>Sunday</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' ORDER BY date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $hrs=0;
                    if($row['check_in']&&$row['check_out']){ $in=strtotime($row['check_in']); $out=strtotime($row['check_out']); if($out>$in) $hrs=($out-$in)/3600; }
                    $st_map=['present'=>'approved','late'=>'pending','half_day'=>'pending','work_from_home'=>'approved','absent'=>'rejected'];
                    $pill=$st_map[$row['status']]??'pending';
                    $wm = $row['work_mode'] ?? 'WFO';
                    $wm_badge = ($wm === 'WFH')
                        ? "<span class='pill blue'>&#127968; WFH</span>"
                        : "<span class='pill green'>&#127970; WFO</span>";
                    echo "<tr><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td>
                    <td><span class='status-pill $pill'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                    <td>{$wm_badge}</td>
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

<?php include 'common_js.php'; ?>
</body>
</html>
