<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}

if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed. Please try again.'); window.location.href='shifts.php';</script>";
    exit();
}

$shift_id = (int) ($_GET['id'] ?? 0);
if($shift_id <= 0){
    header("Location: shifts.php"); exit();
}

$in_use = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE shift_id=$shift_id"))['c'];
if($in_use > 0){
    header("Location: shifts.php?msg=inuse");
    exit();
}

$shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT shift_name FROM shifts WHERE shift_id=$shift_id"));
mysqli_query($conn, "DELETE FROM shifts WHERE shift_id=$shift_id");
if($shift) log_activity($conn, 'deleted', 'Shift', $shift['shift_name']);

header("Location: shifts.php?msg=deleted");
exit();
?>
