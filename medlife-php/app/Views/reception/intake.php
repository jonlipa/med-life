<?php $pageTitle = 'Intake'; ?>

<section class="grid-2">
    <article class="panel-card">
        <p class="eyebrow">Pacient i ri</p>
        <h2>Shto ne intake</h2>
        <form action="/reception/intake" class="form-grid form-grid-2" method="post">
            <?= csrf_field(); ?>
            <label><span>Email</span><input name="email" type="email" value="<?= e(old('email')); ?>"></label>
            <label><span>Telefoni</span><input name="phone" type="text" value="<?= e(old('phone')); ?>"></label>
            <label><span>Data e lindjes</span><input name="date_of_birth" type="date" value="<?= e(old('date_of_birth')); ?>"></label>
            <label><span>Doktori</span><select name="current_doctor_id"><?php foreach ($doctors as $doctor): ?><option value="<?= e((string) $doctor['id']); ?>"><?= e($doctor['full_name']); ?></option><?php endforeach; ?></select></label>
            <label class="span-2"><span>Adresa</span><input name="address" type="text" value="<?= e(old('address')); ?>"></label>
            <label class="span-2"><span>Kontakt emergjent</span><input name="emergency_contact" type="text" value="<?= e(old('emergency_contact')); ?>"></label>
            <label><span>Arsyeja e vizites</span><input name="reason_for_visit" type="text" value="<?= e(old('reason_for_visit')); ?>"></label>
            <label><span>Statusi</span><select name="status"><option value="new">new</option><option value="scheduled">scheduled</option></select></label>
            <label><span>Sigurimi</span><input name="insurance_provider" type="text" value="<?= e(old('insurance_provider')); ?>"></label>
            <label><span>Grupi i gjakut</span><input name="blood_type" type="text" value="<?= e(old('blood_type')); ?>"></label>
            <label class="span-2"><span>Shenime intake</span><textarea name="intake_notes" rows="4"><?= e(old('intake_notes')); ?></textarea></label>
            <div class="span-2"><button class="button button-primary" type="submit">Ruaj intake</button></div>
        </form>
    </article>
    <article class="panel-card">
        <div class="panel-toolbar">
            <div>
                <p class="eyebrow">Lista aktuale</p>
                <h2>Queue snapshot</h2>
            </div>
            <form action="/reception/intake" class="filter-form" method="get">
                <select name="status">
                    <option value="">Te gjitha statuset</option>
                    <option value="new" <?= $selectedStatus === 'new' ? 'selected' : ''; ?>>new</option>
                    <option value="scheduled" <?= $selectedStatus === 'scheduled' ? 'selected' : ''; ?>>scheduled</option>
                    <option value="in_progress" <?= $selectedStatus === 'in_progress' ? 'selected' : ''; ?>>in_progress</option>
                    <option value="completed" <?= $selectedStatus === 'completed' ? 'selected' : ''; ?>>completed</option>
                </select>
                <button class="button button-secondary" type="submit">Filtro</button>
            </form>
        </div>
        <div class="stack-cards">
            <?php foreach ($queue as $entry): ?>
                <div class="line-item line-item-start">
                    <div>
                        <strong><?= e($entry['medical_record_number']); ?></strong>
                        <p><?= e($entry['reason_for_visit']); ?></p>
                    </div>
                    <div class="table-actions">
                        <span class="<?= e(status_class($entry['status'])); ?>"><?= e($entry['status']); ?></span>
                        <span class="muted"><?= e($entry['doctor_name'] ?? 'Pa doktor'); ?></span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
