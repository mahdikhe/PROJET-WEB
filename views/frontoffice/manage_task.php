<?php
require_once(__DIR__ . '/../../config/Database.php');

$pdo = Database::getInstance()->getConnection();
$taskId = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

if (!$taskId) {
    die('<div style="padding: 20px; background: #ffebee; color: #c62828; border-radius: 5px;">Invalid task ID.</div>');
}

// Handle new comment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_comment'])) {
    $author = isset($_POST['author']) ? trim($_POST['author']) : 'Anonymous';
    $comment = trim($_POST['new_comment']);

    if (!empty($comment)) {
        $stmt = $pdo->prepare("INSERT INTO task_comments (task_id, author, comment) VALUES (?, ?, ?)");
        $stmt->execute([$taskId, $author, $comment]);

        // Redirect to avoid duplicate submissions on refresh
        header("Location: manage_task.php?task_id=$taskId");
        exit;
    }
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['attachment'])) {
    $uploadDir = __DIR__ . '/../../uploads/tasks/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = basename($_FILES['attachment']['name']);
    $targetPath = $uploadDir . uniqid() . '_' . $filename;

    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("INSERT INTO task_attachments (task_id, filename, filepath) VALUES (?, ?, ?)");
        $stmt->execute([$taskId, $filename, $targetPath]);
    }

    // Redirect after upload
    header("Location: manage_task.php?task_id=$taskId");
    exit;
}

// Handle new subtask submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_subtask'])) {
    $subtask_title = trim($_POST['new_subtask']);
    if ($subtask_title !== '') {
        $stmt = $pdo->prepare("INSERT INTO subtasks (task_id, title, is_completed) VALUES (?, ?, 0)");
        $stmt->execute([$taskId, $subtask_title]);
    }
    header("Location: manage_task.php?task_id=$taskId");
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_subtask'])) {
    $subtask_id = intval($_POST['toggle_subtask']);
    $is_completed = isset($_POST['is_completed']) ? 1 : 0;
    $stmt = $pdo->prepare("UPDATE subtasks SET is_completed = ? WHERE id = ? AND task_id = ?");
    $stmt->execute([$is_completed, $subtask_id, $taskId]);
    header("Location: manage_task.php?task_id=$taskId");
    exit;
}

