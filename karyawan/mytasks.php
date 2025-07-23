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

// Get user details including profile photo
$userQuery = "SELECT name, profile_photo FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userDetails = $userResult->fetch_assoc();

// Show success message if redirected after successful submission
if (isset($_GET['report']) && $_GET['report'] === 'success') {
    echo '<div id="successMessage" style="position: fixed; top: 20px; right: 20px; background: green; color: white; padding: 10px; border-radius: 5px; z-index: 9999;">Daily report submitted successfully!</div>
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

    // Ambil target saja (tanpa type karena sudah dihapus)
    $typeQuery = "SELECT ut.target_int FROM user_tasks ut WHERE ut.id = ? LIMIT 1";
    $typeStmt = $conn->prepare($typeQuery);
    $typeStmt->bind_param("i", $user_task_id);
    $typeStmt->execute();
    $typeResult = $typeStmt->get_result();
    $typeRow = $typeResult->fetch_assoc();
    $target_int = $typeRow ? (int)$typeRow['target_int'] : 0;

    error_log("Target: $target_int");

    // Karena kolom type sudah dihapus, kita tentukan berdasarkan target_int
    // Jika target_int > 0 maka numeric, jika 0 atau null maka text
    if ($target_int > 0) {
        // Untuk task numeric: progress_int = (capaian/target_int) × 100
        $capaian = isset($_POST['progress_int']) && $_POST['progress_int'] !== '' ? intval($_POST['progress_int']) : 0;
        $progress_int = ($target_int > 0) ? round(($capaian / $target_int) * 100) : 0;
        // Hanya izinkan status Achieved/Non Achieved
        if ($progress_int >= 100) {
            $auto_status = 'Achieved';
            $progress_int = 100;
        } else {
            $auto_status = 'Non Achieved';
        }
    } else {
        // Untuk task text: 
        // Ambil work orders dan work orders completed
        $work_orders = isset($_POST['work_orders']) && $_POST['work_orders'] !== '' ? intval($_POST['work_orders']) : 0;
        $work_orders_completed = isset($_POST['work_orders_completed']) && $_POST['work_orders_completed'] !== '' ? intval($_POST['work_orders_completed']) : 0;
        
        // Calculate progress percentage from work orders
        if ($work_orders > 0) {
            $progress_int = round(($work_orders_completed / $work_orders) * 100);
        } else {
            $progress_int = 0;
        }
        
        // Determine status based on completion
        if ($work_orders_completed >= $work_orders && $work_orders > 0) {
            $auto_status = 'Achieved';
            $progress_int = 100;
        } else {
            $auto_status = 'Non Achieved';
        }
        
        error_log("Work Orders: $work_orders, Completed: $work_orders_completed, Progress: $progress_int%, Status: $auto_status");
    }

    error_log("Final progress_int: $progress_int, status: $auto_status");

    // Insert ke task_achievements (daily report)
    $insertQuery = "INSERT INTO task_achievements (user_task_id, user_id, progress_int, notes, status, work_orders, work_orders_completed, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    
    if (!$insertStmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Database error: " . $conn->error);
    }
    
    // Determine work orders values based on task type
    $work_orders_value = ($target_int > 0) ? null : (isset($_POST['work_orders']) ? intval($_POST['work_orders']) : null);
    $work_orders_completed_value = ($target_int > 0) ? null : (isset($_POST['work_orders_completed']) ? intval($_POST['work_orders_completed']) : null);
    
    $insertStmt->bind_param(
        "iiissii",
        $user_task_id,
        $userId,
        $progress_int,
        $note,
        $auto_status,
        $work_orders_value,
        $work_orders_completed_value
    );
    
    if (!$insertStmt->execute()) {
        error_log("Execute failed: " . $insertStmt->error);
        die("Insert error: " . $insertStmt->error);
    }

    // Update task status only if Achieved (final status)
    if ($auto_status === 'Achieved') {
        $updateQuery = "UPDATE user_tasks SET status = 'Achieved', progress_int = 100, updated_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateQuery);
        
        if (!$updateStmt) {
            error_log("Update prepare failed: " . $conn->error);
        } else {
            $updateStmt->bind_param("i", $user_task_id);
            if (!$updateStmt->execute()) {
                error_log("Update execute failed: " . $updateStmt->error);
            }
        }
    }

    // Redirect agar tidak resubmit
    header("Location: mytasks.php?report=success");
    exit();
}

