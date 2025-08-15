const ChartManager = {
    charts: { task: null, performance: null, progress: null },
    currentChartType: 'bar',

    getResponsiveSettings() {
        const width = window.innerWidth;
        const breakpoints = [
            { max: 480, settings: { 
                titleSize: 12, legendSize: 10, tickSize: 9, 
                progressHeight: 250, barThickness: 15, maxRotation: 45
            }},
            { max: 576, settings: { 
                titleSize: 13, legendSize: 11, tickSize: 10, 
                progressHeight: 300, barThickness: 18, maxRotation: 45
            }},
            { max: 768, settings: { 
                titleSize: 14, legendSize: 12, tickSize: 11, 
                progressHeight: 350, barThickness: 22, maxRotation: 30
            }},
            { max: 992, settings: { 
                titleSize: 16, legendSize: 13, tickSize: 12, 
                progressHeight: 400, barThickness: 26, maxRotation: 20
            }},
            { max: Infinity, settings: { 
                titleSize: 18, legendSize: 14, tickSize: 12, 
                progressHeight: 450, barThickness: 30, maxRotation: 15
            }}
        ];
        
        return breakpoints.find(bp => width <= bp.max).settings;
    },

    initTaskChart() {
        const ctx = document.getElementById('taskChart').getContext('2d');
        const settings = this.getResponsiveSettings();
        
        this.charts.task = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: [],
                datasets: [{
                    data: [],
                    backgroundColor: ['#c41e3a', '#2c5aa0', '#28a745', '#ffc107', '#dc3545', '#6c757d', '#17a2b8', '#fd7e14', '#e83e8c', '#6f42c1']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    title: {
                        display: true,
                        text: window.innerWidth <= 576 ? 'Task Distribution' : 'Task Distribution by Type',
                        font: { size: settings.titleSize, weight: 'bold' }
                    },
                    legend: {
                        position: window.innerWidth <= 576 ? 'bottom' : 'right',
                        labels: { font: { size: settings.legendSize } }
                    }
                }
            }
        });
    },

    initPerformanceChart() {
        const ctx = document.getElementById('performanceChart').getContext('2d');
        const settings = this.getResponsiveSettings();
        
        this.charts.performance = new Chart(ctx, {
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
                        text: window.innerWidth <= 576 ? 'Achievement Status' : 'Achievement Status Overview',
                        font: { size: settings.titleSize, weight: 'bold' }
                    },
                    legend: { display: window.innerWidth > 576 }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        ticks: { font: { size: settings.tickSize } }
                    },
                    x: { 
                        ticks: { font: { size: settings.tickSize } }
                    }
                },
                elements: {
                    bar: {
                        borderRadius: 0, 
                        borderSkipped: false
                    }
                }
            }
        });
    },

    initProgressChart() {
        const ctx = document.getElementById('progressChart').getContext('2d');
        const settings = this.getResponsiveSettings();
        
        this.charts.progress = new Chart(ctx, {
            type: this.currentChartType,
            data: {
                labels: [],
                datasets: [{
                    label: 'Total Work Orders',
                    data: [],
                    backgroundColor: 'rgba(169, 169, 169, 0.8)',
                    borderColor: 'rgba(169, 169, 169, 1)',
                    borderWidth: 1,
                    ...(this.currentChartType === 'bar' && {
                        borderRadius: 0,
                        borderSkipped: false
                    }),
                    ...(this.currentChartType === 'line' && {
                        fill: false,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBorderWidth: 2,
                        pointBackgroundColor: 'rgba(169, 169, 169, 1)',
                        pointBorderColor: 'rgba(255, 255, 255, 1)'
                    })
                }, 
                {
                    label: 'Work Orders Completed',
                    data: [],
                    backgroundColor: 'rgba(108, 117, 125, 0.6)',
                    borderColor: 'rgba(108, 117, 125, 1)',
                    borderWidth: 1,
                    ...(this.currentChartType === 'bar' && {
                        borderRadius: 0, 
                        borderSkipped: false
                    }),
                    
                    ...(this.currentChartType === 'line' && {
                        fill: false,
                        tension: 0.4,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        pointBorderWidth: 2,
                        pointBackgroundColor: 'rgba(108, 117, 125, 1)',
                        pointBorderColor: 'rgba(255, 255, 255, 1)'
                    })
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    title: {
                        display: true,
                        text: window.innerWidth <= 576 ? 'Progress vs Target' : 'Employee Progress vs Target',
                        font: { size: settings.titleSize, weight: 'bold' },
                        padding: { top: 15, bottom: 20 }
                    },
                    legend: {
                        position: window.innerWidth <= 576 ? 'bottom' : 'top',
                        labels: { 
                            font: { size: settings.legendSize },
                            usePointStyle: true,
                            padding: window.innerWidth <= 576 ? 10 : 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { size: settings.tickSize },
                        bodyFont: { size: settings.tickSize - 1 },
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { 
                            font: { size: settings.tickSize },
                            padding: 8,
                            callback: function(value) {
                                return Number.isInteger(value) ? value : '';
                            }
                        },
                        grid: {
                            display: true,
                            color: 'rgba(0, 0, 0, 0.1)',
                            lineWidth: 1
                        }
                    },
                    x: {
                        grid: { 
                            display: this.currentChartType === 'line'
                        },
                        ticks: { 
                            font: { size: settings.tickSize },
                            maxRotation: settings.maxRotation,
                            minRotation: 0,
                            padding: 8,
                            callback: function(value) {
                                const label = this.getLabelForValue(value);
                                if (!label) return label;
                                
                                const maxLength = window.innerWidth <= 480 ? 8 : 
                                                window.innerWidth <= 576 ? 10 : 
                                                window.innerWidth <= 768 ? 15 : 25;
                                
                                return label.length > maxLength ? label.substring(0, maxLength - 3) + '...' : label;
                            }
                        }
                    }
                },
                layout: {
                    padding: { top: 10, bottom: 10, left: 10, right: 10 }
                },
                
                elements: {
                    bar: {
                        borderRadius: 0, 
                        borderSkipped: false
                    },
                    line: {
                        tension: 0.4,
                        borderCapStyle: 'round',
                        borderJoinStyle: 'round'
                    },
                    point: {
                        radius: 6,
                        hoverRadius: 8,
                        borderWidth: 2,
                        backgroundColor: '#ffffff'
                    }
                },
                datasets: {
                    bar: {
                        barThickness: settings.barThickness,
                        maxBarThickness: settings.barThickness + 10,
                        categoryPercentage: 0.8,
                        barPercentage: 0.9,
                        
                        borderRadius: 0,
                        borderSkipped: false
                    }
                }
            }
        });
    },

    toggleChartType() {
        this.currentChartType = this.currentChartType === 'bar' ? 'line' : 'bar';
        
        const currentData = this.charts.progress ? {
            labels: [...this.charts.progress.data.labels],
            datasets: this.charts.progress.data.datasets.map(dataset => ({
                label: dataset.label,
                data: [...dataset.data],
                backgroundColor: dataset.backgroundColor
            }))
        } : null;
        
        if (this.charts.progress) {
            this.charts.progress.destroy();
        }
        
        this.initProgressChart();
        
        if (currentData) {
            this.charts.progress.data.labels = currentData.labels;
            this.charts.progress.data.datasets.forEach((dataset, index) => {
                if (currentData.datasets[index]) {
                    dataset.data = currentData.datasets[index].data;
                    
                    if (index === 1) { 
                        dataset.backgroundColor = this.calculateProgressColors(currentData.datasets[0].data, dataset.data);
                        if (this.currentChartType === 'line') {
                            dataset.pointBackgroundColor = dataset.backgroundColor;
                        }
                    }
                }
            });
            this.charts.progress.update();
        }
        
        DataManager.filterTasks();
        
        
        const toggleButton = document.querySelector('button[onclick="toggleChartType()"]');
        if (toggleButton) {
            const isLine = this.currentChartType === 'line';
            const buttonText = window.innerWidth <= 576 ? 
                (isLine ? 'Bar Chart' : 'Line Chart') :
                (isLine ? 'Switch to Bar Chart' : 'Switch to Line Chart');
            toggleButton.innerHTML = `<i class="bi bi-${isLine ? 'graph-up' : 'bar-chart'} me-1"></i>${buttonText}`;
        }
    },

    calculateProgressColors(totalData, completedData) {
        return completedData.map((completed, index) => {
            const total = totalData[index] || 0;
            const rate = total > 0 ? (completed / total) * 100 : 0;
            if (rate >= 100) return 'rgba(40, 167, 69, 0.8)';
            if (rate >= 80) return 'rgba(255, 193, 7, 0.8)';
            return 'rgba(220, 53, 69, 0.8)';
        });
    },

    updateCharts(data) {
        const taskTypes = [...new Set(data.map(item => item.task_name))];
        const taskCounts = taskTypes.map(type => data.filter(item => item.task_name === type).length);
        this.charts.task.data.labels = taskTypes;
        this.charts.task.data.datasets[0].data = taskCounts;
        this.charts.task.update();

        const filteredAchievement = this.getFilteredAchievementData();
        const achieveCount = filteredAchievement.filter(item => item.status === 'achieved').length;
        const nonAchieveCount = filteredAchievement.filter(item => item.status === 'non achieved').length;
        this.charts.performance.data.datasets[0].data = [achieveCount, nonAchieveCount];
        this.charts.performance.update();
    },

    updateProgressChart() {
        if (!this.charts.progress) return;

        const filters = DataManager.getFilters();
        const taskGroups = {};
        
        achievementStatusData.forEach(item => {
            if (filters.employee && item.employee !== filters.employee) return;
            if (filters.startDate && filters.endDate) {
                if (!item.created_at) return;
                const created = new Date(item.created_at.substring(0, 10));
                const start = new Date(filters.startDate);
                const end = new Date(filters.endDate);
                if (created < start || created > end) return;
            }
            
            const taskName = item.task_name;
            if (!taskGroups[taskName]) {
                taskGroups[taskName] = { name: taskName, totalWorkOrders: 0, totalCompleted: 0 };
            }
            taskGroups[taskName].totalWorkOrders += (parseInt(item.work_orders) || 0);
            taskGroups[taskName].totalCompleted += (parseInt(item.work_orders_completed) || 0);
        });

        const groupedData = Object.values(taskGroups);
        const labels = groupedData.map(group => group.name);
        const workOrdersData = groupedData.map(group => group.totalWorkOrders);
        const completedData = groupedData.map(group => group.totalCompleted);

        const progressColors = this.calculateProgressColors(workOrdersData, completedData);

        this.charts.progress.data.labels = labels;
        this.charts.progress.data.datasets[0].data = workOrdersData;
        this.charts.progress.data.datasets[1].data = completedData;
        this.charts.progress.data.datasets[1].backgroundColor = progressColors;
        
        if (this.currentChartType === 'line') {
            this.charts.progress.data.datasets[1].pointBackgroundColor = progressColors;
        }
        
        this.charts.progress.update();
    },

    getFilteredAchievementData() {
        const filters = DataManager.getFilters();
        let filteredAchievement = [...achievementStatusData];
        
        if (filters.employee) {
            filteredAchievement = filteredAchievement.filter(item => item.employee === filters.employee);
        }
        if (filters.task) {
            filteredAchievement = filteredAchievement.filter(item => item.task_name === filters.task);
        }
        if (filters.startDate && filters.endDate) {
            const start = new Date(filters.startDate);
            const end = new Date(filters.endDate);
            filteredAchievement = filteredAchievement.filter(item => {
                if (!item.created_at) return false;
                const created = new Date(item.created_at.substring(0, 10));
                return created >= start && created <= end;
            });
        }
        
        return filteredAchievement;
    },

    handleResize() {
        Object.values(this.charts).forEach(chart => {
            if (chart) chart.destroy();
        });
        
        setTimeout(() => {
            this.initTaskChart();
            this.initPerformanceChart();
            this.initProgressChart();
            DataManager.filterTasks();
        }, 100);
    }
};

const DataManager = {
    getFilters() {
        return {
            employee: document.getElementById('employeeFilter')?.value.trim(),
            task: document.getElementById('taskFilter')?.value.trim(),
            startDate: document.getElementById('start_date')?.value.trim(),
            endDate: document.getElementById('end_date')?.value.trim()
        };
    },

    getFilteredData() {
        const filters = this.getFilters();
        let filteredData = [...taskData];

        if (filters.employee) {
            filteredData = filteredData.filter(item => item.employee === filters.employee);
        }

        if (filters.task) {
            filteredData = filteredData.filter(item => item.task_name === filters.task);
        }

        if (filters.startDate && filters.endDate) {
            const filterStart = new Date(filters.startDate);
            const filterEnd = new Date(filters.endDate);
            filterEnd.setHours(23, 59, 59, 999);

            filteredData = filteredData.filter(item => {
                const taskStart = item.start_date ? new Date(item.start_date) : null;
                const taskEnd = item.end_date ? new Date(item.end_date) : null;
                if (!taskStart || !taskEnd) return false;
                return taskStart <= filterEnd && taskEnd >= filterStart;
            });
        }

        return filteredData;
    },

    filterTasks() {
        const filteredData = this.getFilteredData();
        ChartManager.updateCharts(filteredData);
        ChartManager.updateProgressChart();
        this.updateProgressTable();
    },

    updateProgressTable() {
        const tbody = document.getElementById('progressTableBody');
        if (!tbody) return;

        tbody.innerHTML = '';
        const filters = this.getFilters();
        const grouped = {};

        achievementStatusData.forEach(item => {
            if (filters.employee && item.employee !== filters.employee) return;
            if (filters.startDate && filters.endDate) {
                if (!item.created_at) return;
                const created = new Date(item.created_at.substring(0, 10));
                const start = new Date(filters.startDate);
                const end = new Date(filters.endDate);
                if (created < start || created > end) return;
            }
            
            const key = filters.employee ? 
                `${item.task_name}||${item.employee}` : 
                item.task_name;
                
            if (!grouped[key]) {
                grouped[key] = {
                    user_task_ids: new Set(),
                    achieved: 0,
                    nonAchieved: 0,
                    work_orders: 0,
                    work_orders_completed: 0,
                    task_name: item.task_name,
                    employee: item.employee,
                    employees: new Set()
                };
            }
            
            if (item.user_task_id) grouped[key].user_task_ids.add(item.user_task_id);
            if (item.status === 'achieved') grouped[key].achieved++;
            else grouped[key].nonAchieved++;
            grouped[key].work_orders += parseInt(item.work_orders) || 0;
            grouped[key].work_orders_completed += parseInt(item.work_orders_completed) || 0;
            if (item.employee) grouped[key].employees.add(item.employee);
        });

        Object.values(grouped).forEach(stats => {
            const achievementRate = stats.work_orders > 0 ? Math.round((stats.work_orders_completed / stats.work_orders) * 100) : 0;
            const employeeDisplay = filters.employee ? 
                filters.employee : 
                Array.from(stats.employees).join(', ') || '-';
            
            const rowClass = achievementRate >= 100 ? 'table-success' : 
                           achievementRate >= 80 ? 'table-warning' : 'table-danger';
            
            const row = document.createElement('tr');
            row.className = rowClass;
            row.innerHTML = `
                <td>${stats.task_name}</td>
                <td>${employeeDisplay}</td>
                <td>${stats.user_task_ids.size}</td>
                <td>${stats.achieved}</td>
                <td>${stats.nonAchieved}</td>
                <td>${stats.work_orders_completed}</td>
                <td>${achievementRate}%</td>
            `;
            tbody.appendChild(row);
        });
    }
};

const Sidebar = {
    init() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;
        
        sidebar.classList.add('collapsed');
        
        if (window.innerWidth >= 992) {
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        }
    },

    toggle() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;
        const isCollapsed = sidebar.classList.toggle('collapsed');
        
        if (window.innerWidth >= 992) {
            mainContent.classList.toggle('collapsed', isCollapsed);
            body.classList.toggle('sidebar-collapsed', isCollapsed);
        } else {
            isCollapsed ? this.removeOverlay() : this.createOverlay();
        }
    },

    close() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;

        sidebar.classList.add('collapsed');
        
        if (window.innerWidth >= 992) {
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        }
        
        this.removeOverlay();
    },

    createOverlay() {
        if (document.querySelector('.sidebar-overlay')) return;
        
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 999; opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(overlay);
        setTimeout(() => overlay.style.opacity = '1', 10);
        overlay.addEventListener('click', () => this.close());
    },

    removeOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) return;
        
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    }
};

