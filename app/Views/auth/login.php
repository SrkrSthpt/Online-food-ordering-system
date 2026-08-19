<?php
$errors = \App\Core\Session::get('_errors', []);
$first = fn (string $field): string => is_array($errors[$field] ?? null) ? (string)$errors[$field][0] : '';
?>
<div class="form-container">
  <h2>Welcome Back</h2>
  <p class="subtitle">Sign in to continue ordering</p>

  <?php if (flash('error')) : ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;">
      <i class="fas fa-exclamation-circle"></i> <?= e(flash('error')) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= e(route('auth.login.store')) ?>" data-validate>
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required placeholder="your@email.com" value="<?= e(old('email')) ?>">
      <div class="error-text"><?= e($first('email')) ?></div>
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
    Don't have an account? <a href="<?= e(route('auth.register')) ?>">Sign Up</a>
  </div>
</div>