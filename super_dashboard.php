<?php
session_start();
if(!isset($_SESSION['user']) || $_SESSION['user']['role']!='super_admin'){
    header("Location: index.php"); exit();
}
require 'db.php';
$page_title = "Dashboard";

// Fetch monthly attendance data (present days per month, current year)
$att_data = array_fill(0, 12, 0);
$r = mysqli_query($conn, "SELECT MONTH(date) as mon, COUNT(*) as cnt FROM attendance WHERE status='present' AND YEAR(date)=YEAR(CURDATE()) GROUP BY MONTH(date)");
while($row = mysqli_fetch_assoc($r)) $att_data[$row['mon']-1] = (int)$row['cnt'];

// Fetch monthly leaves data (leave requests per month, current year)
$leave_data = array_fill(0, 12, 0);
$r = mysqli_query($conn, "SELECT MONTH(from_date) as mon, COUNT(*) as cnt FROM leaves WHERE YEAR(from_date)=YEAR(CURDATE()) GROUP BY MONTH(from_date)");
while($row = mysqli_fetch_assoc($r)) $leave_data[$row['mon']-1] = (int)$row['cnt'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard - EMS</title>
<link rel="stylesheet" href="style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php include 'common_styles.php'; ?>
<style>
.dash-charts-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-top: 24px;
}
@media(max-width: 900px) {
    .dash-charts-grid { grid-template-columns: 1fr; }
}
.chart-card {
    background: #fff;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(0,0,0,.07);
    min-width: 0;
}
.chart-card h3 {
    font-size: 14px;
    color: #60a5fa;
    margin: 0 0 16px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eef2ff;
    font-weight: 700;
}
.stat-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
@media(max-width: 900px) {
    .stat-cards-grid { grid-template-columns: repeat(2, 1fr); }
}
@media(max-width: 600px) {
    .stat-cards-grid { grid-template-columns: 1fr; }
}
</style>
<style>
html, body { overflow: hidden; height: 100%; }
.main-content { overflow-y: scroll; scrollbar-width: none; -ms-overflow-style: none; }
.main-content::-webkit-scrollbar { display: none; }
</style>
</head>
<body>
<div class="dashboard">
<?php include 'sidebar_sa.php'; ?>
<div class="main-content">
<?php include 'topbar_sa.php'; ?>

<div class="section active">

    <!-- Stat Cards -->
    <div class="stat-cards-grid">
        <div class="card"><h3>Total Employees</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM users WHERE role='employee'"))['t']; ?></p></div>
        <div class="card"><h3>Present Today</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM attendance WHERE date=CURDATE() AND status='present'"))['t']; ?></p></div>
        <div class="card"><h3>Pending Leaves</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='pending'"))['t']; ?></p></div>
        <div class="card"><h3>Approved Leaves</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='approved'"))['t']; ?></p></div>
        <div class="card"><h3>Pending Tasks</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='pending'"))['t']; ?></p></div>
        <div class="card"><h3>Completed Tasks</h3><p class="num"><?php echo mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status='completed'"))['t']; ?></p></div>
    </div>

    <!-- Bar Charts -->
    <div class="dash-charts-grid">
        <div class="chart-card">
            <h3>&#128200; Company Monthly Attendance</h3>
            <canvas id="attendanceChart" height="220"></canvas>
        </div>
        <div class="chart-card">
            <h3>&#127809; Company Monthly Leaves</h3>
            <canvas id="leaveChart" height="220"></canvas>
        </div>
    </div>

</div>

</div>
</div>

<script>
const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const attData  = <?php echo json_encode(array_values($att_data)); ?>;
const leaveData = <?php echo json_encode(array_values($leave_data)); ?>;

new Chart(document.getElementById('attendanceChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Present Days',
            data: attData,
            backgroundColor: 'rgba(59,130,246,0.75)',
            borderColor: '#3b82f6',
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + ctx.parsed.y + ' days present'
                }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});

new Chart(document.getElementById('leaveChart'), {
    type: 'bar',
    data: {
        labels: months,
        datasets: [{
            label: 'Leave Requests',
            data: leaveData,
            backgroundColor: 'rgba(239,68,68,0.75)',
            borderColor: '#ef4444',
            borderWidth: 1.5,
            borderRadius: 6,
            borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ' ' + ctx.parsed.y + ' leave requests'
                }
            }
        },
        scales: {
            y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,.05)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
<?php include 'common_js.php'; ?>
</body>
</html>