// Initialize Bootstrap components
let deleteModal;
let pagination;
let allRows = [];
let filteredRows = [];
let rowsPerPage = 5;
let currentPage = 1;

// Initialize setelah DOM loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize modal dan toast
    const deleteModalElement = document.getElementById('deleteModal');
    
    if (deleteModalElement) {
        deleteModal = new bootstrap.Modal(deleteModalElement);
    }
    
    // Set active navigation based on current page
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === currentPage) {
            link.classList.add('active');
        }
    });

    // Initialize table data
    const tableBody = document.getElementById('usersTableBody');
    allRows = Array.from(tableBody.getElementsByTagName('tr'));
    filteredRows = [...allRows];
    
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
    
    // Add some loading animation
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

function initializeEventListeners() {
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
function confirmLogout() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            modal.hide();
            
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
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    
    filteredRows = allRows.filter(row => {
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

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    if (success === '1') {
        showSuccessNotification();

        // Hapus query dari URL
        history.replaceState(null, '', window.location.pathname);
    }
});


function renderTable() {
    const tableBody = document.getElementById('usersTableBody');
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

// Delete modal functions
let currentDeleteUserId = null;

function showDeleteModal(userName, userId) {
    console.log('showDeleteModal called with:', userName, userId);
    
    // Store user ID for deletion
    currentDeleteUserId = userId;
    
    // Update modal content
    const deleteUserNameElement = document.getElementById('deleteUserName');
    if (deleteUserNameElement) {
        deleteUserNameElement.textContent = userName;
    }
    
    // Show modal
    if (deleteModal) {
        deleteModal.show();
        console.log('Modal should be visible now');
    } else {
        console.error('deleteModal is not initialized');
        // Fallback - try to initialize modal again
        const deleteModalElement = document.getElementById('deleteModal');
        if (deleteModalElement) {
            deleteModal = new bootstrap.Modal(deleteModalElement);
            deleteModal.show();
        }
    }
}

function confirmDelete() {
    console.log('confirmDelete called for user ID:', currentDeleteUserId);
    
    if (!currentDeleteUserId) {
        console.error('No user ID found for deletion');
        return;
    }
    
    const deleteBtn = document.querySelector('.btn-delete');
    if (!deleteBtn) {
        console.error('Delete button not found');
        return;
    }
    
    const originalText = deleteBtn.innerHTML;
    
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;
    
    // Make AJAX call to delete user
    fetch('delete_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            user_id: currentDeleteUserId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hide modal
            if (deleteModal) {
                deleteModal.hide();
            }
            
            // Show success notification
            showSuccessNotification(data.message || 'User deleted successfully');
            
            // Reload page to refresh data
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            // Show error
            showErrorNotification(data.message || 'Failed to delete user');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorNotification('An error occurred while deleting user');
    })
    .finally(() => {
        // Reset button
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        currentDeleteUserId = null;
    });
}

// Fungsi untuk menampilkan notifikasi sukses
        function showSuccessNotification(message = 'Account successfully deleted!') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
                z-index: 3000;
                font-weight: 600;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            notification.innerHTML = '✅ ' + message;
            
            document.body.appendChild(notification);
            
            // Animasi slide in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Hapus notifikasi setelah 3 detik
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Fungsi untuk menampilkan notifikasi error
        function showErrorNotification(message = 'An error occurred!') {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
                z-index: 3000;
                font-weight: 600;
                transform: translateX(100%);
                transition: transform 0.3s ease;
            `;
            notification.innerHTML = '❌ ' + message;
            
            document.body.appendChild(notification);
            
            // Animasi slide in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Hapus notifikasi setelah 3 detik
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

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
        if (!this.container) {
            console.error('Pagination container not found');
            return;
        }
        
        const pagination = this.createPagination();
        this.container.innerHTML = pagination;
        this.attachEventListeners();
    }
    
    createPagination() {
        let html = '';
        
        // Previous button
        const prevDisabled = this.currentPage === 1 ? 'disabled' : '';
        html += `
            <li class="page-item ${prevDisabled}">
                <a class="page-link" href="#" data-page="prev">
                    Previous
                </a>
            </li>
        `;
        
        // Page numbers
        const pages = this.getVisiblePages();
        
        pages.forEach(page => {
            if (page === '...') {
                html += `
                    <li class="page-item disabled">
                        <span class="page-link">...</span>
                    </li>
                `;
            } else {
                const active = page === this.currentPage ? 'active' : '';
                html += `
                    <li class="page-item ${active}">
                        <a class="page-link" href="#" data-page="${page}">${page}</a>
                    </li>
                `;
            }
        });
        
        // Next button
        const nextDisabled = this.currentPage === this.totalPages ? 'disabled' : '';
        html += `
            <li class="page-item ${nextDisabled}">
                <a class="page-link" href="#" data-page="next">
                    Next
                </a>
            </li>
        `;
        
        return html;
    }
    
    getVisiblePages() {
        const pages = [];
        const half = Math.floor(this.maxVisible / 2);
        
        let start = Math.max(1, this.currentPage - half);
        let end = Math.min(this.totalPages, start + this.maxVisible - 1);
        
        // Adjust start if we're near the end
        if (end - start < this.maxVisible - 1) {
            start = Math.max(1, end - this.maxVisible + 1);
        }
        
        // Add first page and ellipsis if needed
        if (start > 1) {
            pages.push(1);
            if (start > 2) {
                pages.push('...');
            }
        }
        
        // Add visible pages
        for (let i = start; i <= end; i++) {
            pages.push(i);
        }
        
        // Add ellipsis and last page if needed
        if (end < this.totalPages) {
            if (end < this.totalPages - 1) {
                pages.push('...');
            }
            pages.push(this.totalPages);
        }
        
        return pages;
    }
    
    attachEventListeners() {
        this.container.addEventListener('click', (e) => {
            e.preventDefault();
            
            const link = e.target.closest('.page-link');
            if (!link || link.closest('.page-item').classList.contains('disabled')) {
                return;
            }
            
            const page = link.getAttribute('data-page');
            this.handlePageChange(page);
        });
    }
    
    handlePageChange(page) {
        let newPage = this.currentPage;
        
        if (page === 'prev') {
            newPage = Math.max(1, this.currentPage - 1);
        } else if (page === 'next') {
            newPage = Math.min(this.totalPages, this.currentPage + 1);
        } else {
            newPage = parseInt(page);
        }
        
        if (newPage !== this.currentPage && newPage >= 1 && newPage <= this.totalPages) {
            this.currentPage = newPage;
            this.render();
            this.onPageChange(newPage);
        }
    }
    
    // Public methods
    goToPage(page) {
        this.handlePageChange(page.toString());
    }
    
    setTotalPages(total) {
        this.totalPages = total;
        this.render();
    }
    
    getCurrentPage() {
        return this.currentPage;
    }
}