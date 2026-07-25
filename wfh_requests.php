<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "WFH Requests";
$csrf_tok = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>WFH Requests - EMS</title>
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
        <h3 class="section-title">Work From Home Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Reason</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res = mysqli_query($conn, "SELECT w.*, e.first_name, e.last_name FROM wfh_requests w
                                             JOIN employees e ON w.emp_id = e.emp_id
                                             ORDER BY w.request_id DESC");
                if(mysqli_num_rows($res) === 0){
                    echo "<tr><td colspan='5' style='text-align:center;color:var(--text-3,#9aa1ac);'>No WFH requests yet.</td></tr>";
                }
                while($row = mysqli_fetch_assoc($res)){
                    $pc = ['approved'=>'green','rejected'=>'red','pending'=>'yellow','cancelled'=>'gray'][$row['status']] ?? 'yellow';
                    $action_cell = ($row['status'] === 'pending')
                        ? "<a href='handle_wfh_request.php?id={$row['request_id']}&action=approved&csrf={$csrf_tok}' class='approve-btn'>Approve</a>
                           <a href='handle_wfh_request.php?id={$row['request_id']}&action=rejected&csrf={$csrf_tok}' class='reject-btn'>Reject</a>"
                        : "<span style='color:#9ca3af;font-size:12px;'>-</span>";
                    echo "<tr>
                        <td>".htmlspecialchars($row['first_name'].' '.$row['last_name'])."</td>
                        <td>{$row['wfh_date']}</td>
                        <td>".htmlspecialchars($row['reason'])."</td>
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
