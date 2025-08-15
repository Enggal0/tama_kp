const Sidebar = {
    init() {
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
    },

    toggle() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;

        const isCollapsed = sidebar.classList.toggle('collapsed');
        
        if (window.innerWidth >= 992) {
            mainContent.classList.toggle('collapsed', isCollapsed);
            body.classList.toggle('sidebar-collapsed', isCollapsed);
        } else {
            if (!isCollapsed) {
                this.createOverlay();
            } else {
                this.removeOverlay();
            }
        }
    },

    close() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;

        sidebar.classList.add('collapsed');
        
        if (window.innerWidth >= 992) {
            mainContent.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
        }
        
        this.removeOverlay();
    },

    createOverlay() {
        if (document.querySelector('.sidebar-overlay')) return;
        
        const overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.5); z-index: 999; opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        document.body.appendChild(overlay);
        setTimeout(() => overlay.style.opacity = '1', 10);
        overlay.addEventListener('click', () => this.close());
    },

    removeOverlay() {
        const overlay = document.querySelector('.sidebar-overlay');
        if (!overlay) return;
        
        overlay.style.opacity = '0';
        setTimeout(() => overlay.remove(), 300);
    }
};

const FormManager = {
    init() {
        const form = document.querySelector('form[action="addacc_process.php"]');
        if (form) {
            form.addEventListener('submit', this.handleSubmit.bind(this));
        }
    },

    handleSubmit(e) {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirmPassword');
        
        if (password && confirmPassword) {
            if (password.value !== confirmPassword.value) {
                e.preventDefault();
                this.showError('Passwords do not match!');
                return false;
            }
        }
        
        return true;
    },

    showError(message) {
        const notification = document.createElement('div');
        notification.className = 'alert alert-danger alert-dismissible fade show';
        notification.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 9999;
            min-width: 300px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        `;
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 5000);
    }
};

function toggleSidebar() {
    Sidebar.toggle();
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('toggle' + inputId.charAt(0).toUpperCase() + inputId.slice(1));
    
    if (input && icon) {
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        }
    }
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) modal.hide();
    window.location.href = '../logout.php';
}

function showSuccessNotification() {
    const notification = document.createElement('div');
    notification.className = 'alert alert-success alert-dismissible fade show';
    notification.style.cssText = `
        position: fixed; top: 20px; right: 20px; z-index: 9999;
        min-width: 300px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle me-2"></i>
            <span>Account added successfully!</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
        window.location.href = 'manageaccount.php';
    }, 2500);
}

document.addEventListener('DOMContentLoaded', function() {
    Sidebar.init();
    FormManager.init();
    
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            const currentPage = window.location.pathname.split('/').pop();
            
            if (href && href !== currentPage && href !== '#') {
                e.preventDefault();
                Sidebar.close();
                setTimeout(() => window.location.href = href, 300);
            }
        });
    });
    
    document.addEventListener('click', function(e) {
        const sidebar = document.getElementById('sidebar');
        const burgerBtn = document.getElementById('burgerBtn');
        
        if (window.innerWidth < 992 && !sidebar.classList.contains('collapsed')) {
            if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
                Sidebar.close();
            }
        }
    });

    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const body = document.body;
        
        if (window.innerWidth >= 992) {
            Sidebar.removeOverlay();
            const isCollapsed = sidebar.classList.contains('collapsed');
            mainContent.classList.toggle('collapsed', isCollapsed);
            body.classList.toggle('sidebar-collapsed', isCollapsed);
        } else {
            mainContent.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
            
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                Sidebar.removeOverlay();
            }
        }
    });
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification();
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            if (modal) modal.hide();
            
            const sidebar = document.getElementById('sidebar');
            if (!sidebar.classList.contains('collapsed')) {
                Sidebar.close();
            }
        }
    });
});