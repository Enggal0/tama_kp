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

function navigateWithCloseSidebar(url, event) {
    event.preventDefault();
    
    const currentPage = window.location.pathname.split('/').pop();
    
    if (url !== currentPage) {
        closeSidebar();
        window.location.href = url;
    }
}

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

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    closeSidebar();
});

function confirmLogout() {
    window.location.href = '../logout.php';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
                const reportModal = document.getElementById('reportTaskModal');
        if (reportModal) {
            const reportModalInstance = bootstrap.Modal.getInstance(reportModal);
            if (reportModalInstance) {
                reportModalInstance.hide();
            }
        }
    }
});

function setFilter(filter, event) {
        document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    
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
        } else if (filter === 'passed' && status === 'passed') {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

function filterTasks() {
    const searchTerm = document.querySelector('.search-input')?.value.toLowerCase() || '';
    const tasks = document.querySelectorAll('.task-card');

    tasks.forEach(task => {
        const title = task.querySelector('.task-title')?.textContent.toLowerCase() || '';
        const description = task.querySelector('.task-description')?.textContent.toLowerCase() || '';

        if (title.includes(searchTerm) || description.includes(searchTerm)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

function sortTasks(sortBy) {
    const grid = document.getElementById('tasksGrid');
    const tasks = Array.from(grid.querySelectorAll('.task-card'));
    
    if (!sortBy) {
                tasks.sort((a, b) => {
            const statusA = a.dataset.status || '';
            const statusB = b.dataset.status || '';
            if (statusA === 'inprogress' && statusB !== 'inprogress') return -1;
            if (statusA !== 'inprogress' && statusB === 'inprogress') return 1;
            return 0;
        });
    } else {
        tasks.sort((a, b) => {
            switch(sortBy) {
                case 'name-asc':
                    const nameA = a.dataset.taskName || '';
                    const nameB = b.dataset.taskName || '';
                    return nameA.localeCompare(nameB);
                case 'name-desc':
                    const nameA2 = a.dataset.taskName || '';
                    const nameB2 = b.dataset.taskName || '';
                    return nameB2.localeCompare(nameA2);
                case 'enddate-asc':
                    const dateA = new Date(a.dataset.endDate || '1970-01-01');
                    const dateB = new Date(b.dataset.endDate || '1970-01-01');
                    return dateA - dateB;
                case 'enddate-desc':
                    const dateA2 = new Date(a.dataset.endDate || '1970-01-01');
                    const dateB2 = new Date(b.dataset.endDate || '1970-01-01');
                    return dateB2 - dateA2;
                case 'status-asc':
                    const statusA = a.dataset.status || '';
                    const statusB = b.dataset.status || '';
                    return statusA.localeCompare(statusB);
                case 'status-desc':
                    const statusA2 = a.dataset.status || '';
                    const statusB2 = b.dataset.status || '';
                    return statusB2.localeCompare(statusA2);
                default:
                    return 0;
            }
        });
    }
    tasks.forEach(task => grid.appendChild(task));
}

function filterByTaskName(taskName) {
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const taskNameAttr = task.dataset.taskName?.toLowerCase() || '';
        const selectedTaskName = taskName.toLowerCase();

        if (!selectedTaskName || taskNameAttr === selectedTaskName) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

function openReportModalFromCard(card) {
        const userTaskId = card.getAttribute('data-task-id');
    const taskName = card.getAttribute('data-task-name');
    const taskDesc = card.getAttribute('data-task-desc');
    const targetInt = card.getAttribute('data-target-int');
    const targetStr = card.getAttribute('data-target-str');
    const taskType = card.getAttribute('data-task-type');
    
        document.getElementById('reportTaskId').value = userTaskId;
    document.getElementById('reportTaskName').textContent = taskName || '';
    document.getElementById('reportTaskDesc').textContent = taskDesc || '';
    
    if (taskType === 'numeric') {
        document.getElementById('reportTaskTarget').textContent = targetInt || '-';
        document.getElementById('reportTypeNumeric').style.display = 'block';
        document.getElementById('reportTypeString').style.display = 'none';
        document.getElementById('workOrdersCompletedGroup').style.display = 'none';
        
                const progressInput = document.getElementById('progressInput');
        const autoStatusInput = document.getElementById('autoStatus');
        
        function updateAutoStatusNumeric() {
            const target = parseInt(targetInt, 10) || 0;
            const completed = parseInt(progressInput.value, 10) || 0;
            if (target > 0 && completed >= target) {
                autoStatusInput.value = 'Achieved';
            } else {
                autoStatusInput.value = 'Non Achieved';
            }
        }
        
        progressInput.addEventListener('input', updateAutoStatusNumeric);
        updateAutoStatusNumeric();
    } else {
        document.getElementById('reportTaskTarget').textContent = targetStr || '-';
        document.getElementById('reportTypeNumeric').style.display = 'none';
        document.getElementById('reportTypeString').style.display = 'block';
        document.getElementById('workOrdersCompletedGroup').style.display = 'block';
        
                const workOrdersInput = document.getElementById('workOrdersInput');
        const workOrdersCompletedInput = document.getElementById('workOrdersCompletedInput');
        const autoStatusInput = document.getElementById('autoStatus');
        
        function updateAutoStatus() {
            const workOrders = parseInt(workOrdersInput.value, 10) || 0;
            const workOrdersCompleted = parseInt(workOrdersCompletedInput.value, 10) || 0;
            if (workOrders > 0 && workOrdersCompleted >= workOrders) {
                autoStatusInput.value = 'Achieved';
            } else {
                autoStatusInput.value = 'Non Achieved';
            }
        }
        
        workOrdersInput.addEventListener('input', updateAutoStatus);
        workOrdersCompletedInput.addEventListener('input', updateAutoStatus);
        updateAutoStatus();
    }
    
        document.getElementById('kendalaSelect').value = '';
    document.getElementById('kendalaCustom').style.display = 'none';
    document.getElementById('kendalaCustom').value = '';
    document.getElementById('autoStatus').value = '';
    
        const modalElement = document.getElementById('reportTaskModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
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
    
        setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
        setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('report-btn')) {
            openReportModalFromCard(e.target);
        }
    });

        sortTasks();
});