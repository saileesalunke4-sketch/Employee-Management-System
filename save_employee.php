<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}

if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'], true)){
    header("Location: index.php"); exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $name        = mysqli_real_escape_string($conn, $_POST['name']);
    $email       = mysqli_real_escape_string($conn, $_POST['email']);
    $plain_pass  = $_POST['password']; // save for welcome mail
    $password    = password_hash($plain_pass, PASSWORD_DEFAULT);
    $role        = in_array($_POST['role'], ['employee','admin','super_admin'], true) ? $_POST['role'] : 'employee';
    $first_name  = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name   = mysqli_real_escape_string($conn, $_POST['last_name']);
    $contact     = mysqli_real_escape_string($conn, $_POST['contact']);
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $dob         = mysqli_real_escape_string($conn, $_POST['dob']);
    $religion    = mysqli_real_escape_string($conn, $_POST['religion']);
    $address     = mysqli_real_escape_string($conn, $_POST['address']);

    // Check email exists
    $check = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        echo "<script>alert('Email already exists!'); window.history.back();</script>";
        exit();
    }

    // Save to users table
    $user_query = "INSERT INTO users (name, email, password, role)
                   VALUES ('$name', '$email', '$password', '$role')";

    if(mysqli_query($conn, $user_query)){
        $user_id = mysqli_insert_id($conn);

        // Save to employees table
        $emp_query = "INSERT INTO employees
                      (user_id, first_name, last_name, contact,
                       designation, blood_group, dob, religion, address)
                      VALUES
                      ('$user_id', '$first_name', '$last_name', '$contact',
                       '$designation', '$blood_group', '$dob', '$religion', '$address')";

        if(mysqli_query($conn, $emp_query)){

            // ===== WELCOME MAIL TO EMPLOYEE =====
            $subject = " Welcome to Aller Technologies — Your EMS Account is Ready!";
            $body = "
            <div style='font-family:Arial,sans-serif;max-width:600px;margin:0 auto;'>
                <div style='background:#1a3a6e;padding:20px;text-align:center;border-radius:10px 10px 0 0;'>
                    <h2 style='color:white;margin:0;'>Aller Technologies — EMS</h2>
                    <p style='color:rgba(255,255,255,0.8);margin:4px 0 0;font-size:13px;'>Employee Management System</p>
                </div>
                <div style='background:#f9fafb;padding:24px;border-radius:0 0 10px 10px;border:1px solid #e5e7eb;'>
                    <h3 style='color:#1a3a6e;'> Welcome to Aller Technologies!</h3>
                    <p>Dear <strong>{$first_name} {$last_name}</strong>,</p>
                    <p>We are delighted to welcome you to <strong>Aller Technologies Pvt. Ltd.</strong> Your EMS account has been created successfully.</p>

                    <div style='background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:16px;margin:16px 0;'>
                        <h4 style='color:#1d4ed8;margin:0 0 12px;'> Your Login Credentials</h4>
                        <table style='width:100%;border-collapse:collapse;'>
                            <tr><td style='padding:6px 0;font-weight:600;width:40%;'>Login URL</td><td style='padding:6px 0;'><a href='http://localhost/emp' style='color:#2563eb;'>http://localhost/emp</a></td></tr>
                            <tr><td style='padding:6px 0;font-weight:600;'>Email</td><td style='padding:6px 0;'>{$email}</td></tr>
                            <tr><td style='padding:6px 0;font-weight:600;'>Password</td><td style='padding:6px 0;'>{$plain_pass}</td></tr>
                            <tr><td style='padding:6px 0;font-weight:600;'>Role</td><td style='padding:6px 0;'>".ucfirst($role)."</td></tr>
                        </table>
                    </div>

                    <table style='width:100%;border-collapse:collapse;margin:16px 0;'>
                        <tr style='background:#f3f4f6;'><td style='padding:8px 12px;font-weight:600;'>Designation</td><td style='padding:8px 12px;'>{$designation}</td></tr>
                        <tr><td style='padding:8px 12px;font-weight:600;'>Contact</td><td style='padding:8px 12px;'>{$contact}</td></tr>
                    </table>

                    <p style='background:#fef3c7;padding:12px;border-radius:8px;font-size:13px;color:#92400e;'>⚠️ Please change your password after first login for security.</p>
                    <p style='color:#6b7280;font-size:12px;margin-top:16px;'>This is an auto-generated email from EMS — Aller Technologies.</p>
                </div>
            </div>";

            sendEMSMail($email, $first_name.' '.$last_name, $subject, $body);

            // BUGFIX: hardcoded to admin_dashboard.php regardless of who
            // submitted the form; a super_admin would land in the Admin
            // portal instead of their own employee list.
            $redirect = ($_SESSION['user']['role'] === 'super_admin') ? 'all_employees.php' : 'admin_dashboard.php';
            echo "<script>alert('Employee added successfully! Welcome email sent.'); window.location.href='{$redirect}';</script>";
        } else {
            echo "<script>alert('Employee details save failed!'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('User save failed!'); window.history.back();</script>";
    }
}
?>
