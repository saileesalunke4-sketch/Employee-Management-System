<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$month = $_POST['month'];
$year = $_POST['year'];
$amount = $_POST['amount'];

$query = "INSERT INTO revenue (month, year, amount) VALUES ('$month', '$year', '$amount')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Revenue added!'); window.location.href='super_admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>