<?php

include '../config.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

$resultEmployee = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'employee'");
$rowEmployee = mysqli_fetch_assoc($resultEmployee);
$totalEmployees = $rowEmployee['total'];

$resultActiveTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'In Progress'");
$rowActiveTasks = mysqli_fetch_assoc($resultActiveTasks);
$totalActiveTasks = $rowActiveTasks['total'];

$resultCompletedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Achieved'");
$rowCompletedTasks = mysqli_fetch_assoc($resultCompletedTasks);
$totalCompletedTasks = $rowCompletedTasks['total'];

$resultNonAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Non Achieved'");
$rowNonAchievedTasks = mysqli_fetch_assoc($resultNonAchievedTasks);
$totalNonAchievedTasks = $rowNonAchievedTasks['total'];

$resultTotalTasks = mysqli_query($conn, "SELECT COUNT(*) AS total, AVG(progress_int) AS avg_progress FROM user_tasks");
$rowTotalTasks = mysqli_fetch_assoc($resultTotalTasks);
$totalTasks = $rowTotalTasks['total'];
$achievementRate = $totalTasks > 0 ? round($rowTotalTasks['avg_progress']) : 0;

$sqlRecentTasks = "SELECT ut.task_type, u.name as employee_name, CONCAT(DATE_FORMAT(ut.start_date, '%d %b %Y'), ' - ', DATE_FORMAT(ut.end_date, '%d %b %Y')) as period, ut.target_int, ut.target_str, t.name as task_name, ut.total_completed
                   FROM user_tasks ut
                   JOIN tasks t ON ut.task_id = t.id
                   JOIN users u ON ut.user_id = u.id
                   WHERE CURDATE() BETWEEN ut.start_date AND ut.end_date
                   ORDER BY ut.start_date DESC
                   LIMIT 10";
$resultRecentTasks = mysqli_query($conn, $sqlRecentTasks);
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-dashboard.css" />
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
                <img src="../img/tamaaa.png" alt="TAMA Logo" style="height: 100px; display: block; margin: 0; padding: 0;">
            </div>
        </div>
      </div>

      <div class="sidebar-nav">
        <div class="nav-item">
            <a href="dashboard.php" class="nav-link active">
                <i class="bi bi-grid-1x2-fill nav-icon"></i>
                <span class="nav-text">Dashboard</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="manageaccount.php" class="nav-link">
                <i class="bi bi-people-fill nav-icon"></i>
                <span class="nav-text">Manage Account</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="managetask.php" class="nav-link">
                <i class="bi bi-calendar3 nav-icon"></i>
                <span class="nav-text">Manage Task</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="stats.php" class="nav-link">
                <i class="bi bi-bar-chart-fill nav-icon"></i>
                <span class="nav-text">Performance Stats</span>
            </a>
        </div>

        <div class="nav-item">
            <a href="report.php" class="nav-link">
                <i class="bi bi-file-earmark-text-fill nav-icon"></i>
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
                            <div class="user-avatar me-2">M</div>
                            <span class="fw-semibold" style= "color: #000000;">Manager</span>
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
               <div class="overview-cards">
                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon primary">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="card-info">
                                <div class="card-title">Total Employees</div>
                                <div class="card-value"><?= $totalEmployees ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon secondary">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div>
                                <div class="card-title">Total Tasks</div>
                                <div class="card-value"><?= $totalTasks ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon success">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div>
                                <div class="card-title">Achieved Tasks</div>
                                <div class="card-value"><?= $totalCompletedTasks ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="overview-card">
                        <div class="card-header">
                            <div class="card-icon warning">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                            <div>
                                <div class="card-title">Achievement Rate</div>
                                <div class="card-value"><?= $achievementRate ?>%</div>
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
                                    <th>Task</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Target</th>
                                    <th>Total Completed</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultRecentTasks && mysqli_num_rows($resultRecentTasks) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($resultRecentTasks)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['task_name']) ?></td>
                                        <td><?= htmlspecialchars($row['employee_name']) ?></td>
                                        <td><?= htmlspecialchars($row['period']) ?></td>
                                        <td>
                                            <?php if ($row['task_type'] == 'numeric'): ?>
                                                <?= htmlspecialchars($row['target_int']) ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($row['target_str']) ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($row['total_completed']) ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No active tasks found for today</td>
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
    <script src="../js/admin/dashboard.js"></script>
</body>
</html>