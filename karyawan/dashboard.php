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
    COUNT(*) as total_tasks
FROM user_tasks WHERE user_id = ?";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Get active tasks count - count tasks that are currently within their period (today's date between start_date and end_date)
$activeQuery = "SELECT COUNT(*) as active_tasks
FROM user_tasks ut 
WHERE ut.user_id = ? 
AND CURDATE() >= ut.start_date 
AND CURDATE() <= ut.end_date";

$activeStmt = $conn->prepare($activeQuery);
$activeStmt->bind_param("i", $userId);
$activeStmt->execute();
$activeResult = $activeStmt->get_result();
$activeData = $activeResult->fetch_assoc();
$stats['active_tasks'] = $activeData['active_tasks'];

// Get achieved tasks count - count total "Achieved" status records from task_achievements
$achievedQuery = "SELECT COUNT(*) as achieved_tasks
FROM task_achievements ta 
JOIN user_tasks ut ON ta.user_task_id = ut.id
WHERE ut.user_id = ? 
AND ta.status = 'Achieved'";

$achievedStmt = $conn->prepare($achievedQuery);
$achievedStmt->bind_param("i", $userId);
$achievedStmt->execute();
$achievedResult = $achievedStmt->get_result();
$achievedData = $achievedResult->fetch_assoc();
$stats['achieved_tasks'] = $achievedData['achieved_tasks'];

// Get non-achieved tasks count - count total "Non Achieved" status records from task_achievements
$nonAchievedQuery = "SELECT COUNT(*) as non_achieved_tasks
FROM task_achievements ta 
JOIN user_tasks ut ON ta.user_task_id = ut.id
WHERE ut.user_id = ?
AND ta.status = 'Non Achieved'";

$nonAchievedStmt = $conn->prepare($nonAchievedQuery);
$nonAchievedStmt->bind_param("i", $userId);
$nonAchievedStmt->execute();
$nonAchievedResult = $nonAchievedStmt->get_result();
$nonAchievedData = $nonAchievedResult->fetch_assoc();
$stats['non_achieved_tasks'] = $nonAchievedData['non_achieved_tasks'];

// Debug query to see all achievement records
$debugQuery = "SELECT ut.id, t.name, ta.status, ta.created_at, ta.work_orders_completed
FROM task_achievements ta 
JOIN user_tasks ut ON ta.user_task_id = ut.id
JOIN tasks t ON ut.task_id = t.id
WHERE ut.user_id = ?
ORDER BY ut.id, ta.created_at DESC";

$debugStmt = $conn->prepare($debugQuery);
$debugStmt->bind_param("i", $userId);
$debugStmt->execute();
$debugResult = $debugStmt->get_result();

echo "<!-- Debug All Achievement Records: -->";
while ($debugRow = $debugResult->fetch_assoc()) {
    echo "<!-- Task ID: " . $debugRow['id'] . " | Name: " . $debugRow['name'] . " | Status: " . $debugRow['status'] . " | Created: " . $debugRow['created_at'] . " | Work Orders: " . ($debugRow['work_orders_completed'] ?? 'NULL') . " -->";
}

// Debug: Let's see what we have
echo "<!-- Debug: Total tasks: " . $stats['total_tasks'] . " -->";
echo "<!-- Debug: Active tasks (period includes today): " . $stats['active_tasks'] . " -->";
echo "<!-- Debug: Achieved records count: " . $stats['achieved_tasks'] . " -->";
echo "<!-- Debug: Non-achieved records count: " . $stats['non_achieved_tasks'] . " -->";

// Additional debug - count by status
$statusCountQuery = "SELECT ta.status, COUNT(*) as count
FROM task_achievements ta 
JOIN user_tasks ut ON ta.user_task_id = ut.id
WHERE ut.user_id = ?
GROUP BY ta.status";

$statusStmt = $conn->prepare($statusCountQuery);
$statusStmt->bind_param("i", $userId);
$statusStmt->execute();
$statusResult = $statusStmt->get_result();

