<?php
session_start();
if(!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['admin','super_admin'])){
    header("Location: index.php"); exit();
}
require 'db.php';
$role = $_SESSION['user']['role'];
$page_title = "Asset Management";
$csrf_tok = csrf_token();

$asset_types = ['Laptop','Desktop','Monitor','Phone','Keyboard','Mouse','Headset','Other'];

// BUGFIX (EMS-ADM-013): session flash instead of ?msg= URL param — the old
// version kept showing "Asset marked as returned" / "Asset deleted" etc. on
// every refresh or revisit of the URL, since the message was driven purely
// by the query string rather than a one-time flag.
$flash = $_SESSION['asset_flash'] ?? null;
unset($_SESSION['asset_flash']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Asset Management - EMS</title>
<link rel="stylesheet" href="style.css">
<?php include 'common_styles.php'; ?>
</head>
<body>
<div class="dashboard <?php echo $role==='admin' ? 'admin-theme' : 'super-theme'; ?>">
<?php if($role === 'admin'){ include('sidebar_admin.php'); } else { include('sidebar_sa.php'); } ?>
<div class="main-content">
<?php if($role === 'admin'){ include('topbar_admin.php'); } else { include('topbar_sa.php'); } ?>
<div class="app-content">

<div class="section active">

    <?php if($flash): ?>
        <div class="form-card" style="background:<?php echo $flash['ok'] ? '#f0fdf4' : '#fef2f2'; ?>;border:1px solid <?php echo $flash['ok'] ? '#86efac' : '#fca5a5'; ?>;margin-bottom:16px;">
            <?php echo htmlspecialchars($flash['msg']); ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <h3 class="section-title">Add Asset</h3>
        <form action="save_asset.php" method="POST">
            <div class="form-grid">
                <div class="field"><label>Asset Name</label><input type="text" name="asset_name" placeholder="e.g. Dell Latitude 5420" required></div>
                <div class="field">
                    <label>Type</label>
                    <select name="asset_type" required>
                        <option value="">-- Select --</option>
                        <?php foreach($asset_types as $t): ?><option value="<?php echo $t; ?>"><?php echo $t; ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="field"><label>Serial Number</label><input type="text" name="serial_number" placeholder="Optional"></div>
                <div class="field"><label>Purchase Date</label><input type="date" name="purchase_date"></div>
            </div>
            <button type="submit" class="submit-btn">Add Asset</button>
        </form>
    </div>

    <div class="form-card" style="margin-top:20px;">
        <h3 class="section-title">Asset Inventory</h3>
        <div style="overflow-x:auto;">
        <table class="emp-table">
            <thead><tr><th>Asset</th><th>Type</th><th>Serial No.</th><th>Status</th><th>Assigned To</th><th>Since</th><th>Action</th></tr></thead>
            <tbody>
            <?php
                $assets = mysqli_query($conn, "SELECT a.*, aa.emp_id, aa.assigned_date, e.first_name, e.last_name
                                                FROM assets a
                                                LEFT JOIN asset_assignments aa ON aa.asset_id = a.asset_id AND aa.returned_date IS NULL
                                                LEFT JOIN employees e ON aa.emp_id = e.emp_id
                                                ORDER BY a.asset_id DESC");
                if(mysqli_num_rows($assets) === 0){
                    echo "<tr><td colspan='7' style='text-align:center;color:var(--text-3,#9aa1ac);'>No assets added yet.</td></tr>";
                }
                $emp_options = '';
                $emp_res = mysqli_query($conn, "SELECT emp_id, first_name, last_name FROM employees ORDER BY first_name");
                while($e = mysqli_fetch_assoc($emp_res)){
                    $emp_options .= "<option value='{$e['emp_id']}'>".htmlspecialchars($e['first_name'].' '.$e['last_name'])."</option>";
                }

                while($row = mysqli_fetch_assoc($assets)){
                    $pc = ['available'=>'green','assigned'=>'blue','under_repair'=>'yellow','retired'=>'gray'][$row['status']] ?? 'gray';
                    $assigned_to = $row['emp_id'] ? htmlspecialchars($row['first_name'].' '.$row['last_name']) : "<span style='color:#9ca3af;'>-</span>";
                    $since = $row['assigned_date'] ?: "<span style='color:#9ca3af;'>-</span>";

                    if($row['status'] === 'available'){
                        $action_cell = "<form action='assign_asset.php' method='POST' style='display:flex;gap:6px;align-items:center;margin-bottom:6px;'>
                            <input type='hidden' name='asset_id' value='{$row['asset_id']}'>
                            <select name='emp_id' style='padding:5px 8px;border-radius:6px;border:1px solid #e0e0e0;font-size:12px;' required>
                                <option value=''>-- Assign to --</option>{$emp_options}
                            </select>
                            <button type='submit' style='padding:5px 10px;background:#1a3a6e;color:white;border:none;border-radius:6px;font-size:12px;cursor:pointer;'>Assign</button>
                        </form>
                        <form action='update_asset_status.php' method='POST' style='display:flex;gap:6px;align-items:center;'>
                            <input type='hidden' name='asset_id' value='{$row['asset_id']}'>
                            <select name='status' style='padding:5px 8px;border-radius:6px;border:1px solid #e0e0e0;font-size:12px;'>
                                <option value='under_repair'>Send for Repair</option>
                                <option value='retired'>Retire</option>
                            </select>
                            <button type='submit' style='padding:5px 10px;background:#6b7280;color:white;border:none;border-radius:6px;font-size:12px;cursor:pointer;'>Update</button>
                        </form>";
                    } elseif($row['status'] === 'assigned'){
                        $action_cell = "<a href='return_asset.php?asset_id={$row['asset_id']}&csrf={$csrf_tok}' onclick=\"return confirm('Mark this asset as returned?');\" style='color:#dc2626;font-size:12px;font-weight:600;text-decoration:none;'>Mark Returned</a>";
                    } elseif($row['status'] === 'under_repair'){
                        $action_cell = "<a href='update_asset_status.php?asset_id={$row['asset_id']}&status=available&csrf={$csrf_tok}' style='color:#16a34a;font-size:12px;font-weight:600;text-decoration:none;'>Mark Repaired (Back to Available)</a>";
                    } else {
                        $action_cell = "<a href='delete_asset.php?id={$row['asset_id']}&csrf={$csrf_tok}' onclick=\"return confirm('Delete this asset permanently?');\" style='color:#dc2626;font-size:12px;font-weight:600;text-decoration:none;'>Delete</a>";
                    }

                    echo "<tr>
                        <td><b>".htmlspecialchars($row['asset_name'])."</b></td>
                        <td>".htmlspecialchars($row['asset_type'])."</td>
                        <td>".htmlspecialchars($row['serial_number'] ?: '-')."</td>
                        <td><span class='pill {$pc}'>".ucfirst(str_replace('_',' ',$row['status']))."</span></td>
                        <td>{$assigned_to}</td>
                        <td>{$since}</td>
                        <td>{$action_cell}</td>
                    </tr>";
                }
            ?>
            </tbody>
        </table>
        </div>
    </div>
</div>

</div>
</div>
</div>
<?php include 'common_js.php'; ?>
</body>
</html>
