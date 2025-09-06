<?php
session_start();
require '../config.php'; 


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$sql = "SELECT * FROM users WHERE role = 'employee'";
$result = $conn->query($sql);
$users = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

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
    <title>Dashboard Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-manageaccount.css" />
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
            <a href="manageaccount.php" class="nav-link active">
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
            <a href="stats.php" class="nav-link">
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
          <h1 class="header-title">Manage Account</h1>
        </div>   
        <div class="d-flex align-items-center">
            <div class="dropdown">
                <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-avatar me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;">A</div>
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
        <div class="container-fluid p-4">
            <div class="content-section p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">Employee Accounts</h2>
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
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex justify-content-end">
                    <button class="btn btn-primary" onclick="window.location.href='addaccount.php'">
                        <i class="bi bi-plus-circle me-2"></i>
                        Add New Account
                    </button>
                    </div>
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
                                <th>Action</th>
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
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn-icon" title="Edit" onclick="window.location.href='editdata.php?id=<?= $user['id'] ?>'">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                </button>
                                                <button class="btn-icon" title="Delete" onclick="showDeleteModal('<?= addslashes($user['name']) ?>', <?= $user['id'] ?>)">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="7" class="text-center">No data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-3 px-3 pb-3">
                    <div class="d-flex align-items-center">
                        <label for="rowsPerPageSelect" class="me-2 text-muted">Show:</label>
                        <select id="rowsPerPageSelect" class="form-select w-auto">
                            <option value="5">5 Rows</option>
                            <option value="10">10 Rows</option>
                            <option value="25">25 Rows</option>
                            <option value="50">50 Rows</option>
                        </select>
                </div>

                <div class="col-md-8 d-flex justify-content-end">
                    <nav aria-label="Page navigation">
                        <ul class="pagination custom-pagination mb-0" id="pagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
        
        <div class="container-fluid p-4">
            <div class="content-section p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="section-title mb-0">Other User Accounts</h2>
                </div>
                
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
                                <th>Action</th>
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
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button class="btn-icon" title="Edit" onclick="window.location.href='editdata.php?id=<?= $user['id'] ?>'">
                                                    <i class="bi bi-pencil-square text-primary"></i>
                                                </button>
                                                <button class="btn-icon" title="Delete" onclick="showDeleteModal('<?= addslashes($user['name']) ?>', <?= $user['id'] ?>)">
                                                    <i class="bi bi-trash text-danger"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="8" class="text-center">No data available.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
            
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <div class="w-100">
                            <div class="modal-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <h5 class="modal-title" id="deleteModalLabel">Delete Confirmation</h5>
                            <p class="modal-subtitle mb-0">This action cannot be undone</p>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-3">Are you sure you want to delete <strong id="deleteUserName">this item</strong>?<br>Deleted data cannot be recovered.</p>
                        <div class="alert alert-warning" role="alert">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> All data associated with this item will be permanently removed.
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle me-1"></i>
                            Cancel
                        </button>
                        <button type="button" class="btn btn-delete" onclick="confirmDelete()">
                            <i class="bi bi-trash me-1"></i>
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <div class="modal-icon-logout">
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
        <script src="../js/admin/manageaccount.js"></script>
    </body>
</html>