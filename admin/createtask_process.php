<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $task_id = $_POST['task_id'];
    $task_type = $_POST['task_type']; // Get the selected task type from form
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $target_int = !empty($_POST['target_int']) ? $_POST['target_int'] : null;
    $target_str = !empty($_POST['target_str']) ? $_POST['target_str'] : null;
    
    // Validate required fields
    if (empty($user_id) || empty($task_id) || empty($task_type) || empty($start_date) || empty($end_date)) {
        header("Location: createtask.php?error=missing_fields");
        exit();
    }
    
    // Validate date range
    if (strtotime($end_date) < strtotime($start_date)) {
        header("Location: createtask.php?error=invalid_date_range");
        exit();
    }
    
    // Validate start date is not in the past
    if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        header("Location: createtask.php?error=invalid_start_date");
        exit();
    }
    
    // Validate task type and target consistency
    if ($task_type === 'numeric' && empty($target_int)) {
        header("Location: createtask.php?error=missing_numeric_target");
        exit();
    }
    
    if ($task_type === 'textual' && empty($target_str)) {
        header("Location: createtask.php?error=missing_textual_target");
        exit();
    }
    
    // Insert into user_tasks table with task_type
    $sql = "INSERT INTO user_tasks (user_id, task_id, task_type, description, target_int, target_str, start_date, end_date, status, progress_int) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'In Progress', 0)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iississs", $user_id, $task_id, $task_type, $description, $target_int, $target_str, $start_date, $end_date);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: managetask.php?success=1");
        exit();
    } else {
        header("Location: createtask.php?error=database_error");
        exit();
    }
} else {
    header("Location: createtask.php");
    exit();
}
?>
