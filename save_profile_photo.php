<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user']['id'];
$role    = $_SESSION['user']['role'];

if(isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0){
    $allowed_mime_ext = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    $mime = mime_content_type($_FILES['profile_photo']['tmp_name']);

    if(!array_key_exists($mime, $allowed_mime_ext)){
        echo "<script>alert('Only JPG, PNG, GIF, WEBP allowed!'); window.history.back();</script>";
        exit();
    }

    if($_FILES['profile_photo']['size'] > 5 * 1024 * 1024){
        echo "<script>alert('File is too large (max 5 MB).'); window.history.back();</script>";
        exit();
    }

    // SECURITY: extension is derived from the verified MIME type, NOT from
    // the client-supplied filename — this prevents a disguised file (e.g. an
    // image with embedded PHP code, saved with a .php name) from keeping a
    // dangerous extension.
    $ext      = $allowed_mime_ext[$mime];
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    $dest     = 'uploads/' . $filename;

    if(!is_dir('uploads')) mkdir('uploads', 0755, true);

    // Defense-in-depth: make sure no script can ever execute from uploads/
    $htaccess_path = 'uploads/.htaccess';
    if(!file_exists($htaccess_path)){
        file_put_contents($htaccess_path, "php_flag engine off\nAddHandler cgi-script .php .php3 .php4 .php5 .phtml .pl .py .jsp .asp .sh .cgi\nOptions -ExecCGI\n");
    }

    if(move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)){
        mysqli_query($conn, "UPDATE users SET profile_photo='$filename' WHERE id='$user_id'");
        $redirect = ($role == 'admin') ? 'admin_dashboard.php' : 'emp_dashboard.php';
        echo "<script>alert('Profile photo updated!'); window.location.href='$redirect';</script>";
    }
}
?>