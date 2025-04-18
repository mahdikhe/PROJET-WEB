<?php
// Include database connection
include_once 'create project/db.php';

// Process API calls for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    // Set the response header to JSON
    header('Content-Type: application/json');
    $response = [
        'success' => false,
        'tasks' => [],
        'message' => ''
    ];
    
    // Get the request body or form data
    $data = $_POST;
    if (empty($data)) {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
    }
    
    // Process different actions
    if (isset($data['action'])) {
        $action = $data['action'];
        
        if ($action === 'get_tasks' && isset($data['project_id'])) {
            $project_id = $data['project_id'];
            
            // Prepare the SQL statement
            $stmt = $conn->prepare("SELECT * FROM tasks WHERE project_id = :project_id");
            $stmt->bindParam(':project_id', $project_id);
            $stmt->execute();
            
            // Fetch all tasks associated with the project
            $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Set the response
            $response['success'] = true;
            $response['tasks'] = $tasks;
        } elseif ($action === 'update_status' && isset($data['task_id']) && isset($data['new_status'])) {
            $taskId = $data['task_id'];
            $newStatus = $data['new_status'];
            
            $updateQuery = "UPDATE tasks SET status = :status WHERE id = :id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $taskId);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Task status updated successfully';
            } else {
                $response['message'] = 'Error updating task status';
            }
        } elseif ($action === 'delete_task' && isset($data['task_id'])) {
            $taskId = $data['task_id'];
            
            $deleteQuery = "DELETE FROM tasks WHERE id = :id";
            $stmt = $conn->prepare($deleteQuery);
            $stmt->bindParam(':id', $taskId);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Task deleted successfully';
            } else {
                $response['message'] = 'Error deleting task';
            }
        } elseif ($action === 'update_task' && isset($data['task_id'])) {
            $taskId = $data['task_id'];
            
            // Build the update query dynamically based on the fields provided
            $updateFields = [];
            $params = [':id' => $taskId];
            
            if (isset($data['title']) && !empty($data['title'])) {
                $updateFields[] = "title = :title";
                $params[':title'] = $data['title'];
            }
            
            if (isset($data['description'])) {
                $updateFields[] = "description = :description";
                $params[':description'] = $data['description'];
            }
            
            if (isset($data['status'])) {
                $updateFields[] = "status = :status";
                $params[':status'] = $data['status'];
            }
            
            if (isset($data['priority'])) {
                $updateFields[] = "priority = :priority";
                $params[':priority'] = $data['priority'];
            }
            
            if (isset($data['assigned_to'])) {
                $updateFields[] = "assigned_to = :assigned_to";
                $params[':assigned_to'] = $data['assigned_to'];
            }
            
            if (isset($data['due_date'])) {
                $updateFields[] = "due_date = :due_date";
                $params[':due_date'] = $data['due_date'] ?: null;
            }
            
            if (isset($data['estimated_hours'])) {
                $updateFields[] = "estimated_hours = :estimated_hours";
                $params[':estimated_hours'] = $data['estimated_hours'] ?: null;
            }
            
            if (!empty($updateFields)) {
                $updateQuery = "UPDATE tasks SET " . implode(', ', $updateFields) . " WHERE id = :id";
                $stmt = $conn->prepare($updateQuery);
                
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
                }
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Task updated successfully';
                } else {
                    $response['message'] = 'Error updating task';
                }
            } else {
                $response['message'] = 'No fields to update';
            }
        } elseif ($action === 'create_task') {
            if (!empty($data['title']) && !empty($data['project_id'])) {
                $title = $data['title'];
                $description = $data['description'] ?? '';
                $projectId = $data['project_id'];
                $status = $data['status'] ?? 'To Do';
                $priority = $data['priority'] ?? 'Medium';
                $assignedTo = $data['assigned_to'] ?? '';
                $dueDate = $data['due_date'] ?? null;
                $estimatedHours = $data['estimated_hours'] ?? null;
                
                $insertQuery = "INSERT INTO tasks (project_id, title, description, status, priority, assigned_to, created_by, estimated_hours, due_date)
                               VALUES (:project_id, :title, :description, :status, :priority, :assigned_to, 'Current User', :estimated_hours, :due_date)";
                
                $stmt = $conn->prepare($insertQuery);
                $stmt->bindParam(':project_id', $projectId);
                $stmt->bindParam(':title', $title);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':status', $status);
                $stmt->bindParam(':priority', $priority);
                $stmt->bindParam(':assigned_to', $assignedTo);
                $stmt->bindParam(':estimated_hours', $estimatedHours);
                $stmt->bindParam(':due_date', $dueDate);
                
                if ($stmt->execute()) {
                    $response['success'] = true;
                    $response['message'] = 'Task created successfully';
                    $response['task_id'] = $conn->lastInsertId();
                } else {
                    $response['message'] = 'Error creating task';
                }
            } else {
                $response['message'] = 'Title and project ID are required';
            }
        } else {
            $response['message'] = 'Invalid action or missing parameters';
        }
    } else {
        $response['message'] = 'Action is required';
    }
    
    // Return the JSON response
    echo json_encode($response);
    exit;
}

