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

// Initialize sidebar as closed on page load
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const body = document.body;
    
    sidebar.classList.add('collapsed');
    mainContent.classList.add('collapsed');
    body.classList.add('sidebar-collapsed');
});

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
            window.location.href = '../login.html';
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

    function editProfile() {
        window.location.href = 'editprofile.html';
    }

    function saveProfile() {
      alert('Perubahan profil telah disimpan.');
      // Di sini bisa ditambahkan kode untuk menyimpan data ke server menggunakan AJAX atau fetch
    }

    function uploadPhoto() {
        document.getElementById('photoInput').click();
        }

        function handlePhotoUpload(event) {
  const file = event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function(e) {
    const confirmChange = confirm("Apakah Anda yakin ingin mengganti foto profil?");
    if (confirmChange) {
      const photoDiv = document.querySelector('.profile-photo');
      photoDiv.style.backgroundImage = `url(${e.target.result})`;
      photoDiv.style.backgroundSize = 'cover';
      photoDiv.style.backgroundPosition = 'center';
      photoDiv.textContent = ''; // hapus inisial jika ada
    } else {
      document.getElementById('photoInput').value = ''; // reset input
    }
  };
  reader.readAsDataURL(file);
}

let selectedPhotoFile = null;

  function triggerFileInput() {
    document.getElementById('photoInput').click();
  }

  function handlePhotoUpload(event) {
    const file = event.target.files[0];
    if (!file) return;

    selectedPhotoFile = file;

    // Tampilkan modal konfirmasi Bootstrap
    const modal = new bootstrap.Modal(document.getElementById('confirmPhotoModal'));
    modal.show();
  }

  document.getElementById('confirmPhotoBtn').addEventListener('click', function () {
  if (!selectedPhotoFile) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    const photoDiv = document.querySelector('.profile-photo');
    photoDiv.style.backgroundImage = `url(${e.target.result})`;
    photoDiv.style.backgroundSize = 'cover';
    photoDiv.style.backgroundPosition = 'center';
    photoDiv.textContent = '';
  };
  reader.readAsDataURL(selectedPhotoFile);

  const modal = bootstrap.Modal.getInstance(document.getElementById('confirmPhotoModal'));
  modal.hide();

  // Show toast
  const toast = new bootstrap.Toast(document.getElementById('photoToast'));
  toast.show();

  // Reset
  document.getElementById('photoInput').value = '';
  selectedPhotoFile = null;
});