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

let taskChart, performanceChart, progressChart;
let currentChartType = 'bar';

// Helper function to get status color
function getStatusColor(progress, target, endDate) {
    const today = new Date();
    const end = new Date(endDate);
    const achievementRate = target > 0 ? (progress / target) * 100 : 0;
    
    if (achievementRate >= 100) {
        return '#28a745'; // Green - Achieved
    } else if (today <= end) {
        return '#ffc107'; // Yellow - In Progress (within deadline)
    } else {
        return '#dc3545'; // Red - Not Achieved (past deadline)
    }
}

// Helper function to get status text
function getStatusText(progress, target, endDate) {
    const today = new Date();
    const end = new Date(endDate);
    const achievementRate = target > 0 ? (progress / target) * 100 : 0;
    
    if (achievementRate >= 100) {
        return 'Achieve';
    } else if (today <= end) {
        return 'In Progress';
    } else {
        return 'Non-Achieve';
    }
}

function getFilteredData() {
    const employeeFilter = document.getElementById('employeeFilter').value.trim();
    const taskFilter = document.getElementById('taskFilter').value.trim();
    const startDate = document.getElementById('start_date').value.trim();
    const endDate = document.getElementById('end_date').value.trim();

    let filteredData = [...taskData];

    if (employeeFilter) {
        filteredData = filteredData.filter(item => item.employee === employeeFilter);
    }

    if (taskFilter) {
        filteredData = filteredData.filter(item => item.task_name === taskFilter);
    }

    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        end.setHours(23, 59, 59, 999);
        
        filteredData = filteredData.filter(item => {
            const taskDate = new Date(item.last_update);
            return taskDate >= start && taskDate <= end;
        });
    }

    return filteredData;
}

function filterTasks() {
    const filteredData = getFilteredData();
    updateCharts(filteredData);
    updateProgressChart(filteredData);
    updateProgressTable(filteredData);
}

