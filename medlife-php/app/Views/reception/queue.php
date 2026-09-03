<?php $pageTitle = 'Lista e Pritjes'; ?>

<section class="panel-card">
    <div class="panel-toolbar">
        <div>
            <p class="eyebrow">Queue</p>
            <h2>Intake dhe pritja</h2>
        </div>
        <form action="/reception/queue" class="filter-form" method="get">
            <select name="status">
                <option value="">Te gjitha statuset</option>
                <option value="new" <?= $selectedStatus === 'new' ? 'selected' : ''; ?>>new</option>
                <option value="scheduled" <?= $selectedStatus === 'scheduled' ? 'selected' : ''; ?>>scheduled</option>
                <option value="in_progress" <?= $selectedStatus === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
                <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : ''; ?>>completed</option>
                <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
            </select>
            <button class="button button-secondary" type="submit">Filtro</button>
        </form>
    </div>
    <div class="table-wrap mt-24">
        <table class="data-table">
            <thead><tr><th>MRN</th><th>Email</th><th>Arsyeja</th><th>Doktori</th><th>Statusi</th><th>Perditesuar</th><th>Veprimi</th></tr></thead>
            <tbody>
                <?php foreach ($queue as $entry): ?>
                    <tr>
                        <td><?= e($entry['medical_record_number']); ?></td>
                        <td><?= e($entry['email']); ?></td>
                        <td><?= e($entry['reason_for_visit']); ?></td>
                        <td><?= e($entry['doctor_name'] ?? 'Pa doktor'); ?></td>
                        <td><span class="<?= e(status_class($entry['status'])); ?>"><?= e($entry['status']); ?></span></td>
                        <td><?= e(format_date($entry['updated_at'])); ?></td>
                        <td>
                            <form action="/reception/queue" class="table-form" method="post">
                                <?= csrf_field(); ?>
                                <input name="intake_id" type="hidden" value="<?= e((string) $entry['id']); ?>">
                                <select name="status">
                                    <option value="new" <?= $entry['status'] === 'new' ? 'selected' : ''; ?>>new</option>
                                    <option value="scheduled" <?= $entry['status'] === 'scheduled' ? 'selected' : ''; ?>>scheduled</option>
                                    <option value="in_progress" <?= $entry['status'] === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
                                    <option value="completed" <?= $entry['status'] === 'completed' ? 'selected' : ''; ?>>completed</option>
                                    <option value="cancelled" <?= $entry['status'] === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
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
