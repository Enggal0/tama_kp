<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

// Get statistics from database - same as admin
$resultTotalUsers = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role IN ('employee', 'manager')");
$rowTotalUsers = mysqli_fetch_assoc($resultTotalUsers);
$total_users = $rowTotalUsers['total'];

$resultTotalTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks");
$rowTotalTasks = mysqli_fetch_assoc($resultTotalTasks);
$total_tasks = $rowTotalTasks['total'];

$resultAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Achieved'");
$rowAchievedTasks = mysqli_fetch_assoc($resultAchievedTasks);
$achieved_tasks = $rowAchievedTasks['total'];

$achievement_rate = $total_tasks > 0 ? round(($achieved_tasks / $total_tasks) * 100) : 0;

// Get recent activities
$recentActivitiesQuery = "SELECT ut.*, u.name as user_name, t.name as task_name, ut.updated_at
                         FROM user_tasks ut 
                         JOIN users u ON ut.user_id = u.id 
                         JOIN tasks t ON ut.task_id = t.id 
                         ORDER BY ut.updated_at DESC LIMIT 10";
$recentResult = mysqli_query($conn, $recentActivitiesQuery);
$recentActivities = [];
if ($recentResult) {
    while ($row = mysqli_fetch_assoc($recentResult)) {
        $recentActivities[] = $row;
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
    <title>Dashboard Manager - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manager/style-dashboard.css" />
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()">
        <img src="burger.png" alt="Menu" />
    </button>

  <div class="dashboard-container">
    <!-- Sidebar -->
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
          <a href="manageaccount.php" class="nav-link">
            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            <span class="nav-text">Employee</span>
          </a>
        </div>

        <div class="nav-item">
          <a href="managetask.php" class="nav-link">
            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
            </svg>
            <span class="nav-text">Employee Task</span>
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
                    <a href="report.php" class="nav-link" data-section="reports" onclick="showSection('reports')">
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
          <h1 class="header-title">Dashboard</h1>
        </div>   
        <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;"><?= $userInitials ?></div>
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
            <div class="content">
                <!-- Dashboard Overview -->
                <div class="overview-cards">
                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                <!-- User icon -->
                                <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                            </div>
                            <div class="card-info">
                                <div class="card-title">Total Employees</div>
                                <div class="card-value"><?= $total_users ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon secondary">
                                <!-- Task icon -->
                                <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <div class="card-title">Total Tasks</div>
                                <div class="card-value"><?= $total_tasks ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon success">
                                <!-- Checkmark icon -->
                                <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <div class="card-title">Completed Tasks</div>
                                <div class="card-value"><?= $achieved_tasks ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                <!-- Bar icon -->
                                <svg width="20" height="20" fill="white" viewBox="0 0 20 20">
                                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                            <div>
                                <div class="card-title">Achievement Rate</div>
                                <div class="card-value"><?= $achievement_rate ?>%</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="content-section active">
                    <h2 class="section-title">Recent Activities</h2>
                    <div class="overflow-x-auto mt-4">
                    <div class="table-container">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Task</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recentActivities)): ?>
                                    <?php foreach ($recentActivities as $activity): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($activity['user_name']) ?></td>
                                        <td><?= htmlspecialchars($activity['task_name']) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($activity['status']) {
                                                case 'Achieved': $statusClass = 'status-achieve'; break;
                                                case 'Non Achieved': $statusClass = 'status-nonachieve'; break;
                                                case 'In Progress': $statusClass = 'status-progress'; break;
                                                default: $statusClass = 'status-progress'; break;
                                            }
                                            ?>
                                            <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($activity['status']) ?></span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($activity['updated_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent activities</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manager/dashboard.js"></script>
</script>
</body>
</html> 