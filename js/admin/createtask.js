$(document).ready(function() {
    // Initialize Select2 for multiple task selection
    $('#taskTypes').select2({
        placeholder: 'Select task types',
        allowClear: true,
        width: '100%'
    });
    
    // Initialize sidebar management
    initializeSidebarManagement();
});

function validateField($field) {
    const isRequired = $field.prop('required');
    const value = $field.val();
    
    if (isRequired && (!value || (Array.isArray(value) && value.length === 0))) {
        $field.addClass('is-invalid');
        return false;
    } else {
        $field.removeClass('is-invalid');
        return true;
    }
}

function showSuccessToast() {
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
}

function addTaskToTable(taskData) {
    const taskBadges = taskData.tasks.map(task => 
        `<span class="badge bg-primary me-1">${task}</span>`
    ).join('');

    const statusClass = {
        'Pending': 'bg-warning',
        'In Progress': 'bg-info',
        'Completed': 'bg-success',
        'Overdue': 'bg-danger'
    };

    const row = `
        <tr>
            <td>${taskData.employee}</td>
            <td>${taskBadges}</td>
            <td>${taskData.description}</td>
            <td>${taskData.deadline}</td>
            <td><span class="badge ${statusClass[taskData.status]}">${taskData.status}</span></td>
            <td>${taskData.target}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteTask(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;

    $('#taskTable tbody').append(row);
}

// Enhanced success notification function
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
    notification.innerHTML = '✅ Task added successfully!';
    
    document.body.appendChild(notification);
    
    // Slide in animation
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Cancel edit function
function cancelEdit() {
    if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
        // Close sidebar before navigating
        closeSidebar();
        setTimeout(() => {
            window.location.href = 'managetask.php';
        }, 300);
    }
}

// Enhanced sidebar management functions
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);

    // Add class to body for global CSS control
    if (isCollapsed) {
        body.classList.add('sidebar-collapsed');
        // Store sidebar state
        localStorage.setItem('sidebarState', 'collapsed');
    } else {
        body.classList.remove('sidebar-collapsed');
        localStorage.setItem('sidebarState', 'open');
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
    
    // Update stored state
    localStorage.setItem('sidebarState', 'collapsed');
}

// Function to open sidebar
function openSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.remove('collapsed');
    mainContent.classList.remove('collapsed');
    body.classList.remove('sidebar-collapsed');
    
    // Update stored state
    localStorage.setItem('sidebarState', 'open');
}

// Enhanced navigation function with sidebar close
function navigateWithSidebarClose(url) {
    // Always close sidebar before navigation
    closeSidebar();
    
    // Add loading state
    document.body.style.opacity = '0.8';
    
    // Navigate after animation completes
    setTimeout(() => {
        window.location.href = url;
    }, 300);
}

// Setup navigation links with enhanced behavior
function setupNavigationLinks() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            const currentPage = window.location.pathname.split('/').pop();
            
            // Prevent default and handle navigation
            if (href && href !== currentPage && href !== '#') {
                e.preventDefault();
                navigateWithSidebarClose(href);
            }
        });
    });
}

// Enhanced click outside handler
function setupClickOutside() {
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const burgerBtn = document.getElementById('burgerBtn');
        const isMobile = window.innerWidth <= 768;
        
        // Close sidebar on mobile when clicking outside
        if (isMobile && !sidebar.classList.contains('collapsed')) {
            if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
                closeSidebar();
            }
        }
    });
}

// Enhanced window resize handler
function setupWindowResize() {
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        
        // Always close sidebar on resize to prevent layout issues
        if (window.innerWidth <= 768) {
            closeSidebar();
        } else {
            // On desktop, respect the stored state but default to closed
            const storedState = localStorage.getItem('sidebarState');
            if (storedState === 'open') {
                // Keep closed for consistency
                closeSidebar();
            }
        }
    });
}

// Initialize sidebar state
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    // Always start with sidebar closed for consistency
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
    
    // Set initial state in localStorage
    localStorage.setItem('sidebarState', 'collapsed');
}

// Enhanced sidebar management initialization
function initializeSidebarManagement() {
    // Initialize sidebar as closed
    initializeSidebar();
    
    // Setup all event handlers
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
    
    // Add page visibility change handler to close sidebar when page becomes hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            closeSidebar();
        }
    });
    
    // Add beforeunload handler to close sidebar before page unload
    window.addEventListener('beforeunload', function() {
        closeSidebar();
    });
    
    // Add focus/blur handlers for additional consistency
    window.addEventListener('blur', function() {
        closeSidebar();
    });
}

// Real-time validation feedback
document.addEventListener('DOMContentLoaded', function() {
    const formFields = document.querySelectorAll('.form-input, .form-select');
    
    formFields.forEach(field => {
        field.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.style.borderColor = '#dc3545';
            } else {
                this.style.borderColor = '#e1e5e9';
            }
        });

        field.addEventListener('input', function() {
            if (this.style.borderColor === 'rgb(220, 53, 69)' && this.value.trim()) {
                this.style.borderColor = '#e1e5e9';
            }
        });
    });
});

// Enhanced form submission
document.addEventListener('DOMContentLoaded', function() {
    const taskForm = document.getElementById('taskForm');
    if (taskForm) {
        taskForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const name = document.getElementById('employeeName').value.trim();
            const desc = document.getElementById('taskDesc').value.trim();
            const deadline = document.getElementById('deadline').value.trim();
            const target = document.getElementById('target').value.trim();
            const taskTypes = $('#taskTypes').val();

            if (!name || !taskTypes || taskTypes.length === 0 || !deadline || !target) {
                alert('Mohon lengkapi semua field yang wajib diisi.');
                return;
            }

            showSuccessNotification();

            // Close sidebar and navigate after success
            setTimeout(() => {
                closeSidebar();
                setTimeout(() => {
                    window.location.href = 'managetask.php';
                }, 300);
            }, 2000);
        });
    }
});

// Enhanced priority selection
document.addEventListener('DOMContentLoaded', function() {
    const priorityOptions = document.querySelectorAll('.priority-option');
    let selectedPriority = '';

    priorityOptions.forEach(option => {
        option.addEventListener('click', () => {
            priorityOptions.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            selectedPriority = option.dataset.priority;
        });
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Close sidebar on Escape key
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
    
    // Toggle sidebar with Ctrl+B
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
    }
});

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth loading animation
    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
    
    // Initialize sidebar management
    initializeSidebarManagement();
});

function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmLogout() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            modal.hide();
            
            // Redirect to login page
            window.location.href = '../logout.php';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Add some loading animation
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

// Export functions for global use
window.toggleSidebar = toggleSidebar;
window.closeSidebar = closeSidebar;
window.openSidebar = openSidebar;
window.cancelEdit = cancelEdit;
window.navigateWithSidebarClose = navigateWithSidebarClose;