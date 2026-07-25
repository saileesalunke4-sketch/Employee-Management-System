<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Reimbursement Requests";
$csrf_tok = csrf_token();

$total_pending_amount = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(SUM(amount),0) as t FROM reimbursement_requests WHERE status='pending'"))['t'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reimbursement Requests - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard <?php echo $role==='admin' ? 'admin-theme' : 'super-theme'; ?>">
<?php if($role === 'admin'){ include('sidebar_admin.php'); } else { include('sidebar_sa.php'); } ?>
<div class="main-content">
<?php if($role === 'admin'){ include('topbar_admin.php'); } else { include('topbar_sa.php'); } ?>
<div class="app-content">

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Reimbursement Requests</h3>
        <p style="font-size:12.5px;color:var(--text-3,#9aa1ac);margin-top:-6px;">
            Total pending amount: <strong>₹<?php echo number_format($total_pending_amount, 2); ?></strong>
        </p>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Category</th><th>Amount</th><th>Description</th><th>Receipt</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res = mysqli_query($conn, "SELECT r.*, e.first_name, e.last_name FROM reimbursement_requests r
                                             JOIN employees e ON r.emp_id = e.emp_id
                                             ORDER BY r.request_id DESC");
                if(mysqli_num_rows($res) === 0){
                    echo "<tr><td colspan='7' style='text-align:center;color:var(--text-3,#9aa1ac);'>No reimbursement requests yet.</td></tr>";
                }
                while($row = mysqli_fetch_assoc($res)){
                    $pc = ['approved'=>'green','rejected'=>'red','pending'=>'yellow','cancelled'=>'gray'][$row['status']] ?? 'yellow';
                    $receipt_cell = $row['receipt_filename']
                        ? "<a href='uploads/receipts/".htmlspecialchars($row['receipt_filename'])."' target='_blank' style='color:var(--role-accent,#4F46E5);font-weight:600;'>View</a>"
                        : "<span style='color:#9ca3af;'>-</span>";
                    $action_cell = ($row['status'] === 'pending')
                        ? "<a href='handle_reimbursement.php?id={$row['request_id']}&action=approved&csrf={$csrf_tok}' class='approve-btn'>Approve</a>
                           <a href='handle_reimbursement.php?id={$row['request_id']}&action=rejected&csrf={$csrf_tok}' class='reject-btn'>Reject</a>"
                        : "<span style='color:#9ca3af;font-size:12px;'>-</span>";
                    echo "<tr>
                        <td>".htmlspecialchars($row['first_name'].' '.$row['last_name'])."</td>
                        <td>".htmlspecialchars($row['category'])."</td>
                        <td>₹".number_format($row['amount'], 2)."</td>
                        <td>".htmlspecialchars($row['description'])."</td>
                        <td>{$receipt_cell}</td>
                        <td><span class='pill {$pc}'>".ucfirst($row['status'])."</span></td>
                        <td><div class='row-actions'>{$action_cell}</div></td>
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
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
