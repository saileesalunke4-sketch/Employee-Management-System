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
$page_title = "Daily Work Log";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Daily Work Log - EMS</title>
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
        <h3 class="section-title">&#128221; Submit Daily Work Log</h3>
        <form action="save_log.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Date</label><input type="date" name="log_date" value="<?php echo date('Y-m-d');?>" max="<?php echo date('Y-m-d');?>" required></div>
                <div class="field"><label>Hours Spent Working</label><input type="number" name="hours_spent" min="1" max="12" step="0.5" placeholder="e.g. 8" required></div>
            </div>
            <div class="field" style="margin-top:10px;">
                <label>What did you work on today?</label>
                <textarea name="work_done" rows="5" id="workDone" placeholder="e.g.&#10;- Fixed login page bug&#10;- Completed salary module" style="width:100%;padding:10px;border-radius:8px;border:1px solid #e0e0e0;font-size:13px;resize:vertical;" required></textarea>
            </div>
            <div style="text-align:right;font-size:12px;color:#9ca3af;margin-top:4px;">Words: <span id="wordCount">0</span> <span id="scoreHint" style="margin-left:10px;font-weight:600;"></span></div>
            <button type="submit" class="submit-btn" style="margin-top:16px;">Submit Daily Log</button>
        </form>
    </div>
    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">&#128202; My Productivity Scores</h3>
        <?php
        $avg=round(mysqli_fetch_assoc(mysqli_query($conn,"SELECT AVG(productivity_score) as avg FROM daily_logs WHERE emp_id='$emp_id'"))['avg']??0);
        $score_color='#dc2626'; $score_label='Needs Improvement';
        if($avg>=80){$score_color='#16a34a';$score_label='Excellent';}
        elseif($avg>=60){$score_color='#d97706';$score_label='Good';}
        elseif($avg>=40){$score_color='#2563eb';$score_label='Average';}
        ?>
        <div style="display:flex;align-items:center;gap:20px;background:#f9fafb;border-radius:10px;padding:20px;margin-bottom:20px;">
            <div style="width:80px;height:80px;border-radius:50%;background:<?php echo $score_color;?>;display:flex;align-items:center;justify-content:center;">
                <span style="font-size:24px;font-weight:700;color:white;"><?php echo $avg;?></span>
            </div>
            <div>
                <p style="font-size:18px;font-weight:700;color:<?php echo $score_color;?>;margin:0;"><?php echo $score_label;?></p>
                <p style="font-size:13px;color:#6b7280;margin:4px 0;">Based on attendance, tasks & log quality</p>
            </div>
        </div>
        <table class="emp-table">
            <thead><tr><th>Date</th><th>Work Done</th><th>Hours</th><th>Score</th></tr></thead>
            <tbody>
            <?php
                $logs=mysqli_query($conn,"SELECT * FROM daily_logs WHERE emp_id='$emp_id' ORDER BY log_date DESC");
                while($log=mysqli_fetch_assoc($logs)){
                    $s=$log['productivity_score'];
                    $sc=$s>=80?'#16a34a':($s>=60?'#d97706':($s>=40?'#2563eb':'#dc2626'));
                    $preview=strlen($log['work_done'])>60?substr($log['work_done'],0,60).'...':$log['work_done'];
                    echo "<tr><td>{$log['log_date']}</td><td style='font-size:12px;'>{$preview}</td><td>{$log['hours_spent']} hrs</td><td><b style='color:{$sc};'>{$s}/100</b></td></tr>";
                }
            ?>
            </tbody>
        </table>
    </div>

</div>

</div>
</div>
<script>
const ta=document.getElementById('workDone');
if(ta){ ta.addEventListener('input',function(){
    const w=this.value.trim()===''?0:this.value.trim().split(/\s+/).length;
    document.getElementById('wordCount').textContent=w;
    const h=document.getElementById('scoreHint');
    if(w>=50){h.textContent='✅ Great detail! +30 pts';h.style.color='#16a34a';}
    else if(w>=30){h.textContent='👍 Good! +20 pts';h.style.color='#d97706';}
    else if(w>=10){h.textContent='📝 Add more detail';h.style.color='#2563eb';}
    else{h.textContent='⚠ Too short';h.style.color='#dc2626';}
});}
</script>
<?php include 'common_js.php'; ?>
</body>
</html>
