<?php
// get_notifications.php — loads the notification bell + dropdown data
// asynchronously so the topbar (and the whole page) can paint immediately
// instead of waiting on these two queries on every single page load.
session_start();
require 'db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin','employee'])){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$unread_res   = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM notifications WHERE is_read=0");
$unread_count = (int) mysqli_fetch_assoc($unread_res)['cnt'];

$all_notif = mysqli_query($conn, "SELECT emp_name, leave_type, from_date, to_date, reason, is_read FROM notifications ORDER BY created_at DESC LIMIT 15");

$items = [];
while($n = mysqli_fetch_assoc($all_notif)){
    $items[] = [
        'emp_name'   => $n['emp_name'],
        'leave_type' => $n['leave_type'],
        'from_date'  => $n['from_date'],
        'to_date'    => $n['to_date'],
        'reason'     => $n['reason'],
        'is_read'    => (int) $n['is_read'],
    ];
}

echo json_encode([
    'unread_count' => $unread_count,
    'items'        => $items,
]);
