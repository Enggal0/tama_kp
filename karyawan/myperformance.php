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

// Get user statistics from database - using task_achievements table like dashboard
$statsQuery = "SELECT 
    (SELECT COUNT(*) FROM user_tasks WHERE user_id = ?) as total_tasks,
    (SELECT COUNT(*) 
     FROM user_tasks ut 
     WHERE ut.user_id = ? 
     AND CURDATE() >= ut.start_date 
     AND CURDATE() <= ut.end_date) as active_tasks,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     JOIN user_tasks ut ON ta.user_task_id = ut.id 
     WHERE ut.user_id = ? 
     AND ta.status = 'Achieved') as achieved_tasks,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     JOIN user_tasks ut ON ta.user_task_id = ut.id 
     WHERE ut.user_id = ? 
     AND ta.status = 'Non Achieved') as non_achieved_tasks";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("iiii", $userId, $userId, $userId, $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Calculate success rate based on task_achievements
$totalReports = $stats['achieved_tasks'] + $stats['non_achieved_tasks'];
$successRate = ($totalReports > 0) ? round(($stats['achieved_tasks'] / $totalReports) * 100) : 0;

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

// Get detailed task performance data grouped by task name
$taskPerformanceQuery = "SELECT 
    t.name as task_name,
    ut.id as user_task_id,
    ut.target_int,
    ut.target_str,
    ut.start_date,
    ut.end_date,
    ut.created_at,
    ut.total_completed,
    ut.progress_int,
    (SELECT ta.progress_int 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.created_at DESC 
     LIMIT 1) as last_progress_int,
    (SELECT ta.created_at 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id AND ta.status = 'Achieved' 
     ORDER BY ta.created_at DESC 
     LIMIT 1) as achievement_date,
    (SELECT ta.status 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.created_at DESC 
     LIMIT 1) as last_status,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     AND ta.status = 'Achieved') as achieved_count,
    (SELECT COUNT(*) 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     AND ta.status = 'Non Achieved') as non_achieved_count
FROM user_tasks ut 
JOIN tasks t ON ut.task_id = t.id 
WHERE ut.user_id = ? 
ORDER BY t.name ASC, ut.created_at DESC";

