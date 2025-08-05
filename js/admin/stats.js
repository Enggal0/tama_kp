function toggleSidebar() {
    console.log('Toggle sidebar clicked');
    
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    if (!sidebar || !mainContent) {
        console.error('Sidebar atau mainContent tidak ditemukan');
        return;
    }
    
    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);

    if (isCollapsed) {
        body.classList.add('sidebar-collapsed');
    } else {
        body.classList.remove('sidebar-collapsed');
    }
    
    console.log('Sidebar state changed. Collapsed:', isCollapsed);
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    if (sidebar && mainContent) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
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
        
        if (isMobile && sidebar && burgerBtn && !sidebar.classList.contains('collapsed')) {
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
        
        if (sidebar && window.innerWidth > 768 && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}

function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    if (sidebar && mainContent) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
        console.log('Sidebar initialized as collapsed');
    }
}

function setupBurgerButton() {
    const burgerBtn = document.getElementById('burgerBtn');
    
    if (burgerBtn) {
        burgerBtn.onclick = null;
        
        burgerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Burger button clicked');
            toggleSidebar();
        });
        
        console.log('Burger button event listener attached');
    } else {
        console.error('Burger button not found');
    }
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
    window.location.href = '../logout.php';
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
        if (sidebar && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    
    initializeSidebar();
    
    
    setupBurgerButton();
    
    
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
    
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);

    
    const employeeFilter = document.getElementById('employeeFilter');
    const taskFilter = document.getElementById('taskFilter');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    if (employeeFilter) employeeFilter.addEventListener('change', filterTasks);
    if (taskFilter) taskFilter.addEventListener('change', filterTasks);
    if (startDate) startDate.addEventListener('change', filterTasks);
    if (endDate) endDate.addEventListener('change', filterTasks);
});

const originalOnload = window.onload;
window.onload = () => {
    if (originalOnload) originalOnload();
    initProgressChart();
};

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
                'Status': item.status === 'achieved' ? 'Achieved' : 'Non-Achieved',
                'Deadline': item.deadline || 'N/A',
                'Last Update': item.last_update || 'N/A'
            };
        });

        const worksheet = XLSX.utils.json_to_sheet(reportData);
        
        const columnWidths = [
            { wch: 25 }, 
            { wch: 20 }, 
            { wch: 35 }, 
            { wch: 15 }, 
            { wch: 10 }, 
            { wch: 15 }, 
            { wch: 15 }, 
            { wch: 20 }  
        ];
        worksheet['!cols'] = columnWidths;

        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, 'Performance Report');

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
        
        const employeeFilter = document.getElementById('employeeFilter').value.trim();
        const taskFilter = document.getElementById('taskFilter').value.trim();
        
        if (employeeFilter || taskFilter) {
            summaryData.push({ 'Metric': '', 'Value': '' }); 
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
        
        if (employeeFilter) {
            filename += `_${employeeFilter.replace(/\s+/g, '_')}`;
        }
        if (taskFilter) {
            filename += `_${taskFilter.replace(/\s+/g, '_')}`;
        }
        
        if (!employeeFilter && !taskFilter) {
            filename += '_All_Data';
        }
        
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
                    item.task_name,          
                    item.employee,          
                    item.description || 'N/A',  
                    targetDisplay,      
                    item.progress.toString(),   
                    item.status === 'achieved' ? 'Achieved' : 'Non-Achieved',  
                    item.deadline || 'N/A',     
                    item.last_update || 'N/A'   
                ];
            });

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

        const dateString = new Date().toISOString().split('T')[0];
        let filename = `Performance_Chart_${dateString}`;
        
        if (employeeFilter) {
            filename += `_${employeeFilter.replace(/\s+/g, '_')}`;
        }
        if (taskFilter) {
            filename += `_${taskFilter.replace(/\s+/g, '_')}`;
        }
        
        if (!employeeFilter && !taskFilter) {
            filename += '_All_Data';
        }
        
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
            'Employee Name': item.employee,
            'Task Type': item.task_name,
            'Progress': item.progress,
            'Target': item.target,
            'Unit': item.unit,
            'Achievement Percentage': Math.round((item.progress / item.target) * 100),
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

