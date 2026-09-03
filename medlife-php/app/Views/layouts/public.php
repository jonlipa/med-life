<?php require_once base_path('app/Views/public/_components.php'); ?>
<!DOCTYPE html>
<html lang="sq">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Med Life - klinikë moderne në Prishtinë. Termine, doktorë, shërbime klinike dhe portal i sigurt për pacientët.">
    <meta name="theme-color" content="#078DA3">
    <title><?= e($title ?? config('APP_NAME', 'Med Life')); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(asset('css/app.css')); ?>">
    <link rel="stylesheet" href="<?= e(asset('css/public-redesign.css')); ?>">
    <script src="<?= e(asset('js/app.js')); ?>" defer></script>
</head>
<body class="public-body public-redesign">
    <a class="skip-link" href="#main-content">Kalo te përmbajtja</a>

    <header class="ml-header" id="site-header">
        <div class="ml-container ml-header__inner">
            <?= ml_logo('/'); ?>

            <button class="ml-nav-toggle" type="button" id="nav-toggle" aria-controls="main-nav" aria-expanded="false" aria-label="Hap menunë">
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
                <span aria-hidden="true"></span>
            </button>

            <nav class="ml-nav" id="main-nav" aria-label="Navigimi kryesor">
                <?php foreach (public_navigation() as $item): ?>
                    <?php $active = is_active($item['path'], $item['path'] === '/'); ?>
                    <a class="ml-nav__link<?= $active ? ' is-active' : ''; ?>" href="<?= e($item['path']); ?>"<?= $active ? ' aria-current="page"' : ''; ?>>
                        <?= e($item['label']); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <div class="ml-header__actions">
                <?php if (app('auth')->check()): ?>
                    <a class="ml-btn ml-btn--ghost" href="<?= e(role_home(app('auth')->role() ?? 'patient')); ?>">Portal</a>
                <?php else: ?>
                    <a class="ml-nav__link ml-nav__link--login<?= is_active('/login') ? ' is-active' : ''; ?>" href="/login">Hyr</a>
                    <a class="ml-btn ml-btn--primary" href="/register">Regjistrohu</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main id="main-content">
        <div class="ml-container ml-system-messages">
            <?php require base_path('app/Views/partials/flash.php'); ?>
        </div>
        <?= $content; ?>
    </main>

    <footer class="ml-footer">
        <div class="ml-container">
            <div class="ml-footer__grid">
                <div class="ml-footer__brand">
                    <?= ml_logo('/'); ?>
                    <p>Klinikë moderne në Prishtinë, e fokusuar në kujdes cilësor, teknologji të avancuar dhe përvojë të shkëlqyer për pacientët.</p>
                    <div class="ml-footer__social">
                        <a href="#" aria-label="Facebook">f</a>
                        <a href="#" aria-label="Instagram">ig</a>
                        <a href="#" aria-label="LinkedIn">in</a>
                    </div>
                </div>

                <div class="ml-footer__col">
                    <h4>Navigimi</h4>
                    <a href="/">Ballina</a>
                    <a href="/services">Shërbimet</a>
                    <a href="/doctors">Doktorët</a>
                    <a href="/about">Rreth Nesh</a>
                    <a href="/contact">Kontakt</a>
                </div>

                <div class="ml-footer__col">
                    <h4>Shërbimet</h4>
                    <a href="/services">Kardiologji</a>
                    <a href="/services">Laborator</a>
                    <a href="/services">Mjekësi Familjare</a>
                    <a href="/services">Pediatri</a>
                    <a href="/services">Radiologji</a>
                </div>

                <div class="ml-footer__col ml-footer__contact">
                    <h4>Kontaktoni</h4>
                    <p><?= ml_icon('map'); ?> Rr. Ilaz Kodra, Prishtinë 10000, Kosovë</p>
                    <p><?= ml_icon('phone'); ?> +383 38 555 100</p>
                    <p><?= ml_icon('mail'); ?> info@medlife-ks.com</p>
                    <p><?= ml_icon('clock'); ?> E hënë - e premte: 08:00 - 18:00<br>E shtunë: 09:00 - 14:00</p>
                </div>

                <div class="ml-footer__col">
                    <h4>Na ndiqni</h4>
                    <div class="ml-compliance">
                        <?= ml_icon('shield'); ?>
                        <div><strong>HIPAA</strong><span>COMPLIANT</span></div>
                    </div>
                </div>
            </div>
            <div class="ml-footer__bottom">
                <p>&copy; <?= date('Y'); ?> Med Life. Të gjitha të drejtat e rezervuara.</p>
            </div>
        </div>
    </footer>
</body>
</html>
