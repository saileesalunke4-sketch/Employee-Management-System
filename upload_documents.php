<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';
$user_id    = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn,"SELECT emp_id FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];

// Create upload directory
if(!is_dir('uploads/documents')) mkdir('uploads/documents', 0777, true);

// Create table if not exists
mysqli_query($conn,"CREATE TABLE IF NOT EXISTS employee_documents (
    doc_id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id INT,
    pan_card VARCHAR(255),
    aadhar_card VARCHAR(255),
    marks_card VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

$doc_fields = ['pan_card','aadhar_card','marks_card'];
$updates    = [];

foreach($doc_fields as $field){
    if(isset($_FILES[$field]) && $_FILES[$field]['error']==0){
        $ext      = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $filename = $field.'_'.$emp_id.'_'.time().'.'.$ext;
        $dest     = 'uploads/documents/'.$filename;
        if(move_uploaded_file($_FILES[$field]['tmp_name'], $dest)){
            $updates[$field] = $filename;
        }
    }
}

if(!empty($updates)){
    // Check if record exists
    $existing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT doc_id FROM employee_documents WHERE emp_id='$emp_id'"));
    if($existing){
        $set = implode(',', array_map(fn($k,$v) => "$k='$v'", array_keys($updates), $updates));
        mysqli_query($conn,"UPDATE employee_documents SET $set WHERE emp_id='$emp_id'");
    } else {
        $cols = 'emp_id,'.implode(',', array_keys($updates));
        $vals = "'$emp_id','".implode("','", array_values($updates))."'";
        mysqli_query($conn,"INSERT INTO employee_documents ($cols) VALUES ($vals)");
    }
    echo "<script>alert('Documents uploaded successfully!'); window.location.href='emp_profile.php';</script>";
} else {
    echo "<script>alert('Please select at least one file!'); window.history.back();</script>";
}
?>
