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
    // BUGFIX (EMS-ADM-012): session flash instead of ?msg=inuse — a URL
    // param kept showing this message on every refresh/revisit.
    $_SESSION['shift_flash'] = ['ok' => false, 'msg' => "Can't delete this shift — employees are still assigned to it. Reassign them to a different shift first."];
    header("Location: shifts.php");
    exit();
}

$shift = mysqli_fetch_assoc(mysqli_query($conn, "SELECT shift_name FROM shifts WHERE shift_id=$shift_id"));
mysqli_query($conn, "DELETE FROM shifts WHERE shift_id=$shift_id");
if($shift) log_activity($conn, 'deleted', 'Shift', $shift['shift_name']);

$_SESSION['shift_flash'] = ['ok' => true, 'msg' => 'Shift deleted.'];
header("Location: shifts.php");
exit();
?>
