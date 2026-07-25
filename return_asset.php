<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed. Please try again.'); window.location.href='assets.php';</script>";
    exit();
}

$asset_id = (int) ($_GET['asset_id'] ?? 0);
$asset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assets WHERE asset_id=$asset_id AND status='assigned'"));

if(!$asset){
    header("Location: assets.php");
    exit();
}

$today = date('Y-m-d');

// Close out the open assignment record and free up the asset
mysqli_query($conn, "UPDATE asset_assignments SET returned_date='$today' WHERE asset_id=$asset_id AND returned_date IS NULL");
mysqli_query($conn, "UPDATE assets SET status='available' WHERE asset_id=$asset_id");

log_activity($conn, 'returned', 'Asset', $asset['asset_name']);

header("Location: assets.php?msg=returned");
exit();
?>
