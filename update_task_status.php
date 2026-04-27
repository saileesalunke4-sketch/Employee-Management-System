<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

if($_SESSION['user']['role'] != 'employee'){
    echo "<script>alert('Unauthorized!'); window.history.back();</script>";
    exit();
}

$task_id = (int)$_POST['task_id'];
$status  = $_POST['status'];

$allowed = ['pending', 'in_progress', 'completed'];
if(!in_array($status, $allowed)){
    echo "<script>alert('Invalid status!'); window.history.back();</script>";
    exit();
}

$user_id = $_SESSION['user']['id'];
$emp_res = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp_row = mysqli_fetch_assoc($emp_res);
$emp_id  = $emp_row['emp_id'];

$check = mysqli_query($conn, "SELECT task_id FROM tasks WHERE task_id='$task_id' AND emp_id='$emp_id'");
if(mysqli_num_rows($check) == 0){
    echo "<script>alert('Task not found!'); window.history.back();</script>";
    exit();
}

$query = "UPDATE tasks SET status='$status' WHERE task_id='$task_id' AND emp_id='$emp_id'";
if(mysqli_query($conn, $query)){
    echo "<script>alert('Task status updated successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to update status!'); window.history.back();</script>";
}
?>