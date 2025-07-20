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

        function confirmLogout() {
            window.location.href = '../logout.php';
        }

        // Close modal with Escape key
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

        function togglePassword(fieldId) {
            const input = document.getElementById(fieldId);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }

        function previewPhoto(event) {
            const input = event.target;
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.querySelector('.current-photo');
                    preview.style.backgroundImage = `url(${e.target.result})`;
                    preview.style.backgroundSize = "cover";
                    preview.style.backgroundPosition = "center";
                    preview.textContent = '';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function saveProfile(event) {
            event.preventDefault();
            // This function is now handled by PHP form submission
            // The form will be submitted when user confirms in modal
        }

        function submitEditForm() {
            closeUpdateModal();
            // Submit the actual form
            document.querySelector('.edit-form').submit();
        }
        
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

        function openUpdateModal() {
    document.getElementById('updateModal').style.display = 'flex';
  }

  function closeUpdateModal() {
    document.getElementById('updateModal').style.display = 'none';
  }

  function submitEditForm() {
    closeUpdateModal();
    // Submit the actual form
    document.querySelector('.edit-form').submit();
  }