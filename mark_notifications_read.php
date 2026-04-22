<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

// Mark all notifications as read
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE is_read = 0");

// Redirect back to correct dashboard
$role = $_SESSION['user']['role'];
if($role == 'super_admin'){
    header("Location: super_admin_dashboard.php");
} elseif($role == 'admin'){
    header("Location: admin_dashboard.php");
} else {
    header("Location: emp_dashboard.php");
}
exit();
?>