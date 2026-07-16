<?php
// save_hr_request.php — DEPRECATED. Employees can no longer submit
// department/designation/location change requests. See save_role_update.php,
// which is used by Admin/Super Admin to make these changes directly.
session_start();
header("Location: emp_dashboard.php");
exit();
