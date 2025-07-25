let sidebar;
let mainContent;

document.addEventListener('DOMContentLoaded', function() {
    sidebar = document.getElementById('sidebar');
    mainContent = document.getElementById('mainContent');

        initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();

        const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showNotification('Account added successfully!');
        setTimeout(() => {
            window.location.href = 'manageaccount.php';
        }, 2500);
    }

        const form = document.getElementById('addAccountForm');
    if (form) {
        form.addEventListener('submit', handleFormSubmit);
    }
});

function handleFormSubmit(e) {
    e.preventDefault();
    
    const formData = {
        fullName: document.getElementById('fullName').value.trim(),
        nik: document.getElementById('nik').value.trim(),
        email: document.getElementById('email').value.trim(),
        phone: document.getElementById('phone').value.trim(),
        password: document.getElementById('password').value,
        confirmPassword: document.getElementById('confirmPassword').value,
        position: document.getElementById('position').value
    };

    clearErrors();
    
    if (validateForm(formData)) {
        submitForm(formData);
    }
}

function validateForm(data) {
    let isValid = true;
    
        if (!data.fullName) showError('fullName', 'Full name is required');
    if (!data.nik) showError('nik', 'NIK is required');
    if (!data.phone) showError('phone', 'Phone number is required');
    if (!data.position) showError('position', 'Position is required');
    
        if (!data.email) {
        showError('email', 'Email is required');
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(data.email)) {
        showError('email', 'Enter a valid email');
    }
    
        if (!data.password) {
        showError('password', 'Password is required');
    }
    if (!data.confirmPassword) {
        showError('confirmPassword', 'Confirm your password');
    } else if (data.password !== data.confirmPassword) {
        showError('confirmPassword', 'Passwords do not match');
    }

    return document.querySelectorAll('.error-message').length === 0;
}

function showError(fieldId, message) {
    const errorEl = document.getElementById(`error${fieldId.charAt(0).toUpperCase() + fieldId.slice(1)}`);
    if (errorEl) errorEl.textContent = message;
}

function clearErrors() {
    document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
}

function showNotification(message, type = 'success') {
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${type === 'success' ? '✅' : '❌'} ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    document.body.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
    toast.show();
    
    toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function toggleSidebar() {
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
    document.body.classList.toggle('sidebar-collapsed');
}

function closeSidebar() {
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
}


function navigateWithSidebarClose(url) {
        closeSidebar();
    
        setTimeout(() => {
        window.location.href = url;
    }, 300); }

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
        
                if (isMobile && !sidebar.classList.contains('collapsed')) {
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
        
                if (window.innerWidth > 768 && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}

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
            modal.hide();
            
                        window.location.href = '../logout.php';
        }

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) {
            modal.hide();
        }
        
                const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

document.addEventListener('DOMContentLoaded', function() {
        initializeSidebar();
    
        setupNavigationLinks();
    
        setupClickOutside();
    
        setupWindowResize();
    
        setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});


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
    notification.innerHTML = '✅ Task added successfully!';
    
    document.body.appendChild(notification);
    
        setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
        setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

        document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification();

                setTimeout(() => {
            window.location.href = 'manageaccount.php';
        }, 2500);
    }
});

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) {
        modal.hide();
    }
    window.location.href = '../logout.php';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
                bootstrap.Modal.getInstance(document.getElementById('logoutModal'))?.hide();
        
                if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});
