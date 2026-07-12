<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee'){
    header("Location: index.php");
    exit();
}

$user_id = mysqli_real_escape_string($conn, $_SESSION['user']['id']);
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = (int) $emp['emp_id'];

$request_type = mysqli_real_escape_string($conn, $_POST['request_type'] ?? '');
$reason       = mysqli_real_escape_string($conn, $_POST['reason'] ?? '');

if(!in_array($request_type, ['Department Change','Designation Change','Location Change'], true)){
    echo "<script>alert('Please select a valid request type.'); window.history.back();</script>";
    exit();
}

// Determine current value + requested value based on request type
$current_value = '';
$requested_value = '';

if($request_type === 'Department Change'){
    $current_value = '-';
    if($emp['dept_id']){
        $d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_name FROM departments WHERE dept_id=".(int)$emp['dept_id']));
        $current_value = $d ? $d['dept_name'] : '-';
    }
    $requested_value = trim($_POST['requested_department'] ?? '');
} elseif($request_type === 'Designation Change'){
    $current_value   = $emp['designation'] ?: '-';
    $requested_value = trim($_POST['requested_designation'] ?? '');
} elseif($request_type === 'Location Change'){
    $current_value   = $emp['work_location'] ?: '-';
    $requested_value = trim($_POST['requested_location'] ?? '');
}

if($requested_value === ''){
    echo "<script>alert('Please provide the requested value.'); window.history.back();</script>";
    exit();
}

$current_value_esc   = mysqli_real_escape_string($conn, $current_value);
$requested_value_esc = mysqli_real_escape_string($conn, $requested_value);

// Prevent duplicate pending requests of the same type
$dup = mysqli_query($conn, "SELECT * FROM hr_process_requests WHERE emp_id=$emp_id AND request_type='$request_type' AND status='pending'");
if(mysqli_num_rows($dup) > 0){
    echo "<script>alert('You already have a pending $request_type request. Please wait for it to be reviewed.'); window.history.back();</script>";
    exit();
}

$query = "INSERT INTO hr_process_requests (emp_id, request_type, current_value, requested_value, reason, status)
          VALUES ($emp_id, '$request_type', '$current_value_esc', '$requested_value_esc', '$reason', 'pending')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Your $request_type request has been submitted for approval.'); window.location.href='hr_requests.php';</script>";
} else {
    echo "<script>alert('Failed to submit request: ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
