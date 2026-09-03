<?php $pageTitle = 'Kartelat Klinike'; ?>

<section class="grid-2">
    <article class="panel-card">
        <div class="panel-toolbar">
            <div>
                <p class="eyebrow">Pacienti aktiv</p>
                <h2><?= e($selectedPatient['full_name'] ?? $selectedPatient['medical_record_number'] ?? 'Zgjidh pacientin'); ?></h2>
            </div>
            <form action="/doctor/records" class="filter-form" method="get">
                <select name="patient">
                    <?php foreach ($patients as $patient): ?>
                        <option value="<?= e((string) $patient['id']); ?>" <?= ($selectedPatient['id'] ?? null) === $patient['id'] ? 'selected' : ''; ?>>
                            <?= e(($patient['full_name'] ?? $patient['medical_record_number']) . ' / ' . $patient['medical_record_number']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button class="button button-secondary" type="submit">Ngarko</button>
            </form>
        </div>
        <?php if ($selectedPatient): ?>
            <div class="stack-cards">
                <div class="line-item"><span>MRN</span><strong><?= e($selectedPatient['medical_record_number']); ?></strong></div>
                <div class="line-item"><span>Doktori</span><strong><?= e($selectedPatient['doctor_name'] ?? 'Pa doktor'); ?></strong></div>
                <div class="line-item"><span>Sigurimi</span><strong><?= e($selectedPatient['insurance_provider']); ?></strong></div>
            </div>
        <?php endif; ?>
        <div class="table-wrap mt-24">
            <table class="data-table">
                <thead><tr><th>Pacienti</th><th>Diagnoza</th><th>Koha</th></tr></thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?= e($record['patient_name'] ?? $record['medical_record_number']); ?></td>
                            <td><?= e($record['diagnosis_summary']); ?></td>
                            <td><?= e(format_date($record['updated_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </article>

    <article class="panel-card">
        <p class="eyebrow">Veprime klinike</p>
        <h2>Perditeso kartelen</h2>
        <?php if ($selectedPatient): ?>
            <form action="/doctor/records" class="form-grid" method="post">
                <?= csrf_field(); ?>
                <input name="patient_id" type="hidden" value="<?= e((string) $selectedPatient['id']); ?>">
                <input name="action" type="hidden" value="record">
                <label><span>Diagnoza</span><input name="diagnosis_summary" type="text"></label>
                <label><span>Alergji</span><input name="allergies" type="text"></label>
                <label class="span-2"><span>Shenime klinike</span><textarea name="clinical_notes" rows="4"></textarea></label>
                <button class="button button-primary" type="submit">Ruaj kartelen</button>
            </form>

            <form action="/doctor/records" class="form-grid mt-24" method="post">
                <?= csrf_field(); ?>
                <input name="patient_id" type="hidden" value="<?= e((string) $selectedPatient['id']); ?>">
                <input name="action" type="hidden" value="prescription">
                <label><span>Medikamenti</span><input name="medication_name" type="text"></label>
                <label><span>Instruksionet</span><input name="instructions" type="text"></label>
                <button class="button button-secondary" type="submit">Shto recete</button>
            </form>

            <form action="/doctor/records" class="form-grid mt-24" method="post">
                <?= csrf_field(); ?>
                <input name="patient_id" type="hidden" value="<?= e((string) $selectedPatient['id']); ?>">
                <input name="action" type="hidden" value="lab">
                <label><span>Testi</span><input name="test_name" type="text"></label>
                <label><span>Statusi</span>
                    <select name="status">
                        <option value="in_progress">in_progress</option>
                        <option value="completed">completed</option>
                    </select>
                </label>
                <label class="span-2"><span>Permbledhja e rezultatit</span><textarea name="result_summary" rows="3"></textarea></label>
                <button class="button button-secondary" type="submit">Shto rezultat</button>
            </form>
        <?php else: ?>
            <p class="muted">Nuk ka pacient te zgjedhur.</p>
        <?php endif; ?>
    </article>
</section>

<section class="grid-2 mt-24">
    <article class="panel-card">
        <p class="eyebrow">Recetat</p>
        <div class="stack-cards">
            <?php foreach ($prescriptions as $prescription): ?>
                <div class="line-item line-item-start">
                    <div>
                        <strong><?= e($prescription['medication_name']); ?></strong>
                        <p><?= e($prescription['instructions']); ?></p>
                    </div>
                    <span><?= e(format_date($prescription['created_at'])); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <article class="panel-card">
        <p class="eyebrow">Rezultatet</p>
        <div class="stack-cards">
            <?php foreach ($results as $result): ?>
                <div class="line-item line-item-start">
                    <div>
                        <strong><?= e($result['name']); ?></strong>
                        <p><?= e($result['result_summary']); ?></p>
                    </div>
                    <span class="<?= e(status_class($result['status'])); ?>"><?= e($result['status']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
</section>
