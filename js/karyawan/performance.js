// Task data with targets and achievements
        const taskData = [
            {
                id: 'pelurusan_kpi',
                name: 'Pelurusan KPI',
                type: 'Daily Target',
                target: 50,
                unit: 'WO/Hari',
                achieved: 57,
                status: 'achieve',
                totalTasks: 3,
                completedTasks: 57,
                description: 'KPI alignment and verification'
            },
            {
                id: 'fallout_cons',
                name: 'Fallout CONS/EBIS',
                type: 'Solution Based',
                target: 'All FO solved',
                unit: 'FO',
                achieved: 51,
                status: 'achieve',
                totalTasks: 2,
                completedTasks: 51,
                description: 'CONS/EBIS fallout resolution'
            },
            {
                id: 'up_odp',
                name: 'UP ODP',
                type: 'Solution Based',
                target: 'All FO solved',
                unit: 'FO',
                achieved: 45,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 51,
                description: 'ODP upgrade and maintenance'
            },
            {
                id: 'cek_port',
                name: 'Cek Port BT',
                type: 'Solution Based',
                target: 'All FO solved',
                unit: 'FO',
                achieved: 38,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 38,
                description: 'BT port checking and validation'
            },
            {
                id: 'val_tiang',
                name: 'Val Tiang',
                type: 'Validation',
                target: 'No fixed target',
                unit: 'Validations',
                achieved: 32,
                status: 'progress',
                totalTasks: 4,
                completedTasks: 32,
                description: 'Pole validation and rack verification'
            },
            {
                id: 'odp_kendala',
                name: 'ODP Kendala',
                type: 'Problem Solving',
                target: 'No fixed target',
                unit: 'Issues',
                achieved: 28,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 28,
                description: 'ODP problem resolution'
            },
            {
                id: 'validasi_ftm',
                name: 'Validasi FTM',
                type: 'Equipment Check',
                target: '1 RACK EA, 1 RACK OA',
                unit: 'Racks',
                achieved: 27,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 27,
                description: 'FTM equipment validation'
            },
                        {
                id: 'pelurusan_gdoc',
                name: 'Pelurusan GDOC HS',
                type: 'Documentation',
                target: '40 FO/Day',
                unit: 'Docs',
                achieved: 5,
                status: 'progress',
                totalTasks: 2,
                completedTasks: 1,
                description: 'GDOC HS document validation'
            },
            {
                id: 'fallout_uim',
                name: 'Fallout UIM DAMAN',
                type: 'Solution Based',
                target: 'All Issues Resolved',
                unit: 'FO',
                achieved: 27,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 27,
                description: 'Resolving fallout on UIM DAMAN'
            },
            {
                id: 'pelurusan_ebis',
                name: 'Pelurusan EBIS',
                type: 'Documentation',
                target: 'Completed EBIS Update',
                unit: 'Docs',
                achieved: 39,
                status: 'achieve',
                totalTasks: 1,
                completedTasks: 39,
                description: 'EBIS data alignment and update'
            },
            {
                id: 'pelurusan_aso',
                name: 'Pelurusan GDOC ASO',
                type: 'Documentation',
                target: 'Completed ASO Update',
                unit: 'Docs',
                achieved: 20,
                status: 'nonachieve',
                totalTasks: 1,
                completedTasks: 20,
                description: 'ASO documentation alignment'
            },
            {
                id: 'e2e',
                name: 'E2E',
                type: 'End-to-End Process',
                target: 'Monitor All Processes',
                unit: 'Process',
                achieved: 0,
                status: 'progress',
                totalTasks: 2,
                completedTasks: 0,
                description: 'End-to-End monitoring and verification'
            }
        ];

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
    
    performanceChart = new Chart(ctx, {
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
            maintainAspectRatio: false, // Tambahkan ini
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
}

// Perbaiki fungsi renderTargetVsCompletedChart
function renderTargetVsCompletedChart() {
    const ctx = document.getElementById('targetVsCompletedChart').getContext('2d');

    // Destroy chart yang ada jika ada
    if (targetVsCompletedChart) {
        targetVsCompletedChart.destroy();
    }

    const labels = taskData.map(task => task.name);
    const targets = taskData.map(task => {
        const t = parseInt(task.target);
        return isNaN(t) ? 0 : t;
    });

    const completed = taskData.map(task => task.completedTasks);

    targetVsCompletedChart = new Chart(ctx, {
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
            maintainAspectRatio: false, // Tambahkan ini
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

// Perbaiki window.onload
window.onload = () => {
    renderStatsGrid('all', 'all');
    initPerformanceChart(); // Ganti dari kode inline
    renderTargetVsCompletedChart();
};

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
            const statusFilter = document.getElementById('statusFilter').value;
            renderStatsGrid(taskFilter, statusFilter);
        }

        function filterByStatus() {
            const taskFilter = document.getElementById('taskFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            renderStatsGrid(taskFilter, statusFilter);
        }

        function renderStatsGrid(taskFilter, statusFilter) {
            const grid = document.getElementById('statsGrid');
            grid.innerHTML = '';

            const filtered = taskData.filter(task => {
                const taskMatch = taskFilter === 'all' || task.id === taskFilter;
                const statusMatch = statusFilter === 'all' || task.status === statusFilter;
                return taskMatch && statusMatch;
            });

            filtered.forEach(task => {
                const card = document.createElement('div');
                card.className = 'stat-card';

                const statusBadge = {
                    'achieve': 'badge-achieve',
                    'nonachieve': 'badge-nonachieve',
                    'progress': 'badge-progress'
                }[task.status];

                card.innerHTML = `
                    <div class="stat-card-header">
                        <div class="stat-card-title">${task.name}</div>
                        <span class="stat-badge ${statusBadge}">${task.status.toUpperCase()}</span>
                    </div>
                    <div class="stat-metrics">
                        <div class="metric">
                            <div class="metric-value">${task.achieved}</div>
                            <div class="metric-label">Achieved</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${task.totalTasks}</div>
                            <div class="metric-label">Total Tasks</div>
                        </div>
                        <div class="metric">
                            <div class="metric-value">${task.completedTasks}</div>
                            <div class="metric-label">Completed</div>
                        </div>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-label">
                            <span>Progress</span>
                            <span>${Math.round((task.completedTasks / task.totalTasks) * 100)}%</span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${Math.round((task.completedTasks / task.totalTasks) * 100)}%;"></div>
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

        function confirmLogout() {
            // Redirect logic here
            window.location.href = '../logout.php';
            hideLogoutModal();
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });