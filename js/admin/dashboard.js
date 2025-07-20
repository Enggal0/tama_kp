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
        const mainContent = document.getElementById('mainContent');
        const body = document.body;
        
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
    initializeSidebar();
    setupNavigationLinks();
    setupClickOutside();
    setupWindowResize();

    document.body.style.opacity = 1;
    
    setTimeout(() => {
        document.body.style.opacity = '1';
    }, 100);
});

        function confirmLogout() {
            const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
            modal.hide();
            
            window.location.href = '../logout.php';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('logoutModal'));
                if (modal) {
                    modal.hide();
                }
            }
        });