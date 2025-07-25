<?php
session_start();
include '../config.php';

// Check if user is logged in and is an employee
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $gender = $_POST['gender'] ?? '';
    
    // Handle profile photo upload
    $profile_photo = null;
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/profile_photos/';
        $file_extension = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_path)) {
                $profile_photo = $new_filename;
            }
        }
    }
    
    // Update user data
    if ($profile_photo) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, gender = ?, profile_photo = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("sssssi", $name, $email, $phone, $gender, $profile_photo, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, gender = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $email, $phone, $gender, $user_id);
    }
    
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
        $_SESSION['user_name'] = $name; // Update session name
    } else {
        $error_message = "Error updating profile: " . $conn->error;
    }
    $stmt->close();
}

// Fetch current user data
$stmt = $conn->prepare("SELECT name, nik, email, phone, gender, profile_photo FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    header("Location: ../login.php");
    exit();
}

// Generate user initials for avatar
$user_initials = '';
if ($user['name']) {
    $name_parts = explode(' ', trim($user['name']));
    $user_initials = strtoupper(substr($name_parts[0], 0, 1));
    if (count($name_parts) > 1) {
        $user_initials .= strtoupper(substr($name_parts[1], 0, 1));
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - TAMA</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/karyawan/style-editprofile.css" />
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
                    <a href="myperformance.php" class="nav-link">
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
                <div><h1 class="header-title">Edit Profile</h1></div>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($user['profile_photo'] && file_exists("../uploads/profile_photos/" . $user['profile_photo'])): ?>
                                <img src="../uploads/profile_photos/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile" class="user-avatar me-2" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div class="user-avatar me-2 bg-primary"><?= $user_initials ?></div>
                            <?php endif; ?>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($user['name']) ?></span>
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
                <div class="edit-container">
                    <!-- Page Header -->
                    <div class="page-header">
                        <div>
                            <h1 class="page-title">Edit Profile</h1>
                            <div class="breadcrumb">
                                <a href="profile.php">Profile</a>
                                <span>›</span>
                                <span>Edit</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($success_message): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="successAlert">
                            <?= htmlspecialchars($success_message) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($error_message) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Alert -->
                    <div class="alert alert-info">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            Please double check all the data you have entered to ensure its accuracy before proceeding to the next step.
                        </div>
                    </div>

                    <!-- Edit Form -->
                    <form class="edit-form" method="POST" enctype="multipart/form-data">
                        <!-- Photo Upload Section -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                                </svg>
                                Profile Photo
                            </h2>
                            <div class="photo-upload-section">
                                <div class="current-photo" id="currentPhoto">
                                    <?php if ($user['profile_photo'] && file_exists("../uploads/profile_photos/" . $user['profile_photo'])): ?>
                                        <img src="../uploads/profile_photos/<?= htmlspecialchars($user['profile_photo']) ?>" alt="Profile" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">
                                    <?php else: ?>
                                        <?= $user_initials ?>
                                    <?php endif; ?>
                                </div>
                                <div class="upload-text">Click to upload a new profile photo</div>
                                <input type="file" id="photoInput" name="profile_photo" class="file-input" accept="image/*" onchange="previewPhoto(event)">
                                <button type="button" class="upload-btn" onclick="document.getElementById('photoInput').click();">
                                    Choose Photo
                                </button>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                </svg>
                                Personal Information
                            </h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Full Name <span class="required">*</span></label>
                                    <input type="text" class="form-input" name="name" id="fullName" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Email <span class="required">*</span></label>
                                    <input type="email" class="form-input" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">NIK</label>
                                    <input type="text" class="form-input" id="nik" value="<?= htmlspecialchars($user['nik']) ?>" disabled>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number <span class="required">*</span></label>
                                    <input type="tel" class="form-input" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" required>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Information -->
                        <div class="form-section">
                            <h2 class="section-title">
                                <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"/>
                                </svg>
                                Additional Information
                            </h2>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">Gender</label>
                                    <select class="form-input" name="gender" id="gender">
                                        <option value="">Select Gender</option>
                                        <option value="male" <?= $user['gender'] === 'male' ? 'selected' : '' ?>>Male</option>
                                        <option value="female" <?= $user['gender'] === 'female' ? 'selected' : '' ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="button" class="btn btn-primary" onclick="openUpdateModal()">
                                Save Changes
                            </button>
                            <a href="profile.php" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal Konfirmasi Update -->
    <div id="updateModal" class="modal-overlay" style="display: none;">
        <div class="modal-box">
            <h2>Confirm Update</h2>
            <p>Are you sure you want to save these changes?</p>
            <div class="modal-actions">
                <button class="modal-btn cancel" onclick="closeUpdateModal()">Cancel</button>
                <button class="modal-btn confirm" onclick="submitEditForm()">Yes, Save</button>
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
    <script src="../js/karyawan/editprofile.js"></script>

    <script>
        // Auto-hide success message after 3 seconds
        <?php if ($success_message): ?>
        setTimeout(function() {
            const alert = document.getElementById('successAlert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
        <?php endif; ?>

        function previewPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const currentPhoto = document.getElementById('currentPhoto');
                    currentPhoto.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">`;
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
