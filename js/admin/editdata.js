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

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
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

function validateForm() {
    const requiredFields = ['name', 'nik', 'email', 'phone', 'gender', 'status'];
    let isValid = true;

    requiredFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            isValid = false;
        } else {
            field.style.borderColor = '#e1e5e9';
        }
    });
    
    const email = document.getElementById('email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        document.getElementById('email').style.borderColor = '#dc3545';
        alert('Please enter a valid email address');
        isValid = false;
    }

    return isValid;
}

document.getElementById('editAccountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!validateForm()) {
        return;
    }

    const submitBtn = document.querySelector('.btn-primary');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<div style="width: 16px; height: 16px; border: 2px solid #ffffff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div> Updating...';
    submitBtn.disabled = true;

    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (data.includes('success=1') || data.includes('successfully')) {
            showSuccessNotification();
            
            setTimeout(() => {
                window.location.href = 'manageaccount.php';
            }, 3500);
        } else {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
            alert('Error updating account. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        alert('Error updating account. Please try again.');
    });
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
        opacity: 0;
    `;
    notification.innerHTML = '✅ Account successfully updated!';
    
    document.body.appendChild(notification);
    
    notification.offsetHeight;
    
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 50);
    
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

let initialValues = {};

document.addEventListener('DOMContentLoaded', function () {
    initialValues = {
        name: document.getElementById('name').value,
        nik: document.getElementById('nik').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        gender: document.getElementById('gender').value,
        status: document.getElementById('status').value,
        password: ''
    };

    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    if (success === '1') {
        showSuccessNotification();
        
        setTimeout(() => {
            window.location.href = 'manageaccount.php';
        }, 3500);
    }

    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);

    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.3s ease';
        document.body.style.opacity = '1';
    }, 100);

    document.getElementById('name').focus();
});

function cancelEdit() {
    const currentValues = {
        name: document.getElementById('name').value,
        nik: document.getElementById('nik').value,
        email: document.getElementById('email').value,
        phone: document.getElementById('phone').value,
        gender: document.getElementById('gender').value,
        status: document.getElementById('status').value,
        password: document.getElementById('password').value
    };

    const isChanged =
        currentValues.name !== initialValues.name ||
        currentValues.nik !== initialValues.nik ||
        currentValues.email !== initialValues.email ||
        currentValues.phone !== initialValues.phone ||
        currentValues.gender !== initialValues.gender ||
        currentValues.status !== initialValues.status ||
        currentValues.password !== initialValues.password;

    if (isChanged) {
        const modal = new bootstrap.Modal(document.getElementById('cancelEditModal'));
        modal.show();
    } else {
        window.location.href = 'manageaccount.php';
    }
}

function confirmCancel() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('cancelEditModal'));
    modal.hide();
    window.location.href = 'manageaccount.php';
}

document.querySelectorAll('.form-input, .form-select').forEach(field => {
    field.addEventListener('blur', function() {
        if (this.hasAttribute('required') && !this.value.trim()) {
            this.style.borderColor = '#dc3545';
        } else {
            this.style.borderColor = '#e1e5e9';
        }
    });

    field.addEventListener('input', function() {
        if (this.style.borderColor === 'rgb(220, 53, 69)' && this.value.trim()) {
            this.style.borderColor = '#e1e5e9';
        }
    });
});

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    modal.hide();
    window.location.href = '../logout.php';
}