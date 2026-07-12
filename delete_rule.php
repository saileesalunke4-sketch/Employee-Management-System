<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';

$id       = (int) $_GET['id'];
$redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'admin_rules.php';

if(mysqli_query($conn, "DELETE FROM rules WHERE rule_id=$id")){
    echo "<script>alert('Rule deleted!'); window.location.href='{$redirect}';</script>";
} else {
    echo "<script>alert('Failed to delete!'); window.history.back();</script>";
}
?>
