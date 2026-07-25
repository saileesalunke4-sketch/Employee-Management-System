<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php"); exit();
}
require 'db.php';

if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed. Please try again.'); window.location.href='my_leaves.php';</script>";
    exit();
}

$leave_id = (int) ($_GET['id'] ?? 0);
$user_id  = $_SESSION['user']['id'];
$emp      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id, first_name, last_name FROM employees WHERE user_id='$user_id'"));
$emp_id   = $emp['emp_id'] ?? 0;

// SECURITY: only allow cancelling your OWN leave, and only while it's
// still pending — an approved/rejected request is a historical record and
// shouldn't be alterable after the fact.
$leave = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM leaves WHERE leave_id=$leave_id AND emp_id=$emp_id AND status='pending'"));

if(!$leave){
    echo "<script>alert('This leave request can\\'t be cancelled — it may have already been reviewed, or doesn\\'t belong to you.'); window.location.href='my_leaves.php';</script>";
    exit();
}

mysqli_query($conn, "UPDATE leaves SET status='cancelled' WHERE leave_id=$leave_id");

$emp_full_name = trim($emp['first_name'].' '.$emp['last_name']);
log_activity($conn, 'cancelled', 'Leave Request', "$emp_full_name — {$leave['leave_type']}", "{$leave['from_date']} to {$leave['to_date']}");

echo "<script>alert('Leave request cancelled.'); window.location.href='my_leaves.php';</script>";
?>
