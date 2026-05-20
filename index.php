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
    <title>EMS Login - Aller Technologies</title>
    <link rel="stylesheet" href="style.css">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Arial, sans-serif; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0a1628 0%, #0d2144 50%, #0a1628 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            flex-direction: column;
        }
        body::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: rgba(59,130,246,0.06);
            border-radius: 50%;
            top: -200px; left: -200px;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: rgba(99,102,241,0.06);
            border-radius: 50%;
            bottom: -100px; right: -100px;
        }
        .login-container {
            display: flex;
            width: 900px;
            min-height: 560px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.4);
            position: relative;
            z-index: 1;
        }
        .login-left {
            flex: 1;
            background: linear-gradient(160deg, #1a3a6e 0%, #0d2144 60%, #0a1628 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 40px;
            position: relative;
            overflow: hidden;
        }
        .login-left::before {
            content: '';
            position: absolute;
            width: 300px; height: 300px;
            background: rgba(59,130,246,0.08);
            border-radius: 50%;
            top: -80px; right: -80px;
        }
        .login-left::after {
            content: '';
            position: absolute;
            width: 200px; height: 200px;
            background: rgba(99,102,241,0.08);
            border-radius: 50%;
            bottom: -50px; left: -50px;
        }
        .logo-wrap {
            text-align: center;
            margin-bottom: 32px;
            position: relative;
            z-index: 1;
        }
        .logo-wrap img { height: 110px; filter: drop-shadow(0 4px 20px rgba(59,130,246,0.3)); }
        .logo-wrap h2 { font-size: 22px; color: #60a5fa; margin-top: 10px; letter-spacing: 1px; }
        .logo-wrap p { font-size: 12px; color: rgba(255,255,255,0.4); letter-spacing: 3px; text-transform: uppercase; margin-top: 4px; }
        .features { width: 100%; position: relative; z-index: 1; }
        .feat {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: rgba(255,255,255,0.6);
            font-size: 13px; margin-bottom: 10px; transition: all 0.3s;
        }
        .feat:hover { background: rgba(59,130,246,0.12); border-color: rgba(59,130,246,0.3); color: white; transform: translateX(4px); }
        .feat-icon { font-size: 18px; }
        .login-right {
            flex: 1; background: #ffffff;
            display: flex; flex-direction: column; justify-content: center;
            padding: 50px 44px;
        }
        .login-right .welcome { font-size: 26px; font-weight: 700; color: #0d2144; margin-bottom: 6px; }
        .login-right .sub { font-size: 13px; color: #9ca3af; margin-bottom: 32px; }
        .error-box {
            background: rgba(220,38,38,0.08); border: 1px solid rgba(220,38,38,0.25);
            color: #dc2626; padding: 10px 16px; border-radius: 10px;
            font-size: 13px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
        }
        .inp-group { margin-bottom: 20px; }
        .inp-group label { display: block; font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 8px; letter-spacing: 0.5px; text-transform: uppercase; }
        .inp-wrap { position: relative; }
        .inp-wrap input {
            width: 100%; padding: 13px 16px 13px 44px;
            background: #f8fafc; border: 1.5px solid #e5e7eb;
            border-radius: 10px; color: #1a1a2e; font-size: 14px; outline: none; transition: all 0.3s;
        }
        .inp-wrap input:focus { border-color: #3b82f6; background: white; box-shadow: 0 0 0 4px rgba(59,130,246,0.08); }
        .inp-wrap .inp-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); font-size: 16px; color: #9ca3af; }
        .forgot-row { text-align: right; margin-bottom: 24px; margin-top: -8px; }
        .forgot-row a { font-size: 12px; color: #3b82f6; text-decoration: none; font-weight: 500; }
        .forgot-row a:hover { text-decoration: underline; }
        .login-btn {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #1a3a6e, #3b82f6);
            border: none; border-radius: 10px; color: white;
            font-size: 15px; font-weight: 600; cursor: pointer; transition: all 0.3s; letter-spacing: 0.5px;
        }
        .login-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(59,130,246,0.4); }
        .bottom-note { text-align: center; margin-top: 24px; font-size: 11px; color: #9ca3af; }
        .bottom-links { display: flex; justify-content: center; gap: 20px; margin-top: 12px; }
        .bottom-links a { font-size: 12px; color: #60a5fa; text-decoration: none; }
        .bottom-links a:hover { text-decoration: underline; }

        /* Modal */
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; }
        .modal-overlay.open { display:flex; }
        .modal-box { background:#fff; border-radius:16px; padding:40px; width:560px; max-width:90%; position:relative; max-height:80vh; overflow-y:auto; }
        .modal-box h2 { font-size:20px; color:#1a3a6e; margin-bottom:16px; }
        .modal-box p { font-size:13px; color:#6b7280; line-height:1.8; margin-bottom:12px; }
        .modal-close { position:absolute; top:16px; right:20px; font-size:22px; cursor:pointer; color:#9ca3af; background:none; border:none; }
        .modal-close:hover { color:#1a1a2e; }
        .about-grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-top:16px; }
        .about-card { background:#f8fafc; border-radius:10px; padding:16px; border:1px solid #e5e7eb; }
        .about-card h4 { font-size:13px; color:#1a3a6e; margin-bottom:6px; }
        .about-card p { font-size:12px; color:#6b7280; margin:0; }
        .contact-field { margin-bottom:14px; }
        .contact-field label { display:block; font-size:12px; font-weight:600; color:#6b7280; margin-bottom:6px; text-transform:uppercase; }
        .contact-field input, .contact-field textarea {
            width:100%; padding:10px 14px; border:1.5px solid #e5e7eb;
            border-radius:8px; font-size:13px; color:#1a1a2e; outline:none; transition:all 0.3s;
        }
        .contact-field input:focus, .contact-field textarea:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.08); }
        .contact-field textarea { resize:vertical; min-height:80px; }
        .contact-submit { background:linear-gradient(135deg,#1a3a6e,#3b82f6); color:white; border:none; padding:10px 24px; border-radius:8px; font-size:13px; cursor:pointer; font-weight:600; }
        .contact-submit:hover { opacity:0.9; }
    </style>
</head>
<body>

<div class="login-container">
    <!-- Left Panel -->
    <div class="login-left">
        <div class="logo-wrap">
            <img src="allerlogo.png" alt="Aller Technologies">
            <h2>EMS</h2>
            <p>Employee Management System</p>
        </div>
        <div class="features">
            <div class="feat"><span class="feat-icon">📅</span> Attendance & Leave Tracking</div>
            <div class="feat"><span class="feat-icon">💰</span> Salary & Payslip Management</div>
            <div class="feat"><span class="feat-icon">📊</span> Performance & Task Reports</div>
            <div class="feat"><span class="feat-icon">🔔</span> Real-time Notifications</div>
        </div>
    </div>

    <!-- Right Panel -->
    <div class="login-right">
        <p class="welcome">Welcome Back</p>
        <p class="sub">Sign in to your EMS account</p>

        <?php if(isset($_GET['error'])): ?>
            <div class="error-box">⚠️ Invalid email or password. Please try again.</div>
        <?php endif; ?>

        <form action="login.php" method="POST" id="loginForm">
            <div class="inp-group">
                <label>Email Address</label>
                <div class="inp-wrap">
                    <span class="inp-icon">📧</span>
                    <input type="email" name="email" placeholder="Enter your email" required>
                </div>
            </div>
            <div class="inp-group">
                <label>Password</label>
                <div class="inp-wrap">
                    <span class="inp-icon">🔒</span>
                    <input type="password" name="password" id="passwordField" placeholder="Enter your password" required>
                </div>
            </div>
            <div class="forgot-row">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>
            <button type="submit" class="login-btn">Sign In →</button>
        </form>

        <p class="bottom-note">© <?php echo date('Y'); ?> Aller Technologies Pvt. Ltd. All rights reserved.</p>
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
        <h2>🏢 About Aller Technologies</h2>
        <p>Aller Technologies is a dynamic IT company dedicated to building innovative software solutions for modern businesses. Our Employee Management System (EMS) streamlines HR operations, attendance tracking, payroll, and performance management.</p>
        <p><strong>Our Mission:</strong> To simplify workforce management through smart, scalable, and user-friendly technology.</p>
        <div class="about-grid">
            <div class="about-card"><h4>🎯 Our Vision</h4><p>Empowering organizations with cutting-edge HR technology.</p></div>
            <div class="about-card"><h4>💡 Innovation</h4><p>Continuously improving our platform with the latest tech.</p></div>
            <div class="about-card"><h4>🤝 Support</h4><p>24/7 dedicated support for all our clients.</p></div>
            <div class="about-card"><h4>🔒 Security</h4><p>Enterprise-grade security for your sensitive data.</p></div>
        </div>
    </div>
</div>

<!-- Contact Us Modal -->
<div class="modal-overlay" id="contactModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeModal('contactModal')">&times;</button>
        <h2>📬 Contact Us</h2>
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
        <button class="contact-submit" onclick="submitContact()">Send Message ✉️</button>
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
    div.innerHTML = '⚠️ ' + msg;
    const form = document.getElementById('loginForm');
    form.parentNode.insertBefore(div, form);
}
</script>
</body>
</html>