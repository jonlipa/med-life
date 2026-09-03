<section class="section">
    <div class="container narrow">
        <div class="panel-card">
            <p class="eyebrow">Ndrysho fjalkalimin</p>
            <h1>Vendos fjalkalimin e ri.</h1>
            <p class="lead">Zgjidhni nje fjalkalim te sigurt dhe te dallueshem.</p>
            <?php if (!empty($setupRequired)): ?>
                <div class="panel-subtle mb-16">
                    <strong>Setup mode</strong>
                    <p class="muted">Reset password kerkon databazen aktive per te verifikuar token-in.</p>
                </div>
            <?php endif; ?>
            <form action="/reset-password" class="form-grid" method="post">
                <?= csrf_field(); ?>
                <input name="token" type="hidden" value="<?= e($token); ?>">
                <label>
                    <span>Fjalkalimi i ri</span>
                    <input name="password" type="password" autocomplete="new-password" <?= empty($token) ? 'disabled' : ''; ?>>
                </label>
                <label>
                    <span>Konfirmo fjalkalimin</span>
                    <input name="password_confirmation" type="password" autocomplete="new-password" <?= empty($token) ? 'disabled' : ''; ?>>
                </label>
                <button class="button button-primary" type="submit" <?= empty($token) ? 'disabled' : ''; ?>>Ndrysho fjalkalimin</button>
            </form>
        </div>
    </div>
</section>
