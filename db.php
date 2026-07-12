<?php
// Set correct timezone (India Standard Time) so date()/time() everywhere
// in the app — attendance check-in/out, leave dates, etc — is accurate.
// Without this, PHP defaults to UTC (or the server's OS timezone), which
// is why check-in time was showing ~4.5 hours behind actual IST time.
date_default_timezone_set('Asia/Kolkata');

// SECURITY: real credentials now live in config.php, which is git-ignored.
// See config.sample.php for the template if setting up a new environment.
require_once 'config.php';

// ===== ERROR HANDLING (environment-aware) =====
// development: show errors on screen (helpful while building/debugging locally)
// production:  hide errors from users, log them to a file instead — showing
//              raw PHP errors/SQL/file paths to real users is a security risk.
error_reporting(E_ALL);
if(defined('APP_ENV') && APP_ENV === 'production'){
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    if(!is_dir(__DIR__.'/logs')) mkdir(__DIR__.'/logs', 0755, true);
    $logs_htaccess = __DIR__.'/logs/.htaccess';
    if(!file_exists($logs_htaccess)){
        file_put_contents($logs_htaccess, "Require all denied\n"); // Apache 2.4+; also blocks direct access to log files
    }
    ini_set('error_log', __DIR__.'/logs/php_errors.log');
} else {
    ini_set('display_errors', '1');
}

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if(!$conn){
    die("Connection Failed: " . mysqli_connect_error());
}

// Extend session lifetime safely
if (session_status() === PHP_SESSION_ACTIVE) {
    setcookie(session_name(), session_id(), time() + 1800, '/');
}

// ===== PHPMAILER CONFIG =====
require_once 'PHPMailer/src/PHPMailer.php';
require_once 'PHPMailer/src/SMTP.php';
require_once 'PHPMailer/src/Exception.php';

function sendEMSMail($to_email, $to_name, $subject, $body){
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->setFrom(SMTP_USERNAME, SMTP_FROM_NAME);
        $mail->addAddress($to_email, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->send();
        return true;
    } catch (\Exception $e) {
        return false;
    }
}

// ===== GEO-FENCE CONFIG (Attendance) =====
// TODO: Replace with YOUR office's actual coordinates.
// How to get them: open Google Maps -> right-click on your office location
// -> click the lat,lng shown at top (e.g. "20.9463, 78.9797") -> copy here.
define('OFFICE_LAT', 21.1458);   // <-- replace with your office latitude
define('OFFICE_LNG', 79.0882);   // <-- replace with your office longitude
define('OFFICE_RADIUS_METERS', 200); // allowed distance from office (in meters)

// Haversine formula: distance in meters between two lat/lng points
function getDistanceMeters($lat1, $lon1, $lat2, $lon2){
    $earthRadius = 6371000; // meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earthRadius * $c;
}

// ===== SHARED LEAVE DAY CALCULATOR (Sandwich Policy aware) =====
// Used consistently everywhere (apply form, balance tracker, records table)
// so the "days deducted" figure always matches across the whole app.
function getLeaveDaysWithSandwich($from_date, $to_date){
    $cal_days = (strtotime($to_date) - strtotime($from_date)) / 86400 + 1;
    $from_day = date('N', strtotime($from_date)); // 1=Mon ... 5=Fri, 7=Sun
    $to_day   = date('N', strtotime($to_date));

    $sandwich_days = 0;
    if($from_day == 5 && $to_day == 1){
        $sandwich_days = 0; // Fri->Mon: weekend already inside the range
    } elseif($from_day == 5){
        $sandwich_days = 2; // starts Friday -> Sat+Sun added
    } elseif($to_day == 1){
        $sandwich_days = 1; // ends Monday -> Sun added
    }
    return $cal_days + $sandwich_days;
}
// ===== REGULARIZATION REQUESTS TABLE =====
// Lets an employee request a correction to a past attendance day
// (e.g. forgot to check in/out), for admin/super_admin to approve or reject.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `regularization_requests` (
  `request_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT DEFAULT NULL,
  `att_date` DATE DEFAULT NULL,
  `requested_check_in` TIME DEFAULT NULL,
  `requested_check_out` TIME DEFAULT NULL,
  `requested_status` VARCHAR(30) DEFAULT NULL,
  `reason` TEXT,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` INT DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add a work_location column to employees if it doesn't already exist
