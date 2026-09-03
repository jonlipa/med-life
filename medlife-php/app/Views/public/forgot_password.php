<section class="section">
    <div class="container narrow">
        <div class="panel-card">
            <p class="eyebrow">Password reset</p>
            <h1>Regjistro kerkesen per reset.</h1>
            <p class="lead">Per momentin sistemi ruan token-in ne databaze; rikthimi final mund te lidhet me email provider ne fazen tjeter.</p>
            <?php if (setup_mode()): ?>
                <div class="panel-subtle mb-16">
                    <strong>Setup mode</strong>
                    <p class="muted">Kjo forme mund te hapet, por krijimi i token-it kerkon databazen aktive.</p>
                </div>
            <?php endif; ?>
            <form action="/forgot-password" class="form-grid" method="post">
                <?= csrf_field(); ?>
                <label>
                    <span>Email</span>
                    <input name="email" type="email" value="<?= e(old('email')); ?>">
                </label>
                <button class="button button-primary" type="submit">Regjistro kerkesen</button>
            </form>
        </div>
    </div>
</section>
