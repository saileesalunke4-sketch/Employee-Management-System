<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}

$task_id  = intval($_POST['task_id']);
$user_id  = $_SESSION['user']['id'];

$emp_res  = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp      = mysqli_fetch_assoc($emp_res);
$emp_id   = $emp['emp_id'];
$emp_name = $emp['first_name'] . ' ' . $emp['last_name'];

$task_res = mysqli_query($conn, "SELECT * FROM tasks WHERE task_id='$task_id' AND emp_id='$emp_id'");
$task     = mysqli_fetch_assoc($task_res);

if(!$task){
    echo "<script>alert('Task not found!'); window.history.back();</script>";
    exit();
}

// Mark task completed
mysqli_query($conn, "UPDATE tasks SET status='completed' WHERE task_id='$task_id'");

// Insert notification for admin
$task_name = mysqli_real_escape_string($conn, $task['task_name']);
$msg       = mysqli_real_escape_string($conn, "Task completed by $emp_name: $task_name");
mysqli_query($conn, "INSERT INTO notifications 
    (emp_id, emp_name, leave_type, from_date, to_date, reason, is_read)
    VALUES ('$emp_id','$emp_name','task_completion',CURDATE(),CURDATE(),'$msg',0)");

echo "<script>alert('Admin notified of task completion!'); window.location.href='emp_dashboard.php';</script>";
?>