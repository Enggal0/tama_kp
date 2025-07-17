<?php
require_once '../config.php';

// Get users from database for dropdown
$sql_users = "SELECT id, name FROM users WHERE status = 'Active' ORDER BY name";
$result_users = mysqli_query($conn, $sql_users);
$users = [];
if ($result_users) {
    while ($row = mysqli_fetch_assoc($result_users)) {
        $users[] = $row;
    }
}

// Get tasks from database for dropdown
$sql_tasks = "SELECT id, name, type FROM tasks ORDER BY name";
$result_tasks = mysqli_query($conn, $sql_tasks);
$tasks = [];
if ($result_tasks) {
    while ($row = mysqli_fetch_assoc($result_tasks)) {
        $tasks[] = $row;
    }
}

// Handle messages from URL parameters
$success_message = '';
$error_message = '';

if (isset($_GET['success']) && $_GET['success'] == 'task_created') {
    $success_message = "Task created successfully!";
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'missing_fields':
            $error_message = "Please fill in all required fields.";
            break;
        case 'invalid_task':
            $error_message = "Invalid task selected.";
            break;
        case 'database_error':
            $error_message = "Database error occurred. Please try again.";
            break;
        default:
            $error_message = "An error occurred. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Task - Kaon</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/admin/style-createtask.css" />
</head>
<body>
  <button class="toggle-burger" id="burgerBtn" onclick="toggleSidebar()"></button>

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
          <a href="manageaccount.php" class="nav-link">
            <svg class="nav-icon" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
            </svg>
            <span class="nav-text">Manage Account</span>
          </a>
        </div>

        <div class="nav-item">
          <a href="managetask.php" class="nav-link active">
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
          <h1 class="header-title">Create Task</h1>
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
        <!-- Task Table -->
        <section class="content-section">
          <h2 class="section-title">Create Task</h2>
          
          <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i><?php echo $success_message; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>
          
          <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error_message; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
          <?php endif; ?>
          
          <!-- Form Tambah Tugas -->
<form id="taskForm" class="task-form" method="POST" action="createtask_process.php">
  <div class="form-group">
    <label class="form-label" for="user_id">Employee Name <span class="text-danger">*</span></label>
    <select id="user_id" name="user_id" class="form-select" required>
      <option value="" disabled selected>Select Employee</option>
      <?php foreach ($users as $user): ?>
        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group">
    <label for="task_id" class="form-label">Task Type <span class="text-danger">*</span></label>
    <select id="task_id" name="task_id" class="form-select" required>
      <option value="" disabled selected>Select Task Type</option>
      <?php foreach ($tasks as $task): ?>
        <option value="<?php echo $task['id']; ?>" data-type="<?php echo $task['type']; ?>"><?php echo htmlspecialchars($task['name']); ?></option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="form-group full-width">
    <label class="form-label" for="description">Description</label>
    <textarea id="description" name="description" class="form-textarea" placeholder="Enter task description..."></textarea>
  </div>
  
  <div class="form-group">
    <label class="form-label" for="deadline">Deadline <span class="text-danger">*</span></label>
    <input type="date" id="deadline" name="deadline" class="form-input" required>
  </div>
  
  <div class="form-group" id="target-numeric" style="display: none;">
    <label class="form-label" for="target_int">Target (Numeric)</label>
    <input type="number" id="target_int" name="target_int" class="form-input" placeholder="e.g., 50">
  </div>
  
  <div class="form-group" id="target-text" style="display: none;">
    <label class="form-label" for="target_str">Target (Text)</label>
    <input type="text" id="target_str" name="target_str" class="form-input" placeholder="e.g., Complete documentation">
  </div>
    <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="window.location.href='managetask.php'">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                                </svg>
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083l6-15Zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471-.47 1.178Z"/>
                                </svg>
                                Create Task
                            </button>
                        </div>
  </form>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2
    $('#user_id').select2({
        placeholder: 'Select Employee',
        width: '100%',
        allowClear: true
    });
    
    $('#task_id').select2({
        placeholder: 'Select Task Type',
        width: '100%',
        allowClear: true
    });
    
    // Handle task type change to show/hide target fields
    $('#task_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const taskType = selectedOption.data('type');
        
        if (taskType === 'numeric') {
            $('#target-numeric').show();
            $('#target-text').hide();
            $('#target_str').val('');
        } else if (taskType === 'text') {
            $('#target-text').show();
            $('#target-numeric').hide();
            $('#target_int').val('');
        } else {
            $('#target-numeric').hide();
            $('#target-text').hide();
            $('#target_int').val('');
            $('#target_str').val('');
        }
    });
    
    // Form validation - hanya trigger saat form di-submit
    $('#taskForm').on('submit', function(e) {
        let isValid = true;
        let errorMessages = [];
        
        // Remove previous error styling
        $('.form-select, .form-input').removeClass('is-invalid');
        
        // Check required fields
        if (!$('#user_id').val()) {
            isValid = false;
            errorMessages.push('Please select an employee.');
            $('#user_id').next('.select2-container').addClass('is-invalid');
        }
        
        if (!$('#task_id').val()) {
            isValid = false;
            errorMessages.push('Please select a task type.');
            $('#task_id').next('.select2-container').addClass('is-invalid');
        }
        
        if (!$('#deadline').val()) {
            isValid = false;
            errorMessages.push('Please select a deadline.');
            $('#deadline').addClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            
            // Show error messages in a more user-friendly way
            let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            errorHtml += '<i class="bi bi-exclamation-triangle me-2"></i>';
            errorHtml += '<strong>Please fix the following errors:</strong><br>';
            errorHtml += '<ul class="mb-0">';
            errorMessages.forEach(function(message) {
                errorHtml += '<li>' + message + '</li>';
            });
            errorHtml += '</ul>';
            errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            errorHtml += '</div>';
            
            // Insert error message at the top of the form
            $('.section-title').after(errorHtml);
            
            // Scroll to top to show error
            $('html, body').animate({
                scrollTop: $('.section-title').offset().top - 100
            }, 500);
        }
    });
});

// Burger menu toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (sidebar && mainContent) {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    }
}

// Logout function
function confirmLogout() {
    window.location.href = '../logout.php';
}
</script>
</body>
</html>