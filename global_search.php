<?php
// global_search.php — actually searches across the relevant tables for the
// logged-in role, instead of the topbar search box always dumping you on
// view_employees.php no matter what you typed.
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$role  = $_SESSION['user']['role'];
$q     = trim($_GET['q'] ?? '');
$esc   = $q !== '' ? mysqli_real_escape_string($conn, $q) : '';
$page_title = "Search Results";

$employee_results = [];
$task_results     = [];
$request_results  = [];   // HR process requests (admin/super_admin) or leave requests (employee)

if($q !== ''){

    if($role === 'admin' || $role === 'super_admin'){

        // Employees — name, email, designation, employee code
        $res = mysqli_query($conn, "SELECT u.id,u.name,u.email,e.designation,e.employee_code
                                     FROM users u LEFT JOIN employees e ON u.id=e.user_id
                                     WHERE u.role='employee' AND
                                     (u.name LIKE '%{$esc}%' OR u.email LIKE '%{$esc}%'
                                      OR e.designation LIKE '%{$esc}%' OR e.employee_code LIKE '%{$esc}%')
                                     LIMIT 25");
        while($row = mysqli_fetch_assoc($res)) $employee_results[] = $row;

        // Tasks — task name / description
        $res = mysqli_query($conn, "SELECT t.task_name,t.description,t.status,t.target_date,e.first_name,e.last_name
                                     FROM tasks t JOIN employees e ON t.emp_id=e.emp_id
                                     WHERE t.task_name LIKE '%{$esc}%' OR t.description LIKE '%{$esc}%'
                                     ORDER BY t.target_date DESC LIMIT 25");
        while($row = mysqli_fetch_assoc($res)) $task_results[] = $row;

        // HR process requests — request type / reason
        $res = mysqli_query($conn, "SELECT h.request_type,h.requested_value,h.reason,h.status,e.first_name,e.last_name
                                     FROM hr_process_requests h JOIN employees e ON h.emp_id=e.emp_id
                                     WHERE h.request_type LIKE '%{$esc}%' OR h.reason LIKE '%{$esc}%'
                                        OR h.requested_value LIKE '%{$esc}%'
                                     ORDER BY h.request_id DESC LIMIT 25");
        while($row = mysqli_fetch_assoc($res)) $request_results[] = $row;

    } elseif($role === 'employee'){

        $user_id = $_SESSION['user']['id'];
        $emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'"));
        $emp_id  = $emp_row['emp_id'] ?? 0;

        // Own tasks
        $res = mysqli_query($conn, "SELECT task_name,description,status,target_date
                                     FROM tasks WHERE emp_id='{$emp_id}'
                                     AND (task_name LIKE '%{$esc}%' OR description LIKE '%{$esc}%')
                                     ORDER BY target_date DESC LIMIT 25");
        while($row = mysqli_fetch_assoc($res)) $task_results[] = $row;

        // Own leave requests
        $res = mysqli_query($conn, "SELECT leave_type,reason,status,from_date,to_date
                                     FROM leaves WHERE emp_id='{$emp_id}'
                                     AND (leave_type LIKE '%{$esc}%' OR reason LIKE '%{$esc}%')
                                     ORDER BY from_date DESC LIMIT 25");
        while($row = mysqli_fetch_assoc($res)) $request_results[] = $row;
    }
}

$total_results = count($employee_results) + count($task_results) + count($request_results);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Search Results - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard <?php echo $role==='admin' ? 'admin-theme' : ($role==='super_admin' ? 'super-theme' : 'emp-theme'); ?>">
<?php
if($role === 'admin'){ include 'sidebar_admin.php'; }
elseif($role === 'super_admin'){ include 'sidebar_sa.php'; }
else { include 'sidebar_emp.php'; }
?>
<div class="main-content">
<?php
if($role === 'admin'){ include 'topbar_admin.php'; }
elseif($role === 'super_admin'){ include 'topbar_sa.php'; }
else { include 'topbar_emp.php'; }
?>
<div class="app-content">

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">
            <?php echo $q !== '' ? 'Search results for "'.htmlspecialchars($q).'"' : 'Search'; ?>
        </h3>

        <?php if($q === ''): ?>
            <p style="color:var(--text-3);">Type something into the search box above and press Enter.</p>
        <?php elseif($total_results === 0): ?>
            <p style="color:var(--text-3);">No matches found for "<?php echo htmlspecialchars($q); ?>".</p>
        <?php else: ?>

            <?php if(!empty($employee_results)): ?>
                <h4 style="margin:18px 0 8px;">Employees</h4>
                <div style="overflow-x:auto;">
                <table class="emp-table">
                    <thead><tr><th>Name</th><th>Email</th><th>Designation</th><th>Employee Code</th></tr></thead>
                    <tbody>
                    <?php foreach($employee_results as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['designation'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($row['employee_code'] ?? ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>

            <?php if(!empty($task_results)): ?>
                <h4 style="margin:18px 0 8px;">Tasks</h4>
                <div style="overflow-x:auto;">
                <table class="emp-table">
                    <thead><tr><?php echo ($role!=='employee') ? '<th>Employee</th>' : ''; ?><th>Task</th><th>Description</th><th>Status</th><th>Target Date</th></tr></thead>
                    <tbody>
                    <?php foreach($task_results as $row): ?>
                        <tr>
                            <?php if($role!=='employee'): ?>
                                <td><?php echo htmlspecialchars(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')); ?></td>
                            <?php endif; ?>
                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst(str_replace('_',' ',$row['status']))); ?></td>
                            <td><?php echo htmlspecialchars($row['target_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            <?php endif; ?>

            <?php if(!empty($request_results)): ?>
                <h4 style="margin:18px 0 8px;"><?php echo $role==='employee' ? 'Leave Requests' : 'HR Process Requests'; ?></h4>
                <div style="overflow-x:auto;">
                <table class="emp-table">
                    <?php if($role === 'employee'): ?>
                        <thead><tr><th>Leave Type</th><th>Reason</th><th>From</th><th>To</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach($request_results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['leave_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td><?php echo htmlspecialchars($row['from_date']); ?></td>
                                <td><?php echo htmlspecialchars($row['to_date']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    <?php else: ?>
                        <thead><tr><th>Employee</th><th>Type</th><th>Requested Value</th><th>Reason</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach($request_results as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(($row['first_name'] ?? '').' '.($row['last_name'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($row['request_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['requested_value']); ?></td>
                                <td><?php echo htmlspecialchars($row['reason']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($row['status'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    <?php endif; ?>
                </table>
                </div>
            <?php endif; ?>

        <?php endif; ?>
    </div>
</div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
