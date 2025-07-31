document.getElementById('loginForm').addEventListener('submit', function (e) {

  const email = document.getElementById('email').value;
  const password = document.getElementById('password').value;

  if (!email || !password) {
    alert('Please fill in all required fields.');
    return;
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert('Please enter a valid email address.');
    return;
  }

  const button = document.querySelector('.login-button');
  const originalText = button.textContent;

  button.textContent = 'Logging in...';
  button.disabled = true;

  setTimeout(() => {
    alert(`Login attempt:\nEmail: ${email}`);
    button.textContent = originalText;
    button.disabled = false;
  }, 1500);
});

document.getElementById('loginLink').addEventListener('click', function () {
});

document.addEventListener('DOMContentLoaded', function () {
  const elements = document.querySelectorAll('.login-header, .form-group, .form-options, .login-button');
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

const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('password');

togglePassword.addEventListener('click', () => {
  const type = passwordInput.type === 'password' ? 'text' : 'password';
  passwordInput.type = type;
  togglePassword.src = type === 'password' ? 'pass-close.png' : 'pass-open.png';
});
