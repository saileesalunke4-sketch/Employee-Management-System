<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];

$query = "DELETE FROM leave_types WHERE id=$id";

// BUGFIX: always redirected to admin_dashboard.php instead of back to the
// Leave Types page (leave_types.php) that this action is actually
// performed from — the same "dumped into the wrong portal" issue as the
// other handler files, plus a stray leftover "yes" was being printed after
// the closing PHP tag.
if(mysqli_query($conn, $query)){
    echo "<script>alert('Leave type deleted successfully!'); window.location.href='leave_types.php';</script>";
} else {
    echo "<script>alert('Failed to delete!'); window.history.back();</script>";
}
?>