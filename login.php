<?php
session_start();

// Kalau sudah login, langsung redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['role']) {
        case 'admin':
            header('Location: admin/dashboard.php');
            break;
        case 'employee':
            header('Location: karyawan/dashboard.php');
            break;
        case 'manajer':
            header('Location: manajer/dashboard.php');
            break;
        default:
            header('Location: login.php?error=role');
            break;
    }
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login - Kaon</title>
  <link rel="stylesheet" href="css/style-login.css" />
</head>
<body>
  <div class="version">Login v5</div>

  <div class="container">
    <div class="left-panel">
      <div class="logo">Kaon</div>
      <div class="tagline">
        An application that will make your gift sending experience even more memorable
      </div>
    </div>

    <div class="right-panel">
      <div class="login-header">
        <h1 class="login-title">Login</h1>
        <p class="login-subtitle">Login to your account.</p>
      </div>

      <?php if (isset($_GET['error'])): ?>
        <div style="color: red; margin-bottom: 10px;">
          <?= htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login_process.php">
        <div class="form-group">
          <label for="nik" class="form-label">NIK</label>
          <input type="text" id="nik" name="nik" class="form-input" required />
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <div class="password-container">
            <input type="password" id="password" name="password" class="form-input" required />
            <img src="pass-close.png" id="togglePassword" class="toggle-password-icon" alt="Toggle Password" />
          </div>
        </div>

        <div class="form-options">
          <a href="reset-password.html">Reset Password?</a>
        </div>

        <button type="submit" class="login-button">Login</button>
      </form>

      <p class="login-link">
        Don't have an account? <a href="register.php" id="loginLink">Register here</a>
      </p>
    </div>
  </div>

  <script src="js/login.js"></script>
</body>
</html>