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

// BUGFIX: 'admin_dashboard.php?section=view_employees' pointed at a dead
// ?section param admin_dashboard.php never reads, so this always landed
// on the plain Admin dashboard — including for a super_admin, which looked
// like being redirected into the Admin portal. view_employees.php is the
// real page this form lives on (now role-aware), so go back there instead.
if(mysqli_query($conn, $query)){
    echo "<script>alert('Department assigned successfully!'); window.location.href='view_employees.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>