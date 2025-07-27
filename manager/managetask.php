<?php
session_start();
require_once '../config.php';

// Check access - changed to manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

// Get task data from database

$sql = "SELECT ut.*, u.name as user_name, t.name as task_name, ut.task_type, 
        (SELECT SUM(work_orders_completed) FROM task_achievements ta WHERE ta.user_task_id = ut.id) AS total_completed 
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

// Get distinct task names for filter
$sql_task_names = "SELECT DISTINCT name FROM tasks ORDER BY name";
$result_task_names = mysqli_query($conn, $sql_task_names);
$task_names = [];
if ($result_task_names) {
    while ($row = mysqli_fetch_assoc($result_task_names)) {
        $task_names[] = $row['name'];
    }
}

// Calculate statistics - Using direct queries like dashboard
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
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Task - Manager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manager/style-managetask.css" />
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
          <a href="managetask.php" class="nav-link active">
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
          <h1 class="header-title">Manage Task (Read Only)</h1>
        </div>   
        <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;">M</div>
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

        <div class="container-fluid p-4">
            <!-- Statistics Cards -->
            <div class="row mb-4 g-3">
                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-primary text-white rounded-3 p-2 me-3">
                                <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold">Total Tasks</small>
                        </div>
                        <div class="stats-value" id="totalCount"><?php echo $total_tasks; ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-warning text-white rounded-3 p-2 me-3">
                                <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold">In Progress</small>
                        </div>
                        <div class="stats-value" id="inProgressCount"><?php echo $in_progress_tasks; ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-success text-white rounded-3 p-2 me-3">
                               <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold">Achieved</small>
                        </div>
                        <div class="stats-value" id="completedCount"><?php echo $achieved_tasks; ?></div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-danger text-white rounded-3 p-2 me-3">
                                <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold">Non Achieved</small>
                        </div>
                        <div class="stats-value" id="overdueCount"><?php echo $non_achieved_tasks; ?></div>
                    </div>
                </div>
            </div>

            <!-- Task Table Section -->
            <section class="content-section p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h2 class="section-title mb-0">Task List</h2>
                    <span class="badge bg-info">Read Only View</span>
                    <!-- Removed Create Task button for read-only access -->
                </div>

                <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Search tasks..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="Achieved">Achieved</option>
                            <option value="Non Achieved">Non Achieved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="taskNameFilter">
                            <option value="">All Tasks</option>
                            <?php foreach ($task_names as $task_name): ?>
                                <option value="<?php echo htmlspecialchars($task_name); ?>"><?php echo htmlspecialchars($task_name); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="taskTable">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Employee</th>
                                    <th>Description</th>
                                    <th>Period</th>
                                    <th>Completed</th>
                                    <th>Status</th>
                                    <th>Target</th>
                                    <!-- Removed Action column for read-only access -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($tasks) > 0): ?>
                                    <?php foreach ($tasks as $task): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                                        <td><?php echo htmlspecialchars($task['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($task['description'] ?? '-'); ?></td>
                                        <td>
                                            <?php 
                                            // Display period from start_date to end_date
                                            if (!empty($task['start_date']) && !empty($task['end_date'])) {
                                                $startFormatted = date('M j, Y', strtotime($task['start_date']));
                                                $endFormatted = date('M j, Y', strtotime($task['end_date']));
                                                echo $startFormatted . ' - ' . $endFormatted;
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($task['total_completed'] ?? 0); ?></td>
                                        <td>
                                            <?php 
                                            // Status logic sama seperti admin, tapi read-only
                                            $currentDate = date('Y-m-d');
                                            $taskEndDate = !empty($task['end_date']) ? date('Y-m-d', strtotime($task['end_date'])) : null;
                                            $taskStartDate = !empty($task['start_date']) ? date('Y-m-d', strtotime($task['start_date'])) : null;
                                            $isPeriodEnded = ($taskEndDate && $currentDate > $taskEndDate);
                                            $isNotYetActive = ($taskStartDate && $currentDate < $taskStartDate);
                                            $isWithinPeriod = ($taskStartDate && $taskEndDate && $currentDate >= $taskStartDate && $currentDate <= $taskEndDate);
                                            $actualStatus = 'Not Yet Reported';
                                            $status_class = 'status-progress';
                                            if ($isNotYetActive) {
                                                $actualStatus = 'Not Yet Active';
                                                $status_class = 'status-notyetactive';
                                            } elseif ($isPeriodEnded) {
                                                $actualStatus = 'Period Passed';
                                                $status_class = 'status-passed';
                                            } else {
                                                // For active tasks, use status if available
                                                if (!empty($task['status'])) {
                                                    if ($task['status'] == 'Achieved') {
                                                        $actualStatus = 'Achieved';
                                                        $status_class = 'status-achieve';
                                                    } elseif ($task['status'] == 'Non Achieved') {
                                                        $actualStatus = 'Non Achieved';
                                                        $status_class = 'status-nonachieve';
                                                    } else {
                                                        $actualStatus = 'Not Yet Reported';
                                                        $status_class = 'status-progress';
                                                    }
                                                }
                                            }
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($actualStatus); ?></span>
<style>
    .badge.status-notyetactive {
        background: #e3f0ff;
        color: #1976d2;
        border: 1px solid #90caf9;
        font-weight: 600;
    }
    .badge.status-passed {
        background: #f8d7da;
        color: #b02a37;
        border: 1px solid #f5c2c7;
        font-weight: 600;
    }
    .badge.status-progress {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        font-weight: 600;
    }
    .badge.status-achieve {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        font-weight: 600;
    }
    .badge.status-nonachieve {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
        font-weight: 600;
    }
</style>
                                        </td>
                                        <td>
                                            <?php 
                                            // Conditional target display based on task type
                                            if ($task['task_type'] == 'numeric') {
                                                echo '<span class="badge priority-low">' . htmlspecialchars($task['target_int'] ?? '-') . '</span>';
                                            } else {
                                                echo '<span class="badge priority-low">' . htmlspecialchars($task['target_str'] ?? '-') . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <!-- Removed old Completed column to avoid duplication -->
                                        <!-- Removed action buttons for read-only access -->
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4"></i>
                                                <p class="mt-3">No tasks found</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                        <div class="d-flex align-items-center">
                            <label for="rowsPerPageSelect" class="me-2 text-muted">Show:</label>
                            <select id="rowsPerPageSelect" class="form-select w-auto">
                                <option value="5" selected>5 Rows</option>
                                <option value="10">10 Rows</option>
                                <option value="25">25 Rows</option>
                                <option value="50">50 Rows</option>
                            </select>
                        </div>
                        <nav aria-label="Table pagination">
                            <ul class="pagination custom-pagination mb-0" id="pagination"></ul>
                        </nav>
                    </div>
                </div>
                </div>
            </section>
        </div>
    </main>

    <!-- Removed Delete Confirmation Modal for read-only access -->

    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <div class="modal-icon">
                        <i class="bi bi-box-arrow-right"></i>
                    </div>
                    
                    <h5 class="modal-title-logout" id="logoutModalLabel">Confirm Logout</h5>
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
    <script src="../js/manager/managetask.js"></script>
    
    <script>
    function confirmLogout() {
        window.location.href = '../logout.php';
    }
    </script>
</body>
</html>
