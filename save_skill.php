<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$emp_res = mysqli_query($conn,"SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp_row = mysqli_fetch_assoc($emp_res);
$emp_id  = $emp_row['emp_id'];

$skill_name  = mysqli_real_escape_string($conn, $_POST['skill_name']);
$skill_level = $_POST['skill_level'];
$description = mysqli_real_escape_string($conn, $_POST['description']);
$added_date  = mysqli_real_escape_string($conn, $_POST['added_date']);

$allowed_levels = ['Beginner','Intermediate','Advanced','Expert'];
if(!in_array($skill_level, $allowed_levels)){
    echo "<script>alert('Invalid skill level!'); window.history.back();</script>";
    exit();
}

$query = "INSERT INTO emp_skills (emp_id, skill_name, skill_level, description, added_date) VALUES ('$emp_id','$skill_name','$skill_level','$description','$added_date')";
if(mysqli_query($conn, $query)){
    echo "<script>alert('Skill added successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to add skill!'); window.history.back();</script>";
}
?>