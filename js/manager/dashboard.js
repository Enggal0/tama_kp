// Mobile sidebar toggle - FIXED VERSION
function toggleSidebar() {
    console.log('toggleSidebar called');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;

    if (!sidebar || !mainContent) {
        console.error('Sidebar or main content not found');
        return;
    }

    const isCollapsed = sidebar.classList.contains('collapsed');
    console.log('Current state - collapsed:', isCollapsed);

    if (isCollapsed) {
        // Show sidebar
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('collapsed');
        body.classList.remove('sidebar-collapsed');
        console.log('Showing sidebar');
    } else {
        // Hide sidebar
        sidebar.classList.add('collapsed');
        mainContent.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
        console.log('Hiding sidebar');
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
    if (modal) {
        modal.hide();
    }
    
    // Redirect to login page
    window.location.href = '../logout.php';
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard DOMContentLoaded - initializing...');
    
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
    
    console.log('Dashboard initialization complete');
});

// Optional: Add keyboard shortcut
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
        if (modal) {
            modal.hide();
        }
        
        // Also close sidebar on Escape key
        const sidebar = document.getElementById('sidebar');
        if (sidebar && !sidebar.classList.contains('collapsed')) {
            closeSidebar();
        }
    }
});

// Make toggleSidebar function available globally
window.toggleSidebar = toggleSidebar;