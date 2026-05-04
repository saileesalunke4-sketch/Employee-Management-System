<?php
session_start();
if(isset($_SESSION['user'])){
    $role = $_SESSION['user']['role'];
    if($role == 'super_admin') header("Location: super_admin_dashboard.php");
    elseif($role == 'admin') header("Location: admin_dashboard.php");
    else header("Location: emp_dashboard.php");
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
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
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

        /* Left Panel */
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
        .logo-wrap img {
            height: 110px;
            filter: drop-shadow(0 4px 20px rgba(59,130,246,0.3));
        }
        .logo-wrap h2 {
            font-size: 22px;
            color: #60a5fa;
            margin-top: 10px;
            letter-spacing: 1px;
        }
        .logo-wrap p {
            font-size: 12px;
            color: rgba(255,255,255,0.4);
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }

        .features { width: 100%; position: relative; z-index: 1; }
        .feat {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: rgba(255,255,255,0.6);
            font-size: 13px;
            margin-bottom: 10px;
            transition: all 0.3s;
        }
        .feat:hover {
            background: rgba(59,130,246,0.12);
            border-color: rgba(59,130,246,0.3);
            color: white;
            transform: translateX(4px);
        }
        .feat-icon { font-size: 18px; }

        /* Right Panel */
        .login-right {
            flex: 1;
            background: #ffffff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px 44px;
        }
        .login-right .welcome {
            font-size: 26px;
            font-weight: 700;
            color: #0d2144;
            margin-bottom: 6px;
        }
        .login-right .sub {
            font-size: 13px;
            color: #9ca3af;
            margin-bottom: 32px;
        }

        .error-box {
            background: rgba(220,38,38,0.08);
            border: 1px solid rgba(220,38,38,0.25);
            color: #dc2626;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .inp-group { margin-bottom: 20px; }
        .inp-group label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .inp-wrap { position: relative; }
        .inp-wrap input {
            width: 100%;
            padding: 13px 16px 13px 44px;
            background: #f8fafc;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            color: #1a1a2e;
            font-size: 14px;
            outline: none;
            transition: all 0.3s;
        }
        .inp-wrap input:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.08);
        }
        .inp-wrap .inp-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #9ca3af;
        }

        .forgot-row {
            text-align: right;
            margin-bottom: 24px;
            margin-top: -8px;
        }
        .forgot-row a {
            font-size: 12px;
            color: #3b82f6;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-row a:hover { text-decoration: underline; }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a3a6e, #3b82f6);
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            letter-spacing: 0.5px;
        }
        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59,130,246,0.4);
        }

        .bottom-note {
            text-align: center;
            margin-top: 20px;
            font-size: 11px;
            color: #d1d5db;
        }

        /* ===== FOOTER LINKS ===== */
        .page-footer {
            position: relative;
            z-index: 2;
            margin-top: 24px;
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .footer-link {
            color: rgba(255,255,255,0.45);
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
            transition: color 0.2s;
            letter-spacing: 0.3px;
        }
        .footer-link:hover { color: #60a5fa; }
        .footer-dot {
            width: 3px; height: 3px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
        }

        /* ===== MODAL OVERLAY ===== */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.65);
            z-index: 999;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: #fff;
            border-radius: 16px;
            width: 560px;
            max-height: 85vh;
            overflow-y: auto;
            box-shadow: 0 30px 80px rgba(0,0,0,0.4);
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn {
            from { opacity:0; transform: translateY(20px) scale(0.97); }
            to   { opacity:1; transform: translateY(0) scale(1); }
        }

        .modal-header {
            background: linear-gradient(135deg, #1a3a6e, #3b82f6);
            padding: 24px 28px;
            border-radius: 16px 16px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h2 {
            color: white;
            font-size: 18px;
            font-weight: 600;
        }
        .modal-close {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            width: 32px; height: 32px;
            border-radius: 50%;
            font-size: 16px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        .modal-close:hover { background: rgba(255,255,255,0.3); }

        .modal-body { padding: 28px; }

        /* About Us styles */
        .about-logo {
            text-align: center;
            margin-bottom: 20px;
        }
        .about-logo img {
            height: 70px;
        }
        .about-logo h3 {
            font-size: 18px;
            color: #1a3a6e;
            margin-top: 8px;
            font-weight: 700;
        }
        .about-logo p {
            font-size: 12px;
            color: #9ca3af;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .about-desc {
            font-size: 13px;
            color: #4b5563;
            line-height: 1.8;
            text-align: center;
            margin-bottom: 24px;
            padding: 0 10px;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .about-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            text-align: center;
        }
        .about-card .ac-icon { font-size: 24px; margin-bottom: 8px; }
        .about-card h4 { font-size: 13px; font-weight: 600; color: #1a3a6e; margin-bottom: 4px; }
        .about-card p { font-size: 11px; color: #6b7280; line-height: 1.5; }

        .mv-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .mv-card {
            background: linear-gradient(135deg, #1a3a6e, #3b82f6);
            border-radius: 10px;
            padding: 16px;
            color: white;
        }
        .mv-card h4 { font-size: 13px; font-weight: 600; margin-bottom: 6px; opacity: 0.8; }
        .mv-card p { font-size: 12px; line-height: 1.6; opacity: 0.9; }

        /* Contact Us styles */
        .contact-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }
        .contact-card {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 14px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .contact-card .cc-icon {
            width: 38px; height: 38px;
            background: linear-gradient(135deg, #1a3a6e, #3b82f6);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            flex-shrink: 0;
        }
        .contact-card h4 { font-size: 12px; font-weight: 600; color: #6b7280; margin-bottom: 2px; }
        .contact-card p { font-size: 12px; color: #1a3a6e; font-weight: 500; }

        .contact-form .cf-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }
        .contact-form .cf-field {
            margin-bottom: 12px;
        }
        .contact-form label {
            display: block;
            font-size: 11px;
            font-weight: 600;
            color: #6b7280;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .contact-form input,
        .contact-form textarea,
        .contact-form select {
            width: 100%;
            padding: 10px 14px;
            background: #f8fafc;
            border: 1.5px solid #e5e7eb;
            border-radius: 8px;
            font-size: 13px;
            color: #1a1a2e;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }
        .contact-form input:focus,
        .contact-form textarea:focus,
        .contact-form select:focus {
            border-color: #3b82f6;
            background: white;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.08);
        }
        .contact-form textarea { resize: vertical; min-height: 90px; }

        .cf-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #1a3a6e, #3b82f6);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .cf-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(59,130,246,0.35);
        }

        .cf-success {
            background: rgba(22,163,74,0.08);
            border: 1px solid rgba(22,163,74,0.25);
            color: #16a34a;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 13px;
            text-align: center;
            display: none;
            margin-top: 12px;
        }
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
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
            </div>

            <div class="forgot-row">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <button type="submit" class="login-btn">Sign In →</button>
        </form>

        <p class="bottom-note">© <?php echo date('Y'); ?> Aller Technologies Pvt. Ltd. All rights reserved.</p>
    </div>

</div>

<!-- ===== FOOTER LINKS ===== -->
<div class="page-footer">
    <span class="footer-link" onclick="openModal('aboutModal')">About Us</span>
    <div class="footer-dot"></div>
    <span class="footer-link" onclick="openModal('contactModal')">Contact Us</span>
    <div class="footer-dot"></div>
    <span class="footer-link" style="cursor:default;">© <?php echo date('Y'); ?> Aller Technologies Pvt. Ltd.</span>
</div>

<!-- ===== ABOUT US MODAL ===== -->
<div class="modal-overlay" id="aboutModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2>🏢 About Us</h2>
            <button class="modal-close" onclick="closeModal('aboutModal')">✕</button>
        </div>
        <div class="modal-body">
            <div class="about-logo">
                <img src="allerlogo.png" alt="Aller Technologies">
                <h3>Aller Technologies Pvt. Ltd.</h3>
                <p>Executing Opportunities</p>
            </div>

            <p class="about-desc">
                Aller Technologies is a leading software development company dedicated to building
                innovative digital solutions. Our Employee Management System (EMS) is designed to
                streamline HR operations, boost productivity, and empower organizations with
                real-time workforce insights.
            </p>

            <div class="about-grid">
                <div class="about-card">
                    <div class="ac-icon">👥</div>
                    <h4>Expert Team</h4>
                    <p>Skilled professionals dedicated to delivering quality software solutions</p>
                </div>
                <div class="about-card">
                    <div class="ac-icon">🚀</div>
                    <h4>Innovation First</h4>
                    <p>Cutting-edge technology to solve real-world business challenges</p>
                </div>
                <div class="about-card">
                    <div class="ac-icon">🔒</div>
                    <h4>Secure & Reliable</h4>
                    <p>Enterprise-grade security with 99.9% uptime guarantee</p>
                </div>
                <div class="about-card">
                    <div class="ac-icon">🤝</div>
                    <h4>Client Focused</h4>
                    <p>Tailored solutions built around your unique business needs</p>
                </div>
            </div>

            <div class="mv-section">
                <div class="mv-card">
                    <h4>🎯 Our Mission</h4>
                    <p>To empower businesses with intelligent, automated HR solutions that save time and drive growth.</p>
                </div>
                <div class="mv-card">
                    <h4>🌟 Our Vision</h4>
                    <p>To be the most trusted HR technology partner for organizations across India and beyond.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ===== CONTACT US MODAL ===== -->
<div class="modal-overlay" id="contactModal">
    <div class="modal-box">
        <div class="modal-header">
            <h2>📞 Contact Us</h2>
            <button class="modal-close" onclick="closeModal('contactModal')">✕</button>
        </div>
        <div class="modal-body">

            <div class="contact-info">
                <div class="contact-card">
                    <div class="cc-icon">📧</div>
                    <div>
                        <h4>Email</h4>
                        <p>info@aller.in</p>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="cc-icon">📞</div>
                    <div>
                        <h4>Phone</h4>
                        <p>+91-9765718881</p>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="cc-icon">📍</div>
                    <div>
                        <h4>Address</h4>
                        <p>Pune, Maharashtra, India</p>
                    </div>
                </div>
                <div class="contact-card">
                    <div class="cc-icon">🕐</div>
                    <div>
                        <h4>Working Hours</h4>
                        <p>Mon–Sat, 9 AM – 6 PM</p>
                    </div>
                </div>
            </div>

            <div class="contact-form">
                <div class="cf-row">
                    <div>
                        <label>Your Name</label>
                        <input type="text" id="cf_name" placeholder="Enter your name">
                    </div>
                    <div>
                        <label>Email Address</label>
                        <input type="email" id="cf_email" placeholder="Enter your email">
                    </div>
                </div>
                <div class="cf-field">
                    <label>Subject</label>
                    <select id="cf_subject">
                        <option value="">-- Select Subject --</option>
                        <option>EMS Support</option>
                        <option>Technical Issue</option>
                        <option>New Project Inquiry</option>
                        <option>General Inquiry</option>
                    </select>
                </div>
                <div class="cf-field">
                    <label>Message</label>
                    <textarea id="cf_message" placeholder="Write your message here..."></textarea>
                </div>
                <button class="cf-submit" onclick="submitContact()">Send Message →</button>
                <div class="cf-success" id="cfSuccess">
                    ✅ Message sent successfully! We'll get back to you within 24 hours.
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function openModal(id){
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id){
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(overlay){
    overlay.addEventListener('click', function(e){
        if(e.target === overlay) closeModal(overlay.id);
    });
});

// Contact form submit
function submitContact(){
    const name    = document.getElementById('cf_name').value.trim();
    const email   = document.getElementById('cf_email').value.trim();
    const subject = document.getElementById('cf_subject').value;
    const message = document.getElementById('cf_message').value.trim();

    if(!name || !email || !subject || !message){
        alert('Please fill in all fields!');
        return;
    }
    // Show success message
    document.getElementById('cfSuccess').style.display = 'block';
    document.getElementById('cf_name').value    = '';
    document.getElementById('cf_email').value   = '';
    document.getElementById('cf_subject').value = '';
    document.getElementById('cf_message').value = '';

    setTimeout(function(){
        document.getElementById('cfSuccess').style.display = 'none';
    }, 4000);
}

// Login form validation
document.getElementById('loginForm').addEventListener('submit', function(e){
    const email    = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value.trim();

    if(email === '' || password === ''){
        e.preventDefault();
        showError('Email and password cannot be blank!');
        return;
    }
    const passRegex = /^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%^&*])/;
    if(!passRegex.test(password)){
        e.preventDefault();
        showError('Password must have uppercase, number & special character (e.g. Pass@123)');
        return;
    }
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

// ESC key to close modal
document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){
        document.querySelectorAll('.modal-overlay.open').forEach(function(m){
            closeModal(m.id);
        });
    }
});
</script>

</body>
</html>