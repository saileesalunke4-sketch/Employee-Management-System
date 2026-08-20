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

// SECURITY: leave_type/from_date/to_date previously went straight into the
// INSERT query below with zero escaping or validation — fixed here.
$leave_type = mysqli_real_escape_string($conn, $_POST['leave_type'] ?? '');
$from_date  = $_POST['from_date'] ?? '';
$to_date    = $_POST['to_date'] ?? '';
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $to_date)){
    echo "<script>alert('Invalid date format.'); window.history.back();</script>";
    exit();
}
$from_date  = mysqli_real_escape_string($conn, $from_date);
$to_date    = mysqli_real_escape_string($conn, $to_date);
$reason     = mysqli_real_escape_string($conn, $_POST['reason']);

// BUGFIX (BUG-002): the system had no check at all for an employee
// applying leave again on a date range that overlaps an existing Pending
// or Approved request of theirs — silently allowing duplicate/overlapping
// applications for the same date(s).
$overlap_check = mysqli_query($conn, "SELECT leave_id FROM leaves
    WHERE emp_id='$emp_id' AND status IN ('pending','approved')
    AND from_date <= '$to_date' AND to_date >= '$from_date'");
if(mysqli_num_rows($overlap_check) > 0){
    echo "<script>alert('Leave has already been applied for this date (or an overlapping date range is Pending/Approved).'); window.history.back();</script>";
    exit();
}

// ===== HALF-DAY LEAVE =====
// Only valid for a single-day request — a half-day across a multi-day
// range doesn't mean anything, so silently ignore the checkbox if the
// dates don't match rather than erroring (defensive, in case the JS
// checkbox state and dates somehow get out of sync before submit).
$is_half_day = (isset($_POST['is_half_day']) && $_POST['is_half_day'] == '1' && $from_date === $to_date) ? 1 : 0;

// ===== SANDWICH LEAVE POLICY =====
// BUGFIX: only applies to a multi-day range — a single day off (whether
// half-day or full) that happens to fall on a Monday isn't "sandwiching"
// a weekend, so skip sandwich entirely when from_date === to_date.
$from_day = date('N', strtotime($from_date)); // 1=Mon ... 5=Fri, 7=Sun
$to_day   = date('N', strtotime($to_date));

$sandwich_days = 0;
if ($from_date === $to_date) {
    $sandwich_days = 0;
} elseif ($from_day == 5 && $to_day == 1) {
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
    $requested_days = getLeaveDaysForRecord($from_date, $to_date, $is_half_day);

    $lt_row = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT total_days FROM leave_types WHERE leave_type_name='".mysqli_real_escape_string($conn,$leave_type)."'"));
    $allotted = $lt_row ? (int)$lt_row['total_days'] : 0;

    $used_days = 0;
    $used_res = mysqli_query($conn,
        "SELECT from_date, to_date, is_half_day FROM leaves WHERE emp_id='$emp_id' AND leave_type='".mysqli_real_escape_string($conn,$leave_type)."' AND status='approved'");
    while($u = mysqli_fetch_assoc($used_res)){
        $used_days += getLeaveDaysForRecord($u['from_date'], $u['to_date'], $u['is_half_day']);
    }

    $remaining = $allotted - $used_days;

    if($requested_days > $remaining){
        echo "<script>alert('Insufficient leave balance for $leave_type. Remaining: {$remaining} day(s), Requested: {$requested_days} day(s).'); window.history.back();</script>";
        exit();
    }
}

// Save the leave request
$query = "INSERT INTO leaves (emp_id, leave_type, from_date, to_date, reason, status, is_half_day)
          VALUES ('$emp_id', '$leave_type', '$from_date', '$to_date', '$reason', 'pending', $is_half_day)";

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
    $half_day_note = $is_half_day ? " [Half Day]" : "";
    $notif_reason_full = mysqli_real_escape_string($conn, $reason . $sandwich_note . $half_day_note);

    $notif_query = "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, is_read)
                    VALUES ('$emp_id', '$notif_name', '$notif_type', '$notif_from', '$notif_to', '$notif_reason_full', 0)";
    mysqli_query($conn, $notif_query);

    echo "<script>alert('Leave applied successfully!'); window.location.href='my_leaves.php';</script>";
} else {
    echo "<script>alert('Failed to apply leave!'); window.history.back();</script>";
}
?>
