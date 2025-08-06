        const taskData = [
            { type: "Pelurusan KPI", name: "Fajar Rafiudin", status: "non-achieve", completed: 45, target: 50, unit: "WO/HARI" },
            { type: "Validasi FTM", name: "Odi Rinanda", status: "achieve", completed: 52, target: 52, unit: "RACK" },
            { type: "Fallout CONS/EBIS", name: "Yosef Tobir", status: "achieve", completed: 52, target: 52, unit: "FO" },
            { type: "Val Tiang", name: "M. Nuril Adinata", status: "achieve", completed: 52, target: 52, unit: "Quality" },
            { type: "Validasi FTM", name: "Aji Pangestu", status: "achieve", completed: 52, target: 52, unit: "RACK" },
            { type: "Pelurusan KPI", name: "Erik Efendi", status: "non-achieve", completed: 47, target: 50, unit: "WO/HARI" },
            { type: "Fallout CONS/EBIS", name: "Eddo Bentano", status: "achieve", completed: 31, target: 31, unit: "FO" },
            { type: "Pelurusan KPI", name: "Herlando", status: "achieve", completed: 66, target: 50, unit: "WO/HARI" },
            { type: "Fallout CONS/EBIS", name: "Imam Sutrisno", status: "achieve", completed: 27, target: 27, unit: "FO" },
            { type: "Pelurusan GDOC Fallout", name: "PKL", status: "achieve", completed: 40, target: 40, unit: "FO/HARI" }
        ];

        let taskChart, performanceChart;

        function initCharts() {
            const taskCtx = document.getElementById('taskChart').getContext('2d');
            const taskTypes = [...new Set(taskData.map(item => item.type))];
            const taskCounts = taskTypes.map(type => taskData.filter(item => item.type === type).length);

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

            const perfCtx = document.getElementById('performanceChart').getContext('2d');
            const achieveCount = taskData.filter(item => item.status === 'achieve').length;
            const nonAchieveCount = taskData.filter(item => item.status === 'non-achieve').length;

            performanceChart = new Chart(perfCtx, {
                type: 'bar',
                data: {
                    labels: ['Achieve', 'Non-Achieve'],
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
                        }
                    }
                }
            });
        }

        function populateEmployeeDetails() {
            const container = document.getElementById('employeeDetails');
            container.innerHTML = '';

            taskData.forEach(employee => {
                const achievementPercentage = Math.round((employee.completed / employee.target) * 100);
                const card = document.createElement('div');
                card.className = 'col-md-6 col-lg-4 mb-3';
                card.innerHTML = `
                    <div class="employee-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">${employee.name}</h6>
                            <span class="achievement-badge ${employee.status}">${employee.status === 'achieve' ? 'Achieve' : 'Non-Achieve'}</span>
                        </div>
                        <p class="text-muted mb-2">${employee.type}</p>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Progress: ${employee.completed}/${employee.target} ${employee.unit}</small>
                            <small class="fw-bold">${achievementPercentage}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar ${employee.status === 'achieve' ? 'bg-success' : 'bg-danger'}" 
                                 style="width: ${Math.min(achievementPercentage, 100)}%"></div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        function filterTasks() {
            const taskFilter = document.getElementById('taskFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            let filteredData = taskData;
            
            if (taskFilter) {
                filteredData = filteredData.filter(item => item.type === taskFilter);
            }
            
            if (statusFilter) {
                filteredData = filteredData.filter(item => item.status === statusFilter);
            }
            
            updateCharts(filteredData);
            updateEmployeeDetails(filteredData);
        }

        function updateCharts(data) {
            const taskTypes = [...new Set(data.map(item => item.type))];
            const taskCounts = taskTypes.map(type => data.filter(item => item.type === type).length);
            
            taskChart.data.labels = taskTypes;
                        taskChart.data.datasets[0].data = taskCounts;
            taskChart.update();

            
            const achieveCount = data.filter(item => item.status === 'achieve').length;
            const nonAchieveCount = data.filter(item => item.status === 'non-achieve').length;

            performanceChart.data.datasets[0].data = [achieveCount, nonAchieveCount];
            performanceChart.update();
        }

        function updateEmployeeDetails(data) {
            const container = document.getElementById('employeeDetails');
            container.innerHTML = '';

            data.forEach(employee => {
                const achievementPercentage = Math.round((employee.completed / employee.target) * 100);
                const card = document.createElement('div');
                card.className = 'col-md-6 col-lg-4 mb-3';
                card.innerHTML = `
                    <div class="employee-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0 fw-bold">${employee.name}</h6>
                            <span class="achievement-badge ${employee.status}">${employee.status === 'achieve' ? 'Achieve' : 'Non-Achieve'}</span>
                        </div>
                        <p class="text-muted mb-2">${employee.type}</p>
                        <div class="d-flex justify-content-between mb-2">
                            <small class="text-muted">Progress: ${employee.completed}/${employee.target} ${employee.unit}</small>
                            <small class="fw-bold">${achievementPercentage}%</small>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar ${employee.status === 'achieve' ? 'bg-success' : 'bg-danger'}" 
                                 style="width: ${Math.min(achievementPercentage, 100)}%"></div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });
        }
        
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

