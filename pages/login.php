<?php require_once __DIR__ . '/../includes/db.php';
if (isAuthenticated()) redirect('/index.php');

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = sanitize($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';

  if (!empty($email) && !empty($password)) {
    $result = dbQuery("SELECT * FROM users WHERE email = ?", [$email]);
    if ($result && $user = $result->fetch_assoc()) {
      if (password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        redirect('/index.php');
      } else {
        $error = 'Invalid email or password.';
      }
    } else {
      $error = 'Invalid email or password.';
    }
  } else {
    $error = 'Please fill in all fields.';
  }
}
?>
<?php include __DIR__ . '/../includes/header.php'; ?>

<div class="form-container">
  <h2>Welcome Back</h2>
  <p class="subtitle">Sign in to continue ordering</p>

  <?php if ($error): ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;">
      <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="" data-validate>
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required placeholder="your@email.com">
      <div class="error-text">Please enter a valid email</div>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="Enter your password">
      <div class="error-text">Password must be at least 6 characters</div>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
      <i class="fas fa-sign-in-alt"></i> Sign In
    </button>
  </form>

  <div class="form-footer">
    Don't have an account? <a href="signup.php">Sign Up</a>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
