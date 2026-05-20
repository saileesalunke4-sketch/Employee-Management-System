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
$page_title = "Holiday Calendar";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Holiday Calendar - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
<style>
.status-pill{display:inline-block;padding:3px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.status-pill.approved{background:#dcfce7;color:#16a34a;}
.status-pill.rejected{background:#fee2e2;color:#dc2626;}
.status-pill.pending{background:#fef3c7;color:#d97706;}
.status-pill.completed{background:#dcfce7;color:#16a34a;}
.status-pill.in_progress{background:#fef3c7;color:#d97706;}
.skill-tag{display:inline-block;background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:20px;padding:4px 14px;font-size:12px;font-weight:600;margin:4px;}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_emp.php'; ?>
<div class="main-content">
<?php include 'topbar_emp.php'; ?>

<div class="section active">

    <div class="form-card">
        <h3 class="section-title">&#127974; Holiday Calendar <?php echo date('Y');?></h3>
        <div id="calContainer" style="margin-bottom:28px;"></div>
        <h3 class="section-title">All Holidays <?php echo date('Y');?></h3>
        <table class="emp-table">
            <thead><tr><th>#</th><th>Holiday</th><th>Date</th><th>Day</th><th>Type</th></tr></thead>
            <tbody>
            <?php
                $hres=mysqli_query($conn,"SELECT * FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) ORDER BY holiday_date ASC");
                $cnt=1;
                while($h=mysqli_fetch_assoc($hres)){
                    $htype=$h['holiday_type']??'National';
                    echo "<tr><td>{$cnt}</td><td><b>{$h['holiday_name']}</b></td>
                    <td>".date('d M Y',strtotime($h['holiday_date']))."</td>
                    <td>".date('l',strtotime($h['holiday_date']))."</td>
                    <td><span class='hl-badge {$htype}'>{$htype}</span></td></tr>";
                    $cnt++;
                }
            ?>
            </tbody>
        </table>
    </div>

</div>

</div>
</div>
<?php
$h_map=[]; $hr=mysqli_query($conn,"SELECT holiday_date,holiday_name,holiday_type FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())");
while($hrow=mysqli_fetch_assoc($hr)) $h_map[$hrow['holiday_date']]=['name'=>$hrow['holiday_name'],'type'=>$hrow['holiday_type']??'National'];
?>
<script>
const fullMonths=['January','February','March','April','May','June','July','August','September','October','November','December'];
const dayNames=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const holidays=<?php echo json_encode($h_map);?>;
const todayStr="<?php echo date('Y-m-d');?>";
let calYear=<?php echo date('Y');?>, calMonth=<?php echo date('n')-1;?>;
function buildCalendar(year,month){
    const c=document.getElementById('calContainer'); if(!c) return;
    const firstDay=new Date(year,month,1).getDay(), days=new Date(year,month+1,0).getDate();
    let html=`<div style="background:linear-gradient(135deg,#1a3a6e,#3b82f6);color:#fff;padding:16px 20px;border-radius:12px 12px 0 0;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:16px;">${fullMonths[month]} ${year}</h3>
        <div><button onclick="changeMonth(-1)" style="background:rgba(255,255,255,.2);color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;margin-right:8px;">&#8592; Prev</button>
        <button onclick="changeMonth(1)" style="background:rgba(255,255,255,.2);color:#fff;border:none;padding:6px 14px;border-radius:8px;cursor:pointer;">Next &#8594;</button></div></div>
        <div class="cal-grid" style="padding:12px;background:#fff;border-radius:0 0 12px 12px;border:1px solid #f0f0f0;">`;
    dayNames.forEach(d=>{html+=`<div class="cal-day-name">${d}</div>`;});
    for(let i=0;i<firstDay;i++) html+=`<div class="cal-cell empty"></div>`;
    for(let d=1;d<=days;d++){
        const ds=`${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isSun=new Date(year,month,d).getDay()===0,isToday=ds===todayStr,isHol=holidays[ds]!==undefined;
        let cls='cal-cell';
        if(isToday)cls+=' today';else if(isHol)cls+=' holiday';else if(isSun)cls+=' sunday';
        let hname=isHol?`<div class="cal-hname">${holidays[ds].name}</div>`:(isSun?`<div class="cal-hname" style="color:#9ca3af">Sunday</div>`:'');
        html+=`<div class="${cls}"><span class="cal-num">${d}</span>${hname}</div>`;
    }
    html+=`</div>`; c.innerHTML=html;
}
function changeMonth(dir){calMonth+=dir;if(calMonth>11){calMonth=0;calYear++;}if(calMonth<0){calMonth=11;calYear--;}buildCalendar(calYear,calMonth);}
buildCalendar(calYear,calMonth);
</script>
<?php include 'common_js.php'; ?>
</body>
</html>
