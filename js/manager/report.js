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
});

        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        function confirmLogout() {
            // Simulasi logout
            alert('Logout confirmed! Redirecting to login page...');
            // Redirect logic here
            // window.location.href = '../login.html';
            hideLogoutModal();
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

        function generatePDF() {
            const element = document.querySelector('#reports');
            html2pdf()
                .set({
                    margin: 0.5,
                    filename: 'employee-report.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
                })
                .from(element)
                .save();
        }
        function exportExcel() {
            const table = document.querySelector('.table'); // atau pakai id
            const workbook = XLSX.utils.table_to_book(table, { sheet: "Employee Report" });
            XLSX.writeFile(workbook, 'employee-report.xlsx');
        }

        function printReport() {
            window.print();
        }

        document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchInput');
        const statusFilter = document.getElementById('statusFilter');
        const taskTypeFilter = document.getElementById('typeFilter'); // Ambil filter kedua (jenis tugas)
        const tableRows = document.querySelectorAll('tbody tr'); // Pastikan tbody ada!

        function filterTable() {
            const searchValue = searchInput.value.toLowerCase();
            const statusValue = statusFilter.value.toLowerCase();
            const taskTypeValue = taskTypeFilter.value.toLowerCase();

            tableRows.forEach(row => {
                const rowText = row.innerText.toLowerCase();
                const rowTaskType = row.querySelector('td:nth-child(1)')?.textContent.trim().toLowerCase();
                const statusEl = row.querySelector('td:nth-child(6) .badge');
                const rowStatus = statusEl ? statusEl.textContent.trim().toLowerCase() : '';

                const matchesSearch = rowText.includes(searchValue);
                const matchesStatus = statusValue === '' || rowStatus === statusValue;
                const matchesTaskType = taskTypeValue === '' || rowTaskType === taskTypeValue;
                
                if (matchesSearch && matchesStatus && matchesTaskType) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        searchInput.addEventListener('input', filterTable);
        statusFilter.addEventListener('change', filterTable);
        taskTypeFilter.addEventListener('change', filterTable);
    });

    function generatePDF() {
    const today = new Date();
    const dateStr = today.toLocaleDateString('en-GB', { year: 'numeric', month: 'long', day: 'numeric' });
    let html = '';
    html += '<div id="pdf-report-header">';
    html += '<h2>Employee Task Report</h2>';
    html += '<div class="pdf-date">Printed: ' + dateStr + '</div>';
    html += '</div>';
    html += '<table id="pdf-report-table">';
    const table = document.getElementById('taskTable');
    const ths = table.querySelectorAll('thead th');
    let colIndexes = [];
    html += '<thead><tr>';
    for (let i = 0; i < ths.length; i++) {
        if (ths[i].innerText.trim().toLowerCase() === 'action') continue;
        colIndexes.push(i);
        html += '<th>' + ths[i].innerText + '</th>';
    }
    html += '</tr></thead><tbody>';
    const rows = table.querySelectorAll('tbody tr');
    if (rows.length === 0) {
        html += '<tr><td colspan="' + colIndexes.length + '" style="text-align:center">No data found.</td></tr>';
    } else {
        rows.forEach(function(row) {
    if (row.classList.contains('hidden-row')) return;
    
    const tds = row.querySelectorAll('td');
            if (tds.length === 1 && tds[0].innerText.trim().toLowerCase().includes('no data')) {
                html += '<tr><td colspan="' + colIndexes.length + '" style="text-align:center">' + tds[0].innerText + '</td></tr>';
                return;
            }
            html += '<tr>';
            colIndexes.forEach(function(idx) {
                let cell = tds[idx];
                if (ths[idx].innerText.trim().toLowerCase() === 'status') {
                    let statusText = cell.querySelector('.badge') ? cell.querySelector('.badge').innerText : cell.innerText;
                    html += '<td>' + statusText + '</td>';
                } else {
                    html += '<td>' + cell.innerText + '</td>';
                }
            });
            html += '</tr>';
        });
    }
    html += '</tbody></table>';
    html2pdf().set({
        margin: [7, 5, 7, 5],
        filename: 'Employee_Task_Report_' + today.toISOString().slice(0,10) + '.pdf',
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
    }).from(html).save();
}

function exportExcel() {
    const table = document.getElementById('taskTable');
    const ths = table.querySelectorAll('thead th');
    const rows = table.querySelectorAll('tbody tr');
    let wb = XLSX.utils.book_new();
    let ws_data = [];
    let header = [];
    for (let i = 0; i < ths.length; i++) {
        if (ths[i].innerText.trim().toLowerCase() === 'action') continue;
        header.push(ths[i].innerText);
    }
    ws_data.push(header);
    rows.forEach(function(row) {
    if (row.classList.contains('hidden-row')) return; // ⬅️ Lewati baris tersembunyi

    const tds = row.querySelectorAll('td');
        if (tds.length < ths.length - 1) return;
        let rowData = [];
        for (let i = 0; i < tds.length; i++) {
            if (ths[i] && ths[i].innerText.trim().toLowerCase() === 'action') continue;
            rowData.push(tds[i].innerText);
        }
        ws_data.push(rowData);
    });
    let ws = XLSX.utils.aoa_to_sheet(ws_data);
    XLSX.utils.book_append_sheet(wb, ws, 'Report');
    XLSX.writeFile(wb, 'Employee_Task_Report_' + (new Date()).toISOString().slice(0,10) + '.xlsx');
}