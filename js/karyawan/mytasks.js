// Mobile sidebar toggle
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
    window.location.href = '../login.html';
}

// Close modal with Escape key for logout modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        // Also close report modal if open
        closeReportModal();
    }
});

function showLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function setFilter(status, event) {
    const filterButtons = document.querySelectorAll('.filter-btn');
    filterButtons.forEach(btn => btn.classList.remove('active'));
    if (event) event.target.classList.add('active');

    const cards = document.querySelectorAll('.task-card');
    cards.forEach(card => {
        const cardStatus = card.getAttribute('data-status');
        if (status === 'all' || cardStatus === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    // Apply search again to visible cards
    filterTasks();
}

function filterTasks() {
    const input = document.querySelector('.search-input').value.toLowerCase();
    const cards = document.querySelectorAll('.task-card');

    cards.forEach(card => {
        if (card.style.display === 'none') return; // ⛔ skip yang sudah disembunyikan filter status

        const title = card.querySelector('.task-title').innerText.toLowerCase();
        const description = card.querySelector('.task-description').innerText.toLowerCase();
        if (title.includes(input) || description.includes(input)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

function sortTasks(criteria) {
    const grid = document.getElementById('tasksGrid');
    const tasks = Array.from(grid.children);

    tasks.sort((a, b) => {
        let aVal = '', bVal = '';

        if (criteria === 'type') {
            aVal = a.querySelector('.task-type')?.innerText.toLowerCase() || '';
            bVal = b.querySelector('.task-type')?.innerText.toLowerCase() || '';
        } else {
            aVal = a.dataset[criteria]?.toLowerCase() || '';
            bVal = b.dataset[criteria]?.toLowerCase() || '';
        }

        return aVal.localeCompare(bVal);
    });

    tasks.forEach(task => grid.appendChild(task));
}

function openReportModal(taskId = 'task1') {
    document.getElementById('reportModal').style.display = 'block';
    document.getElementById('reportTaskId').value = taskId;
    document.body.style.overflow = 'hidden';
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('reportForm').reset();
        document.body.style.overflow = 'auto';
    }
}

function showSuccessNotification() {
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
            notification.innerHTML = '✅ Task successfully reported!';
            
            document.body.appendChild(notification);
            
            // Animasi slide in
            setTimeout(() => {
                notification.style.transform = 'translateX(0)';
            }, 10);
            
            // Hapus notifikasi setelah 3 detik
            setTimeout(() => {
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

function submitReport(event) {
    event.preventDefault();

    const taskId = document.getElementById('reportTaskId').value.trim();
    const achieved = document.getElementById('achieved').value.trim();
    const target = document.getElementById('target').value.trim();
    const status = document.getElementById('status').value.trim();

    // Validasi hanya jika ada field kosong
    if (!achieved || !target) {
        return;
    }

    console.log("Submitted:", { taskId, achieved, target, status });

    showSuccessNotification();
    closeReportModal();

    setTimeout(() => {
        window.location.href = 'mytasks.html';
    }, 2000);
}

// Auto-pilih status berdasarkan jumlah tercapai vs target
function autoSelectStatus() {
    const achieved = parseInt(document.getElementById('achieved').value);
    const target = parseInt(document.getElementById('target').value);
    const status = document.getElementById('status');

    if (!isNaN(achieved) && !isNaN(target)) {
        status.value = achieved >= target ? 'achieve' : 'non-achieve';
    }
}

// Event listeners for form functionality
document.addEventListener('DOMContentLoaded', function() {
    // Auto-select status based on achieved vs target
    const achievedInput = document.getElementById('achieved');
    const targetInput = document.getElementById('target');
    
    if (achievedInput) {
        achievedInput.addEventListener('input', autoSelectStatus);
    }
    
    if (targetInput) {
        targetInput.addEventListener('input', autoSelectStatus);
    }
    
    // Form submission
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', submitReport);
    }
    
    // Click outside modal to close
    const reportModal = document.getElementById('reportModal');
    if (reportModal) {
        reportModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeReportModal();
            }
        });
    }
});