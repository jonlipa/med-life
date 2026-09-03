<?php
require_once base_path('app/Views/public/_components.php');
$publicDoctors = ml_public_doctors($doctors ?? []);
$filters = ['Kardiologji' => 'heart', 'Pediatri' => 'baby', 'Neurologji' => 'activity', 'Radiologji' => 'xray'];
?>

<section class="ml-page-hero ml-page-hero--doctors" aria-labelledby="doctors-hero-title">
    <div class="ml-container ml-page-hero__shell">
        <div class="ml-page-hero__content" data-reveal>
            <span class="ml-label">DOKTORËT</span>
            <h1 id="doctors-hero-title">Ekip multidisiplinar, kujdes i personalizuar për ju.</h1>
            <p class="ml-lead">Në Med Life bashkojmë përvojën, teknologjinë dhe përkushtimin për t'ju ofruar kujdes shëndetësor të standardeve më të larta.</p>
            <div class="ml-proof-row">
                <span><?= ml_icon('users'); ?> Ekip multidisiplinar</span>
                <span><?= ml_icon('shield'); ?> Standarde të larta</span>
                <span><?= ml_icon('heart'); ?> Qëndrimi juaj në qendër</span>
            </div>
        </div>
        <div class="ml-page-hero__visual ml-doctor-hero-visual" data-reveal>
            <div class="ml-floating-card ml-appointment-card">
                <h2>Rezervo termin tuaj</h2>
                <p><span><?= ml_icon('users'); ?></span>Zgjidh shërbimin</p>
                <p><span><?= ml_icon('calendar'); ?></span>Zgjidh datën &amp; orën</p>
                <p><span><?= ml_icon('clock'); ?></span>Konfirmo dhe vizitohuni</p>
                <a class="ml-btn ml-btn--primary" href="/register"><span>Rezervo tani</span><?= ml_icon('arrow'); ?></a>
            </div>
        </div>
    </div>
</section>

<section class="ml-section ml-section--doctor-directory" aria-label="Kërko doktor">
    <div class="ml-container">
        <div class="ml-filter-bar" data-reveal>
            <label class="ml-search-field" for="doctor-search">
                <?= ml_icon('search'); ?>
                <input type="search" id="doctor-search" placeholder="Kërko doktor ose specialitet..." autocomplete="off">
            </label>
            <div class="ml-filter-bar__pills" role="list" aria-label="Filtra specialitetesh">
                <button class="ml-pill is-active" type="button" data-filter="all">Të gjitha specialitetet</button>
                <?php foreach ($filters as $label => $icon): ?>
                    <button class="ml-pill" type="button" data-filter="<?= e($label); ?>"><?= ml_icon($icon); ?> <?= e($label); ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ml-doctor-directory" id="doctors-grid">
            <?php foreach ($publicDoctors as $doctor): ?>
                <?= ml_doctor_card($doctor, 'directory'); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?= ml_cta_section('Gati për t’u kujdesur për shëndetin tuaj?', 'Rezervo termin tani', '/register', 'ml-cta-band--slim'); ?>
