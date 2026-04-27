<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}

$user_id    = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];

$skill_name  = mysqli_real_escape_string($conn, trim($_POST['skill_name']));
$description = mysqli_real_escape_string($conn, trim($_POST['description']));
$date_added  = mysqli_real_escape_string($conn, $_POST['date_added']);

if(empty($skill_name) || empty($description) || empty($date_added)){
    echo "<script>alert('All fields are required!'); window.history.back();</script>";
    exit();
}

// Ensure performance table exists
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `performance` (
  `perf_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT DEFAULT NULL,
  `skill_name` VARCHAR(200) DEFAULT NULL,
  `description` TEXT,
  `date_added` DATE DEFAULT NULL,
  PRIMARY KEY (`perf_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$query = "INSERT INTO performance (emp_id, skill_name, description, date_added)
          VALUES ('$emp_id', '$skill_name', '$description', '$date_added')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Skill saved successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
