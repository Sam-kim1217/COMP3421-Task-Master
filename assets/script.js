// Message display function
function showMessage(type, text) {
    const messageDiv = document.getElementById('message');
    messageDiv.textContent = text;
    messageDiv.className = `message ${type}`;
    setTimeout(() => {
        messageDiv.className = 'message';
        messageDiv.textContent = '';
    }, 3000);
}

// HTML escaping function
function escapeHtml(unsafe) {
    return unsafe.replace(/&/g, "&amp;")
                 .replace(/</g, "&lt;")
                 .replace(/>/g, "&gt;")
                 .replace(/"/g, "&quot;")
                 .replace(/'/g, "&#039;");
}

// Generic response handler
function handleResponse(response) {
    if (!response.ok) {
        return response.json().then(err => Promise.reject(err));
    }
    return response.json();
}

// Generic error handler
function handleError(error) {
    showMessage('error', error.message || 'Network error. Please try again.');
    console.error('Error:', error);
}

// Modal management
let activeModal = null;

// Toggle completed tasks menu
function toggleCompletedMenu() {
    const menu = document.querySelector('.completed-tasks-menu');
    const list = menu.querySelector('.completed-list');
    menu.classList.toggle('expanded');
    
    if (menu.classList.contains('expanded')) {
        list.style.maxHeight = '50vh';
        list.style.padding = '1rem 0';
    } else {
        list.style.maxHeight = '0';
        list.style.padding = '0';
    }
}

// Add Task
document.getElementById('taskForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const taskInput = document.getElementById('taskInput');
    const dueDate = document.getElementById('dueDate');
    const taskDescription = document.getElementById('taskDescription');
    
    if (!taskInput.value.trim()) {
        showMessage('error', 'Please enter a task name');
        taskInput.focus();
        return;
    }

    const taskData = {
        task_name: taskInput.value.trim(),
        description: taskDescription.value.trim(),
        due_date: dueDate.value,
        csrf_token: document.querySelector('meta[name="csrf-token"]').content
    };

    fetch('add_task.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(taskData)
    })
    .then(handleResponse)
    .then(data => {
        if(data.status === 'success') {
            showMessage('success', data.message);
            taskInput.value = '';
            dueDate.value = '';
            taskDescription.value = '';
            loadTasks(); // Reload tasks after adding
        }
    })
    .catch(handleError);
});

