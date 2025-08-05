<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../login.php");
    exit();
}

require_once('../config.php');

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2);
}

$userInitials = getInitials($_SESSION['user_name']);
$userId = $_SESSION['user_id'];
$userQuery = "SELECT name, profile_photo FROM users WHERE id = ?";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userDetails = $userResult->fetch_assoc();

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_task_id'])) {
    error_log("POST data received: " . print_r($_POST, true));
    
    $user_task_id = intval($_POST['user_task_id']);
    
    $kendala = '';
    if (isset($_POST['kendala']) && trim($_POST['kendala']) !== '') {
        if ($_POST['kendala'] === 'Other' && isset($_POST['kendala_custom']) && trim($_POST['kendala_custom']) !== '') {
            $kendala = trim($_POST['kendala_custom']);
        } else {
            $kendala = trim($_POST['kendala']);
        }
    }
    
    $auto_status = isset($_POST['auto_status']) ? trim($_POST['auto_status']) : '';
    $progress_int = 0;

    $typeQuery = "SELECT ut.target_int FROM user_tasks ut WHERE ut.id = ? LIMIT 1";
    $typeStmt = $conn->prepare($typeQuery);
    $typeStmt->bind_param("i", $user_task_id);
    $typeStmt->execute();
    $typeResult = $typeStmt->get_result();
    $typeRow = $typeResult->fetch_assoc();
    $target_int = $typeRow ? (int)$typeRow['target_int'] : 0;

    error_log("Target: $target_int");
    if ($target_int > 0) {
        $work_orders_completed = isset($_POST['progress_int']) && $_POST['progress_int'] !== '' ? intval($_POST['progress_int']) : 0;
        $progress_int = ($target_int > 0) ? round(($work_orders_completed / $target_int) * 100) : 0;
        
        if ($work_orders_completed >= $target_int) {
            $auto_status = 'Achieved';
            $progress_int = 100;
        } else {
            $auto_status = 'Non Achieved';
        }
        
        error_log("Numeric Task - Target: $target_int, Completed: $work_orders_completed, Progress: $progress_int%, Status: $auto_status");
    } else {
        $work_orders = isset($_POST['work_orders']) && $_POST['work_orders'] !== '' ? intval($_POST['work_orders']) : 0;
        $work_orders_completed = isset($_POST['work_orders_completed']) && $_POST['work_orders_completed'] !== '' ? intval($_POST['work_orders_completed']) : 0;
        
        if ($work_orders > 0) {
            $progress_int = round(($work_orders_completed / $work_orders) * 100);
        } else {
            $progress_int = 0;
        }
        if ($work_orders_completed >= $work_orders && $work_orders > 0) {
            $auto_status = 'Achieved';
            $progress_int = 100;
        } else {
            $auto_status = 'Non Achieved';
        }
        
        error_log("Work Orders: $work_orders, Completed: $work_orders_completed, Progress: $progress_int%, Status: $auto_status");
    }

    error_log("Final progress_int: $progress_int, status: $auto_status");

    $insertQuery = "INSERT INTO task_achievements (user_task_id, user_id, progress_int, kendala, status, work_orders, work_orders_completed, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertQuery);
    
    if (!$insertStmt) {
        error_log("Prepare failed: " . $conn->error);
        die("Database error: " . $conn->error);
    }
    
    if ($target_int > 0) {
        $work_orders_value = $target_int;
        $work_orders_completed_value = isset($_POST['progress_int']) ? intval($_POST['progress_int']) : 0;
    } else {
        $work_orders_value = isset($_POST['work_orders']) ? intval($_POST['work_orders']) : null;
        $work_orders_completed_value = isset($_POST['work_orders_completed']) ? intval($_POST['work_orders_completed']) : null;
    }
    
    $insertStmt->bind_param(
        "iiissii",
        $user_task_id,
        $userId,
        $progress_int,
        $kendala,
        $auto_status,
        $work_orders_value,
        $work_orders_completed_value
    );
    
    if (!$insertStmt->execute()) {
        error_log("Execute failed: " . $insertStmt->error);
        die("Insert error: " . $insertStmt->error);
    }
    
    $updateTotalAndAvgQuery = "UPDATE user_tasks 
        SET total_completed = (
            SELECT COALESCE(SUM(work_orders_completed), 0) FROM task_achievements WHERE user_task_id = ?
        ),
        progress_int = (
            SELECT COALESCE(ROUND(AVG(progress_int)), 0) FROM task_achievements WHERE user_task_id = ?
        ),
        updated_at = NOW()
        WHERE id = ?";
    $updateTotalAndAvgStmt = $conn->prepare($updateTotalAndAvgQuery);
    if (!$updateTotalAndAvgStmt) {
        error_log("Update total/avg prepare failed: " . $conn->error);
    } else {
        $updateTotalAndAvgStmt->bind_param("iii", $user_task_id, $user_task_id, $user_task_id);
        if (!$updateTotalAndAvgStmt->execute()) {
            error_log("Update total/avg execute failed: " . $updateTotalAndAvgStmt->error);
        }
    }
    
    header("Location: mytasks.php?report=success");
    exit();
}

