<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$emp_id      = $_POST['emp_id'];
$basic_pay   = $_POST['basic_pay'];
$allowances  = $_POST['allowances'];
$deductions  = $_POST['deductions'];
$lop_days    = $_POST['lop_days'];
$month       = $_POST['month'];
$year        = $_POST['year'];

// Calculate per day salary
$per_day     = $basic_pay / 30;

// Calculate LOP amount
$lop_amount  = $per_day * $lop_days;

// Calculate net pay
$net_pay     = ($basic_pay + $allowances) - $deductions - $lop_amount;

$query = "INSERT INTO salary (emp_id, basic_pay, allowances, deductions, lop_days, lop_amount, net_pay, month, year)
          VALUES ('$emp_id', '$basic_pay', '$allowances', '$deductions', '$lop_days', '$lop_amount', '$net_pay', '$month', '$year')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Salary added successfully!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>