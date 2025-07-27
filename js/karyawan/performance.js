// Task data will be loaded from PHP
let taskData = [];

// Initialize data from PHP
document.addEventListener('DOMContentLoaded', function() {
    if (window.taskPerformanceData) {
        taskData = window.taskPerformanceData.map(task => {
            // Determine task type based on target_int
            const isNumeric = task.target_int && parseInt(task.target_int) > 0;
            
            return {
                id: task.task_name.toLowerCase().replace(/\s+/g, '_'),
                name: task.task_name,
                type: isNumeric ? 'Numeric Target' : 'Text Based',
                target: isNumeric ? task.target_int : (task.target_str || 'Text-based task'),
                unit: isNumeric ? 'Work Orders' : '',
                achieved: task.last_progress_int || 0,
                status: task.last_status === 'Achieved' ? 'achieve' : 
                        task.last_status === 'Non Achieved' ? 'nonachieve' : 'progress',
                totalTasks: 1,
                completedTasks: task.last_progress_int || 0,
                description: task.task_name,
                start_date: task.start_date,
                end_date: task.end_date,
                created_at: task.created_at,
                achievement_date: task.achievement_date,
                achieved_count: task.achieved_count || 0,
                non_achieved_count: task.non_achieved_count || 0
            };
        });
    }
    
    // Initialize everything after data is loaded
    initializePerformance();
});
function initializePerformance() {
    // Initialize charts and displays
    renderTaskCards();
    initializeCharts();
}

// Tambahkan variabel global untuk menyimpan referensi chart
let taskStatsChart = null;

// Perbaiki fungsi toggleSidebar
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

    // Tambahkan delay untuk resize chart setelah transisi selesai
    setTimeout(() => {
        resizeCharts();
    }, 400); // 400ms sesuai dengan durasi transisi CSS
}

function renderTaskCards() {
    renderStatsGrid('all');
}

function initializeCharts() {
    initTaskStatsChart();
}

// Perbaiki fungsi closeSidebar
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');

    // Tambahkan delay untuk resize chart setelah transisi selesai
    setTimeout(() => {
        resizeCharts();
    }, 400);
}

// Fungsi untuk resize semua chart
function resizeCharts() {
    if (taskStatsChart) {
        taskStatsChart.resize();
    }
}

// Inisialisasi chart statistik task berdasarkan nama
function initTaskStatsChart() {
    const ctx = document.getElementById('taskStatsChart').getContext('2d');
    
    // Destroy chart yang ada jika ada
    if (taskStatsChart) {
        taskStatsChart.destroy();
    }
    
    // Group tasks by name and sum work_orders & work_orders_completed from task_achievements
    const taskGroups = {};
    if (window.taskPerformanceData) {
        window.taskPerformanceData.forEach(task => {
            // Pastikan task_achievements sudah di-embed di setiap task (array)
            if (Array.isArray(task.achievements)) {
                const taskName = task.task_name;
                if (!taskGroups[taskName]) {
                    taskGroups[taskName] = {
                        name: taskName,
                        totalWorkOrders: 0,
                        totalCompleted: 0
                    };
                }
                task.achievements.forEach(ach => {
                    taskGroups[taskName].totalWorkOrders += (parseInt(ach.work_orders) || 0);
                    taskGroups[taskName].totalCompleted += (parseInt(ach.work_orders_completed) || 0);
                });
            }
        });
    }

    const groupedData = Object.values(taskGroups);
    const labels = groupedData.map(group => group.name);
    const workOrdersData = groupedData.map(group => group.totalWorkOrders);
    const completedData = groupedData.map(group => group.totalCompleted);

    taskStatsChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Total Work Orders',
                    data: workOrdersData,
                    backgroundColor: 'rgba(44, 90, 160, 0.6)',
                    borderColor: 'rgba(44, 90, 160, 1)',
                    borderWidth: 1,
                    barThickness: 50
                },
                {
                    label: 'Work Orders Completed',
                    data: completedData,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: 'rgba(40, 167, 69, 1)',
                    borderWidth: 1,
                    barThickness: 50
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        afterLabel: function(context) {
                            if (context.datasetIndex === 1) { // Completed dataset
                                const total = context.chart.data.datasets[0].data[context.dataIndex];
                                const completed = context.raw;
                                const percentage = total > 0 ? ((completed / total) * 100).toFixed(1) : 0;
                                return `Completion Rate: ${percentage}%`;
                            }
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: "#e0e0e0"
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: "#e0e0e0"
                    }
                }
            }
        }
    });
}

