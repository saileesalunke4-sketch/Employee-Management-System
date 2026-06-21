<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}

$user_id    = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];

$today      = date('Y-m-d');
$now_time   = date('H:i:s');   // SERVER TIME — cannot be manipulated
$action     = $_POST['action']; // 'check_in' or 'check_out'
$work_mode  = mysqli_real_escape_string($conn, $_POST['status'] ?? 'present'); // present or work_from_home

if($action === 'check_in'){

    // Check if already checked in today
    $existing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$today'"));
    if($existing){
        echo "<script>alert('You have already checked in today!'); window.history.back();</script>";
        exit();
    }

    // ===== AUTO LATE DETECTION =====
    // Office cut-off time: 09:15 AM
    $cutoff_time = '09:15:00';
    $final_status = $work_mode; // default: present or work_from_home

    if($work_mode === 'present' && $now_time > $cutoff_time){
        $final_status = 'late';
    }

    $query = "INSERT INTO attendance (emp_id, date, check_in, status)
              VALUES ('$emp_id', '$today', '$now_time', '$final_status')";

    if(mysqli_query($conn, $query)){
        $late_msg = ($final_status === 'late') ? " You are marked LATE (after 9:15 AM)." : "";
        echo "<script>alert('Checked in successfully at $now_time!$late_msg'); window.location.href='emp_dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
    }

} elseif($action === 'check_out'){

    $existing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$today'"));

    if(!$existing){
        echo "<script>alert('You have not checked in today!'); window.history.back();</script>";
        exit();
    }

    if(!empty($existing['check_out'])){
        echo "<script>alert('You have already checked out today!'); window.history.back();</script>";
        exit();
    }

    $att_id = $existing['attendance_id'];

    // Calculate overtime
    $check_in_time = strtotime($existing['check_in']);
    $check_out_time = strtotime($now_time);
    $hours_worked = ($check_out_time - $check_in_time) / 3600;
    $overtime = $hours_worked > 8 ? round($hours_worked - 8, 2) : 0;

    $query = "UPDATE attendance SET check_out='$now_time', overtime_hours='$overtime' WHERE attendance_id='$att_id'";

    if(mysqli_query($conn, $query)){
        $ot_msg = $overtime > 0 ? " Overtime: {$overtime} hrs." : "";
        echo "<script>alert('Checked out successfully at $now_time!$ot_msg'); window.location.href='emp_dashboard.php';</script>";
    } else {
        echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
    }

} else {
    echo "<script>alert('Invalid action!'); window.history.back();</script>";
}
?>
