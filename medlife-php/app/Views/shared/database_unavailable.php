<section class="section">
    <div class="container narrow">
        <div class="panel-card">
            <p class="eyebrow">Setup mode</p>
            <h1><?= e($title ?? 'Databaza nuk eshte gati'); ?></h1>
            <p class="lead"><?= e($setup_message ?? 'Portali u nis me sukses, por databaza ende nuk eshte gati per veprime qe ndryshojne ose lexojne te dhena operative.'); ?></p>

            <div class="stack-cards mt-24">
                <div class="panel-subtle">
                    <strong>Gabimi aktual</strong>
                    <p class="muted"><?= e($db_error ?? 'Lidhja me databazen deshtoi.'); ?></p>
                </div>

                <div class="panel-subtle">
                    <strong>Konfigurimi aktiv</strong>
                    <p class="muted">
                        Host: <?= e($db_host ?? '127.0.0.1'); ?> |
                        Port: <?= e($db_port ?? '3306'); ?> |
                        DB: <?= e($db_name ?? 'medlife'); ?> |
                        User: <?= e($db_user ?? 'root'); ?>
                    </p>
                </div>

                <div class="panel-subtle">
                    <strong>Kerkesa aktuale</strong>
                    <p class="muted">
                        Metoda: <?= e($request_method ?? 'GET'); ?> |
                        Path: <?= e($request_path ?? '/'); ?>
                    </p>
                </div>

                <div class="panel-subtle">
                    <strong>Cfare duhet te besh</strong>
                    <div class="meta-stack">
                        <span>1. Nise `start-med-life.cmd` nga root dhe sigurohu qe MySQL eshte aktiv.</span>
                        <span>2. Nese perdor DB te jashtme, perditeso `medlife-php\.env` me kredencialet reale.</span>
                        <span>3. Ekzekuto `medlife-php\migrate.cmd` dhe pastaj `medlife-php\seed-demo.cmd`.</span>
                        <span>4. Verifiko statusin me `medlife-php\php-runtime.cmd scripts\health_check.php`.</span>
                    </div>
                </div>
            </div>

            <div class="button-row mt-24">
                <a class="button button-primary" href="/">Riprovo</a>
                <a class="button button-secondary" href="/contact">Kontakt</a>
            </div>
        </div>
    </div>
</section>
