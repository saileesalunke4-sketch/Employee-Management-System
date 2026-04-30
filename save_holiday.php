<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$holiday_name = mysqli_real_escape_string($conn, $_POST['holiday_name']);
$holiday_date = $_POST['holiday_date'];
$holiday_type = mysqli_real_escape_string($conn, $_POST['holiday_type'] ?? 'National');

$check = mysqli_query($conn,"SELECT id FROM holidays WHERE holiday_date='$holiday_date' AND holiday_name='$holiday_name'");
if(mysqli_num_rows($check) > 0){
    echo "<script>alert('Holiday already exists!'); window.history.back();</script>";
    exit();
}

$query = "INSERT INTO holidays (holiday_name, holiday_date, holiday_type) VALUES ('$holiday_name','$holiday_date','$holiday_type')";
if(mysqli_query($conn, $query)){
    echo "<script>alert('Holiday added successfully!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! " . mysqli_error($conn) . "'); window.history.back();</script>";
}
?>