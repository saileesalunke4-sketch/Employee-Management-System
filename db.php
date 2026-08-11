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


define('OFFICE_LAT',17.320221642122643);   // <-- replace with your office latitude
define('OFFICE_LNG',76.84761154518559);   // <-- replace with your office longitude
define('OFFICE_RADIUS_METERS', 200); // allowed distance from office (in meters)

// A browser-reported location comes with its own accuracy radius (meters).
// Laptops without GPS hardware fall back to WiFi/IP-based positioning,
// which can report itself as "close" to a point while actually being far
// off — too uncertain to trust against a tight office radius. Readings
// less precise than this are rejected rather than silently accepted.
define('ACCURACY_WARN_METERS', 150);

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
    $from_ts = strtotime($from_date);
    $to_ts   = strtotime($to_date);
    $cal_days = ($to_ts - $from_ts) / 86400 + 1;

    // BUGFIX: sandwich policy only makes sense for a multi-day range — a
    // single day off that happens to fall on a Monday isn't "sandwiching"
    // a weekend (there's no weekend inside a 1-day range to begin with).
    // This used to add +1 day to any single-day Monday leave, deducting
    // 2 days from balance for what should only ever be 1.
    if($cal_days <= 1){
        return $cal_days;
    }

    $from_day = date('N', $from_ts); // 1=Mon ... 5=Fri, 7=Sun
    $to_day   = date('N', $to_ts);

    // BUGFIX (BUG-004): the sandwich charge only applies when the leave
    // deliberately bridges a weekend by starting on Friday and/or ending
    // on Monday — that is the specific pattern this policy exists to
    // discourage. Every other date range used to fall straight through to
    // a plain calendar-day count, which meant an ordinary leave that
    // simply happened to include a Sunday (e.g. Sat-Tue) got charged for
    // that Sunday too, instead of it being excluded as a weekly off.
    if($from_day == 5 || $to_day == 1){
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

    // Not a sandwich pattern: exclude Sunday (the configured weekly off)
    // from the count instead of charging every calendar day.
    $working_days = 0;
    for($ts = $from_ts; $ts <= $to_ts; $ts += 86400){
        if(date('N', $ts) != 7){ // 7 = Sunday
            $working_days++;
        }
    }
    return $working_days;
}

// Half-day aware wrapper — used wherever a leave *record* (with its
// is_half_day flag) needs to count toward balance/display. Half-day only
// ever applies to a single-day request (from_date == to_date); the
// sandwich policy is irrelevant for a single day, so this is a simple 0.5
// instead of calling the sandwich function.
function getLeaveDaysForRecord($from_date, $to_date, $is_half_day){
    if($is_half_day && $from_date === $to_date){
        return 0.5;
    }
    return getLeaveDaysWithSandwich($from_date, $to_date);
}
// ===== ONE-TIME SCHEMA SETUP (runs once, not on every page load) =====
// Everything below used to run its CREATE TABLE / SHOW COLUMNS / ALTER /
// backfill queries on EVERY single page request — 10+ extra DB round trips
// before the page's own queries even start. That's the main reason
// navigation felt like "blank white page, then the real page pops in":
// the server was busy re-checking/re-creating schema that already existed.
// A marker file now makes this run once; delete the marker (or the file)
// to force it to re-check the schema again (e.g. after a fresh DB import).
$schema_marker = __DIR__ . '/logs/.schema_ready';
if(!is_dir(__DIR__.'/logs')) @mkdir(__DIR__.'/logs', 0755, true);
if(!file_exists($schema_marker)){

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

    // Mark schema as verified so none of the above runs again until this
    // marker is removed.
    file_put_contents($schema_marker, date('c'));
}

