<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Organization Chart";

// topbar_emp.php needs $emp_id in scope for the logged-in employee
if($role === 'employee'){
    $user_id = $_SESSION['user']['id'];
    $emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id FROM employees WHERE user_id='$user_id'"));
    $emp_id  = $emp_row['emp_id'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Organization Chart - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.org-wrap{overflow-x:auto;padding:30px 10px;}
.org-chart{text-align:center;min-width:600px;}

.org-card{
    display:inline-block;background:#fff;border:2px solid #3b82f6;border-radius:10px;
    padding:14px 22px;font-size:14px;font-weight:600;color:#1a1a2e;
    box-shadow:0 2px 8px rgba(0,0,0,.06);cursor:pointer;
    transition:transform .2s ease, box-shadow .2s ease;
}
.org-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(59,130,246,.18);}
.org-card small{display:block;font-weight:400;color:#6b7280;margin-top:4px;font-size:11px;}

.org-card.company{background:#1a3a6e;color:#fff;border-color:#1a3a6e;font-size:16px;cursor:default;}
.org-card.company:hover{transform:none;box-shadow:0 2px 8px rgba(0,0,0,.06);}
.org-card.company small{color:rgba(255,255,255,.7);}

.org-card.emp{border-color:#16a34a;font-weight:500;font-size:12px;padding:10px 16px;cursor:default;}
.org-card.emp:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(22,163,74,.15);}
.org-card.emp small{color:#888;}

.org-departments{
    display:flex;justify-content:center;gap:36px;margin-top:44px;
    position:relative;flex-wrap:wrap;
}
.org-departments::before{
    content:'';position:absolute;top:-22px;left:12%;right:12%;height:2px;background:#cbd5e1;
}
.org-dept-branch{position:relative;padding-top:22px;}
.org-dept-branch::before{
    content:'';position:absolute;top:0;left:50%;width:2px;height:22px;background:#cbd5e1;
}

.org-employees{
    display:flex;flex-direction:column;gap:10px;margin-top:18px;
    position:relative;padding-top:18px;align-items:center;
}
.org-employees::before{
    content:'';position:absolute;top:0;left:50%;width:2px;height:18px;background:#cbd5e1;
}
.org-employees.expanded{ animation: orgFadeIn .25s ease; }

@keyframes orgFadeIn{
    from{opacity:0;transform:translateY(-6px);}
    to{opacity:1;transform:translateY(0);}
}

.org-toggle-hint{font-size:10px;color:#93c5fd;margin-top:2px;}
</style>
</head>
<body>
<div class="dashboard">
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

<div class="section active">
    <div class="form-card">
        <h3 class="section-title">Organization Chart</h3>
        <p style="font-size:12px;color:#888;margin-top:-6px;margin-bottom:10px;">Click on a department to view / hide its team members.</p>

        <div class="org-wrap">
        <div class="org-chart">
            <div class="org-root">
                <?php
                    $total_emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees"))['c'];
                ?>
                <div class="org-card company">🏢 Aller Technologies<small><?php echo $total_emp; ?> employee(s) total</small></div>
            </div>

            <div class="org-departments">
            <?php
                $dept_icon_map = ['Development'=>'💻','UI Design'=>'🎨','QA'=>'🧪','Marketing'=>'📢','Management'=>'🏛️','HR'=>'🧑‍💼','IT'=>'💻'];
                $dept_res = mysqli_query($conn, "SELECT * FROM departments ORDER BY dept_name");

                if(mysqli_num_rows($dept_res) === 0){
                    echo "<p style='color:#9ca3af;'>No departments added yet.</p>";
                }

                while($d = mysqli_fetch_assoc($dept_res)){
                    $dept_id   = (int) $d['dept_id'];
                    $dept_name = htmlspecialchars($d['dept_name']);
                    $dept_head = $d['dept_head'] ? htmlspecialchars($d['dept_head']) : 'Not Assigned';
                    $icon      = $dept_icon_map[$d['dept_name']] ?? '🏢';

                    $count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE dept_id=$dept_id"))['c'];

                    echo "<div class='org-dept-branch'>";
                    echo "<div class='org-card dept' onclick=\"toggleDept({$dept_id})\">{$icon} {$dept_name}
                            <small>Head: {$dept_head} &middot; {$count} member(s)</small>
                            <span class='org-toggle-hint' style='color:#3b82f6;'>click to expand</span>
                          </div>";

                    echo "<div class='org-employees' id='dept-{$dept_id}' style='display:none;'>";
                    $emp_res = mysqli_query($conn, "SELECT first_name, last_name, designation FROM employees WHERE dept_id=$dept_id ORDER BY first_name");
                    if(mysqli_num_rows($emp_res) === 0){
                        echo "<div style='font-size:12px;color:#9ca3af;'>No employees in this department yet</div>";
                    } else {
                        while($e = mysqli_fetch_assoc($emp_res)){
                            $ename  = htmlspecialchars($e['first_name'].' '.$e['last_name']);
                            $edesig = htmlspecialchars($e['designation'] ?: '-');
                            echo "<div class='org-card emp'>👤 {$ename}<small>{$edesig}</small></div>";
                        }
                    }
                    echo "</div>"; // org-employees
                    echo "</div>"; // org-dept-branch
                }
            ?>
            </div>
        </div>
        </div>
    </div>
</div>

</div>
</div>

<script>
function toggleDept(deptId){
    const el = document.getElementById('dept-' + deptId);
    if(!el) return;
    const isHidden = (el.style.display === 'none' || el.style.display === '');
    if(isHidden){
        el.style.display = 'flex';
        el.classList.add('expanded');
    } else {
        el.style.display = 'none';
        el.classList.remove('expanded');
    }
}
</script>

<?php include 'common_js.php'; ?>
</body>
</html>
