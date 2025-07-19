<?php
/**
 * Automatic Overdue Tasks Update Script
 * 
 * Script ini secara otomatis memproses task-task yang sudah melewati deadline
 * dengan mengubah statusnya menjadi "Non Achieved" dan membuat record achievement.
 * 
 * Dijalankan sebagai cron job atau manual untuk memproses semua task overdue.
 * 
 * Usage: php auto_overdue_update.php
 */

require_once 'config.php';

echo "Starting automatic overdue tasks update...\n";
echo "Current date: " . date('Y-m-d H:i:s') . "\n";

$currentDate = date('Y-m-d');
$processedTasks = 0;
$createdAchievements = 0;

try {
    // Get all overdue tasks that are still "In Progress"
    $getOverdueQuery = "SELECT ut.id, ut.user_id, ut.deadline, t.name as task_name 
                        FROM user_tasks ut
                        JOIN tasks t ON ut.task_id = t.id
                        WHERE ut.status = 'In Progress' 
                        AND ut.deadline < ?
                        ORDER BY ut.user_id, ut.deadline";

    $getOverdueStmt = $conn->prepare($getOverdueQuery);
    $getOverdueStmt->bind_param("s", $currentDate);
    $getOverdueStmt->execute();
    $overdueResults = $getOverdueStmt->get_result();

    echo "Found " . $overdueResults->num_rows . " overdue tasks to process.\n";

    // Process each overdue task
    while ($overdueTask = $overdueResults->fetch_assoc()) {
        $user_task_id = $overdueTask['id'];
        $task_user_id = $overdueTask['user_id'];
        $deadline = $overdueTask['deadline'];
        $task_name = $overdueTask['task_name'];
        
        // Check if achievement record already exists for this overdue task
        $checkAchievementQuery = "SELECT id FROM task_achievements 
                                 WHERE user_task_id = ? AND status = 'Non Achieved' 
                                 ORDER BY submitted_at DESC LIMIT 1";
        $checkStmt = $conn->prepare($checkAchievementQuery);
        $checkStmt->bind_param("i", $user_task_id);
        $checkStmt->execute();
        $achievementExists = $checkStmt->get_result()->num_rows > 0;
        
        // Only create achievement record if it doesn't exist
        if (!$achievementExists) {
            // Insert achievement record for overdue task
            $insertAchievementQuery = "INSERT INTO task_achievements (user_task_id, user_id, progress_int, notes, status, submitted_at) 
                                      VALUES (?, ?, ?, ?, ?, NOW())";
            $insertAchievementStmt = $conn->prepare($insertAchievementQuery);
            $progress_int = 0; // Overdue tasks have 0% progress
            $notes = "Task otomatis ditandai Non Achieved karena melewati deadline pada " . $currentDate;
            $status = "Non Achieved";
            $insertAchievementStmt->bind_param("iiiss", $user_task_id, $task_user_id, $progress_int, $notes, $status);
            
            if ($insertAchievementStmt->execute()) {
                $createdAchievements++;
                echo "  ✓ Created achievement record for task: {$task_name} (User: {$task_user_id})\n";
            } else {
                echo "  ✗ Failed to create achievement record for task: {$task_name}\n";
            }
        }
        
        $processedTasks++;
    }

    // Update all overdue tasks status in bulk
    if ($processedTasks > 0) {
        $overdueUpdateQuery = "UPDATE user_tasks 
                              SET status = 'Non Achieved', updated_at = NOW() 
                              WHERE status = 'In Progress' 
                              AND deadline < ?";

        $overdueStmt = $conn->prepare($overdueUpdateQuery);
        $overdueStmt->bind_param("s", $currentDate);
        
        if ($overdueStmt->execute()) {
            $affectedRows = $overdueStmt->affected_rows;
            echo "\n✓ Updated {$affectedRows} tasks to 'Non Achieved' status\n";
        } else {
            echo "\n✗ Failed to update task statuses\n";
        }
    }

    echo "\nUpdate completed successfully!\n";
    echo "Summary:\n";
    echo "- Processed tasks: {$processedTasks}\n";
    echo "- Created achievement records: {$createdAchievements}\n";
    echo "- Completion time: " . date('Y-m-d H:i:s') . "\n";

} catch (Exception $e) {
    echo "\n✗ Error occurred: " . $e->getMessage() . "\n";
    exit(1);
}

$conn->close();
echo "\nScript finished.\n";
?>
