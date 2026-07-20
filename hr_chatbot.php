<?php
// hr_chatbot.php — the "Zia-style" HR assistant.
// It never lets the AI invent numbers: we pull the employee's real leave
// balance / attendance / tasks (or company-wide stats for admin/super_admin)
// straight from the DB first, then hand THAT to the model as context and
// ask it to answer using only that data. The model is just doing the
// "turn numbers into a friendly sentence" part — not the data lookup.
session_start();
header('Content-Type: application/json');

if(!isset($_SESSION['user'])){
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

require 'db.php';

if(!defined('GEMINI_API_KEY') || GEMINI_API_KEY === '' || GEMINI_API_KEY === 'your_gemini_api_key_here'){
    echo json_encode(['error' => 'The chatbot is not configured yet — add GEMINI_API_KEY to config.php. Get a free key at https://aistudio.google.com/apikey']);
    exit();
}

$input   = json_decode(file_get_contents('php://input'), true);
$message = trim($input['message'] ?? '');
if($message === ''){
    echo json_encode(['error' => 'Empty message']);
    exit();
}
if(mb_strlen($message) > 500){
    echo json_encode(['error' => 'Message too long — keep it under 500 characters']);
    exit();
}

$role    = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['id'];
$context_lines = [];

if($role === 'employee'){

    $emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM employees WHERE user_id='".(int)$user_id."'"));
    $emp_id = $emp['emp_id'] ?? 0;
    $context_lines[] = "Employee: {$emp['first_name']} {$emp['last_name']}, designation: {$emp['designation']}.";

    // Leave balance — same calculation used on the My Leaves page
    $lt_all = mysqli_query($conn, "SELECT * FROM leave_types ORDER BY leave_type_name");
    while($lt = mysqli_fetch_assoc($lt_all)){
        $allotted = (int)$lt['total_days'];
        if($allotted <= 0) continue;
        $used_days = 0;
        $ures = mysqli_query($conn, "SELECT from_date, to_date FROM leaves WHERE emp_id='{$emp_id}' AND leave_type='".mysqli_real_escape_string($conn,$lt['leave_type_name'])."' AND status='approved'");
        while($u = mysqli_fetch_assoc($ures)){
            $used_days += getLeaveDaysWithSandwich($u['from_date'], $u['to_date']);
        }
        $remaining = max(0, $allotted - $used_days);
        $context_lines[] = "{$lt['leave_type_name']}: {$remaining} of {$allotted} days remaining ({$used_days} used).";
    }

    // This month's attendance
    $month_start = date('Y-m-01');
    $att_counts = ['present'=>0,'late'=>0,'half_day'=>0,'work_from_home'=>0,'absent'=>0];
    $ares = mysqli_query($conn, "SELECT status, COUNT(*) as c FROM attendance WHERE emp_id='{$emp_id}' AND date >= '{$month_start}' GROUP BY status");
    while($a = mysqli_fetch_assoc($ares)){
        if(isset($att_counts[$a['status']])) $att_counts[$a['status']] = (int)$a['c'];
    }
    $context_lines[] = "This month's attendance so far: {$att_counts['present']} present, {$att_counts['late']} late, {$att_counts['half_day']} half-day, {$att_counts['work_from_home']} work-from-home, {$att_counts['absent']} absent.";

    // Pending / in-progress tasks
    $tres = mysqli_query($conn, "SELECT task_name, status, target_date FROM tasks WHERE emp_id='{$emp_id}' AND status != 'completed' ORDER BY target_date ASC LIMIT 10");
    $tasks = [];
    while($t = mysqli_fetch_assoc($tres)){
        $tasks[] = "\"{$t['task_name']}\" (status: {$t['status']}, due {$t['target_date']})";
    }
    $context_lines[] = !empty($tasks) ? ("Open tasks: " . implode('; ', $tasks) . ".") : "No open tasks right now.";

} elseif($role === 'admin' || $role === 'super_admin'){

    $total_emp = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM users WHERE role='employee'"))['t'];
    $context_lines[] = "Total employees: {$total_emp}.";

    $today = date('Y-m-d');
    $att_counts = ['present'=>0,'late'=>0,'half_day'=>0,'work_from_home'=>0,'absent'=>0];
    $ares = mysqli_query($conn, "SELECT status, COUNT(*) as c FROM attendance WHERE date='{$today}' GROUP BY status");
    while($a = mysqli_fetch_assoc($ares)){
        if(isset($att_counts[$a['status']])) $att_counts[$a['status']] = (int)$a['c'];
    }
    $context_lines[] = "Today's attendance: {$att_counts['present']} present, {$att_counts['late']} late, {$att_counts['half_day']} half-day, {$att_counts['work_from_home']} WFH, {$att_counts['absent']} absent.";

    $pending_hr = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM hr_process_requests WHERE status='pending'"))['t'] ?? 0;
    $pending_reg = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM regularization_requests WHERE status='pending'"))['t'] ?? 0;
    $pending_leave = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM leaves WHERE status='pending'"))['t'] ?? 0;
    $context_lines[] = "Pending approvals: {$pending_leave} leave requests, {$pending_hr} HR process requests (designation/department/location change), {$pending_reg} attendance regularization requests.";

    $open_tasks = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status != 'completed'"))['t'] ?? 0;
    $overdue_tasks = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as t FROM tasks WHERE status != 'completed' AND target_date < '{$today}'"))['t'] ?? 0;
    $context_lines[] = "Open tasks company-wide: {$open_tasks} ({$overdue_tasks} overdue).";

} else {
    echo json_encode(['error' => 'Unsupported role']);
    exit();
}

$context = implode("\n", $context_lines);

$system_prompt = "You are an HR assistant embedded in this company's Employee Management System (EMS). "
    . "Answer the user's question using ONLY the data below about them — never invent numbers or facts. "
    . "If the answer isn't in the data, say you don't have that information instead of guessing. "
    . "Be warm but concise — 2 to 4 sentences, no long lists unless asked.\n\nDATA:\n" . $context;

$payload = json_encode([
    'system_instruction' => ['parts' => [['text' => $system_prompt]]],
    'contents' => [['parts' => [['text' => $message]]]],
]);

$ch = curl_init('https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'x-goog-api-key: ' . GEMINI_API_KEY],
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_TIMEOUT => 20,
]);
$response = curl_exec($ch);
$curl_err = curl_error($ch);
curl_close($ch);

if($curl_err){
    echo json_encode(['error' => 'Could not reach the AI service: ' . $curl_err]);
    exit();
}

$data = json_decode($response, true);
$reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

if(!$reply){
    $api_error = $data['error']['message'] ?? 'Unknown error from AI service';
    echo json_encode(['error' => $api_error]);
    exit();
}

echo json_encode(['reply' => trim($reply)]);
