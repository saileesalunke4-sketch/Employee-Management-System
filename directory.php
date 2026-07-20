<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Employee Directory";

// topbar_emp.php needs $emp_id in scope for the logged-in employee
if($role === 'employee'){
    $user_id = $_SESSION['user']['id'];
    $emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'"));
    $emp_id  = $emp_row['emp_id'];
}

$directory = [];
$res = mysqli_query($conn, "SELECT e.emp_id, e.first_name, e.last_name, e.designation, e.contact, e.work_location,
                                    e.employee_code, d.dept_name, u.email, u.profile_photo
                             FROM employees e
                             LEFT JOIN departments d ON e.dept_id = d.dept_id
                             LEFT JOIN users u ON e.user_id = u.id
                             ORDER BY e.first_name, e.last_name");
while($row = mysqli_fetch_assoc($res)){ $directory[] = $row; }

$dept_list = [];
$dres = mysqli_query($conn, "SELECT DISTINCT dept_name FROM departments WHERE dept_name IS NOT NULL ORDER BY dept_name");
while($d = mysqli_fetch_assoc($dres)){ $dept_list[] = $d['dept_name']; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Employee Directory - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.dir-controls{ display:flex; gap:10px; flex-wrap:wrap; margin-bottom:18px; }
.dir-search{ flex:1; min-width:220px; padding:9px 14px; border:1px solid var(--border,#e5e7eb); border-radius:10px; font-size:13px; outline:none; }
.dir-search:focus{ border-color:var(--role-accent,#4F46E5); }
.dir-dept-filter{ padding:9px 14px; border:1px solid var(--border,#e5e7eb); border-radius:10px; font-size:13px; background:#fff; }

.dir-grid{ display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:16px; }
.dir-card{
    background:var(--surface,#fff); border:1px solid var(--border-soft,#eef0f3); border-radius:14px;
    padding:18px; text-align:center; transition:transform .15s ease, box-shadow .15s ease;
}
.dir-card:hover{ transform:translateY(-3px); box-shadow:0 8px 20px rgba(15,23,42,0.08); }
.dir-avatar{
    width:64px; height:64px; border-radius:50%; margin:0 auto 10px; object-fit:cover;
    display:flex; align-items:center; justify-content:center;
    background:linear-gradient(135deg, var(--role-accent,#4F46E5), var(--role-accent-2,#6D64F2));
    color:#fff; font-weight:700; font-size:20px;
}
.dir-name{ font-weight:700; font-size:14px; color:var(--text-1,#14161a); }
.dir-designation{ font-size:12px; color:var(--text-3,#9aa1ac); margin-top:2px; }
.dir-dept-pill{
    display:inline-block; margin-top:8px; padding:3px 10px; border-radius:20px;
    font-size:11px; font-weight:600; background:var(--surface-soft,#f3f4f7); color:var(--text-2,#666d7a);
}
.dir-meta{ margin-top:10px; font-size:11.5px; color:var(--text-3,#9aa1ac); line-height:1.6; text-align:left; }
.dir-meta div{ display:flex; gap:6px; align-items:center; }
.dir-empty{ text-align:center; color:var(--text-3,#9aa1ac); padding:40px 0; grid-column:1/-1; }
</style>
</head>
<body>
<div class="dashboard <?php echo $role==='employee' ? 'emp-theme' : ($role==='admin' ? 'admin-theme' : 'super-theme'); ?>">
<?php
    if($role === 'employee') include 'sidebar_emp.php';
    elseif($role === 'admin') include 'sidebar_admin.php';
    else include 'sidebar_sa.php';
?>
<div class="main-content">
<?php
    if($role === 'employee') include 'topbar_emp.php';
    elseif($role === 'admin') include 'topbar_admin.php';
    else include 'topbar_sa.php';
?>
<div class="app-content">

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Employee Directory</h3>
        <p style="font-size:12px;color:var(--text-3,#9aa1ac);margin-top:-6px;margin-bottom:14px;">
            <?php echo count($directory); ?> employee(s) — search by name, designation, or department.
        </p>

        <div class="dir-controls">
            <input type="text" id="dirSearch" class="dir-search" placeholder="Search by name, designation, employee code…" oninput="filterDirectory()">
            <select id="dirDeptFilter" class="dir-dept-filter" onchange="filterDirectory()">
                <option value="">All Departments</option>
                <?php foreach($dept_list as $dn): ?>
                    <option value="<?php echo htmlspecialchars($dn); ?>"><?php echo htmlspecialchars($dn); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="dir-grid" id="dirGrid">
            <?php if(empty($directory)): ?>
                <div class="dir-empty">No employees found.</div>
            <?php else: foreach($directory as $e):
                $full_name = trim($e['first_name'].' '.$e['last_name']);
                $initials  = strtoupper(substr($e['first_name'],0,1) . substr($e['last_name'],0,1));
                $dept      = $e['dept_name'] ?: 'Unassigned';
                $has_photo = !empty($e['profile_photo']) && file_exists('uploads/'.$e['profile_photo']);
            ?>
                <div class="dir-card" data-name="<?php echo htmlspecialchars(strtolower($full_name)); ?>"
                     data-designation="<?php echo htmlspecialchars(strtolower($e['designation'] ?? '')); ?>"
                     data-code="<?php echo htmlspecialchars(strtolower($e['employee_code'] ?? '')); ?>"
                     data-dept="<?php echo htmlspecialchars($dept); ?>">
                    <?php if($has_photo): ?>
                        <img class="dir-avatar" src="uploads/<?php echo htmlspecialchars($e['profile_photo']); ?>" alt="">
                    <?php else: ?>
                        <div class="dir-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <?php endif; ?>
                    <div class="dir-name"><?php echo htmlspecialchars($full_name); ?></div>
                    <div class="dir-designation"><?php echo htmlspecialchars($e['designation'] ?: '-'); ?></div>
                    <span class="dir-dept-pill"><?php echo htmlspecialchars($dept); ?></span>
                    <div class="dir-meta">
                        <?php if(!empty($e['employee_code'])): ?><div><?php echo ems_icon('tag',12); ?> <?php echo htmlspecialchars($e['employee_code']); ?></div><?php endif; ?>
                        <?php if(!empty($e['email'])): ?><div><?php echo ems_icon('mail',12); ?> <?php echo htmlspecialchars($e['email']); ?></div><?php endif; ?>
                        <?php if(!empty($e['contact'])): ?><div><?php echo ems_icon('phone',12); ?> <?php echo htmlspecialchars($e['contact']); ?></div><?php endif; ?>
                        <?php if(!empty($e['work_location'])): ?><div><?php echo ems_icon('map-pin',12); ?> <?php echo htmlspecialchars($e['work_location']); ?></div><?php endif; ?>
                    </div>
                </div>
            <?php endforeach; endif; ?>
        </div>
        <div class="dir-empty" id="dirNoMatch" style="display:none;">No employees match your search.</div>
    </div>
</div>

</div>
</div>
</div>

<script>
function filterDirectory(){
    var q = document.getElementById('dirSearch').value.trim().toLowerCase();
    var dept = document.getElementById('dirDeptFilter').value;
    var cards = document.querySelectorAll('#dirGrid .dir-card');
    var visibleCount = 0;

    cards.forEach(function(card){
        var matchesSearch = !q ||
            card.dataset.name.indexOf(q) !== -1 ||
            card.dataset.designation.indexOf(q) !== -1 ||
            card.dataset.code.indexOf(q) !== -1;
        var matchesDept = !dept || card.dataset.dept === dept;

        if(matchesSearch && matchesDept){
            card.style.display = '';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('dirNoMatch').style.display = (visibleCount === 0 && cards.length > 0) ? 'block' : 'none';
}
</script>

<?php include 'common_js.php'; ?>
</body>
</html>
