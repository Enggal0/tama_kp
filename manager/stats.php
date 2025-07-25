<?php
require_once '../config.php';
// Statistik utama
$resultTotalTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks");
$rowTotalTasks = mysqli_fetch_assoc($resultTotalTasks);
$total_tasks = $rowTotalTasks['total'];

$resultAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Achieved'");
$rowAchievedTasks = mysqli_fetch_assoc($resultAchievedTasks);
$achieved_tasks = $rowAchievedTasks['total'];

// In Progress: tasks that have status 'In Progress'
$resultInProgressTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'In Progress'");
$rowInProgressTasks = mysqli_fetch_assoc($resultInProgressTasks);
$in_progress_tasks = $rowInProgressTasks['total'];

// Non Achieved: tasks that have status 'Non Achieved'
$resultNonAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks WHERE status = 'Non Achieved'");
$rowNonAchievedTasks = mysqli_fetch_assoc($resultNonAchievedTasks);
$non_achieved_tasks = $rowNonAchievedTasks['total'];

$achievement_rate = $total_tasks > 0 ? round(($achieved_tasks / $total_tasks) * 100) : 0;

// Get all employees (users)
$result_employees = mysqli_query($conn, "SELECT DISTINCT name FROM users WHERE role = 'employee' ORDER BY name");
$employees = [];
if ($result_employees) {
    while ($row = mysqli_fetch_assoc($result_employees)) {
        $employees[] = $row['name'];
    }
}
// Get all task types (tasks)
$result_tasks = mysqli_query($conn, "SELECT DISTINCT name FROM tasks ORDER BY name");
$task_types = [];
if ($result_tasks) {
    while ($row = mysqli_fetch_assoc($result_tasks)) {
        $task_types[] = $row['name'];
    }
}
// Ambil data detail task untuk chart dan filter
$tasks_data = [];
$sql = "SELECT ut.*, u.name as user_name, t.name as task_name, t.type as task_type,
               (SELECT progress_int FROM task_achievements 
                WHERE user_task_id = ut.id 
                ORDER BY created_at DESC LIMIT 1) as latest_progress,
               (SELECT created_at FROM task_achievements 
                WHERE user_task_id = ut.id 
                ORDER BY created_at DESC LIMIT 1) as last_update
        FROM user_tasks ut 
        JOIN users u ON ut.user_id = u.id 
        JOIN tasks t ON ut.task_id = t.id 
        WHERE u.role = 'employee'
        ORDER BY t.name ASC, u.name ASC";
