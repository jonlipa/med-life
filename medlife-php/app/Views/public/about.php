<?php
require_once base_path('app/Views/public/_components.php');
$features = [
    ['icon' => 'users', 'title' => 'Kujdes multidisiplinar', 'description' => 'Ekipet mjekësore, laboratorët dhe shërbimet specialiste punojnë në harmoni për të ofruar kujdes të koordinuar dhe të personalizuar.'],
    ['icon' => 'monitor', 'title' => 'Portal i unifikuar', 'description' => 'Të gjitha shërbimet tuaja në një vend: terminet, komunikimet, rezultatet e analizave, dosjet mjekësore, faturat dhe pagesat.'],
    ['icon' => 'bolt', 'title' => 'Shpejtësi dhe efikasitet', 'description' => 'Procese të automatizuara dhe transparente në çdo hap, për më pak pritje dhe më shumë kohë për kujdesin që ju nevojitet.'],
];
$trust = [
    ['icon' => 'shield', 'title' => 'Të dhënat tuaja të sigurta', 'description' => 'Mbrojtje maksimale dhe përputhje me standardet ndërkombëtare.'],
    ['icon' => 'users', 'title' => '50+ Mjekë dhe specialistë', 'description' => 'Mjekë dhe specialistë të përkushtuar.'],
    ['icon' => 'clock', 'title' => '24/7 Akses në shërbime', 'description' => 'Akses në shërbimet dhe të dhënat tuaja.'],
    ['icon' => 'smile', 'title' => 'Qëndruar në qendër', 'description' => 'Përvojë moderne, e thjeshtë dhe e orientuar te ju.'],
];
?>

<section class="ml-page-hero ml-page-hero--about" aria-labelledby="about-title">
    <div class="ml-container ml-page-hero__inner">
        <div class="ml-page-hero__content" data-reveal>
            <span class="ml-label">RRETH NESH</span>
            <h1 id="about-title">Një rrjedhë e vetme, <span>kujdes i integruar</span> për çdo hap.</h1>
            <p class="ml-lead">Med Life është portali i unifikuar i klinikës sonë, që lidh në mënyrë të sigurt çdo pjesë të udhëtimit të pacientit. Nga rezervimi i terminit te rezultatet e laboratorit, dosja mjekësore, faturimi dhe koordinimi i shërbimeve - gjithçka në një platformë të vetme, të besueshme dhe të lehtë për t’u përdorur.</p>
            <div class="ml-hero__actions">
                <a class="ml-btn ml-btn--primary ml-btn--lg" href="/register"><?= ml_icon('calendar'); ?><span>Rezervo termin</span></a>
                <a class="ml-btn ml-btn--ghost ml-btn--lg" href="/doctors"><?= ml_icon('user'); ?><span>Gjej doktorin</span></a>
            </div>
        </div>
        <div class="ml-about-orbit" data-reveal aria-hidden="true">
            <div class="ml-orbit-ring ml-orbit-ring--outer"></div>
            <div class="ml-orbit-ring ml-orbit-ring--inner"></div>
            <div class="ml-orbit-center"><?= ml_logo('#', 'ml-logo--stacked'); ?></div>
            <span class="ml-orbit-node ml-orbit-node--calendar"><?= ml_icon('calendar'); ?></span>
            <span class="ml-orbit-node ml-orbit-node--doctor"><?= ml_icon('doctor'); ?></span>
            <span class="ml-orbit-node ml-orbit-node--folder"><?= ml_icon('folder'); ?></span>
            <span class="ml-orbit-node ml-orbit-node--document"><?= ml_icon('document'); ?></span>
        </div>
    </div>
</section>

<section class="ml-section" aria-label="Përfitimet kryesore">
    <div class="ml-container">
        <div class="ml-feature-grid">
            <?php foreach ($features as $feature): ?>
                <article class="ml-feature-card" data-reveal>
                    <span class="ml-card-icon"><?= ml_icon($feature['icon']); ?></span>
                    <div>
                        <h2><?= e($feature['title']); ?></h2>
                        <p><?= e($feature['description']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="ml-section ml-section--trust" aria-label="Besueshmëria e Med Life">
    <div class="ml-container">
        <div class="ml-trust-strip">
            <?php foreach ($trust as $item): ?>
                <article class="ml-trust-item" data-reveal>
                    <span class="ml-card-icon"><?= ml_icon($item['icon']); ?></span>
                    <div>
                        <h3><?= e($item['title']); ?></h3>
                        <p><?= e($item['description']); ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
