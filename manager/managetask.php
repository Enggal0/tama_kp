<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';

unset($_SESSION['error_message']);
unset($_SESSION['success_message']);

$sql = "SELECT ut.*, u.name as user_name, t.name as task_name,
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

$sql_task_names = "SELECT DISTINCT name FROM tasks ORDER BY name";
$result_task_names = mysqli_query($conn, $sql_task_names);
$task_names = [];
if ($result_task_names) {
    while ($row = mysqli_fetch_assoc($result_task_names)) {
        $task_names[] = $row['name'];
    }
}

$resultTotalTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks");
$rowTotalTasks = mysqli_fetch_assoc($resultTotalTasks);
$total_tasks = $rowTotalTasks['total'];

$resultActiveTasks = mysqli_query($conn, "SELECT COUNT(*) AS total 
FROM user_tasks ut 
WHERE CURDATE() >= ut.start_date 
AND CURDATE() <= ut.end_date");
$rowActiveTasks = mysqli_fetch_assoc($resultActiveTasks);
$active_tasks = $rowActiveTasks['total'];

$resultAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total 
FROM task_achievements ta 
JOIN user_tasks ut ON ta.user_task_id = ut.id 
WHERE ta.status = 'Achieved'");
$rowAchievedTasks = mysqli_fetch_assoc($resultAchievedTasks);
$achieved_tasks = $rowAchievedTasks['total'];

$resultNonAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total 
FROM task_achievements ta 
JOIN user_tasks ut ON ta.user_task_id = ut.id 
WHERE ta.status = 'Non Achieved'");
$rowNonAchievedTasks = mysqli_fetch_assoc($resultNonAchievedTasks);
$non_achieved_tasks = $rowNonAchievedTasks['total'];

$achievement_rate = $total_tasks > 0 ? round(($achieved_tasks / $total_tasks) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Task</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-managetask.css" />
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
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            <span class="nav-text">Account Overview</span>
          </a>
        </div>

        <div class="nav-item">
          <a href="managetask.php" class="nav-link active">
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
          <h1 class="header-title">Employee Task</h1>
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

        <div class="container-fluid p-4">
            <?php if ($error_message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success_message): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <?php echo htmlspecialchars($success_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            
            <div class="row g-4 mb-4">
                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon stats-icon-total-task text-white rounded-3 p-2 me-3">
                                <i class="bi bi-list-check"></i>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold">Total Tasks</small>
                        </div>
                        <div class="stats-value" id="totalCount"><?php echo $total_tasks; ?></div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="stats-card p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="stats-icon bg-primary text-white rounded-3 p-2 me-3">
                                <i class="bi bi-clock"></i>
                            </div>
                            <small class="text-muted text-uppercase fw-semibold">Active Tasks</small>
                        </div>
                        <div class="stats-value" id="activeCount"><?php echo $active_tasks; ?></div>
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
                        <div class="stats-value" id="completedCount"><?php echo $achieved_tasks; ?></div>
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
                        <div class="stats-value" id="overdueCount"><?php echo $non_achieved_tasks; ?></div>
                    </div>
                </div>
            </div>

            <section class="content-section p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                    <h2 class="section-title mb-0">Task List</h2>
                </div>

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
                            <option value="Not Yet Reported">Not Yet Reported</option>
                            <option value="Period Passed">Period Passed</option>
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
                                    <th>Progress (%)</th>
                                    <th>Status</th>
                                    <th>Target</th>
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
                                        <td>
                                            <?php 
                                            
                                            if (!empty($task['last_work_orders_completed'])) {
                                                echo htmlspecialchars($task['last_work_orders_completed']);
                                            } else {
                                                echo htmlspecialchars($task['progress_int'] ?? 0);
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php 
                                            
                                            $currentDate = date('Y-m-d');
                                            $taskEndDate = date('Y-m-d', strtotime($task['end_date']));
                                            $taskStartDate = date('Y-m-d', strtotime($task['start_date']));
                                            $isPeriodEnded = ($currentDate > $taskEndDate);
                                            $isNotYetActive = ($currentDate < $taskStartDate);
                                            $isWithinPeriod = ($currentDate >= $taskStartDate && $currentDate <= $taskEndDate);
                                            $actualStatus = 'Not Yet Reported'; 
                                            $status_class = 'status-progress';
                                            if ($isNotYetActive) {
                                                $actualStatus = 'Not Yet Active';
                                                $status_class = 'status-notyetactive';
                                            } elseif ($isPeriodEnded) {
                                                $actualStatus = 'Period Passed';
                                                $status_class = 'status-passed';
                                            } else {
                                                
                                                if ($task['last_status']) {
                                                    if ($task['last_status'] == 'Achieved') {
                                                        $actualStatus = 'Achieved';
                                                        $status_class = 'status-achieve';
                                                    } elseif ($task['last_status'] == 'Non Achieved') {
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
                                        </td>
                                        <td>
                                            <?php 
                                            $task_type = ($task['target_int'] > 0) ? 'numeric' : 'text';
                                            if ($task_type == 'numeric') {
                                                echo '<span class="badge priority-low">' . htmlspecialchars($task['target_int'] ?? '-') . '</span>';
                                            } else {
                                                echo '<span class="badge priority-low">' . htmlspecialchars($task['target_str'] ?? '-') . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="bi bi-inbox display-4"></i>
                                                <p class="mt-3">No tasks found</p>
                                                <p class="small">Start by creating a new task</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

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
    
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header border-0">
            <div class="w-100">
            <div class="modal-icon">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <h5 class="modal-title" id="deleteModalLabel">Delete Confirmation</h5>
            <p class="modal-subtitle mb-0">This action cannot be undone</p>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p class="mb-3">Are you sure you want to delete task <strong id="deleteTaskName">this task</strong>?<br>Deleted data cannot be recovered.</p>
            <div class="alert alert-warning" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Warning:</strong> All data associated with this task will be permanently removed.
            </div>
        </div>
        <div class="modal-footer border-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Cancel
            </button>
            <button type="button" class="btn btn-delete" onclick="confirmDelete()">
            <i class="bi bi-trash me-1"></i>Delete
            </button>
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
    <script src="../js/admin/managetask.js"></script>
</body>
</html>