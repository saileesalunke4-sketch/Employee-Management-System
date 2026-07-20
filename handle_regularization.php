<?php
session_start();
require 'db.php';

// SECURITY: only admin/super_admin can approve/reject regularization requests
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$request_id = (int) ($_GET['id'] ?? 0);
$action     = $_GET['action'] ?? '';
$redirect   = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin_attendance.php';

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

// Everything below is wrapped in try/catch so that any unexpected DB error
// (e.g. mysqli throwing an exception) shows a clear message instead of a
// blank page, and still gets us back to the attendance list.
try {

    $req_res = mysqli_query($conn, "SELECT * FROM regularization_requests WHERE request_id=$request_id");
    $req = $req_res ? mysqli_fetch_assoc($req_res) : null;

    if(!$req){
        header("Location: $redirect?rr_msg=notfound");
        exit();
    }

    if($action === 'approved'){
        $emp_id     = (int) $req['emp_id'];
        $att_date   = $req['att_date'];
        $check_in   = $req['requested_check_in'];
        $check_out  = $req['requested_check_out'];
        $status     = mysqli_real_escape_string($conn, $req['requested_status']);

        // Does an attendance row already exist for this employee+date?
        $ex_res   = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id=$emp_id AND date='$att_date'");
        $existing = $ex_res ? mysqli_fetch_assoc($ex_res) : null;

        if($existing){
            $set_parts = ["status='$status'"];
            if($check_in)  $set_parts[] = "check_in='".mysqli_real_escape_string($conn,$check_in)."'";
            if($check_out) $set_parts[] = "check_out='".mysqli_real_escape_string($conn,$check_out)."'";
            $set_sql = implode(', ', $set_parts);
            mysqli_query($conn, "UPDATE attendance SET $set_sql WHERE attendance_id={$existing['attendance_id']}");
        } else {
            $ci = $check_in  ? "'".mysqli_real_escape_string($conn,$check_in)."'"  : "NULL";
            $co = $check_out ? "'".mysqli_real_escape_string($conn,$check_out)."'" : "NULL";
            $is_sunday = (date('N', strtotime($att_date)) == 7) ? 1 : 0;
            mysqli_query($conn, "INSERT INTO attendance (emp_id, date, check_in, check_out, status, is_sunday)
                                  VALUES ($emp_id, '$att_date', $ci, $co, '$status', $is_sunday)");
        }
    }

    mysqli_query($conn, "UPDATE regularization_requests SET status='$action', reviewed_by=$reviewer_id WHERE request_id=$request_id");

    // Notify the employee (reusing the notifications table; leave_type/from_date/to_date
    // are required columns there, so we repurpose them for this notification's context)
    $emp_id_notif   = (int) $req['emp_id'];
    $emp_row_res    = mysqli_query($conn, "SELECT first_name,last_name FROM employees WHERE emp_id=$emp_id_notif");
    $emp_row        = $emp_row_res ? mysqli_fetch_assoc($emp_row_res) : null;
    $emp_full_name  = $emp_row ? trim($emp_row['first_name'].' '.$emp_row['last_name']) : 'Employee';
    $emp_name_notif = mysqli_real_escape_string($conn, $emp_full_name);
    $att_date_notif = mysqli_real_escape_string($conn, $req['att_date']);
    $icon           = $action === 'approved' ? '✅' : '❌';
    $msg            = mysqli_real_escape_string($conn, "$icon Your attendance regularization request for $att_date_notif has been ".ucfirst($action).".");

    mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                          VALUES ('$emp_id_notif', '$emp_name_notif', 'Regularization', '$att_date_notif', '$att_date_notif', '$msg', '$msg', 'regularization_status', 'employee', 0)");

    log_activity($conn, $action, 'Attendance Regularization', "$emp_full_name — {$req['att_date']}");

    // Redirect back with details in the query string so the list page can
    // show a confirmation banner (instead of just silently landing back).
    $banner_params = http_build_query([
        'rr_msg'   => $action,
        'rr_emp'   => $emp_full_name,
        'rr_date'  => $req['att_date']
    ]);
    header("Location: $redirect?$banner_params");
    exit();

} catch (\Throwable $e) {
    // In production, don't leak raw exception details (file paths, SQL, etc.)
    // to the user — show a generic message and let it go to the error log instead.
    $detail = (defined('APP_ENV') && APP_ENV === 'production')
        ? 'Please try again or contact your system administrator.'
        : htmlspecialchars($e->getMessage());

    echo "<div style='font-family:sans-serif;max-width:600px;margin:60px auto;background:#fee2e2;border:1px solid #fca5a5;padding:20px;border-radius:10px;color:#7f1d1d;'>";
    echo "<h3>Something went wrong while processing this request</h3>";
    echo "<p>$detail</p>";
    echo "<a href='$redirect' style='color:#1d4ed8;'>&larr; Back to Attendance</a>";
    echo "</div>";
    exit();
}
?>
