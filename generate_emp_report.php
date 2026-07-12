<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
require 'fpdf/fpdf.php';

// SECURITY: emp_id must be an integer, month must be a known month name,
// year must be an integer — all previously went straight into SQL unescaped.
$emp_id  = (int) $_GET['emp_id'];
$valid_months = ['January','February','March','April','May','June','July','August','September','October','November','December'];
$month   = in_array($_GET['month'] ?? '', $valid_months, true) ? $_GET['month'] : date('F');
$year    = (int) ($_GET['year'] ?? date('Y'));
$mon_num = date('m', strtotime("$month 1 $year"));

// Employee details
$emp = mysqli_fetch_assoc(mysqli_query($conn,"SELECT e.*, u.name, u.email FROM employees e JOIN users u ON e.user_id=u.id WHERE e.emp_id=$emp_id"));
if(!$emp) die("Employee not found!");

// Attendance
$att = mysqli_fetch_assoc(mysqli_query($conn,"SELECT
    COUNT(*) as total,
    SUM(status='present') as present,
    SUM(status='absent') as absent,
    SUM(status='late') as late,
    SUM(status='work_from_home') as wfh
    FROM attendance WHERE emp_id=$emp_id AND YEAR(date)=$year AND MONTH(date)='$mon_num'"));

// Hours
$hrs_res = mysqli_query($conn,"SELECT check_in,check_out FROM attendance WHERE emp_id=$emp_id AND YEAR(date)=$year AND MONTH(date)='$mon_num' AND check_in IS NOT NULL AND check_out IS NOT NULL");
$total_hrs = 0; $ot_hrs = 0;
while($h = mysqli_fetch_assoc($hrs_res)){
    $diff = (strtotime($h['check_out']) - strtotime($h['check_in'])) / 3600;
    if($diff > 0){ $total_hrs += $diff; $ot_hrs += max(0,$diff-8); }
}

// Leaves
$leave_count    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND status='approved'"))['t'];
$pending_leaves = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE emp_id='$emp_id' AND status='pending'"))['t'];

// Salary
$salary = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM salary WHERE emp_id='$emp_id' AND month='$month' AND year='$year'"));

// Tasks
$tasks = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as total, SUM(status='completed') as done, SUM(status='pending') as pend, SUM(status='in_progress') as inprog FROM tasks WHERE emp_id='$emp_id'"));

// Skills
$skills_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM performance WHERE emp_id='$emp_id'"))['t'];
$skills_res   = mysqli_query($conn,"SELECT skill_name FROM performance WHERE emp_id='$emp_id' ORDER BY date_added DESC LIMIT 5");
$skills_list  = [];
while($sk = mysqli_fetch_assoc($skills_res)) $skills_list[] = $sk['skill_name'];

// Performance Score
$task_score  = $tasks['total'] > 0 ? round(($tasks['done']/$tasks['total'])*40) : 0;
$att_score   = $att['total']   > 0 ? round(($att['present']/max($att['total'],1))*40) : 0;
$skill_score = min($skills_count*4, 20);
$score       = min($task_score + $att_score + $skill_score, 100);
if($score>=85)     { $grade='A+ Excellent'; }
elseif($score>=70) { $grade='A Good'; }
elseif($score>=55) { $grade='B Average'; }
elseif($score>=40) { $grade='C Below Average'; }
else               { $grade='D Needs Improvement'; }

// ===== PDF GENERATION =====
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetMargins(12, 12, 12);

// ── HEADER ──
$pdf->SetFillColor(26, 58, 110);
$pdf->Rect(0, 0, 210, 42, 'F');
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetY(7);
$pdf->Cell(0, 10, 'ALLER TECHNOLOGIES PVT. LTD.', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 7, 'EMPLOYEE REPORT — '.$month.' '.$year, 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 6, 'Generated on: '.date('d M Y, h:i A').' | Confidential Document', 0, 1, 'C');

// ── EMPLOYEE INFO BOX ──
$pdf->SetY(48);
$pdf->SetFillColor(240, 246, 255);
$pdf->SetDrawColor(59, 130, 246);
$pdf->Rect(12, 48, 186, 38, 'FD');
$pdf->SetTextColor(26, 58, 110);
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(16, 51);
$pdf->Cell(0, 6, 'EMPLOYEE DETAILS', 0, 1);

$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(60, 60, 60);

$pdf->SetXY(16, 59);
$pdf->Cell(30, 5, 'Name:', 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(55, 5, $emp['first_name'].' '.$emp['last_name'], 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(28, 5, 'Employee ID:', 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 5, 'EMP-'.$emp_id, 0, 1);

$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(16, 66);
$pdf->Cell(30, 5, 'Designation:', 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(55, 5, $emp['designation'] ?: '-', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(28, 5, 'Email:', 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 5, $emp['email'], 0, 1);

$pdf->SetFont('Arial', '', 9);
$pdf->SetXY(16, 73);
$pdf->Cell(30, 5, 'Contact:', 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(55, 5, $emp['contact'] ?: '-', 0, 0);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(28, 5, 'Blood Group:', 0, 0);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 5, $emp['blood_group'] ?: '-', 0, 1);

// ── SECTION HELPER ──
function sectionHeader($pdf, $title, $y=null){
    if($y) $pdf->SetY($y);
    else $pdf->SetY($pdf->GetY() + 8);
    $pdf->SetFillColor(26, 58, 110);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(186, 8, '  '.$title, 0, 1, 'L', true);
    $pdf->SetTextColor(50, 50, 50);
}

// ── ATTENDANCE SECTION ──
sectionHeader($pdf, 'ATTENDANCE SUMMARY — '.$month.' '.$year, 94);

$pdf->SetFont('Arial', '', 9);
$pdf->SetFillColor(220, 252, 231);
$pdf->Cell(45, 8, 'Present: '.($att['present']??0).' days', 1, 0, 'C', true);
$pdf->SetFillColor(254, 226, 226);
$pdf->Cell(45, 8, 'Absent: '.($att['absent']??0).' days', 1, 0, 'C', true);
$pdf->SetFillColor(254, 243, 199);
$pdf->Cell(48, 8, 'Late: '.($att['late']??0).' days', 1, 0, 'C', true);
$pdf->SetFillColor(219, 234, 254);
$pdf->Cell(48, 8, 'WFH: '.($att['wfh']??0).' days', 1, 1, 'C', true);

$pdf->SetFillColor(248, 250, 255);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(93, 7, '  Total Hours Worked: '.number_format($total_hrs,1).' hrs', 1, 0, 'L', true);
$pdf->Cell(93, 7, '  Overtime Hours: +'.number_format($ot_hrs,1).' hrs', 1, 1, 'L', true);

// ── LEAVE SECTION ──
sectionHeader($pdf, 'LEAVE SUMMARY');

$pdf->SetFillColor(248, 250, 255);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(93, 7, '  Approved Leaves: '.$leave_count.' days', 1, 0, 'L', true);
$pdf->Cell(93, 7, '  Pending Leaves: '.$pending_leaves, 1, 1, 'L', true);

// ── SALARY SECTION ──
sectionHeader($pdf, 'SALARY — '.$month.' '.$year);

if($salary){
    $pdf->SetFillColor(248, 250, 255);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(46, 7, '  Basic Pay', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(46, 7, 'Rs.'.number_format($salary['basic_pay'],2), 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(46, 7, '  Allowances', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(48, 7, 'Rs.'.number_format($salary['allowances'],2), 1, 1, 'R', true);

    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(46, 7, '  Deductions / PF', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(46, 7, 'Rs.'.number_format($salary['deductions'],2), 1, 0, 'R', true);
    $pdf->SetFont('Arial', '', 9);
    $pdf->Cell(46, 7, '  LOP ('.$salary['lop_days'].' days)', 1, 0, 'L', true);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->Cell(48, 7, 'Rs.'.number_format($salary['lop_amount'],2), 1, 1, 'R', true);

    // Net Pay
    $pdf->SetFillColor(22, 163, 74);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Arial', 'B', 11);
    $pdf->Cell(186, 10, '  NET PAY:  Rs. '.number_format($salary['net_pay'],2), 1, 1, 'L', true);
    $pdf->SetTextColor(50, 50, 50);
} else {
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(150, 150, 150);
    $pdf->Cell(186, 8, '  No salary record found for '.$month.' '.$year, 1, 1, 'L');
    $pdf->SetTextColor(50, 50, 50);
}

// ── TASK SECTION ──
sectionHeader($pdf, 'TASK SUMMARY');

$pdf->SetFillColor(248, 250, 255);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(46, 7, '  Total Tasks: '.($tasks['total']??0), 1, 0, 'L', true);
$pdf->Cell(46, 7, '  Completed: '.($tasks['done']??0), 1, 0, 'L', true);
$pdf->Cell(46, 7, '  Pending: '.($tasks['pend']??0), 1, 0, 'L', true);
$pdf->Cell(48, 7, '  In Progress: '.($tasks['inprog']??0), 1, 1, 'L', true);

// ── PERFORMANCE SCORE ──
sectionHeader($pdf, 'PERFORMANCE SCORE');

$pdf->SetFillColor(248, 250, 255);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(62, 7, '  Task Score (40pts): '.$task_score, 1, 0, 'L', true);
$pdf->Cell(62, 7, '  Attendance Score (40pts): '.$att_score, 1, 0, 'L', true);
$pdf->Cell(62, 7, '  Skill Score (20pts): '.$skill_score, 1, 1, 'L', true);

// Score bar
$pdf->SetY($pdf->GetY() + 4);
$pdf->SetFont('Arial', 'B', 12);
$pdf->SetFillColor(26, 58, 110);
$pdf->SetTextColor(255, 255, 255);
$pdf->Cell(93, 12, '  OVERALL SCORE: '.$score.' / 100', 1, 0, 'L', true);
$pdf->SetFillColor(22, 163, 74);
$pdf->Cell(93, 12, '  GRADE: '.$grade, 1, 1, 'L', true);
$pdf->SetTextColor(50, 50, 50);

// Skills list
if(!empty($skills_list)){
    $pdf->SetY($pdf->GetY() + 4);
    $pdf->SetFont('Arial', 'B', 9);
    $pdf->SetFillColor(239, 246, 255);
    $pdf->Cell(186, 6, '  Skills: '.implode('  |  ', $skills_list), 1, 1, 'L', true);
}

// ── FOOTER ──
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(12, $pdf->GetY(), 198, $pdf->GetY());
$pdf->SetY($pdf->GetY() + 4);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 5, 'This is a system generated report. | Aller Technologies Pvt. Ltd. | Confidential', 0, 1, 'C');
$pdf->Cell(0, 5, 'Generated by: '.$_SESSION['user']['name'].' on '.date('d-m-Y h:i A'), 0, 1, 'C');

// Output
$filename = 'Employee_Report_'.$emp['first_name'].'_'.$emp['last_name'].'_'.$month.'_'.$year.'.pdf';
$pdf->Output('D', $filename);
?>
