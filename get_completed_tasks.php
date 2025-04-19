<?php
session_start();
include('includes/connection.php');

try {
    $user_id = $_SESSION['user_id'] ?? null;
    if (!$user_id) throw new Exception('Unauthorized');

    $result = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id AND status = 'completed'");
    $tasks = [];
    
    while ($task = $result->fetch_assoc()) {
        $tasks[] = [
            'id' => $task['task_id'],
            'name' => htmlspecialchars($task['task_name']),
            'description' => htmlspecialchars($task['description']),
            'due_date' => $task['due_date'] ? date('Y-m-d', strtotime($task['due_date'])) : ''
        ];
    }
    
    echo json_encode(['status' => 'success', 'tasks' => $tasks]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>