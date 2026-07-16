<?php
// hr_requests.php — DEPRECATED for employees.
// Department/Designation/Location changes are now decided directly by
// Admin / Super Admin (see admin_hr_requests.php), based on employee
// performance, not on employee-submitted requests. This page now just
// redirects any old links/bookmarks back to the employee dashboard.
session_start();
header("Location: emp_dashboard.php");
exit();
