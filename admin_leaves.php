<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Leaves";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Leaves - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.badge-sabbatical{display:inline-block;background:#f3e8ff;color:#7c3aed;border:1px solid #d8b4fe;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;}
.badge-sandwich{display:inline-block;background:#fef3c7;color:#92400e;border:1px solid #fcd34d;border-radius:20px;padding:2px 10px;font-size:11px;font-weight:700;}
.days-cell{text-align:center;}
.days-main{font-size:15px;font-weight:700;color:#1a1a2e;}
.days-extra{font-size:11px;color:#92400e;margin-top:2px;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_admin.php'; ?>
<div class="main-content">
<?php include 'topbar_admin.php'; ?>

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Leave Requests</h3>
        <div style="text-align:right;margin-bottom:12px;">
            <a href="export_leaves_report.php" style="display:inline-block;background:#16a34a;color:white;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;">📥 Download Excel</a>
        </div>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days Deducted</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php
                $res = mysqli_query($conn, "SELECT l.*, e.first_name, e.last_name FROM leaves l JOIN employees e ON l.emp_id=e.emp_id ORDER BY l.leave_id DESC");
                while($row = mysqli_fetch_assoc($res)){

                    // Base days
                    $cal_days = (strtotime($row['to_date']) - strtotime($row['from_date'])) / 86400 + 1;

                    // Sandwich calculation
                    // BUGFIX: only applies to a multi-day range — skip
                    // entirely for a single-day request regardless of what
                    // day of the week it falls on.
                    $from_day = date('N', strtotime($row['from_date'])); // 5=Fri, 1=Mon
                    $to_day   = date('N', strtotime($row['to_date']));
                    $sandwich_days = 0;
                    $sandwich_label = '';
                    if ($cal_days <= 1) {
                        // single day — no sandwich possible
                        $total_days = $cal_days;
                    } elseif ($from_day == 5 && $to_day == 1) {
                        $sandwich_days = 0; // weekend already inside range
                        $sandwich_label = 'Fri–Mon (Weekend Included)';
                        $total_days = $cal_days + $sandwich_days;
                    } elseif ($from_day == 5) {
                        $sandwich_days = 2;
                        $sandwich_label = '+2 (Sat & Sun)';
                        $total_days = $cal_days + $sandwich_days;
                    } elseif ($to_day == 1) {
                        $sandwich_days = 1;
                        $sandwich_label = '+1 (Sun)';
                        $total_days = $cal_days + $sandwich_days;
                    } else {
                        // BUGFIX: not a sandwich pattern — exclude Sunday
                        // (weekly off) from the count instead of charging
                        // every calendar day, matching db.php's
                        // getLeaveDaysWithSandwich() so this display always
                        // agrees with what the employee's own page shows.
                        $total_days = 0;
                        for($ts = strtotime($row['from_date']); $ts <= strtotime($row['to_date']); $ts += 86400){
                            if(date('N', $ts) != 7) $total_days++;
                        }
                    }
                    if(!empty($row['is_half_day'])) $total_days = 0.5;

                    $pc = ['approved'=>'green','rejected'=>'red','pending'=>'yellow','cancelled'=>'gray'][$row['status']] ?? 'yellow';
                    $csrf_tok = csrf_token();

                    // Sabbatical badge
                    $type_display = $row['leave_type'];
                    if ($row['leave_type'] === 'Sabbatical') {
                        $type_display = "<span class='badge-sabbatical'>🧘 Sabbatical</span><br><small style='color:#6b7280;font-size:11px;'>Unpaid · Once in career</small>";
                    }
                    if (!empty($row['is_half_day'])) {
                        $type_display .= " <span style='background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;'>HALF DAY</span>";
                    }

                    // Days cell
                    $days_cell = "<div class='days-cell'><span class='days-main'>{$total_days}</span>";
                    if ($sandwich_days > 0) {
                        $days_cell .= "<br><span class='badge-sandwich'>🥪 Sandwich: {$sandwich_label}</span>";
                    } elseif (!empty($sandwich_label)) {
                        $days_cell .= "<br><span class='badge-sandwich'>🥪 {$sandwich_label}</span>";
                    }
                    $days_cell .= "</div>";

                    echo "<tr>
                        <td>{$row['first_name']} {$row['last_name']}</td>
                        <td>{$type_display}</td>
                        <td>{$row['from_date']}</td>
                        <td>{$row['to_date']}</td>
                        <td>{$days_cell}</td>
                        <td>{$row['reason']}</td>
                        <td><span class='pill {$pc}'>".ucfirst($row['status'])."</span></td>
                        <td>
                            <div class='row-actions'>";
                    if($row['status'] === 'pending'){
                        echo "<a href='leave_action.php?id={$row['leave_id']}&action=approved&redirect=admin_leaves.php&csrf={$csrf_tok}' class='approve-btn'>Approve</a>
                                <a href='leave_action.php?id={$row['leave_id']}&action=rejected&redirect=admin_leaves.php&csrf={$csrf_tok}' class='reject-btn'>Reject</a>";
                    } else {
                        echo "<span style='color:#9ca3af;font-size:12px;'>-</span>";
                    }
                    echo "</div>
                        </td>
                    </tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
