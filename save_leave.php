<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}

$user_id  = $_SESSION['user']['id'];
$emp_name = $_SESSION['user']['name'];

$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp    = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];

$leave_type = $_POST['leave_type'];
$from_date  = $_POST['from_date'];
$to_date    = $_POST['to_date'];
$reason     = mysqli_real_escape_string($conn, $_POST['reason']);

// ===== SANDWICH LEAVE POLICY =====
$from_day = date('N', strtotime($from_date)); // 1=Mon ... 5=Fri, 7=Sun
$to_day   = date('N', strtotime($to_date));

$sandwich_days = 0;
if ($from_day == 5 && $to_day == 1) {
    $sandwich_days = 0; // Fri to Mon — weekend already in range
} elseif ($from_day == 5) {
    $sandwich_days = 2; // Fri leave → +Sat+Sun
} elseif ($to_day == 1) {
    $sandwich_days = 1; // Mon leave → +Sun
}

// ===== SABBATICAL VALIDATION =====
if ($leave_type === 'Sabbatical') {
    // Check already used
    $sab_check = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND leave_type='Sabbatical'"));
    if ($sab_check['t'] > 0) {
        echo "<script>alert('You have already used your Sabbatical leave. It can only be availed once in a career.'); window.history.back();</script>";
        exit();
    }
    // Check notice period (30 days)
    $notice_days = (strtotime($from_date) - time()) / 86400;
    if ($notice_days < 30) {
        echo "<script>alert('Sabbatical leave requires 30 days advance notice. Please select a later start date.'); window.history.back();</script>";
        exit();
    }
    // Check duration
    $total_days = (strtotime($to_date) - strtotime($from_date)) / 86400 + 1 + $sandwich_days;
    if ($total_days < 30) {
        echo "<script>alert('Sabbatical leave minimum duration is 30 days.'); window.history.back();</script>";
        exit();
    }
    if ($total_days > 90) {
        echo "<script>alert('Sabbatical leave maximum duration is 90 days.'); window.history.back();</script>";
        exit();
    }
}

// ===== LEAVE BALANCE CHECK =====
// Sabbatical and Unpaid Leave are exempt (Sabbatical has its own validation
// above; Unpaid Leave has 0 allotted days by design and is meant to be
// unlimited, subject to salary/LOP deduction elsewhere in the system).
if($leave_type !== 'Sabbatical' && $leave_type !== 'Unpaid Leave'){
    $requested_days = getLeaveDaysWithSandwich($from_date, $to_date);

    $lt_row = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT total_days FROM leave_types WHERE leave_type_name='".mysqli_real_escape_string($conn,$leave_type)."'"));
    $allotted = $lt_row ? (int)$lt_row['total_days'] : 0;

    $used_days = 0;
    $used_res = mysqli_query($conn,
        "SELECT from_date, to_date FROM leaves WHERE emp_id='$emp_id' AND leave_type='".mysqli_real_escape_string($conn,$leave_type)."' AND status='approved'");
    while($u = mysqli_fetch_assoc($used_res)){
        $used_days += getLeaveDaysWithSandwich($u['from_date'], $u['to_date']);
    }

    $remaining = $allotted - $used_days;

    if($requested_days > $remaining){
        echo "<script>alert('Insufficient leave balance for $leave_type. Remaining: {$remaining} day(s), Requested: {$requested_days} day(s).'); window.history.back();</script>";
        exit();
    }
}

// Save the leave request
$query = "INSERT INTO leaves (emp_id, leave_type, from_date, to_date, reason, status)
          VALUES ('$emp_id', '$leave_type', '$from_date', '$to_date', '$reason', 'pending')";

if(mysqli_query($conn, $query)){

    // Notification for Admin/Super Admin
    $notif_name   = mysqli_real_escape_string($conn, $emp_name);
    $notif_type   = mysqli_real_escape_string($conn, $leave_type);
    $notif_from   = mysqli_real_escape_string($conn, $from_date);
    $notif_to     = mysqli_real_escape_string($conn, $to_date);
    $notif_reason = mysqli_real_escape_string($conn, $reason);

    $sandwich_note = '';
    if ($sandwich_days > 0) {
        $sandwich_note = " [Sandwich Policy: +{$sandwich_days} weekend day(s) included]";
    }
    $notif_reason_full = mysqli_real_escape_string($conn, $reason . $sandwich_note);

    $notif_query = "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, is_read)
                    VALUES ('$emp_id', '$notif_name', '$notif_type', '$notif_from', '$notif_to', '$notif_reason_full', 0)";
    mysqli_query($conn, $notif_query);

    echo "<script>alert('Leave applied successfully!'); window.location.href='my_leaves.php';</script>";
} else {
    echo "<script>alert('Failed to apply leave!'); window.history.back();</script>";
}
?>
