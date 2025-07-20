<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $task_id = $input['task_id'] ?? null;
    
    if (!$task_id || !is_numeric($task_id)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
        exit();
    }
    
    try {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        // Get task info before deletion for response
        $stmt = mysqli_prepare($conn, "SELECT t.name as task_name, u.name as user_name 
                                       FROM user_tasks ut 
                                       JOIN tasks t ON ut.task_id = t.id 
                                       JOIN users u ON ut.user_id = u.id 
                                       WHERE ut.id = ?");
        mysqli_stmt_bind_param($stmt, "i", $task_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $task_info = mysqli_fetch_assoc($result);
        
        if (!$task_info) {
            mysqli_rollback($conn);
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Task not found']);
            exit();
        }
        
        // Delete related task achievements first
        $stmt = mysqli_prepare($conn, "DELETE FROM task_achievements WHERE user_task_id = ?");
        mysqli_stmt_bind_param($stmt, "i", $task_id);
        mysqli_stmt_execute($stmt);
        
        // Delete the user task
        $stmt = mysqli_prepare($conn, "DELETE FROM user_tasks WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $task_id);
        $delete_result = mysqli_stmt_execute($stmt);
        
        if ($delete_result && mysqli_affected_rows($conn) > 0) {
            mysqli_commit($conn);
            echo json_encode([
                'success' => true, 
                'message' => 'Task deleted successfully',
                'task_name' => $task_info['task_name'],
                'user_name' => $task_info['user_name']
            ]);
        } else {
            mysqli_rollback($conn);
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to delete task']);
        }
        
    } catch (Exception $e) {
        mysqli_rollback($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
?>
