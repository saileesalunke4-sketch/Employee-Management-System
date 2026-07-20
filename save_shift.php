<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}

$shift_name  = mysqli_real_escape_string($conn, trim($_POST['shift_name'] ?? ''));
$start_time  = $_POST['start_time'] ?? '';
$end_time    = $_POST['end_time'] ?? '';
$grace       = (int) ($_POST['grace_minutes'] ?? 15);
$half_day    = (int) ($_POST['half_day_after_minutes'] ?? 180);

// SECURITY: validate time format before it reaches SQL (HTML time inputs
// should already send HH:MM, but never trust client input as-is)
if($shift_name === '' || !preg_match('/^\d{2}:\d{2}$/', $start_time) || !preg_match('/^\d{2}:\d{2}$/', $end_time)){
    echo "<script>alert('Please fill all fields correctly.'); window.history.back();</script>";
    exit();
}

$start_time = mysqli_real_escape_string($conn, $start_time.':00');
$end_time   = mysqli_real_escape_string($conn, $end_time.':00');
$grace      = max(0, min(120, $grace));
$half_day   = max(30, min(600, $half_day));

$query = "INSERT INTO shifts (shift_name, start_time, end_time, grace_minutes, half_day_after_minutes)
          VALUES ('$shift_name', '$start_time', '$end_time', $grace, $half_day)";

if(mysqli_query($conn, $query)){
    log_activity($conn, 'created', 'Shift', $shift_name, "{$start_time}-{$end_time}");
    echo "<script>alert('Shift added successfully!'); window.location.href='shifts.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
