<?php
require_once base_path('app/Views/public/_components.php');
$publicServices = ml_public_services($services ?? []);
?>

<section class="ml-page-hero ml-page-hero--services" aria-labelledby="services-hero-title">
    <div class="ml-container ml-page-hero__inner">
        <div class="ml-page-hero__content" data-reveal>
            <span class="ml-label">SHËRBIMET</span>
            <h1 id="services-hero-title">Shërbime të organizuara sipas departamenteve klinike.</h1>
            <p class="ml-lead">Në Med Life, çdo shërbim është i organizuar me kujdes sipas standardeve më të larta mjekësore për t'ju ofruar përvojën më të mirë të kujdesit shëndetësor.</p>
            <div class="ml-proof-row">
                <span><?= ml_icon('shield'); ?> Standarde të larta mjekësore</span>
                <span><?= ml_icon('users'); ?> Ekip multidisiplinar ekspertësh</span>
                <span><?= ml_icon('clock'); ?> Rezervim i shpejtë dhe i lehtë</span>
            </div>
        </div>
        <div class="ml-page-hero__visual ml-clinic-scene ml-clinic-scene--wide" data-reveal>
            <div class="ml-arch-frame">
                <img class="ml-hero-doctor-backdrop" src="<?= e(asset('images/optimized/doctor-hero.jpg')); ?>" alt="" aria-hidden="true" width="620" height="640">
                <img class="ml-hero-doctor" src="<?= e(asset('images/optimized/doctor-hero.jpg')); ?>" alt="Doktor i Med Life në klinikë moderne" width="620" height="640">
            </div>
            <div class="ml-orbit-line" aria-hidden="true"></div>
        </div>
    </div>
</section>

<section class="ml-section" aria-label="Lista e shërbimeve">
    <div class="ml-container">
        <div class="ml-service-grid">
            <?php foreach ($publicServices as $service): ?>
                <?= ml_service_card($service, 'full'); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= ml_cta_section(); ?>
