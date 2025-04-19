<?php
session_start();
include('includes/connection.php');
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    $task_id = $_GET['id'] ?? null;
    if (!$task_id || !filter_var($task_id, FILTER_VALIDATE_INT)) {
        throw new Exception('Invalid task ID');
    }

    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

    // Toggle status
    $stmt = $conn->prepare("UPDATE tasks 
                          SET status = CASE 
                            WHEN status = 'completed' THEN 'pending' 
                            ELSE 'completed' 
                          END
                          WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    // Get updated status
    $result = $conn->query("SELECT status FROM tasks WHERE task_id = $task_id");
    $new_status = $result->fetch_assoc()['status'];

    echo json_encode([
        'status' => 'success',
        'message' => 'Task status updated',
        'new_status' => $new_status
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>