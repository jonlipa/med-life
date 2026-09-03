<?php if ($flash = flash('flash')): ?>
    <div class="alert alert-<?= e($flash['type'] ?? 'info'); ?>">
        <?= e($flash['message'] ?? 'Njoftim sistemi.'); ?>
    </div>
<?php endif; ?>
