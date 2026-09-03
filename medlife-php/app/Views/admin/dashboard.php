<?php
$pageTitle = 'Paneli i Administratorit';
$adminHeroImage = asset('images/admin-hero-reference.png');
$adminMetricCards = [
    [
        'label' => 'Totali pacientë',
        'value' => $metrics[0]['value'] ?? 0,
        'description' => 'Pacientë të regjistruar',
        'icon' => 'patients',
        'tone' => 'teal',
    ],
    [
        'label' => 'Doktorë aktiv',
        'value' => $metrics[1]['value'] ?? 0,
        'description' => 'Mjekë të disponueshëm',
        'icon' => 'doctor',
        'tone' => 'blue',
    ],
    [
        'label' => 'Termine sot',
        'value' => $metrics[2]['value'] ?? 0,
        'description' => 'Takime të planifikuara',
        'icon' => 'calendar',
        'tone' => 'amber',
    ],
    [
        'label' => 'Të ardhura totale',
        'value' => $metrics[3]['value'] ?? money($billingSummary['total']),
        'description' => 'Totali i ardhurave',
        'icon' => 'wallet',
        'tone' => 'green',
    ],
];
$chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
$departmentIconMap = [
    'Kardiologji' => 'heart',
    'Pediatri' => 'child',
    'Neurologji' => 'brain',
    'Radiologji' => 'scan',
];

$adminIcon = static function (string $name): string {
    $icons = [
        'patients' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm10 0a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM3.5 20v-1.3A4.7 4.7 0 0 1 8.2 14h.6a4.7 4.7 0 0 1 4.7 4.7V20m-3-5.1A4.9 4.9 0 0 1 15.2 12h.6a4.7 4.7 0 0 1 4.7 4.7V20"/></svg>',
        'doctor' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 9v-1a7 7 0 0 1 14 0v1M12 15v5m-2.5-2.5h5"/></svg>',
        'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3v4m10-4v4M4 8h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Zm3 7h3v3H8Zm5 0h3v3h-3Z"/></svg>',
        'wallet' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7.5A2.5 2.5 0 0 1 6.5 5H19v14H6.5A2.5 2.5 0 0 1 4 16.5v-9Zm14 5h3v4h-3a2 2 0 0 1 0-4Z"/></svg>',
        'heart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.2 6.6a5 5 0 0 0-7.1 0L12 7.7l-1.1-1.1a5 5 0 1 0-7.1 7.1L12 22l8.2-8.3a5 5 0 0 0 0-7.1Z"/><path d="M4 12h4l2-4 3 8 2-4h5"/></svg>',
        'child' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm-7 8a7 7 0 0 1 14 0M8 10l-3 2m11-2 3 2"/></svg>',
        'brain' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 4a4 4 0 0 0-4 4v8a4 4 0 0 0 4 4m6-16a4 4 0 0 1 4 4v8a4 4 0 0 1-4 4M9 4v16m6-16v16M5 10h4m6 0h4M5 14h4m6 0h4"/></svg>',
        'scan' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 4h14v16H5z"/><path d="M9 8h6M9 12h6M9 16h6"/></svg>',
    ];

    return $icons[$name] ?? $icons['doctor'];
};
?>

<section class="admin-dashboard-view">
    <section class="admin-hero-panel">
        <div class="admin-hero-copy">
            <p class="eyebrow">Kontrolli Admin</p>
            <h2>Mirë se vini në panelin e administratorit.</h2>
            <p>Menaxhoni sistemin, monitoroni aktivitetet dhe siguroni një përvojë të shkëlqyer për pacientët dhe stafin.</p>
        </div>
        <div class="admin-hero-visual">
            <img alt="Administrator Med Life" src="<?= e($adminHeroImage); ?>">
        </div>
    </section>

    <section class="admin-metrics-grid">
        <?php foreach ($adminMetricCards as $metric): ?>
            <article class="admin-metric-card admin-metric-card--<?= e($metric['tone']); ?>">
                <span class="admin-metric-icon"><?= $adminIcon($metric['icon']); ?></span>
                <span class="admin-metric-copy">
                    <span><?= e((string) $metric['label']); ?></span>
                    <strong><?= e((string) $metric['value']); ?></strong>
                    <small><?= e((string) $metric['description']); ?></small>
                </span>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="admin-overview-grid">
        <article class="admin-chart-card">
            <div class="admin-card-toolbar">
                <div>
                    <p class="eyebrow">Analitika</p>
                    <h2>Terminet dhe ritmi operativ</h2>
                </div>
                <a class="button button-secondary" href="/admin/reports">Shiko raportet</a>
            </div>
            <div class="admin-chart">
                <div class="admin-chart-scale">
                    <span>40</span>
                    <span>30</span>
                    <span>20</span>
                    <span>10</span>
                    <span>0</span>
                </div>
                <div class="admin-chart-plot">
                    <?php foreach ($chartLabels as $index => $label): ?>
                        <div class="admin-chart-column">
                            <span class="admin-chart-bar admin-chart-bar--<?= e((string) ($index + 1)); ?>"></span>
                            <small><?= e($label); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </article>

        <article class="admin-departments-card">
            <p class="eyebrow">Departamentet</p>
            <h2>Ngarkesa kryesore</h2>
            <div class="admin-department-list">
                <?php foreach (array_slice($departmentLoad, 0, 3) as $department): ?>
                    <?php $departmentName = (string) $department['department']; ?>
                    <div class="admin-department-row">
                        <span class="admin-department-icon"><?= $adminIcon($departmentIconMap[$departmentName] ?? 'scan'); ?></span>
                        <strong><?= e($departmentName); ?></strong>
                        <span><?= e((string) $department['patients_count']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </article>
    </section>
</section>
