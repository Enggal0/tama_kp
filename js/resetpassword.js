 // Form handling
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const button = document.getElementById('resetButton');
            const successMessage = document.getElementById('successMessage');
            const errorMessage = document.getElementById('errorMessage');
            
            // Hide previous messages
            successMessage.style.display = 'none';
            errorMessage.style.display = 'none';
            
            // Basic validation
            if (!email) {
                errorMessage.textContent = 'Please enter your email address.';
                errorMessage.style.display = 'block';
                return;
            }
            
            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorMessage.textContent = 'Please enter a valid email address.';
                errorMessage.style.display = 'block';
                return;
            }
            
            // Simulate reset process
            const originalText = button.textContent;
            button.textContent = 'Sending...';
            button.disabled = true;
            
            setTimeout(() => {
                successMessage.style.display = 'block';
                button.textContent = 'Email Sent!';
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.textContent = originalText;
                    button.disabled = false;
                }, 3000);
            }, 1500);
        });
        
        // Cancel button
        document.getElementById('cancelButton').addEventListener('click', function() {
            window.location.href = 'login.html';
        });
        
        // Back to login link
        document.getElementById('backToLogin').addEventListener('click', function(e) {
            e.preventDefault();
            window.location.href = 'login.html';
        });
        
        // Input focus animations
        const inputs = document.querySelectorAll('.form-input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        });
        
        // Add some interactive feedback
        document.addEventListener('DOMContentLoaded', function() {
            // Animate elements on load
            const elements = document.querySelectorAll('.reset-icon, .reset-header, .form-group, .reset-button, .cancel-button, .back-link');
            elements.forEach((el, index) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'all 0.6s ease';
                
                setTimeout(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Auto-focus email input
        document.getElementById('email').focus();