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

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}


function navigateWithSidebarClose(url) {
    closeSidebar();
    
    setTimeout(() => {
        window.location.href = url;
    }, 300);
}

function setupNavigationLinks() {
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            const currentPage = window.location.pathname.split('/').pop();
            
            if (href && href !== currentPage && href !== '#') {
                e.preventDefault();
                navigateWithSidebarClose(href);
            }
        });
    });
}

function setupClickOutside() {
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
}

function setupWindowResize() {
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;
        
        if (window.innerWidth > 768 && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}

function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}
function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    modal.hide();
    window.location.href = '../logout.php';
    }

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) {
            modal.hide();
        }
        
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
    
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

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

        let deleteModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        });

        function showDeleteModal() {
            deleteModal.show();
        }

        function confirmDelete() {
            const deleteBtn = document.querySelector('#deleteModal .btn-delete');
            const originalText = deleteBtn.innerHTML;
            
            deleteBtn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Deleting...';
            deleteBtn.disabled = true;
            
            setTimeout(() => {
                deleteModal.hide();
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                showSuccessNotification('Task deleted successfully!');
            }, 1500);
        }

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
        });

let currentPage = 1;
let rowsPerPage = 5;
let filteredData = [];
let allTasks = [];

let taskTable, searchInput, statusFilter, taskNameFilter, rowsPerPageSelect, paginationContainer;

document.addEventListener('DOMContentLoaded', function() {
    taskTable = document.getElementById('taskTable');
    searchInput = document.getElementById('searchInput');
    statusFilter = document.getElementById('statusFilter');
    taskNameFilter = document.getElementById('taskNameFilter');
    rowsPerPageSelect = document.getElementById('rowsPerPageSelect');
    paginationContainer = document.getElementById('pagination');
    
    if (taskTable && searchInput && statusFilter) {
        initializeTable();
        setupEventListeners();
        updateStats();
    }
});

function initializeTable() {
    const rows = Array.from(taskTable.querySelectorAll('tbody tr'));
    
    allTasks = rows.map((row, index) => {
        const cells = row.querySelectorAll('td');
        if (cells.length < 8) return null;
        
        return {
            id: index + 1,
            taskName: cells[0].textContent.trim(),
            employeeName: cells[1].textContent.trim(),
            description: cells[2].textContent.trim(),
            deadline: cells[3].textContent.trim(),
            progress: cells[4].textContent.trim(),
            status: cells[5].querySelector('.badge') ? cells[5].querySelector('.badge').textContent.trim() : '',
            target: cells[6].textContent.trim(),
            element: row.cloneNode(true)
        };
    }).filter(task => task !== null);
    
    filteredData = [...allTasks];
    renderTable();
}


function setupEventListeners() {
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentPage = 1;
            filterAndRenderTable();
        });
    }
    
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            currentPage = 1;
            filterAndRenderTable();
        });
    }
    
    if (taskNameFilter) {
        taskNameFilter.addEventListener('change', function() {
            currentPage = 1;
            filterAndRenderTable();
        });
    }
    
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });
    }
}

function filterAndRenderTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const taskNameValue = taskNameFilter ? taskNameFilter.value.toLowerCase() : '';
    
    console.log('Filtering with:', { searchTerm, statusValue, taskNameValue });
    
    filteredData = allTasks.filter(task => {
        const matchesSearch = searchTerm === '' || 
                            task.taskName.toLowerCase().includes(searchTerm) ||
                            task.employeeName.toLowerCase().includes(searchTerm) ||
                            task.description.toLowerCase().includes(searchTerm);
        
        const matchesStatus = statusValue === '' || 
                            task.status.toLowerCase() === statusValue;
        
        const matchesTaskName = taskNameValue === '' || 
                              task.taskName.toLowerCase() === taskNameValue;
        
        return matchesSearch && matchesStatus && matchesTaskName;
    });
    
    console.log('Filtered results:', filteredData.length, 'out of', allTasks.length);
    
    currentPage = 1;
    renderTable();
}

function renderTable() {
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = filteredData.slice(startIndex, endIndex);
    
    const tbody = taskTable.querySelector('tbody');
    tbody.innerHTML = '';
    
    if (filteredData.length === 0) {
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
        paginatedData.forEach(task => {
            const row = task.element.cloneNode(true);
            tbody.appendChild(row);
        });
    }
    
    renderPagination();
}

function renderPagination() {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
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
    
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    renderTable();
}

function updateStats() {
    const totalTasks = filteredData.length;
    const achievedTasks = filteredData.filter(task => 
        task.status.toLowerCase().includes('achieved')).length;
    const nonAchievedTasks = filteredData.filter(task => 
        task.status.toLowerCase().includes('non achieved')).length;
    const achievementRate = totalTasks > 0 ? 
        Math.round((achievedTasks / totalTasks) * 100) : 0;
    
    document.getElementById('totalCount').textContent = totalTasks;
    document.getElementById('achievementRate').textContent = achievementRate + '%';
    document.getElementById('completedCount').textContent = achievedTasks;
    document.getElementById('overdueCount').textContent = nonAchievedTasks;
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}

let taskToDelete = null;

function showDeleteModal(taskId, taskName) {
    console.log('showDeleteModal called with:', { taskId, taskName });
    taskToDelete = { id: taskId, name: taskName };
    document.getElementById('deleteTaskName').textContent = taskName;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function confirmDelete() {
    console.log('confirmDelete called with taskToDelete:', taskToDelete);
    if (!taskToDelete) {
        console.error('No task selected for deletion');
        return;
    }

    const confirmBtn = document.querySelector('#deleteModal .btn-delete');
    if (!confirmBtn) {
        console.error('Delete button not found');
        return;
    }
    
    const originalText = confirmBtn.innerHTML;
    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Deleting...';
    confirmBtn.disabled = true;

    console.log('Sending delete request for task ID:', taskToDelete.id);

    fetch('delete_task.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            task_id: taskToDelete.id
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
            deleteModal.hide();

            showNotification('Task deleted successfully!', 'success');
            
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(data.message || 'Failed to delete task');
        }
    })
    .catch(error => {
        console.error('Error deleting task:', error);
        showNotification('Error deleting task: ' + error.message, 'error');
    })
    .finally(() => {
        confirmBtn.innerHTML = originalText;
        confirmBtn.disabled = false;
        taskToDelete = null;
    });
}

function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification-toast');
    existingNotifications.forEach(notification => notification.remove());

    const notification = document.createElement('div');
    notification.className = `notification-toast alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    `;
    
    notification.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

function logout() {
    localStorage.removeItem('user');
    window.location.href = '../login.html';
}

window.toggleSidebar = toggleSidebar;
window.showDeleteModal = showDeleteModal;
window.confirmDelete = confirmDelete;
window.changePage = changePage;
window.logout = logout;

document.getElementById('searchInput').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#taskTable tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase();
        if (name.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Initialize tooltips for disabled buttons
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Add custom tooltip behavior for disabled edit buttons
    const disabledEditButtons = document.querySelectorAll('.action-btn[disabled]');
    disabledEditButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            // Show custom tooltip or message
            this.setAttribute('data-bs-original-title', 'Cannot edit achieved tasks');
        });
    });
});
