<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

// This one's reachable both as a GET link ("Mark Repaired") and a POST
// form ("Send for Repair" / "Retire" dropdown), so read from whichever is set.
$asset_id  = (int) ($_POST['asset_id'] ?? $_GET['asset_id'] ?? 0);
$new_status = trim($_POST['status'] ?? $_GET['status'] ?? '');

if($_SERVER['REQUEST_METHOD'] === 'GET' && !csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed. Please try again.'); window.location.href='assets.php';</script>";
    exit();
}

$valid_statuses = ['available','under_repair','retired'];
if($asset_id <= 0 || !in_array($new_status, $valid_statuses, true)){
    header("Location: assets.php");
    exit();
}

// Only allow this direct status change for assets that aren't currently
// assigned to someone — an assigned asset must go through "Mark Returned"
// first, so we never silently free/retire something someone still has.
$asset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assets WHERE asset_id=$asset_id AND status != 'assigned'"));
if(!$asset){
    header("Location: assets.php");
    exit();
}

mysqli_query($conn, "UPDATE assets SET status='$new_status' WHERE asset_id=$asset_id");
log_activity($conn, 'updated', 'Asset', $asset['asset_name'], "Status changed to ".str_replace('_',' ',$new_status));

// BUGFIX (EMS-ADM-013): session flash instead of ?msg=saved
$_SESSION['asset_flash'] = ['ok' => true, 'msg' => 'Asset status updated.'];
header("Location: assets.php");
exit();
?>
