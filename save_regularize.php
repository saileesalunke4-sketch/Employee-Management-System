<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$attendance_id = $_POST['attendance_id'];
$status        = mysqli_real_escape_string($conn, $_POST['status']);
$check_in      = mysqli_real_escape_string($conn, $_POST['check_in']);
$check_out     = mysqli_real_escape_string($conn, $_POST['check_out']);

$query = "UPDATE attendance SET status='$status', check_in='$check_in', check_out='$check_out' WHERE attendance_id='$attendance_id'";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Attendance updated successfully!'); window.location.href='super_admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>