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

// Get user statistics from database
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

// Get latest tasks from database
$latestTasksQuery = "SELECT 
    t.name as task_name,
    ut.description,
    ut.deadline,
    ut.progress_int,
    ut.status,
    ut.target_int,
    ut.target_str,
    t.type as task_type,
    (SELECT ta.progress_int 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.submitted_at DESC 
     LIMIT 1) as last_progress_int
FROM user_tasks ut
JOIN tasks t ON ut.task_id = t.id
WHERE ut.user_id = ?
ORDER BY ut.created_at DESC
LIMIT 10";

$latestTasksStmt = $conn->prepare($latestTasksQuery);
$latestTasksStmt->bind_param("i", $userId);
$latestTasksStmt->execute();
$latestTasksResult = $latestTasksStmt->get_result();
$latestTasks = [];
while ($row = $latestTasksResult->fetch_assoc()) {
    $latestTasks[] = $row;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Karyawan - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/karyawan/style-dashboard.css" />
</head>
<body>
  <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()"></button>
  <div class="d-flex vh-100">
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
          <a href="dashboard.php" class="nav-link active">
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
          <a href="myperformance.php" class="nav-link">
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
          <h1 class="header-title">Dashboard</h1>
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
      <div class="content">
        <div id="dashboard-section">
          <div class="welcome-card">
            <div class="welcome-title">Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?>!</div>
            <div class="welcome-subtitle">Today is a great day to complete your tasks</div>
          </div>
          <div class="overview-cards">
            <div class="overview-card">
              <div class="card-header">
                <div class="card-icon primary">
                    <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="card-info">
                  <div class="card-title">Active Tasks</div>
                  <div class="card-value"><?= $stats['active_tasks'] ?? 0 ?></div>
                </div>
              </div>
            </div>
            <div class="overview-card">
              <div class="card-header">
                <div class="card-icon success">
                    <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="card-info">
                  <div class="card-title">Achieved Tasks</div>
                  <div class="card-value"><?= $stats['achieved_tasks'] ?? 0 ?></div>
                </div>
              </div>
            </div>
            <div class="overview-card">
              <div class="card-header">
                <div class="card-icon warning">
                    <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 13V7a1 1 0 00-.553-.894l-7-3.5a1 1 0 00-.894 0l-7 3.5A1 1 0 002 7v6a1 1 0 00.553.894l7 3.5a1 1 0 00.894 0l7-3.5A1 1 0 0018 13zM9 9a1 1 0 012 0v2a1 1 0 11-2 0V9zm1 6a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="card-info">
                  <div class="card-title">Non Achieved Tasks</div>
                  <div class="card-value"><?= $stats['non_achieved_tasks'] ?? 0 ?></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="content-section">
          <h2 class="section-title">My Latest Tasks</h2>
          <div class="table-container">
            <table class="table table-hover table-striped">
              <thead>
                <tr>
                  <th>Task Name</th>
                  <th>Description</th>
                  <th>Deadline</th>
                  <th>Progress</th>
                  <th>Status</th>
                  <th>Target</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($latestTasks)): ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted">No tasks found</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($latestTasks as $task): 
                    $statusClass = '';
                    switch ($task['status']) {
                      case 'In Progress':
                        $statusClass = 'status-progress';
                        break;
                      case 'Achieved':
                        $statusClass = 'status-achieve';
                        break;
                      case 'Non Achieved':
                        $statusClass = 'status-nonachieve';
                        break;
                    }
                    
                    // Format deadline
                    $deadline = $task['deadline'] ? date('M j, Y', strtotime($task['deadline'])) : '-';
                    
                    // Format target based on task type
                    $targetDisplay = '';
                    if ($task['task_type'] == 'numeric') {
                      $targetDisplay = $task['target_int'] ? $task['target_int'] . ' units' : '-';
                    } else {
                      $targetDisplay = $task['target_str'] ? $task['target_str'] : '-';
                    }
                    
                    // Progress display as percentage - use data from task_achievements if available
                    $actualProgress = $task['last_progress_int'] !== null ? $task['last_progress_int'] : $task['progress_int'];
                    $progressDisplay = ($actualProgress !== null && $actualProgress !== '') ? $actualProgress . '%' : '0%';
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($task['task_name']) ?></td>
                    <td><?= htmlspecialchars($task['description'] ?? '-') ?></td>
                    <td><?= $deadline ?></td>
                    <td>
                      <span class="progress-percentage <?= $actualProgress >= 100 ? 'progress-complete' : ($actualProgress >= 50 ? 'progress-medium' : 'progress-low') ?>">
                        <?= $progressDisplay ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-badge <?= $statusClass ?>">
                        <?= htmlspecialchars($task['status']) ?>
                      </span>
                    </td>
                    <td>
                      <span class="priority-badge priority-low">
                        <?= htmlspecialchars($targetDisplay) ?>
                      </span>
                    </td>
                  </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
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
    </main>
  </div>
      <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
          <script src="../js/karyawan/dashboard.js"></script>
</body>
</html>