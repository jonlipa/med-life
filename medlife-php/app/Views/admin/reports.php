<?php $pageTitle = 'Raportet'; ?>

<section class="metrics-grid">
    <article class="metric-card"><p>Konsulta totale</p><strong><?= e((string) $metrics['appointments']); ?></strong></article>
    <article class="metric-card"><p>Rezultate laboratorike</p><strong><?= e((string) $metrics['lab_results']); ?></strong></article>
    <article class="metric-card"><p>Fatura aktive</p><strong><?= e((string) $metrics['active_bills']); ?></strong></article>
    <article class="metric-card"><p>Te ardhura</p><strong><?= e(money($billingSummary['total'])); ?></strong></article>
</section>

<section class="panel-card mt-24">
    <div class="panel-toolbar">
        <div>
            <p class="eyebrow">Billing control</p>
            <h2>Filtro dhe perditeso faturimin</h2>
        </div>
        <form action="/admin/reports" class="filter-form" method="get">
            <select name="status">
                <option value="">Te gjitha statuset</option>
                <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : ''; ?>>pending</option>
                <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : ''; ?>>paid</option>
                <option value="overdue" <?= $selectedStatus === 'overdue' ? 'selected' : ''; ?>>overdue</option>
            </select>
            <button class="button button-secondary" type="submit">Filtro</button>
        </form>
    </div>
    <div class="table-wrap mt-24">
        <table class="data-table">
            <thead><tr><th>Pacienti</th><th>Shuma</th><th>Statusi</th><th>Veprimi</th></tr></thead>
            <tbody>
                <?php foreach ($billings as $billing): ?>
                    <tr>
                        <td><?= e($billing['patient_name'] ?? $billing['medical_record_number']); ?></td>
                        <td><?= e(money($billing['amount'])); ?></td>
                        <td><span class="<?= e(status_class($billing['status'])); ?>"><?= e($billing['status']); ?></span></td>
                        <td>
                            <form action="/admin/reports" class="table-form" method="post">
                                <?= csrf_field(); ?>
                                <input name="billing_id" type="hidden" value="<?= e((string) $billing['id']); ?>">
                                <select name="status">
                                    <option value="pending" <?= $billing['status'] === 'pending' ? 'selected' : ''; ?>>pending</option>
                                    <option value="paid" <?= $billing['status'] === 'paid' ? 'selected' : ''; ?>>paid</option>
                                    <option value="overdue" <?= $billing['status'] === 'overdue' ? 'selected' : ''; ?>>overdue</option>
                                </select>
                                <button class="button button-secondary" type="submit">Ruaj</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="grid-2 mt-24">
    <article class="panel-card">
        <p class="eyebrow">Muajt e fundit</p>
        <h2>Pasqyra mujore e te ardhurave</h2>
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
        <h2>Eventet operative</h2>
        <div class="timeline compact">
            <?php foreach ($audit as $event): ?>
                <article class="timeline-item">
                    <h3><?= e($event['action_text']); ?></h3>
                    <p><?= e($event['actor_name']); ?> / <?= e(format_date($event['created_at'])); ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </article>
</section>
