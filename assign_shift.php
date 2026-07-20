<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

$emp_id   = (int) $_POST['emp_id'];
$shift_id = (int) $_POST['shift_id'];

if(empty($shift_id)){
    echo "<script>alert('Please select a shift first!'); window.history.back();</script>";
    exit();
}

$query = "UPDATE employees SET shift_id=$shift_id WHERE emp_id=$emp_id";

if(mysqli_query($conn, $query)){
    $emp   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name,last_name FROM employees WHERE emp_id=$emp_id"));
    $shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT shift_name FROM shifts WHERE shift_id=$shift_id"));
    if($emp && $shift){
        log_activity($conn, 'assigned', 'Shift', trim($emp['first_name'].' '.$emp['last_name']), "Assigned to {$shift['shift_name']}");
    }
    echo "<script>alert('Shift assigned successfully!'); window.location.href='view_employees.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
