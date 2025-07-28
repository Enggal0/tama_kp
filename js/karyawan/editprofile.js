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
         
        closeUpdateModal();
    }
});

function previewPhoto(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.current-photo');
            preview.innerHTML = `<img src="${e.target.result}" alt="Preview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function openUpdateModal() {
    document.getElementById('updateModal').style.display = 'flex';
}

function closeUpdateModal() {
    document.getElementById('updateModal').style.display = 'none';
}

function submitEditForm() {
    closeUpdateModal();
    document.querySelector('.edit-form').submit();
}

function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    if (input && input.type === "password") {
        input.type = "text";
    } else if (input) {
        input.type = "password";
    }
}