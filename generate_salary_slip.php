<?php
session_start();
require 'db.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

require 'fpdf/fpdf.php';

$salary_id = (int) $_GET['salary_id'];

// Get salary details
$sal_result = mysqli_query($conn, "SELECT s.*, e.first_name, e.last_name, e.designation, e.emp_id, e.user_id
                                   FROM salary s
                                   JOIN employees e ON s.emp_id = e.emp_id
                                   WHERE s.salary_id = $salary_id");
$sal = mysqli_fetch_assoc($sal_result);

if(!$sal){
    die("Salary record not found!");
}

// SECURITY: an employee may only view/download their OWN salary slip.
// Previously any logged-in employee could change salary_id in the URL and
// download anyone else's confidential salary details.
if($_SESSION['user']['role'] === 'employee' && $sal['user_id'] != $_SESSION['user']['id']){
    header("Location: index.php");
    exit();
}

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();

// ── Header Background ──
$pdf->SetFillColor(26, 58, 110);
$pdf->Rect(0, 0, 210, 38, 'F');

// Company Name
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 20);
$pdf->SetY(7);
$pdf->Cell(0, 10, 'ALLER TECHNOLOGIES PVT. LTD.', 0, 1, 'C');

// Subtitle
$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 7, 'SALARY SLIP', 0, 1, 'C');
$pdf->SetFont('Arial', 'I', 9);
$pdf->Cell(0, 6, 'Executing Opportunities', 0, 1, 'C');

// ── Employee Info Box ──
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFillColor(240, 246, 255);
$pdf->Rect(10, 45, 190, 42, 'F');
$pdf->SetDrawColor(59, 130, 246);
$pdf->Rect(10, 45, 190, 42, 'D');

$pdf->SetFont('Arial', 'B', 10);
$pdf->SetXY(15, 49);
$pdf->SetTextColor(26, 58, 110);
$pdf->Cell(0, 7, 'EMPLOYEE DETAILS', 0, 1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(60, 60, 60);

// Row 1
$pdf->SetXY(15, 58);
$pdf->Cell(35, 6, 'Employee Name', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 6, ': '.$sal['first_name'].' '.$sal['last_name'], 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'Employee ID', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 6, ': EMP-'.$sal['emp_id'], 0, 1);

// Row 2
$pdf->SetFont('Arial', '', 10);
$pdf->SetXY(15, 67);
$pdf->Cell(35, 6, 'Designation', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(60, 6, ': '.$sal['designation'], 0, 0);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(30, 6, 'Pay Period', 0, 0);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 6, ': '.$sal['month'].' '.$sal['year'], 0, 1);

// ── Salary Table Header ──
$pdf->SetY(95);
$pdf->SetFillColor(26, 58, 110);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);
$pdf->Cell(95, 10, 'EARNINGS', 1, 0, 'C', true);
$pdf->Cell(95, 10, 'DEDUCTIONS', 1, 1, 'C', true);

// Row 1 — Basic Pay / Deductions
$pdf->SetFillColor(248, 250, 255);
$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(55, 9, '  Basic Pay', 1, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 9, 'Rs. '.number_format($sal['basic_pay'], 2), 1, 0, 'R', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(55, 9, '  PF Deduction', 1, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 9, 'Rs. '.number_format($sal['deductions'], 2), 1, 1, 'R', true);

// Row 2 — Allowances / LOP
$pdf->SetFillColor(255, 255, 255);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(55, 9, '  Allowances', 1, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 9, 'Rs. '.number_format($sal['allowances'], 2), 1, 0, 'R', true);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(55, 9, '  LOP ('.$sal['lop_days'].' days)', 1, 0, 'L', true);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(40, 9, 'Rs. '.number_format($sal['lop_amount'], 2), 1, 1, 'R', true);

// Total Row
$total_earnings = $sal['basic_pay'] + $sal['allowances'];
$pdf->SetFillColor(230, 240, 255);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(55, 9, '  Total Earnings', 1, 0, 'L', true);
$pdf->Cell(40, 9, 'Rs. '.number_format($total_earnings, 2), 1, 0, 'R', true);
$pdf->Cell(55, 9, '  Total Deductions', 1, 0, 'L', true);
$pdf->Cell(40, 9, 'Rs. '.number_format($sal['deductions'], 2), 1, 1, 'R', true);

// ── Net Pay Box ──
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetFillColor(22, 163, 74);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 14, 'NET PAY:  Rs. '.number_format($sal['net_pay'], 2), 1, 1, 'C', true);

// ── Note ──
$pdf->SetY($pdf->GetY() + 10);
$pdf->SetTextColor(26, 58, 110);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(0, 6, 'Amount in Words: '.numberToWords($sal['net_pay']).' Rupees Only', 0, 1, 'C');

// ── Footer ──
$pdf->SetY($pdf->GetY() + 15);
$pdf->SetDrawColor(200, 200, 200);
$pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
$pdf->SetY($pdf->GetY() + 5);
$pdf->SetTextColor(150, 150, 150);
$pdf->SetFont('Arial', 'I', 8);
$pdf->Cell(0, 5, 'This is a system generated salary slip and does not require a signature.', 0, 1, 'C');
$pdf->Cell(0, 5, 'Generated on: '.date('d-m-Y').' | Aller Technologies Pvt. Ltd.', 0, 1, 'C');

// ── Number to Words function ──
function numberToWords($num){
    $num = (int)$num;
    $ones = ['','One','Two','Three','Four','Five','Six','Seven','Eight','Nine',
             'Ten','Eleven','Twelve','Thirteen','Fourteen','Fifteen','Sixteen',
             'Seventeen','Eighteen','Nineteen'];
    $tens = ['','','Twenty','Thirty','Forty','Fifty','Sixty','Seventy','Eighty','Ninety'];

    if($num < 20) return $ones[$num];
    if($num < 100) return $tens[intval($num/10)].($num%10?' '.$ones[$num%10]:'');
    if($num < 1000) return $ones[intval($num/100)].' Hundred'.($num%100?' '.numberToWords($num%100):'');
    if($num < 100000) return numberToWords(intval($num/1000)).' Thousand'.($num%1000?' '.numberToWords($num%1000):'');
    if($num < 10000000) return numberToWords(intval($num/100000)).' Lakh'.($num%100000?' '.numberToWords($num%100000):'');
    return numberToWords(intval($num/10000000)).' Crore'.($num%10000000?' '.numberToWords($num%10000000):'');
}

// Output PDF
$pdf->Output('D', 'Salary_Slip_'.$sal['first_name'].'_'.$sal['month'].'_'.$sal['year'].'.pdf');
?>