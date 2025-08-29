<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$resultTotalTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_tasks");
$rowTotalTasks = mysqli_fetch_assoc($resultTotalTasks);
$total_tasks = $rowTotalTasks['total'];

$resultAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM task_achievements WHERE status = 'Achieved'");
$rowAchievedTasks = mysqli_fetch_assoc($resultAchievedTasks);
$achieved_tasks = $rowAchievedTasks['total'];

$resultNonAchievedTasks = mysqli_query($conn, "SELECT COUNT(*) AS total FROM task_achievements WHERE status = 'Non Achieved'");
$rowNonAchievedTasks = mysqli_fetch_assoc($resultNonAchievedTasks);
$non_achieved_tasks = $rowNonAchievedTasks['total'];

$today = date('Y-m-d');
$resultNotYetReportedTasks = mysqli_query($conn, "
    SELECT COUNT(*) AS total
    FROM user_tasks ut
    JOIN users u ON ut.user_id = u.id
    WHERE u.role = 'employee'
      AND ut.start_date <= '$today'
      AND ut.end_date >= '$today'
      AND NOT EXISTS (
        SELECT 1 FROM task_achievements ta
        WHERE ta.user_task_id = ut.id AND DATE(ta.created_at) = '$today'
      )
");
$rowNotYetReportedTasks = mysqli_fetch_assoc($resultNotYetReportedTasks);
$not_yet_reported_tasks = $rowNotYetReportedTasks['total'];

$achievement_rate = $total_tasks > 0 ? round(($achieved_tasks / $total_tasks) * 100) : 0;

$result_employees = mysqli_query($conn, "SELECT DISTINCT name FROM users WHERE role = 'employee' ORDER BY name");
$employees = [];
if ($result_employees) {
    while ($row = mysqli_fetch_assoc($result_employees)) {
        $employees[] = $row['name'];
    }
}
$result_tasks = mysqli_query($conn, "SELECT DISTINCT name FROM tasks ORDER BY name");
$task_types = [];
if ($result_tasks) {
    while ($row = mysqli_fetch_assoc($result_tasks)) {
        $task_types[] = $row['name'];
    }
}
$tasks_data = [];
$sql = "SELECT ut.*, u.name as user_name, t.name as task_name,
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
    <title>Statistics</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-stats.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
                    <a href="stats.php" class="nav-link active">
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
                                    <i class="bi bi-list-check"></i>
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
                                    <i class="bi bi-check-circle"></i>
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
                                    <i class="bi bi-hourglass-split"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Not Yet Reported Today</h6>
                                    <h3 class="mb-0 fw-bold text-warning"><?php echo $not_yet_reported_tasks; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-danger text-white rounded-3 p-2 me-3">
                                    <i class="bi bi-x-circle"></i>
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
                                <select class="form-select" id="employeeFilter">
                                    <option value="">All Employees</option>
                                    <?php foreach ($employees as $employee): ?>
                                        <option value="<?php echo htmlspecialchars($employee); ?>"><?php echo htmlspecialchars($employee); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="filter-card">
                                <select class="form-select" id="taskFilter">
                                    <option value="">All Tasks</option>
                                    <?php foreach ($task_types as $task_type): ?>
                                        <option value="<?php echo htmlspecialchars($task_type); ?>"><?php echo htmlspecialchars($task_type); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="d-flex">
                                <div class="filter-card input-with-icon me-2 position-relative">
                                    <input type="text" class="form-control" id="start_date" name="start_date" placeholder="Start Date" autocomplete="off">
                                    <img src="../img/calendar.png" alt="Calendar Icon" style="position:absolute; right:52px; top:50%; transform:translateY(-50%); width:18px; height:18px; pointer-events:none;">
                                    <button type="button" id="clearStartDate" class="btn btn-link p-40 m-0 position-absolute" style="right:15px; top:50%; transform:translateY(-50%); color:#888; font-size:16px;" tabindex="-1" aria-label="Clear start date"><span aria-hidden="true">&times;</span></button>
                                </div>
                                <div class="filter-card input-with-icon position-relative">
                                    <input type="text" class="form-control" id="end_date" name="end_date" placeholder="End Date" autocomplete="off">
                                    <img src="../img/calendar.png" alt="Calendar Icon" style="position:absolute; right:52px; top:50%; transform:translateY(-50%); width:18px; height:18px; pointer-events:none;">
                                    <button type="button" id="clearEndDate" class="btn btn-link p-40 m-0 position-absolute" style="right:15px; top:50%; transform:translateY(-50%); color:#888; font-size:16px;" tabindex="-1" aria-label="Clear end date"><span aria-hidden="true">&times;</span></button>
                                </div>
                            </div>
                        </div>
                    </div>
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

                        <div class="row">
                        <div class="col-12">
                            <div style="height: 500px; overflow-x: auto;">
                                <canvas id="progressChart" style="min-width: 800px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                    <div class="table-responsive">
                        <table class="table table-striped" id="progressTable">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Employee</th>
                                    <th>Total</th>
                                    <th>Achieved</th>
                                    <th>Non Achieved</th>
                                    <th>Completed</th>
                                    <th>Achievement Rate (%)</th>
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
        const taskData = <?php echo json_encode(array_map(function($row) {
            $target_value = 0;
            if ($row['task_type'] === 'numeric' && !empty($row['target_int'])) {
                $target_value = (int)$row['target_int'];
            } elseif ($row['task_type'] === 'textual' && !empty($row['target_str'])) {
                $target_value = is_numeric($row['target_str']) ? (int)$row['target_str'] : 1;
            }

            $progress_value = !empty($row['latest_progress']) ? (int)$row['latest_progress'] : (int)($row['progress_int'] ?? 0);

            $last_update = !empty($row['last_update']) ? date('Y-m-d H:i:s', strtotime($row['last_update'])) : 
                (!empty($row['updated_at']) ? date('Y-m-d H:i:s', strtotime($row['updated_at'])) : 
                date('Y-m-d H:i:s', strtotime($row['created_at'])));

            $today = date('Y-m-d');
            $last_report_date = !empty($row['last_update']) ? date('Y-m-d', strtotime($row['last_update'])) : '';
            $is_reported_today = ($last_report_date === $today);
            
            $display_status = $row['status'];
            
            if ($display_status === 'In Progress') {
                $display_status = 'Non Achieved';
            }
            
            if (!$is_reported_today && $row['status'] !== 'Achieved' && $row['status'] !== 'Non Achieved') {
                $display_status = 'Non Achieved';
            }

            return [
                'id' => $row['id'],
                'task_name' => $row['task_name'],
                'employee' => $row['user_name'],
                'description' => $row['description'] ?? '',
                'target' => $target_value,
                'progress' => $progress_value,
                'status' => strtolower($display_status),
                'start_date' => $row['start_date'] ?? '',
                'end_date' => $row['end_date'] ?? '',
                'last_update' => $last_update,
                'task_type' => $row['task_type'],
                'target_str' => $row['target_str'] ?? '',
                'target_int' => $row['target_int'] ?? 0,
                'total_completed' => $row['total_completed'] ?? 0
            ];
        }, $tasks_data)); ?>;

        const achievementStatusData = <?php
            $result = mysqli_query($conn, "
                SELECT ta.status, u.name AS employee, u.id AS user_id, t.name AS task_name, ta.created_at, ta.work_orders, ta.work_orders_completed, ta.user_task_id
                FROM task_achievements ta
                JOIN user_tasks ut ON ta.user_task_id = ut.id
                JOIN users u ON ut.user_id = u.id
                JOIN tasks t ON ut.task_id = t.id
                WHERE u.role = 'employee'
            ");
            $data = [];
            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $data[] = [
                        'status' => strtolower($row['status']),
                        'employee' => $row['employee'],
                        'user_id' => $row['user_id'],
                        'task_name' => $row['task_name'],
                        'created_at' => $row['created_at'],
                        'work_orders' => isset($row['work_orders']) ? (int)$row['work_orders'] : 0,
                        'work_orders_completed' => isset($row['work_orders_completed']) ? (int)$row['work_orders_completed'] : 0,
                        'user_task_id' => $row['user_task_id']
                    ];
                }
            }
            echo json_encode($data);
        ?>;

        flatpickr("#start_date", {
            dateFormat: "Y-m-d",
            allowInput: true,
        });
        flatpickr("#end_date", {
            dateFormat: "Y-m-d",
            allowInput: true,
        });

        document.addEventListener('DOMContentLoaded', function() {
            var startInput = document.getElementById('start_date');
            var endInput = document.getElementById('end_date');
            var clearStart = document.getElementById('clearStartDate');
            var clearEnd = document.getElementById('clearEndDate');
            if (clearStart && startInput) {
                clearStart.addEventListener('click', function(e) {
                    startInput.value = '';
                    startInput.dispatchEvent(new Event('change'));
                });
            }
            if (clearEnd && endInput) {
                clearEnd.addEventListener('click', function(e) {
                    endInput.value = '';
                    endInput.dispatchEvent(new Event('change'));
                });
            }
        });
    </script>
    <script src="../js/admin/stats.js"></script>
</body>
</html>