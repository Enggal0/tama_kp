// Mobile sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);

    // Tambahkan class di body agar CSS bisa kontrol global
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

// Function to handle navigation with sidebar auto-close
function navigateWithSidebarClose(url) {
    // Close sidebar first
    closeSidebar();
    
    // Add a small delay to allow the animation to complete
    setTimeout(() => {
        window.location.href = url;
    }, 300); // 300ms matches the CSS transition duration
}

// Add event listeners to all navigation links
function setupNavigationLinks() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only prevent default if it's not the current page
            const href = this.getAttribute('href');
            const currentPage = window.location.pathname.split('/').pop();
            
            if (href && href !== currentPage && href !== '#') {
                e.preventDefault();
                navigateWithSidebarClose(href);
            }
        });
    });
}

// Close sidebar when clicking outside of it (mobile)
function setupClickOutside() {
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const burgerBtn = document.getElementById('burgerBtn');
        const isMobile = window.innerWidth <= 768;
        
        // Only apply this behavior on mobile
        if (isMobile && !sidebar.classList.contains('collapsed')) {
            // Check if click is outside sidebar and not on burger button
            if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
                closeSidebar();
            }
        }
    });
}

// Close sidebar on window resize if switching to desktop
function setupWindowResize() {
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;
        
        // If switching to desktop and sidebar is open, close it
        if (window.innerWidth > 768 && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}

// Initialize sidebar as closed on page load
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    // Always start with sidebar closed
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}
function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    modal.hide();
            
    // Redirect to login page
    window.location.href = '../logout.php';
    }

// Optional: Add keyboard shortcut
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) {
            modal.hide();
        }
        
        // Also close sidebar on Escape key
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar as closed
    initializeSidebar();
    
    // Setup navigation links
    setupNavigationLinks();
    
    // Setup click outside handler
    setupClickOutside();
    
    // Setup window resize handler
    setupWindowResize();
    
    // Add some loading animation
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

        // Delete Modal Functions
        let deleteModal;
        let successToast;
        
        document.addEventListener('DOMContentLoaded', function() {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            successToast = new bootstrap.Toast(document.getElementById('successToast'));
        });

        function showDeleteModal() {
            deleteModal.show();
        }

        function confirmDelete() {
            // Simulate deletion process
            const deleteBtn = document.querySelector('#deleteModal .btn-delete');
            const originalText = deleteBtn.innerHTML;
            
            deleteBtn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Deleting...';
            deleteBtn.disabled = true;
            
            setTimeout(() => {
                deleteModal.hide();
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                
                // Show success notification
                showSuccessNotification('Task deleted successfully!');
            }, 1500);
        }

        function showSuccessNotification(message) {
            document.getElementById('toastMessage').textContent = message;
            successToast.show();
        }

        // Auto-hide sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target) && 
                                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebar.classList.add('collapsed');
                document.getElementById('mainContent').classList.remove('expanded');
                document.body.classList.add('sidebar-collapsed');
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
        // This is handled by the main initialization function below
        // No need for duplicate event listeners
        });

    // Global variables
let currentPage = 1;
let rowsPerPage = 5;
let filteredData = [];
let allTasks = [];

// DOM Elements - will be initialized after DOM loads
let taskTable, searchInput, statusFilter, taskNameFilter, rowsPerPageSelect, paginationContainer;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DOM elements
    taskTable = document.getElementById('taskTable');
    searchInput = document.getElementById('searchInput');
    statusFilter = document.getElementById('statusFilter');
    taskNameFilter = document.getElementById('taskNameFilter');
    rowsPerPageSelect = document.getElementById('rowsPerPageSelect');
    paginationContainer = document.getElementById('pagination');
    
    // Only proceed if essential elements exist
    if (taskTable && searchInput && statusFilter) {
        initializeTable();
        setupEventListeners();
        updateStats();
    }
});

// Initialize table data
function initializeTable() {
    // Get all table rows (excluding header)
    const rows = Array.from(taskTable.querySelectorAll('tbody tr'));
    
    // Extract data from existing table rows
    allTasks = rows.map((row, index) => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 8) return null; // Skip empty rows
        
        return {
            id: index + 1,
            taskName: cells[0].textContent.trim(), // Task name (first column)
            employeeName: cells[1].textContent.trim(), // Employee name (second column)
            description: cells[2].textContent.trim(),
            deadline: cells[3].textContent.trim(),
            progress: cells[4].textContent.trim(),
            status: cells[5].querySelector('.badge') ? cells[5].querySelector('.badge').textContent.trim() : '',
            target: cells[6].textContent.trim(),
            element: row.cloneNode(true) // Store the original row element
        };
    }).filter(task => task !== null); // Remove null entries
    
    filteredData = [...allTasks];
    renderTable();
}

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            filterAndRenderTable();
        });
    }
    
    // Status filter
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            filterAndRenderTable();
        });
    }
    
    // Task name filter
    if (taskNameFilter) {
        taskNameFilter.addEventListener('change', function() {
            currentPage = 1;
            filterAndRenderTable();
        });
    }
    
    // Rows per page
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });
    }
}

