<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$leave_id = $_GET['id'];
$action = $_GET['action'];

$query = "UPDATE leaves SET status='$action' WHERE leave_id='$leave_id'";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Leave $action successfully!'); 
          window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed!'); 
          window.history.back();</script>";
}
?>