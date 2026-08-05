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
$assigned_emp_id = (int) $_POST['assigned_emp_id'];
$start_date      = mysqli_real_escape_string($conn, $_POST['start_date']);
$target_date     = mysqli_real_escape_string($conn, $_POST['target_date']);
$status          = in_array($_POST['status'], ['ongoing','completed','on_hold'], true) ? $_POST['status'] : 'ongoing';

$query = "INSERT INTO projects (project_name, description, dept_id, assigned_emp_id, start_date, target_date, status)
          VALUES ('$project_name', '$description', $dept_id, $assigned_emp_id, '$start_date', '$target_date', '$status')";

// BUGFIX x2:
// 1) both branches showed "Project added successfully!" and both redirected
//    to the same place — a failed insert reported success as if it worked,
//    hiding real errors (e.g. bad dept_id/assigned_emp_id) from the user.
// 2) 'admin_dashboard.php?section=projects' pointed at a ?section param
//    admin_dashboard.php never reads, so it just landed on the plain Admin
//    dashboard regardless of role. projects.php is the real page.
if(mysqli_query($conn, $query)){
    echo "<script>alert('Project added successfully!'); window.location.href='projects.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>