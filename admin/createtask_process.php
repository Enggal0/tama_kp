<?php
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $task_id = $_POST['task_id'];
    $description = $_POST['description'] ?? '';
    $deadline = $_POST['deadline'];
    $target_int = !empty($_POST['target_int']) ? $_POST['target_int'] : null;
    $target_str = !empty($_POST['target_str']) ? $_POST['target_str'] : null;
    
    // Validate required fields
    if (empty($user_id) || empty($task_id) || empty($deadline)) {
        header("Location: createtask.php?error=missing_fields");
        exit();
    }
    
    // Get task type to determine which target to use
    $task_type_sql = "SELECT type FROM tasks WHERE id = ?";
    $stmt = mysqli_prepare($conn, $task_type_sql);
    mysqli_stmt_bind_param($stmt, "i", $task_id);
    mysqli_stmt_execute($stmt);
    $task_type_result = mysqli_stmt_get_result($stmt);
    $task_type_row = mysqli_fetch_assoc($task_type_result);
    
    if (!$task_type_row) {
        header("Location: createtask.php?error=invalid_task");
        exit();
    }
    
    $task_type = $task_type_row['type'];
    
    // Insert into user_tasks table
    $sql = "INSERT INTO user_tasks (user_id, task_id, description, target_int, target_str, deadline, status, progress_int) VALUES (?, ?, ?, ?, ?, ?, 'In Progress', 0)";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iisiss", $user_id, $task_id, $description, $target_int, $target_str, $deadline);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: managetask.php?success=task_created");
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
