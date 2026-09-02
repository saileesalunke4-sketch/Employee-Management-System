<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

$project_name    = mysqli_real_escape_string($conn, $_POST['project_name']);
$description     = mysqli_real_escape_string($conn, $_POST['description']);
$dept_id         = (int) $_POST['dept_id'];
$start_date      = mysqli_real_escape_string($conn, $_POST['start_date']);
$target_date     = mysqli_real_escape_string($conn, $_POST['target_date']);
$status          = in_array($_POST['status'], ['ongoing','completed','on_hold'], true) ? $_POST['status'] : 'ongoing';

// BUGFIX: a project could only ever be assigned to ONE employee
// (assigned_emp_id). Now accepts a list of employee IDs and assigns the
// project to all of them via the project_assignments table.
$assigned_emp_ids = isset($_POST['assigned_emp_ids']) && is_array($_POST['assigned_emp_ids']) ? array_map('intval', $_POST['assigned_emp_ids']) : [];
if(empty($assigned_emp_ids)){
    echo "<script>alert('Please select at least one employee to assign.'); window.history.back();</script>";
    exit();
}
// Keep assigned_emp_id populated with the first selected employee, purely
// for backward compatibility with anything still reading that column.
$first_emp_id = $assigned_emp_ids[0];

$query = "INSERT INTO projects (project_name, description, dept_id, assigned_emp_id, start_date, target_date, status)
          VALUES ('$project_name', '$description', $dept_id, $first_emp_id, '$start_date', '$target_date', '$status')";

// BUGFIX x2:
// 1) both branches showed "Project added successfully!" and both redirected
//    to the same place — a failed insert reported success as if it worked,
//    hiding real errors (e.g. bad dept_id/assigned_emp_id) from the user.
// 2) 'admin_dashboard.php?section=projects' pointed at a ?section param
//    admin_dashboard.php never reads, so it just landed on the plain Admin
//    dashboard regardless of role. projects.php is the real page.
if(mysqli_query($conn, $query)){
    $new_project_id = mysqli_insert_id($conn);
    foreach($assigned_emp_ids as $eid){
        if($eid > 0){
            mysqli_query($conn, "INSERT IGNORE INTO project_assignments (project_id, emp_id) VALUES ($new_project_id, $eid)");
        }
    }
    echo "<script>alert('Project added successfully!'); window.location.href='projects.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>