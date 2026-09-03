<?php $pageTitle = 'Faturat'; ?>

<section class="metrics-grid">
    <article class="metric-card"><p>Total i klinikes</p><strong><?= e(money($summary['total'])); ?></strong></article>
    <article class="metric-card"><p>Te paguara</p><strong><?= e(money($summary['paid'])); ?></strong></article>
    <article class="metric-card"><p>Ne pritje</p><strong><?= e((string) $summary['pending']); ?></strong></article>
    <article class="metric-card"><p>Te vonuara</p><strong><?= e((string) $summary['overdue']); ?></strong></article>
</section>

<section class="panel-card mt-24">
    <div class="panel-toolbar">
        <div>
            <p class="eyebrow">Faturat</p>
            <h2>Lista e faturave</h2>
        </div>
        <form action="/patient/billing" class="filter-form" method="get">
            <select name="status">
                <option value="">Te gjitha statuset</option>
                <option value="pending" <?= $selectedStatus === 'pending' ? 'selected' : ''; ?>>pending</option>
                <option value="paid" <?= $selectedStatus === 'paid' ? 'selected' : ''; ?>>paid</option>
                <option value="overdue" <?= $selectedStatus === 'overdue' ? 'selected' : ''; ?>>overdue</option>
            </select>
            <button class="button button-secondary" type="submit">Filtro</button>
        </form>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>ID</th><th>Shuma</th><th>Statusi</th><th>Koha</th></tr></thead>
            <tbody>
                <?php foreach ($billings as $billing): ?>
                    <tr>
                        <td><?= e('INV-' . $billing['id']); ?></td>
                        <td><?= e(money($billing['amount'])); ?></td>
                        <td><span class="<?= e(status_class($billing['status'])); ?>"><?= e($billing['status']); ?></span></td>
                        <td><?= e(format_date($billing['issued_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
