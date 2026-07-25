<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$asset_name = trim($_POST['asset_name'] ?? '');
$asset_type = trim($_POST['asset_type'] ?? '');
$serial     = trim($_POST['serial_number'] ?? '');
$purchase   = trim($_POST['purchase_date'] ?? '');

$valid_types = ['Laptop','Desktop','Monitor','Phone','Keyboard','Mouse','Headset','Other'];
if($asset_name === '' || !in_array($asset_type, $valid_types, true)){
    echo "<script>alert('Please fill in the asset name and a valid type.'); window.history.back();</script>";
    exit();
}
if($purchase !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $purchase)){
    echo "<script>alert('Invalid purchase date.'); window.history.back();</script>";
    exit();
}

$asset_name_esc = mysqli_real_escape_string($conn, $asset_name);
$asset_type_esc = mysqli_real_escape_string($conn, $asset_type);
$serial_esc     = $serial !== '' ? "'".mysqli_real_escape_string($conn, $serial)."'" : 'NULL';
$purchase_esc   = $purchase !== '' ? "'".mysqli_real_escape_string($conn, $purchase)."'" : 'NULL';

$query = "INSERT INTO assets (asset_name, asset_type, serial_number, purchase_date, status)
          VALUES ('$asset_name_esc','$asset_type_esc',$serial_esc,$purchase_esc,'available')";

if(mysqli_query($conn, $query)){
    log_activity($conn, 'created', 'Asset', $asset_name, $asset_type);
    echo "<script>alert('Asset added successfully!'); window.location.href='assets.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
