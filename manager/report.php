<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

// Same database queries as admin report.php
$sql = "SELECT ta.*, u.name as user_name, t.name as task_name, t.type as task_type 
        FROM task_achievements ta 
        JOIN users u ON ta.user_id = u.id 
        JOIN tasks t ON ta.task_id = t.id 
        ORDER BY ta.achievement_date DESC";
$result = mysqli_query($conn, $sql);
$achievements = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $achievements[] = $row;
    }
}

// Get filter options
$users_sql = "SELECT DISTINCT u.id, u.name FROM users u JOIN task_achievements ta ON u.id = ta.user_id ORDER BY u.name";
$users_result = mysqli_query($conn, $users_sql);
$users = [];
if ($users_result) {
    while ($row = mysqli_fetch_assoc($users_result)) {
        $users[] = $row;
    }
}

$tasks_sql = "SELECT DISTINCT t.id, t.name FROM tasks t JOIN task_achievements ta ON t.id = ta.task_id ORDER BY t.name";
$tasks_result = mysqli_query($conn, $tasks_sql);
$tasks = [];
if ($tasks_result) {
    while ($row = mysqli_fetch_assoc($tasks_result)) {
        $tasks[] = $row;
    }
}

// Calculate summary statistics
$resultTotalAchievements = mysqli_query($conn, "SELECT COUNT(*) AS total FROM task_achievements");
$rowTotalAchievements = mysqli_fetch_assoc($resultTotalAchievements);
$total_achievements = $rowTotalAchievements['total'];

$resultAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM task_achievements WHERE status = 'Achieved'");
$rowAchievedTasks = mysqli_fetch_assoc($resultAchievedTasks);
$achieved_count = $rowAchievedTasks['total'];

$resultAvgProgress = mysqli_query($conn, "SELECT AVG(progress_percentage) AS avg_progress FROM task_achievements");
$rowAvgProgress = mysqli_fetch_assoc($resultAvgProgress);
$avg_progress = round($rowAvgProgress['avg_progress'], 1);

$achievement_rate = $total_achievements > 0 ? round(($achieved_count / $total_achievements) * 100) : 0;

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
    <title>Employee Report - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manager/style-report.css" />
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
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 616 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 715 5v1H1v-1a5 5 0 715-5z"/>
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
                    <a href="stats.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Performance Stats</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="report.php" class="nav-link active">
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
                    <h1 class="header-title">Employee Report</h1>
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
                <!-- Summary Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-card-icon">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $total_achievements ?></div>
                                <div class="stat-card-label">Total Reports</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-card-icon">
                                <i class="bi bi-check-circle"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $achieved_count ?></div>
                                <div class="stat-card-label">Achieved</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-info">
                            <div class="stat-card-icon">
                                <i class="bi bi-percent"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $avg_progress ?>%</div>
                                <div class="stat-card-label">Avg Progress</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-card-icon">
                                <i class="bi bi-graph-up"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $achievement_rate ?>%</div>
                                <div class="stat-card-label">Achievement Rate</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Report Table Section -->
                <section class="content-section p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <h2 class="section-title mb-0">Task Achievement Reports</h2>
                        <div class="text-muted small">Read-only view for Manager</div>
                    </div>

                    <!-- Filters -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search..." disabled>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="userFilter" disabled>
                                <option value="">All Employees</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= htmlspecialchars($user['id']) ?>"><?= htmlspecialchars($user['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="taskFilter" disabled>
                                <option value="">All Tasks</option>
                                <?php foreach ($tasks as $task): ?>
                                    <option value="<?= htmlspecialchars($task['id']) ?>"><?= htmlspecialchars($task['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter" disabled>
                                <option value="">All Status</option>
                                <option value="Achieved">Achieved</option>
                                <option value="Non Achieved">Non Achieved</option>
                                <option value="In Progress">In Progress</option>
                            </select>
                        </div>
                    </div>

                    <!-- Data Table -->
                    <div class="table-container">
                        <table class="table table-hover" id="reportTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Employee</th>
                                    <th>Task</th>
                                    <th>Task Type</th>
                                    <th>Target</th>
                                    <th>Progress</th>
                                    <th>Progress %</th>
                                    <th>Status</th>
                                    <th>Achievement Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($achievements)): ?>
                                    <?php foreach ($achievements as $achievement): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($achievement['id']) ?></td>
                                        <td><?= htmlspecialchars($achievement['user_name']) ?></td>
                                        <td><?= htmlspecialchars($achievement['task_name']) ?></td>
                                        <td><?= htmlspecialchars($achievement['task_type']) ?></td>
                                        <td><?= htmlspecialchars($achievement['target']) ?></td>
                                        <td><?= htmlspecialchars($achievement['progress']) ?></td>
                                        <td><?= htmlspecialchars($achievement['progress_percentage']) ?>%</td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($achievement['status']) {
                                                case 'Achieved': $statusClass = 'bg-success'; break;
                                                case 'Non Achieved': $statusClass = 'bg-danger'; break;
                                                case 'In Progress': $statusClass = 'bg-warning'; break;
                                                default: $statusClass = 'bg-secondary'; break;
                                            }
                                            ?>
                                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($achievement['status']) ?></span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($achievement['achievement_date'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No achievement data found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
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
    <script src="../js/manager/report.js"></script>
    <script>
    // Disable all interactive elements for read-only view
    window.onload = function() {
        document.querySelectorAll('input, select, textarea, button:not([data-bs-toggle])').forEach(function(el) {
            el.disabled = true;
        });
        
        // Remove any action buttons
        document.querySelectorAll('.btn-export, .btn-download, .btn-edit, .btn-delete').forEach(function(el) {
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.5';
            el.remove();
        });
    };
    
    function confirmLogout() {
        window.location.href = '../logout.php';
    }
    </script>
</body>
</html>
