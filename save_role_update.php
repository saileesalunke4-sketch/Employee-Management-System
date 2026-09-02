<?php
session_start();
require 'db.php';

// SECURITY: only admin/super_admin can make role/department/location updates
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}

$redirect = 'admin_hr_requests.php';

// SECURITY: CSRF check
if(!csrf_verify($_POST['csrf'] ?? '')){
    echo "<script>alert('Security check failed (invalid or expired form). Please try again.'); window.location.href='$redirect';</script>";
    exit();
}

$emp_id      = (int) ($_POST['emp_id'] ?? 0);
$rtype       = $_POST['request_type'] ?? '';
$reason      = trim($_POST['reason'] ?? '');
$reviewer_id = (int) $_SESSION['user']['id'];

if($emp_id <= 0 || !in_array($rtype, ['Department Change','Designation Change','Location Change'], true) || $reason === ''){
    echo "<script>alert('Please fill all fields.'); window.history.back();</script>";
    exit();
}

try {
    // Fetch employee + current values (for the history log)
    $emp_res = mysqli_query($conn, "SELECT e.*, d.dept_name, u.email FROM employees e LEFT JOIN departments d ON e.dept_id=d.dept_id JOIN users u ON e.user_id=u.id WHERE e.emp_id=$emp_id");
    $emp = $emp_res ? mysqli_fetch_assoc($emp_res) : null;
    if(!$emp){
        echo "<script>alert('Employee not found.'); window.location.href='$redirect';</script>";
        exit();
    }

    $current_value  = '';
    $requested_value = '';

    if($rtype === 'Department Change'){
        $current_value = $emp['dept_name'] ?: '-';
        $requested_value = trim($_POST['requested_department'] ?? '');
        if($requested_value === ''){
            echo "<script>alert('Please select a department.'); window.history.back();</script>"; exit();
        }
        $dept_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_id FROM departments WHERE dept_name='".mysqli_real_escape_string($conn,$requested_value)."'"));
        if($dept_row){
            mysqli_query($conn, "UPDATE employees SET dept_id=".(int)$dept_row['dept_id']." WHERE emp_id=$emp_id");
        }
    } elseif($rtype === 'Designation Change'){
        $current_value = $emp['designation'] ?: '-';
        $requested_value = trim($_POST['requested_designation'] ?? '');
        if($requested_value === ''){
            echo "<script>alert('Please enter a new designation.'); window.history.back();</script>"; exit();
        }
        mysqli_query($conn, "UPDATE employees SET designation='".mysqli_real_escape_string($conn,$requested_value)."' WHERE emp_id=$emp_id");
    } elseif($rtype === 'Location Change'){
        $current_value = $emp['work_location'] ?: '-';
        $requested_value = trim($_POST['requested_location'] ?? '');
        if($requested_value === ''){
            echo "<script>alert('Please enter a new location.'); window.history.back();</script>"; exit();
        }
        mysqli_query($conn, "UPDATE employees SET work_location='".mysqli_real_escape_string($conn,$requested_value)."' WHERE emp_id=$emp_id");
    }

    // Log this admin-initiated change directly as 'approved' (no employee request/approval step anymore)
    $current_esc   = mysqli_real_escape_string($conn, $current_value);
    $requested_esc = mysqli_real_escape_string($conn, $requested_value);
    $reason_esc    = mysqli_real_escape_string($conn, $reason);
    $rtype_esc     = mysqli_real_escape_string($conn, $rtype);

    mysqli_query($conn, "INSERT INTO hr_process_requests (emp_id, request_type, current_value, requested_value, reason, status, reviewed_by, created_at)
                          VALUES ($emp_id, '$rtype_esc', '$current_esc', '$requested_esc', '$reason_esc', 'approved', $reviewer_id, NOW())");

    // Notify the employee
    $emp_full_name = trim($emp['first_name'].' '.$emp['last_name']);
    $emp_name_esc  = mysqli_real_escape_string($conn, $emp_full_name);
    $today         = date('Y-m-d');
    $role_label    = $_SESSION['user']['role'] === 'admin' ? 'Admin' : 'Super Admin';
    $msg           = mysqli_real_escape_string($conn, "✅ Your $rtype has been updated by $role_label to: $requested_value");

    mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                          VALUES ($emp_id, '$emp_name_esc', '$rtype_esc', '$today', '$today', '$reason_esc', '$msg', 'hr_request_status', 'employee', 0)");

    // BUGFIX: a role/department/location/designation change by Admin/Super
    // Admin only ever showed an in-app notification — no email, unlike
    // Leave/Task/Salary which do.
    if(!empty($emp['email'])){
        sendEMSMail($emp['email'], $emp_full_name, "Your $rtype Has Been Updated", "Hi " . htmlspecialchars($emp_full_name) . ",<br><br>✅ Your $rtype has been updated by $role_label to: <b>" . htmlspecialchars($requested_value) . "</b>.<br><br>— EMS Notification");
    }

    log_activity($conn, 'approved', $rtype, "$emp_full_name", "Changed from '$current_value' to '$requested_value'");

    $banner_params = http_build_query([
        'hr_msg'  => 'approved',
        'hr_emp'  => $emp_full_name,
        'hr_type' => $rtype
    ]);
    header("Location: $redirect?$banner_params");
    exit();

} catch (\Throwable $e) {
    $detail = (defined('APP_ENV') && APP_ENV === 'production')
        ? 'Please try again or contact your system administrator.'
        : htmlspecialchars($e->getMessage());

    echo "<div style='font-family:sans-serif;max-width:600px;margin:60px auto;background:#fee2e2;border:1px solid #fca5a5;padding:20px;border-radius:10px;color:#7f1d1d;'>";
    echo "<h3>Something went wrong while applying this update</h3>";
    echo "<p>$detail</p>";
    echo "<a href='$redirect' style='color:#1d4ed8;'>&larr; Back to Role & Department Updates</a>";
    echo "</div>";
    exit();
}
?>
