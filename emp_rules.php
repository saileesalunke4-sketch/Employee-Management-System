<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='employee'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Rules & Regulations";
$user_id    = $_SESSION['user']['id'];
$emp_result = mysqli_query($conn, "SELECT * FROM employees WHERE user_id='$user_id'");
$emp        = mysqli_fetch_assoc($emp_result);
$emp_id     = $emp['emp_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Rules & Regulations - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.rule-card{background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:14px;overflow:hidden;}
.rule-header{padding:14px 18px;border-left:4px solid #1a1a2e;}
.rule-category{display:inline-block;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;margin-bottom:6px;}
.cat-general {background:#eff6ff;color:#2563eb;}
.cat-leave   {background:#f0fdf4;color:#16a34a;}
.cat-salary  {background:#fef3c7;color:#d97706;}
.cat-conduct {background:#fee2e2;color:#dc2626;}
.cat-privacy {background:#f3e8ff;color:#7c3aed;}
.rule-title{font-size:14px;font-weight:700;color:#1a1a2e;margin:0;}
.rule-desc{padding:0 18px 14px;font-size:13px;color:#4b5563;line-height:1.7;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#1d4ed8;">
         <strong>Company Rules & Regulations</strong> — Please read and follow all policies carefully.
    </div>

    <?php
    $categories = [
        'General' => ['icon'=>'','class'=>'cat-general'],
        'Leave'   => ['icon'=>'','class'=>'cat-leave'],
        'Salary'  => ['icon'=>'','class'=>'cat-salary'],
        'Conduct' => ['icon'=>'','class'=>'cat-conduct'],
        'Privacy' => ['icon'=>'','class'=>'cat-privacy'],
    ];
    foreach($categories as $cat => $meta){
        $rules = mysqli_query($conn,"SELECT * FROM rules WHERE category='$cat' ORDER BY rule_id ASC");
        $count = mysqli_num_rows($rules);
        if($count == 0) continue;
        echo "<div style='margin-bottom:24px;'>
            <h4 style='font-size:14px;color:#6b7280;margin-bottom:12px;display:flex;align-items:center;gap:8px;'>
                {$meta['icon']} {$cat} Rules
                <span style='background:#f3f4f6;color:#6b7280;border-radius:20px;padding:1px 10px;font-size:12px;'>{$count}</span>
            </h4>";
        while($r = mysqli_fetch_assoc($rules)){
            echo "<div class='rule-card'>
                <div class='rule-header'>
                    <span class='rule-category {$meta['class']}'>{$meta['icon']} {$cat}</span>
                    <p class='rule-title'>{$r['title']}</p>
                </div>
                <div class='rule-desc'>{$r['description']}</div>
            </div>";
        }
        echo "</div>";
    }
    ?>
</div>

</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
