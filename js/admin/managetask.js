function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);
    body.classList.toggle('sidebar-collapsed', isCollapsed);
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
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

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) modal.hide();
    window.location.href = '../logout.php';
}

let currentPage = 1;
let rowsPerPage = 5;
let filteredData = [];
let allTasks = [];
let taskTable, searchInput, statusFilter, taskNameFilter, rowsPerPageSelect, paginationContainer;

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
    
    filteredData = allTasks.filter(task => {
        const matchesSearch = searchTerm === '' || 
                            task.taskName.toLowerCase().includes(searchTerm) ||
                            task.employeeName.toLowerCase().includes(searchTerm) ||
                            task.description.toLowerCase().includes(searchTerm);
        
        const matchesStatus = statusValue === '' || 
                            task.status.toLowerCase().includes(statusValue);
        
        const matchesTaskName = taskNameValue === '' || 
                              task.taskName.toLowerCase() === taskNameValue;
        
        return matchesSearch && matchesStatus && matchesTaskName;
    });
    
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
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(1)">1</a></li>`;
        if (startPage > 2) {
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
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
            paginationHTML += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a></li>`;
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
    const deleteBtn = document.querySelector('#deleteModal .btn-delete');
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;

    fetch('delete_task.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
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
            
            showSuccessNotification(data.message || 'Task deleted successfully');
            
            
            const row = document.querySelector(`button[onclick*='showDeleteModal(${selectedTaskId},']`).closest('tr');
            if (row) {
                row.remove();
                
                if (taskTable) initializeTable();
            }
        } else {
            showErrorNotification(data.message || 'Failed to delete task.');
        }
        selectedTaskId = null;
    })
    .catch(err => {
        modal.hide();
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        showErrorNotification('Failed to delete task: ' + (err.message || 'Unknown error'));
        selectedTaskId = null;
    });
}

function showSuccessNotification(message = 'Operation completed successfully') {
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
    notification.innerHTML = '✅ ' + message;
    
    document.body.appendChild(notification);
    requestAnimationFrame(() => notification.style.transform = 'translateX(0)');
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function showErrorNotification(message = 'An error occurred') {
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        z-index: 3000;
        font-weight: 600;
        transform: translateX(100%);
        transition: transform 0.3s ease;
    `;
    notification.innerHTML = '❌ ' + message;
    
    document.body.appendChild(notification);
    requestAnimationFrame(() => notification.style.transform = 'translateX(0)');
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

document.addEventListener('DOMContentLoaded', function() {
    closeSidebar();
    setupClickOutside();
    
    taskTable = document.getElementById('taskTable');
    searchInput = document.getElementById('searchInput');
    statusFilter = document.getElementById('statusFilter');
    taskNameFilter = document.getElementById('taskNameFilter');
    rowsPerPageSelect = document.getElementById('rowsPerPageSelect');
    paginationContainer = document.getElementById('pagination');
    
    if (taskTable && searchInput && statusFilter) {
        initializeTable();
        setupEventListeners();
    }
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification('Task created successfully!');
        
        history.replaceState(null, '', window.location.pathname);
    }
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const logoutModal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        const deleteModal = bootstrap.Modal.getInstance(document.getElementById('deleteModal')); 
        if (logoutModal) logoutModal.hide();
        if (deleteModal) deleteModal.hide();
        
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

window.toggleSidebar = toggleSidebar;
window.showDeleteModal = showDeleteModal;
window.confirmDelete = confirmDelete;
window.changePage = changePage;