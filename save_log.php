<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];

$log_date    = mysqli_real_escape_string($conn, $_POST['log_date']);
$work_done   = mysqli_real_escape_string($conn, $_POST['work_done']);
$hours_spent = (float) $_POST['hours_spent'];

// Duplicate check - ek din mein ek hi log
$dup = mysqli_query($conn, "SELECT * FROM daily_logs WHERE emp_id='$emp_id' AND log_date='$log_date'");
if(mysqli_num_rows($dup) > 0){
    echo "<script>alert('Log already submitted for today!'); window.history.back();</script>";
    exit();
}

// ── Productivity Score Calculate ──
$score = 0;

// 1. Attendance check — 30 points
$att = mysqli_query($conn, "SELECT * FROM attendance WHERE emp_id='$emp_id' AND date='$log_date'");
if(mysqli_num_rows($att) > 0){
    $score += 30;
}

// 2. Tasks completed today — 40 points
$tasks = mysqli_fetch_assoc(mysqli_query($conn, 
    "SELECT COUNT(*) as c FROM tasks WHERE emp_id='$emp_id' AND status='completed'"));
$task_count = $tasks['c'];
if($task_count >= 3) $score += 40;
elseif($task_count == 2) $score += 25;
elseif($task_count == 1) $score += 15;

// 3. Log quality — 30 points
$word_count = str_word_count($work_done);
if($word_count >= 50) $score += 30;
elseif($word_count >= 30) $score += 20;
elseif($word_count >= 10) $score += 10;

// Save log
$query = "INSERT INTO daily_logs (emp_id, log_date, work_done, hours_spent, productivity_score)
          VALUES ('$emp_id', '$log_date', '$work_done', '$hours_spent', '$score')";

if(mysqli_query($conn, $query)){
    echo "<script>alert('Daily log saved! Your Productivity Score: $score/100'); 
          window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>