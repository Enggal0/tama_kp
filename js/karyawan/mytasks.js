// Mobile sidebar toggle
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

// Function to close sidebar
function closeSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
}

// Function to navigate with sidebar close
function navigateWithCloseSidebar(url, event) {
    event.preventDefault();
    
    const currentPage = window.location.pathname.split('/').pop();
    
    // Jika bukan halaman yang sama
    if (url !== currentPage) {
        // Tutup sidebar langsung
        closeSidebar();
        
        // Navigasi langsung tanpa delay
        window.location.href = url;
    }
}

// Close sidebar when clicking outside of it (mobile)
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

// Close sidebar on window resize if switching to desktop
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

// Initialize sidebar as closed on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
});

// Logout functionality
function confirmLogout() {
    window.location.href = '../logout.php';
}

// Close modal with Escape key for logout modal
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        const modal = document.getElementById('logoutModal');
        if (modal) {
            const modalInstance = bootstrap.Modal.getInstance(modal);
            if (modalInstance) {
                modalInstance.hide();
            }
        }
        // Also close report modal if open
        closeReportModal();
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

// Task filtering functionality
function setFilter(filter, event) {
    // Remove active class from all buttons
    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
    
    // Add active class to clicked button
    if (event) {
        event.target.classList.add('active');
    }
    
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const status = task.dataset.status;
        
        if (filter === 'all') {
            task.style.display = 'block';
        } else if (filter === 'inprogress' && status === 'inprogress') {
            task.style.display = 'block';
        } else if (filter === 'achieved' && status === 'achieved') {
            task.style.display = 'block';
        } else if (filter === 'nonachieved' && status === 'nonachieved') {
            task.style.display = 'block';
        } else if (filter === 'passed' && status === 'passed') {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

// Task search functionality
function filterTasks() {
    const searchTerm = document.querySelector('.search-input').value.toLowerCase();
    const tasks = document.querySelectorAll('.task-card');
    
    tasks.forEach(task => {
        const title = task.querySelector('.task-title').textContent.toLowerCase();
        const description = task.querySelector('.task-description').textContent.toLowerCase();
        const type = task.querySelector('.task-type').textContent.toLowerCase();
        
        if (title.includes(searchTerm) || description.includes(searchTerm) || type.includes(searchTerm)) {
            task.style.display = 'block';
        } else {
            task.style.display = 'none';
        }
    });
}

// Task sorting functionality
function sortTasks(sortBy) {
    if (!sortBy) return; // Don't sort if no option selected
    
    const grid = document.getElementById('tasksGrid');
    const tasks = Array.from(grid.querySelectorAll('.task-card'));
    
    tasks.sort((a, b) => {
        switch(sortBy) {
            case 'name-asc':
                const nameA = a.dataset.taskName || '';
                const nameB = b.dataset.taskName || '';
                return nameA.localeCompare(nameB);
                
            case 'name-desc':
                const nameA2 = a.dataset.taskName || '';
                const nameB2 = b.dataset.taskName || '';
                return nameB2.localeCompare(nameA2);
                
            case 'enddate-asc':
                const dateA = new Date(a.dataset.endDate || '1970-01-01');
                const dateB = new Date(b.dataset.endDate || '1970-01-01');
                return dateA - dateB;
                
            case 'enddate-desc':
                const dateA2 = new Date(a.dataset.endDate || '1970-01-01');
                const dateB2 = new Date(b.dataset.endDate || '1970-01-01');
                return dateB2 - dateA2;
                
            case 'status-asc':
                const statusA = a.dataset.status || '';
                const statusB = b.dataset.status || '';
                return statusA.localeCompare(statusB);
                
            case 'status-desc':
                const statusA2 = a.dataset.status || '';
                const statusB2 = b.dataset.status || '';
                return statusB2.localeCompare(statusA2);
                
            default:
                return 0;
        }
    });
    
    tasks.forEach(task => grid.appendChild(task));
}

// Report Modal Functions
function openReportModal(userTaskId, taskName, taskType, targetInt, targetStr) {
    console.log('Opening modal with:', { userTaskId, taskName, taskType, targetInt, targetStr });
    
    try {
        // Set hidden values
        document.getElementById('userTaskId').value = userTaskId;
        document.getElementById('taskType').value = taskType;
        document.getElementById('taskName').value = taskName;
        
        // Reset forms
        document.getElementById('numericForm').style.display = 'none';
        document.getElementById('textForm').style.display = 'none';
        document.getElementById('progressPercentageDiv').style.display = 'none';
        
        // Show appropriate form based on task type
        if (taskType === 'numeric') {
            document.getElementById('numericForm').style.display = 'block';
            document.getElementById('targetValue').value = targetInt || 0;
            document.getElementById('achievedValue').value = '';
            document.getElementById('achievedValue').required = true;
        } else {
            document.getElementById('textForm').style.display = 'block';
            document.getElementById('targetText').value = targetStr || '';
            document.getElementById('completionStatus').value = '';
            document.getElementById('completionStatus').required = true;
        }
        
        // Clear notes
        document.getElementById('reportNotes').value = '';
        
        // Show modal using Bootstrap
        const modalElement = document.getElementById('reportModal');
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(modalElement);
            modal.show();
        } else {
            // Fallback if Bootstrap is not available
            console.warn('Bootstrap not available, using fallback modal display');
            modalElement.style.display = 'block';
            modalElement.classList.add('show');
            document.body.classList.add('modal-open');
        }
    } catch (error) {
        console.error('Error opening modal:', error);
        alert('Error opening report modal. Please try again.');
    }
}

function closeReportModal() {
    const modal = bootstrap.Modal.getInstance(document.getElementById('reportModal'));
    if (modal) {
        modal.hide();
    }
}

function submitReport() {
    console.log('submitReport() function called');
    const form = document.getElementById('reportForm');
    const formData = new FormData(form);
    
    console.log('Form data collected:', Object.fromEntries(formData));
    
    // Basic validation
    const taskType = document.getElementById('taskType').value;
    
    if (taskType === 'numeric') {
        const achievedValue = document.getElementById('achievedValue').value;
        if (!achievedValue || isNaN(achievedValue)) {
            alert('Please enter a valid achieved value');
            return;
        }
    } else if (taskType === 'text') {
        const completionStatus = document.getElementById('completionStatus').value;
        if (!completionStatus) {
            alert('Please select completion status');
            return;
        }
        
        if (completionStatus === 'in_progress') {
            const progressPercentage = document.getElementById('progressPercentage').value;
            if (!progressPercentage || isNaN(progressPercentage) || progressPercentage < 0 || progressPercentage > 100) {
                alert('Please enter a valid progress percentage (0-100)');
                return;
            }
        }
    }
    
    // Show loading state
    const submitBtn = document.querySelector('#reportModal .btn-primary');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Submitting...';
    submitBtn.disabled = true;
    
    // Submit via AJAX
    console.log('About to fetch submit_report.php');
    console.log('Current location:', window.location.href);
    console.log('Fetch URL will be:', new URL('submit_report.php', window.location.href).href);
    console.log('TIMESTAMP: Calling SUBMIT_REPORT.PHP at', new Date().toISOString());
    
    // Add error handling for FormData
    try {
        console.log('FormData entries:', Object.fromEntries(formData));
    } catch (e) {
        console.error('FormData error:', e);
    }
    
    fetch('../karyawan/submit_report.php', { 
    method: 'POST',
    body: formData,
    credentials: 'same-origin'
    })
    .then(response => {
        console.log('Response received:', {
            status: response.status,
            statusText: response.statusText,
            ok: response.ok,
            headers: Array.from(response.headers.entries())
        });
        
        // Add console log before error throws as suggested
        console.log("Server response:", response);
        console.log("Response status OK?", response.ok);
        console.log("Response type:", response.type);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        
        return response.text(); // Get raw text first
    })
    .then(text => {
        console.log('Raw response length:', text.length);
        console.log('Raw response:', text);
        console.log('Raw response (first 100 chars):', text.substring(0, 100));
        
        // Add more debugging before JSON parse
        console.log("About to parse JSON. Text is:", typeof text, text);
        
        if (!text || text.trim() === '') {
            throw new Error('Empty response from server');
        }
        
        try {
            const data = JSON.parse(text);
            console.log('Parsed JSON:', data);
            
            if (data.status === "OK") {
                alert('Connection test successful! Response: ' + data.message);
                // Close modal
                closeReportModal();
                
                // Show success notification
                showSuccessNotification();
                
                // Reload page after short delay to show updated task status
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else if (data.success) {
                // Close modal
                closeReportModal();
                
                // Show success notification
                showSuccessNotification();
                
                // Reload page after short delay to show updated task status
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                alert('Error: ' + data.message);
            }
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Raw response that failed to parse:', text);
            alert('Server response error: ' + (text || 'Empty response'));
        }
    })
    .catch(error => {
        console.error('Network/Fetch error:', error);
        console.error('Error details:', {
            message: error.message,
            stack: error.stack,
            name: error.name
        });
        alert('Network error occurred: ' + (error.message || 'Unknown error'));
    })
    .finally(() => {
        // Reset button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
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
    notification.innerHTML = '✅ Task successfully reported!';
    
    document.body.appendChild(notification);
    
    // Animasi slide in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Hapus notifikasi setelah 3 detik
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

// Debug function to test modal
function testModal() {
    console.log('Testing modal...');
    openReportModal('1', 'Test Task', 'numeric', 100, '');
}

// Function to check if all required elements exist
function checkModalElements() {
    const elements = [
        'reportModal',
        'userTaskId', 
        'taskType',
        'taskName',
        'numericForm',
        'textForm',
        'progressPercentageDiv',
        'targetValue',
        'achievedValue',
        'targetText',
        'completionStatus',
        'progressPercentage',
        'reportNotes'
    ];
    
    elements.forEach(id => {
        const element = document.getElementById(id);
        if (!element) {
            console.error(`Element with ID '${id}' not found`);
        } else {
            console.log(`✓ Element '${id}' found`);
        }
    });
}

// Function to refresh and show report buttons for In Progress tasks
function refreshReportButtons() {
    const tasks = document.querySelectorAll('.task-card');
    console.log(`Found ${tasks.length} task cards`);
    
    tasks.forEach((task, index) => {
        const status = task.dataset.status;
        const taskActions = task.querySelector('.task-actions');
        console.log(`Task ${index + 1}: status = ${status}`);
        
        if (status === 'inprogress') {
            // Check if report button already exists
            const existingReportBtn = taskActions.querySelector('.btn-primary');
            if (!existingReportBtn) {
                console.log(`Adding report button to task ${index + 1}`);
                // Create report button if it doesn't exist
                const reportBtn = document.createElement('button');
                reportBtn.className = 'task-btn btn-primary';
                reportBtn.textContent = 'Report';
                reportBtn.onclick = function() {
                    // You might need to extract task data from the card
                    testModal();
                };
                taskActions.insertBefore(reportBtn, taskActions.firstChild);
            }
        }
    });
}

// Initialize page functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, initializing...');
    
    // Check if Bootstrap is loaded
    if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded!');
    } else {
        console.log('✓ Bootstrap loaded successfully');
    }
    
    // Check modal elements
    checkModalElements();
    
    // Check for report buttons
    const reportButtons = document.querySelectorAll('.btn-primary');
    console.log(`Found ${reportButtons.length} report buttons`);
    
    reportButtons.forEach((btn, index) => {
        if (btn.textContent.includes('Report')) {
            console.log(`Report button ${index + 1}:`, btn);
            console.log(`onclick:`, btn.getAttribute('onclick'));
        }
    });
    
    // Handle completion status change for text tasks
    const completionStatus = document.getElementById('completionStatus');
    const progressPercentageDiv = document.getElementById('progressPercentageDiv');
    
    if (completionStatus) {
        completionStatus.addEventListener('change', function() {
            if (this.value === 'in_progress') {
                progressPercentageDiv.style.display = 'block';
                document.getElementById('progressPercentage').required = true;
            } else {
                progressPercentageDiv.style.display = 'none';
                document.getElementById('progressPercentage').required = false;
                document.getElementById('progressPercentage').value = '';
            }
        });
    }
    
    // Mobile responsive adjustments
    function handleResize() {
        const isMobile = window.innerWidth <= 768;
        if (isMobile) {
            closeSidebar();
        }
    }
    
    window.addEventListener('resize', handleResize);
    handleResize(); // Initial check
});