<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$request_id = (int) ($_GET['id'] ?? 0);
$action     = $_GET['action'] ?? '';
$redirect   = 'reimbursements.php';

if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed (invalid or expired link). Please try again.'); window.location.href='$redirect';</script>";
    exit();
}

if(!in_array($action, ['approved','rejected'], true) || $request_id <= 0){
    header("Location: $redirect");
    exit();
}

$reviewer_id = (int) $_SESSION['user']['id'];

$req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM reimbursement_requests WHERE request_id=$request_id"));

if(!$req){
    header("Location: $redirect");
    exit();
}

// CORRECTNESS: don't act twice on the same request (e.g. employee cancelled
// it after this page loaded but before the link was clicked).
if($req['status'] !== 'pending'){
    echo "<script>alert('This request is no longer pending (current status: {$req['status']}) — no action taken.'); window.location.href='$redirect';</script>";
    exit();
}

$emp_id = (int) $req['emp_id'];

mysqli_query($conn, "UPDATE reimbursement_requests SET status='$action', reviewed_by=$reviewer_id WHERE request_id=$request_id");

// Notify the employee
$emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name,last_name FROM employees WHERE emp_id=$emp_id"));
$emp_full_name = $emp_row ? trim($emp_row['first_name'].' '.$emp_row['last_name']) : 'Employee';
$emp_name_esc  = mysqli_real_escape_string($conn, $emp_full_name);
$icon = $action === 'approved' ? '✅' : '❌';
$msg  = mysqli_real_escape_string($conn, "$icon Your reimbursement request for {$req['category']} (₹".number_format($req['amount'],2).") has been ".ucfirst($action).".");
$today = date('Y-m-d');

mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                      VALUES ($emp_id, '$emp_name_esc', 'Reimbursement', '$today', '$today', '$msg', '$msg', 'reimbursement_status', 'employee', 0)");

log_activity($conn, $action, 'Reimbursement Request', $emp_full_name, "{$req['category']} — ₹".number_format($req['amount'],2));

header("Location: $redirect");
exit();
?>
