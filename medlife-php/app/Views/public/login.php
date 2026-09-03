<?php require_once base_path('app/Views/public/_components.php'); ?>

<section class="ml-auth-page ml-auth-page--login" aria-labelledby="login-title">
    <div class="ml-container ml-auth-layout">
        <div class="ml-auth-panel" data-reveal>
            <div class="ml-auth-panel__top">
                <?= ml_logo('/'); ?>
                <span class="ml-auth-badge">KYÇU</span>
            </div>

            <h1 id="login-title">Hyr në portalin Med Life.</h1>
            <p class="ml-lead">Qasuni në të dhënat tuaja shëndetësore, rezervimet dhe rezultatet e analizave në mënyrë të sigurt dhe të shpejtë.</p>

            <form action="/login" class="ml-form ml-form--auth" method="post">
                <?= csrf_field(); ?>
                <label>
                    <span>Email ose username</span>
                    <div class="ml-input-shell"><?= ml_icon('user'); ?><input name="identifier" type="text" placeholder="Shkruani emailin ose username" value="<?= e(old('identifier')); ?>" autocomplete="username"></div>
                </label>
                <label>
                    <span>Fjalëkalimi</span>
                    <div class="ml-input-shell"><?= ml_icon('lock'); ?><input name="password" type="password" placeholder="Shkruani fjalëkalimin" autocomplete="current-password"><?= ml_icon('eye', 'ml-input-trailing'); ?></div>
                </label>
                <div class="ml-form__row">
                    <label class="ml-checkbox">
                        <input type="checkbox" name="remember" checked>
                        <span>Më mbaj të kyçur</span>
                    </label>
                    <a class="ml-text-link" href="/forgot-password">Harrove fjalëkalimin?</a>
                </div>
                <button class="ml-btn ml-btn--primary ml-btn--block" type="submit"><span>Hyr</span><?= ml_icon('arrow'); ?></button>
            </form>

            <div class="ml-auth-separator"><span>ose</span></div>
            <p class="ml-auth-foot">Nuk ke një llogari? <a href="/register">Krijo llogari <?= ml_icon('arrow'); ?></a></p>
        </div>

        <div class="ml-auth-visual" data-reveal>
            <div class="ml-auth-visual__scene">
                <?= ml_logo('#'); ?>
            </div>
            <div class="ml-auth-security-card">
                <span class="ml-card-icon"><?= ml_icon('shield'); ?></span>
                <div>
                    <h2>Portali juaj. I sigurt. Privat. I mbrojtur.</h2>
                    <p>Të dhënat klinike qëndrojnë brenda sistemit.</p>
                </div>
                <ul>
                    <li><?= ml_icon('lock'); ?><span><strong>Hyrje e sigurt dhe e enkriptuar</strong>Mbrojtje e nivelit të lartë për të dhënat tuaja.</span></li>
                    <li><?= ml_icon('user'); ?><span><strong>Qasje vetëm për ju</strong>Informacioni juaj është personal dhe i mbrojtur.</span></li>
                    <li><?= ml_icon('shield'); ?><span><strong>Përputhshmëri me standardet</strong>Sistemi ynë ndjek standardet më të larta të sigurisë dhe privatësisë.</span></li>
                </ul>
                <p class="ml-security-note"><?= ml_icon('lock'); ?> Sistemi ynë është i certifikuar dhe monitorohet 24/7</p>
            </div>
        </div>
    </div>
</section>