function downloadReport() {
    try {
        const filteredData = getFilteredData();
        
        if (filteredData.length === 0) {
            showNotification('No data available for the selected filters.', 'warning');
            return;
        }

        const reportData = filteredData.map(item => {
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
                'Status': getStatusText(item.progress, item.target, item.end_date),
                'Deadline': item.end_date || 'N/A',
                'Last Update': item.last_update || 'N/A'
            };
        });

        const worksheet = XLSX.utils.json_to_sheet(reportData);
        const columnWidths = [
            { wch: 25 }, { wch: 20 }, { wch: 35 }, { wch: 15 },
            { wch: 10 }, { wch: 15 }, { wch: 15 }, { wch: 20 }
        ];
        worksheet['!cols'] = columnWidths;

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Performance Report');

        const achievedCount = filteredData.filter(item => 
            getStatusText(item.progress, item.target, item.end_date) === 'Achieve'
        ).length;
        const nonAchievedCount = filteredData.filter(item => 
            getStatusText(item.progress, item.target, item.end_date) === 'Non-Achieve'
        ).length;
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
        
        const employeeFilter = document.getElementById('employeeFilter').value.trim();
        const taskFilter = document.getElementById('taskFilter').value.trim();
        
        if (employeeFilter || taskFilter) {
            summaryData.push({ 'Metric': '', 'Value': '' });
            summaryData.push({ 'Metric': 'APPLIED FILTERS', 'Value': '' });
            summaryData.push({ 'Metric': 'Employee Filter', 'Value': employeeFilter || 'All Employees' });
            summaryData.push({ 'Metric': 'Task Type Filter', 'Value': taskFilter || 'All Task Types' });
        } else {
            summaryData.push({ 'Metric': '', 'Value': '' });
            summaryData.push({ 'Metric': 'APPLIED FILTERS', 'Value': '' });
            summaryData.push({ 'Metric': 'Filter Status', 'Value': 'No filters applied (All data)' });
        }
        
        const summaryWorksheet = XLSX.utils.json_to_sheet(summaryData);
        summaryWorksheet['!cols'] = [{ wch: 20 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(workbook, summaryWorksheet, 'Summary');

        const currentDate = new Date();
        const dateString = currentDate.toISOString().split('T')[0];
        let filename = `Performance_Report_${dateString}`;
        
        if (employeeFilter) filename += `_${employeeFilter.replace(/\s+/g, '_')}`;
        if (taskFilter) filename += `_${taskFilter.replace(/\s+/g, '_')}`;
        if (!employeeFilter && !taskFilter) filename += '_All_Data';
        
        filename += '.xlsx';

        XLSX.writeFile(workbook, filename);
        showNotification('Report downloaded successfully!', 'success');

    } catch (error) {
        console.error('Error downloading report:', error);
        showNotification('Error downloading report. Please try again.', 'error');
    }
}

function exportChart() {
    try {
        const filteredData = getFilteredData();
        
        if (filteredData.length === 0) {
            showNotification('No data available for the selected filters.', 'warning');
            return;
        }

        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF();

        pdf.setFontSize(20);
        pdf.setTextColor(196, 30, 58);
        pdf.text('Performance Statistics Report', 20, 20);

        pdf.setFontSize(12);
        pdf.setTextColor(0, 0, 0);
        const currentDate = new Date().toLocaleDateString('id-ID');
        pdf.text(`Generated on: ${currentDate}`, 20, 30);

        const employeeFilter = document.getElementById('employeeFilter').value.trim();
        const taskFilter = document.getElementById('taskFilter').value.trim();
        
        let yPosition = 40;
        if (employeeFilter || taskFilter) {
            pdf.setFontSize(11);
            pdf.setTextColor(100, 100, 100);
            pdf.text('Applied Filters:', 20, yPosition);
            yPosition += 10;
            
            pdf.text(`Employee: ${employeeFilter || 'All Employees'}`, 25, yPosition);
            yPosition += 8;
            pdf.text(`Task Type: ${taskFilter || 'All Task Types'}`, 25, yPosition);
            yPosition += 8;
            yPosition += 5;
        } else {
            pdf.setFontSize(11);
            pdf.setTextColor(100, 100, 100);
            pdf.text('Filter Status: No filters applied (All data)', 20, yPosition);
            yPosition += 15;
        }

        pdf.setFontSize(14);
        pdf.setTextColor(44, 90, 160);
        pdf.text('Summary Statistics', 20, yPosition);
        yPosition += 15;

        pdf.setFontSize(11);
        pdf.setTextColor(0, 0, 0);
        const totalTasks = filteredData.length;
        const achievedTasks = filteredData.filter(item => 
            getStatusText(item.progress, item.target, item.end_date) === 'Achieve'
        ).length;
        const nonAchievedTasks = filteredData.filter(item => 
            getStatusText(item.progress, item.target, item.end_date) === 'Non-Achieve'
        ).length;
        const successRate = totalTasks > 0 ? Math.round((achievedTasks / totalTasks) * 100) : 0;

        pdf.text(`Total Tasks: ${totalTasks}`, 20, yPosition);
        pdf.text(`Achieved: ${achievedTasks}`, 20, yPosition + 10);
        pdf.text(`Non-Achieved: ${nonAchievedTasks}`, 20, yPosition + 20);
        pdf.text(`Success Rate: ${successRate}%`, 20, yPosition + 30);

        const taskCanvas = document.getElementById('taskChart');
        const taskImgData = taskCanvas.toDataURL('image/png');
        pdf.addImage(taskImgData, 'PNG', 20, yPosition + 45, 80, 80);

        const perfCanvas = document.getElementById('performanceChart');
        const perfImgData = perfCanvas.toDataURL('image/png');
        pdf.addImage(perfImgData, 'PNG', 110, yPosition + 45, 80, 80);

        if (filteredData.length > 0) {
            pdf.addPage();
            
            pdf.setFontSize(14);
            pdf.setTextColor(44, 90, 160);
            pdf.text('Detailed Performance Data', 20, 20);

            const tableData = filteredData.map(item => {
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
                    item.task_name, item.employee, item.description || 'N/A',
                    targetDisplay, item.progress.toString(),
                    getStatusText(item.progress, item.target, item.end_date),
                    item.end_date || 'N/A', item.last_update || 'N/A'
                ];
            });

            pdf.autoTable({
                head: [['Task Type', 'Employee', 'Description', 'Target', 'Progress', 'Status', 'Deadline', 'Last Update']],
                body: tableData,
                startY: 30,
                styles: { fontSize: 8, cellPadding: 2 },
                headStyles: { fillColor: [196, 30, 58], textColor: 255 },
                alternateRowStyles: { fillColor: [245, 245, 245] }
            });
        }

        const dateString = new Date().toISOString().split('T')[0];
        let filename = `Performance_Chart_${dateString}`;
        
        if (employeeFilter) filename += `_${employeeFilter.replace(/\s+/g, '_')}`;
        if (taskFilter) filename += `_${taskFilter.replace(/\s+/g, '_')}`;
        if (!employeeFilter && !taskFilter) filename += '_All_Data';
        
        filename += '.pdf';

        pdf.save(filename);
        showNotification('Chart exported successfully!', 'success');

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

function initCharts() {
    const taskCtx = document.getElementById('taskChart').getContext('2d');
    taskChart = new Chart(taskCtx, {
        type: 'doughnut',
        data: {
            labels: [],
            datasets: [{
                data: [],
                backgroundColor: [
                    '#c41e3a', '#2c5aa0', '#28a745', '#ffc107', '#dc3545',
                    '#6c757d', '#17a2b8', '#fd7e14', '#e83e8c', '#6f42c1'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Task Distribution by Type',
                    font: { size: 16, weight: 'bold' },
                    padding: 20
                },
                legend: {
                    position: 'right',
                    labels: { font: { size: 12 } }
                }
            }
        }
    });

    const perfCtx = document.getElementById('performanceChart').getContext('2d');
    performanceChart = new Chart(perfCtx, {
        type: 'bar',
        data: {
            labels: ['Achieved', 'Non-Achieved'],
            datasets: [{
                label: 'Task Count',
                data: [],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Achievement Status Overview',
                    font: { size: 16, weight: 'bold' },
                    padding: 20
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 12 } }
                },
                x: {
                    ticks: { font: { size: 12 } }
                }
            }
        }
    });
}

function initProgressChart() {
    const ctx = document.getElementById('progressChart').getContext('2d');
    progressChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: [],
            datasets: [{
                label: 'Target',
                data: [],
                backgroundColor: 'rgba(169, 169, 169, 0.8)',
                borderColor: 'rgba(169, 169, 169, 1)',
                borderWidth: 2,
                tension: 0  // Garis lurus untuk Target
            }, {
                label: 'Progress',
                data: [],
                backgroundColor: [],
                borderColor: [],
                borderWidth: 2,
                tension: 0,  // Garis lurus untuk Progress
                pointRadius: currentChartType === 'line' ? 6 : 0,
                pointHoverRadius: currentChartType === 'line' ? 8 : 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            aspectRatio: 1.5,
            plugins: {
                title: {
                    display: true,
                    text: 'Employee Progress vs Target',
                    font: { size: 14, weight: 'bold' },
                    padding: { top: 8, bottom: 15 }
                },
                legend: {
                    position: 'top',
                    labels: { font: { size: 12 }, padding: 10 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { font: { size: 12 }, padding: 8 }
                },
                x: {
                    grid: { display: true,
                    color: 'rgba(0, 0, 0, 0.15)',
                    lineWidth: 1  
                     },
                    ticks: { 
                        font: { size: 11 },
                        maxRotation: 30,    // Ubah dari 45 ke 30 derajat
                        minRotation: 15,    // Ubah dari 45 ke 15 derajat
                        padding: 8
                    }
                }
            },
            layout: {
                padding: { left: 15, right: 15, top: 10, bottom: 25 }  // Tambah bottom padding
            },
            barThickness: 30,
            maxBarThickness: 40
        }
    });
}

function updateCharts(data) {
    const taskTypes = [...new Set(data.map(item => item.task_name))];
    const taskCounts = taskTypes.map(type => data.filter(item => item.task_name === type).length);
    taskChart.data.labels = taskTypes;
    taskChart.data.datasets[0].data = taskCounts;
    taskChart.update();

    const achieveCount = data.filter(item => 
        getStatusText(item.progress, item.target, item.end_date) === 'Achieve'
    ).length;
    const nonAchieveCount = data.filter(item => 
        getStatusText(item.progress, item.target, item.end_date) === 'Non-Achieve'
    ).length;
    
    performanceChart.data.datasets[0].data = [achieveCount, nonAchieveCount];
    performanceChart.update();
}

function updateProgressChart(data) {
    if (!progressChart) return;

    // Potong label jika terlalu panjang untuk readability yang lebih baik
    const labels = data.map(item => {
        const fullLabel = `${item.employee} - ${item.task_name}`;
        // Potong label jika lebih dari 25 karakter
        return fullLabel.length > 25 ? fullLabel.substring(0, 22) + '...' : fullLabel;
    });
    
    const targetData = data.map(item => item.target);
    const progressData = data.map(item => item.progress);
    
    // Generate colors based on status
    const progressColors = data.map(item => getStatusColor(item.progress, item.target, item.end_date));
    
    progressChart.data.labels = labels;
    progressChart.data.datasets[0].data = targetData;
    progressChart.data.datasets[1].data = progressData;
    progressChart.data.datasets[1].backgroundColor = progressColors;
    progressChart.data.datasets[1].borderColor = progressColors;
    
    progressChart.update();
}

function updateProgressTable(data) {
    const tbody = document.getElementById('progressTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';

    data.forEach(item => {
        const achievementRate = item.target > 0 ? Math.round((item.progress / item.target) * 100) : 0;
        const statusText = getStatusText(item.progress, item.target, item.end_date);
        const statusColor = getStatusColor(item.progress, item.target, item.end_date);
        
        // Determine row background color based on status
        let rowClass = '';
        if (statusText === 'Achieve') {
            rowClass = 'table-success';
        } else if (statusText === 'In Progress') {
            rowClass = 'table-warning';
        } else {
            rowClass = 'table-light';
        }
        
        const row = document.createElement('tr');
        row.className = rowClass;
        row.innerHTML = `
            <td><strong>${item.employee}</strong></td>
            <td>${item.task_name}</td>
            <td>${item.progress}</td>
            <td>${item.target}</td>
            <td>WO/HARI</td>
            <td>
                ${achievementRate}%
                <div class="progress mt-1" style="height: 6px;">
                    <div class="progress-bar" style="width: ${Math.min(achievementRate, 100)}%; background-color: ${statusColor};"></div>
                </div>
            </td>
            <td><span class="badge" style="background-color: ${statusColor}; color: white;">${statusText}</span></td>
        `;
        tbody.appendChild(row);
    });
}

function toggleChartType() {
    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
    progressChart.destroy();
    initProgressChart();
    filterTasks();
}

document.addEventListener('DOMContentLoaded', function() {
    closeSidebar();
    setupClickOutside();
    
    initCharts();
    initProgressChart();
    
    const filters = ['employeeFilter', 'taskFilter', 'start_date', 'end_date'];
    filters.forEach(id => {
        document.getElementById(id)?.addEventListener('change', filterTasks);
    });
    
    flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        onChange: function() { filterTasks(); }
    });

    flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        onChange: function() { filterTasks(); }
    });
    
    filterTasks();
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) modal.hide();
        
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});