<?php
require_once '../config.php';

// Get all task types from database
$result_tasks = mysqli_query($conn, "SELECT DISTINCT name FROM tasks ORDER BY name");
$task_types = [];
if ($result_tasks) {
    while ($row = mysqli_fetch_assoc($result_tasks)) {
        $task_types[] = $row['name'];
    }
}

// Date filter removed

// Query task_achievements joined with users and user_tasks and tasks
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
    <title>Employee Report - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-report.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
/* PDF print style */
#pdf-report-header {
    text-align: center;
    margin-bottom: 10px;
}
#pdf-report-header h2 {
    margin: 0;
    font-size: 1.08rem;
    font-weight: bold;
    letter-spacing: 0.5px;
}
#pdf-report-header .pdf-date {
    font-size: 0.8rem;
    margin-top: 2px;
    color: #555;
}
#pdf-report-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 3px;
    font-size: 0.72rem;
    table-layout: fixed;
}
#pdf-report-table th, #pdf-report-table td {
    border: 0.5px solid #888;
    padding: 2.5px 3px;
    text-align: left;
    word-break: break-word;
}
#pdf-report-table th {
    background: #f6f6f6;
    font-weight: bold;
}
</style>
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
                    <a href="dashboard.php" class="nav-link" data-section="dashboard">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="manageaccount.php" class="nav-link" data-section="manage-accounts">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                        </svg>
                        <span class="nav-text">Manage Account</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="managetask.php" class="nav-link" data-section="manage-tasks">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">Manage Task</span>
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="stats.php" class="nav-link" data-section="statistics">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Performance Stats</span>
                    </a>
                </div>

                <div class="nav-item">
                    <a href="#" class="nav-link active" data-section="reports">
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
            <h1 class="header-title">Employee Report</h1>
            </div>
            <div class="d-flex align-items-center">
                        <div class="dropdown">
                            <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <div class="user-avatar me-2">A</div>
                                <span class="fw-semibold" style= "color: #000000;">Admin</span>
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
                    
                <!-- Search and Filters in One Row -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                    <!-- Search Input -->
                    <div class="input-group" style="flex: 1 1 220px; min-width: 200px;">
                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" class="form-control" placeholder="Search tasks and employees..." id="searchInput">
                    </div>

                    <!-- Status Filter -->
                    <select class="form-select" id="statusFilter" style="flex: 1 1 160px; min-width: 150px;">
                        <option value="">All Status</option>
                        <option value="achieved">Achieved</option>
                        <option value="non achieved">Non Achieved</option>
                    </select>

                    <!-- Task Type Filter -->
                    <select class="form-select" id="typeFilter" style="flex: 1 1 160px; min-width: 150px;">
                        <option value="">All Task Types</option>
                        <?php foreach ($task_types as $task_type): ?>
                            <option value="<?php echo htmlspecialchars($task_type); ?>"><?php echo htmlspecialchars($task_type); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <!-- Start Date -->
                    <div class="position-relative" style="flex: 1 1 140px; min-width: 130px;">
                        <input type="text" class="form-control" id="start_date" placeholder="Start Date">
                        <img src="../img/calendar.png" alt="Calendar Icon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:16px;">
                    </div>

                    <!-- End Date -->
                    <div class="position-relative" style="flex: 1 1 140px; min-width: 130px;">
                        <input type="text" class="form-control" id="end_date" placeholder="End Date">
                        <img src="../img/calendar.png" alt="Calendar Icon" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); width:16px;">
                    </div>
                </div>


                <!-- Date Filter -->
                <!-- Date Filter removed -->

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
                                                    // Use task_status from latest task_achievements and map to current logic
                                                    $status = strtolower($row['task_status'] ?? '');
                                                    if ($status === 'achieved') {
                                                        echo '<span class="badge status-achieve">Achieved</span>';
                                                    } else {
                                                        // Map everything else to Non Achieved (including In Progress)
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
    <script>

function generatePDF() {
    const today = new Date();
    const dateStr = today.toLocaleDateString('en-GB', { year: 'numeric', month: 'long', day: 'numeric' });
    let html = '';
    html += '<div id="pdf-report-header">';
    html += '<h2>Employee Task Report</h2>';
    html += '<div class="pdf-date">Printed: ' + dateStr + '</div>';
    html += '</div>';
    html += '<table id="pdf-report-table">';
    const table = document.getElementById('taskTable');
    const ths = table.querySelectorAll('thead th');
    // Buat array index kolom yang akan diambil (kecuali Action)
    let colIndexes = [];
    html += '<thead><tr>';
    for (let i = 0; i < ths.length; i++) {
        if (ths[i].innerText.trim().toLowerCase() === 'action') continue;
        colIndexes.push(i);
        html += '<th>' + ths[i].innerText + '</th>';
    }
    html += '</tr></thead><tbody>';
    // Ambil data baris
    const rows = table.querySelectorAll('tbody tr');
    if (rows.length === 0) {
        html += '<tr><td colspan="' + colIndexes.length + '" style="text-align:center">No data found.</td></tr>';
    } else {
        rows.forEach(function(row) {
            const tds = row.querySelectorAll('td');
            // Jika baris "No data found", tampilkan apa adanya
            if (tds.length === 1 && tds[0].innerText.trim().toLowerCase().includes('no data')) {
                html += '<tr><td colspan="' + colIndexes.length + '" style="text-align:center">' + tds[0].innerText + '</td></tr>';
                return;
            }
            html += '<tr>';
            colIndexes.forEach(function(idx) {
                let cell = tds[idx];
                // Untuk kolom Status, ambil teks badge (tanpa HTML)
                if (ths[idx].innerText.trim().toLowerCase() === 'status') {
                    let statusText = cell.querySelector('.badge') ? cell.querySelector('.badge').innerText : cell.innerText;
                    html += '<td>' + statusText + '</td>';
                } else {
                    html += '<td>' + cell.innerText + '</td>';
                }
            });
            html += '</tr>';
        });
    }
    html += '</tbody></table>';
    html2pdf().set({
        margin: [7, 5, 7, 5],
        filename: 'Employee_Task_Report_' + today.toISOString().slice(0,10) + '.pdf',
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(html).save();
}

function exportExcel() {
    const table = document.getElementById('taskTable');
    const ths = table.querySelectorAll('thead th');
    const rows = table.querySelectorAll('tbody tr');
    let wb = XLSX.utils.book_new();
    let ws_data = [];
    // Header
    let header = [];
    for (let i = 0; i < ths.length; i++) {
        if (ths[i].innerText.trim().toLowerCase() === 'action') continue;
        header.push(ths[i].innerText);
    }
    ws_data.push(header);
    // Data
    rows.forEach(function(row) {
        const tds = row.querySelectorAll('td');
        if (tds.length < ths.length - 1) return;
        let rowData = [];
        for (let i = 0; i < tds.length; i++) {
            if (ths[i] && ths[i].innerText.trim().toLowerCase() === 'action') continue;
            rowData.push(tds[i].innerText);
        }
        ws_data.push(rowData);
    });
    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    XLSX.utils.book_append_sheet(wb, ws, 'Report');
    XLSX.writeFile(wb, 'Employee_Task_Report_' + (new Date()).toISOString().slice(0,10) + '.xlsx');
}
</script>
</body>
</html>