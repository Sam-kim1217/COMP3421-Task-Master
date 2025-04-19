<?php
session_start();
include('includes/connection.php');

try {
    $task_id = $_GET['id'] ?? null;
    $user_id = $_SESSION['user_id'] ?? null;
    
    if (!$user_id) throw new Exception('Unauthorized');
    if (!$task_id || !filter_var($task_id, FILTER_VALIDATE_INT)) {
        throw new Exception('Invalid task ID');
    }

    $stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $task_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 0) throw new Exception('Task not found');
    
    $task = $result->fetch_assoc();
    
    echo json_encode([
        'status' => 'success',
        'task_name' => htmlspecialchars($task['task_name']),
        'description' => htmlspecialchars($task['description']),
        'due_date' => $task['due_date']
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>