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
    $task_type = $_POST['task_type']; 
    $description = $_POST['description'] ?? '';
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $target_int = !empty($_POST['target_int']) ? $_POST['target_int'] : null;
    $target_str = !empty($_POST['target_str']) ? $_POST['target_str'] : null;
    $new_task_name = $_POST['new_task_name'] ?? ''; 
    
    if (empty($user_id) || empty($task_id) || empty($task_type) || empty($start_date) || empty($end_date)) {
        header("Location: createtask.php?error=missing_fields");
        exit();
    }
    
    if (strtotime($end_date) < strtotime($start_date)) {
        header("Location: createtask.php?error=invalid_date_range");
        exit();
    }
    
    if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        header("Location: createtask.php?error=invalid_start_date");
        exit();
    } 
    
    if ($task_type === 'numeric') {
        if (empty($target_int)) {
            header("Location: createtask.php?error=missing_numeric_target");
            exit();
        }
        
        $target_str = null;
    }
    
    if ($task_type === 'textual') {
        if (empty($target_str)) {
            header("Location: createtask.php?error=missing_textual_target");
            exit();
        }
        
        $target_int = null;
    }
    
    if ($task_id === 'add_new') {
        if (empty($new_task_name)) {
            header("Location: createtask.php?error=missing_new_task_name");
            exit();
        }
        
        $insertTaskSql = "INSERT INTO tasks (name) VALUES (?)";
        $insertTaskStmt = mysqli_prepare($conn, $insertTaskSql);
        mysqli_stmt_bind_param($insertTaskStmt, "s", $new_task_name);
        if (!mysqli_stmt_execute($insertTaskStmt)) {
            header("Location: createtask.php?error=database_error");
            exit();
        }
        $task_id = mysqli_insert_id($conn);
    }
    
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