// Event Delegation for Task Actions
document.body.addEventListener('click', function(e) {
    const taskItem = e.target.closest('.task-item, .completed-task');
    const taskId = taskItem?.dataset.id;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Delete Task
    if (e.target.closest('.delete-btn')) {
        if (!confirm('Are you sure you want to delete this task?')) return;
        
        fetch(`delete_task.php?id=${taskId}`, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ csrf_token: csrfToken })
        })
        .then(handleResponse)
        .then(data => {
            if(data.status === 'success') {
                showMessage('success', data.message);
                loadTasks(); // Reload tasks after deletion
            }
        })
        .catch(handleError);
    }

    // Complete Task
    if (e.target.closest('.complete-btn')) {
        fetch(`complete_task.php?id=${taskId}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ csrf_token: csrfToken })
        })
        .then(handleResponse)
        .then(data => {
            if(data.status === 'success') {
                showMessage('success', data.message);
                loadTasks(); // Reload tasks after completion
            }
        })
        .catch(handleError);
    }

    // Restore Task
    if (e.target.closest('.restore-btn')) {
        fetch(`complete_task.php?id=${taskId}`, {
            method: 'PUT',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ csrf_token: csrfToken })
        })
        .then(handleResponse)
        .then(data => {
            if(data.status === 'success') {
                showMessage('success', 'Task restored to pending');
                loadTasks(); // Reload tasks after restore
            }
        })
        .catch(handleError);
    }

    // Edit Task
    if (e.target.closest('.edit-btn')) {
        if(activeModal) activeModal.remove();
        
        const taskText = taskItem.querySelector('.task-text').textContent;
        const dueDateElement = taskItem.querySelector('.due-date');
        const rawDueDate = dueDateElement ? dueDateElement.textContent : '';
        const description = taskItem.querySelector('.task-description')?.textContent || '';

        // Date conversion
        let dueDateValue = '';
        if (rawDueDate) {
            const dateParts = rawDueDate.replace(',', '').split(' ');
            const monthMap = {
                'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
                'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
                'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
            };
            dueDateValue = `${dateParts[2]}-${monthMap[dateParts[0]]}-${dateParts[1].padStart(2, '0')}`;
        }

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'edit-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <h3>Edit Task</h3>
                <form class="edit-task-form">
                    <div class="form-group">
                        <label>Task Name:</label>
                        <input type="text" class="edit-task-name" value="${escapeHtml(taskText)}" required>
                    </div>
                    <div class="form-group">
                        <label>Description:</label>
                        <textarea class="edit-description">${escapeHtml(description)}</textarea>
                    </div>
                    <div class="form-group">
                        <label>Due Date:</label>
                        <input type="date" class="edit-due-date" value="${dueDateValue}">
                    </div>
                    <div class="modal-actions">
                        <button type="button" class="btn cancel-btn">Cancel</button>
                        <button type="submit" class="btn primary">Save Changes</button>
                    </div>
                </form>
            </div>
        `;

        document.body.appendChild(modal);
        activeModal = modal;

        // Handle form submission
        modal.querySelector('.edit-task-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const updatedData = {
                task_name: modal.querySelector('.edit-task-name').value.trim(),
                description: modal.querySelector('.edit-description').value.trim(),
                due_date: modal.querySelector('.edit-due-date').value,
                csrf_token: csrfToken
            };

            if (!updatedData.task_name) {
                showMessage('error', 'Task name cannot be empty');
                return;
            }

            fetch(`update_task.php?id=${taskId}`, {
                method: 'PUT',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(updatedData)
            })
            .then(handleResponse)
            .then(data => {
                if(data.status === 'success') {
                    loadTasks(); // Reload tasks after edit
                    modal.remove();
                    activeModal = null;
                }
            })
            .catch(handleError);
        });

        // Handle cancel
        modal.querySelector('.cancel-btn').addEventListener('click', () => {
            modal.remove();
            activeModal = null;
        });
    }
});

// Load initial tasks
function loadTasks() {
    // Load pending tasks
    fetch('get_pending_tasks.php')
        .then(handleResponse)
        .then(data => {
            if(data.status === 'success') {
                document.querySelector('.task-list').innerHTML = data.tasks
                    .map(task => `
                        <div class="task-item pending" data-id="${task.id}">
                            <div class="task-content">
                                <div class="task-main">
                                    <span class="task-text">${task.name}</span>
                                    ${task.description ? `
                                    <div class="task-description">${task.description}</div>
                                    ` : ''}
                                </div>
                                ${task.due_date ? `
                                <span class="due-date">${ 
                                    new Date(task.due_date).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric'
                                    })
                                }</span>` : ''}
                            </div>
                            <div class="actions">
                                <button class="btn complete-btn" title="Complete">✓</button>
                                <button class="btn edit-btn" title="Edit">✎</button>
                                <button class="btn delete-btn" title="Delete">✕</button>
                            </div>
                        </div>
                    `).join('');
            }
        })
        .catch(handleError);

    // Load completed tasks
    fetch('get_completed_tasks.php')
        .then(handleResponse)
        .then(data => {
            if(data.status === 'success') {
                document.querySelector('.completed-list').innerHTML = data.tasks
                    .map(task => `
                        <div class="completed-task" data-id="${task.id}">
                            <div class="task-content">
                                <div class="task-main">
                                    <span class="task-text">${task.name}</span>
                                    ${task.description ? `
                                    <div class="task-description">${task.description}</div>
                                    ` : ''}
                                </div>
                                ${task.due_date ? `
                                <span class="due-date">${
                                    new Date(task.due_date).toLocaleDateString('en-US', {
                                        month: 'short',
                                        day: 'numeric',
                                        year: 'numeric'
                                    })
                                }</span>` : ''}
                            </div>
                            <div class="actions">
                                <button class="btn restore-btn" title="Restore">↻ Restore</button>
                            </div>
                        </div>
                    `).join('');
            }
        })
        .catch(handleError);
}

// Initialize on page load
window.addEventListener('load', loadTasks);