<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php"); exit();
}
require 'db.php';

if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed. Please try again.'); window.location.href='my_wfh.php';</script>";
    exit();
}

$request_id = (int) ($_GET['id'] ?? 0);
$user_id    = $_SESSION['user']['id'];
$emp        = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id, first_name, last_name FROM employees WHERE user_id='$user_id'"));
$emp_id     = $emp['emp_id'] ?? 0;

// SECURITY: only allow cancelling your OWN request, and only while pending.
$req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM wfh_requests WHERE request_id=$request_id AND emp_id=$emp_id AND status='pending'"));

if(!$req){
    echo "<script>alert('This request can\\'t be cancelled — it may have already been reviewed, or doesn\\'t belong to you.'); window.location.href='my_wfh.php';</script>";
    exit();
}

mysqli_query($conn, "UPDATE wfh_requests SET status='cancelled' WHERE request_id=$request_id");

$emp_full_name = trim($emp['first_name'].' '.$emp['last_name']);
log_activity($conn, 'cancelled', 'WFH Request', $emp_full_name, $req['wfh_date']);

echo "<script>alert('WFH request cancelled.'); window.location.href='my_wfh.php';</script>";
?>
