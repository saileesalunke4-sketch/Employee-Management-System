<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';

$user_id    = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];
$emp_name   = $emp['first_name'].' '.$emp['last_name'];

// Fetch all leaves
$res = mysqli_query($conn, "SELECT * FROM leaves WHERE emp_id='$emp_id' ORDER BY leave_id DESC");

$rows        = [];
$total_days  = 0;
$pending     = 0;
$approved    = 0;
$rejected    = 0;

while($r = mysqli_fetch_assoc($res)){
    $days = (strtotime($r['to_date']) - strtotime($r['from_date'])) / 86400 + 1;

    // Sandwich calculation
    $from_day = date('N', strtotime($r['from_date']));
    $to_day   = date('N', strtotime($r['to_date']));
    $sandwich = 0;
    if($from_day == 5 && $to_day == 1)      $sandwich = 0;
    elseif($from_day == 5)                   $sandwich = 2;
    elseif($to_day == 1)                     $sandwich = 1;

    $r['total_days'] = $days + $sandwich;
    $r['sandwich']   = $sandwich;
    $total_days     += $r['total_days'];
    if($r['status'] == 'pending')  $pending++;
    if($r['status'] == 'approved') $approved++;
    if($r['status'] == 'rejected') $rejected++;
    $rows[] = $r;
}

// Excel headers
$filename = "Leave_History_{$emp_name}_".date('d_M_Y').".xls";
$filename = str_replace(' ', '_', $filename);

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<html>
<head>
<meta charset="UTF-8">
<style>
    body  { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }
    .title { font-size: 16pt; font-weight: bold; color: #1a1a2e; }
    table { border-collapse: collapse; width: 100%; table-layout: auto; }
    th    { background-color: #1a1a2e; color: white; padding: 10px 16px; text-align: center; border: 1px solid #bbb; white-space: nowrap; font-size: 11pt; }
    td    { padding: 8px 16px; border: 1px solid #ddd; text-align: center; white-space: nowrap; font-size: 11pt; min-width: 110px; }
    tr:nth-child(even) { background-color: #f9fafb; }
    .approved  { background-color: #dcfce7; color: #16a34a; font-weight: 600; }
    .rejected  { background-color: #fee2e2; color: #dc2626; font-weight: 600; }
    .pending   { background-color: #fef3c7; color: #d97706; font-weight: 600; }
    .sandwich  { background-color: #fef9c3; color: #92400e; font-size: 10px; }
    .summary   { background-color: #eff6ff; font-weight: bold; }
    .stat-box  { display: inline-block; padding: 6px 18px; border-radius: 6px; margin-right: 10px; font-weight: 700; font-size: 13px; }
</style>
</head>
<body>

<p class="title">🌿 Employee Leave History</p>
<p style="font-size:12px;color:#6b7280;">
    Employee: <strong><?php echo $emp_name; ?></strong> &nbsp;|&nbsp;
    Employee ID: <strong><?php echo $emp_id; ?></strong> &nbsp;|&nbsp;
    Generated: <strong><?php echo date('d M Y, h:i A'); ?></strong>
</p>

<!-- Summary -->
<table style="width:auto;margin-bottom:16px;">
    <tr>
        <th style="background:#1a1a2e;">Total Leaves Taken</th>
        <th style="background:#16a34a;">Approved</th>
        <th style="background:#d97706;">Pending</th>
        <th style="background:#dc2626;">Rejected</th>
        <th style="background:#7c3aed;">Total Days</th>
    </tr>
    <tr>
        <td><?php echo count($rows); ?></td>
        <td class="approved"><?php echo $approved; ?></td>
        <td class="pending"><?php echo $pending; ?></td>
        <td class="rejected"><?php echo $rejected; ?></td>
        <td><strong><?php echo $total_days; ?> days</strong></td>
    </tr>
</table>

<!-- Detail Table -->
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Leave Type</th>
            <th>From Date</th>
            <th>To Date</th>
            <th>Days Deducted</th>
            <th>Sandwich Policy</th>
            <th>Reason</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if(empty($rows)){
        echo "<tr><td colspan='8' style='text-align:center;color:#9ca3af;'>No leave records found.</td></tr>";
    }
    $sr = 1;
    foreach($rows as $row){
        $sandwich_txt = '-';
        if($row['sandwich'] == 2) $sandwich_txt = '🥪 +2 days (Sat & Sun)';
        if($row['sandwich'] == 1) $sandwich_txt = '🥪 +1 day (Sun)';

        $sab_label = $row['leave_type'];
        if($row['leave_type'] == 'Sabbatical') $sab_label = '🧘 Sabbatical (Unpaid)';

        echo "<tr>
            <td>{$sr}</td>
            <td>{$sab_label}</td>
            <td>{$row['from_date']}</td>
            <td>{$row['to_date']}</td>
            <td><strong>{$row['total_days']}</strong></td>
            <td class='sandwich'>{$sandwich_txt}</td>
            <td>{$row['reason']}</td>
            <td class='{$row['status']}'>".ucfirst($row['status'])."</td>
        </tr>";
        $sr++;
    }
    ?>
    <!-- Total Row -->
    <tr class="summary">
        <td colspan="4" style="text-align:right;"><strong>Total Days Deducted:</strong></td>
        <td><strong><?php echo $total_days; ?> days</strong></td>
        <td colspan="3"></td>
    </tr>
    </tbody>
</table>

<br>
<p style="font-size:11px;color:#9ca3af;">
    * Sandwich Policy: Weekend days included if leave taken on Friday/Monday &nbsp;|&nbsp;
    * Generated by EMS — Aller Technologies
</p>

</body>
</html>
<?php mysqli_close($conn); ?>
