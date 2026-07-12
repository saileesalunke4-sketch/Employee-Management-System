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
// Shift starts at 09:00, 15-min grace period.
// 09:00–09:15  -> Present
// 09:15–12:00  -> Late
// 12:00–CUTOFF -> Half Day (missed more than half the working day)
// After CUTOFF -> self check-in blocked entirely; must go through admin regularization.
// This stops an employee from logging in at, say, 11 PM and marking "attendance" for a day
// they were never present for.
$now_ts       = strtotime("$today $now_time");
$grace_until  = strtotime("$today 09:15:00");
$noon_cutoff  = strtotime("$today 12:00:00");
$day_cutoff   = strtotime("$today 18:00:00"); // no self check-in allowed after 6:00 PM

if(!$is_wfh && $now_ts > $day_cutoff){
    echo "<script>alert('It is past 6:00 PM — self check-in is closed for today. Please contact your Admin/HR to regularize this day\\'s attendance.'); window.history.back();</script>";
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
    $status_note = ' Note: You checked in after 9:15 AM, so this is marked as LATE.';
} elseif($status === 'half_day'){
    $status_note = ' Note: You checked in after 12:00 PM, so this is marked as HALF DAY.';
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
