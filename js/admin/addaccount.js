// Navigation functionality
        function setActiveNav(sectionId) {
            // Remove active class from all nav links
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            
            // Add active class to current nav link
            document.querySelector(`[data-section="${sectionId}"]`).classList.add('active');
            
            // Hide all content sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Show current section
            document.getElementById(sectionId).classList.add('active');
            
            // Update page title
            const titles = {
                'dashboard': 'Dashboard',
                'manage-accounts': 'Mengelola Tugas',
                'manage-tasks': 'Manage Task',
                'statistics': 'Statistik Kinerja',
                'employee-tasks': 'Tugas Karyawan'
            };
            document.getElementById('pageTitle').textContent = titles[sectionId];
        }

        // Task management functions
        function showAddTaskForm() {
            document.getElementById('addTaskForm').style.display = 'block';
        }

        function hideAddTaskForm() {
            document.getElementById('addTaskForm').style.display = 'none';
            // Reset form
            document.getElementById('employeeSelect').value = '';
            document.getElementById('taskType').value = '';
            document.getElementById('weeklyTarget').value = '';
            document.getElementById('taskDescription').value = '';
        }

        function addTask() {
            const employee = document.getElementById('employeeSelect').value;
            const taskType = document.getElementById('taskType').value;
            const target = document.getElementById('weeklyTarget').value;
            const description = document.getElementById('taskDescription').value;

            if (!employee || !taskType || !target) {
                alert('Mohon lengkapi semua field yang wajib diisi');
                return;
            }

            // Simulate adding task to table
            const tableBody = document.getElementById('tasksTableBody');
            const employeeName = document.getElementById('employeeSelect').options[document.getElementById('employeeSelect').selectedIndex].text;
            const taskTypeName = document.getElementById('taskType').options[document.getElementById('taskType').selectedIndex].text;
            
            const newRow = `
                <tr>
                    <td>${employeeName}</td>
                    <td>${taskTypeName}</td>
                    <td>${target} data</td>
                    <td>0/${target}</td>
                    <td><span class="status-badge status-progress">Baru</span></td>
                    <td>
                        <button class="btn btn-secondary" style="padding: 0.5rem; font-size: 0.8rem;">Edit</button>
                    </td>
                </tr>
            `;
            
            tableBody.insertAdjacentHTML('beforeend', newRow);
            hideAddTaskForm();
            alert('Akun berhasil ditambahkan!');
        }

        // User management functions
        function showAddUserForm() {
            document.getElementById('addUserForm').style.display = 'block';
        }

        function hideAddUserForm() {
            document.getElementById('addUserForm').style.display = 'none';
            // Reset form
            document.getElementById('fullName').value = '';
            document.getElementById('nik').value = '';
            document.getElementById('email').value = '';
            document.getElementById('password').value = '';
            document.getElementById('position').value = '';
        }

        function validateAndRedirect() {
            // Ambil nilai
            const fullName = document.getElementById('fullName').value.trim();
            const nik = document.getElementById('nik').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const position = document.getElementById('position').value;

            // Kosongkan pesan error sebelumnya
            document.getElementById('errorFullName').textContent = '';
            document.getElementById('errorNik').textContent = '';
            document.getElementById('errorEmail').textContent = '';
            document.getElementById('errorPhone').textContent = '';
            document.getElementById('errorPassword').textContent = '';
            document.getElementById('errorConfirmPassword').textContent = '';
            document.getElementById('errorPosition').textContent = '';

            let valid = true;

            if (!fullName) {
                document.getElementById('errorFullName').textContent = 'Full name is required.';
                valid = false;
            }

            if (!nik) {
                document.getElementById('errorNik').textContent = 'NIK is required.';
                valid = false;
            }

            if (!email) {
                document.getElementById('errorEmail').textContent = 'Email is required.';
                valid = false;
            } else {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                document.getElementById('errorEmail').textContent = 'Enter a valid email.';
                valid = false;
                }
            }

            if (!phone) {
                document.getElementById('errorPhone').textContent = 'Phone number is required.';
                valid = false;
            }

            if (!password) {
                document.getElementById('errorPassword').textContent = 'Password is required.';
                valid = false;
            }

            if (!confirmPassword) {
                document.getElementById('errorConfirmPassword').textContent = 'Confirm your password.';
                valid = false;
            } else if (password !== confirmPassword) {
                document.getElementById('errorConfirmPassword').textContent = 'Passwords do not match.';
                valid = false;
            }

            if (!position) {
                document.getElementById('errorPosition').textContent = 'Please select a gender.';
                valid = false;
            }

            if (valid) {
                // Berhasil -> notif hijau di bawah tombol
                const notif = document.getElementById('formNotification');
                notif.style.color = 'green';
                notif.textContent = '✅ Account has been successfully added.';
                
                setTimeout(() => {
                window.location.href = 'manageaccount.html';
                }, 1500);
            }
            }


        // Filter employee tasks
        function filterEmployeeTasks() {
            const filter = document.getElementById('employeeFilter').value.toLowerCase();
            const rows = document.getElementById('employeeTasksBody').getElementsByTagName('tr');
            
            for (let i = 0; i < rows.length; i++) {
                const employeeName = rows[i].getElementsByTagName('td')[1].textContent.toLowerCase();
                if (filter === '' || employeeName.includes(filter)) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }

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

function confirmLogout() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            modal.hide();
            
            // Redirect to login page
            window.location.href = '../logout.php';
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

        function showLogoutModal() {
            document.getElementById('logoutModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function hideLogoutModal() {
            document.getElementById('logoutModal').style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                hideLogoutModal();
            }
        });

        // Initialize dashboard
        document.addEventListener('DOMContentLoaded', function() {
            // Add some loading animation
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
        });

        // Auto-refresh data simulation
        setInterval(() => {
            // Simulate real-time updates
            const progressCells = document.querySelectorAll('tbody td:nth-child(4)');
            progressCells.forEach(cell => {
                if (cell.textContent.includes('/') && Math.random() > 0.9) {
                    const [current, total] = cell.textContent.split('/');
                    const currentNum = parseInt(current);
                    const totalNum = parseInt(total);
                    if (currentNum < totalNum) {
                        cell.textContent = `${currentNum + 1}/${total}`;
                    }
                }
            });
        }, 30000); // Update every 30 seconds

        // Initialize sidebar state
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;
            
            // Ensure sidebar is not collapsed on page load
            sidebar.classList.remove('collapsed');
            mainContent.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
        });

        // Enhanced success notification function
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
    
    // Slide in animation
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove notification after 3 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (document.body.contains(notification)) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

        // Cek apakah URL punya parameter ?success=1
document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success') === '1') {
        showSuccessNotification();

        // Tunggu 2.5 detik lalu redirect ke halaman managetask
        setTimeout(() => {
            window.location.href = 'manageaccount.php';
        }, 2500);
    }
});
