<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Attendance";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Attendance - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">
    <?php if(isset($_GET['rr_msg']) && in_array($_GET['rr_msg'], ['approved','rejected'])):
        $rr_is_approved = $_GET['rr_msg'] === 'approved';
        $rr_emp  = htmlspecialchars($_GET['rr_emp'] ?? '');
        $rr_date = htmlspecialchars($_GET['rr_date'] ?? '');
    ?>
    <div style="background:<?php echo $rr_is_approved?'#dcfce7':'#fee2e2'; ?>;border:1px solid <?php echo $rr_is_approved?'#86efac':'#fca5a5'; ?>;color:<?php echo $rr_is_approved?'#166534':'#7f1d1d'; ?>;padding:14px 18px;border-radius:10px;margin-bottom:18px;font-size:14px;">
        <?php echo $rr_is_approved ? '✅' : '❌'; ?>
        Regularization request for <b><?php echo $rr_emp; ?></b> (<?php echo $rr_date; ?>) has been
        <b><?php echo ucfirst($_GET['rr_msg']); ?></b>.
    </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Pending Regularization Requests</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Requested Check In</th><th>Requested Check Out</th><th>Requested Status</th><th>Reason</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $rr_res = mysqli_query($conn, "SELECT r.*, e.first_name, e.last_name FROM regularization_requests r JOIN employees e ON r.emp_id=e.emp_id WHERE r.status='pending' ORDER BY r.request_id DESC");
                if(mysqli_num_rows($rr_res) === 0){
                    echo "<tr><td colspan='7' style='text-align:center;color:#9ca3af;padding:16px;'>No pending regularization requests.</td></tr>";
                } else {
                    while($rr = mysqli_fetch_assoc($rr_res)){
                        echo "<tr>
                            <td>{$rr['first_name']} {$rr['last_name']}</td>
                            <td>{$rr['att_date']}</td>
                            <td>".($rr['requested_check_in'] ?: '-')."</td>
                            <td>".($rr['requested_check_out'] ?: '-')."</td>
                            <td>".ucfirst(str_replace('_',' ',$rr['requested_status']))."</td>
                            <td>".htmlspecialchars($rr['reason'])."</td>
                            <td>
                                <a href='handle_regularization.php?id={$rr['request_id']}&action=approved&redirect=sa_attendance.php&csrf=".csrf_token()."' class='approve-btn'>Approve</a>
                                <a href='handle_regularization.php?id={$rr['request_id']}&action=rejected&redirect=sa_attendance.php&csrf=".csrf_token()."' class='approve-btn' style='background:#dc2626;margin-left:6px;'>Reject</a>
                            </td>
                        </tr>";
                    }
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>

    <div class="form-card">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
            <h3 class="section-title" style="margin-bottom:0;">🟢 Who's In Office Right Now</h3>
            <span id="attLastUpdated" style="font-size:11px;color:#9ca3af;"></span>
        </div>
        <p style="font-size:12px;color:#888;margin:4px 0 14px;">Live view of every employee's current status — refreshes automatically every 20 seconds.</p>

        <div id="attSummaryRow" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;"></div>

        <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
            <input type="text" id="attSearchBox" placeholder="🔍 Search employee or department..." style="flex:1;min-width:200px;padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
            <select id="attFilterSelect" style="padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:13px;">
                <option value="all">All</option>
                <option value="in_office">In Office</option>
                <option value="wfh_active">WFH (Active)</option>
                <option value="checked_out">Checked Out</option>
                <option value="wfh_done">WFH (Done)</option>
                <option value="not_checked_in">Not Checked-in</option>
            </select>
        </div>

        <div style="max-height:420px;overflow-y:auto;">
        <table class="emp-table" style="width:100%;">
            <thead><tr><th>Employee</th><th>Department</th><th>Presence</th><th>Marked As</th><th>Check In</th><th>Check Out</th></tr></thead>
            <tbody id="attStatusBody">
                <tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:20px;">Loading...</td></tr>
            </tbody>
        </table>
        </div>
    </div>

    <div class="form-card">
        <h3 class="section-title">Attendance Records</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th><th>Status</th><th>Type</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $res=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id ORDER BY a.date DESC");
                while($row=mysqli_fetch_assoc($res)){
                    $type=($row['status']=='work_from_home')?"<span class='pill blue'>WFH</span>":"<span class='pill green'>Office</span>";
                    echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td><td>".ucfirst(str_replace('_',' ',$row['status']))."</td><td>{$type}</td><td><a href='regularize.php?id={$row['attendance_id']}' class='approve-btn'>Regularize</a></td></tr>";
                }
            ?>
            </tbody>
        </table>
        </div>

        <h3 class="section-title" style="margin-top:28px;">Work From Home Today</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Employee</th><th>Date</th><th>Check In</th><th>Check Out</th></tr></thead>
            <tbody>
            <?php
                $wfh=mysqli_query($conn,"SELECT a.*,e.first_name,e.last_name FROM attendance a JOIN employees e ON a.emp_id=e.emp_id WHERE a.status='work_from_home' AND a.date=CURDATE()");
                if(mysqli_num_rows($wfh)>0){ while($row=mysqli_fetch_assoc($wfh)) echo "<tr><td>{$row['first_name']} {$row['last_name']}</td><td>{$row['date']}</td><td>{$row['check_in']}</td><td>{$row['check_out']}</td></tr>"; }
                else echo "<tr><td colspan='4' style='text-align:center;color:#9ca3af;'>No WFH employees today</td></tr>";
            ?>
            </tbody>
        </table>
        </div>
    </div>

</div>

</div>
</div>

<script>
// ===== WHO'S IN OFFICE RIGHT NOW — live presence widget =====
const presenceMeta = {
    in_office:      { bg:'#dcfce7', color:'#16a34a', label:'🟢 In Office' },
    wfh_active:     { bg:'#dbeafe', color:'#1d4ed8', label:'🏠 WFH (Active)' },
    checked_out:    { bg:'#f3f4f6', color:'#6b7280', label:'⚪ Checked Out' },
    wfh_done:       { bg:'#e0e7ff', color:'#4338ca', label:'🏠 WFH (Done)' },
    not_checked_in: { bg:'#fee2e2', color:'#dc2626', label:'🔴 Not Checked-in' }
};
const statusLabels = { present:'Present', late:'Late', half_day:'Half Day', work_from_home:'Work From Home', not_checked_in:'—' };

let attEmployees = [];

function renderAttStatusBody(){
    const search = (document.getElementById('attSearchBox').value || '').toLowerCase().trim();
    const filter = document.getElementById('attFilterSelect').value;

    const filtered = attEmployees.filter(emp => {
        const matchesSearch = !search || emp.name.toLowerCase().includes(search) || (emp.department||'').toLowerCase().includes(search);
        const matchesFilter = filter === 'all' || emp.presence === filter;
        return matchesSearch && matchesFilter;
    });

    const body = document.getElementById('attStatusBody');
    if(!filtered.length){
        body.innerHTML = '<tr><td colspan="6" style="text-align:center;color:#9ca3af;padding:20px;">No employees match this search/filter.</td></tr>';
        return;
    }
    body.innerHTML = filtered.map(emp => {
        const p = presenceMeta[emp.presence] || presenceMeta.not_checked_in;
        return `<tr>
            <td>${emp.name}</td>
            <td>${emp.department || '-'}</td>
            <td><span style="background:${p.bg};color:${p.color};padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;white-space:nowrap;">${p.label}</span></td>
            <td>${statusLabels[emp.status] || '-'}</td>
            <td>${emp.check_in || '-'}</td>
            <td>${emp.check_out || '-'}</td>
        </tr>`;
    }).join('');
}

function loadAttendanceStatus(){
    fetch('get_attendance_status.php')
        .then(r => r.json())
        .then(data => {
            if(data.error) return;

            attEmployees = data.employees;

            const pc = data.presence_counts;
            const summaryHtml = Object.keys(presenceMeta).map(key => {
                const p = presenceMeta[key];
                const n = pc[key] || 0;
                return `<div style="background:${p.bg};color:${p.color};padding:8px 16px;border-radius:8px;font-size:13px;font-weight:600;">${p.label}: ${n}</div>`;
            }).join('');
            document.getElementById('attSummaryRow').innerHTML = summaryHtml;

            renderAttStatusBody();
            document.getElementById('attLastUpdated').textContent = 'Last updated: ' + data.as_of;
        })
        .catch(err => console.error('Attendance status fetch failed', err));
}

document.getElementById('attSearchBox').addEventListener('input', renderAttStatusBody);
document.getElementById('attFilterSelect').addEventListener('change', renderAttStatusBody);

loadAttendanceStatus();
setInterval(loadAttendanceStatus, 20000);
</script>

<?php include 'common_js.php'; ?>
</body>
</html>
