<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'],['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Rules & Regulations";
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
.rule-header{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-left:4px solid #1a1a2e;}
.rule-category{display:inline-block;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;margin-bottom:6px;}
.cat-general   {background:#eff6ff;color:#2563eb;}
.cat-leave     {background:#f0fdf4;color:#16a34a;}
.cat-salary    {background:#fef3c7;color:#d97706;}
.cat-conduct   {background:#fee2e2;color:#dc2626;}
.cat-privacy   {background:#f3e8ff;color:#7c3aed;}
.rule-title{font-size:14px;font-weight:700;color:#1a1a2e;margin:0;}
.rule-desc{padding:0 18px 14px;font-size:13px;color:#4b5563;line-height:1.7;}
.rule-actions{display:flex;gap:8px;}
.edit-btn{background:#eff6ff;color:#2563eb;border:none;padding:5px 14px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600;}
.del-btn{background:#fee2e2;color:#dc2626;border:none;padding:5px 14px;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600;}
.modal-overlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;}
.modal-overlay.active{display:flex;}
.modal{background:white;border-radius:14px;padding:28px;width:500px;max-width:90%;}
.modal h3{margin:0 0 20px;font-size:16px;color:#1a1a2e;}

.modal input[type="text"],
.modal textarea,
.modal select {
    color: #1a1a2e !important;
    background: white !important;
}
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">

    <!-- Add Rule Button -->
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <div></div>
        <button onclick="openModal()" class="submit-btn" style="padding:10px 22px;">+ Add Rule</button>
    </div>

    <!-- Rules by Category -->
    <?php
    $categories = [
        'General'   => ['icon'=>'','class'=>'cat-general'],
        'Leave'     => ['icon'=>'','class'=>'cat-leave'],
        'Salary'    => ['icon'=>'','class'=>'cat-salary'],
        'Conduct'   => ['icon'=>'','class'=>'cat-conduct'],
        'Privacy'   => ['icon'=>'','class'=>'cat-privacy'],
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
                    <div>
                        <span class='rule-category {$meta['class']}'>{$meta['icon']} {$cat}</span>
                        <p class='rule-title'>{$r['title']}</p>
                    </div>
                    <div class='rule-actions'>
                        <button class='edit-btn' onclick=\"openEdit('{$r['rule_id']}','{$cat}',`{$r['title']}`,`{$r['description']}`)\"> Edit</button>
                        <button class='del-btn' onclick=\"deleteRule({$r['rule_id']})\"> Delete</button>
                    </div>
                </div>
                <div class='rule-desc'>{$r['description']}</div>
            </div>";
        }
        echo "</div>";
    }
    ?>

</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="ruleModal">
    <div class="modal">
        <h3 id="modal_title">Add New Rule</h3>
        <form action="save_rule.php" method="POST">
            <input type="hidden" name="rule_id" id="rule_id" value="">
            <div class="field" style="margin-bottom:14px;"><label>Category</label>
                <select name="category" id="modal_cat" required style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;">
                    <option value="General"> General</option>
                    <option value="Leave"> Leave</option>
                    <option value="Salary"> Salary</option>
                    <option value="Conduct"> Conduct</option>
                    <option value="Privacy"> Privacy</option>
                </select>
            </div>
            <div class="field" style="margin-bottom:14px;"><label>Rule Title</label>
            <input type="text" name="title" id="modal_title_input" placeholder="e.g. Office Timing Policy" required style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;color:#1a1a2e;background:white;">            </div>
            <div class="field" style="margin-bottom:20px;"><label>Description</label>
                <textarea name="description" id="modal_desc" rows="4" placeholder="Describe the rule in detail..." required style="width:100%;padding:9px 12px;border:1px solid #d1d5db;border-radius:8px;resize:vertical;"></textarea>
            </div>
            <div style="display:flex;gap:10px;justify-content:flex-end;">
                <button type="button" onclick="closeModal()" style="padding:9px 20px;border:1px solid #d1d5db;border-radius:8px;background:white;cursor:pointer;font-weight:600;">Cancel</button>
                <button type="submit" class="submit-btn" style="padding:9px 22px;">Save Rule</button>
            </div>
        </form>
    </div>
</div>

</div>
</div>
<script>
function openModal(){
    document.getElementById('modal_title').innerText='Add New Rule';
    document.getElementById('rule_id').value='';
    document.getElementById('modal_cat').value='General';
    document.getElementById('modal_title_input').value='';
    document.getElementById('modal_desc').value='';
    document.getElementById('ruleModal').classList.add('active');
}
function openEdit(id,cat,title,desc){
    document.getElementById('modal_title').innerText='Edit Rule';
    document.getElementById('rule_id').value=id;
    document.getElementById('modal_cat').value=cat;
    document.getElementById('modal_title_input').value=title;
    document.getElementById('modal_desc').value=desc;
    document.getElementById('ruleModal').classList.add('active');
}
function closeModal(){
    document.getElementById('ruleModal').classList.remove('active');
}
function deleteRule(id){
    if(confirm('Delete this rule?')){
        window.location.href='delete_rule.php?id='+id+'&redirect=sa_rules.php';
    }
}
</script>
<?php include 'common_js.php'; ?>
</body>
</html>
