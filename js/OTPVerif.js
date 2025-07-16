const verifyBtn = document.getElementById('verifyBtn');
  const otpInputs = document.querySelectorAll('#otpInputs input');
  const notification = document.getElementById('notification');

  // Auto focus next input
  otpInputs.forEach((input, index) => {
    input.addEventListener('input', () => {
      if (input.value && index < otpInputs.length - 1) {
        otpInputs[index + 1].focus();
      }
    });
  });

  verifyBtn.addEventListener('click', () => {
    let otpComplete = true;

    otpInputs.forEach(input => {
      if (!input.value.trim()) {
        otpComplete = false;
      }
    });

    if (!otpComplete) {
      notification.style.display = 'block';
      setTimeout(() => {
        notification.style.display = 'none';
      }, 3000);
    } else {
      // Jika semua input terisi, redirect ke halaman newpassword.html
      window.location.href = 'newpassword.html';
    }
  });