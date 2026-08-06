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
               a.check_in, a.check_out, a.status, a.work_mode
        FROM employees e
        LEFT JOIN departments d ON e.dept_id = d.dept_id
        LEFT JOIN attendance a ON a.emp_id = e.emp_id AND a.date = '$today'
        ORDER BY e.first_name, e.last_name";

$res = mysqli_query($conn, $sql);

$employees = [];
$counts = ['present'=>0, 'late'=>0, 'half_day'=>0, 'work_from_home'=>0, 'not_checked_in'=>0, 'absent'=>0];
$work_mode_counts = ['WFH'=>0, 'WFO'=>0];

while($row = mysqli_fetch_assoc($res)){
    $status = $row['status'] ?: 'not_checked_in';
    if(!isset($counts[$status])) $counts[$status] = 0;
    $counts[$status]++;

    // work_mode is only meaningful once an attendance record exists for
    // today; someone not_checked_in (or explicitly marked absent) has no
    // work mode yet.
    $work_mode = ($row['status'] && $row['work_mode']) ? $row['work_mode'] : null;
    if($work_mode && isset($work_mode_counts[$work_mode])) $work_mode_counts[$work_mode]++;

    $employees[] = [
        'name'        => trim($row['first_name'] . ' ' . $row['last_name']),
        'designation' => $row['designation'],
        'department'  => $row['dept_name'],
        'check_in'    => $row['check_in'],
        'check_out'   => $row['check_out'],
        'status'      => $status,
        'work_mode'   => $work_mode
    ];
}

echo json_encode([
    'counts'          => $counts,
    'work_mode_counts'=> $work_mode_counts,
    'employees'       => $employees,
    'as_of'           => date('H:i:s')
]);
?>
