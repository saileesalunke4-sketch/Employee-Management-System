<?php
// ===== SENSITIVE CONFIG — DO NOT COMMIT THIS FILE TO GIT/VERSION CONTROL =====
// This file holds real credentials for THIS environment (local/staging/production).
// Add "config.php" to your .gitignore so it never gets pushed to GitHub or
// any shared repository. Use config.sample.php as the template for what
// keys are needed when setting up a new environment.

// --- Environment ---
// 'development' -> shows PHP errors on screen (useful while building/debugging on localhost)
// 'production'  -> hides errors from users, logs them to a file instead (use this once hosted/live)
define('APP_ENV', 'development');

// --- Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_NAME', 'emp1_db');

// --- SMTP / Email (Gmail App Password — NOT your normal Gmail password) ---
// How to get a Gmail App Password: Google Account -> Security -> 2-Step
// Verification -> App Passwords -> generate one for "Mail".
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USERNAME', 'saileesalunke4@gmail.com');
define('SMTP_PASSWORD', 'noxx isym tpad ltnx');
define('SMTP_PORT', 587);
define('SMTP_FROM_NAME', 'EMS - Aller Technologies');
?>
