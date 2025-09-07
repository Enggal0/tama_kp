<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$userName = $_SESSION['user_name'] ?? 'Admin';

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
    <title>Dashboard Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-addaccount.css" />

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
                <img src="../img/tamaaa.png" alt="TAMA Logo" style="height: 100px; display: block; margin: 0; padding: 0;">
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

        <main class="main-content" id="mainContent">
            <header class="header">
                <div>
                    <h1 class="header-title">Manage Account</h1>
                </div>   
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="user-avatar me-2 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-size: 1.25rem; font-weight: 600; background-color: #b02a37; color: #fff;">A</div>
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
            
            <div class="content">
                <section class="content-section" id="manage-tasks">
                    <h2 class="section-title">Add Account</h2>
                    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>

                    <?php elseif (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($_GET['error']) ?>
                    </div>
                    <?php endif; ?>
                        <form action="addacc_process.php" method="POST">
                            <div class="form-group">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-input" name="name" placeholder="full name" required value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-input" name="email" placeholder="email address" required value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender" required>
                                <option value="">Select</option>
                                <option value="male" <?= ($_GET['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($_GET['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label">NIK</label>
                                <input type="text" class="form-input" name="nik" placeholder="NIK" required value="<?= htmlspecialchars($_GET['nik'] ?? '') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-input-disabled" name="role" value="Employee" readonly>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-input" name="phone" placeholder="phone number" required value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
                            </div>

                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <div class="password-field position-relative">
                                <input type="password" class="form-input pe-5" name="password" id="password" placeholder="password" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted px-3" 
                                        onclick="togglePasswordVisibility('password')" tabindex="-1">
                                    <i class="bi bi-eye-slash" id="togglePassword"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Confirm Password</label>
                            <div class="password-field position-relative">
                                <input type="password" class="form-input pe-5" name="confirmPassword" id="confirmPassword" placeholder="Re-enter password" required>
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted px-3" 
                                        onclick="togglePasswordVisibility('confirmPassword')" tabindex="-1">
                                    <i class="bi bi-eye-slash" id="toggleConfirmPassword"></i>
                                </button>
                            </div>
                        </div>

                            <button type="submit" class="btn btn-primary">Save</button>
                            <a href="manageaccount.php" class="btn btn-secondary">Cancel</a>
                        </form>
                    </div>
                </section>
            </div>
        </main>
        
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
    <script src="../js/admin/addaccount.js"></script>
    
</body>
</html>