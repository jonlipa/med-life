<?php
require_once base_path('app/Views/public/_components.php');

$publicServices = array_slice(ml_public_services($services ?? []), 0, 4);
$featuredDoctors = array_slice(ml_public_doctors($featuredDoctors ?? []), 0, 3);
$heroStats = [
    ['value' => '3,287', 'label' => 'Pacientë aktivë', 'description' => 'Na besojnë kujdesin e tyre.', 'icon' => 'users'],
    ['value' => '46', 'label' => 'Doktorë aktivë', 'description' => 'Ekip multidisiplinar i përkushtuar.', 'icon' => 'doctor'],
    ['value' => '7,842', 'label' => 'Termine në sistem', 'description' => 'Koordinim i shpejtë & i lehtë.', 'icon' => 'calendar'],
];
$whyUs = [
    ['icon' => 'shield', 'title' => 'Të dhënat tuaja të sigurta', 'description' => 'Mbrojtje maksimale dhe përputhje me standardet.'],
    ['icon' => 'users', 'title' => 'Ekip multidisiplinar', 'description' => 'Mjekë dhe specialistë të përkushtuar për ju.'],
    ['icon' => 'clock', 'title' => 'Akses 24/7', 'description' => 'Portali i disponueshëm kurdo dhe kudo.'],
    ['icon' => 'smile', 'title' => 'Përvojë e shkëlqyer', 'description' => 'Shërbim mjekësor, i shpejtë dhe i personalizuar.'],
];
?>

<section class="ml-hero ml-hero--home" aria-labelledby="hero-title">
    <div class="ml-container ml-hero__inner">
        <div class="ml-hero__content" data-reveal>
            <span class="ml-label"><?= ml_icon('activity'); ?> KLINIKË MODERNE NË PRISHTINË</span>
            <h1 id="hero-title">Kujdes modern.<br><span>Shëndet për jetën tuaj.</span></h1>
            <p class="ml-lead">Med Life është klinika juaj e besuar në Prishtinë. Rezervoni shpejt, takohuni me mjekë multidisiplinarë dhe menaxhoni kujdesin tuaj përmes portalit tonë të sigurt.</p>
            <div class="ml-hero__actions">
                <a class="ml-btn ml-btn--primary ml-btn--lg" href="/register"><?= ml_icon('calendar'); ?><span>Rezervo termin</span></a>
                <a class="ml-btn ml-btn--ghost ml-btn--lg" href="/doctors"><?= ml_icon('user'); ?><span>Gjej doktorin</span></a>
            </div>
        </div>

        <div class="ml-hero__visual ml-clinic-scene" data-reveal>
            <div class="ml-arch-frame">
                <img class="ml-hero-doctor-backdrop" src="<?= e(asset('images/optimized/doctor-hero.jpg')); ?>" alt="" aria-hidden="true" width="620" height="640">
                <img class="ml-hero-doctor" src="<?= e(asset('images/optimized/doctor-hero.jpg')); ?>" alt="Doktor i Med Life me veshje mjekësore" width="620" height="640" fetchpriority="high">
            </div>
            <div class="ml-orbit-line" aria-hidden="true"></div>
            <div class="ml-floating-card ml-appointment-card">
                <h2>Rezervo termin tuaj</h2>
                <p><span><?= ml_icon('lab'); ?></span>Zgjidh shërbimin</p>
                <p><span><?= ml_icon('calendar'); ?></span>Zgjidh datën &amp; orën</p>
                <p><span><?= ml_icon('shield'); ?></span>Konfirmo dhe vizitohuni</p>
                <a class="ml-btn ml-btn--primary" href="/register"><span>Rezervo tani</span><?= ml_icon('arrow'); ?></a>
            </div>
        </div>
    </div>
</section>

<section class="ml-stats-strip" aria-label="Statistika klinike">
    <div class="ml-container">
        <div class="ml-stats-strip__grid">
            <?php foreach ($heroStats as $stat): ?>
                <?= ml_stat_card($stat['value'], $stat['label'], $stat['description'], $stat['icon']); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ml-section ml-section--services-preview" aria-labelledby="services-title">
    <div class="ml-container ml-split-section">
        <div class="ml-section__header" data-reveal>
            <span class="ml-label">SHËRBIMET TONA KRYESORE</span>
            <h2 id="services-title">Shërbime të avancuara për kujdes të plotë shëndetësor.</h2>
            <a class="ml-text-link" href="/services">Shiko të gjitha shërbimet <?= ml_icon('arrow'); ?></a>
        </div>
        <div class="ml-services-row">
            <?php foreach ($publicServices as $service): ?>
                <?= ml_service_card($service, 'compact'); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ml-section ml-section--doctors-preview" aria-labelledby="doctors-title">
    <div class="ml-container ml-split-section">
        <div class="ml-section__header" data-reveal>
            <span class="ml-label">EKIPI YNË MJEKËSOR</span>
            <h2 id="doctors-title">Profesionistë të dedikuar për shëndetin tuaj.</h2>
            <a class="ml-text-link" href="/doctors">Shiko të gjithë mjekët <?= ml_icon('arrow'); ?></a>
        </div>
        <div class="ml-doctors-row">
            <?php foreach ($featuredDoctors as $doctor): ?>
                <?= ml_doctor_card($doctor, 'preview'); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ml-why-strip" aria-labelledby="why-title">
    <div class="ml-container">
        <span class="ml-label" id="why-title">PSE TË ZGJIDHNI MED LIFE?</span>
        <div class="ml-why-strip__grid">
            <?php foreach ($whyUs as $item): ?>
                <article class="ml-why-item" data-reveal>
                    <span class="ml-line-icon"><?= ml_icon($item['icon']); ?></span>
                    <div>
                        <h3><?= e($item['title']); ?></h3>
                        <p><?= e($item['description']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= ml_cta_section(); ?>
