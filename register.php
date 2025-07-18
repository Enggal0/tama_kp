<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Register - Kaon</title>
  <link rel="stylesheet" href="css/style-register.css" />
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <div class="diagonal-overlay"></div>
      <div class="sidebar-logo">
         <img src="img/tamaa.png" alt="TAMA Logo" style="height: 200px; display: block; margin: 0; padding: 0;">
      </div>
      <p class="tagline">
        An application designed to enhance task coordination and employee performance tracking in one integrated platform
      </p>
    </div>

    <div class="right-panel">
      <h1 class="register-title">Register</h1>
      <p class="subtitle">Sign up to get started.</p>

      <?php if (isset($_GET['error'])): ?>
        <div style="color: red; margin-bottom: 10px;">
          <?= htmlspecialchars($_GET['error']); ?>
        </div>
      <?php endif; ?>

      <form id="registerForm" method="POST" action="register_process.php">
        <div class="form-group">
          <input type="text" class="form-input" name="name" placeholder="full name" required value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
        </div>

        <div class="form-group">
        <input type="email" class="form-input" name="email" placeholder="email address" required value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
        </div>

        <div class="form-group">
        <input type="text" class="form-input" name="nik" placeholder="NIK" required value="<?= htmlspecialchars($_GET['nik'] ?? '') ?>">
        </div>

        <div class="form-group">
          <input type="text" class="form-input" name="phone" placeholder="phone number" required value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
        </div>

        <div class="form-group">
          <input type="password" name="password" placeholder="Password" required />
        </div>

        <div class="form-group">
          <input type="password" name="confirm_password" placeholder="Confirm Password" required />
        </div>

        <div class="form-group">
          <select name="role" required>
            <option value="" disabled selected>Select Role</option>
            <option value="admin">Admin</option>
            <option value="employee">Employee</option>
            <option value="manager">Manager</option>
          </select>
        </div>

        <button type="submit" class="register-btn">Register</button>
      </form>

      <p class="login-link">
        Already have an account? <a href="login.php">Login here</a>
      </p>
    </div>
  </div>
  <script src="js/register.js"></script>
</body>
</html>
