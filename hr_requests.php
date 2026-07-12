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
$page_title = "HR Process Requests";

// Fetch current department name for display / default value
$current_dept_name = '-';
if($emp['dept_id']){
    $d = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_name FROM departments WHERE dept_id='".(int)$emp['dept_id']."'"));
    $current_dept_name = $d ? $d['dept_name'] : '-';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>HR Process Requests - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">Raise an HR Process Request</h3>
        <p style="font-size:12px;color:#888;margin-top:-6px;margin-bottom:14px;">
            Current Department: <b><?php echo htmlspecialchars($current_dept_name); ?></b> &middot;
            Current Designation: <b><?php echo htmlspecialchars($emp['designation'] ?: '-'); ?></b> &middot;
            Current Location: <b><?php echo htmlspecialchars($emp['work_location'] ?: '-'); ?></b>
        </p>

        <form action="save_hr_request.php" method="POST" id="hrRequestForm">
            <div class="form-grid">
                <div class="field">
                    <label>Request Type</label>
                    <select name="request_type" id="requestType" required>
                        <option value="">-- Select --</option>
                        <option value="Department Change">Department Change</option>
                        <option value="Designation Change">Designation Change</option>
                        <option value="Location Change">Location Change</option>
                    </select>
                </div>

                <div class="field" id="deptFieldWrap" style="display:none;">
                    <label>Requested Department</label>
                    <select name="requested_department">
                        <option value="">-- Select Department --</option>
                        <?php
                            $dres = mysqli_query($conn, "SELECT * FROM departments ORDER BY dept_name");
                            while($dd = mysqli_fetch_assoc($dres)){
                                echo "<option value='".htmlspecialchars($dd['dept_name'])."'>".htmlspecialchars($dd['dept_name'])."</option>";
                            }
                        ?>
                    </select>
                </div>

                <div class="field" id="desigFieldWrap" style="display:none;">
                    <label>Requested Designation</label>
                    <input type="text" name="requested_designation" placeholder="e.g. Senior Developer">
                </div>

                <div class="field" id="locFieldWrap" style="display:none;">
                    <label>Requested Location</label>
                    <input type="text" name="requested_location" placeholder="e.g. Pune Office / Remote">
                </div>

                <div class="field" style="grid-column:1/-1">
                    <label>Reason</label>
                    <textarea name="reason" rows="2" placeholder="Why are you requesting this change?" required></textarea>
                </div>
            </div>
            <button type="submit" class="submit-btn">Submit Request</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">My HR Process Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Type</th><th>Current Value</th><th>Requested Value</th><th>Reason</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php
                $hr_res = mysqli_query($conn, "SELECT * FROM hr_process_requests WHERE emp_id='$emp_id' ORDER BY request_id DESC");
                if(mysqli_num_rows($hr_res) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:#9ca3af;padding:16px;'>No HR process requests yet.</td></tr>";
                } else {
                    while($hr = mysqli_fetch_assoc($hr_res)){
                        echo "<tr>
                            <td>".htmlspecialchars($hr['request_type'])."</td>
                            <td>".htmlspecialchars($hr['current_value'] ?: '-')."</td>
                            <td>".htmlspecialchars($hr['requested_value'])."</td>
                            <td>".htmlspecialchars($hr['reason'])."</td>
                            <td><span class='status-pill {$hr['status']}'>".ucfirst($hr['status'])."</span></td>
                            <td>".date('d M Y', strtotime($hr['created_at']))."</td>
                        </tr>";
                    }
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

</div>

</div>
</div>

<script>
const requestType = document.getElementById('requestType');
const deptWrap  = document.getElementById('deptFieldWrap');
const desigWrap = document.getElementById('desigFieldWrap');
const locWrap   = document.getElementById('locFieldWrap');

requestType.addEventListener('change', function(){
    deptWrap.style.display  = 'none';
    desigWrap.style.display = 'none';
    locWrap.style.display   = 'none';

    if(this.value === 'Department Change') deptWrap.style.display = 'block';
    else if(this.value === 'Designation Change') desigWrap.style.display = 'block';
    else if(this.value === 'Location Change') locWrap.style.display = 'block';
});
</script>

<?php include 'common_js.php'; ?>
</body>
</html>
