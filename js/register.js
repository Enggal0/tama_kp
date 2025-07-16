// Form elements
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    const messageContainer = document.getElementById('messageContainer');

    // Input elements
    const fullName = document.getElementById('fullName');
    const email = document.getElementById('email');
    const nik = document.getElementById('nik');
    const phone = document.getElementById('phone');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirmPassword');

    // Password toggle functionality
   function togglePassword(inputId, toggleElement) {
    const input = document.getElementById(inputId);
    const iconImg = toggleElement.querySelector('img');

    if (input.type === 'password') {
        input.type = 'text';
        iconImg.src = 'pass-open.png'; // ganti ke ikon buka
    } else {
        input.type = 'password';
        iconImg.src = 'pass-close.png'; // ganti ke ikon tutup
    }
    }


    // NIK validation - only numbers
    nik.addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
      validateField(this, 'nik');
    });

    // Phone validation
    phone.addEventListener('input', function() {
      this.value = this.value.replace(/[^0-9+\-\s()]/g, '');
      validateField(this, 'phone');
    });

    // Real-time validation
    fullName.addEventListener('blur', () => validateField(fullName, 'fullName'));
    email.addEventListener('blur', () => validateField(email, 'email'));
    password.addEventListener('input', () => validateField(password, 'password'));
    confirmPassword.addEventListener('blur', () => validateField(confirmPassword, 'confirmPassword'));

    function validateField(field, type) {
      const value = field.value.trim();
      const errorElement = document.getElementById(type + 'Error');
      let isValid = true;
      let message = '';

      switch(type) {
        case 'fullName':
          if (value.length < 2) {
            isValid = false;
            message = 'Full name must be at least 2 characters';
          } else if (!/^[a-zA-Z\s]+$/.test(value)) {
            isValid = false;
            message = 'Full name can only contain letters and spaces';
          }
          break;

        case 'email':
          const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
          if (!emailRegex.test(value)) {
            isValid = false;
            message = 'Please enter a valid email address';
          }
          break;

        case 'phone':
          if (value.length < 10) {
            isValid = false;
            message = 'Phone number must be at least 10 digits';
          }
          break;

        case 'password':
          if (value.length < 8) {
            isValid = false;
            message = 'Password must be at least 8 characters';
          } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/.test(value)) {
            isValid = false;
            message = 'Password must contain uppercase, lowercase, and number';
          }
          break;

        case 'confirmPassword':
          if (value !== password.value) {
            isValid = false;
            message = 'Passwords do not match';
          }
          break;
      }

      // Update field appearance
      field.classList.remove('valid', 'invalid');
      if (value) {
        field.classList.add(isValid ? 'valid' : 'invalid');
      }

      // Update error message
      errorElement.textContent = message;
      errorElement.className = 'validation-message ' + (isValid ? 'success' : 'error');

      return isValid;
    }

    // Form submission
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      
      // Validate all fields
      const isValidForm = [
        validateField(fullName, 'fullName'),
        validateField(email, 'email'),
        validateField(nik, 'nik'),
        validateField(phone, 'phone'),
        validateField(password, 'password'),
        validateField(confirmPassword, 'confirmPassword')
      ].every(Boolean);

      if (!isValidForm) {
        showMessage('Please fix the errors above before submitting.', 'error');
        return;
      }

      // Show loading state
      const originalText = submitBtn.textContent;
      submitBtn.innerHTML = '<span class="loading-spinner"></span>Registering...';
      submitBtn.disabled = true;

      // Clear previous messages
      messageContainer.innerHTML = '';

      // Simulate registration process
      setTimeout(() => {
        // Simulate success
        showMessage('Registration successful! Welcome to Kaon. You can now login with your credentials.', 'success');
        
        // Reset form
        form.reset();
        
        // Remove validation classes
        document.querySelectorAll('form input').forEach(input => {
          input.classList.remove('valid', 'invalid');
        });
        
        // Clear validation messages
        document.querySelectorAll('.validation-message').forEach(msg => {
          msg.textContent = '';
          msg.className = 'validation-message';
        });
        
        // Reset button
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
        
        // Scroll to top to see success message
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }, 2000);
    });

    function showMessage(message, type) {
      const messageClass = type === 'error' ? 'error-message' : 'success-message';
      messageContainer.innerHTML = `<div class="${messageClass}">${message}</div>`;
    }

    // Input focus animations
    const inputs = document.querySelectorAll('form input');
    inputs.forEach(input => {
      input.addEventListener('focus', function() {
        if (this.parentElement.classList.contains('password-field')) {
          this.parentElement.parentElement.classList.add('focused');
        } else {
          this.parentElement.classList.add('focused');
        }
      });
      
      input.addEventListener('blur', function() {
        if (this.parentElement.classList.contains('password-field')) {
          this.parentElement.parentElement.classList.remove('focused');
        } else {
          this.parentElement.classList.remove('focused');
        }
      });
    });

    // Animate elements on load
    document.addEventListener('DOMContentLoaded', function() {
      const elements = document.querySelectorAll('.register-title, .subtitle, .form-group, .register-btn, .login-link');
      elements.forEach((el, index) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
        
        setTimeout(() => {
          el.style.opacity = '1';
          el.style.transform = 'translateY(0)';
        }, index * 150);
      });

      // Animate logo
      setTimeout(() => {
        document.querySelector('.logo').style.transform = 'scale(1.05)';
        setTimeout(() => {
          document.querySelector('.logo').style.transform = 'scale(1)';
        }, 300);
      }, 500);
    });