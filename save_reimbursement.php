<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php"); exit();
}
require 'db.php';

$user_id = $_SESSION['user']['id'];
$emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id, first_name, last_name FROM employees WHERE user_id='$user_id'"));
$emp_id = $emp['emp_id'];
$emp_name = trim($emp['first_name'].' '.$emp['last_name']);

$category    = trim($_POST['category'] ?? '');
$amount      = $_POST['amount'] ?? '';
$description = trim($_POST['description'] ?? '');

$valid_categories = ['Travel','Food','Internet/Phone','Medical','Office Supplies','Other'];
if(!in_array($category, $valid_categories, true)){
    echo "<script>alert('Please select a valid category.'); window.history.back();</script>";
    exit();
}
if(!is_numeric($amount) || (float)$amount <= 0){
    echo "<script>alert('Please enter a valid amount.'); window.history.back();</script>";
    exit();
}
if($description === ''){
    echo "<script>alert('Please enter a description.'); window.history.back();</script>";
    exit();
}

$amount_esc      = (float) $amount;
$category_esc    = mysqli_real_escape_string($conn, $category);
$description_esc = mysqli_real_escape_string($conn, $description);

// ===== RECEIPT UPLOAD (optional) =====
// Same validation approach as upload_documents.php: extension whitelist +
// real file-content MIME check (not just trusting the filename), server-
// generated filename, and a .htaccess in the folder that disables script
// execution — so even if something slipped through, it couldn't run as PHP.
$receipt_filename = null;
if(isset($_FILES['receipt']) && $_FILES['receipt']['error'] == 0){
    if(!is_dir('uploads/receipts')) mkdir('uploads/receipts', 0777, true);
    $htaccess_path = 'uploads/receipts/.htaccess';
    if(!file_exists($htaccess_path)){
        file_put_contents($htaccess_path, "php_flag engine off\nAddHandler cgi-script .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\nOptions -ExecCGI\n");
    }

    $allowed_ext_mime = ['pdf'=>'application/pdf','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png'];
    $max_file_size = 5 * 1024 * 1024;

    $orig_name = $_FILES['receipt']['name'];
    $tmp_path  = $_FILES['receipt']['tmp_name'];
    $size      = $_FILES['receipt']['size'];
    $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

    if(!array_key_exists($ext, $allowed_ext_mime)){
        echo "<script>alert('Receipt must be a PDF, JPG or PNG file.'); window.history.back();</script>";
        exit();
    }
    if($size > $max_file_size){
        echo "<script>alert('Receipt file is too large (max 5 MB).'); window.history.back();</script>";
        exit();
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $real_mime = finfo_file($finfo, $tmp_path);
    finfo_close($finfo);
    if($real_mime !== $allowed_ext_mime[$ext]){
        echo "<script>alert('Receipt file content does not match a valid $ext file.'); window.history.back();</script>";
        exit();
    }

    $receipt_filename = 'receipt_'.$emp_id.'_'.time().'.'.$ext;
    move_uploaded_file($tmp_path, 'uploads/receipts/'.$receipt_filename);
}

$receipt_esc = $receipt_filename ? "'".mysqli_real_escape_string($conn,$receipt_filename)."'" : 'NULL';

mysqli_query($conn, "INSERT INTO reimbursement_requests (emp_id, category, amount, description, receipt_filename, status)
                      VALUES ('$emp_id','$category_esc',$amount_esc,'$description_esc',$receipt_esc,'pending')");

// Notify admin/super_admin
$msg = "$emp_name has submitted a reimbursement request for $category — ₹".number_format($amount_esc,2).".";
$msg_esc = mysqli_real_escape_string($conn, $msg);
$emp_name_esc = mysqli_real_escape_string($conn, $emp_name);
$today = date('Y-m-d');
mysqli_query($conn, "INSERT INTO notifications (emp_id, emp_name, leave_type, from_date, to_date, reason, message, type, for_role, is_read)
                      VALUES ('$emp_id','$emp_name_esc','Reimbursement','$today','$today','$description_esc','$msg_esc','reimbursement_status','admin',0)");

// BUGFIX (EMS-EMP-006): session flash instead of ?sent=1 — see
// my_reimbursements.php for why the URL-param version went stale.
$_SESSION['reimb_flash'] = "Reimbursement request submitted — waiting for admin approval.";
header("Location: my_reimbursements.php");
exit();
?>
