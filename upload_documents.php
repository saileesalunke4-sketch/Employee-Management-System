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

// SECURITY: prevent any script from ever being executed if it somehow ends
// up in this folder (defense-in-depth on top of the extension/MIME checks
// below). Written once; harmless if it already exists.
$htaccess_path = 'uploads/documents/.htaccess';
if(!file_exists($htaccess_path)){
    file_put_contents($htaccess_path, "php_flag engine off\nAddHandler cgi-script .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\nOptions -ExecCGI\n");
}

// Create table if not exists
mysqli_query($conn,"CREATE TABLE IF NOT EXISTS employee_documents (
    doc_id INT AUTO_INCREMENT PRIMARY KEY,
    emp_id INT,
    pan_card VARCHAR(255),
    aadhar_card VARCHAR(255),
    marks_card VARCHAR(255),
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// SECURITY: only these document types are allowed. Both the file extension
// AND the actual file content (MIME type, detected server-side — not trusting
// what the browser/client claims) must match, so a renamed .php file cannot
// slip through as a "PDF" or "JPG".
$allowed_ext_mime = [
    'pdf'  => 'application/pdf',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png'  => 'image/png',
];
$max_file_size = 5 * 1024 * 1024; // 5 MB

$doc_fields = ['pan_card','aadhar_card','marks_card'];
$updates    = [];
$upload_errors = [];

foreach($doc_fields as $field){
    if(isset($_FILES[$field]) && $_FILES[$field]['error']==0){

        $orig_name = $_FILES[$field]['name'];
        $tmp_path  = $_FILES[$field]['tmp_name'];
        $size      = $_FILES[$field]['size'];

        $ext = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        if(!array_key_exists($ext, $allowed_ext_mime)){
            $upload_errors[] = "$field: only PDF, JPG or PNG files are allowed.";
            continue;
        }

        if($size > $max_file_size){
            $upload_errors[] = "$field: file is too large (max 5 MB).";
            continue;
        }

        // Verify the file's REAL content type server-side (finfo reads the
        // actual bytes, not the filename), so a .php file renamed to .pdf
        // is still rejected.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $real_mime = finfo_file($finfo, $tmp_path);
        finfo_close($finfo);

        if($real_mime !== $allowed_ext_mime[$ext]){
            $upload_errors[] = "$field: file content does not match a valid $ext file.";
            continue;
        }

        // Filename is fully server-generated (field + emp_id + timestamp + our
        // own validated extension) — the original filename is never reused,
        // so there's no path-traversal or double-extension risk.
        $filename = $field.'_'.$emp_id.'_'.time().'.'.$ext;
        $dest     = 'uploads/documents/'.$filename;

        if(move_uploaded_file($tmp_path, $dest)){
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
    if(!empty($upload_errors)){
        $err_msg = implode('\\n', $upload_errors);
        echo "<script>alert('Some documents were uploaded, but others were rejected:\\n$err_msg'); window.location.href='emp_profile.php';</script>";
    } else {
        echo "<script>alert('Documents uploaded successfully!'); window.location.href='emp_profile.php';</script>";
    }
} elseif(!empty($upload_errors)){
    $err_msg = implode('\\n', $upload_errors);
    echo "<script>alert('Upload failed:\\n$err_msg'); window.history.back();</script>";
} else {
    echo "<script>alert('Please select at least one file!'); window.history.back();</script>";
}
?>
