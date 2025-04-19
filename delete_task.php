<?php
session_start();
include('includes/connection.php');
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get task ID and CSRF token
    $task_id = isset($_GET['id']) ? intval($_GET['id']) : null;
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!$task_id) {
        throw new Exception('Invalid task ID');
    }

    // Validate CSRF token
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

    $stmt = $conn->prepare("DELETE FROM tasks WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    if ($stmt->affected_rows === 0) {
        throw new Exception('Task not found');
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Task deleted successfully'
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>