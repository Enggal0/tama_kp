<?php
session_start();
require '../config.php'; // koneksi database (pastikan ada $conn)

// Cek akses - changed to manager
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: ../login.php");
    exit();
}

// Query ambil data users (karyawan)
$sql = "SELECT * FROM users WHERE role = 'employee'";
$result = $conn->query($sql);
$users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Query ambil data users non-employee
$sql_nonemp = "SELECT * FROM users WHERE role != 'employee'";
$result_nonemp = $conn->query($sql_nonemp);
$users_nonemp = [];
if ($result_nonemp && $result_nonemp->num_rows > 0) {
    while ($row = $result_nonemp->fetch_assoc()) {
        $users_nonemp[] = $row;
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Manager - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manager/style-manageaccount.css" />
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
          <a href="manageaccount.php" class="nav-link active">
            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 616 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 715-5z"/>
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
          <h1 class="header-title">Manage Account (Read Only)</h1>
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

        <!-- Content -->
        <div class="container-fluid p-4">
            <div class="content-section p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">Employee Accounts</h2>
                    <span class="badge bg-info">Read Only View</span>
                </div>

                <!-- Search and Filter - Remove Add button -->
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
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <!-- Removed Add New Account button for read-only access -->
                </div>

                <!-- Table -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>NIK</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <!-- Removed Action column for read-only access -->
                            </tr>
                        </thead>
                        <tbody id="usersTableBody">
                            <?php if (count($users) > 0): ?>
                                <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['nik']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td><?= isset($user['gender']) && $user['gender'] !== null ? ucfirst($user['gender']) : 'null' ?></td>
                                        <td><?= htmlspecialchars($user['status']) ?></td>
                                        <!-- Removed action buttons for read-only access -->
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="6" class="text-center">No data available.</td></tr>
                            <?php endif; ?>
                        </tbody>

                    </table>
                </div>

                <div class="row align-items-center justify-content-between mb-3">
                <!-- Sebelah kiri: Dropdown rows Rows -->
                <div class="col-md-4">
                    <select id="rowsPerPageSelect" class="form-select w-auto">
                        <option value="5">5 Rows</option>
                        <option value="10">10 Rows</option>
                        <option value="25">25 Rows</option>
                        <option value="50">50 Rows</option>
                    </select>
                </div>

                <!-- Sebelah kanan: Pagination -->
                <div class="col-md-8 d-flex justify-content-end">
            <nav aria-label="Page navigation">
                <ul class="pagination custom-pagination mb-0" id="pagination">
                    <!-- Pagination akan di-generate oleh JavaScript -->
                </ul>
            </nav>
        </div>
            </div>
            </div>
        </div>

        <!-- Second Content Section for Other Users -->
        <div class="container-fluid p-4">
            <div class="content-section p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">Other User Accounts</h2>
                    <span class="badge bg-info">Read Only View</span>
                </div>

                <!-- Table Non-Employee Users -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>NIK</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Gender</th>
                                <th>Status</th>
                                <th>Role</th>
                                <!-- Removed Action column for read-only access -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($users_nonemp) > 0): ?>
                                <?php foreach ($users_nonemp as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['nik']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td><?= isset($user['gender']) && $user['gender'] !== null ? ucfirst($user['gender']) : 'null' ?></td>
                                        <td><?= htmlspecialchars($user['status']) ?></td>
                                        <td><?= htmlspecialchars($user['role']) ?></td>
                                        <!-- Removed action buttons for read-only access -->
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
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

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manager/manageaccount.js"></script>
    
    <script>
    function confirmLogout() {
        window.location.href = '../logout.php';
    }
    </script>
</body>
</html>
