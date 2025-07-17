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
    
    // Close sidebar before redirecting
    closeSidebar();
    
    // Redirect to login page
    setTimeout(() => {
        window.location.href = '../login.html';
    }, 300);
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

        // Delete Modal Functions
        let deleteModal;
        let successToast;
        
        document.addEventListener('DOMContentLoaded', function() {
            deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            successToast = new bootstrap.Toast(document.getElementById('successToast'));
        });

        function showDeleteModal() {
            deleteModal.show();
        }

        function confirmDelete() {
            // Simulate deletion process
            const deleteBtn = document.querySelector('#deleteModal .btn-delete');
            const originalText = deleteBtn.innerHTML;
            
            deleteBtn.innerHTML = '<i class="bi bi-hourglass me-2"></i>Deleting...';
            deleteBtn.disabled = true;
            
            setTimeout(() => {
                deleteModal.hide();
                deleteBtn.innerHTML = originalText;
                deleteBtn.disabled = false;
                
                // Show success notification
                showSuccessNotification('Task deleted successfully!');
            }, 1500);
        }

        function showSuccessNotification(message) {
            document.getElementById('toastMessage').textContent = message;
            successToast.show();
        }

        // Auto-hide sidebar on mobile when clicking outside
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !toggle.contains(e.target) && 
                                sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebar.classList.add('collapsed');
                document.getElementById('mainContent').classList.remove('expanded');
                document.body.classList.add('sidebar-collapsed');
            }
        });

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

    // Global variables
let currentPage = 1;
let rowsPerPage = 5;
let filteredData = [];
let allTasks = [];

// DOM Elements
const taskTable = document.getElementById('taskTable');
const searchInput = document.getElementById('searchInput');
const statusFilter = document.getElementById('statusFilter');
const typeFilter = document.getElementById('typeFilter');
const rowsPerPageSelect = document.getElementById('rowsPerPageSelect');
const paginationContainer = document.getElementById('pagination');

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeTable();
    setupEventListeners();
    updateStats();
});

// Initialize table data
function initializeTable() {
    // Get all table rows (excluding header)
    const rows = Array.from(taskTable.querySelectorAll('tbody tr'));
    
    // Extract data from existing table rows
    allTasks = rows.map((row, index) => {
        const cells = row.querySelectorAll('td');
        return {
            id: index + 1,
            taskType: cells[0].textContent.trim(),
            name: cells[1].textContent.trim(),
            description: cells[2].textContent.trim(),
            deadline: cells[3].textContent.trim(),
            tasksDone: parseInt(cells[4].textContent.trim()),
            status: cells[5].querySelector('.badge').textContent.trim(),
            target: cells[6].textContent.trim(),
            element: row.cloneNode(true) // Store the original row element
        };
    });
    
    filteredData = [...allTasks];
    renderTable();
}

// Setup event listeners
function setupEventListeners() {
    // Search functionality
    searchInput.addEventListener('input', function() {
        currentPage = 1;
        filterAndRenderTable();
    });
    
    // Status filter
    statusFilter.addEventListener('change', function() {
        currentPage = 1;
        filterAndRenderTable();
    });
    
    // Type filter
    typeFilter.addEventListener('change', function() {
        currentPage = 1;
        filterAndRenderTable();
    });
    
    // Rows per page
    rowsPerPageSelect.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        renderTable();
    });
}

// Filter and render table
function filterAndRenderTable() {
    const searchTerm = searchInput.value.toLowerCase();
    const statusValue = statusFilter.value.toLowerCase();
    const typeValue = typeFilter.value.toLowerCase();
    
    filteredData = allTasks.filter(task => {
        const matchesSearch = task.name.toLowerCase().includes(searchTerm) ||
                            task.taskType.toLowerCase().includes(searchTerm) ||
                            task.description.toLowerCase().includes(searchTerm);
        
        const matchesStatus = !statusValue || 
                            (statusValue === 'achieve' && task.status.toLowerCase() === 'achieved') ||
    (statusValue === 'non achieve' && task.status.toLowerCase() === 'non achieved') ||
                            (statusValue === 'progress' && task.status.toLowerCase().includes('progress'));
        
        const matchesType = !typeValue || task.taskType.toLowerCase().includes(typeValue);
        
        return matchesSearch && matchesStatus && matchesType;
    });
    
    renderTable();
}

