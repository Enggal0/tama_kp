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

// Get user tasks with task details
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
    ut.description
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
    <title>My Tasks - Kaon Employee Dashboard</title>
    
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
                            <option value="deadline">Sort by Deadline</option>
                            <option value="priority">Sort by Priority</option>
                            <option value="status">Sort by Status</option>
                            <option value="type">Sort by Type</option>
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
                            switch ($task['status']) {
                                case 'In Progress':
                                    $statusClass = 'status-progress';
                                    $statusText = 'In Progress';
                                    break;
                                case 'Achieved':
                                    $statusClass = 'status-achieve';
                                    $statusText = 'Achieved';
                                    break;
                                case 'Non Achieved':
                                    $statusClass = 'status-nonachieve';
                                    $statusText = 'Non Achieved';
                                    break;
                            }
                            
                            $deadline = date('M j, Y', strtotime($task['deadline']));
                            
                            // Handle target display based on task type
                            $targetDisplay = '';
                            if ($task['task_type'] == 'numeric') {
                                $targetDisplay = $task['target_int'] ? 'Target: ' . $task['target_int'] : 'Target: -';
                            } else {
                                $targetDisplay = $task['target_str'] ? 'Target: ' . $task['target_str'] : 'Target: -';
                            }
                        ?>
                        <div class="task-card priority-high" data-status="<?= strtolower(str_replace(' ', '', $task['status'])) ?>" data-type="<?= htmlspecialchars($task['task_type']) ?>" data-priority="high" data-deadline="<?= $task['deadline'] ?>">
                            <div class="task-header">
                                <div>
                                    <div class="task-type"><?= htmlspecialchars($task['task_name']) ?></div>
                                    <div class="task-title"><?= htmlspecialchars($task['description']) ?></div>
                                </div>
                                <?php if ($task['status'] == 'Achieved' || $task['status'] == 'Non Achieved'): ?>
                                    <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                        <span class="status-badge <?= $statusClass ?>">
                                            <div class="status-indicator"></div>
                                            <?= $statusText ?>
                                        </span>
                                        <?php if ($task['progress_int']): ?>
                                            <div class="achieve-description">
                                                Task completed: <?= $task['progress_int'] ?>
                                            </div>
                                        <?php endif; ?>
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
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                    </svg>
                                    Due: <?= $deadline ?>
                                </div>
                                <div class="task-target"><?= $targetDisplay ?></div>
                            </div>
                            <div class="task-actions">
                                <?php if ($task['status'] == 'In Progress'): ?>
                                    <button class="task-btn btn-primary" onclick="openReportModal('<?= $task['user_task_id'] ?>', '<?= htmlspecialchars($task['task_name'], ENT_QUOTES) ?>', '<?= $task['task_type'] ?>', '<?= $task['target_int'] ? $task['target_int'] : '0' ?>', '<?= htmlspecialchars($task['target_str'], ENT_QUOTES) ?>')">Report</button>
                                <?php endif; ?>
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php?id=<?= $task['user_task_id'] ?>'">View</button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </main>
        </div>

        <!-- Report Modal -->
        <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalLabel">Submit Task Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="reportForm">
                            <input type="hidden" id="userTaskId" name="userTaskId">
                            <input type="hidden" id="taskType" name="taskType">
                            
                            <div class="mb-3">
                                <label for="taskName" class="form-label">Task Name</label>
                                <input type="text" class="form-control" id="taskName" readonly>
                            </div>
                            
                            <!-- Numeric Task Form -->
                            <div id="numericForm" style="display: none;">
                                <div class="mb-3">
                                    <label for="targetValue" class="form-label">Target Value</label>
                                    <input type="number" class="form-control" id="targetValue" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="achievedValue" class="form-label">Achieved Value</label>
                                    <input type="number" class="form-control" id="achievedValue" name="achievedValue" required>
                                </div>
                            </div>
                            
                            <!-- Text Task Form -->
                            <div id="textForm" style="display: none;">
                                <div class="mb-3">
                                    <label for="targetText" class="form-label">Target Description</label>
                                    <textarea class="form-control" id="targetText" rows="3" readonly></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="completionStatus" class="form-label">Completion Status</label>
                                    <select class="form-control" id="completionStatus" name="completionStatus">
                                        <option value="">Select Status</option>
                                        <option value="achieved">Achieved</option>
                                        <option value="in_progress">In Progress</option>
                                    </select>
                                </div>
                                <div id="progressPercentageDiv" style="display: none;">
                                    <div class="mb-3">
                                        <label for="progressPercentage" class="form-label">Progress Percentage (%)</label>
                                        <input type="number" class="form-control" id="progressPercentage" name="progressPercentage" min="0" max="100">
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="reportNotes" class="form-label">Notes (Optional)</label>
                                <textarea class="form-control" id="reportNotes" name="reportNotes" rows="3" placeholder="Add any additional notes or comments..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="submitReport()">Submit Report</button>
                    </div>
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
        <script src="../js/karyawan/mytasks.js"></script>
</body>
</html>
