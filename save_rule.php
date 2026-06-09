<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$rule_id    = $_POST['rule_id'];
$category   = mysqli_real_escape_string($conn, $_POST['category']);
$title      = mysqli_real_escape_string($conn, $_POST['title']);
$description= mysqli_real_escape_string($conn, $_POST['description']);

if(!empty($rule_id)){
    // Update
    $q = "UPDATE rules SET category='$category', title='$title', description='$description' WHERE rule_id='$rule_id'";
} else {
    // Insert
    $q = "INSERT INTO rules (category, title, description) VALUES ('$category','$title','$description')";
}

if(mysqli_query($conn, $q)){
    $redirect = (isset($_POST['redirect']) && $_POST['redirect']=='sa_rules.php') ? 'sa_rules.php' : 'admin_rules.php';
    echo "<script>alert('Rule saved successfully!'); window.location.href='{$redirect}';</script>";
} else {
    echo "<script>alert('Failed to save rule!'); window.history.back();</script>";
}
?>
