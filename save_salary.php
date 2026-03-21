<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$emp_id = $_POST['emp_id'];
$basic_pay = $_POST['basic_pay'];
$allowances = $_POST['allowances'];
$deductions = $_POST['deductions'];
$net_pay = $basic_pay + $allowances - $deductions;
$month = $_POST['month'];
$year = $_POST['year'];

$query = "INSERT INTO salary (emp_id, basic_pay, allowances, deductions, net_pay, month, year)
          VALUES ('$emp_id', '$basic_pay', '$allowances', '$deductions', '$net_pay', '$month', '$year')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Salary added successfully!'); 
          window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); 
          window.history.back();</script>";
}
?>