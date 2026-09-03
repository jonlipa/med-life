<?php $pageTitle = 'Njoftimet'; ?>

<section class="panel-card">
    <div class="panel-toolbar">
        <div>
            <p class="eyebrow">Njoftimet</p>
            <h2>Inbox i pacientit</h2>
        </div>
        <form action="/patient/notifications" method="post">
            <?= csrf_field(); ?>
            <input name="action" type="hidden" value="mark_all">
            <button class="button button-secondary" type="submit">Sheno te gjitha si te lexuara</button>
        </form>
    </div>
    <div class="stack-cards mt-24">
        <?php foreach ($notifications as $notification): ?>
            <form action="/patient/notifications" class="line-item line-item-start panel-form" method="post">
                <?= csrf_field(); ?>
                <input name="notification_id" type="hidden" value="<?= e((string) $notification['id']); ?>">
                <div>
                    <strong><?= e($notification['title']); ?></strong>
                    <p><?= e($notification['message']); ?></p>
                    <small><?= e(format_date($notification['created_at'])); ?></small>
                </div>
                <?php if (!$notification['is_read']): ?>
                    <button class="button button-secondary" type="submit">Sheno si lexuar</button>
                <?php else: ?>
                    <span class="<?= e(status_class('read')); ?>">lexuar</span>
                <?php endif; ?>
            </form>
        <?php endforeach; ?>
    </div>
</section>
