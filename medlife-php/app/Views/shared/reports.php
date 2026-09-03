<?php $pageTitle = 'Raportet'; ?>

<section class="metrics-grid">
    <article class="metric-card"><p>Konsulta</p><strong><?= e((string) $metrics['appointments']); ?></strong></article>
    <article class="metric-card"><p>Lab</p><strong><?= e((string) $metrics['lab_results']); ?></strong></article>
    <article class="metric-card"><p>Fatura aktive</p><strong><?= e((string) $metrics['active_bills']); ?></strong></article>
</section>

<section class="grid-2 mt-24">
    <article class="panel-card">
        <p class="eyebrow">Te ardhurat</p>
        <h2>Muajt e fundit</h2>
        <div class="stack-cards">
            <?php foreach ($metrics['revenue_monthly'] as $row): ?>
                <div class="line-item">
                    <span><?= e($row['month_key']); ?></span>
                    <strong><?= e(money($row['total'])); ?></strong>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="panel-card">
        <p class="eyebrow">Audit</p>
        <h2>Ngjarjet e fundit</h2>
        <div class="stack-cards">
            <?php foreach ($audit as $event): ?>
                <div class="line-item line-item-start">
                    <div>
                        <strong><?= e($event['action_text']); ?></strong>
                        <p><?= e($event['actor_name']); ?> / <?= e($event['target_text']); ?></p>
                    </div>
                    <span><?= e(format_date($event['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>

<section class="panel-card mt-24">
    <p class="eyebrow">Billing</p>
    <h2>Faturat me te fundit</h2>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Pacienti</th><th>Shuma</th><th>Statusi</th></tr></thead>
            <tbody>
                <?php foreach ($billings as $billing): ?>
                    <tr>
                        <td><?= e($billing['patient_name'] ?? $billing['medical_record_number']); ?></td>
                        <td><?= e(money($billing['amount'])); ?></td>
                        <td><span class="<?= e(status_class($billing['status'])); ?>"><?= e($billing['status']); ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
