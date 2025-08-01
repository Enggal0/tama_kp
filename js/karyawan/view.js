function showSuccessNotification(message = 'Report submitted successfully!') {
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
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        function openReportModal() {
            document.getElementById('reportModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeReportModal() {
            document.getElementById('reportModal').style.display = 'none';
            document.getElementById('reportForm').reset();
            document.body.style.overflow = 'auto';
        }

        function submitReport(event) {
            event.preventDefault();

            const achieved = document.getElementById('achieved').value;
            const target = document.getElementById('target').value;
            const status = document.getElementById('status').value;

            console.log("Report submitted:", { achieved, target, status });
            
            showSuccessNotification('Report submitted successfully!');
            closeReportModal();

            setTimeout(() => {
                window.location.href = 'mytasks.html';
            }, 2000);
        }

        document.getElementById('achieved').addEventListener('input', autoSelectStatus);
        document.getElementById('target').addEventListener('input', autoSelectStatus);

        function autoSelectStatus() {
            const achieved = parseInt(document.getElementById('achieved').value);
            const target = parseInt(document.getElementById('target').value);
            const status = document.getElementById('status');

            if (!isNaN(achieved) && !isNaN(target)) {
    status.value = achieved >= target ? 'achieve' : 'non-achieve';
}
        }

        window.addEventListener('click', function(event) {
            const modal = document.getElementById('reportModal');
            const content = document.querySelector('.modal-content-custom');
            if (event.target === modal) {
                closeReportModal();
            }
        });

        window.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeReportModal();
            }
        });