<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user_id  = $_SESSION['user']['id'];
$emp_name = $_SESSION['user']['name'];

$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp    = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];

$leave_type = $_POST['leave_type'];
$from_date  = $_POST['from_date'];
$to_date    = $_POST['to_date'];
$reason     = mysqli_real_escape_string($conn, $_POST['reason']);

// Save the leave request
$query = "INSERT INTO leaves (emp_id, leave_type, from_date, to_date, reason, status)
          VALUES ('$emp_id', '$leave_type', '$from_date', '$to_date', '$reason', 'pending')";

if(mysqli_query($conn, $query)){

    // Save notification for Super Admin
    $notif_name   = mysqli_real_escape_string($conn, $emp_name);
    $notif_type   = mysqli_real_escape_string($conn, $leave_type);
    $notif_from   = mysqli_real_escape_string($conn, $from_date);
    $notif_to     = mysqli_real_escape_string($conn, $to_date);
    $notif_reason = mysqli_real_escape_string($conn, $reason);

    $notif_query = "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, is_read)
                    VALUES ('$emp_id', '$notif_name', '$notif_type', '$notif_from', '$notif_to', '$notif_reason', 0)";
    mysqli_query($conn, $notif_query);

    echo "<script>alert('Leave applied successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to apply leave!'); window.history.back();</script>";
}
?>