$taskPerformanceStmt = $conn->prepare($taskPerformanceQuery);
$taskPerformanceStmt->bind_param("i", $userId);
$taskPerformanceStmt->execute();
$taskPerformanceResult = $taskPerformanceStmt->get_result();
$taskPerformanceData = [];
while ($row = $taskPerformanceResult->fetch_assoc()) {
    // Ambil semua achievements untuk user_task ini
    $achievementsQuery = "SELECT work_orders, work_orders_completed FROM task_achievements WHERE user_task_id = ? AND user_id = ?";
    $achStmt = $conn->prepare($achievementsQuery);
    $achStmt->bind_param("ii", $row['user_task_id'], $userId);
    $achStmt->execute();
    $achResult = $achStmt->get_result();
    $row['achievements'] = $achResult->fetch_all(MYSQLI_ASSOC);
    $taskPerformanceData[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Statistics - Kaon Employee Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">


    <link rel="stylesheet" href="../css/karyawan/style-performance.css" />
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
                <div class="nav-item">
                    <a href="dashboard.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="mytasks.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">My Tasks</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="myperformance.php" class="nav-link active">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Statistics</span>
                    </a>
                </div>
            </div>
        </nav>

        <main class="main-content" id="mainContent">
            <header class="header">
                <div>
                    <h1 class="header-title">Task Statistics</h1>
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
    <div class="row row-cols-5 g-4 mb-4">
    <!-- Total Tasks -->
    <div class="col">
        <div class="stats-card p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                    <div class="stats-icon bg-secondary text-white rounded-3 p-2 me-3">
                        <i class="bi bi-list-check"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-semibold">Total Tasks</small>
                </div>
                <div class="stats-value"><?= $stats['total_tasks'] ?></div>
            </div>
        </div>

        <!-- Tasks Achieved -->
        <div class="col">
        <div class="stats-card p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                    <div class="stats-icon bg-success text-white rounded-3 p-2 me-3">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-semibold">Tasks Achieved</small>
                </div>
                <div class="stats-value"><?= $stats['achieved_tasks'] ?></div>
            </div>
        </div>

        <!-- Tasks Not Achieved -->
        <div class="col">
        <div class="stats-card p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                    <div class="stats-icon bg-danger text-white rounded-3 p-2 me-3">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-semibold">Tasks Not Achieved</small>
                </div>
                <div class="stats-value"><?= $stats['non_achieved_tasks'] ?></div>
            </div>
        </div>

        <!-- Active Tasks -->
        <div class="col">
        <div class="stats-card p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                    <div class="stats-icon bg-primary text-white rounded-3 p-2 me-3">
                        <i class="bi bi-clock"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-semibold">Active Tasks</small>
                </div>
                <div class="stats-value"><?= $stats['active_tasks'] ?></div>
            </div>
        </div>

        <!-- Success Rate -->
       <div class="col">
        <div class="stats-card p-3 h-100">
                <div class="d-flex align-items-center mb-2">
                    <div class="stats-icon bg-info text-white rounded-3 p-2 me-3">
                        <i class="bi bi-percent"></i>
                    </div>
                    <small class="text-muted text-uppercase fw-semibold">Success Rate</small>
                </div>
                <div class="stats-value"><?= $successRate ?>%</div>
            </div>
        </div>
    </div>
</div>

                
                <div id="reportContent" class="pdf-export" style="display:none;">
                <!-- Summary Statistics -->
                <div class="summary-stats">
                    <div class="summary-card">
                        <div class="summary-number"><?= $stats['total_tasks'] ?></div>
                        <div class="summary-label">Total Tasks</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?= $stats['achieved_tasks'] ?></div>
                        <div class="summary-label">Tasks Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?= $stats['non_achieved_tasks'] ?></div>
                        <div class="summary-label">Tasks Not Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?= $stats['active_tasks'] ?></div>
                        <div class="summary-label">Active Tasks</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number"><?= $successRate ?>%</div>
                        <div class="summary-label">Success Rate</div>
                    </div>
                </div>
                </div>

                <!-- Controls -->
                 <div class="px-4">
                <div class="stats-header">
                    <h2 class="stats-title">Task Performance Summary</h2>
                    <p class="stats-subtitle">Summary of your task achievements</p>
                    
                    <div class="stats-controls">
                        <select class="filter-select" id="taskFilter" onchange="filterByTask()">
                            <option value="all">All Tasks</option>
                            <?php foreach ($uniqueTaskNames as $taskName): ?>
                                <option value="<?= htmlspecialchars($taskName['task_name']) ?>"><?= htmlspecialchars($taskName['task_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button class="download-btn" onclick="downloadStatistics()">
                            <i class="bi bi-download"></i>
                            Download Report
                        </button>
                    </div>
                </div>

                <!-- Individual Task Statistics -->
                <div class="stats-grid" id="statsGrid">
                    <!-- Task cards will be populated by JavaScript -->
                </div>

                <!-- Task Statistics Chart by Name -->
                <div class="chart-container">
                    <h3 class="chart-title">Task Statistics</h3>
                    <div class="chart-wrapper">
                        <canvas id="taskStatsChart"></canvas>
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
                            </div>
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Pass PHP data to JavaScript
        window.taskPerformanceData = <?= json_encode($taskPerformanceData) ?>;
        window.statsData = {
            total_tasks: <?= $stats['total_tasks'] ?>,
            achieved_tasks: <?= $stats['achieved_tasks'] ?>,
            non_achieved_tasks: <?= $stats['non_achieved_tasks'] ?>,
            active_tasks: <?= $stats['active_tasks'] ?>,
            success_rate: <?= $successRate ?>
        };
        
        function confirmLogout() {
            window.location.href = '../logout.php';
        }
        
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
        }
    </script>
    <script src="../js/karyawan/performance.js"></script>
</body>
</html>
