<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

$leave_type_name = mysqli_real_escape_string($conn, trim($_POST['leave_type_name'] ?? ''));
$total_days      = (int) ($_POST['total_days'] ?? 0);

// BUGFIX (BUG-004): no validation existed at all — a leave type could be
// added again with a name that already exists, and Total Days accepted
// any value including negative numbers (e.g. a duplicate "Sick Leave"
// with -31 days).
if($total_days <= 0){
    echo "<script>alert('Total days must be greater than 0.'); window.history.back();</script>";
    exit();
}
$existing_lt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM leave_types WHERE LOWER(leave_type_name)=LOWER('$leave_type_name')"));
if($existing_lt){
    echo "<script>alert('Leave type already exists.'); window.location.href='leave_types.php';</script>";
    exit();
}

$query = "INSERT INTO leave_types (leave_type_name, total_days) 
          VALUES ('$leave_type_name', $total_days)";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Leave type added successfully!'); window.location.href='leave_types.php';</script>";
} else {
    echo "<script>alert('Failed to add leave type!'); window.history.back();</script>";
}
?>