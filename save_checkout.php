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

$today    = date('Y-m-d');
$now_time = date('H:i:s'); // server time, never trust client

// Find today's checked-in-but-not-checked-out record
$res = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id=$emp_id AND date='$today' AND check_out IS NULL");
$att = mysqli_fetch_assoc($res);

if(!$att){
    echo "<script>alert('No open check-in found for today, or you have already checked out.'); window.history.back();</script>";
    exit();
}

$attendance_id = (int) $att['attendance_id'];

// GEO-FENCE CHECK: skip if today was marked Work From Home, or if this
// request is confirmed to be from the office's own static IP (see
// OFFICE_STATIC_IPS in db.php) — otherwise employee's browser location
// must be within office radius to check out.
// (Now driven by the work_mode column instead of the status column, since
// work_mode is the dedicated field for this — they're always set together
// at check-in, so behavior is unchanged.)
if($att['work_mode'] !== 'WFH' && !isOfficeIp()){
    if(!isset($_POST['lat']) || !isset($_POST['lng']) || $_POST['lat']==='' || $_POST['lng']===''){
        echo "<script>alert('Location not detected. Please allow location access in your browser and try again.'); window.history.back();</script>";
        exit();
    }
    $lat = (float) $_POST['lat'];
    $lng = (float) $_POST['lng'];
    // ACCURACY CHECK: same reasoning as save_attendance.php — a location
    // reading with a large accuracy radius (typically a laptop on
    // WiFi/IP-based positioning instead of real GPS) can't be trusted
    // against a tight office geofence.
    $accuracy = isset($_POST['accuracy']) && $_POST['accuracy'] !== '' ? (float) $_POST['accuracy'] : null;
    if($accuracy !== null && $accuracy > ACCURACY_WARN_METERS){
        echo "<script>alert('Your location could only be detected to within ±".round($accuracy)."m accuracy — too imprecise to verify office proximity. If you are on a laptop, enable Location Services (Windows/Mac) or use a phone with GPS.'); window.history.back();</script>";
        exit();
    }
    $distance = getDistanceMeters($lat, $lng, OFFICE_LAT, OFFICE_LNG);

    if($distance > OFFICE_RADIUS_METERS){
        $dist_km = number_format($distance/1000, 2);
        echo "<script>alert('You are ".$dist_km." km away from office. Check-out is only allowed within office premises.'); window.history.back();</script>";
        exit();
    }
}

$check_in_ts  = strtotime("$today {$att['check_in']}");
$check_out_ts = strtotime("$today $now_time");

$hours_worked = $check_out_ts > $check_in_ts ? ($check_out_ts - $check_in_ts) / 3600 : 0;
$overtime     = $hours_worked > 8 ? round($hours_worked - 8, 2) : 0.00;

// Finalize status: if not WFH and worked less than 4 hours, mark half day
$status = $att['status'];
if($status !== 'work_from_home' && $hours_worked > 0 && $hours_worked < 4){
    $status = 'half_day';
}
$status = mysqli_real_escape_string($conn, $status);

$query = "UPDATE attendance
          SET check_out='$now_time', overtime_hours=$overtime, status='$status'
          WHERE attendance_id=$attendance_id";

if(mysqli_query($conn, $query)){
    $hrs_display = number_format($hours_worked, 2);
    echo "<script>alert('Checked out successfully at $now_time. Hours worked: $hrs_display'); window.location.href='my_attendance.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
