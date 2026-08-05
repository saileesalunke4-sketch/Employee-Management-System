<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

// Debug - POST data check karo
if(empty($_POST)){
    echo "<script>alert('POST data empty! Form submission issue.'); window.history.back();</script>";
    exit();
}

$dept_name = mysqli_real_escape_string($conn, $_POST['dept_name']);
$dept_head = mysqli_real_escape_string($conn, $_POST['dept_head']);

$query = "INSERT INTO departments (dept_name, dept_head) VALUES ('$dept_name', '$dept_head')";

// BUGFIX: 'admin_dashboard.php?section=departments' pointed at a ?section
// parameter admin_dashboard.php never actually reads (dead/vestigial), so
// it just landed on the plain Admin dashboard — and did so even for a
// super_admin, dropping them into the Admin portal. departments.php is
// the real page (and is now role-aware, showing the correct sidebar/topbar
// for whoever is logged in), so redirect there instead.
if(mysqli_query($conn, $query)){
    echo "<script>alert('Department added successfully!'); window.location.href='departments.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.location.href='departments.php';</script>";
}
?>