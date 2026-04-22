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

// Fix 
$today = date('Y-m-d');
if($date < $today){
    echo "<script>alert('Backdated attendance not allowed! Please select today only.'); window.history.back();</script>";
    exit();
}

//  Block duplicate attendance
$check_dup = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$date'");
if(mysqli_num_rows($check_dup) > 0){
    echo "<script>alert('Attendance already marked for today! Cannot add duplicate.'); window.history.back();</script>";
    exit();
}

//  Save WFH status properly
$status = mysqli_real_escape_string($conn, $status);
$check_in = mysqli_real_escape_string($conn, $check_in);
$check_out = mysqli_real_escape_string($conn, $check_out);

$query = "INSERT INTO attendance (emp_id, date, check_in, check_out, status)
          VALUES ('$emp_id', '$date', '$check_in', '$check_out', '$status')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Attendance marked successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>