<?php $pageTitle = 'Terminet e Mia'; ?>

<section class="grid-2">
    <article class="panel-card">
        <p class="eyebrow">Rezervo termin</p>
        <h2>Kerkese e re per <?= e($patient['full_name'] ?? 'pacientin'); ?></h2>
        <form action="/patient/appointments" class="form-grid" method="post">
            <?= csrf_field(); ?>
            <label><span>Doktori</span><select name="doctor_id"><?php foreach ($doctors as $doctor): ?><option value="<?= e((string) $doctor['id']); ?>"><?= e($doctor['full_name'] . ' / ' . $doctor['specialization']); ?></option><?php endforeach; ?></select></label>
            <label><span>Sherbimi</span><select name="service_id"><?php foreach ($services as $service): ?><option value="<?= e((string) $service['id']); ?>"><?= e($service['name']); ?></option><?php endforeach; ?></select></label>
            <label><span>Koha</span><input name="scheduled_for" type="datetime-local"></label>
            <label><span>Shenime</span><input name="notes" type="text" value="<?= e(old('notes')); ?>"></label>
            <button class="button button-primary" type="submit">Dergo kerkesen</button>
        </form>
    </article>
    <article class="panel-card">
        <div class="panel-toolbar">
            <div>
                <p class="eyebrow">Historiku</p>
                <h2>Terminet ekzistuese</h2>
            </div>
            <form action="/patient/appointments" class="filter-form" method="get">
                <select name="status">
                    <option value="">Te gjitha statuset</option>
                    <option value="requested" <?= $selectedStatus === 'requested' ? 'selected' : ''; ?>>requested</option>
                    <option value="scheduled" <?= $selectedStatus === 'scheduled' ? 'selected' : ''; ?>>scheduled</option>
                    <option value="confirmed" <?= $selectedStatus === 'confirmed' ? 'selected' : ''; ?>>confirmed</option>
                    <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
                    <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : ''; ?>>completed</option>
                </select>
                <button class="button button-secondary" type="submit">Filtro</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Sherbimi</th><th>Doktori</th><th>Koha</th><th>Statusi</th><th>Veprimi</th></tr></thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= e($appointment['service_name']); ?></td>
                            <td><?= e($appointment['doctor_name']); ?></td>
                            <td><?= e(format_date($appointment['scheduled_for'])); ?></td>
                            <td><span class="<?= e(status_class($appointment['status'])); ?>"><?= e($appointment['status']); ?></span></td>
                            <td>
                                <?php if (in_array($appointment['status'], ['requested', 'scheduled', 'confirmed'], true)): ?>
                                    <form action="/patient/appointments/status" class="table-form" method="post">
                                        <?= csrf_field(); ?>
                                        <input name="appointment_id" type="hidden" value="<?= e((string) $appointment['id']); ?>">
                                        <input name="status" type="hidden" value="cancelled">
                                        <button class="button button-secondary" type="submit">Anulo</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">Nuk kerkohet</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>
</section>
<?php if (isset($paginator)): ?>
    <?php require base_path('app/Views/partials/pagination.php'); ?>
<?php endif; ?>
