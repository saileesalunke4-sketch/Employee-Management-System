<?php
session_start();
require 'db.php';
require 'mailer.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$emp_id      = $_POST['emp_id'];
$task_name   = mysqli_real_escape_string($conn, $_POST['task_name']);
$description = mysqli_real_escape_string($conn, $_POST['description']);
$target_date = $_POST['target_date'];
$status      = $_POST['status'];
$hours_worked = $_POST['hours_worked'];

$query = "INSERT INTO tasks (emp_id, task_name, description, target_date, status, hours_worked)
          VALUES ('$emp_id', '$task_name', '$description', '$target_date', '$status', '$hours_worked')";

if(mysqli_query($conn, $query)){

    // Employee email fetch karo
    $emp_res = mysqli_query($conn, "
        SELECT e.first_name, e.last_name, u.email 
        FROM employees e 
        JOIN users u ON e.user_id = u.id 
        WHERE e.emp_id = '$emp_id'
    ");
    $emp = mysqli_fetch_assoc($emp_res);
    $emp_name  = $emp['first_name'] . ' ' . $emp['last_name'];
    $emp_email = $emp['email'];

    $subject = "New Task Assigned — Aller Technologies";
    $body = "
    <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;'>
        <div style='background:#1a3a6e;padding:20px;text-align:center;'>
            <h2 style='color:white;margin:0;'>Aller Technologies EMS</h2>
        </div>
        <div style='padding:24px;'>
            <p style='font-size:15px;'>Dear <b>$emp_name</b>,</p>
            <p style='font-size:14px;color:#444;'>A new task has been <b style='color:#1a3a6e;'>📋 Assigned</b> to you.</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                <tr><td style='padding:8px;color:#888;'>Task Name</td><td><b>$task_name</b></td></tr>
                <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>Description</td><td><b>$description</b></td></tr>
                <tr><td style='padding:8px;color:#888;'>Target Date</td><td><b>$target_date</b></td></tr>
                <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>Status</td><td><b>$status</b></td></tr>
            </table>
            <p style='font-size:13px;color:#666;'>Please login to EMS to view task details.</p>
            <p style='font-size:13px;color:#666;margin-top:20px;'>Regards,<br><b>HR Team — Aller Technologies</b></p>
        </div>
    </div>";

    sendEmail($emp_email, $emp_name, $subject, $body);

   echo "<script>alert('Task assigned successfully! Email sent to employee.'); 
         window.location.href='admin_dashboard.php';</script>";
        
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>