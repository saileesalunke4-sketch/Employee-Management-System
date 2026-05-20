<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$emp_id = $_POST['emp_id'];
$task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$target_date = $_POST['target_date'];
$status = $_POST['status'];
$hours_worked = $_POST['hours_worked'];

$query = "INSERT INTO tasks (emp_id, task_name, description, target_date, status, hours_worked)
          VALUES ('$emp_id', '$task_name', '$description', '$target_date', '$status', '$hours_worked')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Task added successfully!'); 
          window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); 
          window.history.back();</script>";
}
?>