// For GET requests, display the task board UI
try {
    // Get project ID from URL if provided
    $project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : null;
    
    // Get all projects for the dropdown filter
    $projectsQuery = "SELECT id, projectName FROM projects ORDER BY projectName";
    $projectsStmt = $conn->query($projectsQuery);
    $projects = $projectsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Filter tasks by project if a project is selected
    $tasksQuery = "SELECT * FROM tasks";
    if ($project_id) {
        $tasksQuery .= " WHERE project_id = :project_id";
        $tasksStmt = $conn->prepare($tasksQuery);
        $tasksStmt->bindParam(':project_id', $project_id);
        $tasksStmt->execute();
    } else {
        $tasksStmt = $conn->query($tasksQuery);
    }
    
    $tasks = $tasksStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Group tasks by status
    $tasksByStatus = [
        'To Do' => [],
        'In Progress' => [],
        'Review' => [],
        'Done' => []
    ];
    
    foreach ($tasks as $task) {
        $tasksByStatus[$task['status']][] = $task;
    }
    
} catch(PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Handle form submission for task creation or status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'create_task') {
        try {
            $title = $_POST['title'];
            $description = $_POST['description'];
            $projectId = $_POST['project_id'];
            $status = $_POST['status'];
            $priority = $_POST['priority'];
            $assignedTo = $_POST['assigned_to'];
            $dueDate = $_POST['due_date'];
            $estimatedHours = $_POST['estimated_hours'];
            
            $insertQuery = "INSERT INTO tasks (project_id, title, description, status, priority, assigned_to, created_by, estimated_hours, due_date)
                          VALUES (:project_id, :title, :description, :status, :priority, :assigned_to, 'Current User', :estimated_hours, :due_date)";
            
            $stmt = $conn->prepare($insertQuery);
            $stmt->bindParam(':project_id', $projectId);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':priority', $priority);
            $stmt->bindParam(':assigned_to', $assignedTo);
            $stmt->bindParam(':estimated_hours', $estimatedHours);
            $stmt->bindParam(':due_date', $dueDate);
            
            $stmt->execute();
            
            // Redirect to avoid form resubmission
            header("Location: tasks.php" . ($project_id ? "?project_id=$project_id" : ""));
            exit;
        } catch(PDOException $e) {
            $error = "Error creating task: " . $e->getMessage();
        }
    } else if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        try {
            $taskId = $_POST['task_id'];
            $newStatus = $_POST['new_status'];
            
            $updateQuery = "UPDATE tasks SET status = :status WHERE id = :id";
            $stmt = $conn->prepare($updateQuery);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':id', $taskId);
            
            $stmt->execute();
            
            // Redirect for regular form submissions
            header("Location: tasks.php" . ($project_id ? "?project_id=$project_id" : ""));
            exit;
        } catch(PDOException $e) {
            $error = "Error updating task: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management - Project Management</title>
    <link rel="stylesheet" href="style1.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <style>
        .task-board {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            padding-bottom: 20px;
            margin-top: 20px;
        }
        
        .task-column {
            min-width: 300px;
            width: 300px;
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 15px;
        }
        
        .column-header {
            font-weight: 600;
            padding-bottom: 10px;
            margin-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .task-count {
            background-color: #e0e0e0;
            border-radius: 10px;
            padding: 2px 8px;
            font-size: 0.8em;
        }
        
        .task-card {
            background: white;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid #4361ee;
            transition: transform 0.2s, box-shadow 0.2s;
            cursor: pointer;
            position: relative;
        }
        
        .task-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .task-card.priority-low {
            border-left-color: #4CAF50;
        }
        
        .task-card.priority-medium {
            border-left-color: #2196F3;
        }
        
        .task-card.priority-high {
            border-left-color: #FF9800;
        }
        
        .task-card.priority-critical {
            border-left-color: #F44336;
        }
        
        .task-title {
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .task-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85em;
            color: #666;
            margin-top: 10px;
        }
        
        .task-assignee {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .avatar {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background-color: #4361ee;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.8em;
        }
        
        .task-priority {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 0.8em;
            font-weight: 500;
        }
        
        .priority-low {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .priority-medium {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }
        
        .priority-high {
            background-color: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }
        
        .priority-critical {
            background-color: rgba(244, 67, 54, 0.1);
            color: #F44336;
        }
        
        .task-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: space-between;
        }
        
        .action-button {
            border: none;
            background: none;
            font-size: 0.9em;
            color: #666;
            cursor: pointer;
            padding: 5px;
            transition: color 0.2s;
        }
        
        .action-button:hover {
            color: #4361ee;
        }
        
        .task-description {
            font-size: 0.9em;
            color: #666;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .filters {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .filter-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            min-width: 200px;
        }
        
        .filter-label {
            font-size: 0.9em;
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .filter-control {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 24px;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background-color: white;
            margin: 50px auto;
            padding: 30px;
            border-radius: 8px;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-title {
            margin-top: 0;
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: inherit;
            font-size: 0.9em;
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        .form-row {
            display: flex;
            gap: 20px;
        }
        
        .form-row .form-group {
            flex: 1;
        }
        
        .close {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
        }
        
        .task-due-date {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        
        .task-estimated-time {
            font-size: 0.85em;
            color: #666;
            margin-top: 5px;
        }
        
        .actions-menu {
            position: absolute;
            top: 10px;
            right: 10px;
            display: none;
            background: white;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 10;
            overflow: hidden;
        }
        
        .actions-menu-button {
            background: none;
            border: none;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            color: #666;
        }
        
        .actions-menu-button:hover {
            background-color: #f0f0f0;
            color: #4361ee;
        }
        
        .actions-menu-items {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .actions-menu-items li {
            padding: 0;
        }
        
        .actions-menu-items a {
            display: block;
            padding: 10px 15px;
            color: #333;
            text-decoration: none;
            white-space: nowrap;
            transition: background-color 0.2s;
        }
        
        .actions-menu-items a:hover {
            background-color: #f0f0f0;
        }
        
        .actions-menu-items a.delete-action {
            color: #f44336;
        }
        
        .actions-menu-items a.delete-action:hover {
            background-color: rgba(244, 67, 54, 0.1);
        }
        
        .task-card:hover .actions-menu {
            display: block;
        }
        
        /* Chatbot Styles */
        .chatbot-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .chatbot-button {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: #4361ee;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            font-size: 24px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .chatbot-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.25);
        }
        
        .chatbot-box {
            position: absolute;
            bottom: 70px;
            right: 0;
            width: 350px;
            height: 500px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            display: none;
        }
        
        .chatbot-header {
            background-color: #4361ee;
            color: white;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .chatbot-header h3 {
            margin: 0;
            font-size: 16px;
        }
        
        .chatbot-close {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
        }
        
        .chatbot-messages {
            flex: 1;
            padding: 15px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .message {
            display: flex;
            margin-bottom: 10px;
        }
        
        .bot-message {
            justify-content: flex-start;
        }
        
        .user-message {
            justify-content: flex-end;
        }
        
        .message-content {
            padding: 10px 14px;
            border-radius: 18px;
            max-width: 80%;
            word-break: break-word;
        }
        
        .bot-message .message-content {
            background-color: #f0f2f5;
            color: #333;
            border-bottom-left-radius: 4px;
        }
        
        .user-message .message-content {
            background-color: #4361ee;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .chatbot-input {
            padding: 10px 15px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chatbot-project-select {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
            outline: none;
        }
        
        .chatbot-send {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #4361ee;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
        }
        
        .suggestion-task {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 3px solid #4361ee;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .suggestion-task:hover {
            background-color: #e9ecef;
        }
        
        .add-task-btn {
            background-color: #4361ee;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-top: 5px;
            cursor: pointer;
            font-size: 12px;
        }
        
        .add-task-btn:hover {
            background-color: #3a56d4;
        }
    </style>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<body>
    <header>
        <div class="container header-container">
            <a href="project.html" class="logo">
                <img src="logo.png" alt="Project Logo" style="height: 35px; margin-right: 10px;">
            </a>
            <nav class="main-nav">
                <a href="project.html">Projects</a>
                <a href="tasks.php" class="active">Tasks</a>
                <a href="calendar.php">Calendar</a>
                <a href="#">Reports</a>
            </nav>
            <div class="auth-buttons">
                <a href="login.html" class="btn btn-outline">Log In</a>
                <a href="signup.html" class="btn btn-primary">Sign Up</a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center; margin: 24px 0;">
            <h1>Task Management</h1>
            <button class="btn btn-primary" onclick="openModal('createTaskModal')"><i class="fas fa-plus"></i> New Task</button>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <form action="tasks.php" method="GET">
                <div class="filter-row">
                    <div class="filter-group">
                        <label class="filter-label" for="project_filter">Project</label>
                        <select class="filter-control" id="project_filter" name="project_id" onchange="this.form.submit()">
                            <option value="">All Projects</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>" <?php echo $project_id == $project['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($project['projectName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="priority_filter">Priority</label>
                        <select class="filter-control" id="priority_filter">
                            <option value="">All Priorities</option>
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label" for="assigned_filter">Assigned To</label>
                        <input type="text" class="filter-control" id="assigned_filter" placeholder="Search by name">
                    </div>
                </div>
            </form>
        </div>

        <div class="task-board">
            <!-- To Do Column -->
            <div class="task-column" id="todo-column">
                <div class="column-header">
                    <span>To Do</span>
                    <span class="task-count"><?php echo count($tasksByStatus['To Do']); ?></span>
                </div>
                
                <?php foreach ($tasksByStatus['To Do'] as $task): ?>
                    <div class="task-card priority-<?php echo strtolower($task['priority']); ?>" data-task-id="<?php echo $task['id']; ?>" onclick="openTaskDetails(<?php echo $task['id']; ?>)">
                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                        
                        <div class="task-meta">
                            <span class="task-priority priority-<?php echo strtolower($task['priority']); ?>"><?php echo $task['priority']; ?></span>
                            
                            <?php if ($task['assigned_to']): ?>
                                <div class="task-assignee">
                                    <div class="avatar"><?php echo substr($task['assigned_to'], 0, 1); ?></div>
                                    <span><?php echo htmlspecialchars($task['assigned_to']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($task['due_date']): ?>
                            <div class="task-due-date">
                                <i class="fas fa-calendar-alt"></i> Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="task-actions">
                            <button class="action-button move-task" onclick="moveTask(event, <?php echo $task['id']; ?>, 'In Progress')">
                                <i class="fas fa-arrow-right"></i> Start
                            </button>
                            
                            <button class="actions-menu-button" onclick="toggleActionsMenu(event, <?php echo $task['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="actions-menu" id="actions-menu-<?php echo $task['id']; ?>">
                                <ul class="actions-menu-items">
                                    <li><a href="#" onclick="openEditTaskModal(event, <?php echo $task['id']; ?>)"><i class="fas fa-edit"></i> Edit Task</a></li>
                                    <li><a href="#" class="delete-action" onclick="confirmDeleteTask(event, <?php echo $task['id']; ?>)"><i class="fas fa-trash"></i> Delete Task</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- In Progress Column -->
            <div class="task-column" id="in-progress-column">
                <div class="column-header">
                    <span>In Progress</span>
                    <span class="task-count"><?php echo count($tasksByStatus['In Progress']); ?></span>
                </div>
                
                <?php foreach ($tasksByStatus['In Progress'] as $task): ?>
                    <div class="task-card priority-<?php echo strtolower($task['priority']); ?>" data-task-id="<?php echo $task['id']; ?>" onclick="openTaskDetails(<?php echo $task['id']; ?>)">
                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                        
                        <div class="task-meta">
                            <span class="task-priority priority-<?php echo strtolower($task['priority']); ?>"><?php echo $task['priority']; ?></span>
                            
                            <?php if ($task['assigned_to']): ?>
                                <div class="task-assignee">
                                    <div class="avatar"><?php echo substr($task['assigned_to'], 0, 1); ?></div>
                                    <span><?php echo htmlspecialchars($task['assigned_to']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($task['due_date']): ?>
                            <div class="task-due-date">
                                <i class="fas fa-calendar-alt"></i> Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="task-actions">
                            <button class="action-button move-task" onclick="moveTask(event, <?php echo $task['id']; ?>, 'Review')">
                                <i class="fas fa-check"></i> Ready for Review
                            </button>
                            
                            <button class="actions-menu-button" onclick="toggleActionsMenu(event, <?php echo $task['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="actions-menu" id="actions-menu-<?php echo $task['id']; ?>">
                                <ul class="actions-menu-items">
                                    <li><a href="#" onclick="openEditTaskModal(event, <?php echo $task['id']; ?>)"><i class="fas fa-edit"></i> Edit Task</a></li>
                                    <li><a href="#" class="delete-action" onclick="confirmDeleteTask(event, <?php echo $task['id']; ?>)"><i class="fas fa-trash"></i> Delete Task</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Review Column -->
            <div class="task-column" id="review-column">
                <div class="column-header">
                    <span>Review</span>
                    <span class="task-count"><?php echo count($tasksByStatus['Review']); ?></span>
                </div>
                
                <?php foreach ($tasksByStatus['Review'] as $task): ?>
                    <div class="task-card priority-<?php echo strtolower($task['priority']); ?>" data-task-id="<?php echo $task['id']; ?>" onclick="openTaskDetails(<?php echo $task['id']; ?>)">
                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                        
                        <div class="task-meta">
                            <span class="task-priority priority-<?php echo strtolower($task['priority']); ?>"><?php echo $task['priority']; ?></span>
                            
                            <?php if ($task['assigned_to']): ?>
                                <div class="task-assignee">
                                    <div class="avatar"><?php echo substr($task['assigned_to'], 0, 1); ?></div>
                                    <span><?php echo htmlspecialchars($task['assigned_to']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($task['due_date']): ?>
                            <div class="task-due-date">
                                <i class="fas fa-calendar-alt"></i> Due: <?php echo date('M j, Y', strtotime($task['due_date'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="task-actions">
                            <button class="action-button move-task" onclick="moveTask(event, <?php echo $task['id']; ?>, 'Done')">
                                <i class="fas fa-check-double"></i> Complete
                            </button>
                            
                            <button class="actions-menu-button" onclick="toggleActionsMenu(event, <?php echo $task['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="actions-menu" id="actions-menu-<?php echo $task['id']; ?>">
                                <ul class="actions-menu-items">
                                    <li><a href="#" onclick="openEditTaskModal(event, <?php echo $task['id']; ?>)"><i class="fas fa-edit"></i> Edit Task</a></li>
                                    <li><a href="#" class="delete-action" onclick="confirmDeleteTask(event, <?php echo $task['id']; ?>)"><i class="fas fa-trash"></i> Delete Task</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Done Column -->
            <div class="task-column" id="done-column">
                <div class="column-header">
                    <span>Done</span>
                    <span class="task-count"><?php echo count($tasksByStatus['Done']); ?></span>
                </div>
                
                <?php foreach ($tasksByStatus['Done'] as $task): ?>
                    <div class="task-card priority-<?php echo strtolower($task['priority']); ?>" data-task-id="<?php echo $task['id']; ?>" onclick="openTaskDetails(<?php echo $task['id']; ?>)">
                        <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
                        <div class="task-description"><?php echo htmlspecialchars($task['description']); ?></div>
                        
                        <div class="task-meta">
                            <span class="task-priority priority-<?php echo strtolower($task['priority']); ?>"><?php echo $task['priority']; ?></span>
                            
                            <?php if ($task['assigned_to']): ?>
                                <div class="task-assignee">
                                    <div class="avatar"><?php echo substr($task['assigned_to'], 0, 1); ?></div>
                                    <span><?php echo htmlspecialchars($task['assigned_to']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($task['due_date']): ?>
                            <div class="task-due-date">
                                <i class="fas fa-calendar-alt"></i> Completed: <?php echo date('M j, Y', strtotime($task['updated_at'])); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="task-actions">
                            <button class="actions-menu-button" onclick="toggleActionsMenu(event, <?php echo $task['id']; ?>)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="actions-menu" id="actions-menu-<?php echo $task['id']; ?>">
                                <ul class="actions-menu-items">
                                    <li><a href="#" onclick="openEditTaskModal(event, <?php echo $task['id']; ?>)"><i class="fas fa-edit"></i> Edit Task</a></li>
                                    <li><a href="#" class="delete-action" onclick="confirmDeleteTask(event, <?php echo $task['id']; ?>)"><i class="fas fa-trash"></i> Delete Task</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

     

        <!-- Task Suggestion Chatbot -->
        <div class="chatbot-container">
            <div class="chatbot-button" id="chatbot-toggle">
                <i class="fas fa-robot"></i>
            </div>
            
            <div class="chatbot-box" id="chatbot-box">
                <div class="chatbot-header">
                    <h3>Task Assistant</h3>
                    <button class="chatbot-close" id="chatbot-close">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                
                <div class="chatbot-messages" id="chatbot-messages">
                    <div class="message bot-message">
                        <div class="message-content">
                            Hello! I can help you with task suggestions for your projects. Select a project to get started.
                        </div>
                    </div>
                </div>
                
                <div class="chatbot-input">
                    <select id="chatbot-project-select" class="chatbot-project-select">
                        <option value="">Select a project</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>"><?php echo htmlspecialchars($project['projectName']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button id="chatbot-ask" class="chatbot-send">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Create Task Modal -->
    <div id="createTaskModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('createTaskModal')">&times;</span>
            <h2 class="modal-title">Create New Task</h2>
            
            <form action="tasks.php" method="POST">
                <input type="hidden" name="action" value="create_task">
                
                <div class="form-group">
                    <label class="form-label" for="title">Task Title*</label>
                    <input type="text" class="form-control" id="title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="project_id">Project*</label>
                        <select class="form-control" id="project_id" name="project_id" required>
                            <option value="">Select a project</option>
                            <?php foreach ($projects as $project): ?>
                                <option value="<?php echo $project['id']; ?>" <?php echo $project_id == $project['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($project['projectName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-control" id="status" name="status">
                            <option value="To Do">To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Review">Review</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="priority">Priority</label>
                        <select class="form-control" id="priority" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium" selected>Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="assigned_to">Assigned To</label>
                        <input type="text" class="form-control" id="assigned_to" name="assigned_to">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="due_date">Due Date</label>
                        <input type="date" class="form-control" id="due_date" name="due_date">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="estimated_hours">Estimated Hours</label>
                        <input type="number" step="0.5" min="0" class="form-control" id="estimated_hours" name="estimated_hours">
                    </div>
                </div>
                
                <div class="button-group">
                    <button type="submit" class="btn btn-primary">Create Task</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('createTaskModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Add Edit Task Modal -->
    <div id="editTaskModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editTaskModal')">&times;</span>
            <h2 class="modal-title">Edit Task</h2>
            
            <form id="editTaskForm">
                <input type="hidden" id="edit_task_id" name="task_id">
                
                <div class="form-group">
                    <label class="form-label" for="edit_title">Task Title*</label>
                    <input type="text" class="form-control" id="edit_title" name="title" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="edit_description">Description</label>
                    <textarea class="form-control" id="edit_description" name="description"></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_status">Status</label>
                        <select class="form-control" id="edit_status" name="status">
                            <option value="To Do">To Do</option>
                            <option value="In Progress">In Progress</option>
                            <option value="Review">Review</option>
                            <option value="Done">Done</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="edit_priority">Priority</label>
                        <select class="form-control" id="edit_priority" name="priority">
                            <option value="Low">Low</option>
                            <option value="Medium">Medium</option>
                            <option value="High">High</option>
                            <option value="Critical">Critical</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="edit_assigned_to">Assigned To</label>
                        <input type="text" class="form-control" id="edit_assigned_to" name="assigned_to">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="edit_due_date">Due Date</label>
                        <input type="date" class="form-control" id="edit_due_date" name="due_date">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="edit_estimated_hours">Estimated Hours</label>
                    <input type="number" step="0.5" min="0" class="form-control" id="edit_estimated_hours" name="estimated_hours">
                </div>
                
                <div class="button-group">
                    <button type="button" class="btn btn-primary" onclick="updateTask()">Save Changes</button>
                    <button type="button" class="btn btn-outline" onclick="closeModal('editTaskModal')">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Modal functionality
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
        
        // Task status update
        function moveTask(event, taskId, newStatus) {
            event.stopPropagation(); // Prevent task details from opening
            
            // Send AJAX request to update task status
            fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=update_status&task_id=' + taskId + '&new_status=' + encodeURIComponent(newStatus)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Move the task card to the appropriate column
                    const taskCard = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
                    const targetColumn = document.getElementById(newStatus.toLowerCase().replace(' ', '-') + '-column');
                    
                    if (taskCard && targetColumn) {
                        taskCard.remove(); // Remove from current column
                        
                        // Update the action button based on the new status
                        if (newStatus === 'In Progress') {
                            taskCard.querySelector('.task-actions').innerHTML = `
                                <button class="action-button move-task" onclick="moveTask(event, ${taskId}, 'Review')">
                                    <i class="fas fa-check"></i> Ready for Review
                                </button>
                            `;
                        } else if (newStatus === 'Review') {
                            taskCard.querySelector('.task-actions').innerHTML = `
                                <button class="action-button move-task" onclick="moveTask(event, ${taskId}, 'Done')">
                                    <i class="fas fa-check-double"></i> Complete
                                </button>
                            `;
                        } else if (newStatus === 'Done') {
                            taskCard.querySelector('.task-actions').innerHTML = '';
                        }
                        
                        // Add to target column
                        targetColumn.appendChild(taskCard);
                        
                        // Update task counts
                        updateTaskCounts();
                    }
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Update task counts in column headers
        function updateTaskCounts() {
            const columns = ['todo', 'in-progress', 'review', 'done'];
            
            columns.forEach(column => {
                const columnElement = document.getElementById(`${column}-column`);
                const taskCount = columnElement.querySelectorAll('.task-card').length;
                columnElement.querySelector('.task-count').textContent = taskCount;
            });
        }
        
        // Open task details
        function openTaskDetails(taskId) {
            // In a real application, this would open a modal with task details
            console.log('Opening task details for task ID:', taskId);
        }
        
        // Filter functionality
        document.getElementById('priority_filter').addEventListener('change', function() {
            const priority = this.value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');
            
            taskCards.forEach(card => {
                if (!priority || card.classList.contains(`priority-${priority}`)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            updateTaskCounts();
        });
        
        document.getElementById('assigned_filter').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const taskCards = document.querySelectorAll('.task-card');
            
            taskCards.forEach(card => {
                const assignee = card.querySelector('.task-assignee');
                if (!searchTerm || (assignee && assignee.textContent.toLowerCase().includes(searchTerm))) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
            
            updateTaskCounts();
        });
        
        // Toggle actions menu
        function toggleActionsMenu(event, taskId) {
            event.stopPropagation(); // Prevent task details from opening
            
            const menu = document.getElementById(`actions-menu-${taskId}`);
            
            // Close all other menus
            document.querySelectorAll('.actions-menu').forEach(m => {
                if (m.id !== `actions-menu-${taskId}`) {
                    m.style.display = 'none';
                }
            });
            
            // Toggle this menu
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
        
        // Close all action menus when clicking elsewhere on the page
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.actions-menu') && !event.target.closest('.actions-menu-button')) {
                document.querySelectorAll('.actions-menu').forEach(menu => {
                    menu.style.display = 'none';
                });
            }
        });
        
        // Open edit task modal
        function openEditTaskModal(event, taskId) {
            event.stopPropagation(); // Prevent task details from opening
            
            // Get task data
            const taskCard = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
            const taskTitle = taskCard.querySelector('.task-title').textContent;
            const taskDescription = taskCard.querySelector('.task-description').textContent;
            const taskPriority = taskCard.querySelector('.task-priority').textContent;
            
            // Get status based on which column the task is in
            let taskStatus;
            if (taskCard.closest('#todo-column')) {
                taskStatus = 'To Do';
            } else if (taskCard.closest('#in-progress-column')) {
                taskStatus = 'In Progress';
            } else if (taskCard.closest('#review-column')) {
                taskStatus = 'Review';
            } else if (taskCard.closest('#done-column')) {
                taskStatus = 'Done';
            }
            
            // Set form values
            document.getElementById('edit_task_id').value = taskId;
            document.getElementById('edit_title').value = taskTitle;
            document.getElementById('edit_description').value = taskDescription;
            document.getElementById('edit_status').value = taskStatus;
            document.getElementById('edit_priority').value = taskPriority;
            
            // For the optional fields, we need to get them from the task card if they exist
            const assigneeElement = taskCard.querySelector('.task-assignee span');
            if (assigneeElement) {
                document.getElementById('edit_assigned_to').value = assigneeElement.textContent;
            }
            
            const dueDateElement = taskCard.querySelector('.task-due-date');
            if (dueDateElement) {
                // Try to extract the date in YYYY-MM-DD format
                const dateText = dueDateElement.textContent;
                const dateMatch = dateText.match(/(\w{3} \d{1,2}, \d{4})/);
                
                if (dateMatch) {
                    // Convert from "Mon D, YYYY" to "YYYY-MM-DD"
                    const parsedDate = new Date(dateMatch[1]);
                    const formattedDate = parsedDate.toISOString().split('T')[0];
                    document.getElementById('edit_due_date').value = formattedDate;
                }
            }
            
            // Get estimated hours (this may be more complex to extract from UI)
            
            // Open the modal
            openModal('editTaskModal');
            
            // Close the actions menu
            document.getElementById(`actions-menu-${taskId}`).style.display = 'none';
        }
        
        // Update task 
        function updateTask() {
            const form = document.getElementById('editTaskForm');
            const formData = new FormData(form);
            
            // Add action parameter
            formData.append('action', 'update_task');
            
            // Create URL-encoded string of parameters
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                params.append(key, value);
            }
            
            // Send AJAX request to update task
            fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params.toString()
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Close the modal
                    closeModal('editTaskModal');
                    
                    // Reload the page to see changes
                    // In a more advanced implementation, you would update the UI without reloading
                    window.location.reload();
                } else {
                    alert('Error updating task: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
        
        // Confirm delete task
        function confirmDeleteTask(event, taskId) {
            event.stopPropagation(); // Prevent task details from opening
            
            // Close the actions menu
            document.getElementById(`actions-menu-${taskId}`).style.display = 'none';
            
            // Show confirmation dialog
            if (confirm('Are you sure you want to delete this task? This action cannot be undone.')) {
                deleteTask(taskId);
            }
        }
        
        // Delete task
        function deleteTask(taskId) {
            // Send AJAX request to delete task
            fetch('tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'action=delete_task&task_id=' + taskId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Remove the task card from the UI
                    const taskCard = document.querySelector(`.task-card[data-task-id="${taskId}"]`);
                    if (taskCard) {
                        taskCard.remove();
                        
                        // Update task counts
                        updateTaskCounts();
                    }
                } else {
                    alert('Error deleting task: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Map initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the map if the container exists
            const mapContainer = document.getElementById('map-container');
            if (mapContainer) {
                initMap();
            }
            
            // Add tab switching functionality for map/list views
            const viewTabs = document.querySelectorAll('[data-view]');
            viewTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    viewTabs.forEach(t => t.classList.remove('active'));
                    this.classList.add('active');
                    
                    const viewType = this.dataset.view;
                    if (viewType === 'map') {
                        document.getElementById('map-container').style.display = 'block';
                        document.getElementById('location-list').style.display = 'none';
                    } else {
                        document.getElementById('map-container').style.display = 'none';
                        document.getElementById('location-list').style.display = 'block';
                    }
                });
            });
            
            // Initialize Chatbot Functionality
            initChatbot();
        });

        // Initialize Leaflet map
        function initMap() {
            const map = L.map('map-container').setView([48.8566, 2.3522], 5);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);
            
            loadContributorLocations(map);
        }

        // Initialize chatbot functionality
        function initChatbot() {
            const chatbotToggle = document.getElementById('chatbot-toggle');
            const chatbotBox = document.getElementById('chatbot-box');
            const chatbotClose = document.getElementById('chatbot-close');
            const chatbotMessages = document.getElementById('chatbot-messages');
            const chatbotProjectSelect = document.getElementById('chatbot-project-select');
            const chatbotAskButton = document.getElementById('chatbot-ask');
            
            // Toggle chatbot display when clicking the button
            chatbotToggle.addEventListener('click', function() {
                chatbotBox.style.display = chatbotBox.style.display === 'none' || chatbotBox.style.display === '' ? 'flex' : 'none';
            });
            
            // Close chatbot when clicking the close button
            chatbotClose.addEventListener('click', function() {
                chatbotBox.style.display = 'none';
            });
            
            // Handle project selection and generating suggestions
            chatbotAskButton.addEventListener('click', function() {
                const selectedProjectId = chatbotProjectSelect.value;
                const selectedProjectName = chatbotProjectSelect.options[chatbotProjectSelect.selectedIndex].text;
                
                if (!selectedProjectId) {
                    addBotMessage('Please select a project first.');
                    return;
                }
                
                // Add user message
                addUserMessage(`I need task suggestions for project: ${selectedProjectName}`);
                
                // Simulate bot thinking with typing indicator
                addBotMessage('Thinking of tasks for ' + selectedProjectName + '...', true);
                
                // Get project type from name (for demo purposes)
                const projectType = getProjectType(selectedProjectName);
                
                // Simulate API call with timeout
                setTimeout(() => {
                    // Remove the typing indicator
                    const typingIndicator = chatbotMessages.querySelector('.typing-indicator');
                    if (typingIndicator) {
                        chatbotMessages.removeChild(typingIndicator.parentNode);
                    }
                    
                    // Generate and display task suggestions
                    const suggestions = generateTaskSuggestions(projectType);
                    addSuggestions(suggestions, selectedProjectId);
                }, 1000);
            });
            
            // Helper function to add bot messages
            function addBotMessage(text, isTyping = false) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message bot-message';
                
                const contentDiv = document.createElement('div');
                contentDiv.className = isTyping ? 'message-content typing-indicator' : 'message-content';
                contentDiv.textContent = text;
                
                messageDiv.appendChild(contentDiv);
                chatbotMessages.appendChild(messageDiv);
                
                // Scroll to bottom
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
            
            // Helper function to add user messages
            function addUserMessage(text) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message user-message';
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                contentDiv.textContent = text;
                
                messageDiv.appendChild(contentDiv);
                chatbotMessages.appendChild(messageDiv);
                
                // Scroll to bottom
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
            }
            
            // Helper function to add task suggestions
            function addSuggestions(suggestions, projectId) {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message bot-message';
                
                const contentDiv = document.createElement('div');
                contentDiv.className = 'message-content';
                contentDiv.innerHTML = `<p>Here are some task suggestions for this project:</p>`;
                
                suggestions.forEach(suggestion => {
                    const suggestionDiv = document.createElement('div');
                    suggestionDiv.className = 'suggestion-task';
                    suggestionDiv.innerHTML = `
                        <div><strong>${suggestion.title}</strong></div>
                        <div style="font-size: 0.9em; color: #666;">${suggestion.description}</div>
                        <button class="add-task-btn" data-title="${suggestion.title}" data-description="${suggestion.description}" data-priority="${suggestion.priority}" data-project-id="${projectId}">
                            <i class="fas fa-plus"></i> Add this task
                        </button>
                    `;
                    contentDiv.appendChild(suggestionDiv);
                });
                
                messageDiv.appendChild(contentDiv);
                chatbotMessages.appendChild(messageDiv);
                
                // Scroll to bottom
                chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
                
                // Add event listeners to the add task buttons
                const addTaskButtons = chatbotMessages.querySelectorAll('.add-task-btn');
                addTaskButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const taskTitle = this.getAttribute('data-title');
                        const taskDescription = this.getAttribute('data-description');
                        const taskPriority = this.getAttribute('data-priority');
                        const projectId = this.getAttribute('data-project-id');
                        
                        createTaskFromSuggestion(taskTitle, taskDescription, taskPriority, projectId);
                    });
                });
            }
            
            // Create a task from suggestion
            function createTaskFromSuggestion(title, description, priority, projectId) {
                // Prepare form data
                const formData = new FormData();
                formData.append('action', 'create_task');
                formData.append('title', title);
                formData.append('description', description);
                formData.append('project_id', projectId);
                formData.append('priority', priority);
                formData.append('status', 'To Do');
                
                // Build URL parameters
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    params.append(key, value);
                }
                
                // Send AJAX request to create task
                fetch('tasks.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: params.toString()
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        addBotMessage(`✅ Task "${title}" has been created! Refresh the page to see it.`);
                    } else {
                        addBotMessage(`❌ Error creating task: ${data.message}`);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    addBotMessage('❌ There was an error creating the task. Please try again.');
                });
            }
            
            // Determine project type from name (for demo purposes)
            function getProjectType(projectName) {
                const lowerName = projectName.toLowerCase();
                
                if (lowerName.includes('urban') || lowerName.includes('city') || lowerName.includes('plan')) {
                    return 'urban';
                } else if (lowerName.includes('tech') || lowerName.includes('software') || lowerName.includes('app')) {
                    return 'tech';
                } else if (lowerName.includes('environment') || lowerName.includes('green') || lowerName.includes('sustain')) {
                    return 'environment';
                } else if (lowerName.includes('art') || lowerName.includes('culture') || lowerName.includes('heritage')) {
                    return 'culture';
                } else {
                    return 'general';
                }
            }
            
            // Generate task suggestions based on project type
            function generateTaskSuggestions(projectType) {
                const suggestions = {
                    urban: [
                        { title: 'Conduct traffic analysis', description: 'Analyze current traffic patterns at main intersections', priority: 'High' },
                        { title: 'Create urban space design mockups', description: 'Design initial mockups for public spaces', priority: 'Medium' },
                        { title: 'Research zoning regulations', description: 'Compile applicable zoning regulations and requirements', priority: 'High' },
                        { title: 'Stakeholder consultation planning', description: 'Schedule and plan community consultation sessions', priority: 'Medium' },
                        { title: 'Budget estimation', description: 'Create initial budget estimates for implementation', priority: 'High' }
                    ],
                    tech: [
                        { title: 'Define technical requirements', description: 'Document detailed technical requirements and specifications', priority: 'Critical' },
                        { title: 'Create system architecture', description: 'Design the overall system architecture diagram', priority: 'High' },
                        { title: 'Develop UI wireframes', description: 'Create wireframes for all main user interfaces', priority: 'Medium' },
                        { title: 'Set up development environment', description: 'Configure and document the development environment setup', priority: 'High' },
                        { title: 'Create testing strategy', description: 'Define the testing approach and quality metrics', priority: 'Medium' }
                    ],
                    environment: [
                        { title: 'Conduct environmental impact assessment', description: 'Assess potential environmental impacts of the project', priority: 'Critical' },
                        { title: 'Research sustainable materials', description: 'Identify and evaluate sustainable material options', priority: 'High' },
                        { title: 'Create waste management plan', description: 'Develop a comprehensive waste management strategy', priority: 'Medium' },
                        { title: 'Schedule community garden planning', description: 'Plan layout and plant selection for community gardens', priority: 'Medium' },
                        { title: 'Develop renewable energy options', description: 'Research and propose renewable energy solutions', priority: 'High' }
                    ],
                    culture: [
                        { title: 'Document historical significance', description: 'Research and document the historical context', priority: 'High' },
                        { title: 'Plan exhibition space', description: 'Design layout and flow for exhibition spaces', priority: 'Medium' },
                        { title: 'Develop educational materials', description: 'Create educational content for visitors', priority: 'Medium' },
                        { title: 'Coordinate with cultural experts', description: 'Identify and establish contact with relevant cultural experts', priority: 'High' },
                        { title: 'Plan opening ceremony', description: 'Develop program for the opening celebration', priority: 'Low' }
                    ],
                    general: [
                        { title: 'Create project timeline', description: 'Develop detailed project timeline with milestones', priority: 'High' },
                        { title: 'Define team roles', description: 'Clearly define roles and responsibilities for team members', priority: 'Medium' },
                        { title: 'Set up communication plan', description: 'Establish communication channels and protocols', priority: 'Medium' },
                        { title: 'Create risk assessment', description: 'Identify potential risks and mitigation strategies', priority: 'High' },
                        { title: 'Schedule kick-off meeting', description: 'Plan and organize project kick-off meeting', priority: 'Low' }
                    ]
                };
                
                return suggestions[projectType] || suggestions.general;
            }
        }

        // Load contributor locations from the server
        function loadContributorLocations(map) {
            // Send AJAX request to get contributor data with locations
            fetch('get_contributor_locations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Changed from data.contributors to data.data to match PHP response
                    displayContributorsOnMap(map, data.data);
                    displayContributorsInList(data.data);
                } else {
                    console.error('Error loading contributor locations:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Display markers on the map for each contributor
        function displayContributorsOnMap(map, contributors) {
            if (!contributors || contributors.length === 0) {
                // Add demo data if no contributors are found
                addDemoContributorsToMap(map);
                return;
            }
            
            // Add a marker for each contributor with location data
            contributors.forEach(contributor => {
                if (contributor.latitude && contributor.longitude) {
                    // Create marker with popup showing contributor info
                    L.marker([contributor.latitude, contributor.longitude])
                        .addTo(map)
                        .bindPopup(`
                            <strong>${contributor.first_name} ${contributor.last_name}</strong><br>
                            <em>${contributor.city}</em><br>
                            <span>Role: ${contributor.contribution_type}</span>
                        `);
                }
            });
            
            // Adjust map view to show all markers if any
            // (This would require calculating bounds of all markers)
        }

        // Display contributors in the list view
        function displayContributorsInList(contributors) {
            const listContainer = document.getElementById('contributors-list');
            listContainer.innerHTML = ''; // Clear existing list
            
            if (!contributors || contributors.length === 0) {
                // Add demo data
                const demoContributors = [
                    { first_name: 'Marie', last_name: 'Dubois', city: 'Paris', contribution_type: 'Designer' },
                    { first_name: 'Jean', last_name: 'Martin', city: 'Lyon', contribution_type: 'Developer' },
                    { first_name: 'Sophie', last_name: 'Leroy', city: 'Marseille', contribution_type: 'Project Manager' },
                    { first_name: 'Ahmed', last_name: 'Benyahia', city: 'Tunis', contribution_type: 'Architect' },
                    { first_name: 'Carlos', last_name: 'Rodriguez', city: 'Barcelona', contribution_type: 'Urban Planner' }
                ];
                
                demoContributors.forEach(contributor => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${contributor.first_name} ${contributor.last_name}</td>
                        <td>${contributor.city}</td>
                        <td>${contributor.contribution_type}</td>
                    `;
                    listContainer.appendChild(row);
                });
                return;
            }
            
            // Add each contributor to the list
            contributors.forEach(contributor => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${contributor.first_name} ${contributor.last_name}</td>
                    <td>${contributor.city || 'Not specified'}</td>
                    <td>${contributor.contribution_type || 'General'}</td>
                `;
                listContainer.appendChild(row);
            });
        }

        // Add demo/placeholder data to the map when no real data exists
        function addDemoContributorsToMap(map) {
            // Demo data with European cities
            const demoLocations = [
                { name: 'Marie Dubois', city: 'Paris', role: 'Designer', lat: 48.8566, lng: 2.3522 },
                { name: 'Jean Martin', city: 'Lyon', role: 'Developer', lat: 45.7640, lng: 4.8357 },
                { name: 'Sophie Leroy', city: 'Marseille', role: 'Project Manager', lat: 43.2965, lng: 5.3698 },
                { name: 'Ahmed Benyahia', city: 'Tunis', role: 'Architect', lat: 36.8065, lng: 10.1815 },
                { name: 'Carlos Rodriguez', city: 'Barcelona', role: 'Urban Planner', lat: 41.3851, lng: 2.1734 }
            ];
            
            // Create marker cluster group if you have the plugin
            // const markers = L.markerClusterGroup();
            const bounds = L.latLngBounds();
            
            // Add markers for demo locations
            demoLocations.forEach(location => {
                const marker = L.marker([location.lat, location.lng])
                    .addTo(map)
                    .bindPopup(`
                        <strong>${location.name}</strong><br>
                        <em>${location.city}</em><br>
                        <span>Role: ${location.role}</span>
                    `);
                    
                // Add to bounds for auto-zooming
                bounds.extend([location.lat, location.lng]);
            });
            
            // Adjust map view to fit all markers
            if (bounds.isValid()) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    </script>
</body>
</html> 