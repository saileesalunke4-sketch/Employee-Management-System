<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'super_admin'){
    header("Location: index.php");
    exit();
}

// Mark all notifications as read
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE is_read = 0");

header("Location: super_admin_dashboard.php");
exit();
?>