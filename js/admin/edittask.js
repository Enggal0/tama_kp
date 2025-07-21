let initialValues = {};

$(document).ready(function () {
    initialValues = {
        employeeName: $('#employeeName').val(),
        taskTypes: $('#taskTypes').val(),
        taskDesc: $('#taskDesc').val(),
        deadline: $('#deadline').val(),
        target: $('#target').val()
    };

    $('#taskForm').on('submit', function (e) {
        e.preventDefault();

        const taskId = $(this).data('task-id');
        const employeeName = $('#employeeName').val();
        const taskTypes = $('#taskTypes').val();
        const taskDesc = $('#taskDesc').val();
        const deadline = $('#deadline').val();
        const target = $('#target').val();

        if (!employeeName || !taskTypes || !deadline || !target) {
            showErrorNotification('Please fill in all required fields.');
            return;
        }

        const submitBtn = $('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true);
        submitBtn.html('<span class="spinner-border spinner-border-sm me-2"></span>Updating...');

        fetch('update_task.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                task_id: taskId,
                user_id: employeeName,
                task_type_id: taskTypes,
                description: taskDesc,
                deadline: deadline,
                target: target
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessNotification(data.message);
                setTimeout(() => {
                    window.location.href = 'managetask.php';
                }, 2000);
            } else {
                showErrorNotification(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorNotification('An error occurred while updating the task.');
        })
        .finally(() => {
            submitBtn.prop('disabled', false);
            submitBtn.html(originalText);
        });
    });

    $('.form-control, .form-select').on('blur', function () {
        validateField($(this));
    });

    $('.form-control, .form-select').on('input change', function () {
        if ($(this).hasClass('is-invalid')) {
            validateField($(this));
        }
    });

    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
});

function arraysEqual(arr1, arr2) {
    if (!Array.isArray(arr1) || !Array.isArray(arr2)) return false;
    if (arr1.length !== arr2.length) return false;
    const sorted1 = [...arr1].sort();
    const sorted2 = [...arr2].sort();
    return sorted1.every((val, index) => val === sorted2[index]);
}

function cancelEdit() {
    const modal = new bootstrap.Modal(document.getElementById('cancelEditModal'));
    modal.show();
}

function confirmCancel() {
    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('cancelEditModal'));
    modalInstance.hide();
    window.location.href = 'managetask.php';
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    modal.hide();
    window.location.href = '../logout.php';
    }

function showSuccessNotification(message = 'Task updated successfully!') {
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

function showErrorNotification(message = 'An error occurred!') {
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
    }, 4000);
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('collapsed');
}

function initializeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}

function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
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

function navigateWithSidebarClose(url) {
    closeSidebar();
    setTimeout(() => {
        window.location.href = url;
    }, 300);
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
        if (window.innerWidth > 768 && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    });
}
