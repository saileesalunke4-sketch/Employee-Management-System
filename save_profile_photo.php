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
    $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
    $mime    = mime_content_type($_FILES['profile_photo']['tmp_name']);

    if(!in_array($mime, $allowed)){
        echo "<script>alert('Only JPG, PNG, GIF, WEBP allowed!'); window.history.back();</script>";
        exit();
    }

    $ext      = pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
    $filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
    $dest     = 'uploads/' . $filename;

    if(!is_dir('uploads')) mkdir('uploads', 0755, true);

    if(move_uploaded_file($_FILES['profile_photo']['tmp_name'], $dest)){
        mysqli_query($conn, "UPDATE users SET profile_photo='$filename' WHERE id='$user_id'");
        $redirect = ($role == 'admin') ? 'admin_dashboard.php' : 'emp_dashboard.php';
        echo "<script>alert('Profile photo updated!'); window.location.href='$redirect';</script>";
    }
}
?>