function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmLogout() {
            alert('Logout confirmed! Redirecting to login page...');
            hideLogoutModal();
        }
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

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

        function downloadReport() {
            alert("Download Report triggered.");
        }

        function exportChart() {
            alert("Export Chart triggered.");
        }

        window.onload = () => {
            initCharts();
            populateEmployeeDetails();
        };

let progressChart;
let currentChartType = 'bar';

function initProgressChart() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    
    progressChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: 'Progress',
                data: [],
                backgroundColor: [],
                borderColor: [],
                borderWidth: 1
            }, {
                label: 'Target',
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

function getCurrentProgressData() {
    const viewMode = document.getElementById('progressViewMode').value;
    const sortBy = document.getElementById('progressSortBy').value;
    const showOnly = document.getElementById('progressShowOnly').value;
    
    let data = [...taskData];
    
    
    if (showOnly !== 'all') {
        data = data.filter(item => {
            const percentage = (item.completed / item.target) * 100;
            switch (showOnly) {
                case 'above-target':
                    return percentage > 100;
                case 'below-target':
                    return percentage < 100;
                case 'on-target':
                    return percentage === 100;
                default:
                    return true;
            }
        });
    }
    
        data.sort((a, b) => {
        switch (sortBy) {
            case 'name':
                return a.name.localeCompare(b.name);
            case 'progress':
                return (b.completed / b.target) - (a.completed / a.target);
            case 'target':
                return b.target - a.target;
            default:
                return 0;
        }
    });
    
    return data;
}

function updateProgressChart() {
    const data = getCurrentProgressData();
    
    
    const labels = data.map(item => `${item.name} - ${item.type}`);
    const progressData = data.map(item => item.completed);
    const targetData = data.map(item => item.target);
    
    const backgroundColors = data.map(item => {
        const percentage = (item.completed / item.target) * 100;
        if (percentage >= 100) {
            return 'rgba(40, 167, 69, 0.8)'; 
        } else if (percentage >= 80) {
            return 'rgba(255, 193, 7, 0.8)'; 
        } else {
            return 'rgba(220, 53, 69, 0.8)'; 
        }
    });
    
    const borderColors = data.map(item => {
        const percentage = (item.completed / item.target) * 100;
        if (percentage >= 100) {
            return 'rgba(40, 167, 69, 1)';
        } else if (percentage >= 80) {
            return 'rgba(255, 193, 7, 1)';
        } else {
            return 'rgba(220, 53, 69, 1)';
        }
    });
    
    progressChart.data.labels = labels;
    progressChart.data.datasets[0].data = progressData;
    progressChart.data.datasets[0].backgroundColor = backgroundColors;
    progressChart.data.datasets[0].borderColor = borderColors;
    progressChart.data.datasets[1].data = targetData;
    
    progressChart.update();
    
    updateProgressTable(data);
}

function updateProgressTable(data) {
    const tbody = document.getElementById('progressTableBody');
    tbody.innerHTML = '';
    
    data.forEach(item => {
        const percentage = Math.round((item.completed / item.target) * 100);
        const row = document.createElement('tr');
        
        if (percentage >= 100) {
            row.className = 'table-success';
        } else if (percentage >= 80) {
            row.className = 'table-warning';
        } else {
            row.className = 'table-danger';
        }
        
        row.innerHTML = `
            <td class="fw-semibold">${item.name}</td>
            <td>${item.type}</td>
            <td>${item.completed}</td>
            <td>${item.target}</td>
            <td>${item.unit}</td>
            <td>
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-2">${percentage}%</span>
                    <div class="progress" style="height: 6px; width: 60px;">
                        <div class="progress-bar ${percentage >= 100 ? 'bg-success' : percentage >= 80 ? 'bg-warning' : 'bg-danger'}" 
                             style="width: ${Math.min(percentage, 100)}%"></div>
                    </div>
                </div>
            </td>
            <td>
                <span class="achievement-badge ${item.status}">${item.status === 'achieve' ? 'Achieve' : 'Non-Achieve'}</span>
            </td>
        `;
        
        tbody.appendChild(row);
    });
}

function toggleChartType() {
    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
    
    if (progressChart) {
        progressChart.destroy();
    }
    
    const ctx = document.getElementById('progressChart').getContext('2d');
    const data = getCurrentProgressData();
    
    progressChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: data.map(item => `${item.name} - ${item.type}`),
            datasets: [{
                label: 'Progress',
                data: data.map(item => item.completed),
                backgroundColor: currentChartType === 'line' ? 'rgba(196, 30, 58, 0.2)' : data.map(item => {
                    const percentage = (item.completed / item.target) * 100;
                    return percentage >= 100 ? 'rgba(40, 167, 69, 0.8)' : 
                           percentage >= 80 ? 'rgba(255, 193, 7, 0.8)' : 'rgba(220, 53, 69, 0.8)';
                }),
                borderColor: currentChartType === 'line' ? 'rgba(196, 30, 58, 1)' : data.map(item => {
                    const percentage = (item.completed / item.target) * 100;
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

const originalOnload = window.onload;
window.onload = () => {
    if (originalOnload) originalOnload();
    initProgressChart();
};

function downloadReport() {
    try {
        
        const reportData = taskData.map(item => ({
            'Employee Name': item.name,
            'Task Type': item.type,
            'Progress': item.completed,
            'Target': item.target,
            'Unit': item.unit,
            'Achievement %': Math.round((item.completed / item.target) * 100),
            'Status': item.status === 'achieve' ? 'Achieve' : 'Non-Achieve'
        }));

        const worksheet = XLSX.utils.json_to_sheet(reportData);
        
        const columnWidths = [
            { wch: 20 }, 
            { wch: 25 }, 
            { wch: 10 }, 
            { wch: 10 }, 
            { wch: 15 }, 
            { wch: 15 }, 
            { wch: 15 }  
        ];
        worksheet['!cols'] = columnWidths;

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Performance Report');

        const summaryData = [
            { 'Metric': 'Total Tasks', 'Value': taskData.length },
            { 'Metric': 'Achieved', 'Value': taskData.filter(item => item.status === 'achieve').length },
            { 'Metric': 'Non-Achieved', 'Value': taskData.filter(item => item.status === 'non-achieve').length },
            { 'Metric': 'Success Rate', 'Value': Math.round((taskData.filter(item => item.status === 'achieve').length / taskData.length) * 100) + '%' }
        ];
        
        const summaryWorksheet = XLSX.utils.json_to_sheet(summaryData);
        summaryWorksheet['!cols'] = [{ wch: 15 }, { wch: 15 }];
        XLSX.utils.book_append_sheet(workbook, summaryWorksheet, 'Summary');

        const currentDate = new Date();
        const dateString = currentDate.toISOString().split('T')[0];
        const filename = `Performance_Report_${dateString}.xlsx`;

        XLSX.writeFile(workbook, filename);

        showNotification('Report downloaded successfully!', 'success');

    } catch (error) {
        console.error('Error downloading report:', error);
        showNotification('Error downloading report. Please try again.', 'error');
    }
}

function exportChart() {
    try {
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();

        pdf.setFontSize(20);
        pdf.setTextColor(196, 30, 58);
        pdf.text('Performance Statistics Report', 20, 20);

        pdf.setFontSize(12);
        pdf.setTextColor(0, 0, 0);
        const currentDate = new Date().toLocaleDateString('id-ID');
        pdf.text(`Generated on: ${currentDate}`, 20, 30);

        pdf.setFontSize(14);
        pdf.setTextColor(44, 90, 160);
        pdf.text('Summary Statistics', 20, 45);

        pdf.setFontSize(11);
        pdf.setTextColor(0, 0, 0);
        const totalTasks = taskData.length;
        const achievedTasks = taskData.filter(item => item.status === 'achieve').length;
        const nonAchievedTasks = taskData.filter(item => item.status === 'non-achieve').length;
        const successRate = Math.round((achievedTasks / totalTasks) * 100);

        pdf.text(`Total Tasks: ${totalTasks}`, 20, 55);
        pdf.text(`Achieved: ${achievedTasks}`, 20, 65);
        pdf.text(`Non-Achieved: ${nonAchievedTasks}`, 20, 75);
        pdf.text(`Success Rate: ${successRate}%`, 20, 85);

        const taskCanvas = document.getElementById('taskChart');
        const taskImgData = taskCanvas.toDataURL('image/png');
        pdf.addImage(taskImgData, 'PNG', 20, 95, 80, 80);

        const perfCanvas = document.getElementById('performanceChart');
        const perfImgData = perfCanvas.toDataURL('image/png');
        pdf.addImage(perfImgData, 'PNG', 110, 95, 80, 80);

        pdf.addPage();
        pdf.setFontSize(16);
        pdf.setTextColor(44, 90, 160);
        pdf.text('Employee Progress Chart', 20, 20);

        const progressCanvas = document.getElementById('progressChart');
        const progressImgData = progressCanvas.toDataURL('image/png');
        pdf.addImage(progressImgData, 'PNG', 20, 30, 170, 100);

        pdf.addPage();
        pdf.setFontSize(16);
        pdf.setTextColor(44, 90, 160);
        pdf.text('Detailed Performance Data', 20, 20);

        const tableData = taskData.map(item => [
            item.name,
            item.type,
            item.completed.toString(),
            item.target.toString(),
            item.unit,
            Math.round((item.completed / item.target) * 100) + '%',
            item.status === 'achieve' ? 'Achieve' : 'Non-Achieve'
        ]);

        pdf.autoTable({
            head: [['Employee', 'Task Type', 'Progress', 'Target', 'Unit', 'Achievement %', 'Status']],
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
                fillColor: [245, 247, 250]
            },
            columnStyles: {
                0: { cellWidth: 25 },
                1: { cellWidth: 30 },
                2: { cellWidth: 15 },
                3: { cellWidth: 15 },
                4: { cellWidth: 20 },
                5: { cellWidth: 20 },
                6: { cellWidth: 20 }
            }
        });

        const dateString = new Date().toISOString().split('T')[0];
        const filename = `Performance_Charts_${dateString}.pdf`;

        pdf.save(filename);

        showNotification('Charts exported successfully!', 'success');

    } catch (error) {
        console.error('Error exporting chart:', error);
        showNotification('Error exporting chart. Please try again.', 'error');
    }
}

function showNotification(message, type = 'info') {
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

        document.body.appendChild(notification);

        setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

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

function exportAsCSV() {
    try {
        const csvData = taskData.map(item => ({
            'Employee Name': item.name,
            'Task Type': item.type,
            'Progress': item.completed,
            'Target': item.target,
            'Unit': item.unit,
            'Achievement Percentage': Math.round((item.completed / item.target) * 100),
            'Status': item.status === 'achieve' ? 'Achieve' : 'Non-Achieve'
        }));

        const csvString = convertArrayToCSV(csvData);
        
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

function convertArrayToCSV(array) {
    const headers = Object.keys(array[0]);
    const csvContent = [
        headers.join(','),
        ...array.map(row => headers.map(header => `"${row[header]}"`).join(','))
    ].join('\n');
    
    return csvContent;
}

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
                                <div class="value">${taskData.filter(item => item.status === 'achieve').length}</div>
                            </div>
                            <div class="summary-card">
                                <h3>Non-Achieved</h3>
                                <div class="value">${taskData.filter(item => item.status === 'non-achieve').length}</div>
                            </div>
                            <div class="summary-card">
                                <h3>Success Rate</h3>
                                <div class="value">${Math.round((taskData.filter(item => item.status === 'achieve').length / taskData.length) * 100)}%</div>
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
                                        <td>${item.name}</td>
                                        <td>${item.type}</td>
                                        <td>${item.completed}</td>
                                        <td>${item.target}</td>
                                        <td>${item.unit}</td>
                                        <td>${Math.round((item.completed / item.target) * 100)}%</td>
                                        <td class="${item.status}">${item.status === 'achieve' ? 'Achieve' : 'Non-Achieve'}</td>
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

function createExportDropdown() {
    const exportButton = document.querySelector('button[onclick="exportChart()"]');
    if (exportButton) {
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

function loadExternalLibraries() {
    
    if (!window.XLSX) {
        const script1 = document.createElement('script');
        script1.src = 'https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js';
        document.head.appendChild(script1);
    }
    
    if (!window.jspdf) {
        const script2 = document.createElement('script');
        script2.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js';
        document.head.appendChild(script2);
        
        const script3 = document.createElement('script');
        script3.src = 'https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js';
        document.head.appendChild(script3);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadExternalLibraries();
    
    
    setTimeout(() => {
        createExportDropdown();
    }, 1000);
});

window.toggleSidebar = toggleSidebar;