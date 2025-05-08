<?php
require_once 'conttroler.php';

$controller = new EventController();
$events = $controller->listEvents();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events List</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .event-card {
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .event-card:hover {
            transform: translateY(-5px);
        }
        .event-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .event-body {
            padding: 20px;
        }
        .event-footer {
            background-color: #f8f9fa;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Events List</h1>
        
        <?php if (empty($events)): ?>
            <div class="alert alert-info">
                No events found.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($events as $event): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card event-card">
                            <div class="event-header">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['event_title']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">
                                    <?php echo htmlspecialchars($event['event_type']); ?>
                                </h6>
                            </div>
                            <div class="event-body">
                                <p class="card-text">
                                    <?php echo htmlspecialchars($event['description']); ?>
                                </p>
                                <p class="card-text">
                                    <strong>Date:</strong> <?php echo htmlspecialchars($event['start_date']); ?><br>
                                    <strong>Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?>
                                </p>
                                <p class="card-text">
                                    <strong>Format:</strong> <?php echo htmlspecialchars($event['event_format']); ?><br>
                                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                            </div>
                            <div class="event-footer">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($event['ticket_type']); ?>
                                    </span>
                                    <span class="text-muted">
                                        <?php echo htmlspecialchars($event['capacity']); ?> spots
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <a href="edit.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                    <a href="delete.php?id=<?php echo $event['event_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 