$result = mysqli_query($conn, $sql);
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $tasks_data[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Statistics - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-stats.css" />
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
                            <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;">A</div>
                            <span class="fw-semibold" style="color: #000000;">Admin</span>
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
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-primary text-white rounded-3 p-2 me-3">
                                    <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Total Tasks</h6>
                                    <h3 class="mb-0 fw-bold text-primary"><?php echo $total_tasks; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-success text-white rounded-3 p-2 me-3">
                                    <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Achieved</h6>
                                    <h3 class="mb-0 fw-bold text-success"><?php echo $achieved_tasks; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-warning text-white rounded-3 p-2 me-3">
                                    <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">In Progress</h6>
                                    <h3 class="mb-0 fw-bold text-warning"><?php echo $in_progress_tasks; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-danger text-white rounded-3 p-2 me-3">
                                    <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Non-Achieved</h6>
                                    <h3 class="mb-0 fw-bold text-danger"><?php echo $non_achieved_tasks; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Filters -->
                <div class="chart-container">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="chart-title">Task Performance Analytics</h3>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="downloadReport()">
                                <i class="bi bi-download me-2"></i>Download Report
                            </button>
                            <button class="btn btn-secondary" onclick="exportChart()">
                                <i class="bi bi-file-earmark-pdf me-2"></i>Export Chart
                            </button>
                        </div>
                    </div>

                    <div class="chart-filters mb-4">
                        <div class="chart-filters mb-4">
                        <div class="filter-card">
                            <label class="form-label fw-semibold">Filter by Employee:</label>
                            <select class="form-select" id="employeeFilter" onchange="filterTasks()">
                                <option value="">All Employees</option>
                                <?php foreach ($employees as $employee): ?>
                                    <option value="<?php echo htmlspecialchars($employee); ?>"><?php echo htmlspecialchars($employee); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="filter-card">
                            <label class="form-label fw-semibold">Filter by Task Type:</label>
                            <select class="form-select" id="taskFilter" onchange="filterTasks()">
                                <option value="">All Tasks</option>
                                <?php foreach ($task_types as $task_type): ?>
                                    <option value="<?php echo htmlspecialchars($task_type); ?>"><?php echo htmlspecialchars($task_type); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Chart Area -->
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="taskChart" width="400" height="300"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="performanceChart" width="400" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <div class="chart-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="chart-title">Employee Progress by Task</h3>
        <div class="d-flex gap-2">
            <button class="btn btn-outline-primary btn-sm" onclick="toggleChartType()">
                <i class="bi bi-bar-chart me-1"></i>Toggle Chart Type
            </button>
        </div>
    </div>

    <!-- Progress Chart Filter: Only Employee -->
    <div class="chart-filters mb-4">
        <div class="filter-card">
            <label class="form-label fw-semibold">Filter By Employee:</label>
            <select class="form-select" id="progressEmployeeFilter" onchange="updateProgressChart()">
                <option value="">All Employees</option>
                <?php foreach ($employees as $employee): ?>
                    <option value="<?php echo htmlspecialchars($employee); ?>"><?php echo htmlspecialchars($employee); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    <div class="filter-card">
    <label class="form-label fw-semibold">Filter By Task:</label>
    <select class="form-select" id="progressTaskFilter" onchange="updateProgressChart()">
        <option value="">All Tasks</option>
        <?php foreach ($task_types as $task_type): ?>
            <option value="<?php echo htmlspecialchars($task_type); ?>"><?php echo htmlspecialchars($task_type); ?></option>
        <?php endforeach; ?>
    </select>
</div>
        </div>

    <!-- Progress Chart Area -->
    <div class="row">
        <div class="col-12">
            <div style="height: 500px; overflow-x: auto;">
                <canvas id="progressChart" style="min-width: 800px;"></canvas>
            </div>
        </div>
    </div>

    <!-- Progress Summary Table -->
    <div class="mt-4">
        <div class="table-responsive">
            <table class="table table-striped" id="progressTable">
                <thead>
                    <tr>
                        <th>Task Type</th>
                        <th>Employee</th>
                        <th>Description</th>
                        <th>Target</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Deadline</th>
                        <th>Last Update</th>
                    </tr>
                </thead>
                <tbody id="progressTableBody"></tbody>
            </table>
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
        </main>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <script>
    // Data dari database untuk chart dan filter
    const taskData = <?php echo json_encode(array_map(function($row) {
        // Determine target value based on task type
        $target_value = 0;
        if ($row['task_type'] === 'numeric' && !empty($row['target_int'])) {
            $target_value = (int)$row['target_int'];
        } elseif ($row['task_type'] === 'text' && !empty($row['target_str'])) {
            // For text type, try to extract number or use 1 as default
            $target_value = is_numeric($row['target_str']) ? (int)$row['target_str'] : 1;
        }
        
        // Use latest progress from task_achievements or fallback to user_tasks progress_int
        $progress_value = !empty($row['latest_progress']) ? (int)$row['latest_progress'] : (int)($row['progress_int'] ?? 0);
        
        // Format last update timestamp
        $last_update = !empty($row['last_update']) ? date('Y-m-d H:i:s', strtotime($row['last_update'])) : 
                      (!empty($row['updated_at']) ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 
                       date('Y-m-d H:i:s', strtotime($row['created_at'])));
        
        return [
            'id' => $row['id'],
            'type' => $row['task_name'],  // Task name first
            'name' => $row['user_name'],  // Employee name second
            'task_type' => $row['task_type'],
            'status' => strtolower($row['status']),  // Use actual status from database
            'completed' => $progress_value,  // Latest progress_int
            'target' => $target_value,  // Conditional target
            'task_type' => $row['task_type'],
            'target_str' => $row['target_str'] ?? '',
            'target_int' => $row['target_int'] ?? 0,
            'deadline' => $row['deadline'] ?? '',
            'description' => $row['description'] ?? '',
            'last_update' => $last_update  // Last update timestamp
        ];
    }, $tasks_data)); ?>;
    </script>
    <script src="../js/admin/stats.js"></script>
</body>
</html>
