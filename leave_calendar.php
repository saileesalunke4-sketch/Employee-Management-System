<?php
session_start();
if(!isset($_SESSION['user'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Leave Calendar";

// Employee scope: only their own department's leaves (privacy — don't show
// the whole company's leave data to every employee).
// Admin/Super Admin scope: company-wide, with an optional department filter.
// SECURITY FIX: if an employee has no department assigned (dept_id is NULL),
// $scope_dept_id used to be falsy, which made the filter below silently
// disappear — showing that employee the ENTIRE company's approved leaves
// instead of restricting them. Now that case falls back to "own leaves only".
$scope_dept_id = null;
$no_department_fallback = false;
if($role === 'employee'){
    $user_id = $_SESSION['user']['id'];
    $emp_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT emp_id, dept_id FROM employees WHERE user_id='$user_id'"));
    $emp_id  = $emp_row['emp_id'];
    $scope_dept_id = $emp_row['dept_id'];
    if(!$scope_dept_id){
        $no_department_fallback = true;
    }
} elseif(isset($_GET['dept_id']) && $_GET['dept_id'] !== ''){
    $scope_dept_id = (int) $_GET['dept_id'];
}

// Month navigation
$month_param = isset($_GET['month']) ? $_GET['month'] : date('Y-m');
if(!preg_match('/^\d{4}-\d{2}$/', $month_param)) $month_param = date('Y-m');
$month_ts    = strtotime($month_param.'-01');
$month_start = date('Y-m-01', $month_ts);
$month_end   = date('Y-m-t', $month_ts);
$prev_month  = date('Y-m', strtotime('-1 month', $month_ts));
$next_month  = date('Y-m', strtotime('+1 month', $month_ts));

$first_weekday = (int) date('N', $month_ts); // 1=Mon ... 7=Sun
$days_in_month = (int) date('t', $month_ts);

// Leave-type color map (reused style from Leave Balance Tracker)
$leave_colors = [
    'Sick Leave' => '#dc2626', 'Casual Leave' => '#2563eb', 'Privilege Leave' => '#7c3aed',
    'Earned Leave' => '#d97706', 'Sabbatical' => '#0891b2', 'Paternity Leave' => '#0d9488',
    'Maternity leave' => '#db2777', 'Materninty leave' => '#db2777', 'Special Casual Leave' => '#65a30d',
    'Sub Artical Leave' => '#9333ea', 'Unpaid Leave' => '#6b7280'
];

// Fetch all approved leaves overlapping this month (within scope)
// SECURITY: when an employee has no department assigned, restrict to their
// own leaves only — never fall through to an unfiltered company-wide query.
if($no_department_fallback){
    $dept_filter = " AND l.emp_id=".(int)$emp_id;
} else {
    $dept_filter = $scope_dept_id ? " AND e.dept_id=".(int)$scope_dept_id : "";
}
$lres = mysqli_query($conn, "SELECT l.from_date, l.to_date, l.leave_type, e.first_name, e.last_name
                              FROM leaves l JOIN employees e ON l.emp_id=e.emp_id
                              WHERE l.status='approved'
                              AND l.from_date <= '$month_end' AND l.to_date >= '$month_start'
                              $dept_filter");

$leaves_by_date = [];
while($l = mysqli_fetch_assoc($lres)){
    $cursor = max(strtotime($l['from_date']), strtotime($month_start));
    $end    = min(strtotime($l['to_date']), strtotime($month_end));
    while($cursor <= $end){
        $d = date('Y-m-d', $cursor);
        $leaves_by_date[$d][] = [
            'name' => trim($l['first_name'].' '.$l['last_name']),
            'type' => $l['leave_type']
        ];
        $cursor = strtotime('+1 day', $cursor);
    }
}

if($role === 'employee'){
    $emp_check = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_id FROM employees WHERE emp_id=$emp_id"));
    $dept_name_display = '-';
    if($emp_check['dept_id']){
        $dn = mysqli_fetch_assoc(mysqli_query($conn, "SELECT dept_name FROM departments WHERE dept_id=".(int)$emp_check['dept_id']));
        $dept_name_display = $dn ? $dn['dept_name'] : '-';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Leave Calendar - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-top:14px;}
.cal-head{font-size:11px;font-weight:700;color:#6b7280;text-align:center;padding:6px 0;text-transform:uppercase;}
.cal-cell{background:#fafafa;border:1px solid #eee;border-radius:8px;min-height:90px;padding:6px;font-size:11px;}
.cal-cell.empty{background:transparent;border:none;}
.cal-cell.today{border-color:#3b82f6;background:#eff6ff;}
.cal-daynum{font-weight:700;color:#374151;margin-bottom:4px;}
.cal-leave-chip{display:block;border-radius:6px;padding:2px 6px;margin-bottom:3px;font-size:10px;color:#fff;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.cal-legend{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px;font-size:11px;}
.cal-legend span{display:inline-flex;align-items:center;gap:4px;color:#6b7280;}
.cal-legend i{width:10px;height:10px;border-radius:3px;display:inline-block;}
.cal-nav{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;}
.cal-nav a{background:#eff6ff;color:#1d4ed8;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;}
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
        <h3 class="section-title">Leave Calendar</h3>
        <p style="font-size:12px;color:#888;margin-top:-6px;margin-bottom:6px;">
            <?php if($role === 'employee'): ?>
                <?php if($no_department_fallback): ?>
                    You don't have a department assigned yet — showing only your own approved leaves.
                <?php else: ?>
                    Showing approved leaves for your department: <b><?php echo htmlspecialchars($dept_name_display); ?></b>
                <?php endif; ?>
            <?php else: ?>
                Showing approved leaves company-wide<?php if($scope_dept_id) echo " (filtered by department)"; ?>
            <?php endif; ?>
        </p>

        <?php if(in_array($role, ['admin','super_admin'])): ?>
        <form method="GET" style="display:flex;gap:10px;align-items:flex-end;margin-bottom:14px;">
            <input type="hidden" name="month" value="<?php echo $month_param; ?>">
            <div class="field" style="margin:0;">
                <label>Filter Department</label>
                <select name="dept_id" onchange="this.form.submit()" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;">
                    <option value="">All Departments</option>
                    <?php
                        $dres = mysqli_query($conn, "SELECT * FROM departments ORDER BY dept_name");
                        while($dd = mysqli_fetch_assoc($dres)){
                            $sel = ($scope_dept_id == $dd['dept_id']) ? 'selected' : '';
                            echo "<option value='{$dd['dept_id']}' $sel>".htmlspecialchars($dd['dept_name'])."</option>";
                        }
                    ?>
                </select>
            </div>
        </form>
        <?php endif; ?>

        <div class="cal-nav">
            <a href="?month=<?php echo $prev_month; ?><?php echo $scope_dept_id && $role!=='employee' ? '&dept_id='.$scope_dept_id : ''; ?>">&larr; Prev</a>
            <h3 style="margin:0;font-size:16px;"><?php echo date('F Y', $month_ts); ?></h3>
            <a href="?month=<?php echo $next_month; ?><?php echo $scope_dept_id && $role!=='employee' ? '&dept_id='.$scope_dept_id : ''; ?>">Next &rarr;</a>
        </div>

        <div class="cal-grid">
            <?php foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $wd): ?>
                <div class="cal-head"><?php echo $wd; ?></div>
            <?php endforeach; ?>

            <?php
                // Empty cells before the 1st of the month
                for($i=1; $i<$first_weekday; $i++){
                    echo "<div class='cal-cell empty'></div>";
                }

                for($day=1; $day<=$days_in_month; $day++){
                    $date_str = date('Y-m-', $month_ts) . str_pad($day, 2, '0', STR_PAD_LEFT);
                    $is_today = ($date_str === date('Y-m-d')) ? 'today' : '';
                    echo "<div class='cal-cell $is_today'>";
                    echo "<div class='cal-daynum'>{$day}</div>";
                    if(isset($leaves_by_date[$date_str])){
                        foreach($leaves_by_date[$date_str] as $entry){
                            $color = $leave_colors[$entry['type']] ?? '#3b82f6';
                            $title = htmlspecialchars($entry['name'].' — '.$entry['type']);
                            echo "<span class='cal-leave-chip' style='background:{$color};' title='{$title}'>".htmlspecialchars($entry['name'])."</span>";
                        }
                    }
                    echo "</div>";
                }
            ?>
        </div>

        <div class="cal-legend">
            <?php foreach($leave_colors as $type => $color): ?>
                <span><i style="background:<?php echo $color; ?>;"></i><?php echo htmlspecialchars($type); ?></span>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
