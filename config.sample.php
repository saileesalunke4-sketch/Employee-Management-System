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
?>