// Filter and render table
function filterAndRenderTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const taskNameValue = taskNameFilter ? taskNameFilter.value.toLowerCase() : '';
    
    console.log('Filtering with:', { searchTerm, statusValue, taskNameValue });
    
    filteredData = allTasks.filter(task => {
        // Search functionality - check if search term matches any visible text
        const matchesSearch = searchTerm === '' || 
                            task.taskName.toLowerCase().includes(searchTerm) ||
                            task.employeeName.toLowerCase().includes(searchTerm) ||
                            task.description.toLowerCase().includes(searchTerm);
        
        // Status filter - exact match with status values
        const matchesStatus = statusValue === '' || 
                            task.status.toLowerCase() === statusValue;
        
        // Task name filter - exact match with task name
        const matchesTaskName = taskNameValue === '' || 
                              task.taskName.toLowerCase() === taskNameValue;
        
        return matchesSearch && matchesStatus && matchesTaskName;
    });
    
    console.log('Filtered results:', filteredData.length, 'out of', allTasks.length);
    
    // Reset to first page when filtering
    currentPage = 1;
    renderTable();
}

// Render table with pagination
function renderTable() {
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = filteredData.slice(startIndex, endIndex);
    
    // Clear existing table body
    const tbody = taskTable.querySelector('tbody');
    tbody.innerHTML = '';
    
    // Check if there are any results
    if (filteredData.length === 0) {
        // Show no results message
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-center py-4">
                    <div class="text-muted">
                        <i class="bi bi-inbox display-4"></i>
                        <p class="mt-3">No tasks found</p>
                        <p class="small">Try adjusting your filters</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        // Add filtered and paginated rows
        paginatedData.forEach(task => {
            const row = task.element.cloneNode(true);
            tbody.appendChild(row);
        });
    }
    
    // Update pagination
    renderPagination();
    
    // Update stats if needed
    // updateStats();
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>
        `;
        if (startPage > 2) {
            paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

// Change page function
function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    renderTable();
}

// Update statistics
function updateStats() {
    const totalTasks = filteredData.length;
    const achievedTasks = filteredData.filter(task => 
        task.status.toLowerCase().includes('achieved')).length;
    const nonAchievedTasks = filteredData.filter(task => 
        task.status.toLowerCase().includes('non achieved')).length;
    const achievementRate = totalTasks > 0 ? 
        Math.round((achievedTasks / totalTasks) * 100) : 0;
    
    // Update stats display
    document.getElementById('totalCount').textContent = totalTasks;
    document.getElementById('achievementRate').textContent = achievementRate + '%';
    document.getElementById('completedCount').textContent = achievedTasks;
    document.getElementById('overdueCount').textContent = nonAchievedTasks;
}

// Sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}

// Delete modal functionality
let selectedTaskId = null;
function showDeleteModal(taskId, taskName = 'this task') {
    selectedTaskId = taskId;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteTaskName').textContent = taskName;
    modal.show();
}

function confirmDelete() {
    if (!selectedTaskId) return;
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    // Disable button and show loading
    const deleteBtn = document.querySelector('#deleteModal .btn-delete');
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Deleting...';
    deleteBtn.disabled = true;

    fetch('delete_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ task_id: selectedTaskId })
    })
    .then(async response => {
        let data;
        try {
            data = await response.json();
        } catch (e) {
            throw new Error('Invalid server response');
        }
        modal.hide();
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        if (response.ok && data.success) {
            // Remove the row from the table
            const row = document.querySelector(`button[onclick*='showDeleteModal(${selectedTaskId},']`).closest('tr');
            if (row) row.remove();
            // No notification
        } else {
            alert(data.message || 'Failed to delete task.');
        }
        selectedTaskId = null;
    })
    .catch(err => {
        modal.hide();
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        alert('Gagal menghapus task: ' + (err.message || 'Unknown error'));
        selectedTaskId = null;
    });
}

// Success toast
function showSuccessToast(message) {
    const toast = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    if (toast && toastMessage) {
        toastMessage.textContent = message;
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    } else {
        // Fallback: use alert if toast not found
        alert(message);
    }
}

// Logout functionality
function logout() {
    // Add logout logic here
    localStorage.removeItem('user');
    window.location.href = '../login.html';
}

// Make functions globally accessible
window.toggleSidebar = toggleSidebar;
window.showDeleteModal = showDeleteModal;
window.confirmDelete = confirmDelete;
window.changePage = changePage;
window.logout = logout;

document.getElementById('searchInput').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#taskTable tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase(); // Kolom ke-2 adalah "Name"
        if (name.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
