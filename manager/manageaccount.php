<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'manager') {
    header("Location: ../login.php");
    exit();
}

require_once '../config.php';

// Get users data from database (same as admin)
$sql = "SELECT * FROM users WHERE role IN ('employee', 'manager', 'admin') ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
$users = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $users[] = $row;
    }
}

// Calculate statistics
$resultTotalUsers = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role IN ('employee', 'manager')");
$rowTotalUsers = mysqli_fetch_assoc($resultTotalUsers);
$total_users = $rowTotalUsers['total'];

$resultActiveUsers = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role IN ('employee', 'manager') AND status = 'active'");
$rowActiveUsers = mysqli_fetch_assoc($resultActiveUsers);
$active_users = $rowActiveUsers['total'];

$resultEmployees = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'employee'");
$rowEmployees = mysqli_fetch_assoc($resultEmployees);
$total_employees = $rowEmployees['total'];

$resultManagers = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'manager'");
$rowManagers = mysqli_fetch_assoc($resultManagers);
$total_managers = $rowManagers['total'];

// Ambil nama dari session
$userName = $_SESSION['user_name'] ?? 'Manager';

// Fungsi untuk ambil inisial dari nama
function getInitials($name) {
    $words = explode(' ', $name);
    $initials = '';
    foreach ($words as $word) {
        if (!empty($word)) {
            $initials .= strtoupper($word[0]);
        }
    }
    return substr($initials, 0, 2);
}

$userInitials = getInitials($userName);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Account - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/manager/style-manageaccount.css" />
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
                    <a href="manageaccount.php" class="nav-link active">
                        <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 715 5v1H1v-1a5 5 0 715-5z"/>
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
                    <h1 class="header-title">Manage Account</h1>
                </div>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;"><?= $userInitials ?></div>
                            <span class="fw-semibold" style="color: #000000;"><?= htmlspecialchars($userName) ?></span>
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
                <!-- Stats Grid -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-card-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $total_users ?></div>
                                <div class="stat-card-label">Total Users</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-success">
                            <div class="stat-card-icon">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $active_users ?></div>
                                <div class="stat-card-label">Active Users</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-info">
                            <div class="stat-card-icon">
                                <i class="bi bi-person-workspace"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $total_employees ?></div>
                                <div class="stat-card-label">Employees</div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-xl-3">
                        <div class="stat-card stat-card-warning">
                            <div class="stat-card-icon">
                                <i class="bi bi-person-gear"></i>
                            </div>
                            <div class="stat-card-content">
                                <div class="stat-card-number"><?= $total_managers ?></div>
                                <div class="stat-card-label">Managers</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Table Section -->
                <section class="content-section p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                        <h2 class="section-title mb-0">User Accounts</h2>
                        <div class="text-muted small">Read-only view for Manager</div>
                    </div>

                    <!-- Search and Filter -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search by name, email, or NIK..." disabled>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="roleFilter" disabled>
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="manager">Manager</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="genderFilter" disabled>
                                <option value="">All Genders</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="table table-hover" id="usersTable">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>NIK</th>
                                    <th>Phone</th>
                                    <th>Gender</th>
                                    <th>Role</th>
                                    <th>Created Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($users)): ?>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['id']) ?></td>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['nik']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td>
                                            <span class="badge <?= $user['gender'] == 'male' ? 'bg-primary' : 'bg-info' ?>">
                                                <?= ucfirst($user['gender']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $roleClass = '';
                                            switch($user['role']) {
                                                case 'admin': $roleClass = 'bg-danger'; break;
                                                case 'manager': $roleClass = 'bg-warning'; break;
                                                case 'employee': $roleClass = 'bg-success'; break;
                                                default: $roleClass = 'bg-secondary'; break;
                                            }
                                            ?>
                                            <span class="badge <?= $roleClass ?>"><?= ucfirst($user['role']) ?></span>
                                        </td>
                                        <td><?= date('d M Y', strtotime($user['created_at'])) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No users found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>

        <!-- Logout Modal -->
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
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../js/manager/manageaccount.js"></script>
    <script>
    // Disable all interactive elements for read-only view
    window.onload = function() {
        document.querySelectorAll('input, select, textarea, button:not([data-bs-toggle])').forEach(function(el) {
            el.disabled = true;
        });
        
        // Remove action buttons
        document.querySelectorAll('.btn-edit, .btn-delete, .btn-create, .btn-add').forEach(function(el) {
            el.style.pointerEvents = 'none';
            el.style.opacity = '0.5';
            el.remove();
        });
    };
    
    function confirmLogout() {
        window.location.href = '../logout.php';
    }
    </script>
</body>
</html>
