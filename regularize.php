<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$attendance_id = $_GET['id'];

$query = "UPDATE attendance SET status='present' WHERE attendance_id='$attendance_id'";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Attendance regularized!'); window.location.href='super_admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>