// ===== ACTIVITY LOG TABLE =====
// Records who approved/rejected/changed what, and when — for admin
// accountability (leave approvals, HR requests, regularizations, role
// updates). See log_activity() below for how rows get written.
// NOTE: this sits OUTSIDE the schema-ready guard above on purpose — if it
// were inside, anyone who already had logs/.schema_ready from before this
// table existed would never get it created. A single CREATE TABLE IF NOT
// EXISTS costs almost nothing once the table's there, so it's fine to just
// always run this one.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `activity_log` (
  `log_id` INT NOT NULL AUTO_INCREMENT,
  `actor_id` INT DEFAULT NULL,
  `actor_name` VARCHAR(150) DEFAULT NULL,
  `action` VARCHAR(50) DEFAULT NULL,
  `target_type` VARCHAR(50) DEFAULT NULL,
  `target_name` VARCHAR(150) DEFAULT NULL,
  `details` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Small helper so every approval/action file can log in one line instead of
// repeating the INSERT everywhere.
function log_activity($conn, $action, $target_type, $target_name, $details = ''){
    $actor_id   = (int) ($_SESSION['user']['id'] ?? 0);
    $actor_name = mysqli_real_escape_string($conn, $_SESSION['user']['name'] ?? 'Unknown');
    $action     = mysqli_real_escape_string($conn, $action);
    $target_type= mysqli_real_escape_string($conn, $target_type);
    $target_name= mysqli_real_escape_string($conn, $target_name);
    $details    = mysqli_real_escape_string($conn, $details);
    mysqli_query($conn, "INSERT INTO activity_log (actor_id, actor_name, action, target_type, target_name, details)
                          VALUES ('$actor_id','$actor_name','$action','$target_type','$target_name','$details')");
}

// ===== SHIFT SCHEDULING =====
// Lets admin define multiple shifts (Morning/Evening/Night etc.) instead of
// everyone being on one hardcoded 9-to-6 schedule, and assign each employee
// to one. save_attendance.php reads the employee's assigned shift to decide
// present/late/half-day instead of using fixed hardcoded times.
// NOTE: outside the schema-ready guard, same reason as activity_log above —
// anyone who already has logs/.schema_ready would never get this otherwise.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `shifts` (
  `shift_id` INT NOT NULL AUTO_INCREMENT,
  `shift_name` VARCHAR(100) NOT NULL,
  `start_time` TIME NOT NULL,
  `end_time` TIME NOT NULL,
  `grace_minutes` INT NOT NULL DEFAULT 15,
  `half_day_after_minutes` INT NOT NULL DEFAULT 180,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`shift_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$shift_col_check = mysqli_query($conn, "SHOW COLUMNS FROM employees LIKE 'shift_id'");
if($shift_col_check && mysqli_num_rows($shift_col_check) == 0){
    mysqli_query($conn, "ALTER TABLE employees ADD COLUMN shift_id INT DEFAULT NULL");
}

// Seed a default shift matching the exact hours that were hardcoded before
// (09:00 start, 15-min grace, half-day after 3 hours = 180 min, 18:00 end)
// so existing employees keep behaving exactly the same until an admin
// deliberately creates/assigns something different.
$shift_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM shifts"))['c'];
if($shift_count == 0){
    mysqli_query($conn, "INSERT INTO shifts (shift_name, start_time, end_time, grace_minutes, half_day_after_minutes)
                          VALUES ('General Shift', '09:00:00', '18:00:00', 15, 180)");
}
$default_shift_id = mysqli_fetch_assoc(mysqli_query($conn, "SELECT shift_id FROM shifts ORDER BY shift_id ASC LIMIT 1"))['shift_id'];
mysqli_query($conn, "UPDATE employees SET shift_id=$default_shift_id WHERE shift_id IS NULL");

// ===== HALF-DAY LEAVE + LEAVE CANCELLATION =====
// `leaves` is a pre-existing table (not created by this app), so we only
// ALTER it here rather than assuming its full original definition.
// NOTE: outside the schema-ready guard, same reasoning as shifts/activity_log
// above — anyone who already has logs/.schema_ready would never get this
// otherwise.
$half_day_col_check = mysqli_query($conn, "SHOW COLUMNS FROM leaves LIKE 'is_half_day'");
if($half_day_col_check && mysqli_num_rows($half_day_col_check) == 0){
    mysqli_query($conn, "ALTER TABLE leaves ADD COLUMN is_half_day TINYINT(1) NOT NULL DEFAULT 0");
}
// Widen the status column to allow 'cancelled' alongside the existing
// pending/approved/rejected values, so an employee can cancel their own
// pending request instead of only admin being able to approve/reject.
mysqli_query($conn, "ALTER TABLE leaves MODIFY status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending'");

// ===== WFH REQUEST =====
// This is separate from the existing same-day "mark WFH at check-in"
// checkbox in save_attendance.php (that stays as-is, for spontaneous
// same-day WFH with no approval needed). This table is for *planning
// ahead* — an employee requests a future WFH day, admin/super_admin
// approves or rejects it, and on approval the employee's attendance for
// that date gets pre-marked as work_from_home automatically.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `wfh_requests` (
  `request_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT NOT NULL,
  `wfh_date` DATE NOT NULL,
  `reason` TEXT,
  `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` INT DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ===== REIMBURSEMENT REQUEST =====
// Employee submits an expense claim (category, amount, optional receipt
// file), admin/super_admin approves or rejects it. Same request-and-approve
// shape as leaves/WFH/HR requests above.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `reimbursement_requests` (
  `request_id` INT NOT NULL AUTO_INCREMENT,
  `emp_id` INT NOT NULL,
  `category` VARCHAR(50) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `description` TEXT,
  `receipt_filename` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_by` INT DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ===== ASSET MANAGEMENT =====
// Unlike the request-and-approve modules above, this is inventory tracking:
// admin maintains a list of company assets (laptops, monitors, phones...)
// and assigns/returns them to/from employees, with a full history of who
// had what and when.
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `assets` (
  `asset_id` INT NOT NULL AUTO_INCREMENT,
  `asset_name` VARCHAR(150) NOT NULL,
  `asset_type` VARCHAR(50) NOT NULL,
  `serial_number` VARCHAR(100) DEFAULT NULL,
  `purchase_date` DATE DEFAULT NULL,
  `status` ENUM('available','assigned','under_repair','retired') NOT NULL DEFAULT 'available',
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `asset_assignments` (
  `assignment_id` INT NOT NULL AUTO_INCREMENT,
  `asset_id` INT NOT NULL,
  `emp_id` INT NOT NULL,
  `assigned_date` DATE NOT NULL,
  `returned_date` DATE DEFAULT NULL,
  `condition_notes` VARCHAR(255) DEFAULT NULL,
  `assigned_by` INT DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`assignment_id`),
  KEY `asset_id` (`asset_id`),
  KEY `emp_id` (`emp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// ===== ATTENDANCE WORK MODE (WFH / WFO) =====
// Adds an explicit work_mode column to attendance, independent of the
// existing `status` column (present/late/half_day/work_from_home/absent).
// status keeps deciding present/late/half-day exactly as before; work_mode
// just records whether that day was worked from home or from office, so
// it can be filtered/reported on its own.
// NOTE: outside the schema-ready guard, same reason as activity_log/shifts
// above — existing deployments that already have logs/.schema_ready would
// never get this column otherwise.
$work_mode_col_check = mysqli_query($conn, "SHOW COLUMNS FROM attendance LIKE 'work_mode'");
if($work_mode_col_check && mysqli_num_rows($work_mode_col_check) == 0){
    mysqli_query($conn, "ALTER TABLE attendance ADD COLUMN work_mode ENUM('WFH','WFO') NOT NULL DEFAULT 'WFO' AFTER status");
}

// ===== FORCE PASSWORD CHANGE ON FIRST LOGIN =====
// New employees are created by an admin with a temporary password (see
// add_employee.php); this column flags that they still need to set their
// own. DEFAULT 0 so existing accounts are completely unaffected — only
// newly created users get flagged going forward.
$mcp_col_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'must_change_password'");
if($mcp_col_check && mysqli_num_rows($mcp_col_check) == 0){
    mysqli_query($conn, "ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0");
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
?>
