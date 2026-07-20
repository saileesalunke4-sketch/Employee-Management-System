<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee'){
    header("Location: index.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_SESSION['user']['id']);
$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = (int) $emp['emp_id'];

// SECURITY / LOGIC FIX:
// Date and check-in time now ALWAYS come from the server clock.
// Previously they were taken from $_POST, so an employee could type
// any date/time they wanted (backdating, faking an early check-in, etc).
$today    = date('Y-m-d');
$now_time = date('H:i:s');

// Optional: employee can still declare Work From Home at check-in time.
// This is a legitimate manual input (a declaration, not a fake timestamp).
$is_wfh = isset($_POST['wfh']) && $_POST['wfh'] == '1';

// GEO-FENCE CHECK: skip location check entirely if employee marked WFH.
// Otherwise, employee's browser-reported location must be within
// OFFICE_RADIUS_METERS of the office coordinates.
if(!$is_wfh){
    if(!isset($_POST['lat']) || !isset($_POST['lng']) || $_POST['lat']==='' || $_POST['lng']===''){
        echo "<script>alert('Location not detected. Please allow location access in your browser and try again.'); window.history.back();</script>";
        exit();
    }
    $lat = (float) $_POST['lat'];
    $lng = (float) $_POST['lng'];
    $distance = getDistanceMeters($lat, $lng, OFFICE_LAT, OFFICE_LNG);

    if($distance > OFFICE_RADIUS_METERS){
        $dist_km = number_format($distance/1000, 2);
        echo "<script>alert('You are ".$dist_km." km away from office. Check-in is only allowed within office premises (or mark Work From Home).'); window.history.back();</script>";
        exit();
    }
}

// Block duplicate check-in for today (escaped values now, query built after escaping)
$check_dup = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id=$emp_id AND date='$today'");
if(mysqli_num_rows($check_dup) > 0){
    echo "<script>alert('You have already checked in today!'); window.history.back();</script>";
    exit();
}

// ===== ATTENDANCE WINDOW / STATUS RULES =====
// These used to be hardcoded (09:00 start, 15-min grace, 12:00 half-day,
// 18:00 cutoff) for every employee. Now they come from whichever shift
// this employee is assigned to (see shifts.php / Shift Management), so
// different teams can be on different schedules.
// Present:  check-in within [shift start, shift start + grace]
// Late:     within (grace end, shift start + half_day_after_minutes]
// Half Day: within (half-day cutoff, shift end]
// After shift end -> self check-in blocked; must go through admin regularization.
$shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT s.* FROM shifts s
                                                  JOIN employees e ON e.shift_id = s.shift_id
                                                  WHERE e.emp_id=$emp_id"));
// Fallback to the old hardcoded defaults if this employee somehow has no
// shift assigned yet, so nothing breaks.
$shift_start   = $shift['start_time'] ?? '09:00:00';
$shift_end     = $shift['end_time']   ?? '18:00:00';
$grace_minutes = $shift ? (int)$shift['grace_minutes'] : 15;
$half_day_min  = $shift ? (int)$shift['half_day_after_minutes'] : 180;

$now_ts       = strtotime("$today $now_time");
$shift_start_ts = strtotime("$today $shift_start");
$grace_until  = $shift_start_ts + ($grace_minutes * 60);
$noon_cutoff  = $shift_start_ts + ($half_day_min * 60);
$day_cutoff   = strtotime("$today $shift_end"); // no self check-in allowed after shift ends

if(!$is_wfh && $now_ts > $day_cutoff){
    echo "<script>alert('It is past ".date('h:i A', $day_cutoff)." — self check-in is closed for today. Please contact your Admin/HR to regularize this day\\'s attendance.'); window.history.back();</script>";
    exit();
}

if($is_wfh){
    $status = 'work_from_home';
} elseif($now_ts > $noon_cutoff){
    $status = 'half_day';
} elseif($now_ts > $grace_until){
    $status = 'late';
} else {
    $status = 'present';
}

// Friendly heads-up messages shown along with the success alert
$status_note = '';
if($status === 'late'){
    $status_note = ' Note: You checked in after '.date('h:i A', $grace_until).', so this is marked as LATE.';
} elseif($status === 'half_day'){
    $status_note = ' Note: You checked in after '.date('h:i A', $noon_cutoff).', so this is marked as HALF DAY.';
}

$is_sunday = (date('N', strtotime($today)) == 7) ? 1 : 0;

$query = "INSERT INTO attendance (emp_id, date, check_in, check_out, status, is_sunday)
          VALUES ($emp_id, '$today', '$now_time', NULL, '$status', $is_sunday)";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Checked in successfully at $now_time.$status_note'); window.location.href='my_attendance.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
