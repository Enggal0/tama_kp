<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

$task_id = isset($input['task_id']) ? intval($input['task_id']) : 0;
$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;
$task_type_id = isset($input['task_type_id']) ? intval($input['task_type_id']) : 0;
$description = isset($input['description']) ? trim($input['description']) : '';
$deadline = isset($input['deadline']) ? $input['deadline'] : '';
$target = isset($input['target']) ? trim($input['target']) : '';

if ($task_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid task ID']);
    exit();
}

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select an employee']);
    exit();
}

if ($task_type_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Please select a task type']);
    exit();
}

if (empty($deadline)) {
    echo json_encode(['success' => false, 'message' => 'Please set a deadline']);
    exit();
}

if (empty($target)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a target']);
    exit();
}

$date = DateTime::createFromFormat('Y-m-d', $deadline);
if (!$date || $date->format('Y-m-d') !== $deadline) {
    echo json_encode(['success' => false, 'message' => 'Invalid deadline format']);
    exit();
}

try {
    $stmt = $conn->prepare("SELECT id, status FROM user_tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Task not found');
    }
    
    $current_task = $result->fetch_assoc();
    
    if ($current_task['status'] === 'Achieved') {
        throw new Exception('Cannot edit task that has already been achieved');
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? AND role = 'employee'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Invalid employee selected');
    }
    
    $stmt = $conn->prepare("SELECT task_type FROM user_tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        throw new Exception('Invalid user task selected');
    }
    $task_type_data = $result->fetch_assoc();
    $task_type = $task_type_data['task_type'];
    
    $target_int = null;
    $target_str = null;

    if ($task_type === 'numeric') {
        
        if (is_numeric($target)) {
            $target_int = intval($target);
        } else {
            throw new Exception('Target must be a number for numeric tasks');
        }
    } else {
        
        $target_str = $target;
    }
    
    $stmt = $conn->prepare("UPDATE user_tasks SET user_id = ?, task_id = ?, description = ?, end_date = ?, target_int = ?, target_str = ?, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("iissisi", $user_id, $task_type_id, $description, $deadline, $target_int, $target_str, $task_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Task updated successfully'
        ]);
    } else {
        throw new Exception('Failed to update task');
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Error updating task: ' . $e->getMessage()
    ]);
}

$conn->close();
?>