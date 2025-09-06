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
    const stats = {
        totalCount: document.getElementById('totalCount'),
        achievementRate: document.getElementById('achievementRate'), 
        completedCount: document.getElementById('completedCount'),
        overdueCount: document.getElementById('overdueCount')
    };

    const statsCards = document.querySelectorAll('.stats-card');
    statsCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.transition = 'transform 0.3s ease';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    animateStats();

    closeSidebar();
    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('report-btn')) {
            openReportModalFromCard(e.target);
        }
    });

    sortTasks();

    var kendalaSelect = document.getElementById('kendalaSelect');
    var kendalaCustom = document.getElementById('kendalaCustom');
    if (kendalaSelect && kendalaCustom) {
        kendalaSelect.addEventListener('change', function() {
            kendalaCustom.style.display = (kendalaSelect.value === 'Other') ? '' : 'none';
            if (kendalaSelect.value === 'Other') kendalaCustom.focus();
            if (kendalaSelect.value !== 'Other') kendalaCustom.value = '';
        });
    }
});

function animateStats() {
    const stats = document.querySelectorAll('.stats-value');
    
    stats.forEach(stat => {
        const value = stat.innerText;
        const finalValue = parseInt(value.replace('%', ''));
        
        animateValue(stat, 0, finalValue, 1000);
    });
}

function animateValue(element, start, end, duration) {
    const isPercentage = element.id === 'achievementRate';
    const range = end - start;
    const increment = range / (duration / 16);
    let current = start;
    
    const timer = setInterval(() => {
        current += increment;
        
        if ((increment > 0 && current >= end) || (increment < 0 && current <= end)) {
            current = end;
            clearInterval(timer);
        }
        
        element.textContent = Math.round(current) + (isPercentage ? '%' : '');
    }, 16);
}

function updateStats(newStats) {
    const stats = {
        totalCount: document.getElementById('totalCount'),
        achievementRate: document.getElementById('achievementRate'),
        completedCount: document.getElementById('completedCount'),
        overdueCount: document.getElementById('overdueCount')
    };

    Object.keys(newStats).forEach(key => {
        if (stats[key]) {
            const currentValue = parseInt(stats[key].innerText);
            const newValue = newStats[key];
            animateValue(stats[key], currentValue, newValue, 500);
        }
    });
}

function confirmLogout() {
    window.location.href = '../logout.php';
}

function setFilter(filter, event) {
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    if (event) event.target.classList.add('active');

    const tasks = document.querySelectorAll('.task-card');
    tasks.forEach(task => {
        const status = task.dataset.status;
        if (filter === 'all' || 
            (filter === 'inprogress' && status === 'inprogress') ||
            (filter === 'achieved' && status === 'achieved') ||
            (filter === 'nonachieved' && status === 'nonachieved') ||
            (filter === 'passed' && status === 'passed')
        ) {
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
                    return (a.dataset.taskName || '').localeCompare(b.dataset.taskName || '');
                case 'name-desc':
                    return (b.dataset.taskName || '').localeCompare(a.dataset.taskName || '');
                case 'enddate-asc':
                    return new Date(a.dataset.endDate || '1970-01-01') - new Date(b.dataset.endDate || '1970-01-01');
                case 'enddate-desc':
                    return new Date(b.dataset.endDate || '1970-01-01') - new Date(a.dataset.endDate || '1970-01-01');
                case 'status-asc':
                    return (a.dataset.status || '').localeCompare(b.dataset.status || '');
                case 'status-desc':
                    return (b.dataset.status || '').localeCompare(a.dataset.status || '');
                default:
                    return 0;
            }
        });
    }
    tasks.forEach(task => grid.appendChild(task));
}

function filterByTaskName(taskName) {
    const tasks = document.querySelectorAll('.task-card');
    const selectedTaskName = taskName.toLowerCase();

    tasks.forEach(task => {
        const taskNameAttr = task.dataset.taskName?.toLowerCase() || '';
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
            autoStatusInput.value = (target > 0 && completed >= target) ? 'Achieved' : 'Non Achieved';
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
            autoStatusInput.value = (workOrders > 0 && workOrdersCompleted >= workOrders) ? 'Achieved' : 'Non Achieved';
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