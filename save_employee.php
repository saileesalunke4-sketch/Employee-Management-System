<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name = mysqli_real_escape_string($conn, $_POST['last_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact']);
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $blood_group = $_POST['blood_group'];
    $dob = $_POST['dob'];
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Step 1 - Check email already exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Email already exists!'); window.history.back();</script>";
        exit();
    }

    // Step 2 - Users table mein save karo
    $user_query = "INSERT INTO users (name, email, password, role) 
                   VALUES ('$name', '$email', '$password', '$role')";
    
    if(mysqli_query($conn, $user_query)){
        $user_id = mysqli_insert_id($conn);

        // Step 3 - Employees table mein save karo
        $emp_query = "INSERT INTO employees 
                      (user_id, first_name, last_name, contact, 
                       designation, blood_group, dob, religion, address) 
                      VALUES 
                      ('$user_id', '$first_name', '$last_name', '$contact',
                       '$designation', '$blood_group', '$dob', '$religion', '$address')";
        
        if(mysqli_query($conn, $emp_query)){
            echo "<script>alert('Employee added successfully!'); 
                  window.location.href='admin_dashboard.php';</script>";
        } else {
            echo "<script>alert('Employee details save failed!'); 
                  window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User save failed!'); 
              window.history.back();</script>";
    }
}
?>