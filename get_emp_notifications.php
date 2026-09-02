<?php
session_start();
header('Content-Type: application/json');
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    echo json_encode(['error' => 'Not logged in']);
    exit();
}
require 'db.php';

$user_id = $_SESSION['user']['id'];
$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'"));
$emp_id = $emp['emp_id'] ?? 0;

$unread_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM notifications WHERE emp_id='$emp_id' AND for_role='employee' AND is_read=0"))['c'];

$notif_res = mysqli_query($conn, "SELECT * FROM notifications WHERE emp_id='$emp_id' AND for_role='employee' ORDER BY created_at DESC LIMIT 20");

$items = [];
while($n = mysqli_fetch_assoc($notif_res)){
    switch($n['type']){
        case 'task':
            $label = 'Task Assigned';
            $key   = 'task';
            break;
        case 'task_completion':
            $label = 'Task Completed';
            $key   = 'task';
            break;
        case 'hr_request_status':
            $label = $n['leave_type'];
            $key   = 'hr';
            break;
        case 'regularization_status':
            $label = 'Attendance Regularization';
            $key   = 'regularization';
            break;
        case 'leave_status':
            $label = 'Leave '.$n['leave_type'];
            $key   = 'leave';
            break;
        case 'wfh_status':
            $label = 'Work From Home';
            $key   = 'hr';
            break;
        case 'reimbursement_status':
            $label = 'Reimbursement';
            $key   = 'hr';
            break;
        case 'asset_status':
            $label = 'Asset Assigned';
            $key   = 'hr';
            break;
        case 'announcement_posted':
            // BUGFIX (EMS-SUPADM-020): new case — announcements previously
            // never generated a notification at all.
            $label = 'Announcement';
            $key   = 'hr';
            break;
        default:
            $label = $n['leave_type'] ?: 'Notification';
            $key   = 'leave';
    }
    $items[] = [
        'type_label' => $label,
        'type_key'   => $key,
        'type'       => $n['type'], // raw type, used to route a click to the right module page
        'message'    => $n['message'] ?: $n['reason'],
        'date'       => date('d M Y', strtotime($n['created_at'])),
        'is_read'    => (int) $n['is_read'],
        'is_task'    => ($n['type'] == 'task'),
    ];
}

echo json_encode(['unread_count' => (int)$unread_count, 'items' => $items]);
?>
