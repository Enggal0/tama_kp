function togglePassword(inputId, toggleElement) {
      const input = document.getElementById(inputId);
      const iconImg = toggleElement.querySelector('img');
      if (input.type === 'password') {
        input.type = 'text';
        iconImg.src = 'pass-open.png';
      } else {
        input.type = 'password';
        iconImg.src = 'pass-close.png';
      }
    }

    document.getElementById('passwordForm').addEventListener('submit', function (e) {
      e.preventDefault();
      const password = document.getElementById('password');
      const confirmPassword = document.getElementById('confirmPassword');
      const errorMsg = document.getElementById('confirmPasswordError');
      errorMsg.textContent = '';

      if (!password.value || !confirmPassword.value) {
        errorMsg.textContent = 'Both fields are required';
        return;
      }

      if (password.value !== confirmPassword.value) {
        errorMsg.textContent = 'Password does not match';
        return;
      }

      alert('Password successfully set!');
      window.location.href = 'login.html';
    });

    document.getElementById('cancelButton').addEventListener('click', function () {
      window.location.href = 'login.html';
    });