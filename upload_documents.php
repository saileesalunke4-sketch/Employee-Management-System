<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];

// Get emp_id
$emp_result = mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];

$allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
$max_size      = 2 * 1024 * 1024; // 2MB max

function uploadFile($file_key, $emp_id){
    global $allowed_types, $max_size;

    if(!isset($_FILES[$file_key]) || $_FILES[$file_key]['error'] == 4){
        return null; // No file uploaded
    }

    $file     = $_FILES[$file_key];
    $type     = $file['type'];
    $size     = $file['size'];
    $tmp_name = $file['tmp_name'];

    // Validate type
    if(!in_array($type, $allowed_types)){
        return 'invalid_type';
    }

    // Validate size
    if($size > $max_size){
        return 'too_large';
    }

    // Generate unique filename
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = $emp_id . '_' . $file_key . '_' . time() . '.' . $ext;
    $dest     = 'uploads/' . $filename;

    if(move_uploaded_file($tmp_name, $dest)){
        return $filename;
    }

    return null;
}

$pan_card    = uploadFile('pan_card', $emp_id);
$aadhar_card = uploadFile('aadhar_card', $emp_id);
$marks_card  = uploadFile('marks_card', $emp_id);

// Check for errors
if($pan_card === 'invalid_type' || $aadhar_card === 'invalid_type' || $marks_card === 'invalid_type'){
    echo "<script>alert('Only JPG, PNG and PDF files are allowed!'); window.history.back();</script>";
    exit();
}

if($pan_card === 'too_large' || $aadhar_card === 'too_large' || $marks_card === 'too_large'){
    echo "<script>alert('File size must be less than 2MB!'); window.history.back();</script>";
    exit();
}

// Build update query only for uploaded files
$updates = [];
if($pan_card)    $updates[] = "pan_card='$pan_card'";
if($aadhar_card) $updates[] = "aadhar_card='$aadhar_card'";
if($marks_card)  $updates[] = "marks_card='$marks_card'";

if(!empty($updates)){
    $query = "UPDATE employees SET " . implode(', ', $updates) . " WHERE emp_id='$emp_id'";
    mysqli_query($conn, $query);
    echo "<script>alert('Documents uploaded successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Please select at least one file!'); window.history.back();</script>";
}
?>