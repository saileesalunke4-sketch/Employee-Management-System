<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

// SECURITY FIX: only admin/super_admin can regularize attendance.
// Previously any logged-in employee could POST here directly and edit
// their own (or anyone else's) attendance record.
if(!in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

// SECURITY FIX: attendance_id must be an integer, not raw user input,
// to avoid SQL injection via the id field.
$attendance_id = (int) $_POST['attendance_id'];

// SECURITY: CSRF check
if(!csrf_verify($_POST['csrf'] ?? '')){
    echo "<script>alert('Security check failed (invalid or expired form). Please try again.'); window.history.back();</script>";
    exit();
}

$status        = mysqli_real_escape_string($conn, $_POST['status']);
$check_in      = mysqli_real_escape_string($conn, $_POST['check_in']);
$check_out     = mysqli_real_escape_string($conn, $_POST['check_out']);

$query = "UPDATE attendance SET status='$status', check_in='$check_in', check_out='$check_out' WHERE attendance_id=$attendance_id";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Attendance updated successfully!'); window.location.href='super_admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>