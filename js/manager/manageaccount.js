// Manager read-only version of manageaccount.js
// Delete and edit functions removed for security

// Initialize Bootstrap components
let pagination;
let allRows = [];
let filteredRows = [];
let rowsPerPage = 5;
let currentPage = 1;

// Initialize setelah DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded - initializing manager account page');
    
    // Set active navigation based on current page
    const currentPageFile = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentPageFile) {
            link.classList.add('active');
        }
    });

    // Initialize table data
    const tableBody = document.getElementById('usersTableBody');
    if (tableBody) {
        allRows = Array.from(tableBody.getElementsByTagName('tr'));
        filteredRows = [...allRows];
        console.log('Found', allRows.length, 'rows');
    }
    
    // Initialize pagination
    initializePagination();
    
    // Initialize event listeners
    initializeEventListeners();
    
    // Initial render
    renderTable();

    // Initialize sidebar as closed
    initializeSidebar();
    
    // Setup navigation links
    setupNavigationLinks();
    
    // Setup click outside handler
    setupClickOutside();
    
    // Setup window resize handler
    setupWindowResize();
});

function initializeEventListeners() {
    console.log('Initializing event listeners...');
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            filterTable();
        });
    }

    // Filter functionality
    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function() {
            filterTable();
        });
    }

    // Rows per page functionality
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

    if (sidebar && mainContent) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
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
        if (isMobile && sidebar && !sidebar.classList.contains('collapsed')) {
            // Check if click is outside sidebar and not on burger button
            if (!sidebar.contains(e.target) && burgerBtn && !burgerBtn.contains(e.target)) {
                closeSidebar();
            }
        }
    });
}

// Close sidebar on window resize if switching to desktop
function setupWindowResize() {
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        
        // If switching to desktop and sidebar is open, close it
        if (window.innerWidth > 768 && sidebar && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}

// Initialize sidebar as closed on page load
function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    if (sidebar && mainContent) {
        // Always start with sidebar closed
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) {
        modal.hide();
    }
    
    // Redirect to login page
    window.location.href = '../logout.php';
}

// Optional: Add keyboard shortcut
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) {
            modal.hide();
        }
        
        // Also close sidebar on Escape key
        const sidebar = document.getElementById('sidebar');
        if (sidebar && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    
    filteredRows = allRows.filter(row => {
        if (row.cells.length < 6) return false;
        
        const name = row.cells[0].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const status = row.cells[5].textContent.toLowerCase();

        let showRow = true;

        // Search filter
        if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm)) {
            showRow = false;
        }

        // Status filter
        if (statusFilter && status !== statusFilter) {
            showRow = false;
        }

        return showRow;
    });
    
    // Reset to first page after filtering
    currentPage = 1;
    updatePagination();
    renderTable();
}

function renderTable() {
    const tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;
    
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    
    // Hide all rows first
    allRows.forEach(row => {
        row.style.display = 'none';
    });
    
    // Show filtered rows for current page
    filteredRows.slice(start, end).forEach(row => {
        row.style.display = '';
    });
}

function initializePagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    
    pagination = new PaginationComponent('pagination', {
        currentPage: currentPage,
        totalPages: totalPages,
        maxVisible: 5,
        onPageChange: function(page) {
            currentPage = page;
            renderTable();
        }
    });
}

function updatePagination() {
    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    
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
