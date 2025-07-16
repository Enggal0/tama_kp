<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../login.php");
    exit();
}

function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2); // ambil maksimal 2 huruf aja
}

$userInitials = getInitials($_SESSION['user_name']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Statistics - Kaon Employee Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <link rel="stylesheet" href="../css/karyawan/style-performance.css" />
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()"></button>
    
    <div class="dashboard-container">
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo-container">
                    <div class="sidebar-logo">Kaon</div>
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
                    <a href="mytasks.php" class="nav-link">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">My Tasks</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="myperformance.php" class="nav-link active">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Statistics</span>
                    </a>
                </div>
            </div>
        </nav>

        <main class="main-content" id="mainContent">
            <header class="header">
                <div>
                    <h1 class="header-title">Task Statistics</h1>
                </div>   
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2 bg-primary"><?= $userInitials; ?></div>
                        <span class="fw-semibold text-dark"><?= htmlspecialchars($_SESSION['user_name']); ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm mt-2">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="profile.php">
                                    <i class="bi bi-person me-2"></i> Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <button class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                    <i class="bi bi-box-arrow-right me-2"></i>Logout
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <div class="content">
                 <!-- Summary Statistics -->
                <div class="summary-stats">
                    <div class="summary-card">
                        <div class="summary-number">18</div>
                        <div class="summary-label">Total Tasks</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">12</div>
                        <div class="summary-label">Tasks Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">1</div>
                        <div class="summary-label">Tasks Not Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">5</div>
                        <div class="summary-label">In Progress</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">85%</div>
                        <div class="summary-label">Success Rate</div>
                    </div>
                </div>
                
                <div id="reportContent" class="pdf-export" style="display:none;">
                <!-- Summary Statistics -->
                <div class="summary-stats">
                    <div class="summary-card">
                        <div class="summary-number">18</div>
                        <div class="summary-label">Total Tasks</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">12</div>
                        <div class="summary-label">Tasks Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">1</div>
                        <div class="summary-label">Tasks Not Achieved</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">5</div>
                        <div class="summary-label">In Progress</div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-number">85%</div>
                        <div class="summary-label">Success Rate</div>
                    </div>
                </div>
                </div>

                <!-- Controls -->
                <div class="stats-header">
                    <h2 class="stats-title">Task Performance Analytics</h2>
                    <p class="stats-subtitle">Comprehensive statistics of your task performance and achievements</p>
                    
                    <div class="stats-controls">
                        <select class="filter-select" id="taskFilter" onchange="filterByTask()">
                            <option value="all">All Tasks</option>
                            <option value="pelurusan_kpi">Pelurusan KPI</option>
                            <option value="fallout_cons">Fallout CONS/EBIS</option>
                            <option value="up_odp">UP ODP</option>
                            <option value="cek_port">Cek Port BT</option>
                            <option value="val_tiang">Val Tiang</option>
                            <option value="odp_kendala">ODP Kendala</option>
                            <option value="validasi_ftm">Validasi FTM</option>
                            <option value="pelurusan_gdoc">Pelurusan GDOC HS</option>
                            <option value="fallout_uim">Fallout UIM DAMAN</option>
                            <option value="pelurusan_ebis">Pelurusan EBIS</option>
                            <option value="pelurusan_aso">Pelurusan GDOC ASO</option>
                            <option value="e2e">E2E</option>
                        </select>
                        
                        <select class="filter-select" id="statusFilter" onchange="filterByStatus()">
                            <option value="all">All Status</option>
                            <option value="achieve">Achieved</option>
                            <option value="nonachieve">Not Achieved</option>
                            <option value="progress">In Progress</option>
                        </select>
                        
                        <button class="download-btn" onclick="downloadStatistics()">
                            <i class="bi bi-download"></i>
                            Download Report
                        </button>
                    </div>
                </div>

                <!-- Individual Task Statistics -->
                <div class="stats-grid" id="statsGrid">
                    <!-- Task cards will be populated by JavaScript -->
                </div>

                <!-- Task Performance Chart -->
                <div class="chart-container">
                    <h3 class="chart-title">Task Performance Overview</h3>
                    <div class="chart-wrapper">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>

                <!-- Target vs Achieved Chart -->
                <div class="chart-container">
                    <h3 class="chart-title">Target vs Completed Tasks</h3>
                    <div class="chart-wrapper">
                        <canvas id="targetVsCompletedChart"></canvas>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../js/karyawan/performance.js"></script>
</body>
</html>
