<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Report - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-report.css" />
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()">
        <svg class="burger-icon" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/>
        </svg>
    </button>
    
    <div class="dashboard-container">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-container">
                    <div class="sidebar-logo">Kaon</div>
                    <div class="sidebar-subtitle">Admin Dashboard</div>
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
                <!-- Reports Section -->
                <div class="content-section active" id="reports">
                    <h2 class="section-title">Employee Report List</h2>
                    <div class="report-actions">
                        <button class="btn btn-primary" onclick="generatePDF()">Generate PDF</button>
                        <button class="btn btn-secondary" onclick="exportExcel()">Export Excel</button>
                    </div>
                    <!-- Search and Filter -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Search accounts..." id="searchInput">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="achieve">Achieved</option>
                            <option value="non achieve">Non Achieved</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="">All Type Task</option>
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
                </div>

                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" id="taskTable">
                            <thead>
                                <tr>
                                    <th>Task Type</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Deadline</th>
                                    <th>Tasks Done</th>
                                    <th>Status</th>
                                    <th>Target</th>
                                </tr>

                            </thead>
                            <tbody>
                                <tr>
                                    <td>Pelurusan KPI</td>
                                    <td>Fajar Rafiudin</td>
                                    <td>-</td>
                                    <td>2025-01-17</td>
                                    <td>45</td>
                                    <td><span class="badge status-nonachieve">Non Achieved</span></td>
                                    <td><span class="badge priority-low">50 WO/HARI</span></td>
                                </tr>
                                <tr>
                                    <td>Validasi FTM</td>
                                    <td>Odi Rinanda</td>
                                    <td>-</td>
                                    <td>2025-01-17</td>
                                    <td>52</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">1 RACK EA, 1 RACK OA</span></td>
                                </tr>
                                <tr>
                                    <td>Fallout CONS/EBIS</td>
                                    <td>Yosef Tobir</td>
                                    <td>Cek Port BT</td>
                                    <td>2025-01-17</td>
                                    <td>52</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">Semua FO Tersolusikan</span></td>
                                </tr>
                                <tr>
                                    <td>Val Tiang</td>
                                    <td>M. Nuril Adinata</td>
                                    <td>ODP Kendala</td>
                                    <td>2025-01-17</td>
                                    <td>52</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">Web access quality</span></td>
                                </tr>
                                <tr>
                                    <td>Validasi FTM</td>
                                    <td>Aji Pangestu</td>
                                    <td>-</td>
                                    <td>2025-01-17</td>
                                    <td>52</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">1 RACK EA, 1 RACK OA</span></td>
                                </tr>
                                <tr>
                                    <td>Pelurusan KPI</td>
                                    <td>Erik Efendi</td>
                                    <td>-</td>
                                    <td>2025-01-17</td>
                                    <td>47</td>
                                    <td><span class="badge status-nonachieve">Non Achieved</span></td>
                                    <td><span class="badge priority-low">50 WO/HARI</span></td>
                                </tr>
                                <tr>
                                    <td>Fallout CONS/EBIS</td>
                                    <td>Eddo Bentano</td>
                                    <td>Cek Port BT</td>
                                    <td>2025-01-17</td>
                                    <td>31</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">Semua FO Tersolusikan</span></td>
                                </tr>
                                <tr>
                                    <td>Pelurusan KPI</td>
                                    <td>Herlando</td>
                                    <td>-</td>
                                    <td>2025-01-17</td>
                                    <td>66</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">50 WO/HARI</span></td>
                                </tr>
                                <tr>
                                    <td>Fallout CONS/EBIS</td>
                                    <td>Imam Sutrisno</td>
                                    <td>Cek Port BT</td>
                                    <td>2025-01-17</td>
                                    <td>27</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">Semua FO Tersolusikan</span></td>
                                </tr>
                                <tr>
                                    <td>Pelurusan GDOC Fallout</td>
                                    <td>PKL</td>
                                    <td>-</td>
                                    <td>2025-01-17</td>
                                    <td>40</td>
                                    <td><span class="badge status-achieve">Achieved</span></td>
                                    <td><span class="badge priority-low">40 FO/HARI</span></td>
                                </tr>
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