<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php"); exit();
}
require 'db.php';

// SECURITY: CSRF check
if(!csrf_verify($_GET['csrf'] ?? '')){
    echo "<script>alert('Security check failed (invalid or expired link). Please try again.'); window.location.href='view_employees.php';</script>";
    exit();
}

$target_user_id = (int) ($_GET['id'] ?? 0);
$current_user_id = (int) $_SESSION['user']['id'];

if($target_user_id <= 0){
    header("Location: view_employees.php"); exit();
}

// SAFETY: never allow removing your own logged-in account this way.
if($target_user_id === $current_user_id){
    echo "<script>alert('You cannot remove your own account.'); window.location.href='view_employees.php';</script>";
    exit();
}

$target = mysqli_fetch_assoc(mysqli_query($conn, "SELECT u.id, u.name, u.email, u.role, e.emp_id FROM users u LEFT JOIN employees e ON u.id=e.user_id WHERE u.id=$target_user_id"));
if(!$target){
    echo "<script>alert('Employee not found.'); window.location.href='view_employees.php';</script>";
    exit();
}

// SAFETY: this button only ever appears on rows filtered to role='employee'
// in view_employees.php, but re-check here server-side too in case someone
// crafts the link directly — this action must never be usable to remove
// an Admin/Super Admin account.
if($target['role'] !== 'employee'){
    echo "<script>alert('Only employee accounts can be removed from this page.'); window.location.href='view_employees.php';</script>";
    exit();
}

$emp_id = (int) $target['emp_id'];

// DESIGN NOTE: an actual DELETE of the employees/users rows isn't safely
// possible — the employees table has foreign-key constraints from
// attendance, leaves, salary, tasks, and more, all without ON DELETE
// CASCADE, so a real employee (with any history at all) would just throw
// a foreign-key error. Cascading the delete across every one of those
// tables would also destroy payroll/attendance/audit history that has
// legal/compliance value. Instead, this deactivates the account: it can
// no longer log in, and it's hidden from the active employee list — but
// nothing about them or their history is deleted, and this can be
// reversed by an Admin/Super Admin if it was done by mistake.
if($emp_id > 0){
    mysqli_query($conn, "UPDATE employees SET status='inactive' WHERE emp_id=$emp_id");
}
// Randomize the password too, as defense-in-depth alongside the status
// check in login.php — even if the status check were ever bypassed, the
// account still can't be logged into with its old password.
$random_password_hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
mysqli_query($conn, "UPDATE users SET password='$random_password_hash' WHERE id=$target_user_id");

log_activity($conn, 'deactivated', 'Employee', $target['name'], "Email: {$target['email']}");

echo "<script>alert('Employee removed from the active list and their login access has been disabled. Their historical records have been kept.'); window.location.href='view_employees.php';</script>";
exit();
?>
