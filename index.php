<?php
session_start();
if(isset($_SESSION['user'])){
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EMS Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-left">
            <div class="brand">
                <h1>EMS</h1>
                <p>Employee Management System</p>
            </div>
            <div class="features">
                <div class="feat">Attendance & Leave Tracking</div>
                <div class="feat">Salary & Payslip Management</div>
                <div class="feat">Performance & Task Reports</div>
            </div>
        </div>
        <div class="login-right">
            <h2>Welcome Back</h2>
            <p class="sub">Sign in to your account</p>

            <?php if(isset($_GET['error'])){ ?>
                <div class="error">Invalid email or password!</div>
            <?php } ?>

            <form action="login.php" method="POST">
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email"
                    placeholder="Enter your email" required>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password"
                    placeholder="Enter your password" required>
                </div>
                <div class="forgot">
                   <a href="forgot_password.php">Forgot Password?</a>
                </div>
                <button type="submit">Sign In</button>
            </form>
        </div>
    </div>

<script>
document.querySelector('form').addEventListener('submit', function(e){

    const email    = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();

    // Check blank fields
    if(email === '' || password === ''){
        e.preventDefault();
        showError('Email and password cannot be blank!');
        return;
    }

    // Password must have: 1 uppercase, 1 number, 1 special character
    const passRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/;
    if(!passRegex.test(password)){
        e.preventDefault();
        showError('Password must have uppercase letter, number & special character (e.g. Pass@123)');
        return;
    }
});

function showError(msg){
    const old = document.querySelector('.error');
    if(old) old.remove();

    const div = document.createElement('div');
    div.className = 'error';
    div.innerText = msg;

    const form = document.querySelector('form');
    form.parentNode.insertBefore(div, form);
}
</script>
</body>
</html>