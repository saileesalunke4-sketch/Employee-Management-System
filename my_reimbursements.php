<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';
$user_id = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp = mysqli_fetch_assoc($emp_result);
$emp_id = $emp['emp_id'];
$page_title = "Reimbursements";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Reimbursements - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard emp-theme">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>
<div class="app-content">

<div class="section active">

    <?php if(isset($_GET['sent'])): ?>
        <div class="form-card" style="background:#f0fdf4;border:1px solid #86efac;margin-bottom:16px;">
            Reimbursement request submitted — waiting for admin approval.
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Submit Reimbursement Request</h3>
        <form action="save_reimbursement.php" method="POST" enctype="multipart/form-data">
            <div class="form-grid">
                <div class="field">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="">-- Select --</option>
                        <option value="Travel">Travel</option>
                        <option value="Food">Food</option>
                        <option value="Internet/Phone">Internet/Phone</option>
                        <option value="Medical">Medical</option>
                        <option value="Office Supplies">Office Supplies</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="field"><label>Amount (₹)</label><input type="number" name="amount" step="0.01" min="0.01" placeholder="0.00" required></div>
                <div class="field" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="3" placeholder="What was this expense for?" required></textarea></div>
                <div class="field" style="grid-column:1/-1">
                    <label>Receipt (optional — PDF, JPG or PNG, max 5 MB)</label>
                    <input type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>
            <button type="submit" class="submit-btn">Submit Request</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My Reimbursement Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Category</th><th>Amount</th><th>Description</th><th>Receipt</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res = mysqli_query($conn, "SELECT * FROM reimbursement_requests WHERE emp_id='$emp_id' ORDER BY request_id DESC");
                if(mysqli_num_rows($res) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:var(--text-3,#9aa1ac);'>No reimbursement requests yet.</td></tr>";
                }
                while($row = mysqli_fetch_assoc($res)){
                    $can_cancel = ($row['status'] === 'pending');
                    $receipt_cell = $row['receipt_filename']
                        ? "<a href='uploads/receipts/".htmlspecialchars($row['receipt_filename'])."' target='_blank' style='color:var(--role-accent,#4F46E5);font-weight:600;'>View</a>"
                        : "<span style='color:#9ca3af;'>-</span>";
                    echo "<tr>
                        <td>".htmlspecialchars($row['category'])."</td>
                        <td>₹".number_format($row['amount'], 2)."</td>
                        <td>".htmlspecialchars($row['description'])."</td>
                        <td>{$receipt_cell}</td>
                        <td><span class='status-pill {$row['status']}'>".ucfirst($row['status'])."</span></td>
                        <td>" . ($can_cancel
                            ? "<a href='cancel_reimbursement.php?id={$row['request_id']}&csrf=".urlencode(csrf_token())."' onclick=\"return confirm('Cancel this request?');\" style='color:#dc2626;font-size:12px;font-weight:600;text-decoration:none;'>Cancel</a>"
                            : "<span style='color:#9ca3af;font-size:12px;'>-</span>") . "</td>
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
<style>
.status-pill{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.cancelled{background:#f3f4f6;color:#6b7280;}
</style>
<?php include 'common_js.php'; ?>
</body>
</html>
