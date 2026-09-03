<section class="section">
    <div class="container narrow">
        <div class="panel-card">
            <p class="eyebrow">Gabim</p>
            <h1><?= e($title ?? 'Dicka shkoi gabim'); ?></h1>
            <p class="lead">Ka ndodhur nje gabim i papritur gjate perpunimit te kerkeses suaj.</p>

            <?php if (!empty($is_debug)): ?>
                <div class="stack-cards mt-24">
                    <div class="panel-subtle">
                        <strong>Mesazhi i gabimit</strong>
                        <p class="muted"><?= e($error_message ?? ''); ?></p>
                    </div>

                    <div class="panel-subtle">
                        <strong>Skedari dhe linja</strong>
                        <p class="muted"><?= e($error_file ?? ''); ?> (linja <?= e((string) ($error_line ?? '')); ?>)</p>
                    </div>

                    <?php if (!empty($stack_trace)): ?>
                        <div class="panel-subtle">
                            <strong>Stack Trace</strong>
                            <pre class="muted" style="white-space: pre-wrap; margin-top: 8px; font-size: 13px;"><?= e($stack_trace); ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="lead">Ju lutemi provoni perseri ose na kontaktoni nese problemi vazhdon.</p>
            <?php endif; ?>

            <div class="button-row mt-24">
                <a class="button button-primary" href="/">Ballina</a>
                <?php if (!empty($is_debug)): ?>
                    <a class="button button-secondary" href="/contact">Kontakt</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
