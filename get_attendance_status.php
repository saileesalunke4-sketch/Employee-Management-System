<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Admin/Super Admin only — this exposes all employees' attendance data
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$today = date('Y-m-d');

$sql = "SELECT e.emp_id, e.first_name, e.last_name, e.designation,
               d.dept_name,
               a.check_in, a.check_out, a.status
        FROM employees e
        LEFT JOIN departments d ON e.dept_id = d.dept_id
        LEFT JOIN attendance a ON a.emp_id = e.emp_id AND a.date = '$today'
        ORDER BY e.first_name, e.last_name";

$res = mysqli_query($conn, $sql);

$employees = [];
// "status" (kept for backward compatibility with the dashboard widget) is the
// raw attendance marking: present/late/half_day/work_from_home/not_checked_in.
$counts = ['present'=>0, 'late'=>0, 'half_day'=>0, 'work_from_home'=>0, 'not_checked_in'=>0];

// "presence" answers the actual question admins ask on the Attendance page:
// is this person in the office RIGHT NOW, already gone for the day, working
// from home, or not marked at all — status alone can't tell you that, since
// someone marked "present" this morning could have checked out hours ago.
$presence_counts = ['in_office'=>0, 'wfh_active'=>0, 'checked_out'=>0, 'wfh_done'=>0, 'not_checked_in'=>0];

while($row = mysqli_fetch_assoc($res)){
    $status = $row['status'] ?: 'not_checked_in';
    if(!isset($counts[$status])) $counts[$status] = 0;
    $counts[$status]++;

    if($row['status'] === null){
        $presence = 'not_checked_in';
    } elseif($row['status'] === 'work_from_home'){
        $presence = $row['check_out'] ? 'wfh_done' : 'wfh_active';
    } else {
        $presence = $row['check_out'] ? 'checked_out' : 'in_office';
    }
    $presence_counts[$presence]++;

    $employees[] = [
        'name'        => trim($row['first_name'] . ' ' . $row['last_name']),
        'designation' => $row['designation'],
        'department'  => $row['dept_name'],
        'check_in'    => $row['check_in'],
        'check_out'   => $row['check_out'],
        'status'      => $status,   // how they were marked
        'presence'    => $presence  // where they are right now
    ];
}

echo json_encode([
    'counts'          => $counts,
    'presence_counts' => $presence_counts,
    'employees'       => $employees,
    'as_of'           => date('H:i:s')
]);
?>
