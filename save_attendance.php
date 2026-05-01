<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];

$date      = $_POST['date'];
$check_in  = $_POST['check_in'];
$check_out = $_POST['check_out'];
$status    = $_POST['status'];

// Backdated block
$today = date('Y-m-d');
if($date < $today){
    echo "<script>alert('Backdated attendance not allowed!'); window.history.back();</script>";
    exit();
}

// Duplicate block
$check_dup = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$date'");
if(mysqli_num_rows($check_dup) > 0){
    echo "<script>alert('Attendance already marked for today!'); window.history.back();</script>";
    exit();
}

$status    = mysqli_real_escape_string($conn, $status);
$check_in  = mysqli_real_escape_string($conn, $check_in);
$check_out = mysqli_real_escape_string($conn, $check_out);

// ── Overtime Calculate ──
$overtime_hours = 0;
$office_end     = strtotime('18:00:00'); // 6 PM
$checkout_time  = strtotime($check_out);

if($checkout_time > $office_end){
    $overtime_seconds = $checkout_time - $office_end;
    $overtime_hours   = round($overtime_seconds / 3600, 2); // hours mein
}

// ── Sunday Working Check ──
$is_sunday = (date('N', strtotime($date)) == 7) ? 1 : 0;

$query = "INSERT INTO attendance (emp_id, date, check_in, check_out, status, overtime_hours, is_sunday)
          VALUES ('$emp_id', '$date', '$check_in', '$check_out', '$status', '$overtime_hours', '$is_sunday')";

if(mysqli_query($conn, $query)){
    $msg = 'Attendance marked successfully!';
    if($overtime_hours > 0) $msg .= " Overtime: {$overtime_hours} hrs";
    if($is_sunday) $msg .= " | Sunday Working counted!";
    echo "<script>alert('$msg'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>