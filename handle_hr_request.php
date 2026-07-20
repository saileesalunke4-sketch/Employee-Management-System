<?php
session_start();
require 'db.php';

// SECURITY: only admin/super_admin can approve/reject HR process requests
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$request_id = (int) ($_GET['id'] ?? 0);
$action     = $_GET['action'] ?? '';
$redirect   = 'admin_hr_requests.php';

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
    $req_res = mysqli_query($conn, "SELECT * FROM hr_process_requests WHERE request_id=$request_id");
    $req = $req_res ? mysqli_fetch_assoc($req_res) : null;

    if(!$req){
        header("Location: $redirect");
        exit();
    }

    if($action === 'approved'){
        $emp_id = (int) $req['emp_id'];
        $rtype  = $req['request_type'];
        $rvalue = $req['requested_value'];

        if($rtype === 'Department Change'){
            // Requested value is a department NAME — look up its dept_id
            $rvalue_esc = mysqli_real_escape_string($conn, $rvalue);
            $dept_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_id FROM departments WHERE dept_name='$rvalue_esc'"));
            if($dept_row){
                mysqli_query($conn, "UPDATE employees SET dept_id=".(int)$dept_row['dept_id']." WHERE emp_id=$emp_id");
            }
        } elseif($rtype === 'Designation Change'){
            $rvalue_esc = mysqli_real_escape_string($conn, $rvalue);
            mysqli_query($conn, "UPDATE employees SET designation='$rvalue_esc' WHERE emp_id=$emp_id");
        } elseif($rtype === 'Location Change'){
            $rvalue_esc = mysqli_real_escape_string($conn, $rvalue);
            mysqli_query($conn, "UPDATE employees SET work_location='$rvalue_esc' WHERE emp_id=$emp_id");
        }
    }

    mysqli_query($conn, "UPDATE hr_process_requests SET status='$action', reviewed_by=$reviewer_id WHERE request_id=$request_id");

    // Notify the employee
    $emp_id_notif  = (int) $req['emp_id'];
    $emp_row_res   = mysqli_query($conn, "SELECT first_name,last_name FROM employees WHERE emp_id=$emp_id_notif");
    $emp_row       = $emp_row_res ? mysqli_fetch_assoc($emp_row_res) : null;
    $emp_full_name = $emp_row ? trim($emp_row['first_name'].' '.$emp_row['last_name']) : 'Employee';
    $emp_name_esc  = mysqli_real_escape_string($conn, $emp_full_name);
    $rtype_esc     = mysqli_real_escape_string($conn, $req['request_type']);
    $today         = date('Y-m-d');
    $icon          = $action === 'approved' ? '✅' : '❌';
    $msg           = mysqli_real_escape_string($conn, "$icon Your {$req['request_type']} request has been ".ucfirst($action).".");

    mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                          VALUES ('$emp_id_notif', '$emp_name_esc', '$rtype_esc', '$today', '$today', '$msg', '$msg', 'hr_request_status', 'employee', 0)");

    log_activity($conn, $action, 'HR Process Request', "$emp_full_name — {$req['request_type']}", "Requested: {$req['requested_value']}");

    $banner_params = http_build_query([
        'hr_msg'  => $action,
        'hr_emp'  => $emp_full_name,
        'hr_type' => $req['request_type']
    ]);
    header("Location: $redirect?$banner_params");
    exit();

} catch (\Throwable $e) {
    $detail = (defined('APP_ENV') && APP_ENV === 'production')
        ? 'Please try again or contact your system administrator.'
        : htmlspecialchars($e->getMessage());

    echo "<div style='font-family:sans-serif;max-width:600px;margin:60px auto;background:#fee2e2;border:1px solid #fca5a5;padding:20px;border-radius:10px;color:#7f1d1d;'>";
    echo "<h3>Something went wrong while processing this request</h3>";
    echo "<p>$detail</p>";
    echo "<a href='$redirect' style='color:#1d4ed8;'>&larr; Back to HR Process Requests</a>";
    echo "</div>";
    exit();
}
?>
