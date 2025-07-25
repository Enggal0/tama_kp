

        let taskChart, performanceChart;

        function initCharts() {
            // Task Distribution Chart
            const taskCtx = document.getElementById('taskChart').getContext('2d');
            const taskTypes = [...new Set(taskData.map(item => item.task_name))];
            const taskCounts = taskTypes.map(type => taskData.filter(item => item.task_name === type).length);

            taskChart = new Chart(taskCtx, {
                type: 'doughnut',
                data: {
                    labels: taskTypes,
                    datasets: [{
                        data: taskCounts,
                        backgroundColor: [
                            '#c41e3a', '#2c5aa0', '#28a745', '#ffc107', '#dc3545',
                            '#6c757d', '#17a2b8', '#fd7e14', '#e83e8c', '#6f42c1'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Task Distribution by Type'
                        }
                    }
                }
            });

            // Performance Chart - Only Achieved and Non-Achieved
            const perfCtx = document.getElementById('performanceChart').getContext('2d');
            const achieveCount = taskData.filter(item => item.status === 'achieved').length;
            const nonAchieveCount = taskData.filter(item => item.status === 'non achieved').length;

            performanceChart = new Chart(perfCtx, {
                type: 'bar',
                data: {
                    labels: ['Achieved', 'Non-Achieved'],
                    datasets: [{
                        label: 'Task Count',
                        data: [achieveCount, nonAchieveCount],
                        backgroundColor: ['#28a745', '#dc3545']
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Achievement Status Overview'
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                // This function generates custom legend items
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        return data.labels.map(function(label, i) {
                                            const meta = chart.getDatasetMeta(0);
                                            const style = meta.controller.getStyle(i);
                                            return {
                                                text: label,
                                                fillStyle: style.backgroundColor,
                                                strokeStyle: style.borderColor,
                                                lineWidth: style.borderWidth,
                                                hidden: isNaN(data.datasets[0].data[i]) || meta.data[i].hidden,
                                                index: i
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                // Custom tooltip to show status and count
                                label: function(context) {
                                    return `${context.label}: ${context.parsed.y} Tasks`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function populateEmployeeDetails() {
            const container = document.getElementById('employeeDetails');
            container.innerHTML = '';

            taskData.forEach(employee => {
                const achievementPercentage = Math.round((employee.progress / employee.target) * 100);
                const card = document.createElement('div');
                card.className = 'col-md-6 col-lg-4 mb-3';
                card.innerHTML = `
                    <div class="employee-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">${employee.employee}</h6>
                            <span class="achievement-badge ${employee.status}">${employee.status === 'achieved' ? 'Achieved' : 'Non-Achieved'}</span>
                        </div>
                        <p class="text-muted mb-2">${employee.task_name}</p>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Progress: ${employee.progress}/${employee.target}</small>
                            <small class="fw-bold">${achievementPercentage}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar ${employee.status === 'achieved' ? 'bg-success' : 'bg-danger'}" 
                                 style="width: ${Math.min(achievementPercentage, 100)}%"></div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

function filterTasks() {
    const employeeFilter = document.getElementById('employeeFilter').value.trim();
    const taskFilter = document.getElementById('taskFilter').value.trim();
    const data = getFilteredData();
    
    let filteredData = [...taskData]; // Create a copy of the original data
    
    // Apply employee filter if selected
    if (employeeFilter && employeeFilter !== '') {
        filteredData = filteredData.filter(item => item.employee === employeeFilter);
    }
    
    // Apply task type filter if selected
    if (taskFilter && taskFilter !== '') {
        filteredData = filteredData.filter(item => item.task_name === taskFilter);
    }
    
    updateCharts(filteredData);
    updateEmployeeDetails(filteredData);
    
    // Also update progress chart if filters affect it
    updateProgressChart();
}

        function updateCharts(data) {
            // Update task distribution chart
            const taskTypes = [...new Set(data.map(item => item.task_name))];
            const taskCounts = taskTypes.map(type => data.filter(item => item.task_name === type).length);
            
            taskChart.data.labels = taskTypes;
            taskChart.data.datasets[0].data = taskCounts;
            taskChart.update();

            // Update performance chart - Only Achieved and Non-Achieved
            const achieveCount = data.filter(item => item.status === 'achieved').length;
            const nonAchieveCount = data.filter(item => item.status === 'non achieved').length;

            performanceChart.data.labels = ['Achieved', 'Non-Achieved'];
            performanceChart.data.datasets[0].data = [achieveCount, nonAchieveCount];
            performanceChart.update();
        }

        function updateEmployeeDetails(data) {
            const container = document.getElementById('employeeDetails');
            container.innerHTML = '';

            data.forEach(employee => {
                const achievementPercentage = Math.round((employee.progress / employee.target) * 100);
                const card = document.createElement('div');
                card.className = 'col-md-6 col-lg-4 mb-3';
                card.innerHTML = `
                    <div class="employee-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">${employee.employee}</h6>
                            <span class="achievement-badge ${employee.status}">${employee.status === 'achieved' ? 'Achieved' : 'Non-Achieved'}</span>
                        </div>
                        <p class="text-muted mb-2">${employee.task_name}</p>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Progress: ${employee.progress}/${employee.target}</small>
                            <small class="fw-bold">${achievementPercentage}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar ${employee.status === 'achieved' ? 'bg-success' : 'bg-danger'}" 
                                 style="width: ${Math.min(achievementPercentage, 100)}%"></div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

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

    document.getElementById('employeeFilter').addEventListener('change', filterTasks);
    document.getElementById('taskFilter').addEventListener('change', filterTasks);
    document.getElementById('start_date').addEventListener('change', filterTasks);
    document.getElementById('end_date').addEventListener('change', filterTasks);
});

        // Inisialisasi saat halaman dimuat
        window.onload = () => {
            initCharts();
            initProgressChart();
        };

        let progressChart;
let currentChartType = 'bar';

// Fungsi untuk inisialisasi progress chart
function initProgressChart() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    progressChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Target',
                data: [],
                backgroundColor: 'rgba(40, 167, 69, 0.8)',
                borderColor: 'rgba(40, 167, 69, 1)',
                borderWidth: 1
            }, {
                label: 'Progress',
                data: [],
                backgroundColor: 'rgba(108, 117, 125, 0.3)',
                borderColor: 'rgba(108, 117, 125, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Values'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Task'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Employee Progress vs Target'
                },
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        generateLabels: function(chart) {
                            return [
                                {
                                    text: 'Target',
                                    fillStyle: 'rgba(40, 167, 69, 0.8)',
                                    strokeStyle: 'rgba(40, 167, 69, 1)',
                                    lineWidth: 2,
                                    hidden: false,
                                    index: 0
                                },
                                {
                                    text: 'Progress',
                                    fillStyle: 'rgba(108, 117, 125, 0.3)',
                                    strokeStyle: 'rgba(108, 117, 125, 1)',
                                    lineWidth: 2,
                                    hidden: false,
                                    index: 1
                                }
                            ];
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const dataIndex = context.dataIndex;
                            const task = getCurrentProgressData()[dataIndex];
                            if (task) {
                                return `${context.dataset.label}: ${context.parsed.y} ${task.unit}`;
                            }
                            return `${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    });
    
    updateProgressChart();
}

// Fungsi untuk mendapatkan data progress saat ini
function getCurrentProgressData() {
    const employeeFilter = document.getElementById('progressEmployeeFilter').value.trim();
    const taskFilter = document.getElementById('progressTaskFilter').value.trim(); // Tambahkan ini
    let data = [...taskData];

    if (employeeFilter && employeeFilter !== '') {
        data = data.filter(item => item.employee === employeeFilter);
    }

    if (taskFilter && taskFilter !== '') {
        data = data.filter(item => item.task_name === taskFilter); // Tambahkan ini
    }

    return data;
}

// Fungsi untuk update progress chart
function updateProgressChart() {
    const data = getCurrentProgressData();

    // Group data by task type
    const grouped = {};
    data.forEach(item => {
        if (!grouped[item.task_name]) grouped[item.task_name] = [];
        grouped[item.task_name].push(item);
    });

    // Prepare chart data
    const labels = Object.keys(grouped);
    const achievementRates = labels.map(type => {
        const items = grouped[type];
        const total = items.length;
        const achieved = items.filter(i => i.status === 'achieved').length;
        return total > 0 ? Math.round((achieved / total) * 100) : 0;
    });
    
    const targetLine = labels.map(() => 100); // Target is always 100%
    
    // Update chart
    progressChart.data.labels = labels;
    progressChart.data.datasets[0].data = targetLine; // Target goes to first dataset (left bars)
    progressChart.data.datasets[1].data = achievementRates; // Achievement rate goes to second dataset (right bars)
    
    // Update colors for achievement rates (now in dataset[1])
    progressChart.data.datasets[1].backgroundColor = achievementRates.map(rate => {
        return rate >= 80 ? 'rgba(40, 167, 69, 0.8)' : 
               rate >= 60 ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)';
    });
    
    progressChart.data.datasets[1].borderColor = achievementRates.map(rate => {
        return rate >= 80 ? 'rgba(40, 167, 69, 1)' : 
               rate >= 60 ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)';
    });
    progressChart.update();
    // Update table
    updateProgressTable(data);
}

// Fungsi untuk update progress table
function updateProgressTable(data) {
    const tbody = document.getElementById('progressTableBody');
    tbody.innerHTML = '';

    // Group by task/type
    const grouped = {};
    data.forEach(item => {
        if (!grouped[item.task_name]) grouped[item.task_name] = [];
        grouped[item.task_name].push(item);
    });

    // Set header sesuai permintaan
    const thead = tbody.parentElement.querySelector('thead');
    if (thead) {
        thead.innerHTML = `
            <tr>
                <th>Task</th>
                <th>Employee</th>
                <th>Total</th>
                <th>Achieved</th>
                <th>Non Achieved</th>
                <th>Completed</th>
                <th>Achievement Rate (%)</th>
            </tr>
        `;
    }

    Object.keys(grouped).forEach(type => {
        const items = grouped[type];
        const total = items.length;
        const achieved = items.filter(i => i.status === 'achieved').length;
        const nonAchieved = items.filter(i => i.status === 'non achieved').length;
        const achievementRate = total > 0 ? Math.round((achieved / total) * 100) : 0;

        // Total completed WO (sum of total_completed from all items)
        const completed = items.reduce((sum, i) => sum + (parseInt(i.total_completed) || 0), 0);

        // Get current filter values
        const employeeFilter = document.getElementById('progressEmployeeFilter').value.trim();
        const taskFilter = document.getElementById('progressTaskFilter').value.trim();

        const row = document.createElement('tr');
        row.innerHTML = `
            <td class="fw-semibold">${type}</td>
            <td>${employeeFilter || 'All Employees'}</td>
            <td>${total}</td>
            <td>${achieved}</td>
            <td>${nonAchieved}</td>
            <td>${completed}</td>
            <td>${achievementRate}%</td>
        `;
        tbody.appendChild(row);
    });
}

// Fungsi untuk toggle chart type
function toggleChartType() {
    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
    
    // Destroy existing chart
    if (progressChart) {
        progressChart.destroy();
    }
    
    // Recreate chart with new type
    const ctx = document.getElementById('progressChart').getContext('2d');
    const data = getCurrentProgressData();
    
    // Group data by task type and calculate achievement rates
    const grouped = {};
    data.forEach(item => {
        if (!grouped[item.task_name]) grouped[item.task_name] = [];
        grouped[item.task_name].push(item);
    });

    // Prepare chart data based on grouped task types
    const labels = Object.keys(grouped);
    const achievementRates = labels.map(type => {
        const items = grouped[type];
        const total = items.length;
        const achieved = items.filter(i => i.status === 'achieved').length;
        return total > 0 ? Math.round((achieved / total) * 100) : 0;
    });
    
    const targetLine = labels.map(() => 100); // Target is always 100%
    
    progressChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: data.map(item => `${item.employee} - ${item.task_name}`),
            datasets: [{
                label: 'Progress',
                data: data.map(item => item.progress),
                backgroundColor: currentChartType === 'line' ? 'rgba(196, 30, 58, 0.2)' : data.map(item => {
                    const percentage = (item.progress / item.target) * 100;
                    return percentage >= 100 ? 'rgba(40, 167, 69, 0.8)' : 
                           percentage >= 80 ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)';
                }),
                borderColor: currentChartType === 'line' ? 'rgba(196, 30, 58, 1)' : data.map(item => {
                    const percentage = (item.progress / item.target) * 100;
                    return percentage >= 100 ? 'rgba(40, 167, 69, 1)' : 
                           percentage >= 80 ? 'rgba(255, 193, 7, 1)' : 'rgba(220, 53, 69, 1)';
                }),
                borderWidth: 2,
                fill: currentChartType === 'line' ? false : true
            }, {
                label: 'Target',
                data: data.map(item => item.target),
                backgroundColor: currentChartType === 'line' ? 'rgba(108, 117, 125, 0.2)' : 'rgba(108, 117, 125, 0.3)',
                borderColor: 'rgba(108, 117, 125, 1)',
                borderWidth: 2,
                fill: currentChartType === 'line' ? false : true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Values'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Employee - Task'
                    }
                }
            },
            plugins: {
                title: {
                    display: true,
                    text: 'Employee Progress vs Target'
                },
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const dataIndex = context.dataIndex;
                            const task = data[dataIndex];
                            if (task) {
                                return `${context.dataset.label}: ${context.parsed.y} ${task.unit}`;
                            }
                            return `${context.dataset.label}: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    });
}

// Update fungsi window.onload yang sudah ada
const originalOnload = window.onload;
window.onload = () => {
    if (originalOnload) originalOnload();
    initProgressChart();
};

// Fungsi untuk mendapatkan data yang telah difilter
function getFilteredData() {
    const employeeFilter = document.getElementById('employeeFilter').value.trim();
    const taskFilter = document.getElementById('taskFilter').value.trim();
    const startDate = document.getElementById('start_date').value.trim();
    const endDate = document.getElementById('end_date').value.trim();

    let filteredData = taskData;

    if (employeeFilter) {
        filteredData = filteredData.filter(item => item.employee === employeeFilter);
    }

    if (taskFilter) {
        filteredData = filteredData.filter(item => item.task_name === taskFilter);
    }

    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        filteredData = filteredData.filter(item => {
            const taskStart = new Date(item.start_date);
            const taskEnd = new Date(item.end_date);
            return (taskStart >= start && taskStart <= end) || (taskEnd >= start && taskEnd <= end);
        });
    }

    return filteredData;
}

// Fungsi untuk download report sebagai Excel
function downloadReport() {
    try {
        // Get filtered data
        const filteredData = getFilteredData();
        
        if (filteredData.length === 0) {
            showNotification('No data available for the selected filters.', 'warning');
            return;
        }

        // Prepare data untuk Excel - urutan: Task, Employee, Description, Target, Progress, Status, Deadline, Last Update
        const reportData = filteredData.map(item => {
            // Determine target display value based on task type
            let targetDisplay;
            if (item.task_type === 'text' && item.target_str) {
                targetDisplay = item.target_str;
            } else if (item.task_type === 'numeric' && item.target_int > 0) {
                targetDisplay = item.target_int;
            } else if (item.target > 0) {
                targetDisplay = item.target;
            } else {
                targetDisplay = 'N/A';
            }
            
            return {
                'Task Type': item.task_name,
                'Employee Name': item.employee,
                'Description': item.description || 'N/A',
                'Target': targetDisplay,
                'Progress': item.progress,
                'Status': item.status === 'achieved' ? 'Achieved' : 'Non-Achieved',
                'Deadline': item.deadline || 'N/A',
                'Last Update': item.last_update || 'N/A'
            };
        });

        // Create worksheet
        const worksheet = XLSX.utils.json_to_sheet(reportData);
        
        // Set column widths
        const columnWidths = [
            { wch: 25 }, // Task Type
            { wch: 20 }, // Employee Name
            { wch: 35 }, // Description
            { wch: 15 }, // Target
            { wch: 10 }, // Progress
            { wch: 15 }, // Status
            { wch: 15 }, // Deadline
            { wch: 20 }  // Last Update
        ];
        worksheet['!cols'] = columnWidths;

        // Create workbook
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Performance Report');

        // Add summary sheet with filtered data
        const achievedCount = filteredData.filter(item => item.status === 'achieved').length;
        const nonAchievedCount = filteredData.filter(item => item.status === 'non achieved').length;
        const successRate = filteredData.length > 0 ? Math.round((achievedCount / filteredData.length) * 100) : 0;
        
        const summaryData = [
            { 'Metric': 'Report Generated', 'Value': new Date().toLocaleDateString('id-ID') },
            { 'Metric': '', 'Value': '' },
            { 'Metric': 'SUMMARY STATISTICS', 'Value': '' },
            { 'Metric': 'Total Tasks', 'Value': filteredData.length },
            { 'Metric': 'Achieved Tasks', 'Value': achievedCount },
            { 'Metric': 'Non-Achieved Tasks', 'Value': nonAchievedCount },
            { 'Metric': 'Success Rate', 'Value': successRate + '%' }
        ];
        
        // Add filter information
        const employeeFilter = document.getElementById('employeeFilter').value.trim();
        const taskFilter = document.getElementById('taskFilter').value.trim();
        
        
        if (employeeFilter || taskFilter) {
            summaryData.push({ 'Metric': '', 'Value': '' }); // Empty row
            summaryData.push({ 'Metric': 'APPLIED FILTERS', 'Value': '' });
            
            if (employeeFilter) {
                summaryData.push({ 'Metric': 'Employee Filter', 'Value': employeeFilter });
            } else {
                summaryData.push({ 'Metric': 'Employee Filter', 'Value': 'All Employees' });
            }
            
            if (taskFilter) {
                summaryData.push({ 'Metric': 'Task Type Filter', 'Value': taskFilter });
            } else {
                summaryData.push({ 'Metric': 'Task Type Filter', 'Value': 'All Task Types' });
            }
        } else {
            summaryData.push({ 'Metric': '', 'Value': '' }); // Empty row
            summaryData.push({ 'Metric': 'APPLIED FILTERS', 'Value': '' });
            summaryData.push({ 'Metric': 'Filter Status', 'Value': 'No filters applied (All data)' });
        }
        
        const summaryWorksheet = XLSX.utils.json_to_sheet(summaryData);
        summaryWorksheet['!cols'] = [{ wch: 20 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(workbook, summaryWorksheet, 'Summary');

        // Generate filename with current date and filter info
        const currentDate = new Date();
        const dateString = currentDate.toISOString().split('T')[0];
        let filename = `Performance_Report_${dateString}`;
        
        if (employeeFilter) {
            filename += `_${employeeFilter.replace(/\s+/g, '_')}`;
        }
        if (taskFilter) {
            filename += `_${taskFilter.replace(/\s+/g, '_')}`;
        }
        
        // If no filters applied, add "All_Data" to filename
        if (!employeeFilter && !taskFilter) {
            filename += '_All_Data';
        }
        
        filename += '.xlsx';

        // Download file
        XLSX.writeFile(workbook, filename);

        // Show success message
        showNotification('Report downloaded successfully!', 'success');

    } catch (error) {
        console.error('Error downloading report:', error);
        showNotification('Error downloading report. Please try again.', 'error');
    }
}

// Fungsi untuk export chart sebagai PDF
function exportChart() {
    try {
        // Get filtered data
        const filteredData = getFilteredData();
        
        if (filteredData.length === 0) {
            showNotification('No data available for the selected filters.', 'warning');
            return;
        }

        // Create new jsPDF instance
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();

        // Add title
        pdf.setFontSize(20);
        pdf.setTextColor(196, 30, 58);
        pdf.text('Performance Statistics Report', 20, 20);

        // Add date
        pdf.setFontSize(12);
        pdf.setTextColor(0, 0, 0);
        const currentDate = new Date().toLocaleDateString('id-ID');
        pdf.text(`Generated on: ${currentDate}`, 20, 30);

        // Add filter information
        const employeeFilter = document.getElementById('employeeFilter').value.trim();
        const taskFilter = document.getElementById('taskFilter').value.trim();
        
        let yPosition = 40;
        if (employeeFilter || taskFilter) {
            pdf.setFontSize(11);
            pdf.setTextColor(100, 100, 100);
            pdf.text('Applied Filters:', 20, yPosition);
            yPosition += 10;
            
            if (employeeFilter) {
                pdf.text(`Employee: ${employeeFilter}`, 25, yPosition);
                yPosition += 8;
            } else {
                pdf.text(`Employee: All Employees`, 25, yPosition);
                yPosition += 8;
            }
            if (taskFilter) {
                pdf.text(`Task Type: ${taskFilter}`, 25, yPosition);
                yPosition += 8;
            } else {
                pdf.text(`Task Type: All Task Types`, 25, yPosition);
                yPosition += 8;
            }
            yPosition += 5;
        } else {
            pdf.setFontSize(11);
            pdf.setTextColor(100, 100, 100);
            pdf.text('Filter Status: No filters applied (All data)', 20, yPosition);
            yPosition += 15;
        }

        // Add summary statistics with filtered data
        pdf.setFontSize(14);
        pdf.setTextColor(44, 90, 160);
        pdf.text('Summary Statistics', 20, yPosition);
        yPosition += 15;

        pdf.setFontSize(11);
        pdf.setTextColor(0, 0, 0);
        const totalTasks = filteredData.length;
        const achievedTasks = filteredData.filter(item => item.status === 'achieved').length;
        const nonAchievedTasks = filteredData.filter(item => item.status === 'non achieved').length;
        const successRate = totalTasks > 0 ? Math.round((achievedTasks / totalTasks) * 100) : 0;

        pdf.text(`Total Tasks: ${totalTasks}`, 20, yPosition);
        pdf.text(`Achieved: ${achievedTasks}`, 20, yPosition + 10);
        pdf.text(`Non-Achieved: ${nonAchievedTasks}`, 20, yPosition + 20);
        pdf.text(`Success Rate: ${successRate}%`, 20, yPosition + 30);

        // Export first chart (Task Distribution)
        const taskCanvas = document.getElementById('taskChart');
        const taskImgData = taskCanvas.toDataURL('image/png');
        pdf.addImage(taskImgData, 'PNG', 20, yPosition + 45, 80, 80);

        // Export second chart (Performance Chart)
        const perfCanvas = document.getElementById('performanceChart');
        const perfImgData = perfCanvas.toDataURL('image/png');
        pdf.addImage(perfImgData, 'PNG', 110, yPosition + 45, 80, 80);

        // Add detailed table on new page if there's enough data
        if (filteredData.length > 0) {
            pdf.addPage();
            
            // Table title
            pdf.setFontSize(14);
            pdf.setTextColor(44, 90, 160);
            pdf.text('Detailed Performance Data', 20, 20);

            // Prepare table data - urutan: Task, Employee, Description, Target, Progress, Status, Deadline, Last Update
            const tableData = filteredData.map(item => {
                // Determine target display value based on task type
                let targetDisplay;
                if (item.task_type === 'text' && item.target_str) {
                    targetDisplay = item.target_str;
                } else if (item.task_type === 'numeric' && item.target_int > 0) {
                    targetDisplay = item.target_int.toString();
                } else if (item.target > 0) {
                    targetDisplay = item.target.toString();
                } else {
                    targetDisplay = 'N/A';
                }
                
                return [
                    item.task_name,          // Task Type
                    item.employee,          // Employee name
                    item.description || 'N/A',  // Description
                    targetDisplay,      // Target
                    item.progress.toString(),   // Progress
                    item.status === 'achieved' ? 'Achieved' : 'Non-Achieved',  // Status
                    item.deadline || 'N/A',     // Deadline
                    item.last_update || 'N/A'   // Last Update
                ];
            });

            // Add table
            pdf.autoTable({
                head: [['Task Type', 'Employee', 'Description', 'Target', 'Progress', 'Status', 'Deadline', 'Last Update']],
                body: tableData,
                startY: 30,
                styles: {
                    fontSize: 8,
                    cellPadding: 2
                },
                headStyles: {
                    fillColor: [196, 30, 58],
                    textColor: 255
                },
                alternateRowStyles: {
                    fillColor: [245, 245, 245]
                }
            });
        }

        // Generate filename with current date and filter info
        const dateString = new Date().toISOString().split('T')[0];
        let filename = `Performance_Chart_${dateString}`;
        
        if (employeeFilter) {
            filename += `_${employeeFilter.replace(/\s+/g, '_')}`;
        }
        if (taskFilter) {
            filename += `_${taskFilter.replace(/\s+/g, '_')}`;
        }
        
        // If no filters applied, add "All_Data" to filename
        if (!employeeFilter && !taskFilter) {
            filename += '_All_Data';
        }
        
        filename += '.pdf';

        // Save PDF
        pdf.save(filename);

        // Show success message
        showNotification('Chart exported successfully!', 'success');

    } catch (error) {
        console.error('Error exporting chart:', error);
        showNotification('Error exporting chart. Please try again.', 'error');
    }
}

// Fungsi untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} alert-dismissible fade show`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // Add to body
    document.body.appendChild(notification);

    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

// Fungsi untuk download individual chart sebagai image
function downloadChartAsImage(chartId, filename) {
    try {
        const canvas = document.getElementById(chartId);
        const url = canvas.toDataURL('image/png');
        
        const link = document.createElement('a');
        link.download = filename;
        link.href = url;
        link.click();
        
        showNotification(`${filename} downloaded successfully!`, 'success');
    } catch (error) {
        console.error('Error downloading chart:', error);
        showNotification('Error downloading chart. Please try again.', 'error');
    }
}

// Fungsi untuk export data sebagai CSV
function exportAsCSV() {
    try {
        const csvData = taskData.map(item => ({
            'Employee Name': item.employee,
            'Task Type': item.task_name,
            'Progress': item.progress,
            'Target': item.target,
            'Unit': item.unit,
            'Achievement Percentage': Math.round((item.progress / item.target) * 100),
            'Status': item.status === 'achieve' ? 'Achieve' : 'Non-Achieve'
        }));

        // Convert to CSV
        const csvString = convertArrayToCSV(csvData);
        
        // Create and download file
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', `Performance_Data_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        showNotification('Data exported as CSV successfully!', 'success');
    } catch (error) {
        console.error('Error exporting CSV:', error);
        showNotification('Error exporting CSV. Please try again.', 'error');
    }
}

// Helper function untuk convert array ke CSV
function convertArrayToCSV(array) {
    const headers = Object.keys(array[0]);
    const csvContent = [
        headers.join(','),
        ...array.map(row => headers.map(header => `"${row[header]}"`).join(','))
    ].join('\n');
    
    return csvContent;
}

// Fungsi untuk print report
function printReport() {
    try {
        const printWindow = window.open('', '_blank');
        const currentDate = new Date().toLocaleDateString('id-ID');
        
        const printContent = `
            <html>
                <head>
                    <title>Performance Statistics Report</title>
                    <style>
                        body { font-family: Arial, sans-serif; margin: 20px; }
                        .header { text-align: center; margin-bottom: 30px; }
                        .header h1 { color: #c41e3a; margin: 0; }
                        .header p { color: #666; margin: 5px 0; }
                        .summary { margin-bottom: 30px; }
                        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; }
                        .summary-card { border: 1px solid #ddd; padding: 15px; border-radius: 8px; text-align: center; }
                        .summary-card h3 { margin: 0 0 10px 0; color: #2c5aa0; }
                        .summary-card .value { font-size: 24px; font-weight: bold; color: #c41e3a; }
                        .table-container { margin: 20px 0; }
                        table { width: 100%; border-collapse: collapse; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #f8f9fa; font-weight: bold; }
                        .achieve { background-color: #d4edda; color: #155724; }
                        .non-achieve { background-color: #f8d7da; color: #721c24; }
                        @media print { body { margin: 0; } }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>Performance Statistics Report</h1>
                        <p>Generated on: ${currentDate}</p>
                    </div>
                    
                    <div class="summary">
                        <h2>Summary Statistics</h2>
                        <div class="summary-grid">
                            <div class="summary-card">
                                <h3>Total Tasks</h3>
                                <div class="value">${taskData.length}</div>
                            </div>
                            <div class="summary-card">
                                <h3>Achieved</h3>
                                <div class="value">${taskData.filter(item => item.status === 'achieved').length}</div>
                            </div>
                            <div class="summary-card">
                                <h3>Non-Achieved</h3>
                                <div class="value">${taskData.filter(item => item.status === 'non-achieve').length}</div>
                            </div>
                            <div class="summary-card">
                                <h3>Success Rate</h3>
                                <div class="value">${Math.round((taskData.filter(item => item.status === 'achieved').length / taskData.length) * 100)}%</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-container">
                        <h2>Detailed Performance Data</h2>
                        <table>
                            <thead>
                                <tr>
                                    <th>Employee Name</th>
                                    <th>Task Type</th>
                                    <th>Progress</th>
                                    <th>Target</th>
                                    <th>Unit</th>
                                    <th>Achievement %</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${taskData.map(item => `
                                    <tr>
                                        <td>${item.employee}</td>
                                        <td>${item.task_name}</td>
                                        <td>${item.progress}</td>
                                        <td>${item.target}</td>
                                        <td>${item.unit}</td>
                                        <td>${Math.round((item.progress / item.target) * 100)}%</td>
                                        <td class="${item.status}">${item.status === 'achieved' ? 'Achieved' : 'Non-Achieved'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </body>
            </html>
        `;
        
        printWindow.document.write(printContent);
        printWindow.document.close();
        printWindow.print();
        
        showNotification('Print dialog opened successfully!', 'success');
    } catch (error) {
        console.error('Error printing report:', error);
        showNotification('Error printing report. Please try again.', 'error');
    }
}

// Fungsi untuk menambahkan dropdown export options
function createExportDropdown() {
    const exportButton = document.querySelector('button[onclick="exportChart()"]');
    if (exportButton) {
        // Replace single button with dropdown
        exportButton.outerHTML = `
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-file-earmark-pdf me-2"></i>Export Options
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportChart()">
                        <i class="bi bi-file-earmark-pdf me-2"></i>Export as PDF
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportAsCSV()">
                        <i class="bi bi-file-earmark-excel me-2"></i>Export as CSV
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="downloadChartAsImage('taskChart', 'task_distribution_chart.png')">
                        <i class="bi bi-image me-2"></i>Task Chart as Image
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="downloadChartAsImage('performanceChart', 'performance_chart.png')">
                        <i class="bi bi-image me-2"></i>Performance Chart as Image
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="downloadChartAsImage('progressChart', 'progress_chart.png')">
                        <i class="bi bi-image me-2"></i>Progress Chart as Image
                    </a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="printReport()">
                        <i class="bi bi-printer me-2"></i>Print Report
                    </a></li>
                </ul>
            </div>
        `;
    }
}

// Load external libraries yang diperlukan
function loadExternalLibraries() {
    // Load SheetJS for Excel export
    if (!window.XLSX) {
        const script1 = document.createElement('script');
        script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
        document.head.appendChild(script1);
    }

    // Load jsPDF for PDF export
    if (!window.jspdf) {
        const script2 = document.createElement('script');
        script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        document.head.appendChild(script2);
        
        // Load jsPDF autoTable plugin
        const script3 = document.createElement('script');
        script3.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js';
        document.head.appendChild(script3);
    }
}

// Initialize libraries when page loads
document.addEventListener('DOMContentLoaded', function() {
    loadExternalLibraries();
    
    // Add export dropdown after a short delay to ensure DOM is ready
    setTimeout(() => {
        createExportDropdown();
    }, 1000);
});

document.getElementById('taskFilter').addEventListener('change', function() {
    filterTasks(); // tetap panggil ini untuk chart atas
    updateProgressChart(); // tambahkan ini untuk chart bawah
});

function applyDateFilter() {
    const start = document.getElementById('start_date').value;
    const end = document.getElementById('end_date').value;

    const params = new URLSearchParams(window.location.search);
    if (start) params.set('start_date', start);
    if (end) params.set('end_date', end);
    window.location.search = params.toString();
}

// Inisialisasi nilai awal jika sudah difilter sebelumnya
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('start_date')) {
        document.getElementById('startDate').value = params.get('start_date');
    }
    if (params.get('end_date')) {
        document.getElementById('endDate').value = params.get('end_date');
    }
});

// 1. FUNGSI FILTER UTAMA - SATU UNTUK SEMUA
function filterTasks() {
    const employeeFilter = document.getElementById('employeeFilter').value.trim();
    const taskFilter = document.getElementById('taskFilter').value.trim();
    const startDate = document.getElementById('start_date').value.trim();
    const endDate = document.getElementById('end_date').value.trim();
    
    let filteredData = getFilteredData();
    
    // Update all charts and tables with filtered data
    updateCharts(filteredData);
    updateProgressChart(filteredData);
    updateProgressTable(filteredData);
    updateEmployeeDetails(filteredData);
}

// 2. FUNGSI GET FILTERED DATA - SATU SUMBER DATA
function getFilteredData() {
    const employeeFilter = document.getElementById('employeeFilter').value.trim();
    const taskFilter = document.getElementById('taskFilter').value.trim();
    const startDate = document.getElementById('start_date').value.trim();
    const endDate = document.getElementById('end_date').value.trim();

    let filteredData = [...taskData];

    // Apply employee filter
    if (employeeFilter && employeeFilter !== '') {
        filteredData = filteredData.filter(item => item.employee === employeeFilter);
    }

    // Apply task type filter
    if (taskFilter && taskFilter !== '') {
        filteredData = filteredData.filter(item => item.task_name === taskFilter);
    }

    // Apply date filter
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        end.setHours(23, 59, 59, 999); // Set to end of day
        
        filteredData = filteredData.filter(item => {
            if (!item.deadline) return true; // Include items without deadline
            const deadline = new Date(item.deadline);
            return deadline >= start && deadline <= end;
        });
    }

    return filteredData;
}

// 3. HAPUS FUNGSI getCurrentProgressData - TIDAK DIPERLUKAN LAGI

// 4. HAPUS FUNGSI applyDateFilter - DIGANTI DENGAN LOGIKA DI getFilteredData

// 5. EVENT LISTENERS - SATU SET SAJA
document.addEventListener('DOMContentLoaded', function() {
    // Filter untuk semua chart dan tabel
    document.getElementById('employeeFilter').addEventListener('change', filterTasks);
    document.getElementById('taskFilter').addEventListener('change', filterTasks);
    document.getElementById('start_date').addEventListener('change', filterTasks);
    document.getElementById('end_date').addEventListener('change', filterTasks);
    
    // Initialize semua chart dan tabel
    filterTasks();
});

// 6. HAPUS INISIALISASI URL PARAMETERS - TIDAK DIPERLUKAN
// (Bagian ini bisa dihapus karena sudah digantikan dengan sistem filter yang lebih sederhana)