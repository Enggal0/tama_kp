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
          <label for="fullname" class="form-label">Full Name</label>
          <input type="text" class="form-input" name="name" required value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="email" class="form-label">Email</label>
        <input type="email" class="form-input" name="email" required value="<?= htmlspecialchars($_GET['email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="NIK" class="form-label">NIK</label>
        <input type="text" class="form-input" name="nik" required value="<?= htmlspecialchars($_GET['nik'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="phonenumber" class="form-label">Phone Number</label>
          <input type="text" class="form-input" name="phone" required value="<?= htmlspecialchars($_GET['phone'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="password" class="form-label">Password</label>
          <input type="password" name="password" required />
        </div>

        <div class="form-group">
          <label for="confirmpassword" class="form-label">Confirm Password</label>
          <input type="password" name="confirm_password" required />
        </div>

        <div class="form-group">
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