// Render table with pagination
function renderTable() {
    const startIndex = (currentPage - 1) * rowsPerPage;
    const endIndex = startIndex + rowsPerPage;
    const paginatedData = filteredData.slice(startIndex, endIndex);
    
    // Clear existing table body
    const tbody = taskTable.querySelector('tbody');
    tbody.innerHTML = '';
    
    // Add filtered and paginated rows
    paginatedData.forEach(task => {
        const row = task.element.cloneNode(true);
        // Update action buttons to maintain functionality
        const editBtn = row.querySelector('.action-btn[title="Edit"]');
        const deleteBtn = row.querySelector('.action-btn[title="Delete"]');
        
        if (editBtn) {
            editBtn.onclick = () => window.location.href = 'edittask.php';
        }
        if (deleteBtn) {
            deleteBtn.onclick = () => showDeleteModal(task.name);
        }
        
        tbody.appendChild(row);
    });
    
    // Update pagination
    renderPagination();
    
    // Update stats
    updateStats();
}

// Render pagination
function renderPagination() {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    
    if (totalPages <= 1) {
        paginationContainer.innerHTML = '';
        return;
    }
    
    let paginationHTML = '';
    
    // Previous button
    paginationHTML += `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">
                <i class="bi bi-chevron-left"></i>
            </a>
        </li>
    `;
    
    // Page numbers
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(totalPages, currentPage + 2);
    
    if (startPage > 1) {
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(1)">1</a>
            </li>
        `;
        if (startPage > 2) {
            paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
    }
    
    for (let i = startPage; i <= endPage; i++) {
        paginationHTML += `
            <li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
            </li>
        `;
    }
    
    if (endPage < totalPages) {
        if (endPage < totalPages - 1) {
            paginationHTML += `
                <li class="page-item disabled">
                    <span class="page-link">...</span>
                </li>
            `;
        }
        paginationHTML += `
            <li class="page-item">
                <a class="page-link" href="#" onclick="changePage(${totalPages})">${totalPages}</a>
            </li>
        `;
    }
    
    // Next button
    paginationHTML += `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">
                <i class="bi bi-chevron-right"></i>
            </a>
        </li>
    `;
    
    paginationContainer.innerHTML = paginationHTML;
}

// Change page function
function changePage(page) {
    const totalPages = Math.ceil(filteredData.length / rowsPerPage);
    
    if (page < 1 || page > totalPages) return;
    
    currentPage = page;
    renderTable();
}

// Update statistics
function updateStats() {
    const totalTasks = filteredData.length;
    const achievedTasks = filteredData.filter(task => 
        task.status.toLowerCase().includes('achieved')).length;
    const nonAchievedTasks = filteredData.filter(task => 
        task.status.toLowerCase().includes('non achieved')).length;
    const achievementRate = totalTasks > 0 ? 
        Math.round((achievedTasks / totalTasks) * 100) : 0;
    
    // Update stats display
    document.getElementById('totalCount').textContent = totalTasks;
    document.getElementById('achievementRate').textContent = achievementRate + '%';
    document.getElementById('completedCount').textContent = achievedTasks;
    document.getElementById('overdueCount').textContent = nonAchievedTasks;
}

// Sidebar toggle functionality
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}

// Delete modal functionality
function showDeleteModal(taskName = 'this task') {
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    document.getElementById('deleteUserName').textContent = taskName;
    modal.show();
}

function confirmDelete() {
    // Add your delete logic here
    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
    modal.hide();
    
    // Show success toast
    showSuccessToast('Task deleted successfully!');
    
    // Here you would typically make an API call to delete the task
    // For now, we'll just refresh the table
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Success toast
function showSuccessToast(message) {
    const toast = document.getElementById('successToast');
    const toastMessage = document.getElementById('toastMessage');
    
    toastMessage.textContent = message;
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
}

// Logout functionality
function logout() {
    // Add logout logic here
    localStorage.removeItem('user');
    window.location.href = '../login.html';
}

// Make functions globally accessible
window.toggleSidebar = toggleSidebar;
window.showDeleteModal = showDeleteModal;
window.confirmDelete = confirmDelete;
window.changePage = changePage;
window.logout = logout;

document.getElementById('searchInput').addEventListener('input', function () {
    const query = this.value.toLowerCase();
    const rows = document.querySelectorAll('#taskTable tbody tr');

    rows.forEach(row => {
        const name = row.cells[1].textContent.toLowerCase(); // Kolom ke-2 adalah "Name"
        if (name.includes(query)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});
