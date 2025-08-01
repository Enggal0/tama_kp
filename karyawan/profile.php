<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'employee') {
    header("Location: ../login.php");
    exit();
}

// Database connection
require_once('../config.php');

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

$userInitials = getInitials($_SESSION['user_name']);
$userId = $_SESSION['user_id'];

// Get user details from database
$userQuery = "SELECT * FROM users WHERE id = ? AND role = 'employee'";
$userStmt = $conn->prepare($userQuery);
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userDetails = $userResult->fetch_assoc();

if (!$userDetails) {
    // User not found or not an employee
    header("Location: ../login.php");
    exit();
}

// Get user statistics
$statsQuery = "SELECT 
    COUNT(*) as total_tasks,
    SUM(CASE WHEN status = 'Achieved' THEN 1 ELSE 0 END) as completed_tasks,
    SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as active_tasks,
    SUM(CASE WHEN status = 'Non Achieved' THEN 1 ELSE 0 END) as non_achieved_tasks
FROM user_tasks WHERE user_id = ?";

$statsStmt = $conn->prepare($statsQuery);
$statsStmt->bind_param("i", $userId);
$statsStmt->execute();
$statsResult = $statsStmt->get_result();
$stats = $statsResult->fetch_assoc();

// Calculate years since account creation (as experience)
$createDate = $userDetails['created_at'] ? new DateTime($userDetails['created_at']) : new DateTime();
$currentDate = new DateTime();
$experience = $currentDate->diff($createDate);
$yearsExperience = $experience->y + ($experience->m > 6 ? 1 : 0); // Round up if more than 6 months

// Calculate achievement rate (same as mytasks.php)
$achievementRate = ($stats['total_tasks'] > 0) ? round(($stats['completed_tasks'] / $stats['total_tasks']) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/karyawan/style-profile.css" />
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
        <div><h1 class="header-title">Profile</h1></div>
        <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($userDetails['profile_photo'] && file_exists("../uploads/profile_photos/" . $userDetails['profile_photo'])): ?>
                                <img src="../uploads/profile_photos/<?= htmlspecialchars($userDetails['profile_photo']) ?>" alt="Profile" class="user-avatar me-2" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                            <?php else: ?>
                                <div class="user-avatar me-2 bg-primary"><?= $userInitials; ?></div>
                            <?php endif; ?>
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
        <div class="profile-container">
          <div class="profile-header">
            <div class="profile-photo-container">
              <?php if ($userDetails['profile_photo'] && file_exists("../uploads/profile_photos/" . $userDetails['profile_photo'])): ?>
                <div class="profile-photo">
                  <img src="../uploads/profile_photos/<?= htmlspecialchars($userDetails['profile_photo']) ?>" alt="Profile" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                </div>
              <?php else: ?>
                <div class="profile-photo"><?= $userInitials ?></div>
              <?php endif; ?>
              <a href="editprofile.php" class="photo-upload-btn">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"/>
                </svg>
              </a>
            </div>
            <div class="profile-info">
              <div class="profile-name"><?= htmlspecialchars($userDetails['name']) ?></div>
              <div class="profile-badges">
                <span class="badge badge-status"><?= htmlspecialchars($userDetails['status']) ?></span>
                <span class="badge badge-department">Employee</span>
              </div>
            </div>
          </div>

          <div class="employee-stats">
            <div class="stat-card">
              <div class="stat-value"><?= max(0, $yearsExperience) ?></div>
              <div class="stat-label">Years Since Join</div>
            </div>
            <div class="stat-card">
              <div class="stat-value"><?= $stats['completed_tasks'] ?? 0 ?></div>
              <div class="stat-label">Completed Tasks</div>
            </div>
            <div class="stat-card">
              <div class="stat-value"><?= $achievementRate ?>%</div>
              <div class="stat-label">Achievement Rate</div>
            </div>
          </div>

                <div class="profile-content">
                    <div class="profile-section" style="position: relative;">
                        <h2 class="section-title">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            Personal Information
                        </h2>
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($userDetails['name']) ?>" id="fullName" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" value="<?= htmlspecialchars($userDetails['email']) ?>" id="email" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">NIK</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars($userDetails['nik']) ?>" id="nik" readonly>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-input" value="<?= htmlspecialchars($userDetails['phone'] ?? 'Not provided') ?>" id="phone" readonly>
                        </div>
                        <?php if ($userDetails['gender']): ?>
                        <div class="form-group">
                            <label class="form-label">Gender</label>
                            <input type="text" class="form-input" value="<?= htmlspecialchars(ucfirst($userDetails['gender'])) ?>" id="gender" readonly>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Account Information -->
                    <div class="profile-section">
                        <h2 class="section-title">
                            <svg width="20" height="20" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6 6V5a3 3 0 013-3h2a3 3 0 013 3v1h2a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2h2zm4-3a1 1 0 00-1 1v1h2V4a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            Account Information
                        </h2>
                        <div class="info-card">
                            <div class="info-card-title">Employee ID</div>
                            <div class="info-card-value"><?= htmlspecialchars($userDetails['nik']) ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-title">Role</div>
                            <div class="info-card-value"><?= htmlspecialchars(ucfirst($userDetails['role'])) ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-title">Status</div>
                            <div class="info-card-value"><?= htmlspecialchars($userDetails['status']) ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-title">Account Created</div>
                            <div class="info-card-value"><?= date('F j, Y', strtotime($userDetails['created_at'])) ?></div>
                        </div>
                        <div class="info-card">
                            <div class="info-card-title">Last Updated</div>
                            <div class="info-card-value"><?= date('F j, Y H:i', strtotime($userDetails['updated_at'])) ?></div>
                        </div>
                    </div>
                </div>
                <div style="margin-top: 0.3rem; width: 100%; display: flex; justify-content: flex-end;">
                          <button class="btn btn-primary" onclick="editProfile()">
                          <i class="bi bi-pencil-square me-2"></i>
                          Edit Profile
                        </button>   
                  </div>
                  <!-- Confirm Photo Change Modal -->
<div class="modal fade" id="confirmPhotoModal" tabindex="-1" aria-labelledby="confirmPhotoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title w-100" id="confirmPhotoLabel">Confirm Profile Photo Change</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Are you sure you want to change your profile photo?
      </div>
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmPhotoBtn">Yes, Change</button>
      </div>
    </div>
  </div>
</div>

<!-- Success Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
  <div id="photoToast" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        Profile photo updated successfully!
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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

            </div>
          </main>
        </div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
        <script src="../js/karyawan/profile.js?v=<?= time() ?>"></script>
        <script>
        // Logout functionality
        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        // Edit profile functionality
        function editProfile() {
            window.location.href = 'editprofile.php';
        }

        // Photo upload functionality
        function uploadPhoto() {
            document.getElementById('photoInput').click();
        }

        function handlePhotoUpload(event) {
            const file = event.target.files[0];
            if (file) {
                const modal = new bootstrap.Modal(document.getElementById('confirmPhotoModal'));
                modal.show();
                
                document.getElementById('confirmPhotoBtn').onclick = function() {
                    // Here you would normally upload the file to server
                    // For now, just show success message
                    modal.hide();
                    const toast = new bootstrap.Toast(document.getElementById('photoToast'));
                    toast.show();
                };
            }
        }
        </script>
</body>
</html>