// (needed to support "Location Change" HR process requests)
$col_check = mysqli_query($conn, "SHOW COLUMNS FROM employees LIKE 'work_location'");
if($col_check && mysqli_num_rows($col_check) == 0){
    mysqli_query($conn, "ALTER TABLE employees ADD COLUMN work_location VARCHAR(100) DEFAULT NULL");
}

// ===== HR PROCESS REQUESTS TABLE =====
// Lets an employee request a Department / Designation / Location change,
// for admin/super_admin to review and approve or reject.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `hr_process_requests` (
  `request_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT DEFAULT NULL,
  `request_type` VARCHAR(30) DEFAULT NULL,
  `current_value` VARCHAR(150) DEFAULT NULL,
  `requested_value` VARCHAR(150) DEFAULT NULL,
  `reason` TEXT,
  `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` INT DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ===== ANNOUNCEMENTS TABLE =====
// Company-wide announcements posted by admin/super_admin, visible to everyone.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` INT NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(150) DEFAULT NULL,
  `message` TEXT,
  `posted_by` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`announcement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ===== DEPARTMENT WALL TABLE =====
// Lets employees post short updates visible only to their own department.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `department_wall_posts` (
  `post_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT DEFAULT NULL,
  `dept_id` INT DEFAULT NULL,
  `message` TEXT,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`post_id`),
  KEY `dept_id` (`dept_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ===== AUTO EMPLOYEE ID (employee_code) =====
// Adds a human-friendly employee code (e.g. EMP0001) instead of relying on
// the raw internal emp_id. New employees get one automatically on creation
// (see add_employee.php); this also backfills any existing employees that
// don't have one yet, so nobody is left without a code.
$col_check2 = mysqli_query($conn, "SHOW COLUMNS FROM employees LIKE 'employee_code'");
if($col_check2 && mysqli_num_rows($col_check2) == 0){
    mysqli_query($conn, "ALTER TABLE employees ADD COLUMN employee_code VARCHAR(20) DEFAULT NULL");
}
// Backfill missing codes (covers existing employees + a safety net for any edge case)
$missing_codes = mysqli_query($conn, "SELECT emp_id FROM employees WHERE employee_code IS NULL OR employee_code=''");
if($missing_codes){
    while($mc = mysqli_fetch_assoc($missing_codes)){
        $code = 'EMP' . str_pad($mc['emp_id'], 4, '0', STR_PAD_LEFT);
        mysqli_query($conn, "UPDATE employees SET employee_code='$code' WHERE emp_id=".(int)$mc['emp_id']);
    }
}
// ===== CSRF PROTECTION HELPERS =====
// Used on approve/reject action links (leave, regularization, HR requests)
// so a malicious link/page on another site can't trick a logged-in admin's
// browser into silently approving/rejecting something.
if(session_status() === PHP_SESSION_NONE){
    // db.php can be included both before and after session_start() across
    // different pages, so guard against calling it twice.
    @session_start();
}
function csrf_token(){
    if(empty($_SESSION['csrf_token'])){
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_verify($token){
    return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
}

// ===== LOGIN BRUTE-FORCE PROTECTION =====
// Tracks failed login attempts per user and temporarily locks the account
// after too many wrong passwords in a row.
$col_check3 = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'failed_login_attempts'");
if($col_check3 && mysqli_num_rows($col_check3) == 0){
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN failed_login_attempts INT DEFAULT 0");
}
$col_check4 = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'lockout_until'");
if($col_check4 && mysqli_num_rows($col_check4) == 0){
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN lockout_until DATETIME DEFAULT NULL");
}

// ===== PASSWORD RESET TABLE =====
// Secure, email-verified password reset (token-based, single-use, expiring).
// Replaces the old "reset by email alone" flow, which let anyone reset
// anyone else's password just by knowing their email address.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `password_resets` (
  `reset_id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `token` VARCHAR(64) NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `used` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`reset_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
?>
