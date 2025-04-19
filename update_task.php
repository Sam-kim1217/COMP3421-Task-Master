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
    
    // Validate CSRF
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        throw new Exception('Invalid CSRF token');
    }

    // Validate inputs
    $task_name = trim($data['task_name'] ?? '');
    $description = !empty($data['description']) ? trim($data['description']) : null;
    $due_date = $data['due_date'] ?? null;

    if (empty($task_name)) {
        throw new Exception('Task name cannot be empty');
    }

    if ($due_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $due_date)) {
        throw new Exception('Invalid date format (YYYY-MM-DD)');
    }

    // Update task (now includes description)
    $stmt = $conn->prepare("UPDATE tasks 
                          SET task_name = ?, description = ?, due_date = ?
                          WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("sssii", $task_name, $description, $due_date, $task_id, $_SESSION['user_id']);

    if (!$stmt->execute()) {
        throw new Exception('Database error: ' . $stmt->error);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Task updated successfully',
        'formatted_date' => $due_date ? date('M d, Y', strtotime($due_date)) : ''
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