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

$date = $_POST['date'];
$check_in = $_POST['check_in'];
$check_out = $_POST['check_out'];
$status = $_POST['status'];

$query = "INSERT INTO attendance (emp_id, check_in, check_out, date, status)
          VALUES ('$emp_id', '$check_in', '$check_out', '$date', '$status')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Attendance added!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>