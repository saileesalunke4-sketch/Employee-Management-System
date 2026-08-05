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

$asset_id = (int) ($_GET['id'] ?? 0);
if($asset_id <= 0){
    header("Location: assets.php"); exit();
}

$asset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assets WHERE asset_id=$asset_id"));
if(!$asset){
    header("Location: assets.php"); exit();
}

if($asset['status'] === 'assigned'){
    // BUGFIX (EMS-ADM-013): session flash instead of ?msg=inuse
    $_SESSION['asset_flash'] = ['ok' => false, 'msg' => "Can't delete this asset — it's currently assigned to an employee. Return it first."];
    header("Location: assets.php");
    exit();
}

mysqli_query($conn, "DELETE FROM assets WHERE asset_id=$asset_id");
mysqli_query($conn, "DELETE FROM asset_assignments WHERE asset_id=$asset_id"); // clean up its assignment history too

log_activity($conn, 'deleted', 'Asset', $asset['asset_name']);

$_SESSION['asset_flash'] = ['ok' => true, 'msg' => 'Asset deleted.'];
header("Location: assets.php");
exit();
?>