// Remove old window.onload and use DOMContentLoaded instead

// Tambahkan event listener untuk window resize
window.addEventListener('resize', function() {
    // Delay untuk memastikan transisi selesai
    setTimeout(() => {
        resizeCharts();
    }, 100);
    
    // Handle mobile/desktop switch
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

        // Function to close sidebar
        function closeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;

            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        }

        // Function to navigate with sidebar close
        function navigateWithCloseSidebar(url, event) {
            event.preventDefault();
            
            const currentPage = window.location.pathname.split('/').pop();
            
            // Jika bukan halaman yang sama
            if (url !== currentPage) {
                // Tutup sidebar langsung
                closeSidebar();
                
                // Navigasi langsung tanpa delay
                window.location.href = url;
            }
        }

        // Close sidebar when clicking outside of it (mobile)
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

        // Close sidebar on window resize if switching to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                closeSidebar();
            }
        });

        // Initialize sidebar as closed on page load
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;
            
            sidebar.classList.add('collapsed');
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        });

        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modal = document.getElementById('logoutModal');
                if (modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            }
        });

        function filterByTask() {
            const taskFilter = document.getElementById('taskFilter').value;
            renderStatsGrid(taskFilter);
        }

        function renderStatsGrid(taskFilter = 'all') {
            const grid = document.getElementById('statsGrid');
            grid.innerHTML = '';

            // Group tasks by name and aggregate total_completed & progress_int
            const taskGroups = {};
            if (window.taskPerformanceData) {
                window.taskPerformanceData.forEach(task => {
                    const taskName = task.task_name;
                    if (!taskGroups[taskName]) {
                        taskGroups[taskName] = {
                            name: taskName,
                            totalTasks: 0,
                            totalCompleted: 0,
                            totalProgress: 0,
                            activeTasks: 0
                        };
                    }
                    taskGroups[taskName].totalTasks++;
                    // Ambil total_completed dan progress_int dari user_tasks
                    // Perlu pastikan field ini tersedia di PHP
                    taskGroups[taskName].totalCompleted += (parseInt(task.total_completed) || 0);
                    taskGroups[taskName].totalProgress += (parseInt(task.progress_int) || 0);
                    // Status aktif
                    if (!task.last_status || task.last_status === 'In Progress') {
                        taskGroups[taskName].activeTasks++;
                    }
                });
            }

            // Filter based on selected task
            const filteredGroups = Object.values(taskGroups).filter(taskGroup => {
                return taskFilter === 'all' || taskGroup.name === taskFilter;
            });

            filteredGroups.forEach(taskGroup => {
                const card = document.createElement('div');
                card.className = 'stat-card';

                // Hitung rata-rata progress_int
                const avgProgress = taskGroup.totalTasks > 0 ? Math.round(taskGroup.totalProgress / taskGroup.totalTasks) : 0;

                card.innerHTML = `
                    <div class="stat-card-header">
                        <div class="stat-card-title">${taskGroup.name}</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="metric">
                            <div class="metric-value">${taskGroup.totalCompleted}</div>
                            <div class="metric-label">Completed</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${taskGroup.totalTasks}</div>
                            <div class="metric-label">Total Tasks</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${taskGroup.activeTasks}</div>
                            <div class="metric-label">Active Tasks</div>
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-label">
                            <span>Progress</span>
                            <span>${avgProgress}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${avgProgress}%;"></div>
                        </div>
                    </div>
                `;

                grid.appendChild(card);
            });
        }

        function downloadStatistics() {
    const content = document.getElementById('reportContent');
    if (!content) {
        alert('Elemen #reportContent tidak ditemukan!');
        return;
    }

    const taskFilter = document.getElementById('taskFilter')?.value || 'all';
    content.innerHTML = '';

    const title = `<h2 style="text-align:center; margin-bottom:10px;">Task Performance Report</h2>`;
    const date = `<p style="text-align:center; margin-bottom:20px;">Generated on: ${new Date().toLocaleDateString()}</p>`;

    const taskGroups = {};
    if (window.taskPerformanceData) {
        window.taskPerformanceData
            .filter(task => taskFilter === 'all' || task.task_name === taskFilter)
            .forEach(task => {
                const taskName = task.task_name;
                if (!taskGroups[taskName]) {
                    taskGroups[taskName] = {
                        name: taskName,
                        totalTasks: 0,
                        achievedTasks: 0,
                        activeTasks: 0,
                        nonAchievedTasks: 0
                    };
                }

                taskGroups[taskName].totalTasks++;
                if (task.last_status === 'Achieved') {
                    taskGroups[taskName].achievedTasks++;
                } else if (task.last_status === 'Non Achieved') {
                    taskGroups[taskName].nonAchievedTasks++;
                } else {
                    taskGroups[taskName].activeTasks++;
                }
            });
    }

    const groupedData = Object.values(taskGroups);

    const summaryHTML = `
        <table border="1" cellspacing="0" cellpadding="8" style="width:100%; font-size:12px; border-collapse: collapse; margin-bottom:20px; background:white;">
            <thead style="background:#f8f9fa;">
                <tr>
                    <th style="border:1px solid #ddd; padding:8px;">Task Name</th>
                    <th style="border:1px solid #ddd; padding:8px;">Total</th>
                    <th style="border:1px solid #ddd; padding:8px;">Achieved</th>
                    <th style="border:1px solid #ddd; padding:8px;">Non Achieved</th>
                    <th style="border:1px solid #ddd; padding:8px;">Active Tasks</th>
                    <th style="border:1px solid #ddd; padding:8px;">Percentage</th>
                </tr>
            </thead>
            <tbody>
                ${groupedData.map(taskGroup => {
                    const completionRate = taskGroup.totalTasks > 0
                        ? Math.round((taskGroup.achievedTasks / taskGroup.totalTasks) * 100)
                        : 0;
                    return `
                    <tr>
                        <td style="border:1px solid #ddd; padding:8px;">${taskGroup.name}</td>
                        <td style="border:1px solid #ddd; padding:8px; text-align:center;">${taskGroup.totalTasks}</td>
                        <td style="border:1px solid #ddd; padding:8px; text-align:center;">${taskGroup.achievedTasks}</td>
                        <td style="border:1px solid #ddd; padding:8px; text-align:center;">${taskGroup.nonAchievedTasks}</td>
                        <td style="border:1px solid #ddd; padding:8px; text-align:center;">${taskGroup.activeTasks}</td>
                        <td style="border:1px solid #ddd; padding:8px; text-align:center;">${completionRate}%</td>
                    </tr>`;
                }).join('')}
            </tbody>
        </table>
    `;

    const taskStatsCanvas = document.getElementById('taskStatsChart');
    const taskStatsImg = taskStatsCanvas ? taskStatsCanvas.toDataURL("image/png") : '';

    const chartsHTML = `
        <div class="page-break">
            <h3 style="text-align:center; margin-bottom:15px;">Task Statistics Chart</h3>
            <img src="${taskStatsImg}" style="width:100%; max-width:700px; margin-bottom:30px; display:block; margin-left:auto; margin-right:auto;">
        </div>
    `;

    content.innerHTML = title + date + summaryHTML + chartsHTML;
    content.style.display = 'block';

    html2pdf().set({
        margin: 0.5,
        filename: 'task-statistics.pdf',
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 3, useCORS: true, scrollY: 0, scrollX: 0 },
        jsPDF: { unit: 'in', format: 'a4', orientation: 'portrait' }
    }).from(content).save().then(() => {
        content.style.display = 'none';
    }).catch(err => {
        console.error('Gagal mengunduh PDF:', err);
        alert('Terjadi kesalahan saat mengunduh PDF.');
    });
}

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