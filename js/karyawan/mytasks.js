// Mobile sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);

    if (isCollapsed) {
        body.classList.add('sidebar-collapsed');
    } else {
        body.classList.remove('sidebar-collapsed');
    }
}

// Function to close sidebar
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}

// Function to navigate with sidebar close
function navigateWithCloseSidebar(url, event) {
    event.preventDefault();
    
    const currentPage = window.location.pathname.split('/').pop();
    
    // Jika bukan halaman yang sama
    if (url !== currentPage) {
        // Tutup sidebar langsung
        closeSidebar();
        
        // Navigasi langsung tanpa delay
        window.location.href = url;
    }
}

// Close sidebar when clicking outside of it (mobile)
document.addEventListener('click', function(e) {
    const sidebar = document.getElementById('sidebar');
    const burgerBtn = document.getElementById('burgerBtn');
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile && !sidebar.classList.contains('collapsed')) {
        if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
            closeSidebar();
        }
    }
});

// Close sidebar on window resize if switching to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

// Initialize sidebar as closed on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
});

// Logout functionality
function confirmLogout() {
    window.location.href = '../logout.php';
}

// Close modal with Escape key for logout modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        // Also close report modal if open
        closeReportModal();
    }
});

function showLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Task filtering functionality
function setFilter(filter, event) {
    // Remove active class from all buttons
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    
    // Add active class to clicked button
    if (event) {
        event.target.classList.add('active');
    }
    
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const status = task.dataset.status;
        
        if (filter === 'all') {
            task.style.display = 'block';
        } else if (filter === 'inprogress' && status === 'inprogress') {
            task.style.display = 'block';
        } else if (filter === 'achieved' && status === 'achieved') {
            task.style.display = 'block';
        } else if (filter === 'nonachieved' && status === 'nonachieved') {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

// Task search functionality
function filterTasks() {
    const searchTerm = document.querySelector('.search-input').value.toLowerCase();
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const title = task.querySelector('.task-title').textContent.toLowerCase();
        const description = task.querySelector('.task-description').textContent.toLowerCase();
        const type = task.querySelector('.task-type').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm) || type.includes(searchTerm)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

// Task sorting functionality
function sortTasks(sortBy) {
    const grid = document.getElementById('tasksGrid');
    const tasks = Array.from(grid.querySelectorAll('.task-card'));
    
    tasks.sort((a, b) => {
        switch(sortBy) {
            case 'deadline':
                return new Date(a.dataset.deadline) - new Date(b.dataset.deadline);
            case 'priority':
                const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
                return priorityOrder[b.dataset.priority] - priorityOrder[a.dataset.priority];
            case 'status':
                return a.dataset.status.localeCompare(b.dataset.status);
            case 'type':
                return a.querySelector('.task-type').textContent.localeCompare(b.querySelector('.task-type').textContent);
            default:
                return 0;
        }
    });
    
    tasks.forEach(task => grid.appendChild(task));
}

// Report Modal Functions
function openReportModal(userTaskId, taskName, taskType, targetInt, targetStr) {
    console.log('Opening modal with:', { userTaskId, taskName, taskType, targetInt, targetStr });
    
    // Set hidden values
    document.getElementById('userTaskId').value = userTaskId;
    document.getElementById('taskType').value = taskType;
    document.getElementById('taskName').value = taskName;
    
    // Reset forms
    document.getElementById('numericForm').style.display = 'none';
    document.getElementById('textForm').style.display = 'none';
    document.getElementById('progressPercentageDiv').style.display = 'none';
    
    // Show appropriate form based on task type
    if (taskType === 'numeric') {
        document.getElementById('numericForm').style.display = 'block';
        document.getElementById('targetValue').value = targetInt;
        document.getElementById('achievedValue').value = '';
        document.getElementById('achievedValue').required = true;
    } else {
        document.getElementById('textForm').style.display = 'block';
        document.getElementById('targetText').value = targetStr;
        document.getElementById('completionStatus').value = '';
        document.getElementById('completionStatus').required = true;
    }
    
    // Clear notes
    document.getElementById('reportNotes').value = '';
    
    // Show modal using Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('reportModal'));
    modal.show();
}

function closeReportModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    if (modal) {
        modal.hide();
    }
}

function submitReport() {
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    
    // Basic validation
    const taskType = document.getElementById('taskType').value;
    
    if (taskType === 'numeric') {
        const achievedValue = document.getElementById('achievedValue').value;
        if (!achievedValue || isNaN(achievedValue)) {
            alert('Please enter a valid achieved value');
            return;
        }
    } else if (taskType === 'text') {
        const completionStatus = document.getElementById('completionStatus').value;
        if (!completionStatus) {
            alert('Please select completion status');
            return;
        }
        
        if (completionStatus === 'in_progress') {
            const progressPercentage = document.getElementById('progressPercentage').value;
            if (!progressPercentage || isNaN(progressPercentage) || progressPercentage < 0 || progressPercentage > 100) {
                alert('Please enter a valid progress percentage (0-100)');
                return;
            }
        }
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#reportModal .btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Submit via AJAX
    fetch('submit_report.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Close modal
            closeReportModal();
            
            // Show success notification
            showSuccessNotification();
            
            // Reload page after short delay to show updated task status
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while submitting the report');
    })
    .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function showSuccessNotification() {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        z-index: 3000;
        font-weight: 600;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.innerHTML = '✅ Task successfully reported!';
    
    document.body.appendChild(notification);
    
    // Animasi slide in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Hapus notifikasi setelah 3 detik
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    // Handle completion status change for text tasks
    const completionStatus = document.getElementById('completionStatus');
    const progressPercentageDiv = document.getElementById('progressPercentageDiv');
    
    if (completionStatus) {
        completionStatus.addEventListener('change', function() {
            if (this.value === 'in_progress') {
                progressPercentageDiv.style.display = 'block';
                document.getElementById('progressPercentage').required = true;
            } else {
                progressPercentageDiv.style.display = 'none';
                document.getElementById('progressPercentage').required = false;
                document.getElementById('progressPercentage').value = '';
            }
        });
    }
    
    // Mobile responsive adjustments
    function handleResize() {
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            closeSidebar();
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Initial check
});