// Auto-create "Non Achieved" records for missed daily reports and update truly overdue tasks
$currentDate = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

// 1. Handle missed daily reports (within task period but didn't report yesterday)
$getMissedDailyQuery = "SELECT id, user_id, start_date, end_date FROM user_tasks 
                        WHERE user_id = ? 
                        AND status = 'In Progress' 
                        AND start_date <= ? 
                        AND end_date >= ?";

$getMissedStmt = $conn->prepare($getMissedDailyQuery);
$getMissedStmt->bind_param("iss", $userId, $yesterday, $yesterday);
$getMissedStmt->execute();
$missedResults = $getMissedStmt->get_result();

// Process each task that was active yesterday
while ($missedTask = $missedResults->fetch_assoc()) {
    $user_task_id = $missedTask['id'];
    $task_user_id = $missedTask['user_id'];
    
    // Check if achievement record exists for yesterday
    $checkYesterdayQuery = "SELECT id FROM task_achievements 
                           WHERE user_task_id = ? 
                           AND DATE(created_at) = ?";
    $checkYesterdayStmt = $conn->prepare($checkYesterdayQuery);
    $checkYesterdayStmt->bind_param("is", $user_task_id, $yesterday);
    $checkYesterdayStmt->execute();
    $yesterdayReported = $checkYesterdayStmt->get_result()->num_rows > 0;
    
    // If no report yesterday, create "Non Achieved" record for yesterday
    if (!$yesterdayReported) {
        $insertMissedQuery = "INSERT INTO task_achievements (user_task_id, user_id, progress_int, notes, status, created_at) 
                             VALUES (?, ?, ?, ?, ?, ?)";
        $insertMissedStmt = $conn->prepare($insertMissedQuery);
        $progress_int = 0;
        $notes = "Non Achieved - tidak ada laporan untuk hari ini";
        $status = "Non Achieved";
        $yesterday_datetime = $yesterday . ' 23:59:59'; // Set to end of yesterday
        $insertMissedStmt->bind_param("iiisss", $user_task_id, $task_user_id, $progress_int, $notes, $status, $yesterday_datetime);
        $insertMissedStmt->execute();
    }
}

// 2. Handle tasks that are truly overdue (past end_date)
$getOverdueQuery = "SELECT id, user_id FROM user_tasks 
                    WHERE user_id = ? 
                    AND status = 'In Progress' 
                    AND end_date < ?";

$getOverdueStmt = $conn->prepare($getOverdueQuery);
$getOverdueStmt->bind_param("is", $userId, $currentDate);
$getOverdueStmt->execute();
$overdueResults = $getOverdueStmt->get_result();

// Process each overdue task (past end_date)
while ($overdueTask = $overdueResults->fetch_assoc()) {
    $user_task_id = $overdueTask['id'];
    $task_user_id = $overdueTask['user_id'];
    
    // Check if final achievement record already exists for this overdue task
    $checkFinalQuery = "SELECT id FROM task_achievements 
                       WHERE user_task_id = ? AND notes LIKE '%melewati deadline%' 
                       ORDER BY id DESC LIMIT 1";
    $checkFinalStmt = $conn->prepare($checkFinalQuery);
    $checkFinalStmt->bind_param("i", $user_task_id);
    $checkFinalStmt->execute();
    $finalExists = $checkFinalStmt->get_result()->num_rows > 0;
    
    // Only create final achievement record if it doesn't exist
    if (!$finalExists) {
        // Insert final achievement record for overdue task
        $insertFinalQuery = "INSERT INTO task_achievements (user_task_id, user_id, progress_int, notes, status, created_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())";
        $insertFinalStmt = $conn->prepare($insertFinalQuery);
        $progress_int = 0;
        $notes = "Non Achieved karena melewati deadline";
        $status = "Non Achieved";
        $insertFinalStmt->bind_param("iiiss", $user_task_id, $task_user_id, $progress_int, $notes, $status);
        $insertFinalStmt->execute();
    }
}

