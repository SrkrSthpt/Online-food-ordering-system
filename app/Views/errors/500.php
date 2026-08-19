<?php /** @var string $message */ ?>
<div class="form-container" style="text-align:center;">
  <i class="fas fa-exclamation-triangle" style="font-size:5rem;color:var(--warning);margin-bottom:20px;display:block;"></i>
  <h1 style="font-size:4rem;color:var(--danger);">500</h1>
  <h2><?= e($title ?? 'Something went wrong') ?></h2>
  <p style="color:var(--gray);margin:16px 0 24px;"><?= e($message ?: 'An unexpected error occurred. Please try again.') ?></p>
  <?php if (!empty($exception)) : ?>
    <pre style="text-align:left;background:var(--light);padding:16px;border-radius:8px;overflow:auto;font-size:0.75rem;color:var(--danger);"><?= e((string)$exception) ?></pre>
  <?php endif; ?>
  <a href="<?= e(url('/')) ?>" class="btn btn-primary" style="justify-content:center;"><i class="fas fa-home"></i> Go Home</a>
</div>