<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$leave_type_name = mysqli_real_escape_string($conn, $_POST['leave_type_name']);
$total_days      = mysqli_real_escape_string($conn, $_POST['total_days']);

$query = "INSERT INTO leave_types (leave_type_name, total_days) 
          VALUES ('$leave_type_name', '$total_days')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Leave type added successfully!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to add leave type!'); window.history.back();</script>";
}
?>