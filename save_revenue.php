<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin'){
    header("Location: index.php");
    exit();
}

$month  = mysqli_real_escape_string($conn, $_POST['month']);
$year   = (int) $_POST['year'];
$amount = (float) $_POST['amount'];

$query = "INSERT INTO revenue (month, year, amount) VALUES ('$month', $year, $amount)";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Revenue added!'); window.location.href='super_admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>