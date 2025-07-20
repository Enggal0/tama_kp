// Task data will be loaded from PHP
let taskData = [];

// Initialize data from PHP
document.addEventListener('DOMContentLoaded', function() {
    if (window.taskPerformanceData) {
        taskData = window.taskPerformanceData.map(task => {
            return {
                id: task.task_name.toLowerCase().replace(/\s+/g, '_'),
                name: task.task_name,
                type: task.task_type === 'numeric' ? 'Numeric Target' : 'Text Based',
                target: task.task_type === 'numeric' ? task.target_int : task.target_str,
                unit: task.task_type === 'numeric' ? 'Units' : '',
                achieved: task.last_progress_int || task.progress_int || 0,
                status: task.status === 'Achieved' ? 'achieve' : 
                        task.status === 'Non Achieved' ? 'nonachieve' : 'progress',
                totalTasks: 1,
                completedTasks: task.last_progress_int || task.progress_int || 0,
                description: task.task_name,
                deadline: task.deadline,
                created_at: task.created_at,
                achievement_date: task.achievement_date
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
let performanceChart = null;
let targetVsCompletedChart = null;

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
    initPerformanceChart();
    renderTargetVsCompletedChart();
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
    if (performanceChart) {
        performanceChart.resize();
    }
    if (targetVsCompletedChart) {
        targetVsCompletedChart.resize();
    }
}

// Perbaiki inisialisasi chart performance
function initPerformanceChart() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Destroy chart yang ada jika ada
    if (performanceChart) {
        performanceChart.destroy();
    }
    
    // Group tasks by name and calculate statistics (same logic as renderStatsGrid)
    const taskGroups = {};
    
    if (window.taskPerformanceData) {
        window.taskPerformanceData.forEach(task => {
            const taskName = task.task_name;
            
            if (!taskGroups[taskName]) {
                taskGroups[taskName] = {
                    name: taskName,
                    totalTasks: 0,
                    achievedTasks: 0,
                    inProgressTasks: 0,
                    nonAchievedTasks: 0
                };
            }
            
            taskGroups[taskName].totalTasks++;
            
            if (task.status === 'Achieved') {
                taskGroups[taskName].achievedTasks++;
            } else if (task.status === 'In Progress') {
                taskGroups[taskName].inProgressTasks++;
            } else if (task.status === 'Non Achieved') {
                taskGroups[taskName].nonAchievedTasks++;
            }
        });
    }
    
    const groupedData = Object.values(taskGroups);
    const labels = groupedData.map(group => group.name);
    const achievedData = groupedData.map(group => group.achievedTasks);
    const totalData = groupedData.map(group => group.totalTasks);
    
    performanceChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Achieved Tasks',
                data: achievedData,
                backgroundColor: '#2c5aa0'
            }, {
                label: 'Total Tasks',
                data: totalData,
                backgroundColor: '#e74c3c'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Perbaiki fungsi renderTargetVsCompletedChart
function renderTargetVsCompletedChart() {
    const ctx = document.getElementById('targetVsCompletedChart').getContext('2d');

    // Destroy chart yang ada jika ada
    if (targetVsCompletedChart) {
        targetVsCompletedChart.destroy();
    }

    // Use data from database with proper statistics
    const labels = ['Achieved', 'Non Achieved', 'In Progress'];
    const data = [
        window.statsData ? window.statsData.achieved_tasks : 0,
        window.statsData ? window.statsData.non_achieved_tasks : 0,
        window.statsData ? window.statsData.in_progress_tasks : 0
    ];

    targetVsCompletedChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: [
                    'rgba(46, 204, 113, 0.8)', // Green for Achieved
                    'rgba(231, 76, 60, 0.8)',  // Red for Non Achieved
                    'rgba(241, 196, 15, 0.8)'  // Yellow for In Progress
                ],
                borderColor: [
                    'rgba(46, 204, 113, 1)',
                    'rgba(231, 76, 60, 1)',
                    'rgba(241, 196, 15, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return context.label + ': ' + context.raw + ' (' + percentage + '%)';
                        }
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

            // Group tasks by name and calculate statistics
            const taskGroups = {};
            
            if (window.taskPerformanceData) {
                window.taskPerformanceData.forEach(task => {
                    const taskName = task.task_name;
                    
                    if (!taskGroups[taskName]) {
                        taskGroups[taskName] = {
                            name: taskName,
                            totalTasks: 0,
                            achievedTasks: 0,
                            inProgressTasks: 0,
                            nonAchievedTasks: 0
                        };
                    }
                    
                    taskGroups[taskName].totalTasks++;
                    
                    if (task.status === 'Achieved') {
                        taskGroups[taskName].achievedTasks++;
                    } else if (task.status === 'In Progress') {
                        taskGroups[taskName].inProgressTasks++;
                    } else if (task.status === 'Non Achieved') {
                        taskGroups[taskName].nonAchievedTasks++;
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

                const completedTasks = taskGroup.achievedTasks + taskGroup.nonAchievedTasks;
                const completionRate = taskGroup.totalTasks > 0 ? Math.round((taskGroup.achievedTasks / taskGroup.totalTasks) * 100) : 0;

                card.innerHTML = `
                    <div class="stat-card-header">
                        <div class="stat-card-title">${taskGroup.name}</div>
                    </div>
                    <div class="stat-metrics">
                        <div class="metric">
                            <div class="metric-value">${taskGroup.achievedTasks}</div>
                            <div class="metric-label">Task Achieved</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${taskGroup.totalTasks}</div>
                            <div class="metric-label">Total Tasks</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${taskGroup.inProgressTasks}</div>
                            <div class="metric-label">In Progress</div>
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-label">
                            <span>Achievement Rate</span>
                            <span>${completionRate}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${completionRate}%;"></div>
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

    content.innerHTML = ''; // Kosongkan konten sebelumnya

    const title = `<h2 style="text-align:center; margin-bottom:10px;">Task Performance Report</h2>`;
    const date = `<p style="text-align:center; margin-bottom:20px;">Generated on: ${new Date().toLocaleDateString()}</p>`;

    const summaryHTML = `
        <table border="1" cellspacing="0" cellpadding="6" style="width:100%; font-size:12px; border-collapse: collapse; margin-bottom:20px;">
            <thead style="background:#f0f0f0;">
                <tr>
                    <th>Task Name</th>
                    <th>Target</th>
                    <th>Achieved</th>
                    <th>Total</th>
                    <th>Completed</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                ${taskData.map(task => `
                    <tr>
                        <td>${task.name}</td>
                        <td>${task.target}</td>
                        <td>${task.achieved}</td>
                        <td>${task.totalTasks}</td>
                        <td>${task.completedTasks}</td>
                        <td>${task.status.toUpperCase()}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
    `;

    // Tangkap grafik sebagai gambar
    const performanceCanvas = document.getElementById('performanceChart');
    const achievementCanvas = document.getElementById('targetVsCompletedChart');

    const performanceImg = performanceCanvas ? performanceCanvas.toDataURL("image/png") : '';
    const achievementImg = achievementCanvas ? achievementCanvas.toDataURL("image/png") : '';

    const chartsHTML = `
  <div class="page-break">
    <h3>Performance Chart</h3>
    <img src="${performanceImg}" style="width:100%; max-width:700px; margin-bottom:30px;">
  </div>
  <div>
    <h3>Achievement Chart</h3>
    <img src="${achievementImg}" style="width:100%; max-width:700px;">
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



        window.onload = () => {
            renderStatsGrid('all', 'all');
            renderTargetVsCompletedChart();
        };

        // Tambahkan setelah window.onload
const ctx = document.getElementById('performanceChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: taskData.map(task => task.name),
        datasets: [{
            label: 'Achieved',
            data: taskData.map(task => task.achieved),
            backgroundColor: '#2c5aa0'
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

function renderTargetVsCompletedChart() {
    const ctx = document.getElementById('targetVsCompletedChart').getContext('2d');

    const labels = taskData.map(task => task.name);
    const targets = taskData.map(task => {
    const t = parseInt(task.target);
    return isNaN(t) ? 0 : t;
});

    const completed = taskData.map(task => task.completedTasks);

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Target',
                    data: targets,
                    backgroundColor: 'rgba(44, 90, 160, 0.6)',
                    borderColor: 'rgba(44, 90, 160, 1)',
                    borderWidth: 1
                },
                {
                    label: 'Completed',
                    data: completed,
                    backgroundColor: 'rgba(196, 30, 58, 0.6)',
                    borderColor: 'rgba(196, 30, 58, 1)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            },
            plugins: {
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top'
                }
            }
        }
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