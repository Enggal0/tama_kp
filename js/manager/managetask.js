let pagination;
let allRows = [];
let filteredRows = [];
let rowsPerPage = 5;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - initializing managetask...');
    
    const tableBody = document.querySelector('#taskTable tbody');
    if (tableBody) {
        allRows = Array.from(tableBody.getElementsByTagName('tr'));
        filteredRows = [...allRows];
        console.log('Total rows found:', allRows.length);
    }

    initializePagination();
    initializeEventListeners();
    renderTable();
    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
});

function initializeEventListeners() {
    console.log('Initializing event listeners...');
    
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            console.log('Search input changed:', this.value);
            filterTable();
        });
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            console.log('Status filter changed:', this.value);
            filterTable();
        });
    }

    const taskNameFilter = document.getElementById('taskNameFilter');
    if (taskNameFilter) {
        taskNameFilter.addEventListener('change', function() {
            console.log('Task name filter changed:', this.value);
            filterTable();
        });
    }

    const rowsPerPageSelect = document.getElementById('rowsPerPageSelect');
    if (rowsPerPageSelect) {
        rowsPerPageSelect.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            updatePagination();
            renderTable();
        });
    }
}

// Mobile sidebar toggle - FIXED VERSION
function toggleSidebar() {
    console.log('toggleSidebar called');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    if (!sidebar || !mainContent) {
        console.error('Sidebar or main content not found');
        return;
    }

    const isCollapsed = sidebar.classList.contains('collapsed');
    console.log('Current state - collapsed:', isCollapsed);

    if (isCollapsed) {
        // Show sidebar
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
        body.classList.remove('sidebar-collapsed');
        console.log('Showing sidebar');
    } else {
        // Hide sidebar
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
        console.log('Hiding sidebar');
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
    closeSidebar();
    
    setTimeout(() => {
        window.location.href = url;
    }, 300);
}

// Add event listeners to all navigation links
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

// Close sidebar when clicking outside of it (mobile)
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

// Close sidebar on window resize if switching to desktop
function setupWindowResize() {
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && !document.getElementById('sidebar').classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}

// Initialize sidebar as closed on page load
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) {
        modal.hide();
    }
    
    window.location.href = '../logout.php';
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value;
    const taskNameFilter = document.getElementById('taskNameFilter').value;
    
    console.log('Filtering with:', { searchTerm, statusFilter, taskNameFilter });
    
    filteredRows = allRows.filter(row => {
        if (row.cells.length < 5) return false; // Skip rows that don't have enough cells
        
        const taskName = row.cells[0].textContent.toLowerCase();
        const employeeName = row.cells[1].textContent.toLowerCase();
        const description = row.cells[2].textContent.toLowerCase();
        const status = row.cells[5].textContent.trim();
        
        let showRow = true;

        // Search filter
        if (searchTerm && 
            !taskName.includes(searchTerm) && 
            !employeeName.includes(searchTerm) && 
            !description.includes(searchTerm)) {
            showRow = false;
        }

        // Status filter
        if (statusFilter && status !== statusFilter) {
            showRow = false;
        }

        // Task name filter
        if (taskNameFilter && !taskName.includes(taskNameFilter.toLowerCase())) {
            showRow = false;
        }

        return showRow;
    });
    
    console.log('Filtered rows:', filteredRows.length);
    
    // Reset to first page after filtering
    currentPage = 1;
    updatePagination();
    renderTable();
}

function renderTable() {
    console.log('Rendering table - Page:', currentPage, 'Rows per page:', rowsPerPage);
    
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    
    // Hide all rows first
    allRows.forEach(row => {
        row.style.display = 'none';
    });
    
    // Show filtered rows for current page
    const rowsToShow = filteredRows.slice(start, end);
    console.log('Showing rows:', start, 'to', end, '(', rowsToShow.length, 'rows)');
    
    rowsToShow.forEach(row => {
        row.style.display = '';
    });
    
    // Update stats if needed
    updateStats();
}

