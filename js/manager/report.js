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