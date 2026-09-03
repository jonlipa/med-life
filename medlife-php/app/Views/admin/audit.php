<?php $pageTitle = 'Audit Trail'; ?>

<section class="panel-card">
    <p class="eyebrow">Audit</p>
    <h2>Ngjarjet e plota operative</h2>
    <div class="table-wrap mt-24">
        <table class="data-table">
            <thead><tr><th>Koha</th><th>Aktori</th><th>Veprimi</th><th>Objekti</th><th>Prioriteti</th></tr></thead>
            <tbody>
                <?php foreach ($audit as $event): ?>
                    <tr>
                        <td><?= e(format_date($event['created_at'])); ?></td>
                        <td><?= e($event['actor_name']); ?></td>
                        <td><?= e($event['action_text']); ?></td>
                        <td><?= e($event['target_text']); ?></td>
                        <td><span class="<?= e(severity_class($event['severity'])); ?>"><?= e($event['severity']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if (isset($paginator)): ?>
        <?php require base_path('app/Views/partials/pagination.php'); ?>
    <?php endif; ?>
</section>
