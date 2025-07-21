<?php
require_once '../config.php';

// Get task ID from URL parameter
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id == 0) {
    header("Location: report.php");
    exit();
}

// Get task details from database using user_task_id
$taskQuery = "SELECT 
    ut.id as user_task_id,
    ut.description,
    ut.target_int,
    ut.target_str,
    ut.progress_int,
    ut.deadline,
    ut.status,
    ut.created_at,
    t.name as task_name,
    t.type as task_type,
    u.name as user_name
FROM user_tasks ut
JOIN tasks t ON ut.task_id = t.id
JOIN users u ON ut.user_id = u.id
WHERE ut.id = ?";

$stmt = $conn->prepare($taskQuery);
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: report.php");
    exit();
}

$task = $result->fetch_assoc();

// Get task achievements (timeline)
$achievementsQuery = "SELECT 
    progress_int,
    notes,
    status,
    submitted_at
FROM task_achievements 
WHERE user_task_id = ? 
ORDER BY submitted_at ASC";

$achievementsStmt = $conn->prepare($achievementsQuery);
$achievementsStmt->bind_param("i", $task_id);
$achievementsStmt->execute();
$achievementsResult = $achievementsStmt->get_result();
$achievements = $achievementsResult->fetch_all(MYSQLI_ASSOC);

// Calculate progress percentage from latest achievement
$progress_percentage = 0;
$current_status = $task['status']; // Default to user_tasks status

if (!empty($achievements)) {
    // Get latest progress from achievements
    $latestAchievement = end($achievements);
    $current_status = $latestAchievement['status']; // Use latest achievement status
    
    if ($task['task_type'] == 'numeric' && $task['target_int'] > 0) {
        $progress_percentage = min(100, ($latestAchievement['progress_int'] / $task['target_int']) * 100);
    } else if ($task['task_type'] == 'text') {
        $progress_percentage = ($latestAchievement['status'] == 'Achieved') ? 100 : (($latestAchievement['status'] == 'In Progress') ? 50 : 0);
    }
} else {
    // Fallback to user_tasks progress if no achievements
    if ($task['task_type'] == 'numeric' && $task['target_int'] > 0) {
        $progress_percentage = min(100, ($task['progress_int'] / $task['target_int']) * 100);
    } else if ($task['task_type'] == 'text') {
        $progress_percentage = ($task['status'] == 'Achieved') ? 100 : (($task['status'] == 'In Progress') ? 50 : 0);
    }
}

// Check if task is overdue and still in progress
$current_date = date('Y-m-d');
$is_overdue = $task['deadline'] < $current_date;

// If task is overdue and not achieved, change status to Non Achieved
if ($is_overdue && $current_status == 'In Progress') {
    $current_status = 'Non Achieved';
}

// Format deadline
$deadline_formatted = date('F j, Y', strtotime($task['deadline']));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Detail - Kaon Admin Report</title>
    <link rel="stylesheet" href="../css/karyawan/style-view.css" />
</head>
<body>
    <div class="container">
        <!-- Header with Back Button -->
        <div class="detail-header">
            <a href="report.php" class="back-btn">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Back to Report
            </a>
            <h1 class="page-title">Task Details</h1>
        </div>

        <!-- Main Content -->
        <div class="detail-content">
            <!-- Task Information Card -->
            <div class="task-info-card">
                <div class="task-type-badge"><?php echo ucfirst($task['task_type']); ?></div>
                <h2 class="task-title"><?php echo htmlspecialchars($task['task_name']); ?></h2>

                <div class="task-description">
                    <?php echo htmlspecialchars($task['description']); ?>
                </div>

                <div class="task-details-grid">
                    <div class="detail-item">
                        <div class="detail-label">Target</div>
                        <div class="detail-value">
                            <?php 
                            if ($task['task_type'] == 'numeric') {
                                echo $task['target_int'] . ' units';
                            } else {
                                echo htmlspecialchars($task['target_str']);
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Deadline</div>
                        <div class="detail-value"><?php echo $deadline_formatted; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Employee</div>
                        <div class="detail-value"><?php echo htmlspecialchars($task['user_name']); ?></div>
                    </div>
                </div>

                <div class="progress-section">
                    <div class="progress-label">
                        <span class="progress-title">Progress Completion</span>
                        <span class="progress-percentage"><?php echo round($progress_percentage); ?>%</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo round($progress_percentage); ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="status-card">
                <div class="status-header">
                    <h3 class="status-title">Task Status</h3>
                    <div class="status-badge-large status-<?php echo strtolower(str_replace(' ', '-', $current_status)); ?>">
                        <div class="status-indicator"></div>
                        <?php echo htmlspecialchars($current_status); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Section -->
        <div class="timeline-section">
            <h3 class="timeline-title">Activity Timeline</h3>
            <div class="timeline">
                <!-- Task Created -->
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i', strtotime($task['created_at'])); ?></div>
                    <div class="timeline-content">
                        <strong>Task Created</strong><br>
                        <?php echo htmlspecialchars($task['task_name']); ?> task created and assigned.
                    </div>
                </div>
                
                <!-- Progress Updates from task_achievements -->
                <?php foreach ($achievements as $achievement): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i', strtotime($achievement['submitted_at'])); ?></div>
                    <div class="timeline-content">
                        <strong>Progress Update</strong><br>
                        <?php if ($task['task_type'] == 'numeric'): ?>
                            Progress: <?php echo $achievement['progress_int']; ?> out of <?php echo $task['target_int']; ?> 
                            (<?php echo round(($achievement['progress_int'] / $task['target_int']) * 100); ?>%)
                        <?php endif; ?>
                        <?php if (!empty($achievement['notes'])): ?>
                            <br>Notes: <?php echo htmlspecialchars($achievement['notes']); ?>
                        <?php endif; ?>
                        <br>Status: <?php echo htmlspecialchars($achievement['status']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Current Status -->
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i'); ?></div>
                    <div class="timeline-content">
                        <strong>Current Status: <?php echo htmlspecialchars($current_status); ?></strong><br>
                        <?php if ($current_status == 'In Progress'): ?>
                            Task is still in progress.
                        <?php elseif ($current_status == 'Achieved'): ?>
                            Task has been completed successfully!
                        <?php elseif ($current_status == 'Non Achieved' && $is_overdue): ?>
                            Task deadline has passed and remains incomplete.
                        <?php else: ?>
                            Task has not been achieved.
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