const taskFilterElement = document.getElementById('taskFilter');
if (taskFilterElement) {
    taskFilterElement.addEventListener('change', function() {
        filterTasks(); 
        updateProgressChart(); 
    });
}

let taskChart, performanceChart, progressChart;
let currentChartType = 'bar';

document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    initProgressChart();
    
    const filters = ['employeeFilter', 'taskFilter', 'start_date', 'end_date'];
    filters.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            element.addEventListener('change', filterTasks);
        }
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

function filterTasks() {
    const filteredData = getFilteredData();
    updateCharts(filteredData);
    updateProgressChart(filteredData);
    updateProgressTable(filteredData);
}

function getFilteredData() {
    const employeeFilter = document.getElementById('employeeFilter');
    const taskFilter = document.getElementById('taskFilter');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    let filteredData = [...taskData];

    if (employeeFilter && employeeFilter.value.trim()) {
        filteredData = filteredData.filter(item => item.employee === employeeFilter.value.trim());
    }

    if (taskFilter && taskFilter.value.trim()) {
        filteredData = filteredData.filter(item => item.task_name === taskFilter.value.trim());
    }

    if (startDate && endDate && startDate.value.trim() && endDate.value.trim()) {
        const filterStart = new Date(startDate.value.trim());
        const filterEnd = new Date(endDate.value.trim());
        filterEnd.setHours(23, 59, 59, 999);

        filteredData = filteredData.filter(item => {
            const taskStart = item.start_date ? new Date(item.start_date) : null;
            const taskEnd = item.end_date ? new Date(item.end_date) : null;
            if (!taskStart || !taskEnd) return false;
            return taskStart <= filterEnd && taskEnd >= filterStart;
        });
    }

    return filteredData;
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
            aspectRatio: 1.5,
            plugins: {
                title: {
                    display: true,
                    text: 'Employee Progress vs Target',
                    font: { 
                        size: 18,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                },
                legend: {
                    position: 'top',
                    labels: { 
                        font: { size: 14 },
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        font: { size: 12 },
                        padding: 8
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        font: { size: 11 },
                        maxRotation: 15, 
                        minRotation: 0,  
                        padding: 10,
                        callback: function(value, index, values) {
                            
                            const label = this.getLabelForValue(value);
                            if (label && label.length > 25) {
                                return label.substring(0, 22) + '...';
                            }
                            return label;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 15,
                    right: 15,
                    top: 15,
                    bottom: 25 
                }
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

    const employeeFilter = document.getElementById('employeeFilter');
    const taskFilter = document.getElementById('taskFilter');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    let filteredAchievement = achievementStatusData;
    if (employeeFilter && employeeFilter.value.trim()) {
        filteredAchievement = filteredAchievement.filter(item => item.employee === employeeFilter.value.trim());
    }
    if (taskFilter && taskFilter.value.trim()) {
        filteredAchievement = filteredAchievement.filter(item => item.task_name === taskFilter.value.trim());
    }
    if (startDate && endDate && startDate.value.trim() && endDate.value.trim()) {
        const start = new Date(startDate.value.trim());
        const end = new Date(endDate.value.trim());
        filteredAchievement = filteredAchievement.filter(item => {
            if (!item.created_at) return false;
            const created = new Date(item.created_at.substring(0, 10));
            return created >= start && created <= end;
        });
    }

    const achieveCount = filteredAchievement.filter(item => item.status === 'achieved').length;
    const nonAchieveCount = filteredAchievement.filter(item => item.status === 'non achieved').length;
    performanceChart.data.datasets[0].data = [achieveCount, nonAchieveCount];
    performanceChart.update();
}

function updateProgressChart(data) {
    if (!progressChart) return;

    const employeeFilter = document.getElementById('employeeFilter');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    const taskGroups = {};
    achievementStatusData.forEach(item => {
        if (employeeFilter && employeeFilter.value.trim() && item.employee !== employeeFilter.value.trim()) return;
        if (startDate && endDate && startDate.value.trim() && endDate.value.trim()) {
            if (!item.created_at) return;
            const created = new Date(item.created_at.substring(0, 10));
            const start = new Date(startDate.value.trim());
            const end = new Date(endDate.value.trim());
            if (created < start || created > end) return;
        }
        const taskName = item.task_name;
        if (!taskGroups[taskName]) {
            taskGroups[taskName] = {
                name: taskName,
                totalWorkOrders: 0,
                totalCompleted: 0
            };
        }
        taskGroups[taskName].totalWorkOrders += (parseInt(item.work_orders) || 0);
        taskGroups[taskName].totalCompleted += (parseInt(item.work_orders_completed) || 0);
    });

    const groupedData = Object.values(taskGroups);
    const labels = groupedData.map(group => group.name);
    const workOrdersData = groupedData.map(group => group.totalWorkOrders);
    const completedData = groupedData.map(group => group.totalCompleted);

    const targetColors = [];
    const progressColors = [];
    
    groupedData.forEach(group => {
        const achievementRate = group.totalWorkOrders > 0 ? (group.totalCompleted / group.totalWorkOrders) * 100 : 0;
        
        targetColors.push('rgba(169, 169, 169, 0.8)');
        
        if (achievementRate >= 100) {
            progressColors.push('rgba(40, 167, 69, 0.8)');
        } else if (achievementRate >= 80) {
            progressColors.push('rgba(255, 193, 7, 0.8)');
        } else {
            progressColors.push('rgba(220, 53, 69, 0.8)');
        }
    });

    progressChart.data.labels = labels;
    progressChart.data.datasets[0].label = 'Total Work Orders';
    progressChart.data.datasets[0].data = workOrdersData;
    progressChart.data.datasets[0].backgroundColor = targetColors;
    progressChart.data.datasets[0].borderColor = 'rgba(169, 169, 169, 1)';
    
    progressChart.data.datasets[1].label = 'Work Orders Completed';
    progressChart.data.datasets[1].data = completedData;
    progressChart.data.datasets[1].backgroundColor = progressColors;
    progressChart.data.datasets[1].borderColor = progressColors.map(color => color.replace('0.8', '1'));
    
    if (currentChartType === 'line') {
        progressChart.data.datasets[0].backgroundColor = 'rgba(169, 169, 169, 0.2)';
        progressChart.data.datasets[0].fill = false;
        progressChart.data.datasets[0].pointBackgroundColor = 'rgba(169, 169, 169, 1)';
        progressChart.data.datasets[0].pointBorderColor = 'rgba(169, 169, 169, 1)';
        progressChart.data.datasets[0].pointRadius = 6;
        progressChart.data.datasets[0].tension = 0.4;
        
        progressChart.data.datasets[1].backgroundColor = 'rgba(220, 53, 69, 0.2)';
        progressChart.data.datasets[1].fill = false;
        progressChart.data.datasets[1].pointBackgroundColor = 'rgba(220, 53, 69, 1)';
        progressChart.data.datasets[1].pointBorderColor = 'rgba(220, 53, 69, 1)';
        progressChart.data.datasets[1].pointRadius = 6;
        progressChart.data.datasets[1].tension = 0.4;
    }
    
    progressChart.update();
}

function updateProgressTable(data) {
    const tbody = document.getElementById('progressTableBody');
    if (!tbody) return;

    tbody.innerHTML = '';
    const employeeFilter = document.getElementById('employeeFilter');
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    if (!employeeFilter || !employeeFilter.value.trim()) {
        const groupedByTask = {};
        achievementStatusData.forEach(item => {
            if (startDate && endDate && startDate.value.trim() && endDate.value.trim()) {
                if (!item.created_at) return;
                const created = new Date(item.created_at.substring(0, 10));
                const start = new Date(startDate.value.trim());
                const end = new Date(endDate.value.trim());
                if (created < start || created > end) return;
            }
            const key = item.task_name;
            if (!groupedByTask[key]) {
                groupedByTask[key] = {
                    user_task_ids: new Set(),
                    achieved: 0,
                    nonAchieved: 0,
                    work_orders: 0,
                    work_orders_completed: 0,
                    task_name: item.task_name,
                    employees: new Set()
                };
            }
            if (item.user_task_id) groupedByTask[key].user_task_ids.add(item.user_task_id);
            if (item.status === 'achieved') groupedByTask[key].achieved++;
            else groupedByTask[key].nonAchieved++;
            groupedByTask[key].work_orders += parseInt(item.work_orders) || 0;
            groupedByTask[key].work_orders_completed += parseInt(item.work_orders_completed) || 0;
            if (item.employee) groupedByTask[key].employees.add(item.employee);
        });
        Object.values(groupedByTask).forEach(stats => {
            const achievementRate = stats.work_orders > 0 ? Math.round((stats.work_orders_completed / stats.work_orders) * 100) : 0;
            const employeeList = Array.from(stats.employees).join(', ');
            
            let rowClass = '';
            if (achievementRate >= 100) {
                rowClass = 'table-success';
            } else if (achievementRate >= 80) {
                rowClass = 'table-warning';
            } else {
                rowClass = 'table-danger';
            }
            
            const row = document.createElement('tr');
            row.className = rowClass;
            row.innerHTML = `
                <td>${stats.task_name}</td>
                <td>${employeeList || '-'}</td>
                <td>${stats.user_task_ids.size}</td>
                <td>${stats.achieved}</td>
                <td>${stats.nonAchieved}</td>
                <td>${stats.work_orders_completed}</td>
                <td>${achievementRate}%</td>
            `;
            tbody.appendChild(row);
        });
        return;
    }

    const grouped = {};
    achievementStatusData.forEach(item => {
        if (employeeFilter && employeeFilter.value.trim() && item.employee !== employeeFilter.value.trim()) return;
        if (startDate && endDate && startDate.value.trim() && endDate.value.trim()) {
            if (!item.created_at) return;
            const created = new Date(item.created_at.substring(0, 10));
            const start = new Date(startDate.value.trim());
            const end = new Date(endDate.value.trim());
            if (created < start || created > end) return;
        }
        const key = item.task_name + '||' + (item.employee || 'All');
        if (!grouped[key]) {
            grouped[key] = {
                user_task_ids: new Set(),
                achieved: 0,
                nonAchieved: 0,
                work_orders: 0,
                work_orders_completed: 0,
                task_name: item.task_name,
                employee: item.employee
            };
        }
        if (item.user_task_id) grouped[key].user_task_ids.add(item.user_task_id);
        if (item.status === 'achieved') grouped[key].achieved++;
        else grouped[key].nonAchieved++;
        grouped[key].work_orders += parseInt(item.work_orders) || 0;
        grouped[key].work_orders_completed += parseInt(item.work_orders_completed) || 0;
    });

    Object.values(grouped).forEach(stats => {
        const achievementRate = stats.work_orders > 0 ? Math.round((stats.work_orders_completed / stats.work_orders) * 100) : 0;
        
        let rowClass = '';
        if (achievementRate >= 100) {
            rowClass = 'table-success';
        } else if (achievementRate >= 80) {
            rowClass = 'table-warning';
        } else {
            rowClass = 'table-danger';
        }
        
        const row = document.createElement('tr');
        row.className = rowClass;
        row.innerHTML = `
            <td>${stats.task_name}</td>
            <td>${(employeeFilter && employeeFilter.value.trim()) || stats.employee || 'All'}</td>
            <td>${stats.user_task_ids.size}</td>
            <td>${stats.achieved}</td>
            <td>${stats.nonAchieved}</td>
            <td>${stats.work_orders_completed}</td>
            <td>${achievementRate}%</td>
        `;
        tbody.appendChild(row);
    });
}

function toggleChartType() {
    currentChartType = currentChartType === 'bar' ? 'line' : 'bar';
    
    progressChart.destroy();
    
    const ctx = document.getElementById('progressChart').getContext('2d');
    progressChart = new Chart(ctx, {
        type: currentChartType,
        data: {
            labels: [],
            datasets: [{
                label: 'Target',
                data: [],
                backgroundColor: currentChartType === 'line' ? 'rgba(169, 169, 169, 0.2)' : 'rgba(169, 169, 169, 0.8)',
                borderColor: 'rgba(169, 169, 169, 1)',
                borderWidth: currentChartType === 'line' ? 3 : 1,
                fill: currentChartType === 'line' ? false : true,
                pointBackgroundColor: currentChartType === 'line' ? 'rgba(169, 169, 169, 1)' : undefined,
                pointBorderColor: currentChartType === 'line' ? 'rgba(169, 169, 169, 1)' : undefined,
                pointRadius: currentChartType === 'line' ? 8 : undefined, 
                tension: currentChartType === 'line' ? 0.4 : undefined
            }, {
                label: 'Progress',
                data: [],
                backgroundColor: currentChartType === 'line' ? 'rgba(220, 53, 69, 0.2)' : 'rgba(220, 53, 69, 0.8)',
                borderColor: 'rgba(220, 53, 69, 1)',
                borderWidth: currentChartType === 'line' ? 3 : 1,
                fill: currentChartType === 'line' ? false : true,
                pointBackgroundColor: currentChartType === 'line' ? 'rgba(220, 53, 69, 1)' : undefined,
                pointBorderColor: currentChartType === 'line' ? 'rgba(220, 53, 69, 1)' : undefined,
                pointRadius: currentChartType === 'line' ? 8 : undefined, 
                tension: currentChartType === 'line' ? 0.4 : undefined
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
                    font: { 
                        size: 18,
                        weight: 'bold'
                    },
                    padding: {
                        top: 10,
                        bottom: 20
                    }
                },
                legend: {
                    position: 'top',
                    labels: { 
                        font: { size: 14 },
                        padding: 15
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { 
                        display: true,
                        color: 'rgba(0, 0, 0, 0.1)' 
                    },
                    ticks: { 
                        font: { size: 12 },
                        padding: 8
                    }
                },
                x: {
                    grid: { display: currentChartType === 'line' },
                    ticks: { 
                        font: { size: 11 }, 
                        maxRotation: 15, 
                        minRotation: 0,  
                        padding: 10,
                        callback: function(value, index, values) {
                            
                            const label = this.getLabelForValue(value);
                            if (label && label.length > 25) {
                                return label.substring(0, 22) + '...';
                            }
                            return label;
                        }
                    }
                }
            },
            layout: {
                padding: {
                    left: 15,
                    right: 15,
                    top: 15,
                    bottom: 25 
                }
            },
            barThickness: currentChartType === 'bar' ? 30 : undefined,
            maxBarThickness: currentChartType === 'bar' ? 40 : undefined
        }
    });
    
    filterTasks();
    
    const toggleButton = document.querySelector('button[onclick="toggleChartType()"]');
    if (toggleButton) {
        const icon = toggleButton.querySelector('i');
        if (currentChartType === 'line') {
            if (icon) icon.className = 'bi bi-graph-up me-1';
            toggleButton.innerHTML = '<i class="bi bi-graph-up me-1"></i>Switch to Bar Chart';
        } else {
            if (icon) icon.className = 'bi bi-bar-chart me-1';
            toggleButton.innerHTML = '<i class="bi bi-bar-chart me-1"></i>Switch to Line Chart';
        }
    }
}