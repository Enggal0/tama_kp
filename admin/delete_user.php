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
$userId = isset($input['user_id']) ? intval($input['user_id']) : 0;

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    $conn->begin_transaction();
    $stmt = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('User not found');
    }
    
    $user = $result->fetch_assoc();
    
    if ($userId == $_SESSION['user_id']) {
        throw new Exception('Cannot delete your own account');
    }
    
    $stmt = $conn->prepare("DELETE FROM user_tasks WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    $stmt = $conn->prepare("DELETE FROM task_achievements WHERE user_task_id IN (SELECT id FROM user_tasks WHERE user_id = ?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('Failed to delete user');
    }
    
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'User "' . htmlspecialchars($user['name']) . '" has been deleted successfully'
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode([
        'success' => false, 
        'message' => 'Error deleting user: ' . $e->getMessage()
    ]);
}

$conn->close();
?>