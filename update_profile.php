<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'employee'){
    header("Location: index.php");
    exit();
}

$user_id          = $_SESSION['user']['id'];
$first_name       = mysqli_real_escape_string($conn, $_POST['first_name'] ?? '' );
$last_name        = mysqli_real_escape_string($conn, $_POST['last_name'] ?? '' );
$contact          = mysqli_real_escape_string($conn, $_POST['contact']) ?? '' ;
$designation      = mysqli_real_escape_string($conn, $_POST['designation'] ?? '' );
$blood_group      = mysqli_real_escape_string($conn, $_POST['blood_group']  ?? '' );
$dob              = mysqli_real_escape_string($conn, $_POST['dob'] ?? '' );
$address          = mysqli_real_escape_string($conn, $_POST['address'] ?? '' );
$religion         = mysqli_real_escape_string($conn, $_POST['religion']?? '' );
$caste            = mysqli_real_escape_string($conn, $_POST['caste'] ?? '' );
$sub_caste        = mysqli_real_escape_string($conn, $_POST['sub_caste'] ?? '' );
$permanent_address= mysqli_real_escape_string($conn, $_POST['permanent_address'] ?? '' );
$common_address   = mysqli_real_escape_string($conn, $_POST['common_address'] ?? '' );

$query = "UPDATE employees SET 
            first_name        = '$first_name',
            last_name         = '$last_name',
            contact           = '$contact',
            designation       = '$designation',
            blood_group       = '$blood_group',
            dob               = '$dob',
            address           = '$address',
            religion          = '$religion',
            caste             = '$caste',
            sub_caste         = '$sub_caste',
            permanent_address = '$permanent_address',
            common_address    = '$common_address'
          WHERE user_id = '$user_id'";

if(mysqli_query($conn, $query)){
    $full_name = mysqli_real_escape_string($conn, $first_name.' '.$last_name);
    mysqli_query($conn, "UPDATE users SET name='$full_name' WHERE id='$user_id'");

    echo "<script>alert('Profile updated successfully!'); window.location.href='emp_dashboard.php';</script>";
} else {
    echo "<script>alert('Failed to update profile!'); window.history.back();</script>";
}
?>