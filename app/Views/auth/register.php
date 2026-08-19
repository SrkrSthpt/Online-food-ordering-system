<?php
$errors = \App\Core\Session::get('_errors', []);
$first = fn (string $field): string => is_array($errors[$field] ?? null) ? (string)$errors[$field][0] : '';
?>
<div class="form-container">
  <h2>Create Account</h2>
  <p class="subtitle">Join Bitezy and start ordering</p>

  <?php if (flash('error')) : ?>
    <div style="background:#f8d7da;color:#721c24;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;">
      <i class="fas fa-exclamation-circle"></i> <?= e(flash('error')) ?>
    </div>
  <?php endif; ?>
  <?php if (flash('success')) : ?>
    <div style="background:#d4edda;color:#155724;padding:12px 16px;border-radius:8px;margin-bottom:20px;font-size:0.9rem;">
      <i class="fas fa-check-circle"></i> <?= e(flash('success')) ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= e(route('auth.register.store')) ?>" data-validate>
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="name">Full Name</label>
      <input type="text" id="name" name="name" required placeholder="John Doe" value="<?= e(old('name')) ?>">
      <div class="error-text"><?= e($first('name')) ?></div>
    </div>
    <div class="form-group">
      <label for="email">Email Address</label>
      <input type="email" id="email" name="email" required placeholder="your@email.com" value="<?= e(old('email')) ?>">
      <div class="error-text"><?= e($first('email')) ?></div>
    </div>
    <div class="form-group">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required placeholder="Min 6 characters">
      <div class="error-text">Password must be at least 6 characters</div>
    </div>
    <div class="form-group">
      <label for="password_confirmation">Confirm Password</label>
      <input type="password" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password">
      <div class="error-text">Passwords must match</div>
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
      <i class="fas fa-user-plus"></i> Create Account
    </button>
  </form>

  <div class="form-footer">
    Already have an account? <a href="<?= e(route('auth.login')) ?>">Sign In</a>
  </div>
</div>