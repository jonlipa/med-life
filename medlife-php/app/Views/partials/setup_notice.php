<?php $setup = setup_view_data(); ?>
<div class="panel-subtle">
    <strong>Portal ne setup mode</strong>
    <p class="muted"><?= e($setup['setup_message']); ?></p>
    <p class="muted">
        Host: <?= e($setup['db_host']); ?> |
        Port: <?= e($setup['db_port']); ?> |
        DB: <?= e($setup['db_name']); ?>
    </p>
</div>
