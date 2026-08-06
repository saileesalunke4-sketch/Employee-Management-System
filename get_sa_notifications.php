<?php
// get_sa_notifications.php — async notifications for the Super Admin topbar.
session_start();
require 'db.php';
header('Content-Type: application/json');

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin'){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// BUGFIX: same cross-role leak as get_notifications.php — scoped to
// for_role='admin' (super_admin uses the same admin-targeted notifications)
// so this no longer counts/shows notifications meant for employees.
$unread_res   = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM notifications WHERE is_read=0 AND for_role='admin'");
$unread_count = (int) mysqli_fetch_assoc($unread_res)['cnt'];

$all_notif = mysqli_query($conn, "SELECT emp_name, leave_type, from_date, to_date, is_read FROM notifications WHERE for_role='admin' ORDER BY created_at DESC LIMIT 15");

$items = [];
while($n = mysqli_fetch_assoc($all_notif)){
    $items[] = [
        'emp_name'   => $n['emp_name'],
        'leave_type' => $n['leave_type'],
        'from_date'  => $n['from_date'],
        'to_date'    => $n['to_date'],
        'is_read'    => (int) $n['is_read'],
    ];
}

echo json_encode(['unread_count' => $unread_count, 'items' => $items]);
