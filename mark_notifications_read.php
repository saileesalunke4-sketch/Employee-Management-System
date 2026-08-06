<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

// Mark all read
// BUGFIX: this had no for_role filter, so it marked EVERY notification in
// the whole table as read — including ones meant for employees (e.g. an
// announcement pushed moments earlier) — before the intended recipient
// ever saw them as unread. Scoped to for_role='admin' (covers super_admin
// too, since both share the same admin-targeted notifications).
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE is_read = 0 AND for_role = 'admin'");

// Redirect back to correct dashboard
$role = $_SESSION['user']['role'];
if($role == 'super_admin'){
    header("Location: super_admin_dashboard.php");
} elseif($role == 'admin'){
    header("Location: admin_dashboard.php");
} else {
    header("Location: emp_dashboard.php");
}
exit();
?>