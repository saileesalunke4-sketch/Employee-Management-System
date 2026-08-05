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

$att_date            = mysqli_real_escape_string($conn, $_POST['att_date']);
$requested_check_in  = !empty($_POST['requested_check_in']) ? mysqli_real_escape_string($conn, $_POST['requested_check_in']) : null;
$requested_check_out = !empty($_POST['requested_check_out']) ? mysqli_real_escape_string($conn, $_POST['requested_check_out']) : null;
$requested_status    = mysqli_real_escape_string($conn, $_POST['requested_status']);
$reason               = mysqli_real_escape_string($conn, $_POST['reason']);

// Date must not be in the future (today IS allowed — self check-in can
// close for the day before it ends, and the employee is explicitly told to
// regularize "this day's" attendance at that point, so today has to be a
// valid choice here too).
$today = date('Y-m-d');
if(strtotime($att_date) > strtotime($today)){
    echo "<script>alert('You cannot request regularization for a future date.'); window.history.back();</script>";
    exit();
}

// Prevent duplicate pending requests for the same date
$dup = mysqli_query($conn, "SELECT * FROM regularization_requests WHERE emp_id=$emp_id AND att_date='$att_date' AND status='pending'");
if(mysqli_num_rows($dup) > 0){
    echo "<script>alert('You already have a pending regularization request for this date.'); window.history.back();</script>";
    exit();
}

$check_in_val  = $requested_check_in  ? "'$requested_check_in'"  : "NULL";
$check_out_val = $requested_check_out ? "'$requested_check_out'" : "NULL";

$query = "INSERT INTO regularization_requests (emp_id, att_date, requested_check_in, requested_check_out, requested_status, reason, status)
          VALUES ($emp_id, '$att_date', $check_in_val, $check_out_val, '$requested_status', '$reason', 'pending')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Regularization request submitted. Your Admin will review it.'); window.location.href='my_attendance.php';</script>";
} else {
    echo "<script>alert('Failed to submit request: ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
