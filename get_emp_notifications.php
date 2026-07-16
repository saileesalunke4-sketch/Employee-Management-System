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
    $items[] = [
        'type_label' => ($n['type'] == 'task') ? 'Task Assigned' : ('Leave '.$n['leave_type']),
        'message'    => $n['message'] ?: $n['reason'],
        'date'       => date('d M Y', strtotime($n['created_at'])),
        'is_read'    => (int) $n['is_read'],
        'is_task'    => ($n['type'] == 'task'),
    ];
}

echo json_encode(['unread_count' => $unread_count, 'items' => $items]);
