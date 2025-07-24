<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../login.php");
    exit();
}

// Database connection
require_once('../config.php');

// Get task ID from URL parameter
$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id == 0) {
    header("Location: mytasks.php");
    exit();
}

// Get task details from database
$taskQuery = "SELECT 
    ut.id as user_task_id,
    ut.description,
    ut.target_int,
    ut.target_str,
    ut.start_date,
    ut.end_date,
    ut.total_completed,
    ut.status,
    ut.created_at,
    t.name as task_name,
    (SELECT u.name FROM users u WHERE u.id = ut.user_id) as assigned_to
FROM user_tasks ut
JOIN tasks t ON ut.task_id = t.id
WHERE ut.user_id = ? AND ut.id = ?";

$stmt = $conn->prepare($taskQuery);
$stmt->bind_param("ii", $_SESSION['user_id'], $task_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: mytasks.php");
    exit();
}

$task = $result->fetch_assoc();

// Determine task type based on target_int (since task_type column was removed)
$task_type = ($task['target_int'] > 0) ? 'numeric' : 'text';
$task['task_type'] = $task_type; // Add task_type to task array for consistency

// Get task achievements (timeline)
$achievementsQuery = "SELECT 
    work_orders,
    work_orders_completed,
    notes,
    kendala,
    status,
    created_at
FROM task_achievements 
WHERE user_task_id = ? AND user_id = ?
ORDER BY created_at ASC";

$achievementsStmt = $conn->prepare($achievementsQuery);
$achievementsStmt->bind_param("ii", $task_id, $_SESSION['user_id']);
$achievementsStmt->execute();
$achievementsResult = $achievementsStmt->get_result();
$achievements = $achievementsResult->fetch_all(MYSQLI_ASSOC);

// Calculate progress percentage from work_orders and work_orders_completed in task_achievements
$progress_percentage = 0;
$total_work_orders = 0;
$total_work_orders_completed = 0;

if (!empty($achievements)) {
    // Get the latest achievement entry for progress calculation
    $latestAchievement = end($achievements);
    
    if ($task_type == 'numeric' && $task['target_int'] > 0) {
        // For numeric tasks, use total_completed vs target_int
        $progress_percentage = ($task['total_completed'] / $task['target_int']) * 100;
    } else {
        // For text tasks, calculate based on work_orders vs work_orders_completed
        if (!empty($latestAchievement['work_orders']) && $latestAchievement['work_orders'] > 0) {
            $total_work_orders = $latestAchievement['work_orders'];
            $total_work_orders_completed = $latestAchievement['work_orders_completed'];
            $progress_percentage = ($total_work_orders_completed / $total_work_orders) * 100;
        } else {
            // Check if any achievement has "Achieved" status
            foreach ($achievements as $achievement) {
                if ($achievement['status'] == 'Achieved') {
                    $progress_percentage = 100;
                    break;
                }
            }
        }
    }
}

// Get latest achievement status
$current_status = $task['status']; // Default from user_tasks
if (!empty($achievements)) {
    $latestAchievement = end($achievements);
    $current_status = $latestAchievement['status']; // Use latest achievement status
}

// Check if task period has ended
$current_date = date('Y-m-d');
$is_period_ended = $current_date > $task['end_date'];

// If task period ended and not achieved, consider as passed
if ($is_period_ended) {
    // Determine final achievement status
    if ($task_type == 'numeric' && $task['target_int'] > 0) {
        $final_status = ($task['total_completed'] >= $task['target_int']) ? 'Achieved' : 'Non Achieved';
    } else {
        // For textual tasks, check if there's any achieved status
        $final_status = ($current_status == 'Achieved') ? 'Achieved' : 'Non Achieved';
    }
    $current_status = 'Period Passed (' . $final_status . ')';
}

// Format period dates
$start_formatted = date('F j, Y', strtotime($task['start_date']));
$end_formatted = date('F j, Y', strtotime($task['end_date']));
$period_formatted = $start_formatted . ' - ' . $end_formatted;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Detail - Kaon Employee Dashboard</title>
    <link rel="stylesheet" href="../css/karyawan/style-view.css" />
</head>
<body>
    <div class="container">
        <!-- Header with Back Button -->
        <div class="detail-header">
            <a href="mytasks.php" class="back-btn">
                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Back to Tasks
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
                            if ($task_type == 'numeric' && $task['target_int'] > 0) {
                                echo $task['target_int'] . ' work orders';
                            } else {
                                echo !empty($task['target_str']) ? htmlspecialchars($task['target_str']) : 'Text-based task';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Period</div>
                        <div class="detail-value"><?php echo $period_formatted; ?></div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Work Orders Completed</div>
                        <div class="detail-value">
                            <?php 
                            if ($task_type == 'numeric' && $task['target_int'] > 0) {
                                echo $task['total_completed'] . ' / ' . $task['target_int'] . ' work orders';
                            } else {
                                // For text tasks, show work_orders_completed from latest achievement
                                if (!empty($achievements)) {
                                    $latestAchievement = end($achievements);
                                    if (!empty($latestAchievement['work_orders']) && !empty($latestAchievement['work_orders_completed'])) {
                                        echo $latestAchievement['work_orders_completed'] . ' / ' . $latestAchievement['work_orders'] . ' work orders';
                                    } else {
                                        echo $current_status;
                                    }
                                } else {
                                    echo 'No progress yet';
                                }
                            }
                            ?>
                        </div>
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
                    <div class="timeline-date"><?php echo date('F j, Y - H:i', strtotime($achievement['created_at'])); ?></div>
                    <div class="timeline-content">
                        <strong>Progress Update - <?php echo htmlspecialchars($achievement['status']); ?></strong><br>
                        <?php if ($task_type == 'numeric' && $task['target_int'] > 0): ?>
                            Work Orders Completed: <?php echo $achievement['work_orders_completed']; ?>
                            (<?php echo round(($achievement['work_orders_completed'] / $task['target_int']) * 100); ?>% of target)
                        <?php else: ?>
                            <?php if (!empty($achievement['work_orders'])): ?>
                                Work Orders: <?php echo $achievement['work_orders']; ?><br>
                            <?php endif; ?>
                            <?php if (!empty($achievement['work_orders_completed'])): ?>
                                Work Orders Completed: <?php echo $achievement['work_orders_completed']; ?><br>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($achievement['notes'])): ?>
                            <br>System: <?php echo htmlspecialchars($achievement['notes']); ?>
                        <?php endif; ?>
                        <?php if (!empty($achievement['kendala'])): ?>
                            <br>Issues/Constraints: <?php echo htmlspecialchars($achievement['kendala']); ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <!-- Current Status -->
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i'); ?></div>
                    <div class="timeline-content">
                        <strong>Current Status: <?php echo htmlspecialchars($current_status); ?></strong><br>
                        <?php if (strpos($current_status, 'Period Passed') !== false): ?>
                            Task period has ended. Final result: <?php echo str_replace('Period Passed (', '', str_replace(')', '', $current_status)); ?>
                        <?php elseif ($current_status == 'In Progress'): ?>
                            Task is still in progress.
                        <?php elseif ($current_status == 'Achieved'): ?>
                            Task has been completed successfully!
                        <?php elseif ($current_status == 'Non Achieved'): ?>
                            Task has not been achieved.
                        <?php endif; ?>
                        
                        <?php if ($task_type == 'numeric' && $task['target_int'] > 0): ?>
                            <br>Total Completed: <?php echo $task['total_completed']; ?> / <?php echo $task['target_int']; ?> work orders
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
