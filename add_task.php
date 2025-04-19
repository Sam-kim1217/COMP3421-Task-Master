<?php
declare(strict_types=1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
session_start();
include('includes/connection.php');

header('Content-Type: application/json');

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Check user authentication
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get JSON input
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate input
    if (empty($data['task_name'])) {
        throw new Exception('Task name is required');
    }

    // Prepare statement (now includes description)
    $stmt = $conn->prepare("INSERT INTO tasks (user_id, task_name, description, due_date) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        throw new Exception('Database preparation error: ' . $conn->error);
    }

    $user_id = $_SESSION['user_id'];
    $task_name = trim($data['task_name']);
    $description = !empty($data['description']) ? trim($data['description']) : null;
    $due_date = !empty($data['due_date']) ? $data['due_date'] : null;

    $stmt->bind_param("isss", $user_id, $task_name, $description, $due_date);

    // Execute and respond
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Task added successfully',
            'task_id' => $stmt->insert_id
        ]);
    } else {
        throw new Exception('Database execution error: ' . $stmt->error);
    }
} catch (Exception $e) {
    error_log('Error in add_task.php: ' . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
?>