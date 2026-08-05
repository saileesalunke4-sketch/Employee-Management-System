<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

// BUGFIX: this always sent everyone back to admin_dashboard.php, even a
// super_admin who was deleting a holiday from their own Holiday Calendar
// page (sa_holidays.php) — the page itself already passes back a
// &redirect=... value, it just wasn't being used. Only allow a small
// whitelist of known pages as the redirect target (never trust a raw
// user-supplied URL) and fall back to whichever page matches the caller's
// role if nothing valid was supplied.
$allowed_redirects = ['admin_holidays.php', 'sa_holidays.php'];
$redirect = $_GET['redirect'] ?? '';
if(!in_array($redirect, $allowed_redirects, true)){
    $redirect = ($_SESSION['user']['role'] === 'super_admin') ? 'sa_holidays.php' : 'admin_holidays.php';
}

if(mysqli_query($conn, "DELETE FROM holidays WHERE id='$id'")){
    echo "<script>alert('Holiday deleted!'); window.location.href='{$redirect}';</script>";
} else {
    echo "<script>alert('Failed to delete!'); window.history.back();</script>";
}
?>