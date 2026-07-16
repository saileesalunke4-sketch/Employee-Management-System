<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Role & Department Updates";

// Employees list (for the picker) + their current dept/designation/location
$emp_list = [];
$emp_res = mysqli_query($conn, "SELECT e.emp_id, e.first_name, e.last_name, e.designation, e.work_location, e.dept_id, d.dept_name
                                 FROM employees e LEFT JOIN departments d ON e.dept_id=d.dept_id
                                 ORDER BY e.first_name, e.last_name");
while($er = mysqli_fetch_assoc($emp_res)){ $emp_list[] = $er; }

// Departments list (for the dropdown)
$dept_list = [];
$dept_res = mysqli_query($conn, "SELECT dept_id, dept_name FROM departments ORDER BY dept_name");
while($dr = mysqli_fetch_assoc($dept_res)){ $dept_list[] = $dr; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Role & Department Updates - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.emp-current-box{background:#f8fafc;border:1px solid #eef0f4;border-radius:10px;padding:12px 16px;font-size:12px;color:#555;margin-top:-6px;margin-bottom:16px;display:none;}
.emp-current-box b{color:#1a1a2e;}
</style>
</head>
<body>
<div class="dashboard <?php echo $role==='admin' ? 'admin-theme' : 'super-theme'; ?>">
<?php if($role === 'admin'){ include('sidebar_admin.php'); } else { include('sidebar_sa.php'); } ?>
<div class="main-content">
<?php if($role === 'admin'){ include('topbar_admin.php'); } else { include('topbar_sa.php'); } ?>

<div class="section active">

    <?php if(isset($_GET['hr_msg']) && in_array($_GET['hr_msg'], ['approved','rejected'])):
        $hr_is_approved = $_GET['hr_msg'] === 'approved';
        $hr_emp  = htmlspecialchars($_GET['hr_emp'] ?? '');
        $hr_type = htmlspecialchars($_GET['hr_type'] ?? '');
    ?>
    <div style="background:<?php echo $hr_is_approved?'#dcfce7':'#fee2e2'; ?>;border:1px solid <?php echo $hr_is_approved?'#86efac':'#fca5a5'; ?>;color:<?php echo $hr_is_approved?'#166534':'#7f1d1d'; ?>;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-size:14px;">
        ✅ <b><?php echo $hr_type; ?></b> for <b><?php echo $hr_emp; ?></b> has been updated successfully.
    </div>
    <?php endif; ?>

    <div class="timeline-card" style="margin-top:0;">
        <h3 style="margin-bottom:6px;">📋 Update Employee Role / Department</h3>
        <p style="font-size:12px;color:#888;margin-bottom:16px;">
            This is decided by Admin / Super Admin only — based on employee performance.
            Employees can no longer request these changes themselves.
        </p>

        <form action="save_role_update.php" method="POST" id="roleUpdateForm">
            <input type="hidden" name="csrf" value="<?php echo csrf_token(); ?>">
            <div class="form-grid">
                <div class="field">
                    <label>Employee</label>
                    <select name="emp_id" id="empSelect" required>
                        <option value="">-- Select Employee --</option>
                        <?php foreach($emp_list as $e): ?>
                        <option value="<?php echo (int)$e['emp_id']; ?>"
                            data-dept="<?php echo htmlspecialchars($e['dept_name'] ?: '-'); ?>"
                            data-desig="<?php echo htmlspecialchars($e['designation'] ?: '-'); ?>"
                            data-loc="<?php echo htmlspecialchars($e['work_location'] ?: '-'); ?>">
                            <?php echo htmlspecialchars($e['first_name'].' '.$e['last_name']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label>Change Type</label>
                    <select name="request_type" id="changeType" required>
                        <option value="">-- Select --</option>
                        <option value="Department Change">Department Change</option>
                        <option value="Designation Change">Designation Change</option>
                        <option value="Location Change">Location Change</option>
                    </select>
                </div>

                <div class="field" id="deptFieldWrap" style="display:none;">
                    <label>New Department</label>
                    <select name="requested_department">
                        <option value="">-- Select Department --</option>
                        <?php foreach($dept_list as $d): ?>
                        <option value="<?php echo htmlspecialchars($d['dept_name']); ?>"><?php echo htmlspecialchars($d['dept_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field" id="desigFieldWrap" style="display:none;">
                    <label>New Designation</label>
                    <input type="text" name="requested_designation" placeholder="e.g. Senior Developer">
                </div>

                <div class="field" id="locFieldWrap" style="display:none;">
                    <label>New Work Location</label>
                    <input type="text" name="requested_location" placeholder="e.g. Pune Office / Remote">
                </div>

                <div class="field" style="grid-column:1/-1">
                    <label>Note (performance basis / reason for this change)</label>
                    <textarea name="reason" rows="2" placeholder="e.g. Promoted based on Q2 performance review" required></textarea>
                </div>
            </div>

            <div class="emp-current-box" id="empCurrentBox">
                Current — Department: <b id="curDept">-</b> &middot; Designation: <b id="curDesig">-</b> &middot; Location: <b id="curLoc">-</b>
            </div>

            <button type="submit" class="submit-btn">Apply Update</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">History of Role & Department Updates</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Type</th><th>Previous</th><th>Updated To</th><th>Note</th><th>Date</th></tr></thead>
            <tbody>
            <?php
                $hist_res = mysqli_query($conn, "SELECT h.*, e.first_name, e.last_name FROM hr_process_requests h JOIN employees e ON h.emp_id=e.emp_id ORDER BY h.request_id DESC LIMIT 50");
                if(mysqli_num_rows($hist_res) === 0){
                    echo "<tr><td colspan='6' style='text-align:center;color:#9ca3af;padding:16px;'>No updates made yet.</td></tr>";
                } else {
                    while($hr = mysqli_fetch_assoc($hist_res)){
                        echo "<tr>
                            <td>{$hr['first_name']} {$hr['last_name']}</td>
                            <td>".htmlspecialchars($hr['request_type'])."</td>
                            <td>".htmlspecialchars($hr['current_value'])."</td>
                            <td>".htmlspecialchars($hr['requested_value'])."</td>
                            <td>".htmlspecialchars($hr['reason'])."</td>
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
const empSelect = document.getElementById('empSelect');
const changeType = document.getElementById('changeType');
const deptWrap  = document.getElementById('deptFieldWrap');
const desigWrap = document.getElementById('desigFieldWrap');
const locWrap   = document.getElementById('locFieldWrap');
const curBox = document.getElementById('empCurrentBox');
const curDept = document.getElementById('curDept');
const curDesig = document.getElementById('curDesig');
const curLoc = document.getElementById('curLoc');

function updateCurrentBox(){
    const opt = empSelect.options[empSelect.selectedIndex];
    if(!opt || !opt.value){ curBox.style.display = 'none'; return; }
    curDept.textContent = opt.dataset.dept || '-';
    curDesig.textContent = opt.dataset.desig || '-';
    curLoc.textContent = opt.dataset.loc || '-';
    curBox.style.display = 'block';
}
empSelect.addEventListener('change', updateCurrentBox);

changeType.addEventListener('change', function(){
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
