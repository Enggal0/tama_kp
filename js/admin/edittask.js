let initialValues = {};

$(document).ready(function () {
    
    initialValues = {
        employeeName: $('#employeeName').val(),
        taskList: $('#taskList').val(),
        taskDesc: $('#taskDesc').val(),
        endDate: $('#endDate').val(),
        target: $('#target').val()
    };

    
    closeSidebar();
    setupClickOutside();

    
    $('.form-control, .form-select').on('blur', function () {
        validateField($(this));
    });

    $('.form-control, .form-select').on('input change', function () {
        if ($(this).hasClass('is-invalid')) {
            validateField($(this));
        }
    });

    
    $('#taskForm').on('submit', function (e) {
        e.preventDefault();

        const taskId = $(this).data('task-id');
        const employeeDisabled = $('#employeeName').prop('disabled');
        const taskDisabled = $('#taskList').prop('disabled');
        const targetDisabled = $('#target').prop('disabled');

        const employeeName = $('#employeeName').val();
        const task_type_id = $('#task_type_id').val();
        const taskDesc = $('#taskDesc').val();
        const endDate = $('#endDate').val();
        const target = $('#target').val();
        
        if (!employeeDisabled && !employeeName) {
            showErrorNotification('Please fill in all required fields.');
            return;
        }
        if (!taskDisabled && !task_type_id) {
            showErrorNotification('Please fill in all required fields.');
            return;
        }
        if (!targetDisabled && !target) {
            showErrorNotification('Please fill in all required fields.');
            return;
        }
        
        if (!taskDesc || !endDate) {
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
                task_type_id: task_type_id,
                description: taskDesc,
                deadline: endDate,
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
});

function validateField(field) {
    const value = field.val();
    const isRequired = field.prop('required');
    
    if (isRequired && !value.trim()) {
        field.addClass('is-invalid');
        return false;
    } else {
        field.removeClass('is-invalid');
        return true;
    }
}

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

function cancelEdit() {
    const modal = new bootstrap.Modal(document.getElementById('cancelEditModal'));
    modal.show();
}

function confirmCancel() {
    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('cancelEditModal'));
    if (modalInstance) modalInstance.hide();
    window.location.href = 'managetask.php';
}

function confirmLogout() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
    if (modal) modal.hide();
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

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        
        const logoutModal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        const cancelModal = bootstrap.Modal.getInstance(document.getElementById('cancelEditModal'));
        
        if (logoutModal) logoutModal.hide();
        if (cancelModal) cancelModal.hide();
        
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});