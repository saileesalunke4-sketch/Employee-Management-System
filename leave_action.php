<?php
session_start();
require 'db.php';

// SECURITY: only admin/super_admin can approve/reject leave requests
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}

// SECURITY: CSRF check — this link must have originated from a page we
// rendered ourselves (with a valid token), not a forged external link.
if(!csrf_verify($_GET['csrf'] ?? '')){
    // BUGFIX: this failure path always sent a super_admin to admin_leaves.php
    // (the Admin portal) even though the real page passes its own &redirect=
    // value for the normal success path just below. Use the same
    // role-based fallback here too.
    $fallback_redirect = ($_SESSION['user']['role'] === 'super_admin') ? 'sa_leaves.php' : 'admin_leaves.php';
    echo "<script>alert('Security check failed (invalid or expired link). Please try again from the Leaves page.'); window.location.href='{$fallback_redirect}';</script>";
    exit();
}

$leave_id = (int) ($_GET['id'] ?? 0);
$action   = $_GET['action'] ?? '';
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : (($_SESSION['user']['role'] === 'super_admin') ? 'sa_leaves.php' : 'admin_leaves.php');

if(!in_array($action, ['approved','rejected'], true) || $leave_id <= 0){
    header("Location: $redirect"); exit();
}

// Get leave details for notification
$leave = mysqli_fetch_assoc(mysqli_query($conn,"SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.emp_id=e.emp_id WHERE l.leave_id=$leave_id"));

if(!$leave){
    header("Location: $redirect"); exit();
}

// SECURITY/CORRECTNESS: don't act on a request that's no longer pending —
// e.g. the employee may have cancelled it after this page was loaded but
// before this link was clicked (stale UI / race condition).
if($leave['status'] !== 'pending'){
    echo "<script>alert('This leave request is no longer pending (current status: {$leave['status']}) — no action taken.'); window.location.href='$redirect';</script>";
    exit();
}

if(mysqli_query($conn,"UPDATE leaves SET status='$action' WHERE leave_id=$leave_id")){

    // Notify employee about leave status
    $emp_id   = $leave['emp_id'];
    $emp_name = $leave['first_name'].' '.$leave['last_name'];
    $ltype    = $leave['leave_type'];
    $from     = $leave['from_date'];
    $to       = $leave['to_date'];
    $status   = ucfirst($action);
    $icon     = ($action=='approved') ? '✅' : '❌';
    $msg      = mysqli_real_escape_string($conn, "$icon Your $ltype request ($from to $to) has been $status by Admin.");

    mysqli_query($conn,"INSERT INTO notifications
        (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
        VALUES ('$emp_id','$emp_name','$ltype','$from','$to','$msg','$msg','leave_status','employee',0)");

    log_activity($conn, $action, 'Leave Request', "$emp_name — $ltype", "$from to $to");

        // Send email to employee
$emp_email = mysqli_fetch_assoc(mysqli_query($conn,"SELECT u.email FROM users u JOIN employees e ON u.id=e.user_id WHERE e.emp_id='$emp_id'"))['email'];

$icon    = ($action=='approved') ? '✅' : '❌';
$color   = ($action=='approved') ? '#16a34a' : '#dc2626';
$subject = "Leave Request ".ucfirst($action)." — EMS Aller Technologies";
$body    = "
<div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
    <div style='background:#1a3a6e;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
        <h2 style='color:white;margin:0;'>Aller Technologies — EMS</h2>
    </div>
    <div style='background:#f9fafb;padding:24px;border-radius:0 0 10px 10px;border:1px solid #e5e7eb;'>
        <h3 style='color:{$color};'>{$icon} Leave Request ".ucfirst($action)."</h3>
        <p>Dear <strong>{$emp_name}</strong>,</p>
        <p>Your leave request has been <strong style='color:{$color};'>".ucfirst($action)."</strong>.</p>
        <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
            <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;'>Leave Type</td><td style='padding:8px 12px;'>{$ltype}</td></tr>
            <tr><td style='padding:8px 12px;font-weight:600;'>From Date</td><td style='padding:8px 12px;'>{$from}</td></tr>
            <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;'>To Date</td><td style='padding:8px 12px;'>{$to}</td></tr>
            <tr><td style='padding:8px 12px;font-weight:600;'>Status</td><td style='padding:8px 12px;'><strong style='color:{$color};'>".ucfirst($action)."</strong></td></tr>
        </table>
        <p style='color:#6b7280;font-size:12px;'>This is an auto-generated email from EMS — Aller Technologies.</p>
    </div>
</div>";

sendEMSMail($emp_email, $emp_name, $subject, $body);

    echo "<script>alert('Leave ".ucfirst($action)." successfully!'); window.location.href='{$redirect}';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>
