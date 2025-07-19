<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../login.php");
    exit();
}

// Database connection
require_once('../config.php');

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2); // ambil maksimal 2 huruf aja
}

$userInitials = getInitials($_SESSION['user_name']);
$userId = $_SESSION['user_id'];

// Show success message if redirected after successful submission
if (isset($_GET['report']) && $_GET['report'] === 'success') {
    echo '<div id="successMessage" style="position: fixed; top: 20px; right: 20px; background: green; color: white; padding: 10px; border-radius: 5px; z-index: 9999;">Report submitted successfully!</div>
    <script>
        setTimeout(function() {
            const successMsg = document.getElementById("successMessage");
            if (successMsg) {
                successMsg.style.opacity = "0";
                successMsg.style.transition = "opacity 0.3s ease";
                setTimeout(function() {
                    successMsg.remove();
                }, 300);
            }
        }, 1000);
    </script>';
}

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_task_id'])) {
    error_log("POST data received: " . print_r($_POST, true));
    
    $user_task_id = intval($_POST['user_task_id']);
    $note = isset($_POST['note']) ? trim($_POST['note']) : '';
    $auto_status = isset($_POST['auto_status']) ? trim($_POST['auto_status']) : '';
    $progress_int = 0;

    // Ambil tipe task dan target
    $typeQuery = "SELECT t.type, ut.target_int FROM user_tasks ut JOIN tasks t ON ut.task_id = t.id WHERE ut.id = ? LIMIT 1";
    $typeStmt = $conn->prepare($typeQuery);
    $typeStmt->bind_param("i", $user_task_id);
    $typeStmt->execute();
    $typeResult = $typeStmt->get_result();
    $typeRow = $typeResult->fetch_assoc();
    $task_type = $typeRow ? $typeRow['type'] : '';
    $target_int = $typeRow ? (int)$typeRow['target_int'] : 0;

    error_log("Task type: $task_type, Target: $target_int");

    if ($task_type === 'numeric') {
        // Untuk task numeric: progress_int = (capaian/target_int) × 100
        $capaian = isset($_POST['progress_int']) && $_POST['progress_int'] !== '' ? intval($_POST['progress_int']) : 0;
        $progress_int = ($target_int > 0) ? round(($capaian / $target_int) * 100) : 0;
    } else {
        // Untuk task text: 
        // Jika status Achieved = progress_int 100%
        // Jika status Non Achieved atau In Progress = progress_int sesuai input user
        if ($auto_status === 'Achieved') {
            $progress_int = 100;
        } else {
            // Untuk In Progress dan Non Achieved, ambil dari input persentase user
            $progress_int = isset($_POST['progress_percent']) && $_POST['progress_percent'] !== '' ? intval($_POST['progress_percent']) : 0;
        }
    }

    error_log("Final progress_int: $progress_int, status: $auto_status");

    // Insert ke task_achievements
    $insertQuery = "INSERT INTO task_achievements (user_task_id, user_id, progress_int, notes, status, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    
    if (!$insertStmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Database error: " . $conn->error);
    }
    
    $insertStmt->bind_param(
        "iiiss",
        $user_task_id,
        $userId,
        $progress_int,
        $note,
        $auto_status
    );
    
    if (!$insertStmt->execute()) {
        error_log("Execute failed: " . $insertStmt->error);
        die("Insert error: " . $insertStmt->error);
    }

    // Update status dan progress_int di user_tasks
    if ($auto_status && in_array($auto_status, ['Achieved', 'In Progress', 'Non Achieved'])) {
        $updateQuery = "UPDATE user_tasks SET status = ?, progress_int = ?, updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            error_log("Update prepare failed: " . $conn->error);
        } else {
            $updateStmt->bind_param("sii", $auto_status, $progress_int, $user_task_id);
            if (!$updateStmt->execute()) {
                error_log("Update execute failed: " . $updateStmt->error);
            }
        }
    }

    // Redirect agar tidak resubmit
    header("Location: mytasks.php?report=success");
    exit();
}

// Auto-update overdue tasks to "Non Achieved" status and create achievement records
$currentDate = date('Y-m-d');

// First, get all overdue tasks that need to be updated
$getOverdueQuery = "SELECT id, user_id FROM user_tasks 
                    WHERE user_id = ? 
                    AND status = 'In Progress' 
                    AND deadline < ?";

