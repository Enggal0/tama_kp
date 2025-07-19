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
        const logoutModal = document.getElementById('logoutModal');
        if (logoutModal) {
            const modalInstance = bootstrap.Modal.getInstance(logoutModal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        
        // Also close report modal if open
        const reportModal = document.getElementById('reportTaskModal');
        if (reportModal) {
            const reportModalInstance = bootstrap.Modal.getInstance(reportModal);
            if (reportModalInstance) {
                reportModalInstance.hide();
            }
        }
    }
});

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
        
        if (title.includes(searchTerm) || description.includes(searchTerm)) {
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
    
    if (!sortBy) {
        return; // No sorting if no option selected
    }
    
    tasks.sort((a, b) => {
        let result = 0;
        
        switch(sortBy) {
            case 'name-asc':
                const nameA = a.querySelector('.task-title').textContent.trim();
                const nameB = b.querySelector('.task-title').textContent.trim();
                result = nameA.localeCompare(nameB);
                break;
                
            case 'name-desc':
                const nameDescA = a.querySelector('.task-title').textContent.trim();
                const nameDescB = b.querySelector('.task-title').textContent.trim();
                result = nameDescB.localeCompare(nameDescA);
                break;
                
            case 'deadline-asc':
                result = new Date(a.dataset.deadline) - new Date(b.dataset.deadline);
                break;
                
            case 'deadline-desc':
                result = new Date(b.dataset.deadline) - new Date(a.dataset.deadline);
                break;
                
            case 'status-asc':
                result = a.dataset.status.localeCompare(b.dataset.status);
                break;
                
            case 'status-desc':
                result = b.dataset.status.localeCompare(a.dataset.status);
                break;
                
            // Keep legacy options for backward compatibility
            case 'deadline':
                result = new Date(a.dataset.deadline) - new Date(b.dataset.deadline);
                break;
            case 'priority':
                const priorityOrder = { 'high': 3, 'medium': 2, 'low': 1 };
                result = priorityOrder[b.dataset.priority] - priorityOrder[a.dataset.priority];
                break;
            case 'status':
                result = a.dataset.status.localeCompare(b.dataset.status);
                break;
            case 'type':
                result = a.dataset.type.localeCompare(b.dataset.type);
                break;
                
            default:
                return 0;
        }
        
        return result;
    });
    
    // Re-append sorted tasks to maintain order
    tasks.forEach(task => grid.appendChild(task));
}