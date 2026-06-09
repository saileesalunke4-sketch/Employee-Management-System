<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Employee Report";

// Get all employees for dropdown
$employees = mysqli_query($conn, "SELECT e.emp_id, e.first_name, e.last_name, e.designation FROM employees e ORDER BY e.first_name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Employee Report - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.report-preview{background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);padding:28px;margin-top:20px;display:none;}
.rp-header{background:linear-gradient(135deg,#1a3a6e,#3b82f6);color:white;border-radius:10px;padding:20px 24px;margin-bottom:20px;text-align:center;}
.rp-header h2{margin:0;font-size:18px;}
.rp-header p{margin:4px 0 0;font-size:12px;opacity:0.8;}
.rp-section{margin-bottom:20px;}
.rp-section h4{font-size:13px;font-weight:700;color:#1a1a2e;padding:8px 14px;background:#eff6ff;border-left:4px solid #3b82f6;border-radius:0 8px 8px 0;margin-bottom:10px;}
.rp-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.rp-item{background:#f9fafb;border-radius:8px;padding:10px 14px;}
.rp-item label{font-size:11px;color:#9ca3af;display:block;}
.rp-item span{font-size:13px;font-weight:600;color:#1a1a2e;}
.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:10px;}
.stat-box{text-align:center;padding:14px;border-radius:10px;}
.stat-box .num{font-size:24px;font-weight:800;}
.stat-box .lbl{font-size:11px;margin-top:4px;}
.grade-badge{display:inline-block;padding:6px 18px;border-radius:20px;font-size:13px;font-weight:700;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">Generate Employee Report</h3>
        <form method="GET" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div class="field" style="margin:0;flex:1;min-width:200px;"><label>Select Employee</label>
                <select name="emp_id" required style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;color:#1a1a2e;">
                    <option value="">-- Select Employee --</option>
                    <?php
                    while($e = mysqli_fetch_assoc($employees)){
                        $sel = (isset($_GET['emp_id']) && $_GET['emp_id']==$e['emp_id']) ? 'selected' : '';
                        echo "<option value='{$e['emp_id']}' {$sel}>{$e['first_name']} {$e['last_name']} — {$e['designation']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="field" style="margin:0;"><label>Month</label>
                <select name="month" style="padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;color:#1a1a2e;">
                    <?php
                    $months=['January','February','March','April','May','June','July','August','September','October','November','December'];
                    $sel_m = isset($_GET['month']) ? $_GET['month'] : date('F');
                    foreach($months as $m) echo "<option value='$m'".($sel_m==$m?' selected':'').">$m</option>";
                    ?>
                </select>
            </div>
            <div class="field" style="margin:0;"><label>Year</label>
                <input type="number" name="year" value="<?php echo isset($_GET['year'])?$_GET['year']:date('Y'); ?>" style="padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;width:90px;color:#1a1a2e;">
            </div>
            <button type="submit" class="submit-btn" style="margin:0;padding:9px 22px;"> Preview Report</button>
            <?php if(isset($_GET['emp_id']) && !empty($_GET['emp_id'])): ?>
            <a href="generate_emp_report.php?emp_id=<?php echo $_GET['emp_id']; ?>&month=<?php echo $_GET['month']??date('F'); ?>&year=<?php echo $_GET['year']??date('Y'); ?>"
               style="display:inline-block;background:#dc2626;color:white;padding:9px 22px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">
                Download PDF
            </a>
            <?php endif; ?>
        </form>
    </div>

    <?php
    if(isset($_GET['emp_id']) && !empty($_GET['emp_id'])){
        $emp_id  = $_GET['emp_id'];
        $month   = $_GET['month'] ?? date('F');
        $year    = $_GET['year']  ?? date('Y');
        $mon_num = date('m', strtotime("$month 1 $year"));

        // Employee details
        $emp = mysqli_fetch_assoc(mysqli_query($conn,"SELECT e.*, u.name, u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.emp_id='$emp_id'"));

        // Attendance
        $att = mysqli_fetch_assoc(mysqli_query($conn,"SELECT
            COUNT(*) as total,
            SUM(status='present') as present,
            SUM(status='absent') as absent,
            SUM(status='late') as late,
            SUM(status='work_from_home') as wfh
            FROM attendance WHERE emp_id='$emp_id' AND YEAR(date)='$year' AND MONTH(date)='$mon_num'"));

        // Hours
        $hrs_res = mysqli_query($conn,"SELECT check_in, check_out FROM attendance WHERE emp_id='$emp_id' AND YEAR(date)='$year' AND MONTH(date)='$mon_num' AND check_in IS NOT NULL AND check_out IS NOT NULL");
        $total_hrs = 0; $ot_hrs = 0;
        while($h = mysqli_fetch_assoc($hrs_res)){
            $diff = (strtotime($h['check_out']) - strtotime($h['check_in'])) / 3600;
            if($diff > 0){ $total_hrs += $diff; $ot_hrs += max(0, $diff-8); }
        }

        // Leaves
        $leaves = mysqli_query($conn,"SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC LIMIT 5");
        $leave_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND status='approved'"))['t'];

        // Salary
        $salary = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM salary WHERE emp_id='$emp_id' AND month='$month' AND year='$year'"));

        // Performance
        $tasks = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total, SUM(status='completed') as done, SUM(status='pending') as pend FROM tasks WHERE emp_id='$emp_id'"));
        $skills_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM performance WHERE emp_id='$emp_id'"))['t'];

        // Score
        $task_score = $tasks['total'] > 0 ? round(($tasks['done']/$tasks['total'])*40) : 0;
        $att_score  = ($att['present']+$att['late']) > 0 ? round(($att['present']/max($att['total'],1))*40) : 0;
        $skill_score = min($skills_count*4, 20);
        $score = min($task_score + $att_score + $skill_score, 100);
        if($score>=85) { $grade='A+'; $gcol='#16a34a'; $gbg='#dcfce7'; }
        elseif($score>=70) { $grade='A'; $gcol='#16a34a'; $gbg='#dcfce7'; }
        elseif($score>=55) { $grade='B'; $gcol='#2563eb'; $gbg='#eff6ff'; }
        elseif($score>=40) { $grade='C'; $gcol='#d97706'; $gbg='#fef3c7'; }
        else { $grade='D'; $gcol='#dc2626'; $gbg='#fee2e2'; }
    ?>

    <!-- PREVIEW -->
    <div class="report-preview" id="reportPreview" style="display:block;">

        <div class="rp-header">
            <h2> ALLER TECHNOLOGIES PVT. LTD.</h2>
            <p>Employee Performance & Activity Report — <?php echo $month.' '.$year; ?></p>
        </div>

        <!-- Employee Info -->
        <div class="rp-section">
            <h4> Employee Details</h4>
            <div class="rp-grid">
                <div class="rp-item"><label>Full Name</label><span><?php echo $emp['first_name'].' '.$emp['last_name']; ?></span></div>
                <div class="rp-item"><label>Employee ID</label><span>EMP-<?php echo $emp_id; ?></span></div>
                <div class="rp-item"><label>Email</label><span><?php echo $emp['email']; ?></span></div>
                <div class="rp-item"><label>Designation</label><span><?php echo $emp['designation']; ?></span></div>
                <div class="rp-item"><label>Contact</label><span><?php echo $emp['contact'] ?: '-'; ?></span></div>
                <div class="rp-item"><label>Blood Group</label><span><?php echo $emp['blood_group'] ?: '-'; ?></span></div>
            </div>
        </div>

        <!-- Attendance -->
        <div class="rp-section">
            <h4> Attendance Summary — <?php echo $month.' '.$year; ?></h4>
            <div class="stat-grid">
                <div class="stat-box" style="background:#dcfce7;"><div class="num" style="color:#16a34a;"><?php echo $att['present']??0; ?></div><div class="lbl" style="color:#16a34a;">Present</div></div>
                <div class="stat-box" style="background:#fee2e2;"><div class="num" style="color:#dc2626;"><?php echo $att['absent']??0; ?></div><div class="lbl" style="color:#dc2626;">Absent</div></div>
                <div class="stat-box" style="background:#fef3c7;"><div class="num" style="color:#d97706;"><?php echo $att['late']??0; ?></div><div class="lbl" style="color:#d97706;">Late</div></div>
                <div class="stat-box" style="background:#eff6ff;"><div class="num" style="color:#2563eb;"><?php echo $att['wfh']??0; ?></div><div class="lbl" style="color:#2563eb;">WFH</div></div>
            </div>
            <div class="rp-grid">
                <div class="rp-item"><label>Total Hours Worked</label><span><?php echo number_format($total_hrs,1); ?> hrs</span></div>
                <div class="rp-item"><label>Overtime Hours</label><span style="color:#16a34a;"><?php echo number_format($ot_hrs,1); ?> hrs</span></div>
            </div>
        </div>

        <!-- Leaves -->
        <div class="rp-section">
            <h4> Leave Summary</h4>
            <div class="rp-grid">
                <div class="rp-item"><label>Total Approved Leaves</label><span><?php echo $leave_count; ?> days</span></div>
                <div class="rp-item"><label>Recent Leave Requests</label><span><?php echo mysqli_num_rows($leaves); ?> records</span></div>
            </div>
        </div>

        <!-- Salary -->
        <div class="rp-section">
            <h4> Salary — <?php echo $month.' '.$year; ?></h4>
            <?php if($salary): ?>
            <div class="stat-grid">
                <div class="rp-item"><label>Basic Pay</label><span>₹<?php echo number_format($salary['basic_pay'],2); ?></span></div>
                <div class="rp-item"><label>Allowances</label><span>₹<?php echo number_format($salary['allowances'],2); ?></span></div>
                <div class="rp-item"><label>Deductions</label><span style="color:#dc2626;">₹<?php echo number_format($salary['deductions'],2); ?></span></div>
                <div class="rp-item"><label>Net Pay</label><span style="color:#16a34a;font-size:15px;">₹<?php echo number_format($salary['net_pay'],2); ?></span></div>
            </div>
            <?php else: ?>
            <p style="color:#9ca3af;font-size:13px;">No salary record found for <?php echo $month.' '.$year; ?></p>
            <?php endif; ?>
        </div>

        <!-- Performance -->
        <div class="rp-section">
            <h4> Performance Score</h4>
            <div class="stat-grid">
                <div class="rp-item"><label>Total Tasks</label><span><?php echo $tasks['total']; ?></span></div>
                <div class="rp-item"><label>Completed</label><span style="color:#16a34a;"><?php echo $tasks['done']; ?></span></div>
                <div class="rp-item"><label>Pending</label><span style="color:#dc2626;"><?php echo $tasks['pend']; ?></span></div>
                <div class="rp-item"><label>Skills Added</label><span><?php echo $skills_count; ?></span></div>
            </div>
            <div style="margin-top:12px;text-align:center;">
                <span style="font-size:13px;color:#6b7280;">Overall Score: </span>
                <strong style="font-size:22px;color:#1a1a2e;"><?php echo $score; ?>/100</strong>
                &nbsp;
                <span class="grade-badge" style="background:<?php echo $gbg;?>;color:<?php echo $gcol;?>;">Grade: <?php echo $grade; ?></span>
            </div>
        </div>

        <p style="text-align:center;font-size:11px;color:#9ca3af;margin-top:20px;">
            Generated by EMS — Aller Technologies &nbsp;|&nbsp; <?php echo date('d M Y, h:i A'); ?>
        </p>
    </div>

    <?php } ?>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
