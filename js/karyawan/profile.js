function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    const isCollapsed = sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed', isCollapsed);
    body.classList.toggle('sidebar-collapsed', isCollapsed);
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}

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

window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    closeSidebar();
    
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification('Profile updated successfully!');
        history.replaceState(null, '', window.location.pathname);
    }
    
    const confirmBtn = document.getElementById('confirmPhotoBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (!selectedPhotoFile) return;

            const reader = new FileReader();
            reader.onload = function(e) {
                const photoDiv = document.querySelector('.profile-photo');
                if (photoDiv) {
                    photoDiv.style.backgroundImage = `url(${e.target.result})`;
                    photoDiv.style.backgroundSize = 'cover';
                    photoDiv.style.backgroundPosition = 'center';
                    photoDiv.textContent = '';
                }
            };
            reader.readAsDataURL(selectedPhotoFile);
            
            const modal = bootstrap.Modal.getInstance(document.getElementById('confirmPhotoModal'));
            modal.hide();
            
            const toast = new bootstrap.Toast(document.getElementById('photoToast'));
            toast.show();
            
            const photoInput = document.getElementById('photoInput');
            if (photoInput) {
                photoInput.value = '';
            }
            selectedPhotoFile = null;
        });
    }
});

function confirmLogout() {
    window.location.href = '../logout.php';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
    }
});

function editProfile() {
    window.location.href = 'editprofile.php';
}

let selectedPhotoFile = null;

function uploadPhoto() {
    document.getElementById('photoInput').click();
}

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    selectedPhotoFile = file;

    const modal = new bootstrap.Modal(document.getElementById('confirmPhotoModal'));
    modal.show();
}

// Notification functions
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
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
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
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 4000);
}

function confirmLogout() {
            window.location.href = '../logout.php';
        }

function editProfile() {
    window.location.href = 'editprofile.php';
}

function uploadPhoto() {
    document.getElementById('photoInput').click();
}

function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (file) {
        const modal = new bootstrap.Modal(document.getElementById('confirmPhotoModal'));
        modal.show();
        
        document.getElementById('confirmPhotoBtn').onclick = function() {
            modal.hide();
            const toast = new bootstrap.Toast(document.getElementById('photoToast'));
            toast.show();
        };
    }
}