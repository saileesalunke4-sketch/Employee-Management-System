<?php
session_start();
require 'db.php';
require 'mailer.php';

if(!isset($_SESSION['user'])){
    header("Location: index.php");
    exit();
}

require 'fpdf/fpdf.php';

$salary_id = $_GET['salary_id'];

// Get salary details
$sal_result = mysqli_query($conn, "SELECT s.*, e.first_name, e.last_name, e.designation, e.emp_id, u.email
                                   FROM salary s 
                                   JOIN employees e ON s.emp_id = e.emp_id 
                                   JOIN users u ON e.user_id = u.id
                                   WHERE s.salary_id = '$salary_id'");
$sal = mysqli_fetch_assoc($sal_result);

if(!$sal){
    die("Salary record not found!");
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

// Row 1
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

// Row 2
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

// ── Number to Words ──
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

// ── PDF ko string mein save karo email ke liye ──
$pdf_content = $pdf->Output('S'); // S = string return karta hai

// ── Email bhejo employee ko ──
$emp_name  = $sal['first_name'] . ' ' . $sal['last_name'];
$emp_email = $sal['email'];

$subject = "Salary Slip — " . $sal['month'] . " " . $sal['year'] . " | Aller Technologies";
$body = "
<div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;border:1px solid #e0e0e0;border-radius:10px;overflow:hidden;'>
    <div style='background:#1a3a6e;padding:20px;text-align:center;'>
        <h2 style='color:white;margin:0;'>Aller Technologies EMS</h2>
    </div>
    <div style='padding:24px;'>
        <p style='font-size:15px;'>Dear <b>$emp_name</b>,</p>
        <p style='font-size:14px;color:#444;'>Your salary slip for <b>{$sal['month']} {$sal['year']}</b> has been generated.</p>
        <table style='width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;'>
            <tr><td style='padding:8px;color:#888;'>Basic Pay</td><td><b>Rs. ".number_format($sal['basic_pay'],2)."</b></td></tr>
            <tr style='background:#f9f9f9;'><td style='padding:8px;color:#888;'>Allowances</td><td><b>Rs. ".number_format($sal['allowances'],2)."</b></td></tr>
            <tr><td style='padding:8px;color:#888;'>Deductions</td><td><b>Rs. ".number_format($sal['deductions'],2)."</b></td></tr>
            <tr><td style='padding:8px;color:#888;'>LOP (".$sal['lop_days']." days)</td><td><b>Rs. ".number_format($sal['lop_amount'],2)."</b></td></tr>
            <tr style='background:#e8f5e9;'><td style='padding:8px;color:#16a34a;font-weight:bold;'>Net Pay</td><td><b style='color:#16a34a;'>Rs. ".number_format($sal['net_pay'],2)."</b></td></tr>
        </table>
        <p style='font-size:13px;color:#666;'>Salary slip PDF is attached with this email.</p>
        <p style='font-size:13px;color:#666;margin-top:20px;'>Regards,<br><b>HR Team — Aller Technologies</b></p>
    </div>
</div>";

// Email with PDF attachment
$mail_obj = new PHPMailer\PHPMailer\PHPMailer(true);
try {
    $mail_obj->isSMTP();
    $mail_obj->Host       = 'smtp.gmail.com';
    $mail_obj->SMTPAuth   = true;
    $mail_obj->Username   = 'saileesalunke4@gmail.com'; // apni Gmail
    $mail_obj->Password   = 'pssjxueocublnqvr';       // 16 digit app password
    $mail_obj->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail_obj->Port       = 587;

    $mail_obj->setFrom('aapki_gmail@gmail.com', 'Aller Technologies EMS');
    $mail_obj->addAddress($emp_email, $emp_name);

    // PDF attachment add karo
    $mail_obj->addStringAttachment($pdf_content, 'Salary_Slip_'.$sal['month'].'_'.$sal['year'].'.pdf');

    $mail_obj->isHTML(true);
    $mail_obj->Subject = $subject;
    $mail_obj->Body    = $body;

    $mail_obj->send();
} catch (Exception $e) {
    // Email fail hogi toh bhi PDF download hoga
}

// ── PDF Download ──
$pdf->Output('D', 'Salary_Slip_'.$sal['first_name'].'_'.$sal['month'].'_'.$sal['year'].'.pdf');
?>