// Fetch task details
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$taskId]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch comments
$stmt = $pdo->prepare("SELECT * FROM task_comments WHERE task_id = ? ORDER BY created_at DESC");
$stmt->execute([$taskId]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch attachments
$stmt = $pdo->prepare("SELECT * FROM task_attachments WHERE task_id = ?");
$stmt->execute([$taskId]);
$attachments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subtasks
$subtasks = $pdo->prepare("SELECT * FROM subtasks WHERE task_id = ? ORDER BY id ASC");
$subtasks->execute([$taskId]);
$subtasks = $subtasks->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Task - <?php echo htmlspecialchars($task['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/css/style1.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; font-family: 'Inter', sans-serif; }
        .manage-task-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(67,97,238,0.08);
            padding: 40px 32px 32px 32px;
            animation: fadeInUp 0.7s cubic-bezier(.23,1.02,.32,1) both;
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .task-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1.5px solid #e9ecef;
            padding-bottom: 18px;
            margin-bottom: 28px;
        }
        .task-title {
            font-size: 2.1em;
            font-weight: 700;
            color: #3f37c9;
            letter-spacing: -1px;
        }
        .task-status {
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 1em;
            font-weight: 600;
            background: linear-gradient(90deg,#4361ee 60%,#3f37c9 100%);
            color: #fff;
            box-shadow: 0 2px 8px rgba(67,97,238,0.08);
            transition: background 0.3s;
        }
        .task-meta {
            display: flex;
            gap: 30px;
            margin: 18px 0 28px 0;
            font-size: 1.1em;
            color: #555;
        }
        .task-meta i { color: #4361ee; margin-right: 7px; }
        .task-desc {
            font-size: 1.15em;
            color: #444;
            margin-bottom: 30px;
            line-height: 1.7;
            background: #f6f8ff;
            border-radius: 10px;
            padding: 18px 22px;
            box-shadow: 0 2px 8px rgba(67,97,238,0.03);
            animation: fadeInUp 0.8s 0.1s both;
        }
        .progress-bar {
            width: 100%;
            height: 18px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .progress-bar-inner {
            height: 100%;
            background: linear-gradient(90deg,#4361ee,#3f37c9);
            width: 60%;
            border-radius: 10px;
            transition: width 0.7s cubic-bezier(.23,1.02,.32,1);
            box-shadow: 0 2px 8px rgba(67,97,238,0.12);
        }
        .section-title {
            font-size: 1.3em;
            font-weight: 600;
            color: #3f37c9;
            margin-bottom: 12px;
            margin-top: 30px;
        }
        .subtasks-list {
            margin-bottom: 20px;
        }
        .subtask-item {
            display: flex;
            align-items: center;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 10px;
            box-shadow: 0 1px 4px rgba(67,97,238,0.04);
            transition: background 0.3s;
        }
        .subtask-item input[type=checkbox] {
            margin-right: 12px;
            accent-color: #4361ee;
        }
        .subtask-item.completed { opacity: 0.6; text-decoration: line-through; }
        .add-subtask-btn {
            background: linear-gradient(90deg,#4361ee,#3f37c9);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 8px 18px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 8px rgba(67,97,238,0.08);
        }
        .add-subtask-btn:hover { background: linear-gradient(90deg,#3f37c9,#4361ee); }
        .comments-section {
            background: #f6f8ff;
            border-radius: 10px;
            padding: 18px 22px;
            margin-top: 18px;
            box-shadow: 0 2px 8px rgba(67,97,238,0.03);
            animation: fadeInUp 0.8s 0.2s both;
        }
        .comment {
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
            animation: fadeInUp 0.7s both;
        }
        .comment:last-child { border-bottom: none; }
        .comment-author { font-weight: 600; color: #4361ee; }
        .comment-date { font-size: 0.9em; color: #888; margin-left: 8px; }
        .comment-text { margin-top: 6px; color: #333; }
        .add-comment-form {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }
        .add-comment-form textarea {
            flex: 1;
            border-radius: 8px;
            border: 1.5px solid #e9ecef;
            padding: 10px 12px;
            font-size: 1em;
            resize: none;
            transition: border 0.3s;
        }
        .add-comment-form textarea:focus { border-color: #4361ee; }
        .add-comment-form button {
            background: linear-gradient(90deg,#4361ee,#3f37c9);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 18px;
            font-size: 1em;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.3s, box-shadow 0.3s;
            box-shadow: 0 2px 8px rgba(67,97,238,0.08);
        }
        .add-comment-form button:hover { background: linear-gradient(90deg,#3f37c9,#4361ee); }
        .attachments-section {
            margin-top: 30px;
        }
        .file-upload-zone {
            border: 2px dashed #4361ee;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            background: #f8f9fa;
            transition: background 0.3s, border 0.3s;
        }
        .file-upload-zone.drag-over {
            background: #e0e7ff;
            border-color: #3f37c9;
        }
        .uploaded-files {
            margin-top: 12px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .uploaded-file {
            background: #fff;
            border-radius: 6px;
            padding: 8px 14px;
            box-shadow: 0 1px 4px rgba(67,97,238,0.04);
            font-size: 0.97em;
            color: #3f37c9;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .back-link {
            display: inline-block;
            margin-bottom: 18px;
            color: #4361ee;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover { color: #3f37c9; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="manage-task-container">
        <a href="tasks.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Tasks</a>
        <div class="task-header">
            <div class="task-title"><?php echo htmlspecialchars($task['title']); ?></div>
            <div class="task-status"><?php echo htmlspecialchars($task['status']); ?></div>
        </div>
        <div class="task-meta">
            <div><i class="fas fa-flag"></i> Priority: <b><?php echo htmlspecialchars($task['priority']); ?></b></div>
            <div><i class="fas fa-calendar-alt"></i> Due: <b><?php echo $task['due_date'] ? htmlspecialchars($task['due_date']) : 'N/A'; ?></b></div>
            <div><i class="fas fa-user"></i> Assigned: <b><?php echo htmlspecialchars($task['assigned_to'] ?? 'Unassigned'); ?></b></div>
        </div>
        <div class="task-desc"><?php echo nl2br(htmlspecialchars($task['description'])); ?></div>
        <div class="progress-bar">
            <div class="progress-bar-inner" style="width:60%"></div>
        </div>
        <div class="section-title"><i class="fas fa-tasks"></i> Subtasks</div>
        <div class="subtasks-list" id="subtasks-list">
            <?php foreach ($subtasks as $subtask): ?>
            <form method="POST" style="display:inline;">
                <div class="subtask-item<?php if ($subtask['is_completed']) echo ' completed'; ?>">
                    <input type="checkbox" name="is_completed" onchange="this.form.submit()" <?php if ($subtask['is_completed']) echo 'checked'; ?> />
                    <input type="hidden" name="toggle_subtask" value="<?php echo $subtask['id']; ?>">
                    <?php echo htmlspecialchars($subtask['title']); ?>
                </div>
            </form>
            <?php endforeach; ?>
        </div>
        <form method="POST" class="add-subtask-form" style="margin-bottom:10px;">
            <input type="text" name="new_subtask" placeholder="Add a new subtask..."  style="padding:8px; border-radius:6px; border:1px solid #ccc; width:70%;">
            <button class="add-subtask-btn" type="submit"><i class="fas fa-plus"></i> Add Subtask</button>
        </form>
        <div class="section-title"><i class="fas fa-comments"></i> Comments</div>
        <div class="comments-section" id="comments-section">
            <?php foreach (
                $comments as $comment): ?>
            <div class="comment">
                <span class="comment-author"><?php echo htmlspecialchars($comment['author']); ?></span>
                <span class="comment-date"><?php echo date('Y-m-d', strtotime($comment['created_at'])); ?></span>
                <div class="comment-text"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></div>
            </div>
            <?php endforeach; ?>
            <form class="add-comment-form" method="POST">
                <input type="hidden" name="author" value="You">
                <textarea name="new_comment" id="new-comment" placeholder="Add a comment..." ></textarea>
                <button type="submit"><i class="fas fa-paper-plane"></i></button>
            </form>
        </div>
        <div class="section-title"><i class="fas fa-paperclip"></i> Attachments</div>
        <div class="attachments-section">
            <form method="POST" enctype="multipart/form-data" style="margin-bottom:16px;">
                <div class="file-upload-zone" id="file-upload-zone">
                    <input type="file" id="file-input" name="attachment"  hidden onchange="this.form.submit()">
                    <label for="file-input" style="cursor:pointer;display:block;">
                        <i class="fas fa-cloud-upload-alt" style="font-size:2em;"></i><br>
                        <span>Drag & drop files here or click to upload</span>
                    </label>
                </div>
            </form>
            <div class="uploaded-files" id="uploaded-files">
                <?php foreach ($attachments as $attachment): ?>
                <div class="uploaded-file">
                    <i class="fas fa-file-alt"></i>
                    <a href="<?php echo str_replace('\\', '/', str_replace(__DIR__ . '/../../', '../../', $attachment['filepath'])); ?>" download target="_blank">
                        <?php echo htmlspecialchars($attachment['filename']); ?>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <script>
    // Animate progress bar
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            document.querySelector('.progress-bar-inner').style.width = '80%';
        }, 600);
    });
    // Drag & drop for file upload zone
    const uploadZone = document.getElementById('file-upload-zone');
    if (uploadZone) {
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadZone.classList.add('drag-over');
        });
        uploadZone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
        });
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('drag-over');
            // Handle file upload here
            alert('File upload feature coming soon!');
        });
    }
    // Subtask completion animation
    document.querySelectorAll('.subtask-item input[type=checkbox]').forEach(cb => {
        cb.addEventListener('change', function() {
            if (cb.checked) cb.parentElement.classList.add('completed');
            else cb.parentElement.classList.remove('completed');
        });
    });
    </script>
</body>
</html>
