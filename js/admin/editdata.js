// Toggle sidebar functionality
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

// Form validation
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
    
    // Email validation
    const email = document.getElementById('email').value;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        document.getElementById('email').style.borderColor = '#dc3545';
        alert('Please enter a valid email address');
        isValid = false;
    }

    return isValid;
}

// Form submission - FIXED VERSION
document.getElementById('editAccountForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Always prevent default form submission
    
    if (!validateForm()) {
        return;
    }

    const submitBtn = document.querySelector('.btn-primary');
    const originalText = submitBtn.innerHTML;
    
    submitBtn.innerHTML = '<div style="width: 16px; height: 16px; border: 2px solid #ffffff; border-top: 2px solid transparent; border-radius: 50%; animation: spin 1s linear infinite;"></div> Updating...';
    submitBtn.disabled = true;

    // Submit form data using FormData
    const formData = new FormData(this);
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Check if the response contains success indicator
        if (data.includes('success=1') || data.includes('successfully')) {
            // Show success notification
            showSuccessNotification();
            
            // Redirect after notification animation completes
            setTimeout(() => {
                window.location.href = 'manageaccount.php';
            }, 3500); // Wait for notification to be fully visible
        } else {
            // Reset button if there's an error
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

// Fungsi untuk menampilkan notifikasi sukses - IMPROVED VERSION
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
    
    // Force reflow to ensure initial state is applied
    notification.offsetHeight;
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
        notification.style.opacity = '1';
    }, 50);
    
    // Keep notification visible for longer
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000); // Show for 3 seconds
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

    // Check for success parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const success = urlParams.get('success');

    if (success === '1') {
        showSuccessNotification();
        
        // Clean URL and redirect after showing notification
        setTimeout(() => {
            window.location.href = 'manageaccount.php';
        }, 3500);
    }
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

// Real-time validation feedback
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

function showLogoutModal() {
    document.getElementById('logoutModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function hideLogoutModal() {
    document.getElementById('logoutModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    modal.hide();
    
    // Redirect to login page
    window.location.href = '../logout.php';
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        hideLogoutModal();
    }
});

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    // Add loading animation
    document.body.style.opacity = '0';
    setTimeout(() => {
        document.body.style.transition = 'opacity 0.3s ease';
        document.body.style.opacity = '1';
    }, 100);

    // Auto-focus first input
    document.getElementById('name').focus();
});