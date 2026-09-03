<?php $pageTitle = 'Disponueshmeria'; ?>

<section class="grid-2">
    <article class="panel-card">
        <p class="eyebrow">Disponueshmeria</p>
        <h2>Perditeso orarin</h2>
        <form action="/doctor/availability" class="form-grid" method="post">
            <?= csrf_field(); ?>
            <label><span>Orari i dukshem</span><input name="availability_text" type="text" value="<?= e($doctor['availability_text'] ?? ''); ?>"></label>
            <label class="span-2"><span>Shenime</span><textarea name="availability_notes" rows="4"><?= e($doctor['availability_notes'] ?? ''); ?></textarea></label>
            <button class="button button-primary" type="submit">Ruaj disponueshmerine</button>
        </form>
    </article>

    <article class="panel-card">
        <div class="panel-toolbar">
            <div>
                <p class="eyebrow">Terminet</p>
                <h2>Agjenda e filtruar</h2>
            </div>
            <form action="/doctor/availability" class="filter-form" method="get">
                <select name="status">
                    <option value="">Te gjitha statuset</option>
                    <option value="confirmed" <?= $selectedStatus === 'confirmed' ? 'selected' : ''; ?>>confirmed</option>
                    <option value="in_progress" <?= $selectedStatus === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
                    <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : ''; ?>>completed</option>
                    <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
                </select>
                <button class="button button-secondary" type="submit">Filtro</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Pacienti</th><th>Sherbimi</th><th>Koha</th><th>Statusi</th><th>Veprimi</th></tr></thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= e($appointment['patient_name'] ?? $appointment['medical_record_number']); ?></td>
                            <td><?= e($appointment['service_name']); ?></td>
                            <td><?= e(format_date($appointment['scheduled_for'])); ?></td>
                            <td><span class="<?= e(status_class($appointment['status'])); ?>"><?= e($appointment['status']); ?></span></td>
                            <td>
                                <form action="/doctor/appointments/status" class="table-form" method="post">
                                    <?= csrf_field(); ?>
                                    <input name="appointment_id" type="hidden" value="<?= e((string) $appointment['id']); ?>">
                                    <select name="status">
                                        <option value="confirmed" <?= $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>confirmed</option>
                                        <option value="in_progress" <?= $appointment['status'] === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
                                        <option value="completed" <?= $appointment['status'] === 'completed' ? 'selected' : ''; ?>>completed</option>
                                        <option value="cancelled" <?= $appointment['status'] === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
                                    </select>
                                    <button class="button button-secondary" type="submit">Ruaj</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
