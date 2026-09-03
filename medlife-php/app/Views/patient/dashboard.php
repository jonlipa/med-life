<?php
$pageTitle = 'Paneli i Pacientit';
$heroImage = asset('images/patient-hero-reference.png');
$appointmentItems = is_array($appointments ?? null) ? $appointments : [];
$resultItems = is_array($results ?? null) ? $results : [];
$billingItems = is_array($billings ?? null) ? $billings : [];
$notificationItems = is_array($notifications ?? null) ? $notifications : [];
$prescriptionItems = is_array($prescriptions ?? null) ? $prescriptions : [];
$icon = static function (string $name): string {
    $icons = [
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v4m10-4v4M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm4 7h6m-6 4h4"/></svg>',
        'clipboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4h6l1 2h3v15H5V6h3z"/><path d="M9 12h6m-6 4h5"/></svg>',
        'card' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="M4 10h16"/></svg>',
        'bell' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/></svg>',
        'report' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h9l3 3v15H6z"/><path d="M14 3v4h4M9 12h6M9 16h6"/></svg>',
    ];

    return $icons[$name] ?? $icons['calendar'];
};
?>

<section class="reference-dashboard reference-dashboard--patient">
    <section class="reference-hero reference-hero--patient">
        <div class="reference-hero-copy">
            <h2>Mirë se vini në panelin e pacientit.</h2>
            <p>Menaxhoni terminet, shikoni rezultatet e analizave, monitoroni faturat dhe qëndroni të informuar për kujdesin tuaj shëndetësor.</p>
        </div>
        <div class="reference-hero-media reference-hero-media--patient">
            <img alt="<?= e($patient['full_name'] ?? 'Pacient Med Life'); ?>" src="<?= e($heroImage); ?>">
        </div>
    </section>

    <section class="reference-grid reference-grid--three reference-grid--patient-main">
        <article class="reference-card reference-card--empty">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Terminet</p>
                    <h2>Te ardhshmet</h2>
                </div>
                <a class="reference-button" href="/patient/appointments"><?= $icon('calendar'); ?> Menaxho</a>
            </div>
            <?php if ($appointmentItems === []): ?>
                <div class="reference-empty-state reference-empty-state--patient">
                    <span><?= $icon('calendar'); ?></span>
                    <strong>Nuk keni asnjë termin të planifikuar.</strong>
                    <p>Planifikoni një vizitë për të marrë kujdesin që meritoni.</p>
                    <a class="reference-primary" href="/patient/appointments">Planifiko një termin</a>
                </div>
            <?php else: ?>
                <div class="reference-list">
                    <?php foreach (array_slice($appointmentItems, 0, 2) as $appointment): ?>
                        <div class="reference-list-row">
                            <div>
                                <strong><?= e($appointment['service_name']); ?></strong>
                                <p><?= e($appointment['doctor_name']); ?></p>
                            </div>
                            <span><?= e(format_date($appointment['scheduled_for'])); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </article>

        <article class="reference-card">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Historia Mjekesore</p>
                    <h2>Recetat e mia</h2>
                </div>
                <a class="reference-button" href="/patient/results"><?= $icon('clipboard'); ?> Rezultatet</a>
            </div>
            <div class="reference-list">
                <?php foreach (array_slice($prescriptionItems, 0, 2) as $prescription): ?>
                    <div class="reference-list-row">
                        <div>
                            <strong><?= e($prescription['medication_name']); ?></strong>
                            <p><?= e($prescription['instructions']); ?></p>
                        </div>
                        <span><?= e(format_date($prescription['created_at'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <a class="reference-card-link" href="/patient/results">Shiko të gjitha recetat <span>›</span></a>
        </article>

        <article class="reference-card">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Financat</p>
                    <h2>Faturat e mia</h2>
                </div>
                <a class="reference-button" href="/patient/billing"><?= $icon('card'); ?> Billing</a>
            </div>
            <div class="reference-list">
                <?php foreach (array_slice($billingItems, 0, 2) as $billing): ?>
                    <div class="reference-list-row reference-list-row--billing">
                        <span><?= e('Fatura #' . $billing['id']); ?></span>
                        <strong><?= e(money($billing['amount'])); ?></strong>
                        <em class="reference-status reference-status--<?= e((string) $billing['status']); ?>"><?= e((string) $billing['status']); ?></em>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>

    <section class="reference-grid reference-grid--two reference-grid--lower">
        <article class="reference-card reference-card--short">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Analizat</p>
                    <h2>Rezultatet e fundit</h2>
                </div>
                <a class="reference-button" href="/patient/results">Shiko raportet <?= $icon('report'); ?></a>
            </div>
        </article>

        <article class="reference-card reference-card--short">
            <div class="reference-card-toolbar">
                <div>
                    <p class="eyebrow">Njoftimet</p>
                    <h2>Njoftimet e fundit</h2>
                </div>
                <a class="reference-button" href="/patient/notifications">Shiko të gjitha <?= $icon('bell'); ?></a>
            </div>
        </article>
    </section>
</section>