$getOverdueStmt = $conn->prepare($getOverdueQuery);
$getOverdueStmt->bind_param("is", $userId, $currentDate);
$getOverdueStmt->execute();
$overdueResults = $getOverdueStmt->get_result();

// Process each overdue task
while ($overdueTask = $overdueResults->fetch_assoc()) {
    $user_task_id = $overdueTask['id'];
    $task_user_id = $overdueTask['user_id'];
    
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
        $notes = "Task otomatis ditandai Non Achieved karena melewati deadline";
        $status = "Non Achieved";
        $insertAchievementStmt->bind_param("iiiss", $user_task_id, $task_user_id, $progress_int, $notes, $status);
        $insertAchievementStmt->execute();
    }
}

// Update overdue tasks status
$overdueUpdateQuery = "UPDATE user_tasks 
                      SET status = 'Non Achieved', updated_at = NOW() 
                      WHERE user_id = ? 
                      AND status = 'In Progress' 
                      AND deadline < ?";

$overdueStmt = $conn->prepare($overdueUpdateQuery);
$overdueStmt->bind_param("is", $userId, $currentDate);
$overdueStmt->execute();

// Get user statistics
$statsQuery = "SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as active_tasks,
    SUM(CASE WHEN status = 'Achieved' THEN 1 ELSE 0 END) as achieved_tasks,
    SUM(CASE WHEN status = 'Non Achieved' THEN 1 ELSE 0 END) as non_achieved_tasks
FROM user_tasks WHERE user_id = ?";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Calculate achievement rate
$achievementRate = ($stats['total_tasks'] > 0) ? round(($stats['achieved_tasks'] / $stats['total_tasks']) * 100) : 0;

// Get user tasks with task details and achievement date
$tasksQuery = "SELECT 
    ut.id as user_task_id,
    ut.status,
    ut.progress_int,
    ut.target_int,
    ut.target_str,
    ut.created_at,
    ut.deadline,
    t.name as task_name,
    t.type as task_type,
    ut.description,
    (SELECT ta.submitted_at 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id AND ta.status = 'Achieved' 
     ORDER BY ta.submitted_at DESC 
     LIMIT 1) as achievement_date,
    (SELECT ta.progress_int 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.submitted_at DESC 
     LIMIT 1) as last_progress_int,
    (SELECT ta.notes 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.submitted_at DESC 
     LIMIT 1) as last_notes,
    (SELECT ta.status 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.submitted_at DESC 
     LIMIT 1) as last_report_status
FROM user_tasks ut 
JOIN tasks t ON ut.task_id = t.id 
WHERE ut.user_id = ? 
ORDER BY ut.created_at DESC";

