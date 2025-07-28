let sidebar;
let mainContent;

document.addEventListener('DOMContentLoaded', function() {
    sidebar = document.getElementById('sidebar');
    mainContent = document.getElementById('mainContent');

    closeSidebar();
    setupClickOutside();
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification();
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

function toggleSidebar() {
    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);
    document.body.classList.toggle('sidebar-collapsed', isCollapsed);
}

function closeSidebar() {
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    document.body.classList.add('sidebar-collapsed');
}

function setupClickOutside() {
    document.addEventListener('click', function(e) {
        const burgerBtn = document.getElementById('burgerBtn');
        const isMobile = window.innerWidth <= 768;
        
        if (isMobile && !sidebar.classList.contains('collapsed')) {
            if (!sidebar.contains(e.target) && !burgerBtn.contains(e.target)) {
                closeSidebar();
            }
        }
    });
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) modal.hide();
    window.location.href = '../logout.php';
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
    notification.innerHTML = '✅ Account added successfully!';
    
    document.body.appendChild(notification);
    requestAnimationFrame(() => notification.style.transform = 'translateX(0)');
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('toggle' + inputId.charAt(0).toUpperCase() + inputId.slice(1));
    
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) modal.hide();
        
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});