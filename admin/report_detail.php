<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($task_id == 0) {
    header("Location: report.php");
    exit();
}

$taskQuery = "SELECT 
    ut.id as user_task_id,
    ut.description,
    ut.target_int,
    ut.target_str,
    ut.progress_int,
    ut.start_date,
    ut.end_date,
    ut.status,
    ut.created_at,
    ut.task_type,
    t.name as task_name,
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

$achievementsQuery = "SELECT 
    progress_int,
    notes,
    work_orders,
    work_orders_completed,
    kendala,
    status,
    created_at
FROM task_achievements 
WHERE user_task_id = ? 
ORDER BY created_at ASC";

$achievementsStmt = $conn->prepare($achievementsQuery);
$achievementsStmt->bind_param("i", $task_id);
$achievementsStmt->execute();
$achievementsResult = $achievementsStmt->get_result();
$achievements = $achievementsResult->fetch_all(MYSQLI_ASSOC);

$progress_percentage = isset($task['progress_int']) ? $task['progress_int'] : 0;
$current_status = $task['status']; 
if (!empty($achievements)) {
    $latestAchievement = end($achievements);
    $current_status = $latestAchievement['status'];
}

if ($current_status == 'In Progress') {
    $current_status = 'Non Achieved';
}

$current_date = date('Y-m-d');
$is_overdue = $task['end_date'] < $current_date;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Detail</title>
    <link rel="stylesheet" href="../css/karyawan/style-view.css" />
</head>
<body>
    <div class="container">
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
                            if ($task['task_type'] == 'numeric' && !empty($task['target_int'])) {
                                echo $task['target_int'];
                            } elseif (($task['task_type'] == 'textual' || $task['task_type'] == 'text') && !empty($task['target_str'])) {
                                echo htmlspecialchars($task['target_str']);
                            } elseif (!empty($task['target_str'])) {
                                echo htmlspecialchars($task['target_str']);
                            } elseif (!empty($task['target_int'])) {
                                echo $task['target_int'];
                            } else {
                                echo 'Target not specified';
                            }
                            ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Period</div>
                        <div class="detail-value">
                            <?php 
                            $start_date = $task['start_date'] ? date('F j, Y', strtotime($task['start_date'])) : '-';
                            $end_date = $task['end_date'] ? date('F j, Y', strtotime($task['end_date'])) : '-';
                            echo $start_date . ' - ' . $end_date;
                            ?>
                        </div>
                    </div>
                    <div class="detail-item">
                        <div class="detail-label">Employee</div>
                        <div class="detail-value"><?php echo htmlspecialchars($task['user_name']); ?></div>
                    </div>
                    <?php if (!empty($achievements)): ?>
                    <div class="detail-item">
                        <div class="detail-label">Work Orders Completed</div>
                        <div class="detail-value">
                            <?php 
                            $latestAchievement = end($achievements);
                            $wo_total = (int)($latestAchievement['work_orders'] ?? 0);
                            $wo_completed = (int)($latestAchievement['work_orders_completed'] ?? 0);
                            echo $wo_completed;
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="progress-section">
                    <div class="progress-label">
                        <span class="progress-title">Work Orders Completion</span>
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
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i', strtotime($task['created_at'])); ?></div>
                    <div class="timeline-content">
                        <strong>Task Created</strong><br>
                        <?php echo htmlspecialchars($task['task_name']); ?> task created and assigned.
                    </div>
                </div>
                
                <?php foreach ($achievements as $achievement): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i', strtotime($achievement['created_at'])); ?></div>
                    <div class="timeline-content">
                        <strong>Progress Update</strong><br>
                        <?php 
                        $wo_total = (int)($achievement['work_orders'] ?? 0);
                        $wo_completed = (int)($achievement['work_orders_completed'] ?? 0);
                        $wo_percentage = $wo_total > 0 ? round(($wo_completed / $wo_total) * 100) : 0;
                        ?>
                        Work Orders: <?php echo $wo_completed; ?>/<?php echo $wo_total; ?> (<?php echo $wo_percentage; ?>%)
                        <?php if ($task['task_type'] == 'numeric' && !empty($achievement['progress_int'])): ?>
                        <?php endif; ?>
                        <?php if (!empty($achievement['notes'])): ?>
                            <br>Notes: <?php echo htmlspecialchars($achievement['notes']); ?>
                        <?php endif; ?>
                        <?php if (!empty($achievement['kendala'])): ?>
                            <br>Kendala: <?php echo htmlspecialchars($achievement['kendala']); ?>
                        <?php endif; ?>
                        <br>Status: <?php echo htmlspecialchars($achievement['status'] == 'In Progress' ? 'Non Achieved' : $achievement['status']); ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="timeline-item">
                    <div class="timeline-date"><?php echo date('F j, Y - H:i'); ?></div>
                    <div class="timeline-content">
                        <strong>Current Status: <?php echo htmlspecialchars($current_status); ?></strong><br>
                        <?php if ($current_status == 'Achieved'): ?>
                            Task has been completed successfully!
                        <?php elseif ($current_status == 'Non Achieved' && $is_overdue): ?>
                            Task end date has passed and remains incomplete.
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
