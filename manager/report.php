<?php
require_once '../config.php';

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

$result_tasks = mysqli_query($conn, "SELECT DISTINCT name FROM tasks ORDER BY name");
$task_types = [];
if ($result_tasks) {
    while ($row = mysqli_fetch_assoc($result_tasks)) {
        $task_types[] = $row['name'];
    }
}

$sql = "SELECT ta.*, u.name AS user_name, ut.start_date, ut.end_date, ut.task_id, t.name AS task_name, 
               ta.status as task_status, ta.work_orders, ta.work_orders_completed
        FROM task_achievements ta
        JOIN users u ON ta.user_id = u.id
        JOIN user_tasks ut ON ta.user_task_id = ut.id
        JOIN tasks t ON ut.task_id = t.id
        WHERE u.role = 'employee'
        AND ta.created_at IN (
            SELECT MAX(created_at) FROM task_achievements 
            WHERE user_task_id = ta.user_task_id
            GROUP BY user_task_id
        )
        ORDER BY ta.created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Report</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-report.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()">
        <svg class="burger-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
        </svg>
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
                        <span class="nav-text">Account Overview</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="managetask.php" class="nav-link">
                        <i class="bi bi-calendar3 nav-icon"></i>
                        <span class="nav-text">Employee Task</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="stats.php" class="nav-link">
                        <i class="bi bi-bar-chart-fill nav-icon"></i>
                        <span class="nav-text">Performance Stats</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="report.php" class="nav-link active">
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
            <h1 class="header-title">Employee Report</h1>
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
                <div class="content-section active" id="reports">
                    <h2 class="section-title">Employee Report List</h2>
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="generatePDF()">Generate PDF</button>
                        <button class="btn btn-secondary" onclick="exportExcel()">Export Excel</button>
                    </div>
                    
                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                    <div class="input-group" style="flex: 1 1 220px; min-width: 200px;">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search tasks and employees..." id="searchInput">
                    </div>

                    <select class="form-select" id="statusFilter" style="flex: 1 1 160px; min-width: 150px;">
                        <option value="">All Status</option>
                        <option value="achieved">Achieved</option>
                        <option value="non achieved">Non Achieved</option>
                    </select>

                    <select class="form-select" id="typeFilter" style="flex: 1 1 160px; min-width: 150px;">
                        <option value="">All Task Types</option>
                        <?php foreach ($task_types as $task_type): ?>
                            <option value="<?php echo htmlspecialchars($task_type); ?>"><?php echo htmlspecialchars($task_type); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <div class="position-relative" style="flex: 1 1 140px; min-width: 130px;">
                        <input type="text" class="form-control" id="start_date" placeholder="Start Date">
                        <img src="../img/calendar.png" alt="Calendar Icon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:16px;">
                    </div>

                    <div class="position-relative" style="flex: 1 1 140px; min-width: 130px;">
                        <input type="text" class="form-control" id="end_date" placeholder="End Date">
                        <img src="../img/calendar.png" alt="Calendar Icon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:16px;">
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="taskTable">
                            <thead>
                                <tr>
                                    <th>Task</th>
                                    <th>Employee</th>
                                    <th>Period</th>
                                    <th>Completed</th>
                                    <th>Time</th>
                            <th>Issue</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>

                            </thead>
                            <tbody>
                                <?php if ($result && mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['task_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                            <td>
                                                <?php 
                                                $start_date = $row['start_date'] ? date('d/m/Y', strtotime($row['start_date'])) : '-';
                                                $end_date = $row['end_date'] ? date('d/m/Y', strtotime($row['end_date'])) : '-';
                                                echo $start_date . ' - ' . $end_date;
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                $work_orders = (int)($row['work_orders'] ?? 0);
                                                $work_orders_completed = (int)($row['work_orders_completed'] ?? 0);
                                                echo $work_orders_completed . '/' . $work_orders;
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['created_at'] ?? '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['kendala'] ?? '-'); ?></td>
                                            <td>
                                                <?php
                                                    $status = strtolower($row['task_status'] ?? '');
                                                    if ($status === 'achieved') {
                                                        echo '<span class="badge status-achieve">Achieved</span>';
                                                    } else {
                                                        echo '<span class="badge status-nonachieve">Non Achieved</span>';
                                                    }
                                                ?>
                                            </td>
                                            <td>
                                                <a href="report_detail.php?id=<?php echo (int)$row['user_task_id']; ?>" 
                                                class="btn btn-primary btn-view" 
                                                title="View Details">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center">No data found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../js/admin/report.js"></script>
</body>
</html>