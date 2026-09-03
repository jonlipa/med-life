<?php
$pageTitle = 'Paneli i Doktorit';
$heroImage = asset('images/doctor-hero-reference-panel.png');
$appointmentItems = is_array($appointments ?? null) ? $appointments : [];
$patientItems = array_slice(is_array($patients ?? null) ? $patients : [], 0, 2);
$activityItems = [
    ['label' => 'Kartelat e hapura sot', 'value' => 8, 'icon' => 'folder'],
    ['label' => 'Vizita te realizuara', 'value' => 4, 'icon' => 'users'],
    ['label' => 'Procedura te planifikuara', 'value' => 2, 'icon' => 'calendar'],
];
$icon = static function (string $name): string {
    $icons = [
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v4m10-4v4M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm4 7h6m-6 4h4"/></svg>',
        'folder' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h7l2 2h9v11H3z"/><path d="M8 14h8"/></svg>',
        'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.8M16 3.2a4 4 0 0 1 0 7.6"/></svg>',
    ];

    return $icons[$name] ?? $icons['calendar'];
};
?>

<section class="reference-dashboard reference-dashboard--doctor">
    <section class="reference-hero reference-hero--teal reference-hero--doctor">
        <div class="reference-hero-copy">
            <p class="eyebrow">Mirësevini në Dashboard</p>
            <h2>Mirë se vini. në panelin e doktorit.</h2>
            <p>Menaxhoni pacientët, oraret, kartelat dhe raportet tuaja në një vend të vetëm.</p>
        </div>
        <div class="reference-hero-media reference-hero-media--doctor">
            <img alt="<?= e($doctor['full_name'] ?? 'Doktor Med Life'); ?>" src="<?= e($heroImage); ?>">
        </div>
    </section>

    <section class="reference-grid reference-grid--two reference-grid--doctor-main">
        <article class="reference-card reference-card--empty">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Orari i Sotëm</p>
                    <h2>Terminet e ardhshme</h2>
                </div>
                <a class="reference-button" href="/doctor/availability">Menaxho agjenden</a>
            </div>
            <?php if ($appointmentItems === []): ?>
                <div class="reference-empty-state">
                    <span><?= $icon('calendar'); ?></span>
                    <strong>Nuk ka termine të planifikuara për sot.</strong>
                    <p>Shtoni një termin të ri në agjendën tuaj.</p>
                </div>
            <?php else: ?>
                <div class="reference-list">
                    <?php foreach (array_slice($appointmentItems, 0, 3) as $appointment): ?>
                        <div class="reference-list-row">
                            <div>
                                <strong><?= e($appointment['patient_name'] ?? $appointment['medical_record_number']); ?></strong>
                                <p><?= e($appointment['service_name']); ?> / <?= e(format_date($appointment['scheduled_for'])); ?></p>
                            </div>
                            <span class="reference-status reference-status--<?= e((string) $appointment['status']); ?>"><?= e((string) $appointment['status']); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="reference-card">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Pacientët</p>
                    <h2>Te caktuarit kryesore</h2>
                </div>
                <a class="reference-button" href="/doctor/patients">Shiko listen</a>
            </div>
            <div class="reference-list reference-list--patients">
                <?php foreach ($patientItems as $entry): ?>
                    <div class="reference-patient-row">
                        <img alt="<?= e($entry['full_name'] ?? $entry['medical_record_number']); ?>" loading="lazy" src="<?= e(asset('images/optimized/doctor-2.jpg')); ?>">
                        <div>
                            <strong><?= e($entry['full_name'] ?? $entry['medical_record_number']); ?></strong>
                            <p><?= e($entry['medical_record_number']); ?></p>
                        </div>
                        <a class="reference-button" href="/doctor/records?patient=<?= e((string) $entry['id']); ?>">Kartela</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="reference-grid reference-grid--two reference-grid--doctor-lower">
        <article class="reference-card reference-card--chart">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Ritmi Klinik</p>
                    <h2>Ritmi i dites</h2>
                </div>
                <span class="reference-button">Dhoma <?= e($doctor['room'] ?? 'B-03'); ?></span>
            </div>
            <div class="reference-chart reference-chart--compact">
                <?php foreach ([24, 38, 25, 52, 36, 24, 47, 60, 36, 72, 78, 98] as $index => $height): ?>
                    <div class="reference-chart-column">
                        <span></span>
                        <small><?= e(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'][$index]); ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>

        <article class="reference-card reference-card--activity">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Aktiviteti</p>
                    <h2>Ndryshimet operative</h2>
                </div>
                <a class="reference-button" href="/doctor/records">Hap kartelat</a>
            </div>
            <div class="reference-list reference-list--activity">
                <?php foreach ($activityItems as $item): ?>
                    <div class="reference-activity-row">
                        <span><?= $icon($item['icon']); ?></span>
                        <p><?= e($item['label']); ?></p>
                        <strong><?= e((string) $item['value']); ?></strong>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</section>
