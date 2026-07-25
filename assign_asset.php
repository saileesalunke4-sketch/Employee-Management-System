<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$asset_id = (int) ($_POST['asset_id'] ?? 0);
$emp_id   = (int) ($_POST['emp_id'] ?? 0);
$admin_id = (int) $_SESSION['user']['id'];

if($asset_id <= 0 || $emp_id <= 0){
    echo "<script>alert('Please select an employee.'); window.history.back();</script>";
    exit();
}

// SECURITY/CORRECTNESS: only assign if the asset is actually still
// available — stops a double-assignment race (e.g. two admin tabs open).
$asset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM assets WHERE asset_id=$asset_id AND status='available'"));
if(!$asset){
    echo "<script>alert('This asset is no longer available to assign.'); window.location.href='assets.php';</script>";
    exit();
}

$today = date('Y-m-d');
mysqli_query($conn, "INSERT INTO asset_assignments (asset_id, emp_id, assigned_date, assigned_by) VALUES ($asset_id, $emp_id, '$today', $admin_id)");
mysqli_query($conn, "UPDATE assets SET status='assigned' WHERE asset_id=$asset_id");

$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name,last_name FROM employees WHERE emp_id=$emp_id"));
$emp_full_name = $emp ? trim($emp['first_name'].' '.$emp['last_name']) : 'Employee';
$emp_name_esc  = mysqli_real_escape_string($conn, $emp_full_name);
$asset_name_esc = mysqli_real_escape_string($conn, $asset['asset_name']);

// Notify the employee
$msg = mysqli_real_escape_string($conn, "You've been assigned: {$asset['asset_name']} ({$asset['asset_type']}).");
mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                      VALUES ($emp_id, '$emp_name_esc', 'Asset Assigned', '$today', '$today', '$msg', '$msg', 'asset_status', 'employee', 0)");

log_activity($conn, 'assigned', 'Asset', $asset['asset_name'], "To $emp_full_name");

header("Location: assets.php?msg=assigned");
exit();
?>
