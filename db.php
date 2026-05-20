<?php
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 1800);
session_set_cookie_params(1800);
$host     = "localhost";
$user     = "root";
$password = "root";
$database = "emp1_db";

$conn = mysqli_connect($host, $user, $password, $database);

// If empty password fails try "root"
if(!$conn){
    $conn = mysqli_connect($host, $user, "root", $database);
}

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}
?>