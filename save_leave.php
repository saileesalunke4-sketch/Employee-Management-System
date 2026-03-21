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

$leave_type = $_POST['leave_type'];
$from_date = $_POST['from_date'];
$to_date = $_POST['to_date'];
$reason = mysqli_real_escape_string($conn, $_POST['reason']);

$query = "INSERT INTO leaves (emp_id, leave_type, from_date, to_date, reason, status)
          VALUES ('$emp_id', '$leave_type', '$from_date', '$to_date', '$reason', 'pending')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Leave applied!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>