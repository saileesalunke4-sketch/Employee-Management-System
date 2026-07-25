<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php"); exit();
}
require 'db.php';

$user_id = $_SESSION['user']['id'];
$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id, first_name, last_name FROM employees WHERE user_id='$user_id'"));
$emp_id = $emp['emp_id'];
$emp_name = trim($emp['first_name'].' '.$emp['last_name']);

$wfh_date = $_POST['wfh_date'] ?? '';
$reason   = trim($_POST['reason'] ?? '');

// SECURITY: validate the date format before it reaches SQL, and don't
// allow requesting a WFH day in the past.
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $wfh_date) || $wfh_date < date('Y-m-d')){
    echo "<script>alert('Please select today or a future date.'); window.history.back();</script>";
    exit();
}
if($reason === ''){
    echo "<script>alert('Please enter a reason.'); window.history.back();</script>";
    exit();
}

$wfh_date_esc = mysqli_real_escape_string($conn, $wfh_date);
$reason_esc   = mysqli_real_escape_string($conn, $reason);

// Don't allow two active (pending/approved) requests for the same date
$dup = mysqli_fetch_assoc(mysqli_query($conn, "SELECT request_id FROM wfh_requests WHERE emp_id='$emp_id' AND wfh_date='$wfh_date_esc' AND status IN ('pending','approved')"));
if($dup){
    echo "<script>alert('You already have a WFH request for that date.'); window.history.back();</script>";
    exit();
}

mysqli_query($conn, "INSERT INTO wfh_requests (emp_id, wfh_date, reason, status) VALUES ('$emp_id','$wfh_date_esc','$reason_esc','pending')");

// Notify admin/super_admin — reusing the shared notifications table, same
// pattern as HR process requests / regularization requests.
$msg = "$emp_name has requested Work From Home on $wfh_date.";
$msg_esc = mysqli_real_escape_string($conn, $msg);
$emp_name_esc = mysqli_real_escape_string($conn, $emp_name);
mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                      VALUES ('$emp_id','$emp_name_esc','WFH Request','$wfh_date_esc','$wfh_date_esc','$reason_esc','$msg_esc','wfh_status','admin',0)");

echo "<script>window.location.href='my_wfh.php?sent=1';</script>";
?>
