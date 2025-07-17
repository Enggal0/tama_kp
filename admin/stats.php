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
                            <div class="user-avatar me-2">A</div>
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
                <!-- Overview Stats -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-primary text-white rounded-3 p-2 me-3">
                                    <i class="bi bi-list-check"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Total Tasks</h6>
                                    <h3 class="mb-0 fw-bold text-primary">9</h3>
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
                                    <h3 class="mb-0 fw-bold text-success">7</h3>
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
                                    <h3 class="mb-0 fw-bold text-danger">2</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-xl-3">
                        <div class="stats-card">
                            <div class="d-flex align-items-center mb-3">
                                <div class="stats-icon bg-info text-white rounded-3 p-2 me-3">
                                    <i class="bi bi-percent"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-muted">Success Rate</h6>
                                    <h3 class="mb-0 fw-bold text-info">78%</h3>
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
                                <option value="FAJAR RAFIUDIN">FAJAR RAFIUDIN</option>
                                <option value="IMAM SUTRISNO">IMAM SUTRISNO</option>
                                <option value="ERIK EFENDI">ERIK EFENDI</option>
                                <option value="ODI RINANDA">ODI RINANDA</option>
                                <option value="AJI PANGESTU">AJI PANGESTU</option>
                                <option value="YOSEF TOBIR">YOSEF TOBIR</option>
                                <option value="EDDO BENTANO">EDDO BENTANO</option>
                                <option value="HERLANDO">HERLANDO</option>
                                <option value="M. NURIL ADINATA">M. NURIL ADINATA</option>
                            </select>
                        </div>
                        
                        <div class="filter-card">
                            <label class="form-label fw-semibold">Filter by Task Type:</label>
                            <select class="form-select" id="taskFilter" onchange="filterTasks()">
                                <option value="">All Tasks</option>
                                <option value="Pelurusan KPI">Pelurusan KPI</option>
                                <option value="Fallout CONS/EBIS">Fallout CONS/EBIS</option>
                                <option value="UP ODP">UP ODP</option>
                                <option value="Cek Port BT">Cek Port BT</option>
                                <option value="Val Tiang">Val Tiang</option>
                                <option value="ODP Kendala">ODP Kendala</option>
                                <option value="Validasi FTM">Validasi FTM</option>
                                <option value="Pelurusan GDOC Fallout">Pelurusan GDOC Fallout</option>
                                <option value="Pelurusan EBIS">Pelurusan EBIS</option>
                                <option value="E2E">E2E</option>
                            </select>
                        </div>
                        <div class="filter-card">
                            <label class="form-label fw-semibold">Filter by Status:</label>
                            <select class="form-select" id="statusFilter" onchange="filterTasks()">
                                <option value="">All Status</option>
                                <option value="achieve">Achieve</option>
                                <option value="non-achieve">Non Achieve</option>
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

    <!-- Progress Chart Filters -->
    <div class="chart-filters mb-4">
        <div class="filter-card">
            <label class="form-label fw-semibold">View Mode:</label>
            <select class="form-select" id="progressViewMode" onchange="updateProgressChart()">
                <option value="all">All Tasks</option>
                <option value="employee">By Employee</option>
                <option value="task">By Task Type</option>
            </select>
        </div>

        <div class="filter-card">
            <label class="form-label fw-semibold">Sort By:</label>
            <select class="form-select" id="progressSortBy" onchange="updateProgressChart()">
                <option value="name">Name</option>
                <option value="progress">Progress %</option>
                <option value="target">Target</option>
            </select>
        </div>

        <div class="filter-card">
            <label class="form-label fw-semibold">Show Only:</label>
            <select class="form-select" id="progressShowOnly" onchange="updateProgressChart()">
                <option value="all">All</option>
                <option value="above-target">Above Target</option>
                <option value="below-target">Below Target</option>
                <option value="on-target">On Target</option>
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
                        <th>Employee</th>
                        <th>Task Type</th>
                        <th>Progress</th>
                        <th>Target</th>
                        <th>Unit</th>
                        <th>Achievement %</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="progressTableBody">
                    <!-- Table rows will be populated here -->
                </tbody>
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
    <script src="../js/admin/stats.js"></script>
</body>
</html>
