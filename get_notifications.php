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

// BUGFIX: these two queries had no for_role filter at all, so an admin's
// unread badge/count included EVERY notification in the system — even ones
// meant for employees (e.g. announcement pushes, leave-status updates) —
// and (see mark_notifications_read.php) clicking "Mark all read" would
// silently mark those employee notifications as read too, before the
// employee ever saw them. Scoped to for_role='admin' now, matching how
// these rows are actually inserted (see save_wfh_request.php, leave_action.php, etc).
$unread_res   = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM notifications WHERE is_read=0 AND for_role='admin'");
$unread_count = (int) mysqli_fetch_assoc($unread_res)['cnt'];

$all_notif = mysqli_query($conn, "SELECT emp_name, leave_type, from_date, to_date, reason, is_read FROM notifications WHERE for_role='admin' ORDER BY created_at DESC LIMIT 15");

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
