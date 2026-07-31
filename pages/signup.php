<?php require_once __DIR__ . '/../includes/db.php';
if (isAuthenticated()) redirect('/index.php');

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = sanitize($_POST['name'] ?? '');
  $email = sanitize($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if (!empty($name) && !empty($email) && !empty($password)) {
    if ($password !== $confirm) {
      $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
      $error = 'Password must be at least 6 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = 'Please enter a valid email address.';
    } else {
      $check = dbQuery("SELECT id FROM users WHERE email = ?", [$email]);
      if ($check && $check->num_rows > 0) {
        $error = 'An account with this email already exists.';
      } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';
        $userId = dbInsert("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)",
          [$name, $email, $hashed, $role]);
        if ($userId) {
          $success = 'Account created successfully! You can now login.';
        } else {
          $error = 'Registration failed. Please try again.';
        }
      }
    }
  } else {
    $error = 'Please fill in all fields.';
  }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="form-container">
  <h2>Create Account</h2>
  <p class="subtitle">Join Bitezy and start ordering</p>

  <?php if ($error): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;">
      <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div style="background:#d4edda;color:#155724;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;">
      <i class="fas fa-check-circle"></i> <?php echo $success; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="" data-validate>
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required placeholder="John Doe">
      <div class="error-text">Please enter your name</div>
    </div>
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required placeholder="your@email.com">
      <div class="error-text">Please enter a valid email</div>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="Min 6 characters">
      <div class="error-text">Password must be at least 6 characters</div>
    </div>
    <div class="form-group">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
      <div class="error-text">Passwords must match</div>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
      <i class="fas fa-user-plus"></i> Create Account
    </button>
  </form>

  <div class="form-footer">
    Already have an account? <a href="login.php">Sign In</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
