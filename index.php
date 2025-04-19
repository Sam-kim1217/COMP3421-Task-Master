<?php
declare(strict_types=1);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
include('includes/connection.php');
include('includes/navbar.php');
if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}
$user_id = $_SESSION['user_id'];
$tasks = $conn->query("SELECT * FROM tasks WHERE user_id = $user_id ORDER BY due_date");
?>

<!DOCTYPE html>
<html>
<head>
  <title>Task Master</title>
  <link rel="stylesheet" href="assets/style.css">
  <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
</head>
<body>
  <div class="container">
    <h1>Your Tasks</h1>
    <!-- Add Task Form -->
    <form id="taskForm" class="task-form">
      <div class="form-group">
        <label for="taskInput">Task Name</label>
        <input type="text" id="taskInput" class="task-input" 
              placeholder="Enter task name..." required>
      </div>
      
      <div class="form-group">
        <label for="taskDescription">Description</label>
        <textarea id="taskDescription" class="task-input" 
                  placeholder="Brief description..."></textarea>
      </div>
      
      <div class="form-group">
          <label for="dueDate">Due Date</label>
          <input type="date" id="dueDate" class="due-date-input">
      </div>

      <div class="form-group">
        <button type="submit" class="btn primary">Add Task</button>
      </div>
    </form>
    
    <!-- Message Display -->
    <div id="message" class="message"></div>

    <!-- Task List -->
    <div class="task-list">
      <?php while($task = $tasks->fetch_assoc()): ?>
        <div class="task-item <?php echo $task['status']; ?>" data-id="<?php echo $task['task_id']; ?>">
          <div class="task-content">
            <span class="task-text"><?php echo htmlspecialchars($task['task_name']); ?></span>
            <?php if($task['due_date']): ?>
              <span class="due-date">📅 <?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
            <?php endif; ?>
          </div>
          <div class="actions">
            <button class="btn complete-btn" title="Complete">✓</button>
            <button class="btn edit-btn" title="Edit">✎</button>
            <button class="btn delete-btn" title="Delete">✕</button>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>
  <div class="completed-tasks-menu">
    <div class="menu-header" onclick="toggleCompletedMenu()">
        📋 Completed Tasks
    </div>
    <div class="completed-list"></div>
  </div>
  <script src="assets/script.js"></script>
</body>
</html>