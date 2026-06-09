<?php
session_start();
require 'db.php';
if(!isset($_SESSION['user'])) { header("Location: index.php"); exit(); }
$user_id    = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn,"SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];
mysqli_query($conn,"UPDATE notifications SET is_read=1 WHERE emp_id='$emp_id' AND for_role='employee'");
header("Location: ".$_SERVER['HTTP_REFERER']);
exit();
?>
