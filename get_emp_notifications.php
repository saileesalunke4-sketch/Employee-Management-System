<?php
// get_emp_notifications.php — async notifications for the Employee topbar.
session_start();
require 'db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'employee'){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user']['id'];
$emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'"));
$emp_id  = $emp_row['emp_id'] ?? 0;

$unread_res   = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM notifications WHERE emp_id='$emp_id' AND is_read=0");
$unread_count = (int) mysqli_fetch_assoc($unread_res)['cnt'];

$notif_res = mysqli_query($conn, "SELECT type, leave_type, message, reason, created_at, is_read FROM notifications WHERE emp_id='$emp_id' ORDER BY created_at DESC LIMIT 10");

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
            // leave_type column holds the actual request type here
            // (Designation Change / Department Change / Location Change)
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
        default:
            // Fallback for any older rows saved before 'type' was tracked
            $label = $n['leave_type'] ?: 'Notification';
            $key   = 'leave';
    }
    $items[] = [
        'type_label' => $label,
        'type_key'   => $key,
        'message'    => $n['message'] ?: $n['reason'],
        'date'       => date('d M Y', strtotime($n['created_at'])),
        'is_read'    => (int) $n['is_read'],
        'is_task'    => ($n['type'] == 'task'),
    ];
}

echo json_encode(['unread_count' => $unread_count, 'items' => $items]);
