<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'admin'){
    header("Location: index.php");
    exit();
}

$success = "";
$error = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $contact = trim($_POST['contact'] ?? '');

    // BUGFIX (EMS-ADM-001): these fields were only SQL-escaped before, with
    // no actual format validation — so "abc" or "123" would be silently
    // accepted as a contact number, and numbers/symbols would be accepted
    // as a name. Validate format before anything gets saved.
    $name_pattern = "/^[a-zA-Z\s.'-]{2,100}$/";
    if($name === '' || !preg_match($name_pattern, $name)){
        echo "<script>alert('Please enter a valid Name (letters only, no numbers or symbols).'); window.history.back();</script>";
        exit();
    }
    if($first_name === '' || !preg_match($name_pattern, $first_name)){
        echo "<script>alert('Please enter a valid First Name (letters only, no numbers or symbols).'); window.history.back();</script>";
        exit();
    }
    if($last_name === '' || !preg_match($name_pattern, $last_name)){
        echo "<script>alert('Please enter a valid Last Name (letters only, no numbers or symbols).'); window.history.back();</script>";
        exit();
    }
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        echo "<script>alert('Please enter a valid email address.'); window.history.back();</script>";
        exit();
    }
    // BUGFIX: was '{10,15}' — allowed up to 15 digits through. A contact
    // number here should be exactly 10 digits.
    if(!preg_match('/^[0-9]{10}$/', $contact)){
        echo "<script>alert('Please enter a valid 10-digit contact number (digits only).'); window.history.back();</script>";
        exit();
    }

    // BUGFIX (EMS-ADM-002): the old code relied on this INSERT failing due
    // to a UNIQUE constraint on users.email — but no such constraint exists
    // in the database, so a duplicate email was silently accepted every
    // time (the "Email already exists!" branch was dead code, unreachable).
    // Checking explicitly here is what actually catches it.
    $email_check = mysqli_real_escape_string($conn, $email);
    $existing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$email_check'"));
    if($existing){
        echo "<script>alert('Email already exists! Please use a different email address.'); window.history.back();</script>";
        exit();
    }

    $name = mysqli_real_escape_string($conn, $name);
    $email = mysqli_real_escape_string($conn, $email);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = in_array($_POST['role'], ['employee','admin','super_admin'], true) ? $_POST['role'] : 'employee';
    $first_name = mysqli_real_escape_string($conn, $first_name);
    $last_name = mysqli_real_escape_string($conn, $last_name);
    $contact = mysqli_real_escape_string($conn, $contact);
    $designation = mysqli_real_escape_string($conn, $_POST['designation']);
    $blood_group = mysqli_real_escape_string($conn, $_POST['blood_group']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $religion = mysqli_real_escape_string($conn, $_POST['religion']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // BUGFIX (Employee-024): flag this account to be forced through
    // Change Password on its first login, since the admin just set a
    // temporary password for them above.
    $user_query = "INSERT INTO users (name, email, password, role, must_change_password) VALUES ('$name', '$email', '$password', '$role', 1)";
    if(mysqli_query($conn, $user_query)){
        $user_id = mysqli_insert_id($conn);
        $emp_query = "INSERT INTO employees (user_id, first_name, last_name, contact, designation, blood_group, dob, religion, address) VALUES ('$user_id','$first_name','$last_name','$contact','$designation','$blood_group','$dob','$religion','$address')";
        if(mysqli_query($conn, $emp_query)){
            // Auto-generate a human-friendly Employee ID (e.g. EMP0001) from the new emp_id
            $new_emp_id = mysqli_insert_id($conn);
            $employee_code = 'EMP' . str_pad($new_emp_id, 4, '0', STR_PAD_LEFT);
            mysqli_query($conn, "UPDATE employees SET employee_code='$employee_code' WHERE emp_id=$new_emp_id");

            $success = "Employee added successfully! Employee ID: $employee_code";
        }
    } else {
        $error = "Email already exists!";
    }
}
?>
<?php $page_title = "Add New Employee"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Add Employee - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard admin-theme">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">

        <?php if($success){ ?>
            <div style="background:#dcfce7;color:#16a34a;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;">&#10003; <?php echo $success; ?></div>
        <?php } ?>
        <?php if($error){ ?>
            <div style="background:#fee2e2;color:#dc2626;padding:12px 20px;border-radius:10px;margin-bottom:20px;font-weight:600;">&#10007; <?php echo $error; ?></div>
        <?php } ?>

        <div class="form-card">
            <form action="add_employee.php" method="POST">
                <h3 class="section-title">Login Details</h3>
                <div class="form-grid">
                    <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="Full Name" required></div>
                    <div class="field"><label>Email</label><input type="email" name="email" placeholder="Email" required></div>
                    <div class="field"><label>Password</label><input type="password" name="password" placeholder="Password" required></div>
                    <div class="field"><label>Role</label>
                        <select name="role">
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                </div>

                <h3 class="section-title">Personal Details</h3>
                <div class="form-grid">
                    <div class="field"><label>First Name</label><input type="text" name="first_name" placeholder="First Name" required></div>
                    <div class="field"><label>Last Name</label><input type="text" name="last_name" placeholder="Last Name" required></div>
                    <div class="field"><label>Contact</label><input type="text" name="contact" placeholder="Contact Number" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" title="Enter exactly 10 digits" required></div>
                    <div class="field"><label>Designation</label><input type="text" name="designation" placeholder="Designation" required></div>
                    <div class="field"><label>Blood Group</label>
                        <select name="blood_group">
                            <option>A+</option><option>A-</option><option>B+</option><option>B-</option>
                            <option>O+</option><option>O-</option><option>AB+</option><option>AB-</option>
                        </select>
                    </div>
                    <div class="field"><label>Date of Birth</label><input type="date" name="dob" required></div>
                    <div class="field"><label>Religion</label><input type="text" name="religion" placeholder="Religion" required></div>
                    <div class="field"><label>Address</label><input type="text" name="address" placeholder="Address" required></div>
                </div>

                <button type="submit" class="submit-btn">Add Employee</button>
            </form>
        </div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
