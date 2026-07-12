<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];

$query = "DELETE FROM leave_types WHERE id=$id";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Leave type deleted successfully!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to delete!'); window.history.back();</script>";
}
?>yes