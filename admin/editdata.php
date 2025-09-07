<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: manageaccount.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $nik = $_POST['nik'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $status = $_POST['status'] ?? '';
    $password = $_POST['password'] ?? '';
    $gender = ucfirst(strtolower($gender));
    $status = ucfirst(strtolower($status));

    error_log("Form data received - Name: $name, NIK: $nik, Email: $email, Phone: $phone, Gender: $gender, Status: $status");
    
    if (empty($name) || empty($nik) || empty($email) || empty($phone) || empty($gender) || empty($status)) {
        $missing_fields = [];
        if (empty($name)) $missing_fields[] = 'name';
        if (empty($nik)) $missing_fields[] = 'nik';
        if (empty($email)) $missing_fields[] = 'email';
        if (empty($phone)) $missing_fields[] = 'phone';
        if (empty($gender)) $missing_fields[] = 'gender';
        if (empty($status)) $missing_fields[] = 'status';
        
        die("Error: All required fields must be filled. Missing: " . implode(', ', $missing_fields));
    }

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET name=?, nik=?, email=?, phone=?, gender=?, status=?, password=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("sssssssi", $name, $nik, $email, $phone, $gender, $status, $hashed, $id);
    } else {
        $sql = "UPDATE users SET name=?, nik=?, email=?, phone=?, gender=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ssssssi", $name, $nik, $email, $phone, $gender, $status, $id);
    }

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        echo "<script>window.location.href = 'manageaccount.php?id=$id&success=1';</script>";
        exit();
    } else {
        echo "Gagal update: " . $stmt->error;
        $stmt->close();
        $conn->close();
        exit();
    }
}

$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("Location: manageaccount.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-editdata.css" />
</head>
<body>
    <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()"></button>
    
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
                <div id="notificationBox" style="display:none;" class="notification-box">
                ✅ Item berhasil dihapus!
            </div>
                <div class="edit-form-container">
                    <div class="form-header">
                        <h2 class="form-title">Edit Account Information</h2>
                        <p class="form-subtitle">Update account details and information</p>
                        <div class="status-indicator">
                            <?php 
                            $isActive = ($user['status'] === 'Active');
                            $dotColor = $isActive ? '#28a745' : '#dc3545';
                            $textColor = $isActive ? '#28a745' : '#dc3545';
                            ?>
                            <div class="status-dot" style="background-color: <?php echo $dotColor; ?>"></div>
                            <span class="status-text" style="color: <?php echo $textColor; ?>">
                                Account <?php echo $user['status']; ?>
                            </span>
                        </div>
                    </div>

                    <form id="editAccountForm" method="POST" action="editdata.php?id=<?= $id ?>">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name" class="form-label required">Full Name</label>
                                <input type="text" id="name" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="nik" class="form-label required">NIK</label>
                                <input type="text" id="nik" name="nik" class="form-input" value="<?php echo htmlspecialchars($user['nik']); ?>" required maxlength="16">
                            </div>

                            <div class="form-group">
                                <label for="email" class="form-label required">Email</label>
                                <input type="email" id="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone" class="form-label required">Phone Number</label>
                                <input type="tel" id="phone" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="gender" class="form-label required">Gender</label>
                                <select id="gender" name="gender" class="form-select" required>
                                    <option value="" <?php echo (empty($user['gender']) ? 'selected' : ''); ?>>Select Gender</option>
                                    <option value="male" <?php echo (!empty($user['gender']) && strtolower($user['gender']) === 'male') ? 'selected' : ''; ?>>Male</option>
                                    <option value="female" <?php echo (!empty($user['gender']) && strtolower($user['gender']) === 'female') ? 'selected' : ''; ?>>Female</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="status" class="form-label required">Status</label>
                                <select id="status" name="status" class="form-select" required>
                                    <option value="active" <?php echo ($user['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                    <option value="inactive" <?php echo ($user['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password" class="form-label">New Password</label>
                            <div class="password-field position-relative">
                                <input type="password" id="password" name="password" class="form-input pe-5" placeholder="Leave blank to keep current password">
                                <button type="button" class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted px-2" 
                                        onclick="togglePasswordVisibility('password')" tabindex="-1">
                                    <i class="bi bi-eye-slash" id="togglePassword"></i>
                                </button>
                            </div>
                            <small style="color: #666; font-size: 0.85rem; margin-top: 0.25rem; display: block;">
                                Only fill this field if you want to change the password
                            </small>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" onclick="showSuccessNotification()">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.47 1.178Z"/>
                                </svg>
                                Update Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            </main>

            <div class="modal fade" id="cancelEditModal" tabindex="-1" aria-labelledby="cancelEditModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <div class="modal-icon mb-3">
                            <i class="bi bi-x-circle"></i>
                            </div>
                            <h5 class="modal-title">Cancel Edit</h5>
                            <p class="modal-message">You have unsaved changes. Are you sure you want to cancel?</p>
                            <div class="d-flex gap-2 justify-content-center flex-column flex-sm-row mt-3">
                            <button type="button" class="btn btn-danger btn-cancel-edit" onclick="confirmCancel()">Yes, Cancel</button>
                            <button type="button" class="btn btn-outline-danger btn-stay" data-bs-dismiss="modal">Keep Editing</button>
                            </div>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="../js/admin/editdata.js"></script>
    </body>
</html>