const ExportManager = {
    showNotification(message, type = 'info') {
        const alertType = type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info';
        const iconType = type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle';
        
        const notification = document.createElement('div');
        notification.className = `alert alert-${alertType} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);';
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${iconType} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    },

    downloadReport() {
        try {
            const filteredData = DataManager.getFilteredData();
            
            if (filteredData.length === 0) {
                this.showNotification('No data available for the selected filters.', 'warning');
                return;
            }

            const reportData = filteredData.map(item => ({
                'Task Type': item.task_name,
                'Employee Name': item.employee,
                'Description': item.description || 'N/A',
                'Target': item.target_str || item.target_int || item.target || 'N/A',
                'Progress': item.progress,
                'Status': item.status === 'achieved' ? 'Achieved' : 'Non-Achieved',
                'Last Update': item.last_update || 'N/A'
            }));

            const worksheet = XLSX.utils.json_to_sheet(reportData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'Performance Report');

            const dateString = new Date().toISOString().split('T')[0];
            const filename = `Performance_Report_${dateString}.xlsx`;

            XLSX.writeFile(workbook, filename);
            this.showNotification('Report downloaded successfully!', 'success');

        } catch (error) {
            console.error('Error downloading report:', error);
            this.showNotification('Error downloading report. Please try again.', 'error');
        }
    },

    exportChart() {
        try {
            const filteredData = DataManager.getFilteredData();
            
            if (filteredData.length === 0) {
                this.showNotification('No data available for the selected filters.', 'warning');
                return;
            }

            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF();

            pdf.setFontSize(20);
            pdf.setTextColor(196, 30, 58);
            pdf.text('Performance Statistics Report', 20, 20);

            pdf.setFontSize(12);
            pdf.setTextColor(0, 0, 0);
            pdf.text(`Generated on: ${new Date().toLocaleDateString('id-ID')}`, 20, 30);

            const taskCanvas = document.getElementById('taskChart');
            const taskImgData = taskCanvas.toDataURL('image/png');
            pdf.addImage(taskImgData, 'PNG', 20, 50, 80, 80);

            const perfCanvas = document.getElementById('performanceChart');
            const perfImgData = perfCanvas.toDataURL('image/png');
            pdf.addImage(perfImgData, 'PNG', 110, 50, 80, 80);

            const dateString = new Date().toISOString().split('T')[0];
            const filename = `Performance_Chart_${dateString}.pdf`;

            pdf.save(filename);
            this.showNotification('Chart exported successfully!', 'success');

        } catch (error) {
            console.error('Error exporting chart:', error);
            this.showNotification('Error exporting chart. Please try again.', 'error');
        }
    }
};

function toggleSidebar() { Sidebar.toggle(); }
function toggleChartType() { ChartManager.toggleChartType(); }
function downloadReport() { ExportManager.downloadReport(); }
function exportChart() { ExportManager.exportChart(); }
function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) modal.hide();
    window.location.href = '../logout.php';
}

document.addEventListener('DOMContentLoaded', function() {
    Sidebar.init();
    ChartManager.initTaskChart();
    ChartManager.initPerformanceChart();
    ChartManager.initProgressChart();
    
    document.querySelector('.sidebar-nav').addEventListener('click', function(e) {
        const link = e.target.closest('.nav-link');
        if (!link) return;
        
        const href = link.getAttribute('href');
        const currentPage = window.location.pathname.split('/').pop();
        
        if (href && href !== currentPage && href !== '#') {
            e.preventDefault();
            Sidebar.close();
            setTimeout(() => window.location.href = href, 300);
        }
    });
    
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const burgerBtn = document.getElementById('burgerBtn');
        
        if (window.innerWidth < 992 && !sidebar.classList.contains('collapsed')) {
            if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
                Sidebar.close();
            }
        }
    });

    let resizeTimeout;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;
            
            if (window.innerWidth >= 992) {
                Sidebar.removeOverlay();
                const isCollapsed = sidebar.classList.contains('collapsed');
                mainContent.classList.toggle('collapsed', isCollapsed);
                body.classList.toggle('sidebar-collapsed', isCollapsed);
            } else {
                mainContent.classList.remove('collapsed');
                body.classList.remove('sidebar-collapsed');
                
                if (!sidebar.classList.contains('collapsed')) {
                    sidebar.classList.add('collapsed');
                    Sidebar.removeOverlay();
                }
            }
            
            ChartManager.handleResize();
        }, 300);
    });
    
    const filterContainer = document.querySelector('.chart-filters');
    filterContainer.addEventListener('change', DataManager.filterTasks.bind(DataManager));
    
    flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        onChange: DataManager.filterTasks.bind(DataManager)
    });

    flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        onChange: DataManager.filterTasks.bind(DataManager)
    });

    document.addEventListener('click', function(e) {
        if (e.target.id === 'clearStartDate') {
            document.getElementById('start_date').value = '';
            document.getElementById('start_date').dispatchEvent(new Event('change'));
        }
        if (e.target.id === 'clearEndDate') {
            document.getElementById('end_date').value = '';
            document.getElementById('end_date').dispatchEvent(new Event('change'));
        }
    });

    DataManager.filterTasks();
});