<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

// Same database queries as admin stats.php
$sql = "SELECT ut.*, u.name as user_name, t.name as task_name, t.type as task_type 
        FROM user_tasks ut 
        JOIN users u ON ut.user_id = u.id 
        JOIN tasks t ON ut.task_id = t.id 
        ORDER BY ut.created_at DESC";
$result = mysqli_query($conn, $sql);
$tasks = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks[] = $row;
    }
}

// Calculate statistics
$resultTotalTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks");
$rowTotalTasks = mysqli_fetch_assoc($resultTotalTasks);
$total_tasks = $rowTotalTasks['total'];

$resultAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Achieved'");
$rowAchievedTasks = mysqli_fetch_assoc($resultAchievedTasks);
$achieved_tasks = $rowAchievedTasks['total'];

$resultNonAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Non Achieved'");
$rowNonAchievedTasks = mysqli_fetch_assoc($resultNonAchievedTasks);
$non_achieved_tasks = $rowNonAchievedTasks['total'];

$resultInProgressTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'In Progress'");
$rowInProgressTasks = mysqli_fetch_assoc($resultInProgressTasks);
$in_progress_tasks = $rowInProgressTasks['total'];

$achievement_rate = $total_tasks > 0 ? round(($achieved_tasks / $total_tasks) * 100) : 0;

// Get task achievements data grouped by employee
$sql_achievements = "SELECT u.name as employee_name, 
                            COUNT(ut.id) as total_tasks,
                            SUM(CASE WHEN ut.status = 'Achieved' THEN 1 ELSE 0 END) as achieved_tasks,
                            AVG(CASE WHEN ut.status = 'Achieved' THEN ut.progress_int ELSE 0 END) as avg_progress,
                            (SUM(CASE WHEN ut.status = 'Achieved' THEN 1 ELSE 0 END) / COUNT(ut.id)) * 100 as achievement_rate
                     FROM user_tasks ut 
                     JOIN users u ON ut.user_id = u.id 
                     GROUP BY ut.user_id, u.name
                     ORDER BY achievement_rate DESC";
$result_achievements = mysqli_query($conn, $sql_achievements);
$achievements = [];
if ($result_achievements) {
    while ($row = mysqli_fetch_assoc($result_achievements)) {
        $achievements[] = $row;
    }
}

// Ambil nama dari session
$userName = $_SESSION['user_name'] ?? 'Manager';

// Fungsi untuk ambil inisial dari nama
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

$userInitials = getInitials($userName);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Performance Stats - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manager/style-stats.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()">
        <img src="burger.png" alt="Menu" />
    </button>

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
                    <a href="manageaccount.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 715 5v1H1v-1a5 5 0 715-5z"/>
                        </svg>
                        <span class="nav-text">Manage Account</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="managetask.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">Manage Task</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="stats.php" class="nav-link active">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Performance Stats</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="report.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">Employee Report</span>
                    </a>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <header class="header">
                <div>
                    <h1 class="header-title">Performance Statistics</h1>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;"><?= $userInitials ?></div>
                            <span class="fw-semibold" style="color: #000000;"><?= htmlspecialchars($userName) ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
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
                <!-- Stats Overview -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-card-icon">
                                <i class="bi bi-clipboard-check"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $total_tasks ?></div>
                                <div class="stat-card-label">Total Tasks</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-card-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $achieved_tasks ?></div>
                                <div class="stat-card-label">Achieved</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-danger">
                            <div class="stat-card-icon">
                                <i class="bi bi-x-circle"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $non_achieved_tasks ?></div>
                                <div class="stat-card-label">Non Achieved</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-card-icon">
                                <i class="bi bi-percent"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $achievement_rate ?>%</div>
                                <div class="stat-card-label">Achievement Rate</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h5 class="chart-title">Task Status Distribution</h5>
                                <div class="text-muted small">Read-only view for Manager</div>
                            </div>
                            <div class="chart-body">
                                <canvas id="statusChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="chart-card">
                            <div class="chart-header">
                                <h5 class="chart-title">Employee Progress</h5>
                                <div class="text-muted small">Read-only view for Manager</div>
                            </div>
                            <div class="chart-body">
                                <canvas id="progressChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Achievement Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-card">
                            <div class="table-header">
                                <h5 class="table-title">Employee Achievement Summary</h5>
                                <div class="text-muted small">Read-only view for Manager</div>
                            </div>
                            <div class="table-body">
                                <table class="table table-hover" id="achievementTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Employee Name</th>
                                            <th>Total Tasks</th>
                                            <th>Achieved Tasks</th>
                                            <th>Achievement Rate</th>
                                            <th>Average Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($achievements)): ?>
                                            <?php foreach ($achievements as $achievement): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($achievement['employee_name']) ?></td>
                                                <td><?= $achievement['total_tasks'] ?></td>
                                                <td><?= $achievement['achieved_tasks'] ?></td>
                                                <td>
                                                    <span class="badge <?= $achievement['achievement_rate'] >= 80 ? 'bg-success' : ($achievement['achievement_rate'] >= 60 ? 'bg-warning' : 'bg-danger') ?>">
                                                        <?= round($achievement['achievement_rate']) ?>%
                                                    </span>
                                                </td>
                                                <td><?= round($achievement['avg_progress'], 1) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No achievement data found</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

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
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manager/stats.js"></script>
    <script>
    // Pass PHP data to JavaScript
    const tasksData = {
        achieved: <?= $achieved_tasks ?>,
        nonAchieved: <?= $non_achieved_tasks ?>,
        inProgress: <?= $in_progress_tasks ?>
    };

    const achievementsData = <?= json_encode($achievements) ?>;

    // Initialize charts
    document.addEventListener('DOMContentLoaded', function() {
        initializeCharts();
        
        // Disable all interactive elements for read-only view
        document.querySelectorAll('input, select, textarea, button:not([data-bs-toggle])').forEach(function(el) {
            el.disabled = true;
        });
    });

    function initializeCharts() {
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Achieved', 'Non Achieved', 'In Progress'],
                datasets: [{
                    data: [tasksData.achieved, tasksData.nonAchieved, tasksData.inProgress],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Progress Chart
        const progressCtx = document.getElementById('progressChart').getContext('2d');
        new Chart(progressCtx, {
            type: 'bar',
            data: {
                labels: achievementsData.map(a => a.employee_name),
                datasets: [{
                    label: 'Achievement Rate (%)',
                    data: achievementsData.map(a => a.achievement_rate),
                    backgroundColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
    }
    
    function confirmLogout() {
        window.location.href = '../logout.php';
    }
    </script>
</body>
</html>
