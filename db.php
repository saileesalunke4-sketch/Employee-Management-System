<?php
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