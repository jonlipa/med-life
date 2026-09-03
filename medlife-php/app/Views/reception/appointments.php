<?php $pageTitle = 'Terminet'; ?>

<section class="grid-2">
    <article class="panel-card">
        <p class="eyebrow">Rezervo termin</p>
        <h2>Krijo takim te ri</h2>
        <form action="/reception/appointments" class="form-grid form-grid-2" method="post">
            <?= csrf_field(); ?>
            <label><span>Pacienti</span><select name="patient_id"><?php foreach ($patients as $patient): ?><option value="<?= e((string) $patient['id']); ?>"><?= e(($patient['full_name'] ?? $patient['medical_record_number']) . ' / ' . $patient['medical_record_number']); ?></option><?php endforeach; ?></select></label>
            <label><span>Doktori</span><select name="doctor_id"><?php foreach ($doctors as $doctor): ?><option value="<?= e((string) $doctor['id']); ?>"><?= e($doctor['full_name']); ?></option><?php endforeach; ?></select></label>
            <label><span>Sherbimi</span><select name="service_id"><?php foreach ($services as $service): ?><option value="<?= e((string) $service['id']); ?>"><?= e($service['name']); ?></option><?php endforeach; ?></select></label>
            <label><span>Koha</span><input name="scheduled_for" type="datetime-local"></label>
            <label><span>Statusi</span><select name="status"><option value="scheduled">scheduled</option><option value="requested">requested</option></select></label>
            <label><span>Lokacioni</span><input name="location" type="text" value="<?= e(old('location', 'Qendra Med Life')); ?>"></label>
            <label class="span-2"><span>Shenime</span><textarea name="notes" rows="3"><?= e(old('notes')); ?></textarea></label>
            <div class="span-2"><button class="button button-primary" type="submit">Ruaj terminin</button></div>
        </form>
    </article>
    <article class="panel-card">
        <div class="panel-toolbar">
            <div>
                <p class="eyebrow">Tabela operative</p>
                <h2>Lista e termineve</h2>
            </div>
            <form action="/reception/appointments" class="filter-form" method="get">
                <select name="status">
                    <option value="">Te gjitha statuset</option>
                    <option value="requested" <?= $selectedStatus === 'requested' ? 'selected' : ''; ?>>requested</option>
                    <option value="scheduled" <?= $selectedStatus === 'scheduled' ? 'selected' : ''; ?>>scheduled</option>
                    <option value="confirmed" <?= $selectedStatus === 'confirmed' ? 'selected' : ''; ?>>confirmed</option>
                    <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : ''; ?>>completed</option>
                    <option value="cancelled" <?= $selectedStatus === 'cancelled' ? 'selected' : ''; ?>>cancelled</option>
                </select>
                <button class="button button-secondary" type="submit">Filtro</button>
            </form>
        </div>
        <div class="table-wrap">
            <table class="data-table">
                <thead><tr><th>Pacienti</th><th>Doktori</th><th>Koha</th><th>Statusi</th><th>Veprimi</th></tr></thead>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr>
                            <td><?= e($appointment['patient_name'] ?? $appointment['medical_record_number']); ?></td>
                            <td><?= e($appointment['doctor_name']); ?></td>
                            <td><?= e(format_date($appointment['scheduled_for'])); ?></td>
                            <td><span class="<?= e(status_class($appointment['status'])); ?>"><?= e($appointment['status']); ?></span></td>
                            <td>
                                <form action="/reception/appointments/status" class="table-form" method="post">
                                    <?= csrf_field(); ?>
                                    <input name="appointment_id" type="hidden" value="<?= e((string) $appointment['id']); ?>">
                                    <select name="status">
                                        <option value="requested" <?= $appointment['status'] === 'requested' ? 'selected' : ''; ?>>requested</option>
                                        <option value="scheduled" <?= $appointment['status'] === 'scheduled' ? 'selected' : ''; ?>>scheduled</option>
                                        <option value="confirmed" <?= $appointment['status'] === 'confirmed' ? 'selected' : ''; ?>>confirmed</option>
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
<?php if (isset($paginator)): ?>
    <?php require base_path('app/Views/partials/pagination.php'); ?>
<?php endif; ?>
