let deleteModal;
let allRows = [];
let filteredRows = [];
let rowsPerPage = 5;
let currentPage = 1;

document.addEventListener('DOMContentLoaded', function() {    
    const deleteModalElement = document.getElementById('deleteModal');
    if (deleteModalElement) {
        deleteModal = new bootstrap.Modal(deleteModalElement);
    }
    
    const tableBody = document.getElementById('usersTableBody');
    if (tableBody) {
        allRows = Array.from(tableBody.getElementsByTagName('tr'));
        filteredRows = [...allRows];
        updatePagination();
        renderTable();
    }

    initializeEventListeners();
    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification();
        history.replaceState(null, '', window.location.pathname);
    }
});

function initializeEventListeners() {
    document.getElementById('searchInput')?.addEventListener('input', filterTable);
    document.getElementById('statusFilter')?.addEventListener('change', filterTable);
    document.getElementById('rowsPerPageSelect')?.addEventListener('change', function() {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        updatePagination();
        renderTable();
    });
}

function filterTable() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
    
    filteredRows = allRows.filter(row => {
        const name = row.cells[0].textContent.toLowerCase();
        const email = row.cells[2].textContent.toLowerCase();
        const status = row.cells[5].textContent.toLowerCase();

        return (!searchTerm || name.includes(searchTerm) || email.includes(searchTerm)) && 
               (!statusFilter || status === statusFilter);
    });
    
    currentPage = 1;
    updatePagination();
    renderTable();
}

function renderTable() {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    
    allRows.forEach(row => row.style.display = 'none');
    filteredRows.slice(start, end).forEach(row => row.style.display = '');
}

function updatePagination() {
    const paginationEl = document.getElementById('pagination');
    if (!paginationEl) return;

    const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
    let html = '';
    
    html += `<li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage - 1}">Previous</a>
    </li>`;

    for (let i = 1; i <= totalPages; i++) {
        if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
            html += `<li class="page-item ${i === currentPage ? 'active' : ''}">
                <a class="page-link" href="#" data-page="${i}">${i}</a>
            </li>`;
        } else if (i === currentPage - 2 || i === currentPage + 2) {
            html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
        }
    }

    html += `<li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
        <a class="page-link" href="#" data-page="${currentPage + 1}">Next</a>
    </li>`;

    paginationEl.innerHTML = html;

    paginationEl.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.parentElement.classList.contains('disabled')) return;
            
            const newPage = parseInt(this.dataset.page);
            if (newPage && !isNaN(newPage) && newPage !== currentPage) {
                currentPage = newPage;
                updatePagination();
                renderTable();
            }
        });
    });
}

let currentDeleteUserId = null;

function showDeleteModal(userName, userId) {
    currentDeleteUserId = userId;
    document.getElementById('deleteUserName').textContent = userName;
    deleteModal?.show();
}

function confirmDelete() {
    if (!currentDeleteUserId) return;
    
    const deleteBtn = document.querySelector('.btn-delete');
    if (!deleteBtn) return;
    
    const originalText = deleteBtn.innerHTML;
    deleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';
    deleteBtn.disabled = true;

    fetch('delete_user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user_id: currentDeleteUserId })
    })
    .then(response => response.json())
    .then(data => {
        deleteModal?.hide();
        showSuccessNotification(data.message || 'User deleted successfully');
        if (data.success) {
            setTimeout(() => window.location.reload(), 1500);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorNotification('An error occurred while deleting user');
    })
    .finally(() => {
        deleteBtn.innerHTML = originalText;
        deleteBtn.disabled = false;
        currentDeleteUserId = null;
    });
}

function showSuccessNotification(message = 'Operation completed successfully') {
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
    requestAnimationFrame(() => notification.style.transform = 'translateX(0)');
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => document.body.contains(notification) && document.body.removeChild(notification), 300);
    }, 3000);
}

function showErrorNotification(message = 'An error occurred') {
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
    requestAnimationFrame(() => notification.style.transform = 'translateX(0)');
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => document.body.contains(notification) && document.body.removeChild(notification), 300);
    }, 4000);
}

function confirmLogout() {
    window.location.href = '../logout.php';
}

function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}