function updateStats() {
    // Update any statistics counters if they exist
    const totalCountEl = document.getElementById('totalCount');
    const completedCountEl = document.getElementById('completedCount');
    const inProgressCountEl = document.getElementById('inProgressCount');
    const overdueCountEl = document.getElementById('overdueCount');
    
    if (totalCountEl) {
        let totalVisible = 0;
        let completedVisible = 0;
        let inProgressVisible = 0;
        let overdueVisible = 0;
        
        filteredRows.forEach(row => {
            if (row.cells.length >= 6) {
                totalVisible++;
                const status = row.cells[5].textContent.trim();
                if (status === 'Achieved') completedVisible++;
                else if (status === 'In Progress') inProgressVisible++;
                else if (status === 'Non Achieved') overdueVisible++;
            }
        });
        
        totalCountEl.textContent = totalVisible;
        if (completedCountEl) completedCountEl.textContent = completedVisible;
        if (inProgressCountEl) inProgressCountEl.textContent = inProgressVisible;
        if (overdueCountEl) overdueCountEl.textContent = overdueVisible;
    }
}

function initializePagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    console.log('Initializing pagination - Total pages:', totalPages);
    
    pagination = new PaginationComponent('pagination', {
        currentPage: currentPage,
        totalPages: totalPages,
        maxVisible: 5,
        onPageChange: function(page) {
            console.log('Page changed to:', page);
            currentPage = page;
            renderTable();
        }
    });
}

function updatePagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    console.log('Updating pagination - Total pages:', totalPages);
    
    if (pagination) {
        pagination.currentPage = currentPage;
        pagination.setTotalPages(totalPages);
    } else {
        initializePagination();
    }
}

// Pagination Component
class PaginationComponent {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.currentPage = options.currentPage || 1;
        this.totalPages = options.totalPages || 1;
        this.maxVisible = options.maxVisible || 5;
        this.onPageChange = options.onPageChange || function() {};
        
        this.render();
    }
    
    render() {
        if (!this.container) return;
        
        this.container.innerHTML = '';
        
        if (this.totalPages <= 1) return;
        
        // Previous button
        const prevButton = this.createButton('« Previous', this.currentPage - 1, this.currentPage === 1);
        this.container.appendChild(prevButton);
        
        // Page numbers
        const { start, end } = this.getVisibleRange();
        
        // First page and ellipsis
        if (start > 1) {
            this.container.appendChild(this.createButton('1', 1));
            if (start > 2) {
                this.container.appendChild(this.createEllipsis());
            }
        }
        
        // Visible page numbers
        for (let i = start; i <= end; i++) {
            const button = this.createButton(i.toString(), i, false, i === this.currentPage);
            this.container.appendChild(button);
        }
        
        // Last page and ellipsis
        if (end < this.totalPages) {
            if (end < this.totalPages - 1) {
                this.container.appendChild(this.createEllipsis());
            }
            this.container.appendChild(this.createButton(this.totalPages.toString(), this.totalPages));
        }
        
        // Next button
        const nextButton = this.createButton('Next »', this.currentPage + 1, this.currentPage === this.totalPages);
        this.container.appendChild(nextButton);
    }
    
    createButton(text, page, disabled = false, active = false) {
        const li = document.createElement('li');
        li.className = `page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}`;
        
        const button = document.createElement('button');
        button.className = 'page-link';
        button.textContent = text;
        button.disabled = disabled;
        
        if (!disabled) {
            button.addEventListener('click', () => {
                this.goToPage(page);
            });
        }
        
        li.appendChild(button);
        return li;
    }
    
    createEllipsis() {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        
        const span = document.createElement('span');
        span.className = 'page-link';
        span.textContent = '...';
        
        li.appendChild(span);
        return li;
    }
    
    getVisibleRange() {
        const half = Math.floor(this.maxVisible / 2);
        let start = Math.max(1, this.currentPage - half);
        let end = Math.min(this.totalPages, start + this.maxVisible - 1);
        
        if (end - start + 1 < this.maxVisible) {
            start = Math.max(1, end - this.maxVisible + 1);
        }
        
        return { start, end };
    }
    
    goToPage(page) {
        if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
            this.currentPage = page;
            this.render();
            this.onPageChange(page);
        }
    }
    
    setTotalPages(totalPages) {
        this.totalPages = totalPages;
        if (this.currentPage > totalPages) {
            this.currentPage = Math.max(1, totalPages);
        }
        this.render();
    }
}

// Make toggleSidebar function available globally
window.toggleSidebar = toggleSidebar;
