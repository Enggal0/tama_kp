function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    
    if (window.innerWidth >= 992) {
        
        mainContent.classList.toggle('collapsed', isCollapsed);
        body.classList.toggle('sidebar-collapsed', isCollapsed);
    } else {
        
        if (!isCollapsed) {
            
            if (!document.querySelector('.sidebar-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'sidebar-overlay';
                overlay.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.5);
                    z-index: 999;
                    opacity: 0;
                    transition: opacity 0.3s ease;
                `;
                document.body.appendChild(overlay);
                
                setTimeout(() => overlay.style.opacity = '1', 10);
                overlay.addEventListener('click', closeSidebar);
            }
        } else {
            
            removeOverlay();
        }
    }
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    
    if (window.innerWidth >= 992) {
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
    
    removeOverlay();
}

function removeOverlay() {
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.style.opacity = '0';
        setTimeout(() => {
            if (overlay.parentNode) {
                overlay.parentNode.removeChild(overlay);
            }
        }, 300);
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
        
        if (window.innerWidth < 992 && !sidebar.classList.contains('collapsed')) {
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
        
        if (window.innerWidth >= 992) {
            
            removeOverlay();
            const isCollapsed = sidebar.classList.contains('collapsed');
            mainContent.classList.toggle('collapsed', isCollapsed);
            body.classList.toggle('sidebar-collapsed', isCollapsed);
        } else {
            
            mainContent.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
            
            
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                removeOverlay();
            }
        }
    });
}

function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    sidebar.classList.add('collapsed');
    
    if (window.innerWidth >= 992) {
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    } else {
        mainContent.classList.remove('collapsed');
        body.classList.remove('sidebar-collapsed');
    }
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) modal.hide();
    window.location.href = '../logout.php';
}

function filterTable() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const taskTypeFilter = document.getElementById('typeFilter');
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const tableRows = document.querySelectorAll('#taskTable tbody tr');

    const searchValue = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const taskTypeValue = taskTypeFilter.value.toLowerCase();
    const startDateValue = startDateInput.value;
    const endDateValue = endDateInput.value;

    tableRows.forEach(row => {
        const taskType = row.cells[0]?.textContent.toLowerCase() || '';
        const employee = row.cells[1]?.textContent.toLowerCase() || '';
        const dateText = row.cells[4]?.textContent.trim();
        const statusCell = row.cells[6]?.querySelector('.badge')?.textContent.toLowerCase() || '';

        const matchesSearch = taskType.includes(searchValue) || employee.includes(searchValue);
        const matchesStatus = statusValue === '' || statusCell === statusValue;
        const matchesTaskType = taskTypeValue === '' || taskType === taskTypeValue;

        let matchesDate = true;
        if (dateText && (startDateValue || endDateValue)) {
            const rowDate = new Date(dateText);
            if (!isNaN(rowDate.getTime())) {
                const rowDateStr = rowDate.toISOString().slice(0, 10);
                if (startDateValue && rowDateStr < startDateValue) matchesDate = false;
                if (endDateValue && rowDateStr > endDateValue) matchesDate = false;
            }
        }
        if (matchesSearch && matchesStatus && matchesTaskType && matchesDate) {
            row.classList.remove('hidden-row');
        } else {
            row.classList.add('hidden-row');
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();

    const filterElements = [
        'searchInput', 'statusFilter', 'typeFilter', 
        'start_date', 'end_date'
    ];
    
    filterElements.forEach(id => {
        const element = document.getElementById(id);
        if (element) {
            const eventType = element.type === 'text' ? 'input' : 'change';
            element.addEventListener(eventType, filterTable);
        }
    });

    flatpickr("#start_date", {
        dateFormat: "Y-m-d",
        onChange: filterTable
    });
    
    flatpickr("#end_date", {
        dateFormat: "Y-m-d",
        onChange: filterTable
    });

    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
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
    if (row.classList.contains('hidden-row')) return; 
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