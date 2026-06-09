<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}

$emp_id     = $_POST['emp_id'];
$basic_pay  = $_POST['basic_pay'];
$allowances = $_POST['allowances'];
$deductions = $_POST['deductions'];
$lop_days   = $_POST['lop_days'];
$month      = $_POST['month'];
$year       = $_POST['year'];

// Calculate
$per_day    = $basic_pay / 30;
$lop_amount = $per_day * $lop_days;
$net_pay    = ($basic_pay + $allowances) - $deductions - $lop_amount;

$query = "INSERT INTO salary (emp_id, basic_pay, allowances, deductions, lop_days, lop_amount, net_pay, month, year)
          VALUES ('$emp_id','$basic_pay','$allowances','$deductions','$lop_days','$lop_amount','$net_pay','$month','$year')";

if(mysqli_query($conn, $query)){

    // Get employee details
    $emp = mysqli_fetch_assoc(mysqli_query($conn,"SELECT e.first_name, e.last_name, u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.emp_id='$emp_id'"));
    $emp_name  = $emp['first_name'].' '.$emp['last_name'];
    $emp_email = $emp['email'];

    // ===== MAIL TO EMPLOYEE =====
    $subject = "💰 Salary Credited — {$month} {$year} | EMS Aller Technologies";
    $body = "
    <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
        <div style='background:#1a3a6e;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
            <h2 style='color:white;margin:0;'>Aller Technologies — EMS</h2>
            <p style='color:rgba(255,255,255,0.8);margin:4px 0 0;font-size:13px;'>Salary Slip Notification</p>
        </div>
        <div style='background:#f9fafb;padding:24px;border-radius:0 0 10px 10px;border:1px solid #e5e7eb;'>
            <h3 style='color:#1a3a6e;'>💰 Your Salary for {$month} {$year}</h3>
            <p>Dear <strong>{$emp_name}</strong>,</p>
            <p>Your salary for <strong>{$month} {$year}</strong> has been processed successfully.</p>
            <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;width:50%;'>Basic Pay</td><td style='padding:8px 12px;'>&#8377;".number_format($basic_pay,2)."</td></tr>
                <tr><td style='padding:8px 12px;font-weight:600;'>Allowances</td><td style='padding:8px 12px;'>&#8377;".number_format($allowances,2)."</td></tr>
                <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;'>Deductions / PF</td><td style='padding:8px 12px;color:#dc2626;'>&#8377;".number_format($deductions,2)."</td></tr>
                <tr><td style='padding:8px 12px;font-weight:600;'>LOP Days</td><td style='padding:8px 12px;color:#d97706;'>{$lop_days} days</td></tr>
                <tr style='background:#eff6ff;'><td style='padding:8px 12px;font-weight:600;'>LOP Amount</td><td style='padding:8px 12px;color:#dc2626;'>&#8377;".number_format($lop_amount,2)."</td></tr>
                <tr style='background:#1a3a6e;'><td style='padding:10px 12px;font-weight:700;color:white;font-size:14px;'>NET PAY</td><td style='padding:10px 12px;font-weight:700;color:#86efac;font-size:14px;'>&#8377;".number_format($net_pay,2)."</td></tr>
            </table>
            <p style='background:#dcfce7;padding:12px;border-radius:8px;font-size:13px;color:#16a34a;'>✅ Please login to EMS to download your detailed salary slip PDF.</p>
            <p style='color:#6b7280;font-size:12px;margin-top:16px;'>This is an auto-generated email from EMS — Aller Technologies.</p>
        </div>
    </div>";

    sendEMSMail($emp_email, $emp_name, $subject, $body);

    echo "<script>alert('Salary added successfully!'); window.location.href='admin_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed! ".mysqli_error($conn)."'); window.history.back();</script>";
}
?>
