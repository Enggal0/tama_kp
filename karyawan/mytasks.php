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
    <title>My Tasks - Kaon Employee Dashboard</title>
    
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/karyawan/style-mytasks.css" />
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
                <div class="sidebar-nav">
                    <div class="nav-item">
                    <a href="dashboard.php" class="nav-link" onclick="showSection('dashboard')">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                        </svg>
                        <span class="nav-text">Dashboard</span>
                    </a>
                    </div>
                    <div class="nav-item">
                    <a href="mytasks.php" class="nav-link active" onclick="showSection('my-tasks')">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1 1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                        </svg>
                        <span class="nav-text">My Tasks</span>
                    </a>
                    </div>
                    <div class="nav-item">
                    <a href="myperformance.php" class="nav-link" onclick="showSection('performance')">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                        </svg>
                        <span class="nav-text">Statistics</span>
                    </a>
                    </div>
            </div>
        </nav>

        <!-- Main Content -->
    <main class="main-content" id="mainContent">
      <header class="header">
        <div>
          <h1 class="header-title">My Tasks</h1>
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
                <!-- Task Statistics -->
                <div class="task-stats">
                    <div class="stat-card">
                        <div class="stat-number">5</div>
                        <div class="stat-label">Active Tasks</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">12</div>
                        <div class="stat-label">Achieved</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">1</div>
                        <div class="stat-label">Non Achieved</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">85%</div>
                        <div class="stat-label">Success Rate</div>
                    </div>
                </div>

                <!-- Task Controls -->
                <div class="tasks-header">
                    <h2 class="tasks-title">Task Management</h2>
                    <p class="tasks-subtitle">Manage and track your assigned tasks efficiently</p>
                    
                    <div class="tasks-controls">
                        <div class="search-box">
                            <svg class="search-icon" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"/>
                            </svg>
                            <input type="text" class="search-input" placeholder="Search tasks..." oninput="filterTasks()">
                        </div>
                        
                        <div class="filter-buttons">
                            <button class="filter-btn active" onclick="setFilter('all', event)">All</button>
                            <button class="filter-btn" onclick="setFilter('achieve', event)">Achieved</button>
                            <button class="filter-btn" onclick="setFilter('nonachieve', event)">Non Achieved</button>
                        </div>
                        
                        <select class="sort-select" onchange="sortTasks(this.value)">
                            <option value="deadline">Sort by Deadline</option>
                            <option value="priority">Sort by Priority</option>
                            <option value="status">Sort by Status</option>
                            <option value="type">Sort by Type</option>
                        </select>
                    </div>
                </div>

                <!-- Task Cards -->
                    <div class="tasks-grid" id="tasksGrid">
                        <div class="task-card priority-high" data-status="progress" data-type="Pelurusan KPI" data-priority="high" data-deadline="2025-06-30">
                            <div class="task-header">
                                <div>
                                    <div class="task-type">Val Tiang</div>
                                    <div class="task-title">Pole Validation and Rack Verification</div>
                                </div>
                                <span class="status-badge status-progress">
                                    <div class="status-indicator"></div>
                                    In Progress
                                </span>
                            </div>
                            <div class="task-description">
                                Verify and validate the presence and suitability of pole locations along with the configuration of EA and OA racks to ensure installation readiness and compliance with technical standards.
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="..."/>
                                    </svg>
                                    Due: Jul 14, 2025
                                </div>
                                <div class="task-target">Target: -</div>
                            </div>
                            <div class="task-actions">
                                <button class="task-btn btn-primary" onclick="openReportModal('task1')">Report</button>
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php'">View</button>
                            </div>
                        </div>

                        <!-- Task F: FO CONS/EBIS -->
                        <div class="task-card priority-high" data-status="achieve" data-type="FALLOUT CONS/EBIS, UP ODP, CEK PORT BT" data-priority="high" data-deadline="2025-06-29">
                            <div class="task-header">
                                <div>
                                    <div class="task-type">Pelurusan EBIS</div>
                                    <div class="task-title">EBIS E2E Data Alignment</div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span class="status-badge status-achieve">
                                        <div class="status-indicator"></div>
                                        Achieved
                                    </span>
                                    <div class="achieve-description">
                                        Task completed: 51
                                    </div>
                                </div>
                            </div>
                            <div class="task-description">
                                Validate and align EBIS end-to-end data across internal systems to ensure accuracy and consistency.
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="..." />
                                    </svg>
                                    Due: Jun 27, 2025
                                </div>
                                <div class="task-target">Target: -</div>
                            </div>
                            <div class="task-actions">
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php'">View</button>
                            </div>
                        </div>

                        <!-- Task F: FO CONS/EBIS -->
                        <div class="task-card priority-high" data-status="achieve" data-type="FALLOUT CONS/EBIS, UP ODP, CEK PORT BT" data-priority="high" data-deadline="2025-06-29">
                            <div class="task-header">
                                <div>
                                    <div class="task-type">Val Tiang</div>
                                    <div class="task-title">Pole Validation and Rack Verification</div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span class="status-badge status-achieve">
                                        <div class="status-indicator"></div>
                                        Achieved
                                    </span>
                                    <div class="achieve-description">
                                        Task completed: 32
                                    </div>
                                </div>
                            </div>
                            <div class="task-description">
                                Verify and validate the presence and suitability of pole locations along with the configuration of EA and OA racks to ensure installation readiness and compliance with technical standards.
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="..." />
                                    </svg>
                                    Due: Jun 13, 2025
                                </div>
                                <div class="task-target">Target: -</div>
                            </div>
                            <div class="task-actions">
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php'">View</button>
                            </div>
                        </div>

                        <!-- Task F: FO CONS/EBIS -->
                        <div class="task-card priority-high" data-status="achieve" data-type="FALLOUT CONS/EBIS, UP ODP, CEK PORT BT" data-priority="high" data-deadline="2025-06-29">
                            <div class="task-header">
                                <div>
                                    <div class="task-type">Val Tiang</div>
                                    <div class="task-title">Pole Validation and Rack Verification</div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span class="status-badge status-achieve">
                                        <div class="status-indicator"></div>
                                        Achieved
                                    </span>
                                    <div class="achieve-description">
                                        Task completed: 27
                                    </div>
                                </div>
                            </div>
                            <div class="task-description">
                                Verify and validate the presence and suitability of pole locations along with the configuration of EA and OA racks to ensure installation readiness and compliance with technical standards.
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="..." />
                                    </svg>
                                    Due:  May 31, 2025
                                </div>
                                <div class="task-target">Target: -</div>
                            </div>
                            <div class="task-actions">
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php'">View</button>
                            </div>
                        </div>

                            <!-- Task F: FO CONS/EBIS -->
                        <div class="task-card priority-high" data-status="nonachieve" data-type="FALLOUT CONS/EBIS, UP ODP, CEK PORT BT" data-priority="high" data-deadline="2025-06-29">
                            <div class="task-header">
                                <div>
                                    <div class="task-type">Pelurusan KPI</div>
                                    <div class="task-title">KPI Alignment Verification and Reporting</div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span class="status-badge status-nonachieve">
                                        <div class="status-indicator"></div>
                                        Non Achieved
                                    </span>
                                    <div class="achieve-description">
                                        Task completed: 48
                                    </div>
                                </div>
                            </div>
                            <div class="task-description">
                                “Conduct daily verification and alignment of KPI data to ensure that the set targets are achieved.
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="..." />
                                    </svg>
                                    Due: May 17, 2025
                                </div>
                                <div class="task-target">Target: 50 WO/Hari</div>
                            </div>
                            <div class="task-actions">
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php'">View</button>
                            </div>
                        </div>


                        <!-- Task F: FO CONS/EBIS -->
                        <div class="task-card priority-high" data-status="achieve" data-type="FALLOUT CONS/EBIS, UP ODP, CEK PORT BT" data-priority="high" data-deadline="2025-06-29">
                            <div class="task-header">
                                <div>
                                    <div class="task-type">Pelurusan KPI</div>
                                    <div class="task-title">KPI Alignment Verification and Reporting</div>
                                </div>
                                <div style="display: flex; flex-direction: column; align-items: flex-end;">
                                    <span class="status-badge status-achieve">
                                        <div class="status-indicator"></div>
                                        Achieved
                                    </span>
                                    <div class="achieve-description">
                                        Task completed: 57
                                    </div>
                                </div>
                            </div>
                            <div class="task-description">
                                “Conduct daily verification and alignment of KPI data to ensure that the set targets are achieved.
                            </div>
                            <div class="task-meta">
                                <div class="task-deadline">
                                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="..." />
                                    </svg>
                                    Due: May 17, 2025
                                </div>
                                <div class="task-target">Target: 50 WO/Hari</div>
                            </div>
                            <div class="task-actions">
                                <button class="task-btn btn-secondary" onclick="window.location.href='view.php'">View</button>
                            </div>
                        </div>
                </div>
            </main>
        </div>

        <!-- Modal -->
  <div class="modal-custom" id="reportModal">
    <div class="modal-content-custom">
      <div class="modal-header-custom">
        <h5 class="modal-title mb-0" style="color: white;">Submit Task Report</h5>
        <button class="close-btn" onclick="closeReportModal()">×</button>
        <p class="mb-0 small">Complete your task reporting</p>
      </div>
      <div class="modal-body-custom">
        <div class="task-info">
          <strong>Current Task:</strong>
          <p class="mb-0">Val Tiang</p>
        </div>
        <form id="reportForm" onsubmit="submitReport(event)">
          <div class="row mb-3">
            <div class="col-md-6">
              <label for="achieved" class="form-label">Achieved</label>
              <input type="number" id="achieved" name="achieved" class="form-control" placeholder="e.g. 50" min="0" required>
              <div class="help-text">Amount of work done</div>
            </div>
            <div class="col-md-6">
              <label for="target" class="form-label">Target</label>
              <input type="number" id="target" name="target" class="form-control" placeholder="e.g. 50" min="0" required>
              <div class="help-text">Target specified</div>
            </div>
          </div>

          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select id="status" name="status" class="form-select" required>
              <option value="">-- Select Status --</option>
              <option value="achieve">✅ Achieved</option>
              <option value="non-achieve">❌ Non Achieved</option>
            </select>
          </div>
          <input type="hidden" id="reportTaskId" value="task1" />
          <button type="submit" class="submit-btn">Submit Report</button>
        </form>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="../js/karyawan/mytasks.js"></script>
</body>
</html>