// Update overdue tasks status (only for tasks past end_date)
$overdueUpdateQuery = "UPDATE user_tasks 
                      SET status = 'Non Achieved', updated_at = NOW() 
                      WHERE user_id = ? 
                      AND status = 'In Progress' 
                      AND end_date < ?";

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
    ut.start_date,
    ut.end_date,
    t.name as task_name,
    ut.description,
    (SELECT ta.id 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id AND ta.status = 'Achieved' 
     ORDER BY ta.id DESC 
     LIMIT 1) as achievement_id,
    (SELECT ta.progress_int 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.id DESC 
     LIMIT 1) as last_progress_int,
    (SELECT ta.notes 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.id DESC 
     LIMIT 1) as last_notes,
    (SELECT ta.status 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.id DESC 
     LIMIT 1) as last_report_status,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id) as total_reports
FROM user_tasks ut 
JOIN tasks t ON ut.task_id = t.id 
WHERE ut.user_id = ? 
ORDER BY ut.created_at DESC";

$tasksStmt = $conn->prepare($tasksQuery);
$tasksStmt->bind_param("i", $userId);
$tasksStmt->execute();
$tasksResult = $tasksStmt->get_result();
$userTasks = $tasksResult->fetch_all(MYSQLI_ASSOC);

// Get unique task names for filter dropdown
$taskNamesQuery = "SELECT DISTINCT t.name as task_name 
                   FROM user_tasks ut 
                   JOIN tasks t ON ut.task_id = t.id 
                   WHERE ut.user_id = ? 
                   ORDER BY t.name ASC";

