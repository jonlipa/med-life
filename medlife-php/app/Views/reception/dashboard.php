<?php
$pageTitle = 'Paneli i Recepsionit';
$heroImage = asset('images/reception-hero-reference.png');
$queueItems = array_slice(is_array($queue ?? null) ? $queue : [], 0, 2);
$billingItems = array_slice(is_array($billings ?? null) ? $billings : [], 0, 3);
$icon = static function (string $name): string {
    $icons = [
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v4m10-4v4M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm4 7h6m-6 4h4"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.8M16 3.2a4 4 0 0 1 0 7.6"/></svg>',
        'heart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 6.6a5 5 0 0 0-7.1 0L12 7.7l-1.1-1.1a5 5 0 1 0-7.1 7.1L12 22l8.2-8.3a5 5 0 0 0 0-7.1Z"/><path d="M4 12h4l2-4 3 8 2-4h5"/></svg>',
        'report' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l3 3v15H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/></svg>',
        'trend' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m4 16 5-5 4 4 7-8"/><path d="M14 7h6v6"/></svg>',
    ];

    return $icons[$name] ?? $icons['calendar'];
};
?>

<section class="reference-dashboard reference-dashboard--reception">
    <section class="reference-hero reference-hero--teal">
        <div class="reference-hero-copy">
            <p class="eyebrow">Recepsioni</p>
            <h2>Front desk i sinkronizuar per intake, queue dhe faturim.</h2>
            <p>Forma e rezervimit, lista e pritjes dhe faturat po jetojne ne te njejtin ekran pune si ne mockup-in reference.</p>
        </div>
        <div class="reference-hero-media reference-hero-media--reception">
            <img alt="Recepsioni Med Life" src="<?= e($heroImage); ?>">
        </div>
    </section>

    <section class="reference-grid reference-grid--two reference-grid--reception-main">
        <article class="reference-card">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Rezervo Termin</p>
                    <h2>Krijo takim te ri</h2>
                </div>
                <a class="reference-button" href="/reception/appointments"><?= $icon('calendar'); ?> Kalendari</a>
            </div>
            <form action="/reception/appointments" class="reference-form reference-form--two" method="post">
                <?= csrf_field(); ?>
                <label>
                    <span>Pacienti</span>
                    <select name="patient_id">
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= e((string) $patient['id']); ?>"><?= e(($patient['full_name'] ?? $patient['medical_record_number']) . ' / ' . $patient['medical_record_number']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Doktori</span>
                    <select name="doctor_id">
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= e((string) $doctor['id']); ?>"><?= e($doctor['full_name'] . ' / ' . $doctor['specialization']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Sherbimi</span>
                    <select name="service_id">
                        <?php foreach ($services as $service): ?>
                            <option value="<?= e((string) $service['id']); ?>"><?= e($service['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>
                    <span>Koha</span>
                    <input name="scheduled_for" type="datetime-local">
                </label>
                <div class="span-2">
                    <button class="reference-primary" type="submit">Rezervo Terminin</button>
                </div>
            </form>
        </article>

        <article class="reference-card">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Lista e Pritjes</p>
                    <h2>Pacientet ne pritje</h2>
                </div>
                <a class="reference-button" href="/reception/queue"><?= $icon('users'); ?> Menaxho queue</a>
            </div>
            <div class="reference-list reference-list--queue">
                <?php foreach ($queueItems as $entry): ?>
                    <div class="reference-list-row">
                        <div>
                            <strong><?= e($entry['medical_record_number']); ?></strong>
                            <p><?= e($entry['reason_for_visit']); ?> / <?= e($entry['doctor_name'] ?? 'Pa doktor'); ?></p>
                        </div>
                        <span class="reference-status reference-status--<?= e((string) $entry['status']); ?>"><?= e((string) $entry['status']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="reference-grid reference-grid--two reference-grid--lower">
        <article class="reference-card reference-card--short">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Billing</p>
                    <h2>Faturat aktive</h2>
                </div>
                <a class="reference-button" href="/admin/reports"><?= $icon('report'); ?> Raportet</a>
            </div>
            <div class="reference-list">
                <?php foreach ($billingItems as $billing): ?>
                    <div class="reference-list-row reference-list-row--compact">
                        <span><?= e($billing['patient_name'] ?? $billing['medical_record_number']); ?></span>
                        <strong><?= e(money($billing['amount'])); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="reference-card reference-card--short">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Ritmi i Dites</p>
                    <h2>Fluksi i front desk</h2>
                </div>
                <span class="reference-button"><?= $icon('trend'); ?> Live desk</span>
            </div>
        </article>
    </section>
</section>
