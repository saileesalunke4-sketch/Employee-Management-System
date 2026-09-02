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
    $_SESSION['asset_flash'] = ['ok' => false, 'msg' => 'This asset is no longer available to assign.'];
    header("Location: assets.php");
    exit();
}

$today = date('Y-m-d');
mysqli_query($conn, "INSERT INTO asset_assignments (asset_id, emp_id, assigned_date, assigned_by) VALUES ($asset_id, $emp_id, '$today', $admin_id)");
mysqli_query($conn, "UPDATE assets SET status='assigned' WHERE asset_id=$asset_id");

$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT e.first_name,e.last_name,u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.emp_id=$emp_id"));
$emp_full_name = $emp ? trim($emp['first_name'].' '.$emp['last_name']) : 'Employee';
$emp_name_esc  = mysqli_real_escape_string($conn, $emp_full_name);

// Notify the employee
$msg = mysqli_real_escape_string($conn, "You've been assigned: {$asset['asset_name']} ({$asset['asset_type']}).");
mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                      VALUES ($emp_id, '$emp_name_esc', 'Asset Assigned', '$today', '$today', '$msg', '$msg', 'asset_status', 'employee', 0)");

// BUGFIX: asset assignment only ever showed an in-app notification — no
// email, unlike Leave/Task/Salary which do.
if($emp && !empty($emp['email'])){
    sendEMSMail($emp['email'], $emp_full_name, "Asset Assigned To You", "Hi " . htmlspecialchars($emp_full_name) . ",<br><br>You've been assigned: <b>" . htmlspecialchars($asset['asset_name']) . " (" . htmlspecialchars($asset['asset_type']) . ")</b>.<br><br>— EMS Notification");
}

log_activity($conn, 'assigned', 'Asset', $asset['asset_name'], "To $emp_full_name");

// BUGFIX (EMS-ADM-013): session flash instead of ?msg=assigned
$_SESSION['asset_flash'] = ['ok' => true, 'msg' => 'Asset assigned successfully.'];
header("Location: assets.php");
exit();
?>