echo "<!-- Status Breakdown: -->";
while ($statusRow = $statusResult->fetch_assoc()) {
    echo "<!-- Status '" . $statusRow['status'] . "': " . $statusRow['count'] . " records -->";
}

// Get latest tasks from database
$latestTasksQuery = "SELECT 
    t.name as task_name,
    ut.description,
    ut.start_date,
    ut.end_date,
    ut.target_int,
    ut.target_str,
    ut.total_completed,
    ut.status,
    (SELECT ta.status 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.created_at DESC 
     LIMIT 1) as last_status,
    (SELECT ta.work_orders_completed 
     FROM task_achievements ta 
     WHERE ta.user_task_id = ut.id 
     ORDER BY ta.created_at DESC 
     LIMIT 1) as last_work_orders_completed
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
                  <th>Period</th>
                  <th>Tasks Done</th>
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
                    // Determine task type based on target_int
                    $task_type = ($task['target_int'] > 0) ? 'numeric' : 'text';
                    
                    // Determine actual status similar to mytasks.php logic
                    $currentDate = date('Y-m-d');
                    $taskEndDate = date('Y-m-d', strtotime($task['end_date']));
                    $taskStartDate = date('Y-m-d', strtotime($task['start_date']));
                    $isPeriodEnded = ($currentDate > $taskEndDate);
                    $isWithinPeriod = ($currentDate >= $taskStartDate && $currentDate <= $taskEndDate);
                    
                    $actualStatus = 'Not Yet Reported'; // Default for active tasks
                    $statusClass = 'status-progress';
                    
                    if ($isPeriodEnded) {
                        $actualStatus = 'Period Passed';
                        $statusClass = 'status-passed';
                    } else {
                        // For active tasks, determine status based on latest report
                        if ($task['last_status']) {
                            if ($task['last_status'] == 'Achieved') {
                                $actualStatus = 'Achieved';
                                $statusClass = 'status-achieve';
                            } elseif ($task['last_status'] == 'Non Achieved') {
                                $actualStatus = 'Non Achieved';
                                $statusClass = 'status-nonachieve';
                            } else {
                                $actualStatus = 'Not Yet Reported';
                                $statusClass = 'status-progress';
                            }
                        }
                    }
                    
                    // Format period from start_date and end_date
                    $period = '';
                    if (!empty($task['start_date']) && !empty($task['end_date'])) {
                        $startFormatted = date('M j', strtotime($task['start_date']));
                        $endFormatted = date('M j, Y', strtotime($task['end_date']));
                        $period = $startFormatted . ' - ' . $endFormatted;
                    } else {
                        $period = '-';
                    }
                    
                    // Format target based on task type
                    $targetDisplay = '';
                    if ($task_type == 'numeric' && $task['target_int'] > 0) {
                      $targetDisplay = $task['target_int'] . ' work orders';
                    } else {
                      $targetDisplay = !empty($task['target_str']) ? $task['target_str'] : 'Text-based task';
                    }
                    
                    // Tasks Done display - show work_orders_completed from task_achievements
                    $tasksDoneDisplay = '';
                    if (!empty($task['last_work_orders_completed'])) {
                        $tasksDoneDisplay = $task['last_work_orders_completed'];
                    } else {
                        $tasksDoneDisplay = '0';
                    }
                    
                    // Debug output
                    echo "<!-- Task: " . $task['task_name'] . " | Period: $taskStartDate to $taskEndDate | Current: $currentDate | Status: $actualStatus | Last Status: " . ($task['last_status'] ?? 'NULL') . " -->";
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($task['task_name']) ?></td>
                    <td><?= htmlspecialchars($task['description'] ?? '-') ?></td>
                    <td><?= $period ?></td>
                    <td>
                      <span class="progress-percentage <?= intval($tasksDoneDisplay) > 0 ? 'progress-complete' : 'progress-low' ?>">
                        <?= $tasksDoneDisplay ?>
                      </span>
                    </td>
                    <td>
                      <span class="status-badge <?= $statusClass ?>">
                        <?= htmlspecialchars($actualStatus) ?>
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