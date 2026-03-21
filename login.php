<?php
session_start();
require 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user'] = $user;
        if($user['role'] == 'admin'){
            header("Location: admin_dashboard.php");
        } elseif($user['role'] == 'super_admin'){
            header("Location: admin_dashboard.php");
        } else {
            header("Location: emp_dashboard.php");
        }
        exit();
    } else {
        header("Location: index.php?error=1");
        exit();
    }

    } else
     {
    header("Location: emp_dashboard.php");
     }

 
?>