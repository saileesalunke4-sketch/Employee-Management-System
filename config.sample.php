<?php
// ===== CONFIG TEMPLATE — safe to commit to git =====
// Copy this file to "config.php" (which is git-ignored) and fill in the
// real values for your environment. Never put real credentials in this
// sample file.

// --- Environment ---
// 'development' on localhost while building; switch to 'production' once hosted/live.
define('APP_ENV', 'development');

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'your_db_username_here');
define('DB_PASS', 'your_db_password_here');
define('DB_NAME', 'your_database_name_here');

// --- SMTP / Email ---
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'your_email@gmail.com');
define('SMTP_PASSWORD', 'your_gmail_app_password_here'); // NOT your normal Gmail password — generate an "App Password"
define('SMTP_PORT', 587);
define('SMTP_FROM_NAME', 'EMS - Your Company Name');

// --- AI HR Assistant (Gemini) ---
// Get a free key at https://aistudio.google.com/apikey
define('GEMINI_API_KEY', 'your_gemini_api_key_here');


// --- SMS Gateway (for Login OTP / 2-Step Verification) ---
// Fill these in once you've picked a provider and they've given you API
// access + an approved SMS template. Until SMS_GATEWAY_API_KEY is filled
// in, login OTP is sent by EMAIL ONLY — SMS is skipped automatically, so
// the feature still works without this.
define('SMS_GATEWAY_API_KEY', '');       // e.g. your MSG91/Twilio/Fast2SMS auth key
define('SMS_GATEWAY_API_URL', '');       // the exact API endpoint your provider gave you
define('SMS_GATEWAY_SENDER_ID', '');     // your approved DLT sender ID, if applicable
?>
