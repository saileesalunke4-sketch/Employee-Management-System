<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$project_name    = mysqli_real_escape_string($conn, $_POST['project_name']);
$description     = mysqli_real_escape_string($conn, $_POST['description']);
$dept_id         = $_POST['dept_id'];
$assigned_emp_id = $_POST['assigned_emp_id'];
$start_date      = $_POST['start_date'];
$target_date     = $_POST['target_date'];
$status          = $_POST['status'];

$query = "INSERT INTO projects (project_name, description, dept_id, assigned_emp_id, start_date, target_date, status)
          VALUES ('$project_name', '$description', '$dept_id', '$assigned_emp_id', '$start_date', '$target_date', '$status')";

         if(mysqli_query($conn, $query))
            {
          echo "<script>alert('Project added successfully!'); window.location.href='admin_dashboard.php?section=projects';</script>";
          } 
          else 
            {
          echo "<script>alert('Project added successfully!'); window.location.href='admin_dashboard.php?section=projects';</script>";
          }
?>