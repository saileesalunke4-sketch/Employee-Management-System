<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php"); exit();
}

$emp_id       = (int) $_POST['emp_id'];
$task_name    = mysqli_real_escape_string($conn, $_POST['task_name']);
$description  = mysqli_real_escape_string($conn, $_POST['description']);
$target_date  = mysqli_real_escape_string($conn, $_POST['target_date']);
$status       = in_array($_POST['status'], ['pending','in_progress','completed'], true) ? $_POST['status'] : 'pending';
$hours_worked = (float) ($_POST['hours_worked'] ?? 0);

$query = "INSERT INTO tasks (emp_id, task_name, description, target_date, status, hours_worked)
          VALUES ($emp_id,'$task_name','$description','$target_date','$status',$hours_worked)";

if(mysqli_query($conn, $query)){

    // Get employee details
    $emp = mysqli_fetch_assoc(mysqli_query($conn,"SELECT e.first_name, e.last_name, u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.emp_id=$emp_id"));
    $emp_name  = $emp['first_name'].' '.$emp['last_name'];
    $emp_email = $emp['email'];

    // Notification
    $msg = mysqli_real_escape_string($conn,"📋 New task assigned to you: $task_name. Target date: $target_date.");
    mysqli_query($conn,"INSERT INTO notifications
        (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
        VALUES ('$emp_id','$emp_name','Task Assigned',CURDATE(),'$target_date','$msg','$msg','task','employee',0)");

    // ===== MAIL TO EMPLOYEE =====
    $assigned_by = $_SESSION['user']['name'];
    $subject = "📋 New Task Assigned — EMS Aller Technologies";
    $body = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:#1a3a6e;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
            <h2 style='color:white;margin:0;'>Aller Technologies — EMS</h2>
            <p style='color:rgba(255,255,255,0.8);margin:4px 0 0;font-size:13px;'>Task Assignment Notification</p>
        </div>
        <div style='background:#f9fafb;padding:24px;border-radius:0 0 10px 10px;border:1px solid #e5e7eb;'>
            <h3 style='color:#1a3a6e;'>📋 New Task Assigned to You</h3>
            <p>Dear <strong>{$emp_name}</strong>,</p>
            <p>A new task has been assigned to you by <strong>{$assigned_by}</strong>.</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;width:40%;'>Task Name</td><td style='padding:8px 12px;'><strong>{$task_name}</strong></td></tr>
                <tr><td style='padding:8px 12px;font-weight:600;'>Description</td><td style='padding:8px 12px;'>{$description}</td></tr>
                <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;'>Target Date</td><td style='padding:8px 12px;'>{$target_date}</td></tr>
                <tr><td style='padding:8px 12px;font-weight:600;'>Status</td><td style='padding:8px 12px;'>".ucfirst($status)."</td></tr>
                <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;'>Assigned By</td><td style='padding:8px 12px;'>{$assigned_by}</td></tr>
            </table>
            <p style='background:#eff6ff;padding:12px;border-radius:8px;font-size:13px;color:#1d4ed8;'>📌 Please login to EMS and update the task status regularly.</p>
            <p style='color:#6b7280;font-size:12px;margin-top:16px;'>This is an auto-generated email from EMS — Aller Technologies.</p>
        </div>
    </div>";

    sendEMSMail($emp_email, $emp_name, $subject, $body);

    // BUGFIX (defense-in-depth): fallback ignored role entirely — a
    // super_admin posting here without an explicit redirect would land on
    // admin_dashboard.php. All current UI usage (admin_tasks.php) doesn't
    // pass a redirect, so the role-based fallback also restores the same
    // behavior for admin while fixing it for super_admin.
    if(isset($_POST['redirect'])){
        $redirect = $_POST['redirect'];
    } else {
        $redirect = ($_SESSION['user']['role'] === 'super_admin') ? 'sa_tasks.php' : 'admin_dashboard.php';
    }
    echo "<script>alert('Task added successfully!'); window.location.href='{$redirect}';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>
