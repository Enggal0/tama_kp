$(document).ready(function() {
    
    $('#user_id').select2({
        placeholder: 'Select Employee',
        width: '100%',
        allowClear: true
    });
    
    $('#task_id').select2({
        placeholder: 'Select Task Type',
        width: '100%',
        allowClear: true
    });
    
    $('#task_id').on('change', function() {
        // Show task type selection when task is selected
        if ($(this).val()) {
            $('#task-type-selection').show();
        } else {
            $('#task-type-selection').hide();
            $('#target-numeric').hide();
            $('#target-text').hide();
            $('input[name="task_type"]').prop('checked', false);
        }
    });
    
    // Handle task type radio button change
    $('input[name="task_type"]').on('change', function() {
        const selectedType = $(this).val();
        
        if (selectedType === 'numeric') {
            $('#target-numeric').show();
            $('#target-text').hide();
            $('#target_str').val('');
        } else if (selectedType === 'textual') {
            $('#target-text').show();
            $('#target-numeric').hide();
            $('#target_int').val('');
        }
    });
    
    // Date validation
    $('#start_date').on('change', function() {
        const startDate = $(this).val();
        const endDate = $('#end_date').val();
        
        // Update min date for end date
        $('#end_date').attr('min', startDate);
        
        // Check if end date is before start date
        if (endDate && new Date(endDate) < new Date(startDate)) {
            $('#end_date').val(startDate);
        }
    });
    
    $('#end_date').on('change', function() {
        const startDate = $('#start_date').val();
        const endDate = $(this).val();
        
        // Validate end date is not before start date
        if (startDate && new Date(endDate) < new Date(startDate)) {
            alert('End date cannot be before start date');
            $(this).val(startDate);
        }
    });
    
    
    $('#taskForm').on('submit', function(e) {
        console.log('Form submit triggered');
        let isValid = true;
        let errorMessages = [];
        
        
        $('.form-select, .form-input').removeClass('is-invalid');
        
        
        if (!$('#user_id').val()) {
            isValid = false;
            errorMessages.push('Please select an employee.');
            $('#user_id').next('.select2-container').addClass('is-invalid');
        }
        
        if (!$('#task_id').val()) {
            isValid = false;
            errorMessages.push('Please select a task type.');
            $('#task_id').next('.select2-container').addClass('is-invalid');
        }
        
        // Validate task type selection
        if ($('#task_id').val() && !$('input[name="task_type"]:checked').val()) {
            isValid = false;
            errorMessages.push('Please select target type (Numeric or Textual).');
        }
        
        // Validate start date
        if (!$('#start_date').val()) {
            isValid = false;
            errorMessages.push('Please select a start date.');
            $('#start_date').addClass('is-invalid');
        }
        
        // Validate end date
        if (!$('#end_date').val()) {
            isValid = false;
            errorMessages.push('Please select an end date.');
            $('#end_date').addClass('is-invalid');
        }
        
        // Validate date range
        if ($('#start_date').val() && $('#end_date').val()) {
            const startDate = new Date($('#start_date').val());
            const endDate = new Date($('#end_date').val());
            
            if (endDate < startDate) {
                isValid = false;
                errorMessages.push('End date cannot be before start date.');
                $('#end_date').addClass('is-invalid');
            }
        }
        
        console.log('Validation result:', isValid);
        console.log('Form data:', {
            user_id: $('#user_id').val(),
            task_id: $('#task_id').val(),
            start_date: $('#start_date').val(),
            end_date: $('#end_date').val(),
            description: $('#description').val()
        });
        
        if (!isValid) {
            e.preventDefault();
            console.log('Form submission prevented due to validation errors');
            
            
            let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert">';
            errorHtml += '<i class="bi bi-exclamation-triangle me-2"></i>';
            errorHtml += '<strong>Please fix the following errors:</strong><br>';
            errorHtml += '<ul class="mb-0">';
            errorMessages.forEach(function(message) {
                errorHtml += '<li>' + message + '</li>';
            });
            errorHtml += '</ul>';
            errorHtml += '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
            errorHtml += '</div>';
            
            
            $('.section-title').after(errorHtml);
            
            
            $('html, body').animate({
                scrollTop: $('.section-title').offset().top - 100
            }, 500);
        } else {
            console.log('Form is valid, allowing submission');
        }
    });
    
    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();
});

function validateField($field) {
    const isRequired = $field.prop('required');
    const value = $field.val();
    
    if (isRequired && (!value || (Array.isArray(value) && value.length === 0))) {
        $field.addClass('is-invalid');
        return false;
    } else {
        $field.removeClass('is-invalid');
        return true;
    }
}

function showSuccessToast() {
    const toast = new bootstrap.Toast(document.getElementById('successToast'));
    toast.show();
}

function addTaskToTable(taskData) {
    const taskBadges = taskData.tasks.map(task => 
        `<span class="badge bg-primary me-1">${task}</span>`
    ).join('');

    const statusClass = {
        'Pending': 'bg-warning',
        'In Progress': 'bg-info',
        'Completed': 'bg-success',
        'Overdue': 'bg-danger'
    };

    const row = `
        <tr>
            <td>${taskData.employee}</td>
            <td>${taskBadges}</td>
            <td>${taskData.description}</td>
            <td>${taskData.deadline}</td>
            <td><span class="badge ${statusClass[taskData.status]}">${taskData.status}</span></td>
            <td>${taskData.target}</td>
            <td>
                <button class="btn btn-sm btn-outline-primary me-1" title="Edit">
                    <i class="bi bi-pencil"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="deleteTask(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;

    $('#taskTable tbody').append(row);
}

function showSuccessNotification(callback) {
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
            if (typeof callback === 'function') {
                callback();
            }
        }, 300);
    }, 3000);
}

function cancelEdit() {
    if (confirm('Are you sure you want to cancel? Any unsaved changes will be lost.')) {
        
        closeSidebar();
        setTimeout(() => {
            window.location.href = 'managetask.php';
        }, 300);
    }
}

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

document.addEventListener('DOMContentLoaded', function() {
    const formFields = document.querySelectorAll('.form-input, .form-select');   
    formFields.forEach(field => {
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

    const priorityOptions = document.querySelectorAll('.priority-option');
    let selectedPriority = '';

    priorityOptions.forEach(option => {
        option.addEventListener('click', () => {
            priorityOptions.forEach(opt => opt.classList.remove('selected'));
            option.classList.add('selected');
            selectedPriority = option.dataset.priority;
        });
    });

    document.body.style.opacity = '0';
    document.body.style.transition = 'opacity 0.3s ease';
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
    
    initializeSidebarManagement();

    setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);


            const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification();
        
        setTimeout(() => {
            window.location.href = 'managetask.php';
        }, 2500);
    }
});

document.addEventListener('keydown', function(e) {    
    if (e.key === 'Escape') {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
    
    if (e.ctrlKey && e.key === 'b') {
        e.preventDefault();
        toggleSidebar();
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

        function confirmLogout() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            modal.hide();
            
            
            window.location.href = '../logout.php';
        }
        
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

window.toggleSidebar = toggleSidebar;
window.closeSidebar = closeSidebar;
window.openSidebar = openSidebar;
window.cancelEdit = cancelEdit;
window.navigateWithSidebarClose = navigateWithSidebarClose;