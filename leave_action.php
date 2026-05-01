<?php
session_start();
require 'db.php';
require 'mailer.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$leave_id = $_GET['id'];
$action   = $_GET['action'];

// Leave details fetch karo
$leave_res = mysqli_query($conn, "
    SELECT l.*, e.first_name, e.last_name, u.email 
    FROM leaves l 
    JOIN employees e ON l.emp_id = e.emp_id 
    JOIN users u ON e.user_id = u.id 
    WHERE l.leave_id = '$leave_id'
");
$leave = mysqli_fetch_assoc($leave_res);

// Status update karo
$query = "UPDATE leaves SET status='$action' WHERE leave_id='$leave_id'";

if(mysqli_query($conn, $query)){

    // Email bhejo employee ko
    $emp_name  = $leave['first_name'] . ' ' . $leave['last_name'];
    $emp_email = $leave['email'];
    $leave_type = $leave['leave_type'];
    $from_date  = $leave['from_date'];
    $to_date    = $leave['to_date'];

    if($action == 'approved'){
        $subject = "Leave Approved — Aller Technologies";
        $body = "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;'>
            <div style='background:#1a3a6e;padding:20px;text-align:center;'>
                <h2 style='color:white;margin:0;'>Aller Technologies EMS</h2>
            </div>
            <div style='padding:24px;'>
                <p style='font-size:15px;'>Dear <b>$emp_name</b>,</p>
                <p style='font-size:14px;color:#444;'>Your leave request has been <b style='color:green;'>✅ Approved</b>.</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                    <tr><td style='padding:8px;color:#888;'>Leave Type</td><td><b>$leave_type</b></td></tr>
                    <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>From</td><td><b>$from_date</b></td></tr>
                    <tr><td style='padding:8px;color:#888;'>To</td><td><b>$to_date</b></td></tr>
                </table>
                <p style='font-size:13px;color:#666;'>Enjoy your time off!</p>
                <p style='font-size:13px;color:#666;margin-top:20px;'>Regards,<br><b>HR Team — Aller Technologies</b></p>
            </div>
        </div>";
    } else {
        $subject = "Leave Rejected — Aller Technologies";
        $body = "
        <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;'>
            <div style='background:#1a3a6e;padding:20px;text-align:center;'>
                <h2 style='color:white;margin:0;'>Aller Technologies EMS</h2>
            </div>
            <div style='padding:24px;'>
                <p style='font-size:15px;'>Dear <b>$emp_name</b>,</p>
                <p style='font-size:14px;color:#444;'>Your leave request has been <b style='color:red;'>❌ Rejected</b>.</p>
                <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
                    <tr><td style='padding:8px;color:#888;'>Leave Type</td><td><b>$leave_type</b></td></tr>
                    <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>From</td><td><b>$from_date</b></td></tr>
                    <tr><td style='padding:8px;color:#888;'>To</td><td><b>$to_date</b></td></tr>
                </table>
                <p style='font-size:13px;color:#666;'>Please contact HR for more details.</p>
                <p style='font-size:13px;color:#666;margin-top:20px;'>Regards,<br><b>HR Team — Aller Technologies</b></p>
            </div>
        </div>";
    }

    sendEmail($emp_email, $emp_name, $subject, $body);

    $redirect = ($_SESSION['user']['role'] == 'super_admin') 
                ? 'super_admin_dashboard.php' 
                : 'admin_dashboard.php';

    echo "<script>alert('Leave $action successfully! Email sent to employee.'); 
          window.location.href='$redirect';</script>";
} else {
    echo "<script>alert('Failed!'); window.history.back();</script>";
}
?>