$tasksStmt = $conn->prepare($tasksQuery);
$tasksStmt->bind_param("i", $userId);
$tasksStmt->execute();
$tasksResult = $tasksStmt->get_result();
$userTasks = $tasksResult->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks - Tama</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/karyawan/style-mytasks.css" />
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()"></button>
    
    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
        <div class="sidebar-logo-container">
            <div class="sidebar-logo">
                <img src="../img/tamaaa.png" alt="TAMA Logo" style="height: 100px; display: block; margin: 0; padding: 0; margin-left: 60px;">
            </div>
        </div>
      </div>
            <div class="sidebar-nav">
                <div class="sidebar-nav">
                    <div class="nav-item">
                    <a href="dashboard.php" class="nav-link" onclick="showSection('dashboard')">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                    </div>
                    <div class="nav-item">
                    <a href="mytasks.php" class="nav-link active" onclick="showSection('my-tasks')">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">My Tasks</span>
                    </a>
                    </div>
                    <div class="nav-item">
                    <a href="myperformance.php" class="nav-link" onclick="showSection('performance')">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Statistics</span>
                    </a>
                    </div>
            </div>
        </nav>

        <!-- Main Content -->
    <main class="main-content" id="mainContent">
      <header class="header">
        <div>
          <h1 class="header-title">My Tasks</h1>
        </div>   
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar me-2 bg-primary"><?= $userInitials; ?></div>
                <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['user_name']); ?></span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                <li>
                    <a class="dropdown-item d-flex align-items-center" href="profile.php">
                    <i class="bi bi-person me-2"></i> Profile
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                      <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                      </button>
                </li>
                </ul>
            </div>
        </div>
    </header>

            <!-- Content -->
        <div class="container-fluid p-4">
            <!-- Stats Grid -->
            <div class="row g-4 mb-4">
            <div class="col-md-6 col-xl-3">
                <div class="stats-card p-3">
                    <div class="d-flex align-items-center mb-2">
                        <div class="stats-icon bg-primary text-white rounded-3 p-2 me-3">
                            <i class="bi bi-list-check"></i>
                        </div>
                        <small class="text-muted text-uppercase fw-semibold">Active Tasks</small>
                    </div>
                    <div class="stats-value" id="totalCount"><?= $stats['active_tasks'] ?></div>
                </div>
            </div>

                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                        <div class="stats-icon bg-info text-white rounded-3 p-2 me-3">
                                    <i class="bi bi-percent"></i>
                                </div>
                        <small class="text-muted text-uppercase fw-semibold">Achievement Rate</small>
                        </div>
                        <div class="stats-value" id="achievementRate"><?= $achievementRate ?>%</div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-success text-white rounded-3 p-2 me-3">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                            <small class="text-muted text-uppercase fw-semibold">Achieved</small>
                        </div>
                        <div class="stats-value" id="completedCount"><?= $stats['achieved_tasks'] ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-danger text-white rounded-3 p-2 me-3">
                                    <i class="bi bi-x-circle"></i>
                                </div>
                            <small class="text-muted text-uppercase fw-semibold">Non Achieved</small>
                        </div>
                        <div class="stats-value" id="overdueCount"><?= $stats['non_achieved_tasks'] ?></div>
                    </div>
                </div>
            </div>

                <!-- Task Controls -->
                <div class="tasks-header">
                    <h2 class="tasks-title">Task Management</h2>
                    <p class="tasks-subtitle">Manage and track your assigned tasks efficiently</p>
                    
                    <div class="tasks-controls">
                        <div class="search-box">
                            <svg class="search-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                            <input type="text" class="search-input" placeholder="Search tasks..." oninput="filterTasks()">
                        </div>
                        
                        <div class="filter-buttons">
                            <button class="filter-btn active" onclick="setFilter('all', event)">All</button>
                            <button class="filter-btn" onclick="setFilter('inprogress', event)">In Progress</button>
                            <button class="filter-btn" onclick="setFilter('achieved', event)">Achieved</button>
                            <button class="filter-btn" onclick="setFilter('nonachieved', event)">Non Achieved</button>
                        </div>
                        
                        <select class="sort-select" onchange="sortTasks(this.value)">
                            <option value="">Sort Tasks</option>
                            <option value="name-asc">Task Name (A-Z)</option>
                            <option value="name-desc">Task Name (Z-A)</option>
                            <option value="deadline-asc">Deadline (Earliest First)</option>
                            <option value="deadline-desc">Deadline (Latest First)</option>
                            <option value="status-asc">Status (A-Z)</option>
                            <option value="status-desc">Status (Z-A)</option>
                        </select>
                    </div>
                </div>

                <!-- Task Cards -->
                <div class="tasks-grid" id="tasksGrid">
                    <?php if (empty($userTasks)): ?>
                        <div class="empty-state text-center p-5">
                            <div class="empty-state-icon mb-3">
                                <i class="bi bi-clipboard-x display-1 text-muted"></i>
                            </div>
                            <h4 class="empty-state-title">No Tasks Found</h4>
                            <p class="empty-state-message text-muted">You don't have any tasks assigned to you at the moment.</p>
                            <div class="empty-state-action">
                                <button class="btn btn-primary" onclick="window.location.reload()">
                                    <i class="bi bi-arrow-clockwise me-2"></i>
                                    Refresh Page
                                </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($userTasks as $task): 
                            $statusClass = '';
                            $statusText = '';
                            $statusData = '';
                            switch ($task['status']) {
                                case 'In Progress':
                                    $statusClass = 'status-progress';
                                    $statusText = 'In Progress';
                                    $statusData = 'inprogress';
                                    break;
                                case 'Achieved':
                                    $statusClass = 'status-achieve';
                                    $statusText = 'Achieved';
                                    $statusData = 'achieved';
                                    break;
                                case 'Non Achieved':
                                    $statusClass = 'status-nonachieve';
                                    $statusText = 'Non Achieved';
                                    $statusData = 'nonachieved';
                                    break;
                            }
                            
                            $deadline = date('M j, Y', strtotime($task['deadline']));
                            
                            // Check if task is overdue
                            $currentDate = date('Y-m-d');
                            $taskDeadline = date('Y-m-d', strtotime($task['deadline']));
                            $isOverdue = ($currentDate > $taskDeadline);
                            
                            // Determine if should show overdue indicator
                            $showOverdue = false;
                            $overdueMessage = '';
                            
                            if ($isOverdue) {
                                if ($task['status'] == 'Non Achieved') {
                                    // Show overdue for Non Achieved tasks that passed deadline
                                    $showOverdue = true;
                                    $overdueMessage = '(OVERDUE)';
                                } elseif ($task['status'] == 'Achieved' && $task['achievement_date']) {
                                    // Check if achieved after deadline
                                    $achievedDate = date('Y-m-d', strtotime($task['achievement_date']));
                                    if ($achievedDate > $taskDeadline) {
                                        $showOverdue = true;
                                        $achievedFormatted = date('M j, Y H:i', strtotime($task['achievement_date']));
                                        $overdueMessage = '(OVERDUE - Achieved: ' . $achievedFormatted . ')';
                                    }
                                }
                            }
                            
                            // Handle target display based on task type
                            $targetDisplay = '';
                            if ($task['task_type'] == 'numeric') {
                                $targetDisplay = $task['target_int'] ? 'Target: ' . $task['target_int'] : 'Target: -';
                            } else {
                                $targetDisplay = $task['target_str'] ? 'Target: ' . $task['target_str'] : 'Target: -';
                            }
                        ?>
                        <div class="task-card priority-high" data-status="<?= $statusData ?>" data-type="<?= htmlspecialchars($task['task_type']) ?>" data-priority="high" data-deadline="<?= $task['deadline'] ?>">
                            <div class="task-header">
                                <div>
                                    <div class="task-title"><?= htmlspecialchars($task['task_name']) ?></div>
                                </div>
                                <?php if ($task['status'] == 'Achieved' || $task['status'] == 'Non Achieved'): ?>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                        <span class="status-badge <?= $statusClass ?>">
                                            <div class="status-indicator"></div>
                                            <?= $statusText ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="status-badge <?= $statusClass ?>">
                                        <div class="status-indicator"></div>
                                        <?= $statusText ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="task-description">
                                <?= htmlspecialchars($task['description']) ?>
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline <?= $showOverdue ? 'overdue' : '' ?>">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Due: <?= $deadline ?> 
                                    <?php if ($showOverdue): ?>
                                        <span class="overdue-indicator"><?= $overdueMessage ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="task-target"><?= $targetDisplay ?></div>
                            </div>
                            <div class="task-actions">
                                <?php 
                                // Tombol Report hanya jika belum Achieved
                                if ($task['status'] != 'Achieved') {
                                    echo '<button class="task-btn btn-primary ms-2" onclick="openReportModal(' . htmlspecialchars(json_encode([
                                        'id' => $task['user_task_id'],
                                        'name' => $task['task_name'],
                                        'description' => $task['description'],
                                        'target_int' => $task['target_int'],
                                        'target_str' => $task['target_str'],
                                        'type' => $task['task_type'],
                                        'status' => $task['status'],
                                        'last_progress_int' => $task['last_progress_int'],
                                        'last_notes' => $task['last_notes'],
                                        'last_report_status' => $task['last_report_status']
                                    ]), ENT_QUOTES, 'UTF-8') . ')">Report</button>';
                                }

                                // Tombol View selalu ada
                                echo '<button class="task-btn btn-secondary" onclick="window.location.href=\'view.php?id=' . $task['user_task_id'] . '\'">View</button>';
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>

        <!-- Report Modal -->
        <div class="modal fade" id="reportTaskModal" tabindex="-1" aria-labelledby="reportTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-sm-custom">
                <div class="modal-content modal-content-compact">
                    <form id="reportTaskForm" method="post" action="mytasks.php" novalidate>
                        <div class="modal-header modal-header-compact modal-header-custom">
                            <h6 class="modal-title modal-title-gradient" id="reportTaskModalLabel">Submit Task Report</h6>
                            <button type="button" class="btn-close btn-close-sm btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body modal-body-compact">
                            <div class="alert alert-info alert-compact" id="editModeAlert" style="display:none;">
                                <i class="bi bi-info-circle me-1"></i>
                                <small><strong>Edit Mode:</strong> You are editing your previous report.</small>
                            </div>
                            <input type="hidden" name="user_task_id" id="reportTaskId">
                            
                            <div class="report-form-grid-compact">
                                <div class="form-group-compact">
                                    <label class="form-label-compact">Task Name</label>
                                    <div class="form-control-plaintext-compact" id="reportTaskName"></div>
                                </div>
                                <div class="form-group-compact">
                                    <label class="form-label-compact">Target</label>
                                    <div class="form-control-plaintext-compact" id="reportTaskTarget"></div>
                                </div>
                                <div class="form-group-compact">
                                    <label class="form-label-compact">Description</label>
                                    <div class="form-control-plaintext-compact" id="reportTaskDesc"></div>
                                </div>
                                
                                <div class="form-group-compact" id="reportTypeNumeric" style="display:none;">
                                    <label class="form-label-compact">Capaian *</label>
                                    <input type="number" class="form-control form-control-compact" name="progress_int" id="progressInput" min="0" step="1" placeholder="Masukkan capaian...">
                                </div>
                                <div class="form-group-compact" id="reportTypeString" style="display:none;">
                                    <label class="form-label-compact">Status</label>
                                    <select class="form-select form-control-compact" name="status_select" id="statusSelect">
                                        <option value="In Progress">In Progress</option>
                                        <option value="Achieved">Achieved</option>
                                    </select>
                                </div>
                                <div class="form-group-compact" id="progressPercentGroup" style="display:none;">
                                    <label class="form-label-compact">Persentase Capaian (%) *</label>
                                    <input type="number" class="form-control form-control-compact" name="progress_percent" id="progressPercentInput" min="0" max="100" step="1" placeholder="Masukkan persentase...">
                                </div>
                                <div class="form-group-compact">
                                    <label class="form-label-compact">Note <span class="text-muted">(optional)</span></label>
                                    <textarea class="form-control form-control-compact" name="note" id="reportNote" rows="2" placeholder="Tambahkan catatan..."></textarea>
                                </div>
                                <div class="form-group-compact">
                                    <label class="form-label-compact">Status Otomatis</label>
                                    <input type="text" class="form-control form-control-compact" id="autoStatus" name="auto_status" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer modal-footer-compact">
                            <button type="submit" class="btn btn-primary btn-compact" onclick="console.log('Submit button clicked!')">Submit Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Logout Modal -->
  <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="modal-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <p class="modal-message">Are you sure you want to sign out?</p>
                    
                    <div class="d-flex gap-2 justify-content-center flex-column flex-sm-row">
                        <button type="button" class="btn btn-danger btn-logout" onclick="confirmLogout()">
                            Yes, Logout
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-cancel" data-bs-dismiss="modal">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="../js/karyawan/mytasks.js?v=<?= time() ?>"></script>
        <script>
        // Modal logic
        let reportModal;
        document.addEventListener('DOMContentLoaded', function() {
            reportModal = new bootstrap.Modal(document.getElementById('reportTaskModal'));
        });

        function openReportModal(taskData) {
            console.log('Opening report modal with data:', taskData);
            if (typeof taskData === 'string') taskData = JSON.parse(taskData);
            
            document.getElementById('reportTaskId').value = taskData.id;
            document.getElementById('reportTaskName').textContent = taskData.name;
            document.getElementById('reportTaskDesc').textContent = taskData.description;
            let targetText = '-';
            
            console.log('Task ID set to:', taskData.id);
            console.log('Task type:', taskData.type);
            
            // Check if this task has been reported before (and not achieved)
            const hasBeenReported = taskData.last_progress_int !== null && taskData.last_report_status !== 'Achieved';
            const editAlert = document.getElementById('editModeAlert');
            
            if (hasBeenReported) {
                editAlert.style.display = 'block';
            } else {
                editAlert.style.display = 'none';
            }
            
            if (taskData.type === 'numeric') {
                targetText = taskData.target_int ? taskData.target_int : '-';
                document.getElementById('reportTypeNumeric').style.display = '';
                document.getElementById('reportTypeString').style.display = 'none';
                document.getElementById('progressPercentGroup').style.display = 'none';
                
                // Set required attributes
                document.getElementById('progressInput').required = true;
                document.getElementById('progressPercentInput').required = false;
                
                // Set previous progress if exists, otherwise empty
                if (hasBeenReported) {
                    // For numeric tasks, calculate back the actual achievement from percentage
                    const targetInt = parseInt(taskData.target_int) || 0;
                    const progressPercent = parseInt(taskData.last_progress_int) || 0;
                    const actualAchievement = targetInt > 0 ? Math.round((progressPercent * targetInt) / 100) : 0;
                    document.getElementById('progressInput').value = actualAchievement;
                } else {
                    document.getElementById('progressInput').value = '';
                }
                
                document.getElementById('progressInput').oninput = updateAutoStatus;
            } else {
                targetText = taskData.target_str ? taskData.target_str : '-';
                document.getElementById('reportTypeNumeric').style.display = 'none';
                document.getElementById('reportTypeString').style.display = '';
                
                // Set required attributes
                document.getElementById('progressInput').required = false;
                
                // Set previous status and progress if exists
                if (hasBeenReported) {
                    document.getElementById('statusSelect').value = taskData.last_report_status;
                    if (taskData.last_progress_int !== null) {
                        document.getElementById('progressPercentInput').value = taskData.last_progress_int;
                    } else {
                        document.getElementById('progressPercentInput').value = '';
                    }
                } else {
                    document.getElementById('statusSelect').value = 'In Progress';
                    document.getElementById('progressPercentInput').value = '';
                }
                
                document.getElementById('progressPercentGroup').style.display = '';
                document.getElementById('statusSelect').onchange = function() {
                    if (this.value === 'In Progress') {
                        // Show percentage input for In Progress
                        document.getElementById('progressPercentGroup').style.display = '';
                        document.getElementById('progressPercentInput').required = true;
                    } else {
                        // Hide percentage input for Achieved
                        document.getElementById('progressPercentGroup').style.display = 'none';
                        document.getElementById('progressPercentInput').required = false;
                    }
                    updateAutoStatus();
                };
                document.getElementById('progressPercentInput').oninput = updateAutoStatus;
                document.getElementById('statusSelect').dispatchEvent(new Event('change'));
            }
            
            document.getElementById('reportTaskTarget').textContent = targetText;
            
            // Set previous notes if exists
            if (hasBeenReported && taskData.last_notes) {
                document.getElementById('reportNote').value = taskData.last_notes;
            } else {
                document.getElementById('reportNote').value = '';
            }
            
            document.getElementById('autoStatus').value = '';
            updateAutoStatus();
            reportModal.show();
        }

        function updateAutoStatus() {
            console.log('Updating auto status...');
            const typeNumeric = document.getElementById('reportTypeNumeric').style.display !== 'none';
            let status = 'In Progress';
            
            if (typeNumeric) {
                const progress = parseInt(document.getElementById('progressInput').value);
                const target = parseInt(document.getElementById('reportTaskTarget').textContent);
                console.log('Numeric task - Progress:', progress, 'Target:', target);
                if (!isNaN(progress) && !isNaN(target) && progress >= target) {
                    status = 'Achieved';
                }
            } else {
                const statusSelect = document.getElementById('statusSelect').value;
                console.log('Text task - Status select:', statusSelect);
                if (statusSelect === 'Achieved') {
                    status = 'Achieved';
                } else {
                    status = 'In Progress';
                }
            }
            console.log('Auto status set to:', status);
            document.getElementById('autoStatus').value = status;
        }

        // Form validation
        document.getElementById('reportTaskForm').addEventListener('submit', function(e) {
            console.log('Form submit event triggered');
            
            // Collect all form data
            const formData = new FormData(this);
            const formObject = {};
            for (let [key, value] of formData.entries()) {
                formObject[key] = value;
            }
            console.log('Form data:', formObject);
            
            const typeNumeric = document.getElementById('reportTypeNumeric').style.display !== 'none';
            console.log('Type numeric:', typeNumeric);
            
            // Manual validation instead of HTML5 validation
            let isValid = true;
            let errorMessage = '';
            
            if (typeNumeric) {
                const progressInput = document.getElementById('progressInput');
                console.log('Progress input value:', progressInput.value);
                if (!progressInput.value || progressInput.value === '') {
                    isValid = false;
                    errorMessage = 'Harap isi field Capaian sebelum submit!';
                }
            } else {
                const statusSelect = document.getElementById('statusSelect').value;
                const progressPercentGroup = document.getElementById('progressPercentGroup').style.display !== 'none';
                console.log('Status select:', statusSelect);
                console.log('Progress percent group visible:', progressPercentGroup);
                
                if (progressPercentGroup) {
                    const progressPercentInput = document.getElementById('progressPercentInput');
                    console.log('Progress percent value:', progressPercentInput.value);
                    if (!progressPercentInput.value || progressPercentInput.value === '') {
                        isValid = false;
                        errorMessage = 'Harap isi field Persentase Capaian sebelum submit!';
                    }
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
            
            console.log('Form validation passed, submitting...');
            // Form will submit normally
        });
        </script>
</body>
</html>
