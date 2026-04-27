<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$holiday_name = mysqli_real_escape_string($conn, $_POST['holiday_name']);
$holiday_date = $_POST['holiday_date'];
$description  = mysqli_real_escape_string($conn, $_POST['description']);

$check = mysqli_query($conn,"SELECT id FROM holidays WHERE holiday_date='$holiday_date'");
if(mysqli_num_rows($check) > 0){
    echo "<script>alert('Holiday already exists for this date!'); window.history.back();</script>";
    exit();
}

$query = "INSERT INTO holidays (holiday_name, holiday_date, description) VALUES ('$holiday_name','$holiday_date','$description')";
if(mysqli_query($conn, $query)){
    echo "<script>alert('Holiday added successfully!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to add holiday!'); window.history.back();</script>";
}
?>