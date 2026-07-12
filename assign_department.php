<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

$emp_id  = (int) $_POST['emp_id'];
$dept_id = (int) $_POST['dept_id'];

// Empty check
if(empty($dept_id)){
    echo "<script>alert('Please select a department first!'); window.history.back();</script>";
    exit();
}

$query = "UPDATE employees SET dept_id=$dept_id WHERE emp_id=$emp_id";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Department assigned successfully!'); window.location.href='admin_dashboard.php?section=view_employees';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>