<?php
session_start();
require 'db.php';

// SECURITY: only admin/super_admin can approve/reject WFH requests
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$request_id = (int) ($_GET['id'] ?? 0);
$action     = $_GET['action'] ?? '';
$redirect   = 'wfh_requests.php';

// SECURITY: CSRF check
if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed (invalid or expired link). Please try again.'); window.location.href='$redirect';</script>";
    exit();
}

if(!in_array($action, ['approved','rejected'], true) || $request_id <= 0){
    header("Location: $redirect");
    exit();
}

$reviewer_id = (int) $_SESSION['user']['id'];

try {
    $req = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM wfh_requests WHERE request_id=$request_id"));

    if(!$req){
        header("Location: $redirect");
        exit();
    }

    // CORRECTNESS: don't act on a request that's no longer pending — e.g.
    // the employee may have cancelled it after this page was loaded but
    // before this link was clicked.
    if($req['status'] !== 'pending'){
        echo "<script>alert('This request is no longer pending (current status: {$req['status']}) — no action taken.'); window.location.href='$redirect';</script>";
        exit();
    }

    $emp_id   = (int) $req['emp_id'];
    $wfh_date = $req['wfh_date'];

    if($action === 'approved'){
        // Pre-mark that day's attendance as work_from_home so the employee
        // doesn't need to separately check in/mark WFH that day — unless
        // an attendance record for that date already exists (e.g. they
        // already checked in normally), in which case leave it alone.
        $existing_att = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM attendance WHERE emp_id=$emp_id AND date='$wfh_date'"));
        if(!$existing_att){
            $is_sunday = (date('N', strtotime($wfh_date)) == 7) ? 1 : 0;
            mysqli_query($conn, "INSERT INTO attendance (emp_id, date, check_in, check_out, status, work_mode, is_sunday)
                                  VALUES ($emp_id, '$wfh_date', NULL, NULL, 'work_from_home', 'WFH', $is_sunday)");
        }
    }

    mysqli_query($conn, "UPDATE wfh_requests SET status='$action', reviewed_by=$reviewer_id WHERE request_id=$request_id");

    // Notify the employee
    $emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT first_name,last_name FROM employees WHERE emp_id=$emp_id"));
    $emp_full_name = $emp_row ? trim($emp_row['first_name'].' '.$emp_row['last_name']) : 'Employee';
    $emp_name_esc  = mysqli_real_escape_string($conn, $emp_full_name);
    $icon = $action === 'approved' ? '✅' : '❌';
    $msg  = mysqli_real_escape_string($conn, "$icon Your Work From Home request for $wfh_date has been ".ucfirst($action).".");

    mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                          VALUES ($emp_id, '$emp_name_esc', 'WFH Request', '$wfh_date', '$wfh_date', '$msg', '$msg', 'wfh_status', 'employee', 0)");

    log_activity($conn, $action, 'WFH Request', $emp_full_name, $wfh_date);

    header("Location: $redirect");
    exit();

} catch (\Throwable $e) {
    $detail = (defined('APP_ENV') && APP_ENV === 'production')
        ? 'Please try again or contact your system administrator.'
        : htmlspecialchars($e->getMessage());

    echo "<div style='font-family:sans-serif;max-width:600px;margin:60px auto;background:#fee2e2;border:1px solid #fca5a5;padding:20px;border-radius:10px;color:#7f1d1d;'>";
    echo "<h3>Something went wrong while processing this request</h3>";
    echo "<p>$detail</p>";
    echo "<a href='$redirect' style='color:#1d4ed8;'>&larr; Back to WFH Requests</a>";
    echo "</div>";
    exit();
}
?>
