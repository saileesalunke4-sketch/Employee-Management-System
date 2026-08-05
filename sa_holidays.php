<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Holiday Calendar";
$h_res=mysqli_query($conn,"SELECT holiday_date,holiday_name,holiday_type FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())");
$holiday_map=[];
while($hrow=mysqli_fetch_assoc($h_res)) $holiday_map[$hrow['holiday_date']]=(['name'=>$hrow['holiday_name'],'type'=>($hrow['holiday_type']??'National')]);
$holidays_json=json_encode($holiday_map);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Holiday Calendar - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">
    <?php
    $totalH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE())"))['c'];
    $natH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='National'"))['c'];
    $festH=mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) AND holiday_type='Festival'"))['c'];
    ?>
    <div class="hol-cards">
        <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #3b82f6;">
            <p style="font-size:11px;color:#6b7280;margin:0;">Total Holidays <?php echo date('Y');?></p>
            <p style="font-size:32px;font-weight:800;color:#1a3a6e;margin:6px 0;"><?php echo $totalH;?></p>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #1d4ed8;">
            <p style="font-size:11px;color:#6b7280;margin:0;">National Holidays</p>
            <p style="font-size:32px;font-weight:800;color:#1d4ed8;margin:6px 0;"><?php echo $natH;?></p>
        </div>
        <div style="background:#fff;border-radius:12px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.06);text-align:center;border-top:4px solid #d97706;">
            <p style="font-size:11px;color:#6b7280;margin:0;">Festivals</p>
            <p style="font-size:32px;font-weight:800;color:#d97706;margin:6px 0;"><?php echo $festH;?></p>
        </div>
    </div>

    <div class="form-card" style="padding:0;overflow:hidden;margin-bottom:20px;">
        <div id="calContainer"></div>
    </div>

    <div class="form-card">
        <h3 class="section-title">&#43; Add New Holiday</h3>
        <form action="save_holiday.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Holiday Name</label><input type="text" name="holiday_name" placeholder="e.g. Eid al-Fitr" required></div>
                <div class="field"><label>Date</label><input type="date" name="holiday_date" required></div>
                <div class="field"><label>Type</label>
                    <select name="holiday_type"><option value="National">National</option><option value="Festival">Festival</option><option value="State">State</option><option value="Government">Government</option></select>
                </div>
            </div>
            <button type="submit" class="submit-btn">&#43; Add Holiday</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:0;">
        <h3 class="section-title">&#127974; All Holidays <?php echo date('Y');?></h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>#</th><th>Holiday Name</th><th>Date</th><th>Day</th><th>Type</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $hres=mysqli_query($conn,"SELECT * FROM holidays WHERE YEAR(holiday_date)=YEAR(CURDATE()) ORDER BY holiday_date ASC");
                $cnt=1;
                while($h=mysqli_fetch_assoc($hres)){
                    $ht=$h['holiday_type']??'National';
                    $hi=($h['holiday_date']==date('Y-m-d'))?"background:#eff6ff;":"";
                    echo "<tr style='{$hi}'><td>{$cnt}</td><td><b>{$h['holiday_name']}</b></td>
                    <td>".date('d M Y',strtotime($h['holiday_date']))."</td>
                    <td>".date('l',strtotime($h['holiday_date']))."</td>
                    <td><span class='hl-badge {$ht}'>{$ht}</span></td>
                    <td><a href='delete_holiday.php?id={$h['id']}&redirect=sa_holidays.php' class='reject-btn' onclick='return confirm(\"Delete this holiday?\")'>&#128465; Delete</a></td></tr>";
                    $cnt++;
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
const fullMonths=['January','February','March','April','May','June','July','August','September','October','November','December'];
const dayNames=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
const holidays=<?php echo $holidays_json; ?>;
const todayStr="<?php echo date('Y-m-d'); ?>";
let calYear=<?php echo date('Y'); ?>, calMonth=<?php echo date('n')-1; ?>;

function buildCalendar(year,month){
    const c=document.getElementById('calContainer'); if(!c) return;
    const firstDay=new Date(year,month,1).getDay(), days=new Date(year,month+1,0).getDate();
    let html=`<div class="cal-top"><h3>&#128197; ${fullMonths[month]} ${year}</h3>
        <div><button class="cal-nav-btn" onclick="changeMonth(-1)">&#8592; Prev</button>
        <button class="cal-nav-btn" style="margin-left:8px" onclick="changeMonth(1)">Next &#8594;</button></div></div>
        <div class="cal-grid">`;
    dayNames.forEach(d=>{html+=`<div class="cal-day-name">${d}</div>`;});
    for(let i=0;i<firstDay;i++) html+=`<div class="cal-cell empty"></div>`;
    for(let d=1;d<=days;d++){
        const ds=`${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
        const isSun=new Date(year,month,d).getDay()===0,isToday=ds===todayStr,isHol=holidays[ds]!==undefined;
        let cls='cal-cell';
        if(isToday) cls+=' today'; else if(isHol) cls+=' holiday'; else if(isSun) cls+=' sunday';
        let hname=isHol?`<div class="cal-hname">${holidays[ds].name}</div>`:(isSun?`<div class="cal-hname" style="color:#9ca3af">Sunday</div>`:'');
        html+=`<div class="${cls}"><span class="cal-num">${d}</span>${hname}</div>`;
    }
    html+=`</div>`; c.innerHTML=html;
}
function changeMonth(dir){ calMonth+=dir; if(calMonth>11){calMonth=0;calYear++;} if(calMonth<0){calMonth=11;calYear--;} buildCalendar(calYear,calMonth); }
buildCalendar(calYear,calMonth);
</script>
<?php include 'common_js.php'; ?>
</body>
</html>
