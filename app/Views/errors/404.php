<?php

/** @var string $message */ ?>
<div class="form-container" style="text-align:center;">
  <i class="fas fa-utensils" style="font-size:5rem;color:var(--primary);margin-bottom:20px;display:block;"></i>
  <h1 style="font-size:4rem;color:var(--primary);">404</h1>
  <h2><?= e($title ?? 'Page not found') ?></h2>
  <p style="color:var(--gray);margin:16px 0 24px;"><?= e($message ?: 'The page you are looking for could not be found.') ?></p>
  <a href="<?= e(url('/')) ?>" class="btn btn-primary" style="display:inline-flex;"><i class="fas fa-home"></i> Go Home</a>
</div>