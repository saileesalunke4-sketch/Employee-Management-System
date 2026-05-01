<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

// Debug - POST data check karo
if(empty($_POST)){
    echo "<script>alert('POST data empty! Form submission issue.'); window.history.back();</script>";
    exit();
}

$dept_name = mysqli_real_escape_string($conn, $_POST['dept_name']);
$dept_head = mysqli_real_escape_string($conn, $_POST['dept_head']);

$query = "INSERT INTO departments (dept_name, dept_head) VALUES ('$dept_name', '$dept_head')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Department added successfully!'); window.location.href='admin_dashboard.php?section=departments';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.location.href='admin_dashboard.php?section=departments';</script>";
}
?>