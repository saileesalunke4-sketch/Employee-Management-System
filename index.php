<?php
session_start();
if(isset($_SESSION['user'])){
    $role = $_SESSION['user']['role'];
    if($role === 'admin'){
        header("Location: admin_dashboard.php");
    } elseif($role === 'super_admin'){
        header("Location: super_admin_dashboard.php");
    } else {
        header("Location: emp_dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Sign In - Aller EMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="style.css">
    <style>
        html,body{ height:100%; }
        body {
            min-height: 100vh;
            background:
                radial-gradient(560px circle at 8% 8%, rgba(79,70,229,.07), transparent 60%),
                radial-gradient(520px circle at 94% 92%, rgba(13,148,136,.07), transparent 60%),
                var(--canvas, #F7F8FA);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding: 24px;
        }
        .login-container {
            display: flex;
            width: 940px;
            max-width: 100%;
            min-height: 580px;
            border-radius: 24px;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 30px 70px -20px rgba(16,24,40,0.18), 0 2px 8px rgba(16,24,40,0.04);
            border: 1px solid #EEF0F3;
            position: relative;
            z-index: 1;
            animation: containerIn 0.5s cubic-bezier(0.16,1,0.3,1) both;
        }

        /* ---- Left brand panel ---- */
        .login-left {
            flex: 1;
            background: linear-gradient(160deg, #EEF0FF 0%, #F6F5FF 55%, #FFFFFF 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 52px 44px;
            position: relative;
            overflow: hidden;
            border-right: 1px solid #EEF0F3;
        }
        .login-left::before {
            content: ''; position: absolute; width: 260px; height: 260px;
            background: rgba(79,70,229,0.10); border-radius: 50%;
            top: -70px; right: -70px; animation: ambientDrift 14s ease-in-out infinite;
        }
        .login-left::after {
            content: ''; position: absolute; width: 190px; height: 190px;
            background: rgba(13,148,136,0.10); border-radius: 50%;
            bottom: -50px; left: -50px; animation: ambientDrift 14s ease-in-out infinite 3s;
        }
        .logo-wrap { position: relative; z-index: 1; margin-bottom: 34px; animation: fadeInUp 0.5s ease 0.1s both; }
        .logo-wrap .mark { display:flex; align-items:center; gap:12px; margin-bottom:18px; }
        .logo-wrap .mark img{ width:44px; height:44px; border-radius:12px; object-fit:cover; box-shadow:0 6px 18px rgba(79,70,229,.28); }
        .logo-wrap .mark b{ font-size:19px; font-weight:800; color:#14161A; letter-spacing:-.3px; display:block; }
        .logo-wrap .mark span{ font-size:11px; color:#9AA1AC; font-weight:600; letter-spacing:.6px; text-transform:uppercase; }
        .logo-wrap h1{ font-size: 27px; font-weight: 800; color: #14161A; letter-spacing: -0.5px; line-height:1.3; }
        .logo-wrap p{ font-size: 13.5px; color: #666D7A; margin-top: 8px; line-height:1.6; max-width:340px; }

        .features { position: relative; z-index: 1; display:flex; flex-direction:column; gap:10px; }
        .feat {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.7);
            border: 1px solid #E5E7EB;
            border-radius: 14px;
            color: #40464F;
            font-size: 13px; font-weight: 600;
            transition: all 0.25s ease;
            animation: fadeInUp 0.45s ease both;
        }
        .feat:nth-child(1){ animation-delay:.2s; } .feat:nth-child(2){ animation-delay:.28s; }
        .feat:nth-child(3){ animation-delay:.36s; } .feat:nth-child(4){ animation-delay:.44s; }
        .feat:hover { border-color:#C7C9F2; transform: translateX(3px); box-shadow: var(--shadow-sm); }
        .feat-icon { width:30px; height:30px; border-radius:9px; background:#EEF0FF; color:#4F46E5; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

        /* ---- Right form panel ---- */
        .login-right {
            flex: 1; background: #ffffff;
            display: flex; flex-direction: column; justify-content: center;
            padding: 52px 48px;
        }
        .login-right .welcome { font-size: 25px; font-weight: 800; color: #14161A; margin-bottom: 6px; letter-spacing:-.4px; animation: fadeInUp 0.5s ease 0.15s both; }
        .login-right .sub { font-size: 13px; color: #9AA1AC; margin-bottom: 28px; animation: fadeInUp 0.5s ease 0.15s both; }

        .error-box {
            background: #FDEDED; border: 1px solid #F6C8C8;
            color: #DC2626; padding: 11px 16px; border-radius: 12px;
            font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px; font-weight:600;
        }
        .inp-group { margin-bottom: 18px; animation: fadeInUp 0.45s ease both; }
        .inp-group:nth-of-type(1) { animation-delay: 0.25s; }
        .inp-group:nth-of-type(2) { animation-delay: 0.32s; }
        .inp-group label { display: block; font-size: 11.5px; font-weight: 700; color: #666D7A; margin-bottom: 8px; letter-spacing: 0.5px; text-transform: uppercase; }
        .inp-wrap { position: relative; }
        .inp-wrap input {
            width: 100%; padding: 13px 16px 13px 44px;
            background: #F7F8FA; border: 1.5px solid #E5E7EB;
            border-radius: 12px; color: #14161A; font-size: 14px; outline: none; transition: all 0.2s ease;
        }
        .inp-wrap input:focus { border-color: #4F46E5; background: #fff; box-shadow: 0 0 0 4px #EEF0FF; }
        .inp-wrap .inp-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9AA1AC; display:flex; }
        .inp-wrap input:focus ~ .inp-icon,
        .inp-wrap:focus-within .inp-icon { color:#4F46E5; }
        .forgot-row { text-align: right; margin-bottom: 22px; margin-top: -6px; }
        .forgot-row a { font-size: 12px; color: #4F46E5; text-decoration: none; font-weight: 600; }
        .forgot-row a:hover { text-decoration: underline; }
        .login-btn {
            width: 100%; padding: 14px;
            background: #4F46E5;
            border: none; border-radius: 12px; color: white;
            font-size: 14.5px; font-weight: 700; cursor: pointer; transition: all 0.2s ease; letter-spacing: 0.2px;
            display:flex; align-items:center; justify-content:center; gap:8px;
            animation: fadeInUp 0.45s ease 0.4s both;
        }
        .login-btn:hover { background:#4338CA; transform: translateY(-1px); box-shadow: 0 10px 24px -6px rgba(79,70,229,.45); }
        .bottom-note { text-align: center; margin-top: 26px; font-size: 11px; color: #9AA1AC; }
        .bottom-links { display: flex; justify-content: center; gap: 20px; margin-top: 10px; }
        .bottom-links a { font-size: 12px; color: #666D7A; text-decoration: none; font-weight:600; }
        .bottom-links a:hover { color:#4F46E5; text-decoration: underline; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(20,22,26,0.45); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(2px); }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:20px; padding:38px; width:560px; max-width:90%; position:relative; max-height:80vh; overflow-y:auto; border:1px solid #EEF0F3; box-shadow: 0 30px 70px -20px rgba(16,24,40,.25); animation: containerIn 0.3s cubic-bezier(0.16,1,0.3,1) both; }
        .modal-box h2 { font-size:19px; color:#14161A; margin-bottom:14px; font-weight:800; display:flex; align-items:center; gap:10px; }
        .modal-box p { font-size:13.5px; color:#666D7A; line-height:1.8; margin-bottom:12px; }
        .modal-close { position:absolute; top:18px; right:20px; font-size:20px; cursor:pointer; color:#9AA1AC; background:none; border:none; }
        .modal-close:hover { color:#14161A; }
        .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:14px; }
        .about-card { background:#F7F8FA; border-radius:14px; padding:16px; border:1px solid #EEF0F3; }
        .about-card h4 { font-size:13px; color:#14161A; margin-bottom:6px; font-weight:700; }
        .about-card p { font-size:12px; color:#666D7A; margin:0; }
        .contact-field { margin-bottom:14px; }
        .contact-field label { display:block; font-size:11.5px; font-weight:700; color:#666D7A; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
        .contact-field input, .contact-field textarea {
            width:100%; padding:10px 14px; border:1.5px solid #E5E7EB;
            border-radius:10px; font-size:13px; color:#14161A; outline:none; transition:all 0.2s ease; font-family:inherit;
        }
        .contact-field input:focus, .contact-field textarea:focus { border-color:#4F46E5; box-shadow:0 0 0 4px #EEF0FF; }
        .contact-field textarea { resize:vertical; min-height:80px; }
        .contact-submit { background:#4F46E5; color:white; border:none; padding:11px 24px; border-radius:10px; font-size:13.5px; cursor:pointer; font-weight:700; }
        .contact-submit:hover { background:#4338CA; }

        @keyframes containerIn { from{ opacity:0; transform:translateY(20px) scale(0.98);} to{ opacity:1; transform:translateY(0) scale(1);} }
        @keyframes fadeInUp { from{ opacity:0; transform:translateY(14px);} to{ opacity:1; transform:translateY(0);} }
        @keyframes ambientDrift { 0%,100%{ transform:translate(0,0) scale(1);} 50%{ transform:translate(14px,-14px) scale(1.06);} }

        @media (max-width:760px){
            .login-left{ display:none; }
            .login-container{ min-height:auto; }
            .login-right{ padding:40px 26px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Left Panel -->
    <div class="login-left">
        <div class="logo-wrap">
            <div class="mark">
                <img src="allerlogo.png" alt="Aller Technologies">
                <div>
                    <b>Aller EMS</b>
                    <span>Employee Management System</span>
                </div>
            </div>
            <h1>Everything HR,<br>in one calm place.</h1>
            <p>Attendance, leave, payroll and performance &mdash; one unified workspace for your whole organization.</p>
        </div>
        <div class="features">
            <div class="feat"><span class="feat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.2" y="4.5" width="17.6" height="16" rx="2"/><path d="M3.2 9.5h17.6M8 3v3M16 3v3"/></svg></span> Attendance &amp; Leave Tracking</div>
            <div class="feat"><span class="feat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6.5" width="18" height="13" rx="2.2"/><path d="M3 10h18"/><circle cx="16.6" cy="14.6" r="1.1"/></svg></span> Salary &amp; Payslip Management</div>
            <div class="feat"><span class="feat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V10.5M12 20V4M20 20v-6.5"/><path d="M2.5 20h19"/></svg></span> Performance &amp; Task Reports</div>
            <div class="feat"><span class="feat-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 0 1 12 0c0 4.2 1.2 6 2 6.8H4c.8-.8 2-2.6 2-6.8Z"/><path d="M10 19.5a2.2 2.2 0 0 0 4 0"/></svg></span> Real-time Notifications</div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
        <p class="welcome">Welcome back</p>
        <p class="sub">Sign in with your work email to continue</p>

        <?php if(isset($_GET['error']) && $_GET['error'] === 'locked'): ?>
            <div class="error-box">Too many failed attempts. Account temporarily locked &mdash; please try again in <?php echo (int)($_GET['minutes'] ?? 15); ?> minute(s).</div>
        <?php elseif(isset($_GET['error'])): ?>
            <div class="error-box">Invalid email or password. Please try again.</div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="loginForm">
            <div class="inp-group">
                <label>Email Address</label>
                <div class="inp-wrap">
                    <span class="inp-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="14" rx="2.4"/><path d="M3.5 6.5 12 13l8.5-6.5"/></svg></span>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            <div class="inp-group">
                <label>Password</label>
                <div class="inp-wrap">
                    <span class="inp-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4.5" y="10.5" width="15" height="10" rx="2.2"/><path d="M7.5 10.5V7.2a4.5 4.5 0 0 1 9 0v3.3"/></svg></span>
                    <input type="password" name="password" id="passwordField" placeholder="Enter your password" required>
                </div>
            </div>
            <div class="forgot-row">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            <button type="submit" class="login-btn">Sign In
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
            </button>
        </form>

        <p class="bottom-note">&copy; <?php echo date('Y'); ?> Aller Technologies Pvt. Ltd. All rights reserved.</p>
        <div class="bottom-links">
            <a href="#" onclick="openModal('aboutModal')">About Us</a>
            <a href="#" onclick="openModal('contactModal')">Contact Us</a>
        </div>
    </div>
</div>

<!-- About Us Modal -->
<div class="modal-overlay" id="aboutModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('aboutModal')">&times;</button>
        <h2>About Aller Technologies</h2>
        <p>Aller Technologies is a dynamic IT company dedicated to building innovative software solutions for modern businesses. Our Employee Management System (EMS) streamlines HR operations, attendance tracking, payroll, and performance management.</p>
        <p><strong>Our Mission:</strong> To simplify workforce management through smart, scalable, and user-friendly technology.</p>
        <div class="about-grid">
            <div class="about-card"><h4>Our Vision</h4><p>Empowering organizations with cutting-edge HR technology.</p></div>
            <div class="about-card"><h4>Innovation</h4><p>Continuously improving our platform with the latest tech.</p></div>
            <div class="about-card"><h4>Support</h4><p>24/7 dedicated support for all our clients.</p></div>
            <div class="about-card"><h4>Security</h4><p>Enterprise-grade security for your sensitive data.</p></div>
        </div>
    </div>
</div>

<!-- Contact Us Modal -->
<div class="modal-overlay" id="contactModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('contactModal')">&times;</button>
        <h2>Contact Us</h2>
        <p>Have questions? We'd love to hear from you. Send us a message!</p>
        <div class="contact-field">
            <label>Your Name</label>
            <input type="text" placeholder="Enter your name">
        </div>
        <div class="contact-field">
            <label>Email Address</label>
            <input type="email" placeholder="Enter your email">
        </div>
        <div class="contact-field">
            <label>Message</label>
            <textarea placeholder="Write your message here..."></textarea>
        </div>
        <button class="contact-submit" onclick="submitContact()">Send Message</button>
    </div>
</div>

<script>
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(m=>{
    m.addEventListener('click',function(e){ if(e.target===this) this.classList.remove('open'); });
});
function submitContact(){
    alert('Thank you! Your message has been sent. We will get back to you shortly.');
    closeModal('contactModal');
}
document.getElementById('loginForm').addEventListener('submit', function(e){
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();
    if(email===''||password===''){ e.preventDefault(); showError('Email and password cannot be blank!'); return; }
    const passRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/;
    if(!passRegex.test(password)){ e.preventDefault(); showError('Password must have uppercase letter, number & special character (e.g. Pass@123)'); return; }
});
function showError(msg){
    const old = document.querySelector('.error-box');
    if(old) old.remove();
    const div = document.createElement('div');
    div.className = 'error-box';
    div.innerHTML = msg;
    const form = document.getElementById('loginForm');
    form.parentNode.insertBefore(div, form);
}
</script>
</body>
</html>
