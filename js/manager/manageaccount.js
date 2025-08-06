let pagination;
let allRows = [];
let filteredRows = [];
let rowsPerPage = 5;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {
    const currentPageFile = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentPageFile) {
            link.classList.add('active');
        }
    });

    const tableBody = document.getElementById('usersTableBody');
    if (tableBody) {
        allRows = Array.from(tableBody.getElementsByTagName('tr'));
        filteredRows = [...allRows];
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
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', filterTable);
    }

    const statusFilter = document.getElementById('statusFilter');
    if (statusFilter) {
        statusFilter.addEventListener('change', filterTable);
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

// Sidebar toggle
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    if (!sidebar || !mainContent) return;
    const isCollapsed = sidebar.classList.contains('collapsed');
    if (isCollapsed) {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
        body.classList.remove('sidebar-collapsed');
    } else {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }
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
        if (isMobile && sidebar && !sidebar.classList.contains('collapsed')) {
            if (!sidebar.contains(e.target) && burgerBtn && !burgerBtn.contains(e.target)) {
                closeSidebar();
            }
        }
    });
}

function setupWindowResize() {
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth > 768 && sidebar && !sidebar.classList.contains('collapsed')) {
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
    }
}

function confirmLogout() {
    // Modal is closed automatically by Bootstrap, just redirect
    window.location.href = '../logout.php';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Only close sidebar on Escape key
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
        if (searchTerm && !name.includes(searchTerm) && !email.includes(searchTerm)) {
            showRow = false;
        }
        if (statusFilter && status !== statusFilter) {
            showRow = false;
        }
        return showRow;
    });
    currentPage = 1;
    updatePagination();
    renderTable();
}

function renderTable() {
    const tableBody = document.getElementById('usersTableBody');
    if (!tableBody) return;
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    allRows.forEach(row => {
        row.style.display = 'none';
    });
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
        if (start > 1) {
            this.container.appendChild(this.createButton('1', 1));
            if (start > 2) {
                this.container.appendChild(this.createEllipsis());
            }
        }
        for (let i = start; i <= end; i++) {
            const button = this.createButton(i.toString(), i, false, i === this.currentPage);
            this.container.appendChild(button);
        }
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

window.toggleSidebar = toggleSidebar;