$taskNamesStmt = $conn->prepare($taskNamesQuery);
$taskNamesStmt->bind_param("i", $userId);
$taskNamesStmt->execute();
$taskNamesResult = $taskNamesStmt->get_result();
$uniqueTaskNames = $taskNamesResult->fetch_all(MYSQLI_ASSOC);
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
                <?php if ($userDetails['profile_photo'] && file_exists("../uploads/profile_photos/" . $userDetails['profile_photo'])): ?>
                    <img src="../uploads/profile_photos/<?= htmlspecialchars($userDetails['profile_photo']) ?>" alt="Profile" class="user-avatar me-2" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <div class="user-avatar me-2 bg-primary"><?= $userInitials; ?></div>
                <?php endif; ?>
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
                            <button class="filter-btn" onclick="setFilter('inprogress', event)">Not Started</button>
                            <button class="filter-btn" onclick="setFilter('achieved', event)">Achieved</button>
                            <button class="filter-btn" onclick="setFilter('nonachieved', event)">Non Achieved</button>
                        </div>
                        
                        <select class="sort-select" onchange="filterByTaskName(this.value)">
                            <option value="">All Task</option>
                            <?php foreach ($uniqueTaskNames as $taskName): ?>
                                <option value="<?= htmlspecialchars($taskName['task_name'] ?? '') ?>"><?= htmlspecialchars($taskName['task_name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <select class="sort-select" onchange="sortTasks(this.value)">
                            <option value="">Sort Tasks</option>
                            <option value="name-asc">Task Name (A-Z)</option>
                            <option value="name-desc">Task Name (Z-A)</option>
                            <option value="enddate-asc">End Date (Earliest First)</option>
                            <option value="enddate-desc">End Date (Latest First)</option>
                            <option value="status-asc">Status (A-Z)</option>
                            <option value="status-desc">Status (Z-A)</option>
                        </select>
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
                            // Determine actual display status based on reporting history
                            $actualStatus = $task['status'];
                            $hasBeenReported = ($task['total_reports'] > 0);
                            
                            // If task has been reported and status is still "In Progress", 
                            // use the last report status instead
                            if ($hasBeenReported && $task['status'] == 'In Progress') {
                                $actualStatus = $task['last_report_status'] ?: 'Non Achieved';
                            }
                            
                            $statusClass = '';
                            $statusText = '';
                            $statusData = '';
                            switch ($actualStatus) {
                                case 'In Progress':
                                    $statusClass = 'status-progress';
                                    $statusText = 'Not Started';  // More accurate for tasks that have never been reported
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
                            
                            // Format period from start_date and end_date
                            $period = '';
                            if (!empty($task['start_date']) && !empty($task['end_date'])) {
                                $startFormatted = date('M j, Y', strtotime($task['start_date']));
                                $endFormatted = date('M j, Y', strtotime($task['end_date']));
                                $period = $startFormatted . ' - ' . $endFormatted;
                            } else {
                                $period = '-';
                            }
                            
                            // Check if task is overdue or period ended
                            $currentDate = date('Y-m-d');
                            $taskEndDate = date('Y-m-d', strtotime($task['end_date']));
                            $taskStartDate = date('Y-m-d', strtotime($task['start_date']));
                            $isOverdue = ($currentDate > $taskEndDate);
                            $isPeriodEnded = ($currentDate > $taskEndDate);
                            $isWithinPeriod = ($currentDate >= $taskStartDate && $currentDate <= $taskEndDate);
                            
                            // Check if already reported today (move this up for use in keterangan)
                            $todayReported = false;
                            if ($isWithinPeriod) {
                                $checkTodayQuery = "SELECT id FROM task_achievements 
                                                   WHERE user_task_id = ? 
                                                   AND user_id = ? 
                                                   AND DATE(created_at) = CURDATE()";
                                $checkTodayStmt = $conn->prepare($checkTodayQuery);
                                $checkTodayStmt->bind_param("ii", $task['user_task_id'], $userId);
                                $checkTodayStmt->execute();
                                $todayReported = $checkTodayStmt->get_result()->num_rows > 0;
                            }
                            
                            // Determine if should show overdue indicator
                            $showOverdue = false;
                            $overdueMessage = '';
                            
                            if ($isOverdue) {
                                if ($actualStatus == 'Non Achieved') {
                                    // Show overdue for Non Achieved tasks that passed end date
                                    $showOverdue = true;
                                    $overdueMessage = '(OVERDUE)';
                                } elseif ($actualStatus == 'Achieved') {
                                    // Check if achieved after end date (simplified check)
                                    $showOverdue = true;
                                    $overdueMessage = '(OVERDUE - Achieved)';
                                } elseif ($actualStatus == 'In Progress' && !$hasBeenReported) {
                                    // Show period ended for tasks that never been reported and passed end date
                                    $showOverdue = true;
                                    $overdueMessage = '(PERIOD ENDED)';
                                }
                            }
                            
                            // Tentukan task type berdasarkan target_int (karena kolom type sudah dihapus)
                            $task_type = ($task['target_int'] > 0) ? 'numeric' : 'text';
                            
                            // Handle target display based on task type
                            $targetDisplay = '';
                            if ($task_type == 'numeric') {
                                $targetDisplay = $task['target_int'] ? 'Target: ' . $task['target_int'] : 'Target: -';
                            } else {
                                $targetDisplay = $task['target_str'] ? 'Target: ' . $task['target_str'] : 'Target: -';
                            }
                        ?>
                        <div class="task-card priority-high" data-status="<?= $statusData ?>" data-type="<?= htmlspecialchars($task_type ?? '') ?>" data-priority="high" data-end-date="<?= $task['end_date'] ?>" data-task-name="<?= htmlspecialchars($task['task_name'] ?? '') ?>" <?= $isPeriodEnded && !$hasBeenReported ? 'style="opacity: 0.7; border-left: 4px solid #6c757d;"' : '' ?>>
                            <div class="task-header">
                                <div>
                                    <div class="task-title"><?= htmlspecialchars($task['task_name'] ?? '') ?></div>
                                    <?php if ($isPeriodEnded && !$hasBeenReported): ?>
                                        <small class="text-muted" style="font-style: italic; font-size: 0.8em;">
                                            <i class="bi bi-clock-history me-1"></i>Reporting period has ended
                                        </small>
                                    <?php elseif ($todayReported && $isWithinPeriod && !$hasBeenReported): ?>
                                        <small class="text-success" style="font-style: italic; font-size: 0.8em;">
                                            <i class="bi bi-check-circle me-1"></i>Already reported today
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($actualStatus == 'Achieved' || $actualStatus == 'Non Achieved'): ?>
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
                                <?= htmlspecialchars($task['description'] ?? '') ?>
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline <?= $showOverdue ? 'overdue' : '' ?>">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Period: <?= $period ?> 
                                    <?php if ($showOverdue): ?>
                                        <span class="overdue-indicator"><?= $overdueMessage ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="task-target"><?= $targetDisplay ?></div>
                            </div>
                            <div class="task-actions">
                                <?php 
                                // Show Report button logic:
                                // 1. Within period (between start_date and end_date)
                                // 2. Not reported today 
                                // 3. Task can be final status (Achieved/Non Achieved) but still allow daily reporting

                                if ($isWithinPeriod && !$todayReported) {
                                    echo '<button class="task-btn btn-primary ms-2 report-btn" 
                                          data-task-id="' . htmlspecialchars($task['user_task_id'] ?? '') . '"
                                          data-task-name="' . htmlspecialchars($task['task_name'] ?? '') . '"
                                          data-task-desc="' . htmlspecialchars($task['description'] ?? '') . '"
                                          data-target-int="' . htmlspecialchars($task['target_int'] ?? '') . '"
                                          data-target-str="' . htmlspecialchars($task['target_str'] ?? '') . '"
                                          data-task-type="' . htmlspecialchars($task_type ?? '') . '"
                                          data-task-status="' . htmlspecialchars($task['status'] ?? '') . '"
                                          data-last-progress="' . htmlspecialchars($task['last_progress_int'] ?? '') . '"
                                          data-last-notes="' . htmlspecialchars($task['last_notes'] ?? '') . '"
                                          data-last-status="' . htmlspecialchars($task['last_report_status'] ?? '') . '">
                                          Report Today</button>';
                                } elseif ($todayReported && $isWithinPeriod) {
                                    echo '<div style="display: flex; flex-direction: column; align-items: flex-start;">';
                                    echo '<small class="text-success ms-2" style="font-style: italic; font-size: 0.8em; margin-top: 2px;"><i class="bi bi-check-circle me-1"></i>Already reported today</small>';
                                    echo '</div>';
                                } elseif (!$isWithinPeriod && $currentDate < $taskStartDate) {
                                    echo '<button class="task-btn btn-outline-secondary ms-2" disabled style="opacity: 0.6; cursor: not-allowed;">Not Started</button>';
                                }
                                // No button for tasks that are past end_date - period ended, no more reporting

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
                            <h6 class="modal-title modal-title-gradient" id="reportTaskModalLabel">Daily Task Report</h6>
                            <button type="button" class="btn-close btn-close-sm btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body modal-body-compact">
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
                                    <label class="form-label-compact">Work Orders *</label>
                                    <input type="number" class="form-control form-control-compact" name="work_orders" id="workOrdersInput" min="0" step="1" placeholder="Total work orders...">
                                </div>
                                <div class="form-group-compact" id="workOrdersCompletedGroup" style="display:none;">
                                    <label class="form-label-compact">Work Orders Completed *</label>
                                    <input type="number" class="form-control form-control-compact" name="work_orders_completed" id="workOrdersCompletedInput" min="0" step="1" placeholder="Completed work orders...">
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
            // Event delegation for dynamically generated report buttons
            document.querySelectorAll('.report-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const taskData = {
                        id: btn.getAttribute('data-task-id'),
                        name: btn.getAttribute('data-task-name'),
                        description: btn.getAttribute('data-task-desc'),
                        target_int: btn.getAttribute('data-target-int'),
                        target_str: btn.getAttribute('data-target-str'),
                        type: btn.getAttribute('data-task-type'),
                        status: btn.getAttribute('data-task-status'),
                        last_progress_int: btn.getAttribute('data-last-progress'),
                        last_notes: btn.getAttribute('data-last-notes'),
                        last_report_status: btn.getAttribute('data-last-status')
                    };
                    openReportModal(taskData);
                });
            });
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
                document.getElementById('workOrdersCompletedGroup').style.display = '';
                
                // Set required attributes
                document.getElementById('progressInput').required = false;
                document.getElementById('workOrdersInput').required = true;
                document.getElementById('workOrdersCompletedInput').required = true;
                
                // Set previous work orders if exists
                if (hasBeenReported) {
                    // Set work orders from previous report if available
                    document.getElementById('workOrdersInput').value = '';
                    document.getElementById('workOrdersCompletedInput').value = '';
                } else {
                    document.getElementById('workOrdersInput').value = '';
                    document.getElementById('workOrdersCompletedInput').value = '';
                }
                
                // Event handlers for work orders inputs
                document.getElementById('workOrdersInput').oninput = updateAutoStatus;
                document.getElementById('workOrdersCompletedInput').oninput = updateAutoStatus;
            }
            
            document.getElementById('reportTaskTarget').textContent = targetText;
            
            // Always keep notes/description empty for edit mode
            document.getElementById('reportNote').value = '';
            
            document.getElementById('autoStatus').value = '';
            updateAutoStatus();
            reportModal.show();
        }

        function updateAutoStatus() {
            console.log('Updating auto status...');
            const typeNumeric = document.getElementById('reportTypeNumeric').style.display !== 'none';
            let status = 'Non Achieved';
            
            if (typeNumeric) {
                const progress = parseInt(document.getElementById('progressInput').value);
                const target = parseInt(document.getElementById('reportTaskTarget').textContent);
                console.log('Numeric task - Progress:', progress, 'Target:', target);
                if (!isNaN(progress) && !isNaN(target) && progress >= target) {
                    status = 'Achieved';
                }
            } else {
                // For textual tasks, automatically determine status based on work orders completion
                const workOrders = parseInt(document.getElementById('workOrdersInput').value) || 0;
                const workOrdersCompleted = parseInt(document.getElementById('workOrdersCompletedInput').value) || 0;
                
                console.log('Text task - Work Orders:', workOrders, 'Completed:', workOrdersCompleted);
                
                // Automatically set status based on completion ratio
                if (workOrders > 0 && workOrdersCompleted >= workOrders) {
                    status = 'Achieved';
                } else {
                    status = 'Non Achieved';
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
                const workOrders = parseInt(document.getElementById('workOrdersInput').value) || 0;
                const workOrdersCompleted = parseInt(document.getElementById('workOrdersCompletedInput').value) || 0;
                console.log('Work Orders:', workOrders, 'Completed:', workOrdersCompleted);
                
                // Validate work orders inputs
                if (!workOrders || workOrders <= 0) {
                    isValid = false;
                    errorMessage = 'Harap isi field Work Orders dengan angka yang valid!';
                } else if (!workOrdersCompleted && workOrdersCompleted !== 0) {
                    isValid = false;
                    errorMessage = 'Harap isi field Work Orders Completed!';
                } else if (workOrdersCompleted > workOrders) {
                    isValid = false;
                    errorMessage = 'Work Orders Completed tidak boleh lebih besar dari Work Orders!';
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

        // Logout function
        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        // Toggle sidebar function
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
        </script>
</body>
</html>