$currentDate = date('Y-m-d');
$yesterday = date('Y-m-d', strtotime('-1 day'));

$statsQuery = "SELECT 
    (SELECT COUNT(DISTINCT ut.id) 
     FROM user_tasks ut 
     WHERE ut.user_id = ? 
     AND ut.start_date <= CURDATE() 
     AND ut.end_date >= CURDATE()) as active_tasks,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     JOIN user_tasks ut ON ta.user_task_id = ut.id 
     WHERE ut.user_id = ? 
     AND ta.status = 'Achieved') as achieved_tasks,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     JOIN user_tasks ut ON ta.user_task_id = ut.id 
     WHERE ut.user_id = ? 
     AND ta.status = 'Non Achieved') as non_achieved_tasks,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     JOIN user_tasks ut ON ta.user_task_id = ut.id 
     WHERE ut.user_id = ?) as total_reports";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iiii", $userId, $userId, $userId, $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

$achievementRate = ($stats['total_reports'] > 0) ? round(($stats['achieved_tasks'] / $stats['total_reports']) * 100) : 0;

$tasksQuery = "SELECT 
    ut.id as user_task_id,
    ut.total_completed,
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
    (SELECT ta.kendala 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.id DESC 
     LIMIT 1) as last_kendala,
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
    <title>My Tasks</title>
    
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
                            <button class="filter-btn" onclick="setFilter('inprogress', event)">Not Yet Reported</button>
                            <button class="filter-btn" onclick="setFilter('achieved', event)">Achieved</button>
                            <button class="filter-btn" onclick="setFilter('nonachieved', event)">Non Achieved</button>
                            <button class="filter-btn" onclick="setFilter('passed', event)">Period Passed</button>
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
                    </div>
                </div>
                
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
                        <?php
                        $sortedTasks = [];
                        $activeTasks = [];
                        $notYetActiveTasks = [];
                        $periodPassedTasks = [];
                        foreach ($userTasks as $task) {
                            $currentDate = date('Y-m-d');
                            $taskEndDate = date('Y-m-d', strtotime($task['end_date']));
                            $taskStartDate = date('Y-m-d', strtotime($task['start_date']));
                            $isPeriodEnded = ($currentDate > $taskEndDate);
                            $isWithinPeriod = ($currentDate >= $taskStartDate && $currentDate <= $taskEndDate);
                            $isNotYetActive = ($currentDate < $taskStartDate);
                            $todayReported = false;
                            if ($isWithinPeriod) {
                                $checkTodayQuery = "SELECT id FROM task_achievements 
                                                   WHERE user_task_id = ? 
                                                   AND user_id = ? 
                                                   AND DATE(created_at) = CURDATE()
                                                   AND (notes IS NULL OR notes NOT LIKE 'Auto-update:%')
                                                   AND (notes IS NULL OR notes NOT LIKE '%Period ended%')";
                                $checkTodayStmt = $conn->prepare($checkTodayQuery);
                                $checkTodayStmt->bind_param("ii", $task['user_task_id'], $userId);
                                $checkTodayStmt->execute();
                                $todayReported = $checkTodayStmt->get_result()->num_rows > 0;
                            }
                            if ($isWithinPeriod) {
                                if (!$todayReported) {
                                    $activeTasks[] = $task;
                                } else {
                                    $activeTasks[] = $task;
                                }
                            } elseif ($isNotYetActive) {
                                $notYetActiveTasks[] = $task;
                            } elseif ($isPeriodEnded) {
                                $periodPassedTasks[] = $task;
                            }
                        }
                        
                        $sortedTasks = array_merge($activeTasks, $notYetActiveTasks, $periodPassedTasks);
                        foreach ($sortedTasks as $task):
                            $hasBeenReported = ($task['total_reports'] > 0);
                            $currentDate = date('Y-m-d');
                            $taskEndDate = date('Y-m-d', strtotime($task['end_date']));
                            $taskStartDate = date('Y-m-d', strtotime($task['start_date']));
                            $isPeriodEnded = ($currentDate > $taskEndDate);
                            $isWithinPeriod = ($currentDate >= $taskStartDate && $currentDate <= $taskEndDate);
                            $isNotYetActive = ($currentDate < $taskStartDate);
                            
                            $todayReported = false;
                            if ($isWithinPeriod) {
                                $checkTodayQuery = "SELECT id FROM task_achievements 
                                                   WHERE user_task_id = ? 
                                                   AND user_id = ? 
                                                   AND DATE(created_at) = CURDATE()
                                                   AND (notes IS NULL OR notes NOT LIKE 'Auto-update:%')
                                                   AND (notes IS NULL OR notes NOT LIKE '%Period ended%')";
                                $checkTodayStmt = $conn->prepare($checkTodayQuery);
                                $checkTodayStmt->bind_param("ii", $task['user_task_id'], $userId);
                                $checkTodayStmt->execute();
                                $todayReported = $checkTodayStmt->get_result()->num_rows > 0;
                            }
                            
                            $actualStatus = 'In Progress';
                            $finalAchievementStatus = '';
                            $achievementPercentage = 0;

                            if ($isNotYetActive) {
                                $actualStatus = 'Not Yet Active';
                            } elseif ($isPeriodEnded) {
                                $startDate = new DateTime($task['start_date']);
                                $endDate = new DateTime($task['end_date']);
                                $durationDays = $endDate->diff($startDate)->days + 1;
                                if ($task['target_int'] > 0) {
                                    $achievementPercentage = round($task['last_progress_int'] / $durationDays, 1);
                                    $finalAchievementStatus = ($task['total_completed'] >= $task['target_int']) ? 'Achieved' : 'Non Achieved';
                                } else {
                                    $finalAchievementStatus = ($task['achievement_id'] !== null) ? 'Achieved' : 'Non Achieved';
                                    $achievementPercentage = ($task['achievement_id'] !== null) ? 100 : 0;
                                    $durationDays = 1; 
                                }
                                
                                $actualStatus = 'Passed';
            } else {
                if ($isWithinPeriod && $todayReported) {
                    $getTodayStatusQuery = "SELECT status FROM task_achievements 
                                           WHERE user_task_id = ? AND user_id = ? 
                                           AND DATE(created_at) = CURDATE()
                                           AND (notes IS NULL OR notes NOT LIKE 'Auto-update:%')
                                           AND (notes IS NULL OR notes NOT LIKE '%Period ended%')
                                           ORDER BY created_at DESC LIMIT 1";
                    $getTodayStatusStmt = $conn->prepare($getTodayStatusQuery);
                    $getTodayStatusStmt->bind_param("ii", $task['user_task_id'], $userId);
                    $getTodayStatusStmt->execute();
                    $todayStatusResult = $getTodayStatusStmt->get_result();
                    if ($todayStatusRow = $todayStatusResult->fetch_assoc()) {
                        $actualStatus = $todayStatusRow['status'];
                    } else {
                        $actualStatus = 'In Progress'; 
                    }
                } else {
                    $actualStatus = 'In Progress';
                }
                $durationDays = 0; 
            }               $statusClass = '';
                            $statusText = '';
                            $statusData = '';
                            switch ($actualStatus) {
                                case 'Not Yet Active':
                                    $statusClass = 'status-notactive';
                                    $statusText = 'Not Yet Active';
                                    $statusData = 'notactive';
                                    break;
                                case 'In Progress':
                                    $statusClass = 'status-progress';
                                    $statusText = 'Not Yet Reported';
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
                                case 'Passed':
                                    $statusClass = 'status-passed';
                                    $statusText = 'Period Passed';
                                    $statusData = 'passed';
                                    break;
                            }
                            
                            $period = '';
                            if (!empty($task['start_date']) && !empty($task['end_date'])) {
                                $startFormatted = date('M j, Y', strtotime($task['start_date']));
                                $endFormatted = date('M j, Y', strtotime($task['end_date']));
                                $period = $startFormatted . ' - ' . $endFormatted;
                            } else {
                                $period = '-';
                            }
                            
                            $isOverdue = ($currentDate > $taskEndDate);
                            $showOverdue = false;
                            $overdueMessage = '';
                            
                            if ($isOverdue) {
                                if ($actualStatus == 'Non Achieved') {
                                    
                                    $showOverdue = true;
                                    $overdueMessage = '(OVERDUE)';
                                } elseif ($actualStatus == 'Achieved') {
                                    
                                    $showOverdue = true;
                                    $overdueMessage = '(OVERDUE - Achieved)';
                                } elseif ($actualStatus == 'In Progress' && !$hasBeenReported) {
                                    
                                    $showOverdue = true;
                                    $overdueMessage = '(PERIOD ENDED)';
                                }
                            }
                            
                            $task_type = ($task['target_int'] > 0) ? 'numeric' : 'text';
                            
                            $targetDisplay = '';
                            if ($task_type == 'numeric') {
                                $targetDisplay = $task['target_int'] ? 'Target: ' . $task['target_int'] : 'Target: -';
                            } else {
                                $targetDisplay = $task['target_str'] ? 'Target: ' . $task['target_str'] : 'Target: -';
                            }
                        ?>
                        <div class="task-card priority-high <?= $isPeriodEnded ? 'period-ended task-passed' : '' ?> <?= $isPeriodEnded && $finalAchievementStatus == 'Achieved' ? 'achieved' : '' ?> <?= $isPeriodEnded && $finalAchievementStatus == 'Non Achieved' ? 'non-achieved' : '' ?>" data-status="<?= $statusData ?>" data-type="<?= htmlspecialchars($task_type ?? '') ?>" data-priority="high" data-end-date="<?= $task['end_date'] ?>" data-task-name="<?= htmlspecialchars($task['task_name'] ?? '') ?>">
                            <div class="task-header">
                                <div>
                                    <div class="task-title"><?= htmlspecialchars($task['task_name'] ?? '') ?></div>
                                    <?php if ($isPeriodEnded): ?>
                                        <?php 
                                        $percentageColor = 'text-muted';
                                        if ($achievementPercentage >= 100) {
                                            $percentageColor = 'text-success';
                                        } elseif ($achievementPercentage >= 75) {
                                            $percentageColor = 'text-info';
                                        } elseif ($achievementPercentage >= 50) {
                                            $percentageColor = 'text-warning';
                                        } else {
                                            $percentageColor = 'text-danger';
                                        }
                                        ?>
                                        <small class="<?= $percentageColor ?>" style="font-style: italic; font-size: 0.8em;">
                                            <i class="bi bi-bar-chart me-1"></i>Achievement: <?= $achievementPercentage ?>%
                                            <?php if ($task['target_int'] > 0): ?>
                                                <br><span style="font-size: 0.75em; color: #8b9dc3;">
                                                    (<?= $task['last_progress_int'] ?>% ÷ <?= $durationDays ?> days)
                                                </span>
                                            <?php endif; ?>
                                        </small>
                                    <?php elseif ($todayReported && $isWithinPeriod): ?>
                                        <small class="text-success" style="font-style: italic; font-size: 0.8em;">
                                            <i class="bi bi-check-circle me-1"></i>Already reported today
                                        </small>
                                    <?php elseif (!$isWithinPeriod && $currentDate < $taskStartDate): ?>
                                        <small class="text-primary" style="font-style: italic; font-size: 0.8em;">
                                            <i class="bi bi-clock me-1"></i>Task will start on <?= date('M j, Y', strtotime($task['start_date'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                                <?php if ($actualStatus == 'Passed'): ?>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                        <span class="status-badge <?= $statusClass ?>">
                                            <div class="status-indicator"></div>
                                            <?= $statusText ?>
                                        </span>
                                    </div>
                                <?php elseif ($actualStatus == 'Achieved' || $actualStatus == 'Non Achieved'): ?>
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
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Period: <?= $period ?>
                                </div>
                                <div class="task-target"><?= $targetDisplay ?></div>
                            </div>
                            <div class="task-actions">
                                <?php 
                                if ($isWithinPeriod && !$todayReported) {
                                    echo '<button class="task-btn btn-primary ms-2 report-btn" 
                                          data-task-id="' . htmlspecialchars($task['user_task_id'] ?? '') . '"
                                          data-task-name="' . htmlspecialchars($task['task_name'] ?? '') . '"
                                          data-task-desc="' . htmlspecialchars($task['description'] ?? '') . '"
                                          data-target-int="' . htmlspecialchars($task['target_int'] ?? '') . '"
                                          data-target-str="' . htmlspecialchars($task['target_str'] ?? '') . '"
                                          data-task-type="' . htmlspecialchars($task_type ?? '') . '"
                                          data-total-completed="' . htmlspecialchars($task['total_completed'] ?? '0') . '"
                                          data-last-progress="' . htmlspecialchars($task['last_progress_int'] ?? '') . '"
                                          data-last-kendala="' . htmlspecialchars($task['last_kendala'] ?? '') . '"
                                          data-last-status="' . htmlspecialchars($task['last_report_status'] ?? '') . '">
                                          Report Today</button>';
                                } elseif ($todayReported && $isWithinPeriod) {
                                    echo '<div style="display: flex; flex-direction: column; align-items: flex-start;">';
                                    echo '</div>';
                                } elseif (!$isWithinPeriod && $currentDate < $taskStartDate) {
                                    echo '<button class="task-btn btn-outline-primary ms-2" disabled style="opacity: 0.6; cursor: not-allowed;">Not Yet Active</button>';
                                } elseif ($isPeriodEnded) {
                                    echo '<button class="task-btn btn-outline-secondary ms-2" disabled style="opacity: 0.8; cursor: not-allowed; color: #6c757d;"><i class="bi bi-clock me-1"></i>Period Passed</button>';
                                }
                                echo '<a href="view.php?id=' . $task['user_task_id'] . '" class="task-btn btn-secondary text-decoration-none">View</a>';
                                ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>
        
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
                                    <label class="form-label-compact">Work Orders Completed *</label>
                                    <input type="number" class="form-control form-control-compact" name="progress_int" id="progressInput" min="0" step="1" placeholder="Number of completed work orders...">
                                </div>
                                <div class="form-group-compact" id="reportTypeString" style="display:none;">
                                    <label class="form-label-compact">Work Orders *</label>
                                    <input type="number" class="form-control form-control-compact" name="work_orders" id="workOrdersInput" min="0" step="1" placeholder="Total work orders...">
                                </div>
                                <div class="form-group-compact" id="workOrdersCompletedGroup" style="display:none;">
                                    <label class="form-label-compact">Work Orders Completed *</label>
                                    <input type="number" class="form-control form-control-compact" name="work_orders_completed" id="workOrdersCompletedInput" min="0" step="1" placeholder="Number completed...">
                                </div>
                                <div class="form-group-compact">
                                    <label class="form-label-compact">Issues/Constraints <span class="text-muted">(optional)</span></label>
                                    <select class="form-control form-control-compact" name="kendala" id="kendalaSelect" onchange="handleKendalaChange()">
                                        <option value="">No issues</option>
                                        <option value="Tool problem">Tool problem</option>
                                        <option value="System integration issue">System integration issue</option>
                                        <option value="Database access problem">Database access problem</option>
                                        <option value="Data inconsistency">Data inconsistency</option>
                                        <option value="Data entry error">Data entry error</option>
                                        <option value="Missing data">Missing data</option>
                                        <option value="Data migration issue">Data migration issue</option>
                                        <option value="Health issue">Health issue</option>
                                        <option value="Electricity disruption ">Electricity disruption </option> 
                                        <option value="Other">Other...</option>
                                    </select>
                                    <textarea class="form-control form-control-compact mt-2" name="kendala_custom" id="kendalaCustom" rows="1" placeholder="Enter other issues..." style="display: none;"></textarea>
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
    </body>
</html>