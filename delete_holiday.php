<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

if(mysqli_query($conn, "DELETE FROM holidays WHERE id='$id'")){
    echo "<script>alert('Holiday deleted!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to delete!'); window.history.back();</script>";
}
?>